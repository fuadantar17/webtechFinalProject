<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Quiz</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/createQuiz.css">
</head>

<body>

<div class="page-wrapper">

    <div class="quiz-card">

        <!-- BACK BUTTON -->
        <a href="dashboard.php" class="back-btn">
            ⬅ Back to Dashboard
        </a>

        <h2>📝 Create Quiz</h2>

        <form action="../php/quizInsert.php" method="POST">

            <label>Course</label>
            <select name="course_id" required>
                <option value="">Select Course</option>

                <?php
                $instructor_id = $_SESSION['instructor_id'];

                $sql = "SELECT * FROM courses WHERE instructor_id=$instructor_id";
                $result = $conn->query($sql);

                while($row = $result->fetch_assoc()){
                ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo $row['title']; ?>
                    </option>
                <?php } ?>

            </select>

            <label>Quiz Title</label>
            <input type="text" name="title" placeholder="Enter Quiz Title" required>

            <label>Time Limit (minutes)</label>
            <input type="number" name="time_limit" required>

            <label>Total Marks</label>
            <input type="number" name="total_marks" required>

            <button type="submit">
                ➕ Create Quiz
            </button>

        </form>

    </div>

</div>

</body>
</html>