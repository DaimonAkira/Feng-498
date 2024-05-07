<?php
// update_jquery.php

// Formdan gelen IP ve port değerlerini al
$ip = $_POST['ip'];
$port = $_POST['port'];
$rostop = $_POST['rostop'];

// Mevcut jQuery dosyasının adı ve yolu
$jsFile = 'jquery.js';

// Mevcut jQuery dosyasını oku
$jsContent = file_get_contents($jsFile);

// IP ve port değerlerini değiştir
$updatedJsContent = preg_replace(
    '/const ip = ".+?";/',
    'const ip = "' . $ip . '";',
    $jsContent
);

$updatedJsContent = preg_replace(
    '/const port = ".+?";/',
    'const port = "' . $port . '";',
    $updatedJsContent
);

$updatedJsContent = preg_replace(
    '/const rostop = ".+?";/',
    'const rostop = "' . $rostop . '";',
    $updatedJsContent
);

// Güncellenmiş jQuery dosyasını üzerine yaz
file_put_contents($jsFile, $updatedJsContent);

// Başarılı bir şekilde jQuery dosyası güncellendi mesajını döndür
echo "jQuery dosyası başarıyla güncellendi. Yeni IP,Port ve Topic bilgileri girildi.";
header("refresh:2;url=admin.php");
?>