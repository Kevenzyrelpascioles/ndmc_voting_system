<?php
include("dbcon.php");
 header("Content-type: application/vnd.ms-excel");
 header("Content-Disposition: attachment; filename=Candidate_List.xls" );
 header("Expires: 0");
 header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
 header("Pragma: public");

// Get list of departments
$dept_query = mysqli_query($conn, "SELECT DISTINCT d.department_id, d.department_name 
                                  FROM departments d 
                                  LEFT JOIN candidate c ON d.department_id = c.department_id 
                                  WHERE c.CandidateID IS NOT NULL 
                                  ORDER BY d.department_name ASC");
$departments = array();
while ($dept_row = mysqli_fetch_array($dept_query)) {
    $departments[$dept_row['department_id']] = $dept_row['department_name'];
}

// Also include "Not Assigned" group
$departments['none'] = 'Not Assigned';
?>
<table cellpadding="0" cellspacing="0" border="0" class="display" id="log" class="jtable">
<?php 
// For each department, display candidates
foreach ($departments as $dept_id => $dept_name) {
    if ($dept_id === 'none') {
        $dept_condition = "c.department_id IS NULL OR c.department_id = ''";
    } else {
        $dept_condition = "c.department_id = '$dept_id'";
    }
    
    $candidate_query = mysqli_query($conn, "SELECT c.*, d.department_name FROM candidate c
                                        LEFT JOIN departments d ON c.department_id = d.department_id
                                        WHERE $dept_condition
                                        ORDER BY 
                                        CASE c.Position 
                                            WHEN 'Governor' THEN 1
                                            WHEN 'Vice-Governor' THEN 2
                                            WHEN '1st Year Representative' THEN 3
                                            WHEN '2nd Year Representative' THEN 4
                                            WHEN '3rd Year Representative' THEN 5
                                            WHEN '4th Year Representative' THEN 6
                                            ELSE 7 END,
                                        CASE c.Year
                                            WHEN '1st year' THEN 1
                                            WHEN '2nd year' THEN 2
                                            WHEN '3rd year' THEN 3
                                            WHEN '4th year' THEN 4
                                            ELSE 5 END,
                                        c.LastName, c.FirstName");
    
    $count = mysqli_num_rows($candidate_query);
    if ($count > 0) {
        echo "<tr><th colspan='8' style='background-color: #f0f0f0; font-weight: bold; text-align: center;'>$dept_name</th></tr>";
?>
			<thead>
				<tr>
				<th>Position</th>
				<th>FirstName</th>
				<th>LastName</th>
				<th>MiddleName</th>
				<th>Gender</th>
				<th>Year</th>
				<th>Department</th>
				<th>Party</th>
				</tr>
			</thead>
			<tbody>
<?php
        while ($candidate_rows = mysqli_fetch_array($candidate_query)) { 
            $department = $candidate_rows['department_name'] ? $candidate_rows['department_name'] : 'Not Assigned';
?>
<tr>
	<td align="center"><?php echo $candidate_rows['Position']; ?></td>
	<td><?php echo $candidate_rows['FirstName']; ?></td>
	<td><?php echo $candidate_rows['LastName']; ?></td>
	<td><?php echo $candidate_rows['MiddleName']; ?></td>
	<td><?php echo $candidate_rows['Gender']; ?></td>
	<td align="center"><?php echo $candidate_rows['Year']; ?></td>
	<td align="center"><?php echo $department; ?></td>
	<td align="center"><?php echo $candidate_rows['Party']; ?></td>
</tr>
<?php 
        }
    }
}
?>
			</tbody>
		</table> 