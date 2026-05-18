<?php

session_start();
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

if(isset($_POST['quiz_id'])){

    $quiz_id = $_POST['quiz_id'];
    $question = trim($_POST['question']);
    $option1 = trim($_POST['option1']);
    $option2 = trim($_POST['option2']);
    $option3 = trim($_POST['option3']);
    $option4 = trim($_POST['option4']);
    $correct_option = $_POST['correct_option'];

    $sql = "INSERT INTO questions 
            (quiz_id, question, option1, option2, option3, option4, correct_option)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "isssssi",
        $quiz_id,
        $question,
        $option1,
        $option2,
        $option3,
        $option4,
        $correct_option
    );

    if($stmt->execute()){

        header("Location: ../html/viewQuizzes.php");
        exit();

    }else{

        echo "Error adding question: " . $stmt->error;
    }

}
?>