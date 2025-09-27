<?php
// Bu dosya sadece panel.php'den çağrılmalı ve rol kontrolü yapılmış olmalı.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['rol'])) {
    die("Bu sayfaya erişim yetkiniz yok.");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($sayfa_basligi) ? htmlspecialchars($sayfa_basligi) : 'EDEP'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/favicon.png" type="image/png">
</head>
<body class="panel-body">

<header class="panel-top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <a href="panel.php" class="logo-panel">
            <img src="img/logo.png" alt="EDEP Logosu">
        </a>
    </div>
    <div class="top-bar-right">
        <span class="user-name"><?php echo htmlspecialchars($_SESSION['ad_soyad']); ?></span>
        <a href="actions/cikis.php" class="nav-logout-btn">Çıkış Yap</a>
    </div>
</header>
