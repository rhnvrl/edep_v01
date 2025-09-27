<?php
$sayfa = 'anasayfa';
include 'includes/header.php';
?>

<main>
    <!-- Karşılama Alanı -->
    <section class="hero-section">
        <div class="container">
            <h1>Ortaokulda Başarıya Giden En Akıllı Yol!</h1>
            <p class="alt-baslik">5, 6, 7 ve 8. sınıf öğrencileri için okul derslerine destek, LGS'ye profesyonel hazırlık ve yeni nesil ödev takibi EDEP'te!</p>
            <div class="hero-buttons">
                 <a href="programlar.php" class="btn btn-primary">LGS Programlarımızı İncele</a>
                 <a href="programlar.php" class="btn btn-secondary">Tüm Ders Paketleri</a>
            </div>
        </div>
    </section>

    <!-- Branşlar Alanı -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">Alanında Uzman Öğretmenlerle Başarıyı Garantile!</h2>
            <p class="section-subtitle">Her biri kendi alanında deneyimli, pedagojik formasyona sahip ve LGS müfredatına hakim öğretmen kadromuzla tanışın.</p>
            <div class="brans-grid">
                <div class="brans-card"><h3>Matematik</h3></div>
                <div class="brans-card"><h3>Türkçe</h3></div>
                <div class="brans-card"><h3>Fen Bilimleri</h3></div>
                <div class="brans-card"><h3>T.C. İnkılap Tarihi</h3></div>
                <div class="brans-card"><h3>Din Kültürü ve A.B.</h3></div>
                <div class="brans-card"><h3>İngilizce</h3></div>
            </div>
        </div>
    </section>

    <!-- Ödev Takip Sistemi Tanıtımı -->
    <section class="content-section feature-section">
        <div class="container feature-layout">
            <div class="feature-text">
                <h3 class="feature-title">Artık Hiçbir Ödev Kaçmaz!</h3>
                <p>Türkiye'de bir ilk olan Veli Onaylı Ödev Takip Sistemimiz ile tanışın. Sistemimiz, öğrenci sorumluluğunu artırırken veliye tam kontrol imkanı sunar.</p>
                <ul>
                    <li>Öğretmen ödevi atar.</li>
                    <li>Öğrenci veya veli dosyaları yükler.</li>
                    <li>Veli, ödevin yapıldığını tek tuşla onaylar.</li>
                    <li>Öğretmen ödevi notlandırır ve raporlar.</li>
                </ul>
                <a href="giris.php" class="btn btn-primary">Sisteme Giriş Yap</a>
            </div>
            <div class="feature-image">
                <img src="https://placehold.co/500x350/0A2B4C/FFFFFF?text=Ödev+Takip+Sistemi" alt="Ödev Takip Sistemi Akışı" style="width: 100%; border-radius: 8px;">
            </div>
        </div>
    </section>
    
</main>

<?php include 'includes/footer.php'; ?>