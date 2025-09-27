<?php
// HATA AYIKLAMA SÜRÜMÜ
// Bu dosya, Telegram'dan gelen tüm mesajları ve komutları işler.
// Tüm adımları telegram_debug_log.txt dosyasına yazar.

// Hata loglamayı aktif et (sorun giderme için)
ini_set('log_errors', 1);
ini_set('error_log', 'telegram_error.log'); 

// Loglama fonksiyonu
function log_message($message) {
    file_put_contents('telegram_debug_log.txt', "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

// Log dosyasını temizle (her yeni istekte)
file_put_contents('telegram_debug_log.txt', '');

log_message("Webhook tetiklendi.");

require_once '../includes/db_connect.php';
require_once '../includes/telegram_helper.php';

// Gelen veriyi al
$update = json_decode(file_get_contents("php://input"), TRUE);

if (!$update || !isset($update['message'])) {
    log_message("HATA: Geçersiz veya boş JSON verisi alındı.");
    exit();
}

$chat_id = $update['message']['chat']['id'] ?? null;
$message_text = trim($update['message']['text'] ?? '');

if (!$chat_id) {
    log_message("HATA: Mesajdan Chat ID alınamadı.");
    exit();
}
log_message("Gelen Mesaj -> Chat ID: $chat_id, Metin: '$message_text'");


// Veritabanından kullanıcıyı bul
$stmt_user = $conn->prepare("SELECT * FROM kullanicilar WHERE telegram_chat_id = ? AND rol = 'veli'");
$stmt_user->bind_param("s", $chat_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows == 0) {
    log_message("Kullanıcı bulunamadı. Bağlantı kodu kontrol ediliyor...");
    // ... (Mevcut hesap bağlama kodu burada çalışır, bu kısım doğru) ...
    exit();
}

$user = $user_result->fetch_assoc();
$user_id = $user['id'];
$stmt_user->close();
log_message("Kullanıcı bulundu -> ID: $user_id, Adı: " . $user['ad_soyad']);

// Komutları işle
$command_parts = explode(' ', $message_text, 2);
$command = strtolower(trim($command_parts[0]));

if ($command === '/odevler') {
    log_message("/ODEVLER komutu algılandı.");

    // Veliye bağlı öğrencileri bul
    $stmt_ogrenciler = $conn->prepare("SELECT ogrenci_id FROM veli_ogrenci_iliskisi WHERE veli_id = ?");
    $stmt_ogrenciler->bind_param("i", $user_id);
    $stmt_ogrenciler->execute();
    $ogrenciler_result = $stmt_ogrenciler->get_result();
    $ogrenci_idler = [];
    while($row = $ogrenciler_result->fetch_assoc()){ 
        $ogrenci_idler[] = $row['ogrenci_id']; 
    }
    $stmt_ogrenciler->close();

    if(empty($ogrenci_idler)){
        log_message("Bu veliye bağlı öğrenci bulunamadı.");
        sendMessage($chat_id, "Sisteme kayıtlı sorumlu olduğunuz bir öğrenci bulunmuyor.");
        exit();
    }
    log_message("Veliye bağlı öğrenci ID'leri: " . implode(', ', $ogrenci_idler));

    $ogrenci_idler_str = implode(',', array_map('intval', $ogrenci_idler));
    $bekleyen_odevler_sql = "
        SELECT o.id, o.baslik, o.odev_kodu, k.ad_soyad as ogrenci_adi, o.teslim_tarihi
        FROM odevler o
        JOIN ogrenci_sinif_iliskisi osi ON o.sinif_id = osi.sinif_id
        JOIN kullanicilar k ON osi.ogrenci_id = k.id
        WHERE osi.ogrenci_id IN ($ogrenci_idler_str) 
        AND NOT EXISTS (SELECT 1 FROM odev_teslimleri ot WHERE ot.odev_id = o.id AND ot.ogrenci_id = osi.ogrenci_id)
        ORDER BY k.ad_soyad, o.teslim_tarihi ASC";
    
    log_message("Çalıştırılacak SQL: " . $bekleyen_odevler_sql);
    $bekleyen_odevler = $conn->query($bekleyen_odevler_sql)->fetch_all(MYSQLI_ASSOC);

    if (empty($bekleyen_odevler)) {
        log_message("Teslim bekleyen ödev bulunamadı.");
        sendMessage($chat_id, "Tebrikler! Teslim bekleyen herhangi bir ödeviniz bulunmuyor.");
    } else {
        log_message("Bulunan bekleyen ödev sayısı: " . count($bekleyen_odevler));
        $mesaj = "📋 *Teslim Bekleyen Ödevler:*\n\n";
        $count = 1;
        foreach ($bekleyen_odevler as $odev) {
            $mesaj .= $count . ". *" . $odev['odev_kodu'] . "* - " . $odev['baslik'] . " (_" . $odev['ogrenci_adi'] . "_)\n";
            $mesaj .= "   Son Teslim: " . date('d.m.Y H:i', strtotime($odev['teslim_tarihi'])) . "\n\n";
            $count++;
        }
        $mesaj .= "Görsel yüklemeye başlamak için `YUKLE [Ödev Kodu]` (örn: `YUKLE MAT123`) komutunu gönderin.";
        
        log_message("Gönderilecek mesaj hazırlanıyor...");
        sendMessage($chat_id, $mesaj);
        log_message("Mesaj gönderildi.");
    }
} 
// Diğer komutlar (YUKLE, ONAYLA vb.) burada devam eder...

?>