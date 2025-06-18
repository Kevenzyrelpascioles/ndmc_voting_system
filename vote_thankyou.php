<?php
session_start();
include('dbcon.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Get voter information for thank you message
$voter_id = $_SESSION['id'];
$voter_query = mysqli_query($conn, "SELECT * FROM voters WHERE VoterID='$voter_id'") or die(mysqli_error($conn));
$voter = mysqli_fetch_array($voter_query);
$voter_name = $voter['FirstName'] . ' ' . $voter['LastName'];

// Destroy session to prevent re-login
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thank You - NDMC Voting System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #E8F5E9, #ffffff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .thank-you-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 90%;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        
        .success-icon {
            font-size: 5rem;
            color: #4CAF50;
            margin-bottom: 1.5rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
        
        .thank-you-title {
            color: #2E7D32;
            font-weight: bold;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .voter-name {
            color: #4CAF50;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }
        
        .thank-you-message {
            color: #5a5a5a;
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .completion-badge {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 2rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .info-box {
            background: #e8f5e9;
            border: 1px solid #4CAF50;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .info-box i {
            color: #2E7D32;
            margin-right: 0.5rem;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }
        
        .ndmc-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 3px solid #4CAF50;
        }
        
        @media (max-width: 768px) {
            .thank-you-container {
                padding: 2rem;
            }
            
            .thank-you-title {
                font-size: 2rem;
            }
            
            .voter-name {
                font-size: 1.5rem;
            }
            
            .thank-you-message {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="thank-you-container">
        <img src="admin/images/NDMC logo.jpg" alt="NDMC Logo" class="ndmc-logo">
        
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        
        <h1 class="thank-you-title">Thank You for Voting!</h1>
        
        <div class="voter-name"><?php echo htmlspecialchars($voter_name); ?></div>
        
        <div class="completion-badge">
            <i class="bi bi-award me-2"></i>Vote Successfully Submitted
        </div>
        
        <div class="thank-you-message">
            Your vote has been successfully recorded and counted. Thank you for participating in the NDMC democratic process.
        </div>
        
        <div class="info-box">
            <div class="row">
                <div class="col-md-12">
                    <i class="bi bi-info-circle"></i>
                    <strong>Important:</strong> Your voting session has ended. You cannot vote again or access the voting system.
                </div>
            </div>
        </div>
        
        <div class="info-box">
            <div class="row">
                <div class="col-md-12">
                    <i class="bi bi-shield-check"></i>
                    <strong>Your vote is secure:</strong> All votes are encrypted and stored securely. Results will be announced after the voting period ends.
                </div>
            </div>
        </div>
        
        <a href="index.php" class="btn-home">
            <i class="bi bi-house me-2"></i>Return to Home
        </a>
        
        <div class="mt-4">
            <small class="text-muted">
                © <?php echo date('Y'); ?> Notre Dame of Midsayap College. All rights reserved.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Prevent back button after voting
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
        
        // Disable context menu and text selection
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('selectstart', function(e) {
            e.preventDefault();
        });
        
        // Clear any remaining session data
        if (typeof(Storage) !== "undefined") {
            localStorage.clear();
            sessionStorage.clear();
        }
    </script>
</body>
</html> 