<?php
session_start();
require_once '../includes/db_connect.php';

// Yetki kontrolü - Sadece öğretmenler erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ogretmen') {
    die("Bu işleme yetkiniz yok.");
}
$ogretmen_id = $_SESSION['kullanici_id'];

// İşlem türünü belirle
$islem = $_POST['islem'] ?? $_GET['islem'] ?? '';

// --- ANKET SİLME ---
if ($islem === 'sil' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $anket_id = isset($_GET['anket_id']) ? intval($_GET['anket_id']) : 0;
    if ($anket_id > 0) {
        $stmt = $conn->prepare("DELETE FROM anketler WHERE id = ? AND ogretmen_id = ?");
        $stmt->bind_param("ii", $anket_id, $ogretmen_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['mesaj'] = "Anket başarıyla silindi.";
            $_SESSION['mesaj_tur'] = "basari";
        } else {
            $_SESSION['mesaj'] = "Anket silinirken bir hata oluştu veya bu işleme yetkiniz yok.";
            $_SESSION['mesaj_tur'] = "hata";
        }
        $stmt->close();
    } else {
        $_SESSION['mesaj'] = "Geçersiz Anket ID'si.";
        $_SESSION['mesaj_tur'] = "hata";
    }
    header("Location: ../panel.php?sayfa=anket_yonetimi");
    exit();
}

// --- ANKET EKLEME ---
if ($islem === 'ekle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sinif_id = isset($_POST['sinif_id']) ? intval($_POST['sinif_id']) : 0;
    $anket_basligi = isset($_POST['anket_basligi']) ? trim($_POST['anket_basligi']) : ''; // DÜZELTME
    $secenekler = isset($_POST['secenekler']) ? $_POST['secenekler'] : [];

    if (empty($sinif_id) || empty($anket_basligi) || count($secenekler) < 2) {
        $_SESSION['mesaj'] = "Lütfen tüm alanları doldurun ve en az 2 seçenek ekleyin.";
        $_SESSION['mesaj_tur'] = "hata";
        header("Location: ../panel.php?sayfa=anket_form");
        exit();
    }

    $stmt_anket = $conn->prepare("INSERT INTO anketler (ogretmen_id, sinif_id, anket_basligi) VALUES (?, ?, ?)"); // DÜZELTME
    $stmt_anket->bind_param("iis", $ogretmen_id, $sinif_id, $anket_basligi);
    
    if ($stmt_anket->execute()) {
        $yeni_anket_id = $conn->insert_id;
        $stmt_anket->close();

        $stmt_secenek = $conn->prepare("INSERT INTO anket_secenekleri (anket_id, secenek_metni) VALUES (?, ?)");
        foreach ($secenekler as $secenek) {
            $secenek_metni = trim($secenek);
            if (!empty($secenek_metni)) {
                $stmt_secenek->bind_param("is", $yeni_anket_id, $secenek_metni);
                $stmt_secenek->execute();
            }
        }
        $stmt_secenek->close();
        $_SESSION['mesaj'] = "Anket başarıyla oluşturuldu.";
        $_SESSION['mesaj_tur'] = "basari";
    } else {
        $_SESSION['mesaj'] = "Anket oluşturulurken bir hata oluştu: " . $stmt_anket->error;
        $_SESSION['mesaj_tur'] = "hata";
    }
    
    header("Location: ../panel.php?sayfa=anket_yonetimi");
    exit();
}

$_SESSION['mesaj'] = "Geçersiz işlem isteği.";
$_SESSION['mesaj_tur'] = "hata";
header("Location: ../panel.php?sayfa=anket_yonetimi");
exit();
?>