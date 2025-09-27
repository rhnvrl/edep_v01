<?php
// Bu sayfanın içeriği includes/paneller/admin_paneli.php tarafından çağrılır.
global $conn;

// Rollerin Türkçe karşılıkları (filtre için)
$roller = [
    'admin' => 'Admin',
    'bas_ogretmen' => 'Baş Öğretmen',
    'ogretmen' => 'Öğretmen',
    'veli' => 'Veli',
    'ogrenci' => 'Öğrenci'
];

// Filtreleme için seçilen rolü al
$secili_rol = isset($_GET['rol_filtre']) ? $_GET['rol_filtre'] : '';

// Veritabanı sorgusunu filtreye göre dinamik olarak oluştur
$sql = "SELECT id, ad_soyad, kullanici_adi, rol, email, telefon FROM kullanicilar";
if (!empty($secili_rol)) {
    // SQL Injection'a karşı koruma için prepared statement kullanıyoruz
    $sql .= " WHERE rol = ?";
}
$sql .= " ORDER BY rol, ad_soyad";

$stmt = $conn->prepare($sql);

if (!empty($secili_rol)) {
    $stmt->bind_param("s", $secili_rol);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<div class="card">
    <div class="card-header">
        <h1>Kullanıcı Yönetimi</h1>
        <a href="panel.php?sayfa=kullanici_form" class="btn btn-primary">Yeni Kullanıcı Ekle</a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?>">
                <?= htmlspecialchars($_SESSION['mesaj']) ?>
            </div>
            <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
        <?php endif; ?>

        <!-- YENİ EKLENEN FİLTRELEME FORMU -->
        <form method="GET" class="styled-form panel-filters">
            <input type="hidden" name="sayfa" value="kullanicilar">
            <div class="form-group">
                <label for="rol_filtre">Role Göre Filtrele</label>
                <select name="rol_filtre" id="rol_filtre" onchange="this.form.submit()">
                    <option value="">Tüm Roller</option>
                    <?php foreach ($roller as $rol_key => $rol_value): ?>
                        <option value="<?= $rol_key ?>" <?= ($secili_rol == $rol_key) ? 'selected' : '' ?>>
                            <?= $rol_value ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Kullanıcı Adı</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($kullanici = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($kullanici['ad_soyad']) ?></td>
                                <td><?= htmlspecialchars($kullanici['kullanici_adi']) ?></td>
                                <td><?= htmlspecialchars($kullanici['email']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $kullanici['rol'] ?>">
                                        <?= $roller[$kullanici['rol']] ?? ucfirst($kullanici['rol']) ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="panel.php?sayfa=kullanici_form&id=<?= $kullanici['id'] ?>" class="btn btn-secondary btn-sm">Düzenle</a>
                                    <?php if ($kullanici['id'] != $_SESSION['kullanici_id']): // Kullanıcı kendini silemesin ?>
                                        <a href="actions/kullanici_kaydet.php?islem=sil&id=<?= $kullanici['id'] ?>" class="btn btn-danger btn-sm btn-sil">Sil</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Filtreye uygun kullanıcı bulunamadı.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $stmt->close(); ?>

