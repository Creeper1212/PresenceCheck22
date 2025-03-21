<?php
// Zweck: Haupt-Dashboard für die Einreichung der Benutzeranwesenheit.
session_start();

require 'Dashboard/config.php';
require_once 'Dashboard/error_handler.php'; // Verwende zentrale Fehlerbehandlung

$today = date('Y-m-d');
$alreadyLoggedIn = false;
$userName = '';

try {
    // Überprüfe, ob dieser spezielle Benutzer sich heute schon angemeldet hat
    $stmt = $conn->prepare("SELECT name FROM presencetable WHERE date = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $today);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();

    // Überprüfe, ob Zeilen zurückgegeben wurden
    if ($row = $result->fetch_assoc()) {
        $userName = $row["name"];
        $alreadyLoggedIn = true;
    }
    $stmt->close();

} catch (Exception $e) {
    handleError("Database Error", $e->getMessage(), null, true);
}

// Deaktiviere den Absenden-Button, wenn bereits angemeldet
$disabled = $alreadyLoggedIn ? 'disabled' : '';

// Include Header
include 'Dashboard/header.php';
?>

<div class="container my-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="welcome-section mb-4 text-center">
                <h1 class="display-4">Dashboard</h1>
                <p class="lead">Willkommen im Dashboard. Hier kannst du deine Anwesenheit bestätigen.</p>
            </div>
        </div>
        <div class="col-lg-12">
            <?php echo displayError(); // Zeige alle Fehler aus der Session an ?>

            <?php if ($alreadyLoggedIn): ?>
                <div class="alert alert-success text-center" role="alert">
                    Hallo <?php echo htmlspecialchars($userName); ?>, du hast dich für heute schon angemeldet.
                </div>
            <?php else: ?>
                <div class="presence-form mx-auto" style="max-width: 400px;">
                    <form action="presence_request.php" method="POST">
                        <div class="mb-3">
                            <label for="formGroupExampleInput" class="form-label">Name</label>
                            <input type="text"
                                   class="form-control"
                                   id="formGroupExampleInput"
                                   name="fname"
                                   placeholder="Gib deinen Namen ein"
                                   value="<?php echo htmlspecialchars($userName); ?>"
                                   required
                                   pattern="[A-Za-z ]{2,50}"
                                   title="Der Name muss 2-50 Zeichen lang sein und darf nur Buchstaben und Leerzeichen enthalten">
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #d40612; border-color: #d40612;" <?= $disabled; ?>>Absenden</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include 'AI.php';
include 'Dashboard/footer.php';
?>