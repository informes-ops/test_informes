<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/telegram_lib.php';

$key = $_GET['key'] ?? '';
if ($key !== TG_TEST_KEY) {
    echo json_encode([
        'ok' => false,
        'error' => 'Clave incorrecta. Usa test_telegram.php?key=' . TG_TEST_KEY
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$res = enviarTelegramNuevoInforme(
    'Carlos Ruiz',
    'Nestlé Perú SAC',
    '10020261054',
    'ASISTENCIA TECNICA',
    date('d/m/Y H:i:s'),
    '',
    'Ubicación de prueba'
);

echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
