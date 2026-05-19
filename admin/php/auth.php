<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAdminLogin() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: login.php?error=Please login as admin first");
        exit;
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

?>
