<?php
require_once 'includes/db_connect.php';

// Filtreleme için tüm sınıfları çek
$siniflar = $conn->query("SELECT id, sinif_adi FROM siniflar ORDER BY seviye, sinif_adi");

// Filtreleme koşulunu oluştur
$where_kosulu = "WHERE k.rol = 'ogrenci'"; // Ana koşul
$secilen_sinif_id = 0;
if (isset($_GET['sinif_id']) && !empty($_GET['sinif_id'])) {
    $secilen_sinif_id = (int)$_GET['sinif_id'];
    // Ana koşula ek filtreyi AND ile ekle
    $where_kosulu .= " AND osi.sinif_id = " . $secilen_sinif_id;
}
?>
<div class="panel-header">
    <h1>Genel Öğrenci Raporları</h1>
    <p>Tüm öğrencilerin ödev teslim durumlarını ve başarı ortalamalarını izleyin.</p>
</div>
<div class="panel-content">
    <div class="panel-filters card">
        <form method="GET">
            <input type="hidden" name="sayfa" value="genel_raporlar">
            <div class="form-group">
                <label for="sinif-rapor-filtre">Sınıfa Göre Filtrele</label>
                <select id="sinif-rapor-filtre" name="sinif_id" onchange="this.form.submit()">
                    <option value="">Tüm Öğrenciler</option>
                    <?php while($sinif = $siniflar->fetch_assoc()): ?>
                        <option value="<?php echo $sinif['id']; ?>" <?php echo ($secilen_sinif_id == $sinif['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sinif['sinif_adi']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
    </div>

    <div class="liste-container card">
        <h3>Öğrenci İstatistikleri</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Öğrenci Adı Soyadı</th>
                    <th>Toplam Ödev</th>
                    <th>Teslim Edilen</th>
                    <th>Teslim Oranı</th>
                    <th>Not Ortalaması</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Filtrelenmiş öğrencileri çekmek için tek ve doğru sorgu
                $sql_ogrenciler = "
                    SELECT k.id, k.ad_soyad 
                    FROM kullanicilar k 
                    INNER JOIN ogrenci_sinif_iliskisi osi ON k.id = osi.ogrenci_id 
                    $where_kosulu 
                    GROUP BY k.id 
                    ORDER BY k.ad_soyad";
                $ogrenciler_filtrelenmis = $conn->query($sql_ogrenciler);

                if ($ogrenciler_filtrelenmis && $ogrenciler_filtrelenmis->num_rows > 0):
                    while($ogrenci = $ogrenciler_filtrelenmis->fetch_assoc()):
                        // Her öğrenci için istatistikleri hesapla
                        $ogrenci_id = $ogrenci['id'];
                        $stats_sql = "
                            SELECT 
                                (SELECT COUNT(o.id) FROM odevler o INNER JOIN ogrenci_sinif_iliskisi osi_inner ON o.sinif_id = osi_inner.sinif_id WHERE osi_inner.ogrenci_id = $ogrenci_id) as toplam_odev,
                                (SELECT COUNT(ot.id) FROM odev_teslimleri ot WHERE ot.ogrenci_id = $ogrenci_id) as teslim_edilen,
                                (SELECT AVG(ot.puan) FROM odev_teslimleri ot WHERE ot.ogrenci_id = $ogrenci_id AND ot.puan IS NOT NULL) as not_ortalamasi
                        ";
                        $stats_result = $conn->query($stats_sql)->fetch_assoc();
                        $toplam_odev = $stats_result['toplam_odev'] ?? 0;
                        $teslim_edilen = $stats_result['teslim_edilen'] ?? 0;
                        $teslim_orani = ($toplam_odev > 0) ? round(($teslim_edilen / $toplam_odev) * 100) : 0;
                        $not_ortalamasi = $stats_result['not_ortalamasi'] ? round($stats_result['not_ortalamasi'], 2) : 'N/A';
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ogrenci['ad_soyad']); ?></td>
                        <td><?php echo $toplam_odev; ?></td>
                        <td><?php echo $teslim_edilen; ?></td>
                        <td>%<?php echo $teslim_orani; ?></td>
                        <td><?php echo $not_ortalamasi; ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5">Seçilen kritere uygun öğrenci bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
