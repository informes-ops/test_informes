<?php
/* Prueba de plantilla WhatsApp: nuevo_informe_tecnico */
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Lima');

try {
    require_once __DIR__ . '/whatsapp_lib.php';

    if (!function_exists('enviarWhatsAppNuevoInforme')) {
        throw new Exception('No existe la función enviarWhatsAppNuevoInforme() en whatsapp_lib.php');
    }

    $resultado = enviarWhatsAppNuevoInforme(
        'Carlos Ruiz',
        'Nestlé Perú SAC',
        '10020261054',
        'INGRESO EQUIPO NEW GENSET',
        date('d/m/Y H:i:s')
    );

    echo json_encode([
        'ok' => !empty($resultado['ok']),
        'plantilla' => 'nuevo_informe_tecnico',
        'idioma' => 'es_PE',
        'resultado' => $resultado
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
