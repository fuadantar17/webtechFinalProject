<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'];
?>

<link rel="stylesheet" href="../css/style.css">

<div class="container">

<h2>❓ Q&A Questions</h2>

<?php
$sql = "SELECT q.*, u.name
        FROM qa_questions q
        JOIN users u ON q.student_id = u.id
        WHERE q.course_id = $course_id
        ORDER BY q.id DESC";

$result = $conn->query($sql);

if($result->num_rows == 0){
    echo "<p>No questions found for this course.</p>";
}

while($row = $result->fetch_assoc()){
?>

<div class="card">

<h3><?= $row['title'] ?></h3>
<p><?= $row['body'] ?></p>

<small>
Asked by: <?= $row['name'] ?> |
Date: <?= $row['created_at'] ?>
</small>

</div>

<?php } ?>

</div>




