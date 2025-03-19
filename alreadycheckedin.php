<?php
// Purpose: Display a message when the user has already checked in.
// Redirects to index.php after 5 seconds.

// Start the session to access any error messages
session_start();

// Include required files for consistent functionality
require_once 'Dashboard/config.php';
require_once 'Dashboard/error_handler.php';

// Language Handling to match the rest of your application
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

// Get username if available
$username = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['already_checked_in_title'] ?? 'Already Checked In'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --asb-red: #d40612;
            --asb-yellow: #ffee00;
        }
        
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Roboto', sans-serif;
        }
        
        .container {
            margin-top: 50px;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 500px;
            text-align: center;
        }
        
        .countdown {
            font-weight: bold;
            color: var(--asb-red);
            font-size: 1.2rem;
            margin-top: 10px;
        }
        
        /* ASB branding colors */
        .text-danger {
            color: var(--asb-red) !important;
        }
        
        .btn-primary {
            background-color: var(--asb-red);
            border-color: var(--asb-red);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #b00510;
            border-color: #b00510;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(212, 6, 18, 0.3);
        }
    </style>
    <script>
        // Redirect to index.php after 5 seconds with countdown
        let secondsLeft = 5;
        
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
        }, 5500);
    </script>
</head>
<body>
    <div class="container">
        <h1 class="mb-4 text-danger"><?php echo $translations['already_checked_in_title'] ?? 'Already Checked In!'; ?></h1>
        <p class="lead">
            <?php 
            $messageTemplate = $translations['already_checked_in'] ?? 'Hello, %s! You have already checked in today.';
            // Handle both {name} and %s formats that appear in your translation files
            if (strpos($messageTemplate, '{name}') !== false) {
                echo str_replace('{name}', $username, $messageTemplate);
            } else {
                echo sprintf($messageTemplate, $username);
            }
            ?>
        </p>
        <p><?php echo $translations['already_checked_in_message'] ?? 'You will be redirected to the home page in'; ?> <span id="countdown" class="countdown">5</span> <?php echo $translations['seconds'] ?? 'seconds'; ?>.</p>
        <button class="btn btn-primary mt-3" onclick="window.location.href='index.php';"><?php echo $translations['go_back_now'] ?? 'Go Back Now'; ?></button>
    </div>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger mt-3" role="alert">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
    <?php endif; ?>
</body>
</html>
