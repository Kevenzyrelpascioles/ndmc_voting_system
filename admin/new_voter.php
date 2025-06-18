<?php
include('session.php');
include('header.php');
include('dbcon.php');
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

	  <li class="active"><a  href="voter_list.php"><i class="icon-align-justify icon-large"></i>Voters List</a></li>  
		 <li><a  href="canvassing_report.php"><i class="icon-book icon-large"></i>Canvassing Report</a></li>
		    <li><a  href="History.php"><i class="icon-table icon-large"></i>History Log</a>
		   <li><a data-toggle="modal" href="#about"><i class="icon-exclamation-sign icon-large"></i>About</a></li>
		   <div class="modal hide fade" id="about">
	<div class="modal-header"> 
	<button type="button" class="close" data-dismiss="modal">�</button>
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

	<form id="save_voter" class="form-horizontal">
	<input type="hidden" class="pc_date" name="pc_date"/>
	<input type="hidden" class="pc_time" name="pc_time" />
	<input type="hidden" name="user_name" class="user_name" value="<?php echo $_SESSION['User_Type']; ?>"/>
	
    <fieldset>
        <!-- Header with Back Button and Title -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="voter_list.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; backdrop-filter: blur(10px);"
                   onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-1px)';"
                   onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)';">
                    <i class="icon-arrow-left"></i> Back to Voters List
                </a>
                <h3 style="color: white; margin: 0; font-size: 20px; font-weight: 600;">
                    <i class="icon-plus-sign" style="margin-right: 8px;"></i>Add New Voter
                </h3>
            </div>
        </div>

        <!-- Clean tab navigation -->
        <div class="pagination" style="margin-bottom: 20px;">
            <ul>
                <li><a href="voter_list.php"><font color="white">All Voters</font></a></li>
                <li class="active"><a href="new_voter.php"><font color="white"><i class="icon-plus icon-large"></i>Add Voters</font></a></li>
            </ul>
        </div>
	
	</br>
	<div class="new_voter_margin">
	<ul class="thumbnails_new_voter">
    <li class="span3">
    <div class="thumbnail_new_voter">
  
	<div class="control-group">
    <label class="control-label" for="input01">FirstName:</label>
    <div class="controls">
    <input type="text" name="FirstName" class="FirstName">
    </div>
    </div>
	
	<div class="control-group">
    <label class="control-label" for="input01">LastName:</label>
    <div class="controls">
    <input type="text" name="LastName" class="LastName">
    </div>
    </div>
	
	<div class="control-group">
    <label class="control-label" for="input01">MiddleName:</label>
    <div class="controls">
   <input type="text" name="Section" class="Section">
    </div>
    </div>
	
	<div class="control-group">
    <label class="control-label" for="input01" >Year Level:</label>
    <div class="controls">
   <select name="Year" class="Year" id="span2">
	<option>1st year</option>
	<option>2nd year</option>
	<option>3rd year</option>
	<option>4th year</option>
	</select>
    </div>
    </div>
	
	<div class="control-group">
    <label class="control-label" for="department">Department:</label>
    <div class="controls">
      <select name="department_id" id="department" class="span333" required>
        <option value="">-- Select Department --</option>
        <?php
          $dept_query = mysqli_query($conn, "SELECT * FROM departments WHERE is_active = 1 ORDER BY department_name ASC");
          while($dept_row = mysqli_fetch_array($dept_query)) {
            echo '<option value="'.$dept_row['department_id'].'">'.$dept_row['department_name'].'</option>';
          }
        ?>
      </select>
    </div>
    </div>

	<div class="control-group">
    <label class="control-label" for="course">Course:</label>
    <div class="controls">
      <select name="course_id" id="course" class="span333" required>
        <option value="">-- Select Department First --</option>
      </select>
    </div>
    </div>
	
	<div class="control-group">
    <label class="control-label" for="input01">UserName:</label>
    <div class="controls">
  <input type="text" name="UserName" class="UserName">
    </div>
    </div>
	
	<div class="control-group">
    <label class="control-label" for="input01">Password:</label>
    <div class="controls">
      <input type="text" name="Password" class="Password" placeholder="Leave empty to auto-generate">
      <p class="help-block"><small>If left empty, a random password will be generated</small></p>
    </div>
    </div>
	
	<div class="control-group">
    <div class="controls">
        <button id="save_voter" class="btn btn-primary" name="save" style="margin-right: 10px;">
            <i class="icon-save icon-large"></i>Save
        </button>
        <a href="voter_list.php" class="btn" style="background-color: #6c757d; color: white; text-decoration: none;">
            <i class="icon-remove"></i> Cancel
        </a>
    </div>
    </div>
	
    </fieldset>
    </form>
	
	
    </div>
    </li>
    </ul>
	
	<?php include('footer.php'); ?>	
	</div>
	</div>
	</div>
		
	
	
</div>

</body>
</html>

<script type="text/javascript">
$(document).ready( function () {

/*  another date shit*/

var myDate = new Date();
var pc_date = (myDate.getMonth()+1) + '/' + (myDate.getDate()) + '/' + myDate.getFullYear();
var pc_time = myDate.getHours()+':'+myDate.getMinutes()+':'+myDate.getSeconds();
jQuery(".pc_date").val(pc_date);
jQuery(".pc_time").val(pc_time);
/*asta d*/

jQuery(document).ready(function(){
    jQuery('#department').on('change', function(){
        var department_id = jQuery(this).val();
        if(department_id){
            jQuery.ajax({
                type:'POST',
                url:'get_courses.php',
                data:'department_id='+department_id,
                success:function(html){
                    jQuery('#course').html(html);
                }
            }); 
        }else{
            jQuery('#course').html('<option value="">-- Select Department First --</option>');
        }
    });
});

jQuery('#save_voter').submit(function(e){
    e.preventDefault();
var FirstName = jQuery('.FirstName').val();
var LastName = jQuery('.LastName').val();
var Section = jQuery('.Section').val();
var Year = jQuery('.Year').val();
var UserName = jQuery('.UserName').val();
var Password = jQuery('.Password').val();
var department_id = jQuery('#department').val();
var course_id = jQuery('#course').val();
	
    e.preventDefault();
if (FirstName && LastName && Section && Year && UserName && department_id && course_id){	
    var formData = jQuery(this).serialize();	
	
    jQuery.ajax({
        type: 'POST',
        url:  'save_voter.php',
        data: formData,
             success: function(response){
            // If password was autogenerated, display it
            if (Password === '' && response) {
                showNotification({
                    message: "Voter Successfully Added with password: " + response,
                    type: "success", 
                    autoClose: true, 
                    duration: 10
                });
            } else {
                showNotification({
                    message: "Voter Successfully Added",
                    type: "success", 
                    autoClose: true, 
                    duration: 5
                });
            }

		 var delay = 3000;
		setTimeout(function(){ window.location = 'voter_list.php';}, delay);  
	
        }
    });
	
}else
{
alert('All fields are required except Password (will be auto-generated if empty)!');
return false;
}	
});


});
</script>