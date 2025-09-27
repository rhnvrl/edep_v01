<?php
// Bu sayfanın içeriği includes/paneller/admin_paneli.php tarafından çağrılır.
global $conn;

// Tüm sınıfları, öğretmenleri ve branşları formlar için çekelim
$siniflar = $conn->query("SELECT * FROM siniflar ORDER BY sinif_adi ASC")->fetch_all(MYSQLI_ASSOC);
$ogretmenler = $conn->query("SELECT id, ad_soyad FROM kullanicilar WHERE rol = 'ogretmen' ORDER BY ad_soyad ASC")->fetch_all(MYSQLI_ASSOC);
$branslar = $conn->query("SELECT * FROM branslar ORDER BY brans_adi ASC")->fetch_all(MYSQLI_ASSOC);

// GET parametresinden seçilen sınıf ID'sini al
$secili_sinif_id = isset($_GET['sinif_id']) ? intval($_GET['sinif_id']) : 0;
$ders_programi = [];
$secili_sinif_adi = '';

if ($secili_sinif_id > 0) {
    // Seçilen sınıfın adını al
    $sinif_adi_sorgu = $conn->prepare("SELECT sinif_adi FROM siniflar WHERE id = ?");
    $sinif_adi_sorgu->bind_param("i", $secili_sinif_id);
    $sinif_adi_sorgu->execute();
    $sinif_adi_result = $sinif_adi_sorgu->get_result();
    if($sinif_adi_result->num_rows > 0){
        $secili_sinif_adi = $sinif_adi_result->fetch_assoc()['sinif_adi'];
    }
    $sinif_adi_sorgu->close();


    // Seçilen sınıfa ait ders programını çek
    $stmt = $conn->prepare("
        SELECT dp.*, b.brans_adi, k.ad_soyad as ogretmen_adi 
        FROM ders_programi dp
        JOIN branslar b ON dp.brans_id = b.id
        JOIN kullanicilar k ON dp.ogretmen_id = k.id
        WHERE dp.sinif_id = ?
        ORDER BY dp.gun, dp.baslangic_saati
    ");
    $stmt->bind_param("i", $secili_sinif_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ders_programi[$row['gun']][] = $row;
    }
    $stmt->close();
}

$gunler = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
?>
<div class="card">
    <div class="card-header">
        <h1>Ders Programı Yönetimi</h1>
    </div>
    <div class="card-body">
        <p class="subtitle" style="margin-bottom: 30px;">Sınıflar için haftalık ders programlarını oluşturun ve düzenleyin.</p>
        
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?>">
                <?= htmlspecialchars($_SESSION['mesaj']) ?>
            </div>
            <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
        <?php endif; ?>

        <!-- Sınıf Seçim Formu -->
        <form method="GET" class="styled-form" style="margin-bottom: 30px;">
            <input type="hidden" name="sayfa" value="ders_programi_yonetimi">
            <div class="form-group">
                <label for="sinif_id">Programını Görüntülemek İçin Sınıf Seçin</label>
                <div style="display: flex; gap: 10px;">
                    <select name="sinif_id" id="sinif_id" required onchange="this.form.submit()">
                        <option value="">Sınıf Seçiniz...</option>
                        <?php foreach ($siniflar as $sinif): ?>
                            <option value="<?= $sinif['id'] ?>" <?= ($secili_sinif_id == $sinif['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sinif['sinif_adi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <?php if ($secili_sinif_id > 0): ?>
            <hr style="margin: 40px 0;">
            <!-- Yeni Ders Ekleme Formu -->
            <div class="yeni-ders-formu">
                <h3><?= htmlspecialchars($secili_sinif_adi) ?> Sınıfına Yeni Ders Ekle</h3>
                <form action="actions/ders_programi_action.php" method="POST" class="styled-form">
                    <input type="hidden" name="islem" value="ekle">
                    <input type="hidden" name="sinif_id" value="<?= $secili_sinif_id ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gun">Gün</label>
                            <select name="gun" id="gun" required>
                                <?php foreach ($gunler as $num => $gun_adi): ?>
                                    <option value="<?= $num ?>"><?= $gun_adi ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="baslangic_saati">Başlangıç Saati</label>
                            <input type="time" name="baslangic_saati" id="baslangic_saati" required>
                        </div>
                        <div class="form-group">
                            <label for="bitis_saati">Bitiş Saati</label>
                            <input type="time" name="bitis_saati" id="bitis_saati" required>
                        </div>
                    </div>
                    <div class="form-row">
                         <div class="form-group">
                            <label for="brans_id">Branş</label>
                            <select name="brans_id" id="brans_id" required>
                                <option value="">Branş Seçiniz...</option>
                                <?php foreach ($branslar as $brans): ?>
                                    <option value="<?= $brans['id'] ?>"><?= htmlspecialchars($brans['brans_adi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ogretmen_id">Öğretmen</label>
                            <select name="ogretmen_id" id="ogretmen_id" required>
                                <option value="">Öğretmen Seçiniz...</option>
                                <?php foreach ($ogretmenler as $ogretmen): ?>
                                    <option value="<?= $ogretmen['id'] ?>"><?= htmlspecialchars($ogretmen['ad_soyad']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Dersi Ekle</button>
                    </div>
                </form>
            </div>

            <!-- Haftalık Ders Programı Tablosu -->
            <div class="ders-programi-container" style="margin-top: 40px;">
                <h3><?= htmlspecialchars($secili_sinif_adi) ?> Haftalık Ders Programı</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <?php foreach ($gunler as $gun_adi): ?>
                                    <th><?= $gun_adi ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php foreach ($gunler as $num => $gun_adi): ?>
                                    <td valign="top">
                                        <?php if (isset($ders_programi[$num])): ?>
                                            <ul class="ders-listesi">
                                                <?php foreach ($ders_programi[$num] as $ders): ?>
                                                    <li>
                                                        <div class="ders-bilgi">
                                                            <strong><?= substr($ders['baslangic_saati'], 0, 5) ?> - <?= substr($ders['bitis_saati'], 0, 5) ?></strong><br>
                                                            <?= htmlspecialchars($ders['brans_adi']) ?><br>
                                                            <small><?= htmlspecialchars($ders['ogretmen_adi']) ?></small>
                                                        </div>
                                                        <a href="actions/ders_programi_action.php?islem=sil&id=<?= $ders['id'] ?>&sinif_id=<?= $secili_sinif_id ?>" class="btn-sil-ders btn-sil">&times;</a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span style="color: #999;">Boş</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>