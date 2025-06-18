<?php 
include('session.php');
include('dbcon.php');
include('header.php');
 ?>
 <link rel="stylesheet" type="text/css" href="admin/css/style.css" />
 <script type="text/javascript">
	$(document).ready(function()
		{
		 // Removed automatic redirect to index.php
		 // setTimeout(function(){ window.location = 'index.php';}, delay);  
    });
	
	

</script>
 
<script src="jquery.iphone-switch.js" type="text/javascript"></script>
</head>
<body>

	<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
	<div class="container">
	     
		<a class="brand">
		<img src="admin/images/chmsc.png" width="60" height="60">
 	</a>
	<a class="brand">
	 <h2>NDMC School Voting System</h2>
	 <div class="chmsc_nav"><font size="4" color="white">Notre Dame of Midsayap College</font></div>
 	</a>

	<?php include('head.php'); ?>
 
	</div>
	</div>
	</div>
<div class="wrapper">
<?php 
$result=mysqli_query($conn,"select * from voters where VoterID='$session_id'") or die(mysqli_error());
$row=mysqli_fetch_array($result);
?>
<div class="thank_you">
<div class="thank">
<h2><font size="6" color="white">Thank You For Voting:&nbsp;&nbsp;<?php echo $row['FirstName']." ".$row['LastName'];?></font></h2>
</div>
<?php session_destroy(); ?>
<div class="vote_complete">
<p style="color: white; text-align: center; font-size: 18px; margin-top: 20px;">
    Your vote has been successfully recorded. The voting session has ended for your account.
</p>
<div style="text-align: center; margin-top: 30px;">
    <a href="index.php" class="btn btn-success">Return to Login Page</a>
</div>
</div>
</div>
</div>




	
</div>

    </body>
	
</html>
												
											
	