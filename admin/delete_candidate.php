<?php
include('dbcon.php');
include('enhanced_logger.php'); // Include enhanced logger

$id = $_POST['id'];
$data_name = $_POST['data_name'];

// Delete the candidate
mysqli_query($conn, "DELETE FROM candidate WHERE CandidateID='$id'");

// Log the deletion using enhanced logger
logCandidateActivity($conn, 'Delete Candidate', $data_name, null, $id);

?>