<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'];

// INSERT MATERIAL (if submitted)
if(isset($_POST['upload'])){

    $title = $_POST['title'];
    $type = $_POST['type'];
    $file = $_POST['file_path'];

    $sql = "INSERT INTO course_materials (course_id, title, file_path, material_type)
            VALUES ($course_id, '$title', '$file', '$type')";

    $conn->query($sql);
}
?>

<link rel="stylesheet" href="../css/style.css">

<div class="container">

<h2>📚 Study Materials</h2>

<!-- 🔵 ADD MATERIAL FORM -->
<div class="card">

<form method="POST">

<input type="