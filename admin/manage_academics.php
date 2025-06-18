<?php
include('session.php');
include('header.php');
include('dbcon.php');

// Super Admin Access Code
$access_code = "Pascioles"; // As requested: Pascioles
$is_authorized = false;

// Check if user is already authorized in the session
if (isset($_SESSION['is_academics_authorized']) && $_SESSION['is_academics_authorized'] === true) {
    $is_authorized = true;
}

// Check if the form has been submitted
if (isset($_POST['access_code'])) {
    if ($_POST['access_code'] === $access_code) {
        $_SESSION['is_academics_authorized'] = true;
        $is_authorized = true;
    } else {
        $error_message = "Invalid Access Code. Please try again.";
    }
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
	  <li>....</li>
	  <li><a href="home.php"><i class="icon-home icon-large"></i>Home</a></li>
	  <li><a  href="candidate_list.php"><i class="icon-align-justify icon-large"></i>Candidates List</a></li>  
	  <li class=""><a  href="voter_list.php"><i class="icon-align-justify icon-large"></i>Voters List</a></li>  
	  <li><a  href="canvassing_report.php"><i class="icon-book icon-large"></i>Canvassing Report</a></li>
    <li class="active"><a  href="manage_academics.php"><i class="icon-cog icon-large"></i>Academics</a></li>
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
	<button type="button" class="close" data-dismiss="modal">�</button>
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
    <?php if ($is_authorized): ?>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;">Academics Management</h2>
            <a href="lock_academics.php" class="btn btn-warning"><i class="icon-lock"></i> Back to Home & Lock</a>
        </div>
        <div class="row">
            <div class="span6">
                <h3>Manage Departments</h3>
                <form id="add-department-form" class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label" for="department_name">Department Name</label>
                        <div class="controls">
                            <input type="text" id="department_name" name="department_name" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="department_code">Department Code</label>
                        <div class="controls">
                            <input type="text" id="department_code" name="department_code">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <button type="submit" class="btn btn-primary">Add Department</button>
                        </div>
                    </div>
                </form>
                <hr>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th>Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="departments-list">
                        <!-- Departments will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
            <div class="span6">
                <h3>Manage Courses</h3>
                <form id="add-course-form" class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label" for="course_department">Department</label>
                        <div class="controls">
                            <select id="course_department" name="department_id" required>
                                <option value="">-- Select Department --</option>
                                <?php
                                $depts = mysqli_query($conn, "SELECT * FROM departments ORDER BY department_name");
                                while($dept = mysqli_fetch_array($depts)) {
                                    echo "<option value='{$dept['department_id']}'>{$dept['department_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="course_name">Course Name</label>
                        <div class="controls">
                            <input type="text" id="course_name" name="course_name" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="course_code">Course Code</label>
                        <div class="controls">
                            <input type="text" id="course_code" name="course_code">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <button type="submit" class="btn btn-primary">Add Course</button>
                        </div>
                    </div>
                </form>
                <hr>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="courses-list">
                        <!-- Courses will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="container">
        <div class="row">
            <div class="span6 offset3">
                <div class="access-code-container" style="margin-top: 50px; padding: 30px; background: #f9f9f9; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1);">
                    <h3 style="text-align: center; color: #333;">Restricted Access</h3>
                    <p style="text-align: center; color: #666;">This page requires a special access code. Please enter it below.</p>
                    <hr>
                    <form method="POST" action="manage_academics.php" class="form-horizontal">
                        <div class="control-group">
                            <label class="control-label" for="access_code">Access Code</label>
                            <div class="controls">
                                <input type="password" name="access_code" id="access_code" required>
                            </div>
                        </div>
                        <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger" style="text-align: center;">
                            <?php echo $error_message; ?>
                        </div>
                        <?php endif; ?>
                        <div class="control-group">
                            <div class="controls">
                                <button type="submit" class="btn btn-primary">Unlock Access</button>
                                <a href="home.php" class="btn">Go Back</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
	</div>
	<?php include('footer.php')?>	
</div>
</div>

<!-- Edit Department Modal -->
<div id="edit-department-modal" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h3>Edit Department</h3>
    </div>
    <div class="modal-body">
        <form id="edit-department-form" class="form-horizontal">
            <input type="hidden" id="edit_department_id" name="department_id">
            <div class="control-group">
                <label class="control-label" for="edit_department_name">Department Name</label>
                <div class="controls">
                    <input type="text" id="edit_department_name" name="department_name" required>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="edit_department_code">Department Code</label>
                <div class="controls">
                    <input type="text" id="edit_department_code" name="department_code">
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Close</a>
        <button class="btn btn-primary" id="save-department-changes">Save Changes</button>
    </div>
</div>

<!-- Edit Course Modal -->
<div id="edit-course-modal" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h3>Edit Course</h3>
    </div>
    <div class="modal-body">
        <form id="edit-course-form" class="form-horizontal">
            <input type="hidden" id="edit_course_id" name="course_id">
            <div class="control-group">
                <label class="control-label" for="edit_course_department">Department</label>
                <div class="controls">
                    <select id="edit_course_department" name="department_id" required>
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="edit_course_name">Course Name</label>
                <div class="controls">
                    <input type="text" id="edit_course_name" name="course_name" required>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="edit_course_code">Course Code</label>
                <div class="controls">
                    <input type="text" id="edit_course_code" name="course_code">
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Close</a>
        <button class="btn btn-primary" id="save-course-changes">Save Changes</button>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load initial lists
    loadDepartments();
    loadCourses();

    // Add Department
    $('#add-department-form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'add_department.php',
            data: $(this).serialize(),
            success: function(response) {
                loadDepartments();
                $('#add-department-form')[0].reset();
            }
        });
    });

    // Add Course
    $('#add-course-form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'add_course.php',
            data: $(this).serialize(),
            success: function(response) {
                loadCourses();
                $('#add-course-form')[0].reset();
            }
        });
    });

    // Functions to load data
    function loadDepartments() {
        $.ajax({
            url: 'get_departments.php',
            success: function(data) {
                $('#departments-list').html(data);
            }
        });
    }

    function loadCourses() {
        $.ajax({
            url: 'get_all_courses.php',
            success: function(data) {
                $('#courses-list').html(data);
            }
        });
    }

    // Delete Department
    $(document).on('click', '.delete-department', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to delete this department? This might affect existing courses and voters.')) {
            var id = $(this).data('id');
            $.ajax({
                type: 'POST',
                url: 'delete_department.php',
                data: { id: id },
                success: function(response) {
                    loadDepartments();
                    loadCourses(); // Also reload courses as they might be affected
                }
            });
        }
    });

    // Delete Course
    $(document).on('click', '.delete-course', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to delete this course?')) {
            var id = $(this).data('id');
            $.ajax({
                type: 'POST',
                url: 'delete_course.php',
                data: { id: id },
                success: function(response) {
                    loadCourses();
                }
            });
        }
    });

    // Edit Department - Open Modal
    $(document).on('click', '.edit-department', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: 'get_department_details.php',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                $('#edit_department_id').val(data.department_id);
                $('#edit_department_name').val(data.department_name);
                $('#edit_department_code').val(data.department_code);
                $('#edit-department-modal').modal('show');
            }
        });
    });

    // Save Department Changes
    $('#save-department-changes').click(function() {
        $.ajax({
            type: 'POST',
            url: 'update_department.php',
            data: $('#edit-department-form').serialize(),
            success: function(response) {
                $('#edit-department-modal').modal('hide');
                loadDepartments();
            }
        });
    });

    // Edit Course - Open Modal
    $(document).on('click', '.edit-course', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: 'get_course_details.php',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                $('#edit_course_id').val(data.course.course_id);
                $('#edit_course_name').val(data.course.course_name);
                $('#edit_course_code').val(data.course.course_code);
                
                var options = '';
                data.departments.forEach(function(dept) {
                    options += '<option value="' + dept.department_id + '"' + (dept.department_id == data.course.department_id ? ' selected' : '') + '>' + dept.department_name + '</option>';
                });
                $('#edit_course_department').html(options);
                
                $('#edit-course-modal').modal('show');
            }
        });
    });

    // Save Course Changes
    $('#save-course-changes').click(function() {
        $.ajax({
            type: 'POST',
            url: 'update_course.php',
            data: $('#edit-course-form').serialize(),
            success: function(response) {
                $('#edit-course-modal').modal('hide');
                loadCourses();
            }
        });
    });
});
</script>
</body>
</html> 