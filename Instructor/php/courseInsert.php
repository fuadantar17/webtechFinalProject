<?php
session_start();
require_once("../database/db.php");

if(isset($_POST['create_course'])){

    $title = $_POST['title'];
    $description = $_POST['description'];
    $subject_id = $_POST['subject_id'];
    $enrollment_type = $_POST['enrollment_type'];
    $max_students = $_POST['max_students'];
    $status = $_POST['status'];

    $instructor_id = $_SESSION['instructor_id'];

    $sql = "INSERT INTO courses
    (instructor_id, subject_id, title, description, enrollment_type, max_students, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iisssis",
        $instructor_id,
        $subject_id,
        $title,
        $description,
        $enrollment_type,
        $max_students,
        $status
    );

    if($stmt->execute()){
        header("Location: ../html/dashboard.php");
        exit();
    } else {
        echo "❌ Failed to Create Course";
    }

}
?>