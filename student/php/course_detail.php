<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];
$course_id = (int)($_GET["id"] ?? 0);

$check = $conn->prepare("SELECT status FROM enrollments WHERE student_id = ? AND course_id = ? AND status IN ('active','pending')");
$check->bind_param("ii", $user_id, $course_id);
$check->execute();

if ($check->get_result()->num_rows < 1) {
    header("Location: courses.php?error=Enroll first");
    exit;
}

$stmt = $conn->prepare("SELECT c.*, u.name AS instructor_name, s.name AS subject_name,
        (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') FROM course_tas ct JOIN users t ON ct.ta_id = t.id WHERE ct.course_id = c.id) AS tas
        FROM courses c
        JOIN users u ON c.instructor_id = u.id
        JOIN subjects s ON c.subject_id = s.id
        WHERE c.id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

include "../html/header.html";
?>
<div class="card">
    <h2><?php echo e($course['title']); ?></h2>
    <p><?php echo e($course['description']); ?></p>
    <p><b>Subject:</b> <?php echo e($course['subject_name']); ?></p>
    <p><b>Instructor:</b> <?php echo e($course['instructor_name']); ?></p>
    <p><b>Assigned TA:</b> <?php echo e($course['tas'] ?: 'Not assigned'); ?></p>
    <a class="btn-link danger" href="drop_course.php?course_id=<?php echo e($course_id); ?>" onclick="return confirm('Drop this course?')">Drop Course</a>
</div>

<div class="card">
    <h3>Announcements</h3>
    <?php
    $a = $conn->prepare("SELECT title, body, created_at FROM announcements WHERE course_id = ? ORDER BY created_at DESC");
    $a->bind_param("i", $course_id);
    $a->execute();
    $ars = $a->get_result();
    while ($row = $ars->fetch_assoc()) {
        echo "<div class='list-item'><b>" . e($row['title']) . "</b><p>" . e($row['body']) . "</p><small>" . e($row['created_at']) . "</small></div>";
    }
    ?>
</div>

<div class="card">
    <h3>Materials</h3>
    <?php
    $m = $conn->prepare("SELECT title, file_path, material_type FROM course_materials WHERE course_id = ?");
    $m->bind_param("i", $course_id);
    $m->execute();
    $mrs = $m->get_result();
    while ($row = $mrs->fetch_assoc()) {
        echo "<div class='list-item'><b>" . e($row['title']) . "</b> <span class='badge'>" . e($row['material_type']) . "</span><br><a href='" . e($row['file_path']) . "' target='_blank'>Open / Download</a></div>";
    }
    ?>
</div>

<div class="card">
    <h3>Published Quizzes</h3>
    <?php
    $q = $conn->prepare("SELECT id, title, quiz_type, time_limit_minutes, total_marks FROM quizzes WHERE course_id = ? AND status = 'published'");
    $q->bind_param("i", $course_id);
    $q->execute();
    $qrs = $q->get_result();

    while ($row = $qrs->fetch_assoc()) {
        echo "<div class='list-item'><b>" . e($row['title']) . "</b> <span class='badge'>" . e($row['quiz_type']) . "</span><p>Time: " . e($row['time_limit_minutes']) . " minutes | Marks: " . e($row['total_marks']) . "</p><a class='btn-link' href='take_quiz.php?quiz_id=" . e($row['id']) . "'>Take Quiz</a></div>";
    }
    ?>
</div>

<a class="btn-link" href="qa.php?course_id=<?php echo e($course_id); ?>">Open Q&A Board</a>
<?php include "../html/footer.html"; ?>
