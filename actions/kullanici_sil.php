

<?php // actions/kullanici_sil.php ?>
<?php
session_start();
require_once '../includes/db_connect.php';

if (isset($_GET['id'])) {
    $kullanici_id = (int)$_GET['id'];
    
    // Admin kullanıcısının silinmesini engelle (güvenlik önlemi)
    $stmt_check = $conn->prepare("SELECT rol FROM kullanicilar WHERE id = ?");
    $stmt_check->bind_param("i", $kullanici_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['rol'] == 'admin') {
        $_SESSION['form_hatasi'] = "Admin kullanıcısı silinemez!";
    } else {
        $stmt = $conn->prepare("DELETE FROM kullanicilar WHERE id = ?");
        $stmt->bind_param("i", $kullanici_id);
        if ($stmt->execute()) {
            $_SESSION['form_mesaji'] = "Kullanıcı başarıyla silindi.";
        } else {
            $_SESSION['form_hatasi'] = "Kullanıcı silinirken bir hata oluştu.";
        }
        $stmt->close();
    }
    $stmt_check->close();
    $conn->close();
}
header("Location: ../panel.php?sayfa=kullanicilar");
exit();
?>
