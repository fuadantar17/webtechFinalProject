<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

$id = $_GET['id'];

$sql = "SELECT * FROM courses WHERE id=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Course Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card p-4 shadow">

        <h2><?php echo $row['title']; ?></h2>
        <p><?php echo $row['description']; ?></p>

        <p><b>Status:</b> <?php echo $row['status']; ?></p>

        <a class="btn btn-primary" href="viewCourses.php">Back</a>

    </div>

</div>

</body>
</html>