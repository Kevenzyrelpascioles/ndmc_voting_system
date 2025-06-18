<?php
include('session.php');
include('header.php');
include('dbcon.php');
include('timezone_config.php');
include('enhanced_logger.php'); // Include our enhanced logger

// Removed page access logging - we don't want to track page visits

// Handle filters
$date_filter = "";
$user_filter = "";
$action_filter = "";
$search_filter = "";

// Date range filter
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $date_from = mysqli_real_escape_string($conn, $_GET['date_from']);
    $date_filter .= " AND DATE(date) >= '$date_from'";
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $date_to = mysqli_real_escape_string($conn, $_GET['date_to']);
    $date_filter .= " AND DATE(date) <= '$date_to'";
}

// User filter
if (isset($_GET['user_filter']) && !empty($_GET['user_filter']) && $_GET['user_filter'] != 'all') {
    $user = mysqli_real_escape_string($conn, $_GET['user_filter']);
    $user_filter = " AND user = '$user'";
}

// Action filter
if (isset($_GET['action_filter']) && !empty($_GET['action_filter']) && $_GET['action_filter'] != 'all') {
    $action = mysqli_real_escape_string($conn, $_GET['action_filter']);
    $action_filter = " AND action LIKE '%$action%'";
}

// Search filter
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = mysqli_real_escape_string($conn, trim($_GET['search']));
    $search_filter = " AND (data LIKE '%$search_term%' OR action LIKE '%$search_term%' OR user LIKE '%$search_term%')";
    
    // Log the search activity
    $search_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM history WHERE 1=1 $date_filter $user_filter $action_filter $search_filter");
    $search_count = mysqli_fetch_array($search_count_query)['count'];
    logSearch($conn, $search_term, 'history', $search_count);
}

// Combine all filters
$where_clause = "WHERE 1=1 $date_filter $user_filter $action_filter $search_filter";

// Pagination
$records_per_page = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total records for pagination
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM history $where_clause");
$total_records = mysqli_fetch_array($total_query)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get unique users for filter dropdown
$users_query = mysqli_query($conn, "SELECT DISTINCT user FROM history ORDER BY user");

// Get unique actions for filter dropdown
$actions_query = mysqli_query($conn, "SELECT DISTINCT action FROM history ORDER BY action");
?>
<style>
/* Modern styles for history log */
.back-button {
    display: inline-block;
    position: relative;
    background-color: #4CAF50;
    color: white;
    padding: 10px 18px;
    font-size: 15px;
    font-weight: bold;
    border-radius: 6px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    text-decoration: none;
    border: 2px solid white;
    text-transform: uppercase;
    margin-left: 10px;
    margin-right: 0;
    z-index: 1000;
    vertical-align: middle;
    transition: all 0.3s ease;
}

.back-button:hover {
    background-color: #d32f2f;
    color: white;
    text-decoration: none;
    border-color: #b71c1c;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.back-button i {
    margin-right: 7px;
}

/* Ensure back button is visible on all screen sizes */
@media (max-width: 979px) {
    .back-button {
        display: inline-block !important;
        margin-top: 10px;
        margin-bottom: 10px;
    }
}

/* History Table Styles */
.history-container {
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    margin: 20px;
}

.history-title {
    color: #2C3E50;
    font-size: 24px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #4CAF50;
}

#log {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}

#log thead {
    background: #4CAF50;
    color: white;
}

#log th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
}

#log td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    transition: background 0.3s ease;
}

#log tbody tr:hover {
    background-color: #f5f9f5;
}

/* Action badges */
.action-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.action-add {
    background-color: #E8F5E9;
    color: #2E7D32;
}

.action-delete {
    background-color: #FFEBEE;
    color: #C62828;
}

.action-edit {
    background-color: #E3F2FD;
    color: #1565C0;
}

/* User badge */
.user-badge {
    background: #f1f1f1;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #666;
}

/* Date styling */
.date-time {
    color: #666;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.date-time i {
    font-size: 14px;
    color: #4CAF50;
}

/* Enhanced styles for history log */
.back-button {
    display: inline-block;
    position: relative;
    background-color: #4CAF50;
    color: white;
    padding: 10px 18px;
    font-size: 15px;
    font-weight: bold;
    border-radius: 6px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    text-decoration: none;
    border: 2px solid white;
    text-transform: uppercase;
    margin-left: 10px;
    margin-right: 0;
    z-index: 1000;
    vertical-align: middle;
    transition: all 0.3s ease;
}

.back-button:hover {
    background-color: #d32f2f;
    color: white;
    text-decoration: none;
    border-color: #b71c1c;
    transform: translateY(-2px);
}

.back-button i {
    margin-right: 7px;
}

/* History Container Styles */
.history-container {
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    margin: 20px;
}

.history-title {
    color: #2C3E50;
    font-size: 24px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #4CAF50;
}

/* Filters Section */
.filters-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}

.filters-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: end;
    margin-bottom: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #495057;
    font-size: 14px;
}

.filter-group input,
.filter-group select {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
    background: white;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.filter-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-filter {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    color: white;
    text-decoration: none;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background: #e0a800;
    color: #212529;
    text-decoration: none;
}

/* Search Section */
.search-container {
    background: linear-gradient(135deg, #2e8b57 0%, #228b22 100%);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.search-box {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 12px 15px;
    border: none;
    border-radius: 25px;
    font-size: 15px;
    outline: none;
}

.search-btn {
    padding: 12px 20px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: #45a049;
    transform: translateY(-1px);
}

/* Statistics Cards */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #4CAF50;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #2C3E50;
    margin-bottom: 5px;
}

.stat-label {
    color: #6c757d;
    font-size: 14px;
}

/* Table Styles */
#log {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

#log thead {
    background: #4CAF50;
    color: white;
}

#log th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
}

#log td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    transition: background 0.3s ease;
}

#log tbody tr:hover {
    background-color: #f5f9f5;
}

#log tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

/* Action badges */
.action-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    white-space: nowrap;
}

.action-add { background-color: #E8F5E9; color: #2E7D32; }
.action-delete { background-color: #FFEBEE; color: #C62828; }
.action-edit { background-color: #E3F2FD; color: #1565C0; }
.action-login { background-color: #F3E5F5; color: #7B1FA2; }
.action-logout { background-color: #FFF3E0; color: #F57C00; }
.action-system { background-color: #FFEBEE; color: #D32F2F; }
.action-search { background-color: #E0F2F1; color: #00695C; }
.action-export { background-color: #F1F8E9; color: #558B2F; }

/* User badge */
.user-badge {
    background: #f1f1f1;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #666;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Date styling */
.date-time {
    color: #666;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.date-time i {
    font-size: 14px;
    color: #4CAF50;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.pagination {
    display: flex;
    gap: 5px;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    text-decoration: none;
    color: #495057;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

.pagination .current {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

/* Responsive */
@media (max-width: 768px) {
    .filters-row {
        flex-direction: column;
    }
    
    .filter-group {
        min-width: 100%;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
    }
    
    .search-box {
        flex-direction: column;
    }
    
    .search-input {
        margin-bottom: 10px;
    }
}
</style>
</head>
<body>
<?php include('nav_top.php'); ?>

<div class="wrapper">
<div class="home_body">
<div class="navbar">
    <div class="navbar-inner">
    <div class="container">    
    <ul class="nav nav-pills">
      <li>....</li>
      <li><a href="home.php"><i class="icon-home icon-large"></i>Home</a></li>
      <li><a  href="candidate_list.php"><i class="icon-align-justify icon-large"></i>Candidates List</a></li>  
      <li><a  href="voter_list.php"><i class="icon-align-justify icon-large"></i>Voters List</a></li>  
      <li><a  href="manage_academics.php"><i class="icon-cog icon-large"></i>Academics</a></li>
      <li><a  href="canvassing_report.php"><i class="icon-book icon-large"></i>Canvassing Report</a></li>
      <li class="active"><a  href="history.php"><i class="icon-table icon-large"></i>History Log</a>
      <li><a data-toggle="modal" href="#about"><i class="icon-exclamation-sign icon-large"></i>About</a></li>
      <div class="modal hide fade" id="about">
        <div class="modal-header"> 
          <button type="button" class="close" data-dismiss="modal"></button>
          <h3> </h3>
        </div>
        <div class="modal-body">
          <?php include('about.php') ?>
          <div class="modal-footer_about">
            <a href="#" class="btn" data-dismiss="modal">Close</a>
          </div>
        </div>
      <li>....</li>
    </ul>
    <form class="navbar-form pull-right" style="display: flex; align-items: center; gap: 10px;">
        <?php 
        $result=mysqli_query($conn,"select * from users where User_id='$id_session'");
        $row=mysqli_fetch_array($result);
        
        // Handle case where user query fails or returns no results
        if (!$row) {
            $user_type = isset($_SESSION['User_Type']) ? $_SESSION['User_Type'] : 'Admin';
        } else {
            $user_type = $row['User_Type'];
        }
        ?>
        <font color="white">Welcome:<i class="icon-user-md"></i><?php echo $user_type; ?></font>
        <a class="btn btn-danger" id="logout" data-toggle="modal" href="#myModal"><i class="icon-off"></i>&nbsp;Logout</a>
        <!-- Back to Home button with modal -->
        <a class="back-button" data-toggle="modal" href="#backHomeModal" title="Back to Home">
            <i class="icon-arrow-left"></i> BACK TO HOME
        </a>
        <div class="modal hide fade" id="backHomeModal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"></button>
                <h3>Confirm Navigation</h3>
            </div>
            <div class="modal-body">
                <p><font color="gray">Are you sure you want to go back to Home?</font></p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn" data-dismiss="modal">No</a>
                <a href="home.php" class="btn btn-primary">Yes</a>
            </div>
        </div>
        <div class="modal hide fade" id="myModal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"></button>
                <h3> </h3>
            </div>
            <div class="modal-body">
                <p><font color="gray">Are You Sure you Want to LogOut?</font></p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn" data-dismiss="modal">No</a>
                <a href="logout.php" class="btn btn-primary">Yes</a>
            </div>
        </div>
    </form>
    </div>
    </div>
    </div>
    
    <div id="element" class="hero-body">
    <div class="history-container">
            <h2 class="history-title"><i class="icon-time"></i> Enhanced System Activity Log</h2>
            
            <!-- Add inline back button here -->
            <div style="margin-bottom: 20px;">
                <a href="home.php" class="btn btn-primary" style="background-color: #4CAF50; border-color: #4CAF50;">
                    <i class="icon-arrow-left"></i> Back to Home
                </a>
            </div>
            
            <!-- Statistics Cards -->
            <?php
            $stats_today = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) as count FROM history WHERE DATE(date) = CURDATE()"))['count'];
            $stats_week = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) as count FROM history WHERE date >= DATE_SUB(NOW(), INTERVAL 7 DAY)"))['count'];
            $stats_total = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) as count FROM history"))['count'];
            $stats_users = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(DISTINCT user) as count FROM history"))['count'];
            ?>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats_today; ?></div>
                    <div class="stat-label">Today's Activities</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats_week; ?></div>
                    <div class="stat-label">This Week</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats_total; ?></div>
                    <div class="stat-label">Total Records</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats_users; ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
            </div>

            <!-- Search Container -->
            <div class="search-container">
                <form method="GET" action="history.php">
                    <div class="search-box">
                        <input type="text" 
                               name="search" 
                               class="search-input" 
                               placeholder="Search activities, users, or data..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="search-btn">
                            <i class="icon-search"></i> Search
                        </button>
                        <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="history.php" class="btn-filter btn-secondary">
                                <i class="icon-remove"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Filters Container -->
            <div class="filters-container">
                <h4 style="margin-top: 0; color: #495057;"><i class="icon-filter"></i> Advanced Filters</h4>
                <form method="GET" action="history.php">
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                    <?php endif; ?>
                    
                    <div class="filters-row">
                        <div class="filter-group">
                            <label for="date_from">From Date</label>
                            <input type="date" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_to">To Date</label>
                            <input type="date" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="user_filter">User</label>
                            <select id="user_filter" name="user_filter">
                                <option value="all">All Users</option>
                                <?php while($user_row = mysqli_fetch_array($users_query)): ?>
                                    <option value="<?php echo htmlspecialchars($user_row['user']); ?>" 
                                            <?php echo (isset($_GET['user_filter']) && $_GET['user_filter'] == $user_row['user']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user_row['user']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="action_filter">Action Type</label>
                            <select id="action_filter" name="action_filter">
                                <option value="all">All Actions</option>
                                <option value="Login" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Login') ? 'selected' : ''; ?>>Authentication</option>
                                <option value="Add" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Add') ? 'selected' : ''; ?>>Creation</option>
                                <option value="Edit" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Edit') ? 'selected' : ''; ?>>Modification</option>
                                <option value="Delete" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Delete') ? 'selected' : ''; ?>>Deletion</option>
                                <option value="Reset" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Reset') ? 'selected' : ''; ?>>System Operations</option>
                                <option value="Search" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Search') ? 'selected' : ''; ?>>Search Activities</option>
                                <option value="Export" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Export') ? 'selected' : ''; ?>>Export/Download</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter btn-primary">
                            <i class="icon-filter"></i> Apply Filters
                        </button>
                        <a href="history.php" class="btn-filter btn-secondary">
                            <i class="icon-refresh"></i> Reset All
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results Info -->
            <div style="margin-bottom: 15px; padding: 10px; background: #e9ecef; border-radius: 5px; color: #495057;">
                <strong>Showing <?php echo number_format($total_records); ?> records</strong>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    | Search: "<strong><?php echo htmlspecialchars($_GET['search']); ?></strong>"
                <?php endif; ?>
                <?php if ($total_records > $records_per_page): ?>
                    | Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                <?php endif; ?>
            </div>

            <!-- History Table -->
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="log">
            <thead>
                <tr>
                        <th>Date & Time</th>
                <th>Action</th>
                        <th>Details</th>
                <th>User</th>
                        <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $history_query = mysqli_query($conn, "SELECT *, DATE_FORMAT(date, '%Y-%m-%d %l:%i:%s %p') as formatted_date FROM history $where_clause ORDER BY date DESC LIMIT $offset, $records_per_page");
                
                if (mysqli_num_rows($history_query) > 0) {
                    while($history_rows = mysqli_fetch_array($history_query)) { 
                        $id = $history_rows['history_id'];
                        
                        // Determine action class for styling
                        $action_class = 'action-general';
                        $action_lower = strtolower($history_rows['action']);
                        if (strpos($action_lower, 'add') !== false) $action_class = 'action-add';
                        elseif (strpos($action_lower, 'delete') !== false) $action_class = 'action-delete';
                        elseif (strpos($action_lower, 'edit') !== false || strpos($action_lower, 'update') !== false) $action_class = 'action-edit';
                        elseif (strpos($action_lower, 'login') !== false) $action_class = 'action-login';
                        elseif (strpos($action_lower, 'logout') !== false) $action_class = 'action-logout';
                        elseif (strpos($action_lower, 'reset') !== false || strpos($action_lower, 'system') !== false) $action_class = 'action-system';
                        elseif (strpos($action_lower, 'search') !== false) $action_class = 'action-search';
                        elseif (strpos($action_lower, 'export') !== false) $action_class = 'action-export';
                ?>
<tr class="del<?php echo $id ?>">
	<td>
		<div class="date-time">
			<i class="icon-calendar"></i>
			<?php echo $history_rows['formatted_date']; ?>
		</div>
	</td>
	<td>
                        <span class="action-badge <?php echo $action_class; ?>">
                            <?php echo htmlspecialchars($history_rows['action']); ?>
		</span>
	</td>
                    <td>
                        <div style="max-width: 300px; word-wrap: break-word;">
                            <?php echo htmlspecialchars($history_rows['data']); ?>
                        </div>
                    </td>
	<td>
		<span class="user-badge">
			<i class="icon-user"></i>
                            <?php echo htmlspecialchars($history_rows['user']); ?>
		</span>
	</td>
                    <td>
                        <small style="color: #6c757d;">
                            <?php 
                            // Try to get IP from additional tables if available
                            $ip_display = 'Not tracked';
                            if (isset($history_rows['ip_address']) && !empty($history_rows['ip_address'])) {
                                $ip_display = $history_rows['ip_address'];
                            } else {
                                // Check if system_activity table exists before querying
                                $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'system_activity'");
                                if ($table_check && mysqli_num_rows($table_check) > 0) {
                                    // Try to get from system_activity table if it exists
                                    $ip_query = mysqli_query($conn, "SELECT ip_address FROM system_activity WHERE user_name = '{$history_rows['user']}' AND timestamp >= '{$history_rows['date']}' ORDER BY timestamp ASC LIMIT 1");
                                    if ($ip_query && mysqli_num_rows($ip_query) > 0) {
                                        $ip_row = mysqli_fetch_array($ip_query);
                                        $ip_display = $ip_row['ip_address'];
                                    }
                                }
                            }
                            echo $ip_display;
                            ?>
                        </small>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="icon-exclamation-sign icon-large" style="color: #ffc107; margin-right: 10px;"></i>
                        <strong>No Activity Records Found</strong>
                        <br><br>
                        <?php if (isset($_GET['search']) || isset($_GET['date_from']) || isset($_GET['user_filter'])): ?>
                            No records match your current filters. Try adjusting your search criteria.
                        <?php else: ?>
                            No activity has been recorded yet.
                        <?php endif; ?>
                    </td>
	</tr>
<?php } ?>
			</tbody>
		</table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <div>
                    Showing <?php echo (($page - 1) * $records_per_page) + 1; ?> to 
                    <?php echo min($page * $records_per_page, $total_records); ?> of 
                    <?php echo number_format($total_records); ?> records
                </div>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">First</a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    </div>
    </div>

    <?php include('footer.php')?>	
</div>

</body>
</html>
