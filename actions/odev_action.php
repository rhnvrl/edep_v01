<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/telegram_helper.php';
require_once '../includes/whatsapp_helper.php';

// Yetki kontrolü
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ogretmen') {
    die("Bu sayfaya erişim yetkiniz yok.");
}
$ogretmen_id = $_SESSION['kullanici_id'];

// İşlem türünü belirle
$islem = $_POST['islem'] ?? $_GET['islem'] ?? '';

// --- ÖDEV EKLEME ---
if ($islem === 'ekle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sinif_id = intval($_POST['sinif_id']);
    $brans_id = intval($_POST['brans_id']);
    $baslik = trim($_POST['baslik']);
    $aciklama = trim($_POST['aciklama']);
    $teslim_tarihi = $_POST['teslim_tarihi'];

    $conn->begin_transaction();
    try {
        // 1. Adım: Ödevi veritabanına ekle
        $stmt_insert = $conn->prepare("INSERT INTO odevler (ogretmen_id, sinif_id, brans_id, baslik, aciklama, teslim_tarihi, olusturma_tarihi) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt_insert->bind_param("iiisss", $ogretmen_id, $sinif_id, $brans_id, $baslik, $aciklama, $teslim_tarihi);
        $stmt_insert->execute();
        $yeni_odev_id = $conn->insert_id;
        $stmt_insert->close();

        // 2. Adım: Akıllı ödev kodunu oluştur ve güncelle
        $stmt_brans = $conn->prepare("SELECT brans_adi, brans_kisaltma FROM branslar WHERE id = ?");
        $stmt_brans->bind_param("i", $brans_id);
        $stmt_brans->execute();
        $brans_result = $stmt_brans->get_result();
        $brans_data = $brans_result->fetch_assoc();
        $brans_adi = $brans_data['brans_adi'];
        $brans_kisaltma = $brans_data['brans_kisaltma'] ?? 'BRANS';
        $odev_kodu = $brans_kisaltma . $yeni_odev_id;
        $stmt_brans->close();
        
        $stmt_update = $conn->prepare("UPDATE odevler SET odev_kodu = ? WHERE id = ?");
        $stmt_update->bind_param("si", $odev_kodu, $yeni_odev_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        // 3. Adım: Bildirimleri gönder
        $ogretmen_sorgu = $conn->prepare("SELECT ad_soyad FROM kullanicilar WHERE id = ?");
        $ogretmen_sorgu->bind_param("i", $ogretmen_id);
        $ogretmen_sorgu->execute();
        $ogretmen_adi = $ogretmen_sorgu->get_result()->fetch_assoc()['ad_soyad'];
        $ogretmen_sorgu->close();
        
        $veli_sorgu = $conn->prepare("SELECT DISTINCT k.id, k.ad_soyad, k.telefon, k.telegram_chat_id FROM kullanicilar k JOIN veli_ogrenci_iliskisi voi ON k.id = voi.veli_id JOIN ogrenci_sinif_iliskisi osi ON voi.ogrenci_id = osi.ogrenci_id WHERE osi.sinif_id = ? AND k.rol = 'veli'");
        $veli_sorgu->bind_param("i", $sinif_id);
        $veli_sorgu->execute();
        $veliler = $veli_sorgu->get_result()->fetch_all(MYSQLI_ASSOC);
        $veli_sorgu->close();

        foreach ($veliler as $veli) {
            // Telegram Bildirimi
            if (!empty($veli['telegram_chat_id'])) {
                $telegram_mesaj = "🔔 *Yeni Ödev Atandı*\n\n" .
                                  "Merhaba *" . $veli['ad_soyad'] . "*,\n\n" .
                                  "*Ödev Kodu:* `" . $odev_kodu . "`\n" .
                                  "*Öğretmen:* " . $ogretmen_adi . "\n" .
                                  "*Branş:* " . $brans_adi . "\n" .
                                  "*Başlık:* " . $baslik . "\n" .
                                  "*Açıklama:* " . $aciklama . "\n" .
                                  "*Son Teslim:* " . date('d.m.Y H:i', strtotime($teslim_tarihi)) . "\n\n" .
                                  "Görsel yüklemeye başlamak için `YUKLE " . $odev_kodu . "` komutunu gönderebilirsiniz. Tüm bekleyen ödevler için ise `/ODEVLER` yazın.";
                sendMessage($veli['telegram_chat_id'], $telegram_mesaj);
            }

            // WhatsApp Bildirimi
            if (!empty($veli['telefon'])) {
                $sablon_adi = 'yeni_odev_detayli_bildirim'; 
                $parametreler = [
                    $veli['ad_soyad'],
                    $odev_kodu,
                    $ogretmen_adi,
                    $brans_adi,
                    $baslik,
                    $aciklama,
                    date('d.m.Y H:i', strtotime($teslim_tarihi))
                ];
                sendWhatsAppTemplateMessage($veli['telefon'], $sablon_adi, $parametreler);
            }
        }
        
        $conn->commit();
        $_SESSION['mesaj'] = "Ödev başarıyla eklendi ve bildirimler gönderildi.";
        $_SESSION['mesaj_tur'] = "basari";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['mesaj'] = "Bir hata oluştu: " . $e->getMessage();
        $_SESSION['mesaj_tur'] = "hata";
    }

    header("Location: ../panel.php?sayfa=odev_yonetimi");
    exit();
}
// --- ÖDEV GÜNCELLEME ---
elseif ($islem === 'guncelle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $odev_id = intval($_POST['odev_id']);
    $sinif_id = intval($_POST['sinif_id']);
    $brans_id = intval($_POST['brans_id']);
    $baslik = trim($_POST['baslik']);
    $aciklama = trim($_POST['aciklama']);
    $teslim_tarihi = $_POST['teslim_tarihi'];

    $stmt = $conn->prepare("UPDATE odevler SET sinif_id = ?, brans_id = ?, baslik = ?, aciklama = ?, teslim_tarihi = ? WHERE id = ? AND ogretmen_id = ?");
    $stmt->bind_param("iisssii", $sinif_id, $brans_id, $baslik, $aciklama, $teslim_tarihi, $odev_id, $ogretmen_id);
    
    if ($stmt->execute()) {
        $_SESSION['mesaj'] = "Ödev başarıyla güncellendi.";
        $_SESSION['mesaj_tur'] = "basari";
    } else {
        $_SESSION['mesaj'] = "Ödev güncellenirken bir hata oluştu.";
        $_SESSION['mesaj_tur'] = "hata";
    }
    $stmt->close();
    header("Location: ../panel.php?sayfa=odev_form&id=" . $odev_id);
    exit();
}
// --- ÖDEV SİLME ---
elseif ($islem === 'sil' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $odev_id = isset($_GET['odev_id']) ? intval($_GET['odev_id']) : 0;
    
    if ($odev_id > 0) {
        $stmt = $conn->prepare("DELETE FROM odevler WHERE id = ? AND ogretmen_id = ?");
        $stmt->bind_param("ii", $odev_id, $ogretmen_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['mesaj'] = "Ödev başarıyla silindi.";
            $_SESSION['mesaj_tur'] = "basari";
        } else {
            $_SESSION['mesaj'] = "Ödev silinirken bir hata oluştu veya silme yetkiniz yok.";
            $_SESSION['mesaj_tur'] = "hata";
        }
        $stmt->close();
    } else {
        $_SESSION['mesaj'] = "Geçersiz ID.";
        $_SESSION['mesaj_tur'] = "hata";
    }
    header("Location: ../panel.php?sayfa=odev_yonetimi");
    exit();
}
// --- GEÇERSİZ İŞLEM ---
else {
    $_SESSION['mesaj'] = "Geçersiz işlem isteği.";
    $_SESSION['mesaj_tur'] = "hata";
    header("Location: ../panel.php?sayfa=odev_yonetimi");
    exit();
}
?>