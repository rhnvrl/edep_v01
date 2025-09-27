
<?php // giris.php ?>
<?php 
    $sayfa_basligi = "Giriş Yap - Eğitim Destek Platformu";
    include 'includes/header.php'; 
?>
<section class="content-section">
    <div class="login-container card">
        <h2>Platforma Giriş Yap</h2>
        <p>Öğrenci, veli veya öğretmen panelinize erişmek için giriş yapın.</p>
        <form action="actions/giris_kontrol.php" method="POST" class="styled-form">
            <div class="form-group">
                <label for="kullanici_adi">Kullanıcı Adı</label>
                <input type="text" id="kullanici_adi" name="kullanici_adi" required>
            </div>
            <div class="form-group">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" required>
            </div>
            <button type="submit" class="btn btn-primary">Giriş Yap</button>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
