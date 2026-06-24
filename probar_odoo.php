<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (empty($_SESSION['panel_ok'])) {
    http_response_code(403);
    exit('Primero inicia sesión en panel.php y luego vuelve a abrir esta prueba.');
}

function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$ticketRef = trim((string)($_GET['ticket_ref'] ?? ''));
$result = null;
$error = '';

if ($ticketRef !== '') {
    try {
        require_once __DIR__ . '/odoo_config.php';
        require_once __DIR__ . '/odoo_lib.php';

        if (ODOO_API_KEY === '' || ODOO_API_KEY === 'PEGA_AQUI_TU_API_KEY_NUEVA') {
            throw new RuntimeException('Todavía falta colocar la API Key nueva en odoo_config.php.');
        }

        $uid = zgOdooAuthenticate();
        $model = (string)ODOO_TICKET_MODEL;
        $field = (string)ODOO_TICKET_REF_FIELD;
        $fields = zgOdooExecuteKw($uid, $model, 'fields_get', [], [
            'attributes' => ['string', 'type'],
        ]);
        if (!is_array($fields) || !array_key_exists($field, $fields)) {
            throw new RuntimeException("El campo técnico '$field' no existe o no es visible para este usuario.");
        }
        $ids = zgOdooExecuteKw($uid, $model, 'search', [
            [[$field, '=', $ticketRef]],
        ], ['limit' => 5]);

        $result = [
            'uid' => $uid,
            'field' => $field,
            'ticket_ids' => is_array($ids) ? $ids : [],
        ];
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Prueba Odoo XML-RPC</title>
<style>
body{font-family:Arial,sans-serif;background:#eef3f8;color:#14233a;padding:24px}.box{max-width:720px;margin:auto;background:#fff;border-radius:18px;padding:24px;box-shadow:0 14px 40px #1232}.ok{background:#e9f8ee;color:#176b34;border:1px solid #a9dfba;padding:14px;border-radius:12px}.err{background:#fff0f0;color:#a32020;border:1px solid #f1b4b4;padding:14px;border-radius:12px}input,button{font:inherit;padding:12px;border-radius:10px}input{border:1px solid #ccd8e6;width:min(320px,100%)}button{border:0;background:#1f6fc4;color:#fff;font-weight:700;cursor:pointer}code{background:#eef3f8;padding:2px 5px;border-radius:5px}
</style>
</head>
<body><div class="box">
<h1>Prueba de conexión Odoo XML-RPC</h1>
<p>Esta prueba solo autentica y busca el ticket. No adjunta ni modifica archivos.</p>
<form method="get">
<input name="ticket_ref" value="<?= h($ticketRef) ?>" placeholder="Ejemplo: 1732" required>
<button type="submit">Probar conexión</button>
</form>
<?php if ($error !== ''): ?>
<p class="err"><b>Error:</b> <?= h($error) ?></p>
<?php elseif (is_array($result)): ?>
<div class="ok">
<p><b>Autenticación correcta.</b> UID: <?= h($result['uid']) ?></p>
<p>Campo usado: <code><?= h($result['field']) ?></code></p>
<?php if ($result['ticket_ids']): ?>
<p>Ticket encontrado. ID interno: <b><?= h(implode(', ', $result['ticket_ids'])) ?></b></p>
<?php else: ?>
<p>La conexión funciona, pero no se encontró un ticket con esa referencia.</p>
<?php endif; ?>
</div>
<?php endif; ?>
<p><a href="panel.php">Volver al panel</a></p>
</div></body></html>
