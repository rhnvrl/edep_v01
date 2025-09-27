<?php
session_start();
require_once '../includes/db_connect.php';

// Yetki kontrolü
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ogretmen') {
    die("Bu işleme yetkiniz yok.");
}
$ogretmen_id = $_SESSION['kullanici_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zoom_id = trim($_POST['zoom_id']);
    $zoom_parola = trim($_POST['zoom_parola']);

    $stmt = $conn->prepare("UPDATE kullanicilar SET zoom_id = ?, zoom_parola = ? WHERE id = ?");
    $stmt->bind_param("ssi", $zoom_id, $zoom_parola, $ogretmen_id);
    
    if ($stmt->execute()) {
        $_SESSION['mesaj'] = "Zoom bilgileri başarıyla güncellendi.";
        $_SESSION['mesaj_tur'] = "basari";
    } else {
        $_SESSION['mesaj'] = "Bilgiler güncellenirken bir hata oluştu: " . $stmt->error;
        $_SESSION['mesaj_tur'] = "hata";
    }
    $stmt->close();
} else {
    $_SESSION['mesaj'] = "Geçersiz istek.";
    $_SESSION['mesaj_tur'] = "hata";
}

header("Location: ../panel.php?sayfa=zoom_ayarlari");
exit();
?>