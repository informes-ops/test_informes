<?php
/* ============================================================
   guardar.php CORREGIDO + Telegram con debug seguro
   - Guarda el PDF en /informes
   - Registra el informe en MySQL
   - Envía Telegram sin romper el guardado si falla
   - Siempre responde JSON válido al index.php
   ============================================================ */

ob_start();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Lima');

function limpiarSalidaAntesDeJSON() {
    $salida = trim(ob_get_contents() ?: '');
    if ($salida !== '') {
        @file_put_contents(
            __DIR__ . '/php_output_debug.log',
            '[' . date('Y-m-d H:i:s') . '] Salida inesperada antes del JSON: ' . $salida . PHP_EOL,
            FILE_APPEND
        );
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
}

function responderJSON($data) {
    limpiarSalidaAntesDeJSON();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail($msg) {
    responderJSON(['ok' => false, 'error' => $msg]);
}

try {
    require __DIR__ . '/db.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        fail('No se encontró la conexión PDO en db.php.');
    }
} catch (Throwable $e) {
    fail('Error cargando db.php: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    fail('Método no permitido.');
}

$tecnico_id = isset($_POST['tecnico_id']) ? (int) $_POST['tecnico_id'] : 0;
if ($tecnico_id <= 0) fail('Debes elegir un técnico.');

if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    fail('No se recibió el PDF. Si el archivo es grande, revisa el tamaño del archivo.');
}

$tmp = $_FILES['pdf']['tmp_name'];
if (!is_uploaded_file($tmp)) fail('El archivo recibido no es una subida válida.');

if (function_exists('mime_content_type')) {
    $mime = mime_content_type($tmp);
    if ($mime !== 'application/pdf' && $mime !== 'application/octet-stream') {
        fail('El archivo recibido no parece ser un PDF válido. MIME: ' . $mime);
    }
}

$orden     = trim($_POST['orden']     ?? '');
$cliente   = trim($_POST['cliente']   ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$trabajos  = trim($_POST['trabajos']  ?? '');
$fecha     = trim($_POST['fecha']     ?? '');

if ($orden === '') fail('Falta ingresar el N° de cotización.');
if (!preg_match('/^\d+$/', $orden)) fail('El N° de cotización debe contener solo números.');
if ($cliente === '') fail('Falta ingresar el cliente.');
if ($direccion === '') fail('Falta elegir la dirección/ubicación.');
if ($trabajos === '') fail('Falta seleccionar el trabajo realizado.');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) $fecha = date('Y-m-d');

$tecnico_nombre = 'Técnico';
try {
    $stmtTec = $pdo->prepare('SELECT nombre FROM tecnicos WHERE id = ? LIMIT 1');
    $stmtTec->execute([$tecnico_id]);
    $tecRow = $stmtTec->fetch(PDO::FETCH_ASSOC);
    if ($tecRow && !empty($tecRow['nombre'])) $tecnico_nombre = $tecRow['nombre'];
} catch (Throwable $e) {
    @file_put_contents(__DIR__ . '/telegram_debug.log', '[' . date('Y-m-d H:i:s') . '] No se pudo obtener técnico: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

$dir = __DIR__ . '/informes';
if (!is_dir($dir) && !mkdir($dir, 0775, true)) fail('No se pudo crear la carpeta "informes".');

$baseName = preg_replace('/[^A-Za-z0-9_-]/', '_', $orden !== '' ? $orden : 'sin-cotizacion');
$filename = 'informe_' . $baseName . '_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 6) . '.pdf';
$dest = $dir . '/' . $filename;

if (!move_uploaded_file($tmp, $dest)) fail('No se pudo guardar el archivo en el servidor.');

try {
    $creado = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare(
        'INSERT INTO informes (tecnico_id, orden, cliente, direccion, fecha, trabajos, archivo, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$tecnico_id, $orden, $cliente, $direccion, $fecha, $trabajos, $filename, $creado]);
} catch (PDOException $e) {
    @unlink($dest);
    fail('Error al guardar en la base de datos: ' . $e->getMessage());
}

$tg_result = ['ok' => false, 'skipped' => true, 'message' => 'Telegram no ejecutado.'];
try {
    $telegramLib = __DIR__ . '/telegram_lib.php';
    if (!file_exists($telegramLib)) {
        $tg_result = ['ok' => false, 'error' => 'No existe telegram_lib.php en la carpeta del sistema.'];
    } else {
        require_once $telegramLib;
        if (!function_exists('enviarTelegramNuevoInforme')) {
            $tg_result = ['ok' => false, 'error' => 'No existe la función enviarTelegramNuevoInforme().'];
        } else {
            $tg_result = enviarTelegramNuevoInforme(
                $tecnico_nombre,
                $cliente,
                $orden,
                $trabajos,
                date('d/m/Y H:i:s'),
                $filename,
                $direccion
            );
        }
    }
} catch (Throwable $e) {
    $tg_result = ['ok' => false, 'error' => $e->getMessage()];
}

@file_put_contents(
    __DIR__ . '/telegram_debug.log',
    '[' . date('Y-m-d H:i:s') . '] Informe: ' . $filename . ' | Telegram: ' . json_encode($tg_result, JSON_UNESCAPED_UNICODE) . PHP_EOL,
    FILE_APPEND
);

// --- Enviar WhatsApp sin romper el guardado ---
$wa_result = ['ok' => false, 'skipped' => true, 'message' => 'WhatsApp no ejecutado.'];
try {
    $whatsappLib = __DIR__ . '/whatsapp_lib.php';
    if (!file_exists($whatsappLib)) {
        $wa_result = ['ok' => false, 'error' => 'No existe whatsapp_lib.php en la carpeta del sistema.'];
    } else {
        require_once $whatsappLib;
        if (!function_exists('enviarWhatsAppNuevoInforme')) {
            $wa_result = ['ok' => false, 'error' => 'No existe la función enviarWhatsAppNuevoInforme().'];
        } else {
            $wa_result = enviarWhatsAppNuevoInforme(
                $tecnico_nombre,
                $cliente,
                $orden,
                $trabajos,
                date('d/m/Y H:i:s')
            );
        }
    }
} catch (Throwable $e) {
    $wa_result = ['ok' => false, 'error' => $e->getMessage()];
}

@file_put_contents(
    __DIR__ . '/whatsapp_debug.log',
    '[' . date('Y-m-d H:i:s') . '] Informe: ' . $filename . ' | WhatsApp: ' . json_encode($wa_result, JSON_UNESCAPED_UNICODE) . PHP_EOL,
    FILE_APPEND
);

responderJSON([
    'ok' => true,
    'archivo' => $filename,
    'tecnico' => $tecnico_nombre,
    'telegram' => $tg_result,
    'whatsapp' => $wa_result
]);
