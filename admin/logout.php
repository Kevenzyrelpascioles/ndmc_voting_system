<?php
include('session.php');
include('dbcon.php');
include('enhanced_logger.php');

$login_query=mysqli_query($conn,"select * from users where User_id=$id_session");
$count=mysqli_num_rows($login_query);
$row=mysqli_fetch_array($login_query);
$f=$row['FirstName'];
$l=$row['LastName'];
$type=$row['User_Type'];

// Use enhanced logging for admin logout
logAdminAction($conn, 'Admin Logout', "$f $l", 'authentication', $id_session);

session_destroy();
header('location:index.php');
?>