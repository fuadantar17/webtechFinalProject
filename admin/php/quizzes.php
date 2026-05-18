<?php
require_once "auth.php";
requireAdminLogin();
require_once "../database/db.php";

$action = $_GET['action'] ?? '';

// ── Delete quiz ───────────────────────────────────────────────────────────────
if ($action === 'delete' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: quizzes.php?success=Quiz deleted");
    exit;
}

// ── Toggle status ─────────────────────────────────────────────────────────────
if ($action === 'toggle' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $conn->query(
        "UPDATE quizzes SET status = CASE WHEN status='published' THEN 'closed' ELSE 'published' END WHERE id = $id"
    );
    
    header("Location: quizzes.php?success=Quiz status updated");
    exit;
}

// ── Add / Edit ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id            = (int)($_POST['edit_id'] ?? 0);
    $course_id          = (int)$_POST['course_id'];
    $title              = trim($_POST['title'] ?? '');
    $description        = trim($_POST['description'] ?? '');
    $quiz_type          = $_POST['quiz_type'] === 'graded' ? 'graded' : 'practice';
    $time_limit_minutes = max(1, (int)$_POST['time_limit_minutes']);
    $total_marks        = (float)$_POST['total_marks'];
    $pass_mark          = (float)$_POST['pass_mark'];
    $available_from     = $_POST['available_from'] ?? date('Y-m-d H:i:s');
    $status             = in_array($_POST['status'] ?? '', ['draft','published','closed']) ? $_POST['status'] : 'published';

    if ($title === '' || $course_id === 0) {
        header("Location: quizzes.php?error=Title and course are required");
        exit;
    }

    if ($edit_id > 0) {
        $stmt = $conn->prepare(
            "UPDATE quizzes SET course_id=?, title=?, description=?, quiz_type=?, time_limit_minutes=?,
             total_marks=?, pass_mark=?, available_from=?, status=? WHERE id=?"
        );
        $stmt->bind_param("isssiddssi", $course_id, $title, $description, $quiz_type,
            $time_limit_minutes, $total_marks, $pass_mark, $available_from, $status, $edit_id);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO quizzes (course_id, title, description, quiz_type, time_limit_minutes,
             total_marks, pass_mark, available_from, status) VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("isssiddss", $course_id, $title, $description, $quiz_type,
            $time_limit_minutes, $total_marks, $pass_mark, $available_from, $status);
    }
    $stmt->execute();
    header("Location: quizzes.php?success=Quiz " . ($edit_id ? "updated" : "added") . " successfully");
    exit;
}

// ── Load edit data ────────────────────────────────────────────────────────────
$edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $s  = $conn->prepare("SELECT * FROM quizzes WHERE id = ? LIMIT 1");
    $s->bind_param("i", $id);
    $s->execute();
    $edit = $s->get_result()->fetch_assoc();
}

$courses = $conn->query("SELECT id, title FROM courses WHERE status='active' ORDER BY title");

// ── List ──────────────────────────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$filter = $_GET['status'] ?? '';
$sql    = "SELECT q.*, c.title AS course_title,
           (SELECT COUNT(*) FROM attempts a WHERE a.quiz_id = q.id) AS attempt_count
           FROM quizzes q JOIN courses c ON c.id = q.course_id WHERE 1=1";
$params = []; $types = "";

if ($search !== '') {
    $sql .= " AND q.title LIKE ?"; $like = "%$search%"; $params[] = $like; $types .= "s";
}
if ($filter !== '') {
    $sql .= " AND q.status = ?"; $params[] = $filter; $types .= "s";
}
$sql .= " ORDER BY q.created_at DESC";
$stmt = $conn->prepare($sql);
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$quizzes = $stmt->get_result();

include "../html/header.html";
?>

<?php if (!empty($_GET['success'])): ?><div class="message-success"><?php echo e($_GET['success']); ?></div><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><div class="message-error"><?php echo e($_GET['error']); ?></div><?php endif; ?>

<!-- Form -->
<div class="card">
    <h2><?php echo $edit ? 'Edit Quiz' : 'Add New Quiz'; ?></h2>
    <form method="POST" action="quizzes.php">
        <?php if ($edit): ?>
            <input type="hidden" name="edit_id" value="<?php echo e($edit['id']); ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div>
                <label>Title *</label>
                <input name="title" required value="<?php echo e($edit['title'] ?? ''); ?>">
            </div>
            <div>
                <label>Course *</label>
                <select name="course_id">
                    <?php $courses->data_seek(0); while ($c = $courses->fetch_assoc()): ?>
                        <option value="<?php echo e($c['id']); ?>" <?php echo ($edit['course_id'] ?? 0) == $c['id'] ? 'selected' : ''; ?>><?php echo e($c['title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Quiz Type</label>
                <select name="quiz_type">
                    <option value="practice" <?php echo ($edit['quiz_type'] ?? '') === 'practice' ? 'selected' : ''; ?>>Practice</option>
                    <option value="graded" <?php echo ($edit['quiz_type'] ?? '') === 'graded' ? 'selected' : ''; ?>>Graded</option>
                </select>
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <?php foreach (['draft','published','closed'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo ($edit['status'] ?? 'published') === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Time Limit (minutes)</label>
                <input type="number" name="time_limit_minutes" min="1" value="<?php echo e($edit['time_limit_minutes'] ?? 30); ?>">
            </div>
            <div>
                <label>Total Marks</label>
                <input type="number" step="0.01" name="total_marks" value="<?php echo e($edit['total_marks'] ?? 0); ?>">
            </div>
            <div>
                <label>Pass Mark</label>
                <input type="number" step="0.01" name="pass_mark" value="<?php echo e($edit['pass_mark'] ?? 0); ?>">
            </div>
            <div>
                <label>Available From</label>
                <input type="datetime-local" name="available_from" value="<?php echo e(isset($edit['available_from']) ? date('Y-m-d\TH:i', strtotime($edit['available_from'])) : date('Y-m-d\TH:i')); ?>">
            </div>
            <div style="grid-column:1/-1">
                <label>Description</label>
                <textarea name="description"><?php echo e($edit['description'] ?? ''); ?></textarea>
            </div>
        </div>
        <button type="submit"><?php echo $edit ? 'Update Quiz' : 'Add Quiz'; ?></button>
        <?php if ($edit): ?>
            <a class="btn-link secondary" href="quizzes.php" style="margin-left:8px">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- Filter -->
<div class="card">
    <form method="GET" class="inline-form" style="grid-template-columns:2fr 1fr 1fr auto">
        <input name="search" placeholder="Search quizzes..." value="<?php echo e($search); ?>">
        <select name="status">
            <option value="">All Status</option>
            <?php foreach (['draft','published','closed'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $filter === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
        </select>
        <button>Filter</button>
        <a class="btn-link secondary" href="quizzes.php">Reset</a>
    </form>
</div>

<!-- Table -->
<div class="card">
    <h3>All Quizzes</h3>
    <table>
        <thead><tr><th>#</th><th>Title</th><th>Course</th><th>Type</th><th>Marks</th><th>Time</th><th>Attempts</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php $i=1; while ($q = $quizzes->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo e($q['title']); ?></td>
                <td><?php echo e($q['course_title']); ?></td>
                <td><span class="badge"><?php echo e($q['quiz_type']); ?></span></td>
                <td><?php echo e($q['total_marks']); ?> / <?php echo e($q['pass_mark']); ?> pass</td>
                <td><?php echo e($q['time_limit_minutes']); ?> min</td>
                <td><?php echo e($q['attempt_count']); ?></td>
                <td>
                    <?php
                    $bc = ['published'=>'badge success','draft'=>'badge','closed'=>'badge danger-badge'];
                    echo '<span class="'.($bc[$q['status']]??'badge').'">'.e($q['status']).'</span>';
                    ?>
                </td>
                <td>
                    <a class="btn-link" style="padding:5px 10px;font-size:13px" href="quizzes.php?action=edit&id=<?php echo e($q['id']); ?>">Edit</a>
                    <a class="btn-link secondary" style="padding:5px 10px;font-size:13px" href="quizzes.php?action=toggle&id=<?php echo e($q['id']); ?>"><?php echo $q['status']==='published' ? 'Close' : 'Publish'; ?></a>
                    <a class="btn-link danger" style="padding:5px 10px;font-size:13px" href="quizzes.php?action=delete&id=<?php echo e($q['id']); ?>" onclick="return confirm('Delete this quiz and all attempts?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../html/footer.html"; ?>
