<?php
session_start();
require_once '../includes/db_connect.php';

// Yetki kontrolü
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ogretmen') {
    die("Bu sayfaya erişim yetkiniz yok.");
}
$ogretmen_id = $_SESSION['kullanici_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $odev_id = isset($_POST['odev_id']) ? intval($_POST['odev_id']) : 0;

    if ($odev_id <= 0) {
        $_SESSION['mesaj'] = "Geçersiz ID.";
        $_SESSION['mesaj_tur'] = "hata";
        header("Location: ../panel.php?sayfa=odev_yonetimi");
        exit();
    }

    // Güvenlik: Öğretmenin sadece kendi ödevini notlayabildiğinden emin ol
    $stmt_check = $conn->prepare("SELECT id FROM odevler WHERE id = ? AND ogretmen_id = ?");
    $stmt_check->bind_param("ii", $odev_id, $ogretmen_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        $_SESSION['mesaj'] = "Bu ödevi notlandırma yetkiniz yok.";
        $_SESSION['mesaj_tur'] = "hata";
        header("Location: ../panel.php?sayfa=odev_yonetimi");
        exit();
    }
    $stmt_check->close();

    // Formdan gelen verileri al
    $teslim_idler = $_POST['teslim_id'] ?? [];
    $notlar = $_POST['notu'] ?? [];
    $yorumlar = $_POST['ogretmen_yorumu'] ?? [];

    $basarili_islem_sayisi = 0;
    
    // Her bir teslimat için güncelleme yap
    foreach ($teslim_idler as $teslim_id) {
        $teslim_id = intval($teslim_id);
        
        // Not ve yorumu al, boşsa null yap
        $not = isset($notlar[$teslim_id]) && $notlar[$teslim_id] !== '' ? intval($notlar[$teslim_id]) : null;
        $yorum = isset($yorumlar[$teslim_id]) ? trim($yorumlar[$teslim_id]) : null;

        // Sadece not girildiyse durumu "Notlandırıldı" yap
        $durum = ($not !== null) ? 'Notlandırıldı' : 'Teslim Edildi - Veli Tarafından Onaylandı';

        $stmt = $conn->prepare("UPDATE odev_teslimleri SET notu = ?, ogretmen_yorumu = ?, durum = ? WHERE id = ?");
        $stmt->bind_param("issi", $not, $yorum, $durum, $teslim_id);
        
        if ($stmt->execute()) {
            $basarili_islem_sayisi++;
        }
        $stmt->close();
    }

    if ($basarili_islem_sayisi > 0) {
        $_SESSION['mesaj'] = "Notlar başarıyla kaydedildi.";
        $_SESSION['mesaj_tur'] = "basari";
    } else {
        $_SESSION['mesaj'] = "Notları kaydederken bir sorun oluştu veya hiç değişiklik yapılmadı.";
        $_SESSION['mesaj_tur'] = "hata";
    }
    
    // İşlem sonrası aynı sayfaya geri yönlendir
    header("Location: ../panel.php?sayfa=odev_detay&odev_id=" . $odev_id);
    exit();

} else {
    // POST isteği değilse ana sayfaya yönlendir
    header("Location: ../panel.php?sayfa=odev_yonetimi");
    exit();
}
?>