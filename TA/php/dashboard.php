<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$role = $_SESSION['role'];
?>

<link rel="stylesheet" href="../css/style.css">

<div class="container">

<h2>Welcome, <?= $name ?></h2>
<p>Role: <?= $role ?></p>

<hr>

<h1 style="text-align:center; color:white; font-weight:bold;">
    📚 Your Assigned Courses
</h1>

<?php
$sql = "SELECT c.id, c.title, c.description
        FROM courses c
        JOIN course_tas ct ON c.id = ct.course_id
        WHERE ct.ta_id = $user_id";

$result = $conn->query($sql);

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
?>

<div class="card">

    <h3><?= $row['title'] ?></h3>
    <p><?= $row['description'] ?></p>

    <a class="btn" href="course_details.php?course_id=<?= $row['id'] ?>">
        Open Course
    </a>

</div>

<?php
    }
}else{
    echo "<p>No courses assigned to you.</p>";
}
?>

</div>