<?php
include 'dbcon.php';

if(isset($_POST['department_id'])) {
    $id = (int)$_POST['department_id'];
    $name = mysqli_real_escape_string($conn, $_POST['department_name']);
    $code = mysqli_real_escape_string($conn, $_POST['department_code']);

    mysqli_query($conn, "UPDATE departments SET department_name = '$name', department_code = '$code' WHERE department_id = $id");
}
?> 