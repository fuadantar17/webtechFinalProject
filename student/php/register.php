<?php
session_start();
require_once "../database/db.php";

function redirect_error($msg) {
    header("Location: register.php?error=" . urlencode($msg));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $student_id = trim($_POST["student_id"] ?? "");
    $program = trim($_POST["program"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $student_id === "" || $program === "" || $password === "" || $confirm_password === "") {
        redirect_error("All required fields must be filled");
    }

    if (!preg_match("/^[a-zA-Z .'-]{3,100}$/", $name)) {
        redirect_error("Invalid name");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_error("Enter a valid email address");
    }

    $domain = substr(strrchr($email, "@"), 1);
    if (!$domain || !checkdnsrr($domain, "MX")) {
        redirect_error("Email domain is not valid");
    }

    if (!preg_match("/^[A-Za-z0-9-]{3,50}$/", $student_id)) {
        redirect_error("Student ID can contain letters, numbers and hyphen only");
    }

    if (!preg_match("/^[A-Za-z0-9 .-]{2,100}$/", $program)) {
        redirect_error("Invalid program");
    }

    if ($phone !== "" && !preg_match("/^(01[3-9][0-9]{8}|\+8801[3-9][0-9]{8})$/", $phone)) {
        redirect_error("Invalid Bangladeshi phone number");
    }

    if (strlen($password) < 6) {
        redirect_error("Password must be at least 6 characters");
    }

    if ($password !== $confirm_password) {
        redirect_error("Passwords do not match");
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1");
    if (!$check) {
        die("Query failed: " . $conn->error);
    }

    $check->bind_param("ss", $email, $student_id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        redirect_error("Email or Student ID already exists");
    }

    $profile_pic = "default.png";

    if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES["profile_pic"]["error"] !== UPLOAD_ERR_OK) {
            redirect_error("Profile picture upload failed");
        }

        $allowed = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp"
        ];

        $mime = mime_content_type($_FILES["profile_pic"]["tmp_name"]);

        if (!isset($allowed[$mime])) {
            redirect_error("Only JPG, PNG or WEBP image allowed");
        }

        if ($_FILES["profile_pic"]["size"] > 2 * 1024 * 1024) {
            redirect_error("Profile picture must be under 2MB");
        }

        $profile_pic = "student_" . time() . "_" . rand(1000, 9999) . "." . $allowed[$mime];

        if (!move_uploaded_file($_FILES["profile_pic"]["tmp_name"], "../pictures/" . $profile_pic)) {
            redirect_error("Could not save profile picture");
        }
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = "student";

    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, phone, role, profile_pic, student_id, program, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
    if (!$stmt) {
        die("Query failed: " . $conn->error);
    }

    $stmt->bind_param("ssssssss", $name, $email, $hash, $phone, $role, $profile_pic, $student_id, $program);

    if ($stmt->execute()) {
        header("Location: login.php?success=Registration successful. Login with Student ID");
        exit;
    }

    redirect_error("Registration failed");
}

include "../html/register.html";
?>