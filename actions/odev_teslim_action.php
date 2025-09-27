<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/telegram_helper.php';
require_once '../includes/whatsapp_helper.php';

// Yetki kontrolü - Sadece öğrenciler erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ogrenci') {
    die("Bu işleme yetkiniz yok.");
}
$ogrenci_id = $_SESSION['kullanici_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['odev_id'])) {
    $odev_id = intval($_POST['odev_id']);

    // Güvenlik: Öğrencinin bu ödeve gerçekten atanıp atanmadığını kontrol et
    $stmt_check = $conn->prepare("SELECT o.id FROM odevler o JOIN ogrenci_sinif_iliskisi osi ON o.sinif_id = osi.sinif_id WHERE o.id = ? AND osi.ogrenci_id = ?");
    $stmt_check->bind_param("ii", $odev_id, $ogrenci_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        die("Bu ödeve erişim yetkiniz bulunmuyor.");
    }
    $stmt_check->close();

    // Adım 1: Mevcut teslimatı bul veya yeni bir tane oluştur
    $stmt_teslim = $conn->prepare("SELECT id FROM odev_teslimleri WHERE odev_id = ? AND ogrenci_id = ?");
    $stmt_teslim->bind_param("ii", $odev_id, $ogrenci_id);
    $stmt_teslim->execute();
    $teslim_result = $stmt_teslim->get_result();
    
    if ($teslim_result->num_rows > 0) {
        $teslim_id = $teslim_result->fetch_assoc()['id'];
        // Durumu ve tarihi güncelle
        $stmt_update = $conn->prepare("UPDATE odev_teslimleri SET durum = 'Teslim Edildi - Veli Onayı Bekliyor', teslim_tarihi = NOW() WHERE id = ?");
        $stmt_update->bind_param("i", $teslim_id);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Yeni teslimat kaydı oluştur
        $stmt_create = $conn->prepare("INSERT INTO odev_teslimleri (odev_id, ogrenci_id, durum) VALUES (?, ?, 'Teslim Edildi - Veli Onayı Bekliyor')");
        $stmt_create->bind_param("ii", $odev_id, $ogrenci_id);
        $stmt_create->execute();
        $teslim_id = $conn->insert_id;
        $stmt_create->close();
    }
    $stmt_teslim->close();

    // Adım 2: Dosyaları işle
    if (isset($_FILES['odev_dosyalari']) && count($_FILES['odev_dosyalari']['name']) > 0) {
        $upload_dir = '../uploads/odevler/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['odev_dosyalari']['name'] as $key => $name) {
            if ($_FILES['odev_dosyalari']['error'][$key] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['odev_dosyalari']['tmp_name'][$key];
                $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                $file_name = uniqid('odev_' . $teslim_id . '_', true) . '.' . $file_extension;
                $destination = $upload_dir . $file_name;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    $dosya_yolu = 'uploads/odevler/' . $file_name;
                    $stmt_dosya = $conn->prepare("INSERT INTO odev_dosyalari (teslim_id, dosya_yolu, yukleyen_kullanici_id) VALUES (?, ?, ?)");
                    $stmt_dosya->bind_param("isi", $teslim_id, $dosya_yolu, $ogrenci_id);
                    $stmt_dosya->execute();
                    $stmt_dosya->close();
                }
            }
        }
    }

    // Adım 3: Veli(ler)e bildirim gönder
    $ogrenci_sorgu = $conn->query("SELECT ad_soyad FROM kullanicilar WHERE id = $ogrenci_id")->fetch_assoc();
    $odev_sorgu = $conn->query("SELECT baslik, odev_kodu FROM odevler WHERE id = $odev_id")->fetch_assoc();
    $ogrenci_adi = $ogrenci_sorgu['ad_soyad'];
    $odev_basligi = $odev_sorgu['baslik'];

    $veli_sorgu = $conn->prepare("SELECT k.id, k.ad_soyad, k.telefon, k.telegram_chat_id FROM kullanicilar k JOIN veli_ogrenci_iliskisi voi ON k.id = voi.veli_id WHERE voi.ogrenci_id = ?");
    $veli_sorgu->bind_param("i", $ogrenci_id);
    $veli_sorgu->execute();
    $veliler = $veli_sorgu->get_result()->fetch_all(MYSQLI_ASSOC);
    $veli_sorgu->close();

    foreach ($veliler as $veli) {
        // Telegram Bildirimi
        if (!empty($veli['telegram_chat_id'])) {
            $telegram_mesaj = "🔔 *Ödev Teslim Edildi*\n\n" .
                              "Merhaba *" . $veli['ad_soyad'] . "*,\n" .
                              "Öğrenciniz *" . $ogrenci_adi . "*, `" . $odev_basligi . "` başlıklı ödevini teslim etti.\n\n" .
                              "Lütfen web sitemizden veya bottan girerek ödevi onaylayın.";
            sendMessage($veli['telegram_chat_id'], $telegram_mesaj);
        }

        // WhatsApp Bildirimi
        if (!empty($veli['telefon'])) {
            $sablon_adi = 'odev_onay_bekliyor';
            $parametreler = [$veli['ad_soyad'], $ogrenci_adi, $odev_basligi];
            sendWhatsAppTemplateMessage($veli['telefon'], $sablon_adi, $parametreler);
        }
    }

    $_SESSION['mesaj'] = "Ödev başarıyla teslim edildi. Velinizin onayı bekleniyor.";
    $_SESSION['mesaj_tur'] = "basari";
    header("Location: ../panel.php?sayfa=odevlerim");
    exit();
} else {
    $_SESSION['mesaj'] = "Geçersiz istek.";
    $_SESSION['mesaj_tur'] = "hata";
    header("Location: ../panel.php?sayfa=odevlerim");
    exit();
}
?>
