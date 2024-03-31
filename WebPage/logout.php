<?php
// Oturumu başlat
session_start();

// Oturumu sonlandır (tüm oturum değişkenlerini sıfırlar)
session_unset();

// Oturumu yok eder
session_destroy();

// Kullanıcıyı login sayfasına yönlendir
header("Location: index.php");
exit();
?>