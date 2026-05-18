<?php
require_once("../database/db.php");

if(isset($_GET['id'])){

    $id = $_GET['id'];

    $sql = "DELETE FROM courses WHERE id = $id";

    if($conn->query($sql)){
        echo "Course Deleted Successfully";
    } else {
        echo "Delete Failed";
    }
}
?>