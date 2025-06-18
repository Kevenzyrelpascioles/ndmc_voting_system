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
		<img src="admin/images/NDMC logo.jpg" class="circle-logo">
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
<div class="hero-body-voting">
<div class="vote_wise"><font color="white" size="6">&nbsp;&nbsp;&nbsp;&nbsp;"How to Vote"</font></div>

<div class="help">
<a class="btn btn-success" id="back"  href="voting3.php"><i class="icon-arrow-left icon-large"></i>&nbsp;Back</a>
</div>
<hr>


<div class="helping">
    <ol style="color: #fff; font-size: 20px; padding-left: 30px;">
        <li>Select your preferred candidate for each position (Governor, Vice-Governor, and Representative) by clicking the dropdown and choosing a name.</li>
        <li>Review your selections carefully.</li>
        <li>Click the <b>Submit Vote</b> button to cast your vote.</li>
        <li>If you are not ready, you can click <b>Vote later</b> to return and vote at another time.</li>
    </ol>
</div>
</br>


</div>

    </body>
	
</html>
	<div class="modal hide fade" id="myModal">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">�</button>
	    <h3> </h3>
	  </div>
	  <div class="modal-body">
	    <p><font color="gray">Are You Sure you Want to Vote Later?</font></p>
	  </div>
	  <div class="modal-footer">
	    <a href="#" class="btn" data-dismiss="modal">No</a>
	    <a href="logout_back.php" class="btn btn-success">Yes</a>
		</div>
		</div>		
											
	