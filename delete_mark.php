<?php
require_once "db.php";

if (!isset($_GET['id'], $_GET['student_id'])) {
    header("Location: index.php");
    exit();
}

$mark_id = intval($_GET['id']);
$student_id = intval($_GET['student_id']);

$sql = "DELETE FROM marks WHERE mark_id = ? AND student_id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $mark_id, $student_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($link);

header("Location: manage_marks.php?id=" . $student_id);
exit();
