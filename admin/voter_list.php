<?php
include('session.php');
include('header.php');
include('dbcon.php');

// Handle search functionality
$search_filter = "";
$search_term = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = mysqli_real_escape_string($conn, trim($_GET['search']));
    
    // Create comprehensive search conditions for voters
    $search_conditions = array();
    
    $lower_term = strtolower($search_term);
    
    // Check for specific voting status searches first (exact filtering)
    if ($lower_term === 'voted' || $lower_term === 'vote') {
        // Only show voted voters
        $search_filter = " AND v.Status = 'Voted'";
    } elseif ($lower_term === 'unvoted' || $lower_term === 'not voted' || $lower_term === 'unvote' || $lower_term === 'not vote') {
        // Only show unvoted voters
        $search_filter = " AND v.Status = 'UnVoted'";
    } else {
        // Enhanced search with smart combinations
        $year_conditions = array();
        $course_conditions = array();
        $name_conditions = array();
        $general_conditions = array();
        
        // Year mappings with all possible variations
        $year_patterns = array(
            '1st year' => array('1', '1st', 'first', 'first year', '1st year'),
            '2nd year' => array('2', '2nd', 'second', 'second year', '2nd year'),
            '3rd year' => array('3', '3rd', 'third', 'third year', '3rd year'),
            '4th year' => array('4', '4th', 'fourth', 'fourth year', '4th year')
        );
        
        // Department/Course mappings with extensive variations
        $dept_course_patterns = array(
            'Information Technology' => array(
                'keywords' => array('it', 'information technology', 'info tech', 'infotech', 'bsit', 'bs it', 'bs-it'),
                'course_names' => array('Information Technology', 'IT', 'Bachelor of Science in Information Technology', 'BSIT', 'BS Information Technology')
            ),
            'Computer Science' => array(
                'keywords' => array('cs', 'computer science', 'comp sci', 'compsci', 'bscs', 'bs cs', 'bs-cs'),
                'course_names' => array('Computer Science', 'CS', 'Bachelor of Science in Computer Science', 'BSCS', 'BS Computer Science')
            ),
            'Computer Engineering' => array(
                'keywords' => array('ce', 'computer engineering', 'comp eng', 'compeng', 'bsce', 'bs ce', 'bs-ce', 'cpe', 'bscpe'),
                'course_names' => array('Computer Engineering', 'CpE', 'CPE', 'Bachelor of Science in Computer Engineering', 'BSCE', 'BS Computer Engineering', 'BSCpE')
            ),
            'Information System' => array(
                'keywords' => array('is', 'information system', 'info sys', 'infosys', 'bsis', 'bs is', 'bs-is'),
                'course_names' => array('Information System', 'IS', 'Bachelor of Science in Information System', 'BSIS', 'BS Information System')
            ),
            'Electronics and Communications Engineering' => array(
                'keywords' => array('ece', 'electronics', 'electronics engineering', 'electronics and communications', 'comm eng', 'bsece', 'bs ece', 'bs-ece'),
                'course_names' => array('Electronics and Communications Engineering', 'ECE', 'BSECE', 'BS Electronics Engineering')
            ),
            'Electrical Engineering' => array(
                'keywords' => array('ee', 'electrical', 'electrical engineering', 'elec eng', 'bsee', 'bs ee', 'bs-ee'),
                'course_names' => array('Electrical Engineering', 'EE', 'BSEE', 'BS Electrical Engineering')
            ),
            'Mechanical Engineering' => array(
                'keywords' => array('me', 'mechanical', 'mechanical engineering', 'mech eng', 'bsme', 'bs me', 'bs-me'),
                'course_names' => array('Mechanical Engineering', 'ME', 'BSME', 'BS Mechanical Engineering')
            ),
            'Industrial Engineering' => array(
                'keywords' => array('ie', 'industrial', 'industrial engineering', 'ind eng', 'bsie', 'bs ie', 'bs-ie'),
                'course_names' => array('Industrial Engineering', 'IE', 'BSIE', 'BS Industrial Engineering')
            )
        );
        
        // Split search term into individual words for better matching
        $search_words = explode(' ', $lower_term);
        $remaining_words = array();
        
        // Check for year patterns in search term
        $year_found = false;
        foreach ($year_patterns as $db_year => $variations) {
            foreach ($variations as $variation) {
                if (strpos($lower_term, $variation) !== false) {
                    $year_conditions[] = "v.Year = '$db_year'";
                    $year_found = true;
                    // Remove year-related words from remaining search
                    $remaining_words = array_filter($search_words, function($word) use ($variation) {
                        return strpos($variation, $word) === false && strpos($word, str_replace(' ', '', $variation)) === false;
                    });
                    break 2;
                }
            }
        }
        
        // If no year pattern found, keep all words for other searches
        if (!$year_found) {
            $remaining_words = $search_words;
        }
        
        // Check for course patterns in search term
        $course_found = false;
        foreach ($dept_course_patterns as $category => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (strpos($lower_term, $keyword) !== false) {
                    // Add course conditions (search in course names)
                    foreach ($data['course_names'] as $course_name) {
                        $course_conditions[] = "c.course_name LIKE '%$course_name%'";
                    }
                    $course_found = true;
                    // Remove course-related words from remaining search
                    $remaining_words = array_filter($remaining_words, function($word) use ($keyword) {
                        return strpos($keyword, $word) === false && strpos($word, str_replace(' ', '', $keyword)) === false;
                    });
                    break 2;
                }
            }
        }
        
        // Use remaining words for name search
        if (!empty($remaining_words)) {
            foreach ($remaining_words as $word) {
                $word = trim($word);
                if (!empty($word) && strlen($word) > 1) { // Only search for words longer than 1 character
                    $name_conditions[] = "(v.FirstName LIKE '%$word%' OR v.LastName LIKE '%$word%' OR v.MiddleName LIKE '%$word%')";
                }
            }
        }
        
        // Combine conditions with AND logic for precise results
        $all_conditions = array();
        
        if (!empty($year_conditions)) {
            $all_conditions[] = "(" . implode(' OR ', $year_conditions) . ")";
        }
        
        if (!empty($course_conditions)) {
            $all_conditions[] = "(" . implode(' OR ', $course_conditions) . ")";
        }
        
        if (!empty($name_conditions)) {
            $all_conditions[] = "(" . implode(' AND ', $name_conditions) . ")";
        }
        
        // If we have specific patterns (year/course) with names, combine with AND
        if (!empty($all_conditions)) {
            $search_filter = " AND (" . implode(' AND ', $all_conditions) . ")";
        }
        // If no specific patterns found, do general search
        else {
            $general_conditions[] = "v.FirstName LIKE '%$search_term%'";
            $general_conditions[] = "v.LastName LIKE '%$search_term%'";
            $general_conditions[] = "v.MiddleName LIKE '%$search_term%'";
            $general_conditions[] = "v.Password LIKE '%$search_term%'";
            $general_conditions[] = "d.department_name LIKE '%$search_term%'";
            $general_conditions[] = "c.course_name LIKE '%$search_term%'";
            
            if (!empty($general_conditions)) {
                $search_filter = " AND (" . implode(' OR ', $general_conditions) . ")";
            }
        }
    }
}

// Get current department filter
$dept_filter = "";
$dept_name = "All Departments";

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
  $dept_filter = " AND v.department_id = '$dept_id'";
  
  // Get department name for display
  $dept_query = mysqli_query($conn, "SELECT department_name FROM departments WHERE department_id = '$dept_id'");
  if($dept_row = mysqli_fetch_array($dept_query)) {
    $dept_name = $dept_row['department_name'];
  }
}

// Combine search and department filters
$combined_filter = $dept_filter . $search_filter;

// Get the ID of the recently updated voter
$updated_id = isset($_GET['updated_id']) ? (int)$_GET['updated_id'] : 0;
?>
</head>
<body>
<?php include('nav_top.php'); ?>

<!-- Department filter alert with better styling -->
<div class="alert alert-info" style="margin-top: 55px; background-color: #d9edf7; color: #31708f; border-color: #bce8f1; border-radius: 4px; border-left: 5px solid #31708f;">
  <strong>Current Filter:</strong> Showing voters from <strong><?php echo $dept_name; ?></strong>
  <?php if($dept_id != 'all'): ?>
    <a href="voter_list.php?department=all" class="btn btn-mini btn-warning" style="margin-left: 10px; background-color: #f0ad4e; border-color: #eea236; color: white; text-shadow: none;">Show All</a>
  <?php endif; ?>
</div>

<!-- Success message for updates -->
<?php if(isset($_SESSION['update_success'])): ?>
<div class="alert alert-success" style="background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; border-radius: 4px; border-left: 5px solid #3c763d; margin-bottom: 15px;">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>Success!</strong> <?php echo $_SESSION['update_success']; ?>
</div>
<?php unset($_SESSION['update_success']); endif; ?>

<!-- Error message display -->
<?php if(isset($_SESSION['reset_error']) && $_SESSION['reset_error']): ?>
<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; border-radius: 4px; border-left: 5px solid #721c24; margin-bottom: 15px;">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>Error!</strong> <?php echo $_SESSION['error_message']; ?>
  <?php if(isset($_SESSION['debug_info']) && $_SESSION['User_Type'] == 'admin'): ?>
  <br><small>Debug info: <?php echo $_SESSION['debug_info']; ?></small>
  <?php endif; ?>
</div>
<?php 
  // Clear the session variables
  unset($_SESSION['reset_error']);
  unset($_SESSION['error_message']);
  unset($_SESSION['debug_info']);
endif; 
?>

<!-- Success messages for password resets -->
<?php if(isset($_SESSION['reset_success']) && $_SESSION['reset_success']): ?>
<div class="alert alert-success" style="background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; border-radius: 4px; border-left: 5px solid #3c763d; margin-bottom: 15px;">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>Success!</strong> <?php echo $_SESSION['reset_message']; ?>
</div>
<?php 
  // Clear the session variables
  unset($_SESSION['reset_success']);
  unset($_SESSION['reset_message']);
endif; 
?>

<?php if(isset($_SESSION['reset_all_success']) && $_SESSION['reset_all_success']): ?>
<div class="alert alert-success" style="background-color: #dff0d8; color: #3c763d; border-color: #d6e9c6; border-radius: 4px; border-left: 5px solid #3c763d; margin-bottom: 15px;">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>Success!</strong> All <?php echo $_SESSION['reset_all_count']; ?> voter passwords have been reset and all voters set to Unvoted status.
  <br>
  <a class="btn btn-mini btn-info" data-toggle="modal" href="#resetPasswordsModal" style="margin-top: 5px;">
    <i class="icon-list"></i> View New Passwords
  </a>
</div>

<!-- Modal for displaying all reset passwords -->
<div class="modal hide fade" id="resetPasswordsModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Reset Passwords</h3>
  </div>
  <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>New Password</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($_SESSION['reset_all_data'] as $voter): ?>
        <tr>
          <td><?php echo $voter['id']; ?></td>
          <td><?php echo $voter['name']; ?></td>
          <td><code><?php echo $voter['password']; ?></code></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
  </div>
</div>

<?php 
  // Clear the session variables
  unset($_SESSION['reset_all_success']);
  unset($_SESSION['reset_all_count']);
  unset($_SESSION['reset_all_data']);
endif; 
?>

<div class="wrapper">
<div class="home_body">
<div class="navbar">
	<div class="navbar-inner">
	<div class="container">	
	<ul class="nav nav-pills">
	  <li>....</li>
	  <li><a href="home.php"><i class="icon-home icon-large"></i>Home</a></li>
	  <li><a  href="candidate_list.php"><i class="icon-align-justify icon-large"></i>Candidates List</a></li>  
	  <li class="active"><a  href="voter_list.php"><i class="icon-align-justify icon-large"></i>Voters List</a></li>  
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
	
        <!-- Search container replacing voted/unvoted tabs -->
        <div class="search-container" style="background: linear-gradient(135deg, #2e8b57 0%, #228b22 100%); padding: 15px; margin-bottom: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; box-sizing: border-box;">
            <div style="text-align: center; margin-bottom: 15px;">
                <h3 style="color: white; margin: 0; font-size: 18px; font-weight: 600;">
                    <i class="icon-search" style="margin-right: 8px;"></i>Search Voters
                </h3>
            </div>
            
            <form method="GET" action="voter_list.php" style="margin: 0;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; width: 100%;">
                    <div style="flex: 1; min-width: 200px; max-width: 100%; position: relative;">
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search_term); ?>" 
                               placeholder="Search by name, status, department, year..." 
                               style="width: 100%; max-width: 100%; padding: 12px 45px 12px 15px; border: none; border-radius: 25px; font-size: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); outline: none; transition: all 0.3s ease; box-sizing: border-box;"
                               onfocus="this.style.boxShadow='0 4px 20px rgba(0,0,0,0.2)'; this.style.transform='translateY(-1px)';"
                               onblur="this.style.boxShadow='0 2px 10px rgba(0,0,0,0.1)'; this.style.transform='translateY(0)';">
                        <i class="icon-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #999; font-size: 16px;"></i>
                    </div>
                    <div style="display: flex; gap: 8px; flex-shrink: 0;">
                        <button type="submit" class="btn" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; border: none; padding: 12px 18px; border-radius: 25px; cursor: pointer; font-weight: 600; box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3); transition: all 0.3s ease; white-space: nowrap;"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(76, 175, 80, 0.4)';"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(76, 175, 80, 0.3)';">
                            <i class="icon-search"></i> Search
                        </button>
                        <?php if (!empty($search_term)): ?>
                            <a href="voter_list.php" class="btn" style="background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%); color: white; text-decoration: none; padding: 12px 18px; border-radius: 25px; font-weight: 600; box-shadow: 0 3px 10px rgba(244, 67, 54, 0.3); transition: all 0.3s ease; white-space: nowrap;"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(244, 67, 54, 0.4)';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(244, 67, 54, 0.3)';">
                                <i class="icon-remove"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($search_term)): ?>
                    <div style="margin-top: 15px; text-align: center; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px; backdrop-filter: blur(10px);">
                        <i class="icon-info-sign" style="color: #fff; margin-right: 5px;"></i>
                        <span style="color: white; font-size: 13px; font-weight: 500;">
                            Showing results for: "<strong style="color: #ffeb3b;"><?php echo htmlspecialchars($search_term); ?></strong>"
                        </span>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Simplified navigation - only All and Statistics -->
        <div class="pagination">
            <ul>
                <li class="active"><a href="voter_list.php"><font color="white">All</font></a></li>
                <li><a href="new_voter.php"><font color="white"><i class="icon-plus icon-large"></i>Add Voters</font></a></li>
                <li><a href="#statsModal" data-toggle="modal"><font color="white"><i class="icon-bar-chart icon-large"></i>Statistics</font></a></li>
            </ul>
        </div>

    <!-- Add export error alert -->
    <?php if(isset($_SESSION['export_error'])): ?>
    <div class="alert alert-error" style="background-color: #f2dede; color: #b94a48; border-color: #eed3d7; border-radius: 4px; border-left: 5px solid #b94a48; margin-bottom: 15px; clear: both;">
      <i class="icon-exclamation-sign icon-large"></i> <strong>Export Error:</strong> <?php echo $_SESSION['export_error']; ?>
    </div>
    <?php unset($_SESSION['export_error']); endif; ?>

	<div class="excel-download-container">
			<form method="POST" action="excel_voter.php">
	<button id="excel" class="btn btn-success" name="save"><i class="icon-download icon-large"></i>Download Excel File</button>
	</form>
	</div>
	
	<!-- Reset All Passwords Button -->
	<div class="reset-all-container" style="float: right; margin-right: 10px;">
		<a href="batch_reset_passwords.php" class="btn btn-warning"><i class="icon-refresh icon-large"></i> Reset All Passwords</a>
	</div>
	
	<!-- Clear any floats -->
    <div class="clear-fix" style="clear: both;"></div>

	<!-- Voters Table with Improved Styling -->
    <div class="voters-table-container">
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="log" class="jtable">
            <thead>
                <tr>
                    <th>FirstName</th>
                    <th>LastName</th>
                    <th>Year</th>
                    <th>Department</th>
                    <th>Course</th>
                    <th>Password</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            if (!empty($search_term)) {
                // When searching, show all results without year grouping
                $voter_query = mysqli_query($conn, "SELECT v.*, c.course_name, d.department_name 
                                            FROM voters v 
                                            LEFT JOIN courses c ON v.course_id = c.course_id
                                            LEFT JOIN departments d ON v.department_id = d.department_id 
                                            WHERE 1=1 $combined_filter
                                            ORDER BY v.Status DESC, d.department_name, c.course_name, v.LastName, v.FirstName");
                
                $result_count = mysqli_num_rows($voter_query);
                
                if ($result_count > 0) {
                    while ($voter_rows = mysqli_fetch_array($voter_query)) { 
                        $id = $voter_rows['VoterID'];
                        $highlight_class = ($id == $updated_id) ? 'highlight-update' : '';
            ?>
            <tr class="del<?php echo $id ?> <?php echo $highlight_class ?>" id="voter-<?php echo $id ?>">
                <td><?php echo $voter_rows['FirstName']; ?></td>
                <td><?php echo $voter_rows['LastName']; ?></td>
                <td align="center"><?php echo $voter_rows['Year']; ?></td>
                <td align="center"><?php echo $voter_rows['department_name'] ? $voter_rows['department_name'] : 'Not Assigned'; ?></td>
                <td align="center"><?php echo $voter_rows['course_name'] ? $voter_rows['course_name'] : 'Not Assigned'; ?></td>
                <td align="center"><?php echo $voter_rows['Password']; ?></td>
                <td align="center">
                    <span class="status-badge <?php echo strtolower($voter_rows['Status']); ?>-badge">
                        <?php echo $voter_rows['Status']; ?>
                    </span>
                </td>
                <td width="240" align="center">
                    <a class="btn btn-Success" href="edit_voter.php<?php echo '?id='.$id; ?>"><i class="icon-edit icon-large"></i>&nbsp;Edit</a>&nbsp;
                    <a class="btn btn-danger1" id="<?php echo $id; ?>"><i class="icon-trash icon-large"></i>&nbsp;Delete</a>&nbsp;
                    <a class="btn btn-warning" href="reset_voter_password.php<?php echo '?id='.$id; ?>"><i class="icon-refresh icon-large"></i>&nbsp;Reset Password</a>
                </td>
            </tr>
            <?php 
                    }
                } else {
                    // No results found - display message
            ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; background-color: #f8f9fa; color: #6c757d; font-size: 16px; border: 2px dashed #dee2e6;">
                    <i class="icon-exclamation-sign icon-large" style="color: #ffc107; margin-right: 10px;"></i>
                    <strong>No Results Found</strong>
                    <br><br>
                    <span style="font-size: 14px;">
                        No voters match your search criteria: "<strong style="color: #dc3545;"><?php echo htmlspecialchars($search_term); ?></strong>"
                        <br>
                        Try adjusting your search terms or <a href="voter_list.php" style="color: #007bff; text-decoration: underline;">clear the search</a> to see all voters.
                    </span>
                </td>
            </tr>
            <?php 
                }
            } else {
                // Default view with year grouping when no search
                $years_query = mysqli_query($conn, "SELECT DISTINCT Year FROM voters ORDER BY 
                                                CASE Year 
                                                WHEN '1st year' THEN 1
                                                WHEN '2nd year' THEN 2
                                                WHEN '3rd year' THEN 3
                                                WHEN '4th year' THEN 4
                                                ELSE 5 END");
                
                while ($year_row = mysqli_fetch_array($years_query)) {
                    $year = $year_row['Year'];
                    
                    // Display year header with styled background
                    echo '<tr class="position-header"><td colspan="8" style="background-color: #4a86e8; color: white; font-weight: bold; padding: 10px; border-top: 1px solid #3a76d8; border-bottom: 1px solid #3a76d8;">' . $year . ' Students</td></tr>';
                    
                    // Get voters for this year with proper sorting
                    $voter_query = mysqli_query($conn, "SELECT v.*, c.course_name, d.department_name 
                                                FROM voters v 
                                                LEFT JOIN courses c ON v.course_id = c.course_id
                                                LEFT JOIN departments d ON v.department_id = d.department_id 
                                                WHERE v.Year = '$year' $combined_filter
                                                ORDER BY d.department_name, c.course_name, v.LastName, v.FirstName");
                    
                    while ($voter_rows = mysqli_fetch_array($voter_query)) { 
                        $id = $voter_rows['VoterID'];
                        $highlight_class = ($id == $updated_id) ? 'highlight-update' : '';
            ?>
            <tr class="del<?php echo $id ?> <?php echo $highlight_class ?>" id="voter-<?php echo $id ?>">
                <td><?php echo $voter_rows['FirstName']; ?></td>
                <td><?php echo $voter_rows['LastName']; ?></td>
                <td align="center"><?php echo $voter_rows['Year']; ?></td>
                <td align="center"><?php echo $voter_rows['department_name'] ? $voter_rows['department_name'] : 'Not Assigned'; ?></td>
                <td align="center"><?php echo $voter_rows['course_name'] ? $voter_rows['course_name'] : 'Not Assigned'; ?></td>
                <td align="center"><?php echo $voter_rows['Password']; ?></td>
                <td align="center">
                    <span class="status-badge <?php echo strtolower($voter_rows['Status']); ?>-badge">
                        <?php echo $voter_rows['Status']; ?>
                    </span>
                </td>
                <td width="240" align="center">
                    <a class="btn btn-Success" href="edit_voter.php<?php echo '?id='.$id; ?>"><i class="icon-edit icon-large"></i>&nbsp;Edit</a>&nbsp;
                    <a class="btn btn-danger1" id="<?php echo $id; ?>"><i class="icon-trash icon-large"></i>&nbsp;Delete</a>&nbsp;
                    <a class="btn btn-warning" href="reset_voter_password.php<?php echo '?id='.$id; ?>"><i class="icon-refresh icon-large"></i>&nbsp;Reset Password</a>
                </td>
            </tr>
            <?php 
                    } // end while voter_rows
                } // end while year_row
            } // end else
            ?>
            </tbody>
        </table>
    </div>

<style>
/* Add custom styling for status badges */
.status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: bold;
    font-size: 12px;
    display: inline-block;
    min-width: 80px;
}
.voted-badge {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.unvoted-badge {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}

/* Additional table styling */
.voters-table-container {
    margin-top: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    padding: 15px;
    overflow: hidden;
}

/* Style for the highlighted row */
@keyframes highlight-fade {
    from { background-color: #fcf8e3; }
    to { background-color: inherit; }
}

.highlight-update td {
    animation: highlight-fade 3s ease-out;
}

/* Clear fix for floating elements */
.clear-fix {
    clear: both;
    height: 20px;
}

/* Excel button styling */
.excel-download-container {
    float: right;
    margin-bottom: 15px;
}

/* Pagination styling */
.pagination {
    float: left;
    margin-bottom: 15px;
}
.pagination ul {
    display: flex;
    list-style: none;
}
.pagination ul li {
    margin-right: 5px;
}
.pagination ul li a {
    padding: 8px 15px;
    border-radius: 5px;
    background-color: var(--primary-green);
    display: inline-block;
    transition: all 0.3s ease;
}
.pagination ul li.active a,
.pagination ul li a:hover {
    background-color: var(--hover-green);
}
</style>
	
	
	<?php include('footer.php')?>
</div>
</div>

<!-- Statistics Modal -->
<div class="modal hide fade" id="statsModal" style="width: 800px; margin-left: -400px;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3>Voter Statistics for <?php echo htmlspecialchars($dept_name); ?></h3>
    </div>
    <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
        <div class="row-fluid">
            <div class="span6">
                <h4 style="text-align: center;">Voted vs. Unvoted</h4>
                <canvas id="voterStatusChart"></canvas>
            </div>
            <div class="span6">
                <h4 style="text-align: center;">Voters by Course</h4>
                <canvas id="votersByCourseChart"></canvas>
            </div>
        </div>
        <?php if ($dept_id === 'all'): ?>
        <div class="row-fluid" style="margin-top: 30px;">
            <div class="span12">
                <h4 style="text-align: center;">Voters by Department</h4>
                <canvas id="votersByDepartmentChart"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
    </div>
</div>

<input type="hidden" class="pc_date" name="pc_date"/>
<input type="hidden" class="pc_time" name="pc_time"/>
<?php include('footer_scripts.php'); ?>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('.btn-danger1').click(function() {
        var id = $(this).attr("id");
		var pc_date = $('.pc_date').val();
		var pc_time = $('.pc_time').val();
		var data_name = $('.data_name'+id).val();
		var user_name = $('.user_name').val();
		if(confirm("Are you sure you want to delete this Voter?")){
			$.ajax({
			    type: "POST",
			    url: "delete_voter.php",
			    data: ({id: id,pc_time:pc_time,pc_date:pc_date,data_name:data_name,user_name:user_name}),
			    cache: false,
			    success: function(html){
			        $(".del"+id).fadeOut('slow'); 
			    } 
			}); 
		} else {
			return false;
        }
    });

    var voterStatusChart = null;
    var votersByCourseChart = null;
    var votersByDepartmentChart = null;

    $('#statsModal').on('shown.bs.modal', function () {
        $.ajax({
            url: 'get_voter_stats.php',
            dataType: 'json',
            success: function(data) {
                if(voterStatusChart) voterStatusChart.destroy();
                if(votersByCourseChart) votersByCourseChart.destroy();
                if(votersByDepartmentChart) votersByDepartmentChart.destroy();

                var statusCtx = document.getElementById('voterStatusChart').getContext('2d');
                voterStatusChart = new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: data.status.labels,
                        datasets: [{
                            label: 'Voter Status',
                            data: data.status.values,
                            backgroundColor: ['#4CAF50', '#FFC107'],
                        }]
                    }
                });

                var courseCtx = document.getElementById('votersByCourseChart').getContext('2d');
                votersByCourseChart = new Chart(courseCtx, {
                    type: 'bar',
                    data: {
                        labels: data.courses.labels,
                        datasets: [{
                            label: 'Number of Voters',
                            data: data.courses.values,
                            backgroundColor: '#36A2EB',
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: { legend: { display: false } }
                    }
                });
                
                var deptChartElement = document.getElementById('votersByDepartmentChart');
                if (deptChartElement && data.departments) {
                    var deptCtx = deptChartElement.getContext('2d');
                    votersByDepartmentChart = new Chart(deptCtx, {
                        type: 'bar',
                        data: {
                            labels: data.departments.labels,
                            datasets: [{
                                label: 'Number of Voters',
                                data: data.departments.values,
                                backgroundColor: '#FF6384',
                            }]
                        },
                         options: {
                            responsive: true,
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            },
            error: function() {
                $('#statsModal .modal-body').html('<div class="alert alert-danger">Failed to load statistics. Please try again.</div>');
            }
        });
    });
});
</script>
