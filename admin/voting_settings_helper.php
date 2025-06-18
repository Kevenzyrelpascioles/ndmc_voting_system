<?php
/**
 * Voting Settings Helper Functions
 * This file contains helper functions to check if votes should be hidden based on voting settings
 */

// Function to check if votes should be hidden
function shouldHideVotes($conn) {
    $hide_votes = false;
    $remaining_time = null;
    $settings_exist = false;
    $voting_end = null;
    
    // Check if voting_settings table exists
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'voting_settings'");
    if(mysqli_num_rows($check_table) > 0) {
        // Get voting settings
        $settings_query = mysqli_query($conn, "SELECT * FROM voting_settings LIMIT 1");
        if($settings_query && mysqli_num_rows($settings_query) > 0) {
            $settings = mysqli_fetch_assoc($settings_query);
            $settings_exist = true;
            $voting_end = $settings['voting_end'];
            
            // Check if we should hide results
            if($settings['hide_results'] == 1) {
                // Set timezone to Asia/Manila (Philippines) for consistency
                date_default_timezone_set('Asia/Manila');
                
                // Create DateTime objects with the correct timezone
                $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                $end_time = new DateTime($voting_end, new DateTimeZone('Asia/Manila'));
                
                if($now < $end_time) {
                    $hide_votes = true;
                    
                    // Calculate remaining time
                    $interval = $now->diff($end_time);
                    
                    // Format remaining time based on interval
                    if($interval->d > 0) {
                        // If days remain, show days, hours, minutes
                        $remaining_time = $interval->format('%d days, %h hours, %i minutes');
                    } elseif($interval->h > 0) {
                        // If hours remain but no days, show hours and minutes
                        $remaining_time = $interval->format('%h hours, %i minutes');
                    } else {
                        // If only minutes remain, just show minutes
                        $remaining_time = $interval->format('%i minutes');
                    }
                }
            }
        }
    }
    
    return [
        'hide_votes' => $hide_votes,
        'remaining_time' => $remaining_time,
        'settings_exist' => $settings_exist,
        'voting_end' => $voting_end
    ];
}

// Function to display vote count based on settings
function displayVoteCount($count, $hide_votes) {
    if($hide_votes) {
        return '<span class="vote-count-hidden">Votes Hidden</span>';
    } else {
        return '<span class="vote-count-badge">' . $count . '</span>';
    }
}

// Function to add status alerts for voting
function displayVotingStatusAlert($settings) {
    $html = '';
    
    if($settings['settings_exist']) {
        if($settings['hide_votes']) {
            $html .= '<div class="alert alert-warning" style="background-color: #fcf8e3; color: #8a6d3b; border-color: #faebcc; border-radius: 4px; border-left: 5px solid #8a6d3b;">
                <i class="icon-time icon-large"></i> <strong>Voting in Progress:</strong> Vote counts are hidden until voting ends in ' . $settings['remaining_time'] . '.
                <a href="voting_settings.php" class="btn btn-mini btn-warning" style="margin-left: 10px;">View Settings</a>
            </div>';
        } else {
            $html .= '<div class="alert alert-success" style="background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; border-radius: 4px; border-left: 5px solid #3c763d;">
                <i class="icon-ok icon-large"></i> <strong>Voting Complete:</strong> Final results are now visible.
                <a href="voting_settings.php" class="btn btn-mini btn-success" style="margin-left: 10px;">View Settings</a>
            </div>';
        }
    }
    
    return $html;
}

// CSS for hidden vote count badge
function getHiddenVoteCountCSS() {
    return '
    /* Hidden vote count badge */
    .vote-count-hidden {
        padding: 8px 15px;
        border-radius: 20px;
        background: #EEEEEE;
        color: #757575;
        font-weight: bold;
        display: inline-block;
        min-width: 40px;
        text-align: center;
        border: 2px solid #BDBDBD;
    }
    ';
}
?> 