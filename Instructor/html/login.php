<!DOCTYPE html>
<html>

<head>
    <title>Instructor Login</title>

    <!-- CSS FILE LINK -->
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>

    <form action="../php/loginCheck.php" method="POST">

        <h2>Instructor Login</h2>

        <input type="email" name="email" placeholder="Enter Email" required>

        <input type="password" name="password" placeholder="Enter Password" required>

        <button type="submit" name="login">Login</button>

    </form>

</body>
</html>