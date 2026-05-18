<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Available Quizzes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card p-4 shadow">

    <h3 class="text-center mb-4">Available Quizzes</h3>

    <table class="table table-bordered">

        <tr>
            <th>Title</th>
            <th>Time</th>
            <th>Marks</th>
            <th>Action</th>
        </tr>

        <?php
        $sql = "SELECT * FROM quizzes";
        $result = $conn->query($sql);

        while($row = $result->fetch_assoc()){
        ?>

        <tr>
            <td><?php echo $row['title']; ?></td>
            <td><?php echo $row['time_limit_minutes']; ?> min</td>
            <td><?php echo $row['total_marks']; ?></td>

            <td>
                <a class="btn btn-success btn-sm"
                   href="takeQuiz.php?id=<?php echo $row['id']; ?>">
                    Start Quiz
                </a>
            </td>

        </tr>

        <?php } ?>

    </table>

</div>

</div>

</body>
</html>