<?php

session_start();

require_once("../database/db.php");

if(isset($_POST['login'])){

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT *
            FROM users
            WHERE email = ?
            AND role = 'instructor'
            AND is_active = 1";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows == 1){

        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password_hash'])){

            // session data
            $_SESSION['instructor_id'] = $user['id'];
            $_SESSION['instructor_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            header("Location: ../html/dashboard.php");
            exit();

        }else{

            echo "Wrong Password";

        }

    }else{

        echo "Instructor Not Found";

    }

}
?>