<?php
require_once "auth.php";
requireAdminLogin();
require_once "../database/db.php";

$action = $_GET['action'] ?? '';

// ── Delete course ─────────────────────────────────────────────────────────────
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: courses.php?success=Course deleted");
    exit;
}

// ── Toggle status ─────────────────────────────────────────────────────────────
if ($action === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query(
        "UPDATE courses SET status = CASE WHEN status='active' THEN 'inactive' ELSE 'active' END WHERE id = $id"
    );
    header("Location: courses.php?success=Course status updated");
    exit;
}

// ── Add / Edit ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id         = (int)($_POST['edit_id'] ?? 0);
    $subject_id      = (int)$_POST['subject_id'];
    $instructor_id   = (int)$_POST['instructor_id'];
    $title           = trim($_POST['title'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $max_students    = (int)($_POST['max_students'] ?? 50);
    $enrollment_type = $_POST['enrollment_type'] === 'approval' ? 'approval' : 'open';
    $status          = in_array($_POST['status'] ?? '', ['active','inactive','archived']) ? $_POST['status'] : 'active';

    if ($title === '' || $subject_id === 0 || $instructor_id === 0) {
        header("Location: courses.php?error=Title, subject and instructor are required");
        exit;
    }

    if ($edit_id > 0) {
        $stmt = $conn->prepare(
            "UPDATE courses SET subject_id=?, instructor_id=?, title=?, description=?, max_students=?, enrollment_type=?, status=? WHERE id=?"
        );
        $stmt->bind_param("iississi", $subject_id, $instructor_id, $title, $description, $max_students, $enrollment_type, $status, $edit_id);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO courses (subject_id, instructor_id, title, description, max_students, enrollment_type, status) VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("iississ", $subject_id, $instructor_id, $title, $description, $max_students, $enrollment_type, $status);
    }
    $stmt->execute();
    header("Location: courses.php?success=Course " . ($edit_id ? "updated" : "added") . " successfully");
    exit;
}

// ── Load edit data ────────────────────────────────────────────────────────────
$edit_course = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $s  = $conn->prepare("SELECT * FROM courses WHERE id = ? LIMIT 1");
    $s->bind_param("i", $id);
    $s->execute();
    $edit_course = $s->get_result()->fetch_assoc();
}

// ── Dropdown data ─────────────────────────────────────────────────────────────
$subjects     = $conn->query("SELECT id, name FROM subjects ORDER BY name");
$instructors  = $conn->query("SELECT id, name FROM users WHERE role='instructor' ORDER BY name");

// ── List courses ──────────────────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$filter = $_GET['status'] ?? '';
$sql    = "SELECT c.*, s.name AS subject_name, u.name AS instructor_name,
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status='active') AS enrolled_count
           FROM courses c
           JOIN subjects s ON c.subject_id = s.id
           JOIN users u ON c.instructor_id = u.id
           WHERE 1=1";
$params = []; $types = "";

if ($search !== '') {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $like = "%$search%"; $params = array_merge($params,[$like,$like]); $types .= "ss";
}
if ($filter !== '') {
    $sql .= " AND c.status = ?"; $params[] = $filter; $types .= "s";
}
$sql .= " ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$courses = $stmt->get_result();

include "../html/header.html";
?>

<?php if (!empty($_GET['success'])): ?><div class="message-success"><?php echo e($_GET['success']); ?></div><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><div class="message-error"><?php echo e($_GET['error']); ?></div><?php endif; ?>

<!-- Form -->
<div class="card">
    <h2><?php echo $edit_course ? 'Edit Course' : 'Add New Course'; ?></h2>
    <form method="POST" action="courses.php">
        <?php if ($edit_course): ?>
            <input type="hidden" name="edit_id" value="<?php echo e($edit_course['id']); ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div>
                <label>Title *</label>
                <input name="title" required value="<?php echo e($edit_course['title'] ?? ''); ?>">
            </div>
            <div>
                <label>Subject *</label>
                <select name="subject_id">
                    <?php $subjects->data_seek(0); while ($s = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo e($s['id']); ?>" <?php echo ($edit_course['subject_id'] ?? 0) == $s['id'] ? 'selected' : ''; ?>><?php echo e($s['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Instructor *</label>
                <select name="instructor_id">
                    <?php $instructors->data_seek(0); while ($ins = $instructors->fetch_assoc()): ?>
                        <option value="<?php echo e($ins['id']); ?>" <?php echo ($edit_course['instructor_id'] ?? 0) == $ins['id'] ? 'selected' : ''; ?>><?php echo e($ins['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Max Students</label>
                <input type="number" name="max_students" min="1" value="<?php echo e($edit_course['max_students'] ?? 50); ?>">
            </div>
            <div>
                <label>Enrollment Type</label>
                <select name="enrollment_type">
                    <option value="open" <?php echo ($edit_course['enrollment_type'] ?? '') === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="approval" <?php echo ($edit_course['enrollment_type'] ?? '') === 'approval' ? 'selected' : ''; ?>>Approval Required</option>
                </select>
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <?php foreach (['active','inactive','archived'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo ($edit_course['status'] ?? 'active') === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="grid-column:1/-1">
                <label>Description</label>
                <textarea name="description"><?php echo e($edit_course['description'] ?? ''); ?></textarea>
            </div>
        </div>
        <button type="submit"><?php echo $edit_course ? 'Update Course' : 'Add Course'; ?></button>
        <?php if ($edit_course): ?>
            <a class="btn-link secondary" href="courses.php" style="margin-left:8px">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- Filter -->
<div class="card">
    <form method="GET" class="inline-form" style="grid-template-columns:2fr 1fr 1fr auto">
        <input name="search" placeholder="Search courses..." value="<?php echo e($search); ?>">
        <select name="status">
            <option value="">All Status</option>
            <?php foreach (['active','inactive','archived'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $filter === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
            <?php endforeach; ?>
        </select>
        <button>Filter</button>
        <a class="btn-link secondary" href="courses.php">Reset</a>
    </form>
</div>

<!-- Table -->
<div class="card">
    <h3>All Courses</h3>
    <table>
        <thead><tr><th>#</th><th>Title</th><th>Subject</th><th>Instructor</th><th>Enrolled / Max</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php $i=1; while ($c = $courses->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo e($c['title']); ?></td>
                <td><?php echo e($c['subject_name']); ?></td>
                <td><?php echo e($c['instructor_name']); ?></td>
                <td><?php echo e($c['enrolled_count']); ?> / <?php echo e($c['max_students']); ?></td>
                <td><span class="badge"><?php echo e($c['enrollment_type']); ?></span></td>
                <td>
                    <?php
                    $badge = $c['status'] === 'active' ? 'badge success' : ($c['status'] === 'inactive' ? 'badge danger-badge' : 'badge');
                    ?>
                    <span class="<?php echo $badge; ?>"><?php echo e($c['status']); ?></span>
                </td>
                <td>
                    <a class="btn-link" style="padding:5px 10px;font-size:13px" href="courses.php?action=edit&id=<?php echo e($c['id']); ?>">Edit</a>
                    <a class="btn-link secondary" style="padding:5px 10px;font-size:13px" href="courses.php?action=toggle&id=<?php echo e($c['id']); ?>"><?php echo $c['status']==='active' ? 'Deactivate' : 'Activate'; ?></a>
                    <a class="btn-link danger" style="padding:5px 10px;font-size:13px" href="courses.php?action=delete&id=<?php echo e($c['id']); ?>" onclick="return confirm('Delete this course and all its data?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../html/footer.html"; ?>
