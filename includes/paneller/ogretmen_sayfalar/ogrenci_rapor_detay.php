<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];
$ogrenci_id = isset($_GET['ogrenci_id']) ? intval($_GET['ogrenci_id']) : 0;

if ($ogrenci_id <= 0) {
    die("Geçersiz Öğrenci ID'si.");
}

// Öğrenci bilgilerini çek
$stmt_ogrenci = $conn->prepare("SELECT ad_soyad FROM kullanicilar WHERE id = ? AND rol = 'ogrenci'");
$stmt_ogrenci->bind_param("i", $ogrenci_id);
$stmt_ogrenci->execute();
$result_ogrenci = $stmt_ogrenci->get_result();
if ($result_ogrenci->num_rows == 0) {
    die("Öğrenci bulunamadı.");
}
$ogrenci = $result_ogrenci->fetch_assoc();
$stmt_ogrenci->close();


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
        <div>
            <h1>Öğrenci Gelişim Raporu</h1>
            <p class="subtitle">Öğrenci: <strong><?= htmlspecialchars($ogrenci['ad_soyad']) ?></strong></p>
        </div>
        <a href="panel.php?sayfa=sinif_listeleri" class="btn btn-secondary">← Sınıf Listelerine Geri Dön</a>
    </div>
    <div class="card-body">
        
        <!-- YENİ EKLENEN BÖLÜM: Not Ortalaması -->
        <div class="ortalama-kutusu">
             <strong>Genel Not Ortalaması:</strong> <?= $ortalama ?> / 100
        </div>
        
        <h4 style="margin-top: 30px; margin-bottom: 20px;">Detaylı Not Dökümü</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Branş</th>
                        <th>Ödev Başlığı</th>
                        <th>Puan</th>
                        <th>Değerlendirme</th>
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
                            <td colspan="4" style="text-align: center;">Öğrencinin henüz notlandırılmış bir ödevi bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>