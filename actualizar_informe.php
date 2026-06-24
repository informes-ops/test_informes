<?php
/* ZGROUP V27: permite supervisión de hora inicio/fin y actualiza PDF */
/* ZGROUP V26: actualización completa con fecha POST como autoridad final */
session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Lima');

function zgResponder(array $data, int $status = 200): void {
    while (ob_get_level()) ob_end_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function zgFail(string $msg, int $status = 400): void {
    zgResponder(['ok' => false, 'error' => $msg], $status);
}
function zgCol(PDO $pdo, string $tabla, string $col): bool {
    $st = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $st->execute([$tabla, $col]);
    return (int)$st->fetchColumn() > 0;
}
function zgAddCol(PDO $pdo, string $tabla, string $col, string $def): void {
    if (!zgCol($pdo, $tabla, $col)) {
        $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$col` $def");
    }
}
function zgField(array $snapshot, string $id, $default = '') {
    return isset($snapshot['fields'][$id]['value'])
        ? trim((string)$snapshot['fields'][$id]['value'])
        : $default;
}
function zgSafePdfName(string $orden): string {
    $base = preg_replace('/[^A-Za-z0-9_-]/', '_', $orden !== '' ? $orden : 'sin_reporte');
    return 'informe_' . $base . '_ACT_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(5)), 0, 8) . '.pdf';
}
function zgHoraSql($value): ?string {
    $value = trim((string)$value);
    if ($value === '') return null;
    $value = str_replace('T', ' ', $value);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) $value .= ':00';
    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value) ? $value : null;
}

if (empty($_SESSION['panel_ok'])) zgFail('La sesión del panel venció. Vuelve a ingresar.', 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') zgFail('Método no permitido.', 405);

try {
    require __DIR__ . '/db.php';
} catch (Throwable $e) {
    zgFail('No se pudo cargar db.php: ' . $e->getMessage(), 500);
}
if (!isset($pdo) || !($pdo instanceof PDO)) zgFail('No se encontró la conexión PDO.', 500);
try {
    require_once __DIR__ . '/odoo_lib.php';
} catch (Throwable $e) {
    @file_put_contents(__DIR__ . '/odoo_debug.log', '[' . date('Y-m-d H:i:s') . '] No se pudo cargar odoo_lib.php: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

$id = (int)($_POST['informe_id'] ?? 0);
$tecnico_id = (int)($_POST['tecnico_id'] ?? 0);
$orden = trim((string)($_POST['orden'] ?? ''));
$odoo_ticket_ref_post = preg_replace('/\D+/', '', trim((string)($_POST['odoo_ticket_ref'] ?? '')));
$cliente = trim((string)($_POST['cliente'] ?? ''));
$direccion = trim((string)($_POST['direccion'] ?? ''));
$fecha = trim((string)($_POST['fecha'] ?? ''));
$trabajos = trim((string)($_POST['trabajos'] ?? ''));
$direccion_coords = trim((string)($_POST['direccion_coords'] ?? ''));
$tipo_equipo_post = trim((string)($_POST['tipo_equipo'] ?? ''));
$tamano_contenedor_post = trim((string)($_POST['tamano_contenedor'] ?? ''));
$hora_inicio_post = zgHoraSql($_POST['hora_inicio_servicio'] ?? null);
$hora_fin_post = zgHoraSql($_POST['hora_fin_servicio'] ?? null);

if ($id <= 0) zgFail('Informe inválido.');
if ($tecnico_id <= 0) zgFail('Selecciona un técnico.');
if ($orden === '') zgFail('Falta el N° de reporte.');
if ($cliente === '') zgFail('Falta el cliente.');
if ($direccion === '') zgFail('Falta la dirección.');
if ($trabajos === '') zgFail('Falta seleccionar el trabajo realizado.');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) $fecha = date('Y-m-d');
@file_put_contents(
    __DIR__ . '/actualizar_informe_debug.log',
    '[' . date('Y-m-d H:i:s') . '] ID ' . $id . ' | fecha POST recibida: ' . $fecha . PHP_EOL,
    FILE_APPEND
);

$datos_json = trim((string)($_POST['datos_json'] ?? ''));
$snapshot = [];
if ($datos_json !== '') {
    $snapshot = json_decode($datos_json, true);
    if (!is_array($snapshot)) zgFail('Los datos del formulario no son válidos.');
}
if (!isset($snapshot['fields']) || !is_array($snapshot['fields'])) $snapshot['fields'] = [];
// La fecha POST es la autoridad final durante la edición. También se guarda en
// la instantánea para impedir que una carga posterior recupere la fecha antigua.
$snapshot['fields']['fecha'] = [
    'type' => 'date',
    'value' => $fecha,
    'checked' => false,
];
$datos_json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$tipo_equipo = $tipo_equipo_post !== '' ? $tipo_equipo_post : zgField($snapshot, 'zgTipoEquipo', '');
if ($tipo_equipo === '') {
    $tipo_equipo = preg_match('/^SG[- ]?(3000|5000)$/i', zgField($snapshot, 'controladorEquipo', '')) ? 'Genset' : 'Reefer';
}
$tamano_contenedor = $tamano_contenedor_post !== '' ? $tamano_contenedor_post : zgField($snapshot, 'zgTamanoContenedor', '');
if (strcasecmp($tipo_equipo, 'Genset') === 0) $tamano_contenedor = 'No aplica';

$latitud = '';
$longitud = '';
if ($direccion_coords !== '' && strpos($direccion_coords, ',') !== false) {
    [$latitud, $longitud] = array_map('trim', explode(',', $direccion_coords, 2));
}

/*
 * Los materiales llegan también como campo POST independiente.
 * Así no dependen del orden en que carguen los scripts de la tabla.
 */
$repuestos_actualizados = ((string)($_POST['repuestos_actualizados'] ?? '')) === '1';
$repuestos_post = trim((string)($_POST['repuestos_manual'] ?? ''));
$repuestos_snapshot = trim((string)($snapshot['fields']['repuestosManual']['value'] ?? ''));
$repuestos_manual = $repuestos_actualizados ? $repuestos_post : $repuestos_snapshot;

if ($repuestos_actualizados || $repuestos_manual !== '') {
    $snapshot['fields']['repuestosManual'] = [
        'type' => 'textarea',
        'value' => $repuestos_manual,
        'checked' => false,
    ];
    $snapshot['fields']['requiereRepuesto'] = [
        'type' => 'hidden',
        'value' => $repuestos_manual !== '' ? 'si' : 'no',
        'checked' => false,
    ];
    $datos_json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    zgFail('No se recibió el PDF actualizado.');
}
$tmp = $_FILES['pdf']['tmp_name'];
if (!is_uploaded_file($tmp)) zgFail('La subida del PDF no es válida.');
if (function_exists('mime_content_type')) {
    $mime = mime_content_type($tmp);
    if (!in_array($mime, ['application/pdf', 'application/octet-stream'], true)) {
        zgFail('El archivo no parece ser PDF.');
    }
}

$nuevoDest = null;
$dbCommitted = false;
try {
    zgAddCol($pdo, 'informes', 'datos_json', 'LONGTEXT DEFAULT NULL');
    zgAddCol($pdo, 'informes', 'repuestos_manual', 'LONGTEXT DEFAULT NULL');
    zgAddCol($pdo, 'informes', 'actualizado_en', 'DATETIME DEFAULT NULL');
    zgAddCol($pdo, 'informes', 'tipo_equipo', 'VARCHAR(30) DEFAULT NULL');
    zgAddCol($pdo, 'informes', 'tamano_contenedor', 'VARCHAR(60) DEFAULT NULL');
    zgAddCol($pdo, 'informes', 'hora_inicio_servicio', 'DATETIME DEFAULT NULL');
    zgAddCol($pdo, 'informes', 'hora_fin_servicio', 'DATETIME DEFAULT NULL');
    if (function_exists('zgOdooEnsureColumns')) zgOdooEnsureColumns($pdo);

    $preColumns = [
        'modalidad_comercial' => 'VARCHAR(40) DEFAULT NULL',
        'tipo_instalacion' => 'VARCHAR(80) DEFAULT NULL',
        'tipo_equipo' => 'VARCHAR(30) DEFAULT NULL',
        'tamano_contenedor' => 'VARCHAR(60) DEFAULT NULL',
        'presion_alta' => 'VARCHAR(50) DEFAULT NULL',
        'presion_baja' => 'VARCHAR(50) DEFAULT NULL',
        'alarma_encontrada' => 'VARCHAR(180) DEFAULT NULL',
        'genset_horometro_inicial' => 'DECIMAL(12,1) DEFAULT NULL',
        'genset_voltaje_bateria_inicial' => 'VARCHAR(50) DEFAULT NULL',
        'genset_nivel_combustible_inicial' => 'VARCHAR(40) DEFAULT NULL',
        'genset_nivel_aceite_inicial' => 'VARCHAR(50) DEFAULT NULL',
        'genset_refrigerante_motor_inicial' => 'VARCHAR(60) DEFAULT NULL',
        'genset_arranque_inicial' => 'VARCHAR(80) DEFAULT NULL',
        'genset_frecuencia_inicial' => 'DECIMAL(8,2) DEFAULT NULL',
        'genset_presion_aceite_inicial' => 'VARCHAR(50) DEFAULT NULL',
        'evidencias_json' => 'LONGTEXT DEFAULT NULL',
        'actualizado_en' => 'DATETIME DEFAULT NULL',
        'hora_inicio_servicio' => 'DATETIME DEFAULT NULL',
        'hora_fin_servicio' => 'DATETIME DEFAULT NULL',
    ];
    foreach ($preColumns as $preCol => $preDef) zgAddCol($pdo, 'inspecciones_preliminares', $preCol, $preDef);

    /*
     * Estas tablas se aseguran ANTES de iniciar la transacción.
     * MySQL confirma implícitamente una transacción cuando ejecuta CREATE/ALTER TABLE.
     * Si el DDL se ejecuta dentro de la transacción, commit() termina con
     * "There is no active transaction" y el registro puede apuntar a un PDF eliminado.
     */
    $pdo->exec("CREATE TABLE IF NOT EXISTS clientes_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(180) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_clientes_catalogo_nombre (nombre)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cotizaciones_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cotizacion VARCHAR(30) NOT NULL UNIQUE,
        cliente_id INT DEFAULT NULL,
        cliente_nombre VARCHAR(180) DEFAULT NULL,
        descripcion VARCHAR(220) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $st = $pdo->prepare('SELECT * FROM informes WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    $inf = $st->fetch(PDO::FETCH_ASSOC);
    if (!$inf) zgFail('El informe ya no existe.', 404);
    $odoo_ticket_ref = $odoo_ticket_ref_post !== '' ? $odoo_ticket_ref_post : preg_replace('/\D+/', '', (string)($inf['odoo_ticket_ref'] ?? ''));
    if ($odoo_ticket_ref === '') $odoo_ticket_ref = $orden;

    $hora_inicio_servicio = $hora_inicio_post ?: trim((string)($inf['hora_inicio_servicio'] ?? ''));
    $hora_fin_servicio = $hora_fin_post ?: trim((string)($inf['hora_fin_servicio'] ?? ''));
    if ($hora_inicio_servicio === '') $hora_inicio_servicio = trim((string)($inf['creado_en'] ?? '')) ?: date('Y-m-d H:i:s');
    if ($hora_fin_servicio === '') $hora_fin_servicio = trim((string)($inf['creado_en'] ?? '')) ?: date('Y-m-d H:i:s');
    $snapshot['fields']['horaInicioServicio'] = ['type'=>'datetime-local','value'=>str_replace(' ', 'T', substr($hora_inicio_servicio,0,16)),'checked'=>false];
    $snapshot['fields']['horaFinServicio'] = ['type'=>'datetime-local','value'=>str_replace(' ', 'T', substr($hora_fin_servicio,0,16)),'checked'=>false];
    $datos_json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    /* Si el navegador no envió el campo explícito, conservamos el respaldo anterior. */
    if (!$repuestos_actualizados && $repuestos_manual === '') {
        $repuestos_manual = trim((string)($inf['repuestos_manual'] ?? ''));
        if ($repuestos_manual !== '') {
            $snapshot['fields']['repuestosManual'] = ['type'=>'textarea','value'=>$repuestos_manual,'checked'=>false];
            $snapshot['fields']['requiereRepuesto'] = ['type'=>'hidden','value'=>'si','checked'=>false];
            $datos_json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    $archivoAnterior = basename((string)($inf['archivo'] ?? ''));
    if ($archivoAnterior === '' || !preg_match('/^[A-Za-z0-9._-]+\.pdf$/i', $archivoAnterior)) {
        zgFail('El nombre del PDF guardado no es seguro.');
    }

    $dir = __DIR__ . '/informes';
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) zgFail('No se pudo crear la carpeta informes.', 500);

    /*
     * Se usa un nombre nuevo en cada actualización para impedir que Chrome
     * muestre el PDF anterior desde caché. Después del commit se borra el viejo.
     */
    $archivoNuevo = zgSafePdfName($orden);
    $nuevoDest = $dir . '/' . $archivoNuevo;
    if (!move_uploaded_file($tmp, $nuevoDest)) zgFail('No se pudo guardar el PDF actualizado.', 500);

    $pdo->beginTransaction();
    try {
        $up = $pdo->prepare(
            'UPDATE informes
             SET tecnico_id = ?, orden = ?, cliente = ?, direccion = ?, fecha = ?, trabajos = ?,
                 archivo = ?, datos_json = ?, repuestos_manual = ?, tipo_equipo = ?, tamano_contenedor = ?,
                 hora_inicio_servicio = ?, hora_fin_servicio = ?, odoo_ticket_ref = ?, actualizado_en = NOW()
             WHERE id = ? LIMIT 1'
        );
        $up->execute([
            $tecnico_id,
            $orden,
            $cliente,
            $direccion,
            $fecha,
            $trabajos,
            $archivoNuevo,
            $datos_json !== '' ? $datos_json : null,
            $repuestos_manual !== '' ? $repuestos_manual : null,
            $tipo_equipo !== '' ? $tipo_equipo : null,
            $tamano_contenedor !== '' ? $tamano_contenedor : null,
            $hora_inicio_servicio,
            $hora_fin_servicio,
            $odoo_ticket_ref,
            $id,
        ]);

        $preId = (int)($inf['preinspeccion_id'] ?? 0);
        if ($preId > 0 && $snapshot) {
            $map = [
                'tecnico_id' => ['tecnicoId', 'int'],
                'cliente' => ['cliente', 'str'],
                'cotizacion' => ['orden', 'str'],
                'trabajo' => [null, 'trabajos'],
                'modalidad_comercial' => ['zgModalidadComercial', 'str'],
                'tipo_instalacion' => ['zgTipoInstalacion', 'str'],
                'tipo_equipo' => ['zgTipoEquipo', 'str'],
                'tamano_contenedor' => ['zgTamanoContenedor', 'str'],
                'numero_equipo' => ['equipoNo', 'str'],
                'serie_unidad' => ['serialUnidad', 'str'],
                'marca_equipo' => ['marcaEquipo', 'str'],
                'modelo_equipo' => ['modeloEquipo', 'str'],
                'controlador' => ['controladorEquipo', 'str'],
                'anio_fabricacion' => ['anioFabricacion', 'str'],
                'refrigerante' => ['refrigerante', 'str'],
                'set_point' => ['setPoint', 'nullable'],
                'temperatura_ambiente' => ['temperaturaAmbiente', 'nullable'],
                'retorno_aire' => ['retornoAire', 'nullable'],
                'suministro_aire' => ['suministroAire', 'nullable'],
                'presion_alta' => ['presionAlta', 'str'],
                'presion_baja' => ['presionBaja', 'str'],
                'voltaje_l1_l2' => ['voltajeL1L2', 'str'],
                'voltaje_l2_l3' => ['voltajeL2L3', 'str'],
                'voltaje_l1_l3' => ['voltajeL1L3', 'str'],
                'estado_inicial' => ['estadoInicial', 'str'],
                'alarma_encontrada' => ['alarmaEncontrada', 'str'],
                'observacion_inicial' => ['observacionInicial', 'str'],
                'ubicacion_texto' => ['direccion', 'str'],
                'latitud' => [null, 'latitud'],
                'longitud' => [null, 'longitud'],
                'genset_horometro_inicial' => ['gensetHorometroInicial', 'nullable'],
                'genset_voltaje_bateria_inicial' => ['gensetVoltajeBateriaInicial', 'str'],
                'genset_nivel_combustible_inicial' => ['gensetNivelCombustibleInicial', 'str'],
                'genset_nivel_aceite_inicial' => ['gensetNivelAceiteInicial', 'str'],
                'genset_refrigerante_motor_inicial' => ['gensetRefrigeranteMotorInicial', 'str'],
                'genset_arranque_inicial' => ['gensetArranqueInicial', 'str'],
                'genset_frecuencia_inicial' => ['gensetFrecuenciaInicial', 'nullable'],
                'genset_presion_aceite_inicial' => ['gensetPresionAceiteInicial', 'str'],
                'evidencias_json' => [null, 'evidencias'],
                'actualizado_en' => [null, 'now'],
            ];
            $sets = [];
            $vals = [];
            foreach ($map as $col => $spec) {
                if (!zgCol($pdo, 'inspecciones_preliminares', $col)) continue;
                [$fid, $kind] = $spec;
                if ($kind === 'now') {
                    $sets[] = "`$col` = NOW()";
                    continue;
                }
                if ($kind === 'trabajos') $v = $trabajos;
                elseif ($kind === 'int') $v = (int)zgField($snapshot, $fid, $tecnico_id);
                elseif ($kind === 'latitud') $v = $latitud !== '' ? $latitud : null;
                elseif ($kind === 'longitud') $v = $longitud !== '' ? $longitud : null;
                elseif ($kind === 'nullable') {
                    $raw = zgField($snapshot, $fid, '');
                    $v = $raw !== '' ? $raw : null;
                } elseif ($kind === 'evidencias') {
                    $v = isset($snapshot['preEvidence']) && is_array($snapshot['preEvidence'])
                        ? json_encode($snapshot['preEvidence'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : null;
                } else $v = zgField($snapshot, $fid, '');

                if ($col === 'tipo_equipo' && trim((string)$v) === '') $v = $tipo_equipo;
                if ($col === 'tamano_contenedor') $v = strcasecmp($tipo_equipo, 'Genset') === 0 ? 'No aplica' : ($v !== '' ? $v : $tamano_contenedor);
                if (strcasecmp($tipo_equipo, 'Genset') === 0 && in_array($col, ['tipo_instalacion','refrigerante','set_point','temperatura_ambiente','retorno_aire','suministro_aire','presion_alta','presion_baja'], true)) $v = null;

                $sets[] = "`$col` = ?";
                $vals[] = $v;
            }
            if ($sets) {
                $vals[] = $preId;
                $sql = 'UPDATE inspecciones_preliminares SET ' . implode(', ', $sets) . ' WHERE id = ? LIMIT 1';
                $ps = $pdo->prepare($sql);
                $ps->execute($vals);
            }
            $psTiempo = $pdo->prepare('UPDATE inspecciones_preliminares SET hora_inicio_servicio = ?, hora_fin_servicio = ?, finalizado_en = ? WHERE id = ? LIMIT 1');
            $psTiempo->execute([$hora_inicio_servicio, $hora_fin_servicio, $hora_fin_servicio, $preId]);
        }

        $pdo->commit();
        $dbCommitted = true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if (!$dbCommitted && $nuevoDest && is_file($nuevoDest)) @unlink($nuevoDest);
        throw $e;
    }

    /*
     * Odoo se sincroniza DESPUÉS del commit principal. Si Odoo falla, la
     * actualización local y el PDF nuevo permanecen guardados.
     */
    $odoo_result = ['ok' => false, 'skipped' => true, 'estado' => 'no_ejecutado', 'error' => 'La integración de Odoo no se ejecutó.'];
    try {
        if (function_exists('zgOdooSyncInforme')) {
            $odoo_result = zgOdooSyncInforme(
                $pdo,
                $id,
                $odoo_ticket_ref,
                $nuevoDest,
                $archivoNuevo,
                (int)($inf['odoo_attachment_id'] ?? 0)
            );
        } else {
            $odoo_result = ['ok' => false, 'estado' => 'sin_libreria', 'error' => 'No se encontró odoo_lib.php.'];
        }
    } catch (Throwable $odooError) {
        $odoo_result = ['ok' => false, 'estado' => 'error', 'error' => $odooError->getMessage()];
        @file_put_contents(__DIR__ . '/odoo_debug.log', '[' . date('Y-m-d H:i:s') . '] Actualización informe ID ' . $id . ': ' . $odooError->getMessage() . PHP_EOL, FILE_APPEND);
    }

    /*
     * La sincronización de catálogos se hace DESPUÉS del commit principal.
     * Una falla secundaria aquí no invalida el informe ni elimina su PDF.
     */
    try {
        $findClient = $pdo->prepare('SELECT id FROM clientes_catalogo WHERE LOWER(nombre) = LOWER(?) LIMIT 1');
        $findClient->execute([$cliente]);
        $clientId = (int)$findClient->fetchColumn();
        if ($clientId > 0) {
            $pdo->prepare('UPDATE clientes_catalogo SET nombre = ?, activo = 1 WHERE id = ?')->execute([$cliente, $clientId]);
        } else {
            $pdo->prepare('INSERT INTO clientes_catalogo (nombre, activo) VALUES (?, 1)')->execute([$cliente]);
            $clientId = (int)$pdo->lastInsertId();
        }
        $pdo->prepare('INSERT INTO cotizaciones_catalogo (cotizacion, cliente_id, cliente_nombre, activo) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE cliente_id = VALUES(cliente_id), cliente_nombre = VALUES(cliente_nombre), activo = 1')->execute([$orden, $clientId ?: null, $cliente]);
    } catch (Throwable $catalogError) {
        @file_put_contents(
            __DIR__ . '/actualizar_informe_debug.log',
            '[' . date('Y-m-d H:i:s') . '] Catálogo no sincronizado para informe ID ' . $id . ': ' . $catalogError->getMessage() . PHP_EOL,
            FILE_APPEND
        );
    }

    $rutaAnterior = $dir . '/' . $archivoAnterior;
    if ($archivoAnterior !== $archivoNuevo && is_file($rutaAnterior)) @unlink($rutaAnterior);

    $checkFecha = $pdo->prepare('SELECT fecha FROM informes WHERE id = ? LIMIT 1');
    $checkFecha->execute([$id]);
    $fechaPersistida = trim((string)$checkFecha->fetchColumn());
    @file_put_contents(
        __DIR__ . '/actualizar_informe_debug.log',
        '[' . date('Y-m-d H:i:s') . '] ID ' . $id . ' | fecha persistida: ' . $fechaPersistida . ' | PDF: ' . $archivoNuevo . PHP_EOL,
        FILE_APPEND
    );

    zgResponder([
        'ok' => true,
        'informe_id' => $id,
        'archivo' => $archivoNuevo,
        'pdf_url' => 'informes/' . rawurlencode($archivoNuevo) . '?v=' . time(),
        'fecha_guardada' => $fechaPersistida !== '' ? $fechaPersistida : $fecha,
        'hora_inicio_servicio' => $hora_inicio_servicio,
        'hora_fin_servicio' => $hora_fin_servicio,
        'actualizado' => date('Y-m-d H:i:s'),
        'odoo' => $odoo_result,
    ]);
} catch (Throwable $e) {
    if (!$dbCommitted && $nuevoDest && is_file($nuevoDest)) @unlink($nuevoDest);
    zgFail('No se pudo actualizar el informe: ' . $e->getMessage(), 500);
}
