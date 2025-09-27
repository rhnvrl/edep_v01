<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];
$anket_id = isset($_GET['anket_id']) ? intval($_GET['anket_id']) : 0;

if ($anket_id <= 0) {
    die("Geçersiz Anket ID'si.");
}

// DÜZELTME: Doğru sütun adı kullanıldı (anket_basligi)
$stmt_anket = $conn->prepare("
    SELECT a.id, a.anket_basligi, a.olusturma_tarihi, s.sinif_adi 
    FROM anketler a
    JOIN siniflar s ON a.sinif_id = s.id
    WHERE a.id = ? AND a.ogretmen_id = ?
");
$stmt_anket->bind_param("ii", $anket_id, $ogretmen_id);
$stmt_anket->execute();
$result_anket = $stmt_anket->get_result();
if ($result_anket->num_rows == 0) {
    die("Anket bulunamadı veya bu anketi görüntüleme yetkiniz yok.");
}
$anket = $result_anket->fetch_assoc();
$stmt_anket->close();

// DÜZELTME: Doğru sütun adı kullanıldı (secilen_secenek_id)
$stmt_sonuclar = $conn->prepare("
    SELECT 
        ase.id as secenek_id, 
        ase.secenek_metni, 
        COUNT(ac.id) as oy_sayisi
    FROM anket_secenekleri ase
    LEFT JOIN anket_cevaplari ac ON ase.id = ac.secilen_secenek_id
    WHERE ase.anket_id = ?
    GROUP BY ase.id, ase.secenek_metni
    ORDER BY oy_sayisi DESC
");
$stmt_sonuclar->bind_param("i", $anket_id);
$stmt_sonuclar->execute();
$sonuclar = $stmt_sonuclar->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_sonuclar->close();
$toplam_oy = array_sum(array_column($sonuclar, 'oy_sayisi'));

// DÜZELTME: Doğru sütun adı kullanıldı (secilen_secenek_id)
$stmt_detayli_cevaplar = $conn->prepare("
    SELECT k.ad_soyad, ase.secenek_metni
    FROM anket_cevaplari ac
    JOIN kullanicilar k ON ac.ogrenci_id = k.id
    JOIN anket_secenekleri ase ON ac.secilen_secenek_id = ase.id
    WHERE ac.anket_id = ?
    ORDER BY k.ad_soyad ASC
");
$stmt_detayli_cevaplar->bind_param("i", $anket_id);
$stmt_detayli_cevaplar->execute();
$detayli_cevaplar = $stmt_detayli_cevaplar->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_detayli_cevaplar->close();
?>

<div class="card">
    <div class="card-header">
        <div>
            <!-- DÜZELTME: Doğru sütun adı kullanıldı -->
            <h1>Anket Sonuçları: <?= htmlspecialchars($anket['anket_basligi']) ?></h1>
            <p class="subtitle">Sınıf: <?= htmlspecialchars($anket['sinif_adi']) ?> | Toplam Katılım: <?= $toplam_oy ?></p>
        </div>
        <a href="panel.php?sayfa=anket_yonetimi" class="btn btn-secondary">Geri Dön</a>
    </div>
    <div class="card-body">
        
        <h4 style="margin-bottom: 20px;">Seçeneklere Göre Dağılım</h4>
        <div class="anket-sonuclari" style="margin-bottom: 40px; display: flex; flex-direction: column; gap: 15px;">
             <?php if (count($sonuclar) > 0): ?>
                <?php foreach ($sonuclar as $sonuc): 
                    $yuzde = ($toplam_oy > 0) ? round(($sonuc['oy_sayisi'] / $toplam_oy) * 100) : 0;
                ?>
                    <div class="anket-sonuc-item">
                        <div class="secenek-bilgi" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-weight: 500;">
                            <span><?= htmlspecialchars($sonuc['secenek_metni']) ?></span>
                            <span><?= $sonuc['oy_sayisi'] ?> Oy (%<?= $yuzde ?>)</span>
                        </div>
                        <div class="progress-bar-container" style="width: 100%; background-color: #e9ecef; border-radius: 5px;">
                            <div class="progress-bar" style="height: 20px; background-color: var(--accent-color); border-radius: 5px; width: <?= $yuzde ?>%; transition: width 0.5s ease-in-out;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Bu anket için henüz bir seçenek bulunmuyor.</p>
            <?php endif; ?>
        </div>

        <h4 style="margin-top: 40px; margin-bottom: 20px;">Öğrenci Cevapları</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Öğrenci Adı</th>
                        <th>Verdiği Cevap</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($detayli_cevaplar) > 0): ?>
                        <?php foreach ($detayli_cevaplar as $cevap): ?>
                            <tr>
                                <td><?= htmlspecialchars($cevap['ad_soyad']) ?></td>
                                <td><?= htmlspecialchars($cevap['secenek_metni']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center;">Bu ankete henüz katılım olmadı.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>