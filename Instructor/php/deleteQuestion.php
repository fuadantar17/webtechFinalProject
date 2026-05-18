<?php
require_once("../database/db.php");

$id = $_GET['id'];

$sql = "DELETE FROM quiz_questions WHERE id=$id";

if($conn->query($sql)){
    echo "Deleted Successfully";
}else{
    echo "Delete Failed";
}
?>