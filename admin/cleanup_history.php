<?php
// CLEANUP SCRIPT - Run this once to clean up existing repetitive logs
include('dbcon.php');
include('enhanced_logger.php');

// Remove repetitive page access logs
$cleanup_query = "DELETE FROM history WHERE action = 'Page Access' AND data LIKE '%Accessed: %'";
$result = mysqli_query($conn, $cleanup_query);

if ($result) {
    $deleted_count = mysqli_affected_rows($conn);
    echo "Cleanup completed: Removed $deleted_count repetitive page access logs.<br>";
    
    // Log the cleanup action
    logAdminAction($conn, 'History Cleanup', "Removed $deleted_count repetitive page access logs");
    
    echo "History has been cleaned up and is now focused on important activities only.<br>";
    echo "<a href='history.php'>View Clean History</a>";
} else {
    echo "Error during cleanup: " . mysqli_error($conn);
}
?> 