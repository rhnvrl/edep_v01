<?php
// Bu sayfanın içeriği includes/paneller/ogrenci_paneli.php tarafından çağrılır.
global $conn;
$ogrenci_id = $_SESSION['kullanici_id'];

// Öğrencinin dahil olduğu sınıflardaki tüm ödevleri ve teslim durumlarını çek
$stmt = $conn->prepare("
    SELECT 
        o.id as odev_id, 
        o.baslik, 
        o.teslim_tarihi, 
        b.brans_adi,
        ot.durum
    FROM odevler o
    JOIN branslar b ON o.brans_id = b.id
    JOIN ogrenci_sinif_iliskisi osi ON o.sinif_id = osi.sinif_id
    LEFT JOIN odev_teslimleri ot ON o.id = ot.odev_id AND ot.ogrenci_id = ?
    WHERE osi.ogrenci_id = ?
    ORDER BY o.teslim_tarihi DESC
");
$stmt->bind_param("ii", $ogrenci_id, $ogrenci_id);
$stmt->execute();
$odevler = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="card">
    <div class="card-header">
        <h1>Ödevlerim</h1>
    </div>
    <div class="card-body">
        <p class="subtitle" style="margin-bottom: 30px;">Sana atanan tüm ödevleri buradan takip edebilir ve teslim edebilirsin.</p>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Branş</th>
                        <th>Ödev Başlığı</th>
                        <th>Son Teslim Tarihi</th>
                        <th>Durum</th>
                        <th style="width: 200px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($odevler) > 0): ?>
                        <?php foreach ($odevler as $odev): 
                            $durum = $odev['durum'] ?? 'Atandı';
                            $gecmis_mi = strtotime($odev['teslim_tarihi']) < time();
                            if ($durum == 'Atandı' && $gecmis_mi) {
                                $durum = 'Süresi Geçti';
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($odev['brans_adi']) ?></td>
                                <td><?= htmlspecialchars($odev['baslik']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($odev['teslim_tarihi'])) ?></td>
                                <td>
                                     <?php 
                                    $durum_class = str_replace([' ', '-'], '_', strtolower($durum));
                                    $durum_class = preg_replace('/[^a-z0-9_]/', '', $durum_class);
                                    ?>
                                    <span class="badge badge-<?= $durum_class ?>"><?= htmlspecialchars($durum) ?></span>
                                </td>
                                <td>
                                    <a href="panel.php?sayfa=odev_teslim_form&odev_id=<?= $odev['odev_id'] ?>" class="btn btn-primary btn-sm">Görüntüle ve Teslim Et</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <tr>
                            <td colspan="5" style="text-align: center;">Henüz sana atanmış bir ödev bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>