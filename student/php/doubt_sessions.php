<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];

if (isset($_GET['book'])) {
    $session_id = (int)$_GET['book'];

    $stmt = $conn->prepare("INSERT IGNORE INTO doubt_session_bookings (doubt_session_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();

    header("Location: doubt_sessions.php?success=Booked");
    exit;
}

include "../html/header.html";
?>
<div class="card">
    <h2>Upcoming Doubt Sessions</h2>
    <table>
        <tr><th>Course</th><th>Title</th><th>TA</th><th>Time</th><th>Location</th><th>Action</th></tr>
        <?php
        $sql = "SELECT ds.*, c.title AS course_title, u.name AS ta_name,
                (SELECT COUNT(*) FROM doubt_session_bookings b WHERE b.doubt_session_id = ds.id AND b.student_id = ?) AS mine
                FROM doubt_sessions ds
                JOIN courses c ON ds.course_id = c.id
                JOIN enrollments e ON e.course_id = c.id
                JOIN users u ON ds.ta_id = u.id
                WHERE e.student_id = ? AND e.status = 'active' AND ds.scheduled_at >= NOW()
                ORDER BY ds.scheduled_at";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $rs = $stmt->get_result();

        while ($r = $rs->fetch_assoc()) {
            echo "<tr><td>" . e($r['course_title']) . "</td><td>" . e($r['title']) . "</td><td>" . e($r['ta_name']) . "</td><td>" . e($r['scheduled_at']) . "</td><td>" . e($r['location_or_link']) . "</td><td>" . ($r['mine'] ? "Booked" : "<a href='doubt_sessions.php?book=" . e($r['id']) . "'>Book</a>") . "</td></tr>";
        }
        ?>
    </table>
</div>
<?php include "../html/footer.html"; ?>
