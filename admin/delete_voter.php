<?php
include('dbcon.php');
include('enhanced_logger.php');

$id=$_POST['id'];
$pc_date = $_POST['pc_date'];
$pc_time = $_POST['pc_time'];
$data_name = $_POST['data_name'];
$user_name = $_POST['user_name'];

mysqli_query($conn,"delete from voters where VoterID='$id'");

logVoterActivity($conn, 'Delete Voter', $data_name, $id);

?>