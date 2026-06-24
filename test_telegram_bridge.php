<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/telegram_config.php';

if (!isset($_GET['key']) || $_GET['key'] !== TG_TEST_KEY) {
    echo json_encode(['ok' => false, 'error' => 'Clave incorrecta.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require __DIR__ . '/telegram_lib.php';

$res = enviarTelegramNuevoInforme(
    'Carlos Ruiz',
    'Nestlé Perú SAC',
    '10020261054',
    'ASISTENCIA TECNICA',
    date('d/m/Y H:i:s'),
    '',
    'Prueba de ubicación'
);

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
