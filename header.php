<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<html lang="en">		
<head>
<title>NDMC Voting System</title>
<!-- Responsive meta tags -->
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<!-- Base scripts -->
<script src="admin/js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="admin/js/bootstrap.js"></script>
<script type="text/javascript" src="admin/js/bootstrap-transition.js"></script>
<script type="text/javascript" src="admin/js/bootstrap-collapse.js"></script>

<!-- Responsive CSS and JavaScripts -->
<link rel="stylesheet" type="text/css" href="css/responsive.css">
<script type="text/javascript" src="helper.js"></script>

<!-- Hover popup scripts -->
<script src="admin/js/main.js" type="text/javascript"></script>
<script src="admin/js/mouseover_popup.js" type="text/javascript"></script>

<!-- Tooltip div for hovering -->
<div style="display: none; position: absolute; z-index:100; color:white; width:auto; height:auto;" id="preview_div"></div>

<!-- QTip scripts -->
<script type="text/javascript" src="admin/js/qtip/jquery.qtip.min.js"></script>
<link href="admin/js/qtip/jquery.qtip.min.css" rel="stylesheet" type="text/css">

<script type="text/javascript" language="JavaScript">
<!-- Copyright 2002 Bontrager Connection, LLC

function getCalendarDate()
{
   var months = new Array(13);
   months[0]  = "January";
   months[1]  = "February";
   months[2]  = "March";
   months[3]  = "April";
   months[4]  = "May";
   months[5]  = "June";
   months[6]  = "July";
   months[7]  = "August";
   months[8]  = "September";
   months[9]  = "October";
   months[10] = "November";
   months[11] = "December";
   var now         = new Date();
   var monthnumber = now.getMonth();
   var monthname   = months[monthnumber];
   var monthday    = now.getDate();
   var year        = now.getYear();
   if(year < 2000) { year = year + 1900; }
   var dateString = monthname +
                    ' ' +
                    monthday +
                    ', ' +
                    year;
   return dateString;
} // function getCalendarDate()
//-->
</script>	
<script language="javascript" type="text/javascript">
/* Visit http://www.yaldex.com/ for full source code
and get more free JavaScript, CSS and DHTML scripts! */
<!-- Begin
var timerID = null;
var timerRunning = false;
function stopclock (){
if(timerRunning)
clearTimeout(timerID);
timerRunning = false;
}
function showtime () {
var now = new Date();
var hours = now.getHours();
var minutes = now.getMinutes();
var seconds = now.getSeconds()
var timeValue = "" + ((hours >12) ? hours -12 :hours)
if (timeValue == "0") timeValue = 12;
timeValue += ((minutes < 10) ? ":0" : ":") + minutes
timeValue += ((seconds < 10) ? ":0" : ":") + seconds
timeValue += (hours >= 12) ? " P.M." : " A.M."
document.clock.face.value = timeValue;
timerID = setTimeout("showtime()",1000);
timerRunning = true;
}
function startclock() {
stopclock();
showtime();
}
window.onload=startclock;
// End -->
</SCRIPT>		

<?php include('admin/hover.php'); ?>
    
<!-- Favicon -->
<link rel="icon" href="admin/images/chmsc.png" type="image/png" />

<!-- Additional styles and scripts -->
<script type="text/javascript" src="admin/js/eye.js"></script>
<script type="text/javascript" src="admin/js/spacegallery.js"></script>
<script type="text/javascript" src="admin/js/layout.js"></script>
	
<!-- Base stylesheets -->
<link rel="stylesheet" type="text/css" href="admin/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="admin/css/bootstrap-responsive.css">
<link rel="stylesheet" href="admin/css/font-awesome.css">
<link rel="stylesheet" type="text/css" href="admin/css/Home.css">

<!-- Mobile-specific adjustments -->
<style type="text/css">
    @media (max-width: 767px) {
        .navbar-fixed-top .brand {
            display: flex;
            align-items: center;
        }
        
        .navbar-fixed-top .brand h2 {
            font-size: 18px;
            margin: 0;
        }
        
        .chmsc_nav font {
            font-size: 14px;
        }
        
        .circle-logo {
            max-width: 40px;
            height: auto;
        }
        
        input[type="text"],
        input[type="password"],
        select,
        .form-control {
            width: 100%;
            box-sizing: border-box;
        }
        
        .modal {
            width: 90%;
            margin: 0 auto;
            left: 0;
            right: 0;
        }
    }
</style>

<?php
function _redirect($url=''){
	if(!empty($url)){
		echo "<script> location.href = '".$url."' </script>";
	}
}
?>

<!-- Add Modal CSS -->
<style>
    .modal-header {
        background: #f8f9fa;
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
    }
    .modal-body {
        padding: 20px;
        font-size: 16px;
    }
    .modal-footer {
        background: #f8f9fa;
        padding: 15px;
        border-top: 1px solid #dee2e6;
    }
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }
    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
</style>

<!-- Add Modal HTML -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to logout?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal JavaScript -->
<script>
    $(document).ready(function() {
        // Add click handler to logout links
        $('a[href="logout.php"]').click(function(e) {
            e.preventDefault();
            $('#logoutModal').modal('show');
        });
    });
</script>

