<?php

include("../database/db.php");

$query = "INSERT INTO courses
(instructor_id,subject_id,
title,description,
enrollment_type,
max_students,status)

VALUES(?,?,?,?,?,?,?)";

$stmt = mysqli_prepare($conn,$query);

mysqli_stmt_bind_param(

$stmt,

"iisssis",

$_SESSION['user_id'],

$_POST['subject_id'],

$_POST['title'],

$_POST['description'],

$_POST['enrollment_type'],

$_POST['max_students'],

$_POST['status']

);

if(mysqli_stmt_execute($stmt)){

echo "Course Created";

}
else{

echo "Failed";

}

?>