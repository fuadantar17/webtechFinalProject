<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT a.*, q.title, q.total_marks, q.quiz_type, c.title AS course_title FROM attempts a JOIN quizzes q ON a.quiz_id = q.id JOIN courses c ON q.course_id = c.id WHERE a.student_id = ? ORDER BY a.completed_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rs = $stmt->get_result();

include "../html/header.html";
?>
<div class="card">
    <h2>Attempt History</h2>
    <table>
        <tr><th>Course</th><th>Quiz</th><th>Type</th><th>Score</th><th>Completed</th></tr>
        <?php while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?php echo e($r['course_title']); ?></td>
                <td><?php echo e($r['title']); ?></td>
                <td><?php echo e($r['quiz_type']); ?></td>
                <td><?php echo e($r['score']); ?>/<?php echo e($r['total_marks']); ?></td>
                <td><?php echo e($r['completed_at']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php include "../html/footer.html"; ?>