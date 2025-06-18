<?php
session_start();

// Unset the session variable to lock the Academics page
if (isset($_SESSION['is_academics_authorized'])) {
    unset($_SESSION['is_academics_authorized']);
}

// Redirect back to the homepage
header('Location: home.php');
exit();
?> 