<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

if(!isset($_GET['quiz_id'])){
    die("Quiz ID missing");
}

$quiz_id = intval($_GET['quiz_id']);

$sql = "SELECT * FROM quiz_questions WHERE quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Quiz Questions</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/questions.css">
</head>

<body>

<div class="container">

    <div class="card-box">

        <a href="viewQuizzes.php" class="btn-back">⬅ Back</a>

        <h3>Quiz Questions</h3>

        <a href="addQuestion.php?quiz_id=<?php echo $quiz_id; ?>" class="btn-add">
            ➕ Add Question
        </a>

        <hr>

        <?php if($result->num_rows == 0){ ?>
            <p>No questions found</p>
        <?php } ?>

        <?php while($row = $result->fetch_assoc()){ ?>

        <div class="q-card">

            <h5><?php echo $row['question']; ?></h5>

            <p>A: <?php echo $row['option_a']; ?></p>
            <p>B: <?php echo $row['option_b']; ?></p>
            <p>C: <?php echo $row['option_c']; ?></p>
            <p>D: <?php echo $row['option_d']; ?></p>

            <b>Correct: <?php echo $row['correct_answer']; ?></b>

            <br><br>

            <button class="btn btn-danger btn-sm"
                onclick="deleteQuestion(<?php echo $row['id']; ?>)">
                Delete
            </button>

        </div>

        <?php } ?>

    </div>

</div>

<script src="../js/questions.js"></script>

</body>
</html>