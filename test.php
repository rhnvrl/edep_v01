<?php
// Hata ayıklama için hata raporlamayı açalım
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Telegram yardımcı dosyamızı dahil edelim
require_once 'includes/telegram_helper.php';

echo "<h1>Telegram Mesaj Gönderme Testi</h1>";

// --- DEĞİŞTİRİLECEK ALAN ---
// Lütfen buraya mesaj göndermek istediğiniz, sisteme kayıtlı ve botu başlatmış
// bir kullanıcının (kendi hesabınız olabilir) Telegram Chat ID'sini yazın.
$test_chat_id = "7584309829";
// --------------------------

$mesaj = "<b>Test Mesajı</b>\n\n";
$mesaj .= "Eğer bu mesajı alıyorsanız, Telegram botunuz doğru bir şekilde çalışıyor demektir! 🎉";

echo "<p><b>Gönderilecek Mesaj:</b></p>";
echo "<pre>" . $mesaj . "</pre>";
echo "<hr>";

if ($test_chat_id == "KENDI_TELEGRAM_CHAT_ID_NIZI_BURAYA_YAZIN" || empty($test_chat_id)) {
    echo "<p style='color:red; font-weight:bold;'>LÜTFEN KOD İÇİNDEKİ '$test_chat_id' DEĞİŞKENİNİ KENDİ TELEGRAM CHAT ID'NİZ İLE DEĞİŞTİRİN.</p>";
} else {
    echo "<p>Mesaj, <b>" . htmlspecialchars($test_chat_id) . "</b> ID'li kullanıcıya gönderiliyor...</p>";
    
    $sonuc = sendMessage($test_chat_id, $mesaj);

    if ($sonuc) {
        echo "<p style='color:green; font-weight:bold;'>Mesaj gönderme isteği başarıyla tamamlandı!</p>";
        echo "<p><b>Telegram'dan Dönen Cevap:</b></p>";
        echo "<pre>" . htmlspecialchars($sonuc) . "</pre>";
        echo "<p>Lütfen Telegram hesabınızı kontrol edin.</p>";
    } else {
        echo "<p style='color:red; font-weight:bold;'>Mesaj gönderilemedi!</p>";
        echo "<p><b>Olası Sebepler:</b></p>";
        echo "<ul>";
        echo "<li><code>includes/telegram_helper.php</code> dosyasındaki Bot Token'ı yanlış olabilir.</li>";
        echo "<li>Sunucunuzda cURL eklentisi aktif olmayabilir.</li>";
        echo "<li>Girdiğiniz Chat ID yanlış olabilir.</li>";
        echo "</ul>";
    }
}

echo "<hr><p><b>Not:</b> Testi tamamladıktan sonra güvenlik için bu <code>test.php</code> dosyasını sunucudan silmeyi unutmayın.</p>";

?>
