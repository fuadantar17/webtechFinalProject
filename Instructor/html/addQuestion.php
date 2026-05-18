<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

// fetch quizzes
$sql = "SELECT id, title FROM quizzes";
$quizzes = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card p-4 shadow">

        <h3 class="text-center mb-3">Add Question</h3>

        <form action="../php/questionInsert.php" method="POST">

            <!-- QUIZ DROPDOWN -->
            <div class="mb-3">
                <label>Select Quiz</label>
                <select name="quiz_id" class="form-control" required>
                    <option value="">-- Select Quiz --</option>

                    <?php while($row = $quizzes->fetch_assoc()){ ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo $row['title']; ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <div class="mb-3">
                <label>Question</label>
                <textarea name="question" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label>Option 1</label>
                <input type="text" name="option1" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Option 2</label>
                <input type="text" name="option2" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Option 3</label>
                <input type="text" name="option3" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Option 4</label>
                <input type="text" name="option4" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Correct Option (1-4)</label>
                <input type="number" name="correct_option" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Add Question
            </button>

        </form>

    </div>

</div>

</body>
</html>