<?php
session_start();
require_once '../includes/db_connect.php';

// Sadece öğretmen ve admin erişebilir
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['ogretmen', 'admin'])) {
    die("Yetkisiz erişim.");
}

$teslim_id = isset($_GET['teslim_id']) ? (int)$_GET['teslim_id'] : 0;
if ($teslim_id === 0) {
    die("Geçersiz teslimat ID'si.");
}

// Güvenlik kontrolü: Bu öğretmen bu teslimatı görmeye yetkili mi?
$ogretmen_id = $_SESSION['kullanici_id'];
$sql_check = "
    SELECT ot.id 
    FROM odev_teslimleri ot
    INNER JOIN odevler o ON ot.odev_id = o.id
    WHERE ot.id = ? AND o.ogretmen_id = ?
";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $teslim_id, $ogretmen_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows === 0 && $_SESSION['rol'] != 'admin') {
    die("Bu dosyaları indirme yetkiniz yok.");
}

// İndirilecek dosyaların yollarını veritabanından al
$dosya_sorgu = $conn->prepare("SELECT dosya_yolu FROM odev_dosyalari WHERE teslim_id = ?");
$dosya_sorgu->bind_param("i", $teslim_id);
$dosya_sorgu->execute();
$dosyalar_result = $dosya_sorgu->get_result();
$dosya_yollari = $dosyalar_result->fetch_all(MYSQLI_ASSOC);

if (empty($dosya_yollari)) {
    die("İndirilecek dosya bulunamadı.");
}

// Öğrenci ve ödev bilgilerini alarak zip dosyası için bir isim oluştur
$info_sql = "SELECT k.ad_soyad, o.odev_kodu FROM odev_teslimleri ot JOIN kullanicilar k ON ot.ogrenci_id = k.id JOIN odevler o ON ot.odev_id = o.id WHERE ot.id = ?";
$info_stmt = $conn->prepare($info_sql);
$info_stmt->bind_param("i", $teslim_id);
$info_stmt->execute();
$info = $info_stmt->get_result()->fetch_assoc();
$zip_filename = 'odev_' . $info['odev_kodu'] . '_' . preg_replace('/[^A-Za-z0-9\-]/', '', $info['ad_soyad']) . '.zip';

// ZipArchive sınıfı sunucuda mevcut mu diye kontrol et
if (!class_exists('ZipArchive')) {
    die("Sunucunuzda ZipArchive eklentisi aktif değil. Lütfen hosting sağlayıcınızla görüşün.");
}

$zip = new ZipArchive();
$temp_zip_file = tempnam(sys_get_temp_dir(), 'odev_zip'); // Geçici bir dosya oluştur

if ($zip->open($temp_zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Zip dosyası oluşturulamadı.");
}

// Dosyaları zip arşivine ekle
foreach ($dosya_yollari as $dosya) {
    $file_path_on_server = '../' . $dosya['dosya_yolu'];
    if (file_exists($file_path_on_server)) {
        $zip->addFile($file_path_on_server, basename($file_path_on_server));
    }
}

$zip->close();

// İndirme için gerekli HTTP başlıklarını gönder
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
header('Content-Length: ' . filesize($temp_zip_file));
header('Pragma: no-cache'); 
header('Expires: 0');

// Zip dosyasının içeriğini oku ve tarayıcıya gönder
readfile($temp_zip_file);

// Geçici zip dosyasını sil
unlink($temp_zip_file);

exit();
?>
