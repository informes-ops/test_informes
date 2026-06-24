<?php
/* ZGROUP V27: conserva la primera hora de inicio y fin del servicio */
/* ZGROUP BUILD: guardar.php V9 REAL 2026-06-20
   Guarda tipo de equipo y tamaño del contenedor junto con el informe.
   ============================================================ */
/* ============================================================
   guardar.php CORREGIDO
   - Guarda el PDF en /informes
   - Registra el informe final en MySQL
   - Cierra la inspección preliminar asociada si llega preinspeccion_id o token
   - Envía Telegram / WhatsApp sin romper el guardado si fallan
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

function columnaExiste(PDO $pdo, $tabla, $columna) {
    $stmt = $pdo->prepare("\n        SELECT COUNT(*)\n        FROM INFORMATION_SCHEMA.COLUMNS\n        WHERE TABLE_SCHEMA = DATABASE()\n          AND TABLE_NAME = ?\n          AND COLUMN_NAME = ?\n    ");
    $stmt->execute([$tabla, $columna]);
    return (int)$stmt->fetchColumn() > 0;
}

function agregarColumnaSiFalta(PDO $pdo, $tabla, $columna, $definicion) {
    if (!columnaExiste($pdo, $tabla, $columna)) {
        $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$columna` $definicion");
    }
}
function valorSnapshot(array $snapshot, string $id, string $default = ''): string {
    if (isset($snapshot['fields'][$id]['value'])) {
        return trim((string)$snapshot['fields'][$id]['value']);
    }
    return $default;
}

try {
    require __DIR__ . '/db.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        fail('No se encontró la conexión PDO en db.php.');
    }
} catch (Throwable $e) {
    fail('Error cargando db.php: ' . $e->getMessage());
}

try {
    require_once __DIR__ . '/odoo_lib.php';
} catch (Throwable $e) {
    @file_put_contents(__DIR__ . '/odoo_debug.log', '[' . date('Y-m-d H:i:s') . '] No se pudo cargar odoo_lib.php: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
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
$odoo_ticket_ref = preg_replace('/\D+/', '', trim((string)($_POST['odoo_ticket_ref'] ?? '')));
$cliente   = trim($_POST['cliente']   ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$trabajos  = trim($_POST['trabajos']  ?? '');
$fecha     = trim($_POST['fecha']     ?? '');
$datos_json = trim((string)($_POST['datos_json'] ?? ''));
$repuestos_manual = '';
$snapshot_guardado = [];
if ($datos_json !== '') {
    $tmpJson = json_decode($datos_json, true);
    if (!is_array($tmpJson)) {
        $datos_json = '';
    } else {
        $snapshot_guardado = $tmpJson;
        $repuestos_manual = trim((string)($tmpJson['fields']['repuestosManual']['value'] ?? ''));
        if ($repuestos_manual !== '') {
            if (!isset($tmpJson['fields']) || !is_array($tmpJson['fields'])) $tmpJson['fields'] = [];
            $tmpJson['fields']['requiereRepuesto'] = ['type'=>'hidden','value'=>'si','checked'=>false];
            $tmpJson['fields']['repuestosManual'] = ['type'=>'textarea','value'=>$repuestos_manual,'checked'=>false];
            $datos_json = json_encode($tmpJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }
}

$preinspeccion_id = isset($_POST['preinspeccion_id']) ? (int)$_POST['preinspeccion_id'] : 0;
if ($odoo_ticket_ref === '' && $preinspeccion_id > 0) {
    try {
        agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'odoo_ticket_ref', 'VARCHAR(120) DEFAULT NULL');
        $stOdooRef = $pdo->prepare('SELECT odoo_ticket_ref FROM inspecciones_preliminares WHERE id = ? LIMIT 1');
        $stOdooRef->execute([$preinspeccion_id]);
        $odoo_ticket_ref = preg_replace('/\D+/', '', (string)$stOdooRef->fetchColumn());
    } catch (Throwable $e) {}
}
if ($odoo_ticket_ref === '') $odoo_ticket_ref = $orden;
$token_continuacion = trim((string)($_POST['token_continuacion'] ?? ''));
$hora_inicio_post = trim((string)($_POST['hora_inicio_servicio'] ?? ''));
$hora_fin_post = trim((string)($_POST['hora_fin_servicio'] ?? ''));
function zgroupHoraSql($value) {
    $value = trim((string)$value);
    if ($value === '') return null;
    $value = str_replace('T', ' ', $value);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) $value .= ':00';
    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value) ? $value : null;
}
$tipo_equipo = trim((string)($_POST['tipo_equipo'] ?? ''));
$tamano_contenedor = trim((string)($_POST['tamano_contenedor'] ?? ''));
if ($tipo_equipo === '') $tipo_equipo = valorSnapshot($snapshot_guardado, 'zgTipoEquipo', '');
if ($tipo_equipo === '') $tipo_equipo = strtoupper(valorSnapshot($snapshot_guardado, 'marcaEquipo', '')) === 'GENSET' ? 'Genset' : 'Reefer';
if ($tamano_contenedor === '') $tamano_contenedor = valorSnapshot($snapshot_guardado, 'zgTamanoContenedor', '');
if (strcasecmp($tipo_equipo, 'Genset') === 0) $tamano_contenedor = 'No aplica';

if ($orden === '') fail('Falta ingresar el N° de reporte.');
if (!preg_match('/^\d+$/', $orden)) fail('El N° de reporte debe contener solo números.');
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

/* ============================================================
   Asegurar columnas para unir informe final con preliminar
   ============================================================ */
try {
    agregarColumnaSiFalta($pdo, 'informes', 'preinspeccion_id', 'INT DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'informes', 'datos_json', 'LONGTEXT DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'informes', 'repuestos_manual', 'LONGTEXT DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'informes', 'tipo_equipo', "VARCHAR(30) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'informes', 'tamano_contenedor', "VARCHAR(60) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'informes', 'actualizado_en', 'DATETIME DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'informes', 'hora_inicio_servicio', 'DATETIME DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'informes', 'hora_fin_servicio', 'DATETIME DEFAULT NULL');
    if (function_exists('zgOdooEnsureColumns')) zgOdooEnsureColumns($pdo);

    // Si existe la tabla de preliminares, asegurar sus columnas de cierre.
    $pdo->exec("CREATE TABLE IF NOT EXISTS inspecciones_preliminares (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tecnico_id INT NOT NULL,
        cliente VARCHAR(150) DEFAULT NULL,
        cotizacion VARCHAR(100) DEFAULT NULL,
        trabajo VARCHAR(150) DEFAULT NULL,
        modalidad_comercial VARCHAR(40) DEFAULT NULL,
        tipo_instalacion VARCHAR(80) DEFAULT NULL,
        tipo_equipo VARCHAR(30) DEFAULT NULL,
        tamano_contenedor VARCHAR(60) DEFAULT NULL,
        numero_equipo VARCHAR(100) DEFAULT NULL,
        serie_unidad VARCHAR(100) DEFAULT NULL,
        marca_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,
        refrigerante VARCHAR(50) DEFAULT NULL,
        set_point DECIMAL(6,2) DEFAULT NULL,
        temperatura_ambiente DECIMAL(6,2) DEFAULT NULL,
        retorno_aire DECIMAL(6,2) DEFAULT NULL,
        suministro_aire DECIMAL(6,2) DEFAULT NULL,
        presion_alta VARCHAR(50) DEFAULT NULL,
        presion_baja VARCHAR(50) DEFAULT NULL,
        alarma_encontrada VARCHAR(180) DEFAULT NULL,
        genset_horometro_inicial DECIMAL(12,1) DEFAULT NULL,
        genset_voltaje_bateria_inicial VARCHAR(50) DEFAULT NULL,
        genset_nivel_combustible_inicial VARCHAR(40) DEFAULT NULL,
        genset_nivel_aceite_inicial VARCHAR(50) DEFAULT NULL,
        genset_refrigerante_motor_inicial VARCHAR(60) DEFAULT NULL,
        genset_arranque_inicial VARCHAR(80) DEFAULT NULL,
        genset_frecuencia_inicial DECIMAL(8,2) DEFAULT NULL,
        genset_presion_aceite_inicial VARCHAR(50) DEFAULT NULL,
        voltaje_l1_l2 VARCHAR(50) DEFAULT NULL,
        voltaje_l2_l3 VARCHAR(50) DEFAULT NULL,
        voltaje_l1_l3 VARCHAR(50) DEFAULT NULL,
        estado_inicial VARCHAR(150) DEFAULT NULL,
        observacion_inicial TEXT DEFAULT NULL,
        ubicacion_texto TEXT DEFAULT NULL,
        latitud VARCHAR(50) DEFAULT NULL,
        longitud VARCHAR(50) DEFAULT NULL,
        creado_en DATETIME NOT NULL,
        hora_inicio_servicio DATETIME DEFAULT NULL,
        hora_fin_servicio DATETIME DEFAULT NULL,
        estado VARCHAR(30) NOT NULL DEFAULT 'abierto',
        token_continuacion VARCHAR(120) DEFAULT NULL,
        informe_id INT DEFAULT NULL,
        finalizado_en DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'modalidad_comercial', "VARCHAR(40) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tipo_instalacion', "VARCHAR(80) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tipo_equipo', "VARCHAR(30) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tamano_contenedor', "VARCHAR(60) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'presion_alta', "VARCHAR(50) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'presion_baja', "VARCHAR(50) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'alarma_encontrada', "VARCHAR(180) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_horometro_inicial', "DECIMAL(12,1) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_voltaje_bateria_inicial', "VARCHAR(50) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_nivel_combustible_inicial', "VARCHAR(40) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_nivel_aceite_inicial', "VARCHAR(50) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_refrigerante_motor_inicial', "VARCHAR(60) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_arranque_inicial', "VARCHAR(80) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_frecuencia_inicial', "DECIMAL(8,2) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_presion_aceite_inicial', "VARCHAR(50) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'estado', "VARCHAR(30) NOT NULL DEFAULT 'abierto'");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'token_continuacion', 'VARCHAR(120) DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'informe_id', 'INT DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'finalizado_en', 'DATETIME DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'hora_inicio_servicio', 'DATETIME DEFAULT NULL');
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'hora_fin_servicio', 'DATETIME DEFAULT NULL');
} catch (Throwable $e) {
    // No detenemos todavía el guardado del PDF por este problema, pero lo registramos.
    @file_put_contents(__DIR__ . '/preinspeccion_debug.log', '[' . date('Y-m-d H:i:s') . '] Error asegurando columnas: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

/* ============================================================
   Si llegó token pero no ID, buscar la preliminar correspondiente
   ============================================================ */
try {
    if ($preinspeccion_id <= 0 && $token_continuacion !== '') {
        $stmtPre = $pdo->prepare('SELECT id FROM inspecciones_preliminares WHERE token_continuacion = ? LIMIT 1');
        $stmtPre->execute([$token_continuacion]);
        $foundPre = (int)$stmtPre->fetchColumn();
        if ($foundPre > 0) $preinspeccion_id = $foundPre;
    }
} catch (Throwable $e) {
    @file_put_contents(__DIR__ . '/preinspeccion_debug.log', '[' . date('Y-m-d H:i:s') . '] Error buscando preliminar por token: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

$hora_inicio_servicio = zgroupHoraSql($hora_inicio_post);
$hora_fin_servicio = zgroupHoraSql($hora_fin_post);
try {
    if ($preinspeccion_id > 0) {
        $stTiempo = $pdo->prepare('SELECT hora_inicio_servicio, hora_fin_servicio, creado_en, finalizado_en FROM inspecciones_preliminares WHERE id = ? LIMIT 1');
        $stTiempo->execute([$preinspeccion_id]);
        $tiempoPre = $stTiempo->fetch(PDO::FETCH_ASSOC) ?: [];
        $hora_inicio_servicio = trim((string)($tiempoPre['hora_inicio_servicio'] ?? '')) ?: (trim((string)($tiempoPre['creado_en'] ?? '')) ?: $hora_inicio_servicio);
        $hora_fin_primera = trim((string)($tiempoPre['hora_fin_servicio'] ?? '')) ?: trim((string)($tiempoPre['finalizado_en'] ?? ''));
        if ($hora_fin_primera !== '') $hora_fin_servicio = $hora_fin_primera;
        $stPrimero = $pdo->prepare('SELECT hora_inicio_servicio, hora_fin_servicio FROM informes WHERE preinspeccion_id = ? ORDER BY id ASC LIMIT 1');
        $stPrimero->execute([$preinspeccion_id]);
        $primero = $stPrimero->fetch(PDO::FETCH_ASSOC) ?: [];
        if (trim((string)($primero['hora_inicio_servicio'] ?? '')) !== '') $hora_inicio_servicio = trim((string)$primero['hora_inicio_servicio']);
        if (trim((string)($primero['hora_fin_servicio'] ?? '')) !== '') $hora_fin_servicio = trim((string)$primero['hora_fin_servicio']);
    }
} catch (Throwable $e) {}
if (!$hora_inicio_servicio) $hora_inicio_servicio = date('Y-m-d H:i:s');
if (!$hora_fin_servicio) $hora_fin_servicio = date('Y-m-d H:i:s');
if ($datos_json !== '') {
    $snapTiempo = json_decode($datos_json, true);
    if (is_array($snapTiempo)) {
        if (!isset($snapTiempo['fields']) || !is_array($snapTiempo['fields'])) $snapTiempo['fields'] = [];
        $snapTiempo['fields']['horaInicioServicio'] = ['type'=>'datetime-local','value'=>str_replace(' ', 'T', substr($hora_inicio_servicio,0,16)),'checked'=>false];
        $snapTiempo['fields']['horaFinServicio'] = ['type'=>'datetime-local','value'=>str_replace(' ', 'T', substr($hora_fin_servicio,0,16)),'checked'=>false];
        $datos_json = json_encode($snapTiempo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

$dir = __DIR__ . '/informes';
if (!is_dir($dir) && !mkdir($dir, 0775, true)) fail('No se pudo crear la carpeta "informes".');

$baseName = preg_replace('/[^A-Za-z0-9_-]/', '_', $orden !== '' ? $orden : 'sin-cotizacion');
$filename = 'informe_' . $baseName . '_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 6) . '.pdf';
$dest = $dir . '/' . $filename;

if (!move_uploaded_file($tmp, $dest)) fail('No se pudo guardar el archivo en el servidor.');

$informe_id = 0;
$cierre_preliminar = ['ok' => false, 'skipped' => true, 'message' => 'No se recibió preinspeccion_id.'];

try {
    $creado = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare(
        'INSERT INTO informes (tecnico_id, orden, odoo_ticket_ref, cliente, direccion, fecha, trabajos, archivo, creado_en, preinspeccion_id, datos_json, repuestos_manual, tipo_equipo, tamano_contenedor, hora_inicio_servicio, hora_fin_servicio)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $tecnico_id,
        $orden,
        $odoo_ticket_ref,
        $cliente,
        $direccion,
        $fecha,
        $trabajos,
        $filename,
        $creado,
        $preinspeccion_id > 0 ? $preinspeccion_id : null,
        $datos_json !== '' ? $datos_json : null,
        $repuestos_manual !== '' ? $repuestos_manual : null,
        $tipo_equipo !== '' ? $tipo_equipo : null,
        $tamano_contenedor !== '' ? $tamano_contenedor : null,
        $hora_inicio_servicio,
        $hora_fin_servicio
    ]);
    $informe_id = (int)$pdo->lastInsertId();
} catch (PDOException $e) {
    @unlink($dest);
    fail('Error al guardar en la base de datos: ' . $e->getMessage());
}

/* ============================================================
   Enviar el PDF a Odoo sin poner en riesgo el guardado local
   ============================================================ */
$odoo_result = ['ok' => false, 'skipped' => true, 'estado' => 'no_ejecutado', 'error' => 'La integración de Odoo no se ejecutó.'];
try {
    if (function_exists('zgOdooSyncInforme')) {
        $odoo_result = zgOdooSyncInforme($pdo, $informe_id, $odoo_ticket_ref, $dest, $filename, 0);
    } else {
        $odoo_result = ['ok' => false, 'estado' => 'sin_libreria', 'error' => 'No se encontró odoo_lib.php.'];
    }
} catch (Throwable $e) {
    $odoo_result = ['ok' => false, 'estado' => 'error', 'error' => $e->getMessage()];
    @file_put_contents(__DIR__ . '/odoo_debug.log', '[' . date('Y-m-d H:i:s') . '] Informe ID ' . $informe_id . ': ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

/* ============================================================
   Cerrar preliminar después de registrar el informe final
   ============================================================ */
try {
    if ($preinspeccion_id > 0) {
        $stmtCerrar = $pdo->prepare("\n            UPDATE inspecciones_preliminares
            SET estado = 'cerrado',
                informe_id = COALESCE(informe_id, ?),
                hora_inicio_servicio = COALESCE(hora_inicio_servicio, ?),
                hora_fin_servicio = COALESCE(hora_fin_servicio, ?),
                finalizado_en = COALESCE(finalizado_en, ?)
            WHERE id = ?
            LIMIT 1
        ");
        $stmtCerrar->execute([$informe_id, $hora_inicio_servicio, $hora_fin_servicio, $hora_fin_servicio, $preinspeccion_id]);

        $cierre_preliminar = [
            'ok' => $stmtCerrar->rowCount() >= 0,
            'preinspeccion_id' => $preinspeccion_id,
            'informe_id' => $informe_id,
            'estado' => 'cerrado'
        ];
    }
} catch (Throwable $e) {
    $cierre_preliminar = ['ok' => false, 'error' => $e->getMessage(), 'preinspeccion_id' => $preinspeccion_id];
}

/* El informe final ya contiene la información definitiva; se elimina el borrador del servicio. */
try {
    if ($preinspeccion_id > 0) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS borradores_servicio (
            preinspeccion_id INT NOT NULL PRIMARY KEY,
            token_continuacion VARCHAR(120) DEFAULT NULL,
            datos_json LONGTEXT NOT NULL,
            actualizado_en DATETIME NOT NULL,
            INDEX idx_borrador_token (token_continuacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $delDraft = $pdo->prepare('DELETE FROM borradores_servicio WHERE preinspeccion_id = ?');
        $delDraft->execute([$preinspeccion_id]);
    }
} catch (Throwable $e) {
    @file_put_contents(__DIR__ . '/preinspeccion_debug.log', '[' . date('Y-m-d H:i:s') . '] No se pudo eliminar borrador del servicio: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

@file_put_contents(
    __DIR__ . '/preinspeccion_debug.log',
    '[' . date('Y-m-d H:i:s') . '] Informe final ' . $filename . ' | cierre preliminar: ' . json_encode($cierre_preliminar, JSON_UNESCAPED_UNICODE) . PHP_EOL,
    FILE_APPEND
);

$fecha_mostrar = date('d/m/Y H:i:s');

/* ============================================================
   Telegram
   ============================================================ */
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
                $fecha_mostrar,
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

/* ============================================================
   WhatsApp
   ============================================================ */
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
            $ref = new ReflectionFunction('enviarWhatsAppNuevoInforme');
            $n = $ref->getNumberOfParameters();
            if ($n >= 7) {
                $wa_result = enviarWhatsAppNuevoInforme($tecnico_nombre, $cliente, $orden, $trabajos, $fecha_mostrar, $filename, $direccion);
            } elseif ($n >= 6) {
                $wa_result = enviarWhatsAppNuevoInforme($tecnico_nombre, $cliente, $orden, $trabajos, $fecha_mostrar, $filename);
            } else {
                $wa_result = enviarWhatsAppNuevoInforme($tecnico_nombre, $cliente, $orden, $trabajos, $fecha_mostrar);
            }
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
    'informe_id' => $informe_id,
    'tecnico' => $tecnico_nombre,
    'preinspeccion_id' => $preinspeccion_id > 0 ? $preinspeccion_id : null,
    'hora_inicio_servicio' => $hora_inicio_servicio,
    'hora_fin_servicio' => $hora_fin_servicio,
    'preliminar' => $cierre_preliminar,
    'odoo' => $odoo_result,
    'telegram' => $tg_result,
    'whatsapp' => $wa_result
]);
