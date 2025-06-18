	<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
	<div class="container">
	     
		<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		
		<div class="brand-container d-flex align-items-center">
			<a class="brand">
				<img src="images/NDMC logo.jpg" class="circle-logo" alt="NDMC Logo">
			</a>
			<a class="brand">
				<h2>NDMC Voting System</h2>
				<div class="chmsc_nav">
					<span class="school-name d-none d-md-inline">Notre Dame of Midsayap College</span>
					<span class="school-name d-inline d-md-none">NDMC</span>
				</div>
			</a>
		</div>

		<?php 
		// Display current department filter if available
		if (isset($_SESSION['current_department'])) {
			$dept_id = $_SESSION['current_department'];
			if ($dept_id != 'all') {
				$dept_query = mysqli_query($conn, "SELECT department_name FROM departments WHERE department_id = '$dept_id'");
				if ($dept_row = mysqli_fetch_array($dept_query)) {
					echo '<span class="dept-filter d-none d-md-inline">
						<span class="label label-info">' . $dept_row['department_name'] . '</span>
					</span>';
				}
			}
		}
		?>

		<?php include('head.php'); ?>
 
 
	</div>
	</div>
	</div>

<style>
/* Mobile optimizations for top nav */
@media (max-width: 767px) {
	.navbar-fixed-top .brand h2 {
		font-size: 18px !important;
		margin: 0 !important;
		line-height: 1.2 !important;
	}
	
	.navbar-fixed-top .brand {
		display: flex !important;
		align-items: center !important;
		max-width: 80% !important;
	}
	
	.circle-logo {
		max-width: 40px !important;
		height: auto !important;
		margin-right: 10px !important;
	}
	
	.chmsc_nav {
		font-size: 14px !important;
	}
	
	.navbar-inner .container {
		display: flex !important;
		align-items: center !important;
		justify-content: space-between !important;
	}
	
	.brand-container {
		display: flex !important;
		align-items: center !important;
	}
	
	/* Adjust date and time display */
	.top_date, .hero-unit-clock {
		display: none !important;
	}
}
</style>
	