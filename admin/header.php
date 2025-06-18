<!DOCTYPE html>
<html lang="en">		
<head>
<title>NDMC Voting System - Admin</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<script src="js/jquery-1.7.2.min.js" type="text/javascript"></script>
		
<script type="text/javascript" src="js/bootstrap.js"></script>
<script type="text/javascript" src="js/bootstrap-transition.js"></script>
<script type="text/javascript" src="js/bootstrap-collapse.js"></script>
<script type="text/javascript" src="js/bootstrap-tab.js"></script>

<!-- Hover popup scripts -->
<script src="js/main.js" type="text/javascript"></script>
<script src="js/mouseover_popup.js" type="text/javascript"></script>

<!-- Tooltip div for hovering -->
<div style="display: none; position: absolute; color:white; z-index:100; width:auto; height:auto;" id="preview_div"></div>

<!-- Notify CSS and JS -->
<link href="css/notify/jquery_notification.css" type="text/css" rel="stylesheet" media="screen, projection"/>
<script type="text/javascript" src="js/notify/jquery_notification_v.1.js"></script>

<!-- DataTables CSS and JS -->
<style type="text/css" title="currentStyle">
    @import "css/datatable/demo_page.css";
    @import "css/datatable/demo_table_jui.css";
    @import "css/datatable/jquery-ui-1.8.4.custom.css";
</style>

<script type="text/javascript" language="javascript" src="js/dataTables/jquery.dataTables.js"></script>

<!-- Bootstrap JS components -->
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize Bootstrap components
        $('.dropdown-toggle').dropdown();
        $('.collapse').collapse();
        $('.carousel').carousel({
            interval: 5000
        });
        
        // Make tables responsive
        $('table.display').wrap('<div class="table-responsive"></div>');
        
        // Fix navbar on scroll
        $(window).scroll(function() {
            if ($(window).width() > 767) {
                if ($(this).scrollTop() > 50) {
                    $('.navbar-fixed-top').addClass('scrolled');
                } else {
                    $('.navbar-fixed-top').removeClass('scrolled');
                }
            }
        });

        // Fix position tabs layout
        fixPositionTabs();
        
        // Handle resize event
        $(window).resize(function() {
            fixPositionTabs();
        });
        
        function fixPositionTabs() {
            // Add classes to the position tabs container
            $('.pagination:first').addClass('position-tabs year-tabs-container');
            
            // Add class to the "Add Candidates" button container
            $('.pagination:eq(1)').addClass('add-candidates-container');
            
            // Fix responsive layout for year tabs
            $('.position-tabs a').each(function() {
                $(this).css('margin', '3px').css('display', 'inline-block');
            });
            
            // Make 4th Year Representative tab not overlap with Add Candidates button
            $('a[href="4th_year.php"]').css('clear', 'none');
            
            // Fix Excel button
            $('button[name="save"]').parent('form').addClass('excel-download-container');
        }
    });
</script>

<!-- QTip JS and CSS -->
<script type="text/javascript" src="js/qtip/jquery.qtip.min.js"></script>
<link href="js/qtip/jquery.qtip.min.css" rel="stylesheet" type="text/css">

<!-- Date and Time functions -->
<script type="text/javascript" language="JavaScript">
function getCalendarDate() {
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
    var dateString = monthname + ' ' + monthday + ', ' + year;
    return dateString;
}
</script>	

<script language="javascript" type="text/javascript">
var timerID = null;
var timerRunning = false;
function stopclock() {
    if(timerRunning)
        clearTimeout(timerID);
    timerRunning = false;
}
function showtime() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds()
    var timeValue = "" + ((hours >12) ? hours -12 :hours)
    if (timeValue == "0") timeValue = 12;
    timeValue += ((minutes < 10) ? ":0" : ":") + minutes
    timeValue += ((seconds < 10) ? ":0" : ":") + seconds
    timeValue += (hours >= 12) ? " P.M." : " A.M."
    
    // Only update the clock if the element exists
    if (document.clock && document.clock.face) {
        document.clock.face.value = timeValue;
    }

    timerID = setTimeout("showtime()",1000);
    timerRunning = true;
}
function startclock() {
    stopclock();
    showtime();
}
window.onload=startclock;
</script>

<?php include('hover.php'); ?>
    
<!-- Favicon -->
<link rel="icon" href="images/chmsc.png" type="image/png" />

<!-- Space Gallery CSS -->
<link rel="stylesheet" media="screen" type="text/css" href="css/spacegallery.css" />
<link rel="stylesheet" media="screen" type="text/css" href="css/custom.css" />

<!-- Gallery and Layout JS -->
<script type="text/javascript" src="js/eye.js"></script>
<script type="text/javascript" src="js/spacegallery.js"></script>
<script type="text/javascript" src="js/layout.js"></script>
	
<!-- Bootstrap CSS -->
<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="css/bootstrap-responsive.css">
<link rel="stylesheet" href="css/font-awesome.css">

<!-- Main CSS -->
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/Home.css">

<!-- Responsive Admin CSS -->
<link rel="stylesheet" type="text/css" href="css/responsive-admin.css">
<!-- Dashboard CSS for fixing layout issues -->
<link rel="stylesheet" type="text/css" href="css/dashboard.css">

<!-- Green Theme CSS (Add this after all other CSS files for override) -->
<link rel="stylesheet" type="text/css" href="css/green-theme.css">

<!-- Custom table styling -->
<style>
    /* Table text colors */
    .demo_jui table, .jtable {
        color: black !important;
        background-color: #f9f9f9 !important;
    }
    
    /* Table header styling */
    .demo_jui table th, .jtable th, .dataTables_wrapper th {
        color: white !important;
        background-color: #0066cc !important;
        font-weight: bold !important;
        padding: 8px !important;
        text-shadow: none !important;
        border: 1px solid #004080 !important;
    }
    
    /* Table cell styling */
    .demo_jui table td, .jtable td {
        color: black !important;
        padding: 6px !important;
        border: 1px solid #ddd !important;
        background-color: white !important;
    }
    
    /* Alternating row colors */
    .demo_jui table tr:nth-child(even) td {
        background-color: #f2f2f2 !important;
    }
    
    /* Row hover effect */
    .demo_jui table tr:hover td {
        background-color: #e6f0ff !important;
    }
    
    /* Position header styling */
    .position-header td {
        background-color: #4a86e8 !important;
        font-weight: bold !important;
        font-size: 14px !important;
        text-align: left !important;
        padding: 10px !important;
        border-top: 1px solid #3a76d8 !important;
        border-bottom: 1px solid #3a76d8 !important;
        color: white !important;
        text-shadow: none !important;
    }
    
    /* Button styling */
    .btn-Success, .btn-success {
        background-color: #4CAF50 !important;
        background-image: none !important;
        color: white !important;
        border: 1px solid #3d8b40 !important;
        text-shadow: none !important;
    }
    
    .btn-info {
        background-color: #2196F3 !important;
        background-image: none !important;
        color: white !important;
        border: 1px solid #0b7dda !important;
        text-shadow: none !important;
    }
    
    .btn-danger, .btn-danger1 {
        background-color: #f44336 !important;
        background-image: none !important;
        color: white !important;
        border: 1px solid #d32f2f !important;
        text-shadow: none !important;
    }
    
    /* Modal styling */
    .modal-body {
        color: #333 !important;
    }
    
    /* Navigation pills */
    .nav-pills>li>a {
        color: white !important;
    }
    
    .nav-pills>li.active>a {
        background-color: #0055aa !important;
    }
    
    /* Fix for DataTables styling */
    .dataTables_info, .dataTables_length, .dataTables_filter {
        color: white !important;
    }
    
    .dataTables_paginate .ui-button {
        background-color: #f9f9f9 !important;
        color: #333 !important;
        border: 1px solid #ddd !important;
    }
    
    /* Navbar scrolled effect */
    .navbar-fixed-top.scrolled {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
    }
    
    /* Mobile-specific adjustments */
    @media (max-width: 767px) {
        .navbar-fixed-top .brand {
            display: flex;
            align-items: center;
        }
        
        .navbar-fixed-top .brand h2 {
            font-size: 18px;
            margin: 0;
        }
        
        .chmsc_nav {
            font-size: 14px;
        }
        
        .hero-unit-clock, .top_date {
            display: none;
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
   

