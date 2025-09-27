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
    <h1>Ödev Takibi</h1>
    <p>Çocuğunuzun ödev durumunu takip edin ve teslim edilen ödevlere onay verin.</p>
</div>
<div class="panel-content">
    
    <?php if (count($ogrenciler) > 1): ?>
    <div class="panel-filters card">
        <form method="GET">
            <input type="hidden" name="sayfa" value="odev_takibi">
            <div class="form-group">
                <label for="ogrenci-filtre">Öğrenci Seçimi</label>
                <select id="ogrenci-filtre" name="ogrenci_id" onchange="this.form.submit()">
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

    <div class="liste-container card">
        <h3>
            <?php 
            if ($secilen_ogrenci_id > 0) {
                foreach($ogrenciler as $o) { if ($o['id'] == $secilen_ogrenci_id) echo htmlspecialchars($o['ad_soyad']); }
            } else if (count($ogrenciler) == 1) {
                echo htmlspecialchars($ogrenciler[0]['ad_soyad']);
            }
            ?> - Ödev Listesi
        </h3>
        <?php
        if (isset($_SESSION['form_mesaji_onay'])) {
            echo '<div class="form-message success">' . $_SESSION['form_mesaji_onay'] . '</div>';
            unset($_SESSION['form_mesaji_onay']);
        }
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Branş</th>
                    <th>Ödev Başlığı</th>
                    <th>Teslim Tarihi</th>
                    <th>Durum</th>
                    <th>Yapılacak İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($secilen_ogrenci_id > 0) {
                    $sinif_idler_sorgu = $conn->query("SELECT sinif_id FROM ogrenci_sinif_iliskisi WHERE ogrenci_id = $secilen_ogrenci_id");
                    $sinif_idler = array_column($sinif_idler_sorgu->fetch_all(MYSQLI_ASSOC), 'sinif_id');

                    if (!empty($sinif_idler)) {
                        $sinif_idler_str = implode(',', $sinif_idler);
                        $sql = "SELECT o.id, o.baslik, o.teslim_tarihi, b.brans_adi, ot.id as teslim_id, ot.durum FROM odevler o INNER JOIN branslar b ON o.brans_id = b.id LEFT JOIN odev_teslimleri ot ON o.id = ot.odev_id AND ot.ogrenci_id = ? WHERE o.sinif_id IN ($sinif_idler_str) ORDER BY o.teslim_tarihi DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $secilen_ogrenci_id);
                        $stmt->execute();
                        $odevler_result = $stmt->get_result();

                        if ($odevler_result->num_rows > 0) {
                            while($odev = $odevler_result->fetch_assoc()) {
                                $durum = $odev['durum'] ?? 'Atandı';
                                if (strtotime($odev['teslim_tarihi']) < time() && $durum == 'Atandı') $durum = 'Süresi Geçti';
                                
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($odev['brans_adi']) . '</td>';
                                echo '<td>' . htmlspecialchars($odev['baslik']) . '</td>';
                                echo '<td>' . date('d/m/Y H:i', strtotime($odev['teslim_tarihi'])) . '</td>';
                                echo '<td><span class="badge badge-'. strtolower(str_replace(' ', '_', $durum)) .'">' . htmlspecialchars($durum) . '</span></td>';
                                echo '<td>';
                                if ($durum == 'Notlandırıldı') {
                                    echo '<a href="panel.php?sayfa=veli_odev_detay&odev_id='.$odev['id'].'&ogrenci_id='.$secilen_ogrenci_id.'" class="btn btn-primary btn-sm">Detaylar</a>';
                                } else if ($durum != 'Süresi Geçti') {
                                    echo '<a href="panel.php?sayfa=veli_odev_detay&odev_id='.$odev['id'].'&ogrenci_id='.$secilen_ogrenci_id.'" class="btn btn-secondary btn-sm">İşlem Yap</a>';
                                } else {
                                    echo '<i>İşlem Yok</i>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5">Öğrenciye atanmış ödev bulunmuyor.</td></tr>';
                        }
                    } else {
                         echo '<tr><td colspan="5">Öğrenci herhangi bir sınıfa kayıtlı değil.</td></tr>';
                    }
                } else if (!empty($ogrenciler)) {
                     echo '<tr><td colspan="5">Lütfen bir öğrenci seçin.</td></tr>';
                } else {
                    echo '<tr><td colspan="5">Sorumlu olduğunuz öğrenci bulunmamaktadır.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
