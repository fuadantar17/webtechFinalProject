<?php
session_start();

if(!isset($_SESSION['instructor_id'])){
    header("Location: ../html/login.php");
    exit();
}
?>