<?php
// At the very top of the file
error_log("Starting voting_settings.php execution", 3, "error_log.txt");

// Enable error reporting and logging to file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session first
session_start();

// Basic authentication check - if no session, redirect to login
if (!isset($_SESSION['id'])){
  header('location:index.php');
  exit;
}

$id_session = $_SESSION['id'];

// Set timezone to Asia/Manila for consistency
date_default_timezone_set('Asia/Manila');

// Database connection - use MySQLi with error handling
$conn = null;
try {
    // Include database connection
    include('dbcon.php');
    include('voting_settings_helper.php');
    include('enhanced_logger.php'); // Add enhanced logger
    
    // Test database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Force table creation regardless of SHOW TABLES result
    // This will ensure the table exists, and if it already exists, the query will fail gracefully
    $create_table_sql = "CREATE TABLE IF NOT EXISTS voting_settings (
        id INT(11) NOT NULL AUTO_INCREMENT,
        voting_start DATETIME NOT NULL,
        voting_end DATETIME NOT NULL,
        hide_results TINYINT(1) NOT NULL DEFAULT 1,
        last_updated DATETIME NOT NULL,
        PRIMARY KEY (id)
    )";
    
    if(!mysqli_query($conn, $create_table_sql)) {
        throw new Exception("Failed to create settings table: " . mysqli_error($conn));
    }
    
    // Now the table should exist, proceed with normal operations
    
    // Check if form is submitted
    if(isset($_POST['save_settings'])) {
        $voting_start = $_POST['voting_start'];
        $voting_end = $_POST['voting_end'];
        $hide_results = isset($_POST['hide_results']) ? 1 : 0;
        
        // Get old settings for logging
        $old_settings_query = mysqli_query($conn, "SELECT * FROM voting_settings LIMIT 1");
        $old_settings = $old_settings_query ? mysqli_fetch_assoc($old_settings_query) : null;
        
        // Check if settings already exist
        $check_settings = mysqli_query($conn, "SELECT COUNT(*) as count FROM voting_settings");
        if(!$check_settings) {
            throw new Exception("Error checking settings: " . mysqli_error($conn));
        }
        
        $count_row = mysqli_fetch_assoc($check_settings);
        $settings_exist = $count_row['count'] > 0;
        
        if($settings_exist) {
            // Update existing settings
            $update = mysqli_query($conn, "UPDATE voting_settings SET 
                                        voting_start = '$voting_start',
                                        voting_end = '$voting_end',
                                        hide_results = '$hide_results',
                                        last_updated = NOW()");
            if($update) {
                $_SESSION['settings_success'] = "Voting settings updated successfully!";
                
                // Log the settings update with detailed information
                $changes = array();
                if($old_settings) {
                    if($old_settings['voting_start'] != $voting_start) {
                        $changes[] = "Start: {$old_settings['voting_start']} → $voting_start";
                    }
                    if($old_settings['voting_end'] != $voting_end) {
                        $changes[] = "End: {$old_settings['voting_end']} → $voting_end";
                    }
                    if($old_settings['hide_results'] != $hide_results) {
                        $old_hide = $old_settings['hide_results'] ? 'Hidden' : 'Visible';
                        $new_hide = $hide_results ? 'Hidden' : 'Visible';
                        $changes[] = "Results: $old_hide → $new_hide";
                    }
                }
                
                $change_details = !empty($changes) ? implode(', ', $changes) : 'Updated voting settings';
                logAdminAction($conn, 'Update Voting Settings', $change_details, 'voting_settings', 1);
                
            } else {
                $_SESSION['settings_error'] = "Failed to update settings: " . mysqli_error($conn);
                logAdminAction($conn, 'Failed Voting Settings Update', "Error: " . mysqli_error($conn), 'voting_settings', 1);
            }
        } else {
            // Insert new settings
            $insert = mysqli_query($conn, "INSERT INTO voting_settings (voting_start, voting_end, hide_results, last_updated) 
                                        VALUES ('$voting_start', '$voting_end', '$hide_results', NOW())");
            if($insert) {
                $_SESSION['settings_success'] = "Voting settings saved successfully!";
                
                // Log the new settings creation
                $hide_text = $hide_results ? 'Hidden' : 'Visible';
                $details = "Start: $voting_start, End: $voting_end, Results: $hide_text";
                logAdminAction($conn, 'Create Voting Settings', $details, 'voting_settings', 1);
                
            } else {
                $_SESSION['settings_error'] = "Failed to save settings: " . mysqli_error($conn);
                logAdminAction($conn, 'Failed Voting Settings Creation', "Error: " . mysqli_error($conn), 'voting_settings', 1);
            }
        }
        
        // Redirect to refresh the page
        header("Location: voting_settings.php");
        exit();
    }

    // Get current settings
    $settings_query = mysqli_query($conn, "SELECT * FROM voting_settings LIMIT 1");
    if(!$settings_query) {
        throw new Exception("Error fetching settings: " . mysqli_error($conn));
    }
    
    $settings = mysqli_fetch_assoc($settings_query);

    // Default values if no settings exist
    $voting_start = isset($settings['voting_start']) ? date('Y-m-d\TH:i', strtotime($settings['voting_start'])) : date('Y-m-d\TH:i');
    $voting_end = isset($settings['voting_end']) ? date('Y-m-d\TH:i', strtotime($settings['voting_end'])) : date('Y-m-d\TH:i', strtotime('+1 day'));
    $hide_results = isset($settings['hide_results']) ? $settings['hide_results'] : 1;
    
} catch (Exception $e) {
    // Log error
    error_log("Error in voting_settings.php: " . $e->getMessage(), 3, "error_log.txt");
    $_SESSION['settings_error'] = "System error: " . $e->getMessage();
    
    // Log system error
    if(isset($conn)) {
        logAdminAction($conn, 'Voting Settings Error', $e->getMessage(), 'voting_settings', null);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Voting Settings - NDMC Voting System</title>
    <!-- Green-themed CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #0d6320;
            color: white;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-bottom: 3px solid #0a4c18;
        }
        .content {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #0d6320;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            margin-top: 0;
        }
        hr {
            border: 0;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }
        input[type="datetime-local"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .help-text {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
            font-style: italic;
        }
        .checkbox-label {
            font-weight: bold;
            cursor: pointer;
        }
        input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }
        button {
            background-color: #0d6320;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0a4c18;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .nav-bar {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        .nav-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 900px;
            margin: 0 auto;
        }
        .nav-links .left-links {
            display: flex;
            flex-wrap: wrap;
        }
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
            color: #0d6320;
            font-weight: bold;
            padding: 5px 0;
            position: relative;
        }
        .nav-links a:hover {
            color: #0a4c18;
        }
        .nav-links a.active {
            color: #0a4c18;
            border-bottom: 2px solid #0a4c18;
        }
        .nav-links .logout {
            color: #dc3545;
        }
        .note-box {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            padding: 15px;
            margin-top: 30px;
        }
        .note-box strong {
            color: #0c5460;
        }
        .note-box ul {
            margin-top: 10px;
            margin-bottom: 0;
        }
        .note-box li {
            margin-bottom: 5px;
        }
        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            input[type="datetime-local"] {
                width: 100%;
            }
            .nav-links {
                flex-direction: column;
            }
            .nav-links .left-links {
                margin-bottom: 10px;
            }
            .nav-links .logout {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <div class="nav-links">
                <div class="left-links">
                    <a href="home.php">Home</a>
                    <a href="candidate_list.php">Candidates List</a>
                    <a href="voter_list.php">Voters List</a>
                    <a href="canvassing_report.php">Canvassing Report</a>
                    <a href="voting_settings.php" class="active">Voting Settings</a>
                    <a href="history.php">History Log</a>
                </div>
                <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>
        
        <div class="content">
            <h2>Voting Settings</h2>
            
            <!-- Display Success Message -->
            <?php if(isset($_SESSION['settings_success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['settings_success']; ?>
            </div>
            <?php unset($_SESSION['settings_success']); endif; ?>
            
            <!-- Display Error Message -->
            <?php if(isset($_SESSION['settings_error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['settings_error']; ?>
            </div>
            <?php unset($_SESSION['settings_error']); endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Voting Start Time:</label>
                    <input type="datetime-local" name="voting_start" value="<?php echo $voting_start; ?>" required>
                    <div class="help-text">When voting will begin</div>
                </div>
                
                <div class="form-group">
                    <label>Voting End Time:</label>
                    <input type="datetime-local" name="voting_end" value="<?php echo $voting_end; ?>" required>
                    <div class="help-text">When voting will end</div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="hide_results" <?php echo $hide_results ? 'checked' : ''; ?>>
                        Hide vote counts until voting ends
                    </label>
                    <div class="help-text">If checked, vote counts will not be visible in the canvassing report until voting ends</div>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="save_settings">Save Settings</button>
                </div>
            </form>
            
            <div class="note-box">
                <strong>Note:</strong> These settings control when voting begins and ends, and whether vote counts are visible during voting.
                <ul>
                    <li>Set the voting start and end times according to your schedule</li>
                    <li>If "Hide Vote Counts" is checked, no one will see the vote counts until the voting end time is reached</li>
                    <li>Vote counts will automatically become visible once the voting end time has passed</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html> 