<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php
    session_start();
    if (isset($_SESSION["error"])) {
        echo "<p style='color: red;'>".$_SESSION["error"]."</p>";
        unset($_SESSION["error"]);
    }
    ?>
    <form action="authenticate.php" method="post">
        Username: <input type="text" name="username"><br>
        Password: <input type="password" name="password"><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
