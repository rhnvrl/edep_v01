<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];

// Öğretmenin sorumlu olduğu sınıfları çek
$stmt_siniflar = $conn->prepare("
    SELECT s.id, s.sinif_adi 
    FROM siniflar s 
    JOIN ogretmen_sinif_iliskisi osi ON s.id = osi.sinif_id 
    WHERE osi.ogretmen_id = ? 
    GROUP BY s.id 
    ORDER BY s.sinif_adi
");
$stmt_siniflar->bind_param("i", $ogretmen_id);
$stmt_siniflar->execute();
$siniflar = $stmt_siniflar->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_siniflar->close();
?>

<div class="card">
    <div class="card-header">
        <h1>Yeni Anket Oluştur</h1>
        <a href="panel.php?sayfa=anket_yonetimi" class="btn btn-secondary">Geri Dön</a>
    </div>
    <div class="card-body">
        <form action="actions/anket_action.php" method="POST" class="styled-form">
            <input type="hidden" name="islem" value="ekle">

            <div class="form-group">
                <label for="anket_basligi">Anket Başlığı</label>
                <input type="text" name="anket_basligi" id="anket_basligi" required>
            </div>

            <div class="form-group">
                <label for="sinif_id">Sınıf</label>
                <select name="sinif_id" id="sinif_id" required>
                    <option value="">Sınıf Seçiniz...</option>
                    <?php foreach ($siniflar as $sinif): ?>
                        <option value="<?= $sinif['id'] ?>"><?= htmlspecialchars($sinif['sinif_adi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Seçenekler (En az 2 adet)</label>
                <div id="anket-secenekleri-container" style="display: flex; flex-direction: column; gap: 10px;">
                    <div class="option-group" style="display: flex; gap: 10px;">
                        <input type="text" name="secenekler[]" placeholder="Seçenek 1" required class="form-control" style="flex-grow: 1;">
                    </div>
                    <div class="option-group" style="display: flex; gap: 10px;">
                        <input type="text" name="secenekler[]" placeholder="Seçenek 2" required class="form-control" style="flex-grow: 1;">
                    </div>
                </div>
                <button type="button" id="add-option-btn" class="btn btn-secondary btn-sm" style="margin-top: 10px; align-self: flex-start;">+ Seçenek Ekle</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Anketi Oluştur</button>
            </div>
        </form>
    </div>
</div>