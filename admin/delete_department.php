<?php
include 'dbcon.php';

if(isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Note: You might want to decide what happens to courses in a department being deleted.
    // The foreign key constraint `ON DELETE SET NULL` will set their department_id to NULL.
    // You could also choose to delete them:
    // mysqli_query($conn, "DELETE FROM courses WHERE department_id = $id");

    mysqli_query($conn, "DELETE FROM departments WHERE department_id = $id");
}
?> 