<?php
// Bu dosya, WhatsApp'tan gelen mesajları ve komutları işler.

// Adım 1: Facebook Webhook Doğrulaması
$verify_token = 'EDEP_DOGRULAMA_TOKENI'; // Bu token'ı Meta panelinde de aynı şekilde girmelisiniz.
if (isset($_REQUEST['hub_challenge']) && isset($_REQUEST['hub_verify_token']) && $_REQUEST['hub_verify_token'] == $verify_token) {
    echo $_REQUEST['hub_challenge'];
    exit;
}

// Hata loglamayı aktif et (sorun giderme için)
ini_set('log_errors', 1);
ini_set('error_log', 'whatsapp_error.log');

// Gerekli dosyaları dahil et
require_once '../includes/db_connect.php';
require_once '../includes/whatsapp_helper.php';

// Gelen veriyi al
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Sadece mesajları işle
if (empty($data['entry'][0]['changes'][0]['value']['messages'])) {
    http_response_code(200);
    exit();
}

// Gerekli bilgileri çıkar
$message_data = $data['entry'][0]['changes'][0]['value']['messages'][0];
$from_number = $message_data['from'];
$text = strtolower(trim($message_data['text']['body'] ?? ''));
$image_id = $message_data['image']['id'] ?? null;

// Veritabanından kullanıcıyı bul
$stmt_user = $conn->prepare("SELECT * FROM kullanicilar WHERE telefon = ? AND rol = 'veli'");
$stmt_user->bind_param("s", $from_number);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows == 0) {
    sendWhatsAppTextMessage($from_number, "Merhaba! Sizi sistemde tanıyamadım. Lütfen platform yöneticinizin, telefon numaranızı sisteme doğru kaydettiğinden emin olun.");
    exit();
}
$user = $user_result->fetch_assoc();
$user_id = $user['id'];
$stmt_user->close();

// Kullanıcının mevcut bot oturumunu kontrol et
$stmt_session = $conn->prepare("SELECT * FROM bot_sessions WHERE user_id = ? AND platform = 'whatsapp'");
$stmt_session->bind_param("i", $user_id);
$stmt_session->execute();
$session = $stmt_session->get_result()->fetch_assoc();
$stmt_session->close();

// --- RESİM YÜKLEME OTURUMU ---
if ($session && $session['current_action'] === 'uploading_images' && $image_id) {
    // NOT: Gerçek bir senaryoda, bu $image_id kullanılarak görselin URL'si alınır ve sunucuya indirilir.
    // Bu kısım, Facebook App Review'dan sonra tam olarak implemente edilmelidir.
    $context = json_decode($session['context_data'], true);
    $teslim_id = $context['teslim_id'];
    $dosya_yolu = 'uploads/odevler/wa_' . uniqid() . '.jpg'; // Simülasyon
    
    $stmt_dosya = $conn->prepare("INSERT INTO odev_dosyalari (teslim_id, dosya_yolu, yukleyen_kullanici_id) VALUES (?, ?, ?)");
    $stmt_dosya->bind_param("isi", $teslim_id, $dosya_yolu, $user_id);
    $stmt_dosya->execute();
    $stmt_dosya->close();
    
    exit();
}

// --- KOMUT İŞLEME ---
$command_parts = explode(' ', $text, 3);
$command = strtolower(trim($command_parts[0]));
$argument1 = $command_parts[1] ?? '';
$argument2 = $command_parts[2] ?? '';

switch ($command) {
    case 'odevler':
        $stmt_ogrenciler = $conn->prepare("SELECT ogrenci_id FROM veli_ogrenci_iliskisi WHERE veli_id = ?");
        $stmt_ogrenciler->bind_param("i", $user_id);
        $stmt_ogrenciler->execute();
        $ogrenciler_result = $stmt_ogrenciler->get_result();
        $ogrenci_idler = [];
        while($row = $ogrenciler_result->fetch_assoc()){ $ogrenci_idler[] = $row['ogrenci_id']; }
        $stmt_ogrenciler->close();

        if(empty($ogrenci_idler)){
            sendWhatsAppTextMessage($from_number, "Sisteme kayıtlı sorumlu olduğunuz bir öğrenci bulunmuyor.");
            break;
        }

        $ogrenci_idler_str = implode(',', array_map('intval', $ogrenci_idler));
        $bekleyen_odevler_sql = "
            SELECT o.id, o.baslik, o.odev_kodu, k.ad_soyad as ogrenci_adi, o.teslim_tarihi
            FROM odevler o
            JOIN ogrenci_sinif_iliskisi osi ON o.sinif_id = osi.sinif_id
            JOIN kullanicilar k ON osi.ogrenci_id = k.id
            WHERE osi.ogrenci_id IN ($ogrenci_idler_str) 
            AND NOT EXISTS (SELECT 1 FROM odev_teslimleri ot WHERE ot.odev_id = o.id AND ot.ogrenci_id = osi.ogrenci_id)
            ORDER BY k.ad_soyad, o.teslim_tarihi ASC";
        
        $bekleyen_odevler = $conn->query($bekleyen_odevler_sql)->fetch_all(MYSQLI_ASSOC);

        if (empty($bekleyen_odevler)) {
            sendWhatsAppTextMessage($from_number, "Tebrikler! Teslim bekleyen herhangi bir ödeviniz bulunmuyor.");
        } else {
            $mesaj = "📋 *Teslim Bekleyen Ödevler:*\n\n";
            $count = 1;
            foreach ($bekleyen_odevler as $odev) {
                $mesaj .= $count . ". *" . $odev['odev_kodu'] . "* - " . $odev['baslik'] . " (_" . $odev['ogrenci_adi'] . "_)\n";
                $mesaj .= "   Son Teslim: " . date('d.m.Y H:i', strtotime($odev['teslim_tarihi'])) . "\n\n";
                $count++;
            }
            $mesaj .= "Görsel yüklemeye başlamak için `YUKLE [Ödev Kodu]` (örn: `YUKLE MAT123`) komutunu gönderin.";
            sendWhatsAppTextMessage($from_number, $mesaj);
        }
        break;

    case 'yukle':
        $odev_kodu = trim($argument1);
        $ogrenci_adi_arg = trim($argument2);

        $stmt_ogrenciler = $conn->prepare("SELECT k.id, k.ad_soyad FROM kullanicilar k JOIN veli_ogrenci_iliskisi voi ON k.id = voi.ogrenci_id WHERE voi.veli_id = ?");
        $stmt_ogrenciler->bind_param("i", $user_id);
        $stmt_ogrenciler->execute();
        $veli_ogrencileri = $stmt_ogrenciler->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_ogrenciler->close();
        
        $uygun_ogrenciler = [];
        foreach($veli_ogrencileri as $ogrenci){
            $stmt_odev_check = $conn->prepare("SELECT o.id as odev_id, o.baslik FROM odevler o JOIN ogrenci_sinif_iliskisi osi ON o.sinif_id = osi.sinif_id WHERE o.odev_kodu = ? AND osi.ogrenci_id = ? AND NOT EXISTS (SELECT 1 FROM odev_teslimleri ot WHERE ot.odev_id = o.id AND ot.ogrenci_id = osi.ogrenci_id)");
            $stmt_odev_check->bind_param("si", $odev_kodu, $ogrenci['id']);
            $stmt_odev_check->execute();
            $odev_result = $stmt_odev_check->get_result();
            if($odev_result->num_rows > 0){
                $odev_data = $odev_result->fetch_assoc();
                $uygun_ogrenciler[] = array_merge($ogrenci, $odev_data);
            }
            $stmt_odev_check->close();
        }

        if (count($uygun_ogrenciler) === 0) {
            sendWhatsAppTextMessage($from_number, "❌ Hata: Girdiğiniz kod (`" . htmlspecialchars($odev_kodu) . "`) geçersiz, daha önce teslim edilmiş veya sorumlu olduğunuz öğrencilere ait değil.");
        } elseif (count($uygun_ogrenciler) > 1 && empty($ogrenci_adi_arg)) {
            $ogrenci_isimleri = implode(', ', array_column($uygun_ogrenciler, 'ad_soyad'));
            sendWhatsAppTextMessage($from_number, "Bu ödev birden fazla çocuğunuza (" . $ogrenci_isimleri . ") atanmış görünüyor. Lütfen hangisi için yükleme yapmak istediğinizi belirtin:\n`YUKLE " . $odev_kodu . " [Öğrencinin Adı Soyadı]`");
        } else {
            $hedef_ogrenci = null;
            if(count($uygun_ogrenciler) === 1){
                $hedef_ogrenci = $uygun_ogrenciler[0];
            } else { 
                foreach($uygun_ogrenciler as $ogrenci){
                    if(stripos($ogrenci['ad_soyad'], $ogrenci_adi_arg) !== false){
                        $hedef_ogrenci = $ogrenci;
                        break;
                    }
                }
            }
            
            if($hedef_ogrenci){
                $odev_id = $hedef_ogrenci['odev_id'];
                $ogrenci_id = $hedef_ogrenci['id'];

                $stmt_create_teslim = $conn->prepare("INSERT INTO odev_teslimleri (odev_id, ogrenci_id, durum) VALUES (?, ?, 'Teslim Edildi - Veli Onayı Bekliyor') ON DUPLICATE KEY UPDATE durum='Teslim Edildi - Veli Onayı Bekliyor'");
                $stmt_create_teslim->bind_param("ii", $odev_id, $ogrenci_id);
                $stmt_create_teslim->execute();
                $teslim_id = $conn->insert_id > 0 ? $conn->insert_id : $stmt_create_teslim->insert_id;
                $stmt_create_teslim->close();

                $context_data = json_encode(['odev_kodu' => $odev_kodu, 'teslim_id' => $teslim_id]);
                $stmt_set_session = $conn->prepare("INSERT INTO bot_sessions (user_id, platform, platform_user_id, current_action, context_data) VALUES (?, 'whatsapp', ?, 'uploading_images', ?) ON DUPLICATE KEY UPDATE current_action = VALUES(current_action), context_data = VALUES(context_data)");
                $stmt_set_session->bind_param("iss", $user_id, $from_number, $context_data);
                $stmt_set_session->execute();
                $stmt_set_session->close();

                sendWhatsAppTextMessage($from_number, "✅ Harika! Şimdi *" . $hedef_ogrenci['ad_soyad'] . "* adlı öğrencinin *'" . $hedef_ogrenci['baslik'] . "'* ödevi için görselleri gönderebilirsiniz.\n\nİşiniz bittiğinde `ONAYLA " . $odev_kodu . "` komutunu göndererek teslimatı tamamlayın.");
            } else {
                sendWhatsAppTextMessage($from_number, "❌ Hata: Belirttiğiniz isimde uygun bir öğrenci bulunamadı.");
            }
        }
        break;

    case 'onayla':
        $odev_kodu = trim($argument1);
        if ($session && $session['current_action'] === 'uploading_images') {
             $context = json_decode($session['context_data'], true);
             if ($context['odev_kodu'] == $odev_kodu) {
                 $teslim_id = $context['teslim_id'];
                 $stmt_onay = $conn->prepare("UPDATE odev_teslimleri SET durum = 'Teslim Edildi - Veli Tarafından Onaylandı', veli_onay_tarihi = NOW() WHERE id = ?");
                 $stmt_onay->bind_param("i", $teslim_id);
                 $stmt_onay->execute();
                 $stmt_onay->close();

                 $stmt_clear_session = $conn->prepare("DELETE FROM bot_sessions WHERE id = ?");
                 $stmt_clear_session->bind_param("i", $session['id']);
                 $stmt_clear_session->execute();
                 $stmt_clear_session->close();

                 sendWhatsAppTextMessage($from_number, "✅ Başarılı! Ödev teslimi onaylandı ve öğretmene iletildi.");
             } else {
                 sendWhatsAppTextMessage($from_number, "❌ Hata: Onaylamaya çalıştığınız kod (`" . htmlspecialchars($odev_kodu) . "`), şu anki resim yükleme oturumunuzla eşleşmiyor. Lütfen `ONAYLA " . $context['odev_kodu'] . "` komutunu kullanın.");
             }
        } else {
            sendWhatsAppTextMessage($from_number, "❌ Hata: Aktif bir resim yükleme oturumunuz bulunmuyor. Lütfen önce `YUKLE [Ödev Kodu]` komutuyla bir oturum başlatın.");
        }
        break;

    default:
        if(!$session){
             sendWhatsAppTextMessage($from_number, "Anlayamadım. Bekleyen ödevlerinizi görmek için *ODEVLER* komutunu gönderebilirsiniz.");
        }
        break;
}

// WhatsApp'a her zaman 200 OK cevabı dönmek zorunludur.
http_response_code(200);
?>

