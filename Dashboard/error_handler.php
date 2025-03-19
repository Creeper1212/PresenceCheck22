<?php
/**
 * Centralized error handler for the presence system
 * 
 * This file provides error handling functions for the application.
 */

// Set up custom error handler for PHP errors
set_error_handler("customErrorHandler");

/**
 * Custom PHP error handler
 * 
 * @param int $errno Error number
 * @param string $errstr Error message
 * @param string $errfile File where the error occurred
 * @param int $errline Line where the error occurred
 * @return bool
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "PHP Error [$errno] $errstr in $errfile on line $errline";
    error_log($error_message);
    
    // Don't display system path in production
    $errfile = basename($errfile);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['error_type'] = "System";
    $_SESSION['error_message'] = "An unexpected error occurred. Please try again later.";
    
    // For development only - comment out in production
    $_SESSION['error_detail'] = "$errstr in $errfile on line $errline";
    
    return true;
}

/**
 * Centralized error handler for application errors
 * 
 * @param string $errorType Type of error (Database, Validation, System, etc)
 * @param string $message Detailed error message
 * @param string|null $redirectTo Optional URL to redirect to
 * @param bool $displayToUser Whether to display this error to the user
 * @param int $logLevel Error log level (optional)
 */
function handleError($errorType, $message, $redirectTo = null, $displayToUser = true, $logLevel = 0) {
    // Log the error to server logs
    error_log("[$errorType] $message");
    
    // Store error in session for displaying to user if needed
    if ($displayToUser) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['error_type'] = $errorType;
        $_SESSION['error_message'] = $message;
        $_SESSION['error_time'] = date('Y-m-d H:i:s');
    }
    
    // Redirect if specified
    if ($redirectTo) {
        header("Location: $redirectTo");
        exit();
    }
}

/**
 * Display error message if it exists in session
 * 
 * @param bool $clear Whether to clear the error after displaying
 * @return string HTML for error message or empty string if no error
 */
function displayError($clear = true) {
    if (isset($_SESSION['error_type']) && isset($_SESSION['error_message'])) {
        $type = htmlspecialchars($_SESSION['error_type']);
        $message = htmlspecialchars($_SESSION['error_message']);
        $time = isset($_SESSION['error_time']) ? htmlspecialchars($_SESSION['error_time']) : '';
        
        // Debug details (for development only)
        $detail = isset($_SESSION['error_detail']) ? htmlspecialchars($_SESSION['error_detail']) : '';
        
        // Clear error from session if requested
        if ($clear) {
            unset($_SESSION['error_type']);
            unset($_SESSION['error_message']);
            unset($_SESSION['error_time']);
            unset($_SESSION['error_detail']);
        }
        
        $html = "<div class='alert alert-danger' role='alert'>";
        $html .= "<strong>$type Error:</strong> $message";
        
        if (!empty($time)) {
            $html .= "<br><small>Time: $time</small>";
        }
        
        // Only show details in development environment
        if (!empty($detail) && $_SERVER['SERVER_NAME'] === 'localhost') {
            $html .= "<hr><details><summary>Technical Details (Dev only)</summary>$detail</details>";
        }
        
        $html .= "</div>";
        
        return $html;
    }
    return "";
}

/**
 * Record a successful operation
 * 
 * @param string $message Success message
 * @param string|null $redirectTo Optional URL to redirect to
 */
function recordSuccess($message, $redirectTo = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['success_message'] = $message;
    $_SESSION['success_time'] = date('Y-m-d H:i:s');
    
    if ($redirectTo) {
        header("Location: $redirectTo");
        exit();
    }
}

/**
 * Display success message if it exists in session
 * 
 * @param bool $clear Whether to clear the message after displaying
 * @return string HTML for success message or empty string if no message
 */
function displaySuccess($clear = true) {
    if (isset($_SESSION['success_message'])) {
        $message = htmlspecialchars($_SESSION['success_message']);
        $time = isset($_SESSION['success_time']) ? htmlspecialchars($_SESSION['success_time']) : '';
        
        if ($clear) {
            unset($_SESSION['success_message']);
            unset($_SESSION['success_time']);
        }
        
        $html = "<div class='alert alert-success' role='alert'>";
        $html .= "<i class='fas fa-check-circle me-2'></i> $message";
        
        if (!empty($time)) {
            $html .= "<br><small>Time: $time</small>";
        }
        
        $html .= "</div>";
        
        return $html;
    }
    return "";
}
?>
