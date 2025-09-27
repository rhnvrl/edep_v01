<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/whatsapp_helper.php';
$sonuc = '';
$alici_no = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alici_no = $_POST['alici_no'];
    // Meta'nın varsayılan test şablonu
    $sablon_adi = 'hello_world';
    // Bu şablon parametre almadığı için dizi boş
    $parametreler = [];
    // Bu şablon sadece İngilizce olduğu için dil kodunu 'en_US' olarak gönderiyoruz
    $dil_kodu = 'en_US';

    $sonuc = sendWhatsAppTemplateMessage($alici_no, $sablon_adi, $parametreler, $dil_kodu);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>WhatsApp Mesaj Testi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container" style="margin-top: 50px;">
    <div class="card">
        <h1>WhatsApp API Test Sayfası</h1>
        <p>Bu sayfa, Meta tarafından sağlanan `hello_world` şablonunu kullanarak mesaj gönderir.</p>
        <hr>
        <form method="POST" class="styled-form">
            <div class="form-group">
                <label for="alici_no">Alıcı Telefon Numarası (*)</label>
                <input type="text" id="alici_no" name="alici_no" value="<?php echo htmlspecialchars($alici_no); ?>" placeholder="Örn: 905551234567" required>
                <small>Meta Geliştirici Panelinde "Alıcı" olarak eklediğiniz kendi telefon numaranızı girin.</small>
            </div>
            <button type="submit" class="btn btn-primary">Test Mesajı Gönder</button>
        </form>
        
        <?php if (!empty($sonuc)): ?>
        <hr style="margin-top: 30px;">
        <h3>Gönderim Sonucu:</h3>
        <pre style="background: #f4f4f4; padding: 15px; border-radius: 5px; white-space: pre-wrap; word-wrap: break-word;"><?php print_r(json_decode($sonuc)); ?></pre>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
