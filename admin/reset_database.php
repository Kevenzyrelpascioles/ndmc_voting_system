<?php
include('dbcon.php');
include('session.php');

// Check if user is admin
$user_query = mysqli_query($conn,"select * from users where User='$session_id'") or die(mysqli_error($conn));
$user_row = mysqli_fetch_array($user_query);
if ($user_row['UserType'] !== 'admin') {
    header('location:index.php');
    exit();
}

// Read SQL file
$sql = file_get_contents('reset_database.sql');

// Delete all photos from upload folders
$admin_upload_dir = "upload/";
$root_upload_dir = "../upload/";

// Function to safely delete files from a directory
function cleanDirectory($dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// Clean both upload directories
cleanDirectory($admin_upload_dir);
cleanDirectory($root_upload_dir);

// Execute SQL commands
if (mysqli_multi_query($conn, $sql)) {
    do {
        // Consume all results to allow next query to execute
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    
    // Log the reset action
    mysqli_query($conn,"INSERT INTO history (data,action,date,user)VALUES('Database reset', 'Reset Database', NOW(),'$session_id')") or die(mysqli_error($conn));
    
    // Redirect with success message
    header('location:candidate_list.php?msg=Database has been reset successfully');
} else {
    // Redirect with error message
    header('location:candidate_list.php?msg=Error resetting database: ' . mysqli_error($conn));
}
?>
