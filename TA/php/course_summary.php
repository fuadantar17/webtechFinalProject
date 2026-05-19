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

<h2>📊 Course Summary Report</h2>

<?php
// 1. TOTAL STUDENTS
$total_students = $conn->query("
    SELECT COUNT(*) AS total 
    FROM enrollments 
    WHERE course_id=$course_id
")->fetch_assoc()['total'];


// 2. TOTAL QUIZZES
$total_quizzes = $conn->query("
    SELECT COUNT(*) AS total 
    FROM quizzes 
    WHERE course_id=$course_id
")->fetch_assoc()['total'];


// 3. TOTAL ATTEMPTS
$total_attempts = $conn->query("
    SELECT COUNT(*) AS total
    FROM attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    WHERE q.course_id=$course_id
")->fetch_assoc()['total'];


// 4. UNIQUE STUDENTS WHO ATTEMPTED
$unique_attempts = $conn->query("
    SELECT COUNT(DISTINCT a.student_id) AS total
    FROM attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    WHERE q.course_id=$course_id
")->fetch_assoc()['total'];


// 5. AVERAGE SCORE
$avg_score = $conn->query("
    SELECT AVG(a.score) AS avg_score
    FROM attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    WHERE q.course_id=$course_id
")->fetch_assoc()['avg_score'];


// 6. ATTEMPT RATE (%)
$attempt_rate = ($total_students > 0)
    ? ($unique_attempts / $total_students) * 100
    : 0;
?>

<!-- DISPLAY -->
<div class="card">

<h3>📌 Report Overview</h3>

<p>👨‍🎓 Total Students: <b><?= $total_students ?></b></p>

<p>📝 Total Quizzes: <b><?= $total_quizzes ?></b></p>

<p>📊 Total Attempts: <b><?= $total_attempts ?></b></p>

<p>🎯 Quiz Attempt Rate: 
    <b><?= round($attempt_rate, 2) ?>%</b>
</p>

<p>⭐ Average Score: 
    <b><?= round($avg_score, 2) ?></b>
</p>

</div>

</div>