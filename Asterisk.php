<?php
// Configuration for the contacts database
include 'Dashboard/config.php'; // Adjust path as needed

// Create database connection
$contacts_conn = new mysqli(
    $contacts_db_config['servername'],
    $contacts_db_config['username'],
    $contacts_db_config['password'],
    $contacts_db_config['database']
);

if ($contacts_conn->connect_error) {
    error_log("Contacts DB Connection failed: " . $contacts_conn->connect_error);
    echo "Failed to connect to contacts database.\n";
    exit(1);
}

// AMI configuration
$ami_config = [
    'host'     => '10.24.100.20', // Update as per your setup
    'port'     => 5038,
    'username' => 'mark',         // Update as per your AMI user
    'password' => 'Bremen2025'     // Update as per your AMI password
];

// Fetch contacts with multiple phone numbers
$contacts = [];
$sql = "SELECT id, phone_number1, phone_number2, phone_number3, duration FROM contacts ORDER BY id";
try {
    $result = $contacts_conn->query($sql);
    if (!$result) throw new Exception($contacts_conn->error);
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
    $result->free();
} catch (Exception $e) {
    error_log("Error fetching contacts: " . $e->getMessage());
    echo "Error fetching contacts: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Fetched " . count($contacts) . " contacts successfully.\n";

// Connect to Asterisk AMI
$socket = fsockopen($ami_config['host'], $ami_config['port'], $errno, $errstr, 30);
if (!$socket) {
    echo "AMI connection failed: $errstr ($errno)\n";
    exit(1);
}

// Login to AMI
fputs($socket, "Action: Login\r\n");
fputs($socket, "Username: {$ami_config['username']}\r\n");
fputs($socket, "Secret: {$ami_config['password']}\r\n\r\n");

// Read and ignore initial events (like FullyBooted)
while ($line = fgets($socket)) {
    if (trim($line) === "") {
        break;
    }
}
echo "AMI login successful.\n";

// Audio file to play (if needed)
$audio_file = 'Sound.mp3'; // Adjust if needed

// Process each contact
foreach ($contacts as $contact) {
    $id = $contact['id'];
    echo "Processing contact ID {$id}...\n";

    // Collect and filter phone numbers
    $numbers = array_filter([
        $contact['phone_number1'],
        $contact['phone_number2'],
        $contact['phone_number3']
    ], function($num) { return !empty($num); });

    if (empty($numbers)) {
        echo "No valid phone numbers for contact ID {$id}\n";
        $log_sql = "INSERT INTO call_log (contact_id, status, call_time) VALUES (?, 'NO_NUMBERS', NOW())";
        $stmt = $contacts_conn->prepare($log_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        continue;
    }

    // Prepare variables for Asterisk
    $variables = [
        "contact_id={$id}",
        "AUDIO_FILE={$audio_file}"
    ];
    $i = 1;
    foreach ($numbers as $number) {
        $variables[] = "number{$i}={$number}";
        $i++;
        if ($i > 3) break;
    }

    // Generate a unique ActionID for this originate command
    $actionID = "call_{$id}_" . time();

    // Use Local/100@default/n as the channel.
    // /n verhindert, dass Asterisk erneut den Dialplan auswertet, was sonst zum Fehler "Extension does not exist" führen kann.
    // Stelle sicher, dass deine Dialplan-Konfiguration (Extension 100 im Kontext [default]) korrekt geladen wurde.
    $originateCmd  = "Action: Originate\r\n";
    $originateCmd .= "Channel: PJSIP/004942143812199@easybell\r\n";
    $originateCmd .= "Context: default\r\n";
    $originateCmd .= "Exten: 100\r\n";
    $originateCmd .= "Priority: 1\r\n";
    $originateCmd .= "Variable: " . implode(',', $variables) . "\r\n";
    $originateCmd .= "CallerID: YourCallerID <1234567890>\r\n"; // Update CallerID as needed
    $originateCmd .= "Async: yes\r\n";
    $originateCmd .= "ActionID: {$actionID}\r\n\r\n";

    fputs($socket, $originateCmd);

    // Wait for response matching our ActionID (timeout after 300 sec)
    $start_time = time();
    $responseReceived = false;
    $fullResponse = "";
    while ((time() - $start_time) < 300) {
        $line = fgets($socket);
        if ($line === false) {
            sleep(1);
            continue;
        }
        $fullResponse .= $line;
        // A blank line indicates end of a response block
        if (trim($line) === "") {
            if (strpos($fullResponse, "ActionID: {$actionID}") !== false) {
                if (strpos($fullResponse, 'Response: Success') !== false) {
                    echo "Contact ID {$id} call originated successfully.\n";
                    $log_status = 'ANSWERED';
                } else {
                    echo "Failed to originate call for contact ID {$id}: $fullResponse\n";
                    $log_status = 'ORIGINATE_FAILED';
                }
                $log_sql = "INSERT INTO call_log (contact_id, status, call_time) VALUES (?, ?, NOW())";
                $stmt = $contacts_conn->prepare($log_sql);
                $stmt->bind_param("is", $id, $log_status);
                $stmt->execute();
                $stmt->close();
                $responseReceived = true;
                break;
            }
            $fullResponse = "";
        }
    }

    if (!$responseReceived) {
        echo "Timeout waiting for response for contact ID {$id}\n";
        $log_sql = "INSERT INTO call_log (contact_id, status, call_time) VALUES (?, 'TIMEOUT', NOW())";
        $stmt = $contacts_conn->prepare($log_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Logout from AMI and cleanup
fputs($socket, "Action: Logoff\r\n\r\n");
fclose($socket);
$contacts_conn->close();

echo "Call campaign completed.\n";
?>

