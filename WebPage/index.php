<?php
session_start();

// Oturum kontrolü yapılır
if (!isset($_SESSION["username"])) {
    // Kullanıcı girişi yapılmamışsa, giriş sayfasına yönlendirilir
    header("Location: login.php");
    exit();
}
$welcomeMessage = "Hoş geldin, " . $_SESSION["username"] . "!";
$role = $_SESSION["isAdmin"] ? "Admin" : ($_SESSION["isSuperAdmin"] ? "Süper Admin" : "Kullanıcı");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Ros Remote User Interface</title>
    <meta charset="utf-8" />
    <link rel="icon" href="data:,">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/107/three.min.js"></script>
	<script src="roslib1.min.js"></script>
	<script src="nipplejs.js"></script>
</head>
	
<body>
    <div class="topnav"><h1><i class="far fa-sun"></i>Ros Remote User Interface<i class="far fa-sun"></i></h1></div>
	 <!-- Hoş geldin mesajı ve yetki düzeyi -->
    <div id="welcomeMessage">
        <p><?php echo $welcomeMessage; ?></p>
        <p>Yetki Düzeyin: <?php echo $role; ?></p>
    </div>
    <div class="card"><p>Ros connection status: <span id="status"></span></p></div>
    <h1>Hovercraft Controls</h1>
    <button id="startButton">Skirt Motor Start</button>
    <button id="stopButton">Skirt Motor Stop</button> 
    
    <!-- IP ve port bilgilerini gösterme bölümü -->
    <div id="currentIpPort">
        <p>Current IP: <span id="currentIp"><?php echo $currentIp; ?></span></p>
        <p>Current Port: <span id="currentPort"><?php echo $currentPort; ?></span></p>
		<p>Current Topic: <span id="currentTopic"><?php echo $currentTopic; ?></span></p>
    </div>

    <!-- jQuery dosyası -->
    <script src="jquery.js"></script>
    <script>
		document.getElementById("currentIp").textContent = ip;
        document.getElementById("currentPort").textContent = port;
        document.getElementById("currentTopic").textContent = rostop;
    </script>
    <!-- Joystick alanı -->
    <div id="zone_joystick" style="display: none;"></div>

    <!-- Çıkış butonu -->
    <form action="logout.php" method="post">
        <input type="submit" value="Logout">
    </form>
	<button onclick="refreshPage()">Sayfayı Yenile</button>
    <script>
        function refreshPage() {
            location.reload(true); // Sayfayı baştan yükle (cache'i atarak)
        }
    </script>
    <!-- Admin veya super admin ise, admin paneline yönlendirme butonu -->
    <?php if ($_SESSION["isAdmin"] || $_SESSION["isSuperAdmin"]) { ?>
        <a href="admin.php">Admin Panel</a>
    <?php } ?>
</body>
	
</html>
