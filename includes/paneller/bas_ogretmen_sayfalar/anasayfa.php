<?php
require_once 'includes/db_connect.php';

// İstatistikler
$toplam_ogrenci = $conn->query("SELECT COUNT(*) as sayi FROM kullanicilar WHERE rol = 'ogrenci'")->fetch_assoc()['sayi'];
$toplam_odev = $conn->query("SELECT COUNT(*) as sayi FROM odevler")->fetch_assoc()['sayi'];
$toplam_teslim = $conn->query("SELECT COUNT(*) as sayi FROM odev_teslimleri")->fetch_assoc()['sayi'];
$genel_ortalama = $conn->query("SELECT AVG(puan) as ortalama FROM odev_teslimleri WHERE puan IS NOT NULL")->fetch_assoc()['ortalama'];
?>
<div class="panel-header">
    <h1>Gösterge Paneli</h1>
    <p>Sayın <?php echo htmlspecialchars($_SESSION['ad_soyad']); ?>, Baş Öğretmen paneline hoş geldiniz.</p>
</div>
<div class="panel-content">
    <div class="stat-cards-container">
        <div class="stat-card"><h4>Toplam Öğrenci</h4><p><?php echo $toplam_ogrenci; ?></p></div>
        <div class="stat-card"><h4>Toplam Atanan Ödev</h4><p><?php echo $toplam_odev; ?></p></div>
        <div class="stat-card"><h4>Genel Başarı Ort.</h4><p><?php echo $genel_ortalama ? round($genel_ortalama, 2) : 'N/A'; ?></p></div>
    </div>
</div>
