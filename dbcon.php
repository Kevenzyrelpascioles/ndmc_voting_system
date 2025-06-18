<?php 
// Include the configuration
require_once __DIR__ . '/config.php';

// Create connection using the auto-detected configuration
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    // More detailed error for localhost debugging
    if ($isLocalhost) {
        die("Connection failed: " . mysqli_connect_error() . "<br>Environment: " . $environment . "<br>Host: " . $db_host . "<br>Database: " . $db_name);
    } else {
        die("Connection failed: " . mysqli_connect_error());
    }
}

// Set timezone and charset
mysqli_query($conn, "SET time_zone = '{$dbConfig['timezone']}'");
mysqli_set_charset($conn, $dbConfig['charset']);

// Optional: Display environment info for localhost debugging
if ($isLocalhost) {
    // You can uncomment this line for debugging
    // echo "<!-- Running on {$environment} environment - Database: {$db_name} -->";
}
?>