<?php
include 'dbcon.php';

$query = mysqli_query($conn, "SELECT c.*, d.department_name FROM courses c LEFT JOIN departments d ON c.department_id = d.department_id ORDER BY d.department_name, c.course_name");
while($row = mysqli_fetch_array($query)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['course_code']) . "</td>";
    echo "<td>" . htmlspecialchars($row['department_name']) . "</td>";
    echo "<td>";
    echo "<a href='#' class='btn btn-mini btn-info edit-course' data-id='{$row['course_id']}'>Edit</a> ";
    echo "<a href='#' class='btn btn-mini btn-danger delete-course' data-id='{$row['course_id']}'>Delete</a>";
    echo "</td>";
    echo "</tr>";
}
?> 