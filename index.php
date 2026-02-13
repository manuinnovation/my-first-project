<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'lecture') {
        header("Location: add_student.php");
        exit();
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: student.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Simple fixed users
    $users = [
        'Lecture' => '1234',
        'student' => 'manu123',
    ];

    if (array_key_exists($username, $users) && $users[$username] === $password) {
        // Set session role
        $_SESSION['username'] = $username;
        $_SESSION['role'] = ($username === 'Lecture') ? 'lecture' : 'student';

        // Redirect accordingly
        if ($_SESSION['role'] === 'lecture') {
            header("Location: add_student.php");
            exit();
        } else {
            header("Location: student.php");
            exit();
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login - Student Marks System</title>
<style>
  body {
    background-color: skyblue;
    font-family: Arial, sans-serif;
    padding: 50px;
  }
  .login-box {
    background: white;
    max-width: 400px;
    margin: auto;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 0 10px #ccc;
  }
  h2 {
    text-align: center;
    color: #2980b9;
  }
  label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
  }
  input[type=text], input[type=password] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #3498db;
    border-radius: 4px;
  }
  button {
    margin-top: 20px;
    width: 100%;
    padding: 10px;
    background-color: #2980b9;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  .error {
    color: red;
    margin-top: 15px;
    text-align: center;
  }
</style>
</head>
<body>
<div class="login-box">
  <h2>Login</h2>
  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required autofocus>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Log In</button>
  </form>
</div>
</body>
</html>
