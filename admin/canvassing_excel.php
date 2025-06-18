<?php
include("dbcon.php");
include("voting_settings_helper.php");

// Set timezone to Asia/Manila for consistency
date_default_timezone_set('Asia/Manila');

// Check voting settings
$settings = shouldHideVotes($conn);
$hide_votes = $settings['hide_votes'];

// Only allow export if voting is complete or admin has permission
$allow_export = !$hide_votes;

if($allow_export) {
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Canvassing_Report.xls" );
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="log" class="jtable">
    <thead>
        <tr>
        <th>Position</th>
        <th>LastName</th>
        <th>FirstName</th>
        <th>Year</th>
        <th>Department</th>
        <th>No. of Votes</th>
        </tr>
    </thead>
    <tbody>

<?php 
$candidate_query=mysqli_query($conn,"SELECT c.*, d.department_name FROM candidate c
                             LEFT JOIN departments d ON c.department_id = d.department_id
                             ORDER BY FIELD(c.Position,
                             'Governor',
                             'Vice-Governor',
                            '1st Year Representative',
                             '2nd Year Representative',
                             '3rd Year Representative',
                             '4th Year Representative'
                             ), c.LastName asc, c.FirstName asc
                             ");
while($candidate_rows=mysqli_fetch_array($candidate_query)){ 
    $id=$candidate_rows['CandidateID'];
    $fl=$candidate_rows['FirstName'];
    $department = $candidate_rows['department_name'] ? $candidate_rows['department_name'] : 'Not Assigned';
?>

<tr>
    <td><?php echo $candidate_rows['Position']; ?></td>
    <td><?php echo $candidate_rows['LastName']; ?></td>
    <td><?php echo $candidate_rows['FirstName']; ?></td>
    <td><?php echo $candidate_rows['Year']; ?></td>
    <td><?php echo $department; ?></td>
    <td><?php 
        $votes_query=mysqli_query($conn,"select * from votes where CandidateID='$id'");
        $vote_count=mysqli_num_rows($votes_query);
        echo $vote_count;
    ?></td>
</tr>
<?php } ?>

    </tbody>
</table>
<?php 
} else {
    // Redirect back to canvassing report with message
    session_start();
    
    // Format the end date properly
    try {
        // Create DateTime object with the correct timezone
        $end_time = new DateTime($settings['voting_end'], new DateTimeZone('Asia/Manila'));
        $formatted_date = $end_time->format('F j, Y, g:i a');
    } catch (Exception $e) {
        // Fallback if there's an issue with the date
        $formatted_date = "the scheduled end time";
    }
    
    $_SESSION['export_error'] = "Export is disabled until voting ends at " . $formatted_date;
    header("Location: canvassing_report.php");
    exit();
}
?>