<?php
session_start();
require_once '../includes/db_connect.php';

// Sadece admin erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Yetkisiz erişim.");
}

// Ekleme ve Güncelleme İşlemi
if (isset($_POST['kaydet'])) {
    $brans_id = (int)$_POST['brans_id'];
    $brans_adi = trim($_POST['brans_adi']);
    $brans_kisaltma = trim(strtoupper($_POST['brans_kisaltma']));

    if (empty($brans_adi) || empty($brans_kisaltma)) {
        $_SESSION['form_hatasi'] = "Branş adı ve kısaltması boş bırakılamaz.";
    } else {
        if ($brans_id > 0) { // Güncelleme
            $stmt = $conn->prepare("UPDATE branslar SET brans_adi = ?, brans_kisaltma = ? WHERE id = ?");
            $stmt->bind_param("ssi", $brans_adi, $brans_kisaltma, $brans_id);
            $_SESSION['form_mesaji'] = "Branş başarıyla güncellendi.";
        } else { // Ekleme
            $stmt = $conn->prepare("INSERT INTO branslar (brans_adi, brans_kisaltma) VALUES (?, ?)");
            $stmt->bind_param("ss", $brans_adi, $brans_kisaltma);
            $_SESSION['form_mesaji'] = "Branş başarıyla eklendi.";
        }

        if (!$stmt->execute()) {
            $_SESSION['form_hatasi'] = "İşlem sırasında bir hata oluştu: " . $stmt->error;
            unset($_SESSION['form_mesaji']);
        }
        $stmt->close();
    }
}

// Silme İşlemi
if (isset($_GET['delete_id'])) {
    $brans_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM branslar WHERE id = ?");
    $stmt->bind_param("i", $brans_id);
    if ($stmt->execute()) {
        $_SESSION['form_mesaji'] = "Branş başarıyla silindi.";
    } else {
        $_SESSION['form_hatasi'] = "Branş silinirken bir hata oluştu. Bu branşa atanmış ödevler olabilir.";
    }
    $stmt->close();
}

$conn->close();
header("Location: ../panel.php?sayfa=branslar");
exit();
?>
