<?php
session_start();
include('dbcon.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Check if vote selections are in session
if (!isset($_SESSION['temp_governor']) || !isset($_SESSION['temp_vice'])) {
    header("Location: voting4.php");
    exit();
}

// Get voter information
$voter_id = $_SESSION['id'];
$voter_query = mysqli_query($conn, "SELECT v.*, d.department_name, c.course_name 
                                    FROM voters v 
                                    LEFT JOIN departments d ON v.department_id = d.department_id
                                    LEFT JOIN courses c ON v.course_id = c.course_id
                                    WHERE v.VoterID = '$voter_id'") or die(mysqli_error($conn));
$voter = mysqli_fetch_array($voter_query);
$voter_department = $voter['department_id'];

// Get candidate details
$governor_id = $_SESSION['temp_governor'];
$vice_id = $_SESSION['temp_vice'];
$representative1_id = $_SESSION['temp_representative1'] ?? '';
$representative2_id = $_SESSION['temp_representative2'] ?? '';
$representative3_id = $_SESSION['temp_representative3'] ?? '';

// Get Governor details
$governor_query = mysqli_query($conn, "SELECT * FROM candidate WHERE CandidateID='$governor_id'") or die(mysqli_error($conn));
$governor_data = mysqli_fetch_array($governor_query);

// Get Vice-Governor details
$vice_query = mysqli_query($conn, "SELECT * FROM candidate WHERE CandidateID='$vice_id'") or die(mysqli_error($conn));
$vice_data = mysqli_fetch_array($vice_query);

// Get Representative details
$representatives = array();
if (!empty($representative1_id) && $representative1_id != '--Select Candidate--') {
    $rep1_query = mysqli_query($conn, "SELECT * FROM candidate WHERE CandidateID='$representative1_id'") or die(mysqli_error($conn));
    $representatives[] = mysqli_fetch_array($rep1_query);
}
if (!empty($representative2_id) && $representative2_id != '--Select Candidate--') {
    $rep2_query = mysqli_query($conn, "SELECT * FROM candidate WHERE CandidateID='$representative2_id'") or die(mysqli_error($conn));
    $representatives[] = mysqli_fetch_array($rep2_query);
}
if (!empty($representative3_id) && $representative3_id != '--Select Candidate--') {
    $rep3_query = mysqli_query($conn, "SELECT * FROM candidate WHERE CandidateID='$representative3_id'") or die(mysqli_error($conn));
    $representatives[] = mysqli_fetch_array($rep3_query);
}

// Handle final vote submission
if (isset($_POST['finalize_vote'])) {
    // Check votes table structure
    $check_table = mysqli_query($conn, "SHOW COLUMNS FROM votes") or die(mysqli_error($conn));
    $columns = array();
    while($row = mysqli_fetch_array($check_table)) {
        $columns[] = $row['Field'];
    }
    
    // Determine the correct voter ID field name
    $voter_id_field = in_array('VoterID', $columns) ? 'VoterID' : 'voter_id';
    
    // Insert governor and vice-governor votes
    mysqli_query($conn, "INSERT INTO votes (CandidateID, $voter_id_field) VALUES ('$governor_id', '$voter_id')") or die(mysqli_error($conn));
    mysqli_query($conn, "INSERT INTO votes (CandidateID, $voter_id_field) VALUES ('$vice_id', '$voter_id')") or die(mysqli_error($conn));
    
    // Insert representative votes (if selected)
    if (!empty($representative1_id) && $representative1_id != '--Select Candidate--') {
        mysqli_query($conn, "INSERT INTO votes (CandidateID, $voter_id_field) VALUES ('$representative1_id', '$voter_id')") or die(mysqli_error($conn));
    }
    if (!empty($representative2_id) && $representative2_id != '--Select Candidate--') {
        mysqli_query($conn, "INSERT INTO votes (CandidateID, $voter_id_field) VALUES ('$representative2_id', '$voter_id')") or die(mysqli_error($conn));
    }
    if (!empty($representative3_id) && $representative3_id != '--Select Candidate--') {
        mysqli_query($conn, "INSERT INTO votes (CandidateID, $voter_id_field) VALUES ('$representative3_id', '$voter_id')") or die(mysqli_error($conn));
    }
    
    // Update voter status
    mysqli_query($conn, "UPDATE voters SET Status = 'Voted' WHERE VoterID = '$voter_id'") or die(mysqli_error($conn));
    
    // Clear temporary vote data from session
    unset($_SESSION['temp_governor']);
    unset($_SESSION['temp_vice']);
    unset($_SESSION['temp_representative1']);
    unset($_SESSION['temp_representative2']);
    unset($_SESSION['temp_representative3']);
    
    // Redirect to thank you page
    header("Location: vote_thankyou.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Your Vote - NDMC Voting System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="admin/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #E8F5E9, #ffffff);
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(90deg, #2e7d32, #4caf50) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .confirmation-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 100px;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        
        .page-title {
            color: #2E7D32;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .candidate-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .candidate-card:hover {
            border-color: #4CAF50;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .position-title {
            color: #2E7D32;
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .candidate-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .candidate-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #4CAF50;
        }
        
        .candidate-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2E7D32;
        }
        
        .btn-finalize {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-finalize:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-change {
            background: linear-gradient(135deg, #6c757d, #adb5bd);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .alert-info {
            background-color: #e8f4f8;
            border-color: #4CAF50;
            color: #2E7D32;
            border-radius: 10px;
        }
        
        .voter-info {
            background: #e8f5e9;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .representative-group {
            background: #f0f8f0;
            border-radius: 10px;
            padding: 1rem;
        }
        
        .representative-item {
            background: white;
            border: 1px solid #4CAF50;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="admin/images/NDMC logo.jpg" class="rounded-circle img-fluid me-2" alt="NDMC Logo" style="height:40px;">
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

    <div class="container">
        <div class="confirmation-container">
            <h1 class="page-title">
                <i class="bi bi-check-circle me-2"></i>Confirm Your Vote
            </h1>
            
            <div class="voter-info">
                <div class="row">
                    <div class="col-md-4"><strong>Voter:</strong> <?php echo $voter['FirstName'] . ' ' . $voter['LastName']; ?></div>
                    <div class="col-md-4"><strong>Department:</strong> <?php echo $voter['department_name'] ?? 'N/A'; ?></div>
                    <div class="col-md-4"><strong>Course:</strong> <?php echo $voter['course_name'] ?? 'N/A'; ?></div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Please review your selections carefully.</strong> Once you finalize your vote, you cannot change it.
            </div>
            
            <!-- Governor Selection -->
            <div class="candidate-card">
                <div class="position-title">
                    <i class="bi bi-person-badge me-2"></i>Governor
                </div>
                <div class="candidate-info">
                    <img src="admin/<?php echo $governor_data['Photo']; ?>" alt="Governor Photo" class="candidate-photo">
                    <div>
                        <div class="candidate-name"><?php echo $governor_data['FirstName'] . ' ' . $governor_data['LastName']; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Vice-Governor Selection -->
            <div class="candidate-card">
                <div class="position-title">
                    <i class="bi bi-person-badge me-2"></i>Vice-Governor
                </div>
                <div class="candidate-info">
                    <img src="admin/<?php echo $vice_data['Photo']; ?>" alt="Vice-Governor Photo" class="candidate-photo">
                    <div>
                        <div class="candidate-name"><?php echo $vice_data['FirstName'] . ' ' . $vice_data['LastName']; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Representatives Selection -->
            <div class="candidate-card">
                <div class="position-title">
                    <i class="bi bi-people me-2"></i>4th Year Representatives
                </div>
                <div class="representative-group">
                    <?php if (count($representatives) > 0): ?>
                        <?php foreach ($representatives as $rep): ?>
                            <div class="representative-item">
                                <div class="candidate-info">
                                    <img src="admin/<?php echo $rep['Photo']; ?>" alt="Representative Photo" class="candidate-photo">
                                    <div>
                                        <div class="candidate-name"><?php echo $rep['FirstName'] . ' ' . $rep['LastName']; ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No representatives selected</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="voting4.php" class="btn btn-change">
                    <i class="bi bi-arrow-left me-2"></i>Change My Mind
                </a>
                
                <form method="POST" style="display: inline;">
                    <button type="submit" name="finalize_vote" class="btn btn-finalize" 
                            onclick="return confirm('Are you sure you want to finalize your vote? This action cannot be undone.');">
                        <i class="bi bi-check-circle me-2"></i>Finalize Vote
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 