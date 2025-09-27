<?php
// Bu dosya, sunucudaki Cron Job tarafından otomatik olarak çalıştırılacaktır.
// Zaman Dilimini Ayarla (ÇOK ÖNEMLİ)
date_default_timezone_set('Europe/Istanbul');

// Projenin ana dizin yolunu belirle
$project_root = dirname(__DIR__);

// Gerekli dosyaları dahil et
require_once $project_root . '/includes/db_connect.php';
require_once $project_root . '/includes/telegram_helper.php';
require_once $project_root . '/includes/whatsapp_helper.php';

// --- Gerekli Bilgileri Hesapla ---
$bugun_gun_no = date('N'); // 1 (Pazartesi) - 7 (Pazar)
$simdiki_zaman_str = date('H:i:00');
$hedef_zaman_str = date('H:i:00', strtotime('+30 minutes'));

// --- Hatırlatılacak Dersleri Bul ---
$sql = "
    SELECT dp.id, dp.sinif_id, dp.baslangic_saati, b.brans_adi, k.ad_soyad as ogretmen_adi
    FROM ders_programi dp
    JOIN branslar b ON dp.brans_id = b.id
    JOIN kullanicilar k ON dp.ogretmen_id = k.id
    WHERE dp.gun = ? 
      AND (dp.son_hatirlatma_tarihi IS NULL OR dp.son_hatirlatma_tarihi != CURDATE())
";

// Gece yarısını geçen zaman dilimlerini doğru hesapla
if ($hedef_zaman_str < $simdiki_zaman_str) {
    // Örn: şimdiki zaman 23:45, hedef 00:15. Bu durumda 23:45'ten BÜYÜK VEYA 00:15'ten KÜÇÜK olanları al.
    $sql .= " AND (dp.baslangic_saati > ? OR dp.baslangic_saati <= ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $bugun_gun_no, $simdiki_zaman_str, $hedef_zaman_str);
} else {
    // Örn: şimdiki zaman 14:00, hedef 14:30. Normal aralık sorgusu.
    $sql .= " AND dp.baslangic_saati > ? AND dp.baslangic_saati <= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $bugun_gun_no, $simdiki_zaman_str, $hedef_zaman_str);
}

$stmt->execute();
$dersler = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (count($dersler) == 0) {
    exit; // Hatırlatılacak ders yoksa scripti sessizce sonlandır
}

// --- Her Ders İçin Veli Bildirimlerini Gönder ---
foreach ($dersler as $ders) {
    
    // Derse kayıtlı öğrencilerin velilerini bul
    $veli_sorgu = $conn->prepare("
        SELECT DISTINCT k.id, k.ad_soyad, k.telefon, k.telegram_chat_id 
        FROM kullanicilar k
        JOIN veli_ogrenci_iliskisi voi ON k.id = voi.veli_id
        JOIN ogrenci_sinif_iliskisi osi ON voi.ogrenci_id = osi.ogrenci_id
        WHERE osi.sinif_id = ? AND k.rol = 'veli'
    ");
    $veli_sorgu->bind_param("i", $ders['sinif_id']);
    $veli_sorgu->execute();
    $veliler = $veli_sorgu->get_result()->fetch_all(MYSQLI_ASSOC);
    $veli_sorgu->close();

    foreach ($veliler as $veli) {
        $baslangic_saati_formatli = substr($ders['baslangic_saati'], 0, 5);
        
        // Telegram Bildirimi
        if (!empty($veli['telegram_chat_id'])) {
            $telegram_mesaj = "🔔 *Ders Başlıyor!*\n\n" .
                              "Merhaba *" . $veli['ad_soyad'] . "*,\n" .
                              "*" . $ders['brans_adi'] . "* dersi *" . $baslangic_saati_formatli . "*'da başlayacaktır.\n\n" .
                              "Öğrencinizin derse zamanında katılmasını rica ederiz.";
            sendMessage($veli['telegram_chat_id'], $telegram_mesaj);
        }

        // WhatsApp Bildirimi
        if (!empty($veli['telefon'])) {
            $sablon_adi = 'ders_hatirlatma'; // Meta'da oluşturduğunuz şablonun adı
            $parametreler = [$veli['ad_soyad'], $ders['brans_adi'], $baslangic_saati_formatli];
            sendWhatsAppTemplateMessage($veli['telefon'], $sablon_adi, $parametreler);
        }
    }
    
    // Bu ders için hatırlatmanın yapıldığını veritabanına kaydet
    $update_stmt = $conn->prepare("UPDATE ders_programi SET son_hatirlatma_tarihi = CURDATE() WHERE id = ?");
    $update_stmt->bind_param("i", $ders['id']);
    $update_stmt->execute();
    $update_stmt->close();
}

$conn->close();
?>