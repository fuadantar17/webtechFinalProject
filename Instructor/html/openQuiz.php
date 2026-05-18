<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

if(!isset($_GET['quiz_id'])){
    echo "Quiz not selected";
    exit();
}

$quiz_id = $_GET['quiz_id'];

// get quiz info
$quizSql = "SELECT * FROM quizzes WHERE id = $quiz_id";
$quiz = $conn->query($quizSql)->fetch_assoc();

// get questions
$qSql = "SELECT * FROM questions WHERE quiz_id = $quiz_id";
$questions = $conn->query($qSql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Questions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card p-4 shadow">

        <h3 class="text-center">
            <?php echo $quiz['title']; ?>
        </h3>

        <hr>

        <?php while($q = $questions->fetch_assoc()){ ?>

            <div class="mb-4">

                <h5>
                    <?php echo $q['question']; ?>
                </h5>

                <ul class="list-group">

                    <li class="list-group-item">
                        A) <?php echo $q['option1']; ?>
                    </li>

                    <li class="list-group-item">
                        B) <?php echo $q['option2']; ?>
                    </li>

                    <li class="list-group-item">
                        C) <?php echo $q['option3']; ?>
                    </li>

                    <li class="list-group-item">
                        D) <?php echo $q['option4']; ?>
                    </li>

                </ul>

            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>