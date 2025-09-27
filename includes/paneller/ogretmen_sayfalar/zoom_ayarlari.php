<?php
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];

// Mevcut zoom bilgilerini çek
$stmt = $conn->prepare("SELECT zoom_id, zoom_parola FROM kullanicilar WHERE id = ?");
$stmt->bind_param("i", $ogretmen_id);
$stmt->execute();
$result = $stmt->get_result();
$zoom_bilgileri = $result->fetch_assoc();
$stmt->close();
?>

<div class="card">
    <div class="card-header">
        <h1>Zoom Toplantı Bilgileri</h1>
    </div>
    <div class="card-body">
        <p class="subtitle" style="margin-bottom: 30px;">Canlı dersler için kullanacağınız kişisel Zoom toplantı bilgilerinizi buraya girin.</p>
        
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?>">
                <?= htmlspecialchars($_SESSION['mesaj']) ?>
            </div>
            <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
        <?php endif; ?>

        <form action="actions/zoom_ayarlari_action.php" method="POST" class="styled-form">
            <div class="form-group">
                <label for="zoom_id">Zoom Toplantı ID</label>
                <input type="text" name="zoom_id" id="zoom_id" value="<?= htmlspecialchars($zoom_bilgileri['zoom_id'] ?? '') ?>" placeholder="Örn: 123 456 7890">
            </div>
            <div class="form-group">
                <label for="zoom_parola">Zoom Parolası</label>
                <input type="text" name="zoom_parola" id="zoom_parola" value="<?= htmlspecialchars($zoom_bilgileri['zoom_parola'] ?? '') ?>" placeholder="Örn: 123456">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Bilgileri Kaydet</button>
            </div>
        </form>
    </div>
</div>