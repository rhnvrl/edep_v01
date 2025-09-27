<?php
require_once 'includes/db_connect.php';
$veli_id = $_SESSION['kullanici_id'];

// Veliye bağlı öğrencileri bul
$ogrenci_idler_sorgu = $conn->query("SELECT ogrenci_id FROM veli_ogrenci_iliskisi WHERE veli_id = $veli_id");
$ogrenci_idler = array_column($ogrenci_idler_sorgu->fetch_all(MYSQLI_ASSOC), 'ogrenci_id');
$ogrenci_idler_str = !empty($ogrenci_idler) ? implode(',', $ogrenci_idler) : '0';

// İstatistikler
$onay_bekleyen_sayisi_sorgu = $conn->query("SELECT COUNT(*) as sayi FROM odev_teslimleri WHERE ogrenci_id IN ($ogrenci_idler_str) AND durum = 'Teslim Edildi - Veli Onayı Bekliyor'");
$onay_bekleyen_sayisi = $onay_bekleyen_sayisi_sorgu ? $onay_bekleyen_sayisi_sorgu->fetch_assoc()['sayi'] : 0;

$aktif_odev_sayisi_sorgu = $conn->query("SELECT COUNT(o.id) as sayi FROM odevler o JOIN ogrenci_sinif_iliskisi osi ON o.sinif_id = osi.sinif_id WHERE osi.ogrenci_id IN ($ogrenci_idler_str) AND o.id NOT IN (SELECT odev_id FROM odev_teslimleri WHERE ogrenci_id IN ($ogrenci_idler_str))");
$aktif_odev_sayisi = $aktif_odev_sayisi_sorgu ? $aktif_odev_sayisi_sorgu->fetch_assoc()['sayi'] : 0;

// DÜZELTME: AVG sorgusu NULL döndürürse, COALESCE ile 0 olarak kabul et
$not_ortalamasi_sorgu = $conn->query("SELECT COALESCE(AVG(puan), 0) as ortalama FROM odev_teslimleri WHERE ogrenci_id IN ($ogrenci_idler_str) AND puan IS NOT NULL");
$not_ortalamasi = $not_ortalamasi_sorgu ? $not_ortalamasi_sorgu->fetch_assoc()['ortalama'] : 0;
?>
<div class="panel-header">
    <h1>Gösterge Paneli</h1>
    <p>Sayın <?php echo htmlspecialchars($_SESSION['ad_soyad']); ?>, veli paneline hoş geldiniz.</p>
</div>
<div class="panel-content">
    <div class="stat-cards-container">
        <div class="stat-card"><h4>Onay Bekleyen Ödev</h4><p><?php echo $onay_bekleyen_sayisi; ?></p></div>
        <div class="stat-card"><h4>Aktif Ödev Sayısı</h4><p><?php echo $aktif_odev_sayisi; ?></p></div>
        <div class="stat-card"><h4>Genel Not Ortalaması</h4><p><?php echo round($not_ortalamasi, 2); ?></p></div>
    </div>
</div>

