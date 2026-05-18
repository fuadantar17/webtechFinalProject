<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

$quiz_id = $_GET['id'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Quiz Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card p-4 shadow">

<h3 class="text-center mb-4">Quiz Details</h3>

<?php
// quiz info
$sql = "SELECT * FROM quizzes WHERE id=$quiz_id";
$quiz = $conn->query($sql)->fetch_assoc();

echo "<h5>Title: ".$quiz['title']."</h5>";
echo "<p>Time: ".$quiz['time_limit_minutes']." min</p>";
echo "<p>Marks: ".$quiz['total_marks']."</p>";
?>

<hr>

<h5>Questions</h5>

<?php
$sql = "SELECT * FROM questions WHERE quiz_id=$quiz_id";
$questions = $conn->query($sql);

if($questions->num_rows > 0){

    while($q = $questions->fetch_assoc()){
        echo "<div class='border p-3 mb-3'>";
        echo "<b>Q: ".$q['question_text']."</b><br>";

        $qid = $q['id'];

        $opt = "SELECT * FROM options WHERE question_id=$qid";
        $options = $conn->query($opt);

        while($o = $options->fetch_assoc()){
            echo "- ".$o['option_text']."<br>";
        }

        echo "</div>";
    }

}else{
    echo "<p class='text-danger'>No Questions Found</p>";
}
?>

</div>

</div>

</body>
</html>