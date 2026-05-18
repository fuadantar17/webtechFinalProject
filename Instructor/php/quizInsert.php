<?php

session_start();

require_once("../php/sessionCheck.php");
require_once("../database/db.php");

if(isset($_POST['title'])){

    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $time_limit = $_POST['time_limit'];
    $total_marks = $_POST['total_marks'];

    $created_by = $_SESSION['instructor_id'];

    $sql = "INSERT INTO quizzes 
            (
                course_id,
                title,
                time_limit_minutes,
                total_marks,
                created_by
            )
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "isiii",
        $course_id,
        $title,
        $time_limit,
        $total_marks,
        $created_by
    );

    if($stmt->execute()){

        header("Location: ../html/viewQuizzes.php");
        exit();

    }else{

        echo "Error Creating Quiz : " . $stmt->error;
    }

}
?>