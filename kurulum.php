<?php
// Bu dosya, veritabanı tablolarını ve ilk admin kullanıcısını oluşturur.
// Kurulumdan sonra bu dosyayı ve setup klasörünü sunucudan SİLİN!
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db_config_path = 'includes/db_connect.php';

// Form gönderildi mi kontrolü
$mesaj = '';
$mesaj_tur = '';
$tablolar_kuruldu = false;

// Veritabanı bağlantı dosyasının varlığını kontrol et
if (!file_exists($db_config_path)) {
    $mesaj = "<strong>HATA:</strong> `includes/db_connect.php` dosyası bulunamadı. Lütfen önce bu dosyayı oluşturun ve veritabanı bilgilerinizi girin.";
    $mesaj_tur = 'hata';
} else {
    // Tabloların var olup olmadığını kontrol et
    require_once $db_config_path;
    $result = $conn->query("SHOW TABLES LIKE 'kullanicilar'");
    if ($result && $result->num_rows > 0) {
        $tablolar_kuruldu = true;
    }
    $conn->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tablolari_kur'])) {
        if(!file_exists('setup/schema.sql')){
             $mesaj = "<strong>HATA:</strong> `setup/schema.sql` dosyası bulunamadı. Lütfen bu dosyayı sunucuya yükleyin.";
             $mesaj_tur = 'hata';
        } else {
            require_once $db_config_path;
            $sql_commands = file_get_contents('setup/schema.sql'); // SQL komutlarını ayrı bir dosyadan oku
            if ($conn->multi_query($sql_commands)) {
                // multi_query'den sonra bağlantıyı temizle
                while ($conn->next_result()) {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                }
                $mesaj = "Veritabanı tabloları başarıyla oluşturuldu! Şimdi aşağıdan yönetici hesabınızı oluşturabilirsiniz.";
                $mesaj_tur = 'basari';
                $tablolar_kuruldu = true;
            } else {
                $mesaj = "Tablolar oluşturulurken bir hata oluştu: " . $conn->error;
                $mesaj_tur = 'hata';
            }
            $conn->close();
        }
    } elseif (isset($_POST['admin_olustur'])) {
        require_once $db_config_path;

        $ad_soyad = trim($_POST['ad_soyad']);
        $kullanici_adi = trim($_POST['kullanici_adi']);
        $email = trim($_POST['email']);
        $telefon = trim($_POST['telefon']);
        $sifre = $_POST['sifre'];
        $sifre_tekrar = $_POST['sifre_tekrar'];

        if ($sifre !== $sifre_tekrar) {
            $mesaj = "Şifreler uyuşmuyor!";
            $mesaj_tur = 'hata';
        } elseif (empty($ad_soyad) || empty($kullanici_adi) || empty($email) || empty($sifre)) {
            $mesaj = "Lütfen tüm zorunlu alanları doldurun.";
            $mesaj_tur = 'hata';
        } else {
            $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
            $rol = 'admin';

            $stmt = $conn->prepare("INSERT INTO kullanicilar (ad_soyad, kullanici_adi, email, telefon, sifre, rol) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $ad_soyad, $kullanici_adi, $email, $telefon, $hashed_sifre, $rol);

            if ($stmt->execute()) {
                $mesaj = "Admin kullanıcısı başarıyla oluşturuldu! Artık bu `kurulum.php` dosyasını ve `setup` klasörünü sunucudan güvenle silebilirsiniz.";
                $mesaj_tur = 'basari';
            } else {
                $mesaj = "Admin oluşturulurken bir hata oluştu: " . $stmt->error;
                $mesaj_tur = 'hata';
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eğitim Destek Platformu - Kurulum</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background-color: var(--background-color); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px;}
        .kurulum-container { max-width: 600px; width: 100%; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: var(--border-radius); border: 1px solid transparent; }
        .alert-basari { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-hata { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <div class="kurulum-container">
        <div class="card">
            <div class="card-header">
                <h1>Platform Kurulum Sihirbazı</h1>
            </div>
            <div class="card-body">
                <?php if (!empty($mesaj)): ?>
                    <div class="alert alert-<?= $mesaj_tur ?>"><?= $mesaj ?></div>
                <?php endif; ?>

                <?php if (!$tablolar_kuruldu): ?>
                    <h2>Adım 1: Veritabanı Kurulumu</h2>
                    <p>Başlamak için aşağıdaki butona tıklayarak gerekli veritabanı tablolarını oluşturun.</p>
                    <form method="POST">
                        <button type="submit" name="tablolari_kur" class="btn btn-primary" style="width: 100%;">Veritabanı Tablolarını Kur</button>
                    </form>
                <?php else: ?>
                    <h2>Adım 2: Yönetici Hesabı Oluşturma</h2>
                    <p>Veritabanı hazır. Şimdi sisteme giriş yapacak ilk yönetici (admin) hesabını oluşturun.</p>
                    <form method="POST" class="styled-form">
                        <div class="form-group">
                            <label for="ad_soyad">Ad Soyad</label>
                            <input type="text" name="ad_soyad" id="ad_soyad" required>
                        </div>
                        <div class="form-group">
                            <label for="kullanici_adi">Kullanıcı Adı</label>
                            <input type="text" name="kullanici_adi" id="kullanici_adi" required>
                        </div>
                         <div class="form-group">
                            <label for="email">E-posta Adresi</label>
                            <input type="email" name="email" id="email" required>
                        </div>
                         <div class="form-group">
                            <label for="telefon">Telefon Numarası (Opsiyonel)</label>
                            <input type="text" name="telefon" id="telefon">
                        </div>
                        <div class="form-group">
                            <label for="sifre">Şifre</label>
                            <input type="password" name="sifre" id="sifre" required>
                        </div>
                        <div class="form-group">
                            <label for="sifre_tekrar">Şifre (Tekrar)</label>
                            <input type="password" name="sifre_tekrar" id="sifre_tekrar" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="admin_olustur" class="btn btn-primary">Admin Hesabını Oluştur</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>