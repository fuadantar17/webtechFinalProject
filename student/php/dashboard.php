<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];

function prepareOrDie($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error . "<br>Please import the provided project.sql file first.");
    }
    return $stmt;
}

$stmt = prepareOrDie($conn, "SELECT name, email, student_id, program, phone, profile_pic FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$enrolled = prepareOrDie($conn, "SELECT COUNT(*) AS total FROM enrollments WHERE student_id = ? AND status = 'active'");
$enrolled->bind_param("i", $user_id);
$enrolled->execute();
$enrolled_count = $enrolled->get_result()->fetch_assoc()["total"];

$attempts = prepareOrDie($conn, "SELECT COUNT(*) AS total_attempts, COALESCE(AVG(score),0) AS avg_score FROM attempts WHERE student_id = ?");
$attempts->bind_param("i", $user_id);
$attempts->execute();
$stat = $attempts->get_result()->fetch_assoc();

$next = prepareOrDie($conn, "SELECT q.title FROM quizzes q JOIN enrollments e ON e.course_id = q.course_id WHERE e.student_id = ? AND e.status = 'active' AND q.status = 'published' ORDER BY q.available_from ASC LIMIT 1");
$next->bind_param("i", $user_id);
$next->execute();
$next_quiz = $next->get_result()->fetch_assoc();

include "../html/header.html";
?>
<div class="profile-card">
    <img src="../pictures/<?php echo e($user['profile_pic'] ?: 'default.png'); ?>" alt="Profile">
    <div>
        <h2>Welcome, <?php echo e($user['name']); ?></h2>
        <p><b>Student ID:</b> <?php echo e($user['student_id']); ?></p>
        <p><b>Email:</b> <?php echo e($user['email']); ?></p>
        <p><b>Program:</b> <?php echo e($user['program']); ?></p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><h3><?php echo e($enrolled_count); ?></h3><p>Active Courses</p></div>
    <div class="stat-card"><h3><?php echo e($stat['total_attempts']); ?></h3><p>Quiz Attempts</p></div>
    <div class="stat-card"><h3><?php echo number_format((float)$stat['avg_score'], 2); ?></h3><p>Average Score</p></div>
    <div class="stat-card"><h3><?php echo $next_quiz ? e($next_quiz['title']) : 'No Quiz'; ?></h3><p>Next Quiz</p></div>
</div>

<div class="card">
    <h3>Course Leaderboard via AJAX</h3>
    <select id="quiz_id">
        <?php
        $q = prepareOrDie($conn, "SELECT q.id, q.title FROM quizzes q JOIN enrollments e ON e.course_id = q.course_id WHERE e.student_id = ? AND e.status = 'active' AND q.status = 'published'");
        $q->bind_param("i", $user_id);
        $q->execute();
        $rs = $q->get_result();
        while ($row = $rs->fetch_assoc()) {
            echo '<option value="' . e($row['id']) . '">' . e($row['title']) . '</option>';
        }
        ?>
    </select>
    <button onclick="loadLeaderboard()">Load Leaderboard</button>
    <div id="leaderboard"></div>
</div>
<?php include "../html/footer.html"; ?>
