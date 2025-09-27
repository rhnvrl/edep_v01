<?php
session_start();

// Eğer kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit();
}

// Gerekli dosyaları dahil et
require_once 'includes/db_connect.php';

// Kullanıcının rolüne göre doğru paneli yükle
$rol = $_SESSION['rol'];

// $conn değişkeninin alt sayfalarda kullanılabilmesi için global yapalım
global $conn;

$panel_dosyasi = "includes/paneller/{$rol}_paneli.php";

if (file_exists($panel_dosyasi)) {
    include $panel_dosyasi;
} else {
    // Geçersiz rol durumunda güvenli bir çıkış yap
    session_destroy();
    header("Location: giris.php?hata=gecersiz_rol");
    exit();
}
?>