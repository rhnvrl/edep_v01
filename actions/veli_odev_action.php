<?php
// Hata raporlamayı aç (Geliştirme aşaması için)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/telegram_helper.php';
require_once '../includes/whatsapp_helper.php';

// Yetki kontrolü
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'veli') {
    die("Bu işleme yetkiniz yok.");
}
$veli_id = $_SESSION['kullanici_id'];
date_default_timezone_set('Europe/Istanbul');

// Gelen ID'leri hem POST hem de GET için kontrol et
$odev_id = isset($_REQUEST['odev_id']) ? intval($_REQUEST['odev_id']) : 0;
$ogrenci_id = isset($_REQUEST['ogrenci_id']) ? intval($_REQUEST['ogrenci_id']) : 0;

$redirect_url = "../panel.php?sayfa=veli_odev_detay&odev_id=$odev_id&ogrenci_id=$ogrenci_id";

// --- GÜVENLİK KONTROLLERİ ---
if ($odev_id <= 0 || $ogrenci_id <= 0) {
    $_SESSION['mesaj'] = "HATA: Geçersiz ID bilgileri.";
    $_SESSION['mesaj_tur'] = "hata";
    header("Location: ../panel.php?sayfa=odev_takibi");
    exit();
}
// Veli-Öğrenci ilişkisi kontrolü
// DÜZELTME: Sorguda var olmayan 'id' sütunu yerine 'veli_id' kullanıldı.
$stmt_veli_check = $conn->prepare("SELECT veli_id FROM veli_ogrenci_iliskisi WHERE veli_id = ? AND ogrenci_id = ?");
if ($stmt_veli_check === false) {
    die("Sorgu hazırlanamadı: " . $conn->error);
}
$stmt_veli_check->bind_param("ii", $veli_id, $ogrenci_id);
$stmt_veli_check->execute();
if ($stmt_veli_check->get_result()->num_rows == 0) {
    die("HATA: Bu öğrenci için işlem yapma yetkiniz yok.");
}
$stmt_veli_check->close();

// --- Teslimat kaydını bul veya oluştur ---
$stmt_teslim = $conn->prepare("SELECT id, durum, veli_onay_tarihi FROM odev_teslimleri WHERE odev_id = ? AND ogrenci_id = ?");
$stmt_teslim->bind_param("ii", $odev_id, $ogrenci_id);
$stmt_teslim->execute();
$teslimat = $stmt_teslim->get_result()->fetch_assoc();
$stmt_teslim->close();
$teslim_id = $teslimat['id'] ?? null;


// --- İŞLEM YÖNLENDİRME ---

// 1. DOSYA YÜKLEME
if (isset($_POST['dosya_yukle'])) {
    // Eğer teslimat yoksa, dosya yüklenirken oluştur
    if (!$teslim_id) {
        $stmt_create = $conn->prepare("INSERT INTO odev_teslimleri (odev_id, ogrenci_id, durum, teslim_tarihi) VALUES (?, ?, 'Teslim Edildi - Veli Onayı Bekliyor', NOW())");
        $stmt_create->bind_param("ii", $odev_id, $ogrenci_id);
        if (!$stmt_create->execute()) {
             $_SESSION['mesaj'] = "HATA: Yeni teslimat kaydı oluşturulamadı: " . $conn->error;
             $_SESSION['mesaj_tur'] = "hata";
             header("Location: " . $redirect_url);
             exit();
        }
        $teslim_id = $conn->insert_id;
        $stmt_create->close();
    }

    if (isset($_FILES['odev_dosyalari']) && count(array_filter($_FILES['odev_dosyalari']['name'])) > 0) {
        $upload_dir = '../uploads/odevler/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        foreach ($_FILES['odev_dosyalari']['name'] as $key => $name) {
            if ($_FILES['odev_dosyalari']['error'][$key] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['odev_dosyalari']['tmp_name'][$key];
                $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $file_name = uniqid('odev_' . $teslim_id . '_', true) . '.' . $file_extension;
                $destination = $upload_dir . $file_name;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    $dosya_yolu = 'uploads/odevler/' . $file_name;
                    $stmt_dosya = $conn->prepare("INSERT INTO odev_dosyalari (teslim_id, dosya_yolu, yukleyen_kullanici_id) VALUES (?, ?, ?)");
                    $stmt_dosya->bind_param("isi", $teslim_id, $dosya_yolu, $veli_id);
                    $stmt_dosya->execute();
                    $stmt_dosya->close();
                } else {
                    $_SESSION['mesaj'] = "HATA: Dosya sunucuya taşınamadı.";
                    $_SESSION['mesaj_tur'] = "hata";
                    header("Location: " . $redirect_url);
                    exit();
                }
            }
        }
        $_SESSION['mesaj'] = "Dosyalar başarıyla yüklendi. Şimdi ödevi onaylayabilirsiniz.";
        $_SESSION['mesaj_tur'] = "basari";
    } else {
        $_SESSION['mesaj'] = "Lütfen en az bir dosya seçin.";
        $_SESSION['mesaj_tur'] = "hata";
    }
}

// 2. ONAYLAMA
elseif (isset($_POST['onayla'])) {
    if (!$teslim_id) {
        $_SESSION['mesaj'] = "Onaylanacak bir teslimat bulunamadı. Lütfen önce öğrencinin ödevi teslim ettiğinden veya dosya yüklediğinizden emin olun.";
        $_SESSION['mesaj_tur'] = "hata";
    } else {
        $stmt_onay = $conn->prepare("UPDATE odev_teslimleri SET durum = 'Teslim Edildi - Veli Tarafından Onaylandı', veli_onay_tarihi = NOW() WHERE id = ?");
        $stmt_onay->bind_param("i", $teslim_id);
        if ($stmt_onay->execute()) {
            $_SESSION['mesaj'] = "Ödev başarıyla onaylandı ve öğretmene iletildi.";
            $_SESSION['mesaj_tur'] = "basari";
            
            // ÖĞRETMENE BİLDİRİM GÖNDER
            $sorgu = $conn->query("SELECT o.ogretmen_id, k_o.ad_soyad as ogretmen_adi, k_o.telegram_chat_id as ogretmen_tg, k_o.telefon as ogretmen_tel, k_s.ad_soyad as ogrenci_adi, o.baslik as odev_basligi FROM odev_teslimleri ot JOIN odevler o ON ot.odev_id = o.id JOIN kullanicilar k_o ON o.ogretmen_id = k_o.id JOIN kullanicilar k_s ON ot.ogrenci_id = k_s.id WHERE ot.id = $teslim_id")->fetch_assoc();
            if ($sorgu) {
                if (!empty($sorgu['ogretmen_tg'])) {
                    $mesaj = "🔔 *Ödev Onaylandı*\n\nÖğrenci: *" . $sorgu['ogrenci_adi'] . "*\nÖdev: *" . $sorgu['odev_basligi'] . "*\n\nBu ödev velisi tarafından onaylandı. Lütfen panelinizden girerek notlandırın.";
                    sendMessage($sorgu['ogretmen_tg'], $mesaj);
                }
                if (!empty($sorgu['ogretmen_tel'])) {
                    sendWhatsAppTemplateMessage($sorgu['ogretmen_tel'], 'odev_veli_onayladi', [$sorgu['ogretmen_adi'], $sorgu['ogrenci_adi'], $sorgu['odev_basligi']]);
                }
            }
        } else {
            $_SESSION['mesaj'] = "HATA: Ödev onaylanırken veritabanı hatası oluştu: " . $conn->error;
            $_SESSION['mesaj_tur'] = "hata";
        }
        $stmt_onay->close();
    }
}

// 3. REDDETME (GET isteği ile)
elseif (isset($_GET['islem']) && $_GET['islem'] === 'reddet') {
     if (!$teslim_id) {
        $_SESSION['mesaj'] = "Reddedilecek bir teslimat bulunamadı.";
        $_SESSION['mesaj_tur'] = "hata";
     } else {
        $stmt_reddet = $conn->prepare("UPDATE odev_teslimleri SET durum = 'Reddedildi', veli_onay_tarihi = NULL WHERE id = ?");
        $stmt_reddet->bind_param("i", $teslim_id);
        if ($stmt_reddet->execute()) {
            $_SESSION['mesaj'] = "Ödev reddedildi. Öğrencinin tekrar teslim etmesi gerekiyor.";
            $_SESSION['mesaj_tur'] = "basari";
        } else {
            $_SESSION['mesaj'] = "HATA: Ödev reddedilirken bir veritabanı hatası oluştu: " . $conn->error;
            $_SESSION['mesaj_tur'] = "hata";
        }
        $stmt_reddet->close();
    }
}

// Geçersiz işlem
else {
    $_SESSION['mesaj'] = "HATA: Geçersiz veya eksik işlem isteği.";
    $_SESSION['mesaj_tur'] = "hata";
}

header("Location: " . $redirect_url);
exit();
?>

