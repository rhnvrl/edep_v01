<?php
// Bu sayfanın içeriği includes/paneller/admin_paneli.php tarafından çağrılır.
global $conn;
$ogrenci_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ogrenci_id <= 0) {
    die("Geçersiz ID.");
}

// Öğrenci bilgilerini çek
$stmt_ogrenci = $conn->prepare("SELECT * FROM kullanicilar WHERE id = ? AND rol = 'ogrenci'");
$stmt_ogrenci->bind_param("i", $ogrenci_id);
$stmt_ogrenci->execute();
$result_ogrenci = $stmt_ogrenci->get_result();
if ($result_ogrenci->num_rows == 0) {
    die("Öğrenci bulunamadı.");
}
$ogrenci = $result_ogrenci->fetch_assoc();
$stmt_ogrenci->close();

// Öğrencinin mevcut sınıflarını çek
$mevcut_siniflar_sorgu = $conn->query("SELECT sinif_id FROM ogrenci_sinif_iliskisi WHERE ogrenci_id = $ogrenci_id");
$mevcut_siniflar_idler = array_column($mevcut_siniflar_sorgu->fetch_all(MYSQLI_ASSOC), 'sinif_id');

// Öğrencinin atanabileceği, henüz atanmamış olan tüm sınıfları çek
$atanabilir_siniflar = $conn->query("SELECT * FROM siniflar WHERE id NOT IN (" . (empty($mevcut_siniflar_idler) ? '0' : implode(',', $mevcut_siniflar_idler)) . ") ORDER BY sinif_adi")->fetch_all(MYSQLI_ASSOC);

// Öğrencinin atanmış olduğu sınıfların adlarını çek
$atanmis_siniflar_detay = [];
if (!empty($mevcut_siniflar_idler)) {
    $atanmis_siniflar_detay = $conn->query("SELECT id, sinif_adi FROM siniflar WHERE id IN (" . implode(',', $mevcut_siniflar_idler) . ")")->fetch_all(MYSQLI_ASSOC);
}

?>
<div class="card">
    <div class="card-header">
        <div>
            <h1>Öğrenci Sınıf Atama</h1>
            <p class="subtitle"><strong><?= htmlspecialchars($ogrenci['ad_soyad']) ?></strong> adlı öğrencinin sınıf atamalarını yönetin.</p>
        </div>
        <a href="panel.php?sayfa=atamalar" class="btn btn-secondary">← Atama Yönetimine Geri Dön</a>
    </div>
    <div class="card-body">
        <form action="actions/atama_kaydet.php" method="POST" class="styled-form">
            <input type="hidden" name="islem" value="ogrenci_sinif_ata">
            <input type="hidden" name="ogrenci_id" value="<?= $ogrenci['id'] ?>">
            
            <!-- SINIF EKLEME BÖLÜMÜ -->
            <div class="atama-ekleme-formu">
                <div class="form-group">
                    <label for="ogrenci-sinif-sec">Atanacak Sınıfı Seçin</label>
                    <div class="input-grup">
                        <select id="ogrenci-sinif-sec">
                            <option value="">Seçiniz...</option>
                            <?php foreach ($atanabilir_siniflar as $sinif): ?>
                                <option value="<?= $sinif['id'] ?>"><?= htmlspecialchars($sinif['sinif_adi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="ogrenci-sinif-ekle-btn" class="btn btn-info">Ekle</button>
                    </div>
                </div>
            </div>

            <!-- ATANMIŞ SINIFLAR LİSTESİ -->
            <div class="form-group">
                <label>Atanmış Sınıflar</label>
                <div class="atanan-listesi" id="atanan-siniflar-listesi">
                    <?php if (empty($atanmis_siniflar_detay)): ?>
                        <p id="sinif-henuz-yok" style="color: #888;">Bu öğrenciye henüz sınıf atanmamış.</p>
                    <?php else: ?>
                        <?php foreach ($atanmis_siniflar_detay as $atanmis_sinif): ?>
                            <div class="atanan-item">
                                <span><?= htmlspecialchars($atanmis_sinif['sinif_adi']) ?></span>
                                <input type="hidden" name="siniflar[]" value="<?= $atanmis_sinif['id'] ?>">
                                <button type="button" class="btn-kaldir-item">Kaldır</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Atamaları Kaydet</button>
            </div>
        </form>
    </div>
</div>

