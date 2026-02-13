<?php
require_once "db.php";
$name = $email = $enrollment_date = "";
$name_err = $email_err = $date_err = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
// Validate name
if(empty(trim($_POST["name"]))){
$name_err = "Please enter a name.";
} else{
$name = trim($_POST["name"]);
}
// Validate email
if(empty(trim($_POST["email"]))){
$email_err = "Please enter an email.";
} else{
$email = trim($_POST["email"]);
}
// Validate date
if(empty(trim($_POST["enrollment_date"]))){
$date_err = "Please enter an enrollment date.";
} else{
$enrollment_date = trim($_POST["enrollment_date"]);
}
// Check input errors before inserting in database
if(empty($name_err) && empty($email_err) && empty($date_err)){
$sql = "INSERT INTO students (name, email, enrollment_date) VALUES (?, ?, ?)";
if($stmt = mysqli_prepare($link, $sql)){
mysqli_stmt_bind_param($stmt, "sss", $param_name, $param_email, $param_date);
$param_name = $name;
$param_email = $email;
$param_date = $enrollment_date;
if(mysqli_stmt_execute($stmt)){
header("location: admin.php");
exit();
} else{
echo "Something went wrong. Please try again later.";
}
mysqli_stmt_close($stmt);
}
}
mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Student</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h2>Add New Student</h2>
<p>Please fill this form to add a student to the database.</p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
<div>
<label>Name</label>
<input type="text" name="name" value="<?php echo $name; ?>">
<span><?php echo $name_err; ?></span>
</div>
<div>
<label>Email</label>
<input type="email" name="email" value="<?php echo $email; ?>">
<span><?php echo $email_err; ?></span>
</div>
<div>
<label>Enrollment Date</label>
<input type="date" name="enrollment_date" value="<?php echo $enrollment_date; ?>">
<span><?php echo $date_err; ?></span>
</div>
<div>
<input type="submit" class="btn" value="Submit">
<a href="index.php" class="btn btn-danger">Cancel</a>
</div>
</form>
</div>
</body>
</html>
