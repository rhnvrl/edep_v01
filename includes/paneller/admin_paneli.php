<?php
// Bu dosya sadece panel.php'den çağrılmalı ve rol kontrolü yapılmış olmalı.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Bu sayfaya erişim yetkiniz yok.");
}
global $conn;
$sayfa_basligi = "Admin Paneli - EDEP";
require_once __DIR__ . '/../header_panel.php';
$sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 'anasayfa';
?>

<div class="panel-container">
    <aside class="panel-sidebar">
        <div class="sidebar-header"><h3>Admin Paneli</h3></div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?= ($sayfa == 'anasayfa') ? 'active' : ''; ?>"><a href="panel.php?sayfa=anasayfa">Gösterge Paneli</a></li>
                <li class="<?= ($sayfa == 'kullanicilar' || $sayfa == 'kullanici_form') ? 'active' : ''; ?>"><a href="panel.php?sayfa=kullanicilar">Kullanıcı Yönetimi</a></li>
                <li class="<?= ($sayfa == 'siniflar') ? 'active' : ''; ?>"><a href="panel.php?sayfa=siniflar">Sınıf Yönetimi</a></li>
                <li class="<?= ($sayfa == 'branslar') ? 'active' : ''; ?>"><a href="panel.php?sayfa=branslar">Branş Yönetimi</a></li>
                <li class="<?= ($sayfa == 'atamalar' || strpos($sayfa, '_atama_form') !== false) ? 'active' : ''; ?>"><a href="panel.php?sayfa=atamalar">Atama Yönetimi</a></li>
                <li class="<?= ($sayfa == 'ders_programi_yonetimi') ? 'active' : ''; ?>"><a href="panel.php?sayfa=ders_programi_yonetimi">Ders Programı Yönetimi</a></li>
                <li class="<?= ($sayfa == 'whatsapp_gonder') ? 'active' : ''; ?>"><a href="panel.php?sayfa=whatsapp_gonder">WhatsApp Gönder</a></li>
                <li class="<?= ($sayfa == 'ayarlar') ? 'active' : ''; ?>"><a href="panel.php?sayfa=ayarlar">Sistem Ayarları</a></li>
            </ul>
        </nav>
    </aside>
    <div class="panel-main-content">
        <?php
        $izinli_sayfalar = ['anasayfa', 'kullanicilar', 'kullanici_form', 'siniflar', 'branslar', 'atamalar', 'ogretmen_atama_form', 'ogrenci_atama_form', 'veli_atama_form', 'whatsapp_gonder', 'ayarlar', 'ders_programi_yonetimi'];
        if (in_array($sayfa, $izinli_sayfalar) && file_exists(__DIR__ . "/admin_sayfalar/{$sayfa}.php")) {
            include __DIR__ . "/admin_sayfalar/{$sayfa}.php";
        } else {
            include __DIR__ . "/admin_sayfalar/anasayfa.php";
        }
        ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../footer_panel.php';
?>