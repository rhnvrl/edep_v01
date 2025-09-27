<?php
require_once 'includes/db_connect.php';

// Düzenleme modu için sınıf verisini çek
$edit_mode = false;
$sinif_verisi = ['id' => 0, 'sinif_adi' => '', 'seviye' => ''];
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT id, sinif_adi, seviye FROM siniflar WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $sinif_verisi = $result->fetch_assoc();
    }
    $stmt->close();
}

// Tüm sınıfları listelemek için çek
$siniflar = $conn->query("SELECT id, sinif_adi, seviye FROM siniflar ORDER BY seviye, sinif_adi");
?>
<div class="panel-header">
    <h1>Sınıf Yönetimi</h1>
    <p>Sisteme yeni sınıflar ekleyin veya mevcutları düzenleyin.</p>
</div>
<div class="panel-content">
    <div class="iletisim-grid">
        <!-- Ekleme/Düzenleme Formu -->
        <div class="form-container card">
            <h3><?php echo $edit_mode ? 'Sınıfı Düzenle' : 'Yeni Sınıf Ekle'; ?></h3>
            <form action="actions/sinif_action.php" method="POST" class="styled-form">
                <input type="hidden" name="sinif_id" value="<?php echo $sinif_verisi['id']; ?>">
                <div class="form-group">
                    <label for="sinif_adi">Sınıf Adı (*)</label>
                    <input type="text" id="sinif_adi" name="sinif_adi" value="<?php echo htmlspecialchars($sinif_verisi['sinif_adi']); ?>" placeholder="Örn: 8. Sınıf LGS VIP" required>
                </div>
                 <div class="form-group">
                    <label for="seviye">Sınıf Seviyesi (*)</label>
                    <select id="seviye" name="seviye" required>
                        <option value="">Seçiniz...</option>
                        <?php for ($i = 5; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($sinif_verisi['seviye'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?>. Sınıf</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" name="kaydet" class="btn btn-primary"><?php echo $edit_mode ? 'Güncelle' : 'Ekle'; ?></button>
                <?php if ($edit_mode): ?>
                    <a href="panel.php?sayfa=siniflar" class="btn btn-secondary">İptal</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Sınıf Listesi -->
        <div class="liste-container card">
            <h3>Mevcut Sınıflar</h3>
            <?php
            if (isset($_SESSION['form_mesaji_sinif'])) {
                echo '<div class="form-message success">' . $_SESSION['form_mesaji_sinif'] . '</div>';
                unset($_SESSION['form_mesaji_sinif']);
            }
            if (isset($_SESSION['form_hatasi_sinif'])) {
                echo '<div class="form-message error">' . $_SESSION['form_hatasi_sinif'] . '</div>';
                unset($_SESSION['form_hatasi_sinif']);
            }
            ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sınıf Adı</th>
                        <th>Seviye</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($siniflar && $siniflar->num_rows > 0): ?>
                        <?php while($row = $siniflar->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['sinif_adi']); ?></td>
                                <td><?php echo htmlspecialchars($row['seviye']); ?>. Sınıf</td>
                                <td class="actions">
                                    <a href="panel.php?sayfa=siniflar&edit_id=<?php echo $row['id']; ?>" class="btn-icon btn-edit" title="Düzenle">✏️</a>
                                    <a href="actions/sinif_action.php?delete_id=<?php echo $row['id']; ?>" class="btn-icon btn-delete delete-confirm" title="Sil">🗑️</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">Kayıtlı sınıf bulunamadı.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $conn->close(); ?>
