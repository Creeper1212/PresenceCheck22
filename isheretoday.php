<?php
include 'authSystem/auth_check.php'; // Enforce login
// Purpose: Display a list of users who have checked in today.

// Include required files
require 'Dashboard/config.php';      // Database configuration
require 'Dashboard/time_range.php';  // Time range functionality
require_once 'Dashboard/error_handler.php'; // Error handling
include 'Dashboard/header.php';      // Page header

// Get current date
$today = date('Y-m-d');
$currentTime = date('H:i');

// Prepare and execute query to get today's presence records with proper error handling
try {
    // More detailed query to get all relevant information
    $sql = "SELECT name, here, time, 
           CASE 
               WHEN here = 1 THEN 'Present' 
               ELSE 'Not Present' 
           END AS status_text
           FROM presencetable 
           WHERE date = ? 
           ORDER BY time DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('s', $today);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $recordCount = $result->num_rows;
    
} catch (Exception $e) {
    handleError("Database Error", $e->getMessage(), null, true);
    $result = null; // Set result to null to avoid errors later
    $recordCount = 0;
}
?>

<div class="container my-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Today's Presence</h3>
                    <span class="badge bg-light text-dark">
                        <?php echo date('l, F j, Y'); ?> | Current Time: <?php echo $currentTime; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        Welcome to the presence tracking system. Here you can see who is present today.
                        <?php if ($timeRangeInfo['is_in_range']): ?>
                            <span class="badge bg-success ms-2">System Active</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark ms-2">System Inactive</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php echo displayError(); // Display any errors ?>
                    
                    <?php if ($result && $recordCount > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Check-in Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1;
                                    while ($row = $result->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>
                                            <?php if ($row['here']): ?>
                                                <span class="badge bg-success">Present</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Not Present</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['time']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-success mt-3">
                            <strong>Total check-ins today:</strong> <?php echo $recordCount; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No sign-ins recorded for today yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Clean up resources
if (isset($stmt)) {
    $stmt->close();
}
include 'Dashboard/footer.php';
?>
