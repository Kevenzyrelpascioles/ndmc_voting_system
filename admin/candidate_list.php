<?php
include('session.php');
include('header.php');
include('dbcon.php');

// Handle search functionality
$search_filter = "";
$search_term = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = mysqli_real_escape_string($conn, trim($_GET['search']));
    
    // Create more intelligent search conditions with alternative terms and partial matching
    $search_conditions = array();
    
    // Basic field searches (original + new fields)
    $search_conditions[] = "c.Position LIKE '%$search_term%'";
    $search_conditions[] = "c.FirstName LIKE '%$search_term%'";
    $search_conditions[] = "c.LastName LIKE '%$search_term%'";
    $search_conditions[] = "c.MiddleName LIKE '%$search_term%'";
    $search_conditions[] = "c.Gender LIKE '%$search_term%'";
    $search_conditions[] = "c.Party LIKE '%$search_term%'";
    $search_conditions[] = "c.Year LIKE '%$search_term%'";
    $search_conditions[] = "d.department_name LIKE '%$search_term%'";
    $search_conditions[] = "co.course_name LIKE '%$search_term%'";
    
    // Enhanced search for positions with alternatives
    $lower_term = strtolower($search_term);
    
    // Gender alternatives
    if ($lower_term === 'male' || $lower_term === 'm' || strpos($lower_term, 'boy') !== false || strpos($lower_term, 'man') !== false) {
        $search_conditions[] = "c.Gender = 'Male'";
    }
    if ($lower_term === 'female' || $lower_term === 'f' || strpos($lower_term, 'girl') !== false || strpos($lower_term, 'woman') !== false) {
        $search_conditions[] = "c.Gender = 'Female'";
    }
    
    // Governor alternatives
    if (strpos($lower_term, 'gov') !== false || $lower_term === 'g') {
        $search_conditions[] = "c.Position = 'Governor'";
    }
    
    // Vice-Governor alternatives
    if (strpos($lower_term, 'vice') !== false || strpos($lower_term, 'vgov') !== false || $lower_term === 'v' || $lower_term === 'vg') {
        $search_conditions[] = "c.Position = 'Vice-Governor'";
    }
    
    // Year-based searches with multiple alternatives
    // 1st Year alternatives
    if ($lower_term === '1' || $lower_term === '1st' || strpos($lower_term, 'first') !== false || strpos($lower_term, '1st year') !== false) {
        $search_conditions[] = "c.Position = '1st Year Representative'";
        $search_conditions[] = "c.Year LIKE '%1st%'";
    }
    
    // 2nd Year alternatives
    if ($lower_term === '2' || $lower_term === '2nd' || strpos($lower_term, 'second') !== false || strpos($lower_term, '2nd year') !== false) {
        $search_conditions[] = "c.Position = '2nd Year Representative'";
        $search_conditions[] = "c.Year LIKE '%2nd%'";
    }
    
    // 3rd Year alternatives
    if ($lower_term === '3' || $lower_term === '3rd' || strpos($lower_term, 'third') !== false || strpos($lower_term, '3rd year') !== false) {
        $search_conditions[] = "c.Position = '3rd Year Representative'";
        $search_conditions[] = "c.Year LIKE '%3rd%'";
    }
    
    // 4th Year alternatives
    if ($lower_term === '4' || $lower_term === '4th' || strpos($lower_term, 'fourth') !== false || strpos($lower_term, '4th year') !== false) {
        $search_conditions[] = "c.Position = '4th Year Representative'";
        $search_conditions[] = "c.Year LIKE '%4th%'";
    }
    
    // Representative alternatives
    if (strpos($lower_term, 'rep') !== false || strpos($lower_term, 'representative') !== false || $lower_term === 'r') {
        $search_conditions[] = "c.Position LIKE '%Representative%'";
    }
    
    // Common party alternatives (add more as needed)
    $party_alternatives = array(
        'dem' => 'Democratic',
        'rep' => 'Republican', 
        'ind' => 'Independent',
        'lib' => 'Liberal',
        'con' => 'Conservative'
    );
    
    foreach ($party_alternatives as $abbrev => $full_name) {
        if ($lower_term === $abbrev) {
            $search_conditions[] = "c.Party LIKE '%$full_name%'";
        }
    }
    
    // Common department abbreviations and alternatives
    $dept_alternatives = array(
        'it' => array('Information Technology', 'IT'),
        'cs' => array('Computer Science', 'CS'),
        'ce' => array('Computer Engineering', 'CE'),
        'ece' => array('Electronics and Communications Engineering', 'ECE'),
        'ee' => array('Electrical Engineering', 'EE'),
        'me' => array('Mechanical Engineering', 'ME'),
        'ie' => array('Industrial Engineering', 'IE'),
        'cite' => array('College of Information Technology and Engineering', 'CITE')
    );
    
    foreach ($dept_alternatives as $abbrev => $full_names) {
        if ($lower_term === $abbrev) {
            foreach ($full_names as $full_name) {
                $search_conditions[] = "d.department_name LIKE '%$full_name%'";
                $search_conditions[] = "co.course_name LIKE '%$full_name%'";
            }
        }
    }
    
    // Combine all search conditions with OR
    if (!empty($search_conditions)) {
        $search_filter = " WHERE (" . implode(' OR ', $search_conditions) . ")";
    }
}

// Inside the PHP section at the top, add department filtering
$dept_filter = "";
$dept_name = "All Departments";

// Get current department from session
if (isset($_SESSION['current_department']) && $_SESSION['current_department'] != 'all') {
  $dept_id = $_SESSION['current_department'];
  if (!empty($search_filter)) {
    $dept_filter = " AND c.department_id = '$dept_id'";
  } else {
    $dept_filter = " WHERE c.department_id = '$dept_id'";
  }
  
  // Get department name for display
  $dept_query = mysqli_query($conn, "SELECT department_name FROM departments WHERE department_id = '$dept_id'");
  if ($dept_row = mysqli_fetch_array($dept_query)) {
    $dept_name = $dept_row['department_name'];
  }
} else {
  $dept_filter = "";
}

// Combine search and department filters
$combined_filter = $search_filter . $dept_filter;
?>
</head>

<body>
<?php include('nav_top.php'); ?>

<!-- Add department filter alert with better styling -->
<div class="alert alert-info" style="margin-top: 55px; background-color: #d9edf7; color: #31708f; border-color: #bce8f1; border-radius: 4px; border-left: 5px solid #31708f;">
  <strong>Current Filter:</strong> Showing candidates for <strong><?php echo $dept_name; ?></strong>
  <?php if(isset($_SESSION['current_department']) && $_SESSION['current_department'] != 'all'): ?>
    <a href="home.php?department=all" class="btn btn-mini btn-warning" style="margin-left: 10px; background-color: #f0ad4e; border-color: #eea236; color: white; text-shadow: none;">Show All</a>
  <?php endif; ?>
</div>

<div class="wrapper">
 
<div class="home_body">
<div class="navbar">
	<div class="navbar-inner">
	<div class="container">	
	<ul class="nav nav-pills">
	  <li>....</li>
	  <li><a href="home.php"><i class="icon-home icon-large"></i>Home</a></li>
	  <li  class="active"><a  href="candidate_list.php"><i class="icon-align-justify icon-large"></i>Candidates List</a></li>  
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
        <!-- Search container replacing position tabs -->
        <div class="search-container" style="background: linear-gradient(135deg, #2e8b57 0%, #228b22 100%); padding: 15px; margin-bottom: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; box-sizing: border-box;">
            <div style="text-align: center; margin-bottom: 15px;">
                <h3 style="color: white; margin: 0; font-size: 18px; font-weight: 600;">
                    <i class="icon-search" style="margin-right: 8px;"></i>Search Candidates
                </h3>
            </div>
            
            <form method="GET" action="candidate_list.php" style="margin: 0;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; width: 100%;">
                    <div style="flex: 1; min-width: 200px; max-width: 100%; position: relative;">
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search_term); ?>" 
                               placeholder="Type to search..." 
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
                            <a href="candidate_list.php" class="btn" style="background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%); color: white; text-decoration: none; padding: 12px 18px; border-radius: 25px; font-weight: 600; box-shadow: 0 3px 10px rgba(244, 67, 54, 0.3); transition: all 0.3s ease; white-space: nowrap;"
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

        <!-- Add candidates button container -->
        <div class="add-candidates-container">
            <a href="new_candidate.php" class="add-candidates-btn">
                <i class="icon-plus icon-large"></i>Add Candidates
            </a>
        </div>
        
        <!-- Excel download container -->
        <div class="excel-download-container">
            <form method="POST" action="candidate_position_excel.php">
                <button id="save_voter" class="btn btn-success excel-download-btn" name="save">
                    <i class="icon-download icon-large"></i>Download Excel File
                </button>
            </form>
        </div>
	
        <!-- Clear any floats -->
        <div class="clear-fix"></div>
        
        <!-- Candidates table -->
        <div class="canvassing-table">
            <table cellpadding="0" cellspacing="0" border="0" class="display" id="log" class="jtable">
                <thead>
                    <tr>
                    <th class="hide">abc</th>
                    <th>Position</th>
                    <th>FirstName</th>
                    <th>LastName</th>
                    <th>Year</th>
                    <th>Department</th>
                    <th>Course</th>
                    <th>Photo</th>
                    <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

            <?php 
            // Get distinct positions to create section headers
            $positions_query = mysqli_query($conn, "SELECT DISTINCT c.Position FROM candidate c 
                                                LEFT JOIN departments d ON c.department_id = d.department_id
                                                LEFT JOIN courses co ON c.course_id = co.course_id
                                                $combined_filter
                                                ORDER BY 
                                                CASE c.Position 
                                                WHEN 'Governor' THEN 1
                                                WHEN 'Vice-Governor' THEN 2
                                                WHEN '1st Year Representative' THEN 3
                                                WHEN '2nd Year Representative' THEN 4
                                                WHEN '3rd Year Representative' THEN 5
                                                WHEN '4th Year Representative' THEN 6
                                                ELSE 7 END");

            $positions_count = mysqli_num_rows($positions_query);
            
            if ($positions_count > 0) {
                while ($position_row = mysqli_fetch_array($positions_query)) {
                    $position = $position_row['Position'];
                    
                    // Display position header with styled background
                    echo '<tr class="position-header"><td class="hide"></td><td colspan="8" style="background-color: #4a86e8; color: white; font-weight: bold; padding: 10px; border-top: 1px solid #3a76d8; border-bottom: 1px solid #3a76d8;">' . $position . '</td></tr>';
                    
                    // Get candidates for this position with proper sorting
                    $position_filter = !empty($combined_filter) ? $combined_filter . " AND c.Position = '$position'" : " WHERE c.Position = '$position'";
                    
                    $candidate_query = mysqli_query($conn, "SELECT c.*, d.department_name, co.course_name 
                                                    FROM candidate c 
                                                    LEFT JOIN departments d ON c.department_id = d.department_id
                                                    LEFT JOIN courses co ON c.course_id = co.course_id
                                                    $position_filter
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
                <td align="center"><?php echo $candidate_rows['Position']; ?></td>
                <td><?php echo $candidate_rows['FirstName']; ?></td>
                <td><?php echo $candidate_rows['LastName']; ?></td>
                <td align="center"><?php echo $candidate_rows['Year']; ?></td>
                <td align="center"><?php echo $candidate_rows['department_name'] ? $candidate_rows['department_name'] : 'Not Assigned'; ?></td>
                <td align="center"><?php echo $candidate_rows['course_name'] ? $candidate_rows['course_name'] : 'Not Assigned'; ?></td>
                <td align="center"><img class="pic" width="40" height="30" src="<?php echo $candidate_rows['Photo'];?>" border="0" onmouseover="showtrail('<?php echo $candidate_rows['Photo'];?>','<?php echo $candidate_rows['FirstName']." ".$candidate_rows['LastName'];?> ',200,5)" onmouseout="hidetrail()"></a></td>
                <td width="240" align="center" class="actions-column">
                    <div class="action-buttons">
                        <a class="btn btn-Success" href="edit_candidate.php<?php echo '?id='.$id; ?>"><i class="icon-edit icon-large"></i>&nbsp;Edit</a>
                        <a class="btn btn-info" data-toggle="modal" href="#<?php echo $id; ?>" ><i class="icon-list icon-large"></i>&nbsp;View</a>
                        <a class="btn btn-danger1" id="<?php echo $id; ?>"><i class="icon-trash icon-large"></i>&nbsp;Delete</a>
                    </div>
                </td>

            <div class="modal hide fade" id="<?php echo $id ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h1>Candidate Information</h1>
                </div>	
                <div class="modal-body">
                    <p><img src="<?php echo $candidate_rows['Photo'];?>" width="200" height="200"></p>
                    <div class="pull-right-modal">
                        <p>FirstName:&nbsp;<?php echo $candidate_rows['FirstName'];  ?></p>
                        <p>LastName:&nbsp;<?php echo $candidate_rows['LastName'];  ?></p>
                        <p>MiddleName:&nbsp;<?php echo $candidate_rows['MiddleName'];  ?></p>
                        <p>Gender:&nbsp;<?php echo $candidate_rows['Gender'];  ?></p>
                        <p>Position:&nbsp;<?php echo $candidate_rows['Position'];  ?></p>
                        <p>Party:&nbsp;<?php echo $candidate_rows['Party'];  ?></p>
                        <p>Year:&nbsp;<?php echo $candidate_rows['Year'];  ?></p>
                        <p>Department:&nbsp;<?php echo $candidate_rows['department_name'] ? $candidate_rows['department_name'] : 'Not Assigned';  ?></p>
                        <p>Course:&nbsp;<?php echo $candidate_rows['course_name'] ? $candidate_rows['course_name'] : 'Not Assigned';  ?></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn" data-dismiss="modal">Close</a>
                </div>
            </div>	

            <input type="hidden" name="data_name" class="data_name<?php echo $id ?>" value="<?php echo $candidate_rows['FirstName']." ".$candidate_rows['LastName']; ?>"/>
            <input type="hidden" name="user_name" class="user_name" value="<?php echo $_SESSION['User_Type']; ?>"/>
            </tr>
            <?php 
                    } // end while candidate_rows
                } // end while position_row
            } else {
                // No results found - display message
            ?>
            <tr>
                <td class="hide"></td>
                <td colspan="8" style="text-align: center; padding: 40px; background-color: #f8f9fa; color: #6c757d; font-size: 16px; border: 2px dashed #dee2e6;">
                    <i class="icon-exclamation-sign icon-large" style="color: #ffc107; margin-right: 10px;"></i>
                    <strong>No Results Found</strong>
                    <br><br>
                    <span style="font-size: 14px;">
                        <?php if (!empty($search_term)): ?>
                            No candidates match your search criteria: "<strong style="color: #dc3545;"><?php echo htmlspecialchars($search_term); ?></strong>"
                            <br>
                            Try adjusting your search terms or <a href="candidate_list.php" style="color: #007bff; text-decoration: underline;">clear the search</a> to see all candidates.
                        <?php else: ?>
                            No candidates found in the current department filter.
                            <br>
                            Try <a href="home.php?department=all" style="color: #007bff; text-decoration: underline;">showing all departments</a> or add some candidates.
                        <?php endif; ?>
                    </span>
                </td>
            </tr>
            <?php 
            }
            ?>

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
<script type="text/javascript">
	$(document).ready( function() {
	
	var myDate = new Date();
	var pc_date = (myDate.getMonth()+1) + '/' + (myDate.getDate()) + '/' + myDate.getFullYear();
	var pc_time = myDate.getHours()+':'+myDate.getMinutes()+':'+myDate.getSeconds();
	jQuery(".pc_date").val(pc_date);
	jQuery(".pc_time").val(pc_time);
	
	
	$('.btn-danger1').click( function() {
		
		var id = $(this).attr("id");
		var pc_date = $('.pc_date').val();
		var pc_time = $('.pc_time').val();
		var data_name = $('.data_name'+id).val();
		var user_name = $('.user_name').val();
		
		if(confirm("Are you sure you want to delete this Candidate?")){
			
		
			$.ajax({
			type: "POST",
			url: "delete_candidate.php",
			data: ({id: id,pc_time:pc_time,pc_date:pc_date,data_name:data_name,user_name:user_name}),
			cache: false,
			success: function(html){
			$(".del"+id).fadeOut('slow'); 
			} 
			}); 
			}else{
			return false;}
		});				
	});
</script>

