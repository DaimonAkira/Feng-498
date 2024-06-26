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
$welcomeMessage = "Hoş geldin, " . $_SESSION["username"] . "!";
$role = $_SESSION["isAdmin"] ? "Admin" : ($_SESSION["isSuperAdmin"] ? "Süper Admin" : "Kullanıcı");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
     <meta charset="utf-8" />
    <link rel="icon" href="data:,">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/107/three.min.js"></script>
	<script src="roslib1.min.js"></script>
	<script src="nipplejs.js"></script>
	<style>
       .likeacom {
        color: #999; /* Gri renk */
        opacity: 0.9; /* Siliklik derecesi (0 ile 1 arasında değer alır) */
   	 }
    </style>
</head>
<body>
	 <!-- Hoş geldin mesajı ve yetki düzeyi -->
    <div id="welcomeMessage">
        <p><?php echo $welcomeMessage; ?></p>
        <p>Yetki Düzeyin: <?php echo $role; ?></p>
    </div>
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
	
	<?php if ($_SESSION["isSuperAdmin"]) { ?>
    <h2>IP, Port ve Topic Değiştirme Formu</h2>
    <form action="update_jquery.php" method="post">
        <label for="ip">IP Adresi:</label>
        <input type="text" id="ip" name="ip"><br><br>
        <label for="port">Port:</label>
        <input type="text" id="port" name="port"><br><br>
		<label for="rostop">Topic:</label>
        <input type="text" id="rostop" name="rostop"><br><br>
        <input type="submit" value="Güncelle">
    </form>
		<button onclick="refreshPage()">Sayfayı Yenile</button>
    <script>
        function refreshPage() {
            location.reload(true); // Sayfayı baştan yükle (cache'i atarak)
        }
    </script>
    <!-- IP ve port bilgilerini gösterme bölümü -->
	<div class="card"><p>Ros connection status: <span id="status"></span></p></div>
    <div id="currentIpPort">
        <p>Current IP: <span id="currentIp"><?php echo $currentIp; ?></span></p>
        <p>Current Port: <span id="currentPort"><?php echo $currentPort; ?></span><span class="likeacom"> (Default Port: 9090)</span></p>
		<p>Current Topic: <span id="currentTopic"><?php echo $currentTopic; ?></span></p>
    </div>
	
	
    <!-- jQuery dosyası -->
    <script src="jquery.js"></script>
    <script>
		document.getElementById("currentIp").textContent = ip;
        document.getElementById("currentPort").textContent = port;
		document.getElementById("currentTopic").textContent = rostop;
		
		var localIpAddress = "<?php echo $_SERVER['SERVER_ADDR']; ?>";
		if (localIpAddress !== ip) {
        // İki IP adresi aynı ise
        var message = "The saved IP address (" + ip + ") does not match the local IP address (" + localIpAddress + "). Need the change saved IP address to the local IP address.";
    	} else {
        // İki IP adresi farklı ise
        var message = "The saved IP address matches the local IP address.(" + localIpAddress + ")";
    	}
		var messageElement = document.createElement("p");
		messageElement.textContent = message;
		document.getElementById("currentIpPort").appendChild(messageElement);
        $(document).ready(function() {
            // Sayfa yüklendiğinde mevcut IP ve portu göster
            var ip = "127.0.0.1"; // Örnek bir IP adresi
            var port = "8080"; // Örnek bir port numarası
			var rostop = "/cmd_vel"; // Örnek Topic
            $("#currentIp").text(ip);
            $("#currentPort").text(port);
			$("#currentTopic").text(rostop);

            // IP ve port güncelleme işlemini gerçekleştir
            $("#saveButton").click(function() {
                var newIp = $("#ip").val();
                var newPort = $("#port").val();
				var newTopic = $("#rostop").val();

                // AJAX ile update_jquery.php dosyasını çağır
                $.ajax({
                    type: "POST",
                    url: "update_jquery.php",
                    data: {ip: newIp, port: newPort, rostop: newTopic},
                    success: function(response) {
                        // Başarılı yanıt alındığında işlemleri gerçekleştir
                        alert(response);
                        // Yeniden yükleme yapabilirsiniz veya başka bir işlem yapabilirsiniz
                    },
                    error: function(xhr, status, error) {
                        // Hata durumunda kullanıcıya bilgi verilebilir
                        alert("Error updating IP,Port and Topic. " + xhr.responseText);
                    }
                });
            });
        });
    </script>
	<?php } ?>
	
	
	
    <form action="logout.php" method="post">
        <input type="submit" value="Logout">
    </form>
	<a href="index.php">Anasayfa</a>
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


