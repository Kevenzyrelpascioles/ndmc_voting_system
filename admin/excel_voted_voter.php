<?php
  require_once('connect.php');
  include('voting_settings_helper.php');
  
  // Set timezone to Asia/Manila for consistency
  date_default_timezone_set('Asia/Manila');
  
  // Check voting settings
  $settings = shouldHideVotes($conn);
  $hide_votes = $settings['hide_votes'];
  
  // Only allow export if voting is complete or admin has permission
  $allow_export = !$hide_votes;
  
  if($allow_export) {
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Voters_voted_List.xls" );
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
?>

<table>
  <thead>
    <tr>
      <th>FirstName</th>
      <th>LastName</th>
      <th>MiddleName</th>
      <th>UserName</th>
      <th>Year</th>
      <th>Department</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $qryreport = mysqli_query($conn,"SELECT v.*, d.department_name 
                                     FROM voters v
                                     LEFT JOIN departments d ON v.department_id = d.department_id
                                     WHERE v.Status ='Voted'") 
                                     or die(mysqli_error());
	
    $sqlrows=mysqli_num_rows($qryreport);
    WHILE ($reportdisp=mysqli_fetch_array($qryreport)) {
      $department = $reportdisp['department_name'] ? $reportdisp['department_name'] : 'Not Assigned';
    ?>
    <tr>
      <td><?php echo $reportdisp['FirstName'] ?></td>
      <td><?php echo $reportdisp['LastName'] ?></td>
      <td><?php echo $reportdisp['MiddleName'] ?></td>
      <td><?php echo $reportdisp['Username'] ?></td>
      <td><?php echo $reportdisp['Year'] ?></td>
      <td><?php echo $department ?></td>
      <td><?php echo $reportdisp['Status'] ?></td>
    <?php } ?>
  </tbody>
</table>
<?php 
} else {
    // Redirect back with message
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
    header("Location: Voted_voters.php");
    exit();
}
?>