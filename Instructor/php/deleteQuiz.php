<?php
require_once("../database/db.php");

$id = $_GET['id'];

$conn->query("DELETE FROM quizzes WHERE id=$id");

header("Location: ../html/viewQuizzes.php");
exit();
?>