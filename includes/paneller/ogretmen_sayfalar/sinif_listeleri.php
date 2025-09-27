<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];

// Öğretmenin sorumlu olduğu sınıfları çek
$stmt_siniflar = $conn->prepare("
    SELECT s.id as sinif_id, s.sinif_adi 
    FROM siniflar s
    JOIN ogretmen_sinif_iliskisi osi ON s.id = osi.sinif_id
    WHERE osi.ogretmen_id = ?
    ORDER BY s.sinif_adi ASC
");
$stmt_siniflar->bind_param("i", $ogretmen_id);
$stmt_siniflar->execute();
$siniflar = $stmt_siniflar->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_siniflar->close();

?>

<div class="card">
    <div class="card-header">
        <h1>Sınıf Listeleri</h1>
    </div>
    <div class="card-body">
        <p class="subtitle" style="margin-bottom: 30px;">Sorumlu olduğunuz sınıflardaki öğrenci listelerini görüntüleyin.</p>

        <?php if (count($siniflar) > 0): ?>
            <?php foreach ($siniflar as $sinif): 
                // Her sınıf için öğrencileri çek
                $stmt_ogrenciler = $conn->prepare("
                    SELECT k.id, k.ad_soyad 
                    FROM kullanicilar k
                    JOIN ogrenci_sinif_iliskisi osi ON k.id = osi.ogrenci_id
                    WHERE osi.sinif_id = ? AND k.rol = 'ogrenci'
                    ORDER BY k.ad_soyad ASC
                ");
                $stmt_ogrenciler->bind_param("i", $sinif['sinif_id']);
                $stmt_ogrenciler->execute();
                $ogrenciler = $stmt_ogrenciler->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt_ogrenciler->close();
            ?>
                <div class="sinif-ogrenci-karti" style="margin-bottom: 30px;">
                    <h3 style="margin-bottom: 15px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; color: var(--secondary-color);"><?= htmlspecialchars($sinif['sinif_adi']) ?></h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Öğrenci Adı Soyadı</th>
                                    <th style="width: 200px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($ogrenciler) > 0): ?>
                                    <?php foreach ($ogrenciler as $ogrenci): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ogrenci['ad_soyad']) ?></td>
                                            <td>
                                                <a href="panel.php?sayfa=ogrenci_rapor_detay&ogrenci_id=<?= $ogrenci['id'] ?>" class="btn btn-primary btn-sm">Raporu Görüntüle</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center;">Bu sınıfa atanmış öğrenci bulunmuyor.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">Henüz sorumlu olduğunuz bir sınıf bulunmuyor.</p>
        <?php endif; ?>
    </div>
</div>