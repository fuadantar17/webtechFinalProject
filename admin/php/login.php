<?php
session_start();
require_once "../database/db.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        header("Location: login.php?error=Email and password are required");
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT id, name, email, password_hash, role, is_active, profile_pic
         FROM users WHERE email = ? AND role = 'admin' LIMIT 1"
    );
    if (!$stmt) { die("Query failed: " . $conn->error); }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        header("Location: login.php?error=Invalid email or password");
        exit;
    }

    $user = $result->fetch_assoc();

    if ((int)$user["is_active"] !== 1) {
        header("Location: login.php?error=Your account is inactive");
        exit;
    }

    if (!password_verify($password, $user["password_hash"])) {
        header("Location: login.php?error=Invalid email or password");
        exit;
    }

    $_SESSION["user_id"]    = $user["id"];
    $_SESSION["name"]       = $user["name"];
    $_SESSION["email"]      = $user["email"];
    $_SESSION["role"]       = $user["role"];
    $_SESSION["profile_pic"] = $user["profile_pic"];

    header("Location: dashboard.php");
    exit;
}

include "../html/login.html";
?>
