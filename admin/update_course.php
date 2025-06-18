<?php
include 'dbcon.php';

if(isset($_POST['course_id'])) {
    $id = (int)$_POST['course_id'];
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $dept_id = (int)$_POST['department_id'];

    mysqli_query($conn, "UPDATE courses SET course_name = '$name', course_code = '$code', department_id = $dept_id WHERE course_id = $id");
}
?> 