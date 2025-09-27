<?php
require_once 'includes/db_connect.php';
$ogrenci_id = $_SESSION['kullanici_id'];
$odev_id = isset($_GET['odev_id']) ? (int)$_GET['odev_id'] : 0;

if ($odev_id === 0) {
    die("Geçersiz ödev ID'si.");
}

// Ödevin detaylarını ve öğrencinin bu ödeve ait teslim bilgilerini çek
$sql = "
    SELECT 
        o.id, o.baslik, o.aciklama, o.teslim_tarihi,
        b.brans_adi,
        k.ad_soyad as ogretmen_adi,
        ot.id as teslim_id,
        ot.teslim_tarihi as odev_teslim_tarihi,
        ot.durum,
        ot.puan,
        ot.ogretmen_notu
    FROM odevler o
    INNER JOIN branslar b ON o.brans_id = b.id
    INNER JOIN kullanicilar k ON o.ogretmen_id = k.id
    LEFT JOIN odev_teslimleri ot ON o.id = ot.odev_id AND ot.ogrenci_id = ?
    WHERE o.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $ogrenci_id, $odev_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ödev bulunamadı veya bu ödeve erişim yetkiniz yok.");
}
$odev = $result->fetch_assoc();
$stmt->close();


// Teslim edilmiş dosyaları çek
$dosyalar = [];
if ($odev['teslim_id']) {
    $dosya_sorgu = $conn->prepare("SELECT dosya_yolu, yukleyen_kullanici_id FROM odev_dosyalari WHERE teslim_id = ?");
    $dosya_sorgu->bind_param("i", $odev['teslim_id']);
    $dosya_sorgu->execute();
    $dosyalar_result = $dosya_sorgu->get_result();
    while($dosya = $dosyalar_result->fetch_assoc()){
        $dosyalar[] = $dosya;
    }
    $dosya_sorgu->close();
}
?>

<div class="panel-header">
    <h1>Ödev Detayı ve Teslim</h1>
    <p><a href="panel.php?sayfa=odevlerim">← Ödev Listesine Geri Dön</a></p>
</div>
<div class="panel-content">
    <div class="iletisim-grid">
        <!-- Ödev Bilgileri -->
        <div class="card">
            <h3><?php echo htmlspecialchars($odev['baslik']); ?></h3>
            <p><strong>Branş:</strong> <?php echo htmlspecialchars($odev['brans_adi']); ?></p>
            <p><strong>Öğretmen:</strong> <?php echo htmlspecialchars($odev['ogretmen_adi']); ?></p>
            <p><strong>Son Teslim Tarihi:</strong> <?php echo date('d/m/Y H:i', strtotime($odev['teslim_tarihi'])); ?></p>
            <hr style="margin: 15px 0;">
            <strong>Açıklama:</strong>
            <p><?php echo nl2br(htmlspecialchars($odev['aciklama'])); ?></p>
        </div>

        <!-- Ödev Teslim Formu ve Durumu -->
        <div class="card">
            <h3>Ödev Teslim Durumu</h3>
            <?php
            if (isset($_SESSION['form_mesaji_teslim'])) {
                echo '<div class="form-message success">' . $_SESSION['form_mesaji_teslim'] . '</div>';
                unset($_SESSION['form_mesaji_teslim']);
            }
             if (isset($_SESSION['form_hatasi_teslim'])) {
                echo '<div class="form-message error">' . $_SESSION['form_hatasi_teslim'] . '</div>';
                unset($_SESSION['form_hatasi_teslim']);
            }
            ?>

            <?php if ($odev['teslim_id']): // Ödev daha önce teslim edilmişse ?>
                <p><strong>Durum:</strong> <span class="badge badge-<?php echo strtolower(str_replace(' ', '_', $odev['durum'])); ?>"><?php echo htmlspecialchars($odev['durum']); ?></span></p>
                <p><strong>Teslim Tarihi:</strong> <?php echo date('d/m/Y H:i', strtotime($odev['odev_teslim_tarihi'])); ?></p>
                
                <?php if (!empty($dosyalar)): ?>
                    <p><strong>Yüklenen Dosyalar:</strong></p>
                    <ul class="file-list">
                        <?php foreach($dosyalar as $dosya): ?>
                            <li><a href="<?php echo htmlspecialchars($dosya['dosya_yolu']); ?>" target="_blank"><?php echo basename($dosya['dosya_yolu']); ?></a> (Yükleyen: <?php echo $dosya['yukleyen_kullanici_id'] == $ogrenci_id ? 'Siz' : 'Veliniz'; ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><strong>Yüklenen Dosyalar:</strong> <i>Dosya yüklenmedi.</i></p>
                <?php endif; ?>

                <?php if ($odev['durum'] == 'Notlandırıldı'): ?>
                    <hr style="margin: 15px 0;">
                    <p><strong>Puan:</strong> <?php echo htmlspecialchars($odev['puan']); ?> / 100</p>
                    <p><strong>Öğretmen Notu:</strong> <?php echo nl2br(htmlspecialchars($odev['ogretmen_notu'])); ?></p>
                <?php endif; ?>

            <?php else: // Ödev henüz teslim edilmemişse ?>
                <?php if (strtotime($odev['teslim_tarihi']) > time()): // Teslim süresi geçmemişse ?>
                    <form action="actions/odev_teslim_action.php" method="POST" enctype="multipart/form-data" class="styled-form">
                        <input type="hidden" name="odev_id" value="<?php echo $odev_id; ?>">
                        <div class="form-group">
                            <label for="odev_dosyalari">Ödev Dosyaları Yükle (Birden fazla seçebilirsiniz)</label>
                            <input type="file" id="odev_dosyalari" name="odev_dosyalari[]" multiple>
                        </div>
                        <p style="text-align: center; font-weight: bold;">VEYA</p>
                        <p>Eğer ödevi yaptığını veline bildirmek istersen (veli dosyaları daha sonra yükleyecekse), aşağıdaki butonu kullanabilirsin.</p>
                        <div class="form-actions" style="display: flex; gap: 10px;">
                             <button type="submit" name="odev_teslim_et" class="btn btn-primary">Dosyaları Yükle ve Tamamla</button>
                             <button type="submit" name="odev_tamamlandi" class="btn btn-secondary">Dosyasız Tamamladım</button>
                        </div>
                    </form>
                <?php else: // Teslim süresi geçmişse ?>
                    <p class="form-message error">Bu ödevin teslim süresi geçmiştir.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
