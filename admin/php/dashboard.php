<?php
require_once "auth.php";
requireAdminLogin();
require_once "../database/db.php";

function prepareOrDie($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("SQL Error: " . $conn->error); }
    return $stmt;
}

// Summary counts
$total_users       = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_students    = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'];
$total_instructors = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='instructor'")->fetch_assoc()['c'];
$total_courses     = $conn->query("SELECT COUNT(*) AS c FROM courses")->fetch_assoc()['c'];
$total_enrollments = $conn->query("SELECT COUNT(*) AS c FROM enrollments WHERE status='active'")->fetch_assoc()['c'];
$total_quizzes     = $conn->query("SELECT COUNT(*) AS c FROM quizzes")->fetch_assoc()['c'];
$total_attempts    = $conn->query("SELECT COUNT(*) AS c FROM attempts")->fetch_assoc()['c'];
$pending_enroll    = $conn->query("SELECT COUNT(*) AS c FROM enrollments WHERE status='pending'")->fetch_assoc()['c'];

// Recent users
$recent_users = $conn->query(
    "SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5"
);

// Recent attempts
$recent_attempts = $conn->query(
    "SELECT u.name AS student, q.title AS quiz, a.score, a.completed_at
     FROM attempts a
     JOIN users u ON u.id = a.student_id
     JOIN quizzes q ON q.id = a.quiz_id
     ORDER BY a.completed_at DESC LIMIT 5"
);

include "../html/header.html";
?>

<div class="profile-card">
    <div>
        <h2>Welcome, <?php echo e($_SESSION['name']); ?></h2>
        <p class="muted">Administrator &nbsp;|&nbsp; <?php echo e($_SESSION['email']); ?></p>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card"><h3><?php echo e($total_users); ?></h3><p>Total Users</p></div>
    <div class="stat-card"><h3><?php echo e($total_courses); ?></h3><p>Courses</p></div>
    <div class="stat-card"><h3><?php echo e($total_enrollments); ?></h3><p>Active Enrollments</p></div>
    <div class="stat-card"><h3><?php echo e($total_quizzes); ?></h3><p>Quizzes</p></div>
</div>
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-top:0">
    <div class="stat-card"><h3><?php echo e($total_students); ?></h3><p>Students</p></div>
    <div class="stat-card"><h3><?php echo e($total_instructors); ?></h3><p>Instructors</p></div>
    <div class="stat-card"><h3><?php echo e($total_attempts); ?></h3><p>Quiz Attempts</p></div>
    <div class="stat-card"><h3 style="color:<?php echo $pending_enroll > 0 ? '#dc2626' : '#2563eb'; ?>"><?php echo e($pending_enroll); ?></h3><p>Pending Enrollments</p></div>
</div>

<!-- Quick Actions -->
<div class="card">
    <h3>Quick Actions</h3>
    <a class="btn-link" href="users.php?action=add">+ Add User</a>
    <a class="btn-link" href="courses.php?action=add" style="margin-left:8px">+ Add Course</a>
    <a class="btn-link" href="subjects.php?action=add" style="margin-left:8px">+ Add Subject</a>
    <a class="btn-link" href="enrollments.php?filter=pending" style="margin-left:8px;background:#d97706">⏳ Pending Enrollments (<?php echo e($pending_enroll); ?>)</a>
</div>

<!-- Recent Users -->
<div class="card">
    <h3>Recently Registered Users</h3>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Registered</th></tr></thead>
        <tbody>
        <?php while ($u = $recent_users->fetch_assoc()): ?>
            <tr>
                <td><?php echo e($u['name']); ?></td>
                <td><?php echo e($u['email']); ?></td>
                <td><span class="badge"><?php echo e($u['role']); ?></span></td>
                <td><?php echo e($u['created_at']); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a class="btn-link secondary" href="users.php" style="margin-top:12px">View All Users</a>
</div>

<!-- Recent Attempts -->
<div class="card">
    <h3>Recent Quiz Attempts</h3>
    <table>
        <thead><tr><th>Student</th><th>Quiz</th><th>Score</th><th>Completed</th></tr></thead>
        <tbody>
        <?php while ($a = $recent_attempts->fetch_assoc()): ?>
            <tr>
                <td><?php echo e($a['student']); ?></td>
                <td><?php echo e($a['quiz']); ?></td>
                <td><?php echo e($a['score']); ?></td>
                <td><?php echo e($a['completed_at']); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../html/footer.html"; ?>
