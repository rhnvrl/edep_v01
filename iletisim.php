<?php 
    // Session'ı başlatarak mesajları alabilmek için
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $sayfa_basligi = "İletişim - Eğitim Destek Platformu";
    include 'includes/header.php'; 
?>
<section class="page-header">
    <div class="container">
        <h1>Bize Ulaşın</h1>
        <p>Sorularınız, önerileriniz veya kayıt işlemleri için bizimle iletişime geçin.</p>
    </div>
</section>
<section class="content-section">
    <div class="container">
        <div class="card">
            <div class="iletisim-grid">
                <div class="iletisim-form">
                    <h3>Mesaj Gönderin</h3>
                    <p>Platformumuza kayıtlar Admin tarafından yapılmaktadır. Kayıt ve bilgi için lütfen aşağıdaki formu doldurun veya telefonla bize ulaşın.</p>
                    
                    <?php
                    // Session'dan gelen başarı veya hata mesajlarını göster
                    if (isset($_SESSION['form_mesaji_iletisim'])) {
                        echo '<div class="form-message success">' . $_SESSION['form_mesaji_iletisim'] . '</div>';
                        unset($_SESSION['form_mesaji_iletisim']); // Mesajı gösterdikten sonra temizle
                    }
                    if (isset($_SESSION['form_hatasi_iletisim'])) {
                        echo '<div class="form-message error">' . $_SESSION['form_hatasi_iletisim'] . '</div>';
                        unset($_SESSION['form_hatasi_iletisim']);
                    }
                    ?>

                    <form action="actions/iletisim_formu_action.php" method="POST" class="styled-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="iletisim-ad">Adınız Soyadınız (*)</label>
                                <input type="text" id="iletisim-ad" name="ad_soyad" required>
                            </div>
                            <div class="form-group">
                                <label for="iletisim-email">E-posta Adresiniz (*)</label>
                                <input type="email" id="iletisim-email" name="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="iletisim-tel">Telefon Numaranız</label>
                            <input type="tel" id="iletisim-tel" name="telefon">
                        </div>
                        <div class="form-group">
                            <label for="iletisim-mesaj">Mesajınız (*)</label>
                            <textarea id="iletisim-mesaj" name="mesaj" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Gönder</button>
                    </form>
                </div>
                <div class="iletisim-bilgi">
                    <h3>İletişim Bilgilerimiz</h3>
                    <p><strong>Telefon:</strong><br>+90 850 495 09 62</p>
                    <p><strong>E-posta:</strong><br>bilgi@egitimdestekplatformu.com.tr</p>
                    <p><strong>Adres:</strong><br>Üsküdar , İstanbul</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
