<?php
require 'Dashboard/config.php';

// Set timezone
date_default_timezone_set('Europe/Berlin');

// Get current day and time
$current_day = date('w'); // 0 (Sunday) to 6 (Saturday)
$current_time = date('H:i');
$today_date = date('Y-m-d');

// 1. Get today's end time from timesettings
$stmt = $conn->prepare("SELECT end_time FROM timesettings WHERE day_id = ?");
$stmt->bind_param("i", $current_day);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()) {
    $end_time = $row['end_time'];
} else {
    // Fallback to default end time if no settings found
    $end_time = '18:00';
}

// Calculate deadline (end time + 10 minutes)
$deadline = date('H:i', strtotime($end_time . ' +10 minutes'));

// Check if current time matches or exceeds deadline
if($current_time >= $deadline) {
    // Check for today's entries
    $check_stmt = $conn->prepare("SELECT COUNT(*) AS count FROM presencetable WHERE date = ?");
    $check_stmt->bind_param("s", $today_date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $data = $check_result->fetch_assoc();

    // 5. Trigger Asterisk.php if no entries found
    if($data['count'] == 0) {
        // Execute Asterisk.php
        shell_exec('Asterisk.php');
        
        // Optional: Log the event
        error_log("[" . date('Y-m-d H:i:s') . "] No entries found. Triggered Asterisk.php");
    }
}
?>
