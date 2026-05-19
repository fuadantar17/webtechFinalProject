<?php
require_once("../database/db.php");

$course_id = $_GET['course_id'];

$sql = "SELECT title, quiz_type
        FROM quizzes
        WHERE course_id = $course_id";

$result = $conn->query($sql);

$quizzes = [];

while($row = $result->fetch_assoc()){
    $quizzes[] = $row;
}

header('Content-Type: application/json');

echo json_encode($quizzes);
?>