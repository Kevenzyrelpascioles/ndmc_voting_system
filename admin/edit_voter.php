<?php
include('session.php');
include('dbcon.php');

// Final, robust check for a database connection failure.
if ($conn === false) {
    // dbcon.php now sets session variables on error without redirecting on this page.
    $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : 'A critical database error occurred.';
    $debug_info = isset($_SESSION['debug_info']) ? '<p><strong>Debug Info:</strong> ' . htmlspecialchars($_SESSION['debug_info']) . '</p>' : '';
    
    // Clean up session variables to prevent the message from showing again.
    unset($_SESSION['db_error'], $_SESSION['error_message'], $_SESSION['debug_info']);
    
    // Display a user-friendly error page and stop script execution.
    die("
        </head>
        <body>
            <div style='padding: 40px; font-family: Arial, sans-serif; background-color: #fff0f0; border: 2px solid #d43f3a; color: #a94442; margin: 50px auto; max-width: 800px; border-radius: 8px;'>
                <h2 style='color: #d43f3a; border-bottom: 2px solid #d43f3a; padding-bottom: 10px;'>Database Connection Failed</h2>
                <p>{$error_message}</p>
                <p>The application cannot proceed. Please contact the system administrator for assistance.</p>
                {$debug_info}
                <a href='voter_list.php' style='display: inline-block; margin-top: 15px; padding: 10px 15px; background-color: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go Back to Voter List</a>
            </div>
        </body>
        </html>
    ");
}

// Process form submission *before any HTML output*
if(isset($_POST['update_voter']) && isset($_GET['id'])) {
    $voter_id = (int)$_GET['id'];
    
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $department_id = (int)$_POST['department_id'];
    $course_id = (int)$_POST['course_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Get current voter status before updating
    $current_status_query = mysqli_query($conn, "SELECT Status FROM voters WHERE VoterID = '$voter_id'");
    $current_status_row = mysqli_fetch_array($current_status_query);
    $previous_status = $current_status_row ? $current_status_row['Status'] : '';
    
    // Check if status is changing from "Voted" to "Unvoted"
    $status_changed_to_unvoted = ($previous_status == 'Voted' && $status == 'Unvoted');
    
    // If status is changing from "Voted" to "Unvoted", remove their votes
    if ($status_changed_to_unvoted) {
        // Check votes table structure to determine the correct voter ID field name
        $check_table = mysqli_query($conn, "SHOW COLUMNS FROM votes");
        $columns = array();
        if ($check_table) {
            while($row = mysqli_fetch_array($check_table)) {
                $columns[] = $row['Field'];
            }
        }
        $voter_id_field = in_array('VoterID', $columns) ? 'VoterID' : 'voter_id';
        
        // Count votes before deletion for logging
        $vote_count_query = mysqli_query($conn, "SELECT COUNT(*) as vote_count FROM votes WHERE $voter_id_field = '$voter_id'");
        $vote_count_row = mysqli_fetch_array($vote_count_query);
        $deleted_votes_count = $vote_count_row ? $vote_count_row['vote_count'] : 0;
        
        // Delete all votes by this voter
        mysqli_query($conn, "DELETE FROM votes WHERE $voter_id_field = '$voter_id'") or die(mysqli_error($conn));
        
        // Log the vote deletion action
        $user_name = $_SESSION['User_Type'];
        $vote_deletion_action = "Removed Votes (Status Reset)";
        $vote_deletion_data = "Voter ID: " . $voter_id . " - " . $first_name . " " . $last_name . " (" . $deleted_votes_count . " votes removed)";
        
        mysqli_query($conn, "INSERT INTO history (data, action, date, user) VALUES ('$vote_deletion_data', '$vote_deletion_action', NOW(), '$user_name')") or die(mysqli_error($conn));
    }
    
    // Update voter in database
    $update_query = mysqli_query($conn, "UPDATE voters SET 
                                        FirstName = '$first_name',
                                        LastName = '$last_name',
                                        MiddleName = '$middle_name',
                                        Year = '$year',
                                        department_id = '$department_id',
                                        course_id = '$course_id',
                                        Status = '$status',
                                        Password = '$password'
                                        WHERE VoterID = '$voter_id'") or die(mysqli_error($conn));
    
    // Log the action in history
    $user_name = $_SESSION['User_Type'];
    $action = "Edited Voter";
    $data = "ID: " . $voter_id . " - " . $first_name . " " . $last_name;
    if ($status_changed_to_unvoted) {
        $data .= " (Status changed to Unvoted - votes removed)";
    }
    
    mysqli_query($conn, "INSERT INTO history (data, action, date, user) VALUES ('$data', '$action', NOW(), '$user_name')") or die(mysqli_error($conn));
    
    // Set success message and redirect with anchor to the updated voter
    if ($status_changed_to_unvoted) {
        $_SESSION['update_success'] = "Voter details updated successfully! Their votes have been removed from canvassing due to status change to Unvoted.";
    } else {
        $_SESSION['update_success'] = "Voter details updated successfully!";
    }
    header("Location: voter_list.php?updated_id=" . $voter_id . "#voter-" . $voter_id);
    exit;
}

include('header.php');

// Check if ID is set in URL for page display
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: voter_list.php");
    exit;
}

$voter_id = (int)$_GET['id'];

// Get voter details from database
$voter_query = mysqli_query($conn, "SELECT * FROM voters WHERE VoterID = '$voter_id'") or die(mysqli_error($conn));
$voter_data = mysqli_fetch_array($voter_query);

// Check if voter exists
if(!$voter_data) {
    // Optionally set an error message
    $_SESSION['update_error'] = "Voter not found.";
    header("Location: voter_list.php");
    exit;
}
?>
</head>
<body>
<?php include('nav_top.php'); ?>
<div class="wrapper">
<div class="home_body">
<div class="navbar">
	<div class="navbar-inner">
	<div class="container">	
	<ul class="nav nav-pills">
	  <li><a href="home.php"><i class="icon-home icon-large"></i>Home</a></li>
	  <li><a  href="candidate_list.php"><i class="icon-align-justify icon-large"></i>Candidates List</a></li>  
	  <li class="active"><a  href="voter_list.php"><i class="icon-align-justify icon-large"></i>Voters List</a></li>  
	  <li><a  href="canvassing_report.php"><i class="icon-book icon-large"></i>Canvassing Report</a></li>
	  <li><a  href="History.php"><i class="icon-table icon-large"></i>History Log</a></li>
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
	<!-- Back to Voter List button -->
    <a class="back-button" href="voter_list.php" title="Back to Voter List">
        <i class="icon-arrow-left"></i> BACK
    </a>
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
        <h2><i class="icon-edit icon-large"></i> Edit Voter</h2>
        <hr>
        
        <form method="POST" class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="first_name">First Name</label>
                <div class="controls">
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($voter_data['FirstName']); ?>" required>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="last_name">Last Name</label>
                <div class="controls">
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($voter_data['LastName']); ?>" required>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="middle_name">Middle Name</label>
                <div class="controls">
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($voter_data['MiddleName']); ?>">
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="year">Year</label>
                <div class="controls">
                    <select id="year" name="year" required>
                        <option value="1st year" <?php if($voter_data['Year'] == '1st year') echo 'selected'; ?>>1st Year</option>
                        <option value="2nd year" <?php if($voter_data['Year'] == '2nd year') echo 'selected'; ?>>2nd Year</option>
                        <option value="3rd year" <?php if($voter_data['Year'] == '3rd year') echo 'selected'; ?>>3rd Year</option>
                        <option value="4th year" <?php if($voter_data['Year'] == '4th year') echo 'selected'; ?>>4th Year</option>
                    </select>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="department_id">Department</label>
                <div class="controls">
                    <select id="department_id" name="department_id" required>
                        <option value="">-- Select Department --</option>
                        <?php 
                        $dept_query = mysqli_query($conn, "SELECT * FROM departments ORDER BY department_name");
                        while($dept_row = mysqli_fetch_array($dept_query)) {
                            $selected = ($dept_row['department_id'] == $voter_data['department_id']) ? 'selected' : '';
                            echo "<option value='".$dept_row['department_id']."' $selected>".htmlspecialchars($dept_row['department_name'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="course_id">Course</label>
                <div class="controls">
                    <select id="course_id" name="course_id" required>
                        <option value="">-- Select Department First --</option>
                        <?php 
                        if(!empty($voter_data['department_id'])) {
                            $course_dept_id = $voter_data['department_id'];
                            $course_query = mysqli_query($conn, "SELECT * FROM courses WHERE department_id = '$course_dept_id' ORDER BY course_name");
                            while($course_row = mysqli_fetch_array($course_query)) {
                                $selected = ($course_row['course_id'] == $voter_data['course_id']) ? 'selected' : '';
                                echo "<option value='".$course_row['course_id']."' $selected>".htmlspecialchars($course_row['course_name'])."</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="password">Password</label>
                <div class="controls">
                    <input type="text" id="password" name="password" value="<?php echo htmlspecialchars($voter_data['Password']); ?>" required>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="status">Status</label>
                <div class="controls">
                    <select id="status" name="status" required>
                        <option value="Unvoted" <?php if($voter_data['Status'] == 'Unvoted') echo 'selected'; ?>>Unvoted</option>
                        <option value="Voted" <?php if($voter_data['Status'] == 'Voted') echo 'selected'; ?>>Voted</option>
                    </select>
                </div>
            </div>
            
            <div class="control-group">
                <div class="controls">
                    <button type="submit" name="update_voter" class="btn btn-success"><i class="icon-save icon-large"></i> Update Voter</button>
                    <a href="voter_list.php" class="btn"><i class="icon-remove icon-large"></i> Cancel</a>
                </div>
            </div>
        </form>
    </div>
    
	<?php include('footer.php')?>
</div>
</div>
<script>
$(document).ready(function() {
    $('#department_id').on('change', function() {
        var department_id = $(this).val();
        if (department_id) {
            $.ajax({
                type: 'POST',
                url: 'get_courses.php',
                data: 'department_id=' + department_id,
                success: function(html) {
                    $('#course_id').html(html);
                }
            });
        } else {
            $('#course_id').html('<option value="">-- Select Department First --</option>');
        }
    });
});
</script>
</body>
</html> 