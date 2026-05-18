<?php
require_once "auth.php";
requireAdminLogin();
require_once "../database/db.php";

$action  = $_GET['action'] ?? '';
$msg     = '';
$msg_type = 'success';

// ── Toggle active status ──────────────────────────────────────────────────────
if ($action === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE users SET is_active = 1 - is_active WHERE id = $id");
    header("Location: users.php?success=User status updated");
    exit;
}

// ── Delete user ───────────────────────────────────────────────────────────────
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id === (int)$_SESSION['user_id']) {
        header("Location: users.php?error=You cannot delete your own account");
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: users.php?success=User deleted");
    exit;
}

// ── Add / Edit user ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id    = (int)($_POST['edit_id'] ?? 0);
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $role       = $_POST['role'] ?? 'student';
    $phone      = trim($_POST['phone'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $program    = trim($_POST['program'] ?? '');
    $password   = $_POST['password'] ?? '';

    $allowed_roles = ['student','instructor','ta','admin'];
    if (!in_array($role, $allowed_roles)) { $role = 'student'; }

    if ($name === '' || $email === '') {
        header("Location: users.php?error=Name and email are required");
        exit;
    }

    if ($edit_id > 0) {
        // UPDATE
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare(
                "UPDATE users SET name=?, email=?, role=?, phone=?, student_id=?, program=?, password_hash=? WHERE id=?"
            );
            $stmt->bind_param("sssssssi", $name, $email, $role, $phone, $student_id, $program, $hash, $edit_id);
        } else {
            $stmt = $conn->prepare(
                "UPDATE users SET name=?, email=?, role=?, phone=?, student_id=?, program=? WHERE id=?"
            );
            $stmt->bind_param("ssssssi", $name, $email, $role, $phone, $student_id, $program, $edit_id);
        }
        $stmt->execute();
        header("Location: users.php?success=User updated successfully");
        exit;
    } else {
        // INSERT
        if ($password === '') {
            header("Location: users.php?error=Password is required for new users");
            exit;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password_hash, phone, role, student_id, program) VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("sssssss", $name, $email, $hash, $phone, $role, $student_id, $program);
        if ($stmt->execute()) {
            header("Location: users.php?success=User added successfully");
        } else {
            header("Location: users.php?error=Email or Student ID already exists");
        }
        exit;
    }
}

// ── Load edit user ────────────────────────────────────────────────────────────
$edit_user = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $s  = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $s->bind_param("i", $id);
    $s->execute();
    $edit_user = $s->get_result()->fetch_assoc();
}

// ── Filters ───────────────────────────────────────────────────────────────────
$filter_role   = $_GET['role'] ?? '';
$search        = trim($_GET['search'] ?? '');
$sql           = "SELECT * FROM users WHERE 1=1";
$params        = [];
$types         = "";

if ($filter_role !== '') {
    $sql    .= " AND role = ?";
    $params[] = $filter_role;
    $types   .= "s";
}
if ($search !== '') {
    $sql    .= " AND (name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
    $like    = "%$search%";
    $params  = array_merge($params, [$like, $like, $like]);
    $types  .= "sss";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$users = $stmt->get_result();

include "../html/header.html";
?>

<?php if (!empty($_GET['success'])): ?><div class="message-success"><?php echo e($_GET['success']); ?></div><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><div class="message-error"><?php echo e($_GET['error']); ?></div><?php endif; ?>

<!-- Add / Edit Form -->
<div class="card">
    <h2><?php echo $edit_user ? 'Edit User' : 'Add New User'; ?></h2>
    <form method="POST" action="users.php">
        <?php if ($edit_user): ?>
            <input type="hidden" name="edit_id" value="<?php echo e($edit_user['id']); ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div>
                <label>Full Name *</label>
                <input name="name" required value="<?php echo e($edit_user['name'] ?? ''); ?>">
            </div>
            <div>
                <label>Email *</label>
                <input type="email" name="email" required value="<?php echo e($edit_user['email'] ?? ''); ?>">
            </div>
            <div>
                <label>Role *</label>
                <select name="role">
                    <?php foreach (['student','instructor','ta','admin'] as $r): ?>
                        <option value="<?php echo $r; ?>" <?php echo ($edit_user['role'] ?? 'student') === $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Phone</label>
                <input name="phone" value="<?php echo e($edit_user['phone'] ?? ''); ?>" placeholder="01XXXXXXXXX">
            </div>
            <div>
                <label>Student ID</label>
                <input name="student_id" value="<?php echo e($edit_user['student_id'] ?? ''); ?>" placeholder="STU-XXXX">
            </div>
            <div>
                <label>Program / Department</label>
                <input name="program" value="<?php echo e($edit_user['program'] ?? ''); ?>">
            </div>
            <div>
                <label>Password <?php echo $edit_user ? '(leave blank to keep)' : '*'; ?></label>
                <input type="password" name="password" placeholder="••••••••">
            </div>
        </div>
        <button type="submit"><?php echo $edit_user ? 'Update User' : 'Add User'; ?></button>
        <?php if ($edit_user): ?>
            <a class="btn-link secondary" href="users.php" style="margin-left:8px">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- Filter -->
<div class="card">
    <form method="GET" class="inline-form" style="grid-template-columns:2fr 1fr 1fr auto">
        <input name="search" placeholder="Search name / email / student ID" value="<?php echo e($search); ?>">
        <select name="role">
            <option value="">All Roles</option>
            <?php foreach (['student','instructor','ta','admin'] as $r): ?>
                <option value="<?php echo $r; ?>" <?php echo $filter_role === $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
            <?php endforeach; ?>
        </select>
        <button>Filter</button>
        <a class="btn-link secondary" href="users.php">Reset</a>
    </form>
</div>

<!-- Users Table -->
<div class="card">
    <h3>All Users</h3>
    <table>
        <thead>
            <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Student ID</th><th>Program</th><th>Active</th><th>Registered</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php $i = 1; while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo e($u['name']); ?></td>
                <td><?php echo e($u['email']); ?></td>
                <td><span class="badge"><?php echo e($u['role']); ?></span></td>
                <td><?php echo e($u['student_id'] ?? '–'); ?></td>
                <td><?php echo e($u['program'] ?? '–'); ?></td>
                <td>
                    <?php if ($u['is_active']): ?>
                        <span class="badge success">Active</span>
                    <?php else: ?>
                        <span class="badge danger-badge">Inactive</span>
                    <?php endif; ?>
                </td>
                <td><?php echo e(date('d M Y', strtotime($u['created_at']))); ?></td>
                <td>
                    <a class="btn-link" style="padding:5px 10px;font-size:13px" href="users.php?action=edit&id=<?php echo e($u['id']); ?>">Edit</a>
                    <a class="btn-link secondary" style="padding:5px 10px;font-size:13px" href="users.php?action=toggle&id=<?php echo e($u['id']); ?>" onclick="return confirm('Toggle active status?')">
                        <?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?>
                    </a>
                    <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                    <a class="btn-link danger" style="padding:5px 10px;font-size:13px" href="users.php?action=delete&id=<?php echo e($u['id']); ?>" onclick="return confirm('Delete this user permanently?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../html/footer.html"; ?>
