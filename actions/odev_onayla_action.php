<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/telegram_helper.php';

// Sadece veli erişebilir
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'veli') {
    die("Yetkisiz erişim.");
}

$veli_id = $_SESSION['kullanici_id'];
$teslim_id = isset($_REQUEST['teslim_id']) ? (int)$_REQUEST['teslim_id'] : 0;
$reddet_id = isset($_GET['reddet_id']) ? (int)$_GET['reddet_id'] : 0;

if ($teslim_id > 0 && isset($_POST['onayla_ve_yukle'])) { // Web formundan Onaylama
    
    // Güvenlik kontrolü
    $sql_check = "SELECT ot.id FROM odev_teslimleri ot INNER JOIN veli_ogrenci_iliskisi voi ON ot.ogrenci_id = voi.ogrenci_id WHERE ot.id = ? AND voi.veli_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $teslim_id, $veli_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $dosya_yuklendi_mi = isset($_FILES['odev_dosyalari']) && !empty(array_filter($_FILES['odev_dosyalari']['name']));
        $mevcut_dosya_sayisi = $conn->query("SELECT COUNT(*) as sayi FROM odev_dosyalari WHERE teslim_id = $teslim_id")->fetch_assoc()['sayi'];

        if (!$dosya_yuklendi_mi && $mevcut_dosya_sayisi == 0) {
            $_SESSION['form_hatasi_onay'] = "Öğrenci dosya yüklemediği için, onaylamak amacıyla sizin en az bir dosya yüklemeniz gerekmektedir.";
        } else {
            // Varsa yeni dosyaları yükle
            if ($dosya_yuklendi_mi) {
                $upload_dir = '../uploads/odevler/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $stmt_dosya = $conn->prepare("INSERT INTO odev_dosyalari (teslim_id, dosya_yolu, yukleyen_kullanici_id) VALUES (?, ?, ?)");
                foreach ($_FILES['odev_dosyalari']['name'] as $key => $name) {
                    if ($_FILES['odev_dosyalari']['error'][$key] == 0) {
                        $file_name = time() . '_' . uniqid() . '_' . basename($name);
                        $target_file = $upload_dir . $file_name;
                        $file_path_for_db = 'uploads/odevler/' . $file_name;
                        if (move_uploaded_file($_FILES['odev_dosyalari']['tmp_name'][$key], $target_file)) {
                            $stmt_dosya->bind_param("isi", $teslim_id, $file_path_for_db, $veli_id);
                            $stmt_dosya->execute();
                        }
                    }
                }
                $stmt_dosya->close();
            }
            
            // Onayı yap
            $stmt_update = $conn->prepare("UPDATE odev_teslimleri SET durum = 'Teslim Edildi - Veli Tarafından Onaylandı' WHERE id = ?");
            $stmt_update->bind_param("i", $teslim_id);
            $stmt_update->execute();
            $_SESSION['form_mesaji_onay'] = "Ödev başarıyla onaylandı.";
            // TODO: Öğretmene bildirim gönder
        }
    } else {
        $_SESSION['form_hatasi_onay'] = "Bu işlemi yapma yetkiniz yok.";
    }
    
} else if ($reddet_id > 0) { // Web formundan Reddetme
    
    $teslim_id = $reddet_id;
    // Güvenlik kontrolü
    $sql_check = "SELECT ot.id FROM odev_teslimleri ot INNER JOIN veli_ogrenci_iliskisi voi ON ot.ogrenci_id = voi.ogrenci_id WHERE ot.id = ? AND voi.veli_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $teslim_id, $veli_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_update = $conn->prepare("UPDATE odev_teslimleri SET durum = 'Reddedildi' WHERE id = ?");
        $stmt_update->bind_param("i", $teslim_id);
        $stmt_update->execute();
        $_SESSION['form_mesaji_onay'] = "Ödev reddedildi.";
        // TODO: Öğrenciye bildirim gönder
    }
}

$conn->close();
header("Location: ../panel.php?sayfa=odev_takibi");
exit();
?>
