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


<?php
$sql = "SELECT * FROM courses WHERE id = $course_id";
$result = $conn->query($sql);
$course = $result->fetch_assoc();
?>

<div class="card">

<h2><?= $course['title'] ?></h2>
<p><?= $course['description'] ?></p>

</div>


<h2>👨‍🎓 Enrolled Students</h2>


<?php
$sql = "SELECT u.name, u.email, e.status
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        WHERE e.course_id = $course_id";

$result = $conn->query($sql);
?>











<table>

<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['status'] ?></td>
</tr>

<?php } ?>

</table>



<br><br>

<div class="button-group">



<div class="button-group">

<a class="btn quiz-btn"
href="create_quiz.php?course_id=<?= $course_id ?>">
📝 Create Practice Quiz
</a>


<a class="btn quiz-btn"
href="monitor_students.php?course_id=<?= $course_id ?>">
👨‍🎓 Monitor Students
</a>

<a class="btn quiz-btn"
href="doubt_session.php?course_id=<?= $course_id ?>">
📅 Doubt Session
</a>



<a class="btn quiz-btn"
href="course_summary.php?course_id=<?= $course_id ?>">
📊 Course Summary
</a>


</div>



<div id="quizArea"></div>


<script src="../js/main.js"></script>


<a class="btn"
href="quiz_list.php?course_id=<?= $course_id ?>">
📚 View Quizzes
</a>


