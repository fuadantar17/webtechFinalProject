<?php
require_once "auth.php";
requireAdminLogin();
require_once "../database/db.php";

$action = $_GET['action'] ?? '';

// ── Delete ────────────────────────────────────────────────────────────────────
if ($action === 'delete' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: subjects.php?success=Subject deleted");
    exit;
}

// ── Add / Edit ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id     = (int)($_POST['edit_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    
    if ($name === '') {
        header("Location: subjects.php?error=Subject name is required");
        exit;
    }

    if ($edit_id > 0) {
        $stmt = $conn->prepare("UPDATE subjects SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $edit_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (name, description) VALUES (?,?)");
        $stmt->bind_param("ss", $name, $description);
    }

    if ($stmt->execute()) {
        header("Location: subjects.php?success=Subject " . ($edit_id ? "updated" : "added") . " successfully");
    } else {
        header("Location: subjects.php?error=Subject name already exists");
    }
    exit;
}

// ── Load edit ─────────────────────────────────────────────────────────────────
$edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $s  = $conn->prepare("SELECT * FROM subjects WHERE id = ? LIMIT 1");
    $s->bind_param("i", $id);
    $s->execute();
    $edit = $s->get_result()->fetch_assoc();
}

// ── List ──────────────────────────────────────────────────────────────────────
$subjects = $conn->query(
    "SELECT s.*, COUNT(c.id) AS course_count
     FROM subjects s
     LEFT JOIN courses c ON c.subject_id = s.id
     GROUP BY s.id ORDER BY s.name"
);

include "../html/header.html";
?>

<?php if (!empty($_GET['success'])): ?><div class="message-success"><?php echo e($_GET['success']); ?></div><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><div class="message-error"><?php echo e($_GET['error']); ?></div><?php endif; ?>

<div class="card">
    <h2><?php echo $edit ? 'Edit Subject' : 'Add New Subject'; ?></h2>
    <form method="POST" action="subjects.php">
        <?php if ($edit): ?>
            <input type="hidden" name="edit_id" value="<?php echo e($edit['id']); ?>">
        <?php endif; ?>
        <label>Subject Name *</label>
        <input name="name" required value="<?php echo e($edit['name'] ?? ''); ?>" placeholder="e.g. Web Technology">
        <label>Description</label>
        <textarea name="description"><?php echo e($edit['description'] ?? ''); ?></textarea>
        <button type="submit"><?php echo $edit ? 'Update Subject' : 'Add Subject'; ?></button>
        <?php if ($edit): ?>
            <a class="btn-link secondary" href="subjects.php" style="margin-left:8px">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>All Subjects</h3>
    <table>
        <thead><tr><th>#</th><th>Name</th><th>Description</th><th>Courses</th><th>Actions</th></tr></thead>
        <tbody>
        <?php $i=1; while ($s = $subjects->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo e($s['name']); ?></td>
                <td><?php echo e($s['description'] ?? '–'); ?></td>
                <td><span class="badge"><?php echo e($s['course_count']); ?> courses</span></td>
                <td>
                    <a class="btn-link" style="padding:5px 10px;font-size:13px" href="subjects.php?action=edit&id=<?php echo e($s['id']); ?>">Edit</a>
                    <a class="btn-link danger" style="padding:5px 10px;font-size:13px" href="subjects.php?action=delete&id=<?php echo e($s['id']); ?>" onclick="return confirm('Delete subject? Related courses will also be deleted.')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../html/footer.html"; ?>
