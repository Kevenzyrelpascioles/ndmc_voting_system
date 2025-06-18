<?php
include('dbcon.php');
include('voting_settings_helper.php');

// Set timezone to Asia/Manila for consistency
date_default_timezone_set('Asia/Manila');

// Check voting settings
$settings = shouldHideVotes($conn);
$hide_votes = $settings['hide_votes'];

// Only allow export if voting is complete or admin has permission
$allow_export = !$hide_votes;

if($allow_export) {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="NDMC_Voters_List.xls"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    // Get current date and time
    $date = date('F d, Y');
    $time = date('h:i A');
?>

<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid black; padding: 8px; }
    .header { text-align: center; font-size: 16px; font-weight: bold; }
    .subheader { text-align: center; font-size: 14px; }
    .info { text-align: right; font-size: 12px; }
</style>

<table>
    <!-- Header Section -->
    <tr>
        <td colspan="7" class="header">NDMC VOTING SYSTEM</td>
    </tr>
    <tr>
        <td colspan="7" class="subheader">Voters Master List</td>
    </tr>
    <tr>
        <td colspan="7" class="info">Date: <?php echo $date; ?><br>Time: <?php echo $time; ?></td>
    </tr>
    <tr><td colspan="7">&nbsp;</td></tr>

    <!-- Column Headers -->
    <tr style="background-color: #f0f0f0;">
        <th style="font-weight: bold; text-align: center;">First Name</th>
        <th style="font-weight: bold; text-align: center;">Last Name</th>
        <th style="font-weight: bold; text-align: center;">Middle Name</th>
        <th style="font-weight: bold; text-align: center;">Username</th>
        <th style="font-weight: bold; text-align: center;">Year</th>
        <th style="font-weight: bold; text-align: center;">Department</th>
        <th style="font-weight: bold; text-align: center;">Status</th>
    </tr>

    <?php 
    // Get total counts
    $total_query = mysqli_query($conn, "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN Status = 'Voted' THEN 1 ELSE 0 END) as voted,
        SUM(CASE WHEN Status = 'Unvoted' THEN 1 ELSE 0 END) as unvoted
        FROM voters");
    $totals = mysqli_fetch_array($total_query);

    $qryreport = mysqli_query($conn,"SELECT v.*, d.department_name 
                                     FROM voters v
                                     LEFT JOIN departments d ON v.department_id = d.department_id
                                     ORDER BY v.LastName, v.FirstName") 
                                     or die(mysqli_error($conn));

    $row_number = 1;
    while ($reportdisp = mysqli_fetch_array($qryreport)) {
        $department = $reportdisp['department_name'] ? $reportdisp['department_name'] : 'Not Assigned';
        $status_style = $reportdisp['Status'] == 'Voted' ? 'color: green;' : 'color: orange;';
    ?>
    <tr>
        <td style="text-align: left;"><?php echo htmlspecialchars($reportdisp['FirstName']) ?></td>
        <td style="text-align: left;"><?php echo htmlspecialchars($reportdisp['LastName']) ?></td>
        <td style="text-align: left;"><?php echo htmlspecialchars($reportdisp['MiddleName']) ?></td>
        <td style="text-align: left;"><?php echo htmlspecialchars($reportdisp['Username']) ?></td>
        <td style="text-align: center;"><?php echo htmlspecialchars($reportdisp['Year']) ?></td>
        <td style="text-align: center;"><?php echo htmlspecialchars($department) ?></td>
        <td style="text-align: center; <?php echo $status_style; ?>"><?php echo htmlspecialchars($reportdisp['Status']) ?></td>
    </tr>
    <?php } ?>

    <!-- Summary Section -->
    <tr><td colspan="7">&nbsp;</td></tr>
    <tr>
        <td colspan="7" style="text-align: center; font-weight: bold; background-color: #f0f0f0;">SUMMARY</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;">Total Voters:</td>
        <td colspan="4" style="text-align: left;"><?php echo $totals['total']; ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;">Total Voted:</td>
        <td colspan="4" style="text-align: left; color: green;"><?php echo $totals['voted']; ?></td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;">Total Unvoted:</td>
        <td colspan="4" style="text-align: left; color: orange;"><?php echo $totals['unvoted']; ?></td>
    </tr>

    <!-- Footer -->
    <tr><td colspan="7">&nbsp;</td></tr>
    <tr>
        <td colspan="7" style="text-align: center; font-size: 12px;">*** End of Report ***</td>
    </tr>
</table>
<?php 
} else {
    // Redirect back to voter list with message
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
    header("Location: voter_list.php");
    exit();
}
?>