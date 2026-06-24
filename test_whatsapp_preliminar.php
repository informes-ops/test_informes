<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Lima');

try {
    require_once __DIR__ . '/whatsapp_lib.php';

    if (!function_exists('enviarWhatsAppPreliminar')) {
        throw new Exception('No existe enviarWhatsAppPreliminar() en whatsapp_lib.php');
    }

    $resultado = enviarWhatsAppPreliminar(
        'Carlos Ruiz',
        'Nestlé Perú SAC',
        '10020261054',
        'REPARACIÓN MÁQUINA REEFER',
        'Equipo con alarma / ruido anormal',
        'Amb: 24°C | Ret: 5°C | Sum: -2°C | Set: -18°C',
        date('d/m/Y H:i:s'),
        'Oquendo, Callao, Lima Metropolitana, Perú'
    );

    echo json_encode([
        'ok' => !empty($resultado['ok']),
        'resultado' => $resultado
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}