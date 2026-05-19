<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'];
?>

<link rel="stylesheet" href="../css/style.css">

<div class="container">

<h2>👨‍🎓 Student Monitoring</h2>

<?php
$sql = "SELECT 
            u.name,
            u.email,
            COALESCE(a.score, 0) AS score
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        LEFT JOIN attempts a ON a.student_id = u.id
        LEFT JOIN quizzes q ON a.quiz_id = q.id AND q.course_id = $course_id
        WHERE e.course_id = $course_id";

$result = $conn->query($sql);
?>

<table border="1" width="100%" style="margin-top:20px;">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Score</th>
        <th>Status</th>
    </tr>

<?php while($row = $result->fetch_assoc()) { ?>

<?php
$status = ($row['score'] >= 2) ? "Pass" : "Fail";
?>

<tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['score'] ?></td>
    <td style="color: <?= ($status=='Pass')?'green':'red' ?>">
        <?= $status ?>
    </td>
</tr>

<?php } ?>

</table>

</div>



<div class="center-container">
    <a class="btn quiz-btn" href="upload_material.php?course_id=<?= $course_id ?>">
        📚 Study Materials
    </a>
</div>

<div class="center-container">
    <a class="btn quiz-btn" href="qa_board.php?course_id=<?= $course_id ?>">
        ❓ Q&A Board
    </a>
</div>