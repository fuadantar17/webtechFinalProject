<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT e.status, c.id, c.title, c.description, u.name AS instructor_name,
        (SELECT q.title FROM quizzes q WHERE q.course_id = c.id AND q.status = 'published' ORDER BY q.available_from ASC LIMIT 1) AS next_quiz
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON c.instructor_id = u.id
        WHERE e.student_id = ? AND e.status IN ('active','pending')
        ORDER BY e.enrolled_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rs = $stmt->get_result();

include "../html/header.html";
?>
<h2>My Enrolled Courses</h2>
<div class="grid-cards">
<?php while ($c = $rs->fetch_assoc()): ?>
    <div class="card">
        <h3><?php echo e($c['title']); ?></h3>
        <p><?php echo e($c['description']); ?></p>
        <p><b>Instructor:</b> <?php echo e($c['instructor_name']); ?></p>
        <p><b>Status:</b> <?php echo e($c['status']); ?></p>
        <p><b>Next Quiz:</b> <?php echo e($c['next_quiz'] ?: 'No upcoming quiz'); ?></p>
        <a class="btn-link" href="course_detail.php?id=<?php echo e($c['id']); ?>">Open Course</a>
    </div>
<?php endwhile; ?>
</div>
<?php include "../html/footer.html"; ?>
