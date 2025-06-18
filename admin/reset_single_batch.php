<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set execution time limit
set_time_limit(30); // 30 seconds for one small batch

include('dbcon.php');

// Check if batch parameters are set
if (!isset($_GET['batch']) || !isset($_GET['size'])) {
    die(json_encode(['error' => 'Missing batch parameters']));
}

$batch = intval($_GET['batch']);
$batch_size = intval($_GET['size']);
$offset = $batch * $batch_size;

// Generate a new random password
function generateRandomPassword($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

try {
    // Get count of total records for progress tracking
    if ($batch == 0) {
        $count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM voters");
        if (!$count_query) {
            throw new Exception("Error counting voters: " . mysqli_error($conn));
        }
        $count_row = mysqli_fetch_assoc($count_query);
        $total_voters = $count_row['total'];
        $_SESSION['total_voters'] = $total_voters;
        $_SESSION['reset_count'] = 0;
        
        // For the first batch, check if there are voters with "Voted" status and remove all votes
        $voted_count_query = mysqli_query($conn, "SELECT COUNT(*) as voted_count FROM voters WHERE Status = 'Voted'");
        if (!$voted_count_query) {
            throw new Exception("Error counting voted users: " . mysqli_error($conn));
        }
        $voted_count_row = mysqli_fetch_assoc($voted_count_query);
        $total_voted_users = $voted_count_row['voted_count'];
        $_SESSION['total_voted_users'] = $total_voted_users;
        
        // If there are voters with "Voted" status, we need to remove all votes before starting
        if ($total_voted_users > 0) {
            // Count total votes before deletion for logging
            $total_votes_query = mysqli_query($conn, "SELECT COUNT(*) as total_votes FROM votes");
            $total_votes_row = mysqli_fetch_array($total_votes_query);
            $total_votes_deleted = $total_votes_row ? $total_votes_row['total_votes'] : 0;
            
            // Delete ALL votes since we're resetting all voters
            mysqli_query($conn, "DELETE FROM votes") or die(mysqli_error($conn));
            
            // Log the vote deletion action
            $user_name = isset($_SESSION['User_Type']) ? $_SESSION['User_Type'] : 'Admin';
            $vote_deletion_action = "Removed All Votes (Batch Password Reset - Start)";
            $vote_deletion_data = "All votes removed (" . $total_votes_deleted . " votes deleted) before batch password reset";
            
            mysqli_query($conn, "INSERT INTO history (date, action, data, user) VALUES (NOW(), '$vote_deletion_action', '$vote_deletion_data', '$user_name')");
        }
    } else {
        $total_voters = isset($_SESSION['total_voters']) ? $_SESSION['total_voters'] : 0;
    }
    
    // Get batch of voters
    $voters_query = mysqli_query($conn, "SELECT VoterID FROM voters LIMIT $offset, $batch_size");
    if (!$voters_query) {
        throw new Exception("Error querying voters batch $batch: " . mysqli_error($conn));
    }
    
    // Count processed in this batch
    $processed = 0;
    $succeeded = 0;
    
    // Process each voter in the batch
    while ($voter = mysqli_fetch_array($voters_query)) {
        $voter_id = $voter['VoterID'];
        $new_password = generateRandomPassword();
        
        // Update voter password and reset status to Unvoted
        $update_query = mysqli_query($conn, "UPDATE voters SET 
                                        Password = '$new_password',
                                        Status = 'Unvoted'
                                        WHERE VoterID = '$voter_id'");
        
        if ($update_query) {
            $succeeded++;
            // Update total successful resets
            $_SESSION['reset_count'] = ($_SESSION['reset_count'] ?? 0) + 1;
        }
        
        $processed++;
    }
    
    // Calculate progress
    $total_processed = $offset + $processed;
    $progress = ($total_voters > 0) ? round(($total_processed / $total_voters) * 100) : 100;
    $is_complete = $total_processed >= $total_voters || mysqli_num_rows($voters_query) < $batch_size;
    
    // If this is the last batch, log the action in history
    if ($is_complete) {
        $user_name = isset($_SESSION['User_Type']) ? $_SESSION['User_Type'] : 'Admin';
        $action = "Reset All Voter Passwords";
        $total_reset = isset($_SESSION['reset_count']) ? $_SESSION['reset_count'] : 0;
        $total_voted_users = isset($_SESSION['total_voted_users']) ? $_SESSION['total_voted_users'] : 0;
        $data = "Reset " . $total_reset . " voter passwords";
        if ($total_voted_users > 0) {
            $data .= " (All votes removed from canvassing)";
        }
        
        mysqli_query($conn, "INSERT INTO history (date, action, data, user) 
                         VALUES (NOW(), '$action', '$data', '$user_name')");
                         
        // Set session variables for success message
        $_SESSION['reset_all_success'] = true;
        $_SESSION['reset_all_count'] = $total_reset;
        
        // Clear progress tracking
        unset($_SESSION['total_voters']);
        unset($_SESSION['reset_count']);
        unset($_SESSION['total_voted_users']);
    }
    
    // Return response as JSON
    echo json_encode([
        'success' => true,
        'batch' => $batch,
        'processed' => $processed,
        'succeeded' => $succeeded,
        'total_processed' => $total_processed,
        'total' => $total_voters,
        'progress' => $progress,
        'is_complete' => $is_complete
    ]);
    
} catch (Exception $e) {
    // Return error as JSON
    echo json_encode([
        'error' => $e->getMessage(),
        'batch' => $batch
    ]);
}
?> 