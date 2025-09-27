<?php
session_start();
require_once '../includes/db_connect.php';

// Sadece admin erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Yetkisiz erişim.");
}

if (isset($_POST['sistemi_sifirla'])) {
    $islem_tipi = $_POST['islem_tipi'];
    $onay_metni = $_POST['onay_metni'];

    if ($onay_metni !== 'SIFIRLA') {
        $_SESSION['form_hatasi_ayar'] = "Onay metni yanlış. İşlem iptal edildi.";
        header("Location: ../panel.php?sayfa=ayarlar");
        exit();
    }

    $conn->begin_transaction();
    try {
        switch ($islem_tipi) {
            case 'odevleri_sil':
                $conn->query("SET FOREIGN_KEY_CHECKS = 0;");
                $conn->query("TRUNCATE TABLE odev_dosyalari;");
                $conn->query("TRUNCATE TABLE odev_teslimleri;");
                $conn->query("TRUNCATE TABLE odevler;");
                $conn->query("SET FOREIGN_KEY_CHECKS = 1;");
                $_SESSION['form_mesaji_ayar'] = "Tüm ödevler, teslimler ve dosyalar başarıyla silindi.";
                break;

            case 'anketleri_sil':
                $conn->query("SET FOREIGN_KEY_CHECKS = 0;");
                $conn->query("TRUNCATE TABLE anket_cevaplari;");
                $conn->query("TRUNCATE TABLE anket_secenekleri;");
                $conn->query("TRUNCATE TABLE anketler;");
                $conn->query("SET FOREIGN_KEY_CHECKS = 1;");
                $_SESSION['form_mesaji_ayar'] = "Tüm anketler ve cevapları başarıyla silindi.";
                break;
            
            case 'ogrenci_veli_sil':
                $conn->query("DELETE FROM kullanicilar WHERE rol IN ('ogrenci', 'veli');");
                $_SESSION['form_mesaji_ayar'] = "Tüm öğrenci ve veli hesapları başarıyla silindi.";
                break;

            case 'tam_sifirlama':
                $conn->query("SET FOREIGN_KEY_CHECKS = 0;");
                $conn->query("TRUNCATE TABLE odev_dosyalari;");
                $conn->query("TRUNCATE TABLE odev_teslimleri;");
                $conn->query("TRUNCATE TABLE odevler;");
                $conn->query("TRUNCATE TABLE anket_cevaplari;");
                $conn->query("TRUNCATE TABLE anket_secenekleri;");
                $conn->query("TRUNCATE TABLE anketler;");
                $conn->query("TRUNCATE TABLE veli_ogrenci_iliskisi;");
                $conn->query("TRUNCATE TABLE ogrenci_sinif_iliskisi;");
                $conn->query("DELETE FROM kullanicilar WHERE rol IN ('ogrenci', 'veli');");
                $conn->query("SET FOREIGN_KEY_CHECKS = 1;");
                $_SESSION['form_mesaji_ayar'] = "SİSTEM SIFIRLANDI! Sistem yeni eğitim dönemine hazır.";
                break;

            default:
                $_SESSION['form_hatasi_ayar'] = "Geçersiz işlem tipi seçildi.";
                break;
        }
        $conn->commit();
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['form_hatasi_ayar'] = "İşlem sırasında bir veritabanı hatası oluştu: " . $exception->getMessage();
    }
}

$conn->close();
header("Location: ../panel.php?sayfa=ayarlar");
exit();
