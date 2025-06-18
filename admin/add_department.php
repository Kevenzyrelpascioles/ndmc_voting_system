<?php
include 'dbcon.php';

if(isset($_POST['department_name'])) {
    $name = mysqli_real_escape_string($conn, $_POST['department_name']);
    $code = isset($_POST['department_code']) ? mysqli_real_escape_string($conn, $_POST['department_code']) : '';

    mysqli_query($conn, "INSERT INTO departments (department_name, department_code, is_active) VALUES ('$name', '$code', 1)");
    
    // Optional: add to history log
    // mysqli_query($conn, "INSERT INTO history (data, action, date, user) VALUES ('$name', 'Added Department', NOW(), 'admin')");
}
?> 