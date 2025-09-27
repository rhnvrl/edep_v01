<?php
// Bu sayfanın içeriği includes/paneller/admin_paneli.php tarafından çağrılır.
global $conn;
$veli_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($veli_id <= 0) {
    die("Geçersiz Veli ID'si.");
}

// Veli bilgilerini çek
$stmt_veli = $conn->prepare("SELECT * FROM kullanicilar WHERE id = ? AND rol = 'veli'");
$stmt_veli->bind_param("i", $veli_id);
$stmt_veli->execute();
$result_veli = $stmt_veli->get_result();
if ($result_veli->num_rows == 0) {
    die("Veli bulunamadı.");
}
$veli = $result_veli->fetch_assoc();
$stmt_veli->close();

// Bu veliye atanmış öğrencileri çek
$stmt_atanmis = $conn->prepare("SELECT k.id, k.ad_soyad FROM kullanicilar k JOIN veli_ogrenci_iliskisi voi ON k.id = voi.ogrenci_id WHERE voi.veli_id = ?");
$stmt_atanmis->bind_param("i", $veli_id);
$stmt_atanmis->execute();
$atanmis_ogrenciler = $stmt_atanmis->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_atanmis->close();

// Henüz HİÇBİR veliye atanmamış öğrencileri çek
$atanmamis_ogrenciler = $conn->query("
    SELECT id, ad_soyad 
    FROM kullanicilar 
    WHERE rol = 'ogrenci' AND id NOT IN (SELECT DISTINCT ogrenci_id FROM veli_ogrenci_iliskisi)
    ORDER BY ad_soyad
")->fetch_all(MYSQLI_ASSOC);

?>
<div class="card">
    <div class="card-header">
        <div>
            <h1>Veli - Öğrenci İlişkilendirme</h1>
            <p class="subtitle"><strong><?= htmlspecialchars($veli['ad_soyad']) ?></strong> adlı veliye sorumlu olduğu öğrencileri atayın.</p>
        </div>
        <a href="panel.php?sayfa=atamalar" class="btn btn-secondary">← Atama Yönetimine Geri Dön</a>
    </div>
    <div class="card-body">
        <form action="actions/atama_kaydet.php" method="POST" class="styled-form">
            <input type="hidden" name="islem" value="veli_ogrenci_ata">
            <input type="hidden" name="veli_id" value="<?= $veli['id'] ?>">
            
            <div class="form-group">
                <label>Sorumlu Olduğu Öğrenciler</label>
                <div id="atanan-ogrenciler-listesi" class="atanan-listesi">
                    <?php if (count($atanmis_ogrenciler) > 0): ?>
                        <?php foreach($atanmis_ogrenciler as $ogrenci): ?>
                            <div class="atanan-item">
                                <span><?= htmlspecialchars($ogrenci['ad_soyad']) ?></span>
                                <input type="hidden" name="ogrenciler[]" value="<?= $ogrenci['id'] ?>">
                                <button type="button" class="btn-kaldir-item">Kaldır</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p id="henuz-yok" style="color: #888;">Bu veliye henüz öğrenci atanmamış.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="ogrenci-sec">Listeye Öğrenci Ekle</label>
                <div style="display: flex; gap: 10px;">
                    <select id="ogrenci-sec" style="flex-grow: 1;">
                        <option value="">Atanmamış bir öğrenci seçin...</option>
                        <?php foreach($atanmamis_ogrenciler as $ogrenci): ?>
                            <option value="<?= $ogrenci['id'] ?>"><?= htmlspecialchars($ogrenci['ad_soyad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="ogrenci-ekle-btn" class="btn btn-success">Ekle</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">İlişkileri Kaydet</button>
            </div>
        </form>
    </div>
</div>