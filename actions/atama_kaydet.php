<?php
session_start();
require_once '../includes/db_connect.php';

// Yetki kontrolü
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Bu işleme yetkiniz yok.");
}

$islem = $_POST['islem'] ?? '';
$redirect_url = "../panel.php?sayfa=atamalar";

// --- ÖĞRENCİ SINIF ATAMA (ÇOKLU SEÇİM İÇİN GÜNCELLENDİ) ---
if ($islem === 'ogrenci_sinif_ata') {
    $ogrenci_id = intval($_POST['ogrenci_id']);
    $yeni_siniflar = $_POST['siniflar'] ?? []; // Formdan dizi olarak gelecek

    if ($ogrenci_id > 0) {
        // Önce öğrencinin mevcut tüm sınıf ilişkilerini sil
        $conn->query("DELETE FROM ogrenci_sinif_iliskisi WHERE ogrenci_id = $ogrenci_id");
        
        // Eğer formdan en az bir sınıf geldiyse, yenilerini ekle
        if (!empty($yeni_siniflar)) {
            $stmt_insert = $conn->prepare("INSERT INTO ogrenci_sinif_iliskisi (ogrenci_id, sinif_id) VALUES (?, ?)");
            foreach($yeni_siniflar as $sinif_id) {
                $sinif_id = intval($sinif_id);
                $stmt_insert->bind_param("ii", $ogrenci_id, $sinif_id);
                $stmt_insert->execute();
            }
            $stmt_insert->close();
        }
        $_SESSION['mesaj'] = "Öğrenci sınıf atamaları başarıyla güncellendi.";
        $_SESSION['mesaj_tur'] = "basari";
    } else {
        $_SESSION['mesaj'] = "Geçersiz öğrenci ID'si.";
        $_SESSION['mesaj_tur'] = "hata";
    }
}


// --- ÖĞRETMEN ATAMA ---
elseif ($islem === 'ogretmen_atama') {
    $ogretmen_id = intval($_POST['ogretmen_id']);
    $branslar = $_POST['branslar'] ?? [];
    $siniflar = $_POST['siniflar'] ?? [];

    if ($ogretmen_id > 0) {
        $conn->query("DELETE FROM ogretmen_brans_iliskisi WHERE ogretmen_id = $ogretmen_id");
        if (!empty($branslar)) {
            $stmt_brans = $conn->prepare("INSERT INTO ogretmen_brans_iliskisi (ogretmen_id, brans_id) VALUES (?, ?)");
            foreach ($branslar as $brans_id) {
                $stmt_brans->bind_param("ii", $ogretmen_id, $brans_id);
                $stmt_brans->execute();
            }
            $stmt_brans->close();
        }
        
        $conn->query("DELETE FROM ogretmen_sinif_iliskisi WHERE ogretmen_id = $ogretmen_id");
        if(!empty($siniflar)){
            $stmt_sinif = $conn->prepare("INSERT INTO ogretmen_sinif_iliskisi (ogretmen_id, sinif_id) VALUES (?, ?)");
             foreach ($siniflar as $sinif_id) {
                $stmt_sinif->bind_param("ii", $ogretmen_id, $sinif_id);
                $stmt_sinif->execute();
            }
            $stmt_sinif->close();
        }
        
        $_SESSION['mesaj'] = "Öğretmen atamaları başarıyla güncellendi.";
        $_SESSION['mesaj_tur'] = "basari";
    } else {
        $_SESSION['mesaj'] = "Geçersiz öğretmen ID'si.";
        $_SESSION['mesaj_tur'] = "hata";
    }
}


// --- VELİ-ÖĞRENCİ İLİŞKİLENDİRME ---
elseif ($islem === 'veli_ogrenci_ata') {
    $veli_id = intval($_POST['veli_id']);
    $yeni_ogrenciler = $_POST['ogrenciler'] ?? [];

    if ($veli_id > 0) {
        $conn->begin_transaction();
        try {
            $conn->query("DELETE FROM veli_ogrenci_iliskisi WHERE veli_id = $veli_id");
            
            if(!empty($yeni_ogrenciler)) {
                $stmt_delete_old = $conn->prepare("DELETE FROM veli_ogrenci_iliskisi WHERE ogrenci_id = ?");
                $stmt_insert_new = $conn->prepare("INSERT INTO veli_ogrenci_iliskisi (veli_id, ogrenci_id) VALUES (?, ?)");

                foreach ($yeni_ogrenciler as $ogrenci_id) {
                    $ogrenci_id = intval($ogrenci_id);
                    $stmt_delete_old->bind_param("i", $ogrenci_id);
                    $stmt_delete_old->execute();
                    $stmt_insert_new->bind_param("ii", $veli_id, $ogrenci_id);
                    $stmt_insert_new->execute();
                }
                $stmt_delete_old->close();
                $stmt_insert_new->close();
            }

            $conn->commit();
            $_SESSION['mesaj'] = "Veli-Öğrenci ilişkileri başarıyla güncellendi.";
            $_SESSION['mesaj_tur'] = "basari";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['mesaj'] = "İşlem sırasında bir hata oluştu: " . $e->getMessage();
            $_SESSION['mesaj_tur'] = "hata";
        }
    } else {
        $_SESSION['mesaj'] = "Geçersiz veli ID'si.";
        $_SESSION['mesaj_tur'] = "hata";
    }
}

else {
    $_SESSION['mesaj'] = "Geçersiz işlem.";
    $_SESSION['mesaj_tur'] = "hata";
}

header("Location: " . $redirect_url);
exit();
?>

