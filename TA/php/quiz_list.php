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

<h2>📚 Quiz List</h2>

<div id="quizArea"></div>

</div>

<script src="../js/main.js"></script>

<script>
loadQuizzes(<?= $course_id ?>);
</script>