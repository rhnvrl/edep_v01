<?php
require_once 'includes/db_connect.php';
$veli_id = $_SESSION['kullanici_id'];
$odev_id = isset($_GET['odev_id']) ? (int)$_GET['odev_id'] : 0;
$ogrenci_id = isset($_GET['ogrenci_id']) ? (int)$_GET['ogrenci_id'] : 0;

if ($odev_id === 0 || $ogrenci_id === 0) die("Geçersiz bilgi.");

// Güvenlik kontrolü: Bu veli bu öğrenciye bağlı mı?
$check_stmt = $conn->prepare("SELECT veli_id FROM veli_ogrenci_iliskisi WHERE veli_id = ? AND ogrenci_id = ?");
$check_stmt->bind_param("ii", $veli_id, $ogrenci_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows === 0) die("Bu sayfayı görüntüleme yetkiniz yok.");
$check_stmt->close();

// Ödev ve teslimat bilgilerini çek
$sql = "SELECT o.baslik, o.aciklama, b.brans_adi, ot.id as teslim_id, ot.durum, ot.puan, ot.ogretmen_notu, ot.veli_onay_tarihi FROM odevler o INNER JOIN branslar b ON o.brans_id = b.id LEFT JOIN odev_teslimleri ot ON o.id = ot.odev_id AND ot.ogrenci_id = ? WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $ogrenci_id, $odev_id);
$stmt->execute();
$odev = $stmt->get_result()->fetch_assoc();
if (!$odev) die("Ödev bulunamadı.");
$stmt->close();

// Mevcut dosyaları çek
$mevcut_dosyalar = [];
if ($odev['teslim_id']) {
    $dosya_sorgu = $conn->prepare("SELECT dosya_yolu FROM odev_dosyalari WHERE teslim_id = ?");
    $dosya_sorgu->bind_param("i", $odev['teslim_id']);
    $dosya_sorgu->execute();
    $mevcut_dosyalar = $dosya_sorgu->get_result()->fetch_all(MYSQLI_ASSOC);
    $dosya_sorgu->close();
}
?>
<div class="panel-header">
    <h1>Ödev Detayı ve Onay</h1>
    <p><a href="panel.php?sayfa=odev_takibi">← Ödev Takibine Geri Dön</a></p>
</div>
<div class="panel-content">
    <div class="iletisim-grid">
        <div class="card">
            <h3><?php echo htmlspecialchars($odev['baslik']); ?></h3>
            <p><strong>Branş:</strong> <?php echo htmlspecialchars($odev['brans_adi']); ?></p>
            <hr>
            <p><?php echo nl2br(htmlspecialchars($odev['aciklama'])); ?></p>
        </div>
        <div class="card">
            <h3>Teslimat ve Not Durumu</h3>
            <?php if ($odev['durum'] == 'Notlandırıldı'): ?>
                <div class="ortalama-kutusu"><strong>Puan:</strong> <?php echo htmlspecialchars($odev['puan']); ?> / 100</div>
                <p><strong>Öğretmen Değerlendirmesi:</strong></p>
                <p><?php echo nl2br(htmlspecialchars($odev['ogretmen_notu'])); ?></p>
            <?php else: ?>
                <p>Bu ödev henüz öğretmen tarafından notlandırılmamıştır.</p>
            <?php endif; ?>
        </div>
    </div>
    <hr style="margin: 30px 0;">
    <div class="card">
        <h3>Dosya Yükleme ve Onaylama</h3>
        <?php
        if (isset($_SESSION['mesaj'])) { echo '<div class="alert alert-' . $_SESSION['mesaj_tur'] . '">' . $_SESSION['mesaj'] . '</div>'; unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); }
        ?>

        <?php if (!empty($mevcut_dosyalar)): ?>
            <p><strong>Yüklenmiş Dosyalar:</strong></p>
            <ul class="file-list">
                <?php foreach($mevcut_dosyalar as $dosya): ?>
                    <li><a href="<?php echo htmlspecialchars($dosya['dosya_yolu']); ?>" target="_blank"><?php echo basename($dosya['dosya_yolu']); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <hr>
        <?php endif; ?>

        <form action="actions/veli_odev_action.php" method="POST" enctype="multipart/form-data" class="styled-form" style="margin-bottom: 20px;">
            <input type="hidden" name="odev_id" value="<?php echo $odev_id; ?>">
            <input type="hidden" name="ogrenci_id" value="<?php echo $ogrenci_id; ?>">
            <div class="form-group">
                <label>Yeni Dosya Yükle (Toplu Seçim Yapabilirsiniz)</label>
                <input type="file" name="odev_dosyalari[]" multiple>
            </div>
            <button type="submit" name="dosya_yukle" class="btn btn-secondary">Seçili Dosyaları Yükle</button>
        </form>
        
        <hr>
        <h4>İşlemi Tamamla</h4>
        <form action="actions/veli_odev_action.php" method="POST" class="styled-form">
            <input type="hidden" name="odev_id" value="<?php echo $odev_id; ?>">
            <input type="hidden" name="ogrenci_id" value="<?php echo $ogrenci_id; ?>">
            <div class="form-actions" style="display: flex; gap: 10px;">
                <button type="submit" name="onayla" class="btn btn-primary">Ödevi Onayla</button>
                <a href="actions/veli_odev_action.php?islem=reddet&odev_id=<?php echo $odev_id; ?>&ogrenci_id=<?php echo $ogrenci_id; ?>" class="btn btn-danger btn-sil">Reddet</a>
            </div>
        </form>
    </div>
</div>

