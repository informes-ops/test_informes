<?php
/* ZGROUP V41: guarda el avance de la segunda etapa de un servicio abierto. */
session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Lima');

function zgDraftRespond(array $data, int $status = 200): void {
    while (ob_get_level()) ob_end_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function zgDraftFail(string $message, int $status = 400): void {
    zgDraftRespond(['ok' => false, 'error' => $message], $status);
}

if (empty($_SESSION['panel_ok']) && empty($_SESSION['zgroup_tecnicos_ok'])) {
    zgDraftFail('La sesión venció. Vuelve a ingresar.', 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') zgDraftFail('Método no permitido.', 405);

try {
    require __DIR__ . '/db.php';
} catch (Throwable $e) {
    zgDraftFail('No se pudo cargar db.php: ' . $e->getMessage(), 500);
}
if (!isset($pdo) || !($pdo instanceof PDO)) zgDraftFail('No se encontró la conexión PDO.', 500);

$preId = (int)($_POST['preinspeccion_id'] ?? 0);
$token = trim((string)($_POST['token_continuacion'] ?? ''));
$datosJson = trim((string)($_POST['datos_json'] ?? ''));

if ($preId <= 0) zgDraftFail('La inspección preliminar no es válida.');
if ($datosJson === '') zgDraftFail('No se recibieron datos del avance.');
if (strlen($datosJson) > 60 * 1024 * 1024) zgDraftFail('El avance supera el tamaño permitido. Reduce la cantidad o el peso de las fotografías.');
$decoded = json_decode($datosJson, true);
if (!is_array($decoded)) zgDraftFail('El avance recibido no tiene un formato válido.');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS borradores_servicio (
        preinspeccion_id INT NOT NULL PRIMARY KEY,
        token_continuacion VARCHAR(120) DEFAULT NULL,
        datos_json LONGTEXT NOT NULL,
        actualizado_en DATETIME NOT NULL,
        INDEX idx_borrador_token (token_continuacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $st = $pdo->prepare('SELECT id, estado, token_continuacion, informe_id FROM inspecciones_preliminares WHERE id = ? LIMIT 1');
    $st->execute([$preId]);
    $pre = $st->fetch(PDO::FETCH_ASSOC);
    if (!$pre) zgDraftFail('La inspección preliminar ya no existe.', 404);
    if (strtolower(trim((string)($pre['estado'] ?? 'abierto'))) !== 'abierto' || (int)($pre['informe_id'] ?? 0) > 0) {
        zgDraftFail('El servicio ya fue cerrado y no admite un borrador nuevo.', 409);
    }

    $tokenDb = trim((string)($pre['token_continuacion'] ?? ''));
    if (empty($_SESSION['panel_ok']) && ($token === '' || $tokenDb === '' || !hash_equals($tokenDb, $token))) {
        zgDraftFail('El enlace de continuación no corresponde a este servicio.', 403);
    }

    $now = date('Y-m-d H:i:s');
    $up = $pdo->prepare("INSERT INTO borradores_servicio (preinspeccion_id, token_continuacion, datos_json, actualizado_en)
                         VALUES (?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE token_continuacion = VALUES(token_continuacion), datos_json = VALUES(datos_json), actualizado_en = VALUES(actualizado_en)");
    $up->execute([$preId, $tokenDb !== '' ? $tokenDb : ($token !== '' ? $token : null), $datosJson, $now]);

    zgDraftRespond([
        'ok' => true,
        'preinspeccion_id' => $preId,
        'actualizado_en' => $now,
        'message' => 'Avance guardado correctamente.'
    ]);
} catch (Throwable $e) {
    zgDraftFail('No se pudo guardar el avance: ' . $e->getMessage(), 500);
}
