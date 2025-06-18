<?php
include 'dbcon.php';

if(isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    mysqli_query($conn, "DELETE FROM courses WHERE course_id = $id");
}
?> 