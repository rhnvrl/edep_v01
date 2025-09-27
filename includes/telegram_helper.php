<?php
/**
 * Telegram Bot ile iletişim kurmak için yardımcı fonksiyonlar içerir.
 */

// ÖNEMLİ: BotFather'dan aldığınız token'ı aşağıdaki tırnak işaretleri arasına yapıştırın.
define('TELEGRAM_BOT_TOKEN', '8220767201:AAEUpePCvDWv-wmmXD4RgCdsM7AUiz_Al8M');

/**
 * Belirtilen Chat ID'sine bir Telegram mesajı gönderir.
 *
 * @param string $chatId Mesajın gönderileceği kullanıcının Telegram Chat ID'si.
 * @param string $message Gönderilecek metin mesajı. HTML etiketleri içerebilir.
 * @return bool|string Başarılı olursa Telegram'dan dönen sonucu, başarısız olursa false döner.
 */
function sendMessage($chatId, $message) {
    if (empty($chatId) || TELEGRAM_BOT_TOKEN == 'BURAYA_BOT_TOKEN_GELECEK') {
        return false;
    }
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $post_fields = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * Belirtilen Chat ID'sine butonlu bir Telegram mesajı gönderir.
 *
 * @param string $chatId Mesajın gönderileceği kullanıcının Telegram Chat ID'si.
 * @param string $message Gönderilecek metin mesajı.
 * @param array $keyboard Butonları içeren dizi.
 * @return bool|string Başarılı olursa Telegram'dan dönen sonucu, başarısız olursa false döner.
 */
function sendMessageWithKeyboard($chatId, $message, $keyboard) {
    if (empty($chatId) || TELEGRAM_BOT_TOKEN == 'BURAYA_BOT_TOKEN_GELECEK') {
        return false;
    }
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $post_fields = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * Mevcut bir Telegram mesajını düzenler (örneğin butonları kaldırmak için).
 *
 * @param string $chatId Mesajın bulunduğu sohbetin ID'si.
 * @param int $messageId Düzenlenecek mesajın ID'si.
 * @param string $newMessage Mesajın yeni metni.
 */
function editMessageText($chatId, $messageId, $newMessage) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/editMessageText";
    $post_fields = [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => $newMessage,
        'parse_mode' => 'HTML'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_exec($ch);
    curl_close($ch);
}

/**
 * Telegram'dan bir dosyayı sunucuya indirir.
 *
 * @param string $file_id İndirilecek dosyanın Telegram'daki file_id'si.
 * @param string $save_path Dosyanın sunucuda kaydedileceği tam yol.
 * @return bool Başarılı olursa true, başarısız olursa false döner.
 */
function downloadFileFromTelegram($file_id, $save_path) {
    $url_path = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/getFile?file_id=" . $file_id;
    $response = json_decode(file_get_contents($url_path), true);
    if (!$response['ok']) {
        return false;
    }
    $file_path = $response['result']['file_path'];

    $file_url = "https://api.telegram.org/file/bot" . TELEGRAM_BOT_TOKEN . "/" . $file_path;
    if (copy($file_url, $save_path)) {
        return true;
    }
    return false;
}
?>
