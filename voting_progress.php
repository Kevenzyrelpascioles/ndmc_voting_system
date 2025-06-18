<?php
session_start();
include('dbcon.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Get department filter if available
$dept_filter = "";
if (isset($_SESSION['department']) && $_SESSION['department'] != 'all') {
    $dept_id = $_SESSION['department'];
    $dept_filter = " WHERE c.department_id = '$dept_id'";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voting Progress and Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="admin/css/style.css" />
    <style>
        :root {
            --primary-green: #2E7D32;
            --light-green: #4CAF50;
            --pale-green: #E8F5E9;
            --hover-green: #1B5E20;
        }

        body {
            background: linear-gradient(135deg, var(--pale-green), #ffffff);
            min-height: 100vh;
        }

        .navbar {
            background: var(--primary-green) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .results-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-top: 90px;
            border: 1px solid rgba(76, 175, 80, 0.1);
        }

        .page-title {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
        }

        .page-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 3px;
            background: var(--light-green);
            border-radius: 2px;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
            border: none;
            margin-top: 1.5rem;
        }

        .table thead {
            background-color: var(--primary-green);
            color: white;
        }

        .table th {
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .table tbody tr:hover {
            background-color: var(--pale-green);
            transition: background-color 0.3s ease;
        }

        .vote-count {
            font-weight: bold;
            color: var(--primary-green);
            background: var(--pale-green);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
        }

        .department-badge {
            background: var(--light-green) !important;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: var(--primary-green);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
            font-size: 1.1rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        .btn-close {
            color: white;
            opacity: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .results-container {
                padding: 1rem;
                margin-top: 80px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .table th, .table td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="admin/images/NDMC logo.jpg" class="rounded-circle img-fluid me-2" alt="NDMC Logo" style="height:40px;">
                <span>Notre Dame of Midsayap College</span>
            </a>
            <div class="ms-auto">
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
            </div>
        </div>
    </nav>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to logout from the voting system?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <a href="logout.php" class="btn btn-danger rounded-pill">Yes, Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container results-container">
        <div class="text-center">
            <h1 class="page-title display-5">Voting Progress and Results</h1>
            <?php
            // Display department name if filtering
            if (!empty($dept_filter)) {
                $dept_query = mysqli_query($conn, "SELECT department_name FROM departments WHERE department_id = '$dept_id'");
                if ($dept_row = mysqli_fetch_array($dept_query)) {
                    echo '<div class="department-badge">
                            <i class="bi bi-building me-2"></i>
                            Showing results for: ' . $dept_row['department_name'] . '
                          </div>';
                }
            }
            ?>
        </div>
        <div class="table-responsive">
            <?php
            $query = "SELECT c.CandidateID, c.FirstName, c.LastName, COUNT(v.CandidateID) as VoteCount 
                     FROM candidate c 
                     LEFT JOIN votes v ON c.CandidateID = v.CandidateID" . 
                     $dept_filter . " 
                     GROUP BY c.CandidateID 
                     ORDER BY VoteCount DESC";
            $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
            ?>
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Candidate Name</th>
                        <th>Total Votes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['CandidateID']; ?></td>
                            <td><?php echo $row['FirstName'] . ' ' . $row['LastName']; ?></td>
                            <td><span class="vote-count"><?php echo $row['VoteCount']; ?></span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include('footer1.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 