<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "quiz_platform";

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database
);

if($conn->connect_error){

    die("Database Connection Failed");

}

?>