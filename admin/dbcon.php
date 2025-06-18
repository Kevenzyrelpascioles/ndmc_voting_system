<?php 
// Include the configuration
require_once __DIR__ . '/../config.php';

// Enable error reporting only if not in production
// Temporarily enable error reporting for debugging on InfinityFree
// REMEMBER TO REVERT THIS AFTER DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection with error handling
try {
    // Create connection using auto-detected configuration
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    // Set timezone and charset
    if (!mysqli_query($conn, "SET time_zone = '{$dbConfig['timezone']}'")) {
        throw new Exception("Failed to set timezone: " . mysqli_error($conn));
    }
    
    if (!mysqli_set_charset($conn, $dbConfig['charset'])) {
        throw new Exception("Error setting charset: " . mysqli_error($conn));
    }
    
} catch (Exception $e) {
    // Log the error
    error_log("Database connection error: " . $e->getMessage());
    
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set session variable for error message
    $_SESSION['db_error'] = true;
    $_SESSION['error_message'] = "Database connection failed. Please try again later.";
    
    // Show detailed error in localhost
    if ($isLocalhost) {
        $_SESSION['debug_info'] = $e->getMessage() . " (Environment: {$environment}, Host: {$db_host}, Database: {$db_name})";
    } else {
    // Only show detailed error in development
    if ($_SERVER['SERVER_NAME'] !== 'ndmc-voting.free.nf') {
        $_SESSION['debug_info'] = $e->getMessage();
        }
    }
    
    // On edit_voter.php, just set conn to false and let the page handle the error display.
    // On all other pages (except index), redirect to the index page to prevent crashes.
    if (strpos($_SERVER['PHP_SELF'], 'edit_voter.php') !== false) {
        $conn = false;
    } else if (strpos($_SERVER['PHP_SELF'], 'index.php') === false) {
        header("Location: index.php");
        exit;
    } else {
        // We're already on index.php, just set conn to false so it can display an error.
        $conn = false;
    }
}
?>