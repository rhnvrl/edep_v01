<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];

// --- İstatistikleri Çek ---

// Sorumlu olunan sınıf sayısı
$stmt_sinif = $conn->prepare("SELECT COUNT(DISTINCT sinif_id) as sayi FROM ogretmen_sinif_iliskisi WHERE ogretmen_id = ?");
$stmt_sinif->bind_param("i", $ogretmen_id);
$stmt_sinif->execute();
$sorumlu_sinif_sayisi = $stmt_sinif->get_result()->fetch_assoc()['sayi'];
$stmt_sinif->close();

// Atanan toplam ödev sayısı
$stmt_odev = $conn->prepare("SELECT COUNT(id) as sayi FROM odevler WHERE ogretmen_id = ?");
$stmt_odev->bind_param("i", $ogretmen_id);
$stmt_odev->execute();
$toplam_odev_sayisi = $stmt_odev->get_result()->fetch_assoc()['sayi'];
$stmt_odev->close();

// Notlandırma bekleyen ödev sayısı
$stmt_bekleyen = $conn->prepare("SELECT COUNT(ot.id) as sayi 
                                 FROM odev_teslimleri ot
                                 JOIN odevler o ON ot.odev_id = o.id
                                 WHERE o.ogretmen_id = ? AND ot.durum = 'Teslim Edildi - Veli Tarafından Onaylandı'");
$stmt_bekleyen->bind_param("i", $ogretmen_id);
$stmt_bekleyen->execute();
$bekleyen_odev_sayisi = $stmt_bekleyen->get_result()->fetch_assoc()['sayi'];
$stmt_bekleyen->close();

// Yaklaşan Teslim Tarihli Ödevler (En yakın 5 ödev)
$stmt_yaklasan = $conn->prepare("SELECT o.baslik, s.sinif_adi, o.teslim_tarihi 
                                 FROM odevler o
                                 JOIN siniflar s ON o.sinif_id = s.id
                                 WHERE o.ogretmen_id = ? AND o.teslim_tarihi > NOW()
                                 ORDER BY o.teslim_tarihi ASC
                                 LIMIT 5");
$stmt_yaklasan->bind_param("i", $ogretmen_id);
$stmt_yaklasan->execute();
$yaklasan_odevler = $stmt_yaklasan->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_yaklasan->close();

?>

<div class="card">
    <div class="card-header">
        <h1>Gösterge Paneli</h1>
    </div>
    <div class="card-body">
        <p>Merhaba <strong><?= htmlspecialchars($_SESSION['ad_soyad']) ?></strong>, panele hoş geldin!</p>
        
        <div class="stat-cards-container" style="margin-top: 30px;">
            <div class="stat-card">
                <h4>Sorumlu Olduğun Sınıf</h4>
                <p><?= $sorumlu_sinif_sayisi ?></p>
            </div>
            <div class="stat-card">
                <h4>Atadığın Toplam Ödev</h4>
                <p><?= $toplam_odev_sayisi ?></p>
            </div>
            <div class="stat-card">
                <h4>Notlandırma Bekleyen</h4>
                <p><?= $bekleyen_odev_sayisi ?></p>
            </div>
        </div>
        
        <!-- DÜZELTİLMİŞ BÖLÜM -->
        <div style="margin-top: 40px;">
             <h3 style="margin-bottom: 20px; color: var(--secondary-color);">Yaklaşan Teslim Tarihleri</h3>
             <div class="table-responsive">
                 <table class="table">
                     <thead>
                         <tr>
                             <th>Ödev Başlığı</th>
                             <th>Sınıf</th>
                             <th>Teslim Tarihi</th>
                         </tr>
                     </thead>
                     <tbody>
                        <?php if (count($yaklasan_odevler) > 0): ?>
                            <?php foreach ($yaklasan_odevler as $odev): ?>
                                <tr>
                                    <td><?= htmlspecialchars($odev['baslik']) ?></td>
                                    <td><?= htmlspecialchars($odev['sinif_adi']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($odev['teslim_tarihi'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">Yaklaşan teslim tarihli bir ödeviniz bulunmuyor.</td>
                            </tr>
                        <?php endif; ?>
                     </tbody>
                 </table>
             </div>
        </div>
        <!-- DÜZELTME SONU -->

    </div>
</div>