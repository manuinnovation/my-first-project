<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once "db.php";
require_once "tcpdf/tcpdf.php";

/* ================================
   HANDLE PDF DOWNLOAD
================================ */
if(isset($_POST['download']) && isset($_POST['student_id'])){

    $student_id = intval($_POST['student_id']);

    // Fetch student
    $stmt = mysqli_prepare($link,"SELECT * FROM students WHERE student_id=?");
    mysqli_stmt_bind_param($stmt,"i",$student_id);
    mysqli_stmt_execute($stmt);
    $student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    // Fetch marks
    $stmt = mysqli_prepare($link,"
        SELECT s.subject_name, m.marks_obtained
        FROM marks m
        JOIN subjects s ON m.subject_id=s.subject_id
        WHERE m.student_id=?
        ORDER BY s.subject_name
    ");
    mysqli_stmt_bind_param($stmt,"i",$student_id);
    mysqli_stmt_execute($stmt);
    $marks = mysqli_fetch_all(mysqli_stmt_get_result($stmt),MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Calculate total marks
    $total_marks = 0;
    foreach($marks as $m){
        $total_marks += $m['marks_obtained'];
    }

    $num_subjects = count($marks);
    $max_per_subject = 100; // Adjust if max marks differ
    $max_total = $num_subjects * $max_per_subject;
    $percentage = ($max_total > 0) ? round(($total_marks / $max_total) * 100, 2) : 0;

    // Create PDF
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont("helvetica","",12);

    $pdf->Cell(0,10,"STUDENT RESULT REPORT",0,1,"C");
    $pdf->Ln(5);
    $pdf->Cell(0,10,"Name: ".$student['name'],0,1);
    $pdf->Cell(0,10,"Email: ".$student['email'],0,1);
    $pdf->Ln(5);

    $html = "<table border='1' cellpadding='6'>
        <tr><th>Subject</th><th>Marks</th></tr>";

    foreach($marks as $m){
        $html .= "<tr>
            <td>{$m['subject_name']}</td>
            <td>{$m['marks_obtained']}</td>
        </tr>";
    }

    // Add total and percentage rows
    $html .= "<tr>
        <td><strong>Total</strong></td>
        <td><strong>{$total_marks}</strong></td>
    </tr>";

    $html .= "<tr>
        <td><strong>Percentage</strong></td>
        <td><strong>{$percentage}%</strong></td>
    </tr>";

    $html .= "</table>";

    $pdf->writeHTML($html);
    $pdf->Output("Student_Report_".$student_id.".pdf","D");
    exit;
}

/* ================================
   NORMAL PAGE VIEW
================================ */

$student = null;
$marks = [];
$student_id = "";
$total_marks = 0;
$percentage = 0;

// Fetch all students
$res = mysqli_query($link,"SELECT student_id,name FROM students ORDER BY name");
$students = mysqli_fetch_all($res,MYSQLI_ASSOC);

// If student selected
if(isset($_POST['student_id']) && !isset($_POST['download'])){
    $student_id = intval($_POST['student_id']);

    $stmt = mysqli_prepare($link,"SELECT * FROM students WHERE student_id=?");
    mysqli_stmt_bind_param($stmt,"i",$student_id);
    mysqli_stmt_execute($stmt);
    $student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($link,"
        SELECT s.subject_name, m.marks_obtained
        FROM marks m
        JOIN subjects s ON m.subject_id=s.subject_id
        WHERE m.student_id=?
        ORDER BY s.subject_name
    ");
    mysqli_stmt_bind_param($stmt,"i",$student_id);
    mysqli_stmt_execute($stmt);
    $marks = mysqli_fetch_all(mysqli_stmt_get_result($stmt),MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Calculate total marks and percentage
    $total_marks = 0;
    foreach($marks as $m){
        $total_marks += $m['marks_obtained'];
    }
    $num_subjects = count($marks);
    $max_per_subject = 100; // Adjust as needed
    $max_total = $num_subjects * $max_per_subject;
    $percentage = ($max_total > 0) ? round(($total_marks / $max_total) * 100, 2) : 0;
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Marks</title>
<style>
body{font-family:Arial;background:#f4f6f9;padding:30px;}
.box{background:white;max-width:700px;margin:auto;padding:25px;border-radius:8px;box-shadow:0 0 10px #ccc;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{padding:10px;border:1px solid #ccc;}
th{background:#3498db;color:white;}
button{padding:10px 20px;background:#2980b9;color:white;border:none;border-radius:5px;cursor:pointer;}
.total-row td {
    font-weight: bold;
    background-color: #dff0d8;
}
</style>
</head>

<body>

<div class="box">
<h2>Student Result Portal</h2>

<form method="post">
<select name="student_id" required>
<option value="">-- Select Student --</option>
<?php foreach($students as $s): ?>
<option value="<?php echo $s['student_id']; ?>" <?php if($student_id==$s['student_id']) echo "selected"; ?>>
<?php echo $s['student_id']." - ".$s['name']; ?>
</option>
<?php endforeach; ?>
</select>
<br><br>
<button type="submit">View Marks</button>
</form>

<?php if($student): ?>
<hr>
<h3><?php echo $student['name']; ?></h3>
<p>Email: <?php echo $student['email']; ?></p>

<form method="post">
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
<button name="download">📄 Download PDF</button>
</form>

<table>
<tr><th>Subject</th><th>Marks</th></tr>
<?php foreach($marks as $m): ?>
<tr>
<td><?php echo htmlspecialchars($m['subject_name']); ?></td>
<td><?php echo htmlspecialchars($m['marks_obtained']); ?></td>
</tr>
<?php endforeach; ?>
<tr class="total-row">
<td>Total</td>
<td><?php echo $total_marks; ?></td>
</tr>
<tr class="total-row">
<td>Percentage</td>
<td><?php echo $percentage; ?>%</td>
</tr>
</table>

<?php endif; ?>
</div>

</body>
</html>
