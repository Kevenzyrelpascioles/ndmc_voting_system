<?php 
include('session.php');
include('dbcon.php');
include('header.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Get voter information including department
$voter_id = $_SESSION['id'];
$voter_query = mysqli_query($conn, "SELECT v.*, d.department_name, c.course_name 
                                    FROM voters v 
                                    LEFT JOIN departments d ON v.department_id = d.department_id
                                    LEFT JOIN courses c ON v.course_id = c.course_id
                                    WHERE v.VoterID = '$voter_id'") or die(mysqli_error($conn));
$voter = mysqli_fetch_array($voter_query);
$voter_department = $voter['department_id'];
$voter_course = $voter['course_id'];

// Process vote submission
if(isset($_POST['save'])){
    $governor = $_POST['governor'];
    $vice = $_POST['vice'];
    $representative = $_POST['representative'];
    
    // Validate selections
    if(empty($governor) || empty($vice) || empty($representative) || 
       $governor == '--Select Candidate--' || $vice == '--Select Candidate--' || $representative == '--Select Candidate--') {
        echo "<script>alert('Please select a candidate for all positions.'); window.location='voting3.php';</script>";
        exit();
    }
    
    // Store selections in session for confirmation page
    $_SESSION['temp_governor'] = $governor;
    $_SESSION['temp_vice'] = $vice;
    $_SESSION['temp_representative'] = $representative;
    
    // Redirect to vote confirmation page
    header("Location: vote_confirmation.php");
    exit();
}
 ?>
 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
 <link rel="stylesheet" type="text/css" href="admin/css/style.css">
 <link rel="stylesheet" type="text/css" href="css/responsive.css">
 <link rel="stylesheet" type="text/css" href="css/voting-common.css">
 <script src="jquery.iphone-switch.js" type="text/javascript"></script>
 <script src="helper.js" type="text/javascript"></script>
</head>
<body class="text-dark">
	<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top" style="background: linear-gradient(90deg, #2e7d32, #4caf50) !important;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="admin/images/NDMC logo.jpg" class="rounded-circle img-fluid me-2 circle-logo" alt="NDMC Logo">
                <div>
                    <span class="d-none d-md-inline">Notre Dame of Midsayap College</span>
                    <span class="d-inline d-md-none">NDMC</span>
                </div>
            </a>
            <div class="ms-auto">
                <a href="logout_back.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container" style="margin-top: 100px;">
        <?php
        // Display department and course info
        echo '<div class="alert alert-info mb-3">
            <div class="row">
                <div class="col-sm-6"><strong>Your Department:</strong> '.($voter['department_name'] ?? 'N/A').'</div>
                <div class="col-sm-6"><strong>Your Course:</strong> '.($voter['course_name'] ?? 'N/A').'</div>
                <div class="col-sm-6"><strong>Year Level:</strong> 3rd Year</div>
            </div>
        </div>';
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h2 class="mb-2 mb-md-0 fw-bold">Please Vote Wisely</h2>
            <a class="btn btn-info" id="help" href="help3.php">
                <i class="bi bi-info-circle"></i>
                <span class="d-none d-md-inline">&nbsp;Help</span>
            </a>
        </div>
        <form method="post" action="voting3.php">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white" style="background: linear-gradient(90deg, #2e7d32, #4caf50) !important;">
                    <h4 class="m-0">Candidate for Governor</h4>
                </div>
                <div class="card-body">
                <div class="row g-3 mb-2">
                    <?php 
                    $governor=mysqli_query($conn,"SELECT * FROM candidate WHERE Position='Governor' AND department_id='$voter_department'") or die(mysqli_error($conn));
                    if(mysqli_num_rows($governor) == 0) {
                        echo '<div class="col-12"><div class="alert alert-warning">No candidates available for your department.</div></div>';
                    }
                    while($row=mysqli_fetch_array($governor)){ ?>
                        <div class="col-6 col-md-3 text-center">
                            <img class="img-fluid rounded mb-2" src="<?php echo $row['Photo'];?>" alt="<?php echo $row['FirstName'].' '.$row['LastName'];?>" style="max-width:120px; max-height:120px;">
                            <div class="small"><?php echo $row['FirstName'].' '.$row['LastName'];?></div>
                        </div>
                    <?php } ?>
                </div>
                <p class="text-muted small mb-2">Please select your candidate from the dropdown below:</p>
                <select name="governor" class="form-select mb-3" required>
                    <option value="">--Select Candidate--</option>
                    <?php
                    $governor=mysqli_query($conn,"SELECT * FROM candidate WHERE Position='Governor' AND department_id='$voter_department'") or die(mysqli_error($conn));
                    while($row=mysqli_fetch_array($governor)){ ?>
                        <option value="<?php echo $row['CandidateID']; ?>"><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white" style="background: linear-gradient(90deg, #2e7d32, #4caf50) !important;">
                    <h4 class="m-0">Candidate for Vice-Governor</h4>
                </div>
                <div class="card-body">
                <div class="row g-3 mb-2">
                    <?php 
                    $vice=mysqli_query($conn,"SELECT * FROM candidate WHERE Position='Vice-Governor' AND department_id='$voter_department'") or die(mysqli_error($conn));
                    if(mysqli_num_rows($vice) == 0) {
                        echo '<div class="col-12"><div class="alert alert-warning">No candidates available for your department.</div></div>';
                    }
                    while($row=mysqli_fetch_array($vice)){ ?>
                        <div class="col-6 col-md-3 text-center">
                            <img class="img-fluid rounded mb-2" src="<?php echo $row['Photo'];?>" alt="<?php echo $row['FirstName'].' '.$row['LastName'];?>" style="max-width:120px; max-height:120px;">
                            <div class="small"><?php echo $row['FirstName'].' '.$row['LastName'];?></div>
                        </div>
                    <?php } ?>
                </div>
                <p class="text-muted small mb-2">Please select your candidate from the dropdown below:</p>
                <select name="vice" class="form-select mb-3" required>
                    <option value="">--Select Candidate--</option>
                    <?php
                    $vice=mysqli_query($conn,"SELECT * FROM candidate WHERE Position='Vice-Governor' AND department_id='$voter_department'") or die(mysqli_error($conn));
                    while($row=mysqli_fetch_array($vice)){ ?>
                        <option value="<?php echo $row['CandidateID']; ?>"><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white" style="background: linear-gradient(90deg, #2e7d32, #4caf50) !important;">
                    <h4 class="m-0">Candidate for 3rd Year Representative</h4>
                </div>
                <div class="card-body">
                    <div class="candidates-grid row g-3 mb-3">
                        <?php 
                        $representative_query=mysqli_query($conn,"SELECT * FROM candidate WHERE Position='3rd Year Representative' AND course_id='$voter_course'") or die(mysqli_error($conn));
                        if(mysqli_num_rows($representative_query) == 0) {
                            echo '<div class="col-12"><div class="alert alert-warning">No candidates available for your course.</div></div>';
                        }
                        while($row=mysqli_fetch_array($representative_query)){ ?>
                            <div class="col-4 col-md-2 text-center candidate-item">
                                <img class="img-fluid rounded mb-2" src="admin/<?php echo $row['Photo'];?>" alt="<?php echo $row['FirstName'].' '.$row['LastName'];?>">
                                <div class="small"><?php echo $row['FirstName'].' '.$row['LastName'];?></div>
                            </div>
                        <?php } ?>
                    </div>
                    <p class="text-muted small mb-2">Please select your candidate from the dropdown below:</p>
                    <select name="representative" class="form-select mb-3" required>
                        <option value="">--Select Candidate--</option>
                        <?php
                        $representative_query=mysqli_query($conn,"SELECT * FROM candidate WHERE Position='3rd Year Representative' AND course_id='$voter_course'") or die(mysqli_error($conn));
                        while($row=mysqli_fetch_array($representative_query)){ ?>
                            <option value="<?php echo $row['CandidateID']; ?>"><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-between my-4 button-group">
                <button id="save_voter" class="btn btn-success" name="save" type="submit">
                    <i class="bi bi-hand-thumbs-up"></i>&nbsp;Submit Vote
                </button>
                <a class="btn btn-outline-secondary" id="index" data-bs-toggle="modal" href="#myModal">
                    <i class="bi bi-arrow-left-circle"></i>&nbsp;Vote later
                </a>
            </div>
        </form>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel">Vote Later</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to vote later?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
            <a href="logout_back.php" class="btn btn-success">Yes</a>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<script>
// Add confirmation for logout button
document.addEventListener('DOMContentLoaded', function() {
    // Find all logout buttons and add click event listener
    const logoutLinks = document.querySelectorAll('a[href="logout_back.php"]');
    logoutLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Prevent the default action
            e.preventDefault();
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to logout? Any unsaved votes will be lost.')) {
                // If confirmed, proceed with logout
                window.location.href = 'logout_back.php';
            }
        });
    });
});
</script>
    