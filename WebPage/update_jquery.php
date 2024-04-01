<?php
// update_jquery.php

// Formdan gelen IP ve port değerlerini al
$ip = $_POST['ip'];
$port = $_POST['port'];

// Mevcut jQuery dosyasının adı ve yolu
$existingJsFile = 'jquery.js';

// Mevcut jQuery dosyasını oku
$existingJsContent = file_get_contents($existingJsFile);

// IP ve port değerlerini değiştir
$updatedJsContent = preg_replace(
    '/const ip = ".+?";/',
    'const ip = "' . $ip . '";',
    $existingJsContent
);

$updatedJsContent = preg_replace(
    '/const port = ".+?";/',
    'const port = "' . $port . '";',
    $updatedJsContent
);

// Güncellenmiş jQuery dosyasının adı ve yolu
$updatedJsFile = 'jquery.js';

// Güncellenmiş jQuery dosyasını oluştur
file_put_contents($updatedJsFile, $updatedJsContent);

// Başarılı bir şekilde jQuery dosyası güncellendi mesajını döndür
echo "jQuery dosyası başarıyla güncellendi. Yeni IP ve Port bilgileri girildi.";
header("refresh:2;url=admin.php");
?>
