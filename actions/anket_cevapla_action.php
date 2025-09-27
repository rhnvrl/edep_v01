<?php
session_start();
require_once '../includes/db_connect.php';

// Sadece öğrenci erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ogrenci') {
    die("Yetkisiz erişim.");
}

if (isset($_POST['anket_cevapla'])) {
    $ogrenci_id = $_SESSION['kullanici_id'];
    $anket_id = (int)$_POST['anket_id'];
    $secenek_id = (int)$_POST['secenek_id'];

    if (empty($anket_id) || empty($secenek_id)) {
        $_SESSION['form_hatasi_anket_cevap'] = "Lütfen bir seçenek işaretleyin.";
    } else {
        // Öğrencinin bu anketi daha önce cevaplayıp cevaplamadığını kontrol et
        $check_stmt = $conn->prepare("SELECT id FROM anket_cevaplari WHERE anket_id = ? AND ogrenci_id = ?");
        $check_stmt->bind_param("ii", $anket_id, $ogrenci_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['form_hatasi_anket_cevap'] = "Bu anketi zaten daha önce cevapladınız.";
        } else {
            // Cevabı kaydet
            $stmt = $conn->prepare("INSERT INTO anket_cevaplari (anket_id, ogrenci_id, secilen_secenek_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $anket_id, $ogrenci_id, $secenek_id);
            if ($stmt->execute()) {
                $_SESSION['form_mesaji_anket_cevap'] = "Anket cevabın başarıyla kaydedildi.";
            } else {
                $_SESSION['form_hatasi_anket_cevap'] = "Cevap kaydedilirken bir hata oluştu.";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

$conn->close();
header("Location: ../panel.php?sayfa=anketlerim");
exit();
?>
