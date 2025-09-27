<?php
require_once 'includes/db_connect.php';
?>
<div class="panel-header">
    <h1>Sistem Ayarları ve Bakım</h1>
    <p>Platformun genel ayarlarını yönetin ve dönem sonu işlemlerini gerçekleştirin.</p>
</div>
<div class="panel-content">
    
    <div class="card danger-zone">
        <h3>Tehlikeli Alan</h3>
        <p><strong>DİKKAT:</strong> Buradaki işlemler geri alınamaz. Yeni bir eğitim dönemine hazırlık dışında kullanılması tavsiye edilmez.</p>
        
        <form action="actions/sistem_ayarlari_action.php" method="POST" class="styled-form" id="reset-form">
            <div class="form-group">
                <label for="islem_tipi">Gerçekleştirmek İstediğiniz İşlem:</label>
                <select name="islem_tipi" id="islem_tipi" required>
                    <option value="">Lütfen bir işlem seçin...</option>
                    <option value="odevleri_sil">Tüm Ödevleri ve Teslimleri Sil</option>
                    <option value="anketleri_sil">Tüm Anketleri ve Cevapları Sil</option>
                    <option value="ogrenci_veli_sil">Tüm Öğrencileri ve Velileri Sil</option>
                    <option value="tam_sifirlama">TAM SİSTEM SIFIRLAMA (Yeni Döneme Hazırlık)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="onay_metni">Onaylamak için "SIFIRLA" yazın:</label>
                <input type="text" name="onay_metni" id="onay_metni" required autocomplete="off">
            </div>
            
            <button type="submit" name="sistemi_sifirla" class="btn btn-danger">Seçili İşlemi Geri Alınamaz Şekilde Uygula</button>
        </form>
    </div>
</div>
