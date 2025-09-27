<?php
session_start();

// Sadece form gönderildiğinde çalışır
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Form verilerini al ve temizle
    $ad_soyad = strip_tags(trim($_POST["ad_soyad"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $telefon = strip_tags(trim($_POST["telefon"]));
    $mesaj = trim($_POST["mesaj"]);

    // Basit doğrulama: Ad, mesaj ve geçerli bir e-posta adresi zorunlu
    if (empty($ad_soyad) || empty($mesaj) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['form_hatasi_iletisim'] = "Lütfen tüm zorunlu alanları doğru bir şekilde doldurun.";
        header("Location: ../iletisim.php");
        exit;
    }

    // --- DEĞİŞTİRİLECEK ALAN ---
    // Mesajların gönderileceği e-posta adresi
    $alici = "bilgi@egitimdestekplatformu.com.tr";
    // --------------------------

    // E-posta konusu
    $konu = "Yeni İletişim Formu Mesajı: $ad_soyad";

    // E-posta içeriğini oluştur
    $email_icerigi = "Ad Soyad: $ad_soyad\n";
    $email_icerigi .= "E-posta: $email\n";
    if (!empty($telefon)) {
        $email_icerigi .= "Telefon: $telefon\n";
    }
    $email_icerigi .= "\nMesaj:\n----------------------------------------\n$mesaj\n";

    // E-posta başlıkları (gönderenin kim olduğunu belirtir)
    $headers = "From: $ad_soyad <$email>";

    // PHP'nin mail() fonksiyonu ile e-postayı gönder
    // Not: Bu fonksiyonun çalışması için sunucunuzda mail gönderme özelliğinin (SMTP) aktif olması gerekir.
    if (mail($alici, $konu, $email_icerigi, $headers)) {
        // Başarılı olursa
        $_SESSION['form_mesaji_iletisim'] = "Mesajınız başarıyla gönderildi. En kısa sürede size geri dönüş yapacağız.";
    } else {
        // Başarısız olursa
        $_SESSION['form_hatasi_iletisim'] = "Mesajınız gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin veya doğrudan e-posta adresimiz üzerinden bize ulaşın.";
    }

    // Kullanıcıyı tekrar iletişim sayfasına yönlendir
    header("Location: ../iletisim.php");
    exit;

} else {
    // Eğer sayfaya direkt erişilmeye çalışılırsa anasayfaya yönlendir
    header("Location: ../index.php");
    exit;
}
?>
