<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Student Marks System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h1>Student Marks Recording System</h1>
<h2>Student Dashboard</h2>
<a href="add_student.php" class="btn">Add New Student</a>
<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();



// Include db connection
require_once "db.php";
// Attempt select query execution
$sql = "SELECT * FROM students ORDER BY name";
if($result = mysqli_query($link, $sql)){
if(mysqli_num_rows($result) > 0){
echo "<table>";
echo "<thead>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>Name</th>";
echo "<th>Email</th>";
echo "<th>Action</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";
while($row = mysqli_fetch_array($result)){
echo "<tr>";
echo "<td>" . $row['student_id'] . "</td>";
echo "<td>" . $row['name'] . "</td>";
echo "<td>" . $row['email'] . "</td>";
echo "<td>";
echo '<a href="manage_marks.php?id='. $row['student_id'] .'">Manage Marks</a>';
echo "</td>";
echo "</tr>";
}
echo
"</tbody>";
echo "</table>";
// Free result set
mysqli_free_result($result);
} else{
echo "<p><em>No students found. Please add one.</em></p>";
}
} else{
echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
// Close connection
mysqli_close($link);
?>

<?php

// Database configuration
//define('DB_SERVER', 'localhost');
//define('DB_USERNAME', 'marks_user');
//define('DB_PASSWORD', 'Manu@2002?!'); // Use the password you created
//define('DB_NAME', 'student_marks_db');
// Attempt to connect to MySQL database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

//session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<a href="index.php?logout=true" class="btn btn-danger btn-sm">Logout</a>
</div>
</body>
</html>

