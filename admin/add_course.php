<?php
include 'dbcon.php';

if(isset($_POST['course_name']) && isset($_POST['department_id'])) {
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $code = isset($_POST['course_code']) ? mysqli_real_escape_string($conn, $_POST['course_code']) : '';
    $dept_id = (int)$_POST['department_id'];

    mysqli_query($conn, "INSERT INTO courses (course_name, course_code, department_id, is_active) VALUES ('$name', '$code', $dept_id, 1)");

    // Optional: add to history log
    // mysqli_query($conn, "INSERT INTO history (data, action, date, user) VALUES ('$name', 'Added Course', NOW(), 'admin')");
}
?> 