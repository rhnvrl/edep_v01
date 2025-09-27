<?php
// Bu sayfanın içeriği includes/paneller/ogretmen_paneli.php tarafından çağrılır.
global $conn;
$ogretmen_id = $_SESSION['kullanici_id'];

// DÜZELTME: Doğru sütun adı kullanıldı (anket_basligi)
$stmt = $conn->prepare("
    SELECT a.id, a.anket_basligi, a.olusturma_tarihi, s.sinif_adi 
    FROM anketler a
    JOIN siniflar s ON a.sinif_id = s.id
    WHERE a.ogretmen_id = ?
    ORDER BY a.olusturma_tarihi DESC
");
$stmt->bind_param("i", $ogretmen_id);
$stmt->execute();
$anketler = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="card">
    <div class="card-header">
        <h1>Anket Yönetimi</h1>
        <a href="panel.php?sayfa=anket_form" class="btn btn-primary">Yeni Anket Oluştur</a>
    </div>
    <div class="card-body">
        <p class="subtitle" style="margin-bottom: 30px;">Yeni anketler oluşturun ve sonuçlarını takip edin.</p>
        
        <?php if (isset($_SESSION['mesaj'])): ?>
            <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?>">
                <?= htmlspecialchars($_SESSION['mesaj']) ?>
            </div>
            <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Anket Başlığı</th>
                        <th>Sınıf</th>
                        <th>Oluşturma Tarihi</th>
                        <th style="width: 200px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($anketler) > 0): ?>
                        <?php foreach ($anketler as $anket): ?>
                            <tr>
                                <!-- DÜZELTME: Doğru sütun adı kullanıldı -->
                                <td><?= htmlspecialchars($anket['anket_basligi']) ?></td>
                                <td><?= htmlspecialchars($anket['sinif_adi']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($anket['olusturma_tarihi'])) ?></td>
                                <td class="actions">
                                    <a href="panel.php?sayfa=anket_detay&anket_id=<?= $anket['id'] ?>" class="btn btn-info btn-sm">Sonuçlar</a>
                                    <a href="actions/anket_action.php?islem=sil&anket_id=<?= $anket['id'] ?>" class="btn btn-danger btn-sm btn-sil">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <tr>
                            <td colspan="4" style="text-align: center;">Henüz oluşturulmuş bir anketiniz bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>