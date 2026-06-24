<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/whatsapp_lib.php';

$resultado = enviarWhatsAppNuevoInforme(
    'Carlos Ruiz',
    'Nestlé Perú SAC',
    '10020261054',
    'INGRESO EQUIPO NEW GENSET',
    date('d/m/Y H:i:s'),
    'informe_10020261054_20260604_071403_demo.pdf',
    'Oquendo, Callao, Lima Metropolitana, Callao, 15112, Perú'
);

echo json_encode([
    'ok' => !empty($resultado['ok']),
    'plantilla' => WA_TEMPLATE_NUEVO_INFORME,
    'idioma' => WA_TEMPLATE_LANG,
    'resultado' => $resultado
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
