<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

$instructor_id = $_SESSION['instructor_id'];

$sql = "SELECT quizzes.*, courses.title AS course_name
        FROM quizzes
        LEFT JOIN courses
        ON quizzes.course_id = courses.id
        WHERE quizzes.created_by = $instructor_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Quizzes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/quiz.css">
</head>

<body>

<div class="container mt-4">

    <!-- BACK -->
    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

    <h2 class="title">📚 My Quizzes</h2>

    <div class="row">

        <?php while($row = $result->fetch_assoc()){ ?>

        <div class="col-md-4">

            <div class="quiz-card">

                <h4><?php echo $row['title']; ?></h4>

                <p>📘 Course: <?php echo $row['course_name']; ?></p>
                <p>⏱ Time: <?php echo $row['time_limit_minutes']; ?> min</p>
                <p>🎯 Marks: <?php echo $row['total_marks']; ?></p>

                <div class="btn-group">

                    <!-- QUESTION FEATURE (KEEP AS IT WAS) -->
                    <a href="viewQuestions.php?quiz_id=<?php echo $row['id']; ?>" 
                       class="btn btn-primary btn-sm">
                        Questions
                    </a>

                    <a href="addQuestion.php?quiz_id=<?php echo $row['id']; ?>" 
                       class="btn btn-success btn-sm">
                        Add Question
                    </a>

                    <!-- ONLY UPDATE -->
                    <a href="createQuiz.php?edit_id=<?php echo $row['id']; ?>" 
                       class="btn btn-warning btn-sm">
                        Update
                    </a>

                    <!-- ONLY DELETE -->
                    <a href="../php/deleteQuiz.php?id=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Are you sure?')"
                       class="btn btn-danger btn-sm">
                        Delete
                    </a>

                </div>

            </div>

        </div>

        <?php } ?>

    </div>

</div>

</body>
</html>