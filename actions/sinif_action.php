<?php
session_start();
require_once '../includes/db_connect.php';

// Sadece admin erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Yetkisiz erişim.");
}

// Ekleme ve Güncelleme İşlemi
if (isset($_POST['kaydet'])) {
    $sinif_id = (int)$_POST['sinif_id'];
    $sinif_adi = trim($_POST['sinif_adi']);
    $seviye = (int)$_POST['seviye'];

    if (empty($sinif_adi) || empty($seviye)) {
        $_SESSION['form_hatasi_sinif'] = "Sınıf adı ve seviyesi boş bırakılamaz.";
    } else {
        if ($sinif_id > 0) { // Güncelleme
            $stmt = $conn->prepare("UPDATE siniflar SET sinif_adi = ?, seviye = ? WHERE id = ?");
            $stmt->bind_param("sii", $sinif_adi, $seviye, $sinif_id);
            $_SESSION['form_mesaji_sinif'] = "Sınıf başarıyla güncellendi.";
        } else { // Ekleme
            $stmt = $conn->prepare("INSERT INTO siniflar (sinif_adi, seviye) VALUES (?, ?)");
            $stmt->bind_param("si", $sinif_adi, $seviye);
            $_SESSION['form_mesaji_sinif'] = "Sınıf başarıyla eklendi.";
        }

        if (!$stmt->execute()) {
            $_SESSION['form_hatasi_sinif'] = "İşlem sırasında bir hata oluştu: " . $stmt->error;
            unset($_SESSION['form_mesaji_sinif']);
        }
        $stmt->close();
    }
}

// Silme İşlemi
if (isset($_GET['delete_id'])) {
    $sinif_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM siniflar WHERE id = ?");
    $stmt->bind_param("i", $sinif_id);
    if ($stmt->execute()) {
        $_SESSION['form_mesaji_sinif'] = "Sınıf başarıyla silindi.";
    } else {
        $_SESSION['form_hatasi_sinif'] = "Sınıf silinirken bir hata oluştu.";
    }
    $stmt->close();
}

$conn->close();
header("Location: ../panel.php?sayfa=siniflar");
exit();
?>
