<?php
/* ZGROUP V44: respaldo definitivo para creación de preliminares.
   Si por caché o restauración del navegador una creación llega a este archivo
   sin un ID válido, se deriva al guardado normal en vez de devolver
   "Inspección preliminar inválida". */
$zgPreAccionInicial = strtolower(trim((string)($_POST['accion_preliminar'] ?? '')));
$zgPreIdInicial = (int)($_POST['preinspeccion_id'] ?? 0);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && ($zgPreAccionInicial === 'crear' || $zgPreIdInicial <= 0)) {
    @file_put_contents(
        __DIR__ . '/preinspeccion_debug.log',
        '[' . date('Y-m-d H:i:s') . '] V44: solicitud sin ID recibida en actualizar_preinspeccion.php; derivada a guardar_preinspeccion.php.' . PHP_EOL,
        FILE_APPEND
    );
    unset($_POST['preinspeccion_id']);
    require __DIR__ . '/guardar_preinspeccion.php';
    exit;
}
/* ZGROUP V27: conserva la hora original de inicio del servicio */
session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Lima');

function zgPreResponder(array $data, int $status = 200): void {
    while (ob_get_level()) ob_end_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function zgPreFail(string $message, int $status = 400): void {
    zgPreResponder(['ok' => false, 'error' => $message], $status);
}
function zgPreTxt(string $key): string {
    return trim((string)($_POST[$key] ?? ''));
}
function zgPrePlano(string $value, int $max = 180): string {
    $value = preg_replace('/\s+/u', ' ', trim($value));
    return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max);
}
function zgPreCodigo(string $value, int $max = 100): string {
    $value = strtoupper(trim($value));
    $value = preg_replace('/[^A-Z0-9\-_.\/]/', '', $value);
    return substr($value, 0, $max);
}
function zgPreCol(PDO $pdo, string $table, string $column): bool {
    $st = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $st->execute([$table, $column]);
    return (int)$st->fetchColumn() > 0;
}
function zgPreAddCol(PDO $pdo, string $table, string $column, string $definition): void {
    if (!zgPreCol($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}
function zgPreNumeroOpcional(string $raw, float $min, float $max, string $label): ?string {
    $raw = trim(str_replace(',', '.', $raw));
    if ($raw === '') return null;
    if (!is_numeric($raw)) zgPreFail($label . ' debe ser numérico.');
    $value = (float)$raw;
    if ($value < $min || $value > $max) zgPreFail($label . ' está fuera del rango permitido.');
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
}

if (empty($_SESSION['panel_ok'])) zgPreFail('La sesión del panel venció. Vuelve a ingresar.', 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') zgPreFail('Método no permitido.', 405);

try {
    require __DIR__ . '/db.php';
} catch (Throwable $e) {
    zgPreFail('No se pudo cargar db.php: ' . $e->getMessage(), 500);
}
if (!isset($pdo) || !($pdo instanceof PDO)) zgPreFail('No se encontró la conexión PDO.', 500);

$id = (int)($_POST['preinspeccion_id'] ?? 0);
$tecnicoId = (int)($_POST['tecnico_id'] ?? 0);
$cliente = zgPrePlano(zgPreTxt('cliente'), 150);
$reporte = preg_replace('/\D+/', '', zgPreTxt('cotizacion'));
$odooTicketRef = preg_replace('/\D+/', '', zgPreTxt('odoo_ticket_ref'));
$trabajo = zgPrePlano(zgPreTxt('trabajo'), 300);
$tipoEquipo = zgPrePlano(zgPreTxt('tipo_equipo'), 30);
$modalidad = zgPrePlano(zgPreTxt('modalidad_comercial'), 40);
$tipoInstalacion = zgPrePlano(zgPreTxt('tipo_instalacion'), 80);
$tamano = zgPrePlano(zgPreTxt('tamano_contenedor'), 60);
$numeroEquipo = zgPreCodigo(zgPreTxt('numero_equipo'), 60);
$serial = zgPreCodigo(zgPreTxt('serie_unidad'), 100);
$marca = zgPrePlano(zgPreTxt('marca_equipo'), 100);
$modelo = zgPrePlano(zgPreTxt('modelo_equipo'), 100);
$controlador = zgPrePlano(zgPreTxt('controlador'), 100);
$anioFabricacion = preg_replace('/\D+/', '', zgPreTxt('anio_fabricacion'));
$anioFabricacion = substr($anioFabricacion, 0, 4);
$refrigerante = zgPrePlano(zgPreTxt('refrigerante'), 50);
$setPoint = zgPreNumeroOpcional(zgPreTxt('set_point'), -50, 60, 'Set point');
$tempAmbiente = zgPreNumeroOpcional(zgPreTxt('temperatura_ambiente'), -30, 80, 'Temperatura ambiente');
$retorno = zgPreNumeroOpcional(zgPreTxt('retorno_aire'), -60, 80, 'Retorno de aire');
$suministro = zgPreNumeroOpcional(zgPreTxt('suministro_aire'), -60, 80, 'Suministro de aire');
$presionAlta = zgPrePlano(zgPreTxt('presion_alta'), 50);
$presionBaja = zgPrePlano(zgPreTxt('presion_baja'), 50);
$v12 = zgPrePlano(zgPreTxt('voltaje_l1_l2'), 50);
$v23 = zgPrePlano(zgPreTxt('voltaje_l2_l3'), 50);
$v13 = zgPrePlano(zgPreTxt('voltaje_l1_l3'), 50);
$estadoInicial = zgPrePlano(zgPreTxt('estado_inicial'), 180);
$alarma = zgPrePlano(zgPreTxt('alarma_encontrada'), 180);
$observacion = trim((string)($_POST['observacion_inicial'] ?? ''));
$ubicacion = trim((string)($_POST['ubicacion_texto'] ?? ''));
$latitud = zgPrePlano(zgPreTxt('latitud'), 50);
$longitud = zgPrePlano(zgPreTxt('longitud'), 50);
$evidenciasJson = trim((string)($_POST['evidencias_preliminares_json'] ?? ''));
if ($evidenciasJson !== '') {
    $evidenciasDec = json_decode($evidenciasJson, true);
    if (!is_array($evidenciasDec)) $evidenciasJson = '[]';
}
$gHorometro = zgPreNumeroOpcional(zgPreTxt('genset_horometro_inicial'), 0, 9999999, 'Horómetro inicial');
$gBateria = zgPrePlano(zgPreTxt('genset_voltaje_bateria_inicial'), 50);
$gCombustible = zgPrePlano(zgPreTxt('genset_nivel_combustible_inicial'), 40);
$gAceite = zgPrePlano(zgPreTxt('genset_nivel_aceite_inicial'), 50);
$gRefrigerante = zgPrePlano(zgPreTxt('genset_refrigerante_motor_inicial'), 60);
$gArranque = zgPrePlano(zgPreTxt('genset_arranque_inicial'), 80);
$gFrecuencia = zgPreNumeroOpcional(zgPreTxt('genset_frecuencia_inicial'), 0, 1000, 'Frecuencia inicial');
$gPresionAceite = zgPrePlano(zgPreTxt('genset_presion_aceite_inicial'), 50);

if ($id <= 0) zgPreFail('Inspección preliminar inválida.');
if ($tecnicoId <= 0) zgPreFail('Selecciona un técnico.');
if ($cliente === '') zgPreFail('Falta el cliente.');
if ($reporte === '') zgPreFail('Falta el número de reporte.');
if ($odooTicketRef === '' || strlen($odooTicketRef) > 15) zgPreFail('El ticket de Odoo debe contener entre 1 y 15 números.');
if ($numeroEquipo === '') zgPreFail('Falta el número de equipo.');
if ($serial === '') zgPreFail('Falta el serial de la unidad.');
if ($marca === '') zgPreFail('Falta la marca del equipo.');
if ($controlador === '') zgPreFail('Falta el controlador.');
if ($ubicacion === '') zgPreFail('Falta la ubicación.');
if ($tipoEquipo === '') $tipoEquipo = preg_match('/^SG[- ]?(3000|5000)$/i', $controlador) ? 'Genset' : 'Reefer';
if (strcasecmp($tipoEquipo, 'Genset') === 0) {
    $tamano = 'No aplica';
    $tipoInstalacion = '';
    $modelo = '';
    $anioFabricacion = '';
    $refrigerante = '';
    $setPoint = $tempAmbiente = $retorno = $suministro = null;
    $presionAlta = $presionBaja = '';
} elseif ($anioFabricacion !== '' && !preg_match('/^(19|20)\d{2}$/', $anioFabricacion)) {
    zgPreFail('El año de fabricación debe tener 4 dígitos.');
}

try {
    $requiredColumns = [
        'odoo_ticket_ref' => "VARCHAR(120) DEFAULT NULL",
        'modalidad_comercial' => "VARCHAR(40) DEFAULT NULL",
        'tipo_instalacion' => "VARCHAR(80) DEFAULT NULL",
        'tipo_equipo' => "VARCHAR(30) DEFAULT NULL",
        'tamano_contenedor' => "VARCHAR(60) DEFAULT NULL",
        'modelo_equipo' => "VARCHAR(100) DEFAULT NULL",
        'anio_fabricacion' => "VARCHAR(4) DEFAULT NULL",
        'presion_alta' => "VARCHAR(50) DEFAULT NULL",
        'presion_baja' => "VARCHAR(50) DEFAULT NULL",
        'alarma_encontrada' => "VARCHAR(180) DEFAULT NULL",
        'genset_horometro_inicial' => "DECIMAL(12,1) DEFAULT NULL",
        'genset_voltaje_bateria_inicial' => "VARCHAR(50) DEFAULT NULL",
        'genset_nivel_combustible_inicial' => "VARCHAR(40) DEFAULT NULL",
        'genset_nivel_aceite_inicial' => "VARCHAR(50) DEFAULT NULL",
        'genset_refrigerante_motor_inicial' => "VARCHAR(60) DEFAULT NULL",
        'genset_arranque_inicial' => "VARCHAR(80) DEFAULT NULL",
        'genset_frecuencia_inicial' => "DECIMAL(8,2) DEFAULT NULL",
        'genset_presion_aceite_inicial' => "VARCHAR(50) DEFAULT NULL",
        'evidencias_json' => "LONGTEXT DEFAULT NULL",
        'actualizado_en' => "DATETIME DEFAULT NULL",
        'hora_inicio_servicio' => "DATETIME DEFAULT NULL",
        'hora_fin_servicio' => "DATETIME DEFAULT NULL",
    ];
    foreach ($requiredColumns as $col => $def) zgPreAddCol($pdo, 'inspecciones_preliminares', $col, $def);

    $check = $pdo->prepare('SELECT id, token_continuacion, tipo_equipo FROM inspecciones_preliminares WHERE id = ? LIMIT 1');
    $check->execute([$id]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);
    if (!$existing) zgPreFail('La inspección preliminar ya no existe.', 404);

    $sql = "UPDATE inspecciones_preliminares SET
        tecnico_id = ?, cliente = ?, cotizacion = ?, odoo_ticket_ref = ?, trabajo = ?,
        modalidad_comercial = ?, tipo_instalacion = ?, tipo_equipo = ?, tamano_contenedor = ?,
        numero_equipo = ?, serie_unidad = ?, marca_equipo = ?, modelo_equipo = ?, controlador = ?, anio_fabricacion = ?, refrigerante = ?,
        set_point = ?, temperatura_ambiente = ?, retorno_aire = ?, suministro_aire = ?,
        presion_alta = ?, presion_baja = ?, voltaje_l1_l2 = ?, voltaje_l2_l3 = ?, voltaje_l1_l3 = ?,
        estado_inicial = ?, alarma_encontrada = ?, observacion_inicial = ?, evidencias_json = ?,
        ubicacion_texto = ?, latitud = ?, longitud = ?,
        genset_horometro_inicial = ?, genset_voltaje_bateria_inicial = ?,
        genset_nivel_combustible_inicial = ?, genset_nivel_aceite_inicial = ?,
        genset_refrigerante_motor_inicial = ?, genset_arranque_inicial = ?,
        genset_frecuencia_inicial = ?, genset_presion_aceite_inicial = ?, actualizado_en = NOW()
        WHERE id = ? LIMIT 1";
    $st = $pdo->prepare($sql);
    $st->execute([
        $tecnicoId, $cliente, $reporte, $odooTicketRef, $trabajo,
        $modalidad ?: null, $tipoInstalacion ?: null, $tipoEquipo, $tamano ?: null,
        $numeroEquipo, $serial, $marca, $modelo ?: null, $controlador, $anioFabricacion ?: null, $refrigerante ?: null,
        $setPoint, $tempAmbiente, $retorno, $suministro,
        $presionAlta ?: null, $presionBaja ?: null, $v12 ?: null, $v23 ?: null, $v13 ?: null,
        $estadoInicial ?: null, $alarma ?: null, $observacion ?: null, $evidenciasJson !== '' ? $evidenciasJson : null,
        $ubicacion, $latitud ?: null, $longitud ?: null,
        $gHorometro, $gBateria ?: null, $gCombustible ?: null, $gAceite ?: null,
        $gRefrigerante ?: null, $gArranque ?: null, $gFrecuencia, $gPresionAceite ?: null,
        $id
    ]);

    // Mantiene disponibles el cliente y el número de reporte en los catálogos universales.
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS clientes_catalogo (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(180) NOT NULL, activo TINYINT(1) NOT NULL DEFAULT 1, creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_clientes_catalogo_nombre (nombre)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS cotizaciones_catalogo (id INT AUTO_INCREMENT PRIMARY KEY, cotizacion VARCHAR(30) NOT NULL UNIQUE, cliente_id INT DEFAULT NULL, cliente_nombre VARCHAR(180) DEFAULT NULL, descripcion VARCHAR(220) DEFAULT NULL, activo TINYINT(1) NOT NULL DEFAULT 1, creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $findClient = $pdo->prepare('SELECT id FROM clientes_catalogo WHERE LOWER(nombre) = LOWER(?) LIMIT 1');
        $findClient->execute([$cliente]);
        $clientId = (int)$findClient->fetchColumn();
        if ($clientId > 0) {
            $pdo->prepare('UPDATE clientes_catalogo SET nombre = ?, activo = 1 WHERE id = ?')->execute([$cliente, $clientId]);
        } else {
            $pdo->prepare('INSERT INTO clientes_catalogo (nombre, activo) VALUES (?, 1)')->execute([$cliente]);
            $clientId = (int)$pdo->lastInsertId();
        }
        $pdo->prepare('INSERT INTO cotizaciones_catalogo (cotizacion, cliente_id, cliente_nombre, activo) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE cliente_id = VALUES(cliente_id), cliente_nombre = VALUES(cliente_nombre), activo = 1')->execute([$reporte, $clientId ?: null, $cliente]);
    } catch (Throwable $e) {
        // El cambio principal ya se guardó. Un fallo de catálogo no debe deshacerlo.
    }

    $borradorEliminado = false;
    $tipoAnterior = trim((string)($existing['tipo_equipo'] ?? ''));
    if ($tipoAnterior !== '' && strcasecmp($tipoAnterior, $tipoEquipo) !== 0) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS borradores_servicio (
                preinspeccion_id INT NOT NULL PRIMARY KEY,
                token_continuacion VARCHAR(120) DEFAULT NULL,
                datos_json LONGTEXT NOT NULL,
                actualizado_en DATETIME NOT NULL,
                INDEX idx_borrador_token (token_continuacion)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $delDraft = $pdo->prepare('DELETE FROM borradores_servicio WHERE preinspeccion_id = ?');
            $delDraft->execute([$id]);
            $borradorEliminado = $delDraft->rowCount() > 0;
        } catch (Throwable $e) {
            // El cambio principal ya fue guardado; no se detiene por este mantenimiento.
        }
    }

    $token = trim((string)($existing['token_continuacion'] ?? ''));
    zgPreResponder([
        'ok' => true,
        'pre_id' => $id,
        'token' => $token,
        'continuar_url' => $token !== '' ? ('index.php?token=' . urlencode($token)) : '',
        'borrador_eliminado' => $borradorEliminado,
        'message' => 'Inspección preliminar actualizada correctamente.'
    ]);
} catch (Throwable $e) {
    zgPreFail('No se pudo actualizar la inspección preliminar: ' . $e->getMessage(), 500);
}
