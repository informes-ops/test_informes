<?php
session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
date_default_timezone_set('America/Lima');

function zgSyncRespond(array $data, int $status = 200): void {
    while (ob_get_level()) ob_end_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (empty($_SESSION['panel_ok'])) {
    zgSyncRespond(['ok' => false, 'error' => 'La sesión del panel venció.'], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    zgSyncRespond(['ok' => false, 'error' => 'Método no permitido.'], 405);
}
$csrf = (string)($_POST['csrf'] ?? '');
$sessionCsrf = (string)($_SESSION['panel_csrf'] ?? '');
if ($sessionCsrf === '' || !hash_equals($sessionCsrf, $csrf)) {
    zgSyncRespond(['ok' => false, 'error' => 'Token de seguridad no válido. Recarga el panel.'], 403);
}

try {
    require __DIR__ . '/db.php';
    require_once __DIR__ . '/odoo_lib.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) throw new RuntimeException('No se encontró la conexión PDO.');

    zgOdooEnsureColumns($pdo);
    $id = (int)($_POST['informe_id'] ?? 0);
    if ($id <= 0) zgSyncRespond(['ok' => false, 'error' => 'Informe no válido.'], 400);

    $st = $pdo->prepare('SELECT id, orden, odoo_ticket_ref, archivo, COALESCE(odoo_attachment_id,0) AS odoo_attachment_id FROM informes WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    $inf = $st->fetch(PDO::FETCH_ASSOC);
    if (!$inf) zgSyncRespond(['ok' => false, 'error' => 'El informe ya no existe.'], 404);

    $archivo = basename((string)$inf['archivo']);
    if ($archivo === '' || !preg_match('/^[A-Za-z0-9._-]+\.pdf$/i', $archivo)) {
        zgSyncRespond(['ok' => false, 'error' => 'El nombre del PDF no es seguro.'], 400);
    }
    $path = __DIR__ . '/informes/' . $archivo;
    $result = zgOdooSyncInforme(
        $pdo,
        $id,
        trim((string)($inf['odoo_ticket_ref'] ?? '')) !== '' ? (string)$inf['odoo_ticket_ref'] : (string)$inf['orden'],
        $path,
        $archivo,
        (int)$inf['odoo_attachment_id']
    );

    zgSyncRespond($result, $result['ok'] ? 200 : 422);
} catch (Throwable $e) {
    zgSyncRespond(['ok' => false, 'error' => $e->getMessage()], 500);
}
