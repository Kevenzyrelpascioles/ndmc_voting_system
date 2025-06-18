<?php
include('session.php');
include('header.php');
include('dbcon.php');

// Get current department filter
$current_dept_id = isset($_GET['department']) ? $_GET['department'] : 'all';
$dept_name = "All Departments";

if($current_dept_id != 'all') {
  $dept_query = mysqli_query($conn, "SELECT department_name FROM departments WHERE department_id = '$current_dept_id'");
  if($dept_row = mysqli_fetch_array($dept_query)) {
    $dept_name = $dept_row['department_name'];
  }
  
  // Store department selection in session
  $_SESSION['current_department'] = $current_dept_id;
} else {
  // Reset to all departments
  $_SESSION['current_department'] = 'all';
}
?>

</head>
<body>
<?php include('nav_top.php'); ?>

<!-- Department filter alert -->
<div class="alert alert-info" style="margin-top: 55px;">
  <strong>Current Filter:</strong> Showing data for <strong><?php echo $dept_name; ?></strong>
  <?php if($current_dept_id != 'all'): ?>
    <a href="home.php?department=all" class="btn btn-mini btn-warning" style="margin-left: 10px;">Show All</a>
  <?php endif; ?>
</div>

<div class="wrapper">
<div class="home_body">
<div class="navbar">
	<div class="navbar-inner">
	<div class="container">	
	<ul class="nav nav-pills">
	  <li>....</li>
	  <li class="active"><a href="home.php"><i class="icon-home icon-large"></i>Home</a></li>
	  <li><a  href="candidate_list.php"><i class="icon-align-justify icon-large"></i>Candidates List</a></li>  
	  <li class=""><a  href="voter_list.php"><i class="icon-align-justify icon-large"></i>Voters List</a></li>  
    <li><a  href="manage_academics.php"><i class="icon-cog icon-large"></i>Academics</a></li>
		 <li><a  href="canvassing_report.php"><i class="icon-book icon-large"></i>Canvassing Report</a></li>
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
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	    <h3>Confirm Logout</h3>
	  </div>
	  <div class="modal-body">
	    <p>Are you sure you want to logout from the admin system?</p>
	  </div>
	  <div class="modal-footer">
	    <a href="#" class="btn" data-dismiss="modal">Cancel</a>
	    <a href="logout.php" class="btn btn-danger">Yes, Logout</a>
		</div>
		</div>

	</form>
	</div>
	</div>
	</div>
	<div id="element" class="hero-body">
	  <div class="dashboard-container">
		<div class="left-column">
		  <div class="thumbnail_gallery" id="ndmc_gallery">
			<h2>NDMC Gallery</h2>
			<p>Click the image to view more...</p>
			<div id="myGallery" class="spacegallery">
			  <img src="images/a1.jpg" alt="" />
			  <img src="images/a2.jpg" alt="" />
			  <img src="images/a3.jpg" alt="" />
			  <img src="images/a4.jpg" alt="" />
			  <img src="images/a5.jpg" alt="" />
			  <img src="images/a6.jpg" alt="" />
			  <img src="images/a7.jpg" alt="" />
			  <img src="images/a8.jpg" alt="" />
			  <img src="images/a9.jpg" alt="" />
			  <img src="images/a10.jpg" alt="" />
			</div>
		  </div>
		</div>
		
		<div class="right-column">
		  <div class="thumbnail_combined">
			<table class="mission-table" width="100%" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td>
				  <h2>Mission</h2>
				</td>
			  </tr>
			  <tr>
				<td>
				  <p class="responsive-text">Notre Dame of Midsayap College aims to develop the total personality of children and youth through quality and excellent education—shaping them to become responsible and productive members of society.</p>
				</td>
			  </tr>
			  <tr>
				<td>
				  <a href="#" class="btn btn-info read-more">Read More</a>
				</td>
			  </tr>
			</table>
			
			<div class="section-divider-small"></div>
			
			<table class="objectives-table" width="100%" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td>
				  <h2>Objectives</h2>
				</td>
			  </tr>
			  <tr>
				<td>
				  <p class="responsive-text">College Level</p>
				</td>
			  </tr>
			  <tr>
				<td>
				  <p class="responsive-text">Develop students' higher-order thinking skills, strong work ethic, and intellectual readiness for informed career and vocational choices or specialized training in their chosen fields.</p>
				</td>
			  </tr>
			  <tr>
				<td>
				  <a href="#" class="btn btn-info read-more">Read More</a>
				</td>
			  </tr>
			</table>
		  </div>
		</div>
	  </div>
	</div>

<?php include('footer.php')?>	
</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
  // Equal heights
  function setEqualHeight() {
    var rightHeight = $('.thumbnail_combined').outerHeight();
    $('.thumbnail_gallery').css('height', rightHeight + 'px');
  }
  
  setEqualHeight();
  $(window).resize(function() {
    setEqualHeight();
  });
});
</script>
</body>
</html> 