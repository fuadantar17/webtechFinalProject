<?php
require_once "auth.php";
requireAdminLogin();
require_once "../database/db.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function countData($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) {
        return 0;
    }
    return $result->fetch_assoc()['c'] ?? 0;
}

/* Summary Counts */
$total_users       = countData($conn, "SELECT COUNT(*) AS c FROM users");
$total_students    = countData($conn, "SELECT COUNT(*) AS c FROM users WHERE role='student'");
$total_instructors = countData($conn, "SELECT COUNT(*) AS c FROM users WHERE role='instructor'");
$total_courses     = countData($conn, "SELECT COUNT(*) AS c FROM courses");
$total_enrollments = countData($conn, "SELECT COUNT(*) AS c FROM enrollments WHERE status='active'");
$total_quizzes     = countData($conn, "SELECT COUNT(*) AS c FROM quizzes");
$total_attempts    = countData($conn, "SELECT COUNT(*) AS c FROM attempts");
$pending_enroll    = countData($conn, "SELECT COUNT(*) AS c FROM enrollments WHERE status='pending'");

/* Recent Users */
$recent_users = $conn->query("
    SELECT name, email, role, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");

/* Recent Attempts */
$recent_attempts = $conn->query("
    SELECT 
        u.name AS student, 
        q.title AS quiz, 
        a.score, 
        a.completed_at
    FROM attempts a
    JOIN users u ON u.id = a.student_id
    JOIN quizzes q ON q.id = a.quiz_id
    ORDER BY a.completed_at DESC 
    LIMIT 5
");

include "../html/header.html";
?>

<div class="profile-card dashboard-hero">
    <div>
        <span class="badge">Admin Panel</span>
        <h2>Welcome, <?php echo e($_SESSION['name']); ?></h2>
        <p class="muted"><?php echo e($_SESSION['email']); ?></p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo e($total_users); ?></h3>
        <p>Total Users</p>
    </div>

    <div class="stat-card">
        <h3><?php echo e($total_students); ?></h3>
        <p>Students</p>
    </div>

    <div class="stat-card">
        <h3><?php echo e($total_instructors); ?></h3>
        <p>Instructors</p>
    </div>

    <div class="stat-card">
        <h3><?php echo e($total_courses); ?></h3>
        <p>Courses</p>
    </div>

    <div class="stat-card">
        <h3><?php echo e($total_enrollments); ?></h3>
        <p>Active Enrollments</p>
    </div>

    <div class="stat-card">
        <h3><?php echo e($total_quizzes); ?></h3>
        <p>Quizzes</p>
    </div>

    <div class="stat-card">
        <h3><?php echo e($total_attempts); ?></h3>
        <p>Quiz Attempts</p>
    </div>

    <div class="stat-card">
        <h3 class="<?php echo $pending_enroll > 0 ? 'text-danger' : ''; ?>">
            <?php echo e($pending_enroll); ?>
        </h3>
        <p>Pending Enrollments</p>
    </div>
</div>

<div class="card">
    <h3>Quick Actions</h3>

    <div class="quick-actions">
        <a class="btn-link" href="users.php?action=add">+ Add User</a>
        <a class="btn-link" href="courses.php?action=add">+ Add Course</a>
        <a class="btn-link" href="subjects.php?action=add">+ Add Subject</a>
        <a class="btn-link warning" href="enrollments.php?filter=pending">
            Pending Enrollments (<?php echo e($pending_enroll); ?>)
        </a>
    </div>
</div>

<div class="grid-cards">
    <div class="card">
        <h3>Recently Registered Users</h3>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($recent_users && $recent_users->num_rows > 0): ?>
                <?php while ($u = $recent_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo e($u['name']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td>
                            <span class="badge">
                                <?php echo e(ucfirst($u['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo e($u['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="empty-text">No users found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <a class="btn-link secondary" href="users.php">View All Users</a>
    </div>

    <div class="card">
        <h3>Recent Quiz Attempts</h3>

        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Quiz</th>
                    <th>Score</th>
                    <th>Completed</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($recent_attempts && $recent_attempts->num_rows > 0): ?>
                <?php while ($a = $recent_attempts->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo e($a['student']); ?></td>
                        <td><?php echo e($a['quiz']); ?></td>
                        <td>
                            <span class="badge success">
                                <?php echo e($a['score']); ?>
                            </span>
                        </td>
                        <td><?php echo e($a['completed_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="empty-text">No quiz attempts found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../html/footer.html"; ?>