<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('dbcon.php');
include('enhanced_logger.php'); // Add enhanced logger
// include('timezone_config.php'); // Temporarily commented out for debugging

// Debug information
echo "<pre>";
echo "POST variables: ";
print_r($_POST);
echo "FILES variables: ";
print_r($_FILES);
echo "</pre>";

// Add a debug marker here to see if the script reaches this point
echo "DEBUG: Reached before save check.<br>";

if (isset($_POST['save'])){

echo "DEBUG: Save button pressed.<br>";

// Check for required variables
if(!isset($_POST['rfirstname']) || !isset($_POST['rlastname']) || !isset($_POST['rgender']) || 
   !isset($_POST['ryear']) || !isset($_POST['rposition']) || !isset($_POST['rmname']) || 
   !isset($_POST['party']) || !isset($_POST['department'])) {
    die("Missing required form fields.");
}
echo "DEBUG: Required fields checked.<br>";

// Handle optional course_id, ensure it's properly NULL for SQL if empty
$course_id_sql = "NULL"; // Default to SQL NULL
if(isset($_POST['course']) && !empty($_POST['course'])) {
    $course_id_sql = "'" . mysqli_real_escape_string($conn, $_POST['course']) . "'";
}
echo "DEBUG: Course ID handled (will be used when column exists).<br>";

$rfirstname=mysqli_real_escape_string($conn, $_POST['rfirstname']);
$rlastname=mysqli_real_escape_string($conn, $_POST['rlastname']);
$rgender=mysqli_real_escape_string($conn, $_POST['rgender']);
$ryear=mysqli_real_escape_string($conn, $_POST['ryear']);
$rposition=mysqli_real_escape_string($conn, $_POST['rposition']);
$rmname=mysqli_real_escape_string($conn, $_POST['rmname']);
$party=mysqli_real_escape_string($conn, $_POST['party']);
$department_id=mysqli_real_escape_string($conn, $_POST['department']);
$user_name=mysqli_real_escape_string($conn, $_POST['user_name']);
echo "DEBUG: POST variables escaped.<br>";

// Check if image was uploaded
if(!isset($_FILES['image']['tmp_name']) || empty($_FILES['image']['tmp_name'])) {
    die("No image uploaded.");
}
echo "DEBUG: Image upload checked.<br>";

// Check for file upload errors more rigorously
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die("File upload error: " . $_FILES['image']['error']);
}
echo "DEBUG: File upload error check passed.<br>";

// Check if the uploaded file is actually an uploaded file
if (!is_uploaded_file($_FILES['image']['tmp_name'])) {
    die("Invalid uploaded file: " . $_FILES['image']['tmp_name']);
}
echo "DEBUG: Is uploaded file check passed.<br>";

// Check if the temporary file is readable
if (!is_readable($_FILES['image']['tmp_name'])) {
    die("Temporary file is not readable: " . $_FILES['image']['tmp_name']);
}
echo "DEBUG: Temporary file readable check passed.<br>";

// $image= addslashes(file_get_contents($_FILES['image']['tmp_name'])); // Commented out to debug potential memory/resource issues
echo "DEBUG: Image content read (or skipped).<br>";

$image_name= addslashes($_FILES['image']['name']);
$image_size= getimagesize($_FILES['image']['tmp_name']);
echo "DEBUG: Image details retrieved.<br>";

			
	if ($rposition=="Governor"){
			
			// Generate a unique filename to avoid overwriting
			$file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
			$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
echo "DEBUG: Filename generated for Governor.<br>";

			// Upload to admin/upload folder
			move_uploaded_file($_FILES["image"]["tmp_name"], "upload/" . $unique_filename) or die("Error uploading file to admin/upload (Governor): " . error_get_last()['message']);
echo "DEBUG: File uploaded to admin/upload for Governor.<br>";

			// Also copy to root upload folder for voter view
			copy("upload/" . $unique_filename, "../upload/" . $unique_filename) or die("Error copying file to root upload (Governor): " . error_get_last()['message']);
echo "DEBUG: File copied to root upload for Governor.<br>";

			// Store the relative path in database
			$location = "upload/" . $unique_filename;
			
			
$sql = "insert into candidate (FirstName,LastName,Year,Position,Gender,MiddleName,Photo,Party,abc,department_id)
			values('$rfirstname','$rlastname','$ryear','$rposition','$rgender','$rmname','$location','$party','a','$department_id')";
echo "DEBUG: SQL query for Governor: " . $sql . "<br>";

if (!mysqli_query($conn, $sql)) {
    die("Error inserting Governor candidate: " . mysqli_error($conn) . ". Query: " . $sql);
}
echo "DEBUG: Governor candidate inserted.<br>";

// Log using enhanced logger with detailed information
$new_candidate_id = mysqli_insert_id($conn);
logCandidateActivity($conn, 'Add Candidate', "$rfirstname $rlastname", $rposition, $new_candidate_id);
echo "DEBUG: History logged for Governor.<br>";

header('location:candidate_list.php');
exit();
}

//


	if ($rposition=="Vice-Governor"){
			
			// Generate a unique filename to avoid overwriting
			$file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
			$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
echo "DEBUG: Filename generated for Vice-Governor.<br>";

			// Upload to admin/upload folder
			move_uploaded_file($_FILES["image"]["tmp_name"], "upload/" . $unique_filename) or die("Error uploading file to admin/upload (Vice-Governor): " . error_get_last()['message']);
echo "DEBUG: File uploaded to admin/upload for Vice-Governor.<br>";

			// Also copy to root upload folder for voter view
			copy("upload/" . $unique_filename, "../upload/" . $unique_filename) or die("Error copying file to root upload (Vice-Governor): " . error_get_last()['message']);
echo "DEBUG: File copied to root upload for Vice-Governor.<br>";

			// Store the relative path in database
			$location = "upload/" . $unique_filename;
			
			
$sql = "insert into candidate (FirstName,LastName,Year,Position,Gender,MiddleName,Photo,Party,abc,department_id)
			values('$rfirstname','$rlastname','$ryear','$rposition','$rgender','$rmname','$location','$party','b','$department_id')";
echo "DEBUG: SQL query for Vice-Governor: " . $sql . "<br>";

if (!mysqli_query($conn, $sql)) {
    die("Error inserting Vice-Governor candidate: " . mysqli_error($conn) . ". Query: " . $sql);
}
echo "DEBUG: Vice-Governor candidate inserted.<br>";

// Log using enhanced logger with detailed information
$new_candidate_id = mysqli_insert_id($conn);
logCandidateActivity($conn, 'Add Candidate', "$rfirstname $rlastname", $rposition, $new_candidate_id);
echo "DEBUG: History logged for Vice-Governor.<br>";

header('location:candidate_list.php');
exit();
}



	if ($rposition=="1st Year Representative"){
			
			// Generate a unique filename to avoid overwriting
			$file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
			$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
echo "DEBUG: Filename generated for 1st Year Rep.<br>";

			// Upload to admin/upload folder
			move_uploaded_file($_FILES["image"]['tmp_name'], "upload/" . $unique_filename) or die("Error uploading file to admin/upload (1st Year Rep): " . error_get_last()['message']);
echo "DEBUG: File uploaded to admin/upload for 1st Year Rep.<br>";

			// Also copy to root upload folder for voter view
			copy("upload/" . $unique_filename, "../upload/" . $unique_filename) or die("Error copying file to root upload (1st Year Rep): " . error_get_last()['message']);
echo "DEBUG: File copied to root upload for 1st Year Rep.<br>";

			// Store the relative path in database
			$location = "upload/" . $unique_filename;
			
			
$sql = "insert into candidate (FirstName,LastName,Year,Position,Gender,MiddleName,Photo,Party,abc,department_id)
			values('$rfirstname','$rlastname','$ryear','$rposition','$rgender','$rmname','$location','$party','c','$department_id')";
echo "DEBUG: SQL query for 1st Year Rep: " . $sql . "<br>";

if (!mysqli_query($conn, $sql)) {
    die("Error inserting 1st Year Representative candidate: " . mysqli_error($conn) . ". Query: " . $sql);
}
echo "DEBUG: 1st Year Rep candidate inserted.<br>";

// Log using enhanced logger with detailed information
$new_candidate_id = mysqli_insert_id($conn);
logCandidateActivity($conn, 'Add Candidate', "$rfirstname $rlastname", $rposition, $new_candidate_id);
echo "DEBUG: History logged for 1st Year Rep.<br>";

header('location:candidate_list.php');
exit();
}

//
	if ($rposition=="2nd Year Representative"){
			
			// Generate a unique filename to avoid overwriting
			$file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
			$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
echo "DEBUG: Filename generated for 2nd Year Rep.<br>";

			// Upload to admin/upload folder
			move_uploaded_file($_FILES["image"]['tmp_name'], "upload/" . $unique_filename) or die("Error uploading file to admin/upload (2nd Year Rep): " . error_get_last()['message']);
echo "DEBUG: File uploaded to admin/upload for 2nd Year Rep.<br>";

			// Also copy to root upload folder for voter view
			copy("upload/" . $unique_filename, "../upload/" . $unique_filename) or die("Error copying file to root upload (2nd Year Rep): " . error_get_last()['message']);
echo "DEBUG: File copied to root upload for 2nd Year Rep.<br>";

			// Store the relative path in database
			$location = "upload/" . $unique_filename;
			
			
$sql = "insert into candidate (FirstName,LastName,Year,Position,Gender,MiddleName,Photo,Party,abc,department_id)
			values('$rfirstname','$rlastname','$ryear','$rposition','$rgender','$rmname','$location','$party','d','$department_id')";
echo "DEBUG: SQL query for 2nd Year Rep: " . $sql . "<br>";

if (!mysqli_query($conn, $sql)) {
    die("Error inserting 2nd Year Representative candidate: " . mysqli_error($conn) . ". Query: " . $sql);
}
echo "DEBUG: 2nd Year Rep candidate inserted.<br>";

// Log using enhanced logger with detailed information
$new_candidate_id = mysqli_insert_id($conn);
logCandidateActivity($conn, 'Add Candidate', "$rfirstname $rlastname", $rposition, $new_candidate_id);
echo "DEBUG: History logged for 2nd Year Rep.<br>";

header('location:candidate_list.php');
exit();
}


//


	if ($rposition=="3rd Year Representative"){
			
			// Generate a unique filename to avoid overwriting
			$file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
			$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
echo "DEBUG: Filename generated for 3rd Year Rep.<br>";

			// Upload to admin/upload folder
			move_uploaded_file($_FILES["image"]['tmp_name'], "upload/" . $unique_filename) or die("Error uploading file to admin/upload (3rd Year Rep): " . error_get_last()['message']);
echo "DEBUG: File uploaded to admin/upload for 3rd Year Rep.<br>";

			// Also copy to root upload folder for voter view
			copy("upload/" . $unique_filename, "../upload/" . $unique_filename) or die("Error copying file to root upload (3rd Year Rep): " . error_get_last()['message']);
echo "DEBUG: File copied to root upload for 3rd Year Rep.<br>";

			// Store the relative path in database
			$location = "upload/" . $unique_filename;
			
			
$sql = "insert into candidate (FirstName,LastName,Year,Position,Gender,MiddleName,Photo,Party,abc,department_id)
			values('$rfirstname','$rlastname','$ryear','$rposition','$rgender','$rmname','$location','$party','e','$department_id')";
echo "DEBUG: SQL query for 3rd Year Rep: " . $sql . "<br>";

if (!mysqli_query($conn, $sql)) {
    die("Error inserting 3rd Year Representative candidate: " . mysqli_error($conn) . ". Query: " . $sql);
}
echo "DEBUG: 3rd Year Rep candidate inserted.<br>";

// Log using enhanced logger with detailed information
$new_candidate_id = mysqli_insert_id($conn);
logCandidateActivity($conn, 'Add Candidate', "$rfirstname $rlastname", $rposition, $new_candidate_id);
echo "DEBUG: History logged for 3rd Year Rep.<br>";

header('location:candidate_list.php');
exit();
}
//




	if ($rposition=="4th Year Representative"){
			
			// Generate a unique filename to avoid overwriting
			$file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
			$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
echo "DEBUG: Filename generated for 4th Year Rep.<br>";

			// Upload to admin/upload folder
			move_uploaded_file($_FILES["image"]['tmp_name'], "upload/" . $unique_filename) or die("Error uploading file to admin/upload (4th Year Rep): " . error_get_last()['message']);
echo "DEBUG: File uploaded to admin/upload for 4th Year Rep.<br>";

			// Also copy to root upload folder for voter view
			copy("upload/" . $unique_filename, "../upload/" . $unique_filename) or die("Error copying file to root upload (4th Year Rep): " . error_get_last()['message']);
echo "DEBUG: File copied to root upload for 4th Year Rep.<br>";

			// Store the relative path in database
			$location = "upload/" . $unique_filename;
			
			
$sql = "insert into candidate (FirstName,LastName,Year,Position,Gender,MiddleName,Photo,Party,abc,department_id)
			values('$rfirstname','$rlastname','$ryear','$rposition','$rgender','$rmname','$location','$party','f','$department_id')";
echo "DEBUG: SQL query for 4th Year Rep: " . $sql . "<br>";

if (!mysqli_query($conn, $sql)) {
    die("Error inserting 4th Year Representative candidate: " . mysqli_error($conn) . ". Query: " . $sql);
}
echo "DEBUG: 4th Year Rep candidate inserted.<br>";

// Log using enhanced logger with detailed information
$new_candidate_id = mysqli_insert_id($conn);
logCandidateActivity($conn, 'Add Candidate', "$rfirstname $rlastname", $rposition, $new_candidate_id);
echo "DEBUG: History logged for 4th Year Rep.<br>";

header('location:candidate_list.php');
exit();
}



}
?>