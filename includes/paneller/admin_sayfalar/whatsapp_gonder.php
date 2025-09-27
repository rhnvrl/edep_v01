<?php
require_once 'includes/db_connect.php';
require_once 'includes/whatsapp_helper.php';

// Sadece telefon numarası olan velileri çek
$veliler = $conn->query("SELECT id, ad_soyad, telefon FROM kullanicilar WHERE rol = 'veli' AND telefon IS NOT NULL AND telefon != '' ORDER BY ad_soyad");

$sonuc_mesaji = '';
$sonuc_tipi = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mesaj_gonder'])) {
    $veli_id = (int)$_POST['veli_id'];
    $mesaj = trim($_POST['mesaj']);

    if (empty($veli_id) || empty($mesaj)) {
        $sonuc_mesaji = "Lütfen bir veli seçin ve mesajınızı yazın.";
        $sonuc_tipi = 'error';
    } else {
        $veli_sorgu = $conn->prepare("SELECT telefon FROM kullanicilar WHERE id = ?");
        $veli_sorgu->bind_param("i", $veli_id);
        $veli_sorgu->execute();
        $veli_telefon = $veli_sorgu->get_result()->fetch_assoc()['telefon'];

        if ($veli_telefon) {
            $response = sendWhatsAppTextMessage($veli_telefon, $mesaj);
            $response_data = json_decode($response, true);
            
            if (isset($response_data['messages'][0]['id'])) {
                $sonuc_mesaji = "Mesaj başarıyla gönderildi.";
                $sonuc_tipi = 'success';
            } else {
                $sonuc_mesaji = "Mesaj gönderilemedi. Hata: " . ($response_data['error']['message'] ?? 'Bilinmeyen bir hata oluştu.');
                $sonuc_tipi = 'error';
            }
        } else {
            $sonuc_mesaji = "Seçilen velinin kayıtlı bir telefon numarası bulunamadı.";
            $sonuc_tipi = 'error';
        }
    }
}
?>
<div class="panel-header">
    <h1>Özel WhatsApp Mesajı Gönder</h1>
    <p>Seçtiğiniz veliye özel bir metin mesajı gönderin.</p>
</div>
<div class="panel-content">
    <div class="card">
        <div class="form-message error" style="display: block;">
            <strong>Önemli Not:</strong> WhatsApp kuralları gereği, bu formdan serbest metinli mesaj gönderebilmeniz için, velinin size son 24 saat içinde bir mesaj göndermiş olması gerekmektedir.
        </div>

        <?php if ($sonuc_mesaji): ?>
            <div class="form-message <?php echo $sonuc_tipi; ?>" style="display: block;"><?php echo $sonuc_mesaji; ?></div>
        <?php endif; ?>

        <form action="panel.php?sayfa=whatsapp_gonder" method="POST" class="styled-form">
            <div class="form-group">
                <label for="veli_id">Veli Seçimi (*)</label>
                <select id="veli_id" name="veli_id" required>
                    <option value="">Lütfen bir veli seçin...</option>
                    <?php if ($veliler && $veliler->num_rows > 0): ?>
                        <?php while($veli = $veliler->fetch_assoc()): ?>
                            <option value="<?php echo $veli['id']; ?>"><?php echo htmlspecialchars($veli['ad_soyad']) . ' (' . htmlspecialchars($veli['telefon']) . ')'; ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="mesaj">Mesajınız (*)</label>
                <textarea id="mesaj" name="mesaj" rows="6" required></textarea>
            </div>
            <button type="submit" name="mesaj_gonder" class="btn btn-primary">Gönder</button>
        </form>
    </div>
</div>
