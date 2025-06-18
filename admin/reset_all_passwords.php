<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set execution time and memory limits to prevent timeouts
set_time_limit(60); // 1 minute should be enough for this lightweight version
ini_set('memory_limit', '32M'); // Limit memory usage

// Include files after setting limits
include('session.php');
include('dbcon.php');

// Generate a new random password (6 characters)
function generateRandomPassword($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

try {
    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    // Before resetting passwords, check how many voters currently have "Voted" status
    $voted_count_query = mysqli_query($conn, "SELECT COUNT(*) as voted_count FROM voters WHERE Status = 'Voted'");
    if (!$voted_count_query) {
        throw new Exception("Error counting voted users: " . mysqli_error($conn));
    }
    $voted_count_row = mysqli_fetch_assoc($voted_count_query);
    $total_voted_users = $voted_count_row['voted_count'];
    
    // If there are voters with "Voted" status, we need to remove all votes
    if ($total_voted_users > 0) {
        // Check votes table structure to determine the correct voter ID field name
        $check_table = mysqli_query($conn, "SHOW COLUMNS FROM votes");
        $columns = array();
        if ($check_table) {
            while($row = mysqli_fetch_array($check_table)) {
                $columns[] = $row['Field'];
            }
        }
        $voter_id_field = in_array('VoterID', $columns) ? 'VoterID' : 'voter_id';
        
        // Count total votes before deletion for logging
        $total_votes_query = mysqli_query($conn, "SELECT COUNT(*) as total_votes FROM votes");
        $total_votes_row = mysqli_fetch_array($total_votes_query);
        $total_votes_deleted = $total_votes_row ? $total_votes_row['total_votes'] : 0;
        
        // Delete ALL votes since we're resetting all voters
        mysqli_query($conn, "DELETE FROM votes") or die(mysqli_error($conn));
        
        // Log the vote deletion action
        $user_name = isset($_SESSION['User_Type']) ? $_SESSION['User_Type'] : 'Admin';
        $vote_deletion_action = "Removed All Votes (Batch Password Reset)";
        $vote_deletion_data = "All votes removed (" . $total_votes_deleted . " votes deleted) due to batch password reset";
        
        mysqli_query($conn, "INSERT INTO history (date, action, data, user) VALUES (NOW(), '$vote_deletion_action', '$vote_deletion_data', '$user_name')");
    }
    
    // Process in smaller batches - first, count total voters
    $count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM voters");
    if (!$count_query) {
        throw new Exception("Error counting voters: " . mysqli_error($conn));
    }
    
    $count_row = mysqli_fetch_assoc($count_query);
    $total_voters = $count_row['total'];
    
    // Process only 50 voters at a time - much more likely to succeed on shared hosting
    $limit = 50;
    $batch_count = ceil($total_voters / $limit);
    
    // Counter for successful resets
    $reset_count = 0;
    
    // Process each batch separately to avoid timeouts
    for ($batch = 0; $batch < $batch_count; $batch++) {
        $offset = $batch * $limit;
        
        // Get a small batch of voters
        $voters_query = mysqli_query($conn, "SELECT VoterID FROM voters LIMIT $offset, $limit");
        if (!$voters_query) {
            throw new Exception("Error querying voters batch $batch: " . mysqli_error($conn));
        }
        
        // Process each voter in the batch
        while ($voter = mysqli_fetch_array($voters_query)) {
            $voter_id = $voter['VoterID'];
            $new_password = generateRandomPassword();
            
            // Update voter password and reset status to Unvoted - one at a time
            $update_query = mysqli_query($conn, "UPDATE voters SET 
                                               Password = '$new_password',
                                               Status = 'Unvoted'
                                               WHERE VoterID = '$voter_id'");
            
            if ($update_query) {
                $reset_count++;
            } else {
                // Log error but continue with next voter
                error_log("Failed to update voter $voter_id: " . mysqli_error($conn));
            }
        }
    }
    
    // Log the action in history
    $pc_date = date('Y-m-d');
    $pc_time = date('H:i:s');
    $user_name = isset($_SESSION['User_Type']) ? $_SESSION['User_Type'] : 'Admin';
    $action = "Reset All Voter Passwords";
    $data = "Reset " . $reset_count . " voter passwords";
    if ($total_voted_users > 0) {
        $data .= " (All votes removed from canvassing)";
    }
    
    $history_query = mysqli_query($conn, "INSERT INTO history (date, action, data, user, pc_time, pc_date) 
                                       VALUES (NOW(), '$action', '$data', '$user_name', '$pc_time', '$pc_date')");
    
    if (!$history_query) {
        error_log("Failed to log history: " . mysqli_error($conn));
        // Continue anyway - this is not critical
    }
    
    // Store minimal data in session
    $_SESSION['reset_all_success'] = true;
    $_SESSION['reset_all_count'] = $reset_count;
    
    // Create a minimal sample dataset for display - only show first 10
    $_SESSION['reset_all_data'] = array();
    $sample_query = mysqli_query($conn, "SELECT VoterID, FirstName, LastName FROM voters LIMIT 10");
    if ($sample_query) {
        while ($voter = mysqli_fetch_array($sample_query)) {
            $_SESSION['reset_all_data'][] = array(
                'id' => $voter['VoterID'],
                'name' => $voter['FirstName'] . ' ' . $voter['LastName'],
                'password' => generateRandomPassword() // Generate new sample passwords just for display
            );
        }
    }
    
    // Success message
    if ($total_voted_users > 0) {
        $_SESSION['success_message'] = "Successfully reset passwords for $reset_count voters. All voters have been set to Unvoted status and all votes have been removed from canvassing.";
    } else {
        $_SESSION['success_message'] = "Successfully reset passwords for $reset_count voters. All voters have been set to Unvoted status.";
    }
    
    // Redirect back to voter list
    header("Location: voter_list.php");
    exit;
    
} catch (Exception $e) {
    // Log the error
    $error_message = "Error in reset_all_passwords.php: " . $e->getMessage();
    error_log($error_message);
    
    // Display friendly error message to the user
    $_SESSION['reset_error'] = true;
    $_SESSION['error_message'] = "An error occurred while resetting passwords. Please try again or contact support.";
    $_SESSION['debug_info'] = $error_message; // For debugging
    
    // Redirect back to voter list
    header("Location: voter_list.php");
    exit;
}
?> 