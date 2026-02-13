<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'marks_user');
define('DB_PASSWORD', 'Manu@2002?!'); // Use the password you created
define('DB_NAME', 'student_marks_db');
// Attempt to connect to MySQL database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Check connection
if($link === false){

die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>
