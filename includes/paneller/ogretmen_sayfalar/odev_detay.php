<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];
$odev_id = isset($_GET['odev_id']) ? intval($_GET['odev_id']) : 0;

if ($odev_id <= 0) {
    die("Geçersiz Ödev ID'si.");
}

// Ödev bilgilerini ve öğretmenin bu ödeve yetkisi olup olmadığını kontrol et
$stmt_odev = $conn->prepare("SELECT o.*, s.sinif_adi, b.brans_adi 
                             FROM odevler o
                             JOIN siniflar s ON o.sinif_id = s.id
                             JOIN branslar b ON o.brans_id = b.id
                             WHERE o.id = ? AND o.ogretmen_id = ?");
$stmt_odev->bind_param("ii", $odev_id, $ogretmen_id);
$stmt_odev->execute();
$result_odev = $stmt_odev->get_result();
if ($result_odev->num_rows == 0) {
    die("Ödev bulunamadı veya bu ödevi görüntüleme yetkiniz yok.");
}
$odev = $result_odev->fetch_assoc();
$stmt_odev->close();

// Ödeve atanan öğrencileri ve teslim durumlarını çek
$stmt_teslimler = $conn->prepare("
    SELECT 
        k.id as ogrenci_id, k.ad_soyad,
        ot.id as teslim_id, ot.durum, ot.notu, ot.ogretmen_yorumu,
        (SELECT COUNT(*) FROM odev_dosyalari od WHERE od.teslim_id = ot.id) as dosya_sayisi
    FROM kullanicilar k
    JOIN ogrenci_sinif_iliskisi osi ON k.id = osi.ogrenci_id
    LEFT JOIN odev_teslimleri ot ON k.id = ot.ogrenci_id AND ot.odev_id = ?
    WHERE osi.sinif_id = ? AND k.rol = 'ogrenci'
    ORDER BY k.ad_soyad ASC
");
$stmt_teslimler->bind_param("ii", $odev_id, $odev['sinif_id']);
$stmt_teslimler->execute();
$teslimler = $stmt_teslimler->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_teslimler->close();

?>

<div class="card">
    <div class="card-header">
        <div>
            <h1>Ödev Sonuçları: <?= htmlspecialchars($odev['baslik']) ?></h1>
            <p class="subtitle"><?= htmlspecialchars($odev['sinif_adi']) ?> - <?= htmlspecialchars($odev['brans_adi']) ?></p>
        </div>
        <a href="panel.php?sayfa=odev_yonetimi" class="btn btn-secondary">Geri Dön</a>
    </div>
    <div class="card-body">
        
        <div class="odev-detay-kutusu">
            <h4>Ödev Açıklaması</h4>
            <p><?= nl2br(htmlspecialchars($odev['aciklama'])) ?></p>
            <hr>
            <small><strong>Son Teslim Tarihi:</strong> <?= date('d.m.Y H:i', strtotime($odev['teslim_tarihi'])) ?></small>
        </div>

        <h4 style="margin-top: 30px; margin-bottom: 20px;">Öğrenci Teslimleri</h4>
        <form action="actions/odev_notlandir_action.php" method="POST">
            <input type="hidden" name="odev_id" value="<?= $odev_id ?>">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Öğrenci Adı</th>
                            <th>Durum</th>
                            <th>Dosyalar</th>
                            <th>Not (0-100)</th>
                            <th>Öğretmen Yorumu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($teslimler)) {
                            echo '<tr><td colspan="5" style="text-align:center;">Bu ödeve atanmış öğrenci bulunmuyor.</td></tr>';
                        } else {
                            foreach ($teslimler as $teslim) {
                                $durum = $teslim['durum'] ?? 'Teslim Etmedi';
                                $teslim_id = $teslim['teslim_id'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($teslim['ad_soyad']) ?></td>
                                    <td>
                                        <?php 
                                        $durum_class = str_replace([' ', '-'], '_', strtolower($durum));
                                        $durum_class = preg_replace('/[^a-z0-9_]/', '', $durum_class);
                                        ?>
                                        <span class="badge badge-<?= $durum_class ?>"><?= htmlspecialchars($durum) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($teslim['dosya_sayisi'] > 0): 
                                            $stmt_dosyalar = $conn->prepare("SELECT dosya_yolu FROM odev_dosyalari WHERE teslim_id = ?");
                                            $stmt_dosyalar->bind_param("i", $teslim_id);
                                            $stmt_dosyalar->execute();
                                            $dosyalar = $stmt_dosyalar->get_result()->fetch_all(MYSQLI_ASSOC);
                                            $stmt_dosyalar->close();
                                        ?>
                                            <div class="file-gallery-container">
                                                <button type="button" class="btn btn-info btn-sm open-lightbox-btn">
                                                    <?= $teslim['dosya_sayisi'] ?> Dosyayı Görüntüle
                                                </button>
                                                <a href="actions/download_files.php?teslim_id=<?= $teslim_id ?>" class="btn btn-success btn-sm btn-download">indir</a>
                                                <div class="d-none">
                                                    <?php foreach ($dosyalar as $dosya): ?>
                                                        <a href="<?= htmlspecialchars($dosya['dosya_yolu']) ?>"></a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($teslim_id): ?>
                                        <input type="hidden" name="teslim_id[]" value="<?= $teslim_id ?>">
                                        <input type="number" name="notu[<?= $teslim_id ?>]" class="form-control" style="max-width: 80px; padding: 5px;" value="<?= htmlspecialchars($teslim['notu'] ?? '') ?>" min="0" max="100">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                         <?php if ($teslim_id): ?>
                                        <textarea name="ogretmen_yorumu[<?= $teslim_id ?>]" class="form-control" style="width: 100%; padding: 5px;" rows="1"><?= htmlspecialchars($teslim['ogretmen_yorumu'] ?? '') ?></textarea>
                                         <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            } // foreach sonu
                        } // else sonu
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Tüm Notları Kaydet</button>
            </div>
        </form>
    </div>
</div>