<?php
require_once 'includes/db_connect.php';
$veli_id = $_SESSION['kullanici_id'];

// Veliye bağlı öğrencileri çek
$ogrenciler_sorgu = $conn->query("
    SELECT k.id, k.ad_soyad 
    FROM kullanicilar k
    INNER JOIN veli_ogrenci_iliskisi voi ON k.id = voi.ogrenci_id
    WHERE voi.veli_id = $veli_id
");
$ogrenciler = $ogrenciler_sorgu->fetch_all(MYSQLI_ASSOC);

// Seçilen öğrenciyi belirle
$secilen_ogrenci_id = $ogrenciler[0]['id'] ?? 0;
if (count($ogrenciler) > 1 && isset($_GET['ogrenci_id'])) {
    $secilen_ogrenci_id = (int)$_GET['ogrenci_id'];
}
?>

<div class="panel-header">
    <h1>Gelişim Raporları</h1>
    <p>Çocuğunuzun notlandırılmış ödevlerini ve başarı durumunu takip edin.</p>
</div>
<div class="panel-content">
    
    <?php if (count($ogrenciler) > 1): ?>
    <div class="panel-filters card">
        <form method="GET">
            <input type="hidden" name="sayfa" value="raporlar">
            <div class="form-group">
                <label for="ogrenci-rapor-filtre">Raporunu Görüntülemek İstediğiniz Öğrenci:</label>
                <select id="ogrenci-rapor-filtre" name="ogrenci_id" onchange="this.form.submit()">
                    <?php foreach ($ogrenciler as $ogrenci): ?>
                        <option value="<?php echo $ogrenci['id']; ?>" <?php echo ($secilen_ogrenci_id == $ogrenci['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ogrenci['ad_soyad']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <?php
    if ($secilen_ogrenci_id > 0):
        // Seçilen öğrencinin notlandırılmış ödevlerini çek
        $sql = "
            SELECT 
                o.baslik, b.brans_adi, k.ad_soyad as ogretmen_adi,
                ot.puan, ot.ogretmen_notu
            FROM odev_teslimleri ot
            INNER JOIN odevler o ON ot.odev_id = o.id
            INNER JOIN branslar b ON o.brans_id = b.id
            INNER JOIN kullanicilar k ON o.ogretmen_id = k.id
            WHERE ot.ogrenci_id = ? AND ot.durum = 'Notlandırıldı' AND ot.puan IS NOT NULL
            ORDER BY o.teslim_tarihi DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $secilen_ogrenci_id);
        $stmt->execute();
        $raporlar_result = $stmt->get_result();
        
        // Genel Not Ortalamasını Hesapla
        $ortalama_sorgu = $conn->query("SELECT AVG(puan) as ortalama FROM odev_teslimleri WHERE ogrenci_id = $secilen_ogrenci_id AND puan IS NOT NULL");
        $ortalama = $ortalama_sorgu->fetch_assoc()['ortalama'];
    ?>
    <div class="liste-container card">
        <h3>
            <?php 
            foreach($ogrenciler as $o) {
                if ($o['id'] == $secilen_ogrenci_id) echo htmlspecialchars($o['ad_soyad']);
            }
            ?> - Gelişim Raporu
        </h3>
        
        <?php if ($ortalama !== null): ?>
        <div class="ortalama-kutusu">
            <strong>Genel Not Ortalaması:</strong> <?php echo round($ortalama, 2); ?> / 100
        </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Branş</th>
                    <th>Ödev Başlığı</th>
                    <th>Puan</th>
                    <th>Öğretmen Değerlendirmesi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($raporlar_result->num_rows > 0): ?>
                    <?php while($rapor = $raporlar_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rapor['brans_adi']); ?></td>
                        <td><?php echo htmlspecialchars($rapor['baslik']); ?></td>
                        <td><strong><?php echo htmlspecialchars($rapor['puan']); ?></strong></td>
                        <td><?php echo nl2br(htmlspecialchars($rapor['ogretmen_notu'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Henüz notlandırılmış bir ödev bulunmuyor.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="card">
            <p>Görüntülenecek öğrenci bulunmuyor.</p>
        </div>
    <?php endif; ?>
</div>
