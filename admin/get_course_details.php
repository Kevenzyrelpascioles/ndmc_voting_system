<?php
include 'dbcon.php';

$response = ['course' => null, 'departments' => []];

if(isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    // Get course details
    $course_query = mysqli_query($conn, "SELECT * FROM courses WHERE course_id = $id");
    $response['course'] = mysqli_fetch_assoc($course_query);

    // Get all departments for the dropdown
    $dept_query = mysqli_query($conn, "SELECT department_id, department_name FROM departments ORDER BY department_name");
    while($dept = mysqli_fetch_assoc($dept_query)) {
        $response['departments'][] = $dept;
    }
}

echo json_encode($response);
?> 