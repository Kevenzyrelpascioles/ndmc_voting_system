<?php
include 'dbcon.php';

if(isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $query = mysqli_query($conn, "SELECT * FROM departments WHERE department_id = $id");
    $row = mysqli_fetch_assoc($query);
    echo json_encode($row);
}
?> 