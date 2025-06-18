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
header('Location: voting2.php');
exit();

// Initialize variables for candidate names
$name = $name1 = $name2 = '';
$governor = $vice1 = $representative = '';

// Check if we have data from voting2.php in session
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

    // Only allow one representative selection
    if ($representative == '--Select Candidate--') {
        echo "<script>alert('Please select a representative.'); window.location='voting2.php';</script>";
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
        echo "<script>alert('Please select a candidate for all positions.'); window.location='voting2.php';</script>";
        exit();
    }
}
?>
<link rel="stylesheet" type="text/css" href="admin/css/style.css" />
<script src="jquery.iphone-switch.js" type="text/javascript"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top" style="background: linear-gradient(90deg, #2e7d32, #4caf50) !important;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="admin/images/NDMC logo.jpg" class="rounded-circle img-fluid me-2 circle-logo" alt="NDMC Logo">
                <div>
                    <span class="d-none d-md-inline">Notre Dame of Midsayap College</span>
                    <span class="d-inline d-md-none">NDMC</span>
                </div>
            </a>
            <?php include('head.php'); ?>
        </div>
    </nav>
    <div class="wrapper">
        <div class="hero-body-voting">
            <div class="vote_wise1"><font color="white" size="6">"Confirm Your Vote"</font></div>
            <div class="back">
                <a class="btn btn-info" id="bak" href="voting2.php"><i class="icon-arrow-left icon-large"></i>&nbsp;Back</a>
            </div>
            <div class="alert alert-success" style="margin-top: 20px;">
                <i class="bi bi-info-circle"></i> Please review your selections below and click "Submit Vote" to confirm.
            </div>
        </div>

        <div class="hero-body-456"></div>

        <form method="POST">
            <?php 
            if (isset($_POST['save'])){
                $governor = $_POST['governor'];
                $vice1 = $_POST['vice'];
                $representative = $_POST['representative'];

                if($representative == '--Select Candidate--'){
                    echo "<script>alert('Please select a representative.'); window.location='voting2.php';</script>";
                    exit();
                }

                // Get governor details
                $result = mysqli_query($conn,"select * from candidate where CandidateID='$governor'") or die(mysqli_error($conn));
                $row = mysqli_fetch_array($result);
                $name = $row ? $row['FirstName']." ".$row['LastName'] : '';

                // Get vice-governor details
                $vice = mysqli_query($conn,"select * from candidate where CandidateID='$vice1'") or die(mysqli_error($conn));
                $row_vice = mysqli_fetch_array($vice);
                $name1 = $row_vice ? $row_vice['FirstName']." ".$row_vice['LastName'] : '';

                // Get representative details
                $Representative = mysqli_query($conn,"select * from candidate where CandidateID='$representative'") or die(mysqli_error($conn));
                $Representative_row = mysqli_fetch_array($Representative);
                $name2 = $Representative_row ? $Representative_row['FirstName']." ".$Representative_row['LastName'] : '';
            }
            ?>

            <div class="ballot">
                <div class="cent">
                    <p>Governor:&nbsp;&nbsp;</p>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php echo $name ? $name : 'NO Candidate Selected'; ?>
                    <input type="hidden" name="gov" value="<?php echo isset($governor) ? $governor : ''; ?>"/>
                </div>
                </br></br>
                <div class="cent">
                    <p>Vice-Governor:&nbsp;&nbsp;</p>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php echo $name1 ? $name1 : 'NO Candidate Selected'; ?>
                    <input type="hidden" name="vice" value="<?php echo isset($vice1) ? $vice1 : ''; ?>"/>
                </div>
                </br></br>
                <div class="cent">
                    <p>2nd Year Representative:&nbsp;&nbsp;</p>
                    <div class="rep2">
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

            <div class="hero-body-456">
                <div class="ok_vote">
                    <a class="btn btn-success" id="logout" data-toggle="modal" href="#myModal"><i class="icon-off"></i>&nbsp;Submit Final Votes</a>
                    <div class="modal hide fade" id="myModal">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h3></h3>
                        </div>
                        <div class="modal-body">
                            <p><font color="gray">Are You Sure you Want to Submit Final Votes?</font></p>
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn" data-dismiss="modal">No</a>
                            <button id="save_voter" class="btn btn-success" name="vote"><i class="icon-save icon-large"></i>&nbsp;Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php include('footer1.php')?>    
    </div>
</body>
</html>