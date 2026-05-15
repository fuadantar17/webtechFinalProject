<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];
$course_id = (int)($_GET["course_id"] ?? 0);

$stmt = $conn->prepare("SELECT enrollment_type FROM courses WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: courses.php?error=Course not found");
    exit;
}

$status = $course["enrollment_type"] === "open" ? "active" : "pending";

$stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)");
$stmt->bind_param("iis", $user_id, $course_id, $status);
$stmt->execute();

header("Location: courses.php?success=Enrollment submitted");
exit;
?>
