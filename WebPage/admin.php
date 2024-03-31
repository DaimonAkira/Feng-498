<?php
session_start();

// Oturum kontrolü yapılır
if (!isset($_SESSION["username"]) || !($_SESSION["isAdmin"] || $_SESSION["isSuperAdmin"])) {
    // Admin veya super admin yetkisi olmayan kullanıcılar bu sayfaya erişemez
    header("Location: index.php");
    exit();
}

// "super admin" yetkisi olan kullanıcılar için kullanıcı listesi
$usersFile = "users.cfg";
$users = file($usersFile, FILE_IGNORE_NEW_LINES);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        /* CSS stilleri buraya eklenebilir */
    </style>
</head>
<body>
    <h2>Admin Panel</h2>
    <h3>Users</h3>
    <ul>
        <?php foreach ($users as $user): ?>
            <?php $userInfo = explode(":", $user); ?>
            <li>
                <?php echo $userInfo[0]; ?> (<?php echo ($userInfo[2] == 2 ? "Super Admin" : ($userInfo[2] == 1 ? "Admin" : "User")); ?>)
                <?php if ($_SESSION["isSuperAdmin"]): ?>
                    <?php if ($userInfo[2] == 0): ?>
                        <form action="" method="post">
                            <input type="hidden" name="username" value="<?php echo $userInfo[0]; ?>">
                            <button type="submit" name="makeAdmin">Make Admin</button>
                        </form>
                    <?php else: ?>
                        <form action="" method="post">
                            <input type="hidden" name="username" value="<?php echo $userInfo[0]; ?>">
                            <button type="submit" name="removeAdmin">Remove Admin</button>
                        </form>
                    <?php endif; ?>
                    <form action="" method="post">
                        <input type="hidden" name="username" value="<?php echo $userInfo[0]; ?>">
                        <button type="submit" name="removeUser">Remove User</button>
                    </form>
                <?php endif; ?>
				<?php if ($_SESSION["isAdmin"]): ?>
				<form action="" method="post">
                            <input type="hidden" name="username" value="<?php echo $userInfo[0]; ?>">
                        <button type="submit" name="removeUser">Remove User</button>
                </form>
				<?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <br>
    <!-- Yeni kullanıcı ekleme formu -->
    <?php if ($_SESSION["isSuperAdmin"] || $_SESSION["isAdmin"]): ?>
        <h3>Add New User</h3>
        <form action="" method="post">
            Username: <input type="text" name="newUsername"><br>
            Password: <input type="password" name="newPassword"><br>
            <button type="submit" name="addUser">Add User</button>
        </form>
    <?php endif; ?>
    <!-- Çıkış butonu -->
    <form action="logout.php" method="post">
        <input type="submit" value="Logout">
    </form>
</body>
</html>

<?php
// Yeni kullanıcı ekleme işlemi
if (isset($_POST["addUser"])) {
    $newUsername = isset($_POST["newUsername"]) ? $_POST["newUsername"] : "";
    $newPassword = isset($_POST["newPassword"]) ? $_POST["newPassword"] : "";
    if (!empty($newUsername) && !empty($newPassword)) {
        $usersFile = "users.cfg";
        $users = file($usersFile, FILE_IGNORE_NEW_LINES);
        $usernameExists = false; // Kullanıcı adının daha önce kullanılıp kullanılmadığını kontrol etmek için bayrak
        foreach ($users as $user) {
            $userInfo = explode(":", $user);
            if ($userInfo[0] === $newUsername) {
                $usernameExists = true; // Kullanıcı adı mevcut
                break;
            }
        }
        if (!$usernameExists) {
            // Yeni kullanıcıyı ekle
            $newUser = $newUsername . ":" . $newPassword . ":0";
            file_put_contents($usersFile, $newUser . PHP_EOL, FILE_APPEND);
            // Yeniden yönlendirme yapar
            header("Location: admin.php");
            exit();
        } else {
            echo "<p style='color: red;'>Username already exists.</p>";
        }
    }
}

// Kullanıcı kaldırma işlemi
if (isset($_POST["removeUser"])) {
    $removeUsername = isset($_POST["username"]) ? $_POST["username"] : "";
    if (!empty($removeUsername)) {
        $usersFile = "users.cfg";
        $users = file($usersFile, FILE_IGNORE_NEW_LINES);
        $adminFound = false;
        foreach ($users as $index => $user) {
            $userInfo = explode(":", $user);
            if ($userInfo[0] === $removeUsername) {
                if ($_SESSION["isSuperAdmin"] || ($_SESSION["isAdmin"] && !$userInfo[2])) {
                    // Süper admin kendi hesabını silemez
                    if ($_SESSION["isSuperAdmin"] && $_SESSION["username"] !== $removeUsername && $userInfo[2] != 2) {
                        unset($users[$index]); // Kullanıcıyı listeden kaldır
                        file_put_contents($usersFile, implode(PHP_EOL, $users) . PHP_EOL);
                        // Yeniden yönlendirme yapar
                        header("Location: admin.php");
                        exit();
                    } elseif ($_SESSION["isAdmin"] && !$userInfo[2]) {
                        unset($users[$index]); // Kullanıcıyı listeden kaldır
                        file_put_contents($usersFile, implode(PHP_EOL, $users) . PHP_EOL);
                        // Yeniden yönlendirme yapar
                        header("Location: admin.php");
                        exit();
                    } else {
                        echo "<p style='color: red;'>You cannot remove this user.</p>";
                        exit();
                    }
                } else {
                    $adminFound = true;
                }
            }
        }
        // Eğer kullanıcı bulunamazsa
        if (!$adminFound) {
            echo "<p style='color: red;'>User not found.</p>";
        } else {
            echo "<p style='color: red;'>You cannot remove this user.</p>";
        }
    }
}


// Admin yetkisi verme işlemi
if (isset($_POST["makeAdmin"])) {
    $adminUsername = isset($_POST["username"]) ? $_POST["username"] : "";
    if (!empty($adminUsername)) {
        $usersFile = "users.cfg";
        $users = file($usersFile, FILE_IGNORE_NEW_LINES);
        foreach ($users as $index => $user) {
            $userInfo = explode(":", $user);
            if ($userInfo[0] === $adminUsername && $userInfo[2] == 0) {
                // Super admin kendi yetkilerini alamaz
                if ($_SESSION["isSuperAdmin"] && $_SESSION["username"] !== $adminUsername) {
                    $userInfo[2] = 1; // Admin yetkisi verilir
                    $users[$index] = implode(":", $userInfo);
                    file_put_contents($usersFile, implode(PHP_EOL, $users) . PHP_EOL);
                    // Yeniden yönlendirme yapar
                    header("Location: admin.php");
                    exit();
                } else {
                    echo "<p style='color: red;'>You cannot modify your own permissions.</p>";
                }
            }
        }
    }
}

// Admin yetkisini kaldırma işlemi
if (isset($_POST["removeAdmin"])) {
    $adminUsername = isset($_POST["username"]) ? $_POST["username"] : "";
    if (!empty($adminUsername)) {
        $usersFile = "users.cfg";
        $users = file($usersFile, FILE_IGNORE_NEW_LINES);
        foreach ($users as $index => $user) {
            $userInfo = explode(":", $user);
            if ($userInfo[0] === $adminUsername && $userInfo[2] == 1) {
                // Süper admin kendi yetkilerini alamaz
                if ($_SESSION["isSuperAdmin"] && $_SESSION["username"] !== $adminUsername) {
                    $userInfo[2] = 0; // Admin yetkisi kaldırılır
                    $users[$index] = implode(":", $userInfo);
                    file_put_contents($usersFile, implode(PHP_EOL, $users) . PHP_EOL);
                    // Yeniden yönlendirme yapar
                    header("Location: admin.php");
                    exit();
                } else {
                    echo "<p style='color: red;'>You cannot modify your own permissions.</p>";
                    exit();
                }
            }
        }
        // Süper admin kendi hesabını silmeye çalışırsa uyarı ver
        if ($_SESSION["isSuperAdmin"] && $_SESSION["username"] === $adminUsername) {
            echo "<p style='color: red;'>You cannot modify your own permissions.</p>";
            exit();
        }
    }
}


?>