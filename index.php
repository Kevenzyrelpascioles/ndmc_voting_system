<?php
session_start();
require_once('admin/error_reporting.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Voter Login - NDMC Voting System</title>
    <link rel="stylesheet" type="text/css" href="admin/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="admin/css/bootstrap-responsive.css" />
    <script src="admin/js/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script src="admin/js/bootstrap.js" type="text/javascript"></script>
    
    <!-- Define the redirect function here to avoid errors -->
    <script type="text/javascript">
        function redirectTo(url) {
            if(url) {
                window.location = url;
            }
        }
    </script>
    <style>
        /* Voter login styling matching admin */
        body {
            background-image: url('admin/images/NdmcBuilding.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .navbar-inner {
            background: #2E7D32 !important;
            background-image: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            padding: 0 20px;
        }
        
        .brand-container {
            display: flex;
            align-items: center;
        }
        
        .brand {
            text-decoration: none;
            margin-right: 10px;
        }
        
        .brand h2 {
            color: white;
            font-size: 22px;
            margin: 0;
            padding: 0;
        }
        
        .circle-logo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .chmsc_nav {
            font-size: 14px;
            color: white;
        }
        
        /* Login container */
        #element.hero-body-index {
            background-color: rgba(46, 125, 50, 0.3) !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4) !important;
            padding: 25px !important;
            margin-top: 100px !important;
            max-width: 350px !important;
            margin-left: auto !important;
            margin-right: auto !important;
            backdrop-filter: blur(5px);
        }
        
        /* Login header */
        .login-header h2 {
            color: white !important;
            text-align: center !important;
            margin-bottom: 25px !important;
            font-size: 24px !important;
            font-weight: bold !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3) !important;
        }
        
        /* Login form inputs */
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 10px !important;
            margin-bottom: 20px !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
            border-radius: 4px !important;
            font-size: 16px !important;
            background-color: rgba(255, 255, 255, 0.9) !important;
        }
        
        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus {
            outline: none !important;
            border-color: #4CAF50 !important;
            box-shadow: 0 0 8px rgba(76, 175, 80, 0.6) !important;
        }
        
        /* Login form labels */
        .login-form label {
            display: block !important;
            color: white !important;
            font-weight: bold !important;
            margin-bottom: 5px !important;
            font-size: 16px !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
        }
        
        /* Login button */
        .login-form .btn-success,
        .login-form button[name="Login"] {
            background-color: #4CAF50 !important;
            background-image: none !important;
            color: white !important;
            border: 1px solid #3d8b40 !important;
            padding: 12px !important;
            font-weight: bold !important;
            text-shadow: none !important;
            border-radius: 4px !important;
            width: 100% !important;
            cursor: pointer !important;
            font-size: 16px !important;
            transition: all 0.3s ease !important;
            margin-top: 10px !important;
        }
        
        .login-form .btn-success:hover,
        .login-form button[name="Login"]:hover {
            background-color: #3d8b40 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3) !important;
        }
        
        /* Error alerts */
        .alert-error {
            background-color: #f8d7da !important;
            border-color: #f5c6cb !important;
            color: #721c24 !important;
            padding: 10px !important;
            margin-top: 15px !important;
            border-radius: 4px !important;
            text-align: center !important;
        }
        
        /* Footer styling */
        .footer {
            margin-top: 40px !important;
        }
        
        .footer p {
            color: white !important;
            text-align: center !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            #element.hero-body-index {
                width: 90% !important;
                padding: 15px !important;
                margin-top: 70px !important;
            }
            
            .login-header h2 {
                font-size: 20px !important;
            }
            
            .login-form input[type="text"],
            .login-form input[type="password"] {
                padding: 8px !important;
            }
            
            .login-form .btn-success,
            .login-form button[name="Login"] {
                padding: 10px !important;
            }
        }
        
        /* Wrapper styling */
        .wrapper {
            padding: 20px;
        }
        
        /* Database error alerts */
        .alert-danger {
            background-color: #f8d7da !important;
            border-color: #f5c6cb !important;
            color: #721c24 !important;
            padding: 12px !important;
            margin: 0 auto 20px auto !important;
            border-radius: 4px !important;
            text-align: center !important;
            max-width: 350px !important;
            font-weight: bold !important;
        }
    </style>
</head>
<body>
<?php include('dbcon.php'); ?>

<!-- Display database connection errors -->
<?php if(isset($_SESSION['db_error']) && $_SESSION['db_error']): ?>
<div class="alert alert-danger">
    <strong>Database Error:</strong> <?php echo $_SESSION['error_message']; ?>
    <?php if(isset($_SESSION['debug_info'])): ?>
    <br><small><?php echo $_SESSION['debug_info']; ?></small>
    <?php endif; ?>
</div>
<?php 
    // Clear the session variables
    unset($_SESSION['db_error']);
    unset($_SESSION['error_message']);
    unset($_SESSION['debug_info']);
endif; 
?>

<!-- Define the redirect function in PHP -->
<?php
function _redirect($url='') {
    if(!empty($url)) {
        echo "<script>redirectTo('$url');</script>";
    }
}
?>

<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <div class="brand-container">
                <a class="brand">
                    <img src="admin/images/NDMC logo.jpg" class="circle-logo">
                </a>
                <a class="brand">
                    <h2>NDMC Voting System</h2>
                    <div class="chmsc_nav"><font size="4" color="white">Notre Dame of Midsayap College</font></div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="wrapper">
    <div class="container">
        <div id="element" class="hero-body-index">
            <div class="login-header">
                <h2>Voter Login</h2>
            </div>
            
            <div class="login-form">
                <form method="POST">
                    <div class="form-group">
                        <label for="UserName"><b>Username:</b></label>
                        <input type="text" name="UserName" id="UserName" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="Password"><b>Password:</b></label>
                        <input type="password" name="Password" id="Password" class="form-control" required>
                    </div>
                    
                    <div class="form-actions">
                        <button class="btn btn-success btn-large" name="Login" type="submit">
                            <i class="icon-ok icon-large"></i>&nbsp;Login
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="error">
                <?php
                if (isset($_POST['Login'])) {
                    $UserName = $_POST['UserName'];
                    $Password = $_POST['Password'];
                    
                    // First, check if the voter exists and get their department
                    $voter_query = mysqli_query($conn, "SELECT * FROM voters WHERE Username='$UserName' AND Password='$Password'");
                    
                    if ($voter_query && mysqli_num_rows($voter_query) > 0) {
                        $voter = mysqli_fetch_array($voter_query);
                        $voter_department = $voter['department_id'];
                        $voter_id = $voter['VoterID'];
                        $voter_year = $voter['Year'];
                        $voter_status = $voter['Status'];
                        $voter_name = $voter['FirstName'] . ' ' . $voter['LastName'];
                        
                        // Store voter info in session
                        $_SESSION['id'] = $voter_id;
                        $_SESSION['department'] = $voter_department;
                        $_SESSION['year'] = $voter_year;
                        
                        // Use try-catch to handle potential errors
                        try {
                            mysqli_query($conn, "INSERT INTO history (data,action,date,user)VALUES('$voter_name', 'Login', NOW(),'Voter')");
                            
                            // If already voted, redirect to voting progress
                            if($voter_status == 'Voted') {
                                echo '<div class="alert alert-error">You have already voted. Thank you for your participation!</div>';
                                exit();
                            }
                            
                            // Redirect based on year level
                            switch($voter_year) {
                                case '1st year':
                                    _redirect("voting.php");
                                    break;
                                case '2nd year':
                                    _redirect("voting2.php");
                                    break;
                                case '3rd year':
                                    _redirect("voting3.php");
                                    break;
                                case '4th year':
                                    _redirect("voting4.php");
                                    break;
                                default:
                                    _redirect("voting.php");
                            }
                        } catch (Exception $e) {
                            echo '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
                        }
                    } else {
                        ?>
                        <div class="alert alert-error">
                            <button class="close" data-dismiss="alert">×</button>
                            Please check your Username and Password
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p class="text-center">
                &copy; <?php echo date('Y'); ?> Notre Dame of Midsayap College. All rights reserved.
            </p>
        </div>
    </footer>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Adjust login form on smaller screens
        function adjustLoginForm() {
            if ($(window).width() < 480) {
                $('#element.hero-body-index').css({
                    'width': '90%',
                    'padding': '15px'
                });
            } else if ($(window).width() < 768) {
                $('#element.hero-body-index').css({
                    'width': '85%',
                    'padding': '20px'
                });
            } else {
                $('#element.hero-body-index').css({
                    'width': '350px',
                    'padding': '25px'
                });
            }
        }
        
        // Run on load and resize
        adjustLoginForm();
        $(window).resize(adjustLoginForm);
    });
</script>
</body>
</html>
																				
											
	