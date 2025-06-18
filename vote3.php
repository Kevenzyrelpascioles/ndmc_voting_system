<?php
// This file is no longer used in the new voting flow
// Redirect to the main voting page
include('session.php');

// Check if user is logged in
if(!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

// Redirect to the main voting page
header('Location: voting3.php');
exit();

// Get voter information including department
$voter_id = $_SESSION['id'];
$voter_query = mysqli_query($conn, "SELECT * FROM voters WHERE VoterID = '$voter_id'") or die(mysqli_error($conn));
$voter = mysqli_fetch_array($voter_query);
$voter_department = $voter['department_id'];

// Initialize variables for candidate names
$name = $name1 = $name2 = '';
$governor = $vice1 = $representative = '';

// Check if we have data from voting3.php in session
if (isset($_SESSION['governor']) && isset($_SESSION['vice']) && isset($_SESSION['representative'])) {
    $governor = $_SESSION['governor'];
    $vice1 = $_SESSION['vice'];
    $representative = $_SESSION['representative'];
    
    // Get governor details
    $result = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$governor' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $row = mysqli_fetch_array($result);
    $name = $row ? $row['FirstName']." ".$row['LastName'] : '';

    // Get vice-governor details
    $vice = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$vice1' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $row_vice = mysqli_fetch_array($vice);
    $name1 = $row_vice ? $row_vice['FirstName']." ".$row_vice['LastName'] : '';

    // Get representative details
    $Representative = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$representative' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $Representative_row = mysqli_fetch_array($Representative);
    $name2 = $Representative_row ? $Representative_row['FirstName']." ".$Representative_row['LastName'] : '';
}
// Process selections from direct form submission (fallback)
elseif (isset($_POST['save'])){
    $governor = $_POST['governor'];
    $vice1 = $_POST['vice'];
    $representative = $_POST['representative'];

    if($representative == '--Select Candidate--'){
        echo "<script>alert('Please select a representative.'); window.location='voting3.php';</script>";
        exit();
    }

    // Get governor details
    $result = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$governor' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $row = mysqli_fetch_array($result);
    $name = $row ? $row['FirstName']." ".$row['LastName'] : '';

    // Get vice-governor details
    $vice = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$vice1' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $row_vice = mysqli_fetch_array($vice);
    $name1 = $row_vice ? $row_vice['FirstName']." ".$row_vice['LastName'] : '';

    // Get representative details
    $Representative = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$representative' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $Representative_row = mysqli_fetch_array($Representative);
    $name2 = $Representative_row ? $Representative_row['FirstName']." ".$Representative_row['LastName'] : '';
}

// Handle final vote submission
if (isset($_POST['vote'])){
    $gov = $_POST['gov'];
    $vice = $_POST['vice'];
    $rep = $_POST['rep']; // Only one representative

    // Check votes table structure
    $check_table = mysqli_query($conn, "SHOW COLUMNS FROM votes") or die(mysqli_error($conn));
    $columns = array();
    while($row = mysqli_fetch_array($check_table)) {
        $columns[] = $row['Field'];
    }
    
    // Determine the correct voter ID field name
    $voter_id_field = in_array('VoterID', $columns) ? 'VoterID' : 'voter_id';

    // Insert votes only if all are selected
    if ($gov != '' && $vice != '' && $rep != '') {
        mysqli_query($conn,"INSERT INTO votes (CandidateID, $voter_id_field) VALUES ('$gov', '$voter_id')") or die(mysqli_error($conn));
        mysqli_query($conn,"INSERT INTO votes (CandidateID, $voter_id_field) VALUES ('$vice', '$voter_id')") or die(mysqli_error($conn));
        mysqli_query($conn,"INSERT INTO votes (CandidateID, $voter_id_field) VALUES ('$rep', '$voter_id')") or die(mysqli_error($conn));
        mysqli_query($conn,"UPDATE voters SET Status='Voted' WHERE VoterID='$voter_id'") or die(mysqli_error($conn));
        // Clear output buffer before redirect
        ob_end_clean();
        header('Location: voting_progress.php');
        exit();
    } else {
        echo "<script>alert('Please select a candidate for all positions.'); window.location='voting3.php';</script>";
        exit();
    }
}
?>
 <link rel="stylesheet" type="text/css" href="admin/css/style.css" />
<script src="jquery.iphone-switch.js" type="text/javascript"></script>
</head>
<body class="bg-light text-dark">
	<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top" style="background: linear-gradient(90deg, #2e7d32, #4caf50) !important;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="admin/images/NDMC logo.jpg" class="rounded-circle img-fluid me-2" alt="NDMC Logo" style="height:40px;">
                <span>Notre Dame of Midsayap College</span>
            </a>
            <div class="ms-auto">
                <a href="logout_back.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container" style="margin-top: 90px;">
        <?php
        // Get department name
        $dept_query = mysqli_query($conn, "SELECT department_name FROM departments WHERE department_id = '$voter_department'") or die(mysqli_error($conn));
        if($dept_row = mysqli_fetch_array($dept_query)) {
            echo '<div class="alert alert-info mb-3">
                <strong>Your Department:</strong> '.$dept_row['department_name'].' | <strong>Year Level:</strong> 3rd Year
            </div>';
        }
        ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Confirm Your Vote</h2>
            <a class="btn btn-info" id="bak" href="voting3.php"><i class="bi bi-arrow-left"></i>&nbsp;Back</a>
        </div>
        <div class="alert alert-success mb-4">
            <i class="bi bi-info-circle"></i> Please review your selections below and click "Submit Final Votes" to confirm.
        </div>

<form method="POST">
<?php 
if (isset($_POST['save'])){
    $governor = $_POST['governor'];
    $vice1 = $_POST['vice'];
    $representative = $_POST['representative'];

    if($representative == '--Select Candidate--'){
        echo "<script>alert('Please select a representative.'); window.location='voting3.php';</script>";
        exit();
    }

    // Get governor details
    $result = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$governor' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $row = mysqli_fetch_array($result);
    $name = $row ? $row['FirstName']." ".$row['LastName'] : '';

    // Get vice-governor details
    $vice = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$vice1' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $row_vice = mysqli_fetch_array($vice);
    $name1 = $row_vice ? $row_vice['FirstName']." ".$row_vice['LastName'] : '';

    // Get representative details
    $Representative = mysqli_query($conn,"SELECT * FROM candidate WHERE CandidateID='$representative' AND department_id='$voter_department'") or die(mysqli_error($conn));
    $Representative_row = mysqli_fetch_array($Representative);
    $name2 = $Representative_row ? $Representative_row['FirstName']." ".$Representative_row['LastName'] : '';
}
?>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-4">Review Your Selections</h5>
        <div class="mb-4">
            <p class="fw-bold mb-1">Governor:</p>
            <div class="ps-3"><?php echo $name ? $name : 'NO Candidate Selected'; ?></div>
            <input type="hidden" name="gov" value="<?php echo isset($governor) ? $governor : ''; ?>"/>
        </div>
        <div class="mb-4">
            <p class="fw-bold mb-1">Vice-Governor:</p>
            <div class="ps-3"><?php echo $name1 ? $name1 : 'NO Candidate Selected'; ?></div>
            <input type="hidden" name="vice" value="<?php echo isset($vice1) ? $vice1 : ''; ?>"/>
        </div>
        <div class="mb-2">
            <p class="fw-bold mb-1">3rd Year Representative:</p>
            <div class="ps-3">
                <?php
                if (empty($representative) || $representative=='--Select Candidate--'){
                    echo 'No Candidate Selected';
                } else {
                    echo $name2; 
                }
                ?>
                <input type="hidden" name="rep" value="<?php echo isset($representative) ? $representative : ''; ?>"/>
            </div>
        </div>
    </div>
</div>

<div class="d-grid gap-2">
    <a class="btn btn-success" id="logout" data-bs-toggle="modal" href="#myModal">
        <i class="bi bi-check-circle"></i>&nbsp;Submit Final Votes
    </a>
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Confirm Vote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to submit your final votes?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button id="save_voter" class="btn btn-success" name="vote">
                        <i class="bi bi-check-lg"></i>&nbsp;Yes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>
	