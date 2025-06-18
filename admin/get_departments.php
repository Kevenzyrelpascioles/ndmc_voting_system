<?php
include 'dbcon.php';

$query = mysqli_query($conn, "SELECT * FROM departments ORDER BY department_name");
while($row = mysqli_fetch_array($query)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['department_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['department_code']) . "</td>";
    echo "<td>";
    echo "<a href='#' class='btn btn-mini btn-info edit-department' data-id='{$row['department_id']}'>Edit</a> ";
    echo "<a href='#' class='btn btn-mini btn-danger delete-department' data-id='{$row['department_id']}'>Delete</a>";
    echo "</td>";
    echo "</tr>";
}
?> 