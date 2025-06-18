<?php
include('session.php');
include('header.php');
include('dbcon.php');

// Check if cleanup was requested
if (isset($_POST['cleanup_votes'])) {
    try {
        // Check votes table structure to determine the correct voter ID field name
        $check_table = mysqli_query($conn, "SHOW COLUMNS FROM votes");
        $columns = array();
        if ($check_table) {
            while($row = mysqli_fetch_array($check_table)) {
                $columns[] = $row['Field'];
            }
        }
        $voter_id_field = in_array('VoterID', $columns) ? 'VoterID' : 'voter_id';
        
        // Count inconsistent votes before cleanup
        $count_query = mysqli_query($conn, "SELECT COUNT(*) as inconsistent_count 
                                           FROM votes v 
                                           INNER JOIN voters vt ON v.$voter_id_field = vt.VoterID 
                                           WHERE vt.Status = 'Unvoted'");
        $count_row = mysqli_fetch_array($count_query);
        $inconsistent_votes = $count_row ? $count_row['inconsistent_count'] : 0;
        
        if ($inconsistent_votes > 0) {
            // Delete inconsistent votes
            mysqli_query($conn, "DELETE v FROM votes v 
                                INNER JOIN voters vt ON v.$voter_id_field = vt.VoterID 
                                WHERE vt.Status = 'Unvoted'") or die(mysqli_error($conn));
            
            // Log the cleanup action
            $user_name = $_SESSION['User_Type'];
            $action = "Cleaned Vote Inconsistencies";
            $data = "Removed $inconsistent_votes votes from voters with Unvoted status";
            
            mysqli_query($conn, "INSERT INTO history (data, action, date, user) VALUES ('$data', '$action', NOW(), '$user_name')") or die(mysqli_error($conn));
            
            $cleanup_message = "Successfully cleaned up $inconsistent_votes inconsistent votes.";
        } else {
            $cleanup_message = "No inconsistent votes found. Database is clean.";
        }
    } catch (Exception $e) {
        $cleanup_error = "Error during cleanup: " . $e->getMessage();
    }
}

// Check for current inconsistencies
$check_table = mysqli_query($conn, "SHOW COLUMNS FROM votes");
$columns = array();
if ($check_table) {
    while($row = mysqli_fetch_array($check_table)) {
        $columns[] = $row['Field'];
    }
}
$voter_id_field = in_array('VoterID', $columns) ? 'VoterID' : 'voter_id';

// Get inconsistent records
$inconsistent_query = mysqli_query($conn, "SELECT DISTINCT vt.VoterID, vt.FirstName, vt.LastName, vt.Status, COUNT(v.ID) as vote_count
                                          FROM voters vt 
                                          INNER JOIN votes v ON v.$voter_id_field = vt.VoterID 
                                          WHERE vt.Status = 'Unvoted'
                                          GROUP BY vt.VoterID, vt.FirstName, vt.LastName, vt.Status");

$inconsistent_count = $inconsistent_query ? mysqli_num_rows($inconsistent_query) : 0;
?>

<style>
.consistency-check {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.status-good {
    color: #28a745;
    font-weight: bold;
}

.status-warning {
    color: #ffc107;
    font-weight: bold;
}

.status-error {
    color: #dc3545;
    font-weight: bold;
}

.cleanup-button {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 15px;
}

.cleanup-button:hover {
    background-color: #c82333;
}

.cleanup-button:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
    padding: 12px;
    margin: 15px 0;
    border-radius: 4px;
    border: 1px solid transparent;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
    padding: 12px;
    margin: 15px 0;
    border-radius: 4px;
    border: 1px solid transparent;
}

.inconsistent-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.inconsistent-table th,
.inconsistent-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.inconsistent-table th {
    background-color: #f8f9fa;
    font-weight: bold;
}
</style>

<body>
<?php include('nav_top.php'); ?>
<div class="wrapper">
<div class="home_body">
<div class="navbar">
    <div class="navbar-inner">
    <div class="container">	
    <ul class="nav nav-pills">
      <li><a href="home.php"><i class="icon-home icon-large"></i>Home</a></li>
      <li><a href="candidate_list.php"><i class="icon-align-justify icon-large"></i>Candidates List</a></li>  
      <li><a href="voter_list.php"><i class="icon-align-justify icon-large"></i>Voters List</a></li>  
      <li><a href="canvassing_report.php"><i class="icon-book icon-large"></i>Canvassing Report</a></li>
      <li><a href="History.php"><i class="icon-table icon-large"></i>History Log</a></li>
      <li class="active"><a href="#"><i class="icon-wrench icon-large"></i>Vote Consistency Check</a></li>
    </ul>
    <form class="navbar-form pull-right">
        <?php 
        $result=mysqli_query($conn,"select * from users where User_id='$id_session'");
        $row=mysqli_fetch_array($result);
        
        if (!$row) {
            $user_type = isset($_SESSION['User_Type']) ? $_SESSION['User_Type'] : 'Admin';
        } else {
            $user_type = $row['User_Type'];
        }
        ?>
    <font color="white">Welcome:<i class="icon-user-md"></i><?php echo $user_type; ?></font>
    <a class="btn btn-danger" id="logout" data-toggle="modal" href="#myModal"><i class="icon-off"></i>&nbsp;Logout</a>
    
    <div class="modal hide fade" id="myModal">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
        <h3> </h3>
      </div>
      <div class="modal-body">
        <p><font color="gray">Are You Sure you Want to LogOut?</font></p>
      </div>
      <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">No</a>
        <a href="logout.php" class="btn btn-primary">Yes</a>
        </div>
        </div>
    </form>
    </div>
    </div>
    </div>

    <div class="consistency-check">
        <h2><i class="icon-wrench icon-large"></i> Vote Consistency Check</h2>
        <hr>
        
        <?php if (isset($cleanup_message)): ?>
            <div class="alert-success">
                <i class="icon-ok"></i> <?php echo $cleanup_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($cleanup_error)): ?>
            <div class="alert-danger">
                <i class="icon-exclamation-sign"></i> <?php echo $cleanup_error; ?>
            </div>
        <?php endif; ?>
        
        <div class="status-section">
            <h3>Database Consistency Status</h3>
            
            <?php if ($inconsistent_count == 0): ?>
                <p class="status-good">
                    <i class="icon-ok-circle"></i> No inconsistencies found. 
                    All voters with "Unvoted" status have no votes in the system.
                </p>
            <?php else: ?>
                <p class="status-error">
                    <i class="icon-warning-sign"></i> Found <?php echo $inconsistent_count; ?> voters with "Unvoted" status who still have votes in the system.
                </p>
                
                <p><strong>Issue:</strong> These voters have their status set to "Unvoted" but their votes are still being counted in the canvassing reports. This creates inconsistent results.</p>
                
                <form method="POST" style="display: inline;">
                    <button type="submit" name="cleanup_votes" class="cleanup-button" 
                            onclick="return confirm('Are you sure you want to remove all votes from voters with Unvoted status? This action cannot be undone.')">
                        <i class="icon-trash"></i> Clean Up Inconsistent Votes
                    </button>
                </form>
                
                <h4 style="margin-top: 25px;">Affected Voters:</h4>
                <table class="inconsistent-table">
                    <thead>
                        <tr>
                            <th>Voter ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Vote Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_array($inconsistent_query)): ?>
                        <tr>
                            <td><?php echo $row['VoterID']; ?></td>
                            <td><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></td>
                            <td><?php echo $row['Status']; ?></td>
                            <td><?php echo $row['vote_count']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <h4>What this tool does:</h4>
            <ul>
                <li>Checks for voters who have "Unvoted" status but still have votes recorded in the database</li>
                <li>These inconsistencies can occur if the database was manually modified or if there were system errors</li>
                <li>When you clean up, votes from "Unvoted" voters are permanently removed from the canvassing results</li>
                <li>This ensures that only votes from voters with "Voted" status are counted in reports</li>
            </ul>
            
            <p><strong>Note:</strong> The system has been updated to automatically handle this when voter status is changed through the admin interface, but this tool can help clean up any existing inconsistencies.</p>
        </div>
    </div>

    <?php include('footer.php')?>
</div>
</div>

</body>
</html> 