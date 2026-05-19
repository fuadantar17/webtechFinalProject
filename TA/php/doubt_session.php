<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'];

// CREATE SESSION
if(isset($_POST['create'])){

    $title = $_POST['title'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $duration = $_POST['duration'];

    $sql = "INSERT INTO doubt_sessions
            (course_id, title, session_date, location, duration)
            VALUES
            ($course_id, '$title', '$date', '$location', $duration)";

    $conn->query($sql);
}
?>

<link rel="stylesheet" href="../css/style.css">

<div class="container">

<h2>📅 Doubt Sessions</h2>

<!-- FORM -->
<div class="card">

<form method="POST" action="session_details.php">

<input type="text" name="title" placeholder="Session Title" required><br><br>

<input type="date" name="date" required><br><br>

<input type="text" name="location" placeholder="Location / Meeting Link" required><br><br>

<input type="number" name="duration" placeholder="Duration (minutes)" required><br><br>
<button class="btn quiz-btn" name="create">
Create Session
</button>

</form>

</div>

<hr>

<!-- SHOW SESSIONS -->
<h3>📌 All Sessions</h3>

<?php
$result = $conn->query("SELECT * FROM doubt_sessions WHERE course_id=$course_id ORDER BY id DESC");

while($row = $result->fetch_assoc()){
?>

<div class="card">

<h4><?= $row['title'] ?></h4>

<p>
📅 <?= isset($row['session_date']) ? $row['session_date'] : 'No date' ?> <br>
📍 <?= isset($row['location']) ? $row['location'] : 'No location' ?> <br>
⏳ <?= isset($row['duration']) ? $row['duration'] : '0' ?> minutes
</p>

</div>

<?php } ?>

</div>