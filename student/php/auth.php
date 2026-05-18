<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireStudentLogin() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
        header("Location: login.php?error=Please login as student first");
        exit;
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
