<?php
session_start();
require_once("../database/db.php");

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows == 1){

        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password_hash'])){

            // ✅ STORE ONLY BASIC INFO
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit();

        } else {
            echo "Wrong password";
        }

    } else {
        echo "User not found";
    }
}
?>

<!-- LOGIN FORM -->
<link rel="stylesheet" href="../css/style.css">

<div class="login-box">

<h2>Login</h2>

<form method="POST">
<h3>"Enter Your Email:"</h3>
<input type="email" name="email" placeholder="Email" required><br><br>

<h3>"Enter Password:"</h3>
<input type="password" name="password" placeholder="Password" required><br><br>

<button type="submit" name="login">Login</button>

</form>

</div>