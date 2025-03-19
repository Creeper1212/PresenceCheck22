<?php
// Purpose: Main dashboard for user presence submission.
session_start();

require 'Dashboard/config.php';
require_once 'Dashboard/error_handler.php'; // Use centralized error handling
require_once 'Dashboard/time_range.php'; // Include time range logic

// Language Handling
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default to English
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'de'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$langFile = 'languages/' . $_SESSION['lang'] . '.php';
if (file_exists($langFile)) {
    $translations = include $langFile;
} else {
    $translations = include 'languages/en.php'; // Fallback to English
}

// Get today's date and initialize variables
$today = date('Y-m-d');
$alreadyLoggedIn = false;
$userName = '';
$checkInTime = '';

try {
    // Get user's name from the session if available
    if (isset($_SESSION['user_name'])) {
        $userName = $_SESSION['user_name'];
        
        // Check if this specific user has logged in today
        $stmt = $conn->prepare("SELECT name, time FROM presencetable WHERE name = ? AND date = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $userName, $today);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        // Check if the user is already logged in today
        if ($row = $result->fetch_assoc()) {
            $alreadyLoggedIn = true;
            $checkInTime = $row['time'];
        }
        
        $stmt->close();
    }
} catch (Exception $e) {
    handleError("Database Error", $e->getMessage(), null, true);
}

// Disable submit button if:
// 1. User already logged in today, OR
// 2. Outside of allowed time range
$disableSubmit = $alreadyLoggedIn || !$timeRangeInfo['is_in_range'];

// Include header
include 'Dashboard/header.php';
?>

<div class="container my-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h1 class="h3 mb-0"><?php echo $translations['dashboard']; ?></h1>
                </div>
                <div class="card-body">
                    <div class="welcome-section mb-4 text-center">
                        <p class="lead"><?php echo $translations['welcome_message']; ?></p>
                        
                        <!-- Display current time and system status -->
                        <div class="alert <?php echo $timeRangeInfo['is_in_range'] ? 'alert-success' : 'alert-warning'; ?> d-inline-block">
                            <strong>System Status:</strong> 
                            <?php echo $timeRangeInfo['is_in_range'] ? 'Active' : 'Inactive'; ?>
                            (<?php echo substr($timeRangeInfo['start_time'], 0, 5) . ' - ' . substr($timeRangeInfo['end_time'], 0, 5); ?>)
                        </div>
                    </div>
                    
                    <?php echo displayError(); // Display any errors from the session ?>
                    
                    <?php if ($alreadyLoggedIn): ?>
                        <div class="alert alert-success text-center" role="alert">
                            <h4 class="alert-heading">Already Checked In!</h4>
                            <p><?php echo sprintf($translations['already_checked_in'], htmlspecialchars($userName)); ?></p>
                            <hr>
                            <p class="mb-0">You checked in today at <strong><?php echo $checkInTime; ?></strong></p>
                        </div>
                    <?php else: ?>
                        <div class="presence-form mx-auto" style="max-width: 400px;">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title text-center mb-4">Check-In Form</h4>
                                    
                                    <?php if (!$timeRangeInfo['is_in_range']): ?>
                                        <div class="alert alert-warning text-center">
                                            <i class="fas fa-clock me-2"></i> Check-in is currently closed.
                                            <br>Available hours: <?php echo substr($timeRangeInfo['start_time'], 0, 5) . ' - ' . substr($timeRangeInfo['end_time'], 0, 5); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form action="presence_request.php" method="POST" novalidate>
                                        <div class="mb-3">
                                            <label for="formGroupExampleInput" class="form-label">
                                                <?php echo $translations['name']; ?>
                                            </label>
                                            <input type="text"
                                                   class="form-control form-control-lg"
                                                   id="formGroupExampleInput"
                                                   name="fname"
                                                   placeholder="<?php echo $translations['enter_your_name']; ?>"
                                                   value="<?php echo htmlspecialchars($userName); ?>"
                                                   required
                                                   pattern="[A-Za-z ]{2,50}"
                                                   title="<?php echo $translations['name_validation_title']; ?>"
                                                   <?php echo $disableSubmit ? 'disabled' : ''; ?>>
                                            <div class="form-text">Please enter your full name (2-50 characters, letters only).</div>
                                        </div>
                                        <button type="submit" 
                                                class="btn btn-primary btn-lg w-100" 
                                                style="background-color: #d40612; border-color: #d40612;" 
                                                <?php echo $disableSubmit ? 'disabled' : ''; ?>>
                                            <?php echo $translations['submit']; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'Dashboard/footer.php';
?>

<style>
    body {
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
    }
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        border-color: #dc3545;
    }
    .btn-primary {
        background-color: #dc3545;
        border-color: #dc3545;
        transition: transform 0.2s ease, background-color 0.2s ease;
        border-radius: 10px;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        background-color: #c82333;
        border-color: #c82333;
    }
    .alert {
        border-radius: 10px;
        animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
