<?php
require_once 'includes/db_connect.php';

// Düzenleme modu için branş verisini çek
$edit_mode = false;
$brans_verisi = ['id' => 0, 'brans_adi' => '', 'brans_kisaltma' => ''];
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT id, brans_adi, brans_kisaltma FROM branslar WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $brans_verisi = $result->fetch_assoc();
    }
    $stmt->close();
}

// Tüm branşları listelemek için çek
$branslar = $conn->query("SELECT id, brans_adi, brans_kisaltma FROM branslar ORDER BY brans_adi");
?>
<div class="panel-header">
    <h1>Branş Yönetimi</h1>
    <p>Sisteme yeni ders branşları ekleyin veya mevcutları düzenleyin.</p>
</div>
<div class="panel-content">
    <div class="iletisim-grid">
        <!-- Ekleme/Düzenleme Formu -->
        <div class="form-container card">
            <h3><?php echo $edit_mode ? 'Branşı Düzenle' : 'Yeni Branş Ekle'; ?></h3>
            <form action="actions/brans_action.php" method="POST" class="styled-form">
                <input type="hidden" name="brans_id" value="<?php echo $brans_verisi['id']; ?>">
                <div class="form-group">
                    <label for="brans_adi">Branş Adı (*)</label>
                    <input type="text" id="brans_adi" name="brans_adi" value="<?php echo htmlspecialchars($brans_verisi['brans_adi']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="brans_kisaltma">Branş Kısaltması (*)</label>
                    <input type="text" id="brans_kisaltma" name="brans_kisaltma" value="<?php echo htmlspecialchars($brans_verisi['brans_kisaltma']); ?>" placeholder="Örn: MAT, FEN, TUR" required>
                </div>
                <button type="submit" name="kaydet" class="btn btn-primary"><?php echo $edit_mode ? 'Güncelle' : 'Ekle'; ?></button>
                <?php if ($edit_mode): ?>
                    <a href="panel.php?sayfa=branslar" class="btn btn-secondary">İptal</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Branş Listesi -->
        <div class="liste-container card">
            <h3>Mevcut Branşlar</h3>
            <?php
            if (isset($_SESSION['form_mesaji'])) {
                echo '<div class="form-message success">' . $_SESSION['form_mesaji'] . '</div>';
                unset($_SESSION['form_mesaji']);
            }
            if (isset($_SESSION['form_hatasi'])) {
                echo '<div class="form-message error">' . $_SESSION['form_hatasi'] . '</div>';
                unset($_SESSION['form_hatasi']);
            }
            ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Branş Adı</th>
                        <th>Kısaltma</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($branslar && $branslar->num_rows > 0): ?>
                        <?php while($row = $branslar->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['brans_adi']); ?></td>
                                <td><?php echo htmlspecialchars($row['brans_kisaltma']); ?></td>
                                <td class="actions">
                                    <a href="panel.php?sayfa=branslar&edit_id=<?php echo $row['id']; ?>" class="btn-icon btn-edit" title="Düzenle">✏️</a>
                                    <a href="actions/brans_action.php?delete_id=<?php echo $row['id']; ?>" class="btn-icon btn-delete delete-confirm" title="Sil">🗑️</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">Kayıtlı branş bulunamadı.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $conn->close(); ?>
