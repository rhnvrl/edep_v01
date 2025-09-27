<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];
date_default_timezone_set('Europe/Istanbul');

// Filtreleme için sınıf ID'sini al
$secili_sinif_id = isset($_GET['sinif_id']) ? intval($_GET['sinif_id']) : 0;

// Sayfalama için sayfa başına kayıt limiti
$limit = 10;

// Öğretmenin sorumlu olduğu sınıfları çek (filtre için)
$stmt_siniflar = $conn->prepare("SELECT s.id, s.sinif_adi FROM siniflar s JOIN ogretmen_sinif_iliskisi osi ON s.id = osi.sinif_id WHERE osi.ogretmen_id = ? GROUP BY s.id ORDER BY s.sinif_adi");
$stmt_siniflar->bind_param("i", $ogretmen_id);
$stmt_siniflar->execute();
$siniflar_filtre = $stmt_siniflar->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_siniflar->close();

// --- AKTİF ÖDEVLER (SÜRESİ DOLMAMIŞ) ---
$aktif_sayfa = isset($_GET['aktif_sayfa']) ? intval($_GET['aktif_sayfa']) : 1;
$offset_aktif = ($aktif_sayfa - 1) * $limit;

// Toplam aktif ödev sayısını bul
$sql_aktif_count = "SELECT COUNT(id) as total FROM odevler WHERE ogretmen_id = ? AND teslim_tarihi >= NOW()";
if ($secili_sinif_id > 0) {
    $sql_aktif_count .= " AND sinif_id = ?";
}
$stmt_aktif_count = $conn->prepare($sql_aktif_count);
if ($secili_sinif_id > 0) {
    $stmt_aktif_count->bind_param("ii", $ogretmen_id, $secili_sinif_id);
} else {
    $stmt_aktif_count->bind_param("i", $ogretmen_id);
}
$stmt_aktif_count->execute();
$total_aktif = $stmt_aktif_count->get_result()->fetch_assoc()['total'];
$stmt_aktif_count->close();
$toplam_aktif_sayfa = ceil($total_aktif / $limit);

// Aktif ödevleri çek
$sql_aktif = "SELECT o.*, s.sinif_adi, b.brans_adi FROM odevler o JOIN siniflar s ON o.sinif_id = s.id JOIN branslar b ON o.brans_id = b.id WHERE o.ogretmen_id = ? AND o.teslim_tarihi >= NOW()";
if ($secili_sinif_id > 0) {
    $sql_aktif .= " AND o.sinif_id = ?";
}
$sql_aktif .= " ORDER BY o.teslim_tarihi ASC LIMIT ? OFFSET ?";
$stmt_aktif = $conn->prepare($sql_aktif);
if ($secili_sinif_id > 0) {
    $stmt_aktif->bind_param("iiii", $ogretmen_id, $secili_sinif_id, $limit, $offset_aktif);
} else {
    $stmt_aktif->bind_param("iii", $ogretmen_id, $limit, $offset_aktif);
}
$stmt_aktif->execute();
$aktif_odevler = $stmt_aktif->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_aktif->close();


// --- GEÇMİŞ ÖDEVLER (SÜRESİ DOLMUŞ) ---
$gecmis_sayfa = isset($_GET['gecmis_sayfa']) ? intval($_GET['gecmis_sayfa']) : 1;
$offset_gecmis = ($gecmis_sayfa - 1) * $limit;

// Toplam geçmiş ödev sayısını bul
$sql_gecmis_count = "SELECT COUNT(id) as total FROM odevler WHERE ogretmen_id = ? AND teslim_tarihi < NOW()";
if ($secili_sinif_id > 0) {
    $sql_gecmis_count .= " AND sinif_id = ?";
}
$stmt_gecmis_count = $conn->prepare($sql_gecmis_count);
if ($secili_sinif_id > 0) {
    $stmt_gecmis_count->bind_param("ii", $ogretmen_id, $secili_sinif_id);
} else {
    $stmt_gecmis_count->bind_param("i", $ogretmen_id);
}
$stmt_gecmis_count->execute();
$total_gecmis = $stmt_gecmis_count->get_result()->fetch_assoc()['total'];
$stmt_gecmis_count->close();
$toplam_gecmis_sayfa = ceil($total_gecmis / $limit);

// Geçmiş ödevleri çek
$sql_gecmis = "SELECT o.*, s.sinif_adi, b.brans_adi FROM odevler o JOIN siniflar s ON o.sinif_id = s.id JOIN branslar b ON o.brans_id = b.id WHERE o.ogretmen_id = ? AND o.teslim_tarihi < NOW()";
if ($secili_sinif_id > 0) {
    $sql_gecmis .= " AND o.sinif_id = ?";
}
$sql_gecmis .= " ORDER BY o.teslim_tarihi DESC LIMIT ? OFFSET ?";
$stmt_gecmis = $conn->prepare($sql_gecmis);
if ($secili_sinif_id > 0) {
    $stmt_gecmis->bind_param("iiii", $ogretmen_id, $secili_sinif_id, $limit, $offset_gecmis);
} else {
    $stmt_gecmis->bind_param("iii", $ogretmen_id, $limit, $offset_gecmis);
}
$stmt_gecmis->execute();
$gecmis_odevler = $stmt_gecmis->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_gecmis->close();

?>

<div class="card">
    <div class="card-header">
        <h1>Ödev Yönetimi</h1>
        <a href="panel.php?sayfa=odev_form" class="btn btn-primary">Yeni Ödev Oluştur</a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?>">
                <?= htmlspecialchars($_SESSION['mesaj']) ?>
            </div>
            <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
        <?php endif; ?>

        <!-- Sınıf Filtreleme Formu -->
        <form method="GET" class="styled-form panel-filters">
            <input type="hidden" name="sayfa" value="odev_yonetimi">
            <div class="form-group">
                <label for="sinif_id_filter">Sınıfa Göre Filtrele</label>
                <select name="sinif_id" id="sinif_id_filter" onchange="this.form.submit()">
                    <option value="0">Tüm Sınıflar</option>
                    <?php foreach ($siniflar_filtre as $sinif): ?>
                        <option value="<?= $sinif['id'] ?>" <?= ($secili_sinif_id == $sinif['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sinif['sinif_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <!-- Aktif Ödevler Tablosu -->
        <div class="odev-tablo-grup">
            <h3>Aktif Ödevler (Teslim Süresi Dolmayanlar)</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sınıf</th>
                            <th>Branş</th>
                            <th>Başlık</th>
                            <th>Teslim Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($aktif_odevler) > 0): ?>
                            <?php foreach ($aktif_odevler as $odev): ?>
                                <tr>
                                    <td><?= htmlspecialchars($odev['sinif_adi']) ?></td>
                                    <td><?= htmlspecialchars($odev['brans_adi']) ?></td>
                                    <td><?= htmlspecialchars($odev['baslik']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($odev['teslim_tarihi'])) ?></td>
                                    <td class="actions">
                                        <a href="panel.php?sayfa=odev_detay&odev_id=<?= $odev['id'] ?>" class="btn btn-info btn-sm">Sonuçlar</a>
                                        <a href="panel.php?sayfa=odev_form&id=<?= $odev['id'] ?>" class="btn btn-secondary btn-sm">Düzenle</a>
                                        <a href="actions/odev_action.php?islem=sil&odev_id=<?= $odev['id'] ?>" class="btn btn-danger btn-sm btn-sil">Sil</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center;">Aktif ödev bulunmuyor.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Aktif Ödevler Sayfalama -->
            <div class="pagination-container">
                <?php for ($i = 1; $i <= $toplam_aktif_sayfa; $i++): ?>
                    <a href="panel.php?sayfa=odev_yonetimi&sinif_id=<?= $secili_sinif_id ?>&aktif_sayfa=<?= $i ?>&gecmis_sayfa=<?= $gecmis_sayfa ?>" class="<?= ($i == $aktif_sayfa) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>

        <hr style="margin: 40px 0;">

        <!-- Geçmiş Ödevler Tablosu -->
        <div class="odev-tablo-grup">
            <h3>Geçmiş Ödevler (Teslim Süresi Dolanlar)</h3>
            <div class="table-responsive">
                 <table class="table">
                    <thead>
                        <tr>
                            <th>Sınıf</th>
                            <th>Branş</th>
                            <th>Başlık</th>
                            <th>Teslim Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($gecmis_odevler) > 0): ?>
                            <?php foreach ($gecmis_odevler as $odev): ?>
                                <tr>
                                    <td><?= htmlspecialchars($odev['sinif_adi']) ?></td>
                                    <td><?= htmlspecialchars($odev['brans_adi']) ?></td>
                                    <td><?= htmlspecialchars($odev['baslik']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($odev['teslim_tarihi'])) ?></td>
                                    <td class="actions">
                                        <a href="panel.php?sayfa=odev_detay&odev_id=<?= $odev['id'] ?>" class="btn btn-info btn-sm">Sonuçlar</a>
                                        <a href="actions/odev_action.php?islem=sil&odev_id=<?= $odev['id'] ?>" class="btn btn-danger btn-sm btn-sil">Sil</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                             <tr><td colspan="5" style="text-align: center;">Geçmiş ödev bulunmuyor.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Geçmiş Ödevler Sayfalama -->
            <div class="pagination-container">
                <?php for ($i = 1; $i <= $toplam_gecmis_sayfa; $i++): ?>
                    <a href="panel.php?sayfa=odev_yonetimi&sinif_id=<?= $secili_sinif_id ?>&aktif_sayfa=<?= $aktif_sayfa ?>&gecmis_sayfa=<?= $i ?>" class="<?= ($i == $gecmis_sayfa) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        
    </div>
</div>