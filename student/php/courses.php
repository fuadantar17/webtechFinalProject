<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = (int)$_SESSION["user_id"];

/* ENROLL COURSE */
if (isset($_GET["enroll"])) {
    $course_id = (int)$_GET["enroll"];

    if ($course_id <= 0) {
        header("Location: courses.php?error=Invalid course");
        exit;
    }

    // Check course exists and active
    $courseCheck = $conn->prepare("SELECT id FROM courses WHERE id = ? AND status = 'active'");
    $courseCheck->bind_param("i", $course_id);
    $courseCheck->execute();

    if ($courseCheck->get_result()->num_rows < 1) {
        header("Location: courses.php?error=Course not found");
        exit;
    }

    // Check enrollment
    $check = $conn->prepare("SELECT id, status FROM enrollments WHERE student_id = ? AND course_id = ? LIMIT 1");
    $check->bind_param("ii", $user_id, $course_id);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();

    if ($existing) {
        if ($existing["status"] === "dropped") {
            $update = $conn->prepare("UPDATE enrollments SET status = 'active', enrolled_at = NOW() WHERE id = ?");
            $update->bind_param("i", $existing["id"]);
            $update->execute();
        }
    } else {
        $insert = $conn->prepare("INSERT INTO enrollments (student_id, course_id, status, enrolled_at) VALUES (?, ?, 'active', NOW())");
        $insert->bind_param("ii", $user_id, $course_id);
        $insert->execute();
    }

    header("Location: my_courses.php?success=Enrolled successfully");
    exit;
}

/* ACTIVE COURSES */
$stmt = $conn->prepare("
    SELECT 
        c.id,
        c.title,
        c.description,
        c.status,
        u.name AS instructor_name,
        s.name AS subject_name,
        e.status AS enrollment_status
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN subjects s ON c.subject_id = s.id
    LEFT JOIN enrollments e 
        ON e.course_id = c.id 
        AND e.student_id = ?
        AND e.status IN ('active','pending')
    WHERE c.status = 'active'
    ORDER BY c.id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include "../html/header.html";
?>

<h2>Browse Active Courses</h2>

<?php if (isset($_GET["error"])): ?>
    <div class="card" style="color:red;">
        <?php echo e($_GET["error"]); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET["success"])): ?>
    <div class="card" style="color:green;">
        <?php echo e($_GET["success"]); ?>
    </div>
<?php endif; ?>

<div class="grid-cards">
    <?php while ($course = $result->fetch_assoc()): ?>
        <div class="card">
            <h3><?php echo e($course["title"]); ?></h3>

            <p><?php echo e($course["description"]); ?></p>

            <p>
                <b>Subject:</b>
                <?php echo e($course["subject_name"] ?: "Not assigned"); ?>
            </p>

            <p>
                <b>Instructor:</b>
                <?php echo e($course["instructor_name"] ?: "Not assigned"); ?>
            </p>

            <?php if ($course["enrollment_status"]): ?>
                <p><b>Status:</b> <?php echo e($course["enrollment_status"]); ?></p>

                <a class="btn-link" href="course_detail.php?id=<?php echo e($course["id"]); ?>">
                    Open Course
                </a>
            <?php else: ?>
                <a 
                    class="btn-link" 
                    href="courses.php?enroll=<?php echo e($course["id"]); ?>"
                    onclick="return confirm('Enroll in this course?');"
                >
                    Enroll
                </a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<?php include "../html/footer.html"; ?>