<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/telegram_config.php';

$key = $_GET['key'] ?? '';
if ($key !== TG_TEST_KEY) {
    echo json_encode([
        'ok' => false,
        'error' => 'Clave incorrecta. Usa telegram_get_updates.php?key=' . TG_TEST_KEY
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (TG_BOT_TOKEN === 'PEGA_AQUI_EL_TOKEN_DE_BOTFATHER') {
    echo json_encode(['ok' => false, 'error' => 'Primero configura TG_BOT_TOKEN en telegram_config.php'], JSON_UNESCAPED_UNICODE);
    exit;
}

$url = 'https://api.telegram.org/bot' . TG_BOT_TOKEN . '/getUpdates';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20
]);
$response = curl_exec($ch);
$error = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$decoded = json_decode($response, true);
echo json_encode([
    'ok' => $code >= 200 && $code < 300,
    'http_code' => $code,
    'curl_error' => $error,
    'telegram_response' => $decoded ?: $response,
    'ayuda' => 'Busca message.chat.id. Ese valor va en TG_CHAT_IDS dentro de telegram_config.php.'
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
