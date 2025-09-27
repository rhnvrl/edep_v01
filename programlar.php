<?php
$sayfa = 'programlar';
include 'includes/header.php';
?>
<main>
    <div class="page-header">
        <h1>Programlarımız</h1>
        <p style="color: #eee; max-width: 600px; margin: 10px auto 0;">Size en uygun eğitim paketini seçerek başarıya ilk adımı atın.</p>
    </div>
    <section class="content-section">
        <div class="container">
            <div class="card" style="padding: 20px;">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-target="okul-destek">Okul Derslerine Destek</button>
                    <button class="tab-btn" data-target="lgs-hazirlik">LGS Profesyonel Hazırlık</button>
                </div>

                <div id="okul-destek" class="tab-content active">
                     <h2 class="section-title" style="font-size: 2rem; margin-bottom: 15px; margin-top: 30px;">Derslerdeki Başarını Artır, Yazılılara Bizimle Hazırlan!</h2>
                     <p class="section-subtitle" style="margin-bottom: 40px;">5, 6 ve 7. sınıf öğrencilerimiz için okul müfredatıyla %100 uyumlu, temel konuları pekiştiren ve özgüveni artıran ders paketlerimiz.</p>
                     <div class="program-grid">
                        <div class="program-card">
                            <h3>5. Sınıf Destek Paketi</h3>
                            <p>Ortaokula sağlam bir başlangıç için temel derslerdeki eksiklerinizi kapatın.</p>
                            <ul>
                                <li>Haftalık Canlı Dersler</li>
                                <li>Konu Anlatımları ve Soru Çözümleri</li>
                                <li>Yazılıya Hazırlık Kampları</li>
                            </ul>
                            <a href="iletisim.php" class="btn btn-primary">Detaylı Bilgi Al</a>
                        </div>
                         <div class="program-card">
                            <h3>6. Sınıf Destek Paketi</h3>
                            <p>Derslerdeki başarınızı bir üst seviyeye taşıyın, yeni konuları kolayca öğrenin.</p>
                             <ul>
                                <li>Haftalık Canlı Dersler</li>
                                <li>Konu Anlatımları ve Soru Çözümleri</li>
                                <li>Yazılıya Hazırlık Kampları</li>
                            </ul>
                            <a href="iletisim.php" class="btn btn-primary">Detaylı Bilgi Al</a>
                        </div>
                         <div class="program-card">
                            <h3>7. Sınıf Destek Paketi</h3>
                            <p>LGS öncesi son virajda eksiklerinizi kapatın, konulara hakimiyetinizi artırın.</p>
                             <ul>
                                <li>Haftalık Canlı Dersler</li>
                                <li>Konu Anlatımları ve Soru Çözümleri</li>
                                <li>Yazılıya Hazırlık Kampları</li>
                            </ul>
                            <a href="iletisim.php" class="btn btn-primary">Detaylı Bilgi Al</a>
                        </div>
                     </div>
                </div>

                <div id="lgs-hazirlik" class="tab-content">
                    <h2 class="section-title" style="font-size: 2rem; margin-bottom: 30px; margin-top: 30px;">LGS'de Zirveyi Hedefleyenlerin Programı</h2>
                    <div class="lgs-paket">
                        <div class="lgs-paket-img">
                             <img src="https://placehold.co/500x350/FF6B00/FFFFFF?text=LGS+Hazırlık" alt="LGS Hazırlık Programı" style="width:100%; border-radius: 8px;">
                        </div>
                        <div class="lgs-paket-detay">
                            <h3>Profesyonel LGS Hazırlık Paketi (8. Sınıf)</h3>
                            <p>8. sınıf öğrencilerimizi, yeni nesil soru tipleri, stratejik konu anlatımları, deneme sınavları ve birebir rehberlik ile LGS'ye eksiksiz hazırlıyoruz.</p>
                            <ul>
                                <li>Haftalık 8 Saat Canlı Ders</li>
                                <li>Matematik, Fen Bilimleri, Türkçe ve İnkılap Tarihi</li>
                                <li>Aylık Deneme Sınavları ve Analizleri</li>
                                <li>Yeni Nesil Soru Çözüm Saatleri</li>
                                <li>Birebir Eğitim Koçluğu ve Rehberlik</li>
                            </ul>
                             <a href="iletisim.php" class="btn btn-primary">Detaylı Bilgi ve Kayıt</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>