<?php
// Bu dosyada WhatsApp ile ilgili yardımcı fonksiyonlar yer alacaktır.

// --- SABİTLER ---
// Bu bilgileri Meta Geliştirici Panelinizdeki "WhatsApp > API Kurulumu" sayfasından alın.
define('WHATSAPP_ACCESS_TOKEN', 'EAAK9lIvRrVkBPbBZA9BlH3Pyj8Wi6NDPctpWSyTzIIEoeT0Dbp6BQTLfkACb2ZBtG6M3yQpyB1VVcWWE2u7Hp7xVg2KdlVREPelNAx58i47mCSaRiw2GAJrFcGRhzWsFGeWedV7XZCjSDtWifNZCf7iQ5zI0SOTPxR3iBuRuXrkr2zXoO0sLJmMZAxTho3xBCRQ4zqZBVNY0nAvJbgwHtaZAup9fAft4NjWWZBLY26y1dwZDZD');
define('WHATSAPP_PHONE_NUMBER_ID', '733816373151288'); // Test numaranızın veya kendi numaranızın ID'si
define('WHATSAPP_API_VERSION', 'v23.0'); // En güncel versiyonu kullanın

/**
 * WhatsApp'a önceden onaylanmış bir şablon mesajı gönderir.
 * @param string $to Gönderilecek telefon numarası (Örn: 905xxxxxxxxx)
 * @param string $templateName Meta'da onaylanmış şablonun adı
 * @param array $parameters Şablondaki {{1}}, {{2}} gibi alanlara gelecek değerlerin dizisi
 * @param string $language Dil kodu (Örn: 'tr' veya 'en_US')
 * @return object|null API'den dönen cevabın JSON objesi veya hata durumunda null
 */
function sendWhatsAppTemplateMessage($to, $templateName, $parameters = [], $language = 'tr') {
    $url = "https://graph.facebook.com/" . WHATSAPP_API_VERSION . "/" . WHATSAPP_PHONE_NUMBER_ID . "/messages";

    $components = [];
    if (!empty($parameters)) {
        $param_objects = [];
        foreach ($parameters as $p) {
            $param_objects[] = ["type" => "text", "text" => $p];
        }
        $components[] = [
            "type" => "body",
            "parameters" => $param_objects
        ];
    }

    $data = [
        "messaging_product" => "whatsapp",
        "to" => $to,
        "type" => "template",
        "template" => [
            "name" => $templateName,
            "language" => ["code" => $language],
            "components" => $components
        ]
    ];

    $jsonData = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . WHATSAPP_ACCESS_TOKEN,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        // Hata durumunda loglama veya hata yönetimi yapılabilir
        return (object)['error' => 'cURL Error #:' . $err];
    } else {
        return json_decode($response);
    }
}

/**
 * WhatsApp'a serbest metinli bir mesaj gönderir. (Sadece 24 saatlik pencere içinde çalışır)
 * @param string $to Gönderilecek telefon numarası (Örn: 905xxxxxxxxx)
 * @param string $message Gönderilecek metin mesajı
 * @return object|null API'den dönen cevabın JSON objesi veya hata durumunda null
 */
function sendWhatsAppTextMessage($to, $message) {
    $url = "https://graph.facebook.com/" . WHATSAPP_API_VERSION . "/" . WHATSAPP_PHONE_NUMBER_ID . "/messages";

    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $to,
        "type" => "text",
        "text" => [
            "preview_url" => false,
            "body" => $message
        ]
    ];

    $jsonData = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . WHATSAPP_ACCESS_TOKEN,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return (object)['error' => 'cURL Error #:' . $err];
    } else {
        return json_decode($response);
    }
}
?>

