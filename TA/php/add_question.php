<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$quiz_id = $_GET['quiz_id'];

if(isset($_POST['save_question'])){

    $question = $_POST['question'];
    $marks = $_POST['marks'];

    // 1. Insert Question
    $sql = "INSERT INTO questions (quiz_id, question_text, marks)
            VALUES ($quiz_id, '$question', $marks)";

    $conn->query($sql);

    $question_id = $conn->insert_id;

    // 2. Insert Options
    $a = $_POST['option_a'];
    $b = $_POST['option_b'];
    $c = $_POST['option_c'];
    $d = $_POST['option_d'];
    $correct = $_POST['correct'];

    function insertOption($conn, $qid, $text, $is_correct){
        $conn->query("INSERT INTO options (question_id, option_text, is_correct)
                      VALUES ($qid, '$text', $is_correct)");
    }

    insertOption($conn, $question_id, $a, ($correct=='A'?1:0));
    insertOption($conn, $question_id, $b, ($correct=='B'?1:0));
    insertOption($conn, $question_id, $c, ($correct=='C'?1:0));
    insertOption($conn, $question_id, $d, ($correct=='D'?1:0));

    header("Location: manage_questions.php?quiz_id=$quiz_id");
    exit();
}
?>

<link rel="stylesheet" href="../css/style.css">

<div class="container">

<h2>➕ Add Question</h2>

<div class="card">

<form method="POST">

<textarea name="question" placeholder="Enter Question" required></textarea><br><br>

<input type="number" name="marks" placeholder="Marks" value="1" required><br><br>

<input type="text" name="option_a" placeholder="Option A" required><br><br>
<input type="text" name="option_b" placeholder="Option B" required><br><br>
<input type="text" name="option_c" placeholder="Option C" required><br><br>
<input type="text" name="option_d" placeholder="Option D" required><br><br>

<select name="correct" required>
    <option value="A">Correct Answer A</option>
    <option value="B">Correct Answer B</option>
    <option value="C">Correct Answer C</option>
    <option value="D">Correct Answer D</option>
</select><br><br>

<button class="btn quiz-btn" name="save_question">
Save Question
</button>

</form>

</div>

</div>










