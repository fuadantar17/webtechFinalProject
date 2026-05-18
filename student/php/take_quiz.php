<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];
$quiz_id = (int)($_GET["quiz_id"] ?? $_POST["quiz_id"] ?? 0);

$q = $conn->prepare("SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id = c.id JOIN enrollments e ON e.course_id = c.id WHERE q.id = ? AND e.student_id = ? AND e.status = 'active' AND q.status = 'published'");
$q->bind_param("ii", $quiz_id, $user_id);
$q->execute();
$quiz = $q->get_result()->fetch_assoc();

if (!$quiz) {
    header("Location: my_courses.php?error=Quiz unavailable");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $is_graded = $quiz['quiz_type'] === 'graded' ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO attempts (quiz_id, student_id, score, started_at, completed_at, is_graded) VALUES (?, ?, 0, NOW(), NOW(), ?)");
    $stmt->bind_param("iii", $quiz_id, $user_id, $is_graded);
    $stmt->execute();

    $attempt_id = $conn->insert_id;
    $score = 0;

    $qs = $conn->prepare("SELECT id, marks FROM questions WHERE quiz_id = ?");
    $qs->bind_param("i", $quiz_id);
    $qs->execute();
    $qrs = $qs->get_result();

    while ($question = $qrs->fetch_assoc()) {
        $selected = (int)($_POST["q_" . $question['id']] ?? 0);

        $ans = $conn->prepare("INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES (?, ?, ?)");
        $ans->bind_param("iii", $attempt_id, $question['id'], $selected);
        $ans->execute();

        $correct = $conn->prepare("SELECT is_correct FROM options WHERE id = ? AND question_id = ?");
        $correct->bind_param("ii", $selected, $question['id']);
        $correct->execute();
        $cr = $correct->get_result()->fetch_assoc();

        if ($cr && (int)$cr['is_correct'] === 1) {
            $score += (float)$question['marks'];
        }
    }

    $up = $conn->prepare("UPDATE attempts SET score = ? WHERE id = ?");
    $up->bind_param("di", $score, $attempt_id);
    $up->execute();

    header("Location: result.php?attempt_id=" . $attempt_id);
    exit;
}

include "../html/header.html";
?>
<div class="card">
    <h2><?php echo e($quiz['title']); ?></h2>
    <p><?php echo e($quiz['description']); ?></p>
    <p><b>Time:</b> <span id="timer" data-minutes="<?php echo e($quiz['time_limit_minutes']); ?>"></span></p>

    <form method="POST" id="quizForm">
        <input type="hidden" name="quiz_id" value="<?php echo e($quiz_id); ?>">

        <?php
        $qs = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY order_index");
        $qs->bind_param("i", $quiz_id);
        $qs->execute();
        $qrs = $qs->get_result();

        while ($question = $qrs->fetch_assoc()):
        ?>
            <div class="question">
                <h3><?php echo e($question['question_text']); ?> (<?php echo e($question['marks']); ?> mark)</h3>

                <?php
                $op = $conn->prepare("SELECT id, option_text FROM options WHERE question_id = ?");
                $op->bind_param("i", $question['id']);
                $op->execute();
                $ors = $op->get_result();

                while ($o = $ors->fetch_assoc()):
                ?>
                    <label class="option">
                        <input type="radio" name="q_<?php echo e($question['id']); ?>" value="<?php echo e($o['id']); ?>" required>
                        <?php echo e($o['option_text']); ?>
                    </label>
                <?php endwhile; ?>
            </div>
        <?php endwhile; ?>

        <button>Submit Quiz</button>
    </form>
</div>
<script src="../js/quiz_timer.js"></script>
<?php include "../html/footer.html"; ?>