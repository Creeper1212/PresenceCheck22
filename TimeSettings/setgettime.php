<?php
// Define the days array (Sunday = 0 ... Saturday = 6)
$days = array(
    0 => "Sonntag",
    1 => "Montag",
    2 => "Dienstag",
    3 => "Mittwoch",
    4 => "Donnerstag",
    5 => "Freitag",
    6 => "Samstag"
);

// Define default times
define('DEFAULT_START_TIME', '08:00');
define('DEFAULT_END_TIME', '18:00');

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $affected_rows = 0;
    foreach ($days as $index => $dayName) {
        // Get the posted start and end times for this day
        $start = $_POST["start_time_{$index}"] ?? DEFAULT_START_TIME;
        $end   = $_POST["end_time_{$index}"] ?? DEFAULT_END_TIME;

        // Use UPDATE to modify existing rows
        $stmt = $conn->prepare("UPDATE timesettings SET start_time = ?, end_time = ? WHERE day_name = ?");
        $stmt->bind_param("sss", $start, $end, $dayName);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $affected_rows++;
        }
        $stmt->close();
    }
    if ($affected_rows > 0) {
        $message = "Einstellungen erfolgreich gespeichert.";
    } else {
        $message = "Es wurden keine Änderungen vorgenommen.";
    }
}

// Fetch current settings from the database
$settings = array();
$result = $conn->query("SELECT day_name, start_time, end_time FROM timesettings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['day_name']] = array('start_time' => $row['start_time'], 'end_time' => $row['end_time']);
}
$result->free();
?>
