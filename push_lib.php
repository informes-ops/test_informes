<?php
if (file_exists(__DIR__ . '/push_config.php')) {
    require_once __DIR__ . '/push_config.php';
} else {
    if (!defined('ZGROUP_ONESIGNAL_APP_ID')) define('ZGROUP_ONESIGNAL_APP_ID', '');
    if (!defined('ZGROUP_ONESIGNAL_REST_API_KEY')) define('ZGROUP_ONESIGNAL_REST_API_KEY', '');
    if (!defined('ZGROUP_ONESIGNAL_AUTH_PREFIX')) define('ZGROUP_ONESIGNAL_AUTH_PREFIX', 'Key');
    if (!defined('ZGROUP_PUSH_TRIGGER_TOKEN')) define('ZGROUP_PUSH_TRIGGER_TOKEN', '');
}

function zgroup_push_config_ok() {
    return defined('ZGROUP_ONESIGNAL_APP_ID') && defined('ZGROUP_ONESIGNAL_REST_API_KEY')
        && ZGROUP_ONESIGNAL_APP_ID !== '' && ZGROUP_ONESIGNAL_REST_API_KEY !== ''
        && strpos(ZGROUP_ONESIGNAL_APP_ID, 'PEGA_AQUI') === false
        && strpos(ZGROUP_ONESIGNAL_REST_API_KEY, 'PEGA_AQUI') === false;
}

function zgroup_url_absoluta($path = 'panel.php') {
    $path = trim((string)$path);
    if ($path === '') $path = 'panel.php';
    if (preg_match('~^https?://~i', $path)) return $path;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    if ($base === '/' || $base === '\\') $base = '';
    return $host ? ($scheme . '://' . $host . $base . '/' . ltrim($path, '/')) : $path;
}

function zgroup_enviar_push($titulo, $detalle = '', $url = 'panel.php', $data = []) {
    if (!zgroup_push_config_ok()) {
        return ['ok' => false, 'error' => 'Falta configurar OneSignal en push_config.php'];
    }

    $titulo = trim((string)$titulo);
    $detalle = trim((string)$detalle);
    if ($titulo === '') $titulo = 'Nueva novedad técnica';
    if ($detalle === '') $detalle = 'Revisa el panel de informes técnicos.';

    $payload = [
        'app_id' => ZGROUP_ONESIGNAL_APP_ID,
        'target_channel' => 'push',
        'included_segments' => ['Subscribed Users'],
        'headings' => ['es' => $titulo, 'en' => $titulo],
        'contents' => ['es' => $detalle, 'en' => $detalle],
        'url' => zgroup_url_absoluta($url),
        'data' => is_array($data) ? $data : [],
        'web_push_topic' => 'zgroup_area_tecnica',
        'chrome_web_icon' => zgroup_url_absoluta('zgroup-logo.png'),
        'chrome_web_badge' => zgroup_url_absoluta('zgroup-logo.png'),
    ];

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $endpoint = 'https://api.onesignal.com/notifications';
    $authPrefix = defined('ZGROUP_ONESIGNAL_AUTH_PREFIX') ? ZGROUP_ONESIGNAL_AUTH_PREFIX : 'Key';
    $headers = [
        'Content-Type: application/json; charset=utf-8',
        'Accept: application/json',
        'Authorization: ' . $authPrefix . ' ' . ZGROUP_ONESIGNAL_REST_API_KEY,
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
        ]);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false) return ['ok' => false, 'error' => $err ?: 'Error cURL'];
        $decoded = json_decode($body, true);
        return ['ok' => ($code >= 200 && $code < 300), 'status' => $code, 'response' => $decoded ?: $body];
    }

    $opts = ['http' => [
        'method' => 'POST',
        'header' => implode("\r\n", $headers),
        'content' => $json,
        'timeout' => 12,
        'ignore_errors' => true,
    ]];
    $body = @file_get_contents($endpoint, false, stream_context_create($opts));
    $decoded = json_decode((string)$body, true);
    return ['ok' => $body !== false, 'response' => $decoded ?: $body];
}
