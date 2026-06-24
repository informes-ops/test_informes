<?php
require_once __DIR__ . '/telegram_config.php';

function tg_log($texto) {
    @file_put_contents(
        __DIR__ . '/telegram_debug.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $texto . PHP_EOL,
        FILE_APPEND
    );
}

function tg_html($texto) {
    $texto = trim((string)$texto);
    if ($texto === '') return '-';
    return htmlspecialchars(mb_substr($texto, 0, 900), ENT_QUOTES, 'UTF-8');
}

function tg_url($ruta = '') {
    $base = rtrim(TG_SITE_URL, '/');
    $ruta = ltrim((string)$ruta, '/');
    return $ruta === '' ? $base : $base . '/' . $ruta;
}

function enviarTelegramNuevoInforme($tecnico, $cliente, $cotizacion, $trabajo, $fecha, $archivo = '', $direccion = '') {
    if (!defined('TG_NOTIFICACIONES_ACTIVAS') || !TG_NOTIFICACIONES_ACTIVAS) {
        return ['ok' => false, 'skipped' => true, 'message' => 'Notificaciones Telegram desactivadas.'];
    }

    if (!defined('TG_BRIDGE_URL') || TG_BRIDGE_URL === '' || strpos(TG_BRIDGE_URL, 'PEGA_AQUI') !== false) {
        return ['ok' => false, 'error' => 'Falta configurar TG_BRIDGE_URL en telegram_config.php'];
    }

    if (!defined('TG_BRIDGE_KEY') || TG_BRIDGE_KEY === '') {
        return ['ok' => false, 'error' => 'Falta configurar TG_BRIDGE_KEY en telegram_config.php'];
    }

    if (!defined('TG_CHAT_IDS') || !is_array(TG_CHAT_IDS) || count(TG_CHAT_IDS) === 0 || strpos((string)TG_CHAT_IDS[0], 'PEGA_AQUI') !== false) {
        return ['ok' => false, 'error' => 'Falta configurar TG_CHAT_IDS en telegram_config.php'];
    }

    $panelUrl = tg_url('panel.php');
    $pdfUrl = $archivo !== '' ? tg_url('informes/' . rawurlencode($archivo)) : '';

    $msg  = "🚨 <b>Nuevo informe técnico registrado</b>\n\n";
    $msg .= "👷 <b>Técnico:</b> " . tg_html($tecnico) . "\n";
    $msg .= "🏢 <b>Cliente:</b> " . tg_html($cliente) . "\n";
    $msg .= "🧾 <b>Cotización:</b> " . tg_html($cotizacion) . "\n";
    $msg .= "🛠 <b>Trabajo:</b> " . tg_html($trabajo) . "\n";
    $msg .= "📅 <b>Fecha:</b> " . tg_html($fecha) . "\n";

    if (trim((string)$direccion) !== '') {
        $msg .= "📍 <b>Ubicación:</b> " . tg_html($direccion) . "\n";
    }

    $msg .= "\n🔎 <a href=\"" . htmlspecialchars($panelUrl, ENT_QUOTES, 'UTF-8') . "\">Ver panel de informes</a>";

    if ($pdfUrl !== '') {
        $msg .= "\n📄 <a href=\"" . htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8') . "\">Abrir PDF generado</a>";
    }

    return enviarTelegramViaBridge($msg);
}

function enviarTelegramViaBridge($mensaje) {
    $payload = [
        'key' => TG_BRIDGE_KEY,
        'chat_ids' => array_values(TG_CHAT_IDS),
        'text' => $mensaje,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];

    $res = tg_http_json_post(TG_BRIDGE_URL, $payload);
    tg_log('Bridge response: ' . json_encode($res, JSON_UNESCAPED_UNICODE));
    return $res;
}

function tg_http_json_post($url, $payload) {
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode((string)$response, true);
        return [
            'ok' => $code >= 200 && $code < 300 && !empty($decoded['ok']),
            'http_code' => $code,
            'response' => $decoded ?: $response,
            'curl_error' => $error
        ];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $json,
            'timeout' => 25
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    $decoded = json_decode((string)$response, true);

    return [
        'ok' => !empty($decoded['ok']),
        'http_code' => 0,
        'response' => $decoded ?: $response,
        'curl_error' => $response === false ? 'file_get_contents falló' : ''
    ];
}
