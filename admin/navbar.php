
	<div class="navbar">
	<div class="navbar-inner">
	<div class="container">
	     
	

	
	
	<ul class="nav nav-pills">
	  <li>....</li>
	  <li><a href="home.php">Home</a></li>
	  <li><a  href="new_voter.php">Add Voters</a></li>  
	  <li><a  href="new_candidate.php">Add Candidate</a></li>  
	  <li><a  href="candidate_list.php">Candidate List</a></li>  


		    <li class="dropdown" id="voter">
	    <a class="dropdown-toggle" data-toggle="dropdown" href="#voter">
	      Voters
	      <b class="caret"></b>
	    </a>
	    <ul class="dropdown-menu">
		 
		  <li><a href="voter_list.php">Voters List</a></li>
	      <li class="divider"></li>
	      <li><a href="Unvoted_voters.php">Unvoted Voters</a></li>
		      <li class="divider"></li>
	      <li><a href="Voted_voters.php">Voted Voters</a></li>
	      </ul>	
	
	      </li>
		     <li class="dropdown" id="report">
	    <a class="dropdown-toggle" data-toggle="dropdown" href="#report">
	      Report
	      <b class="caret"></b>
	    </a>
	    <ul class="dropdown-menu">
		 <li class="divider"></li>
		  <li><a href="canvassing_report.php">Canvassing Report</a></li>
	      <li class="divider"></li>
	      </ul>
	
	      </li>
		    <li><a  href="history.php">History Log</a>
		    
		    <li class="dropdown" id="departments">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#departments">
              Select Department
              <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
              <li><a href="home.php?department=all">All Departments</a></li>
              <li class="divider"></li>
              <?php
                $dept_query = mysqli_query($conn, "SELECT * FROM departments WHERE is_active = 1 ORDER BY department_name ASC") or die(mysqli_error($conn));
                while($dept_row = mysqli_fetch_array($dept_query)) {
                  // Highlight current department if it's selected
                  $active = (isset($_SESSION['current_department']) && $_SESSION['current_department'] == $dept_row['department_id']) ? ' class="active"' : '';
                  echo '<li'.$active.'><a href="home.php?department='.$dept_row['department_id'].'">'.$dept_row['department_name'].'</a></li>';
                }
              ?>
              <li class="divider"></li>
              <li><a href="manage_departments.php"><i class="icon-cog"></i> Manage Departments</a></li>
            </ul>
          </li>
		   
		   <li><a data-toggle="modal" href="#about">About</a></li>
		   <div class="modal hide fade" id="about">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"></button>
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
	<font color="white">Welcome:<i class="icon-user  icon-white"></i><?php echo $user_type; ?></font>
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