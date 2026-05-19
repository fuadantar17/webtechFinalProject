<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$quiz_id = $_GET['quiz_id'];
?>

<link rel="stylesheet" href="../css/style.css">

<div class="container">

<h2>📘 Manage Question Bank</h2>


<?php
$q = $conn->query("SELECT * FROM quizzes WHERE id=$quiz_id");
$quiz = $q->fetch_assoc();
?>

<div class="card">
<h3><?= $quiz['title'] ?></h3>
<p>Type: <?= $quiz['quiz_type'] ?></p>
</div>


<?php
$sql = "SELECT * FROM questions WHERE quiz_id=$quiz_id";
$result = $conn->query($sql);
?>



<?php while($row = $result->fetch_assoc()){ ?>

<div class="card">

<h3><?= $row['question_text'] ?></h3>

<a class="btn"
href="edit_question.php?id=<?= $row['id'] ?>&quiz_id=<?= $quiz_id ?>">
✏ Edit
</a>

<a class="btn danger"
href="delete_question.php?id=<?= $row['id'] ?>&quiz_id=<?= $quiz_id ?>">
🗑 Delete
</a>

</div>

<?php } ?>

<a class="btn quiz-btn"
href="add_question.php?quiz_id=<?= $quiz_id ?>">
➕ Add Question
</a>

</div>



