<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

function limpiar($s) {
    $s = trim((string)$s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return $s;
}
function normtxt($s) {
    $s = limpiar($s);
    $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($ascii !== false) $s = strtolower($ascii);
    $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(60) DEFAULT NULL,
        detalle VARCHAR(220) NOT NULL,
        unidad VARCHAR(40) DEFAULT NULL,
        pendiente_revision TINYINT(1) NOT NULL DEFAULT 0,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_repuestos_catalogo_codigo (codigo),
        INDEX idx_repuestos_catalogo_detalle (detalle)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    try { $pdo->exec("ALTER TABLE repuestos_catalogo MODIFY codigo VARCHAR(60) NULL DEFAULT NULL"); } catch (Throwable $e) {}
    try { $pdo->exec("ALTER TABLE repuestos_catalogo ADD COLUMN pendiente_revision TINYINT(1) NOT NULL DEFAULT 0"); } catch (Throwable $e) {}

    $txt = (string)($_POST['repuestos'] ?? '');
    $lineas = preg_split('/\r?\n/', $txt);
    $agregados = [];
    $omitidos = [];

    $existentes = $pdo->query("SELECT id, COALESCE(codigo,'') codigo, detalle FROM repuestos_catalogo WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($lineas as $linea) {
        $linea = limpiar($linea);
        if ($linea === '') continue;

        $parts = array_map('limpiar', explode('|', $linea));
        $codigo = '';
        $detalle = '';
        if (count($parts) >= 3) {
            $codigo = $parts[0] === '-' ? '' : $parts[0];
            $detalle = implode(' | ', array_slice($parts, 1, -1));
        } elseif (count($parts) >= 2) {
            $codigo = $parts[0] === '-' ? '' : $parts[0];
            $detalle = $parts[1];
        } else {
            $detalle = $linea;
        }
        $codigo = limpiar($codigo);
        $detalle = limpiar($detalle);
        if ($detalle === '') continue;

        $duplicado = false;
        foreach ($existentes as $ex) {
            if ($codigo !== '' && limpiar($ex['codigo'] ?? '') !== '' && normtxt($codigo) === normtxt($ex['codigo'])) { $duplicado = true; break; }
            if (normtxt($detalle) === normtxt($ex['detalle'] ?? '')) { $duplicado = true; break; }
        }
        if ($duplicado) { $omitidos[] = $detalle; continue; }

        $stmt = $pdo->prepare("INSERT INTO repuestos_catalogo (codigo, detalle, unidad, pendiente_revision, activo) VALUES (?, ?, '', 1, 1)");
        $stmt->execute([$codigo !== '' ? $codigo : null, $detalle]);
        $existentes[] = ['id'=>$pdo->lastInsertId(), 'codigo'=>$codigo, 'detalle'=>$detalle];
        $agregados[] = $detalle;
    }

    echo json_encode(['ok'=>true, 'agregados'=>$agregados, 'omitidos'=>$omitidos], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
