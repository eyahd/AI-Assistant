<?php
header('Content-Type: application/json');

// ✅ DEIN ECHTER API-KEY HIER
$api_key = "DEIN API-KEY HIER";

// ⬇️ Benutzer-Eingabe empfangen
$input = json_decode(file_get_contents("php://input"), true);
$user_message = $input['message'] ?? '';

$data = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        ["role" => "system", "content" => "Du bist ein KI-Tutor für Webentwicklung."],
        ["role" => "user", "content" => $user_message]
    ]
];

// ⬇️ Anfrage vorbereiten
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// ⬇️ Anfrage senden
$response = curl_exec($ch);

// ⛔ Netzwerkfehler?
if ($response === false) {
    echo json_encode(['reply' => '❌ cURL-Fehler: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ⬇️ Antwort parsen
$result = json_decode($response, true);

// ⛔ API-Fehler?
if (isset($result['error'])) {
    echo json_encode([
        'reply' => '❌ API-Fehler: ' . $result['error']['message']
    ]);
    exit;
}

// ⛔ HTTP-Fehler?
if ($http_code !== 200) {
    echo json_encode(['reply' => '❌ HTTP-Fehler: ' . $http_code]);
    exit;
}

// ✅ Erfolgreiche Antwort
$reply = $result['choices'][0]['message']['content'] ?? '❌ Leere Antwort.';
echo json_encode(['reply' => $reply]);
?>
