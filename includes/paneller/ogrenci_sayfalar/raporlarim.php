<?php
// Bu sayfanın içeriği includes/paneller/ogrenci_paneli.php tarafından çağrılır.
global $conn;
$ogrenci_id = $_SESSION['kullanici_id'];

// DÜZELTİLMİŞ SORGU: Sadece notu olan (notu NULL olmayan) teslimleri çek
$stmt_rapor = $conn->prepare("
    SELECT
        ot.notu,
        ot.ogretmen_yorumu,
        o.baslik AS odev_basligi,
        b.brans_adi
    FROM odev_teslimleri ot
    JOIN odevler o ON ot.odev_id = o.id
    JOIN branslar b ON o.brans_id = b.id
    WHERE ot.ogrenci_id = ? AND ot.notu IS NOT NULL
    ORDER BY o.teslim_tarihi DESC
");
$stmt_rapor->bind_param("i", $ogrenci_id);
$stmt_rapor->execute();
$notlandirilmis_odevler = $stmt_rapor->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_rapor->close();

// Genel not ortalamasını hesapla
$toplam_not = 0;
$not_sayisi = count($notlandirilmis_odevler);
if ($not_sayisi > 0) {
    foreach ($notlandirilmis_odevler as $odev) {
        $toplam_not += $odev['notu'];
    }
    $ortalama = round($toplam_not / $not_sayisi, 2);
} else {
    $ortalama = 0;
}

?>

<div class="card">
    <div class="card-header">
        <h1>Raporlarım</h1>
    </div>
    <div class="card-body">
        <p class="subtitle" style="margin-bottom: 30px;">Ödev notlarını ve genel başarı durumunu buradan takip edebilirsin.</p>

        <!-- YENİ EKLENEN BÖLÜM: Not Ortalaması -->
        <div class="ortalama-kutusu" style="background-color: var(--secondary-color); color: var(--light-text-color); padding: 15px; margin-bottom: 30px; border-radius: var(--border-radius); font-size: 1.2rem; text-align: center;">
             <strong>Genel Başarı Ortalaman:</strong> <?= $ortalama ?> / 100
        </div>
        
        <h4 style="margin-bottom: 20px;">Gelişim Raporu</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Branş</th>
                        <th>Ödev Başlığı</th>
                        <th>Puanın</th>
                        <th>Öğretmen Değerlendirmesi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($notlandirilmis_odevler) > 0): ?>
                        <?php foreach ($notlandirilmis_odevler as $odev): ?>
                            <tr>
                                <td><?= htmlspecialchars($odev['brans_adi']) ?></td>
                                <td><?= htmlspecialchars($odev['odev_basligi']) ?></td>
                                <td><strong><?= htmlspecialchars($odev['notu']) ?></strong></td>
                                <td><?= htmlspecialchars($odev['ogretmen_yorumu']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">Henüz notlandırılmış bir ödevin bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>