<?php
session_start();

// Verify session validity
if (!isset($_SESSION["username"]) || 
    !isset($_SESSION['ip']) || $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] ||
    !isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    // Invalid session detected - log it for security monitoring
    if (isset($_SESSION["username"])) {
        error_log("Security: Session validation failed for user " . $_SESSION["username"]);
    }
    session_unset();
    session_destroy();
    header("Location: /authSystem/login.php?error=session_invalid");
    exit();
}

// Check session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > 1800)) {
    error_log("Security: Session timeout for user " . $_SESSION["username"]);
    session_unset();
    session_destroy();
    header("Location: /authSystem/login.php?error=session_expired");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Regenerate session ID periodically
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    // Regenerate session ID every 30 minutes
    $old_session_id = session_id();
    session_regenerate_id(true);
    error_log("Security: Session ID regenerated for user " . $_SESSION["username"] . 
              " (Old: $old_session_id, New: " . session_id() . ")");
    $_SESSION['created'] = time();
}
?>
