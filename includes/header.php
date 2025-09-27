<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Aktif sayfa değişkeni tanımlı değilse, varsayılan bir değer ata
if (!isset($sayfa)) {
    $sayfa = '';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eğitim Destek Platformu</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/edepLogo01a.png" type="image/png">
</head>
<body class="frontend-body"> 
    <header class="main-header">
        <div class="container">
            <!-- DÜZELTME: Logo yolu güncellendi -->
            <a href="index.php" class="logo"><img src="img/logo.png" alt="Eğitim Destek Platformu Logosu"></a>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="<?= ($sayfa == 'anasayfa') ? 'active' : '' ?>">Anasayfa</a></li>
                    <li><a href="programlar.php" class="<?= ($sayfa == 'programlar') ? 'active' : '' ?>">Programlarımız</a></li>
                    <li><a href="hakkimizda.php" class="<?= ($sayfa == 'hakkimizda') ? 'active' : '' ?>">Hakkımızda</a></li>
                    <li><a href="iletisim.php" class="<?= ($sayfa == 'iletisim') ? 'active' : '' ?>">İletişim</a></li>
                </ul>
            </nav>

            <div class="header-buttons">
                <?php if (isset($_SESSION['kullanici_id'])): ?>
                    <a href="panel.php" class="btn btn-primary">Panele Git</a>
                    <a href="actions/cikis.php" class="btn btn-secondary">Çıkış Yap</a>
                <?php else: ?>
                    <a href="giris.php" class="btn btn-primary">Giriş Yap</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-nav-toggle">
                <span class="bar"></span><span class="bar"></span><span class="bar"></span>
            </button>
        </div>
    </header>

    <div class="mobile-nav">
        <button class="mobile-nav-close">&times;</button>
        <nav>
            <ul>
                <li><a href="index.php">Anasayfa</a></li>
                <li><a href="programlar.php">Programlarımız</a></li>
                <li><a href="hakkimizda.php">Hakkımızda</a></li>
                <li><a href="iletisim.php">İletişim</a></li>
            </ul>
        </nav>
        <hr>
        <div class="mobile-nav-buttons">
            <?php if (isset($_SESSION['kullanici_id'])): ?>
                <a href="panel.php" class="btn btn-primary">Panele Git</a>
                <a href="actions/cikis.php" class="btn btn-secondary">Çıkış Yap</a>
            <?php else: ?>
                <a href="giris.php" class="btn btn-primary">Giriş Yap</a>
            <?php endif; ?>
        </div>
    </div>