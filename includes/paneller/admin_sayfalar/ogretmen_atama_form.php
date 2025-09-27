<?php
// Bu sayfanın içeriği includes/paneller/admin_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ogretmen_id <= 0) {
    die("Geçersiz Öğretmen ID'si.");
}

// Öğretmen bilgilerini çek
$stmt_ogretmen = $conn->prepare("SELECT * FROM kullanicilar WHERE id = ? AND rol = 'ogretmen'");
$stmt_ogretmen->bind_param("i", $ogretmen_id);
$stmt_ogretmen->execute();
$result_ogretmen = $stmt_ogretmen->get_result();
if ($result_ogretmen->num_rows == 0) {
    die("Öğretmen bulunamadı.");
}
$ogretmen = $result_ogretmen->fetch_assoc();
$stmt_ogretmen->close();

// Tüm branşları ve sınıfları çek
$branslar = $conn->query("SELECT * FROM branslar ORDER BY brans_adi")->fetch_all(MYSQLI_ASSOC);
$siniflar = $conn->query("SELECT * FROM siniflar ORDER BY sinif_adi")->fetch_all(MYSQLI_ASSOC);

// Öğretmenin mevcut atamalarını çek
$mevcut_branslar_sorgu = $conn->query("SELECT brans_id FROM ogretmen_brans_iliskisi WHERE ogretmen_id = $ogretmen_id");
$mevcut_branslar = array_column($mevcut_branslar_sorgu->fetch_all(MYSQLI_ASSOC), 'brans_id');

$mevcut_siniflar_sorgu = $conn->query("SELECT sinif_id FROM ogretmen_sinif_iliskisi WHERE ogretmen_id = $ogretmen_id");
$mevcut_siniflar = array_column($mevcut_siniflar_sorgu->fetch_all(MYSQLI_ASSOC), 'sinif_id');
?>

<div class="card">
    <div class="card-header">
        <div>
            <h1>Öğretmen Atama</h1>
            <p class="subtitle"><strong><?= htmlspecialchars($ogretmen['ad_soyad']) ?></strong> adlı öğretmenin atamalarını yönetin.</p>
        </div>
        <a href="panel.php?sayfa=atamalar" class="btn btn-secondary">← Atama Yönetimine Geri Dön</a>
    </div>
    <div class="card-body">
        <form action="actions/atama_kaydet.php" method="POST" class="styled-form">
            <input type="hidden" name="islem" value="ogretmen_atama">
            <input type="hidden" name="ogretmen_id" value="<?= $ogretmen['id'] ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Branş Atamaları</label>
                    <div class="form-group-checkbox">
                        <?php foreach($branslar as $brans): ?>
                            <label>
                                <input type="checkbox" name="branslar[]" value="<?= $brans['id'] ?>" <?= in_array($brans['id'], $mevcut_branslar) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($brans['brans_adi']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Sınıf Atamaları</label>
                     <div class="form-group-checkbox">
                        <?php foreach($siniflar as $sinif): ?>
                             <label>
                                <input type="checkbox" name="siniflar[]" value="<?= $sinif['id'] ?>" <?= in_array($sinif['id'], $mevcut_siniflar) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($sinif['sinif_adi']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Atamaları Kaydet</button>
            </div>
        </form>
    </div>
</div>