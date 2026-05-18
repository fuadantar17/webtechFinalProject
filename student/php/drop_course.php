<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];
$course_id = (int)($_GET["course_id"] ?? 0);

$check = $conn->prepare("SELECT COUNT(*) AS total FROM attempts a JOIN quizzes q ON a.quiz_id = q.id WHERE a.student_id = ? AND q.course_id = ? AND a.is_graded = 1");
$check->bind_param("ii", $user_id, $course_id);
$check->execute();
$total = $check->get_result()->fetch_assoc()["total"];

if ($total > 0) {
    header("Location: course_detail.php?id=$course_id&error=Cannot drop after graded quiz completed");
    exit;
}

$stmt = $conn->prepare("UPDATE enrollments SET status = 'dropped' WHERE student_id = ? AND course_id = ?");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();

header("Location: my_courses.php?success=Course dropped");
exit;
?>
