<?php
/* ============================================================
   Endpoint de alarmas del panel ZGROUP.
   Revisa nuevos informes y cambios administrativos.
   Solo responde si el supervisor inició sesión en panel.php.
   ============================================================ */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (empty($_SESSION['panel_ok'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

require __DIR__ . '/db.php';
date_default_timezone_set('America/Lima');

function asegurarTablaEventosPanel($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS panel_eventos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo VARCHAR(60) NOT NULL,
        titulo VARCHAR(160) NOT NULL,
        detalle TEXT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function tableHasColumn($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch();
    } catch (Throwable $e) {
        return false;
    }
}

function ejson($v) { return $v === null ? '' : (string)$v; }

try {
    asegurarTablaEventosPanel($pdo);

    $lastInformeId = max(0, (int)($_GET['last_informe_id'] ?? 0));
    $lastEventoId  = max(0, (int)($_GET['last_evento_id'] ?? 0));

    $eventos = [];
    $maxInformeId = 0;
    $maxEventoId = 0;

    // Nuevos informes guardados por técnicos.
    if (tableHasColumn($pdo, 'informes', 'id')) {
        $maxInformeId = (int)$pdo->query('SELECT COALESCE(MAX(id),0) FROM informes')->fetchColumn();

        if ($lastInformeId > 0) {
            $stmt = $pdo->prepare(
                'SELECT i.id, i.orden, i.cliente, i.trabajos, i.fecha, i.archivo, i.creado_en, t.nombre AS tecnico_nombre
                 FROM informes i
                 JOIN tecnicos t ON t.id = i.tecnico_id
                 WHERE i.id > ?
                 ORDER BY i.id ASC
                 LIMIT 15'
            );
            $stmt->execute([$lastInformeId]);
            $nuevos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($nuevos as $r) {
                $cot = trim(ejson($r['orden'] ?? ''));
                $cli = trim(ejson($r['cliente'] ?? ''));
                $tec = trim(ejson($r['tecnico_nombre'] ?? ''));
                $trab = trim(ejson($r['trabajos'] ?? ''));
                $partes = [];
                if ($tec !== '') $partes[] = 'Técnico: ' . $tec;
                if ($trab !== '') $partes[] = 'Trabajo: ' . $trab;
                if ($cot !== '') $partes[] = 'Cotización: ' . $cot;
                if ($cli !== '') $partes[] = 'Cliente: ' . $cli;

                $eventos[] = [
                    'id' => 'inf_' . (int)$r['id'],
                    'tipo' => 'nuevo_informe',
                    'titulo' => 'Nuevo informe técnico',
                    'detalle' => implode(' · ', $partes),
                    'fecha' => ejson($r['creado_en'] ?? ''),
                    'url' => 'informes/' . rawurlencode(ejson($r['archivo'] ?? ''))
                ];
            }
        }
    }

    // Cambios administrativos del panel: técnicos/trabajos.
    $maxEventoId = (int)$pdo->query('SELECT COALESCE(MAX(id),0) FROM panel_eventos')->fetchColumn();
    if ($lastEventoId > 0) {
        $stmt = $pdo->prepare(
            'SELECT id, tipo, titulo, detalle, creado_en
             FROM panel_eventos
             WHERE id > ?
             ORDER BY id ASC
             LIMIT 15'
        );
        $stmt->execute([$lastEventoId]);
        $cambios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cambios as $r) {
            $eventos[] = [
                'id' => 'evt_' . (int)$r['id'],
                'tipo' => ejson($r['tipo'] ?? 'cambio'),
                'titulo' => ejson($r['titulo'] ?? 'Cambio en el panel'),
                'detalle' => ejson($r['detalle'] ?? ''),
                'fecha' => ejson($r['creado_en'] ?? ''),
                'url' => ''
            ];
        }
    }

    echo json_encode([
        'ok' => true,
        'max_informe_id' => $maxInformeId,
        'max_evento_id' => $maxEventoId,
        'eventos' => $eventos,
        'server_time' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
