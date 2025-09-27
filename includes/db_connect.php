<?php
// Veritabanı bağlantı bilgileri
$servername = "localhost:3306"; // Genellikle 'localhost' olarak kalır
$username = "edep2025_admin"; // kullanıcı adı
$password = "edep615T."; //  şifre
$dbname = "edep2025_odevtakip"; // veritabanı adı

// Bağlantı oluşturma (MySQLi - Nesne Yönelimli)
$conn = new mysqli($servername, $username, $password, $dbname);

// ÖNCE bağlantıyı kontrol et
if ($conn->connect_error) {
    // Bağlantı başarısızsa, betiği durdur ve açıklayıcı bir hata mesajı göster.
    die("<h2>Veritabanı Bağlantısı Başarısız!</h2>" . 
        "<p>Hata Mesajı: " . $conn->connect_error . "</p>" .
        "<hr>" .
        "<h3>Lütfen Kontrol Edin:</h3>" .
        "<ol>" .
        "<li>Verdiğiniz veritabanı adı, kullanıcı adı ve şifrenin doğruluğundan emin olun.</li>" .
        "<li>Hosting panelinizde (cPanel vb.) bu kullanıcıyı veritabanına ekleyip <strong>TÜM YETKİLERİ (ALL PRIVILEGES)</strong> verdiğinizden emin olun.</li>" .
        "</ol>");
}

// Bağlantı başarılıysa, karakter setini ayarla
$conn->set_charset("utf8");

// Bu noktadan sonra $conn değişkeni, veritabanı işlemlerinde güvenle kullanılabilir.
?>