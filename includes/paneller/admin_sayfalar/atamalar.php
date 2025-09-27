<?php
// Bu sayfanın içeriği includes/paneller/admin_paneli.php tarafından çağrılır.
global $conn;

// Öğrencileri ve sınıflarını çek (GROUP_CONCAT ile birden fazla sınıfı birleştir)
$ogrenciler = $conn->query("
    SELECT 
        k.id, 
        k.ad_soyad, 
        GROUP_CONCAT(s.sinif_adi SEPARATOR ', ') as atandigi_siniflar
    FROM kullanicilar k 
    LEFT JOIN ogrenci_sinif_iliskisi osi ON k.id = osi.ogrenci_id
    LEFT JOIN siniflar s ON osi.sinif_id = s.id
    WHERE k.rol = 'ogrenci'
    GROUP BY k.id
    ORDER BY k.ad_soyad
")->fetch_all(MYSQLI_ASSOC);

// Öğretmenleri çek
$ogretmenler = $conn->query("SELECT id, ad_soyad FROM kullanicilar WHERE rol = 'ogretmen' ORDER BY ad_soyad")->fetch_all(MYSQLI_ASSOC);

// Velileri çek
$veliler = $conn->query("SELECT id, ad_soyad FROM kullanicilar WHERE rol = 'veli' ORDER BY ad_soyad")->fetch_all(MYSQLI_ASSOC);

?>
<div class="card">
    <div class="card-header">
        <h1>Atama Yönetimi</h1>
    </div>
    <div class="card-body">
        <p class="subtitle" style="margin-bottom: 30px;">Öğrencileri sınıflara, öğretmenleri branşlara/sınıflara ve velileri öğrencilere atayın.</p>
        
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?>">
                <?= htmlspecialchars($_SESSION['mesaj']) ?>
            </div>
            <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
        <?php endif; ?>

        <!-- ÖĞRENCİ SINIF ATAMA BÖLÜMÜ -->
        <div class="atama-grup">
            <h3>Öğrenci Sınıf Atamaları</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Öğrenci Adı Soyadı</th>
                            <th>Atandığı Sınıflar</th>
                            <th style="width: 150px;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ogrenciler as $ogrenci): ?>
                            <tr>
                                <td><?= htmlspecialchars($ogrenci['ad_soyad']) ?></td>
                                <td><?= htmlspecialchars($ogrenci['atandigi_siniflar'] ?? 'Atanmamış') ?></td>
                                <td><a href="panel.php?sayfa=ogrenci_atama_form&id=<?= $ogrenci['id'] ?>" class="btn btn-secondary btn-sm">Ata / Düzenle</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <hr style="margin: 40px 0;">

        <!-- ÖĞRETMEN ATAMA BÖLÜMÜ -->
        <div class="atama-grup">
            <h3>Öğretmen Branş ve Sınıf Atamaları</h3>
            <div class="table-responsive">
                <table class="table">
                     <thead>
                        <tr>
                            <th>Öğretmen Adı Soyadı</th>
                            <th style="width: 150px;">İşlem</th>
                        </tr>
                    </thead>
                     <tbody>
                        <?php foreach($ogretmenler as $ogretmen): ?>
                            <tr>
                                <td><?= htmlspecialchars($ogretmen['ad_soyad']) ?></td>
                                <td><a href="panel.php?sayfa=ogretmen_atama_form&id=<?= $ogretmen['id'] ?>" class="btn btn-secondary btn-sm">Ata / Düzenle</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <hr style="margin: 40px 0;">

        <!-- VELİ-ÖĞRENCİ İLİŞKİLENDİRME BÖLÜMÜ -->
        <div class="atama-grup">
            <h3>Veli - Öğrenci İlişkilendirme</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Veli Adı Soyadı</th>
                            <th>Sorumlu Olduğu Öğrenciler</th>
                            <th style="width: 150px;">İşlem</th>
                        </tr>
                    </thead>
                     <tbody>
                        <?php foreach($veliler as $veli): 
                            $sorumlu_ogrenciler_sorgu = $conn->query("SELECT k.ad_soyad FROM kullanicilar k JOIN veli_ogrenci_iliskisi voi ON k.id = voi.ogrenci_id WHERE voi.veli_id = " . $veli['id']);
                            $sorumlu_ogrenciler = $sorumlu_ogrenciler_sorgu->fetch_all(MYSQLI_ASSOC);
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($veli['ad_soyad']) ?></td>
                                <td><?= count($sorumlu_ogrenciler) > 0 ? implode(', ', array_column($sorumlu_ogrenciler, 'ad_soyad')) : 'Atanmamış' ?></td>
                                <td><a href="panel.php?sayfa=veli_atama_form&id=<?= $veli['id'] ?>" class="btn btn-secondary btn-sm">Ata / Düzenle</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

