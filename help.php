<?php 
include('session.php');
include('dbcon.php');
include('header.php');
 ?>
<link rel="stylesheet" type="text/css" href="admin/css/style.css" />
<script src="jquery.iphone-switch.js" type="text/javascript"></script>
</head>
<body>
	<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
	<div class="container">
	     
		<a class="brand">
		<img src="admin/images/NDMC logo.jpg" class="circle-logo" alt="NDMC Logo">
 	</a>
	<a class="brand">
	 <h2>Notredame</h2>
	 <div class="chmsc_nav"><span class="school-name">Notre Dame of Midsayap College</span></div>
 	</a>

	<?php include('head.php'); ?>
 
	</div>
	</div>
	</div>
<div class="wrapper">
<div class="hero-body-voting">
<div class="vote_wise"><h1 class="page-title">How to Vote</h1></div>

<div class="help">
<a class="btn btn-success" id="back" href="voting.php"><i class="icon-arrow-left icon-large"></i>&nbsp;Back</a>
</div>
<hr>


<div class="helping">
    <ol class="instruction-list">
        <li>Select your preferred candidate for each position (Governor, Vice-Governor, and Representative) by clicking the dropdown and choosing a name.</li>
        <li>Review your selections carefully.</li>
        <li>Click the <b>Submit Vote</b> button to cast your vote.</li>
        <li>If you are not ready, you can click <b>Vote later</b> to return and vote at another time.</li>
    </ol>
</div>

</div>

</body>
	
</html>
	<div class="modal hide fade" id="myModal">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	    <h3> </h3>
	  </div>
	  <div class="modal-body">
	    <p class="modal-text">Are You Sure you Want to Vote Later?</p>
	  </div>
	  <div class="modal-footer">
	    <a href="#" class="btn" data-dismiss="modal">No</a>
	    <a href="logout_back.php" class="btn btn-success">Yes</a>
		</div>
		</div>		
											
	