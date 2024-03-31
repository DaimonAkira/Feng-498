<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST["username"]) ? $_POST["username"] : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if (empty($username) || empty($password)) {
        $_SESSION["error"] = "Username or password cannot be empty.";
        header("Location: login.php");
        exit();
    } else {
        $usersFile = "users.cfg";
        $users = file($usersFile, FILE_IGNORE_NEW_LINES);

        $validUser = false;
        $isAdmin = false;
        $isSuperAdmin = false;

        foreach ($users as $user) {
            $userInfo = explode(":", $user);
            if ($userInfo[0] === $username && $userInfo[1] === $password) {
                $validUser = true;
                $isAdmin = ($userInfo[2] == 1) ? true : false;
                $isSuperAdmin = ($userInfo[2] == 2) ? true : false;
                $_SESSION["username"] = $username;
                $_SESSION["isAdmin"] = $isAdmin;
                $_SESSION["isSuperAdmin"] = $isSuperAdmin;
                break;
            }
        }

        if ($validUser) {
            header("Location: index.php");
            exit();
        } else {
            $_SESSION["error"] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }
    }
} else {
    header("Location: index.php");
    exit();
}
?>
