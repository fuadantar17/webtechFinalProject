<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT COALESCE(AVG(a.score),0) AS avg_score, COUNT(*) AS attempts, SUM(CASE WHEN a.score >= q.pass_mark THEN 1 ELSE 0 END) AS passed FROM attempts a JOIN quizzes q ON a.quiz_id = q.id WHERE a.student_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();

include "../html/header.html";
?>
<div class="stats-grid">
    <div class="stat-card"><h3><?php echo number_format($s['avg_score'], 2); ?></h3><p>Average Score</p></div>
    <div class="stat-card"><h3><?php echo e($s['attempts']); ?></h3><p>Total Attempts</p></div>
    <div class="stat-card"><h3><?php echo $s['attempts'] ? number_format(($s['passed'] / $s['attempts']) * 100, 2) . '%' : '0%'; ?></h3><p>Pass Rate</p></div>
</div>

<div class="card">
    <h3>Average Score Per Subject</h3>
    <table>
        <tr><th>Subject</th><th>Your Average</th><th>Class Average</th></tr>
        <?php
        $q = $conn->prepare("SELECT sub.name, AVG(a.score) AS my_avg, (SELECT AVG(a2.score) FROM attempts a2 JOIN quizzes q2 ON a2.quiz_id = q2.id JOIN courses c2 ON q2.course_id = c2.id WHERE c2.subject_id = sub.id) AS class_avg FROM attempts a JOIN quizzes qz ON a.quiz_id = qz.id JOIN courses c ON qz.course_id = c.id JOIN subjects sub ON c.subject_id = sub.id WHERE a.student_id = ? GROUP BY sub.id");
        $q->bind_param("i", $user_id);
        $q->execute();
        $rs = $q->get_result();

        while ($r = $rs->fetch_assoc()) {
            echo "<tr><td>" . e($r['name']) . "</td><td>" . number_format($r['my_avg'], 2) . "</td><td>" . number_format($r['class_avg'], 2) . "</td></tr>";
        }
        ?>
    </table>
</div>
<?php include "../html/footer.html"; ?>
