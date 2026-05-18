<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];

function prepareOrDie($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error . "<br>Please import latest project.sql file.");
    }
    return $stmt;
}

/* Student Info */
$stmt = prepareOrDie($conn, "
    SELECT name, email, student_id, program, phone, profile_pic
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Student not found. Please login again.");
}

/* Active Courses */
$enrolled = prepareOrDie($conn, "
    SELECT COUNT(*) AS total
    FROM enrollments
    WHERE student_id = ? AND status = 'active'
");
$enrolled->bind_param("i", $user_id);
$enrolled->execute();
$enrolled_count = $enrolled->get_result()->fetch_assoc()["total"] ?? 0;

/* Quiz Stats */
$attempts = prepareOrDie($conn, "
    SELECT COUNT(*) AS total_attempts, COALESCE(AVG(score), 0) AS avg_score
    FROM attempts
    WHERE student_id = ?
");
$attempts->bind_param("i", $user_id);
$attempts->execute();
$stat = $attempts->get_result()->fetch_assoc();

/* Next Quiz */
$next = prepareOrDie($conn, "
    SELECT q.title
    FROM quizzes q
    JOIN enrollments e ON e.course_id = q.course_id
    WHERE e.student_id = ?
      AND e.status = 'active'
      AND q.status = 'published'
    ORDER BY q.available_from ASC
    LIMIT 1
");
$next->bind_param("i", $user_id);
$next->execute();
$next_quiz = $next->get_result()->fetch_assoc();

/* Quiz List for Leaderboard */
$quiz_list = prepareOrDie($conn, "
    SELECT q.id, q.title
    FROM quizzes q
    JOIN enrollments e ON e.course_id = q.course_id
    WHERE e.student_id = ?
      AND e.status = 'active'
      AND q.status = 'published'
    ORDER BY q.title ASC
");
$quiz_list->bind_param("i", $user_id);
$quiz_list->execute();
$quizzes = $quiz_list->get_result();

include "../html/header.html";
?>

<section class="dashboard-wrapper">

    <div class="dashboard-hero">
        <div class="profile-card">
            <img src="../pictures/<?php echo e($user['profile_pic'] ?: 'default.png'); ?>" alt="Profile Picture">

            <div class="profile-info">
                <h2>Welcome, <?php echo e($user['name']); ?></h2>
                <p><b>Student ID:</b> <?php echo e($user['student_id']); ?></p>
                <p><b>Email:</b> <?php echo e($user['email']); ?></p>
                <p><b>Program:</b> <?php echo e($user['program'] ?: 'Not added'); ?></p>
                <p><b>Phone:</b> <?php echo e($user['phone'] ?: 'Not added'); ?></p>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo e($enrolled_count); ?></h3>
            <p>Active Courses</p>
        </div>

        <div class="stat-card">
            <h3><?php echo e($stat['total_attempts'] ?? 0); ?></h3>
            <p>Quiz Attempts</p>
        </div>

        <div class="stat-card">
            <h3><?php echo number_format((float)($stat['avg_score'] ?? 0), 2); ?></h3>
            <p>Average Score</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $next_quiz ? e($next_quiz['title']) : 'No Quiz'; ?></h3>
            <p>Next Quiz</p>
        </div>
    </div>

    <div class="card leaderboard-card">
        <div class="card-header">
            <div>
                <h3>Course Leaderboard</h3>
                <p>Select a quiz to see top student scores.</p>
            </div>
        </div>

        <div class="leaderboard-controls">
            <select id="quiz_id">
                <?php if ($quizzes->num_rows > 0): ?>
                    <?php while ($row = $quizzes->fetch_assoc()): ?>
                        <option value="<?php echo e($row['id']); ?>">
                            <?php echo e($row['title']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">No quiz available</option>
                <?php endif; ?>
            </select>

            <button type="button" onclick="loadLeaderboard()">Load Leaderboard</button>
        </div>

        <div id="leaderboard" class="leaderboard-result">
            <p class="muted-text">Leaderboard result will appear here.</p>
        </div>
    </div>

</section>

<?php include "../html/footer.html"; ?>