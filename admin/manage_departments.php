<?php
session_start();
include('dbcon.php');
include('header.php');

// Check if user is logged in
if(!isset($_SESSION['id'])){
  header('location: index.php');
}

// Handle department operations
if(isset($_POST['add_department'])) {
  $dept_name = mysqli_real_escape_string($conn, $_POST['department_name']);
  $dept_code = mysqli_real_escape_string($conn, $_POST['department_code']);
  
  mysqli_query($conn, "INSERT INTO departments (department_name, department_code) 
                      VALUES ('$dept_name', '$dept_code')") or die(mysqli_error($conn));
  
  // Log the action
  $user_id = $_SESSION['id'];
  $user_query = mysqli_query($conn, "SELECT * FROM users WHERE User_id = '$user_id'");
  $user = mysqli_fetch_array($user_query);
  $user_name = $user['FirstName'] . ' ' . $user['LastName'];
  $user_type = $user['User_Type'];
  
  mysqli_query($conn, "INSERT INTO history (data, action, date, user) 
                      VALUES ('$dept_name', 'Added Department', NOW(), '$user_type')") 
                      or die(mysqli_error($conn));
  
  echo "<script>alert('Department added successfully!');</script>";
  echo "<script>window.location='manage_departments.php';</script>";
}

// Toggle department active/inactive status
if(isset($_GET['toggle']) && isset($_GET['id'])) {
  $dept_id = $_GET['id'];
  $status = $_GET['toggle'] == 'activate' ? 1 : 0;
  $action = $_GET['toggle'] == 'activate' ? 'Activated' : 'Deactivated';
  
  mysqli_query($conn, "UPDATE departments SET is_active = $status WHERE department_id = $dept_id") 
    or die(mysqli_error($conn));
  
  // Log the action  
  $dept_query = mysqli_query($conn, "SELECT department_name FROM departments WHERE department_id = '$dept_id'");
  $dept = mysqli_fetch_array($dept_query);
  $dept_name = $dept['department_name'];
  
  $user_id = $_SESSION['id'];
  $user_query = mysqli_query($conn, "SELECT * FROM users WHERE User_id = '$user_id'");
  $user = mysqli_fetch_array($user_query);
  $user_type = $user['User_Type'];
  
  mysqli_query($conn, "INSERT INTO history (data, action, date, user) 
                      VALUES ('$dept_name', '$action Department', NOW(), '$user_type')") 
                      or die(mysqli_error($conn));
    
  echo "<script>window.location='manage_departments.php';</script>";
}
?>

<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand">
        <img src="images/NDMC logo.jpg" class="circle-logo">
      </a>
      <a class="brand">
        <h2>NDMC Voting System</h2>
        <div class="chmsc_nav"><font size="4" color="white">Notre Dame of Midsayap College</font></div>
      </a>
      <?php include('head.php'); ?>
    </div>
  </div>
</div>

<div class="wrapper">
  <div class="hero-body-voting">
    <div class="vote_wise">
      <font color="white" size="6">Manage Departments</font>
    </div>
    <hr>
    
    <div class="adding_department">
      <form method="POST" class="form-horizontal">
        <div class="control-group">
          <label class="control-label" for="department_name">Department Name:</label>
          <div class="controls">
            <input type="text" name="department_name" id="department_name" placeholder="e.g., Information Technology" required>
          </div>
        </div>
        
        <div class="control-group">
          <label class="control-label" for="department_code">Department Code:</label>
          <div class="controls">
            <input type="text" name="department_code" id="department_code" placeholder="e.g., IT" required>
          </div>
        </div>
        
        <div class="control-group">
          <div class="controls">
            <button type="submit" name="add_department" class="btn btn-success"><i class="icon-plus icon-large"></i> Add Department</button>
          </div>
        </div>
      </form>
    </div>
    
    <div class="department_table">
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Department Name</th>
            <th>Code</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = mysqli_query($conn, "SELECT * FROM departments ORDER BY department_name ASC");
          while($row = mysqli_fetch_array($query)) {
            $status_badge = $row['is_active'] ? 
              '<span class="badge badge-success">Active</span>' : 
              '<span class="badge badge-important">Inactive</span>';
              
            $toggle_action = $row['is_active'] ? 
              '<a href="?toggle=deactivate&id='.$row['department_id'].'" class="btn btn-warning btn-mini"><i class="icon-ban-circle icon-large"></i> Deactivate</a>' : 
              '<a href="?toggle=activate&id='.$row['department_id'].'" class="btn btn-info btn-mini"><i class="icon-ok icon-large"></i> Activate</a>';
            
            echo "<tr>
              <td>".$row['department_id']."</td>
              <td>".$row['department_name']."</td>
              <td>".$row['department_code']."</td>
              <td>$status_badge</td>
              <td>$toggle_action</td>
            </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Back button -->
<div class="back_button">
  <a href="home.php" class="btn btn-info"><i class="icon-arrow-left icon-large"></i> Back to Home</a>
</div>

<?php include('footer.php'); ?> 