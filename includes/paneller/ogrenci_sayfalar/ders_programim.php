<?php
global $conn;
$ogrenci_id = $_SESSION['kullanici_id'];

// Öğrencinin sınıfını bul
$stmt_sinif = $conn->prepare("SELECT sinif_id FROM ogrenci_sinif_iliskisi WHERE ogrenci_id = ? LIMIT 1");
$stmt_sinif->bind_param("i", $ogrenci_id);
$stmt_sinif->execute();
$result_sinif = $stmt_sinif->get_result();

$ders_programi = [];
if ($result_sinif->num_rows > 0) {
    $sinif_id = $result_sinif->fetch_assoc()['sinif_id'];
    $stmt_sinif->close();

    // Sınıfın ders programını çek
    $stmt_program = $conn->prepare("
        SELECT dp.*, b.brans_adi, k.ad_soyad as ogretmen_adi, k.zoom_id, k.zoom_parola
        FROM ders_programi dp
        JOIN branslar b ON dp.brans_id = b.id
        JOIN kullanicilar k ON dp.ogretmen_id = k.id
        WHERE dp.sinif_id = ?
        ORDER BY dp.gun, dp.baslangic_saati
    ");
    $stmt_program->bind_param("i", $sinif_id);
    $stmt_program->execute();
    $result_program = $stmt_program->get_result();
    while ($row = $result_program->fetch_assoc()) {
        $ders_programi[$row['gun']][] = $row;
    }
    $stmt_program->close();
}

$gunler = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
date_default_timezone_set('Europe/Istanbul');
$bugun_gun_no = date('N');
$su_an_zaman = strtotime(date('H:i:s'));
?>

<div class="card">
    <div class="card-header">
        <h1>Haftalık Ders Programım</h1>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table ders-programi-tablosu">
                <thead>
                    <tr>
                        <?php foreach($gunler as $gun_adi): ?>
                            <th class="<?= ($gunler[$bugun_gun_no] == $gun_adi) ? 'bugun' : '' ?>"><?= $gun_adi ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php foreach ($gunler as $num => $gun_adi): ?>
                            <td class="<?= ($num == $bugun_gun_no) ? 'bugun' : '' ?>" valign="top">
                                <?php if (isset($ders_programi[$num])): ?>
                                    <ul class="ders-listesi">
                                        <?php foreach ($ders_programi[$num] as $ders): 
                                            $baslangic_zamani = strtotime($ders['baslangic_saati']);
                                            $bitis_zamani = strtotime($ders['bitis_saati']);
                                            $derse_kalan_sure = $baslangic_zamani - $su_an_zaman;

                                            // Linkin aktif olup olmayacağını belirle
                                            $link_aktif = ($num == $bugun_gun_no && $derse_kalan_sure <= 1800 && $su_an_zaman <= $bitis_zamani);
                                            $zoom_link = "https://zoom.us/j/{$ders['zoom_id']}";
                                            if(!empty($ders['zoom_parola'])) {
                                                $zoom_link .= "?pwd={$ders['zoom_parola']}";
                                            }
                                        ?>
                                            <li class="<?= $link_aktif ? 'aktif-ders' : '' ?>">
                                                <div class="ders-bilgi">
                                                    <strong><?= substr($ders['baslangic_saati'], 0, 5) ?> - <?= substr($ders['bitis_saati'], 0, 5) ?></strong><br>
                                                    <?= htmlspecialchars($ders['brans_adi']) ?><br>
                                                    <small><?= htmlspecialchars($ders['ogretmen_adi']) ?></small>
                                                </div>
                                                <?php if($link_aktif && !empty($ders['zoom_id'])): ?>
                                                    <a href="<?= htmlspecialchars($zoom_link) ?>" target="_blank" class="btn btn-success btn-sm derse-katil-btn">Derse Katıl</a>
                                                <?php else: ?>
                                                     <button class="btn btn-secondary btn-sm derse-katil-btn" disabled>Beklemede</button>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>