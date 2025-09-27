<?php
// Hata raporlamayı açalım ki tüm sorunları görebilelim
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Gerekli yardımcı dosyayı dahil et
require_once 'includes/whatsapp_helper.php';

$sonuc = null;
$telefon = '';
$sablon_adi = 'yeni_odev_bildirimi'; // Varsayılan şablon adı
$parametre_str = '';

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $telefon = trim($_POST['telefon']);
    $sablon_adi = trim($_POST['sablon_adi']);
    $parametre_str = trim($_POST['parametre_str']);

    // Parametreleri virgülle ayırarak bir diziye dönüştür
    $parametreler = !empty($parametre_str) ? array_map('trim', explode(',', $parametre_str)) : [];

    // WhatsApp mesaj gönderme fonksiyonunu çağır
    $sonuc = sendWhatsAppTemplateMessage($telefon, $sablon_adi, $parametreler);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Şablon Test Sayfası</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="main-content">
        <div class="card">
            <h1>WhatsApp Şablon Test Sayfası</h1>
            <p>Bu sayfa üzerinden, Meta'da onaylanmış mesaj şablonlarınızı test edebilirsiniz.</p>
            
            <form action="whatsapp_sablon_test.php" method="POST" class="styled-form">
                <div class="form-group">
                    <label for="telefon">Alıcı Telefon Numarası:</label>
                    <input type="text" id="telefon" name="telefon" value="<?= htmlspecialchars($telefon) ?>" placeholder="Örn: 905xxxxxxxxx" required>
                </div>
                <div class="form-group">
                    <label for="sablon_adi">Şablon Adı:</label>
                    <input type="text" id="sablon_adi" name="sablon_adi" value="<?= htmlspecialchars($sablon_adi) ?>" required>
                </div>
                <div class="form-group">
                    <label for="parametre_str">Parametreler (Virgülle ayırın):</label>
                    <input type="text" id="parametre_str" name="parametre_str" value="<?= htmlspecialchars($parametre_str) ?>" placeholder="Örn: Veli Adı, Öğretmen Adı, Branş Adı">
                    <small>Şablondaki {{1}}, {{2}} gibi alanlara sırasıyla gelecek değerleri girin.</small>
                </div>
                <button type="submit" class="btn btn-primary">Test Mesajı Gönder</button>
            </form>

            <?php if ($sonuc !== null): ?>
                <div class="test-result">
                    <h2>API Cevabı:</h2>
                    <pre><?php print_r($sonuc); ?></pre>
                </div>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>
