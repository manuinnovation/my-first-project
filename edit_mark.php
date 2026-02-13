<?php
require_once "db.php";

if (!isset($_GET['id'], $_GET['student_id'])) {
    header("Location: index.php");
    exit();
}

$mark_id = intval($_GET['id']);
$student_id = intval($_GET['student_id']);

$subject_id = $mark = "";
$subject_err = $mark_err = "";

// Fetch subjects for dropdown
$subjects = [];
$sql_subjects = "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name";
if ($stmt = mysqli_prepare($link, $sql_subjects)) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $subjects = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Fetch existing mark details
$sql = "SELECT subject_id, marks_obtained FROM marks WHERE mark_id = ? AND student_id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $mark_id, $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $subject_id = $row['subject_id'];
        $mark = $row['marks_obtained'];
    } else {
        // Record not found
        header("Location: manage_marks.php?id=" . $student_id);
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    die("Error preparing statement.");
}

// Handle POST to update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate subject_id
    if (empty($_POST["subject_id"])) {
        $subject_err = "Please select a subject.";
    } else {
        $subject_id = intval($_POST["subject_id"]);
    }

    // Validate mark
    if (!isset($_POST["mark"]) || $_POST["mark"] === '') {
        $mark_err = "Please enter a mark.";
    } elseif (!is_numeric($_POST["mark"]) || $_POST["mark"] < 0 || $_POST["mark"] > 100) {
        $mark_err = "Please enter a valid mark between 0 and 100.";
    } else {
        $mark = intval($_POST["mark"]);
    }

    if (empty($subject_err) && empty($mark_err)) {
        $sql_update = "UPDATE marks SET subject_id = ?, marks_obtained = ? WHERE mark_id = ? AND student_id = ?";
        if ($stmt = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt, "iiii", $subject_id, $mark, $mark_id, $student_id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: manage_marks.php?id=" . $student_id);
                exit();
            } else {
                echo "<p style='color:red;'>Error updating mark. Try again.</p>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Mark</title>
<style>
    body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
    .container {
        max-width: 500px;
        margin: auto;
        background: white;
        padding: 20px 25px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    label {
        display: block;
        margin-top: 15px;
        font-weight: bold;
    }
    select, input[type="number"] {
        width: 100%;
        padding: 8px 10px;
        margin-top: 6px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 1rem;
    }
    .error {
        color: #d9534f;
        font-size: 0.9rem;
        margin-top: 4px;
    }
    button, a {
        margin-top: 20px;
        padding: 10px 18px;
        font-size: 1rem;
        border-radius: 4px;
        text-decoration: none;
        cursor: pointer;
    }
    button {
        background-color: #007bff;
        border: none;
        color: white;
    }
    button:hover {
        background-color: #0056b3;
    }
    a {
        background-color: #6c757d;
        color: white;
        margin-left: 10px;
    }
    a:hover {
        background-color: #5a6268;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Edit Mark</h2>
    <form method="post" action="">
        <label for="subject">Subject</label>
        <select name="subject_id" id="subject">
            <option value="">-- Select Subject --</option>
            <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo $sub['subject_id']; ?>" <?php if ($sub['subject_id'] == $subject_id) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($sub['subject_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($subject_err): ?>
            <div class="error"><?php echo $subject_err; ?></div>
        <?php endif; ?>

        <label for="mark">Mark (0-100)</label>
        <input type="number" name="mark" id="mark" min="0" max="100" value="<?php echo htmlspecialchars($mark); ?>">
        <?php if ($mark_err): ?>
            <div class="error"><?php echo $mark_err; ?></div>
        <?php endif; ?>

        <button type="submit">Update Mark</button>
        <a href="manage_marks.php?id=<?php echo $student_id; ?>">Cancel</a>
    </form>
</div>
</body>
</html>
