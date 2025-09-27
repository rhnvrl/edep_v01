<?php
// Bu dosya sadece panel.php'den çağrılmalı ve rol kontrolü yapılmış olmalı.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'bas_ogretmen') {
    die("Bu sayfaya erişim yetkiniz yok.");
}

// Sayfa başlığını ayarla ve header'ı dahil et
$sayfa_basligi = "Baş Öğretmen Paneli - EDEP";
include 'includes/header_panel.php'; 

// Hangi sayfanın yükleneceğini belirle
$sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 'anasayfa';
?>

<div class="panel-container">
    <aside class="panel-sidebar">
        <div class="sidebar-header">
            <h3>Baş Öğretmen Paneli</h3>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo ($sayfa == 'anasayfa') ? 'active' : ''; ?>">
                    <a href="panel.php?sayfa=anasayfa">Gösterge Paneli</a>
                </li>
                <li class="<?php echo ($sayfa == 'genel_raporlar') ? 'active' : ''; ?>">
                    <a href="panel.php?sayfa=genel_raporlar">Genel Raporlar</a>
                </li>
            </ul>
        </nav>
    </aside>
    <div class="panel-main-content">
        <?php
        // İstenen sayfayı yükle
        // Not: Bu sayfaların hepsi `includes/paneller/bas_ogretmen_sayfalar/` klasöründe olmalı
        $izinli_sayfalar = ['anasayfa', 'genel_raporlar'];
        if (in_array($sayfa, $izinli_sayfalar) && file_exists("includes/paneller/bas_ogretmen_sayfalar/{$sayfa}.php")) {
            include "includes/paneller/bas_ogretmen_sayfalar/{$sayfa}.php";
        } else {
            // Sayfa bulunamazsa veya izinli değilse anasayfayı yükle
            include "includes/paneller/bas_ogretmen_sayfalar/anasayfa.php";
        }
        ?>
    </div>
</div>

<?php 
// Panel için özel footer
include 'includes/footer_panel.php'; 
?>
