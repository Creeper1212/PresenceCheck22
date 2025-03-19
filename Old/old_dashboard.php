<?php
// Purpose: Main dashboard for user presence submission.
session_start();

require 'Dashboard/config.php';
require_once 'Dashboard/error_handler.php'; // Use centralized error handling

$today = date('Y-m-d');
$alreadyLoggedIn = false;
$userName = '';

try {
    // Check if this specific user has logged in today
    $stmt = $conn->prepare("SELECT name FROM presencetable WHERE date = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $today);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();

    // Check if any rows were returned
    if ($row = $result->fetch_assoc()) {
        $userName = $row["name"];
        $alreadyLoggedIn = true;
    }
    $stmt->close();

} catch (Exception $e) {
    handleError("Database Error", $e->getMessage(), null, true);
}

// Disable submit button if already logged in
$disabled = $alreadyLoggedIn ? 'disabled' : '';

// Include header
include 'Dashboard/header.php';
?>

<div class="container my-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="welcome-section mb-4 text-center">
                <h1 class="display-4">Dashboard</h1>
                <p class="lead">Welcome to the dashboard. Here you can submit your presence.</p>
            </div>
        </div>
        <div class="col-lg-12">
            <?php echo displayError(); // Display any errors from the session ?>

            <?php if ($alreadyLoggedIn): ?>
                <div class="alert alert-success text-center" role="alert">
                    Hello <?php echo htmlspecialchars($userName); ?>, you have already checked in for today.
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
                                   placeholder="Enter your name"
                                   value="<?php echo htmlspecialchars($userName); ?>"
                                   required
                                   pattern="[A-Za-z ]{2,50}"
                                   title="Name must be 2-50 characters and contain only letters and spaces">
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #d40612; border-color: #d40612;" <?= $disabled; ?>>Submit</button>
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

