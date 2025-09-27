<?php
// Bu dosya sadece panel.php'den çağrılmalı ve rol kontrolü yapılmış olmalı.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'veli') {
    die("Bu sayfaya erişim yetkiniz yok.");
}

// Sayfa başlığını ayarla ve header'ı dahil et
$sayfa_basligi = "Veli Paneli - EDEP";
include 'includes/header_panel.php'; 

// Hangi sayfanın yükleneceğini belirle
$sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 'anasayfa';
?>

<div class="panel-container">
    <aside class="panel-sidebar">
        <div class="sidebar-header">
            <h3>Veli Paneli</h3>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo ($sayfa == 'anasayfa') ? 'active' : ''; ?>">
                    <a href="panel.php?sayfa=anasayfa">Gösterge Paneli</a>
                </li>
                <li class="<?php echo ($sayfa == 'odev_takibi' || $sayfa == 'veli_odev_detay') ? 'active' : ''; ?>">
                    <a href="panel.php?sayfa=odev_takibi">Ödev Takibi</a>
                </li>
                <li class="<?php echo ($sayfa == 'raporlar') ? 'active' : ''; ?>">
                    <a href="panel.php?sayfa=raporlar">Gelişim Raporları</a>
                </li>
            </ul>
        </nav>
    </aside>
    <div class="panel-main-content">
        <?php
        // İstenen sayfayı yükle
        $izinli_sayfalar = ['anasayfa', 'odev_takibi', 'veli_odev_detay', 'raporlar'];
        if (in_array($sayfa, $izinli_sayfalar) && file_exists("includes/paneller/veli_sayfalar/{$sayfa}.php")) {
            include "includes/paneller/veli_sayfalar/{$sayfa}.php";
        } else {
            include "includes/paneller/veli_sayfalar/anasayfa.php";
        }
        ?>
    </div>
</div>

<?php 
// Panel için özel footer
include 'includes/footer_panel.php'; 
?>
