<?php
include('session.php');
include('header.php');
include('dbcon.php');

// Check votes table structure to determine the correct voter ID field name
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'votes'") or die(mysqli_error($conn));
$columns = array();
if (mysqli_num_rows($check_table) > 0) {
    $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM votes") or die(mysqli_error($conn));
    while($row = mysqli_fetch_array($check_columns)) {
        $columns[] = $row['Field'];
    }
}
$voter_id_field = in_array('VoterID', $columns) ? 'VoterID' : 'voter_id';

// Get current department filter
$dept_filter = "";
$dept_name = "All Departments";
$candidate_filter = "";

// Handle both GET parameter and session for consistency
if(isset($_GET['department']) && $_GET['department'] != 'all') {
  $dept_id = $_GET['department'];
  $_SESSION['current_department'] = $dept_id;
} elseif(isset($_SESSION['current_department']) && $_SESSION['current_department'] != 'all') {
  $dept_id = $_SESSION['current_department'];
} else {
  $dept_id = 'all';
}

if($dept_id != 'all') {
  $dept_join = " INNER JOIN voters v ON votes.$voter_id_field = v.VoterID ";
  $dept_filter = " AND v.department_id = '$dept_id'";
  $candidate_filter = " WHERE c.department_id = '$dept_id'";
  
  // Get department name for display
  $dept_query = mysqli_query($conn, "SELECT department_name FROM departments WHERE department_id = '$dept_id'");
  if($dept_row = mysqli_fetch_array($dept_query)) {
    $dept_name = $dept_row['department_name'];
  }
} else {
  $dept_join = "";
  $dept_filter = "";
  $candidate_filter = "";
}

// Check if voting settings exist and if we should hide results
$hide_votes = false;
$remaining_time = null;
$settings_exist = false;

// Check if voting_settings table exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'voting_settings'");
if(mysqli_num_rows($check_table) > 0) {
    // Get voting settings
    $settings_query = mysqli_query($conn, "SELECT * FROM voting_settings LIMIT 1");
    if($settings_query && mysqli_num_rows($settings_query) > 0) {
        $settings = mysqli_fetch_assoc($settings_query);
        $settings_exist = true;
        
        // Check if we should hide results
        if($settings['hide_results'] == 1) {
            // Set timezone to Asia/Manila (Philippines) for consistency
            date_default_timezone_set('Asia/Manila');
            
            // Create DateTime objects with the correct timezone
            $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
            $end_time = new DateTime($settings['voting_end'], new DateTimeZone('Asia/Manila'));
            
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
?>
</head>

<body>
<?php include('nav_top.php'); ?>

<!-- Add department filter alert with better styling -->
<div class="alert alert-info" style="margin-top: 55px; background-color: #d9edf7; color: #31708f; border-color: #bce8f1; border-radius: 4px; border-left: 5px solid #31708f;">
  <strong>Current Filter:</strong> Showing results for <strong><?php echo $dept_name; ?></strong>
  <?php if($dept_id != 'all'): ?>
    <a href="canvassing_report.php?department=all" class="btn btn-mini btn-warning" style="margin-left: 10px; background-color: #f0ad4e; border-color: #eea236; color: white; text-shadow: none;">Show All</a>
  <?php endif; ?>
</div>

<!-- Add export error alert after department filter alert -->
<?php if(isset($_SESSION['export_error'])): ?>
<div class="alert alert-error" style="background-color: #f2dede; color: #b94a48; border-color: #eed3d7; border-radius: 4px; border-left: 5px solid #b94a48;">
  <i class="icon-exclamation-sign icon-large"></i> <strong>Export Error:</strong> <?php echo $_SESSION['export_error']; ?>
</div>
<?php unset($_SESSION['export_error']); endif; ?>

<!-- Display voting status alert if applicable -->
<?php if($settings_exist && $hide_votes): ?>
<div class="alert alert-warning" style="background-color: #fcf8e3; color: #8a6d3b; border-color: #faebcc; border-radius: 4px; border-left: 5px solid #8a6d3b;">
  <i class="icon-time icon-large"></i> <strong>Voting in Progress:</strong> Vote counts are hidden until voting ends in <?php echo $remaining_time; ?>.
  <a href="voting_settings.php" class="btn btn-mini btn-warning" style="margin-left: 10px;">View Settings</a>
</div>
<?php elseif($settings_exist): ?>
<div class="alert alert-success" style="background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; border-radius: 4px; border-left: 5px solid #3c763d;">
  <i class="icon-ok icon-large"></i> <strong>Voting Complete:</strong> Final results are now visible.
  <a href="voting_settings.php" class="btn btn-mini btn-success" style="margin-left: 10px;">View Settings</a>
</div>
<?php endif; ?>

<div class="wrapper">
<div class="home_body">
<div class="navbar">
	<div class="navbar-inner">
	<div class="container">	
	<ul class="nav nav-pills">
	  <li>....</li>
	  <li><a href="home.php"><i class="icon-home icon-large"></i>Home</a></li>
	  <li><a  href="candidate_list.php"><i class="icon-align-justify icon-large"></i>Candidates List</a></li>  
	  <li class=""><a  href="voter_list.php"><i class="icon-align-justify icon-large"></i>Voters List</a></li>  
    <li><a  href="manage_academics.php"><i class="icon-cog icon-large"></i>Academics</a></li>
		 <li class="active"><a  href="canvassing_report.php"><i class="icon-book icon-large"></i>Canvassing Report</a></li>
		 <li><a  href="voting_settings.php"><i class="icon-cog icon-large"></i>Voting Settings</a></li>
		    <li><a  href="history.php"><i class="icon-table icon-large"></i>History Log</a>
		   <li><a data-toggle="modal" href="#about"><i class="icon-exclamation-sign icon-large"></i>About</a></li>
		   <div class="modal hide fade" id="about">
	<div class="modal-header"> 
	<button type="button" class="close" data-dismiss="modal">×</button>
	    <h3> </h3>
	  </div>
	  <div class="modal-body">
	  <?php include('about.php') ?>
	  <div class="modal-footer_about">
	    <a href="#" class="btn" data-dismiss="modal">Close</a>
		</div>
		</div>
		   <li>....</li>
	 </ul>
	<form class="navbar-form pull-right">
		<?php 
		$result=mysqli_query($conn,"select * from users where User_id='$id_session'");
		$row=mysqli_fetch_array($result);
		
		// Handle case where user query fails or returns no results
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
	<div id="element" class="hero-body">
	    <div class="position-tabs-container">
            <div class="position-tabs">
                <a class="active" href="canvassing_report.php">All</a>
                <a href="C_governor.php">Governor</a>
                <a href="C_vice-governor.php">Vice-Governor</a>
                <a href="C_1st_year.php">1st Year Representative</a>
                <a href="C_2nd_year.php">2nd Year Representative</a>
                <a href="C_3rd_year.php">3rd Year Representative</a>
                <a href="C_4th_year.php">4th Year Representative</a>
            </div>
        </div>
        
	    <?php
	    $query=mysqli_query($conn,"select  * from candidate");
	    $row=mysqli_fetch_array($query); $id_excel=$row['CandidateID'];	
	    ?>
	
		<div class="excel-download-container">
            <form method="POST" action="canvassing_excel.php" class="excel-download-container">
                <input type="hidden" name="id_excel" value="<?php echo $id_excel; ?>">
                <button id="save_voter" class="excel-download-btn" name="save">
                    <i class="icon-download icon-large"></i>Download Excel File
                </button>
            </form>
        </div>
        
        <div class="clear-fix"></div>
        
        <div class="canvassing-table">
            <table cellpadding="0" cellspacing="0" border="0" class="display jtable" id="log">
                <thead>
                    <tr>
                    <th class="hide">Abc</th>
                    <th>Position</th>
                    <th>FirstName</th>
                    <th>LastName</th>
                    <th>Year</th>
                    <th>Department</th>
                    <th>Photo</th>
                    <th>No. of Votes</th>
                    
                    </tr>
                </thead>
                <tbody>

            <?php 
            // Get distinct positions to create section headers
            $positions_query = mysqli_query($conn, "SELECT DISTINCT Position FROM candidate ORDER BY 
                                                CASE Position 
                                                WHEN 'Governor' THEN 1
                                                WHEN 'Vice-Governor' THEN 2
                                                WHEN '1st Year Representative' THEN 3
                                                WHEN '2nd Year Representative' THEN 4
                                                WHEN '3rd Year Representative' THEN 5
                                                WHEN '4th Year Representative' THEN 6
                                                ELSE 7 END");

            while ($position_row = mysqli_fetch_array($positions_query)) {
                $position = $position_row['Position'];
                
                // Display position header with styled background
                echo '<tr class="position-header"><td colspan="7" class="position-title">' . $position . '</td></tr>';
                
                // Get candidates for this position with proper sorting
                $candidate_query = mysqli_query($conn, "SELECT c.*, d.department_name 
                                                FROM candidate c 
                                                LEFT JOIN departments d ON c.department_id = d.department_id
                                                WHERE c.Position = '$position' $candidate_filter
                                                ORDER BY 
                                                CASE c.Year
                                                    WHEN '1st year' THEN 1
                                                    WHEN '2nd year' THEN 2
                                                    WHEN '3rd year' THEN 3
                                                    WHEN '4th year' THEN 4
                                                    ELSE 5 END,
                                                c.LastName, c.FirstName");
                
                while ($candidate_rows = mysqli_fetch_array($candidate_query)) { 
                    $id = $candidate_rows['CandidateID'];
                    $fl = $candidate_rows['FirstName'];
            ?>
            <tr class="del<?php echo $id ?>">
                <td align="center" class="hide"><?php echo $candidate_rows['abc']; ?></td>
                <td align="center"><span class="position-header"><?php echo $candidate_rows['Position']; ?></span></td>
                <td><?php echo $candidate_rows['FirstName']; ?></td>
                <td><?php echo $candidate_rows['LastName']; ?></td>
                <td align="center"><span class="year-badge"><?php echo $candidate_rows['Year']; ?></span></td>
                <td align="center"><span class="department-badge"><?php echo $candidate_rows['department_name'] ? $candidate_rows['department_name'] : 'Not Assigned'; ?></span></td>
                <td align="center"><img class="candidate-photo" src="<?php echo $candidate_rows['Photo'];?>" onmouseover="showtrail('<?php echo $candidate_rows['Photo'];?>','<?php echo $candidate_rows['FirstName']." ".$candidate_rows['LastName'];?> ',200,5)" onmouseout="hidetrail()"></td>
                <td align="center">
                    <?php 
                    // Get vote count
                    $votes_query = mysqli_query($conn, "SELECT votes.* 
                                                     FROM votes 
                                                     $dept_join 
                                                     WHERE votes.CandidateID='$id' $dept_filter") 
                                                     or die(mysqli_error($conn));
                    $vote_count = mysqli_num_rows($votes_query);
                    
                    // Display vote count or hide it based on settings
                    if($hide_votes) {
                        echo '<span class="vote-count-hidden">Votes Hidden</span>';
                    } else {
                        echo '<span class="vote-count-badge">' . $vote_count . '</span>';
                    }
                    ?>
                </td>

                <input type="hidden" name="data_name" class="data_name<?php echo $id ?>" value="<?php echo $candidate_rows['FirstName']." ".$candidate_rows['LastName']; ?>"/>
                <input type="hidden" name="user_name" class="user_name" value="<?php echo $_SESSION['User_Type']; ?>"/>
            </tr>
            <?php 
                } // end while candidate_rows
            } // end while position_row
            ?>

                </tbody>
            </table>
        </div>
        
<style>
/* Modern styling for canvassing report */
.position-tabs-container {
    margin: 20px 0;
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.position-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.position-tabs a {
    padding: 10px 20px;
    background: white;
    color: #000000;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
    border: 2px solid #4CAF50;
}

.position-tabs a:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    color: #000000;
}

.position-tabs a.active {
    background: #f0f0f0;
    color: #000000;
    border: 2px solid #4CAF50;
}

/* Table styling */
.canvassing-table {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-top: 20px;
}

#log {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 15px;
    color: #000000;
}

#log thead tr {
    background: white;
    color: #000000;
}

#log th {
    padding: 15px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
    border: none;
    color: #000000;
    border-bottom: 2px solid #4CAF50;
}

#log td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    color: #000000;
}

#log tbody tr:hover {
    background-color: #f5f9f5;
}

/* Vote count badge */
.vote-count-badge {
    padding: 8px 15px;
    border-radius: 20px;
    background: #E8F5E9;
    color: #2E7D32;
    font-weight: bold;
    display: inline-block;
    min-width: 40px;
    text-align: center;
    border: 2px solid #4CAF50;
}

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

/* Excel download button */
.excel-download-container {
    margin: 20px 0;
}

.excel-download-btn {
    background: white;
    color: #000000;
    padding: 12px 25px;
    border-radius: 25px;
    border: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.excel-download-btn:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.excel-download-btn i {
    font-size: 18px;
}

/* Candidate photo */
.candidate-photo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #4CAF50;
    transition: transform 0.3s ease;
}

.candidate-photo:hover {
    transform: scale(1.1);
}

/* Department filter alert */
.department-filter {
    background: #E3F2FD;
    border-left: 5px solid #1976D2;
    padding: 15px 20px;
    border-radius: 8px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.department-filter h4 {
    margin: 0;
    color: #1565C0;
    font-size: 16px;
    font-weight: 500;
}

/* Position header */
.position-header {
    background: #2196F3;
    color: #000000;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: 500;
}

/* Year badge */
.year-badge {
    background: #E8F5E9;
    color: #000000;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 500;
}

/* Department badge */
.department-badge {
    background: #F5F5F5;
    color: #000000;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 500;
}
</style>
	</div>	
	
<?php include('footer.php')?>
	
</div>
<input type="hidden" class="pc_date" name="pc_date"/>
<input type="hidden" class="pc_time" name="pc_time"/>
</body>
</html>
