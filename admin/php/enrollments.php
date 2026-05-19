<?php
require_once "auth.php";
requireAdminLogin();
require_once "../database/db.php";

$action = $_GET['action'] ?? '';

// ── Update enrollment status ───────────────────────────────────────────────────
if ($action === 'approve' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE enrollments SET status='active' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: enrollments.php?success=Enrollment approved");
    exit;
    
}

if ($action === 'reject' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE enrollments SET status='rejected' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: enrollments.php?success=Enrollment rejected");
    exit;
}

if ($action === 'drop' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE enrollments SET status='dropped' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: enrollments.php?success=Student dropped from course");
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: enrollments.php?success=Enrollment deleted");
    exit;
}

// ── Filters ───────────────────────────────────────────────────────────────────
$filter = $_GET['filter'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql  = "SELECT en.*, u.name AS student_name, u.student_id AS stu_id, c.title AS course_title
         FROM enrollments en
         JOIN users u ON u.id = en.student_id
         JOIN courses c ON c.id = en.course_id
         WHERE 1=1";
$params = []; $types = "";

if ($filter !== '') {
    $sql    .= " AND en.status = ?";
    $params[] = $filter; $types .= "s";
}
if ($search !== '') {
    $sql    .= " AND (u.name LIKE ? OR c.title LIKE ? OR u.student_id LIKE ?)";
    $like    = "%$search%";
    $params  = array_merge($params, [$like, $like, $like]); $types .= "sss";
}
$sql .= " ORDER BY en.enrolled_at DESC";
$stmt = $conn->prepare($sql);
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$enrollments = $stmt->get_result();

// Counts
$counts = [];
foreach (['pending','active','dropped','rejected'] as $s) {
    $r = $conn->query("SELECT COUNT(*) AS c FROM enrollments WHERE status='$s'");
    $counts[$s] = $r->fetch_assoc()['c'];
}

include "../html/header.html";
?>

<?php if (!empty($_GET['success'])): ?><div class="message-success"><?php echo e($_GET['success']); ?></div><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><div class="message-error"><?php echo e($_GET['error']); ?></div><?php endif; ?>

<!-- Summary badges -->
<div class="stats-grid">
    <?php foreach ($counts as $st => $cnt): ?>
        <div class="stat-card">
            <h3 style="color:<?php echo $st==='pending' ? '#d97706' : ($st==='active' ? '#16a34a' : '#dc2626'); ?>"><?php echo $cnt; ?></h3>
            <p><?php echo ucfirst($st); ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filter -->
<div class="card">
    <form method="GET" class="inline-form" style="grid-template-columns:2fr 1fr 1fr auto">
        <input name="search" placeholder="Search student / course..." value="<?php echo e($search); ?>">
        <select name="filter">
            <option value="">All Status</option>
            <?php foreach (['pending','active','dropped','rejected'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $filter === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
        </select>
        <button>Filter</button>
        <a class="btn-link secondary" href="enrollments.php">Reset</a>
    </form>
</div>

<!-- Table -->
<div class="card">
    <h3>Enrollments</h3>
    <table>
        <thead><tr><th>#</th><th>Student</th><th>Student ID</th><th>Course</th><th>Status</th><th>Enrolled At</th><th>Actions</th></tr></thead>
        <tbody>
        <?php $i=1; while ($en = $enrollments->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo e($en['student_name']); ?></td>
                <td><?php echo e($en['stu_id'] ?? '–'); ?></td>
                <td><?php echo e($en['course_title']); ?></td>
                <td>
                    <?php
                    $bc = ['active'=>'badge success','pending'=>'badge','dropped'=>'badge danger-badge','rejected'=>'badge danger-badge'];
                    echo '<span class="' . ($bc[$en['status']] ?? 'badge') . '">' . e($en['status']) . '</span>';
                    ?>
                </td>
                <td><?php echo e(date('d M Y', strtotime($en['enrolled_at']))); ?></td>
                <td>
                    <?php if ($en['status'] === 'pending'): ?>
                        <a class="btn-link" style="padding:5px 10px;font-size:13px;background:#16a34a" href="enrollments.php?action=approve&id=<?php echo e($en['id']); ?>">Approve</a>
                        <a class="btn-link danger" style="padding:5px 10px;font-size:13px" href="enrollments.php?action=reject&id=<?php echo e($en['id']); ?>" onclick="return confirm('Reject this enrollment?')">Reject</a>
                    <?php elseif ($en['status'] === 'active'): ?>
                        <a class="btn-link secondary" style="padding:5px 10px;font-size:13px" href="enrollments.php?action=drop&id=<?php echo e($en['id']); ?>" onclick="return confirm('Drop this student?')">Drop</a>
                    <?php endif; ?>
                    <a class="btn-link danger" style="padding:5px 10px;font-size:13px" href="enrollments.php?action=delete&id=<?php echo e($en['id']); ?>" onclick="return confirm('Delete this enrollment record?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../html/footer.html"; ?>
