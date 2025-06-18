<?php
// Database Connection Test File
// Use this to test your database connection after updating credentials

echo "<h2>Database Connection Test</h2>";
echo "<p>Testing connection to database...</p>";

// Include the configuration
require_once __DIR__ . '/config.php';

echo "<h3>Environment Detection:</h3>";
echo "Detected environment: <strong>" . $environment . "</strong><br>";
echo "Is localhost: " . ($isLocalhost ? 'Yes' : 'No') . "<br><br>";

echo "<h3>Database Configuration:</h3>";
echo "Host: <strong>" . $db_host . "</strong><br>";
echo "Username: <strong>" . $db_user . "</strong><br>";
echo "Database: <strong>" . $db_name . "</strong><br>";
echo "Password: " . (empty($db_pass) ? '<em>Empty</em>' : '<em>Set (hidden for security)</em>') . "<br><br>";

echo "<h3>Connection Test:</h3>";

// Test the connection
$test_conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if ($test_conn) {
    echo "<div style='color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>Connection Successful!</strong><br>";
    echo "MySQL Version: " . mysqli_get_server_info($test_conn) . "<br>";
    echo "Connection ID: " . mysqli_thread_id($test_conn);
    echo "</div>";
    
    // Test if we can query the database
    $test_query = @mysqli_query($test_conn, "SHOW TABLES");
    if ($test_query) {
        echo "<h4>Database Tables Found:</h4>";
        echo "<ul>";
        while ($row = mysqli_fetch_array($test_query)) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<div style='color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "⚠️ Connected but cannot read tables: " . mysqli_error($test_conn);
        echo "</div>";
    }
    
    mysqli_close($test_conn);
} else {
    echo "<div style='color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>Connection Failed!</strong><br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
    echo "Error Number: " . mysqli_connect_errno();
    echo "</div>";
    
    echo "<h4>Common Solutions:</h4>";
    echo "<ol>";
    echo "<li><strong>Check your InfinityFree Control Panel</strong> for updated database credentials</li>";
    echo "<li><strong>Database Host</strong> might have changed (sql113, sql201, sql301, etc.)</li>";
    echo "<li><strong>Username/Password</strong> might have been reset</li>";
    echo "<li><strong>Database Name</strong> might have changed</li>";
    echo "<li><strong>Database Server</strong> might be temporarily down</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If connection failed, update the credentials in <code>config.php</code></li>";
echo "<li>Delete this test file after fixing the issue for security</li>";
echo "<li>Try accessing your voting system again</li>";
echo "</ol>";

echo "<p><em>Remember to delete this file after testing for security reasons!</em></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
</style> 