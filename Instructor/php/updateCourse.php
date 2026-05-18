<?php
require_once("../database/db.php");

if(isset($_POST['id'])){

    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $sql = "UPDATE courses 
            SET title='$title', description='$description', status='$status'
            WHERE id=$id";

    if($conn->query($sql)){
        header("Location: ../html/viewCourses.php");
        exit();
    } else {
        echo "Update Failed";
    }

}
?>