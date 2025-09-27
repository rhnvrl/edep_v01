<?php
require_once 'includes/db_connect.php';

$kullanici_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$form_modu = ($kullanici_id > 0) ? 'Duzenle' : 'Ekle';
$sayfa_adi = ($form_modu == 'Duzenle') ? 'Kullanıcıyı Düzenle' : 'Yeni Kullanıcı Ekle';

$kullanici_verisi = [
    'ad_soyad' => '', 'kullanici_adi' => '', 'rol' => '',
    'email' => '', 'telefon' => '', 'telegram_chat_id' => '', 'telegram_linking_code' => ''
];

if ($form_modu == 'Duzenle') {
    $stmt = $conn->prepare("SELECT ad_soyad, kullanici_adi, rol, email, telefon, telegram_chat_id, telegram_linking_code FROM kullanicilar WHERE id = ?");
    $stmt->bind_param("i", $kullanici_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $kullanici_verisi = $result->fetch_assoc();
    } else {
        $_SESSION['form_hatasi'] = "Düzenlenecek kullanıcı bulunamadı.";
        header("Location: panel.php?sayfa=kullanicilar");
        exit();
    }
    $stmt->close();
}
?>
<div class="panel-header">
    <h1><?php echo $sayfa_adi; ?></h1>
    <p><a href="panel.php?sayfa=kullanicilar">← Kullanıcı Listesine Geri Dön</a></p>
</div>
<div class="panel-content">
    <div class="card">
        <?php
        if (isset($_SESSION['form_mesaji'])) {
            echo '<div class="form-message success">' . $_SESSION['form_mesaji'] . '</div>';
            unset($_SESSION['form_mesaji']);
        }
        if (isset($_SESSION['form_hatasi'])) {
            echo '<div class="form-message error">' . $_SESSION['form_hatasi'] . '</div>';
            unset($_SESSION['form_hatasi']);
        }
        ?>
        <form action="actions/kullanici_kaydet.php" method="POST" class="styled-form">
            <input type="hidden" name="kullanici_id" value="<?php echo $kullanici_id; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ad_soyad">Ad Soyad (*)</label>
                    <input type="text" id="ad_soyad" name="ad_soyad" value="<?php echo htmlspecialchars($kullanici_verisi['ad_soyad']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="kullanici_adi">Kullanıcı Adı (*)</label>
                    <input type="text" id="kullanici_adi" name="kullanici_adi" value="<?php echo htmlspecialchars($kullanici_verisi['kullanici_adi']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="sifre">Şifre <?php echo ($form_modu == 'Ekle') ? '(*)' : ''; ?></label>
                <input type="password" id="sifre" name="sifre" placeholder="<?php echo ($form_modu == 'Duzenle') ? 'Değiştirmek istemiyorsanız boş bırakın' : ''; ?>" <?php echo ($form_modu == 'Ekle') ? 'required' : ''; ?>>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="rol">Rol (*)</label>
                    <select id="rol" name="rol" required>
                        <option value="">Seçiniz...</option>
                        <option value="admin" <?php echo ($kullanici_verisi['rol'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="bas_ogretmen" <?php echo ($kullanici_verisi['rol'] == 'bas_ogretmen') ? 'selected' : ''; ?>>Baş Öğretmen</option>
                        <option value="ogretmen" <?php echo ($kullanici_verisi['rol'] == 'ogretmen') ? 'selected' : ''; ?>>Öğretmen</option>
                        <option value="veli" <?php echo ($kullanici_verisi['rol'] == 'veli') ? 'selected' : ''; ?>>Veli</option>
                        <option value="ogrenci" <?php echo ($kullanici_verisi['rol'] == 'ogrenci') ? 'selected' : ''; ?>>Öğrenci</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">E-posta Adresi</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($kullanici_verisi['email']); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="telefon">Telefon Numarası</label>
                    <input type="tel" id="telefon" name="telefon" value="<?php echo htmlspecialchars($kullanici_verisi['telefon']); ?>">
                </div>
                <div class="form-group">
                    <label for="telegram_chat_id">Telegram Chat ID</label>
                    <input type="text" id="telegram_chat_id" name="telegram_chat_id" value="<?php echo htmlspecialchars($kullanici_verisi['telegram_chat_id']); ?>" placeholder="Kullanıcı botu başlatınca otomatik dolacak">
                </div>
            </div>

            <?php if (in_array($kullanici_verisi['rol'], ['veli', 'ogretmen']) && !empty($kullanici_verisi['telegram_linking_code'])): ?>
            <div class="form-group">
                <label>Telegram Bağlantı Kodu (Kullanıcıya İletilecek)</label>
                <input type="text" value="<?php echo htmlspecialchars($kullanici_verisi['telegram_linking_code']); ?>" readonly style="background-color: #e9ecef; font-weight: bold; text-align: center;">
                <small>Lütfen bu kodu kullanıcıya ileterek <strong>@egitimdestekplatform_bot</strong>'a göndermesini isteyin.</small>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
    </div>
</div>
