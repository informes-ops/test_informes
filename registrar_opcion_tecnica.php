<?php
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
function zgLimpiarTexto(string $texto, int $max = 220): string {
    $texto = strip_tags($texto);
    $texto = preg_replace('/\s+/u', ' ', $texto) ?? $texto;
    $texto = trim($texto);
    if (function_exists('mb_substr')) $texto = mb_substr($texto, 0, $max, 'UTF-8');
    else $texto = substr($texto, 0, $max);
    return trim($texto);
}
function zgClaveTrabajo(string $valor): string {
    $valor = strtolower(trim($valor));
    if (function_exists('iconv')) {
        $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        if (is_string($tmp) && $tmp !== '') $valor = $tmp;
    }
    $valor = preg_replace('/[^a-z0-9]+/', '_', $valor) ?? $valor;
    $valor = trim($valor, '_');
    return $valor !== '' ? substr($valor, 0, 100) : 'trabajo_general';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') zgResponder(['ok'=>false,'error'=>'Método no permitido.'],405);

$tipo = strtolower(trim((string)($_POST['tipo_equipo'] ?? '')));
$categoria = strtolower(trim((string)($_POST['categoria'] ?? '')));
$texto = zgLimpiarTexto((string)($_POST['texto'] ?? ''));
$trabajoNombre = zgLimpiarTexto((string)($_POST['trabajo_nombre'] ?? ''), 180);
$trabajoClave = zgClaveTrabajo((string)($_POST['trabajo_clave'] ?? $trabajoNombre));
$preId = (int)($_POST['preinspeccion_id'] ?? 0);
$token = trim((string)($_POST['token_continuacion'] ?? ''));
$tecnicoIdPost = (int)($_POST['tecnico_id'] ?? 0);

if (!in_array($tipo,['reefer','genset'],true)) zgResponder(['ok'=>false,'error'=>'Tipo de equipo no válido.'],422);
if (!in_array($categoria,['actividades','hallazgos'],true)) zgResponder(['ok'=>false,'error'=>'Categoría no válida.'],422);
$longitud = function_exists('mb_strlen') ? mb_strlen($texto,'UTF-8') : strlen($texto);
if ($longitud < 3) zgResponder(['ok'=>false,'error'=>'Escribe una opción más completa.'],422);

try {
    require __DIR__ . '/db.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) throw new RuntimeException('No se encontró la conexión PDO.');

    $autorizado = !empty($_SESSION['zgroup_tecnicos_ok']) || !empty($_SESSION['panel_ok']);
    $tecnicoId = $tecnicoIdPost > 0 ? $tecnicoIdPost : null;
    if (!$autorizado && ($preId > 0 || $token !== '')) {
        $sql = "SELECT id, tecnico_id FROM inspecciones_preliminares WHERE 1=1";
        $params = [];
        if ($preId > 0) { $sql .= " AND id = ?"; $params[] = $preId; }
        if ($token !== '') { $sql .= " AND token_continuacion = ?"; $params[] = $token; }
        $sql .= " LIMIT 1";
        $st = $pdo->prepare($sql); $st->execute($params);
        if ($pre = $st->fetch(PDO::FETCH_ASSOC)) {
            $autorizado = true;
            $tecnicoId = (int)($pre['tecnico_id'] ?? 0) ?: $tecnicoId;
        }
    }
    if (!$autorizado) zgResponder(['ok'=>false,'error'=>'La sesión venció o el enlace del servicio no es válido.'],403);

    $pdo->exec("CREATE TABLE IF NOT EXISTS opciones_tecnicas_por_trabajo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_equipo VARCHAR(20) NOT NULL,
        trabajo_clave VARCHAR(100) NOT NULL,
        trabajo_nombre VARCHAR(180) DEFAULT NULL,
        categoria VARCHAR(30) NOT NULL,
        texto VARCHAR(220) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_por_tecnico_id INT DEFAULT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        actualizado_en DATETIME DEFAULT NULL,
        UNIQUE KEY uq_opcion_por_trabajo (tipo_equipo, trabajo_clave, categoria, texto),
        INDEX idx_opcion_trabajo (tipo_equipo, trabajo_clave, categoria, activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $stmt = $pdo->prepare("INSERT INTO opciones_tecnicas_por_trabajo
        (tipo_equipo, trabajo_clave, trabajo_nombre, categoria, texto, activo, creado_por_tecnico_id, actualizado_en)
        VALUES (?, ?, ?, ?, ?, 1, ?, NOW())
        ON DUPLICATE KEY UPDATE activo=1,
            trabajo_nombre=VALUES(trabajo_nombre),
            creado_por_tecnico_id=COALESCE(creado_por_tecnico_id, VALUES(creado_por_tecnico_id)),
            actualizado_en=NOW()");
    $stmt->execute([$tipo,$trabajoClave,$trabajoNombre!==''?$trabajoNombre:null,$categoria,$texto,$tecnicoId]);

    $idStmt = $pdo->prepare("SELECT id, texto FROM opciones_tecnicas_por_trabajo
        WHERE tipo_equipo=? AND trabajo_clave=? AND categoria=? AND texto=? LIMIT 1");
    $idStmt->execute([$tipo,$trabajoClave,$categoria,$texto]);
    $fila = $idStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    zgResponder([
        'ok'=>true,
        'id'=>(int)($fila['id'] ?? 0),
        'tipo_equipo'=>$tipo,
        'trabajo_clave'=>$trabajoClave,
        'trabajo_nombre'=>$trabajoNombre,
        'categoria'=>$categoria,
        'texto'=>(string)($fila['texto'] ?? $texto),
        'persistente'=>true,
    ]);
} catch (Throwable $e) {
    zgResponder(['ok'=>false,'error'=>'No se pudo guardar la opción: '.$e->getMessage()],500);
}
