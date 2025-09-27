<?php
// Bu dosya sadece panel.php'den çağrılmalı ve rol kontrolü yapılmış olmalı.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ogrenci') {
    die("Bu sayfaya erişim yetkiniz yok.");
}
global $conn;
$sayfa_basligi = "Öğrenci Paneli - EDEP";
require_once __DIR__ . '/../header_panel.php';
$sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 'anasayfa';
?>

<div class="panel-container">
    <aside class="panel-sidebar">
        <div class="sidebar-header"><h3>Öğrenci Paneli</h3></div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?= ($sayfa == 'anasayfa') ? 'active' : ''; ?>"><a href="panel.php?sayfa=anasayfa">Gösterge Paneli</a></li>
                <!-- YENİ MENÜ ELEMANI -->
                <li class="<?= ($sayfa == 'ders_programim') ? 'active' : ''; ?>"><a href="panel.php?sayfa=ders_programim">Ders Programım</a></li>
                <li class="<?= (strpos($sayfa, 'odev') !== false) ? 'active' : ''; ?>"><a href="panel.php?sayfa=odevlerim">Ödevlerim</a></li>
                <li class="<?= ($sayfa == 'anketlerim') ? 'active' : ''; ?>"><a href="panel.php?sayfa=anketlerim">Anketlerim</a></li>
                <li class="<?= ($sayfa == 'raporlarim') ? 'active' : ''; ?>"><a href="panel.php?sayfa=raporlarim">Raporlarım</a></li>
            </ul>
        </nav>
    </aside>
    <div class="panel-main-content">
        <?php
        $izinli_sayfalar = ['anasayfa', 'ders_programim', 'odevlerim', 'odev_teslim_form', 'anketlerim', 'raporlarim'];
        if (in_array($sayfa, $izinli_sayfalar) && file_exists(__DIR__ . "/ogrenci_sayfalar/{$sayfa}.php")) {
            include __DIR__ . "/ogrenci_sayfalar/{$sayfa}.php";
        } else {
            include __DIR__ . "/ogrenci_sayfalar/anasayfa.php";
        }
        ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../footer_panel.php';
?>