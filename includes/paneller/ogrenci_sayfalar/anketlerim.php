<?php
// Bu sayfanın içeriği includes/paneller/ogrenci_paneli.php tarafından çağrılır.
global $conn;
$ogrenci_id = $_SESSION['kullanici_id'];

// Öğrencinin dahil olduğu sınıflardaki tüm anketleri ve cevap durumlarını çek
$stmt = $conn->prepare("
    SELECT 
        a.id as anket_id, 
        a.anket_basligi, 
        s.sinif_adi, 
        k.ad_soyad as ogretmen_adi,
        ac.id as cevap_id
    FROM anketler a
    JOIN siniflar s ON a.sinif_id = s.id
    JOIN kullanicilar k ON a.ogretmen_id = k.id
    JOIN ogrenci_sinif_iliskisi osi ON a.sinif_id = osi.sinif_id
    LEFT JOIN anket_cevaplari ac ON a.id = ac.anket_id AND ac.ogrenci_id = ?
    WHERE osi.ogrenci_id = ?
    GROUP BY a.id
    ORDER BY a.olusturma_tarihi DESC
");
$stmt->bind_param("ii", $ogrenci_id, $ogrenci_id);
$stmt->execute();
$anketler = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="card">
    <div class="card-header">
        <h1>Anketlerim</h1>
    </div>
    <div class="card-body">
        <p class="subtitle" style="margin-bottom: 30px;">Öğretmenlerinin gönderdiği anketleri buradan cevaplayabilirsin.</p>

        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?>">
                <?= htmlspecialchars($_SESSION['mesaj']) ?>
            </div>
            <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
        <?php endif; ?>

        <div class="anket-listesi">
            <?php if (count($anketler) > 0): ?>
                <?php foreach ($anketler as $anket): ?>
                    <div class="card" style="margin-top: 0; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.07);">
                        <div class="anket-card-header" style="padding-bottom: 15px; border-bottom: 1px solid #eee; margin-bottom: 20px;">
                            <h4 style="color: var(--secondary-color);"><?= htmlspecialchars($anket['anket_basligi']) ?></h4>
                            <small class="anket-meta">Öğretmen: <?= htmlspecialchars($anket['ogretmen_adi']) ?> | Sınıf: <?= htmlspecialchars($anket['sinif_adi']) ?></small>
                        </div>
                        
                        <?php if ($anket['cevap_id']): ?>
                            <p style="color: var(--accent-color); font-weight: 500;">Bu anketi zaten cevapladın. Teşekkürler!</p>
                        <?php else: 
                            // Anket seçeneklerini çek
                            $stmt_secenekler = $conn->prepare("SELECT id, secenek_metni FROM anket_secenekleri WHERE anket_id = ?");
                            $stmt_secenekler->bind_param("i", $anket['anket_id']);
                            $stmt_secenekler->execute();
                            $secenekler = $stmt_secenekler->get_result()->fetch_all(MYSQLI_ASSOC);
                            $stmt_secenekler->close();
                        ?>
                            <form action="actions/anket_cevapla_action.php" method="POST">
                                <input type="hidden" name="anket_id" value="<?= $anket['anket_id'] ?>">
                                <div class="anket-secenekler">
                                    <?php foreach ($secenekler as $secenek): ?>
                                        <label class="radio-label">
                                            <input type="radio" name="secenek_id" value="<?= $secenek['id'] ?>" required>
                                            <?= htmlspecialchars($secenek['secenek_metni']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-actions" style="margin-top: 20px;">
                                    <button type="submit" class="btn btn-primary">Gönder</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center;">Henüz sana atanmış bir anket bulunmuyor.</p>
            <?php endif; ?>
        </div>

    </div>
</div>