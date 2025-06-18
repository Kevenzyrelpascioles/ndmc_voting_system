<?php
include('session.php');
include('header.php');
include('dbcon.php');
include('voting_settings_helper.php');

// Check voting settings
$settings = shouldHideVotes($conn);
$hide_votes = $settings['hide_votes'];
?>
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
    background: #4CAF50;
    color: white;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
    border: 2px solid transparent;
}

.position-tabs a:hover {
    background: #388E3C;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.position-tabs a.active {
    background: white;
    color: #4CAF50;
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
}

#log thead tr {
    background: #4CAF50;
    color: white;
}

#log th {
    padding: 15px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
    border: none;
}

#log td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
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

/* Excel download button */
.excel-download-container {
    margin: 20px 0;
}

.excel-download-btn {
    background: #4CAF50;
    color: white;
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
    background: #388E3C;
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

/* Position header */
.position-header {
    background: #2196F3;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: 500;
}

/* Year badge */
.year-badge {
    background: #E8F5E9;
    color: #2E7D32;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 500;
}

/* Department badge */
.department-badge {
    background: #F5F5F5;
    color: #424242;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 500;
}

<?php echo getHiddenVoteCountCSS(); ?>
</style>
</head>

<body>
<?php include('nav_top.php'); ?>

<!-- Display voting status alert if applicable -->
<?php echo displayVotingStatusAlert($settings); ?>
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
        <a href="canvassing_report.php">All</a>
        <a href="C_governor.php">Governor</a>
        <a href="C_vice-governor.php">Vice-Governor</a>
        <a href="C_1st_year.php">1st Year Representative</a>
        <a class="active" href="C_2nd_year.php">2nd Year Representative</a>
        <a href="C_3rd_year.php">3rd Year Representative</a>
        <a href="C_4th_year.php">4th Year Representative</a>
    </div>
   
 
  
    </ul>
    </div>
	<?php
	$query=mysqli_query($conn,"select  * from candidate");
	$row=mysqli_fetch_array($query); $id_excel=$row['CandidateID'];	
	?>
	
		<form method="POST" action="canvassing_excel.php" class="excel-download-container">
	<input type="hidden" name="id_excel" value="<?php echo $id_excel; ?>">
	<button id="save_voter" class="excel-download-btn" name="save">
		<i class="icon-download icon-large"></i>Download Excel File
	</button>
</form>
	<table class="users-table">


<div class="demo_jui">
		<table cellpadding="0" cellspacing="0" border="0" class="display" id="log" class="jtable">
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

<?php $candidate_query=mysqli_query($conn,"select c.*, d.department_name from candidate c LEFT JOIN departments d ON c.department_id = d.department_id where c.Position='2nd Year Representative'");
		while($candidate_rows=mysqli_fetch_array($candidate_query)){ $id=$candidate_rows['CandidateID'];
		$fl=$candidate_rows['FirstName'];
	
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
	<?php $votes_query=mysqli_query($conn,"select * from votes where CandidateID='$id'");
	$vote_count=mysqli_num_rows($votes_query);
	echo displayVoteCount($vote_count, $hide_votes);
	?>
</td>	




	
	
	
<input type="hidden" name="data_name" class="data_name<?php echo $id ?>" value="<?php echo $candidate_rows['FirstName']." ".$candidate_rows['LastName']; ?>"/>
	<input type="hidden" name="user_name" class="user_name" value="<?php echo $_SESSION['User_Type']; ?>"/>
	
	</tr>
<?php } ?>

			</tbody>
		</table>
	</div>	
	</div>	
	
<?php include('footer.php')?>
	
</div>
<input type="hidden" class="pc_date" name="pc_date"/>
<input type="hidden" class="pc_time" name="pc_time"/>
</body>
</html>
