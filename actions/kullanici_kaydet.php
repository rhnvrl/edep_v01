<?php
session_start();
require_once '../includes/db_connect.php';

// Sadece admin erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Yetkisiz erişim.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_id = (int)$_POST['kullanici_id'];
    $ad_soyad = trim($_POST['ad_soyad']);
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = $_POST['sifre'];
    $rol = $_POST['rol'];
    $email = trim($_POST['email']);
    $telefon = trim($_POST['telefon']);
    $telegram_chat_id = trim($_POST['telegram_chat_id']);

    // Form doğrulama
    if (empty($ad_soyad) || empty($kullanici_adi) || empty($rol) || ($kullanici_id == 0 && empty($sifre))) {
        $_SESSION['form_hatasi'] = "Ad Soyad, Kullanıcı Adı, Rol ve (yeni kullanıcı için) Şifre alanları zorunludur.";
        header("Location: ../panel.php?sayfa=kullanici_form" . ($kullanici_id > 0 ? "&id=$kullanici_id" : ""));
        exit();
    }

    if ($kullanici_id > 0) { // Güncelleme işlemi
        if (!empty($sifre)) {
            $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE kullanicilar SET ad_soyad=?, kullanici_adi=?, sifre=?, rol=?, email=?, telefon=?, telegram_chat_id=? WHERE id=?");
            $stmt->bind_param("sssssssi", $ad_soyad, $kullanici_adi, $hashed_sifre, $rol, $email, $telefon, $telegram_chat_id, $kullanici_id);
        } else {
            $stmt = $conn->prepare("UPDATE kullanicilar SET ad_soyad=?, kullanici_adi=?, rol=?, email=?, telefon=?, telegram_chat_id=? WHERE id=?");
            $stmt->bind_param("ssssssi", $ad_soyad, $kullanici_adi, $rol, $email, $telefon, $telegram_chat_id, $kullanici_id);
        }
        $mesaj = "Kullanıcı başarıyla güncellendi.";
        
        if ($stmt->execute()) {
            $_SESSION['form_mesaji'] = $mesaj;
        } else {
            $_SESSION['form_hatasi'] = "İşlem sırasında bir hata oluştu: " . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        header("Location: ../panel.php?sayfa=kullanici_form&id=$kullanici_id");
        exit();

    } else { // Ekleme işlemi
        $telegram_linking_code = null;
        if ($rol == 'veli') {
            $telegram_linking_code = 'VELI-' . strtoupper(bin2hex(random_bytes(4)));
        } else if ($rol == 'ogretmen') {
            $telegram_linking_code = 'OGRT-' . strtoupper(bin2hex(random_bytes(4)));
        }
        
        $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO kullanicilar (ad_soyad, kullanici_adi, sifre, rol, email, telefon, telegram_chat_id, telegram_linking_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $ad_soyad, $kullanici_adi, $hashed_sifre, $rol, $email, $telefon, $telegram_chat_id, $telegram_linking_code);
        
        if ($stmt->execute()) {
            $yeni_kullanici_id = $conn->insert_id;
            $_SESSION['form_mesaji'] = "Kullanıcı başarıyla oluşturuldu.";
            $stmt->close();
            $conn->close();
            header("Location: ../panel.php?sayfa=kullanici_form&id=$yeni_kullanici_id");
            exit();
        } else {
            if ($conn->errno == 1062) {
                 $_SESSION['form_hatasi'] = "Bu kullanıcı adı zaten alınmış.";
            } else {
                 $_SESSION['form_hatasi'] = "İşlem sırasında bir veritabanı hatası oluştu: " . $stmt->error;
            }
            $stmt->close();
            $conn->close();
            header("Location: ../panel.php?sayfa=kullanici_form");
            exit();
        }
    }
}
?>
