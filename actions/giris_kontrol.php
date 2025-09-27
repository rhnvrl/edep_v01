<?php
// Session'ı başlatarak kullanıcı oturum bilgilerini yönetmeye hazırla
if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

// Veritabanı bağlantı dosyasını dahil et
require_once '../includes/db_connect.php';

// Sadece form gönderildiğinde (POST metodu ile) çalışmasını sağla
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Formdan gelen kullanıcı adı ve şifreyi al, boşlukları temizle
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = $_POST['sifre'];

    // Alanların boş olup olmadığını kontrol et
    if (empty($kullanici_adi) || empty($sifre)) {
        $_SESSION['hata_mesaji'] = "Kullanıcı adı ve şifre alanları boş bırakılamaz.";
        header("Location: ../giris.php");
        exit();
    }

    // Kullanıcıyı veritabanında ara (hem normal hem de geçici şifre sütunlarını al)
    $sql = "SELECT id, kullanici_adi, sifre, gecici_sifre, ad_soyad, rol FROM kullanicilar WHERE kullanici_adi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kullanici_adi);
    $stmt->execute();
    $result = $stmt->get_result();

    // Eğer tam olarak bir kullanıcı bulunduysa devam et
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $login_successful = false;

        // 1. Önce geçici şifre ile kontrol et
        // Kullanıcının bir geçici şifresi var mı ve formdan gelen şifreyle eşleşiyor mu?
        if ($user['gecici_sifre'] !== null && $user['gecici_sifre'] === $sifre) {
            $login_successful = true;
            // Geçici şifre kullanıldığı için onu kalıcı hale getir
            $yeni_hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE kullanicilar SET sifre = ?, gecici_sifre = NULL WHERE id = ?");
            $update_stmt->bind_param("si", $yeni_hashed_sifre, $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
        } 
        // 2. Eğer geçici şifre eşleşmezse, normal (hash'lenmiş) şifre ile kontrol et
        else if (password_verify($sifre, $user['sifre'])) {
            $login_successful = true;
        }

        // Eğer giriş başarılıysa (yöntem fark etmeksizin)
        if ($login_successful) {
            // Oturum (session) bilgilerini ayarla
            $_SESSION['kullanici_id'] = $user['id'];
            $_SESSION['kullanici_adi'] = $user['kullanici_adi'];
            $_SESSION['ad_soyad'] = $user['ad_soyad'];
            $_SESSION['rol'] = $user['rol'];

            // Kullanıcıyı ana panele yönlendir
            header("Location: ../panel.php");
            exit();
        } else {
            // Şifre yanlışsa hata mesajı oluştur ve giriş sayfasına geri yönlendir
            $_SESSION['hata_mesaji'] = "Kullanıcı adı veya şifre hatalı.";
            header("Location: ../giris.php");
            exit();
        }
    } else {
        // Kullanıcı bulunamadıysa hata mesajı oluştur ve giriş sayfasına geri yönlendir
        $_SESSION['hata_mesaji'] = "Kullanıcı adı veya şifre hatalı.";
        header("Location: ../giris.php");
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    // Eğer sayfaya form gönderilmeden direkt olarak erişilmeye çalışılırsa anasayfaya yönlendir
    header("Location: ../index.php");
    exit();
}
?>
