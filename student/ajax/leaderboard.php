<?php
require_once "../php/auth.php";
requireStudentLogin();
require_once "../database/db.php";

header("Content-Type: application/json");

$quiz_id = (int)($_GET["quiz_id"] ?? 0);

if ($quiz_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Quiz required"]);
    exit;
}

$sql = "SELECT u.name, u.student_id, MAX(a.score) AS best_score
        FROM attempts a
        JOIN users u ON a.student_id = u.id
        WHERE a.quiz_id = ?
        GROUP BY u.id, u.name, u.student_id
        ORDER BY best_score DESC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();

$rs = $stmt->get_result();
$data = [];

while ($row = $rs->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["status" => "success", "data" => $data]);
?>
