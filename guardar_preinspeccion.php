<?php
/* ZGROUP V44: endpoint único para crear preliminares nuevas */
/* ZGROUP V27: registra hora de inicio al finalizar la preliminar */
/* ZGROUP V22: guarda también evidencias preliminares para edición posterior */
/* ZGROUP BUILD: guardar_preinspeccion V9 REAL 2026-06-20
   Guarda tamaño/tipo de equipo, alarma, presiones y parámetros iniciales de genset.
   ============================================================ */
/* ============================================================
   guardar_preinspeccion.php
   - Guarda cómo se encontró el equipo antes del trabajo.
   - Si cliente/reporte/contenedor no existen en el catálogo,
     los crea automáticamente para que aparezcan luego en el panel e index.
   - Genera token para continuar el informe final.
   ============================================================ */

ob_start();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Lima');

function responderJSON($data) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function fail($msg) { responderJSON(['ok' => false, 'error' => $msg]); }
function postTxt($key) { return trim((string)($_POST[$key] ?? '')); }
function limpiarPlano($s, $max = 180) {
    $s = trim((string)$s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return function_exists('mb_substr') ? mb_substr($s, 0, $max, 'UTF-8') : substr($s, 0, $max);
}
function limpiarCodigo($s, $max = 100) {
    $s = strtoupper(trim((string)$s));
    $s = preg_replace('/[^A-Z0-9\-_.\/]/', '', $s);
    return substr($s, 0, $max);
}
function postTemp($key, $min, $max, $label) {
    $raw = postTxt($key);
    if ($raw === '') return null;
    $raw = str_replace(',', '.', $raw);
    if (!is_numeric($raw)) fail($label . ' debe ser numérica.');
    $val = (float)$raw;
    if ($val < $min || $val > $max) fail($label . " parece incoherente. Debe estar entre $min °C y $max °C.");
    return $val;
}
function postNumeroNullable($key, $min, $max, $label) {
    $raw = postTxt($key);
    if ($raw === '') return null;
    $raw = str_replace(',', '.', $raw);
    if (!is_numeric($raw)) fail($label . ' debe ser numérico.');
    $val = (float)$raw;
    if ($val < $min || $val > $max) fail($label . " parece incoherente. Debe estar entre $min y $max.");
    return $val;
}
function validarVoltaje($raw, $label) {
    $raw = trim((string)$raw);
    if ($raw === '') return '';
    $limpio = str_replace(',', '.', $raw);
    $limpio = preg_replace('/\s*v\s*$/i', '', $limpio);
    if (!is_numeric($limpio)) fail($label . ' debe ser numérico. Ejemplo: 220 o 220 V.');
    $v = (float)$limpio;
    if ($v < 0 || $v > 600) fail($label . ' parece incoherente. Debe estar entre 0 V y 600 V.');
    return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.') . ' V';
}
function columnaExiste(PDO $pdo, $tabla, $columna) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$tabla, $columna]);
    return (int)$stmt->fetchColumn() > 0;
}
function agregarColumnaSiFalta(PDO $pdo, $tabla, $columna, $def) {
    if (!columnaExiste($pdo, $tabla, $columna)) $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$columna` $def");
}
function asegurarCatalogos(PDO $pdo) {
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
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_cotizaciones_catalogo_cotizacion (cotizacion),
        INDEX idx_cotizaciones_catalogo_cliente (cliente_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contenedores_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero VARCHAR(60) NOT NULL UNIQUE,
        serial_unidad VARCHAR(100) DEFAULT NULL,
        marca_equipo VARCHAR(100) DEFAULT NULL,
        modelo_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,
        anio_fabricacion VARCHAR(4) DEFAULT NULL,
        refrigerante VARCHAR(50) DEFAULT NULL,
        descripcion VARCHAR(220) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_contenedores_catalogo_numero (numero)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS maquinas_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serial_unidad VARCHAR(100) NOT NULL UNIQUE,
        marca_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,
        refrigerante VARCHAR(50) DEFAULT NULL,
        descripcion VARCHAR(220) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_maquinas_catalogo_serial (serial_unidad)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS generadores_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero VARCHAR(60) NOT NULL UNIQUE,
        serial_unidad VARCHAR(100) DEFAULT NULL,
        marca_equipo VARCHAR(100) NOT NULL DEFAULT 'THERMO KING',
        controlador VARCHAR(40) NOT NULL,
        descripcion VARCHAR(220) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_generadores_serial (serial_unidad),
        INDEX idx_generadores_controlador (controlador)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function asegurarPreliminares(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS inspecciones_preliminares (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tecnico_id INT NOT NULL,
        cliente VARCHAR(150) DEFAULT NULL,
        cotizacion VARCHAR(100) DEFAULT NULL,
        odoo_ticket_ref VARCHAR(120) DEFAULT NULL,
        trabajo VARCHAR(150) DEFAULT NULL,
        modalidad_comercial VARCHAR(40) DEFAULT NULL,
        tipo_instalacion VARCHAR(80) DEFAULT NULL,
        tipo_equipo VARCHAR(30) DEFAULT NULL,
        tamano_contenedor VARCHAR(60) DEFAULT NULL,
        numero_equipo VARCHAR(100) DEFAULT NULL,
        serie_unidad VARCHAR(100) DEFAULT NULL,
        marca_equipo VARCHAR(100) DEFAULT NULL,
        modelo_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,
        anio_fabricacion VARCHAR(4) DEFAULT NULL,
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
        evidencias_json LONGTEXT DEFAULT NULL,
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
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'odoo_ticket_ref', "VARCHAR(120) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'modalidad_comercial', "VARCHAR(40) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tipo_instalacion', "VARCHAR(80) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tipo_equipo', "VARCHAR(30) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tamano_contenedor', "VARCHAR(60) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'modelo_equipo', "VARCHAR(100) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'anio_fabricacion', "VARCHAR(4) DEFAULT NULL");
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
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'token_continuacion', "VARCHAR(120) DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'informe_id', "INT DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'finalizado_en', "DATETIME DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'hora_inicio_servicio', "DATETIME DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'hora_fin_servicio', "DATETIME DEFAULT NULL");
    agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'evidencias_json', "LONGTEXT DEFAULT NULL");
}
function obtenerOCrearCliente(PDO $pdo, $nombre) {
    $nombre = limpiarPlano($nombre, 180);
    if ($nombre === '') return null;
    $stmt = $pdo->prepare('SELECT id FROM clientes_catalogo WHERE LOWER(nombre) = LOWER(?) LIMIT 1');
    $stmt->execute([$nombre]);
    $id = (int)$stmt->fetchColumn();
    if ($id > 0) {
        $pdo->prepare('UPDATE clientes_catalogo SET activo = 1, nombre = ? WHERE id = ?')->execute([$nombre, $id]);
        return $id;
    }
    $stmt = $pdo->prepare('INSERT INTO clientes_catalogo (nombre, activo) VALUES (?, 1)');
    $stmt->execute([$nombre]);
    return (int)$pdo->lastInsertId();
}
function upsertCatalogosDesdePreliminar(PDO $pdo, $cliente, $cotizacion, $numeroEquipo, $serial, $marca, $controlador, $refrigerante, $tipoEquipo) {
    asegurarCatalogos($pdo);
    $creados = [];
    // Clientes y reportes son universales para reefer y generador.
    $clienteId = obtenerOCrearCliente($pdo, $cliente);
    if ($clienteId) $creados[] = 'cliente';
    $reporte = preg_replace('/\D+/', '', (string)$cotizacion);
    if ($reporte !== '') {
        $stmt = $pdo->prepare('INSERT INTO cotizaciones_catalogo (cotizacion, cliente_id, cliente_nombre, descripcion, activo) VALUES (?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE cliente_id = VALUES(cliente_id), cliente_nombre = VALUES(cliente_nombre), activo = 1');
        $stmt->execute([$reporte, $clienteId ?: null, limpiarPlano($cliente, 180), 'Creado automáticamente desde inspección preliminar']);
        $creados[] = 'reporte';
    }
    // El contenedor puede seguir guardándose como referencia reefer.
    if (strcasecmp((string)$tipoEquipo, 'Genset') !== 0) {
        $num = limpiarCodigo($numeroEquipo, 60);
        if ($num !== '') {
            $stmt = $pdo->prepare('INSERT INTO contenedores_catalogo (numero, descripcion, activo) VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE activo = 1');
            $stmt->execute([$num, 'Creado automáticamente desde inspección preliminar']);
            $creados[] = 'contenedor';
        }
    }
    // Marca/controlador de máquinas y generadores se administran únicamente desde el panel.
    return $creados;
}

try {
    require __DIR__ . '/db.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) fail('No se encontró la conexión PDO en db.php.');
} catch (Throwable $e) { fail('Error cargando db.php: ' . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); fail('Método no permitido.'); }

$tecnico_id = (int)($_POST['tecnico_id'] ?? 0);
if ($tecnico_id <= 0) fail('Debes elegir un técnico.');

$cliente = limpiarPlano(postTxt('cliente'), 150);
$cotizacion = preg_replace('/\D+/', '', postTxt('cotizacion'));
$odoo_ticket_ref = preg_replace('/\D+/', '', postTxt('odoo_ticket_ref'));
$trabajo = limpiarPlano(postTxt('trabajo'), 150);
$modalidad_comercial = limpiarPlano(postTxt('modalidad_comercial'), 40);
$tipo_instalacion = limpiarPlano(postTxt('tipo_instalacion'), 80);
$tipo_equipo = limpiarPlano(postTxt('tipo_equipo'), 30);
if ($tipo_equipo === '') $tipo_equipo = strtoupper(postTxt('marca_equipo')) === 'GENSET' ? 'Genset' : 'Reefer';
$tamano_contenedor = limpiarPlano(postTxt('tamano_contenedor'), 60);
if (strcasecmp($tipo_equipo, 'Genset') === 0) $tamano_contenedor = 'No aplica';
$numero_equipo = limpiarCodigo(postTxt('numero_equipo'), 60);
$serie_unidad = limpiarCodigo(postTxt('serie_unidad'), 100);
$marca_equipo = limpiarPlano(postTxt('marca_equipo'), 100);
$modelo_equipo = limpiarPlano(postTxt('modelo_equipo'), 100);
$controlador = limpiarPlano(postTxt('controlador'), 100);
$anio_fabricacion = preg_replace('/\D+/', '', postTxt('anio_fabricacion'));
$anio_fabricacion = substr($anio_fabricacion, 0, 4);
$refrigerante = limpiarPlano(postTxt('refrigerante'), 50);

$set_point = postTemp('set_point', -35, 30, 'Set point');
$temperatura_ambiente = postTemp('temperatura_ambiente', -10, 60, 'Temperatura ambiente');
$retorno_aire = postTemp('retorno_aire', -40, 60, 'Retorno de aire');
$suministro_aire = postTemp('suministro_aire', -50, 60, 'Suministro de aire');

$presion_alta = limpiarPlano(postTxt('presion_alta'), 50);
$presion_baja = limpiarPlano(postTxt('presion_baja'), 50);
$alarma_encontrada = limpiarPlano(postTxt('alarma_encontrada'), 180);
$genset_horometro_inicial = postNumeroNullable('genset_horometro_inicial', 0, 9999999, 'Horómetro inicial');
$genset_voltaje_bateria_inicial = limpiarPlano(postTxt('genset_voltaje_bateria_inicial'), 50);
$genset_nivel_combustible_inicial = limpiarPlano(postTxt('genset_nivel_combustible_inicial'), 40);
$genset_nivel_aceite_inicial = limpiarPlano(postTxt('genset_nivel_aceite_inicial'), 50);
$genset_refrigerante_motor_inicial = limpiarPlano(postTxt('genset_refrigerante_motor_inicial'), 60);
$genset_arranque_inicial = limpiarPlano(postTxt('genset_arranque_inicial'), 80);
$genset_frecuencia_inicial = null; // Campo retirado del formulario V19
$genset_presion_aceite_inicial = ''; // Campo retirado del formulario V19

$voltaje_l1_l2 = validarVoltaje(postTxt('voltaje_l1_l2'), 'Voltaje L1-L2');
$voltaje_l2_l3 = validarVoltaje(postTxt('voltaje_l2_l3'), 'Voltaje L2-L3');
$voltaje_l1_l3 = validarVoltaje(postTxt('voltaje_l1_l3'), 'Voltaje L1-L3');

$estado_inicial = limpiarPlano(postTxt('estado_inicial'), 150);
$observacion_inicial = postTxt('observacion_inicial');
$ubicacion_texto = postTxt('ubicacion_texto');
$latitud = postTxt('latitud');
$longitud = postTxt('longitud');
$evidencias_json = trim((string)($_POST['evidencias_preliminares_json'] ?? ''));
if ($evidencias_json !== '') {
    $ev = json_decode($evidencias_json, true);
    if (!is_array($ev)) $evidencias_json = '[]';
}

if ($cliente === '') fail('Falta el cliente.');
if ($cotizacion === '') fail('Falta el N° de reporte.');
if (strlen($cotizacion) < 6 || strlen($cotizacion) > 15) fail('El N° de reporte parece incorrecto. Debe tener entre 6 y 15 números.');
if ($odoo_ticket_ref === '' || strlen($odoo_ticket_ref) > 15) fail('El ticket de Odoo debe contener entre 1 y 15 números.');
if ($numero_equipo === '') fail('Falta el contenedor/equipo.');
if ($serie_unidad === '') fail('Falta el serial de la unidad.');
if ($marca_equipo === '') fail('Falta la marca del equipo.');
if ($tipo_equipo === '') fail('Falta seleccionar el tipo de equipo.');
if (!in_array($modalidad_comercial, ['Alquiler', 'Venta'], true)) fail('Falta seleccionar la modalidad comercial.');
if (strcasecmp($tipo_equipo, 'Genset') === 0) {
    $tipo_instalacion = '';
    $tamano_contenedor = 'No aplica';
    $modelo_equipo = '';
    $anio_fabricacion = '';
    if (strcasecmp($marca_equipo, 'THERMO KING') !== 0) fail('Para generadores la marca disponible es THERMO KING.');
    if (!in_array(strtoupper($controlador), ['SG-3000', 'SG-5000'], true)) fail('Para generadores selecciona el controlador SG-3000 o SG-5000.');
} else {
    if ($tipo_instalacion === '') fail('Falta seleccionar el tipo de instalación de la máquina reefer.');
    if ($tamano_contenedor === '') fail('Falta seleccionar el tamaño del contenedor.');
    if (strcasecmp($marca_equipo, 'GENSET') === 0) fail('GENSET no es una marca válida para una máquina reefer.');
    if ($anio_fabricacion !== '' && !preg_match('/^(19|20)\d{2}$/', $anio_fabricacion)) fail('El año de fabricación debe tener 4 dígitos.');
}
if (stripos($estado_inicial, 'Con alarma') !== false && $alarma_encontrada === '') fail('Falta indicar la alarma encontrada.');
if (strcasecmp($tipo_equipo, 'Genset') === 0) {
    if ($genset_horometro_inicial === null) fail('Falta el horómetro inicial del genset.');
    if ($genset_voltaje_bateria_inicial === '') fail('Falta el voltaje inicial de batería del genset.');
    if ($genset_nivel_combustible_inicial === '') fail('Falta el nivel inicial de combustible del genset.');
    if ($genset_nivel_aceite_inicial === '') fail('Falta el nivel inicial de aceite del genset.');
    if ($genset_refrigerante_motor_inicial === '') fail('Falta el estado inicial del refrigerante del motor.');
    if ($genset_arranque_inicial === '') fail('Falta el resultado de la prueba de arranque inicial.');
}
if ($ubicacion_texto === '') fail('Falta la ubicación.');

$tecnico_nombre = 'Técnico';
try {
    $stmtTec = $pdo->prepare('SELECT nombre FROM tecnicos WHERE id = ? LIMIT 1');
    $stmtTec->execute([$tecnico_id]);
    $rowTec = $stmtTec->fetch(PDO::FETCH_ASSOC);
    if ($rowTec && !empty($rowTec['nombre'])) $tecnico_nombre = $rowTec['nombre'];
} catch (Throwable $e) {}

$catalogosCreados = [];
try {
    asegurarPreliminares($pdo);
    $catalogosCreados = upsertCatalogosDesdePreliminar($pdo, $cliente, $cotizacion, $numero_equipo, $serie_unidad, $marca_equipo, $controlador, $refrigerante, $tipo_equipo);

    $creado_en = date('Y-m-d H:i:s');
    $token_continuacion = bin2hex(random_bytes(24));

    $stmt = $pdo->prepare("INSERT INTO inspecciones_preliminares (
        tecnico_id, cliente, cotizacion, odoo_ticket_ref, trabajo,
        modalidad_comercial, tipo_instalacion, tipo_equipo, tamano_contenedor,
        numero_equipo, serie_unidad, marca_equipo, modelo_equipo, controlador, anio_fabricacion, refrigerante,
        set_point, temperatura_ambiente, retorno_aire, suministro_aire,
        presion_alta, presion_baja, alarma_encontrada,
        genset_horometro_inicial, genset_voltaje_bateria_inicial,
        genset_nivel_combustible_inicial, genset_nivel_aceite_inicial,
        genset_refrigerante_motor_inicial, genset_arranque_inicial,
        genset_frecuencia_inicial, genset_presion_aceite_inicial,
        voltaje_l1_l2, voltaje_l2_l3, voltaje_l1_l3,
        estado_inicial, observacion_inicial, evidencias_json,
        ubicacion_texto, latitud, longitud,
        creado_en, hora_inicio_servicio, estado, token_continuacion
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, ?, 'abierto', ?
    )");
    $stmt->execute([
        $tecnico_id, $cliente, $cotizacion, $odoo_ticket_ref, $trabajo,
        $modalidad_comercial, $tipo_instalacion, $tipo_equipo, $tamano_contenedor,
        $numero_equipo, $serie_unidad, $marca_equipo, $modelo_equipo, $controlador, $anio_fabricacion, $refrigerante,
        $set_point, $temperatura_ambiente, $retorno_aire, $suministro_aire,
        $presion_alta, $presion_baja, $alarma_encontrada,
        $genset_horometro_inicial, $genset_voltaje_bateria_inicial,
        $genset_nivel_combustible_inicial, $genset_nivel_aceite_inicial,
        $genset_refrigerante_motor_inicial, $genset_arranque_inicial,
        $genset_frecuencia_inicial, $genset_presion_aceite_inicial,
        $voltaje_l1_l2, $voltaje_l2_l3, $voltaje_l1_l3,
        $estado_inicial, $observacion_inicial, $evidencias_json !== '' ? $evidencias_json : null,
        $ubicacion_texto, $latitud, $longitud,
        $creado_en, $creado_en, $token_continuacion
    ]);
    $pre_id = (int)$pdo->lastInsertId();
} catch (Throwable $e) {
    fail('Error al guardar la inspección preliminar: ' . $e->getMessage());
}

$fecha_mostrar = date('d/m/Y H:i:s');
$temperaturas = 'Amb: ' . ($temperatura_ambiente !== null ? $temperatura_ambiente . '°C' : '-') .
    ' | Ret: ' . ($retorno_aire !== null ? $retorno_aire . '°C' : '-') .
    ' | Sum: ' . ($suministro_aire !== null ? $suministro_aire . '°C' : '-') .
    ' | Set: ' . ($set_point !== null ? $set_point . '°C' : '-');
$continuar_url = 'https://zgroupinformes.com/index.php?token=' . urlencode($token_continuacion);
$notas = [];

try {
    $telegramLib = __DIR__ . '/telegram_lib.php';
    if (file_exists($telegramLib)) {
        require_once $telegramLib;
        $msg = "🟡 INSPECCIÓN PRELIMINAR REGISTRADA\n\n" .
            "⚠️ SOLO PRELIMINAR - NO ES INFORME FINAL\n\n" .
            "👷 Técnico: {$tecnico_nombre}\n🏢 Cliente: {$cliente}\n📄 Reporte: {$cotizacion}\n" .
            "📦 Equipo: {$numero_equipo}\n🔢 Serial: {$serie_unidad}\n" .
            "🏷 Tipo: {$tipo_equipo}" . (strcasecmp($tipo_equipo, 'Genset') === 0 ? "" : " | Tamaño: {$tamano_contenedor}") . "\n" .
            "🛠 Trabajo previsto: " . ($trabajo !== '' ? $trabajo : 'Servicio técnico pendiente de cierre') . "\n" .
            "📋 Estado inicial: " . ($estado_inicial !== '' ? $estado_inicial : 'No especificado') . "\n" .
            "🌡 Temperaturas: {$temperaturas}\n📅 Fecha preliminar: {$fecha_mostrar}\n📍 Ubicación: {$ubicacion_texto}\n\n" .
            "Registro preliminar realizado antes de la intervención técnica.\n\n" .
            "🔎 Ver panel:\nhttps://zgroupinformes.com/panel.php\n\n▶️ Continuar informe final:\n{$continuar_url}";
        if (function_exists('enviarTelegramTexto')) $notas['telegram'] = enviarTelegramTexto($msg);
        elseif (function_exists('enviarTelegramMensaje')) $notas['telegram'] = enviarTelegramMensaje($msg);
        elseif (function_exists('enviarTelegramNuevoInforme')) $notas['telegram'] = enviarTelegramNuevoInforme($tecnico_nombre, $cliente, $cotizacion, 'INSPECCIÓN PRELIMINAR - NO ES INFORME FINAL', $fecha_mostrar, '', $ubicacion_texto);
    }
} catch (Throwable $e) { $notas['telegram_error'] = $e->getMessage(); }

try {
    $whatsappLib = __DIR__ . '/whatsapp_lib.php';
    if (file_exists($whatsappLib)) {
        require_once $whatsappLib;

        // Plantilla exclusiva para la inspección preliminar.
        // No usa PDF, solo el botón estático "Ver panel".
        if (function_exists('enviarWhatsAppPreliminar')) {
            $notas['whatsapp'] = enviarWhatsAppPreliminar(
                $tecnico_nombre,
                $cliente,
                $cotizacion,
                ($trabajo !== '' ? $trabajo : 'Servicio técnico pendiente de cierre'),
                ($estado_inicial !== '' ? $estado_inicial : 'No especificado'),
                $temperaturas,
                $fecha_mostrar,
                $ubicacion_texto
            );
        } else {
            $notas['whatsapp'] = [
                'ok' => false,
                'error' => 'No existe la función enviarWhatsAppPreliminar() en whatsapp_lib.php.'
            ];
        }
    } else {
        $notas['whatsapp'] = [
            'ok' => false,
            'error' => 'No existe whatsapp_lib.php en la carpeta del sistema.'
        ];
    }
} catch (Throwable $e) { $notas['whatsapp_error'] = $e->getMessage(); }

@file_put_contents(__DIR__ . '/preinspeccion_debug.log', '[' . date('Y-m-d H:i:s') . '] ID ' . $pre_id . ' | catalogos=' . json_encode($catalogosCreados, JSON_UNESCAPED_UNICODE) . ' | notas=' . json_encode($notas, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

responderJSON([
    'ok' => true,
    'pre_id' => $pre_id,
    'token' => $token_continuacion,
    'continuar_url' => $continuar_url,
    'hora_inicio_servicio' => $creado_en,
    'catalogos_creados' => array_values(array_unique($catalogosCreados)),
    'message' => 'Inspección preliminar guardada correctamente. Los datos nuevos también fueron registrados en el catálogo del panel.',
    'notificaciones' => $notas
]);
