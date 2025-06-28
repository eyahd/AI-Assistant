<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");


$valid_languages = ['html', 'css', 'javascript'];
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['code'], $data['language'], $data['secret'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields."]);
    exit;
}

if ($data['secret'] !== $secret_key) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid secret key."]);
    exit;
}

$code = trim($data['code']);
$language = strtolower(trim($data['language']));

if (!in_array($language, $valid_languages)) {
    http_response_code(400);
    echo json_encode(["error" => "Unsupported language."]);
    exit;
}

$prompt = "Fix the following $language code and explain what was wrong:\n\n" . $code;

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $openai_key"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-4",
    "messages" => [
        ["role" => "system", "content" => "You are a code fixer. Output only the corrected code and a list of improvements."],
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.3
]));

$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to get response from OpenAI."]);
    exit;
}

$result = json_decode($response, true);
$fullResponse = $result['choices'][0]['message']['content'];

$split = explode("```", $fullResponse);

$fixedCode = trim($split[1] ?? $fullResponse); // Best-effort to extract code
$suggestions = explode("\n", $split[2] ?? "Please check the improvements listed in the response.");

echo json_encode([
    "fixedCode" => $fixedCode,
    "suggestions" => $suggestions
]);
