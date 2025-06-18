<?php
include('session.php');
include('dbcon.php');
include('enhanced_logger.php');

echo "<h2>🔄 Database Trigger Removal Tool</h2>";

if (isset($_POST['revert_triggers'])) {
    echo "<div style='background-color: #f0f8ff; padding: 20px; border: 1px solid #007cba; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Removing Database Triggers...</h3>";
    
    $triggers_to_remove = [
        'log_voter_insert',
        'log_voter_update', 
        'log_voter_delete',
        'log_candidate_insert',
        'log_candidate_update',
        'log_candidate_delete',
        'log_user_insert',
        'log_user_update',
        'log_user_delete',
        'log_vote_insert',
        'log_vote_update',
        'log_vote_delete',
        'log_history_delete',
        'log_department_insert',
        'log_department_update',
        'log_department_delete'
    ];
    
    $removed_count = 0;
    
    foreach ($triggers_to_remove as $trigger) {
        $sql = "DROP TRIGGER IF EXISTS $trigger";
        if (mysqli_query($conn, $sql)) {
            echo "<span style='color: green;'>✅ Removed trigger: $trigger</span><br>";
            $removed_count++;
        } else {
            echo "<span style='color: red;'>❌ Failed to remove: $trigger</span><br>";
        }
    }
    
    echo "<hr>";
    echo "<h4>📊 Summary:</h4>";
    echo "<p><strong>Triggers Removed:</strong> $removed_count</p>";
    
    // Check remaining triggers
    $check_sql = "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()";
    $result = mysqli_query($conn, $check_sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<p><strong>Remaining Triggers:</strong></p>";
        echo "<ul>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<li>" . $row['TRIGGER_NAME'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: green;'><strong>✅ All monitoring triggers have been removed!</strong></p>";
    }
    
    if ($removed_count > 0) {
        echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #28a745;'>";
        echo "<h4>🎉 Revert Complete!</h4>";
        echo "<p>Database monitoring has been disabled. Direct database changes will no longer be logged automatically.</p>";
        echo "</div>";
        
        // Log the removal
        logAdminAction($conn, 'Database Triggers Removed', "Removed $removed_count database monitoring triggers", 'system', null);
    }
    
    echo "</div>";
    
} else {
    echo "<div style='background-color: #fff3cd; padding: 20px; border: 1px solid #ffc107; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>⚠️ Remove Database Monitoring</h3>";
    echo "<p>This will remove all database triggers that automatically log changes to your admin history.</p>";
    echo "<p><strong>What this does:</strong></p>";
    echo "<ul>";
    echo "<li>❌ Removes automatic logging of direct database changes</li>";
    echo "<li>❌ Disables vote tampering alerts</li>";
    echo "<li>❌ Removes history deletion protection</li>";
    echo "<li>✅ Keeps your existing history records intact</li>";
    echo "<li>✅ Keeps the enhanced web interface logging active</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='revert_triggers' style='background-color: #dc3545; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
    echo "🗑️ Remove All Database Triggers";
    echo "</button>";
    echo "</form>";
    
    echo "<p><a href='home.php'>← Back to Home</a></p>";
}
?> 