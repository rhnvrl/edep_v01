<?php
// Hata raporlamayı açalım
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Veritabanı bağlantısı
require_once 'includes/db_connect.php';

echo "<h1>Ödev Kodu Onarım Betiği Başlatıldı</h1>";

// odev_kodu boş olan ödevleri bul
$sql = "SELECT id, brans_id FROM odevler WHERE odev_kodu IS NULL OR odev_kodu = ''";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p>" . $result->num_rows . " adet kodu olmayan ödev bulundu. Düzeltiliyor...</p><ul>";

    // Her bir ödev için işlem yap
    while($odev = $result->fetch_assoc()) {
        $odev_id = $odev['id'];
        $brans_id = $odev['brans_id'];

        // Branş kısaltmasını bul
        $brans_sorgu = $conn->prepare("SELECT brans_kisaltma FROM branslar WHERE id = ?");
        $brans_sorgu->bind_param("i", $brans_id);
        $brans_sorgu->execute();
        $brans_sonuc = $brans_sorgu->get_result()->fetch_assoc();
        
        if ($brans_sonuc) {
            $brans_kisaltma = $brans_sonuc['brans_kisaltma'];
            $yeni_odev_kodu = strtoupper($brans_kisaltma) . $odev_id;

            // Ödevi yeni koduyla güncelle
            $guncelle_stmt = $conn->prepare("UPDATE odevler SET odev_kodu = ? WHERE id = ?");
            $guncelle_stmt->bind_param("si", $yeni_odev_kodu, $odev_id);
            if ($guncelle_stmt->execute()) {
                echo "<li>Ödev ID: $odev_id -> Yeni Kod: <b>$yeni_odev_kodu</b> (BAŞARILI)</li>";
            } else {
                echo "<li>Ödev ID: $odev_id -> Güncelleme başarısız: " . $guncelle_stmt->error . "</li>";
            }
            $guncelle_stmt->close();
        } else {
             echo "<li>Ödev ID: $odev_id -> Branş bilgisi bulunamadı (Branş ID: $brans_id). Atlandı.</li>";
        }
        $brans_sorgu->close();
    }
    echo "</ul><p>Onarım tamamlandı!</p>";
} else {
    echo "<p>Kodu olmayan ödev bulunamadı. Veritabanınız güncel görünüyor.</p>";
}

$conn->close();
?>
