<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "db.php";
require_once "tcpdf/tcpdf.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$student_id = intval($_GET['id']);
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$isDownload = isset($_GET['download']);

/* Fetch student info */
$stmt = mysqli_prepare($link, "SELECT * FROM students WHERE student_id=?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student_result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($student_result);

if (!$student) {
    die("Student not found.");
}

/* Handle Add Mark */
if (!$isDownload && isset($_POST['save'])) {
    $subject_id = $_POST['subject_id'];
    $marks_obtained = $_POST['marks_obtained'];

    $stmt = mysqli_prepare($link,
        "INSERT INTO marks (student_id, subject_id, marks_obtained)
         VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iii", $student_id, $subject_id, $marks_obtained);
    mysqli_stmt_execute($stmt);

    header("Location: manage_marks.php?id=$student_id");
    exit();
}

/* Handle Update Mark */
if (!$isDownload && isset($_POST['update'])) {
    $mark_id = intval($_POST['mark_id']);
    $marks = $_POST['marks_obtained'];

    $stmt = mysqli_prepare($link,
        "UPDATE marks SET marks_obtained=? WHERE mark_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $marks, $mark_id);
    mysqli_stmt_execute($stmt);

    header("Location: manage_marks.php?id=$student_id");
    exit();
}

/* Handle Delete Mark */
if (!$isDownload && isset($_GET['delete'])) {
    $mark_id = intval($_GET['delete']);
    mysqli_query($link, "DELETE FROM marks WHERE mark_id=$mark_id");

    header("Location: manage_marks.php?id=$student_id");
    exit();
}

/* Fetch Subjects */
$subjects = mysqli_query($link, "SELECT * FROM subjects");

/* Fetch Marks */
$sql = "SELECT marks.mark_id,
               subjects.subject_name,
               marks.marks_obtained,
               marks.total_marks
        FROM marks
        JOIN subjects ON marks.subject_id = subjects.subject_id
        WHERE marks.student_id=?";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$marks = mysqli_stmt_get_result($stmt);

/* Calculate Total & Percentage */
$sql_total = "SELECT SUM(marks_obtained) AS obtained,
                     SUM(total_marks) AS total
              FROM marks
              WHERE student_id=?";
$stmt_total = mysqli_prepare($link, $sql_total);
mysqli_stmt_bind_param($stmt_total, "i", $student_id);
mysqli_stmt_execute($stmt_total);
$totals = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total));

$obtained = $totals['obtained'] ?? 0;
$total = $totals['total'] ?? 0;
$percentage = ($total > 0) ? round(($obtained / $total) * 100, 2) : 0;

/* Handle PDF Download */
if ($isDownload) {
    $pdf = new TCPDF();
    $pdf->SetCreator('Student Marks System');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Student Report: ' . $student['name']);
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    // Title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, "Student Report", 0, 1, 'C');

    // Student Info
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(5);
    $pdf->Cell(0, 8, "Name: " . $student['name'], 0, 1);
    $pdf->Cell(0, 8, "Email: " . $student['email'], 0, 1);
    $pdf->Cell(0, 8, "Enrollment Date: " . $student['enrollment_date'], 0, 1);

    $pdf->Ln(5);

    // Table Header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(90, 10, 'Subject', 1);
    $pdf->Cell(30, 10, 'Marks Obtained', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Total Marks', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Percentage (%)', 1, 1, 'C');

    // Table Body
    $pdf->SetFont('helvetica', '', 12);

    // Reset marks result pointer to start
    mysqli_data_seek($marks, 0);
    while ($row = mysqli_fetch_assoc($marks)) {
        $subject = $row['subject_name'];
        $marks_obt = $row['marks_obtained'];
        $total_marks = $row['total_marks'];
        $percent = ($total_marks > 0) ? round(($marks_obt / $total_marks) * 100, 2) : 0;

        $pdf->Cell(90, 8, $subject, 1);
        $pdf->Cell(30, 8, $marks_obt, 1, 0, 'C');
        $pdf->Cell(30, 8, $total_marks, 1, 0, 'C');
        $pdf->Cell(40, 8, $percent, 1, 1, 'C');
    }

    // Total summary row
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(90, 10, 'Total', 1);
    $pdf->Cell(30, 10, $obtained, 1, 0, 'C');
    $pdf->Cell(30, 10, $total, 1, 0, 'C');
    $pdf->Cell(40, 10, $percentage, 1, 1, 'C');

    // Output PDF to browser
    $pdf->Output('Student_Report_' . $student['name'] . '.pdf', 'I');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Marks</title>
<link rel="stylesheet" href="style.css">
<style>
  .btn { padding: 5px 10px; margin: 2px; }
  .btn-danger { background: #d9534f; color: white; text-decoration: none; }
  table { border-collapse: collapse; width: 100%; }
  th, td { padding: 8px 12px; border: 1px solid #ccc; }
  .summary { margin-bottom: 15px; }
  form.inline-form { display: inline; }
</style>
</head>
<body>

<div class="container">
<h2>Marks for <?php echo htmlspecialchars($student['name']); ?></h2>

<div>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
    <p><strong>Enrollment Date:</strong> <?php echo htmlspecialchars($student['enrollment_date']); ?></p>
</div>

<div class="summary">
    <p><strong>Total Marks:</strong> <?php echo $obtained; ?> / <?php echo $total; ?></p>
    <p><strong>Percentage:</strong> <?php echo $percentage; ?>%</p>
</div>

<a href="manage_marks.php?id=<?php echo $student_id; ?>&download=1" class="btn" target="_blank">Download PDF Report</a>

<!-- ADD MARK -->
<form method="post" style="margin-top: 10px;">
<select name="subject_id" required>
<option value="">-- Select Subject --</option>
<?php
mysqli_data_seek($subjects, 0);
while($s = mysqli_fetch_assoc($subjects)){
    echo '<option value="'. $s['subject_id'] .'">'. htmlspecialchars($s['subject_name']) .'</option>';
}
?>
</select>

<input type="number" name="marks_obtained" placeholder="Marks" required min="0" max="100">
<input type="submit" name="save" value="Add Marks" class="btn">
</form>

<hr>

<table>
<tr>
<th>Subject</th>
<th>Marks</th>
<th>Total</th>
<th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($marks)): ?>
<tr>
<td><?php echo htmlspecialchars($row['subject_name']); ?></td>

<td>
<?php if($edit_id === (int)$row['mark_id']): ?>
<form method="post" class="inline-form">
    <input type="hidden" name="mark_id" value="<?php echo $row['mark_id']; ?>">
    <input type="number" name="marks_obtained"
           value="<?php echo $row['marks_obtained']; ?>"
           required min="0" max="100">
    <input type="submit" name="update" value="Save" class="btn">
    <a href="manage_marks.php?id=<?php echo $student_id; ?>" class="btn">Cancel</a>
</form>
<?php else: ?>
    <?php echo $row['marks_obtained']; ?>
<?php endif; ?>
</td>

<td><?php echo $row['total_marks']; ?></td>

<td>
<?php if($edit_id !== (int)$row['mark_id']): ?>
<a href="manage_marks.php?id=<?php echo $student_id; ?>&edit=<?php echo $row['mark_id']; ?>" class="btn">Edit</a>
<a href="manage_marks.php?id=<?php echo $student_id; ?>&delete=<?php echo $row['mark_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this mark?');">Delete</a>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="index.php" class="btn">⬅ Back</a>

</div>

</body>
</html>
