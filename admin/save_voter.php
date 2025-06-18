<?php

include('dbcon.php');
include('enhanced_logger.php'); // Add enhanced logger

// Include sync functionality
require_once '../database_sync.php';
 
$FirstName=$_POST['FirstName'];
$LastName=$_POST['LastName'];
$UserName=$_POST['UserName'];
$Section=$_POST['Section'];
$Year=$_POST['Year'];
$Password=$_POST['Password'];
$VoterID=$_POST['VoterID'];
$department_id = $_POST['department_id'];
$course_id = $_POST['course_id'];

$pc_date = $_POST['pc_date'];
$pc_time = $_POST['pc_time'];
$user_name=$_POST['user_name'];

// Generate a random password if none provided
if(empty($Password)) {
    function generateRandomPassword($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }
    
    $Password = generateRandomPassword();
}

// Check if user already exists
$query = mysqli_query($conn, "SELECT * FROM voters WHERE FirstName='$FirstName' AND LastName='$LastName'") or die(mysqli_error($conn));
$row = mysqli_fetch_array($query);
$num_row = mysqli_num_rows($query);

if ($num_row > 0) {
    echo "<script>alert('Voter Already Exist'); window.location='new_voter.php'</script>";
} else {
    // Insert into local database
    $result = mysqli_query($conn,"insert into voters (FirstName,LastName,UserName,Password,VoterID,Status,Year,MiddleName,department_id,course_id)
    values('$FirstName','$LastName','$UserName','$Password','$VoterID','Unvoted','$Year','$Section','$department_id','$course_id')");

    // Get the new voter ID
    $new_voter_id = mysqli_insert_id($conn);
    
    // Log using enhanced logger with more details
    $additional_info = "Year: $Year, Department ID: $department_id, Course ID: $course_id";
    if(empty($_POST['Password'])) {
        $additional_info .= ", Auto-generated password";
    }
    logVoterActivity($conn, 'Add Voter', "$FirstName $LastName", $new_voter_id, $additional_info);
    
    // Sync to InfinityFree if running on localhost
    try {
        $voterData = [
            'FirstName' => $FirstName,
            'LastName' => $LastName,
            'MiddleName' => $Section,
            'Username' => $UserName,
            'Password' => $Password,
            'Year' => $Year,
            'Status' => 'Unvoted'
        ];
        
        $syncResult = $sync->syncVoter($voterData);
        
        if ($syncResult) {
            // Also sync history to InfinityFree
            $sync->syncHistory("$FirstName $LastName", "Added Voter", $user_name);
            error_log("✅ Voter synced to InfinityFree: $FirstName $LastName");
        } else {
            error_log("⚠️ Failed to sync voter to InfinityFree: $FirstName $LastName");
        }
    } catch (Exception $e) {
        error_log("❌ Sync error: " . $e->getMessage());
    }
    
    // Return the generated password to display
    echo $Password;
}
?>
