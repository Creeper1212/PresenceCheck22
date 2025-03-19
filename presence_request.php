<?php
session_start();
// Include configuration and required files
require 'Dashboard/config.php';
require_once 'Dashboard/error_handler.php';
require_once 'Dashboard/time_range.php'; // Use existing time range logic

try {
    // Check if form was submitted
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }
    
    // Sanitize and validate input
    $name = isset($_POST['fname']) ? trim($_POST['fname']) : '';
    
    // More comprehensive name validation
    if (empty($name)) {
        throw new Exception("Name is required");
    }
    
    if (strlen($name) < 2 || strlen($name) > 50) {
        throw new Exception("Name must be between 2 and 50 characters");
    }
    
    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        throw new Exception("Name can only contain letters and spaces");
    }
    
    // Additional sanitization before database insertion
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    
    // Set timezone and get current date/time
    date_default_timezone_set('Europe/Berlin');
    $time = date('H:i');
    $date = date('Y-m-d');
    
    // Use the time range info from the imported time_range.php
    $ishere = $timeRangeInfo['is_in_range'] ? 1 : 0;
    $status = $ishere ? "Is here" : "Is not here";
    
    // Check if user already logged in today
    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM presencetable WHERE name = ? AND date = ?");
    if (!$checkStmt) {
        throw new Exception("Database preparation error: " . $conn->error);
    }
    
    $checkStmt->bind_param("ss", $name, $date);
    if (!$checkStmt->execute()) {
        throw new Exception("Database execution error: " . $checkStmt->error);
    }
    
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $checkStmt->close();
    
    if ($row['count'] > 0) {
        // Store name in session for personalized message
        $_SESSION['user_name'] = $name;
        
        // Redirect to already checked in page
        header("Location: alreadycheckedin.php");
        exit();
    } else {
        // Insert presence record
        $stmt = $conn->prepare("INSERT INTO presencetable (name, here, date, time) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Database preparation error: " . $conn->error);
        }
        
        $stmt->bind_param("siss", $name, $ishere, $date, $time);
        if (!$stmt->execute()) {
            throw new Exception("Database execution error: " . $stmt->error);
        }
        
        // Store user info in session
        $_SESSION['user_name'] = $name;
        $_SESSION['last_checkin'] = $date;
        $_SESSION['checkin_time'] = $time;
        $_SESSION['checkin_status'] = $status;
        
        // Redirect to success page
        header("Location: success.php");
        exit();
    }
} catch (Exception $e) {
    // Use the centralized error handler
    handleError("Check-in Error", $e->getMessage(), "index.php", true);
    exit();
} finally {
    // Ensure database connection is closed
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
