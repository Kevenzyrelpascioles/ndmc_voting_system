<?php
include('session.php');
include('dbcon.php');

// Initialize response structure
$response = [
    'status' => ['labels' => [], 'values' => []],
    'courses' => ['labels' => [], 'values' => []]
];

// Department filter from session
$dept_filter_sql = "";
$dept_id = 'all';

if(isset($_SESSION['current_department']) && $_SESSION['current_department'] != 'all') {
  $dept_id = $_SESSION['current_department'];
  $dept_filter_sql = " WHERE department_id = '" . mysqli_real_escape_string($conn, $dept_id) . "'";
}

// 1. Get Voted vs. Unvoted stats
$status_query = mysqli_query($conn, "SELECT Status, COUNT(*) as count FROM voters" . $dept_filter_sql . " GROUP BY Status");

$status_data = ['Voted' => 0, 'Unvoted' => 0];
if ($status_query) {
    while ($row = mysqli_fetch_assoc($status_query)) {
        if (isset($status_data[$row['Status']])) {
            $status_data[$row['Status']] = (int)$row['count'];
        }
    }
}
$response['status']['labels'] = array_keys($status_data);
$response['status']['values'] = array_values($status_data);


// 2. Get voters per course
$course_query_sql = "SELECT c.course_name, COUNT(v.VoterID) as count 
                     FROM voters v 
                     LEFT JOIN courses c ON v.course_id = c.course_id";
if ($dept_id !== 'all') {
    $course_query_sql .= " WHERE v.department_id = '" . mysqli_real_escape_string($conn, $dept_id) . "'";
}
$course_query_sql .= " GROUP BY v.course_id ORDER BY count DESC";
$course_query = mysqli_query($conn, $course_query_sql);

if ($course_query) {
    while ($row = mysqli_fetch_assoc($course_query)) {
        $response['courses']['labels'][] = $row['course_name'] ? $row['course_name'] : 'Not Assigned';
        $response['courses']['values'][] = (int)$row['count'];
    }
}


// 3. Get voters per department (only if viewing all)
if ($dept_id === 'all') {
    $response['departments'] = ['labels' => [], 'values' => []];
    $dept_query = mysqli_query($conn, "SELECT d.department_name, COUNT(v.VoterID) as count
                                       FROM voters v
                                       LEFT JOIN departments d ON v.department_id = d.department_id
                                       GROUP BY v.department_id
                                       ORDER BY count DESC");
    if($dept_query){
        while ($row = mysqli_fetch_assoc($dept_query)) {
            $response['departments']['labels'][] = $row['department_name'] ? $row['department_name'] : 'Not Assigned';
            $response['departments']['values'][] = (int)$row['count'];
        }
    }
}


// Send response
header('Content-Type: application/json');
echo json_encode($response);

?> 