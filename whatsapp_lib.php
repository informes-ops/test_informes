<?php
/* ============================================================
   whatsapp_lib.php - ZGROUP
   Notificaciones WhatsApp Cloud API para:
   1) Inspección preliminar registrada
   2) Nuevo informe técnico final con botón PDF

   Plantillas esperadas en Meta:
   - inspeccion_preliminar_registrada
     Body: {{1}} técnico, {{2}} cliente, {{3}} cotización,
           {{4}} trabajo previsto, {{5}} estado inicial,
           {{6}} temperaturas, {{7}} fecha preliminar,
           {{8}} ubicación
     Botón: Ver panel, URL estática

   - nuevo_informe_tecnico_acciones
     Body: {{1}} técnico, {{2}} cliente, {{3}} cotización,
           {{4}} trabajo, {{5}} fecha, {{6}} ubicación
     Botones: Ver panel estático y Abrir PDF dinámico
   ============================================================ */

require_once __DIR__ . '/whatsapp_config.php';

if (!defined('WA_TEMPLATE_NUEVO_INFORME')) {
    define('WA_TEMPLATE_NUEVO_INFORME', 'nuevo_informe_tecnico_acciones');
}

if (!defined('WA_TEMPLATE_PRELIMINAR')) {
    define('WA_TEMPLATE_PRELIMINAR', 'inspeccion_preliminar');
}

if (!defined('WA_TEMPLATE_LANG')) {
    define('WA_TEMPLATE_LANG', 'es_PE');
}

if (!defined('WA_PANEL_URL')) {
    define('WA_PANEL_URL', 'https://zgroupinformes.com/panel.php');
}

if (!defined('WA_PDF_BASE_URL')) {
    define('WA_PDF_BASE_URL', 'https://zgroupinformes.com/informes/');
}

if (!defined('WA_NOTIFICACIONES_ACTIVAS')) {
    define('WA_NOTIFICACIONES_ACTIVAS', true);
}

function obtenerNumerosSupervisoresWA() {
    if (!defined('WA_SUPERVISORES') || !is_array(WA_SUPERVISORES)) {
        return [];
    }

    // Evita enviar 2 veces al mismo número si está repetido en whatsapp_config.php.
    $numeros = [];
    foreach (WA_SUPERVISORES as $numero) {
        $numeroLimpio = limpiarNumeroWA($numero);
        if ($numeroLimpio !== '') {
            $numeros[$numeroLimpio] = true;
        }
    }
    return array_keys($numeros);
}

function whatsappActivoResultado($plantilla = '') {
    if (!WA_NOTIFICACIONES_ACTIVAS) {
        return [
            'ok' => false,
            'skipped' => true,
            'message' => 'WhatsApp está desactivado en whatsapp_config.php.',
            'plantilla' => $plantilla
        ];
    }

    if (!defined('WA_ACCESS_TOKEN') || trim((string)WA_ACCESS_TOKEN) === '' || WA_ACCESS_TOKEN === 'AQUI_PEGA_TU_TOKEN_NUEVO') {
        return [
            'ok' => false,
            'skipped' => true,
            'message' => 'Falta configurar WA_ACCESS_TOKEN en whatsapp_config.php.',
            'plantilla' => $plantilla
        ];
    }

    if (!defined('WA_PHONE_NUMBER_ID') || trim((string)WA_PHONE_NUMBER_ID) === '') {
        return [
            'ok' => false,
            'skipped' => true,
            'message' => 'Falta configurar WA_PHONE_NUMBER_ID en whatsapp_config.php.',
            'plantilla' => $plantilla
        ];
    }

    return null;
}

function enviarWhatsAppPreliminar($tecnico, $cliente, $cotizacion, $trabajoPrevisto, $estadoInicial, $temperaturas, $fechaPreliminar, $ubicacion) {
    $omitido = whatsappActivoResultado(WA_TEMPLATE_PRELIMINAR);
    if ($omitido !== null) return $omitido;

    $numeros = obtenerNumerosSupervisoresWA();
    if (empty($numeros)) {
        return [
            'ok' => false,
            'error' => 'No hay números en WA_SUPERVISORES dentro de whatsapp_config.php.',
            'plantilla' => WA_TEMPLATE_PRELIMINAR
        ];
    }

    $resultados = [];
    foreach ($numeros as $numero) {
        $resultados[] = enviarPlantillaPreliminarRegistrada(
            $numero,
            $tecnico,
            $cliente,
            $cotizacion,
            $trabajoPrevisto,
            $estadoInicial,
            $temperaturas,
            $fechaPreliminar,
            $ubicacion
        );
    }

    return [
        'ok' => count(array_filter($resultados, function($r) { return !empty($r['ok']); })) > 0,
        'plantilla' => WA_TEMPLATE_PRELIMINAR,
        'idioma' => WA_TEMPLATE_LANG,
        'enviados_a' => $numeros,
        'resultados' => $resultados
    ];
}

function enviarWhatsAppNuevoInforme($tecnico, $cliente, $cotizacion, $trabajo, $fecha, $archivoPdf = '', $direccion = '') {
    $omitido = whatsappActivoResultado(WA_TEMPLATE_NUEVO_INFORME);
    if ($omitido !== null) return $omitido;

    $numeros = obtenerNumerosSupervisoresWA();
    if (empty($numeros)) {
        return [
            'ok' => false,
            'error' => 'No hay números en WA_SUPERVISORES dentro de whatsapp_config.php.',
            'plantilla' => WA_TEMPLATE_NUEVO_INFORME
        ];
    }

    $resultados = [];
    foreach ($numeros as $numero) {
        $resultados[] = enviarPlantillaNuevoInformeAcciones(
            $numero,
            $tecnico,
            $cliente,
            $cotizacion,
            $trabajo,
            $fecha,
            $archivoPdf,
            $direccion
        );
    }

    return [
        'ok' => count(array_filter($resultados, function($r) { return !empty($r['ok']); })) > 0,
        'plantilla' => WA_TEMPLATE_NUEVO_INFORME,
        'idioma' => WA_TEMPLATE_LANG,
        'enviados_a' => $numeros,
        'resultados' => $resultados
    ];
}

function enviarPlantillaPreliminarRegistrada($numero, $tecnico, $cliente, $cotizacion, $trabajoPrevisto, $estadoInicial, $temperaturas, $fechaPreliminar, $ubicacion) {
    $components = [
        [
            'type' => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => limpiarTextoWA($tecnico)],
                ['type' => 'text', 'text' => limpiarTextoWA($cliente)],
                ['type' => 'text', 'text' => limpiarTextoWA($cotizacion)],
                ['type' => 'text', 'text' => limpiarTextoWA($trabajoPrevisto)],
                ['type' => 'text', 'text' => limpiarTextoWA($estadoInicial)],
                ['type' => 'text', 'text' => limpiarTextoWA($temperaturas)],
                ['type' => 'text', 'text' => limpiarTextoWA($fechaPreliminar)],
                ['type' => 'text', 'text' => limpiarTextoWA($ubicacion)],
            ]
        ]
    ];

    return enviarTemplateWA($numero, WA_TEMPLATE_PRELIMINAR, $components);
}

function enviarPlantillaNuevoInformeAcciones($numero, $tecnico, $cliente, $cotizacion, $trabajo, $fecha, $archivoPdf, $direccion) {
    $archivoPdf = normalizarNombrePdfWA($archivoPdf);

    if ($archivoPdf === '') {
        return [
            'ok' => false,
            'error' => 'Falta el archivo PDF. Esta plantilla necesita el nombre del PDF para el botón Abrir PDF.'
        ];
    }

    $components = [
        [
            'type' => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => limpiarTextoWA($tecnico)],
                ['type' => 'text', 'text' => limpiarTextoWA($cliente)],
                ['type' => 'text', 'text' => limpiarTextoWA($cotizacion)],
                ['type' => 'text', 'text' => limpiarTextoWA($trabajo)],
                ['type' => 'text', 'text' => limpiarTextoWA($fecha)],
                ['type' => 'text', 'text' => limpiarTextoWA($direccion)],
            ]
        ],
        [
            'type' => 'button',
            'sub_type' => 'url',
            'index' => '1',
            'parameters' => [
                // IMPORTANTE: aquí va SOLO el nombre del PDF.
                // No debe ir {{1}}, ni URL completa, ni /informes/.
                ['type' => 'text', 'text' => $archivoPdf]
            ]
        ]
    ];

    return enviarTemplateWA($numero, WA_TEMPLATE_NUEVO_INFORME, $components);
}

function normalizarNombrePdfWA($archivoPdf) {
    $archivoPdf = trim((string)$archivoPdf);
    if ($archivoPdf === '') return '';

    // Corrige casos donde por error llegó el marcador de Meta como texto real.
    $archivoPdf = urldecode($archivoPdf);
    $archivoPdf = str_replace(['{{1}}', '{{ 1 }}', '%7B%7B1%7D%7D', '%7b%7b1%7d%7d'], '', $archivoPdf);

    // Si llega una URL completa, nos quedamos solo con el nombre del archivo.
    if (filter_var($archivoPdf, FILTER_VALIDATE_URL)) {
        $path = parse_url($archivoPdf, PHP_URL_PATH);
        $archivoPdf = basename((string)$path);
    }

    // Si llega con carpeta /informes/, también nos quedamos solo con el archivo.
    $archivoPdf = basename($archivoPdf);

    // Seguridad: solo permitimos nombres normales de PDF generados por el sistema.
    $archivoPdf = preg_replace('/[^A-Za-z0-9._-]/', '', $archivoPdf);
    if (!preg_match('/\.pdf$/i', $archivoPdf)) return '';

    return $archivoPdf;
}

function enviarTemplateWA($numero, $nombrePlantilla, array $components) {
    $url = 'https://graph.facebook.com/v25.0/' . WA_PHONE_NUMBER_ID . '/messages';

    $data = [
        'messaging_product' => 'whatsapp',
        'to' => limpiarNumeroWA($numero),
        'type' => 'template',
        'template' => [
            'name' => $nombrePlantilla,
            'language' => [
                'code' => WA_TEMPLATE_LANG
            ],
            'components' => $components
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . WA_ACCESS_TOKEN,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);

    $resultado = [
        'ok' => $code >= 200 && $code < 300,
        'template' => $nombrePlantilla,
        'to' => limpiarNumeroWA($numero),
        'http_code' => $code,
        'response' => $decoded ?: $response,
        'curl_error' => $error
    ];

    @file_put_contents(
        __DIR__ . '/whatsapp_debug.log',
        '[' . date('Y-m-d H:i:s') . '] Envio ' . $nombrePlantilla . ': ' . json_encode($resultado, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );

    return $resultado;
}

function limpiarTextoWA($txt) {
    $txt = trim((string)$txt);
    if ($txt === '') return '-';
    $txt = preg_replace('/\s+/', ' ', $txt);
    if (function_exists('mb_substr')) {
        return mb_substr($txt, 0, 250, 'UTF-8');
    }
    return substr($txt, 0, 250);
}

function limpiarNumeroWA($numero) {
    return preg_replace('/\D+/', '', (string)$numero);
}
