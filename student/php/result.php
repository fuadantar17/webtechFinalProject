<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];
$attempt_id = (int)($_GET["attempt_id"] ?? 0);

$stmt = $conn->prepare("SELECT a.*, q.title, q.pass_mark, q.total_marks FROM attempts a JOIN quizzes q ON a.quiz_id = q.id WHERE a.id = ? AND a.student_id = ?");
$stmt->bind_param("ii", $attempt_id, $user_id);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

if (!$attempt) {
    header("Location: history.php");
    exit;
}

include "../html/header.html";
?>
<div class="card">
    <h2>Quiz Result: <?php echo e($attempt['title']); ?></h2>
    <h3>Score: <?php echo e($attempt['score']); ?> / <?php echo e($attempt['total_marks']); ?></h3>
    <p><b>Status:</b> <?php echo ((float)$attempt['score'] >= (float)$attempt['pass_mark']) ? "<span class='badge success'>Pass</span>" : "<span class='badge danger-badge'>Fail</span>"; ?></p>
</div>

<div class="card">
    <h3>Question Breakdown</h3>
    <?php
    $bd = $conn->prepare("SELECT q.question_text, o.option_text, o.is_correct FROM answers an JOIN questions q ON an.question_id = q.id LEFT JOIN options o ON an.selected_option_id = o.id WHERE an.attempt_id = ?");
    $bd->bind_param("i", $attempt_id);
    $bd->execute();
    $rs = $bd->get_result();

    while ($r = $rs->fetch_assoc()) {
        echo "<div class='list-item'><b>" . e($r['question_text']) . "</b><p>Your answer: " . e($r['option_text'] ?: 'Not answered') . " " . ($r['is_correct'] ? "✅" : "❌") . "</p></div>";
    }
    ?>
</div>
<?php include "../html/footer.html"; ?>
