<?php
// NDMC Voting System - Localhost Setup Script
// This script helps you set up the localhost environment

// Include configuration
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>NDMC Voting System - Setup</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #27ae60; background: #d5f4e6; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".error { color: #e74c3c; background: #fdf2f2; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".info { color: #3498db; background: #ebf3fd; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".warning { color: #f39c12; background: #fef9e7; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "h1 { color: #2c3e50; }";
echo "h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 5px; }";
echo ".step { background: #f8f9fa; padding: 20px; border-left: 4px solid #3498db; margin: 20px 0; }";
echo "code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }";
echo ".btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }";
echo ".btn:hover { background: #2980b9; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🗳️ NDMC Voting System - Setup</h1>";

// Environment Detection
echo "<h2>📍 Environment Detection</h2>";
if ($isLocalhost) {
    echo "<div class='success'>✅ <strong>Localhost Detected!</strong><br>";
    echo "Environment: {$environment}<br>";
    echo "Database Host: {$db_host}<br>";
    echo "Database Name: {$db_name}</div>";
} else {
    echo "<div class='info'>🌐 <strong>Production Environment Detected</strong><br>";
    echo "Environment: {$environment}<br>";
    echo "Database Host: {$db_host}<br>";
    echo "Database Name: {$db_name}</div>";
}

// Database Connection Test
echo "<h2>🔗 Database Connection Test</h2>";
try {
    $test_conn = mysqli_connect($db_host, $db_user, $db_pass);
    
    if ($test_conn) {
        echo "<div class='success'>✅ <strong>Database server connection successful!</strong></div>";
        
        // Check if database exists
        $db_exists = mysqli_select_db($test_conn, $db_name);
        
        if ($db_exists) {
            echo "<div class='success'>✅ <strong>Database '{$db_name}' exists and is accessible!</strong></div>";
            
            // Test some tables
            $tables_to_check = ['users', 'voters', 'candidate', 'votes'];
            $missing_tables = [];
            
            foreach ($tables_to_check as $table) {
                $result = mysqli_query($test_conn, "SHOW TABLES LIKE '{$table}'");
                if (mysqli_num_rows($result) == 0) {
                    $missing_tables[] = $table;
                }
            }
            
            if (empty($missing_tables)) {
                echo "<div class='success'>✅ <strong>All required tables exist!</strong></div>";
                
                // Check admin user
                $admin_check = mysqli_query($test_conn, "SELECT * FROM users WHERE UserName = 'admin' LIMIT 1");
                if (mysqli_num_rows($admin_check) > 0) {
                    echo "<div class='success'>✅ <strong>Admin user exists!</strong></div>";
                    echo "<div class='info'>🎉 <strong>System is ready to use!</strong><br>";
                    echo "<a href='index.php' class='btn'>Go to Voting System</a>";
                    echo "<a href='admin/index.php' class='btn'>Go to Admin Panel</a></div>";
                } else {
                    echo "<div class='warning'>⚠️ <strong>Admin user not found!</strong><br>";
                    echo "You may need to create an admin user manually.</div>";
                }
                
            } else {
                echo "<div class='error'>❌ <strong>Missing tables:</strong> " . implode(', ', $missing_tables) . "</div>";
            }
            
        } else {
            if ($isLocalhost) {
                echo "<div class='error'>❌ <strong>Database '{$db_name}' does not exist!</strong></div>";
                echo "<div class='step'>";
                echo "<h3>🛠️ Localhost Setup Instructions:</h3>";
                echo "<ol>";
                echo "<li>Open phpMyAdmin (usually at <code>http://localhost/phpmyadmin</code>)</li>";
                echo "<li>Click on 'Import' tab</li>";
                echo "<li>Choose the file <code>setup_localhost.sql</code> from your project folder</li>";
                echo "<li>Click 'Go' to import the database</li>";
                echo "<li>Refresh this page to test the connection</li>";
                echo "</ol>";
                echo "<p><strong>Alternative:</strong> Run this SQL file directly in MySQL command line:</p>";
                echo "<code>mysql -u root -p < setup_localhost.sql</code>";
                echo "</div>";
            } else {
                echo "<div class='error'>❌ <strong>Database '{$db_name}' is not accessible!</strong><br>";
                echo "This might be a permission issue with your hosting provider.</div>";
            }
        }
        
        mysqli_close($test_conn);
    } else {
        echo "<div class='error'>❌ <strong>Database connection failed!</strong><br>";
        echo "Error: " . mysqli_connect_error() . "</div>";
        
        if ($isLocalhost) {
            echo "<div class='step'>";
            echo "<h3>🔧 Localhost Troubleshooting:</h3>";
            echo "<ul>";
            echo "<li>Make sure XAMPP/WAMP is running</li>";
            echo "<li>Check if MySQL service is started</li>";
            echo "<li>Verify MySQL is running on default port 3306</li>";
            echo "<li>If you changed MySQL root password, update it in <code>config.php</code></li>";
            echo "</ul>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ <strong>Connection error:</strong> " . $e->getMessage() . "</div>";
}

// System Requirements Check
echo "<h2>⚙️ System Requirements</h2>";
echo "<div class='info'>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>MySQL Extension:</strong> " . (extension_loaded('mysqli') ? '✅ Available' : '❌ Missing') . "<br>";
echo "<strong>Session Support:</strong> " . (extension_loaded('session') ? '✅ Available' : '❌ Missing') . "<br>";
echo "<strong>File Upload:</strong> " . (ini_get('file_uploads') ? '✅ Enabled' : '❌ Disabled') . "<br>";
echo "</div>";

// File Permissions Check (for localhost)
if ($isLocalhost) {
    echo "<h2>📁 File Permissions Check</h2>";
    $upload_dirs = ['upload', 'admin/upload', 'pic'];
    
    foreach ($upload_dirs as $dir) {
        if (is_dir($dir)) {
            if (is_writable($dir)) {
                echo "<div class='success'>✅ <code>{$dir}/</code> - Writable</div>";
            } else {
                echo "<div class='warning'>⚠️ <code>{$dir}/</code> - Not writable (may cause upload issues)</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ <code>{$dir}/</code> - Directory does not exist</div>";
        }
    }
}

// Quick Start Guide
echo "<h2>🚀 Quick Start Guide</h2>";
echo "<div class='step'>";
echo "<h3>For Localhost Development:</h3>";
echo "<ol>";
echo "<li>Make sure XAMPP/WAMP is running</li>";
echo "<li>Import <code>setup_localhost.sql</code> into phpMyAdmin</li>";
echo "<li>Access the system at <code>http://localhost/ndmc_voting_system/</code></li>";
echo "<li>Login with username: <code>admin</code>, password: <code>admin</code></li>";
echo "</ol>";
echo "</div>";

echo "<div class='step'>";
echo "<h3>For InfinityFree Hosting:</h3>";
echo "<ol>";
echo "<li>Upload all files to your InfinityFree hosting</li>";
echo "<li>The system will automatically detect the production environment</li>";
echo "<li>Use your existing InfinityFree database credentials</li>";
echo "<li>Access your online voting system</li>";
echo "</ol>";
echo "</div>";

// Additional Information
echo "<h2>ℹ️ Additional Information</h2>";
echo "<div class='info'>";
echo "<strong>Configuration File:</strong> The system automatically detects whether you're running on localhost or InfinityFree and uses the appropriate database settings.<br><br>";
echo "<strong>Switching Environments:</strong> Simply move your files between localhost and InfinityFree - no configuration changes needed!<br><br>";
echo "<strong>Database Sync:</strong> Remember that localhost and production databases are separate. You'll need to manually sync data if needed.";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 