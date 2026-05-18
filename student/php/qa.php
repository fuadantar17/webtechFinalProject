<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];
$course_id = (int)($_GET["course_id"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $body = trim($_POST["body"] ?? "");

    if ($title !== "" && $body !== "") {
        $stmt = $conn->prepare("INSERT INTO qa_questions (course_id, student_id, title, body) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $course_id, $user_id, $title, $body);
        $stmt->execute();
    }

    header("Location: qa.php?course_id=" . $course_id);
    exit;
}

if (isset($_GET["resolve"])) {
    $qid = (int)$_GET["resolve"];
    $stmt = $conn->prepare("UPDATE qa_questions SET is_resolved = 1 WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $qid, $user_id);
    $stmt->execute();

    header("Location: qa.php?course_id=" . $course_id);
    exit;
}

include "../html/header.html";
?>
<div class="card">
    <h2>Course Q&A Board</h2>
    <form method="POST">
        <label>Question Title</label>
        <input name="title" required>

        <label>Question Body</label>
        <textarea name="body" required></textarea>

        <button>Post Question</button>
    </form>
</div>

<?php
$stmt = $conn->prepare("SELECT q.*, u.name FROM qa_questions q JOIN users u ON q.student_id = u.id WHERE q.course_id = ? ORDER BY q.created_at DESC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$rs = $stmt->get_result();

while ($q = $rs->fetch_assoc()):
?>
<div class="card">
    <h3><?php echo e($q['title']); ?> <?php echo $q['is_resolved'] ? "<span class='badge'>Resolved</span>" : ""; ?></h3>
    <p><?php echo e($q['body']); ?></p>
    <small>By <?php echo e($q['name']); ?></small>

    <?php if ($q['student_id'] == $user_id && !$q['is_resolved']): ?>
        <br><a class="btn-link secondary" href="qa.php?course_id=<?php echo e($course_id); ?>&resolve=<?php echo e($q['id']); ?>">Mark Resolved</a>
    <?php endif; ?>

    <?php
    $ans = $conn->prepare("SELECT a.body, a.is_endorsed, u.name, u.role FROM qa_answers a JOIN users u ON a.author_id = u.id WHERE a.qa_question_id = ?");
    $ans->bind_param("i", $q['id']);
    $ans->execute();
    $ansrs = $ans->get_result();

    while ($a = $ansrs->fetch_assoc()) {
        echo "<div class='answer'><b>" . e($a['name']) . " (" . e($a['role']) . ")</b> " . ($a['is_endorsed'] ? "<span class='badge'>Endorsed</span>" : "") . "<p>" . e($a['body']) . "</p></div>";
    }
    ?>
</div>
<?php endwhile; ?>
<?php include "../html/footer.html"; ?>