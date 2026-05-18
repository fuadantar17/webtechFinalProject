<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];
$search = trim($_GET["search"] ?? "");
$subject = (int)($_GET["subject"] ?? 0);

$sql = "SELECT c.*, s.name AS subject_name, u.name AS instructor_name,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'active') AS enrolled_count,
        (SELECT status FROM enrollments e WHERE e.course_id = c.id AND e.student_id = ? LIMIT 1) AS my_status
        FROM courses c
        JOIN subjects s ON c.subject_id = s.id
        JOIN users u ON c.instructor_id = u.id
        WHERE c.status = 'active'";

$params = [$user_id];
$types = "i";

if ($search !== "") {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

if ($subject > 0) {
    $sql .= " AND c.subject_id = ?";
    $params[] = $subject;
    $types .= "i";
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$courses = $stmt->get_result();

include "../html/header.html";
?>
<div class="card">
    <h2>Active Courses</h2>
    <form method="GET" class="inline-form">
        <input name="search" placeholder="Search by keyword" value="<?php echo e($search); ?>">
        <select name="subject">
            <option value="0">All Subjects</option>
            <?php
            $subs = $conn->query("SELECT id, name FROM subjects ORDER BY name");
            while ($s = $subs->fetch_assoc()) {
                $selected = $subject == $s['id'] ? 'selected' : '';
                echo "<option value='" . e($s['id']) . "' $selected>" . e($s['name']) . "</option>";
            }
            ?>
        </select>
        <button>Search</button>
    </form>
</div>

<div class="grid-cards">
<?php while ($c = $courses->fetch_assoc()): ?>
    <div class="card">
        <h3><?php echo e($c['title']); ?></h3>
        <p><?php echo e($c['description']); ?></p>
        <p><b>Subject:</b> <?php echo e($c['subject_name']); ?></p>
        <p><b>Instructor:</b> <?php echo e($c['instructor_name']); ?></p>
        <p><b>Enrolled:</b> <?php echo e($c['enrolled_count']); ?>/<?php echo e($c['max_students']); ?></p>
        <p><b>Enrollment:</b> <?php echo e($c['enrollment_type']); ?></p>

        <?php if ($c['my_status']): ?>
            <span class="badge">Status: <?php echo e($c['my_status']); ?></span>
        <?php else: ?>
            <a class="btn-link" href="enroll.php?course_id=<?php echo e($c['id']); ?>">Enroll</a>
        <?php endif; ?>

        <a class="btn-link secondary" href="course_detail.php?id=<?php echo e($c['id']); ?>">Details</a>
    </div>
<?php endwhile; ?>
</div>
<?php include "../html/footer.html"; ?>
