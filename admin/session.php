<?php
session_start();
if (!isset($_SESSION['id'])){
  header('location:index.php');
}

$id_session = $_SESSION['id'];

// Handle department filtering
if (!isset($_SESSION['current_department'])) {
  $_SESSION['current_department'] = 'all';
}

// Pass department filter to pages if not specified in URL
if (!isset($_GET['department']) && isset($_SESSION['current_department'])) {
  $current_dept_id = $_SESSION['current_department'];
} else if (isset($_GET['department'])) {
  $current_dept_id = $_GET['department'];
  $_SESSION['current_department'] = $current_dept_id;
} else {
  $current_dept_id = 'all';
  $_SESSION['current_department'] = 'all';
}
?>