<?php
// Bu dosya sadece panel.php'den çağrılmalı ve rol kontrolü yapılmış olmalı.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ogretmen') {
    die("Bu sayfaya erişim yetkiniz yok.");
}
global $conn;
$sayfa_basligi = "Öğretmen Paneli - EDEP";
require_once __DIR__ . '/../header_panel.php';
$sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 'anasayfa';
?>

<div class="panel-container">
    <aside class="panel-sidebar">
        <div class="sidebar-header"><h3>Öğretmen Paneli</h3></div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?= ($sayfa == 'anasayfa') ? 'active' : ''; ?>"><a href="panel.php?sayfa=anasayfa">Gösterge Paneli</a></li>
                <li class="<?= (strpos($sayfa, 'odev') !== false) ? 'active' : ''; ?>"><a href="panel.php?sayfa=odev_yonetimi">Ödev Yönetimi</a></li>
                <li class="<?= (strpos($sayfa, 'anket') !== false) ? 'active' : ''; ?>"><a href="panel.php?sayfa=anket_yonetimi">Anket Yönetimi</a></li>
                <li class="<?= (strpos($sayfa, 'sinif') !== false || strpos($sayfa, 'ogrenci_rapor') !== false) ? 'active' : ''; ?>"><a href="panel.php?sayfa=sinif_listeleri">Sınıf Listeleri</a></li>
                <!-- YENİ MENÜ ELEMANI -->
                <li class="<?= ($sayfa == 'zoom_ayarlari') ? 'active' : ''; ?>"><a href="panel.php?sayfa=zoom_ayarlari">Zoom Ayarları</a></li>
            </ul>
        </nav>
    </aside>
    <div class="panel-main-content">
        <?php
        $izinli_sayfalar = ['anasayfa', 'odev_yonetimi', 'odev_form', 'odev_detay', 'anket_yonetimi', 'anket_form', 'anket_detay', 'sinif_listeleri', 'ogrenci_rapor_detay', 'zoom_ayarlari'];
        if (in_array($sayfa, $izinli_sayfalar) && file_exists(__DIR__ . "/ogretmen_sayfalar/{$sayfa}.php")) {
            include __DIR__ . "/ogretmen_sayfalar/{$sayfa}.php";
        } else {
            include __DIR__ . "/ogretmen_sayfalar/anasayfa.php";
        }
        ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../footer_panel.php';
?>