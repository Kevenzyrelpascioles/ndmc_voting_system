<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('session.php');
include('dbcon.php');
include('enhanced_logger.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if ID is set in URL
    if(!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("No voter ID provided");
    }
    
    $voter_id = $_GET['id'];
    
    // Get voter details from database for logging
    $voter_query = mysqli_query($conn, "SELECT FirstName, LastName, Status FROM voters WHERE VoterID = '$voter_id'");
    if (!$voter_query) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }
    
    $voter_data = mysqli_fetch_array($voter_query);
    
    if(!$voter_data) {
        throw new Exception("Voter not found");
    }
    
    // Check if voter currently has "Voted" status
    $previous_status = $voter_data['Status'];
    $was_voted = ($previous_status == 'Voted');
    
    // Generate a new random password (6 characters)
    function generateRandomPassword($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }
    
    $new_password = generateRandomPassword();
    
    // If voter was previously "Voted", remove their votes before resetting status
    if ($was_voted) {
        // Check votes table structure to determine the correct voter ID field name
        $check_table = mysqli_query($conn, "SHOW COLUMNS FROM votes");
        $columns = array();
        if ($check_table) {
            while($row = mysqli_fetch_array($check_table)) {
                $columns[] = $row['Field'];
            }
        }
        $voter_id_field = in_array('VoterID', $columns) ? 'VoterID' : 'voter_id';
        
        // Count votes before deletion for logging
        $vote_count_query = mysqli_query($conn, "SELECT COUNT(*) as vote_count FROM votes WHERE $voter_id_field = '$voter_id'");
        $vote_count_row = mysqli_fetch_array($vote_count_query);
        $deleted_votes_count = $vote_count_row ? $vote_count_row['vote_count'] : 0;
        
        // Delete all votes by this voter
        mysqli_query($conn, "DELETE FROM votes WHERE $voter_id_field = '$voter_id'") or die(mysqli_error($conn));
        
        // Log the vote deletion action using enhanced logger
        $vote_deletion_details = "Voter: {$voter_data['FirstName']} {$voter_data['LastName']} ($deleted_votes_count votes removed)";
        logAdminAction($conn, 'Remove Votes (Password Reset)', $vote_deletion_details, 'votes', null);
    }
    
    // Update voter password and reset status to Unvoted
    $update_query = mysqli_query($conn, "UPDATE voters SET 
                                        Password = '$new_password',
                                        Status = 'Unvoted'
                                        WHERE VoterID = '$voter_id'");
    
    if (!$update_query) {
        throw new Exception("Failed to update voter: " . mysqli_error($conn));
    }
    
    // Log the password reset action using enhanced logger
    $reset_details = $voter_data['FirstName'] . " " . $voter_data['LastName'];
    if ($was_voted) {
        $reset_details .= " (Status reset to Unvoted - votes removed)";
    }
    logPasswordReset($conn, $reset_details, 'voter', $voter_id);
    
    // Display success message
    $_SESSION['reset_success'] = true;
    if ($was_voted) {
        $_SESSION['reset_message'] = "Password for " . $voter_data['FirstName'] . " " . $voter_data['LastName'] . " has been reset to: " . $new_password . ". Their votes have been removed from canvassing due to status reset.";
    } else {
        $_SESSION['reset_message'] = "Password for " . $voter_data['FirstName'] . " " . $voter_data['LastName'] . " has been reset to: " . $new_password;
    }
    
    // Redirect back to voter list
    header("Location: voter_list.php");
    exit;

} catch (Exception $e) {
    // Log the error
    $error_message = "Error in reset_voter_password.php: " . $e->getMessage();
    error_log($error_message);
    
    // Display friendly error message to the user
    $_SESSION['reset_error'] = true;
    $_SESSION['error_message'] = "An error occurred while resetting the password. Please try again.";
    $_SESSION['debug_info'] = $error_message; // For debugging
    
    // Redirect back to voter list
    header("Location: voter_list.php");
    exit;
}
?> 