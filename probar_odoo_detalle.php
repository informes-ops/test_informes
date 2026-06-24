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

function many2oneNombre($value): string {
    if (is_array($value)) {
        if (isset($value[1])) return trim((string)$value[1]);
        if (isset($value['1'])) return trim((string)$value['1']);
    }
    return trim((string)$value);
}

$ticketRef = trim((string)($_GET['ticket_ref'] ?? ''));
$result = null;
$error = '';

if ($ticketRef !== '') {
    try {
        require_once __DIR__ . '/odoo_config.php';
        require_once __DIR__ . '/odoo_lib.php';

        if (!defined('ODOO_API_KEY') || ODOO_API_KEY === '' || ODOO_API_KEY === 'PEGA_AQUI_TU_API_KEY_NUEVA') {
            throw new RuntimeException('Todavía falta colocar la API Key nueva en odoo_config.php.');
        }

        $uid = zgOdooAuthenticate();
        $model = (string)ODOO_TICKET_MODEL;
        $field = (string)ODOO_TICKET_REF_FIELD;

        $fieldsInfo = zgOdooExecuteKw($uid, $model, 'fields_get', [], [
            'attributes' => ['string', 'type'],
        ]);

        if (!is_array($fieldsInfo) || !array_key_exists($field, $fieldsInfo)) {
            throw new RuntimeException("El campo técnico '$field' no existe o no es visible para este usuario.");
        }

        $wantedFields = [$field, 'name', 'team_id', 'stage_id', 'user_id', 'partner_id', 'active'];
        $readFields = [];
        foreach ($wantedFields as $candidate) {
            if (isset($fieldsInfo[$candidate])) $readFields[] = $candidate;
        }

        $rows = zgOdooExecuteKw($uid, $model, 'search_read', [
            [[$field, '=', $ticketRef]],
        ], [
            'fields' => $readFields,
            'limit' => 5,
        ]);

        if (!is_array($rows)) $rows = [];

        $tickets = [];
        foreach ($rows as $row) {
            if (!is_array($row)) continue;
            $tickets[] = [
                'id' => (int)($row['id'] ?? 0),
                'ref' => (string)($row[$field] ?? ''),
                'name' => (string)($row['name'] ?? ''),
                'team' => many2oneNombre($row['team_id'] ?? ''),
                'stage' => many2oneNombre($row['stage_id'] ?? ''),
                'user' => many2oneNombre($row['user_id'] ?? ''),
                'partner' => many2oneNombre($row['partner_id'] ?? ''),
                'active' => array_key_exists('active', $row) ? (bool)$row['active'] : true,
            ];
        }

        $result = [
            'uid' => $uid,
            'field' => $field,
            'tickets' => $tickets,
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
<title>Detalle de ticket Odoo</title>
<style>
*{box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#eef3f8;color:#14233a;padding:24px;margin:0}
.box{max-width:820px;margin:auto;background:#fff;border-radius:18px;padding:24px;box-shadow:0 14px 40px #1232}
.ok{background:#e9f8ee;color:#176b34;border:1px solid #a9dfba;padding:14px;border-radius:12px;margin-top:14px}
.err{background:#fff0f0;color:#a32020;border:1px solid #f1b4b4;padding:14px;border-radius:12px;margin-top:14px}
input,button{font:inherit;padding:12px;border-radius:10px}
input{border:1px solid #ccd8e6;width:min(320px,100%)}
button{border:0;background:#1f6fc4;color:#fff;font-weight:700;cursor:pointer}
code{background:#eef3f8;padding:2px 5px;border-radius:5px}
.ticket{margin-top:14px;border:1px solid #d7e2ee;border-radius:14px;padding:16px;background:#fbfdff}
.ticket h2{margin:0 0 10px;font-size:20px}
.grid{display:grid;grid-template-columns:180px 1fr;gap:8px 12px}
.label{font-weight:700;color:#4b6078}
.team{display:inline-block;background:#eaf2ff;color:#174f92;border:1px solid #bfd4f2;border-radius:999px;padding:6px 10px;font-weight:700}
.note{background:#fff8df;border:1px solid #f0d782;border-radius:12px;padding:12px;margin-top:14px;color:#694e00}
@media(max-width:600px){.grid{grid-template-columns:1fr}.label{margin-top:8px}}
</style>
</head>
<body>
<div class="box">
<h1>Ubicar ticket en Odoo</h1>
<p>Esta prueba indica el equipo exacto del ticket. No adjunta ni modifica archivos.</p>

<form method="get">
<input name="ticket_ref" value="<?= h($ticketRef) ?>" placeholder="Ejemplo: 1731" required>
<button type="submit">Buscar ticket</button>
</form>

<?php if ($error !== ''): ?>
<div class="err"><b>Error:</b> <?= h($error) ?></div>
<?php elseif (is_array($result)): ?>
<div class="ok">
<b>Autenticación correcta.</b> UID: <?= h($result['uid']) ?><br>
Campo usado: <code><?= h($result['field']) ?></code>
</div>

<?php if (!$result['tickets']): ?>
<div class="err">No se encontró un ticket con esa referencia.</div>
<?php else: ?>
<?php foreach ($result['tickets'] as $ticket): ?>
<div class="ticket">
<h2><?= h($ticket['name'] !== '' ? $ticket['name'] : ('Ticket ' . $ticket['ref'])) ?></h2>
<div class="grid">
<div class="label">Referencia</div><div><?= h($ticket['ref']) ?></div>
<div class="label">ID interno</div><div><?= h($ticket['id']) ?></div>
<div class="label">Equipo</div><div><span class="team"><?= h($ticket['team'] !== '' ? $ticket['team'] : 'Sin equipo visible') ?></span></div>
<div class="label">Etapa</div><div><?= h($ticket['stage'] !== '' ? $ticket['stage'] : 'No disponible') ?></div>
<div class="label">Asignado a</div><div><?= h($ticket['user'] !== '' ? $ticket['user'] : 'Sin asignar') ?></div>
<div class="label">Cliente</div><div><?= h($ticket['partner'] !== '' ? $ticket['partner'] : 'No disponible') ?></div>
<div class="label">Activo</div><div><?= $ticket['active'] ? 'Sí' : 'No' ?></div>
</div>
</div>
<?php endforeach; ?>

<div class="note">
<b>Qué hacer:</b> vuelve a “Soporte al cliente” y pulsa el botón morado <b>Tickets</b> dentro del equipo que aparece arriba. Luego quita filtros y busca la referencia.
</div>
<?php endif; ?>
<?php endif; ?>

<p><a href="panel.php">Volver al panel</a></p>
</div>
</body>
</html>
