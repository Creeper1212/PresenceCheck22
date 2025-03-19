<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout in seconds (e.g., 30 minutes)
const SESSION_TIMEOUT = 1800;

//Check if a session exists
if (isset($_SESSION['username'])) {
  //Check last activity
  if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
      session_unset();
      session_destroy();
      header("Location: login.php");
      exit();
  } else {
    $_SESSION['last_activity'] = time();
  }
  //Regenerate session ID every 30 minutes
  if (!isset($_SESSION['created'])) {
      $_SESSION['created'] = time();
  } elseif (time() - $_SESSION['created'] > 1800) {
      session_regenerate_id(true);
      $_SESSION['created'] = time();
  }
}

?>
