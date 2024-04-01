<?php
session_start();

// Oturum kontrolü yapılır
if (!isset($_SESSION["username"])) {
    // Kullanıcı girişi yapılmamışsa, giriş sayfasına yönlendirilir
    header("Location: login.php");
    exit();
}
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
</head>
<body>
    <div class="topnav"><h1><i class="far fa-sun"></i>Ros Remote User Interface<i class="far fa-sun"></i></h1></div>
    <div class="card"><p>Ros connection status: <span id="status"></span></p></div>
    
    
	<?php if ($_SESSION["isAdmin"] || $_SESSION["isSuperAdmin"]) { ?>
    <h2>IP ve Port Değiştirme Formu</h2>
    <form action="update_jquery.php" method="post">
        <label for="ip">IP Adresi:</label>
        <input type="text" id="ip" name="ip"><br><br>
        <label for="port">Port:</label>
        <input type="text" id="port" name="port"><br><br>
        <input type="submit" value="Güncelle">
    </form>

    <!-- IP ve port bilgilerini gösterme bölümü -->
    <div id="currentIpPort">
        <p>Current IP: <span id="currentIp"><?php echo $currentIp; ?></span></p>
        <p>Current Port: <span id="currentPort"><?php echo $currentPort; ?></span></p>
    </div>

    <!-- jQuery dosyası -->
    <script src="jquery.js"></script>
    <script>
		document.getElementById("currentIp").textContent = ip;
        document.getElementById("currentPort").textContent = port;
        $(document).ready(function() {
            // Sayfa yüklendiğinde mevcut IP ve portu göster
            var ip = "127.0.0.1"; // Örnek bir IP adresi
            var port = "8080"; // Örnek bir port numarası
            $("#currentIp").text(ip);
            $("#currentPort").text(port);

            // IP ve port güncelleme işlemini gerçekleştir
            $("#saveButton").click(function() {
                var newIp = $("#ip").val();
                var newPort = $("#port").val();

                // AJAX ile update_jquery.php dosyasını çağır
                $.ajax({
                    type: "POST",
                    url: "update_jquery.php",
                    data: {ip: newIp, port: newPort},
                    success: function(response) {
                        // Başarılı yanıt alındığında işlemleri gerçekleştir
                        alert(response);
                        // Yeniden yükleme yapabilirsiniz veya başka bir işlem yapabilirsiniz
                    },
                    error: function(xhr, status, error) {
                        // Hata durumunda kullanıcıya bilgi verilebilir
                        alert("Error updating IP and port: " + xhr.responseText);
                    }
                });
            });
        });
    </script>
	<?php } ?>
    <!-- Roslib ve NippleJS dosyaları -->
    <script src="roslib.min.js"></script>
    <script src="nipplejs.js"></script>

    <!-- Three.js dosyası -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/107/three.min.js"></script>

    <!-- Joystick alanı -->
    <div id="zone_joystick"></div>

    <!-- Çıkış butonu -->
    <form action="logout.php" method="post">
        <input type="submit" value="Logout">
    </form>

    <!-- Admin veya super admin ise, admin paneline yönlendirme butonu -->
    <?php if ($_SESSION["isAdmin"] || $_SESSION["isSuperAdmin"]) { ?>
        <a href="admin.php">Admin Panel</a>
    <?php } ?>
</body>
	
</html>
