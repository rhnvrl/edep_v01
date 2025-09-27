<?php
// Bu sayfanın içeriği includes/paneller/ogrenci_paneli.php tarafından çağrılır.
global $conn;
$ogrenci_id = $_SESSION['kullanici_id'];

// --- İstatistikleri Çek ---

// Toplam atanan ödev sayısı
$stmt_toplam = $conn->prepare("
    SELECT COUNT(o.id) as sayi 
    FROM odevler o
    JOIN ogrenci_sinif_iliskisi osi ON o.sinif_id = osi.sinif_id
    WHERE osi.ogrenci_id = ?
");
$stmt_toplam->bind_param("i", $ogrenci_id);
$stmt_toplam->execute();
$toplam_odev_sayisi = $stmt_toplam->get_result()->fetch_assoc()['sayi'];
$stmt_toplam->close();

// Tamamlanan (teslim edilen) ödev sayısı
$stmt_tamamlanan = $conn->prepare("SELECT COUNT(id) as sayi FROM odev_teslimleri WHERE ogrenci_id = ?");
$stmt_tamamlanan->bind_param("i", $ogrenci_id);
$stmt_tamamlanan->execute();
$tamamlanan_odev_sayisi = $stmt_tamamlanan->get_result()->fetch_assoc()['sayi'];
$stmt_tamamlanan->close();

// DÜZELTİLMİŞ SORGU: Sadece notu olan ödevlerin ortalamasını al
$stmt_ortalama = $conn->prepare("SELECT AVG(notu) as ortalama FROM odev_teslimleri WHERE ogrenci_id = ? AND notu IS NOT NULL");
$stmt_ortalama->bind_param("i", $ogrenci_id);
$stmt_ortalama->execute();
$ortalama_sonuc = $stmt_ortalama->get_result()->fetch_assoc();
// Ortalama NULL ise 0 olarak ayarla
$genel_ortalama = $ortalama_sonuc['ortalama'] ? round($ortalama_sonuc['ortalama'], 2) : 0;
$stmt_ortalama->close();

?>

<div class="card">
    <div class="card-header">
        <h1>Gösterge Paneli</h1>
    </div>
    <div class="card-body">
        <p>Merhaba <strong><?= htmlspecialchars($_SESSION['ad_soyad']) ?></strong>, paneline hoş geldin!</p>
        
        <div class="stat-cards-container" style="margin-top: 30px;">
            <div class="stat-card">
                <h4>Toplam Ödevin</h4>
                <p><?= $toplam_odev_sayisi ?></p>
            </div>
            <div class="stat-card">
                <h4>Tamamladığın Ödev</h4>
                <p><?= $tamamlanan_odev_sayisi ?></p>
            </div>
            <div class="stat-card">
                <h4>Genel Başarı Ortalaman</h4>
                <p><?= $genel_ortalama ?></p>
            </div>
        </div>
        
        <div style="margin-top: 40px;">
             <h3 style="margin-bottom: 20px; color: var(--secondary-color);">Yapılacaklar Listesi</h3>
             <p>Yakında eklenecek olan takvim ve yapılacaklar listesi özellikleri ile tüm görevlerini buradan takip edebileceksin.</p>
        </div>

    </div>
</div>