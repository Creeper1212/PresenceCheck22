<?php
// Purpose: Display a success message when the user successfully checks in for the day.
// Redirects to index.php after 10 seconds.

// Include required files
require_once 'Dashboard/config.php';      // Database configuration
require_once 'Dashboard/error_handler.php'; // Error handling

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Retrieve user data from session
$userName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ($translations['user'] ?? 'User');
$checkInTime = isset($_SESSION['checkin_time']) ? htmlspecialchars($_SESSION['checkin_time']) : ($translations['unknown_time'] ?? 'Unknown time');
$checkInStatus = isset($_SESSION['checkin_status']) ? htmlspecialchars($_SESSION['checkin_status']) : '';

// Set page title and description
$pageTitle = $translations['success_checked_in'] ?? 'Check-In Success';
$pageDescription = sprintf($translations['success_thank_you'] ?? 'Thank you, %s! You will be redirected shortly.', $userName);

// Include header
include 'Dashboard/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white text-center">
                    <h2 class="h4 mb-0"><?php echo $pageTitle; ?></h2>
                </div>
                <div class="card-body text-center">
                    <p class="lead">
                        <?php echo sprintf($translations['success_thank_you'] ?? 'Thank you, %s! Your presence has been recorded.', htmlspecialchars($userName)); ?>
                    </p>
                    
                    <div class="user-details my-4 p-3 bg-light rounded">
                        <p class="mb-1"><strong><?php echo $translations['date'] ?? 'Date'; ?>:</strong> <?php echo date('l, F j, Y'); ?></p>
                        <p class="mb-1"><strong><?php echo $translations['time'] ?? 'Time'; ?>:</strong> <?php echo $checkInTime; ?></p>
                        <p class="mb-0"><strong><?php echo $translations['status'] ?? 'Status'; ?>:</strong> <?php echo $checkInStatus; ?></p>
                    </div>
                    
                    <p>
                        <?php echo $translations['success_redirect'] ?? 'You will be redirected to the home page in'; ?> 
                        <span id="countdown" class="fw-bold text-success">10</span> 
                        <?php echo $translations['seconds'] ?? 'seconds'; ?>.
                    </p>
                    <button class="btn btn-primary mt-3" onclick="window.location.href='index.php';">
                        <?php echo $translations['go_back_now'] ?? 'Go Back Now'; ?>
                    </button>
                </div>
            </div>
            
            <?php echo displayError(); // Display any errors using centralized error handler ?>
        </div>
    </div>
</div>

<script>
    // Redirect to index.php after 10 seconds with countdown
    let secondsLeft = 10;
    
    function updateCountdown() {
        const countdownElement = document.getElementById('countdown');
        secondsLeft--;
        
        if (secondsLeft <= 0) {
            window.location.href = "index.php";
        } else {
            countdownElement.textContent = secondsLeft;
            setTimeout(updateCountdown, 1000);
        }
    }
    
    // Initialize countdown when page loads
    window.onload = function() {
        updateCountdown();
    };
    
    // Backup redirect in case the countdown fails
    setTimeout(function() {
        window.location.href = "index.php";
    }, 10500);
</script>

<?php
// Include footer
include 'Dashboard/footer.php';
?>
