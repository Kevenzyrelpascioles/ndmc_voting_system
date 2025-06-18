<?php
// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Set MySQL connection to use the same timezone
if(isset($conn)) {
    mysqli_query($conn, "SET time_zone = '+08:00'");
}
?>
