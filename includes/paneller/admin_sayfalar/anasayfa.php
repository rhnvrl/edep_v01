<?php
require_once 'includes/db_connect.php';

// İstatistikleri çek
$ogretmen_sayisi = $conn->query("SELECT COUNT(*) as sayi FROM kullanicilar WHERE rol = 'ogretmen'")->fetch_assoc()['sayi'];
$ogrenci_sayisi = $conn->query("SELECT COUNT(*) as sayi FROM kullanicilar WHERE rol = 'ogrenci'")->fetch_assoc()['sayi'];
$veli_sayisi = $conn->query("SELECT COUNT(*) as sayi FROM kullanicilar WHERE rol = 'veli'")->fetch_assoc()['sayi'];
$sinif_sayisi = $conn->query("SELECT COUNT(*) as sayi FROM siniflar")->fetch_assoc()['sayi'];
$odev_sayisi = $conn->query("SELECT COUNT(*) as sayi FROM odevler")->fetch_assoc()['sayi'];

// Son eklenen 5 kullanıcı
$son_kullanicilar = $conn->query("SELECT ad_soyad, rol, kayit_tarihi FROM kullanicilar ORDER BY kayit_tarihi DESC LIMIT 5");
?>
<div class="panel-header">
    <h1>Gösterge Paneli</h1>
    <p>Sisteme genel bakış.</p>
</div>
<div class="panel-content">
    <div class="stat-cards-container">
        <div class="stat-card">
            <h4>Toplam Öğretmen</h4>
            <p><?php echo $ogretmen_sayisi; ?></p>
        </div>
        <div class="stat-card">
            <h4>Toplam Öğrenci</h4>
            <p><?php echo $ogrenci_sayisi; ?></p>
        </div>
        <div class="stat-card">
            <h4>Toplam Veli</h4>
            <p><?php echo $veli_sayisi; ?></p>
        </div>
        <div class="stat-card">
            <h4>Toplam Sınıf</h4>
            <p><?php echo $sinif_sayisi; ?></p>
        </div>
        <div class="stat-card">
            <h4>Toplam Ödev</h4>
            <p><?php echo $odev_sayisi; ?></p>
        </div>
    </div>

    <div class="card" style="margin-top: 30px;">
        <h3>Son Kaydolan Kullanıcılar</h3>
        <table class="data-table">
            <thead><tr><th>Ad Soyad</th><th>Rol</th><th>Kayıt Tarihi</th></tr></thead>
            <tbody>
                <?php if($son_kullanicilar && $son_kullanicilar->num_rows > 0): ?>
                    <?php while($kullanici = $son_kullanicilar->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($kullanici['ad_soyad']); ?></td>
                        <td><span class="badge badge-<?php echo $kullanici['rol']; ?>"><?php echo ucfirst(str_replace('_', ' ', $kullanici['rol'])); ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($kullanici['kayit_tarihi'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">Sistemde henüz kullanıcı yok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
