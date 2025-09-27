<?php
session_start();
require_once '../includes/db_connect.php';

// Yetki kontrolü - Sadece admin erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Bu işleme yetkiniz yok.");
}

$islem = $_POST['islem'] ?? $_GET['islem'] ?? '';
$sinif_id = $_POST['sinif_id'] ?? $_GET['sinif_id'] ?? 0;
$sinif_id = intval($sinif_id);

$redirect_url = "../panel.php?sayfa=ders_programi_yonetimi" . ($sinif_id > 0 ? "&sinif_id=" . $sinif_id : "");

// --- DERS EKLEME ---
if ($islem === 'ekle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $gun = intval($_POST['gun']);
    $baslangic_saati = $_POST['baslangic_saati'];
    $bitis_saati = $_POST['bitis_saati'];
    $brans_id = intval($_POST['brans_id']);
    $ogretmen_id = intval($_POST['ogretmen_id']);

    if ($sinif_id && $gun && $baslangic_saati && $bitis_saati && $brans_id && $ogretmen_id) {
        $stmt = $conn->prepare("INSERT INTO ders_programi (sinif_id, gun, baslangic_saati, bitis_saati, brans_id, ogretmen_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssi", $sinif_id, $gun, $baslangic_saati, $bitis_saati, $brans_id, $ogretmen_id);
        
        if ($stmt->execute()) {
            $_SESSION['mesaj'] = "Ders başarıyla eklendi.";
            $_SESSION['mesaj_tur'] = "basari";
        } else {
            $_SESSION['mesaj'] = "Ders eklenirken bir hata oluştu: " . $stmt->error;
            $_SESSION['mesaj_tur'] = "hata";
        }
        $stmt->close();
    } else {
        $_SESSION['mesaj'] = "Lütfen tüm alanları doldurun.";
        $_SESSION['mesaj_tur'] = "hata";
    }
}

// --- DERS SİLME ---
elseif ($islem === 'sil' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM ders_programi WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['mesaj'] = "Ders başarıyla silindi.";
            $_SESSION['mesaj_tur'] = "basari";
        } else {
            $_SESSION['mesaj'] = "Ders silinirken bir hata oluştu.";
            $_SESSION['mesaj_tur'] = "hata";
        }
        $stmt->close();
    } else {
        $_SESSION['mesaj'] = "Geçersiz ID.";
        $_SESSION['mesaj_tur'] = "hata";
    }
}

else {
    $_SESSION['mesaj'] = "Geçersiz işlem.";
    $_SESSION['mesaj_tur'] = "hata";
}

header("Location: " . $redirect_url);
exit();
?>