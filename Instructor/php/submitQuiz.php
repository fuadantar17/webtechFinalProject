<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

$quiz_id = $_POST['quiz_id'];
$total_score = 0;


$questions = $conn->query("SELECT * FROM questions WHERE quiz_id=$quiz_id");

while($q = $questions->fetch_assoc()){

    $qid = $q['id'];

   
    if(isset($_POST["q$qid"])){

        $selected_option = $_POST["q$qid"];

        
        $result = $conn->query("SELECT * FROM options WHERE id=$selected_option");
        $opt = $result->fetch_assoc();

        if($opt['is_correct'] == 1){
            $total_score += $q['marks'];
        }
    }
}

// result show
?>

<!DOCTYPE html>
<html>

<head>
    <title>Quiz Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card p-4 shadow text-center">

    <h2>Quiz Completed </h2>

    <h3>Your Score:</h3>

    <h1 class="text-success">
        <?php echo $total_score; ?>
    </h1>

    <a href="quizList.php" class="btn btn-primary mt-3">
        Back to Quizzes
    </a>

</div>

</div>

</body>
</html>