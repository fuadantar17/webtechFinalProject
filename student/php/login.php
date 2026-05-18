<?php
session_start();
require_once "../database/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = trim($_POST["student_id"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($student_id === "" || $password === "") {
        header("Location: login.php?error=Student ID and password are required");
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, email, password_hash, role, is_active, student_id, profile_pic FROM users WHERE student_id = ? AND role = 'student' LIMIT 1");
    if (!$stmt) {
        die("Query failed: " . $conn->error);
    }

    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        header("Location: login.php?error=Invalid Student ID or password");
        exit;
    }

    $user = $result->fetch_assoc();

    if ((int)$user["is_active"] !== 1) {
        header("Location: login.php?error=Your account is inactive");
        exit;
    }

    if (!password_verify($password, $user["password_hash"])) {
        header("Location: login.php?error=Invalid Student ID or password");
        exit;
    }

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["name"] = $user["name"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["student_id"] = $user["student_id"];
    $_SESSION["role"] = $user["role"];
    $_SESSION["profile_pic"] = $user["profile_pic"];

    header("Location: dashboard.php");
    exit;
}

include "../html/login.html";