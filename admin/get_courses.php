<?php
include 'dbcon.php';

if(isset($_POST['department_id']) && !empty($_POST['department_id'])) {
    $department_id = $_POST['department_id'];
    
    // Fetch courses based on department
    $query = mysqli_query($conn, "SELECT * FROM courses WHERE department_id = '$department_id' AND is_active = 1 ORDER BY course_name ASC");
    
    if(mysqli_num_rows($query) > 0) {
        echo '<option value="">-- Select Course --</option>';
        while($row = mysqli_fetch_array($query)) {
            echo '<option value="'.$row['course_id'].'">'.$row['course_name'].'</option>';
        }
    } else {
        echo '<option value="">No courses available</option>';
    }
} else {
    echo '<option value="">-- Select Department First --</option>';
}
?> 