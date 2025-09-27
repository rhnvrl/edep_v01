<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];

// Düzenleme modu kontrolü
$is_edit_mode = isset($_GET['id']) && !empty($_GET['id']);
$odev = null;
$page_title = "Yeni Ödev Oluştur";
$form_islem = "ekle";

if ($is_edit_mode) {
    $odev_id = intval($_GET['id']);
    // Güvenlik: Öğretmenin sadece kendi ödevini düzenleyebildiğinden emin ol
    $stmt = $conn->prepare("SELECT * FROM odevler WHERE id = ? AND ogretmen_id = ?");
    $stmt->bind_param("ii", $odev_id, $ogretmen_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $odev = $result->fetch_assoc();
        $page_title = "Ödevi Düzenle: " . htmlspecialchars($odev['baslik']);
        $form_islem = "guncelle";
    } else {
        // Hata durumunda işlemi durdur
        die("HATA: Ödev bulunamadı veya bu ödevi düzenleme yetkiniz yok.");
    }
    $stmt->close();
}

// Öğretmenin sorumlu olduğu sınıfları çek
$stmt_siniflar = $conn->prepare("SELECT s.id, s.sinif_adi FROM siniflar s JOIN ogretmen_sinif_iliskisi osi ON s.id = osi.sinif_id WHERE osi.ogretmen_id = ? GROUP BY s.id ORDER BY s.sinif_adi");
$stmt_siniflar->bind_param("i", $ogretmen_id);
$stmt_siniflar->execute();
$siniflar = $stmt_siniflar->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_siniflar->close();

// Öğretmenin sorumlu olduğu branşları çek
$stmt_branslar = $conn->prepare("SELECT b.id, b.brans_adi FROM branslar b JOIN ogretmen_brans_iliskisi obi ON b.id = obi.brans_id WHERE obi.ogretmen_id = ? GROUP BY b.id ORDER BY b.brans_adi");
$stmt_branslar->bind_param("i", $ogretmen_id);
$stmt_branslar->execute();
$branslar = $stmt_branslar->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_branslar->close();
?>

<div class="card">
    <div class="card-header">
        <h1><?= $page_title ?></h1>
        <a href="panel.php?sayfa=odev_yonetimi" class="btn btn-secondary">Geri Dön</a>
    </div>
    <div class="card-body">
        <form action="actions/odev_action.php" method="POST" class="styled-form">
            <input type="hidden" name="islem" value="<?= $form_islem ?>">
            <?php if ($is_edit_mode): ?>
                <input type="hidden" name="odev_id" value="<?= $odev['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="sinif_id">Sınıf</label>
                <select name="sinif_id" id="sinif_id" required>
                    <option value="">Sınıf Seçiniz...</option>
                    <?php foreach ($siniflar as $sinif): ?>
                        <option value="<?= $sinif['id'] ?>" <?= ($odev && $odev['sinif_id'] == $sinif['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sinif['sinif_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="brans_id">Branş</label>
                <select name="brans_id" id="brans_id" required>
                    <option value="">Branş Seçiniz...</option>
                    <?php foreach ($branslar as $brans): ?>
                         <option value="<?= $brans['id'] ?>" <?= ($odev && $odev['brans_id'] == $brans['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brans['brans_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="baslik">Ödev Başlığı</label>
                <input type="text" name="baslik" id="baslik" value="<?= htmlspecialchars($odev['baslik'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="aciklama">Açıklama</label>
                <textarea name="aciklama" id="aciklama" rows="5"><?= htmlspecialchars($odev['aciklama'] ?? '') ?></textarea>
            </div>

             <div class="form-group">
                <label for="teslim_tarihi">Son Teslim Tarihi</label>
                <input type="datetime-local" name="teslim_tarihi" id="teslim_tarihi" value="<?= $odev ? date('Y-m-d\TH:i', strtotime($odev['teslim_tarihi'])) : '' ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>