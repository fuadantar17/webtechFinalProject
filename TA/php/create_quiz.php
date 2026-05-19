<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'];

if(isset($_POST['create'])){

    $title = $_POST['title'];
    $time = $_POST['time_limit'];
    $marks = $_POST['total_marks'];
    $pass = $_POST['pass_mark'];

    $sql = "INSERT INTO quizzes
            (course_id, title, quiz_type, time_limit_minutes, total_marks, pass_mark, status)
            VALUES
            ($course_id, '$title', 'practice', $time, $marks, $pass, 'draft')";

    $conn->query($sql);

    $quiz_id = $conn->insert_id;

    header("Location: manage_questions.php?quiz_id=$quiz_id");
    exit();
}
?>

<link rel="stylesheet" href="../css/style.css">

<div class="container">

<h2>📝 Create Practice Quiz</h2>

<div class="card">

<form method="POST">

<input type="text" name="title" placeholder="Quiz Title" required><br><br>

<input type="number" name="time_limit" placeholder="Time Limit" required><br><br>

<input type="number" name="total_marks" placeholder="Total Marks" required><br><br>

<input type="number" name="pass_mark" placeholder="Pass Mark" required><br><br>

<button class="btn quiz-btn" name="create">
Create Quiz
</button>

</form>

</div>

</div>