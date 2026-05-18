<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Instructor Dashboard</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>Instructor</h3>

    <a href="createCourse.php"> Create Course</a>
    <a href="viewCourses.php"> View Courses</a>
    <a href="createQuiz.php"> Create Quiz</a>
    <a href="viewQuizzes.php"> View Quizzes</a>
    <a href="../php/logout.php"> Logout</a>
</div>

<!-- MAIN -->
<div class="main">

    <div class="card-box">

        <div class="welcome">
            Welcome <?php echo $_SESSION['instructor_name']; ?>
        </div>

        <div class="stat">
            Total Courses:

            <?php
            $sql = "SELECT COUNT(*) as total 
                    FROM courses 
                    WHERE instructor_id=".$_SESSION['instructor_id'];

            $result = $conn->query($sql);
            $row = $result->fetch_assoc();

            echo "<b>".$row['total']."</b>";
            ?>
        </div>

        <hr>

        <div class="text-center">

            <a class="btn btn-primary btn-custom" href="createCourse.php">Create Course</a>
            <a class="btn btn-success btn-custom" href="viewCourses.php">View Courses</a>
            <a class="btn btn-warning btn-custom" href="createQuiz.php">Create Quiz</a>
            <a class="btn btn-info btn-custom" href="viewQuizzes.php">View Quizzes</a>
            <a class="btn btn-danger btn-custom" href="../php/logout.php">Logout</a>

        </div>

    </div>

</div>

</body>
</html>