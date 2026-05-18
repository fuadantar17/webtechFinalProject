<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

$quiz_id = $_GET['id'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Take Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card p-4 shadow">

<?php
$quiz = $conn->query("SELECT * FROM quizzes WHERE id=$quiz_id")->fetch_assoc();

echo "<h3>".$quiz['title']."</h3>";
echo "<p>Time: ".$quiz['time_limit_minutes']." minutes</p>";
?>

<hr>

<form action="submitQuiz.php" method="POST">

<input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">

<?php
$q = $conn->query("SELECT * FROM questions WHERE quiz_id=$quiz_id");

while($question = $q->fetch_assoc()){
    $qid = $question['id'];
?>

<div class="mb-3">

    <h5><?php echo $question['question_text']; ?></h5>

    <?php
    $opt = $conn->query("SELECT * FROM options WHERE question_id=$qid");

    while($o = $opt->fetch_assoc()){
    ?>

    <div>
        <input type="radio" name="q<?php echo $qid; ?>" value="<?php echo $o['id']; ?>">
        <?php echo $o['option_text']; ?>
    </div>

    <?php } ?>

</div>

<?php } ?>

<button type="submit" class="btn btn-primary">
    Submit Quiz
</button>

</form>

</div>

</div>

</body>
</html>