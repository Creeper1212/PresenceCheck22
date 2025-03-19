<?php
/**
 * Time Range Handler
 * 
 * Determines if the current time is within the allowed range for check-ins.
 * Uses database settings for flexible configuration.
 */

// Ensure we have database connection
if (!isset($conn) || !$conn instanceof mysqli) {
    die("Database connection not available. Please include config.php before time_range.php");
}

// Get current day of week (0 = Sunday, 6 = Saturday) and time
$currentDayOfWeek = date('w');
$currentTime = date('H:i:s');
$currentDayName = date('l');

// Initialize default values
$startTime = '08:00:00';
$endTime = '18:00:00';
$isInRange = false;
$isClosed = false;

try {
    // Fetch time settings for today with optimized query
    $sql = "
        SELECT 
            day_name,
            start_time, 
            end_time,
            -- Check if start and end times indicate a closed day (both 00:00:00)
            (start_time = '00:00:00' AND end_time = '00:00:00') AS is_closed,
            -- Check if current time is within range
            (? BETWEEN start_time AND end_time) AS is_in_range
        FROM timesettings 
        WHERE day_id = ? 
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare time settings query: " . $conn->error);
    }
    
    $stmt->bind_param("si", $currentTime, $currentDayOfWeek);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute time settings query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $startTime = $row['start_time'];
        $endTime = $row['end_time'];
        $isClosed = (bool)$row['is_closed'];
        $isInRange = !$isClosed && (bool)$row['is_in_range'];
        $dayName = $row['day_name'];
    } else {
        // Log if no settings found
        error_log("No time settings found for day {$currentDayOfWeek}. Using defaults.");
    }
    
    $stmt->close();
} catch (Exception $e) {
    // Log the error but continue with defaults
    error_log("Time range error: " . $e->getMessage());
    
    // Default values are already set above
    $isInRange = ($currentTime >= $startTime && $currentTime <= $endTime);
}

// Make the time range info available for other scripts
$timeRangeInfo = [
    'day_id' => $currentDayOfWeek,
    'day_name' => $currentDayName,
    'start_time' => $startTime,
    'end_time' => $endTime,
    'current_time' => $currentTime,
    'is_in_range' => $isInRange,
    'is_closed' => $isClosed
];

// Helper function to format times for display
function formatTimeRange($startTime, $endTime, $isClosed = false) {
    if ($isClosed) {
        return "Closed";
    }
    return substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5);
}

// Determine whether the submit button should be enabled
$disableSubmit = !$isInRange || $isClosed;
?>
