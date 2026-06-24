<?php
/* ZGROUP V51: catálogo reefer ampliado con modelo, año y refrigerante automáticos */
/* ZGROUP V40: números de reporte agrupados dentro de cada cliente */
/* ZGROUP V33: catálogo general + seriales independientes de máquinas reefer */
/* ZGROUP V27: muestra y permite editar horario del servicio */
/* ZGROUP V22: edición completa de preliminares e informes desde panel */
/* ZGROUP V15: MP400 eliminado; solo MP3000, MP4000 y MP5000 */
/* ============================================================
   Panel PRIVADO: lista de técnicos con sus informes guardados.
   Acceso solo con clave (para ti y tus supervisores).
   ============================================================ */

session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

/* ZGROUP BUILD: PANEL_REAL_V9_20260620
   - Eliminación múltiple de preliminares e informes.
   - Selección total y por técnico.
   - Limpieza segura de datos de prueba con confirmación.
   - Conserva técnicos, clientes, catálogos y configuraciones. */
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}
if (empty($_SESSION['panel_csrf'])) {
    $_SESSION['panel_csrf'] = bin2hex(random_bytes(24));
}
$PANEL_CSRF = (string)$_SESSION['panel_csrf'];

// Configuración para enlace del grupo de Telegram.
// Agrega TG_GROUP_INVITE_LINK en telegram_config.php para mostrar el botón de invitación al grupo de supervisores.
$telegramConfigPath = __DIR__ . '/telegram_config.php';
if (file_exists($telegramConfigPath)) {
    require_once $telegramConfigPath;
}
$TG_GROUP_INVITE_LINK_PANEL = defined('TG_GROUP_INVITE_LINK') ? (string)TG_GROUP_INVITE_LINK : '';


// Carga segura de notificaciones push. Si falta algún archivo, el panel NO se cae con error 500.
$pushLibPath = __DIR__ . '/push_lib.php';
if (file_exists($pushLibPath)) {
    require_once $pushLibPath;
} else {
    if (!defined('ZGROUP_ONESIGNAL_APP_ID')) define('ZGROUP_ONESIGNAL_APP_ID', '');
    if (!function_exists('zgroup_enviar_push')) {
        function zgroup_enviar_push($titulo, $detalle = '', $url = 'panel.php', $data = []) {
            return ['ok' => false, 'error' => 'Falta subir push_lib.php'];
        }
    }
}

// >>> CAMBIA esta clave por una tuya (compártela solo con tus supervisores) <<<
$CLAVE_PANEL = '123456';

// Cerrar sesión
if (isset($_GET['salir'])) { session_destroy(); header('Location: panel.php'); exit; }

// Revisar la clave enviada
$login_error = false;
if (isset($_POST['clave'])) {
    if (hash_equals($CLAVE_PANEL, (string)$_POST['clave'])) {
        $_SESSION['panel_ok'] = true;
    } else {
        $login_error = true;
    }
}

// Sin sesión válida -> mostrar pantalla de acceso y detener
if (empty($_SESSION['panel_ok'])) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acceso · ZGROUP</title>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;display:grid;place-items:center;background:linear-gradient(rgba(18,30,52,.9),rgba(18,30,52,.96)),url('zgroup-bg.jpg') center/cover no-repeat;font-family:'Manrope',system-ui,sans-serif;padding:20px}
.box{background:#fff;border-radius:18px;box-shadow:0 18px 50px rgba(0,0,0,.32);padding:32px 28px;width:100%;max-width:360px;text-align:center}
.box img{height:46px;margin-bottom:18px}
.box h1{font-family:'Archivo';font-size:19px;color:#16263f;margin-bottom:4px}
.box p{font-size:13px;color:#5a6b80;margin-bottom:20px}
.box input{width:100%;font-size:15px;padding:13px 14px;border:1.5px solid #dde4ec;border-radius:11px;background:#f7fafd;margin-bottom:12px}
.box input:focus{outline:none;border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb;background:#fff}
.box button{width:100%;font-family:'Archivo';font-weight:700;font-size:15px;color:#fff;background:#1f6fc4;border:none;border-radius:11px;padding:13px;cursor:pointer}
.box button:hover{filter:brightness(1.05)}
.err{color:#e03131;font-size:13px;font-weight:600;margin-bottom:12px}

/* ---------- Notificaciones push reales ---------- */
.push-card{display:flex;align-items:center;justify-content:space-between;gap:14px;background:linear-gradient(135deg,#10213a,#1f6fc4);color:#fff;border:none;border-radius:20px;box-shadow:0 16px 38px rgba(31,111,196,.25);padding:17px 18px;margin-bottom:18px;overflow:hidden;position:relative}
.push-card::after{content:"";position:absolute;right:-80px;top:-80px;width:210px;height:210px;background:radial-gradient(circle,rgba(255,255,255,.20),transparent 70%);pointer-events:none}
.push-left{position:relative;z-index:1;display:flex;align-items:center;gap:14px;min-width:0}
.push-ic{width:48px;height:48px;border-radius:15px;background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.22);display:grid;place-items:center;font-size:23px;flex:none}
.push-title{font-family:'Archivo';font-weight:900;font-size:18px;line-height:1.1}
.push-sub{font-size:13.5px;color:#d9e8fb;font-weight:700;margin-top:3px}
.push-actions{position:relative;z-index:1;display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:flex-end}
.push-pill{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.23);border-radius:999px;padding:8px 11px;font-size:13px;font-weight:900;color:#fff}
.push-dot{width:11px;height:11px;border-radius:50%;background:#e03131;box-shadow:0 0 0 0 rgba(224,49,49,.45)}
.push-dot.on{background:#51cf66;box-shadow:0 0 0 6px rgba(81,207,102,.12)}
.push-switch{width:78px;height:42px;border:none;border-radius:999px;background:rgba(255,255,255,.32);padding:4px;cursor:pointer;box-shadow:inset 0 0 0 1px rgba(255,255,255,.25);transition:.18s;display:flex;align-items:center}
.push-switch span{width:34px;height:34px;border-radius:50%;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.22);transition:.18s;display:block}
.push-switch.on{background:#51cf66}
.push-switch.on span{transform:translateX(36px)}
.push-switch:disabled{opacity:.55;cursor:not-allowed}
@media(max-width:700px){.push-card{align-items:flex-start;flex-direction:column}.push-actions{width:100%;justify-content:space-between}.push-switch{width:82px;height:44px}.push-switch span{width:36px;height:36px}.push-switch.on span{transform:translateX(38px)}}

.catalog-detail{display:block!important;margin-top:6px!important;clear:both!important}\n</style>


<style id="zg-edit-report-panel-style">
.report-actions .edit-report{background:#fff7db!important;color:#8a5a00!important;border:1px solid #ffe08a!important}.report-actions .edit-report:hover{background:#ffefb8!important;transform:translateY(-1px)}
.report-actions .edit-time{background:#eaf4ff!important;color:#155293!important;border:1px solid #bdd9f5!important}.report-actions .edit-time:hover{background:#dbeeff!important;transform:translateY(-1px)}
.service-time-cell{min-width:160px;font-size:11.5px;line-height:1.45}.service-time-cell b{color:#17385d}.service-time-cell span{display:block;color:#60738a}
</style>


<style id="zg-panel-bulk-v9-style">
.bulk-danger-zone{margin:0 0 18px;background:linear-gradient(135deg,#fff7f7,#fff);border:1.5px solid #ffc9c9;border-radius:18px;padding:16px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:var(--shadow)}
.bulk-danger-zone h3{font-family:Archivo,sans-serif;font-size:17px;color:#8f1d1d;margin-bottom:4px}
.bulk-danger-zone p{font-size:12.5px;color:#7a4a4a;font-weight:650;max-width:720px}
.bulk-danger-btn,.bulk-delete-btn{border:0;border-radius:12px;background:#d92d20;color:#fff;font-family:Archivo,sans-serif;font-weight:800;cursor:pointer;padding:11px 15px;box-shadow:0 8px 18px rgba(217,45,32,.18)}
.bulk-danger-btn:hover,.bulk-delete-btn:hover{filter:brightness(.96);transform:translateY(-1px)}
.bulk-delete-btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
.bulk-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:12px 13px;margin-bottom:12px;background:#f5f9fe;border:1px solid #d8e7f7;border-radius:14px}
.bulk-toolbar-left,.bulk-toolbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.bulk-check-label{display:inline-flex;align-items:center;gap:8px;font-size:12.5px;font-weight:800;color:#17385d;cursor:pointer}
.bulk-check-label input,.bulk-row-check{width:18px;height:18px;accent-color:#1f6fc4;cursor:pointer}
.bulk-selected-count{display:inline-flex;align-items:center;background:#e7f0fb;color:#155293;border:1px solid #c9ddf4;border-radius:999px;padding:6px 10px;font-size:11.5px;font-weight:900}
.bulk-select-col{width:44px;text-align:center!important}
.tech-bulk-row{display:flex;align-items:center;justify-content:flex-end;padding:9px 10px;background:#f7faff;border-bottom:1px solid #e3edf8}
.bulk-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(13,27,48,.62);backdrop-filter:blur(6px)}
.bulk-modal.show{display:flex}
.bulk-modal-box{width:min(520px,100%);background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 28px 80px rgba(0,0,0,.30);border:1px solid #dce6f2}
.bulk-modal-head{padding:18px 20px;background:linear-gradient(135deg,#fff1f1,#fff);border-bottom:1px solid #ffd2d2;display:flex;align-items:center;gap:12px}
.bulk-modal-icon{width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#ffe3e3;font-size:22px}
.bulk-modal-head h3{font-family:Archivo,sans-serif;font-size:19px;color:#7d1919}
.bulk-modal-body{padding:18px 20px;color:#53677f;font-weight:650;line-height:1.5}
.bulk-modal-body strong{color:#10213a}
.bulk-confirm-wrap{margin-top:14px;display:none}
.bulk-confirm-wrap.show{display:block}
.bulk-confirm-wrap label{display:block;font-size:12px;font-weight:900;color:#7d1919;margin-bottom:6px}
.bulk-confirm-wrap input{width:100%;min-height:46px;border:1.5px solid #efb3b3;border-radius:12px;padding:10px 12px;font:inherit;text-transform:uppercase}
.bulk-modal-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px}
.bulk-cancel{border:0;border-radius:12px;padding:11px 15px;background:#eaf0f6;color:#10213a;font-weight:900;cursor:pointer}
.bulk-confirm{border:0;border-radius:12px;padding:11px 15px;background:#d92d20;color:#fff;font-weight:900;cursor:pointer}
.bulk-confirm:disabled{opacity:.45;cursor:not-allowed}
@media(max-width:720px){
  .bulk-danger-zone{align-items:flex-start;flex-direction:column}.bulk-danger-btn{width:100%}
  .bulk-toolbar{align-items:stretch}.bulk-toolbar-left,.bulk-toolbar-right{width:100%;justify-content:space-between}
  .bulk-delete-btn{width:100%}.bulk-modal-actions{flex-direction:column-reverse}.bulk-modal-actions button{width:100%}
  .bulk-select-col{width:38px}
}
</style>


<style id="zg-odoo-central-panel-style">

</style>

<style id="zg-odoo-ticket-catalog-style">
.ticket-report-box{grid-column:1/-1;background:linear-gradient(180deg,#f8fbff,#f3f8fd);border:1.5px solid #cfe0f1!important}
.ticket-report-box .ticket-report-title{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.ticket-report-box .ticket-report-title span{width:38px;height:38px;border-radius:13px;background:#e5f1ff;display:grid;place-items:center;font-size:19px}
.ticket-report-box .ticket-report-title b{font-family:Archivo,sans-serif;color:#10213a;font-size:17px}
.ticket-report-box .ticket-report-title small{display:block;color:#6b7d91;font-size:12px;font-weight:700;margin-top:2px}
.ticket-report-fields{display:grid;grid-template-columns:minmax(260px,1.25fr) minmax(220px,.75fr);gap:12px}
.ticket-report-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
.ticket-report-select{width:100%;min-height:46px;border:1.5px solid #d4e1ee;border-radius:12px;background:#fff;padding:11px 12px;font:inherit;color:#10213a;outline:none}
.ticket-report-select:focus{border-color:#1f6fc4;box-shadow:0 0 0 4px #e5f1ff}
.ticket-report-note{font-size:11.5px;color:#64758a;font-weight:700;margin-top:7px;line-height:1.4}
.ticket-catalog-list{display:grid;gap:10px;padding:14px}
.ticket-catalog-row{display:grid;grid-template-columns:110px minmax(180px,1fr) minmax(150px,.7fr);gap:12px;align-items:center;background:#fff;border:1px solid #dce8f4;border-radius:14px;padding:12px 14px}
.ticket-catalog-ref{display:inline-flex;width:max-content;background:#10213a;color:#fff;border-radius:10px;padding:8px 10px;font-family:Archivo,sans-serif;font-weight:900}
.ticket-catalog-client{font-weight:900;color:#17385d;line-height:1.3}
.ticket-catalog-meta{display:block;color:#687b91;font-size:11.5px;font-weight:700;margin-top:3px}
.ticket-catalog-status{justify-self:end;display:inline-flex;align-items:center;border-radius:999px;padding:7px 10px;font-size:11px;font-weight:900}
.ticket-catalog-status.ok{background:#eaf8ef;color:#176b34;border:1px solid #c9ead5}
.ticket-catalog-status.pending{background:#fff8df;color:#8a5a00;border:1px solid #f0df9c}
#reporteTicketForm,#ticketsOdooPanel{scroll-margin-top:22px}
@media(max-width:760px){
  .ticket-report-fields{grid-template-columns:1fr}
  .ticket-catalog-row{grid-template-columns:1fr}
  .ticket-catalog-status{justify-self:start}
}
</style>
</head>
<body>
  <form class="box" method="post">
    <img src="zgroup-logo.png" alt="ZGROUP">
    <h1>Acceso restringido</h1>
    <p>Esta sección es solo para administradores y supervisores.</p>
    <?php if ($login_error): ?><div class="err">Clave incorrecta. Intenta de nuevo.</div><?php endif; ?>
    <input type="password" name="clave" placeholder="Clave de acceso" autofocus required>
    <button type="submit">Entrar</button>
  </form>
</body>
</html>
<?php
    exit;
}

require __DIR__ . '/db.php';
date_default_timezone_set('America/Lima');
try {
    require_once __DIR__ . '/odoo_lib.php';
    if (function_exists('zgOdooEnsureColumns')) zgOdooEnsureColumns($pdo);
} catch (Throwable $e) {
    @file_put_contents(__DIR__ . '/odoo_debug.log', '[' . date('Y-m-d H:i:s') . '] Panel: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

function defaultTrabajosRealizados() {
    return [
        ['slug' => 'asistencia_online', 'nombre' => 'ASISTENCIA ONLINE'],
        ['slug' => 'asistencia_tecnica', 'nombre' => 'ASISTENCIA TECNICA'],
        ['slug' => 'ingreso_new_genset', 'nombre' => 'INGRESO EQUIPO NEW GENSET'],
        ['slug' => 'ingreso_new_reefer', 'nombre' => 'INGRESO EQUIPO NEW MAQUINA REEFER'],
        ['slug' => 'ingreso_almacenaje', 'nombre' => 'INGRESO PARA ALMACENAJE'],
        ['slug' => 'ingreso_reentrega', 'nombre' => 'INGRESO POR REENTREGA'],
        ['slug' => 'ingreso_devolucion', 'nombre' => 'INGRESO/DEVOLUCION'],
        ['slug' => 'instalacion', 'nombre' => 'INSTALACION'],
        ['slug' => 'instalacion_accesorios', 'nombre' => 'INSTALACION ACCESORIOS'],
        ['slug' => 'instalacion_luminarias', 'nombre' => 'INSTALACION DE LUMINARIAS'],
        ['slug' => 'instalacion_reefer', 'nombre' => 'INSTALACION DE MAQUINA REEFER'],
        ['slug' => 'instalacion_humidificacion', 'nombre' => 'INSTALACION DE SIST. HUMIDIFICACION'],
        ['slug' => 'reparacion_bomba', 'nombre' => 'REPARACION DE BOMBA'],
        ['slug' => 'reparacion_carreta', 'nombre' => 'REPARACION DE CARRETA'],
        ['slug' => 'reparacion_estructural', 'nombre' => 'REPARACION ESTRUCTURAL'],
        ['slug' => 'reparacion_genset', 'nombre' => 'REPARACION GENSET'],
        ['slug' => 'reparacion_reefer', 'nombre' => 'REPARACION MAQUINA REEFER'],
        ['slug' => 'reparacion_trailer', 'nombre' => 'REPARACION TRAILER'],
        ['slug' => 'retiro_piezas', 'nombre' => 'RETIRO DE PIEZAS'],
        ['slug' => 'revision_tecnica', 'nombre' => 'REVISION TECNICA'],
        ['slug' => 'revision_prueba_motor', 'nombre' => 'REVISION Y PRUEBA DE MOTOR'],
        ['slug' => 'trabajos_sistema_electrico', 'nombre' => 'TRABAJOS SISTEMA ELECTRICO'],
        ['slug' => 'instalacion_luminarias', 'nombre' => 'INSTALACION DE LUMINARIAS'],
        ['slug' => 'genset_mantenimiento_preventivo', 'nombre' => 'MANTENIMIENTO PREVENTIVO DE GENSET'],
        ['slug' => 'genset_mantenimiento_correctivo', 'nombre' => 'MANTENIMIENTO CORRECTIVO DE GENSET'],
    ];
}

function slugTrabajoRealizado($s) {
    $s = trim((string)$s);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($ascii !== false) $s = $ascii;
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '_', $s);
    $s = trim($s, '_');
    return $s !== '' ? $s : 'trabajo';
}

function upperUtf8($s) {
    return function_exists('mb_strtoupper') ? mb_strtoupper($s, 'UTF-8') : strtoupper($s);
}

function asegurarTablaTrabajos($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS trabajos_realizados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(90) NOT NULL UNIQUE,
        nombre VARCHAR(180) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $count = (int)$pdo->query('SELECT COUNT(*) FROM trabajos_realizados')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO trabajos_realizados (slug, nombre, activo) VALUES (?, ?, 1)');
        foreach (defaultTrabajosRealizados() as $w) {
            $stmt->execute([$w['slug'], $w['nombre']]);
        }
    }
}

function slugTrabajoUnico($pdo, $nombre) {
    $base = slugTrabajoRealizado($nombre);
    $slug = $base;
    $i = 2;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM trabajos_realizados WHERE slug = ?');
    while (true) {
        $stmt->execute([$slug]);
        if ((int)$stmt->fetchColumn() === 0) return $slug;
        $slug = $base . '_' . $i;
        $i++;
    }
}

asegurarTablaTrabajos($pdo);


function asegurarTrabajosNuevosV9(PDO $pdo): void {
    $nuevos = [
        ['instalacion_luminarias', 'INSTALACION DE LUMINARIAS'],
        ['genset_mantenimiento_preventivo', 'MANTENIMIENTO PREVENTIVO DE GENSET'],
        ['genset_mantenimiento_correctivo', 'MANTENIMIENTO CORRECTIVO DE GENSET'],
    ];
    $sel = $pdo->prepare('SELECT id FROM trabajos_realizados WHERE slug = ? LIMIT 1');
    $ins = $pdo->prepare('INSERT INTO trabajos_realizados (slug, nombre, activo) VALUES (?, ?, 1)');
    $up = $pdo->prepare('UPDATE trabajos_realizados SET nombre = ?, activo = 1 WHERE slug = ?');
    foreach ($nuevos as [$slug, $nombre]) {
        $sel->execute([$slug]);
        if ($sel->fetchColumn()) $up->execute([$nombre, $slug]);
        else $ins->execute([$slug, $nombre]);
    }
    $pdo->exec("UPDATE trabajos_realizados SET activo = 0 WHERE slug IN ('genset_inspeccion_diagnostico','genset_cambio_aceite_filtros','genset_sistema_electrico','genset_prueba_carga','ingreso_new_genset','reparacion_genset')");
}
asegurarTrabajosNuevosV9($pdo);

function normalizarNombreTecnico($s) {
    $s = trim((string)$s);
    $s = preg_replace('/\s+/u', ' ', $s);

    if (function_exists('mb_strtolower')) {
        $s = mb_strtolower($s, 'UTF-8');
    } else {
        $s = strtolower($s);
    }

    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($ascii !== false) {
        $s = strtolower($ascii);
    }

    $s = preg_replace('/[^a-z0-9 ]+/', '', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

function buscarTecnicoDuplicado($pdo, $nombre) {
    $objetivo = normalizarNombreTecnico($nombre);
    if ($objetivo === '') return false;

    $stmt = $pdo->query('SELECT id, nombre FROM tecnicos');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
        if (normalizarNombreTecnico($t['nombre'] ?? '') === $objetivo) {
            return $t;
        }
    }
    return false;
}

function asegurarIndiceTecnicosNombre($pdo) {
    // No se fuerza UNIQUE porque podrían existir registros repetidos antiguos.
    // La validación principal se hace por PHP para no romper el panel.
    try {
        $pdo->exec('ALTER TABLE tecnicos ADD INDEX idx_tecnicos_nombre (nombre)');
    } catch (Throwable $e) {
        // Si ya existe el índice o el hosting no permite el ALTER, no detenemos el panel.
    }
}

asegurarIndiceTecnicosNombre($pdo);


/* ============================================================
   Eliminación múltiple segura de informes y preliminares
   ============================================================ */
function panelIdsPost($valor): array {
    if (!is_array($valor)) $valor = [$valor];
    $ids = [];
    foreach ($valor as $id) {
        $n = (int)$id;
        if ($n > 0) $ids[$n] = $n;
    }
    return array_values($ids);
}

function panelPlaceholders(array $ids): string {
    return implode(',', array_fill(0, count($ids), '?'));
}

function panelPdfSeguro($archivo): bool {
    $archivo = trim((string)$archivo);
    return $archivo !== ''
        && basename($archivo) === $archivo
        && (bool)preg_match('/^[A-Za-z0-9._-]+\.pdf$/i', $archivo);
}

function panelBorrarPdfs(array $archivos): int {
    $borrados = 0;
    $dir = __DIR__ . '/informes';
    foreach (array_unique($archivos) as $archivo) {
        if (!panelPdfSeguro($archivo)) continue;
        $ruta = $dir . '/' . $archivo;
        if (is_file($ruta) && @unlink($ruta)) $borrados++;
    }
    return $borrados;
}

function panelEliminarPreliminares(PDO $pdo, array $ids): array {
    $ids = panelIdsPost($ids);
    if (!$ids) return ['preliminares' => 0, 'informes_desvinculados' => 0];

    $ph = panelPlaceholders($ids);
    $desvinculados = 0;

    // Los informes finales permanecen, pero dejan de apuntar a una preliminar eliminada.
    if (panelColumnaExiste($pdo, 'informes', 'preinspeccion_id')) {
        $st = $pdo->prepare("UPDATE informes SET preinspeccion_id = NULL WHERE preinspeccion_id IN ($ph)");
        $st->execute($ids);
        $desvinculados = $st->rowCount();
    }

    $st = $pdo->prepare("DELETE FROM inspecciones_preliminares WHERE id IN ($ph)");
    $st->execute($ids);

    return ['preliminares' => $st->rowCount(), 'informes_desvinculados' => $desvinculados];
}

function panelEliminarInformes(PDO $pdo, array $ids): array {
    $ids = panelIdsPost($ids);
    if (!$ids) return ['informes' => 0, 'preliminares' => 0, 'pdfs' => 0];

    $ph = panelPlaceholders($ids);
    $cols = 'id, archivo';
    if (panelColumnaExiste($pdo, 'informes', 'preinspeccion_id')) $cols .= ', preinspeccion_id';

    $st = $pdo->prepare("SELECT $cols FROM informes WHERE id IN ($ph)");
    $st->execute($ids);
    $rowsDel = $st->fetchAll(PDO::FETCH_ASSOC);
    if (!$rowsDel) return ['informes' => 0, 'preliminares' => 0, 'pdfs' => 0];

    $realIds = [];
    $preIds = [];
    $archivos = [];
    foreach ($rowsDel as $r) {
        $realIds[] = (int)$r['id'];
        if (!empty($r['preinspeccion_id'])) $preIds[] = (int)$r['preinspeccion_id'];
        if (!empty($r['archivo'])) $archivos[] = (string)$r['archivo'];
    }

    $phReal = panelPlaceholders($realIds);
    $preBorradas = 0;

    // Elimina la preliminar que originó cada informe para mantener el comportamiento
    // de la eliminación individual que ya usa el panel.
    try {
        $stPreByInf = $pdo->prepare("DELETE FROM inspecciones_preliminares WHERE informe_id IN ($phReal)");
        $stPreByInf->execute($realIds);
        $preBorradas += $stPreByInf->rowCount();
    } catch (Throwable $e) {}

    $preIds = panelIdsPost($preIds);
    if ($preIds) {
        $phPre = panelPlaceholders($preIds);
        $stPre = $pdo->prepare("DELETE FROM inspecciones_preliminares WHERE id IN ($phPre)");
        $stPre->execute($preIds);
        $preBorradas += $stPre->rowCount();
    }

    $stDel = $pdo->prepare("DELETE FROM informes WHERE id IN ($phReal)");
    $stDel->execute($realIds);
    $informesBorrados = $stDel->rowCount();

    return [
        'informes' => $informesBorrados,
        'preliminares' => $preBorradas,
        'pdfs' => panelBorrarPdfs($archivos),
    ];
}

function panelEliminarTodoPruebas(PDO $pdo): array {
    $archivos = [];
    try {
        $archivos = $pdo->query("SELECT archivo FROM informes WHERE archivo IS NOT NULL AND archivo <> ''")
            ->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {}

    $cantInformes = 0;
    $cantPre = 0;
    $pdo->beginTransaction();
    try {
        $cantPre = (int)$pdo->query("SELECT COUNT(*) FROM inspecciones_preliminares")->fetchColumn();
        $cantInformes = (int)$pdo->query("SELECT COUNT(*) FROM informes")->fetchColumn();
        $pdo->exec("DELETE FROM inspecciones_preliminares");
        $pdo->exec("DELETE FROM informes");
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }

    // Reinicia únicamente la numeración de registros de prueba.
    try { $pdo->exec("ALTER TABLE inspecciones_preliminares AUTO_INCREMENT = 1"); } catch (Throwable $e) {}
    try { $pdo->exec("ALTER TABLE informes AUTO_INCREMENT = 1"); } catch (Throwable $e) {}

    // También limpia PDF huérfanos de la carpeta de informes.
    $dir = __DIR__ . '/informes';
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.pdf') ?: [] as $ruta) {
            $archivos[] = basename($ruta);
        }
    }

    return [
        'informes' => $cantInformes,
        'preliminares' => $cantPre,
        'pdfs' => panelBorrarPdfs($archivos),
    ];
}

function asegurarTablaEventosPanel($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS panel_eventos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo VARCHAR(60) NOT NULL,
        titulo VARCHAR(160) NOT NULL,
        detalle TEXT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function registrarEventoPanel($pdo, $tipo, $titulo, $detalle = '') {
    asegurarTablaEventosPanel($pdo);
    $stmt = $pdo->prepare('INSERT INTO panel_eventos (tipo, titulo, detalle) VALUES (?, ?, ?)');
    $stmt->execute([(string)$tipo, (string)$titulo, (string)$detalle]);

    // Notificación push tipo Facebook: llega a los dispositivos suscritos aunque el panel no esté abierto.
    if (function_exists('zgroup_enviar_push')) {
        @zgroup_enviar_push((string)$titulo, (string)$detalle, 'panel.php', ['tipo' => (string)$tipo]);
    }
}

asegurarTablaEventosPanel($pdo);

function panelColumnaExiste(PDO $pdo, $tabla, $columna) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$tabla, $columna]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function panelAgregarColumnaSiFalta(PDO $pdo, $tabla, $columna, $definicion) {
    try {
        if (!panelColumnaExiste($pdo, $tabla, $columna)) {
            $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$columna` $definicion");
        }
    } catch (Throwable $e) {
        // No detenemos el panel si el hosting no permite ALTER en ese momento.
    }
}

function asegurarTablaPreliminaresPanel(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS inspecciones_preliminares (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tecnico_id INT NOT NULL,
        cliente VARCHAR(150) DEFAULT NULL,
        cotizacion VARCHAR(100) DEFAULT NULL,
        trabajo VARCHAR(150) DEFAULT NULL,
        modalidad_comercial VARCHAR(40) DEFAULT NULL,
        tipo_instalacion VARCHAR(80) DEFAULT NULL,
        tipo_equipo VARCHAR(30) DEFAULT NULL,
        tamano_contenedor VARCHAR(60) DEFAULT NULL,
        numero_equipo VARCHAR(100) DEFAULT NULL,
        serie_unidad VARCHAR(100) DEFAULT NULL,
        marca_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,
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

    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'modalidad_comercial', "VARCHAR(40) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tipo_instalacion', "VARCHAR(80) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tipo_equipo', "VARCHAR(30) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'tamano_contenedor', "VARCHAR(60) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'presion_alta', "VARCHAR(50) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'presion_baja', "VARCHAR(50) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'alarma_encontrada', "VARCHAR(180) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_horometro_inicial', "DECIMAL(12,1) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_voltaje_bateria_inicial', "VARCHAR(50) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_nivel_combustible_inicial', "VARCHAR(40) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_nivel_aceite_inicial', "VARCHAR(50) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_refrigerante_motor_inicial', "VARCHAR(60) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_arranque_inicial', "VARCHAR(80) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_frecuencia_inicial', "DECIMAL(8,2) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'genset_presion_aceite_inicial', "VARCHAR(50) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'estado', "VARCHAR(30) NOT NULL DEFAULT 'abierto'");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'token_continuacion', "VARCHAR(120) DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'informe_id', "INT DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'finalizado_en', "DATETIME DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'hora_inicio_servicio', "DATETIME DEFAULT NULL");
    panelAgregarColumnaSiFalta($pdo, 'inspecciones_preliminares', 'hora_fin_servicio', "DATETIME DEFAULT NULL");
}

function asegurarTecnicosActivosPanel(PDO $pdo) {
    // Permite eliminar técnicos sin romper los informes históricos.
    // activo = 1 aparece en formularios; activo = 0 queda oculto pero conserva sus informes.
    panelAgregarColumnaSiFalta($pdo, 'tecnicos', 'activo', "TINYINT(1) NOT NULL DEFAULT 1");
}

asegurarTecnicosActivosPanel($pdo);
asegurarTablaPreliminaresPanel($pdo);
panelAgregarColumnaSiFalta($pdo, 'informes', 'tipo_equipo', "VARCHAR(30) DEFAULT NULL");
panelAgregarColumnaSiFalta($pdo, 'informes', 'tamano_contenedor', "VARCHAR(60) DEFAULT NULL");
panelAgregarColumnaSiFalta($pdo, 'informes', 'hora_inicio_servicio', "DATETIME DEFAULT NULL");
panelAgregarColumnaSiFalta($pdo, 'informes', 'hora_fin_servicio', "DATETIME DEFAULT NULL");
try {
    $pdo->exec("UPDATE inspecciones_preliminares SET hora_inicio_servicio = creado_en WHERE hora_inicio_servicio IS NULL");
    $pdo->exec("UPDATE inspecciones_preliminares SET hora_fin_servicio = finalizado_en WHERE hora_fin_servicio IS NULL AND finalizado_en IS NOT NULL");
    $pdo->exec("UPDATE informes i LEFT JOIN inspecciones_preliminares ip ON ip.id = i.preinspeccion_id SET i.hora_inicio_servicio = COALESCE(ip.hora_inicio_servicio, ip.creado_en, i.creado_en) WHERE i.hora_inicio_servicio IS NULL");
    $pdo->exec("UPDATE informes i LEFT JOIN inspecciones_preliminares ip ON ip.id = i.preinspeccion_id SET i.hora_fin_servicio = COALESCE(ip.hora_fin_servicio, ip.finalizado_en, i.creado_en) WHERE i.hora_fin_servicio IS NULL");
} catch (Throwable $e) {}


function zgroupMaterialesSG3000(): array {
    return [
        ['119300','ELEMENT-AIR CLEANER 119300','und'],
        ['INDND0910','FILTER FUEL 119342','und'],
        ['119182','OIL FILTER 119182','und'],
        ['RNDND0700','ACEITE DE MOTOR 15W/40 (MOBIL / SHELL RIMULA R4X)','L'],
        ['RNDND0289','LÍQUIDO PARA RADIADOR VISTONY ROJO','L'],
        ['INDND0017','TRAPO INDUSTRIAL SUELTO','und'],
        ['INDND0078','AFLOJATODO','und'],
        ['INDND0079','LIMPIA CONTACTO','und'],
        ['INDND6960','ARANDELA DE COBRE #14 X 18MM X 1.3MM','und'],
        ['INDND0779','DETERGENTE INDUSTRIAL','und'],
        ['INDND4603','FILTRO DE COMBUSTIBLE','und'],
        ['INDND0334','SODA CÁUSTICA','kg'],
        ['INDND1735','FILTRO DE GASOLINA DG1074','und'],
        ['INDND1603','41-3404 RECEPTACLE 480 V, 32A PN 413404','und'],
        ['114607','TANQUE DE RESERVA DE AGUA','und'],
        ['449298','SENSOR RPM 449298','und'],
        ['416539','SENSOR ASSY ENGINE WATER 416539','und'],
        ['41-8283','SENSOR DE PRESIÓN DE ACEITE - 418283 SWITCH-PRESS OIL','L'],
        ['41-4470','SENSOR NIVEL DE ACEITE - 414470 SENSOR OIL LEVEL','L'],
        ['411818','SENSOR DE NIVEL DE AGUA - 41-1818 SENSOR WTR LEV','und'],
        ['401311','KIT MODULE ECOPOWER RETROFIT 401311','und'],
        ['401129','KIT MODULE ECOPOWER RETROFIT 401129','und'],
        ['333878','GASKET - FUEL SENSOR','und'],
        ['987219','DOOR - CURBSIDE','und'],
        ['987221','DOOR - ROADSIDE','und'],
        ['422342','MODULE - OPTO COUPLER','und'],
        ['401130','SENSOR DE COMBUSTIBLE','und'],
        ['8101381','MOTOR - ENGINE NEW TK486VG2 INTER 8101381','und'],
        ['401332','ALTERNADOR 401332','und'],
        ['452177','ARRANCADOR - STARTER TK486V 12V PN 45-2177','und'],
        ['132268','BOMBA DE AGUA - PUMP WATER','und'],
        ['INDND1605','420100 SOLENOID STOP FUEL 41-9100','und'],
        ['RNDND0616','41-7841 KEYPAD-GENSET SG 3000 PN 417841','und'],
        ['120810TKA','RADIADOR','und'],
        ['452830','45-2830 CONTROLLER SG + 1.5 PN 452830','und'],
        ['452554','CONTROLLER GENSET 45-2554 / 8452554','und'],
        ['130972','STRAINER FUEL 130972','und'],
        ['132929','PULLEY - WATER PUMP 132929','und'],
        ['771426','PULLEY WATER PUMP PN 771426','und'],
        ['INDND0829','BATERÍA BOSCH M27 MF 17 PLACAS','und'],
        ['559828','STUD 559828','und'],
        ['443345','44-3345 REGULATOR VOLTAGE 443345','und'],
        ['781968','BELT 781968','und'],
        ['421257','REGULATOR DSR 421257','und'],
        ['118718','GAUGE - FUEL','und'],
        ['120991','TANK FUEL 50 GAL ALUMINUM 120991','und'],
        ['421276','DIODOS - DIODE ASSY 421276','und'],
        ['412147','HEATER','und'],
        ['130669','GAUGE-FUEL 130669','und'],
        ['120790','TANQUE DE COMBUSTIBLE - 80 GALONES','und'],
        ['RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67','und'],
        ['931061','NAMEPLATE FOIL 931061','und'],
        ['INDND0443','FUSIBLE TIPO UÑA 30 AMP','und'],
        ['INDND2439','ELEMENTO FILTRO COMBUSTIBLE AB39/9176/AC','und'],
        ['INDND0820','ABRAZADERA 3/8','und'],
        ['INDND1008','CINTILLO DE AMARRE 350MM','und'],
        ['INDND0619','CEBADOR DE COMBUSTIBLE BOSCH','und'],
        ['130388','INYECTORES','und'],
        ['INDND3648','PROTECTOR BATIENTE PARA TAPA DE COMBUSTIBLE 3MM','und'],
        ['INDND1013','BORNERA DE BATERÍA NEGATIVO (-)','und'],
        ['INDND0874','BORNERA DE BATERÍA POSITIVO (+)','und'],
        ['TB-37-33-4088','RETÉN DE CIGÜEÑAL DELANTERO - SEAL OIL CRANK FRONT','und'],
        ['INDND0106','PINTURA EN SPRAY BLANCO BRILLANTE','und'],
        ['INDND0107','PINTURA EN SPRAY NEGRO BRILLANTE','und'],
        ['RNDND0408','PAÑO DE FRANELA AMARILLA','und'],
        ['41-7904','RELAY 1PDT','und'],
        ['42-1787','421787 RELAY QUAD','und'],
        ['417904','417904 RELAY 1PDT','und'],
        ['INDND2875','FILTRO DE GASOLINA LFG-120','und'],
        ['INDND0968','FILTRO DE ACEITE LF3349','L'],
        ['INDND1569','FILTRO DE ACEITE LF699','L'],
        ['RNDND0393','FILTRO DE PETRÓLEO F1110','und'],
        ['INDND1577','FAJA A28','und'],
        ['INDND1002','PERNO HEX INOX 1/4 X 1','und'],
        ['INDND1390','TUERCA HEX INOX 1/4','und'],
        ['INDND1391','ARANDELA PLANA INOX 1/4','und'],
        ['INDND5055','HARNESS ASSY MAIN SG 417837','und'],
        ['INDND5056','HARNESS - EXCITER CM SYSTEMS 423280','und'],
        ['935685','BRACKET - OPTO ISOLATOR MOUNT','und'],
        ['RNDND0501','CINTILLO DE AMARRE 300 MM','und'],
        ['INDND1004','ARANDELA DE PRESIÓN INOX 1/4','und'],
        ['INDND0907','TOMA EMPOTRABLE 32AMP 3P+T 440V ROJO 3H IP67','und'],
        ['987816','BOX CONTROL','und'],
        ['118048','CAP-FUEL TANK (STEEL)','und'],
        ['923341','BRACKET-AIR CLEANER','und'],
        ['414240','CABLE DE BATERÍA POSITIVO Y NEGATIVO','m'],
        ['INDND2087','ALICATE DE PRESIÓN 6 STANLEY','und'],
        ['INDND4993','CABLE FLEXIBLE AUTOMOTRIZ GPT 0.3KV 14 AWG ROJO','m'],
        ['INDND5052','TERMINAL TIPO UÑA AZUL 14-16AWG','und'],
        ['INDND0134','BROCA DE COBALTO HSS 3/8','und'],
        ['INDND2268','LIJA DE AGUA N.º 1000','und'],
        ['INDND4509','CABLE PASACORRIENTE CON TERMINALES COCODRILO INDUSTRIAL X 2 M','und'],
        ['INDND5591','MANGUERA NEOPRENE 3/8 X 0.60 CONO Y GUÍA','m'],
        ['INDND5533','MANGUERA SYNFLEX 3/8 X 61 CM CON CONECTORES','m'],
        ['GL-0001','COMBUSTIBLE DIÉSEL','L'],
        ['RNDND0313','MEGAGREY SILICONA PARA EMPAQUETADURAS','und'],
        ['INDND0890','ACEITE PARA MOTOR SHELL SAE 5W-30','L'],
        ['118887','TUBE - FUEL FEED','und'],
        ['INDND5747','TAPAS SUPERIORES PARA GENERADOR EN ACERO GALVANIZADO 1.5 MM','und'],
        ['INDND5551','RELAY AUTOMOTRIZ 5P 12V BOSCH 0332209150','und'],
        ['INDND6930','FUEL METERING VALVE','und'],
        ['INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG','und'],
    ];
}

function zgroupAsegurarCatalogoGeneradores(PDO $pdo): void {
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_genset_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        controlador VARCHAR(40) NOT NULL,
        codigo VARCHAR(60) DEFAULT NULL,
        detalle VARCHAR(220) NOT NULL,
        unidad VARCHAR(40) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_genset_rep_controlador (controlador),
        INDEX idx_genset_rep_codigo (codigo),
        INDEX idx_genset_rep_detalle (detalle)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $st = $pdo->prepare("SELECT COUNT(*) FROM repuestos_genset_catalogo WHERE controlador = 'SG-3000'");
    $st->execute();
    if ((int)$st->fetchColumn() === 0) {
        $ins = $pdo->prepare('INSERT INTO repuestos_genset_catalogo (controlador, codigo, detalle, unidad, activo) VALUES (?, ?, ?, ?, 1)');
        foreach (zgroupMaterialesSG3000() as $r) $ins->execute(['SG-3000', $r[0] ?: null, $r[1], $r[2]]);
    }
}


function zgroupMaterialesReeferV12(): array {
    return [
        ['STAR COOL','CIM6','818770B','2 PIN CONNECTOR (3.81mm/90°) (5 pcs)',''],
        ['STAR COOL','CIM6','818270C','AIR EXCHANGE MODULE (75CMH)',''],
        ['STAR COOL','CIM6','818522F','AUXILIARY CONTACT (WHITE DOT 10PCS)',''],
        ['STAR COOL','CIM6','811537D','BRACKET, EVAPORATOR FAN MOTOR',''],
        ['STAR COOL','CIM6','818329A','BUTT SPLICE',''],
        ['STAR COOL','CIM6','818202B','CABLE ADAPTER KIT, FAN MOTOR (10 pcs)',''],
        ['STAR COOL','CIM6','815505D','CABLE ROOM COVER',''],
        ['STAR COOL','CIM6','818561B','CABLE SET (X1, X2, X3), CIM 5',''],
        ['STAR COOL','CIM6','814247C','CABLE, FC (1.0 AND 1.1) TO COMPRESSOR',''],
        ['STAR COOL','CIM6','819526B','COIL CONDENSER',''],
        ['STAR COOL','CIM6','818658B','COMPRESSOR',''],
        ['STAR COOL','CIM6','818760B','CONNECTOR PLUG, SOLENOID COIL (5PCS)',''],
        ['STAR COOL','CIM6','818521B','CONTACTOR',''],
        ['STAR COOL','CIM6','818310C','CONTROLLER DOOR, CIM 6',''],
        ['STAR COOL','CIM6','868510D','CONTROLLER MODULE, CIM 6.0',''],
        ['STAR COOL','CIM6','818925A','CONTROLLER MODULE, USB CIM 6.2',''],
        ['STAR COOL','CIM6','818510E','CONTROLLER MODULE, USB CIM 6.2 REMAN',''],
        ['STAR COOL','CIM6','815209B','COVER PLATE (1715MM), CONDENSER',''],
        ['STAR COOL','CIM6','818250E','DAMPER, AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM6','881523A','DEFROST HEATER, EVAPORATOR (25PCS)',''],
        ['STAR COOL','CIM6','814667F','ECONOMIZER',''],
        ['STAR COOL','CIM6','819737D','ECONOMIZER VALVE, R134A',''],
        ['STAR COOL','CIM6','881527A','EVAPORATOR COIL',''],
        ['STAR COOL','CIM6','819543B','FAN BLADE, CONDENSER',''],
        ['STAR COOL','CIM6','819542C','FAN BLADE, EVAPORATOR',''],
        ['STAR COOL','CIM6','818965B','FREQUENCY CONVERTER 2.1',''],
        ['STAR COOL','CIM6','818274C','FRONT PART, AIR EXCHANGE MODULE (75 CMH)',''],
        ['STAR COOL','CIM6','818530A','FUSE 10A',''],
        ['STAR COOL','CIM6','818534A','FUSE HOLDER 0.4A',''],
        ['STAR COOL','CIM6','818656B','GASKET, COMPRESSOR STOP VALVE',''],
        ['STAR COOL','CIM6','818661B','GASKET, SERVICE VALVE LP',''],
        ['STAR COOL','CIM6','819501A','HIGH PRESSURE SWITCH',''],
        ['STAR COOL','CIM6','814644C','HINGE PIN',''],
        ['STAR COOL','CIM6','889740C','HOT GAS VALVE',''],
        ['STAR COOL','CIM6','818537A','HUMIDITY SENSOR, CIM 6',''],
        ['STAR COOL','CIM6','818523C','INTERLOCK, CONTACTOR',''],
        ['STAR COOL','CIM6','818236B','MELT FUSE IT',''],
        ['STAR COOL','CIM6','818275A','MOTOR, AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM6','818792A','MOTOR, CONDENSER FAN',''],
        ['STAR COOL','CIM6','818783A','MOTOR, EVAPORATOR FAN',''],
        ['STAR COOL','CIM6','818525C','ON/OFF SWITCH CIM 6',''],
        ['STAR COOL','CIM6','881550A','PLUG, EVAPORATOR SERVICE HOLE',''],
        ['STAR COOL','CIM6','818905A','POWER MEASUREMENT MODULE, CIM 6.2',''],
        ['STAR COOL','CIM6','819504D','PRESSURE TRANSMITTER HP NSK',''],
        ['STAR COOL','CIM6','819503D','PRESSURE TRANSMITTER LP NSK',''],
        ['STAR COOL','CIM6','814540B','RECEIVER',''],
        ['STAR COOL','CIM6','818739A','RECEIVER, WATER COOLED CONDENSER',''],
        ['STAR COOL','CIM6','818276A','SENSOR AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM6','818623C','SERVICE VALVE, COMPRESSOR LP',''],
        ['STAR COOL','CIM6','818235B','SIGHT GLASS RECEIVER KIT',''],
        ['STAR COOL','CIM6','818553A','SOLENOID COIL 14W 24VDC CIM 5',''],
        ['STAR COOL','CIM6','818554A','SOLENOID COIL 18W 24VDC CIM 5',''],
        ['STAR COOL','CIM6','886554B','SOLENOID COIL 11W 24VAC',''],
        ['STAR COOL','CIM6','814541C','SQUARE FAN GRILLE, CONDENSER',''],
        ['STAR COOL','CIM6','819500C','STOP VALVE RECEIVER',''],
        ['STAR COOL','CIM6','818940A','TEMPERATURE SENSOR 0.35M',''],
        ['STAR COOL','CIM6','818943B','TEMPERATURE SENSOR INCL. CABLE GLAND (3M)',''],
        ['STAR COOL','CIM6','818639B','TERMINAL BLOCK, COMPRESSOR',''],
        ['STAR COOL','CIM6','818518C','TRANSFORMER 105 VA CIM 6',''],
        ['STAR COOL','CIM6','886513A','USER PANEL, CIM 6.1',''],
        ['STAR COOL','CIM6','INDND0078','ACEITE AFLOJATODO',''],
        ['STAR COOL','CIM6','INDND0411','ACEITE POLYOLESTER BVA',''],
        ['STAR COOL','CIM6','INDND3242','BROCA DE COBALTO HSS 3/16',''],
        ['STAR COOL','CIM6','INDND0259','CABLE VULCANIZADO 4 X10',''],
        ['STAR COOL','CIM6','RNDND0254','CINTA AISLANTE 3M',''],
        ['STAR COOL','CIM6','INDND0432','CINTA FOAM 1/8 X 2 X 9.14M',''],
        ['STAR COOL','CIM6','INDND0433','CINTILLO DE AMARRE 150MM',''],
        ['STAR COOL','CIM6','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['STAR COOL','CIM6','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['STAR COOL','CIM6','INDND1911','EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M',''],
        ['STAR COOL','CIM6','INDND0126','ESTAÑO 0.8',''],
        ['STAR COOL','CIM6','INDND2552','FILTRO SECADOR QDM-164 1/2 - QUALITY',''],
        ['STAR COOL','CIM6','INDND0024','FILTRO SEC. FIJO DE 1/2 FLARE - EK 164 STD',''],
        ['STAR COOL','CIM6','INDND2905','FORMADOR EMPAQUETADURA AVIACION 3H',''],
        ['STAR COOL','CIM6','INDND2237','FUNDENTE',''],
        ['STAR COOL','CIM6','INDND1545','MINI FUSIBLE DE VIDRIO 15 AMP',''],
        ['STAR COOL','CIM6','INDND1542','FUSIBLE DE VIDRIO 10 AMP',''],
        ['STAR COOL','CIM6','RNDND0318','FUSIBLE DE VIDRIO 20 AMP',''],
        ['STAR COOL','CIM6','INDND0120','GAS REFRIGERANTE R-134A X 13.60KG',''],
        ['STAR COOL','CIM6','RNDND0438','JABON LIQUIDO',''],
        ['STAR COOL','CIM6','INDND0016','LIJA FIERRO #40 ASA',''],
        ['STAR COOL','CIM6','INDND0079','LIMPIA CONTACTO',''],
        ['STAR COOL','CIM6','INDND0086','NITROGENO INDUSTRIAL 10 M3',''],
        ['STAR COOL','CIM6','INDND3074','MANGA TERMOCONTRAIBLE 15MM',''],
        ['STAR COOL','CIM6','INDND3322','MANGA TERMOCONTRAIBLE 20MM',''],
        ['STAR COOL','CIM6','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['STAR COOL','CIM6','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['STAR COOL','CIM6','INDND1555','PERNO HEX. RC. INOX 304 M16X50',''],
        ['STAR COOL','CIM6','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['STAR COOL','CIM6','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['STAR COOL','CIM6','INDND2768','RODAJE 6201 2RSH/C3',''],
        ['STAR COOL','CIM6','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['STAR COOL','CIM6','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['STAR COOL','CIM6','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['STAR COOL','CIM6','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['STAR COOL','CIM6','INDND5576','TERMINAL TUBULAR SOBREMOLDEADO ROJO 4MM 12AWG',''],
        ['STAR COOL','CIM6','INDND2711','TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG',''],
        ['STAR COOL','CIM6','INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG',''],
        ['STAR COOL','CIM6','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['STAR COOL','CIM6','INDND0087','SOLVENTE DIELECTRICO SDL-25',''],
        ['STAR COOL','CIM6','INDND1412','VALVULA DE ACCESO 1/4 X 7 CM',''],
        ['STAR COOL','CIM5','818270C','AIR EXCHANGE MODULE (75CMH)',''],
        ['STAR COOL','CIM5','819747B','AIR RELEASE VALVE, RECEIVER',''],
        ['STAR COOL','CIM5','818522F','AUXILIARY CONTACT (WHITE DOT 10PCS)',''],
        ['STAR COOL','CIM5','818522C','AUXILIARY CONTACT',''],
        ['STAR COOL','CIM5','818536B','BATTERY PACK CIM5',''],
        ['STAR COOL','CIM5','811537D','BRACKET, EVAPORATOR FAN MOTOR',''],
        ['STAR COOL','CIM5','818202B','CABLE ADAPTER KIT, FAN MOTOR',''],
        ['STAR COOL','CIM5','815505D','CABLE ROOM COVER',''],
        ['STAR COOL','CIM5','818561B','CABLE SET (X1, X2, X3), CIM 5',''],
        ['STAR COOL','CIM5','814247C','CABLE, FC (1.0 AND 1.1) TO COMPRESSOR',''],
        ['STAR COOL','CIM5','819526B','COIL CONDENSER',''],
        ['STAR COOL','CIM5','818658B','COMPRESSOR',''],
        ['STAR COOL','CIM5','818521B','CONTACTOR',''],
        ['STAR COOL','CIM5','818310B','CONTROLLER DOOR, CIM 5',''],
        ['STAR COOL','CIM5','818320B','CONTROLLER DOOR, COMPLETE CIM 5',''],
        ['STAR COOL','CIM5','818512A','CONTROLLER MODULE, CA',''],
        ['STAR COOL','CIM5','868255C','CONTROLLER MODULE, CIM 5',''],
        ['STAR COOL','CIM5','818255C','CONTROLLER MODULE, CIM 5',''],
        ['STAR COOL','CIM5','818209D','COVER PLATE (2100MM) CONDENSER SCI',''],
        ['STAR COOL','CIM5','818250E','DAMPER, AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM5','811522B','DEFROST HEATER ELEMENT, TRAY',''],
        ['STAR COOL','CIM5','818515A','DISPLAY PCB, CIM 5',''],
        ['STAR COOL','CIM5','814667F','ECONOMIZER',''],
        ['STAR COOL','CIM5','819737D','ECONOMIZER VALVE, R134A',''],
        ['STAR COOL','CIM5','881527A','EVAPORATOR COIL',''],
        ['STAR COOL','CIM5','819543B','FAN BLADE, CONDENSER',''],
        ['STAR COOL','CIM5','819542C','FAN BLADE, EVAPORATOR',''],
        ['STAR COOL','CIM5','819506A','FILTER DRYER, R134A AND R513A',''],
        ['STAR COOL','CIM5','818738A','FILTER DRYER, R134A AND R513A (12 PCS)',''],
        ['STAR COOL','CIM5','818965B','FREQUENCY CONVERTER 2.1',''],
        ['STAR COOL','CIM5','818274C','FRONT PART, AIR EXCHANGE MODULE (75 CMH)',''],
        ['STAR COOL','CIM5','818656B','GASKET, COMPRESSOR STOP VALVE',''],
        ['STAR COOL','CIM5','818661B','GASKET, SERVICE VALVE LP',''],
        ['STAR COOL','CIM5','819501A','HIGH PRESSURE SWITCH',''],
        ['STAR COOL','CIM5','889740C','HOT GAS VALVE',''],
        ['STAR COOL','CIM5','819740B','HOT GAS VALVE, CIM 5',''],
        ['STAR COOL','CIM5','818551A','HUMIDITY SENSOR',''],
        ['STAR COOL','CIM5','814571B','INSULATION, ECONOMIZER',''],
        ['STAR COOL','CIM5','818523C','INTERLOCK, CONTACTOR',''],
        ['STAR COOL','CIM5','818527B','KEY PAD CIM 5',''],
        ['STAR COOL','CIM5','818517A','LED PCB, CIM 5',''],
        ['STAR COOL','CIM5','818906A','MAIN CIRCUIT BREAKER, CIM 5',''],
        ['STAR COOL','CIM5','818236B','MELT FUSE IT',''],
        ['STAR COOL','CIM5','818792A','MOTOR, CONDENSER FAN',''],
        ['STAR COOL','CIM5','818783A','MOTOR, EVAPORATOR FAN',''],
        ['STAR COOL','CIM5','814538D','MOUNTING RING, FILTER',''],
        ['STAR COOL','CIM5','818525B','ON/OFF CIM5',''],
        ['STAR COOL','CIM5','818652A','PERMANENT MAGNET',''],
        ['STAR COOL','CIM5','881550A','PLUG, EVAPORATOR SERVICE HOLE',''],
        ['STAR COOL','CIM5','819541C','PLUG, WATER INLET COUPLING',''],
        ['STAR COOL','CIM5','819540C','PLUG, WATER OUTLET COUPLING',''],
        ['STAR COOL','CIM5','818511B','POWER MEASUREMENT PCB, CIM 5',''],
        ['STAR COOL','CIM5','819503D','PRESSURE TRANSMITTER LP NSK',''],
        ['STAR COOL','CIM5','819504D','PRESSURE TRANSMITTER HP DST',''],
        ['STAR COOL','CIM5','814540B','RECEIVER',''],
        ['STAR COOL','CIM5','818739A','RECEIVER, WATER COOLED CONDENSER',''],
        ['STAR COOL','CIM5','819693D','SCREW, CONTROLLER DOOR CIM 6',''],
        ['STAR COOL','CIM5','818276A','SENSOR AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM5','818675B','SERVICE KIT, HOT GAS VALVE CIM 5',''],
        ['STAR COOL','CIM5','818623C','SERVICE VALVE, COMPRESSOR LP',''],
        ['STAR COOL','CIM5','818235B','SIGHT GLASS KIT, RECEIVER',''],
        ['STAR COOL','CIM5','818554A','SOLENOID COIL 18W 24VDC CIM 5',''],
        ['STAR COOL','CIM5','818553A','SOLENOID COIL 14W 24VDC CIM 5',''],
        ['STAR COOL','CIM5','886554B','SOLENOID COIL 11W 24VAC',''],
        ['STAR COOL','CIM5','818526C','TERMINAL BLOCK PCB, CIM 5',''],
        ['STAR COOL','CIM5','818639B','TERMINAL BLOCK, COMPRESSOR',''],
        ['STAR COOL','CIM5','818676B','TOOL, HOT GAS VALVE',''],
        ['STAR COOL','CIM5','818518B','TRANSFORMER 145 VA, CIM 5',''],
        ['STAR COOL','CIM5','818267B','WING SCREW KIT',''],
        ['STAR COOL','CIM5','INDND0078','ACEITE AFLOJATODO',''],
        ['STAR COOL','CIM5','INDND0411','ACEITE POLYOLESTER BVA',''],
        ['STAR COOL','CIM5','INDND3242','BROCA DE COBALTO HSS 3/16',''],
        ['STAR COOL','CIM5','INDND0259','CABLE VULCANIZADO 4 X10',''],
        ['STAR COOL','CIM5','RNDND0254','CINTA AISLANTE 3M',''],
        ['STAR COOL','CIM5','INDND0432','CINTA FOAM 1/8 X 2 X 9.14M',''],
        ['STAR COOL','CIM5','INDND0433','CINTILLO DE AMARRE 150MM',''],
        ['STAR COOL','CIM5','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['STAR COOL','CIM5','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['STAR COOL','CIM5','INDND1911','EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M',''],
        ['STAR COOL','CIM5','INDND0126','ESTAÑO 0.8',''],
        ['STAR COOL','CIM5','INDND2552','FILTRO SECADOR QDM-164 1/2 - QUALITY',''],
        ['STAR COOL','CIM5','INDND0024','FILTRO SEC. FIJO DE 1/2 FLARE - EK 164 STD',''],
        ['STAR COOL','CIM5','INDND2905','FORMADOR EMPAQUETADURA AVIACION 3H',''],
        ['STAR COOL','CIM5','INDND2237','FUNDENTE',''],
        ['STAR COOL','CIM5','INDND1545','MINI FUSIBLE DE VIDRIO 15 AMP',''],
        ['STAR COOL','CIM5','INDND1542','FUSIBLE DE VIDRIO 10 AMP',''],
        ['STAR COOL','CIM5','RNDND0318','FUSIBLE DE VIDRIO 20 AMP',''],
        ['STAR COOL','CIM5','INDND0120','GAS REFRIGERANTE R-134A X 13.60KG',''],
        ['STAR COOL','CIM5','RNDND0438','JABON LIQUIDO',''],
        ['STAR COOL','CIM5','INDND0016','LIJA FIERRO #40 ASA',''],
        ['STAR COOL','CIM5','INDND0079','LIMPIA CONTACTO',''],
        ['STAR COOL','CIM5','INDND0086','NITROGENO INDUSTRIAL 10 M3',''],
        ['STAR COOL','CIM5','INDND3074','MANGA TERMOCONTRAIBLE 15MM',''],
        ['STAR COOL','CIM5','INDND3322','MANGA TERMOCONTRAIBLE 20MM',''],
        ['STAR COOL','CIM5','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['STAR COOL','CIM5','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['STAR COOL','CIM5','INDND1555','PERNO HEX. RC. INOX 304 M16X50',''],
        ['STAR COOL','CIM5','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['STAR COOL','CIM5','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['STAR COOL','CIM5','INDND2768','RODAJE 6201 2RSH/C3',''],
        ['STAR COOL','CIM5','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['STAR COOL','CIM5','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['STAR COOL','CIM5','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['STAR COOL','CIM5','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['STAR COOL','CIM5','INDND5576','TERMINAL TUBULAR SOBREMOLDEADO ROJO 4MM 12AWG',''],
        ['STAR COOL','CIM5','INDND2711','TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG',''],
        ['STAR COOL','CIM5','INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG',''],
        ['STAR COOL','CIM5','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['STAR COOL','CIM5','INDND0087','SOLVENTE DIELECTRICO SDL-25',''],
        ['STAR COOL','CIM5','INDND1412','VALVULA DE ACCESO 1/4 X 7 CM',''],
        ['THERMO KING','MP5000','672454','COIL - CONDENSER (ALUMINUM FINS)',''],
        ['THERMO KING','MP5000','781924','FAN - CONDENSER',''],
        ['THERMO KING','MP5000','1040858','MOTOR - CONDENSER FAN',''],
        ['THERMO KING','MP5000','970599','HOUSING - EVAPORATOR (2 FANS)',''],
        ['THERMO KING','MP5000','427333','SENSOR - RETURN AIR',''],
        ['THERMO KING','MP5000','782096','FAN - EVAPORATOR 355MM 7 BLADES',''],
        ['THERMO KING','MP5000','941887','BRACKET - MOTOR',''],
        ['THERMO KING','MP5000','1040894','MOTOR - FAN',''],
        ['THERMO KING','MP5000','673471','COIL - EVAPORATOR',''],
        ['THERMO KING','MP5000','427334','SENSOR - DEFROST',''],
        ['THERMO KING','MP5000','422659','SENSOR - HUMIDITY',''],
        ['THERMO KING','MP5000','427338','SENSOR - CO2 RS485',''],
        ['THERMO KING','MP5000','420374','CABLE - SUPPLY RS485',''],
        ['THERMO KING','MP5000','612477','TUBE - VALVE TO COIL',''],
        ['THERMO KING','MP5000','672787','TANK - RECEIVER STANDARD',''],
        ['THERMO KING','MP5000','610786','DEHYDRATOR',''],
        ['THERMO KING','MP5000','671889','HEAT EXCHANGER - ECONOMIZER',''],
        ['THERMO KING','MP5000','618684','VALVE - SOLENOID VAPOR INJECTION',''],
        ['THERMO KING','MP5000','415460','COIL - VALVE',''],
        ['THERMO KING','MP5000','600731','KIT - TXV EXPANSION VALVE',''],
        ['THERMO KING','MP5000','612465','VALVE - BALL',''],
        ['THERMO KING','MP5000','617758','VALVE PWM',''],
        ['THERMO KING','MP5000','421423','SWITCH - LPCO',''],
        ['THERMO KING','MP5000','672853','TANK - RECEIVER WITH SHUT-OFF VALVE',''],
        ['THERMO KING','MP5000','425968','TRANSDUCER - SUCTION',''],
        ['THERMO KING','MP5000','610443','VALVE - EXPANSION',''],
        ['THERMO KING','MP5000','1020795','COMPRESSOR - SCROLL',''],
        ['THERMO KING','MP5000','919021','COVER - TERMINAL BOX',''],
        ['THERMO KING','MP5000','401377','KIT - THERMISTOR',''],
        ['THERMO KING','MP5000','414004','SWITCH - HPCO',''],
        ['THERMO KING','MP5000','612118','VALVE - SUCTION',''],
        ['THERMO KING','MP5000','612119','VALVE - DISCHARGE',''],
        ['THERMO KING','MP5000','335215','GASKET - VALVE SERVICE',''],
        ['THERMO KING','MP5000','400782','KIT - POWER CORD',''],
        ['THERMO KING','MP5000','401044','SENSOR KIT DEFROST/AMBIENT/RETURN/SUPPLY/COIL',''],
        ['THERMO KING','MP5000','451992','CABLE - POWER 19.2 METERS',''],
        ['THERMO KING','MP5000','452889','HEATER 1360W',''],
        ['THERMO KING','MP5000','453031','BASE - CONTROL BOX MP-5000',''],
        ['THERMO KING','MP5000','413595','SWITCH - ON/OFF',''],
        ['THERMO KING','MP5000','426427','TRANSFORMER',''],
        ['THERMO KING','MP5000','426424','BATTERY - MP-5000',''],
        ['THERMO KING','MP5000','426423','CONTROLLER - MP-5000',''],
        ['THERMO KING','MP5000','427238','BUSBAR COMB 63A 3 TAP-OFFS',''],
        ['THERMO KING','MP5000','427239','BUSBAR COMB 63A 4 TAP-OFFS',''],
        ['THERMO KING','MP5000','423820','CONTACTOR AC LC1D 3P 25A',''],
        ['THERMO KING','MP5000','426428','TRANSFORMER CURRENT MP-5000',''],
        ['THERMO KING','MP5000','415104','BREAKER CIRCUIT 25A',''],
        ['THERMO KING','MP5000','940841','DOOR - CONTROLLER MP-5000',''],
        ['THERMO KING','MP5000','426430','KEYPAD - CONTROLLER',''],
        ['THERMO KING','MP5000','427072','DISPLAY - LARGE',''],
        ['THERMO KING','MP5000','426752','MODULE - MP-5000',''],
        ['THERMO KING','MP5000','1021428','COMPRESSOR ASSEMBLY WITH MOTOR',''],
        ['THERMO KING','MP5000','221473','FILTER - COMPRESSOR SUCTION',''],
        ['THERMO KING','MP5000','941897','COVER - COMPRESSOR',''],
        ['THERMO KING','MP5000','427160','SENSOR - O2 RS485',''],
        ['THERMO KING','MP5000','427161','SENSOR - CO2 RS485',''],
        ['THERMO KING','MP5000','918252TKA','VENT - AIR COMPLETE',''],
        ['THERMO KING','MP5000','929522','BRACKET - AIR VENT',''],
        ['THERMO KING','MP5000','417238TKA','ACTUATOR',''],
        ['THERMO KING','MP5000','925687','DOOR - AFAM',''],
        ['THERMO KING','MP5000','937326','GRILLE - FRESH AIR',''],
        ['THERMO KING','MP5000','925661','LABEL - AFAM DOOR POSITION',''],
        ['THERMO KING','MP5000','INDND0411','ACEITE POLYOLESTER',''],
        ['THERMO KING','MP5000','INDND0078','AFLOJATODO',''],
        ['THERMO KING','MP5000','INDND4464','AGENTE LIMPIADOR DE SISTEMAS OPTEON SF FLUSH X 4.54 KG',''],
        ['THERMO KING','MP5000','INDND3876','ANILLO DE TEFLON 1/16 D 15 X 10 MM',''],
        ['THERMO KING','MP5000','INDND3565','ARANDELA DE PRESION INOX M6',''],
        ['THERMO KING','MP5000','INDND1391','ARANDELA PLANA INOX 1/4',''],
        ['THERMO KING','MP5000','INDND2543','BOMBA DE VACIO CPS VP6D 1/2HP',''],
        ['THERMO KING','MP5000','INDND0259','CABLE VULCANIZADO 4X10',''],
        ['THERMO KING','MP5000','RNDND0254','CINTA AISLANTE 3M',''],
        ['THERMO KING','MP5000','INDND0432','CINTA FOAM 1/8 X 2 X 9.4 M',''],
        ['THERMO KING','MP5000','INDND4185','CINTA PARA DUCTO 10MX48MM',''],
        ['THERMO KING','MP5000','INDND2786','CINTA VULCANIZANTE SCOTCH 23 3/4',''],
        ['THERMO KING','MP5000','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['THERMO KING','MP5000','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['THERMO KING','MP5000','INDND1718','CONTACTOR SCHNEIDER 32A 440V LC1D32R7',''],
        ['THERMO KING','MP5000','INDND4864','CONTACTOR TESYS DECA 3P AC-3 12A BOBINA 24VAC LC1D12B7',''],
        ['THERMO KING','MP5000','INDND1911','EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M',''],
        ['THERMO KING','MP5000','RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67',''],
        ['THERMO KING','MP5000','INDND0126','ESTAÑO 0.8',''],
        ['THERMO KING','MP5000','818738A','FILTER DRYER, R134A AND R513A (12 PCS)',''],
        ['THERMO KING','MP5000','RNDND0318','FUSIBLE DE VIDRIO 20 AMP',''],
        ['THERMO KING','MP5000','INDND2144','GAS MAP PRO',''],
        ['THERMO KING','MP5000','INDND0022','GAS REFRIGERANTE R-404A X 10.90KG',''],
        ['THERMO KING','MP5000','RNDND0438','JABON LIQUIDO',''],
        ['THERMO KING','MP5000','INDND1184','LIJA FIERRO #100',''],
        ['THERMO KING','MP5000','INDND0016','LIJA FIERRO #40',''],
        ['THERMO KING','MP5000','INDND0079','LIMPIA CONTACTO',''],
        ['THERMO KING','MP5000','INDND2300','MANGA TERMOCONTRAIBLE 25MM',''],
        ['THERMO KING','MP5000','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['THERMO KING','MP5000','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['THERMO KING','MP5000','INDND0738','MANGUERA CORRUGADA 1/2',''],
        ['THERMO KING','MP5000','INDND0279','MANGUERA CORRUGADA 3/8',''],
        ['THERMO KING','MP5000','INDND2111','MANGUERA CORRUGADA DE 1 PULGADA',''],
        ['THERMO KING','MP5000','INDND4520','ORING VITON 3-023',''],
        ['THERMO KING','MP5000','INDND3649','ORING VITON 2-014',''],
        ['THERMO KING','MP5000','INDND1104','PEGAMENTO SUPERFLEX INDUSTRIAL',''],
        ['THERMO KING','MP5000','INDND1417','PERNO HEX ZINC 1/4 X 1',''],
        ['THERMO KING','MP5000','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['THERMO KING','MP5000','INDND2789','PRENSA ESTOPA 1 NPT',''],
        ['THERMO KING','MP5000','INDND2838','PRENSA ESTOPA 3/8 PG11',''],
        ['THERMO KING','MP5000','INDND3078','RELE PROTECTOR DE FASE TIPO GALLETA GRV8-03',''],
        ['THERMO KING','MP5000','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['THERMO KING','MP5000','INDND2265','RIEL DIN PERFORADO',''],
        ['THERMO KING','MP5000','INDND0260','RODAMIENTO 6203',''],
        ['THERMO KING','MP5000','INDND0081','RODAMIENTO 6205',''],
        ['THERMO KING','MP5000','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['THERMO KING','MP5000','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['THERMO KING','MP5000','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['THERMO KING','MP5000','INDND1962','TERMINAL AISLADO TIPO BALA HEMBRA AZUL',''],
        ['THERMO KING','MP5000','INDND1097','TERMINAL AISLADO TIPO BALA MACHO AZUL',''],
        ['THERMO KING','MP5000','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['THERMO KING','MP5000','INDND0885','TERMINAL OJO VF5.5-6S 1/4',''],
        ['THERMO KING','MP5000','INDND2711','TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG',''],
        ['THERMO KING','MP5000','INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG',''],
        ['THERMO KING','MP5000','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['THERMO KING','MP5000','RNDND0802','TUBERIA DE COBRE 1/2',''],
        ['THERMO KING','MP5000','INDND0754','TUBERIA DE COBRE 1/4',''],
        ['THERMO KING','MP5000','INDND0357','TUBERIA DE COBRE 3/8',''],
        ['THERMO KING','MP5000','RNDND0440','TUBO DE COBRE 1/8 X 15M',''],
        ['THERMO KING','MP5000','INDND0169','TUERCA HEXAGONAL 1/4 ZINCADO',''],
        ['THERMO KING','MP5000','INDND1536','UNION SOLDABLE 1/4',''],
        ['THERMO KING','MP4000','417238TKA','ACTUATOR',''],
        ['THERMO KING','MP4000','918252','AIR VENT',''],
        ['THERMO KING','MP4000','418717','BATTERY LITHIUM MP-4000',''],
        ['THERMO KING','MP4000','413596','BLOCK - TERMINAL 4 POLE',''],
        ['THERMO KING','MP4000','413598','BLOCK - TERMINAL 8 POLE',''],
        ['THERMO KING','MP4000','918466','BRACKET MOTOR',''],
        ['THERMO KING','MP4000','RNDND0724','BREAKER CIRCUIT 25A',''],
        ['THERMO KING','MP4000','418716','CABLE SERIAL CM-4000A0 / PM4000',''],
        ['THERMO KING','MP4000','INDND5049','CABLE SUPPLY 420374',''],
        ['THERMO KING','MP4000','91-9331','CHANNEL - FRESH AIR',''],
        ['THERMO KING','MP4000','671923','COIL EVAPORATOR',''],
        ['THERMO KING','MP4000','415460','COIL VALVE LIQ',''],
        ['THERMO KING','MP4000','INDND1604','COMPRESSOR - SCROLL',''],
        ['THERMO KING','MP4000','69NT4320220','CONDENSER COIL',''],
        ['THERMO KING','MP4000','421636','CONNECTOR 10-PIN J2/J17',''],
        ['THERMO KING','MP4000','412446','CONTACTOR 25A',''],
        ['THERMO KING','MP4000','RNDND0064','CONTACTOR 30 AMP',''],
        ['THERMO KING','MP4000','100043106','CONTACTOR 12AMP',''],
        ['THERMO KING','MP4000','452295','CONTROLLER MP4000',''],
        ['THERMO KING','MP4000','418718','COVER EXPANSION BOARD',''],
        ['THERMO KING','MP4000','937354','DECAL R404A',''],
        ['THERMO KING','MP4000','418723','DOOR FRONT MP-4000 WHITE',''],
        ['THERMO KING','MP4000','610156','DRIER UNIVERSAL CONTAINER',''],
        ['THERMO KING','MP4000','818738A','FILTER DRYER, R134A AND R513A (12 PCS)',''],
        ['THERMO KING','MP4000','781683','EVAPORATOR FAN',''],
        ['THERMO KING','MP4000','78-1684','FAN - CONDENSER',''],
        ['THERMO KING','MP4000','781924','FAN CONDENSER ASSEMBLY',''],
        ['THERMO KING','MP4000','669842','FITTING FOR LPCO',''],
        ['THERMO KING','MP4000','559485','FLATWASHER',''],
        ['THERMO KING','MP4000','RNDND0624','FUSE HOLDER BLK MP4000',''],
        ['THERMO KING','MP4000','332510','GASKET - VALVE PLATE',''],
        ['THERMO KING','MP4000','332805','GASKET DISCHARGE',''],
        ['THERMO KING','MP4000','988244','GRILLE - EVAPORATOR',''],
        ['THERMO KING','MP4000','452889','HEATER ELEMENT 1360W BROWN',''],
        ['THERMO KING','MP4000','45-2451','HEATER ELEMENT 2000W',''],
        ['THERMO KING','MP4000','3504979','HEATER ELEMENT 750W 230V',''],
        ['THERMO KING','MP4000','422659','HUMIDITY SENSOR',''],
        ['THERMO KING','MP4000','INDND4844','KIT - POWER CORD',''],
        ['THERMO KING','MP4000','401044','KIT SENSOR MP4000',''],
        ['THERMO KING','MP4000','900331TKA','KIT SPACER FAN',''],
        ['THERMO KING','MP4000','INDND4843','KIT THERMISTOR THK',''],
        ['THERMO KING','MP4000','INDND1609','KIT TXV ECONOMIZER',''],
        ['THERMO KING','MP4000','420353','MODULE - AFAM+',''],
        ['THERMO KING','MP4000','418719','MODULE POWER MP4000',''],
        ['THERMO KING','MP4000','104-759','MOTOR CONDENSADOR TK',''],
        ['THERMO KING','MP4000','104691','MOTOR EVAPORADOR',''],
        ['THERMO KING','MP4000','47225','MP4000 CONTROL BOX',''],
        ['THERMO KING','MP4000','330727','O RING',''],
        ['THERMO KING','MP4000','927635','RAIL - DIN',''],
        ['THERMO KING','MP4000','421595','SENSOR CO2 RS485',''],
        ['THERMO KING','MP4000','RNDND0562','SENSOR USDA 2.5MM',''],
        ['THERMO KING','MP4000','781737','SHROUD FAN',''],
        ['THERMO KING','MP4000','414004','SWITCH HPCO',''],
        ['THERMO KING','MP4000','INDND1606','SWITCH LPCO',''],
        ['THERMO KING','MP4000','418763','TRANSFORMER MP4000',''],
        ['THERMO KING','MP4000','618179','TX VALVE ECONOMIZER',''],
        ['THERMO KING','MP4000','669900','VALVE EXPANSION ECONOMIZER',''],
        ['THERMO KING','MP4000','RNDND0130','VALVE EXPANSION',''],
        ['THERMO KING','MP4000','612470','VALVE SOLENOID',''],
        ['THERMO KING','MP4000','617758','VALVE DIGITAL',''],
        ['THERMO KING','MP4000','INDND0411','ACEITE POLYOLESTER',''],
        ['THERMO KING','MP4000','INDND0078','AFLOJATODO',''],
        ['THERMO KING','MP4000','INDND4464','AGENTE LIMPIADOR DE SISTEMAS OPTEON SF FLUSH X 4.54 KG',''],
        ['THERMO KING','MP4000','INDND3876','ANILLO DE TEFLON 1/16 D 15 X 10 MM',''],
        ['THERMO KING','MP4000','INDND3565','ARANDELA DE PRESION INOX M6',''],
        ['THERMO KING','MP4000','INDND1391','ARANDELA PLANA INOX 1/4',''],
        ['THERMO KING','MP4000','INDND2543','BOMBA DE VACIO CPS VP6D 1/2HP',''],
        ['THERMO KING','MP4000','INDND0259','CABLE VULCANIZADO 4X10',''],
        ['THERMO KING','MP4000','RNDND0254','CINTA AISLANTE 3M',''],
        ['THERMO KING','MP4000','INDND0432','CINTA FOAM 1/8 X 2 X 9.4 M',''],
        ['THERMO KING','MP4000','INDND4185','CINTA PARA DUCTO 10MX48MM',''],
        ['THERMO KING','MP4000','INDND2786','CINTA VULCANIZANTE SCOTCH 23 3/4',''],
        ['THERMO KING','MP4000','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['THERMO KING','MP4000','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['THERMO KING','MP4000','INDND1718','CONTACTOR SCHNEIDER 32A 440V LC1D32R7',''],
        ['THERMO KING','MP4000','INDND4864','CONTACTOR TESYS DECA 3P AC-3 12A BOBINA 24VAC LC1D12B7',''],
        ['THERMO KING','MP4000','INDND1911','EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M',''],
        ['THERMO KING','MP4000','RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67',''],
        ['THERMO KING','MP4000','INDND0126','ESTAÑO 0.8',''],
        ['THERMO KING','MP4000','RNDND0318','FUSIBLE DE VIDRIO 20 AMP',''],
        ['THERMO KING','MP4000','INDND2144','GAS MAP PRO',''],
        ['THERMO KING','MP4000','INDND0022','GAS REFRIGERANTE R-404A X 10.90KG',''],
        ['THERMO KING','MP4000','RNDND0438','JABON LIQUIDO',''],
        ['THERMO KING','MP4000','INDND1184','LIJA FIERRO #100',''],
        ['THERMO KING','MP4000','INDND0016','LIJA FIERRO #40',''],
        ['THERMO KING','MP4000','INDND0079','LIMPIA CONTACTO',''],
        ['THERMO KING','MP4000','INDND2300','MANGA TERMOCONTRAIBLE 25MM',''],
        ['THERMO KING','MP4000','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['THERMO KING','MP4000','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['THERMO KING','MP4000','INDND0738','MANGUERA CORRUGADA 1/2',''],
        ['THERMO KING','MP4000','INDND0279','MANGUERA CORRUGADA 3/8',''],
        ['THERMO KING','MP4000','INDND2111','MANGUERA CORRUGADA DE 1 PULGADA',''],
        ['THERMO KING','MP4000','INDND4520','ORING VITON 3-023',''],
        ['THERMO KING','MP4000','INDND3649','ORING VITON 2-014',''],
        ['THERMO KING','MP4000','INDND1104','PEGAMENTO SUPERFLEX INDUSTRIAL',''],
        ['THERMO KING','MP4000','INDND1417','PERNO HEX ZINC 1/4 X 1',''],
        ['THERMO KING','MP4000','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['THERMO KING','MP4000','INDND2789','PRENSA ESTOPA 1 NPT',''],
        ['THERMO KING','MP4000','INDND2838','PRENSA ESTOPA 3/8 PG11',''],
        ['THERMO KING','MP4000','INDND3078','RELE PROTECTOR DE FASE TIPO GALLETA GRV8-03',''],
        ['THERMO KING','MP4000','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['THERMO KING','MP4000','INDND2265','RIEL DIN PERFORADO',''],
        ['THERMO KING','MP4000','INDND0260','RODAMIENTO 6203',''],
        ['THERMO KING','MP4000','INDND0081','RODAMIENTO 6205',''],
        ['THERMO KING','MP4000','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['THERMO KING','MP4000','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['THERMO KING','MP4000','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['THERMO KING','MP4000','INDND1962','TERMINAL AISLADO TIPO BALA HEMBRA AZUL',''],
        ['THERMO KING','MP4000','INDND1097','TERMINAL AISLADO TIPO BALA MACHO AZUL',''],
        ['THERMO KING','MP4000','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['THERMO KING','MP4000','INDND0885','TERMINAL OJO VF5.5-6S 1/4',''],
        ['THERMO KING','MP4000','INDND2711','TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG',''],
        ['THERMO KING','MP4000','INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG',''],
        ['THERMO KING','MP4000','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['THERMO KING','MP4000','RNDND0802','TUBERIA DE COBRE 1/2',''],
        ['THERMO KING','MP4000','INDND0754','TUBERIA DE COBRE 1/4',''],
        ['THERMO KING','MP4000','INDND0357','TUBERIA DE COBRE 3/8',''],
        ['THERMO KING','MP4000','RNDND0440','TUBO DE COBRE 1/8 X 15M',''],
        ['THERMO KING','MP4000','INDND0169','TUERCA HEXAGONAL 1/4 ZINCADO',''],
        ['THERMO KING','MP4000','INDND1536','UNION SOLDABLE 1/4',''],
        ['CARRIER','TODOS','10-00439-01','AMPERIMETRO',''],
        ['CARRIER','TODOS','22-50088-01','CAPACITOR 15UF',''],
        ['CARRIER','TODOS','22-50088-00','CAPACITOR 20UF',''],
        ['CARRIER','TODOS','INDND0431','CAPACITOR DE 5UF',''],
        ['CARRIER','TODOS','66U1-7842-13','CIRCUIT BREAKER 460VAC 25AMP',''],
        ['CARRIER','TODOS','14-00247-20','COIL',''],
        ['CARRIER','TODOS','14-00393-10','COIL 2010-2012',''],
        ['CARRIER','TODOS','76-00748-00','COIL EVAPORATOR',''],
        ['CARRIER','TODOS','14-00393-10','COIL EVV',''],
        ['CARRIER','TODOS','14-00247-20','COIL VALVE EXPANSION 2008-2010',''],
        ['CARRIER','TODOS','14-00230-24SV','COIL SOLENOID',''],
        ['CARRIER','TODOS','14-01091-02','COIL SOLENOID 24V',''],
        ['CARRIER','TODOS','18-10134-23','COMPRESSOR SCROLL AZUL',''],
        ['CARRIER','TODOS','18-10178-20','COMPRESSOR SCROLL PLOMO',''],
        ['CARRIER','TODOS','18-10129-20SV','COMPRESSOR CONT 41CFM',''],
        ['CARRIER','TODOS','69NT43-202-20','CONDENSOR COIL',''],
        ['CARRIER','TODOS','100043106','CONTACTOR 12AMP 10-00431-06',''],
        ['CARRIER','TODOS','RNDND0064','CONTACTOR 30 AMP 10-00431-07',''],
        ['CARRIER','TODOS','120057900','MICROLINK 3',''],
        ['CARRIER','TODOS','1256002','MICROLINK 2I',''],
        ['CARRIER','TODOS','400052000','COUPLING M13 LOW',''],
        ['CARRIER','TODOS','400052001','COUPLING M15 HIGH',''],
        ['CARRIER','TODOS','69NT20-2083','COVER JUNCTION BOX',''],
        ['CARRIER','TODOS','12-00433-03RP','DISPLAY',''],
        ['CARRIER','TODOS','14-00393-00SV','EEV 2010-2012',''],
        ['CARRIER','TODOS','38-00585-00','FAN CONDENSER',''],
        ['CARRIER','TODOS','38-00599-00','FAN EVAPORATOR',''],
        ['CARRIER','TODOS','INDND1585','THERMISTOR TEMP SENSOR',''],
        ['CARRIER','TODOS','3504979','HEATER BAR 750W',''],
        ['CARRIER','TODOS','296660300','HEATER 750V 230V',''],
        ['CARRIER','TODOS','14-00221-04','INDICATOR SIGHTGLASS R134A',''],
        ['CARRIER','TODOS','79-66669-02','KEYPAD ASSY',''],
        ['CARRIER','TODOS','INDND2389','KIT EMPAQUETADURA CARRIER',''],
        ['CARRIER','TODOS','12-00495-02SV','KIT AMBIENT/DEFROST SENSOR',''],
        ['CARRIER','TODOS','12-00425-00','MODULE CONTROLLER MICRO-LINK 2i',''],
        ['CARRIER','TODOS','INDND0904','MOTOR EVAPORADOR TRIFASICO',''],
        ['CARRIER','TODOS','54-00586-20','MOTOR CONDENSER',''],
        ['CARRIER','TODOS','54-00585-20','MOTOR EVAPORADOR MONOFASICO',''],
        ['CARRIER','TODOS','30-00407-02SV','PACK BATTERY DATACORDER',''],
        ['CARRIER','TODOS','10-00388-00','POWERPACK STEPPER MOTOR',''],
        ['CARRIER','TODOS','12-00500-01SV','SENSOR COMBINATION RETURN',''],
        ['CARRIER','TODOS','12-00745-00SV','SENSOR HUMIDITY W/BRACKET',''],
        ['CARRIER','TODOS','12-00395-01SV','SENSOR THERMISTOR SUPPLY',''],
        ['CARRIER','TODOS','12-00309-06','SWITCH HIGH PRESSURE HPS',''],
        ['CARRIER','TODOS','RNDND0131','SWITCH THERMOSTAT',''],
        ['CARRIER','TODOS','65-00185-03','TANQUE RECIBIDOR',''],
        ['CARRIER','TODOS','17-40075-05','TERMINAL PLATE',''],
        ['CARRIER','TODOS','12-00352-00','TRANSDUCER PRESSURE HIGH',''],
        ['CARRIER','TODOS','12-00352-07SV','TRANSDUCER PRESSURE LOW',''],
        ['CARRIER','TODOS','INDND2612','TRANSFORMER ELECTRIC CONTROL 440/24V',''],
        ['CARRIER','TODOS','12-00655-01','TRANSDUCER PRIME LINE',''],
        ['CARRIER','TODOS','14-00247-01','VALVE',''],
        ['CARRIER','TODOS','140027308','VALVE HERMETIC TXV THINLINE',''],
        ['CARRIER','TODOS','14-00204-04','VALVE DISCHARGE DPRV',''],
        ['CARRIER','TODOS','14-00247-01','VALVE EVAPORATOR EXPANSION',''],
        ['CARRIER','TODOS','14-00232-33','VALVE EXPANSION',''],
        ['CARRIER','TODOS','14-00206-00','VALVE SERVICE',''],
        ['CARRIER','TODOS','14-00206-01','VALVE SERVICE',''],
        ['CARRIER','TODOS','14-00353-04','VALVE STEPPER MOTOR',''],
        ['CARRIER','TODOS','810147200','TUBE ASSY DISCHARGE',''],
        ['CARRIER','TODOS','14-00232-03','VALVE TXV',''],
        ['CARRIER','TODOS','INDND2543','BOMBA DE VACIO CPS VP6D',''],
        ['CARRIER','TODOS','RNDND0293','CHISPEROS',''],
        ['CARRIER','TODOS','INDND0411','ACEITE POLYOLESTER',''],
        ['CARRIER','TODOS','INDND0078','AFLOJATODO',''],
        ['CARRIER','TODOS','IND0672','ARANDELA DE PRESION ZINC 1/4',''],
        ['CARRIER','TODOS','INDND0175','BROCA DE COBALTO HSS 1/4',''],
        ['CARRIER','TODOS','INDND3242','BROCA DE COBALTO HSS 3/16',''],
        ['CARRIER','TODOS','INDND0134','BROCA DE COBALTO HSS 3/8',''],
        ['CARRIER','TODOS','INDND1448','CABLE FLEXIBLE AUTOMOTRIZ GTP 10AWG',''],
        ['CARRIER','TODOS','RNDND0558','CABLE TW-80 N° 14 AWG',''],
        ['CARRIER','TODOS','INDND0199','CABLE VULCANIZADO 3X16',''],
        ['CARRIER','TODOS','INDND0259','CABLE VULCANIZADO 4X10',''],
        ['CARRIER','TODOS','INDND1589','CAÑA DE SOLDAR',''],
        ['CARRIER','TODOS','RNDND0254','CINTA AISLANTE 3M',''],
        ['CARRIER','TODOS','INDND0432','CINTA FOAM 1/8 X 2 X 9.4 M',''],
        ['CARRIER','TODOS','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['CARRIER','TODOS','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['CARRIER','TODOS','INDND1911','EMPAQUE DE ASBESTO',''],
        ['CARRIER','TODOS','RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67',''],
        ['CARRIER','TODOS','INDND5002','EXTENSION CORRIENTE 3X16 30MTS',''],
        ['CARRIER','TODOS','818738A','FILTER DRYER, R134A AND R513A (12 PCS)',''],
        ['CARRIER','TODOS','INDND0194','FUSIBLE TIPO UÑA 10 AMP',''],
        ['CARRIER','TODOS','INDND0193','FUSIBLE TIPO UÑA 5 AMP',''],
        ['CARRIER','TODOS','INDND0648','FUSIBLE TIPO UÑA 7.5 AMP',''],
        ['CARRIER','TODOS','INDND2144','GAS MAP PRO',''],
        ['CARRIER','TODOS','INDND0120','GAS REFRIGERANTE R-134A X 13.60KG',''],
        ['CARRIER','TODOS','RNDND0423','GRASA GRA LGMT 3/1',''],
        ['CARRIER','TODOS','RNDND0438','JABON LIQUIDO',''],
        ['CARRIER','TODOS','INDND0054','LIJA FIERRO #120',''],
        ['CARRIER','TODOS','INDND0079','LIMPIA CONTACTO',''],
        ['CARRIER','TODOS','INDND2300','MANGA TERMOCONTRAIBLE 25MM',''],
        ['CARRIER','TODOS','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['CARRIER','TODOS','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['CARRIER','TODOS','INDND1417','PERNO HEX ZINC 1/4 X 1',''],
        ['CARRIER','TODOS','INDND0108','PINTURA EN SPRAY ALUMINIO',''],
        ['CARRIER','TODOS','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['CARRIER','TODOS','INDND1547','PORTA FUSIBLE AEREO',''],
        ['CARRIER','TODOS','RNDND0100','RELAY 24V 720W',''],
        ['CARRIER','TODOS','INDND0171','REMACHE POP DE ALUMINIO 3/16X1',''],
        ['CARRIER','TODOS','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['CARRIER','TODOS','RNDND0260','RODAMIENTO 6203',''],
        ['CARRIER','TODOS','INDND0081','RODAMIENTO 6205',''],
        ['CARRIER','TODOS','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['CARRIER','TODOS','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['CARRIER','TODOS','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['CARRIER','TODOS','INDND0087','SOLVENTE DIELECTRICO SDL-25',''],
        ['CARRIER','TODOS','INDND1962','TERMINAL AISLADO TIPO BALA HEMBRA AZUL',''],
        ['CARRIER','TODOS','INDND1097','TERMINAL AISLADO TIPO BALA MACHO AZUL',''],
        ['CARRIER','TODOS','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['CARRIER','TODOS','RNDND0802','TUBERIA DE COBRE 1/2',''],
        ['CARRIER','TODOS','INDND0754','TUBERIA DE COBRE 1/4',''],
        ['CARRIER','TODOS','INDND0169','TUERCA HEXAGONAL 1/4 ZINCADO',''],
        ['DAIKIN','DAIKIN','1612576','ACCESS PANEL EVAPORADOR',''],
        ['DAIKIN','DAIKIN','1387173','AIR COOLED CONDENSER',''],
        ['DAIKIN','DAIKIN','1588349','AIR DISCHARGE GRILLE',''],
        ['DAIKIN','DAIKIN','0954633','BOARD PTCT BOBINA DE CORRIENTE',''],
        ['DAIKIN','DAIKIN','1270408','BODY SMV',''],
        ['DAIKIN','DAIKIN','1270390','COIL SMV',''],
        ['DAIKIN','DAIKIN','1266290','COIL SOLENOID VALVE',''],
        ['DAIKIN','DAIKIN','1315426','COMPRESOR',''],
        ['DAIKIN','DAIKIN','0954936','CONDENSER FAN',''],
        ['DAIKIN','DAIKIN','1787494','CONTROL BOX COMPLETO',''],
        ['DAIKIN','DAIKIN','11739318','CONTROL BOX COVER WELDING',''],
        ['DAIKIN','DAIKIN','1295553','CONTROL PANEL',''],
        ['DAIKIN','DAIKIN','1010815','DISPLAY',''],
        ['DAIKIN','DAIKIN','1241385','DRIER ASSY',''],
        ['DAIKIN','DAIKIN','1381120','EARTH LEAKAGE CIRCUIT BREAKER',''],
        ['DAIKIN','DAIKIN','1254538','ELECTRONIC EXPANSION VALVE BODY ASSY',''],
        ['DAIKIN','DAIKIN','138143J','ELECTRONIC EXPANSION VALVE COIL',''],
        ['DAIKIN','DAIKIN','1787470','EVAPORATOR ASSY',''],
        ['DAIKIN','DAIKIN','0777519','FAN EVAPORADOR',''],
        ['DAIKIN','DAIKIN','0980618','FANBLADE OUTSIDE',''],
        ['DAIKIN','DAIKIN','INDND2552','FILTRO SECADOR QDM-164 1/2 QUALITY',''],
        ['DAIKIN','DAIKIN','INDND0024','FILTRO SEC. FIJO DE 1/2 FLARE EK 164 STD',''],
        ['DAIKIN','DAIKIN','1787456','FRONT PLATE',''],
        ['DAIKIN','DAIKIN','003065J','FUSE CONTROLLER',''],
        ['DAIKIN','DAIKIN','1241378','HIGH PRESSURE SWITCH',''],
        ['DAIKIN','DAIKIN','1587959','HIGH PRESSURE TRANSDUCER HPT',''],
        ['DAIKIN','DAIKIN','1679A30','KIT BATTERY',''],
        ['DAIKIN','DAIKIN','1561796','LOW FREQUENCY TRANSFORMER',''],
        ['DAIKIN','DAIKIN','1587942','LOW PRESSURE TRANSDUCER LPT',''],
        ['DAIKIN','DAIKIN','119891J','MAGNETIC CONTACTOR COMPRESSOR',''],
        ['DAIKIN','DAIKIN','119893J','MAGNETIC CONTACTOR FANS',''],
        ['DAIKIN','DAIKIN','124149J','MAGNETIC CONTACTOR PHASE CORRECTION',''],
        ['DAIKIN','DAIKIN','0955333','MOTOR EVAPORADOR',''],
        ['DAIKIN','DAIKIN','2089294','NEW COIL VALVE EVV',''],
        ['DAIKIN','DAIKIN','2075473','NEW VALVE EXP EVV',''],
        ['DAIKIN','DAIKIN','098333J','SENSOR COMP SUCTION TEMP',''],
        ['DAIKIN','DAIKIN','156282J','SENSOR EIS',''],
        ['DAIKIN','DAIKIN','156283J','SENSOR EOS',''],
        ['DAIKIN','DAIKIN','0798321','SENSOR AMBIENT AIR TEMP',''],
        ['DAIKIN','DAIKIN','098332J','SENSOR DISCHARGE PIPE TEMP',''],
        ['DAIKIN','DAIKIN','1787247','SOLENOID VALVE BODY',''],
        ['DAIKIN','DAIKIN','1256116','TERMINAL STRIP VER. 1',''],
        ['DAIKIN','DAIKIN','1679137','TERMINAL STRIP VER. 2',''],
        ['DAIKIN','DAIKIN','2346269','CONTROL BOX COVER WELDING ASSY',''],
        ['DAIKIN','DAIKIN','1780309','BUSHING',''],
        ['DAIKIN','DAIKIN','2272856','HEXAGON HEAD BOLT',''],
        ['DAIKIN','DAIKIN','1938968','ROLLE',''],
        ['DAIKIN','DAIKIN','1136539','CLAMP',''],
        ['DAIKIN','DAIKIN','112894J','PACKING',''],
        ['DAIKIN','DAIKIN','1938944','CONTROL PANEL WITH SHEET KEY',''],
        ['DAIKIN','DAIKIN','0907062','SEAL WASHER',''],
        ['DAIKIN','DAIKIN','2272863','PAN HEAD MACHINE SCREW',''],
        ['DAIKIN','DAIKIN','INDND0078','ACEITE AFLOJATODO',''],
        ['DAIKIN','DAIKIN','IND411','ACEITE POE 68',''],
        ['DAIKIN','DAIKIN','INDND0405','ADHESIVO POLIURETANO 540 GRIS 600ML',''],
        ['DAIKIN','DAIKIN','INDND4837','SILICON SEALANT 590ML COLOR BLANCO',''],
        ['DAIKIN','DAIKIN','INDND4836','SILICON SEALANT 590ML COLOR GREY',''],
        ['DAIKIN','DAIKIN','INDND2543','BOMBA DE VACIO CPS VP6D 1/2HP',''],
        ['DAIKIN','DAIKIN','INDND0175','BROCA 1/4',''],
        ['DAIKIN','DAIKIN','INDND0176','BROCA 3/16',''],
        ['DAIKIN','DAIKIN','INDND0259','CABLE VULCANIZADO 4 X10',''],
        ['DAIKIN','DAIKIN','RNDND0254','CINTA AISLANTE 3M',''],
        ['DAIKIN','DAIKIN','INDND0432','CINTA FOAM 1/8 X 2 X 9.4 M',''],
        ['DAIKIN','DAIKIN','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['DAIKIN','DAIKIN','RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67',''],
        ['DAIKIN','DAIKIN','30650','FUSIBLE 10A',''],
        ['DAIKIN','DAIKIN','INDND0120','GAS REFRIGERANTE R-134A DE 13.600KG',''],
        ['DAIKIN','DAIKIN','INDN0120','GAS R134A',''],
        ['DAIKIN','DAIKIN','RNDND0438','JABON LIQUIDO',''],
        ['DAIKIN','DAIKIN','INDND0054','LIJA FIERRO #120',''],
        ['DAIKIN','DAIKIN','INDND0079','LIMPIA CONTACTO',''],
        ['DAIKIN','DAIKIN','INDND3136','PERNO HEX. INOX. M6 X 24',''],
        ['DAIKIN','DAIKIN','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['DAIKIN','DAIKIN','INDND0170','REMACHE POP DE ALUMINIO 1/4X1',''],
        ['DAIKIN','DAIKIN','INDND0171','REMACHE POP DE ALUMINIO 3/16X1',''],
        ['DAIKIN','DAIKIN','INDND0260','RODAMIENTO 6203',''],
        ['DAIKIN','DAIKIN','INDND0081','RODAMIENTO 6205',''],
        ['DAIKIN','DAIKIN','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['DAIKIN','DAIKIN','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['DAIKIN','DAIKIN','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['DAIKIN','DAIKIN','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['DAIKIN','DAIKIN','INDND0882','TERMINAL OJO 5.5-8 / 12-10',''],
        ['DAIKIN','DAIKIN','INDND0017','TRAPO INDUSTRIAL',''],
        ['DAIKIN','DAIKIN','INDND3344','TUBERIA CONDUIT FLEXIBLE C/F PVC 3/8','']
    ];
}

function zgroupAsegurarCatalogosV12(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS modelos_reefer_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        marca_equipo VARCHAR(100) NOT NULL,
        controlador VARCHAR(100) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_modelo_reefer (marca_equipo, controlador),
        INDEX idx_modelo_reefer_marca (marca_equipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // MP400 no existe como controlador Thermo King. Desactivar cualquier registro antiguo.
    try {
        $pdo->exec("UPDATE modelos_reefer_catalogo SET activo = 0 WHERE UPPER(TRIM(marca_equipo)) = 'THERMO KING' AND UPPER(REPLACE(TRIM(controlador), ' ', '')) = 'MP400'");
    } catch (Throwable $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS modelos_genset_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        marca_equipo VARCHAR(100) NOT NULL,
        controlador VARCHAR(100) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_modelo_genset (marca_equipo, controlador),
        INDEX idx_modelo_genset_marca (marca_equipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_reefer_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        marca_equipo VARCHAR(100) NOT NULL,
        controlador VARCHAR(100) NOT NULL,
        codigo VARCHAR(60) DEFAULT NULL,
        detalle VARCHAR(220) NOT NULL,
        unidad VARCHAR(40) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_rep_reefer_modelo (marca_equipo, controlador),
        INDEX idx_rep_reefer_codigo (codigo),
        INDEX idx_rep_reefer_detalle (detalle)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS zgroup_config (clave VARCHAR(100) PRIMARY KEY, valor VARCHAR(220) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $seedStmt = $pdo->prepare("SELECT valor FROM zgroup_config WHERE clave = 'catalogos_v12_sembrados' LIMIT 1");
    $seedStmt->execute();
    $yaSembrado = (string)$seedStmt->fetchColumn() !== '';
    if (!$yaSembrado) {
        $modelosReefer = [
            ['THERMO KING','MP3000'],['THERMO KING','MP4000'],['THERMO KING','MP5000'],
            ['CARRIER','MICROLINK 2I'],['CARRIER','MICROLINK 3'],['CARRIER','MICROLINK 5'],
            ['STAR COOL','CIM5'],['STAR COOL','CIM6'],['DAIKIN','DAIKIN']
        ];
        $insR = $pdo->prepare("INSERT INTO modelos_reefer_catalogo (marca_equipo, controlador, activo) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE activo = VALUES(activo)");
        foreach ($modelosReefer as $m) $insR->execute($m);
        $modelosGenset = [['THERMO KING','SG-3000'],['THERMO KING','SG-5000']];
        $insG = $pdo->prepare("INSERT INTO modelos_genset_catalogo (marca_equipo, controlador, activo) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE activo = VALUES(activo)");
        foreach ($modelosGenset as $m) $insG->execute($m);
        $count = (int)$pdo->query("SELECT COUNT(*) FROM repuestos_reefer_catalogo")->fetchColumn();
        if ($count === 0) {
            $ins = $pdo->prepare('INSERT INTO repuestos_reefer_catalogo (marca_equipo, controlador, codigo, detalle, unidad, activo) VALUES (?, ?, ?, ?, ?, 1)');
            foreach (zgroupMaterialesReeferV12() as $r) $ins->execute([$r[0], $r[1], $r[2] !== '' ? $r[2] : null, $r[3], $r[4]]);
        }
        $pdo->prepare("INSERT INTO zgroup_config (clave,valor) VALUES ('catalogos_v12_sembrados',?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)")->execute([date('Y-m-d H:i:s')]);
    }
}

function asegurarCatalogoClientesCotizacionesPanel(PDO $pdo) {
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
        controlador VARCHAR(100) DEFAULT NULL,
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

    panelAgregarColumnaSiFalta($pdo, 'maquinas_catalogo', 'modelo_equipo', "VARCHAR(100) DEFAULT NULL AFTER marca_equipo");
    panelAgregarColumnaSiFalta($pdo, 'maquinas_catalogo', 'anio_fabricacion', "SMALLINT UNSIGNED DEFAULT NULL AFTER controlador");
    foreach (['ruc'=>"VARCHAR(30) DEFAULT NULL",'contacto'=>"VARCHAR(160) DEFAULT NULL",'telefono'=>"VARCHAR(80) DEFAULT NULL",'correo'=>"VARCHAR(180) DEFAULT NULL",'direccion'=>"VARCHAR(255) DEFAULT NULL",'origen'=>"VARCHAR(30) DEFAULT NULL"] as $c=>$d) panelAgregarColumnaSiFalta($pdo,'clientes_catalogo',$c,$d);
    foreach (['ticket_ref'=>"VARCHAR(30) DEFAULT NULL",'cotizacion_odoo'=>"VARCHAR(80) DEFAULT NULL",'origen'=>"VARCHAR(30) DEFAULT NULL"] as $c=>$d) panelAgregarColumnaSiFalta($pdo,'cotizaciones_catalogo',$c,$d);
    foreach (['modelo_equipo'=>"VARCHAR(100) DEFAULT NULL",'anio_fabricacion'=>"SMALLINT UNSIGNED DEFAULT NULL",'tamano_contenedor'=>"VARCHAR(60) DEFAULT NULL",'modalidad_comercial'=>"VARCHAR(40) DEFAULT NULL",'tipo_equipo'=>"VARCHAR(30) DEFAULT NULL",'ticket_ref'=>"VARCHAR(30) DEFAULT NULL",'cliente_nombre'=>"VARCHAR(180) DEFAULT NULL",'origen'=>"VARCHAR(30) DEFAULT NULL"] as $c=>$d) panelAgregarColumnaSiFalta($pdo,'contenedores_catalogo',$c,$d);

    $pdo->exec("CREATE TABLE IF NOT EXISTS odoo_servicios_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,ticket_ref VARCHAR(30) NOT NULL UNIQUE,odoo_ticket_id INT DEFAULT NULL,
        numero_reporte VARCHAR(30) DEFAULT NULL,cotizacion VARCHAR(80) DEFAULT NULL,cliente_id INT DEFAULT NULL,cliente_nombre VARCHAR(180) DEFAULT NULL,
        ruc VARCHAR(30) DEFAULT NULL,contacto VARCHAR(160) DEFAULT NULL,telefono VARCHAR(80) DEFAULT NULL,correo VARCHAR(180) DEFAULT NULL,direccion VARCHAR(255) DEFAULT NULL,
        fecha_servicio DATE DEFAULT NULL,equipo_soporte VARCHAR(120) DEFAULT NULL,asignado_a VARCHAR(160) DEFAULT NULL,tipo_servicio VARCHAR(160) DEFAULT NULL,
        modalidad_comercial VARCHAR(40) DEFAULT NULL,tipo_instalacion VARCHAR(80) DEFAULT NULL,tipo_equipo VARCHAR(30) DEFAULT NULL,tamano_contenedor VARCHAR(60) DEFAULT NULL,
        numero_equipo VARCHAR(60) DEFAULT NULL,serie_unidad VARCHAR(100) DEFAULT NULL,marca_equipo VARCHAR(100) DEFAULT NULL,modelo_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,anio_fabricacion SMALLINT UNSIGNED DEFAULT NULL,refrigerante VARCHAR(50) DEFAULT NULL,titulo_ticket VARCHAR(255) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,importado_en DATETIME NOT NULL,actualizado_en DATETIME NOT NULL,
        INDEX idx_odoo_servicio_cliente(cliente_id),INDEX idx_odoo_servicio_reporte(numero_reporte),INDEX idx_odoo_servicio_equipo(numero_equipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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
    try { $pdo->exec("ALTER TABLE repuestos_catalogo DROP INDEX codigo"); } catch (Throwable $e) {}
    try { $pdo->exec("CREATE INDEX idx_repuestos_catalogo_codigo ON repuestos_catalogo (codigo)"); } catch (Throwable $e) {}
    zgroupAsegurarCatalogoGeneradores($pdo);
}


function normalizarCatalogoTexto($s) {
    $s = trim((string)$s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return $s;
}

function clienteCatalogoDuplicado(PDO $pdo, $nombre) {
    $objetivo = normalizarNombreTecnico($nombre);
    if ($objetivo === '') return false;
    $stmt = $pdo->query('SELECT id, nombre FROM clientes_catalogo WHERE activo = 1');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        if (normalizarNombreTecnico($c['nombre'] ?? '') === $objetivo) return $c;
    }
    return false;
}

asegurarCatalogoClientesCotizacionesPanel($pdo);
zgroupAsegurarCatalogosV12($pdo);

// Procesar formularios administrativos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectQS = '';

    /* ---------- Acciones múltiples del panel ---------- */
    if (isset($_POST['panel_bulk_action'])) {
        $accionBulk = trim((string)$_POST['panel_bulk_action']);
        $csrfBulk = (string)($_POST['panel_csrf'] ?? '');

        if (!hash_equals((string)($_SESSION['panel_csrf'] ?? ''), $csrfBulk)) {
            header('Location: panel.php?error=bulk_csrf');
            exit;
        }

        try {
            if ($accionBulk === 'eliminar_preliminares') {
                $idsBulk = panelIdsPost($_POST['ids'] ?? []);
                if (!$idsBulk) {
                    header('Location: panel.php?error=bulk_sin_seleccion&tipo=preliminares');
                    exit;
                }
                $pdo->beginTransaction();
                $rBulk = panelEliminarPreliminares($pdo, $idsBulk);
                $pdo->commit();
                registrarEventoPanel(
                    $pdo,
                    'preliminares_eliminadas',
                    'Preliminares eliminadas',
                    'Cantidad: ' . (int)$rBulk['preliminares']
                );
                header('Location: panel.php?ok=bulk_preliminares&cantidad=' . (int)$rBulk['preliminares']);
                exit;
            }

            if ($accionBulk === 'eliminar_informes') {
                $idsBulk = panelIdsPost($_POST['ids'] ?? []);
                if (!$idsBulk) {
                    header('Location: panel.php?error=bulk_sin_seleccion&tipo=informes');
                    exit;
                }
                $pdo->beginTransaction();
                $rBulk = panelEliminarInformes($pdo, $idsBulk);
                $pdo->commit();
                registrarEventoPanel(
                    $pdo,
                    'informes_eliminados_multiples',
                    'Informes técnicos eliminados',
                    'Informes: ' . (int)$rBulk['informes'] . ' | PDF: ' . (int)$rBulk['pdfs']
                );
                header(
                    'Location: panel.php?ok=bulk_informes&cantidad=' . (int)$rBulk['informes']
                    . '&pdfs=' . (int)$rBulk['pdfs']
                );
                exit;
            }

            if ($accionBulk === 'eliminar_todo_pruebas') {
                $confirmacion = strtoupper(trim((string)($_POST['confirmacion'] ?? '')));
                if ($confirmacion !== 'ELIMINAR TODO') {
                    header('Location: panel.php?error=bulk_confirmacion');
                    exit;
                }
                $rBulk = panelEliminarTodoPruebas($pdo);
                registrarEventoPanel(
                    $pdo,
                    'datos_prueba_eliminados',
                    'Datos de prueba eliminados',
                    'Informes: ' . (int)$rBulk['informes']
                    . ' | Preliminares: ' . (int)$rBulk['preliminares']
                    . ' | PDF: ' . (int)$rBulk['pdfs']
                );
                header(
                    'Location: panel.php?ok=bulk_todo'
                    . '&informes=' . (int)$rBulk['informes']
                    . '&preliminares=' . (int)$rBulk['preliminares']
                    . '&pdfs=' . (int)$rBulk['pdfs']
                );
                exit;
            }

            header('Location: panel.php?error=bulk_accion');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            @file_put_contents(
                __DIR__ . '/panel_bulk_delete.log',
                '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL,
                FILE_APPEND
            );
            header('Location: panel.php?error=bulk_error');
            exit;
        }
    }


    // Eliminar informe técnico completo desde el panel.
    // Borra el registro de la tabla informes, el PDF asociado y la inspección preliminar relacionada si existe.
    if (isset($_POST['eliminar_informe_id'])) {
        $idInf = (int)$_POST['eliminar_informe_id'];
        if ($idInf > 0) {
            try {
                $pdo->beginTransaction();

                $selectCols = "i.id, i.archivo, i.orden, i.cliente, i.trabajos, COALESCE(t.nombre, 'Técnico') AS tecnico_nombre";
                if (panelColumnaExiste($pdo, 'informes', 'preinspeccion_id')) {
                    $selectCols .= ", i.preinspeccion_id";
                }

                $stmtInfo = $pdo->prepare("SELECT $selectCols
                                           FROM informes i
                                           LEFT JOIN tecnicos t ON t.id = i.tecnico_id
                                           WHERE i.id = ?
                                           LIMIT 1");
                $stmtInfo->execute([$idInf]);
                $infDel = $stmtInfo->fetch(PDO::FETCH_ASSOC);

                if (!$infDel) {
                    $pdo->rollBack();
                    header('Location: panel.php?error=informe_no_encontrado');
                    exit;
                }

                $archivoDel = trim((string)($infDel['archivo'] ?? ''));
                $detalleDel = 'ID ' . $idInf . ' | Técnico: ' . ($infDel['tecnico_nombre'] ?? '') . ' | Reporte: ' . ($infDel['orden'] ?? '') . ' | Cliente: ' . ($infDel['cliente'] ?? '');

                // Si el informe nació desde una preliminar, también se elimina esa preliminar.
                try {
                    $stmtPreDel = $pdo->prepare("DELETE FROM inspecciones_preliminares WHERE informe_id = ?");
                    $stmtPreDel->execute([$idInf]);

                    if (!empty($infDel['preinspeccion_id'])) {
                        $stmtPreDelId = $pdo->prepare("DELETE FROM inspecciones_preliminares WHERE id = ? LIMIT 1");
                        $stmtPreDelId->execute([(int)$infDel['preinspeccion_id']]);
                    }
                } catch (Throwable $e) {
                    // Si la tabla no existe o no aplica, no detenemos la eliminación del informe.
                }

                $stmtDel = $pdo->prepare('DELETE FROM informes WHERE id = ? LIMIT 1');
                $stmtDel->execute([$idInf]);

                $pdo->commit();

                // Borrar PDF físico solo si el nombre es seguro y está dentro de /informes.
                $pdfBorrado = false;
                if ($archivoDel !== '' && basename($archivoDel) === $archivoDel) {
                    $rutaPdf = __DIR__ . '/informes/' . $archivoDel;
                    if (is_file($rutaPdf)) {
                        $pdfBorrado = @unlink($rutaPdf);
                    }
                }

                registrarEventoPanel($pdo, 'informe_eliminado', 'Informe técnico eliminado', $detalleDel . ($pdfBorrado ? ' | PDF eliminado' : ''));
                header('Location: panel.php?ok=informe_eliminado&orden=' . urlencode((string)($infDel['orden'] ?? '')) . '&cliente=' . urlencode((string)($infDel['cliente'] ?? '')));
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                header('Location: panel.php?error=informe_eliminar_error');
                exit;
            }
        }
    }

    // Agregar o editar cliente para autocompletado del técnico
    if (isset($_POST['nuevo_cliente_catalogo'])) {
        $clienteIdEdit = (int)($_POST['cliente_id_edit'] ?? 0);
        $n = normalizarCatalogoTexto($_POST['nuevo_cliente_catalogo'] ?? '');
        if ($n !== '') {
            $duplicadoCliente = false;
            $objetivoCliente = normalizarNombreTecnico($n);
            $stmtDupCliente = $pdo->query('SELECT id, nombre FROM clientes_catalogo WHERE activo = 1');
            foreach ($stmtDupCliente->fetchAll(PDO::FETCH_ASSOC) as $cDup) {
                if ((int)($cDup['id'] ?? 0) !== $clienteIdEdit && normalizarNombreTecnico($cDup['nombre'] ?? '') === $objetivoCliente) {
                    $duplicadoCliente = $cDup;
                    break;
                }
            }
            if ($duplicadoCliente) {
                header('Location: panel.php?error=cliente_duplicado&nombre=' . urlencode($duplicadoCliente['nombre']));
                exit;
            }

            if ($clienteIdEdit > 0) {
                $stmt = $pdo->prepare('UPDATE clientes_catalogo SET nombre = ?, activo = 1 WHERE id = ?');
                $stmt->execute([$n, $clienteIdEdit]);

                // Mantener actualizadas las cotizaciones vinculadas a este cliente.
                $stmtCotCli = $pdo->prepare('UPDATE cotizaciones_catalogo SET cliente_nombre = ? WHERE cliente_id = ?');
                $stmtCotCli->execute([$n, $clienteIdEdit]);

                registrarEventoPanel($pdo, 'cliente_editado', 'Cliente editado en el catálogo', $n);
                $redirectQS = '?ok=cliente_editado&nombre=' . urlencode($n);
            } else {
                $stmt = $pdo->prepare('INSERT INTO clientes_catalogo (nombre, activo) VALUES (?, 1)');
                $stmt->execute([$n]);
                registrarEventoPanel($pdo, 'cliente_agregado', 'Cliente agregado al catálogo', $n);
                $redirectQS = '?ok=cliente_agregado&nombre=' . urlencode($n);
            }
        }
    }

    // Eliminar/ocultar cliente del catálogo. También retira sus cotizaciones del autocompletado.
    if (isset($_POST['eliminar_cliente_id'])) {
        $idCliente = (int)$_POST['eliminar_cliente_id'];
        if ($idCliente > 0) {
            $stmtInfo = $pdo->prepare('SELECT nombre FROM clientes_catalogo WHERE id = ? LIMIT 1');
            $stmtInfo->execute([$idCliente]);
            $clienteTxt = trim((string)$stmtInfo->fetchColumn());

            $stmt = $pdo->prepare('UPDATE clientes_catalogo SET activo = 0 WHERE id = ?');
            $stmt->execute([$idCliente]);

            $stmtCot = $pdo->prepare('UPDATE cotizaciones_catalogo SET activo = 0 WHERE cliente_id = ?');
            $stmtCot->execute([$idCliente]);

            registrarEventoPanel($pdo, 'cliente_eliminado', 'Cliente eliminado del catálogo', $clienteTxt !== '' ? $clienteTxt : ('ID ' . $idCliente));
            $redirectQS = '?ok=cliente_eliminado&nombre=' . urlencode($clienteTxt);
        }
    }
    // Agregar o editar N.° de reporte vinculado directamente a un ticket Odoo.
    if (isset($_POST['nueva_cotizacion_catalogo'])) {
        $cotizacionIdEdit = (int)($_POST['cotizacion_id_edit'] ?? 0);
        $numeroReporte = preg_replace('/\D+/', '', (string)($_POST['nueva_cotizacion_catalogo'] ?? ''));
        $servicioOdooId = (int)($_POST['odoo_servicio_id_reporte'] ?? 0);

        if ($numeroReporte === '' || strlen($numeroReporte) < 6 || strlen($numeroReporte) > 15 || $servicioOdooId <= 0) {
            header('Location: panel.php?error=cotizacion_invalida#reporteTicketForm');
            exit;
        }

        try {
            $stServicio = $pdo->prepare("
                SELECT id, ticket_ref, cliente_id,
                       COALESCE(cliente_nombre,'') AS cliente_nombre,
                       COALESCE(cotizacion,'') AS cotizacion,
                       COALESCE(numero_reporte,'') AS numero_reporte
                FROM odoo_servicios_catalogo
                WHERE id = ? AND activo = 1
                LIMIT 1
            ");
            $stServicio->execute([$servicioOdooId]);
            $servicio = $stServicio->fetch(PDO::FETCH_ASSOC);
            if (!$servicio) {
                header('Location: panel.php?error=cotizacion_invalida#reporteTicketForm');
                exit;
            }

            $ticketRef = trim((string)$servicio['ticket_ref']);
            $clienteId = (int)($servicio['cliente_id'] ?? 0);
            $clienteNombre = trim((string)$servicio['cliente_nombre']);
            $cotizacionOdoo = trim((string)$servicio['cotizacion']);

            if ($ticketRef === '' || $clienteNombre === '') {
                header('Location: panel.php?error=cotizacion_invalida#reporteTicketForm');
                exit;
            }

            $stDup = $pdo->prepare("
                SELECT id, COALESCE(ticket_ref,'') AS ticket_ref
                FROM cotizaciones_catalogo
                WHERE cotizacion = ? AND activo = 1 AND id <> ?
                LIMIT 1
            ");
            $stDup->execute([$numeroReporte, $cotizacionIdEdit]);
            $dup = $stDup->fetch(PDO::FETCH_ASSOC);
            if ($dup && trim((string)$dup['ticket_ref']) !== '' && trim((string)$dup['ticket_ref']) !== $ticketRef) {
                header('Location: panel.php?error=cotizacion_duplicada&cotizacion=' . urlencode($numeroReporte) . '#reporteTicketForm');
                exit;
            }

            $pdo->beginTransaction();

            if ($cotizacionIdEdit > 0) {
                $stAnterior = $pdo->prepare("SELECT COALESCE(ticket_ref,'') AS ticket_ref, cotizacion FROM cotizaciones_catalogo WHERE id=? LIMIT 1");
                $stAnterior->execute([$cotizacionIdEdit]);
                $anterior = $stAnterior->fetch(PDO::FETCH_ASSOC) ?: [];
                $ticketAnterior = trim((string)($anterior['ticket_ref'] ?? ''));
                $reporteAnterior = preg_replace('/\D+/', '', (string)($anterior['cotizacion'] ?? ''));
                if ($ticketAnterior !== '' && $ticketAnterior !== $ticketRef) {
                    $pdo->prepare("UPDATE odoo_servicios_catalogo SET numero_reporte=NULL, actualizado_en=NOW() WHERE ticket_ref=? AND numero_reporte=?")
                        ->execute([$ticketAnterior, $reporteAnterior]);
                }
            }

            $stTicketRow = $pdo->prepare("SELECT id FROM cotizaciones_catalogo WHERE ticket_ref=? AND activo=1 ORDER BY id DESC LIMIT 1");
            $stTicketRow->execute([$ticketRef]);
            $filaTicketId = (int)$stTicketRow->fetchColumn();

            $catalogoId = $cotizacionIdEdit > 0 ? $cotizacionIdEdit : $filaTicketId;
            if ($catalogoId <= 0 && $dup && trim((string)$dup['ticket_ref']) === $ticketRef) {
                $catalogoId = (int)$dup['id'];
            }

            if ($catalogoId > 0) {
                $up = $pdo->prepare("
                    UPDATE cotizaciones_catalogo
                    SET cotizacion=?, cliente_id=?, cliente_nombre=?, descripcion=?,
                        ticket_ref=?, cotizacion_odoo=?, origen='odoo', activo=1
                    WHERE id=?
                ");
                $up->execute([
                    $numeroReporte,
                    $clienteId > 0 ? $clienteId : null,
                    $clienteNombre,
                    'Asignado al Ticket #' . $ticketRef,
                    $ticketRef,
                    $cotizacionOdoo !== '' ? $cotizacionOdoo : null,
                    $catalogoId
                ]);
            } else {
                $ins = $pdo->prepare("
                    INSERT INTO cotizaciones_catalogo
                    (cotizacion, cliente_id, cliente_nombre, descripcion, ticket_ref, cotizacion_odoo, origen, activo)
                    VALUES (?, ?, ?, ?, ?, ?, 'odoo', 1)
                ");
                $ins->execute([
                    $numeroReporte,
                    $clienteId > 0 ? $clienteId : null,
                    $clienteNombre,
                    'Asignado al Ticket #' . $ticketRef,
                    $ticketRef,
                    $cotizacionOdoo !== '' ? $cotizacionOdoo : null
                ]);
                $catalogoId = (int)$pdo->lastInsertId();
            }

            $pdo->prepare("
                UPDATE odoo_servicios_catalogo
                SET numero_reporte=?, actualizado_en=NOW()
                WHERE id=?
            ")->execute([$numeroReporte, $servicioOdooId]);

            if ($pdo->inTransaction()) $pdo->commit();

            registrarEventoPanel(
                $pdo,
                $cotizacionIdEdit > 0 ? 'cotizacion_editada' : 'cotizacion_agregada',
                $cotizacionIdEdit > 0 ? 'Reporte actualizado y vinculado a ticket' : 'Reporte agregado y vinculado a ticket',
                $numeroReporte . ' · Ticket #' . $ticketRef . ' · ' . $clienteNombre
            );

            header('Location: panel.php?ok=' . ($cotizacionIdEdit > 0 ? 'cotizacion_editada' : 'cotizacion_agregada') . '&cotizacion=' . urlencode($numeroReporte) . '#reporteTicketForm');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            @file_put_contents(__DIR__ . '/odoo_reporte_panel.log', '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            header('Location: panel.php?error=cotizacion_invalida#reporteTicketForm');
            exit;
        }
    }

    // Eliminar/ocultar cotización del catálogo sin tocar informes ya creados.
    if (isset($_POST['eliminar_cotizacion_id'])) {
        $idCot = (int)$_POST['eliminar_cotizacion_id'];
        if ($idCot > 0) {
            $stmtInfo = $pdo->prepare('SELECT cotizacion, cliente_nombre FROM cotizaciones_catalogo WHERE id = ? LIMIT 1');
            $stmtInfo->execute([$idCot]);
            $infoCot = $stmtInfo->fetch(PDO::FETCH_ASSOC) ?: [];
            $cotTxt = trim((string)($infoCot['cotizacion'] ?? ''));
            $cliTxt = trim((string)($infoCot['cliente_nombre'] ?? ''));

            $stmt = $pdo->prepare('UPDATE cotizaciones_catalogo SET activo = 0 WHERE id = ?');
            $stmt->execute([$idCot]);

            registrarEventoPanel($pdo, 'cotizacion_eliminada', 'Reporte eliminado del catálogo', $cotTxt . ($cliTxt !== '' ? ' · ' . $cliTxt : ''));
            $redirectQS = '?ok=cotizacion_eliminada&cotizacion=' . urlencode($cotTxt);
        }
    }


    // Agregar o editar CONTENEDOR para autocompletado del técnico.
    // Solo se guarda el número de contenedor/equipo. Los datos de máquina van en su propio catálogo.
    if (isset($_POST['nuevo_contenedor_catalogo'])) {
        $contenedorIdEdit = (int)($_POST['contenedor_id_edit'] ?? 0);
        $numero = strtoupper(normalizarCatalogoTexto($_POST['nuevo_contenedor_catalogo'] ?? ''));
        $numero = preg_replace('/[^A-Z0-9\-_.\/]/', '', $numero);
        $descCont = ''; // Referencia retirada del panel

        if ($numero !== '' && strlen($numero) >= 3 && strlen($numero) <= 60) {
            if ($contenedorIdEdit > 0) {
                $stmtDup = $pdo->prepare('SELECT id FROM contenedores_catalogo WHERE numero = ? AND id <> ? AND activo = 1 LIMIT 1');
                $stmtDup->execute([$numero, $contenedorIdEdit]);
                if ((int)$stmtDup->fetchColumn() > 0) {
                    header('Location: panel.php?error=contenedor_duplicado&contenedor=' . urlencode($numero));
                    exit;
                }
                $stmt = $pdo->prepare('UPDATE contenedores_catalogo SET numero = ?, descripcion = ?, activo = 1 WHERE id = ?');
                $stmt->execute([$numero, $descCont, $contenedorIdEdit]);
                registrarEventoPanel($pdo, 'contenedor_editado', 'Contenedor editado en el catálogo', $numero);
                $redirectQS = '?ok=contenedor_editado&contenedor=' . urlencode($numero);
            } else {
                $stmt = $pdo->prepare('INSERT INTO contenedores_catalogo (numero, descripcion, activo) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), activo = 1');
                $stmt->execute([$numero, $descCont]);
                registrarEventoPanel($pdo, 'contenedor_agregado', 'Contenedor agregado al catálogo', $numero);
                $redirectQS = '?ok=contenedor_agregado&contenedor=' . urlencode($numero);
            }
        } else {
            $redirectQS = '?error=contenedor_invalido';
        }
    }

    // Generadores registrados: número, serial, marca y controlador.
    if (isset($_POST['guardar_generador_unidad'])) {
        $id = (int)($_POST['generador_unidad_id_edit'] ?? 0);
        $numero = strtoupper(normalizarCatalogoTexto($_POST['generador_unidad_numero'] ?? ''));
        $numero = preg_replace('/[^A-Z0-9\-_.\/]/', '', $numero);
        $serial = strtoupper(normalizarCatalogoTexto($_POST['generador_unidad_serial'] ?? ''));
        $serial = preg_replace('/[^A-Z0-9\-_.\/]/', '', $serial);
        $marca = strtoupper(normalizarCatalogoTexto($_POST['generador_unidad_marca'] ?? 'THERMO KING'));
        $controlador = strtoupper(normalizarCatalogoTexto($_POST['generador_unidad_controlador'] ?? ''));
        $controlador = str_replace('ZG-', 'SG-', $controlador);

        if ($numero !== '' && $serial !== '' && $marca === 'THERMO KING' && in_array($controlador, ['SG-3000','SG-5000'], true)) {
            $dupNumero = $pdo->prepare('SELECT id FROM generadores_catalogo WHERE numero = ? AND id <> ? AND activo = 1 LIMIT 1');
            $dupNumero->execute([$numero, $id]);
            $dupSerial = $pdo->prepare('SELECT id FROM generadores_catalogo WHERE serial_unidad = ? AND id <> ? AND activo = 1 LIMIT 1');
            $dupSerial->execute([$serial, $id]);
            if ((int)$dupNumero->fetchColumn() > 0) {
                $redirectQS='?error=generador_numero_duplicado';
            } elseif ((int)$dupSerial->fetchColumn() > 0) {
                $redirectQS='?error=generador_serial_duplicado';
            } elseif ($id > 0) {
                $st=$pdo->prepare('UPDATE generadores_catalogo SET numero=?, serial_unidad=?, marca_equipo=?, controlador=?, activo=1 WHERE id=?');
                $st->execute([$numero,$serial,$marca,$controlador,$id]);
                registrarEventoPanel($pdo,'generador_editado','Generador actualizado',$numero.' · '.$serial.' · '.$controlador);
                $redirectQS='?ok=generador_unidad_guardado';
            } else {
                $st=$pdo->prepare('INSERT INTO generadores_catalogo (numero,serial_unidad,marca_equipo,controlador,activo) VALUES (?,?,?,?,1)');
                $st->execute([$numero,$serial,$marca,$controlador]);
                registrarEventoPanel($pdo,'generador_agregado','Generador agregado al catálogo',$numero.' · '.$serial.' · '.$controlador);
                $redirectQS='?ok=generador_unidad_guardado';
            }
        } else {
            $redirectQS='?error=generador_unidad_invalido';
        }
    }
    if (isset($_POST['eliminar_generador_unidad_id'])) {
        $id=(int)$_POST['eliminar_generador_unidad_id'];
        if($id>0){
            $st=$pdo->prepare('SELECT numero,serial_unidad FROM generadores_catalogo WHERE id=? LIMIT 1');$st->execute([$id]);$g=$st->fetch(PDO::FETCH_ASSOC)?:[];
            $pdo->prepare('UPDATE generadores_catalogo SET activo=0 WHERE id=?')->execute([$id]);
            registrarEventoPanel($pdo,'generador_eliminado','Generador retirado del catálogo',trim(($g['numero']??'').' · '.($g['serial_unidad']??'')));
            $redirectQS='?ok=generador_unidad_eliminado';
        }
    }

    // Series registradas de MÁQUINA REEFER: catálogo separado de generadores.
    if (isset($_POST['guardar_maquina_reefer_serial'])) {
        $id = (int)($_POST['maquina_reefer_serial_id_edit'] ?? 0);
        $serial = strtoupper(normalizarCatalogoTexto($_POST['maquina_reefer_serial'] ?? ''));
        $serial = preg_replace('/[^A-Z0-9\-_.\/]/', '', $serial);
        $marca = strtoupper(normalizarCatalogoTexto($_POST['maquina_reefer_serial_marca'] ?? ''));
        $modelo = strtoupper(normalizarCatalogoTexto($_POST['maquina_reefer_serial_modelo'] ?? ''));
        $controlador = strtoupper(normalizarCatalogoTexto($_POST['maquina_reefer_serial_controlador'] ?? ''));
        $anioRaw = preg_replace('/\D+/', '', (string)($_POST['maquina_reefer_serial_anio'] ?? ''));
        $anio = $anioRaw !== '' ? (int)$anioRaw : null;
        $refrigerante = strtoupper(normalizarCatalogoTexto($_POST['maquina_reefer_serial_refrigerante'] ?? ''));

        // Automatización por marca para que el técnico no tenga que repetir datos.
        if ($marca === 'THERMO KING') {
            $modelo = 'MAGNUM PLUS';
            $refrigerante = 'R404A';
            if (!in_array($controlador, ['MP3000','MP4000','MP5000'], true)) $controlador = '';
        } elseif ($marca === 'CARRIER') {
            if (!in_array($modelo, ['MICROLINK 2','MICROLINK 3'], true)) $modelo = '';
            if ($controlador === '') $controlador = $modelo;
            $refrigerante = 'R134A';
        } elseif ($marca === 'STAR COOL') {
            if (!in_array($modelo, ['CIM 5','CIM 6'], true)) $modelo = '';
            if ($controlador === '') $controlador = str_replace(' ', '', $modelo);
            $refrigerante = 'R134A';
        } elseif ($marca === 'DAIKIN') {
            $modelo = 'DAIKIN';
            if ($controlador === '') $controlador = 'DAIKIN';
            $refrigerante = 'R134A';
        }

        $esGenerador = $marca === 'GENSET'
            || in_array(str_replace('ZG-', 'SG-', $controlador), ['SG-3000','SG-5000'], true);
        $esMp400 = $marca === 'THERMO KING'
            && preg_replace('/\s+/', '', $controlador) === 'MP400';
        $anioValido = $anio === null || ($anio >= 1980 && $anio <= ((int)date('Y') + 1));

        if ($serial !== '' && strlen($serial) >= 3 && strlen($serial) <= 100
            && $marca !== '' && $modelo !== '' && $controlador !== ''
            && $refrigerante !== '' && $anioValido && !$esGenerador && !$esMp400) {

            $dupGenerador = $pdo->prepare('SELECT id FROM generadores_catalogo WHERE serial_unidad = ? AND activo = 1 LIMIT 1');
            $dupGenerador->execute([$serial]);
            $dup = $pdo->prepare('SELECT id FROM maquinas_catalogo WHERE serial_unidad = ? AND id <> ? AND activo = 1 LIMIT 1');
            $dup->execute([$serial, $id]);

            if ((int)$dupGenerador->fetchColumn() > 0) {
                $redirectQS = '?error=maquina_serial_generador&serial=' . urlencode($serial);
            } elseif ((int)$dup->fetchColumn() > 0) {
                $redirectQS = '?error=maquina_duplicada&serial=' . urlencode($serial);
            } elseif ($id > 0) {
                $st = $pdo->prepare('UPDATE maquinas_catalogo
                                     SET serial_unidad = ?, marca_equipo = ?, modelo_equipo = ?,
                                         controlador = ?, anio_fabricacion = ?, refrigerante = ?,
                                         descripcion = NULL, activo = 1
                                     WHERE id = ?');
                $st->execute([$serial, $marca, $modelo, $controlador, $anio, $refrigerante, $id]);
                registrarEventoPanel($pdo, 'maquina_editada', 'Serial de máquina reefer actualizado',
                    $serial . ' · ' . $marca . ' · ' . $modelo . ' · ' . $controlador);
                $redirectQS = '?ok=maquina_serial_guardado&serial=' . urlencode($serial);
            } else {
                $st = $pdo->prepare('INSERT INTO maquinas_catalogo
                                     (serial_unidad, marca_equipo, modelo_equipo, controlador, anio_fabricacion, refrigerante, descripcion, activo)
                                     VALUES (?, ?, ?, ?, ?, ?, NULL, 1)
                                     ON DUPLICATE KEY UPDATE
                                         marca_equipo = VALUES(marca_equipo),
                                         modelo_equipo = VALUES(modelo_equipo),
                                         controlador = VALUES(controlador),
                                         anio_fabricacion = VALUES(anio_fabricacion),
                                         refrigerante = VALUES(refrigerante),
                                         descripcion = NULL,
                                         activo = 1');
                $st->execute([$serial, $marca, $modelo, $controlador, $anio, $refrigerante]);
                registrarEventoPanel($pdo, 'maquina_agregada', 'Serial de máquina reefer agregado',
                    $serial . ' · ' . $marca . ' · ' . $modelo . ' · ' . $controlador);
                $redirectQS = '?ok=maquina_serial_guardado&serial=' . urlencode($serial);
            }
        } else {
            $redirectQS = '?error=maquina_serial_invalido';
        }
    }


    // Modelos de MÁQUINA REEFER: solo marca y controlador.
    if (isset($_POST['guardar_modelo_reefer'])) {
        $id = (int)($_POST['modelo_reefer_id_edit'] ?? 0);
        $marca = strtoupper(normalizarCatalogoTexto($_POST['modelo_reefer_marca'] ?? ''));
        $controlador = strtoupper(normalizarCatalogoTexto($_POST['modelo_reefer_controlador'] ?? ''));
        if ($marca !== '' && $controlador !== '' && $marca !== 'GENSET' && !preg_match('/^S?G-?(3000|5000)$/i', $controlador) && !($marca === 'THERMO KING' && preg_replace('/\s+/', '', $controlador) === 'MP400')) {
            if ($id > 0) {
                $st=$pdo->prepare('UPDATE modelos_reefer_catalogo SET marca_equipo=?, controlador=?, activo=1 WHERE id=?');
                $st->execute([$marca,$controlador,$id]);
            } else {
                $st=$pdo->prepare('INSERT INTO modelos_reefer_catalogo (marca_equipo,controlador,activo) VALUES (?,?,1) ON DUPLICATE KEY UPDATE activo=1');
                $st->execute([$marca,$controlador]);
            }
            $redirectQS='?ok=modelo_reefer_guardado';
        } else $redirectQS='?error=modelo_reefer_invalido';
    }
    if (isset($_POST['eliminar_modelo_reefer_id'])) {
        $id=(int)$_POST['eliminar_modelo_reefer_id'];
        if($id>0){$pdo->prepare('UPDATE modelos_reefer_catalogo SET activo=0 WHERE id=?')->execute([$id]);$redirectQS='?ok=modelo_reefer_eliminado';}
    }

    // Modelos de GENERADOR: solo marca y controlador.
    if (isset($_POST['guardar_modelo_genset'])) {
        $id = (int)($_POST['modelo_genset_id_edit'] ?? 0);
        $marca = strtoupper(normalizarCatalogoTexto($_POST['modelo_genset_marca'] ?? ''));
        $controlador = strtoupper(normalizarCatalogoTexto($_POST['modelo_genset_controlador'] ?? ''));
        $controlador = str_replace('ZG-', 'SG-', $controlador);
        if ($marca === 'THERMO KING' && in_array($controlador, ['SG-3000','SG-5000'], true)) {
            if ($id > 0) {
                $st=$pdo->prepare('UPDATE modelos_genset_catalogo SET marca_equipo=?, controlador=?, activo=1 WHERE id=?');
                $st->execute([$marca,$controlador,$id]);
            } else {
                $st=$pdo->prepare('INSERT INTO modelos_genset_catalogo (marca_equipo,controlador,activo) VALUES (?,?,1) ON DUPLICATE KEY UPDATE activo=1');
                $st->execute([$marca,$controlador]);
            }
            $redirectQS='?ok=modelo_genset_guardado';
        } else $redirectQS='?error=modelo_genset_invalido';
    }
    if (isset($_POST['eliminar_modelo_genset_id'])) {
        $id=(int)$_POST['eliminar_modelo_genset_id'];
        if($id>0){$pdo->prepare('UPDATE modelos_genset_catalogo SET activo=0 WHERE id=?')->execute([$id]);$redirectQS='?ok=modelo_genset_eliminado';}
    }

    // Materiales exclusivos de MÁQUINA REEFER por marca/controlador.
    if (isset($_POST['nuevo_repuesto_reefer_detalle'])) {
        $id=(int)($_POST['repuesto_reefer_id_edit']??0);
        $marca=strtoupper(normalizarCatalogoTexto($_POST['nuevo_repuesto_reefer_marca']??''));
        $ctrl=strtoupper(normalizarCatalogoTexto($_POST['nuevo_repuesto_reefer_controlador']??''));
        $codigo=strtoupper(normalizarCatalogoTexto($_POST['nuevo_repuesto_reefer_codigo']??''));
        $codigo=preg_replace('/[^A-Z0-9\-_.\/]/','',$codigo);
        $detalle=normalizarCatalogoTexto($_POST['nuevo_repuesto_reefer_detalle']??'');
        $unidad=normalizarCatalogoTexto($_POST['nuevo_repuesto_reefer_unidad']??'und');
        if($marca!==''&&$ctrl!==''&&$detalle!==''){
            if($id>0){$st=$pdo->prepare('UPDATE repuestos_reefer_catalogo SET marca_equipo=?,controlador=?,codigo=?,detalle=?,unidad=?,activo=1 WHERE id=?');$st->execute([$marca,$ctrl,$codigo?:null,$detalle,$unidad?:'und',$id]);}
            else{$st=$pdo->prepare('INSERT INTO repuestos_reefer_catalogo (marca_equipo,controlador,codigo,detalle,unidad,activo) VALUES (?,?,?,?,?,1)');$st->execute([$marca,$ctrl,$codigo?:null,$detalle,$unidad?:'und']);}
            $redirectQS='?ok=repuesto_reefer_guardado';
        } else $redirectQS='?error=repuesto_reefer_invalido';
    }
    if(isset($_POST['eliminar_repuesto_reefer_id'])){$id=(int)$_POST['eliminar_repuesto_reefer_id'];if($id>0){$pdo->prepare('UPDATE repuestos_reefer_catalogo SET activo=0 WHERE id=?')->execute([$id]);$redirectQS='?ok=repuesto_reefer_eliminado';}}

    // Materiales exclusivos para GENERADOR por controlador.
    if (isset($_POST['nuevo_genset_repuesto_detalle'])) {
        $id=(int)($_POST['genset_repuesto_id_edit']??0);
        $ctrl=strtoupper(normalizarCatalogoTexto($_POST['nuevo_genset_repuesto_controlador']??''));
        $ctrl=str_replace('ZG-','SG-',$ctrl);
        $codigo=strtoupper(normalizarCatalogoTexto($_POST['nuevo_genset_repuesto_codigo']??''));
        $codigo=preg_replace('/[^A-Z0-9\-_.\/]/','',$codigo);
        $detalle=normalizarCatalogoTexto($_POST['nuevo_genset_repuesto_detalle']??'');
        $unidad=normalizarCatalogoTexto($_POST['nuevo_genset_repuesto_unidad']??'und');
        if($ctrl!==''&&$detalle!==''){
            if($id>0){$st=$pdo->prepare('UPDATE repuestos_genset_catalogo SET controlador=?,codigo=?,detalle=?,unidad=?,activo=1 WHERE id=?');$st->execute([$ctrl,$codigo?:null,$detalle,$unidad?:'und',$id]);}
            else{$st=$pdo->prepare('INSERT INTO repuestos_genset_catalogo (controlador,codigo,detalle,unidad,activo) VALUES (?,?,?,?,1)');$st->execute([$ctrl,$codigo?:null,$detalle,$unidad?:'und']);}
            $redirectQS='?ok=genset_repuesto_guardado';
        } else $redirectQS='?error=genset_repuesto_invalido';
    }
    if(isset($_POST['eliminar_genset_repuesto_id'])){$id=(int)$_POST['eliminar_genset_repuesto_id'];if($id>0){$pdo->prepare('UPDATE repuestos_genset_catalogo SET activo=0 WHERE id=?')->execute([$id]);$redirectQS='?ok=genset_repuesto_eliminado';}}

    // Eliminar/ocultar contenedor del catálogo sin tocar preliminares ni informes ya creados.
    if (isset($_POST['eliminar_contenedor_id'])) {
        $idCont = (int)$_POST['eliminar_contenedor_id'];
        if ($idCont > 0) {
            $stmtInfo = $pdo->prepare('SELECT numero FROM contenedores_catalogo WHERE id = ? LIMIT 1');
            $stmtInfo->execute([$idCont]);
            $contTxt = trim((string)$stmtInfo->fetchColumn());
            $stmt = $pdo->prepare('UPDATE contenedores_catalogo SET activo = 0 WHERE id = ?');
            $stmt->execute([$idCont]);
            registrarEventoPanel($pdo, 'contenedor_eliminado', 'Contenedor eliminado del catálogo', $contTxt);
            $redirectQS = '?ok=contenedor_eliminado&contenedor=' . urlencode($contTxt);
        }
    }

    // Agregar técnico sin permitir nombres/apellidos duplicados
    if (isset($_POST['nuevo_tecnico'])) {
        $n = trim($_POST['nuevo_tecnico']);
        $n = preg_replace('/\s+/u', ' ', $n);

        if ($n !== '') {
            $duplicado = buscarTecnicoDuplicado($pdo, $n);

            if ($duplicado) {
                header('Location: panel.php?error=tecnico_duplicado&nombre=' . urlencode($duplicado['nombre']));
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO tecnicos (nombre) VALUES (?)');
            $stmt->execute([$n]);
            registrarEventoPanel($pdo, 'tecnico_agregado', 'Nuevo técnico agregado', $n);
            $redirectQS = '?ok=tecnico_agregado&nombre=' . urlencode($n);
        }
    }

    // Eliminar/ocultar técnico de la lista sin borrar sus informes históricos
    if (isset($_POST['eliminar_tecnico_id'])) {
        $id = (int)$_POST['eliminar_tecnico_id'];
        if ($id > 0) {
            $stmtName = $pdo->prepare('SELECT nombre FROM tecnicos WHERE id = ? LIMIT 1');
            $stmtName->execute([$id]);
            $nombreTec = trim((string)$stmtName->fetchColumn());

            $stmt = $pdo->prepare('UPDATE tecnicos SET activo = 0 WHERE id = ?');
            $stmt->execute([$id]);

            registrarEventoPanel($pdo, 'tecnico_eliminado', 'Técnico eliminado de la lista', $nombreTec !== '' ? $nombreTec : ('ID ' . $id));
            $redirectQS = '?ok=tecnico_eliminado&nombre=' . urlencode($nombreTec);
        }
    }

    // Agregar trabajo realizado
    if (isset($_POST['nuevo_trabajo'])) {
        $n = trim($_POST['nuevo_trabajo']);
        if ($n !== '') {
            $slug = slugTrabajoUnico($pdo, $n);
            $nombreTrabajo = upperUtf8($n);
            $stmt = $pdo->prepare('INSERT INTO trabajos_realizados (slug, nombre, activo) VALUES (?, ?, 1)');
            $stmt->execute([$slug, $nombreTrabajo]);
            registrarEventoPanel($pdo, 'trabajo_agregado', 'Nuevo trabajo agregado', $nombreTrabajo);
        }
    }

    // Editar nombre de trabajo realizado
    if (isset($_POST['editar_trabajo_id'], $_POST['trabajo_nombre'])) {
        $id = (int)$_POST['editar_trabajo_id'];
        $n = trim($_POST['trabajo_nombre']);
        if ($id > 0 && $n !== '') {
            $oldStmt = $pdo->prepare('SELECT nombre FROM trabajos_realizados WHERE id = ?');
            $oldStmt->execute([$id]);
            $anterior = (string)$oldStmt->fetchColumn();
            $nuevoNombre = upperUtf8($n);
            $stmt = $pdo->prepare('UPDATE trabajos_realizados SET nombre = ? WHERE id = ?');
            $stmt->execute([$nuevoNombre, $id]);
            registrarEventoPanel($pdo, 'trabajo_editado', 'Trabajo editado', trim($anterior . ' → ' . $nuevoNombre));
        }
    }


    // Eliminar/ocultar máquina del catálogo sin tocar preliminares ni informes ya creados.
    if (isset($_POST['eliminar_maquina_id'])) {
        $idMaq = (int)$_POST['eliminar_maquina_id'];
        if ($idMaq > 0) {
            $stmtInfo = $pdo->prepare('SELECT serial_unidad FROM maquinas_catalogo WHERE id = ? LIMIT 1');
            $stmtInfo->execute([$idMaq]);
            $serialTxt = trim((string)$stmtInfo->fetchColumn());
            $stmt = $pdo->prepare('UPDATE maquinas_catalogo SET activo = 0 WHERE id = ?');
            $stmt->execute([$idMaq]);
            registrarEventoPanel($pdo, 'maquina_eliminada', 'Máquina eliminada del catálogo', $serialTxt);
            $redirectQS = '?ok=maquina_eliminada&serial=' . urlencode($serialTxt);
        }
    }

    // Ocultar/desactivar trabajo realizado
    if (isset($_POST['desactivar_trabajo_id'])) {
        $id = (int)$_POST['desactivar_trabajo_id'];
        if ($id > 0) {
            $oldStmt = $pdo->prepare('SELECT nombre FROM trabajos_realizados WHERE id = ?');
            $oldStmt->execute([$id]);
            $nombreTrabajo = (string)$oldStmt->fetchColumn();
            $stmt = $pdo->prepare('UPDATE trabajos_realizados SET activo = 0 WHERE id = ?');
            $stmt->execute([$id]);
            registrarEventoPanel($pdo, 'trabajo_quitado', 'Trabajo quitado de la lista', $nombreTrabajo);
        }
    }

    header('Location: panel.php' . ($redirectQS ?? ''));
    exit;
}

$tecnicos = $pdo->query('SELECT * FROM tecnicos WHERE activo = 1 ORDER BY nombre')->fetchAll();
$tecnicosTodos = $pdo->query('SELECT * FROM tecnicos ORDER BY CASE WHEN activo = 1 THEN 0 ELSE 1 END, nombre')->fetchAll();
$trabajosGestion = $pdo->query('SELECT id, slug, nombre FROM trabajos_realizados WHERE activo = 1 ORDER BY nombre')->fetchAll();
$clientesCatalogoPanel = $pdo->query("SELECT id,nombre,COALESCE(ruc,'') ruc,COALESCE(contacto,'') contacto,COALESCE(telefono,'') telefono,COALESCE(correo,'') correo,COALESCE(direccion,'') direccion,COALESCE(origen,'') origen FROM clientes_catalogo WHERE activo=1 ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$cotizacionesCatalogoPanel = $pdo->query("
    SELECT c.id,c.cotizacion,c.cliente_id,COALESCE(NULLIF(c.cliente_nombre,''),cl.nombre,'') AS cliente_nombre,COALESCE(c.descripcion,'') AS descripcion,COALESCE(c.ticket_ref,'') AS ticket_ref,COALESCE(c.cotizacion_odoo,'') AS cotizacion_odoo,COALESCE(c.origen,'') AS origen,c.creado_en
    FROM cotizaciones_catalogo c
    LEFT JOIN clientes_catalogo cl ON cl.id = c.cliente_id
    WHERE c.activo = 1
    ORDER BY c.creado_en DESC, c.cotizacion DESC
    LIMIT 80
"
)->fetchAll(PDO::FETCH_ASSOC);

// Agrupar los números de reporte por cliente para mostrarlos dentro de cada cliente.
$reportesPorClientePanel = [];
foreach ($cotizacionesCatalogoPanel as $reporteCatalogo) {
    $clienteIdGrupo = (int)($reporteCatalogo['cliente_id'] ?? 0);
    $clienteNombreGrupo = trim((string)($reporteCatalogo['cliente_nombre'] ?? ''));
    if ($clienteNombreGrupo === '') $clienteNombreGrupo = 'Sin cliente vinculado';

    // Se prioriza el ID para evitar mezclar clientes con nombres parecidos.
    $claveGrupo = $clienteIdGrupo > 0
        ? 'cliente_' . $clienteIdGrupo
        : 'nombre_' . normalizarNombreTecnico($clienteNombreGrupo);

    if (!isset($reportesPorClientePanel[$claveGrupo])) {
        $reportesPorClientePanel[$claveGrupo] = [
            'cliente_id' => $clienteIdGrupo,
            'cliente_nombre' => $clienteNombreGrupo,
            'reportes' => [],
        ];
    }
    $reportesPorClientePanel[$claveGrupo]['reportes'][] = $reporteCatalogo;
}

uasort($reportesPorClientePanel, function ($a, $b) {
    return strcasecmp((string)$a['cliente_nombre'], (string)$b['cliente_nombre']);
});

$contenedoresCatalogoPanel = $pdo->query("
    SELECT id,numero,COALESCE(serial_unidad,'') serial_unidad,COALESCE(marca_equipo,'') marca_equipo,COALESCE(modelo_equipo,'') modelo_equipo,COALESCE(controlador,'') controlador,COALESCE(anio_fabricacion,'') anio_fabricacion,COALESCE(refrigerante,'') refrigerante,COALESCE(tamano_contenedor,'') tamano_contenedor,COALESCE(modalidad_comercial,'') modalidad_comercial,COALESCE(tipo_equipo,'') tipo_equipo,COALESCE(ticket_ref,'') ticket_ref,COALESCE(cliente_nombre,'') cliente_nombre,COALESCE(origen,'') origen,COALESCE(descripcion,'') AS descripcion,creado_en
    FROM contenedores_catalogo
    WHERE activo = 1
    ORDER BY creado_en DESC, numero ASC
")->fetchAll(PDO::FETCH_ASSOC);
$serviciosOdooPanel = $pdo->query("SELECT id,ticket_ref,COALESCE(numero_reporte,'') numero_reporte,COALESCE(cotizacion,'') cotizacion,COALESCE(cliente_nombre,'') cliente_nombre,COALESCE(direccion,'') direccion,COALESCE(fecha_servicio,'') fecha_servicio,COALESCE(modalidad_comercial,'') modalidad_comercial,COALESCE(tipo_equipo,'') tipo_equipo,COALESCE(titulo_ticket,'') titulo_ticket,actualizado_en FROM odoo_servicios_catalogo WHERE activo=1 ORDER BY (numero_reporte IS NULL OR numero_reporte='') DESC, actualizado_en DESC LIMIT 60")->fetchAll(PDO::FETCH_ASSOC);
$reporteCatalogoPorTicket = [];
foreach ($cotizacionesCatalogoPanel as $reporteTicket) {
    $ticketMapa = trim((string)($reporteTicket['ticket_ref'] ?? ''));
    if ($ticketMapa !== '') {
        $reporteCatalogoPorTicket[$ticketMapa] = $reporteTicket;
    }
}

$maquinasCatalogoPanel = $pdo->query("
    SELECT id, marca_equipo, controlador, creado_en
    FROM modelos_reefer_catalogo
    WHERE activo = 1
      AND NOT (UPPER(TRIM(marca_equipo)) = 'THERMO KING' AND UPPER(REPLACE(TRIM(controlador), ' ', '')) = 'MP400')
    ORDER BY marca_equipo, controlador
")->fetchAll(PDO::FETCH_ASSOC);
$serialesReeferCatalogoPanel = $pdo->query("
    SELECT id, serial_unidad,
           COALESCE(marca_equipo,'') AS marca_equipo,
           COALESCE(modelo_equipo,'') AS modelo_equipo,
           COALESCE(controlador,'') AS controlador,
           COALESCE(anio_fabricacion,'') AS anio_fabricacion,
           COALESCE(refrigerante,'') AS refrigerante,
           creado_en
    FROM maquinas_catalogo
    WHERE activo = 1
      AND UPPER(COALESCE(marca_equipo,'')) <> 'GENSET'
      AND UPPER(COALESCE(controlador,'')) NOT IN ('SG-3000','SG-5000','ZG-3000','ZG-5000')
      AND NOT (
          UPPER(TRIM(COALESCE(marca_equipo,''))) = 'THERMO KING'
          AND UPPER(REPLACE(TRIM(COALESCE(controlador,'')), ' ', '')) = 'MP400'
      )
      AND serial_unidad NOT IN (
          SELECT COALESCE(serial_unidad,'') FROM generadores_catalogo WHERE activo = 1
      )
    ORDER BY creado_en DESC, serial_unidad ASC
")->fetchAll(PDO::FETCH_ASSOC);
$generadoresUnidadesPanel = $pdo->query("
    SELECT id, numero, COALESCE(serial_unidad,'') AS serial_unidad,
           COALESCE(marca_equipo,'THERMO KING') AS marca_equipo,
           COALESCE(controlador,'') AS controlador, creado_en
    FROM generadores_catalogo
    WHERE activo = 1
    ORDER BY creado_en DESC, numero ASC
")->fetchAll(PDO::FETCH_ASSOC);
$generadoresCatalogoPanel = $pdo->query("
    SELECT id, marca_equipo, controlador, creado_en
    FROM modelos_genset_catalogo
    WHERE activo = 1
    ORDER BY marca_equipo, controlador
")->fetchAll(PDO::FETCH_ASSOC);
$repuestosGensetCatalogoPanel = $pdo->query("
    SELECT id, controlador, COALESCE(codigo,'') AS codigo, detalle, COALESCE(unidad,'und') AS unidad, creado_en
    FROM repuestos_genset_catalogo WHERE activo=1 ORDER BY controlador, detalle
")->fetchAll(PDO::FETCH_ASSOC);
$repuestosCatalogoPanel = $pdo->query("
    SELECT id, marca_equipo, controlador, COALESCE(codigo,'') AS codigo, detalle, COALESCE(unidad,'und') AS unidad, creado_en
    FROM repuestos_reefer_catalogo
    WHERE activo = 1
    ORDER BY marca_equipo, controlador, detalle
")->fetchAll(PDO::FETCH_ASSOC);
$rows = $pdo->query(
    'SELECT i.*, t.nombre AS tecnico_nombre
     FROM informes i
     JOIN tecnicos t ON t.id = i.tecnico_id
     ORDER BY i.creado_en DESC'
)->fetchAll();

try {
    asegurarTablaPreliminaresPanel($pdo);
    $pdo->exec("CREATE TABLE IF NOT EXISTS borradores_servicio (
        preinspeccion_id INT NOT NULL PRIMARY KEY,
        token_continuacion VARCHAR(120) DEFAULT NULL,
        datos_json LONGTEXT NOT NULL,
        actualizado_en DATETIME NOT NULL,
        INDEX idx_borrador_token (token_continuacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $preliminares = $pdo->query(
        "SELECT ip.*, COALESCE(t.nombre, 'Técnico') AS tecnico_nombre,
                bs.actualizado_en AS borrador_actualizado_en
         FROM inspecciones_preliminares ip
         LEFT JOIN tecnicos t ON t.id = ip.tecnico_id
         LEFT JOIN borradores_servicio bs ON bs.preinspeccion_id = ip.id
         ORDER BY CASE WHEN ip.estado = 'abierto' THEN 0 ELSE 1 END, ip.creado_en DESC
         LIMIT 120"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $preliminares = [];
}
$preliminaresAbiertas = 0;
foreach ($preliminares as $p) {
    if (($p['estado'] ?? 'abierto') === 'abierto') $preliminaresAbiertas++;
}

// Filtros del panel: por técnico, trabajo, rango de fechas y orden
$filterTecnico = trim($_GET['tecnico'] ?? '');
$filterTrabajo = trim($_GET['trabajo'] ?? '');
$filterDesde = trim($_GET['fecha_desde'] ?? '');
$filterHasta = trim($_GET['fecha_hasta'] ?? '');
$filterOrden = trim($_GET['orden_fecha'] ?? 'desc');
if (!in_array($filterOrden, ['desc', 'asc'], true)) {
    $filterOrden = 'desc';
}
$hayFiltros = ($filterTecnico !== '' || $filterTrabajo !== '' || $filterDesde !== '' || $filterHasta !== '' || $filterOrden !== 'desc');

function normTxt($s) {
    $s = trim((string)$s);
    if (function_exists('mb_strtolower')) $s = mb_strtolower($s, 'UTF-8');
    else $s = strtolower($s);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($ascii !== false) $s = strtolower($ascii);
    return $s;
}

function fechaFiltroInforme($r) {
    $fechaServicio = trim((string)($r['fecha'] ?? ''));
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaServicio)) {
        return $fechaServicio;
    }
    $creado = trim((string)($r['creado_en'] ?? ''));
    $ts = strtotime($creado);
    return $ts ? date('Y-m-d', $ts) : '0000-00-00';
}

function fechaOrdenInforme($r) {
    $fechaBase = fechaFiltroInforme($r);
    $creado = trim((string)($r['creado_en'] ?? ''));
    return $fechaBase . ' ' . ($creado !== '' ? $creado : '00:00:00');
}

function fechaInputValida($fecha) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$fecha);
}

// Separa los trabajos guardados en un informe. Soporta registros antiguos con coma
// y los nuevos registros con separador vertical " | ". No separa nombres como INGRESO/DEVOLUCION.
function separarTrabajosInforme($valor) {
    $valor = trim((string)$valor);
    if ($valor === '') return [];

    $valor = str_replace(["
", "
"], "
", $valor);
    $partes = preg_split('/\s*(?:
|\|)\s*/u', $valor, -1, PREG_SPLIT_NO_EMPTY);

    if (!$partes || count($partes) <= 1) {
        $partes = preg_split('/\s*,\s*/u', $valor, -1, PREG_SPLIT_NO_EMPTY);
    }

    $limpio = [];
    foreach ($partes as $p) {
        $p = trim((string)$p);
        if ($p !== '' && !in_array($p, $limpio, true)) $limpio[] = $p;
    }
    return $limpio;
}

// Armar lista única de trabajos para el autocompletado
$trabajosOpciones = [];
foreach ($trabajosGestion as $wrow) {
    $key = normTxt($wrow['nombre'] ?? '');
    if ($key !== '') $trabajosOpciones[$key] = $wrow['nombre'];
}
foreach ($rows as $r) {
    foreach (separarTrabajosInforme($r['trabajos'] ?? '') as $w) {
        $key = normTxt($w);
        if ($key !== '') $trabajosOpciones[$key] = $w;
    }
}
asort($trabajosOpciones, SORT_NATURAL | SORT_FLAG_CASE);

// Aplicar filtros combinados
$desdeValido = fechaInputValida($filterDesde);
$hastaValido = fechaInputValida($filterHasta);

$rowsFiltradas = array_values(array_filter($rows, function($r) use ($filterTecnico, $filterTrabajo, $filterDesde, $filterHasta, $desdeValido, $hastaValido) {
    $okTecnico = true;
    $okTrabajo = true;
    $okDesde = true;
    $okHasta = true;

    if ($filterTecnico !== '') {
        $okTecnico = strpos(normTxt($r['tecnico_nombre'] ?? ''), normTxt($filterTecnico)) !== false;
    }

    if ($filterTrabajo !== '') {
        $okTrabajo = strpos(normTxt($r['trabajos'] ?? ''), normTxt($filterTrabajo)) !== false;
    }

    $fechaInforme = fechaFiltroInforme($r);
    if ($desdeValido) {
        $okDesde = ($fechaInforme >= $filterDesde);
    }
    if ($hastaValido) {
        $okHasta = ($fechaInforme <= $filterHasta);
    }

    return $okTecnico && $okTrabajo && $okDesde && $okHasta;
}));

// Ordenar el resultado actual por fecha. Funciona aunque también filtres por técnico o trabajo.
usort($rowsFiltradas, function($a, $b) use ($filterOrden) {
    $fa = fechaOrdenInforme($a);
    $fb = fechaOrdenInforme($b);
    if ($fa === $fb) return 0;
    if ($filterOrden === 'asc') {
        return $fa <=> $fb;
    }
    return $fb <=> $fa;
});

// Agrupar informes por técnico
$porTecnico = [];
foreach ($rowsFiltradas as $r) { $porTecnico[$r['tecnico_id']][] = $r; }
$tecnicosMostrar = $hayFiltros
    ? array_values(array_filter($tecnicosTodos, function($t) use ($porTecnico) { return !empty($porTecnico[$t['id']]); }))
    : array_values(array_filter($tecnicosTodos, function($t) use ($porTecnico) {
        return (int)($t['activo'] ?? 1) === 1 || !empty($porTecnico[$t['id']]);
    }));
$totalMostrado = count($rowsFiltradas);

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function inicial($s) {
    $s = trim((string)$s);
    if ($s === '') return '?';
    if (function_exists('mb_substr') && function_exists('mb_strtoupper')) return mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8');
    return strtoupper(substr($s, 0, 1));
}
function fechaBonita($iso) {
    if (!$iso) return '—';
    $p = explode('-', $iso);
    return count($p) === 3 ? "$p[2]/$p[1]/$p[0]" : $iso;
}
function fechaHora($dt) {
    if (!$dt) return '—';
    $ts = strtotime($dt);
    return $ts ? date('d/m/Y · H:i:s', $ts) : e($dt);
}
function horarioServicioPanel($dt) {
    if (!$dt) return '<span class="muted">—</span>';
    $ts = strtotime((string)$dt);
    return $ts ? date('d/m/Y · H:i', $ts) : e($dt);
}
function valorCorto($v) {
    $v = trim((string)($v ?? ''));
    return $v !== '' ? e($v) : '<span class="muted">—</span>';
}
function tempCorta($v) {
    $v = trim((string)($v ?? ''));
    return $v !== '' ? e($v) . ' °C' : '—';
}
function estadoPreliminarClass($estado) {
    return trim((string)$estado) === 'cerrado' ? 'closed' : 'open';
}

$flash = null;
if (($_GET['error'] ?? '') === 'tecnico_duplicado') {
    $flash = [
        'tipo' => 'err',
        'icono' => '⚠️',
        'titulo' => 'Técnico duplicado',
        'texto' => 'Ya existe un técnico registrado con el mismo nombre y apellido: ' . trim((string)($_GET['nombre'] ?? '')) . '. No se creó otro registro.'
    ];
} elseif (($_GET['ok'] ?? '') === 'tecnico_agregado') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '✅',
        'titulo' => 'Técnico agregado',
        'texto' => 'Se registró correctamente: ' . trim((string)($_GET['nombre'] ?? '')) . '.'
    ];
} elseif (($_GET['ok'] ?? '') === 'tecnico_eliminado') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '🗑️',
        'titulo' => 'Técnico eliminado de la lista',
        'texto' => 'El técnico ' . trim((string)($_GET['nombre'] ?? '')) . ' ya no aparecerá en el formulario principal. Sus informes históricos se conservan.'
    ];
} elseif (($_GET['ok'] ?? '') === 'cliente_agregado') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '🏢',
        'titulo' => 'Cliente agregado',
        'texto' => 'El cliente ' . trim((string)($_GET['nombre'] ?? '')) . ' ya aparecerá como sugerencia en el index.'
    ];
} elseif (($_GET['ok'] ?? '') === 'cliente_editado') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '✏️',
        'titulo' => 'Cliente actualizado',
        'texto' => 'El cliente ' . trim((string)($_GET['nombre'] ?? '')) . ' fue corregido correctamente.'
    ];
} elseif (($_GET['ok'] ?? '') === 'cliente_eliminado') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '🗑️',
        'titulo' => 'Cliente eliminado',
        'texto' => 'El cliente ' . trim((string)($_GET['nombre'] ?? '')) . ' ya no aparecerá como sugerencia para los técnicos.'
    ];
} elseif (($_GET['ok'] ?? '') === 'cotizacion_agregada') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '📄',
        'titulo' => 'Reporte agregado',
        'texto' => 'El reporte ' . trim((string)($_GET['cotizacion'] ?? '')) . ' ya aparecerá como sugerencia para el técnico.'
    ];
} elseif (($_GET['ok'] ?? '') === 'cotizacion_editada') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '✏️',
        'titulo' => 'Reporte actualizado',
        'texto' => 'El reporte ' . trim((string)($_GET['cotizacion'] ?? '')) . ' fue corregido y ya saldrá vinculada al cliente actualizado.'
    ];
} elseif (($_GET['ok'] ?? '') === 'cotizacion_eliminada') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '🗑️',
        'titulo' => 'Reporte retirado',
        'texto' => 'El reporte ' . trim((string)($_GET['cotizacion'] ?? '')) . ' ya no aparecerá como sugerencia para los técnicos. Los informes anteriores no se modifican.'
    ];
} elseif (($_GET['ok'] ?? '') === 'contenedor_agregado') {
    $toast = [
        'type' => 'ok',
        'titulo' => 'Contenedor agregado',
        'texto' => 'El contenedor ' . trim((string)($_GET['contenedor'] ?? '')) . ' ya aparecerá como sugerencia en el index.'
    ];
} elseif (($_GET['ok'] ?? '') === 'contenedor_editado') {
    $toast = [
        'type' => 'ok',
        'titulo' => 'Contenedor actualizado',
        'texto' => 'El contenedor ' . trim((string)($_GET['contenedor'] ?? '')) . ' fue corregido.'
    ];
} elseif (($_GET['ok'] ?? '') === 'contenedor_eliminado') {
    $toast = [
        'type' => 'ok',
        'titulo' => 'Contenedor retirado',
        'texto' => 'El contenedor ' . trim((string)($_GET['contenedor'] ?? '')) . ' ya no aparecerá como sugerencia para los técnicos.'
    ];
} elseif (($_GET['error'] ?? '') === 'contenedor_duplicado') {
    $toast = [
        'type' => 'warn',
        'titulo' => 'Contenedor duplicado',
        'texto' => 'Ya existe otro contenedor activo con el número ' . trim((string)($_GET['contenedor'] ?? '')) . '.'
    ];
} elseif (($_GET['error'] ?? '') === 'contenedor_invalido') {
    $toast = [
        'type' => 'warn',
        'titulo' => 'Contenedor inválido',
        'texto' => 'El contenedor debe tener entre 3 y 60 caracteres y solo letras, números o guion.'
    ];
} elseif (($_GET['ok'] ?? '') === 'maquina_serial_guardado') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '✅',
        'titulo' => 'Serie reefer guardada',
        'texto' => 'El número de serie ' . trim((string)($_GET['serial'] ?? '')) . ' ya aparecerá como sugerencia en el formulario técnico.'
    ];
} elseif (($_GET['error'] ?? '') === 'maquina_serial_invalido') {
    $flash = [
        'tipo' => 'err',
        'icono' => '⚠️',
        'titulo' => 'Serie reefer inválida',
        'texto' => 'Completa el número de serie, la marca y el controlador. Los controladores SG-3000 y SG-5000 pertenecen al catálogo de generadores.'
    ];
} elseif (($_GET['error'] ?? '') === 'maquina_serial_generador') {
    $flash = [
        'tipo' => 'err',
        'icono' => '⚠️',
        'titulo' => 'Serie perteneciente a generador',
        'texto' => 'El número de serie ' . trim((string)($_GET['serial'] ?? '')) . ' ya está registrado en el catálogo independiente de generadores.'
    ];
} elseif (($_GET['error'] ?? '') === 'maquina_duplicada') {
    $flash = [
        'tipo' => 'err',
        'icono' => '⚠️',
        'titulo' => 'Serie duplicada',
        'texto' => 'Ya existe una máquina reefer activa con el número de serie ' . trim((string)($_GET['serial'] ?? '')) . '.'
    ];
} elseif (($_GET['ok'] ?? '') === 'maquina_eliminada') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '🗑️',
        'titulo' => 'Serie reefer retirada',
        'texto' => 'El número de serie ' . trim((string)($_GET['serial'] ?? '')) . ' ya no aparecerá como sugerencia. Los informes existentes se conservan.'
    ];
} elseif (($_GET['ok'] ?? '') === 'preliminar_actualizada') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '✅',
        'titulo' => 'Inspección preliminar actualizada',
        'texto' => 'Los datos iniciales fueron corregidos correctamente desde supervisión.'
    ];
} elseif (($_GET['ok'] ?? '') === 'informe_actualizado') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '✅',
        'titulo' => 'Informe actualizado',
        'texto' => 'Se guardaron las correcciones y se reemplazó el PDF del reporte ' . trim((string)($_GET['orden'] ?? '')) . '. Cliente: ' . trim((string)($_GET['cliente'] ?? ''))
    ];
} elseif (($_GET['ok'] ?? '') === 'informe_eliminado') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '🗑️',
        'titulo' => 'Informe técnico eliminado',
        'texto' => 'Se eliminó el informe técnico' . ((trim((string)($_GET['orden'] ?? '')) !== '') ? ' del reporte ' . trim((string)($_GET['orden'] ?? '')) : '') . '. También se retiró el PDF asociado si existía en el servidor.'
    ];
} elseif (($_GET['error'] ?? '') === 'informe_no_encontrado') {
    $flash = [
        'tipo' => 'err',
        'icono' => '⚠️',
        'titulo' => 'Informe no encontrado',
        'texto' => 'No se encontró el informe técnico solicitado. Es posible que ya haya sido eliminado.'
    ];
} elseif (($_GET['error'] ?? '') === 'informe_eliminar_error') {
    $flash = [
        'tipo' => 'err',
        'icono' => '⚠️',
        'titulo' => 'No se pudo eliminar',
        'texto' => 'Ocurrió un problema al eliminar el informe técnico. Intenta nuevamente o revisa la conexión.'
    ];
} elseif (($_GET['error'] ?? '') === 'cotizacion_duplicada') {
    $flash = [
        'tipo' => 'warn',
        'icono' => '⚠️',
        'titulo' => 'Reporte duplicado',
        'texto' => 'Ya existe otro reporte activo con el número ' . trim((string)($_GET['cotizacion'] ?? '')) . '. Corrige el número o edita el registro existente.'
    ];
} elseif (($_GET['error'] ?? '') === 'cotizacion_invalida') {
    $flash = [
        'tipo' => 'warn',
        'icono' => '⚠️',
        'titulo' => 'Reporte inválido',
        'texto' => 'El reporte debe tener solo números, entre 6 y 15 dígitos, y debe estar vinculada a un cliente activo.'
    ];
} elseif (($_GET['error'] ?? '') === 'cliente_duplicado') {
    $flash = [
        'tipo' => 'warn',
        'icono' => '⚠️',
        'titulo' => 'Cliente duplicado',
        'texto' => 'Ya existe un cliente registrado como ' . trim((string)($_GET['nombre'] ?? '')) . '.'
    ];
} elseif (($_GET['ok'] ?? '') === 'bulk_preliminares') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '🗑️',
        'titulo' => 'Preliminares eliminadas',
        'texto' => 'Se eliminaron ' . (int)($_GET['cantidad'] ?? 0) . ' inspección(es) preliminar(es).'
    ];
} elseif (($_GET['ok'] ?? '') === 'bulk_informes') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '🗑️',
        'titulo' => 'Informes eliminados',
        'texto' => 'Se eliminaron ' . (int)($_GET['cantidad'] ?? 0) . ' informe(s) final(es) y ' . (int)($_GET['pdfs'] ?? 0) . ' PDF.'
    ];
} elseif (($_GET['ok'] ?? '') === 'bulk_todo') {
    $flash = [
        'tipo' => 'ok',
        'icono' => '✅',
        'titulo' => 'Datos de prueba eliminados',
        'texto' => 'Se eliminaron ' . (int)($_GET['informes'] ?? 0) . ' informe(s), ' . (int)($_GET['preliminares'] ?? 0) . ' preliminar(es) y ' . (int)($_GET['pdfs'] ?? 0) . ' PDF. Los técnicos y catálogos se conservaron.'
    ];
} elseif (($_GET['error'] ?? '') === 'bulk_sin_seleccion') {
    $flash = [
        'tipo' => 'warn',
        'icono' => '⚠️',
        'titulo' => 'No seleccionaste registros',
        'texto' => 'Marca al menos un registro antes de presionar eliminar.'
    ];
} elseif (($_GET['error'] ?? '') === 'bulk_confirmacion') {
    $flash = [
        'tipo' => 'warn',
        'icono' => '⚠️',
        'titulo' => 'Confirmación incorrecta',
        'texto' => 'Para eliminar todos los datos de prueba debes escribir exactamente ELIMINAR TODO.'
    ];
} elseif (in_array(($_GET['error'] ?? ''), ['bulk_error','bulk_csrf','bulk_accion'], true)) {
    $flash = [
        'tipo' => 'err',
        'icono' => '⚠️',
        'titulo' => 'No se pudo completar la eliminación',
        'texto' => 'La operación fue cancelada por seguridad. Recarga el panel e intenta nuevamente.'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Informes guardados — Panel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#eef2f7;--surface:#fff;--ink:#16263f;--ink-soft:#5a6b80;--ink-faint:#97a3b3;
  --line:#dde4ec;--accent:#1f6fc4;--accent-2:#47a3ff;--accent-soft:#e7f0fb;
  --ok:#2f9e44;--warn:#f59f00;--danger:#e03131;--danger-soft:#ffeaea;
  --radius:18px;--shadow:0 1px 2px rgba(22,38,63,.05),0 12px 34px rgba(22,38,63,.09)
}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--ink);font-family:'Manrope',system-ui,sans-serif;line-height:1.5;-webkit-font-smoothing:antialiased;padding-bottom:56px}
.hero{position:relative;overflow:hidden;color:#fff;padding:32px 20px 34px;background:linear-gradient(120deg,rgba(18,30,52,.96),rgba(18,30,52,.86)),url('zgroup-bg.jpg') center/cover no-repeat}
.hero::after{content:"";position:absolute;inset:auto -80px -120px auto;width:420px;height:260px;background:radial-gradient(circle,rgba(71,163,255,.25),transparent 70%);pointer-events:none}
.hero-inner{position:relative;z-index:1;max-width:1080px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
.hero-left{display:flex;align-items:center;gap:16px;min-width:280px}
.brand-plate{background:#fff;border-radius:14px;padding:10px 16px;box-shadow:0 10px 28px rgba(0,0,0,.28);display:inline-flex}
.brand-plate img{height:42px;width:auto;display:block}
.hero h1{font-family:'Archivo';font-weight:800;font-size:clamp(25px,5vw,38px);letter-spacing:-.02em;line-height:1.05}
.hero p{color:#c7d6e8;font-size:14px;margin-top:6px;font-weight:600}
.hero-actions{display:flex;gap:10px;flex-wrap:wrap}
.back{color:#fff;text-decoration:none;font-weight:800;font-size:13.5px;border:1.5px solid rgba(255,255,255,.25);padding:12px 17px;border-radius:13px;white-space:nowrap;background:rgba(255,255,255,.06);backdrop-filter:blur(3px)}
.back:hover{border-color:#8fc0f2;background:rgba(143,192,242,.14)}
.wrap{max-width:1080px;margin:0 auto;padding:24px 16px 0}
.tech-showcase{display:grid;grid-template-columns:1.35fr .9fr .9fr;gap:14px;margin-top:-10px;margin-bottom:18px}
.show-card{position:relative;overflow:hidden;min-height:142px;border-radius:20px;color:#fff;box-shadow:var(--shadow);border:1px solid rgba(255,255,255,.2);background:#16263f}
.show-card::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,rgba(22,38,63,.88),rgba(31,111,196,.35));z-index:1}
.show-card.bg1{background:linear-gradient(rgba(22,38,63,.5),rgba(22,38,63,.5)),url('zgroup-tec.jpg') center/cover no-repeat}
.show-card.bg2{background:linear-gradient(rgba(22,38,63,.5),rgba(22,38,63,.5)),url('zgroup-bg.jpg') center/cover no-repeat}
.show-card.bg3{background:linear-gradient(140deg,#17355c,#1f6fc4)}
.show-card .inside{position:absolute;z-index:2;left:18px;right:18px;bottom:16px}
.show-card .mini{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#b6d7ff;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);border-radius:999px;padding:5px 10px;margin-bottom:9px}
.show-card h2{font-family:'Archivo';font-size:20px;line-height:1.08;margin-bottom:5px}
.show-card p{font-size:12.5px;color:#d9e8fb;font-weight:600}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
.stat{background:var(--surface);border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);padding:16px;display:flex;align-items:center;gap:13px;text-align:left}
.stat-jump{cursor:pointer;font-family:inherit;width:100%;transition:.18s}
.stat-jump:hover{transform:translateY(-2px);border-color:#bcd8f4;box-shadow:0 18px 44px rgba(22,64,115,.13)}
#preliminaresPanel,#historialTecnicosPanel,#trabajosPanel,#clientesCreadosPanel,#reporteTicketForm,#ticketsOdooPanel{scroll-margin-top:22px}
.stat .ic{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;background:var(--accent-soft);font-size:22px}
.stat b{font-family:'Archivo';font-size:25px;color:var(--ink);line-height:1}
.stat span{display:block;color:var(--ink-faint);font-size:12.5px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-top:3px}
.card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;margin-bottom:18px}
.card.soft{background:linear-gradient(180deg,#fff,#f9fbfe)}
.card-head-line{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px}
.eyebrow{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);background:var(--accent-soft);border-radius:999px;padding:5px 10px;margin-bottom:7px}
.sect-title{font-family:'Archivo';font-weight:800;font-size:18px;color:var(--ink);display:flex;align-items:center;gap:8px;letter-spacing:-.01em}
.help{font-size:13px;color:var(--ink-faint);margin-top:4px;font-weight:600}
.add-form,.filter-form{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.add-form .f,.filter-form .f{flex:1;min-width:230px;display:flex;flex-direction:column;gap:6px}
label{font-size:12px;font-weight:800;color:var(--ink-soft);letter-spacing:.01em}
input[type=text],input[type=date],select{font-family:inherit;font-size:15px;color:var(--ink);background:#f7fafd;border:1.5px solid var(--line);border-radius:13px;padding:13px 14px;width:100%;transition:border-color .15s,box-shadow .15s,background .15s}
input[type=text]:focus,input[type=date]:focus,select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 4px var(--accent-soft);background:#fff}
select{appearance:none;-webkit-appearance:none;background-image:linear-gradient(45deg,transparent 50%,#5a6b80 50%),linear-gradient(135deg,#5a6b80 50%,transparent 50%);background-position:calc(100% - 18px) 55%,calc(100% - 12px) 55%;background-size:6px 6px,6px 6px;background-repeat:no-repeat;padding-right:36px}
.btn{font-family:'Archivo';font-weight:800;font-size:14.5px;border:none;border-radius:13px;padding:13px 18px;cursor:pointer;background:var(--accent);color:#fff;white-space:nowrap;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 7px 16px rgba(31,111,196,.22)}
.btn:hover{filter:brightness(1.05);transform:translateY(-1px)}
.btn:active{transform:translateY(0) scale(.99)}
.btn.secondary{background:#e6ebf2;color:var(--ink-soft);box-shadow:none}
.btn.secondary:hover{background:#d9e0ea;color:var(--ink)}
.btn.danger{background:var(--danger-soft);color:var(--danger);box-shadow:none}
.btn.danger:hover{background:var(--danger);color:#fff}
.btn:disabled{opacity:.45;cursor:not-allowed;transform:none;filter:none}
.work-manager{display:grid;grid-template-columns:.9fr 1.1fr;gap:16px;align-items:start}
.add-box,.search-box{border:1px solid var(--line);border-radius:16px;background:#fbfdff;padding:16px}
.search-wrap{position:relative;margin-top:10px}
.smart-results{position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:20;background:#fff;border:1.5px solid var(--line);border-radius:14px;box-shadow:0 16px 36px rgba(22,38,63,.16);max-height:260px;overflow-y:auto;display:none}
.smart-results.show{display:block}
.smart-item{padding:12px 14px;border-bottom:1px solid var(--line);cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:12px}
.smart-item:last-child{border-bottom:none}
.smart-item:hover{background:var(--accent-soft)}
.smart-item strong{font-size:13.5px;color:var(--ink)}
.smart-item small{font-size:11.5px;color:var(--ink-faint);font-weight:800;letter-spacing:.05em;text-transform:uppercase}
.empty-result{padding:13px 14px;color:var(--ink-faint);font-size:13px;font-style:italic}
.selected-work{margin-top:13px;border:1.5px solid #cfe1f7;border-radius:15px;background:linear-gradient(180deg,#f5f9ff,#fff);padding:14px;display:none}
.selected-work.show{display:block;animation:pop .18s ease}
@keyframes pop{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}
.selected-work .sel-label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:900;color:var(--accent);margin-bottom:4px}
.selected-work .sel-name{font-family:'Archivo';font-weight:800;font-size:17px;color:var(--ink);margin-bottom:11px}
.selected-work .sel-note{font-size:12.5px;color:var(--ink-soft);margin-top:10px}
.filter-actions{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}.filter-actions .btn{height:48px}.filter-note{margin-top:12px;background:var(--accent-soft);color:var(--ink-soft);border:1px solid #cfe1f7;border-radius:12px;padding:11px 13px;font-size:13px;font-weight:700}
.filter-note b{color:var(--accent)}
.no-results{background:#fff7e6;border:1px solid #f3d9a4;color:#7a5a13;border-radius:13px;padding:14px 15px;font-size:13.5px;margin-top:10px;font-weight:700}
.tec{padding:16px 0;border-top:1px solid var(--line)}
.tec:first-of-type{border-top:none;padding-top:0}
.tec-head{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.tec-avatar{flex:none;width:42px;height:42px;border-radius:14px;background:var(--accent-soft);color:var(--accent);display:grid;place-items:center;font-family:'Archivo';font-weight:900;font-size:16px}
.tec-name{font-family:'Archivo';font-weight:800;font-size:17px;line-height:1.1}
.tec-count{font-size:12.5px;color:var(--ink-faint);font-weight:700;margin-top:2px}
.table-wrap{overflow-x:auto;border-radius:14px;border:1px solid var(--line)}
table{width:100%;border-collapse:collapse;font-size:13.5px;min-width:720px;background:#fff}
th{text-align:left;font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--ink-faint);font-weight:900;padding:10px 11px;border-bottom:1.5px solid var(--line);background:#fbfdff}
td{padding:12px 11px;border-bottom:1px solid var(--line);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover{background:#f8fbff}
.dl{display:inline-flex;align-items:center;gap:5px;color:var(--accent);text-decoration:none;font-weight:800;font-size:13px;border:1.5px solid var(--accent-soft);background:var(--accent-soft);padding:8px 12px;border-radius:10px;white-space:nowrap}
.dl:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
.report-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}
.dl.danger{background:#fff0f0;border-color:#ffc9c9;color:#c92a2a}
.dl.danger:hover{background:#e03131;color:#fff;border-color:#e03131}
.empty{color:var(--ink-faint);font-size:13.5px;padding:8px 0 5px;font-style:italic}
.tag{display:inline-block;background:#eef0f3;color:var(--ink-soft);font-size:11.5px;font-weight:800;padding:4px 9px;border-radius:8px;margin:2px 3px;line-height:1.25}
.muted{color:var(--ink-faint)}
.flash{display:flex;gap:12px;align-items:flex-start;border-radius:16px;padding:14px 16px;margin-bottom:18px;border:1.5px solid var(--line);box-shadow:var(--shadow);background:#fff}
.flash .fi{width:36px;height:36px;border-radius:12px;display:grid;place-items:center;flex:none;font-size:18px}
.flash b{font-family:'Archivo';font-size:15px;display:block;margin-bottom:2px}
.flash p{font-size:13.5px;color:var(--ink-soft);font-weight:700}
.flash.err{border-color:#ffc9c9;background:#fff7f7}.flash.err .fi{background:#ffe3e3}.flash.err b{color:#c92a2a}
.flash.ok{border-color:#b2f2bb;background:#f2fff5}.flash.ok .fi{background:#d3f9d8}.flash.ok b{color:#2b8a3e}
.alarm-card{display:flex;align-items:center;justify-content:space-between;gap:14px;background:linear-gradient(135deg,#10213a,#1f6fc4);color:#fff;border:none;border-radius:20px;box-shadow:0 16px 38px rgba(31,111,196,.25);padding:17px 18px;margin-bottom:18px;overflow:hidden;position:relative}
.alarm-card::after{content:"";position:absolute;right:-80px;top:-80px;width:210px;height:210px;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 65%);pointer-events:none}
.alarm-left{position:relative;z-index:1;display:flex;align-items:center;gap:13px;min-width:0}
.alarm-icon{flex:none;width:46px;height:46px;border-radius:15px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.22);display:grid;place-items:center;font-size:23px}
.alarm-title{font-family:'Archivo';font-weight:900;font-size:16px;line-height:1.1}
.alarm-sub{font-size:12.5px;color:#d7e8ff;font-weight:700;margin-top:3px}
.alarm-actions{position:relative;z-index:1;display:flex;gap:9px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
.alarm-btn{font-family:'Archivo';font-weight:900;border:none;border-radius:13px;background:#fff;color:#155293;padding:12px 15px;cursor:pointer;box-shadow:0 8px 18px rgba(0,0,0,.18);white-space:nowrap}
.alarm-btn:hover{filter:brightness(1.04);transform:translateY(-1px)}
.alarm-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.22);padding:8px 11px;font-size:12px;font-weight:900;color:#eaf4ff;white-space:nowrap}
.alarm-dot{width:8px;height:8px;border-radius:50%;background:#ffcf33;box-shadow:0 0 0 4px rgba(255,207,51,.18)}
.alarm-dot.on{background:#51cf66;box-shadow:0 0 0 4px rgba(81,207,102,.18)}

/* ---------- Push tipo app / Facebook ---------- */
.push-card{display:flex;align-items:center;justify-content:space-between;gap:16px;background:linear-gradient(135deg,#10213a 0%,#1b4f88 55%,#1f6fc4 100%);color:#fff;border:1px solid rgba(255,255,255,.18);border-radius:24px;box-shadow:0 18px 46px rgba(31,111,196,.22);padding:20px 22px;margin-bottom:18px;overflow:hidden;position:relative}
.push-card::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 86% 0%,rgba(255,255,255,.24),transparent 34%),linear-gradient(90deg,rgba(255,255,255,.06),transparent 55%);pointer-events:none}
.push-left{position:relative;z-index:1;display:flex;align-items:center;gap:15px;min-width:0}
.push-ic{width:54px;height:54px;border-radius:17px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);display:grid;place-items:center;font-size:25px;flex:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.16)}
.push-title{font-family:'Archivo';font-weight:900;font-size:20px;line-height:1.1;letter-spacing:-.01em}
.push-sub{font-size:13.5px;color:#dcecff;font-weight:800;margin-top:4px;max-width:620px}
.push-actions{position:relative;z-index:1;display:flex;align-items:center;gap:13px;flex-wrap:wrap;justify-content:flex-end}
.push-pill{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:9px 12px;font-size:13px;font-weight:900;color:#fff;white-space:nowrap}
.push-dot{width:11px;height:11px;border-radius:50%;background:#ff6b6b;box-shadow:0 0 0 5px rgba(255,107,107,.16)}
.push-dot.on{background:#51cf66;box-shadow:0 0 0 6px rgba(81,207,102,.14)}
.push-switch{width:86px;height:46px;border:none;border-radius:999px;background:rgba(255,255,255,.30);padding:5px;cursor:pointer;box-shadow:inset 0 0 0 1px rgba(255,255,255,.30);transition:.18s;display:flex;align-items:center}
.push-switch span{width:36px;height:36px;border-radius:50%;background:#fff;box-shadow:0 5px 14px rgba(0,0,0,.24);transition:.18s;display:block}
.push-switch.on{background:#2f9e44}
.push-switch.on span{transform:translateX(40px)}
.push-switch:disabled{opacity:.55;cursor:not-allowed}
@media(max-width:700px){.push-card{align-items:flex-start;flex-direction:column;padding:18px}.push-actions{width:100%;justify-content:space-between}.push-switch{width:88px;height:48px}.push-switch span{width:38px;height:38px}.push-switch.on span{transform:translateX(40px)}.push-title{font-size:18px}.push-sub{font-size:12.8px}}


.telegram-link-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:#136fce;text-decoration:none;font-family:'Archivo';font-weight:900;font-size:13.5px;border-radius:999px;padding:13px 20px;box-shadow:0 8px 18px rgba(0,0,0,.16);white-space:nowrap;border:none;cursor:pointer;text-align:center}
.telegram-link-btn:hover{filter:brightness(.98);transform:translateY(-1px)}
.telegram-card .push-ic{background:rgba(255,255,255,.16)}
.tg-join-modal{position:fixed;inset:0;background:rgba(7,16,30,.55);z-index:10000;display:none;align-items:center;justify-content:center;padding:18px;backdrop-filter:blur(3px)}
.tg-join-modal.show{display:flex}
.tg-join-box{width:100%;max-width:410px;background:#fff;border-radius:22px;box-shadow:0 24px 80px rgba(0,0,0,.35);padding:24px;text-align:center;color:var(--ink);animation:pop .18s ease}
.tg-join-ic{width:62px;height:62px;margin:0 auto 13px;border-radius:20px;background:var(--accent-soft);display:grid;place-items:center;font-size:31px}
.tg-join-box h3{font-family:'Archivo';font-size:21px;margin-bottom:8px}
.tg-join-box p{font-size:14px;color:var(--ink-soft);font-weight:700;line-height:1.45;margin-bottom:18px}
.tg-join-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.tg-join-actions .telegram-link-btn{background:var(--accent);color:#fff;min-width:170px}
.tg-cancel-btn{border:none;border-radius:999px;padding:13px 18px;background:#e6ebf2;color:var(--ink-soft);font-family:'Archivo';font-weight:900;cursor:pointer}
.tg-cancel-btn:hover{background:#d9e0ea}
@media(max-width:700px){.telegram-link-btn{width:100%;margin-top:4px}.telegram-card .push-actions{gap:10px}.tg-join-actions{flex-direction:column}.tg-cancel-btn{width:100%}}

.live-popup{position:fixed;right:18px;bottom:22px;z-index:9999;max-width:min(380px,calc(100vw - 30px));background:#fff;border:1.5px solid #cfe1f7;border-left:6px solid var(--accent);border-radius:18px;box-shadow:0 18px 50px rgba(0,0,0,.24);padding:15px 15px 14px;display:none;animation:pop .18s ease}
.live-popup.show{display:block}
.live-popup .lp-top{display:flex;gap:11px;align-items:flex-start}
.live-popup .lp-ic{width:38px;height:38px;border-radius:12px;background:var(--accent-soft);display:grid;place-items:center;font-size:20px;flex:none}
.live-popup b{font-family:'Archivo';font-size:15.5px;color:var(--ink)}
.live-popup p{font-size:13px;color:var(--ink-soft);margin-top:3px;font-weight:700}
.live-popup .lp-actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
.live-popup a,.live-popup button{font-family:'Archivo';font-weight:800;font-size:12.5px;border-radius:10px;padding:9px 11px;border:none;text-decoration:none;cursor:pointer}
.live-popup a{background:var(--accent);color:#fff}.live-popup button{background:#e6ebf2;color:var(--ink-soft)}


.catalog-grid{display:grid;grid-template-columns:1fr;gap:14px}
.catalog-box{background:#f8fbff;border:1px solid var(--line);border-radius:16px;padding:14px}
.catalog-fold{margin-top:16px;border:1px solid var(--line);border-radius:20px;overflow:hidden;background:#fff;box-shadow:0 10px 26px rgba(15,34,58,.05)}
.catalog-fold>summary{list-style:none;cursor:pointer;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:18px;transition:.18s;background:#fff}
.catalog-fold>summary::-webkit-details-marker{display:none}
.catalog-fold>summary:hover{background:#f8fbff}
.catalog-fold[open]>summary{border-bottom:1px solid var(--line);background:linear-gradient(180deg,#fff,#f8fbff)}
.catalog-fold-left{display:flex;align-items:center;gap:13px;min-width:0}
.catalog-fold-icon{width:42px;height:42px;border-radius:16px;background:#eaf3ff;display:grid;place-items:center;font-size:20px;flex:none}
.catalog-fold-kicker{display:inline-flex;align-items:center;background:var(--accent-soft);color:var(--accent);font-size:11px;text-transform:uppercase;letter-spacing:.16em;font-weight:900;border-radius:999px;padding:6px 10px;margin-bottom:7px}
.catalog-fold-title{font-family:'Archivo';font-weight:900;color:var(--ink);font-size:20px;line-height:1.1}
.catalog-fold-sub{font-size:13px;color:var(--ink-faint);font-weight:750;margin-top:4px}
.catalog-fold-right{display:flex;align-items:center;gap:10px;flex:none}
.catalog-fold-count{background:#eef4fb;color:var(--ink-soft);border:1px solid var(--line);padding:9px 12px;border-radius:999px;font-size:13px;font-weight:900;white-space:nowrap}
.catalog-fold-arrow{width:34px;height:34px;border-radius:12px;background:#10213a;color:#fff;display:grid;place-items:center;font-weight:900;transition:.18s}
.catalog-fold[open] .catalog-fold-arrow{transform:rotate(180deg);background:var(--accent)}
.catalog-fold-body{padding:0}
.catalog-list{margin:0;border:none;border-radius:0;overflow:hidden;background:#fff}
.catalog-list-title{font-family:'Archivo';font-weight:900;color:var(--ink-soft);font-size:13px;text-transform:uppercase;letter-spacing:.08em;background:#f5f9ff;padding:11px 13px;border-bottom:1px solid var(--line)}
.catalog-row{display:grid;grid-template-columns:160px 1fr 1fr 210px;gap:10px;align-items:center;padding:11px 13px;border-bottom:1px solid #edf2f8}
.catalog-row:last-child{border-bottom:none}
.catalog-row b{font-family:'Archivo';color:var(--accent);font-size:14px}
.catalog-row span{font-weight:850;color:var(--ink)}
.catalog-row em{font-style:normal;color:var(--ink-faint);font-size:12.5px;font-weight:750}
.catalog-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}
.mini-action{border:1px solid #cfe1f7;background:#eef6ff;color:var(--accent);font-family:'Archivo';font-weight:900;border-radius:12px;padding:8px 10px;cursor:pointer;font-size:12.5px;text-decoration:none}
.mini-action:hover{filter:brightness(.98)}
.mini-action.danger{background:var(--danger-soft);border-color:#ffc9c9;color:var(--danger)}
.inline-delete{display:inline;margin:0}

/* ---------- Reportes agrupados por cliente ---------- */
.report-client-groups{padding:14px;background:#f4f8fd;display:grid;gap:12px}
.report-client-group{border:1px solid #d8e5f4;border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 7px 18px rgba(16,33,58,.055)}
.report-client-group>summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 15px;background:#fff;transition:.16s}
.report-client-group>summary::-webkit-details-marker{display:none}
.report-client-group>summary:hover{background:#f8fbff}
.report-client-group[open]>summary{background:linear-gradient(180deg,#fff,#f1f7ff);border-bottom:1px solid #dce8f5}
.report-client-main{display:flex;align-items:center;gap:11px;min-width:0}
.report-client-avatar{width:38px;height:38px;border-radius:13px;background:#e8f2ff;color:#1f6fc4;display:grid;place-items:center;font-family:Archivo,sans-serif;font-weight:900;flex:none}
.report-client-name{font-family:Archivo,sans-serif;font-size:15px;font-weight:900;color:#10213a;line-height:1.2;word-break:break-word}
.report-client-sub{font-size:11.5px;color:#73839a;font-weight:750;margin-top:3px}
.report-client-summary-right{display:flex;align-items:center;gap:9px;flex:none}
.report-client-count{display:inline-flex;align-items:center;background:#eaf3ff;color:#155293;border:1px solid #cfe1f6;border-radius:999px;padding:7px 10px;font-size:11.5px;font-weight:900;white-space:nowrap}
.report-client-chevron{width:30px;height:30px;border-radius:10px;background:#10213a;color:#fff;display:grid;place-items:center;font-weight:900;transition:.16s}
.report-client-group[open] .report-client-chevron{transform:rotate(180deg);background:#1f6fc4}
.report-client-body{background:#fff}
.report-client-row{display:grid;grid-template-columns:minmax(150px,1fr) minmax(250px,2fr);gap:12px;align-items:center;padding:12px 14px;border-bottom:1px solid #edf2f8}
.report-client-row:last-child{border-bottom:none}
.report-number-wrap{display:flex;align-items:center;gap:10px;min-width:0}
.report-number-icon{width:34px;height:34px;border-radius:11px;background:#eef6ff;color:#1f6fc4;display:grid;place-items:center;font-size:15px;flex:none}
.report-number-label{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:#8795a8;font-weight:900;margin-bottom:2px}
.report-number-value{font-family:Archivo,sans-serif;color:#1f6fc4;font-size:14px;font-weight:900;word-break:break-word}
.report-client-empty{padding:18px;color:#73839a;font-size:13px;font-weight:750;text-align:center}
@media(max-width:720px){
  .report-client-groups{padding:10px}.report-client-group>summary{align-items:flex-start}.report-client-summary-right{margin-top:2px}
  .report-client-row{grid-template-columns:1fr}.report-client-row .catalog-actions{justify-content:flex-start;padding-left:44px}
  .report-client-name{font-size:14px}.report-client-count{padding:6px 8px}
}

.editing-hint{display:none;margin-top:8px;background:#fff7e6;border:1px solid #ffd8a8;color:#7a4d00;border-radius:12px;padding:9px 11px;font-weight:850;font-size:13px}
.editing-hint.on{display:block}
@media(max-width:860px){.catalog-row{grid-template-columns:1fr}.catalog-actions{justify-content:flex-start}.catalog-box{padding:12px}.catalog-fold>summary{align-items:flex-start;flex-direction:column}.catalog-fold-right{width:100%;justify-content:space-between}.catalog-fold-title{font-size:18px}}

/* ---------- Panel compacto / Ver todo ---------- */
.panel-compact-note{display:inline-flex;align-items:center;gap:7px;background:#f5f9ff;border:1px solid #cfe1f7;color:var(--ink-soft);font-weight:850;border-radius:999px;padding:7px 11px;font-size:12.5px;margin-top:8px}
.row-hidden{display:none}
.row-hidden.show{display:table-row}
.toggle-btn{margin-top:12px;width:auto;box-shadow:none}
.admin-divider{height:1px;background:var(--line);margin:18px 0}
.muted-tag{margin-left:8px;background:#eef2f7!important;color:#7a8796!important;border:1px solid #d9e0ea!important}
.tec-summary-line{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px}
.small-danger-note{font-size:12px;color:var(--ink-faint);font-weight:700;margin-top:8px}
@media(max-width:650px){.toggle-btn{width:100%}}



/* ---------- Panel compacto desplegable ---------- */
details.fold-card{padding:0;overflow:hidden}
details.fold-card>summary{list-style:none;cursor:pointer;padding:22px 24px;display:flex;align-items:center;justify-content:space-between;gap:18px;border-radius:var(--radius);transition:.18s;background:#fff}
details.fold-card>summary::-webkit-details-marker{display:none}
details.fold-card>summary:hover{background:#f8fbff}
details.fold-card[open]>summary{border-bottom:1px solid var(--line);border-radius:var(--radius) var(--radius) 0 0;background:linear-gradient(180deg,#fff,#f8fbff)}
.fold-title{display:flex;align-items:flex-start;gap:12px;min-width:0}
.fold-icon{width:38px;height:38px;border-radius:14px;background:#eaf3ff;color:#0b61b3;display:grid;place-items:center;font-size:18px;flex:none;font-weight:900}
.fold-kicker{display:inline-flex;align-items:center;gap:6px;background:var(--accent-soft);color:var(--accent);font-size:11px;text-transform:uppercase;letter-spacing:.16em;font-weight:900;border-radius:999px;padding:7px 11px;margin-bottom:8px}
.fold-main{font-family:'Archivo';font-weight:900;color:var(--ink);font-size:22px;line-height:1.1}
.fold-sub{font-size:13.5px;color:var(--ink-faint);font-weight:700;margin-top:5px;max-width:760px}
.fold-right{display:flex;align-items:center;gap:10px;flex:none}
.fold-count{background:#eef4fb;color:var(--ink-soft);border:1px solid var(--line);padding:9px 12px;border-radius:999px;font-size:13px;font-weight:900;white-space:nowrap}
.fold-arrow{width:34px;height:34px;border-radius:12px;background:#10213a;color:#fff;display:grid;place-items:center;font-weight:900;transition:.18s}
details[open] .fold-arrow{transform:rotate(180deg);background:var(--accent)}
.fold-body{padding:18px 24px 24px}
.tech-accordion{margin:0 0 14px;border:1px solid var(--line);border-radius:18px;background:#fff;overflow:hidden}
.tech-accordion>summary{list-style:none;cursor:pointer;padding:17px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px;transition:.18s}
.tech-accordion>summary::-webkit-details-marker{display:none}
.tech-accordion>summary:hover{background:#f8fbff}
.tech-accordion[open]>summary{border-bottom:1px solid var(--line);background:#f8fbff}
.tech-summary-left{display:flex;align-items:center;gap:12px;min-width:0}
.tech-summary-text{min-width:0}
.tech-summary-name{font-family:'Archivo';font-weight:900;color:var(--ink);font-size:20px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tech-summary-count{color:var(--ink-faint);font-size:13px;font-weight:800;margin-top:2px}
.tech-summary-action{display:flex;align-items:center;gap:10px;color:var(--accent);font-weight:900;font-size:13px;white-space:nowrap}
.tech-summary-action .fold-arrow{width:30px;height:30px;border-radius:10px;font-size:12px}
.tech-body{padding:16px 18px 18px}
@media(max-width:720px){details.fold-card>summary{align-items:flex-start;flex-direction:column}.fold-right{width:100%;justify-content:space-between}.fold-main{font-size:19px}.tech-accordion>summary{align-items:flex-start;flex-direction:column}.tech-summary-action{width:100%;justify-content:space-between}.fold-body{padding:14px}.tech-body{padding:14px}}

/* ---------- Preliminares en panel ---------- */
.pre-status-badge{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 10px;font-size:11.5px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
.pre-status-badge.open{background:#fff4d6;color:#8a5a00;border:1px solid #ffd978}
.pre-status-badge.closed{background:#e7f7ee;color:#1f7a3d;border:1px solid #b2e7c3}
.pre-details{margin-top:8px}
.pre-details summary{cursor:pointer;color:var(--accent);font-weight:900;font-size:12.5px;list-style:none;display:inline-flex;align-items:center;gap:6px;background:var(--accent-soft);border-radius:10px;padding:7px 10px}
.pre-details summary::-webkit-details-marker{display:none}
.pre-details summary:hover{background:var(--accent);color:#fff}
.pre-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:10px;background:#f8fbff;border:1px solid var(--line);border-radius:14px;padding:12px;min-width:520px}
.pre-item{background:#fff;border:1px solid #e5edf6;border-radius:11px;padding:10px}
.pre-item b{display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-faint);margin-bottom:3px}
.pre-item span{font-size:13px;font-weight:750;color:var(--ink-soft);white-space:pre-wrap}
.pre-item.full{grid-column:1/-1}
.pre-temp{display:inline-flex;gap:6px;flex-wrap:wrap}
.pre-temp span{background:#eef4fb;border-radius:999px;padding:4px 8px;font-size:12px;color:var(--ink-soft);font-weight:900}
@media(max-width:650px){.pre-grid{grid-template-columns:1fr;min-width:0}.pre-item.full{grid-column:auto}}

@media(max-width:860px){.tech-showcase{grid-template-columns:1fr}.stat-grid{grid-template-columns:1fr}.work-manager{grid-template-columns:1fr}.hero-left{align-items:flex-start}.hero-inner{align-items:flex-start}.card-head-line{flex-direction:column}}
@media(max-width:650px){.add-form,.filter-form{flex-direction:column;align-items:stretch}.btn{width:100%}.hero-left{flex-direction:column}.brand-plate img{height:34px}.alarm-card{flex-direction:column;align-items:stretch}.alarm-actions{justify-content:stretch}.alarm-btn{width:100%}.alarm-pill{justify-content:center}.live-popup{left:12px;right:12px;bottom:16px;max-width:none}}
</style>

<style id="zg-edit-report-panel-style-main">
.report-actions .edit-report{background:#fff7db!important;color:#8a5a00!important;border:1px solid #ffe08a!important}.report-actions .edit-report:hover{background:#ffefb8!important;transform:translateY(-1px)}
.report-actions .edit-time{background:#eaf4ff!important;color:#155293!important;border:1px solid #bdd9f5!important}.report-actions .edit-time:hover{background:#dbeeff!important;transform:translateY(-1px)}
.service-time-cell{min-width:160px;font-size:11.5px;line-height:1.45}.service-time-cell b{color:#17385d}.service-time-cell span{display:block;color:#60738a}
</style>
<style id="zg-panel-bulk-v9-style">
.bulk-danger-zone{margin:0 0 18px;background:linear-gradient(135deg,#fff7f7,#fff);border:1.5px solid #ffc9c9;border-radius:18px;padding:16px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:var(--shadow)}
.bulk-danger-zone h3{font-family:Archivo,sans-serif;font-size:17px;color:#8f1d1d;margin-bottom:4px}
.bulk-danger-zone p{font-size:12.5px;color:#7a4a4a;font-weight:650;max-width:720px}
.bulk-danger-btn,.bulk-delete-btn{border:0;border-radius:12px;background:#d92d20;color:#fff;font-family:Archivo,sans-serif;font-weight:800;cursor:pointer;padding:11px 15px;box-shadow:0 8px 18px rgba(217,45,32,.18)}
.bulk-danger-btn:hover,.bulk-delete-btn:hover{filter:brightness(.96);transform:translateY(-1px)}
.bulk-delete-btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
.bulk-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:12px 13px;margin-bottom:12px;background:#f5f9fe;border:1px solid #d8e7f7;border-radius:14px}
.bulk-toolbar-left,.bulk-toolbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.bulk-check-label{display:inline-flex;align-items:center;gap:8px;font-size:12.5px;font-weight:800;color:#17385d;cursor:pointer}
.bulk-check-label input,.bulk-row-check{width:18px;height:18px;accent-color:#1f6fc4;cursor:pointer}
.bulk-selected-count{display:inline-flex;align-items:center;background:#e7f0fb;color:#155293;border:1px solid #c9ddf4;border-radius:999px;padding:6px 10px;font-size:11.5px;font-weight:900}
.bulk-select-col{width:44px;text-align:center!important}
.tech-bulk-row{display:flex;align-items:center;justify-content:flex-end;padding:9px 10px;background:#f7faff;border-bottom:1px solid #e3edf8}
.bulk-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(13,27,48,.62);backdrop-filter:blur(6px)}
.bulk-modal.show{display:flex}
.bulk-modal-box{width:min(520px,100%);background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 28px 80px rgba(0,0,0,.30);border:1px solid #dce6f2}
.bulk-modal-head{padding:18px 20px;background:linear-gradient(135deg,#fff1f1,#fff);border-bottom:1px solid #ffd2d2;display:flex;align-items:center;gap:12px}
.bulk-modal-icon{width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#ffe3e3;font-size:22px}
.bulk-modal-head h3{font-family:Archivo,sans-serif;font-size:19px;color:#7d1919}
.bulk-modal-body{padding:18px 20px;color:#53677f;font-weight:650;line-height:1.5}
.bulk-modal-body strong{color:#10213a}
.bulk-confirm-wrap{margin-top:14px;display:none}
.bulk-confirm-wrap.show{display:block}
.bulk-confirm-wrap label{display:block;font-size:12px;font-weight:900;color:#7d1919;margin-bottom:6px}
.bulk-confirm-wrap input{width:100%;min-height:46px;border:1.5px solid #efb3b3;border-radius:12px;padding:10px 12px;font:inherit;text-transform:uppercase}
.bulk-modal-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px}
.bulk-cancel{border:0;border-radius:12px;padding:11px 15px;background:#eaf0f6;color:#10213a;font-weight:900;cursor:pointer}
.bulk-confirm{border:0;border-radius:12px;padding:11px 15px;background:#d92d20;color:#fff;font-weight:900;cursor:pointer}
.bulk-confirm:disabled{opacity:.45;cursor:not-allowed}
@media(max-width:720px){
  .bulk-danger-zone{align-items:flex-start;flex-direction:column}.bulk-danger-btn{width:100%}
  .bulk-toolbar{align-items:stretch}.bulk-toolbar-left,.bulk-toolbar-right{width:100%;justify-content:space-between}
  .bulk-delete-btn{width:100%}.bulk-modal-actions{flex-direction:column-reverse}.bulk-modal-actions button{width:100%}
  .bulk-select-col{width:38px}
}
</style>
</head>
<body>
<header class="hero">
  <div class="hero-inner">
    <div class="hero-left">
      <div class="brand-plate"><img src="zgroup-logo.png" alt="ZGROUP"></div>
      <div>
        <h1>Informes guardados</h1>
        <p>Lista de informes técnicos - Área técnica.</p>
      </div>
    </div>
    <div class="hero-actions">
      <a class="back" href="index.php">← Nuevo informe</a>
      <a class="back" href="odoo_importar_ticket.php">Importar Odoo</a>
      <a class="back" href="?salir=1">Salir</a>
    </div>
  </div>
</header>

<main class="wrap">
  <?php if ($flash): ?>
    <div class="flash <?= e($flash['tipo']) ?>">
      <div class="fi"><?= e($flash['icono']) ?></div>
      <div><b><?= e($flash['titulo']) ?></b><p><?= e($flash['texto']) ?></p></div>
    </div>
  <?php endif; ?>
  <section class="push-card telegram-card">
    <div class="push-left">
      <div class="push-ic">✈️</div>
      <div>
        <div class="push-title">Activar notificaciones</div>
        <div class="push-sub" id="pushText">Activa el switch y luego presiona “Unirse al grupo” para abrir Telegram en otra pestaña.</div>
      </div>
    </div>
    <div class="push-actions">
      <span class="push-pill" id="pushPill"><span class="push-dot" id="pushDot"></span><span id="pushStatus">Desactivado</span></span>
      <button class="push-switch" type="button" id="pushToggle" role="switch" aria-checked="false" title="Activar notificaciones por Telegram">
        <span></span>
      </button>
      <a class="telegram-link-btn" id="telegramJoinBtn" href="#" target="_blank" rel="noopener" style="display:none">UNIRSE AL GRUPO</a>
    </div>
  </section>

  <div class="tg-join-modal" id="telegramJoinModal" aria-hidden="true">
    <div class="tg-join-box">
      <div class="tg-join-ic">✈️</div>
      <h3>Unirse al grupo de supervisores</h3>
      <p>Para recibir las alertas en este dispositivo, abre Telegram en otra pestaña y únete al grupo. El panel se mantendrá abierto.</p>
      <div class="tg-join-actions">
        <button class="tg-cancel-btn" type="button" id="telegramCancelBtn">Ahora no</button>
        <a class="telegram-link-btn" id="telegramModalJoinBtn" href="#" target="_blank" rel="noopener">UNIRSE AL GRUPO</a>
      </div>
    </div>
  </div>

  <div class="live-popup" id="livePopup" aria-live="polite">
    <div class="lp-top">
      <div class="lp-ic">🔔</div>
      <div>
        <b id="liveTitle">Nuevo aviso</b>
        <p id="liveMsg">Se registró una novedad.</p>
      </div>
    </div>
    <div class="lp-actions">
      <a href="#" id="liveOpen" target="_blank" rel="noopener" style="display:none">Abrir PDF</a>
      <button type="button" id="liveReload" onclick="location.reload()">Actualizar panel</button>
      <button type="button" id="liveClose" onclick="document.getElementById('livePopup').classList.remove('show')">Cerrar</button>
    </div>
  </div>

  <section class="tech-showcase">
    <article class="show-card bg1">
      <div class="inside">
        <span class="mini">● Área técnica</span>
        <h2>Control de servicios en campo</h2>
        <p>Informes, evidencias y PDFs listos para supervisión.</p>
      </div>
    </article>
    <article class="show-card bg2">
      <div class="inside">
        <span class="mini">Reefer</span>
        <h2>Trabajos técnicos</h2>
        <p>Reparación, instalación, revisión y asistencia.</p>
      </div>
    </article>
    <article class="show-card bg3">
      <div class="inside">
        <span class="mini">PDF</span>
        <h2>Evidencias ordenadas</h2>
        <p>Fecha, hora, técnico, cliente y descarga rápida.</p>
      </div>
    </article>
  </section>

  <section class="stat-grid">
    <button type="button" class="stat stat-jump" data-jump="historialTecnicosPanel"><div class="ic">📄</div><div><b><?= count($rows) ?></b><span>Informes</span></div></button>
    <button type="button" class="stat stat-jump" data-jump="preliminaresPanel"><div class="ic">🟡</div><div><b><?= count($preliminares) ?></b><span>Preliminares</span></div></button>
    <button type="button" class="stat stat-jump" data-jump="historialTecnicosPanel"><div class="ic">👷</div><div><b><?= count($tecnicos) ?></b><span>Técnicos</span></div></button>
    <button type="button" class="stat stat-jump" data-jump="trabajosPanel"><div class="ic">🛠️</div><div><b><?= count($trabajosGestion) ?></b><span>Trabajos activos</span></div></button>
  </section>

  <div class="card soft" id="catalogoUniversalPanel">
    <div class="card-head-line"><div><span class="eyebrow">Catálogo general</span><div class="sect-title">📁 Clientes y equipos</div><p class="help">Los clientes y equipos quedan disponibles para las inspecciones técnicas.</p></div></div>
    <div class="catalog-grid">
      <form class="add-form catalog-box" method="post" id="clienteCatalogoForm">
        <input type="hidden" id="cliente_id_edit" name="cliente_id_edit" value="">
        <div class="f"><label for="nuevo_cliente_catalogo">Crear / editar cliente</label><input type="text" id="nuevo_cliente_catalogo" name="nuevo_cliente_catalogo" placeholder="Ej. Cliente SAC" autocomplete="off" required></div>
        <div class="editing-hint" id="clienteEditingHint">Editando un cliente existente.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn" id="clienteSubmitBtn" type="submit">Guardar cliente</button><button class="btn muted" id="clienteCancelEditBtn" type="button" style="display:none">Cancelar edición</button></div>
      </form>
      <form class="add-form catalog-box" method="post" id="contenedorCatalogoForm">
        <input type="hidden" id="contenedor_id_edit" name="contenedor_id_edit" value="">
        <div class="f"><label for="nuevo_contenedor_catalogo">Crear / editar contenedor</label><input type="text" id="nuevo_contenedor_catalogo" name="nuevo_contenedor_catalogo" placeholder="Ej. ZGRU01220-7" autocomplete="off" required></div>
        <div class="editing-hint" id="contenedorEditingHint">Editando un contenedor existente.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn" id="contenedorSubmitBtn" type="submit">Guardar contenedor</button><button class="btn muted" id="contenedorCancelEditBtn" type="button" style="display:none">Cancelar edición</button></div>
      </form>

      <form class="add-form catalog-box ticket-report-box" method="post" id="cotizacionCatalogoForm">
        <input type="hidden" id="cotizacion_id_edit" name="cotizacion_id_edit" value="">
        <div class="ticket-report-title">
          <span>🔗</span>
          <div>
            <b>Vincular ticket con N.° de reporte</b>
            <small>Selecciona un ticket importado de Odoo y asigna el reporte que utilizará el técnico.</small>
          </div>
        </div>
        <div class="ticket-report-fields">
          <div class="f">
            <label for="odoo_servicio_id_reporte">Ticket Odoo</label>
            <select class="ticket-report-select" id="odoo_servicio_id_reporte" name="odoo_servicio_id_reporte" required>
              <option value="">Selecciona un ticket importado...</option>
              <?php foreach ($serviciosOdooPanel as $ticketOdoo): ?>
                <?php $reporteVinculado = $reporteCatalogoPorTicket[(string)$ticketOdoo['ticket_ref']] ?? null; ?>
                <option value="<?= (int)$ticketOdoo['id'] ?>"
                        data-ticket-ref="<?= e($ticketOdoo['ticket_ref']) ?>"
                        data-report="<?= e($reporteVinculado['cotizacion'] ?? $ticketOdoo['numero_reporte'] ?? '') ?>"
                        data-catalog-id="<?= (int)($reporteVinculado['id'] ?? 0) ?>">
                  #<?= e($ticketOdoo['ticket_ref']) ?> — <?= e($ticketOdoo['cliente_nombre'] ?: 'Cliente no registrado') ?><?= ($reporteVinculado || $ticketOdoo['numero_reporte']!=='') ? ' — Reporte '.e($reporteVinculado['cotizacion'] ?? $ticketOdoo['numero_reporte']) : ' — Sin reporte' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="ticket-report-note">Los tickets aparecen automáticamente después de importarlos desde Odoo.</div>
          </div>
          <div class="f">
            <label for="nueva_cotizacion_catalogo">N.° de reporte</label>
            <input type="text" id="nueva_cotizacion_catalogo" name="nueva_cotizacion_catalogo" placeholder="Ej. 10020254524" inputmode="numeric" pattern="[0-9]{6,15}" maxlength="15" autocomplete="off" required>
            <div class="ticket-report-note">Debe contener entre 6 y 15 números.</div>
          </div>
        </div>
        <div class="editing-hint" id="cotizacionEditingHint">Editando un reporte ya vinculado.</div>
        <div class="ticket-report-actions">
          <button class="btn" id="cotizacionSubmitBtn" type="submit">Guardar reporte</button>
          <button class="btn muted" id="cotizacionCancelEditBtn" type="button" style="display:none">Cancelar edición</button>
        </div>
      </form>
    </div>
    <details class="catalog-fold" id="clientesCreadosPanel"><summary><div class="catalog-fold-left"><span class="catalog-fold-icon">🏢</span><div><div class="catalog-fold-kicker">General</div><div class="catalog-fold-title">Clientes creados</div><div class="catalog-fold-sub">Se usan para cualquier tipo de equipo.</div></div></div><div class="catalog-fold-right"><span class="catalog-fold-count"><?= count($clientesCatalogoPanel) ?> cliente(s)</span><span class="catalog-fold-arrow">⌄</span></div></summary><div class="catalog-fold-body"><div class="catalog-list"><?php foreach($clientesCatalogoPanel as $c):?><div class="catalog-row"><div><div class="catalog-title-row"><b><?= e($c['nombre']) ?></b><?= $c['origen']==='odoo' ? '<span class="catalog-origin">Odoo</span>' : '' ?></div><?php if($c['ruc']!==''||$c['contacto']!==''||$c['telefono']!==''||$c['correo']!==''||$c['direccion']!==''): ?><span class="catalog-detail"><?= e(implode(' · ',array_filter([$c['ruc']!==''?'RUC '.$c['ruc']:'',$c['contacto'],$c['telefono'],$c['correo']]))) ?></span><?php endif; ?><?php if($c['direccion']!==''): ?><span class="catalog-line">📍 <?= e($c['direccion']) ?></span><?php endif; ?></div><div class="catalog-actions"><button type="button" class="mini-action" data-edit-cliente data-id="<?= (int)$c['id'] ?>" data-nombre="<?= e($c['nombre']) ?>">✏️ Editar</button><form method="post" class="inline-delete" onsubmit="return confirm('¿Retirar este cliente del catálogo?');"><input type="hidden" name="eliminar_cliente_id" value="<?= (int)$c['id'] ?>"><button type="submit" class="mini-action danger">🗑️ Eliminar</button></form></div></div><?php endforeach;?></div></div></details>
    <details class="catalog-fold" id="cotizacionesCreadasPanel">
      <summary>
        <div class="catalog-fold-left">
          <span class="catalog-fold-icon">📄</span>
          <div>
            <div class="catalog-fold-kicker">Reportes</div>
            <div class="catalog-fold-title">Números de reporte por cliente</div>
            <div class="catalog-fold-sub">Abre un cliente para ver, editar o retirar sus números de reporte.</div>
          </div>
        </div>
        <div class="catalog-fold-right">
          <span class="catalog-fold-count"><?= count($reportesPorClientePanel) ?> cliente(s) · <?= count($cotizacionesCatalogoPanel) ?> reporte(s)</span>
          <span class="catalog-fold-arrow">⌄</span>
        </div>
      </summary>
      <div class="catalog-fold-body">
        <?php if (!$reportesPorClientePanel): ?>
          <div class="report-client-empty">Aún no hay números de reporte registrados.</div>
        <?php else: ?>
          <div class="report-client-groups">
            <?php foreach ($reportesPorClientePanel as $grupoReportes): ?>
              <?php
                $nombreClienteGrupo = (string)$grupoReportes['cliente_nombre'];
                $inicialClienteGrupo = function_exists('mb_substr')
                    ? mb_strtoupper(mb_substr($nombreClienteGrupo, 0, 1, 'UTF-8'), 'UTF-8')
                    : strtoupper(substr($nombreClienteGrupo, 0, 1));
              ?>
              <details class="report-client-group">
                <summary>
                  <div class="report-client-main">
                    <span class="report-client-avatar"><?= e($inicialClienteGrupo) ?></span>
                    <div>
                      <div class="report-client-name"><?= e($nombreClienteGrupo) ?></div>
                      <div class="report-client-sub">Reportes vinculados a este cliente</div>
                    </div>
                  </div>
                  <div class="report-client-summary-right">
                    <span class="report-client-count"><?= count($grupoReportes['reportes']) ?> reporte(s)</span>
                    <span class="report-client-chevron">⌄</span>
                  </div>
                </summary>
                <div class="report-client-body">
                  <?php foreach ($grupoReportes['reportes'] as $co): ?>
                    <div class="report-client-row">
                      <div class="report-number-wrap">
                        <span class="report-number-icon">📄</span>
                        <div>
                          <span class="report-number-label">N.º de reporte</span>
                          <span class="report-number-value"><?= e($co['cotizacion']) ?></span><?php if($co['ticket_ref']!==''||$co['cotizacion_odoo']!==''): ?><span class="catalog-detail"><?= e(implode(' · ',array_filter([$co['ticket_ref']!==''?'Ticket #'.$co['ticket_ref']:'',$co['cotizacion_odoo']!==''?'Cotización '.$co['cotizacion_odoo']:'']))) ?></span><?php endif; ?>
                        </div>
                      </div>
                      <div class="catalog-actions">
                        <button type="button" class="mini-action"
                                data-edit-cotizacion
                                data-id="<?= (int)$co['id'] ?>"
                                data-cotizacion="<?= e($co['cotizacion']) ?>"
                                data-cliente-id="<?= (int)$co['cliente_id'] ?>"
                                data-ticket-ref="<?= e($co['ticket_ref'] ?? '') ?>">✏️ Editar</button>
                        <form method="post" class="inline-delete"
                              onsubmit="return confirm('¿Retirar el reporte <?= e($co['cotizacion']) ?> de <?= e($nombreClienteGrupo) ?>?');">
                          <input type="hidden" name="eliminar_cotizacion_id" value="<?= (int)$co['id'] ?>">
                          <button type="submit" class="mini-action danger">🗑️ Eliminar</button>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </details>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </details>
    <details class="catalog-fold" id="contenedoresCreadosPanel"><summary><div class="catalog-fold-left"><span class="catalog-fold-icon">📦</span><div><div class="catalog-fold-kicker">Equipos</div><div class="catalog-fold-title">Contenedores importados</div><div class="catalog-fold-sub">Número, tamaño y datos técnicos guardados desde Odoo.</div></div></div><div class="catalog-fold-right"><span class="catalog-fold-count"><?= count($contenedoresCatalogoPanel) ?> equipo(s)</span><span class="catalog-fold-arrow">⌄</span></div></summary><div class="catalog-fold-body"><div class="odoo-cont-list"><?php foreach($contenedoresCatalogoPanel as $cc): ?><div class="odoo-cont-row"><div><b><?= e($cc['numero']) ?><?= $cc['origen']==='odoo'?'<span class="catalog-origin">Odoo</span>':'' ?></b><span><?= e(implode(' · ',array_filter([$cc['cliente_nombre'],$cc['ticket_ref']!==''?'Ticket #'.$cc['ticket_ref']:'',$cc['modalidad_comercial'],$cc['tamano_contenedor'],$cc['serial_unidad'],$cc['marca_equipo'],$cc['modelo_equipo'],$cc['controlador']]))) ?></span></div></div><?php endforeach; ?></div></div></details>
    <details class="catalog-fold" id="ticketsOdooPanel">
      <summary>
        <div class="catalog-fold-left">
          <span class="catalog-fold-icon">🎫</span>
          <div>
            <div class="catalog-fold-kicker">Odoo</div>
            <div class="catalog-fold-title">Tickets importados</div>
            <div class="catalog-fold-sub">Consulta los tickets disponibles y verifica cuáles ya tienen un N.° de reporte.</div>
          </div>
        </div>
        <div class="catalog-fold-right">
          <span class="catalog-fold-count"><?= count($serviciosOdooPanel) ?> ticket(s)</span>
          <span class="catalog-fold-arrow">⌄</span>
        </div>
      </summary>
      <div class="catalog-fold-body">
        <?php if (!$serviciosOdooPanel): ?>
          <div class="catalog-empty" style="margin:14px">Aún no hay tickets importados desde Odoo.</div>
        <?php else: ?>
          <div class="ticket-catalog-list">
            <?php foreach ($serviciosOdooPanel as $ticketVista): ?>
              <?php
                $reporteVista = $reporteCatalogoPorTicket[(string)$ticketVista['ticket_ref']]['cotizacion']
                    ?? ($ticketVista['numero_reporte'] ?? '');
              ?>
              <div class="ticket-catalog-row">
                <span class="ticket-catalog-ref">#<?= e($ticketVista['ticket_ref']) ?></span>
                <div>
                  <div class="ticket-catalog-client"><?= e($ticketVista['cliente_nombre'] ?: 'Cliente no registrado') ?></div>
                  <span class="ticket-catalog-meta"><?= e(implode(' · ', array_filter([$ticketVista['tipo_equipo'], $ticketVista['modalidad_comercial']]))) ?></span>
                </div>
                <span class="ticket-catalog-status <?= $reporteVista !== '' ? 'ok' : 'pending' ?>">
                  <?= $reporteVista !== '' ? 'Reporte '.$reporteVista : 'Sin reporte asignado' ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </details>
  </div>

  <div class="card soft" id="maquinasReeferCatalogoPanel">
    <div class="card-head-line"><div><span class="eyebrow">Catálogo independiente</span><div class="sect-title">❄️ Máquinas reefer</div><p class="help">Administra por separado los seriales, marcas, controladores y materiales de las máquinas reefer. No se mezclan con generadores.</p></div></div>
    <div class="catalog-grid">
      <form class="add-form catalog-box" method="post" id="serialReeferCatalogoForm">
        <input type="hidden" id="maquina_reefer_serial_id_edit" name="maquina_reefer_serial_id_edit" value="">
        <input type="hidden" name="guardar_maquina_reefer_serial" value="1">
        <div class="f"><label for="maquina_reefer_serial">N.º de serie de máquina reefer</label><input type="text" id="maquina_reefer_serial" name="maquina_reefer_serial" placeholder="Ej. E0GM030895" autocomplete="off" required></div>
        <div class="f"><label for="maquina_reefer_serial_marca">Marca reefer</label><select id="maquina_reefer_serial_marca" name="maquina_reefer_serial_marca" required><option value="">Seleccionar</option><option>THERMO KING</option><option>CARRIER</option><option>STAR COOL</option><option>DAIKIN</option><option>OTRO</option></select></div>
        <div class="f"><label for="maquina_reefer_serial_modelo">Modelo</label><input type="text" id="maquina_reefer_serial_modelo" name="maquina_reefer_serial_modelo" list="serialReeferModelosList" placeholder="Selecciona el modelo" autocomplete="off" required><datalist id="serialReeferModelosList"></datalist></div>
        <div class="f"><label for="maquina_reefer_serial_controlador">Controlador</label><input type="text" id="maquina_reefer_serial_controlador" name="maquina_reefer_serial_controlador" list="serialReeferControladoresList" placeholder="Selecciona el controlador" autocomplete="off" required><datalist id="serialReeferControladoresList"></datalist></div>
        <div class="f"><label for="maquina_reefer_serial_anio">Año de fabricación</label><input type="number" id="maquina_reefer_serial_anio" name="maquina_reefer_serial_anio" min="1980" max="<?= (int)date('Y') + 1 ?>" step="1" placeholder="Ej. 2022"></div>
        <div class="f"><label for="maquina_reefer_serial_refrigerante">Refrigerante</label><select id="maquina_reefer_serial_refrigerante" name="maquina_reefer_serial_refrigerante" required><option value="">Seleccionar</option><option value="R404A">R-404A</option><option value="R134A">R-134A</option><option value="OTRO">Otro</option></select></div>
        <div class="editing-hint" id="serialReeferEditingHint">Editando un número de serie de máquina reefer.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn" id="serialReeferSubmitBtn" type="submit">Guardar serie reefer</button><button class="btn muted" id="serialReeferCancelEditBtn" type="button" style="display:none">Cancelar edición</button></div>
      </form>
      <form class="add-form catalog-box" method="post" id="maquinaCatalogoForm">
        <input type="hidden" id="maquina_id_edit" name="modelo_reefer_id_edit" value=""><input type="hidden" name="guardar_modelo_reefer" value="1">
        <div class="f"><label for="maquina_marca">Marca reefer</label><select id="maquina_marca" name="modelo_reefer_marca" required><option value="">Seleccionar</option><option>THERMO KING</option><option>CARRIER</option><option>STAR COOL</option><option>DAIKIN</option><option>OTRO</option></select></div>
        <div class="f"><label for="maquina_controlador">Controlador</label><input type="text" id="maquina_controlador" name="modelo_reefer_controlador" placeholder="Ej. MP5000 / CIM6 / MICROLINK 3" required></div>
        <div class="editing-hint" id="maquinaEditingHint">Editando una combinación reefer.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn" id="maquinaSubmitBtn" type="submit">Guardar máquina reefer</button><button class="btn muted" id="maquinaCancelEditBtn" type="button" style="display:none">Cancelar edición</button></div>
      </form>
      <form class="add-form catalog-box" method="post" id="repuestoCatalogoForm">
        <input type="hidden" id="repuesto_id_edit" name="repuesto_reefer_id_edit" value="">
        <div class="f"><label for="repuesto_reefer_marca">Marca</label><select id="repuesto_reefer_marca" name="nuevo_repuesto_reefer_marca" required><option value="">Seleccionar</option><option>THERMO KING</option><option>CARRIER</option><option>STAR COOL</option><option>DAIKIN</option><option>OTRO</option></select></div>
        <div class="f"><label for="repuesto_reefer_controlador">Controlador</label><input type="text" id="repuesto_reefer_controlador" name="nuevo_repuesto_reefer_controlador" placeholder="Ej. MP5000 o TODOS" required></div>
        <div class="f"><label for="nuevo_repuesto_codigo">Código</label><input type="text" id="nuevo_repuesto_codigo" name="nuevo_repuesto_reefer_codigo" placeholder="Ej. 818770B"></div>
        <div class="f" style="flex:2"><label for="nuevo_repuesto_detalle">Material / repuesto</label><input type="text" id="nuevo_repuesto_detalle" name="nuevo_repuesto_reefer_detalle" required></div>
        <div class="f"><label for="nuevo_repuesto_unidad">Unidad</label><input type="text" id="nuevo_repuesto_unidad" name="nuevo_repuesto_reefer_unidad" value="und"></div>
        <div class="editing-hint" id="repuestoEditingHint">Editando material exclusivo de máquina reefer.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn" id="repuestoSubmitBtn" type="submit">Guardar material reefer</button><button class="btn muted" id="repuestoCancelEditBtn" type="button" style="display:none">Cancelar edición</button></div>
      </form>
    </div>
    <details class="catalog-fold"><summary><div class="catalog-fold-left"><span class="catalog-fold-icon">❄️</span><div><div class="catalog-fold-kicker">Modelos reefer</div><div class="catalog-fold-title">Marcas y controladores</div><div class="catalog-fold-sub">Crear, editar o eliminar combinaciones reefer.</div></div></div><div class="catalog-fold-right"><span class="catalog-fold-count"><?= count($maquinasCatalogoPanel) ?> modelo(s)</span><span class="catalog-fold-arrow">⌄</span></div></summary><div class="catalog-fold-body"><div class="catalog-list"><?php foreach($maquinasCatalogoPanel as $m):?><div class="catalog-row"><b><?= e($m['marca_equipo']) ?></b><span><?= e($m['controlador']) ?></span><div class="catalog-actions"><button type="button" class="mini-action" data-edit-maquina data-id="<?= (int)$m['id'] ?>" data-marca="<?= e($m['marca_equipo']) ?>" data-controlador="<?= e($m['controlador']) ?>">✏️ Editar</button><form method="post" class="inline-delete" onsubmit="return confirm('¿Eliminar esta combinación de máquina reefer?');"><input type="hidden" name="eliminar_modelo_reefer_id" value="<?= (int)$m['id'] ?>"><button class="mini-action danger" type="submit">🗑️ Eliminar</button></form></div></div><?php endforeach;?></div></div></details>
    <details class="catalog-fold"><summary><div class="catalog-fold-left"><span class="catalog-fold-icon">🔢</span><div><div class="catalog-fold-kicker">Series reefer</div><div class="catalog-fold-title">Números de serie registrados</div><div class="catalog-fold-sub">Seriales exclusivos de máquinas reefer, separados de los generadores.</div></div></div><div class="catalog-fold-right"><span class="catalog-fold-count"><?= count($serialesReeferCatalogoPanel) ?> serie(s)</span><span class="catalog-fold-arrow">⌄</span></div></summary><div class="catalog-fold-body"><div class="catalog-list"><?php foreach($serialesReeferCatalogoPanel as $sr):?><div class="catalog-row"><b><?= e($sr['serial_unidad']) ?></b><span><?= e($sr['marca_equipo'].' · '.$sr['modelo_equipo']) ?></span><em><?= e($sr['controlador'].' · '.($sr['anio_fabricacion']?:'Año no registrado').' · '.$sr['refrigerante']) ?></em><div class="catalog-actions"><button type="button" class="mini-action" data-edit-serial-reefer data-id="<?= (int)$sr['id'] ?>" data-serial="<?= e($sr['serial_unidad']) ?>" data-marca="<?= e($sr['marca_equipo']) ?>" data-modelo="<?= e($sr['modelo_equipo']) ?>" data-controlador="<?= e($sr['controlador']) ?>" data-anio="<?= e($sr['anio_fabricacion']) ?>" data-refrigerante="<?= e($sr['refrigerante']) ?>">✏️ Editar</button><form method="post" class="inline-delete" onsubmit="return confirm('¿Eliminar este número de serie de máquina reefer?');"><input type="hidden" name="eliminar_maquina_id" value="<?= (int)$sr['id'] ?>"><button class="mini-action danger" type="submit">🗑️ Eliminar</button></form></div></div><?php endforeach;?></div></div></details>
    <details class="catalog-fold"><summary><div class="catalog-fold-left"><span class="catalog-fold-icon">🧰</span><div><div class="catalog-fold-kicker">Materiales reefer</div><div class="catalog-fold-title">Repuestos por marca y controlador</div><div class="catalog-fold-sub">Catálogo editable separado de los materiales de generador.</div></div></div><div class="catalog-fold-right"><span class="catalog-fold-count"><?= count($repuestosCatalogoPanel) ?> material(es)</span><span class="catalog-fold-arrow">⌄</span></div></summary><div class="catalog-fold-body"><div class="catalog-list"><?php foreach($repuestosCatalogoPanel as $r):?><div class="catalog-row"><b><?= e($r['codigo']?:'Sin código') ?></b><span><?= e($r['detalle']) ?></span><em><?= e($r['marca_equipo'].' · '.$r['controlador'].' · '.($r['unidad']?:'Unidad automática')) ?></em><div class="catalog-actions"><button type="button" class="mini-action" data-edit-repuesto data-id="<?= (int)$r['id'] ?>" data-marca="<?= e($r['marca_equipo']) ?>" data-controlador="<?= e($r['controlador']) ?>" data-codigo="<?= e($r['codigo']) ?>" data-detalle="<?= e($r['detalle']) ?>" data-unidad="<?= e($r['unidad']) ?>">✏️ Editar</button><form method="post" class="inline-delete" onsubmit="return confirm('¿Eliminar este material reefer?');"><input type="hidden" name="eliminar_repuesto_reefer_id" value="<?= (int)$r['id'] ?>"><button class="mini-action danger" type="submit">🗑️ Eliminar</button></form></div></div><?php endforeach;?></div></div></details>
  </div>

  <div class="card soft" id="generadoresCatalogoPanel">
    <div class="card-head-line"><div><span class="eyebrow">Catálogo independiente</span><div class="sect-title">⚡ Generadores</div><p class="help">Registra los números y seriales de los generadores, además de administrar sus marcas, controladores y materiales.</p></div></div>
    <div class="catalog-grid">
      <form class="add-form catalog-box" method="post" id="generadorUnidadCatalogoForm">
        <input type="hidden" id="generador_unidad_id_edit" name="generador_unidad_id_edit" value=""><input type="hidden" name="guardar_generador_unidad" value="1">
        <div class="f"><label for="generador_unidad_numero">N.º de generador / equipo</label><input type="text" id="generador_unidad_numero" name="generador_unidad_numero" placeholder="Ej. GSET-001" required></div>
        <div class="f"><label for="generador_unidad_serial">Serial del generador</label><input type="text" id="generador_unidad_serial" name="generador_unidad_serial" placeholder="Ej. SG3K-000123" required></div>
        <div class="f"><label for="generador_unidad_marca">Marca</label><select id="generador_unidad_marca" name="generador_unidad_marca" required><option>THERMO KING</option></select></div>
        <div class="f"><label for="generador_unidad_controlador">Controlador</label><select id="generador_unidad_controlador" name="generador_unidad_controlador" required><option value="">Seleccionar</option><option>SG-3000</option><option>SG-5000</option></select></div>
        <div class="editing-hint" id="generadorUnidadEditingHint">Editando un generador registrado.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn" id="generadorUnidadSubmitBtn" type="submit">Guardar generador registrado</button><button class="btn muted" id="generadorUnidadCancelEditBtn" type="button" style="display:none">Cancelar edición</button></div>
      </form>
      <form class="add-form catalog-box" method="post" id="generadorCatalogoForm">
        <input type="hidden" id="generador_id_edit" name="modelo_genset_id_edit" value=""><input type="hidden" name="guardar_modelo_genset" value="1">
        <div class="f"><label for="generador_marca">Marca del generador</label><select id="generador_marca" name="modelo_genset_marca" required><option>THERMO KING</option></select></div>
        <div class="f"><label for="generador_controlador">Controlador</label><select id="generador_controlador" name="modelo_genset_controlador" required><option value="">Seleccionar</option><option>SG-3000</option><option>SG-5000</option></select></div>
        <div class="editing-hint" id="generadorEditingHint">Editando marca y controlador de generador.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn" id="generadorSubmitBtn" type="submit">Guardar generador</button><button class="btn muted" id="generadorCancelEditBtn" type="button" style="display:none">Cancelar edición</button></div>
      </form>
      <form class="add-form catalog-box" method="post" id="gensetRepuestoCatalogoForm">
        <input type="hidden" id="genset_repuesto_id_edit" name="genset_repuesto_id_edit" value="">
        <div class="f"><label for="nuevo_genset_repuesto_controlador">Controlador</label><select id="nuevo_genset_repuesto_controlador" name="nuevo_genset_repuesto_controlador" required><option>SG-3000</option><option>SG-5000</option></select></div>
        <div class="f"><label for="nuevo_genset_repuesto_codigo">Código</label><input type="text" id="nuevo_genset_repuesto_codigo" name="nuevo_genset_repuesto_codigo"></div>
        <div class="f" style="flex:2"><label for="nuevo_genset_repuesto_detalle">Material / repuesto</label><input type="text" id="nuevo_genset_repuesto_detalle" name="nuevo_genset_repuesto_detalle" required></div>
        <div class="f"><label for="nuevo_genset_repuesto_unidad">Unidad</label><input type="text" id="nuevo_genset_repuesto_unidad" name="nuevo_genset_repuesto_unidad" value="und"></div>
        <div class="editing-hint" id="gensetRepuestoEditingHint">Editando material exclusivo de generador.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn" id="gensetRepuestoSubmitBtn" type="submit">Guardar material SG</button><button class="btn muted" id="gensetRepuestoCancelEditBtn" type="button" style="display:none">Cancelar edición</button></div>
      </form>
    </div>
    <details class="catalog-fold" open><summary><div class="catalog-fold-left"><span class="catalog-fold-icon">⚡</span><div><div class="catalog-fold-kicker">Equipos registrados</div><div class="catalog-fold-title">Números y seriales de generadores</div><div class="catalog-fold-sub">Estos equipos aparecen como sugerencias en el formulario técnico.</div></div></div><div class="catalog-fold-right"><span class="catalog-fold-count"><?= count($generadoresUnidadesPanel) ?> generador(es)</span><span class="catalog-fold-arrow">⌄</span></div></summary><div class="catalog-fold-body"><div class="catalog-list"><?php foreach($generadoresUnidadesPanel as $g):?><div class="catalog-row"><b><?= e($g['numero']) ?></b><span><?= e($g['serial_unidad']) ?></span><em><?= e($g['marca_equipo'].' · '.$g['controlador']) ?></em><div class="catalog-actions"><button type="button" class="mini-action" data-edit-generador-unidad data-id="<?= (int)$g['id'] ?>" data-numero="<?= e($g['numero']) ?>" data-serial="<?= e($g['serial_unidad']) ?>" data-marca="<?= e($g['marca_equipo']) ?>" data-controlador="<?= e($g['controlador']) ?>">✏️ Editar</button><form method="post" class="inline-delete" onsubmit="return confirm('¿Eliminar este generador del catálogo?');"><input type="hidden" name="eliminar_generador_unidad_id" value="<?= (int)$g['id'] ?>"><button class="mini-action danger" type="submit">🗑️ Eliminar</button></form></div></div><?php endforeach;?><?php if(!$generadoresUnidadesPanel):?><div class="catalog-row"><span>Aún no hay generadores registrados.</span></div><?php endif;?></div></div></details>
    <details class="catalog-fold"><summary><div class="catalog-fold-left"><span class="catalog-fold-icon">⚡</span><div><div class="catalog-fold-kicker">Generadores</div><div class="catalog-fold-title">Marcas y controladores</div><div class="catalog-fold-sub">Separados de las máquinas reefer.</div></div></div><div class="catalog-fold-right"><span class="catalog-fold-count"><?= count($generadoresCatalogoPanel) ?> modelo(s)</span><span class="catalog-fold-arrow">⌄</span></div></summary><div class="catalog-fold-body"><div class="catalog-list"><?php foreach($generadoresCatalogoPanel as $g):?><div class="catalog-row"><b><?= e($g['marca_equipo']) ?></b><span><?= e($g['controlador']) ?></span><div class="catalog-actions"><button type="button" class="mini-action" data-edit-generador data-id="<?= (int)$g['id'] ?>" data-marca="<?= e($g['marca_equipo']) ?>" data-controlador="<?= e($g['controlador']) ?>">✏️ Editar</button><form method="post" class="inline-delete" onsubmit="return confirm('¿Eliminar esta combinación de generador?');"><input type="hidden" name="eliminar_modelo_genset_id" value="<?= (int)$g['id'] ?>"><button class="mini-action danger" type="submit">🗑️ Eliminar</button></form></div></div><?php endforeach;?></div></div></details>
    <details class="catalog-fold"><summary><div class="catalog-fold-left"><span class="catalog-fold-icon">🧰</span><div><div class="catalog-fold-kicker">Materiales SG</div><div class="catalog-fold-title">Repuestos de generadores</div><div class="catalog-fold-sub">Catálogo separado por controlador.</div></div></div><div class="catalog-fold-right"><span class="catalog-fold-count"><?= count($repuestosGensetCatalogoPanel) ?> material(es)</span><span class="catalog-fold-arrow">⌄</span></div></summary><div class="catalog-fold-body"><div class="catalog-list"><?php foreach($repuestosGensetCatalogoPanel as $r):?><div class="catalog-row"><b><?= e($r['codigo']?:'Sin código') ?></b><span><?= e($r['detalle']) ?></span><em><?= e($r['controlador'].' · '.$r['unidad']) ?></em><div class="catalog-actions"><button type="button" class="mini-action" data-edit-genset-repuesto data-id="<?= (int)$r['id'] ?>" data-controlador="<?= e($r['controlador']) ?>" data-codigo="<?= e($r['codigo']) ?>" data-detalle="<?= e($r['detalle']) ?>" data-unidad="<?= e($r['unidad']) ?>">✏️ Editar</button><form method="post" class="inline-delete" onsubmit="return confirm('¿Eliminar este material de generador?');"><input type="hidden" name="eliminar_genset_repuesto_id" value="<?= (int)$r['id'] ?>"><button class="mini-action danger" type="submit">🗑️ Eliminar</button></form></div></div><?php endforeach;?></div></div></details>
  </div>

  <div class="card soft" id="trabajosPanel">
    <div class="card-head-line">
      <div>
        <span class="eyebrow">Administración</span>
        <div class="sect-title">➕ Agregar técnico</div>
        <p class="help">Registra nuevos técnicos para que aparezcan en el formulario principal.</p>
      </div>
    </div>
    <form class="add-form" method="post">
      <div class="f">
        <label for="nuevo_tecnico">Nombre del técnico</label>
        <input type="text" id="nuevo_tecnico" name="nuevo_tecnico" placeholder="Nombre y apellido" autocomplete="off" required>
      </div>
      <button class="btn" type="submit">Agregar a la lista</button>
    </form>

    <div class="admin-divider"></div>
    <div class="card-head-line" style="margin-bottom:10px">
      <div>
        <div class="sect-title">🗑️ Eliminar técnico</div>
        <p class="help">Lo retira del formulario principal sin borrar informes ni preliminares ya guardados.</p>
      </div>
    </div>
    <form class="add-form" method="post" onsubmit="return confirmarEliminarTecnicoSelect();">
      <div class="f">
        <label for="eliminar_tecnico_id">Seleccionar técnico</label>
        <select id="eliminar_tecnico_id" name="eliminar_tecnico_id" required>
          <option value="">Elige un técnico...</option>
          <?php foreach ($tecnicos as $t): ?>
            <option value="<?= (int)$t['id'] ?>"><?= e($t['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn danger" type="submit">Eliminar técnico</button>
    </form>
    <div class="small-danger-note">Nota: es una eliminación segura. El técnico deja de aparecer en la lista, pero los PDFs antiguos no se pierden.</div>
  </div>

  <div class="card soft">
    <div class="card-head-line">
      <div>
        <span class="eyebrow">Catálogo técnico</span>
        <div class="sect-title">🛠️ Trabajos realizados</div>
        <p class="help">Agrega trabajos nuevos o busca uno existente para quitarlo del formulario.</p>
      </div>
    </div>

    <div class="work-manager">
      <div class="add-box">
        <form method="post" class="add-form">
          <div class="f">
            <label for="nuevo_trabajo">Agregar nuevo trabajo</label>
            <input type="text" id="nuevo_trabajo" name="nuevo_trabajo" placeholder="Ej. MANTENIMIENTO PREVENTIVO" required>
          </div>
          <button class="btn" type="submit">Agregar trabajo</button>
        </form>
      </div>

      <div class="search-box">
        <label for="trabajoAdminSearch">Buscar trabajo para quitar</label>
        <div class="search-wrap">
          <input type="text" id="trabajoAdminSearch" placeholder="Escribe y selecciona un trabajo…" autocomplete="off">
          <div id="trabajoAdminResults" class="smart-results"></div>
        </div>
        <form method="post" id="deleteTrabajoForm" onsubmit="return confirmarQuitarTrabajo();">
          <input type="hidden" id="desactivarTrabajoId" name="desactivar_trabajo_id" value="">
          <div id="selectedTrabajoCard" class="selected-work">
            <div class="sel-label">Trabajo seleccionado</div>
            <div class="sel-name" id="selectedTrabajoName">—</div>
            <button class="btn danger" id="deleteTrabajoBtn" type="submit" disabled>Quitar de la lista</button>
            <div class="sel-note">No borra informes ya guardados; solo evita que aparezca en el formulario principal.</div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head-line">
      <div>
        <span class="eyebrow">Búsqueda</span>
        <div class="sect-title">🔎 Buscar informes</div>
        <p class="help">Filtra por técnico, trabajo realizado y rango de fechas. El orden se aplica sobre el resultado filtrado.</p>
      </div>
    </div>
    <form class="filter-form" method="get">
      <div class="f">
        <label for="buscar_tecnico">Buscar por técnico</label>
        <input type="text" id="buscar_tecnico" name="tecnico" list="listaTecnicos" value="<?= e($filterTecnico) ?>" placeholder="Ej. Carlos Ruiz">
        <datalist id="listaTecnicos">
          <?php foreach ($tecnicos as $t): ?>
            <option value="<?= e($t['nombre']) ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
      <div class="f">
        <label for="buscar_trabajo">Buscar por trabajo realizado</label>
        <input type="text" id="buscar_trabajo" name="trabajo" list="listaTrabajos" value="<?= e($filterTrabajo) ?>" placeholder="Ej. ASISTENCIA TECNICA">
        <datalist id="listaTrabajos">
          <?php foreach ($trabajosOpciones as $w): ?>
            <option value="<?= e($w) ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
      <div class="f" style="min-width:160px">
        <label for="fecha_desde">Fecha desde</label>
        <input type="date" id="fecha_desde" name="fecha_desde" value="<?= e($filterDesde) ?>">
      </div>
      <div class="f" style="min-width:160px">
        <label for="fecha_hasta">Fecha hasta</label>
        <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?= e($filterHasta) ?>">
      </div>
      <div class="f" style="min-width:180px">
        <label for="orden_fecha">Ordenar por fecha</label>
        <select id="orden_fecha" name="orden_fecha">
          <option value="desc" <?= $filterOrden === 'desc' ? 'selected' : '' ?>>Más recientes primero</option>
          <option value="asc" <?= $filterOrden === 'asc' ? 'selected' : '' ?>>Más antiguos primero</option>
        </select>
      </div>
      <div class="filter-actions">
        <button class="btn" type="submit">Buscar</button>
        <?php if ($hayFiltros): ?>
          <a class="btn secondary" href="panel.php">Limpiar filtros</a>
        <?php endif; ?>
      </div>
    </form>
    <?php if ($hayFiltros): ?>
      <div class="filter-note">
        Mostrando <b><?= $totalMostrado ?></b> informe(s)
        <?php if ($filterTecnico !== ''): ?> del técnico <b><?= e($filterTecnico) ?></b><?php endif; ?>
        <?php if ($filterTrabajo !== ''): ?> con trabajo <b><?= e($filterTrabajo) ?></b><?php endif; ?>
        <?php if ($filterDesde !== ''): ?> desde <b><?= e(fechaBonita($filterDesde)) ?></b><?php endif; ?>
        <?php if ($filterHasta !== ''): ?> hasta <b><?= e(fechaBonita($filterHasta)) ?></b><?php endif; ?>.
        Orden: <b><?= $filterOrden === 'asc' ? 'más antiguos primero' : 'más recientes primero' ?></b>.
      </div>
    <?php endif; ?>
  </div>

  <details class="card fold-card" id="preliminaresPanel">
    <summary>
      <div class="fold-title">
        <div class="fold-icon">🟡</div>
        <div>
          <span class="fold-kicker">Preliminar</span>
          <div class="fold-main">Inspecciones preliminares registradas</div>
          <div class="fold-sub">Presiona aquí para ver cómo encontró el técnico la máquina antes de intervenirla.</div>
        </div>
      </div>
      <div class="fold-right">
        <span class="fold-count">Abiertos: <?= (int)$preliminaresAbiertas ?> · Total: <?= count($preliminares) ?></span>
        <span class="fold-arrow">⌄</span>
      </div>
    </summary>

    <div class="fold-body">
      <?php if (empty($preliminares)): ?>
        <p class="empty">Aún no hay inspecciones preliminares registradas.</p>
      <?php else: ?>
        <div class="filter-note" style="margin-bottom:12px">
          Servicios abiertos: <b><?= (int)$preliminaresAbiertas ?></b> · Total preliminares: <b><?= count($preliminares) ?></b>
        </div>
        <div class="bulk-toolbar">
          <div class="bulk-toolbar-left">
            <label class="bulk-check-label"><input type="checkbox" id="bulkPreSelectAll"> Seleccionar todas las preliminares visibles</label>
            <span class="bulk-selected-count" id="bulkPreCount">0 seleccionadas</span>
          </div>
          <div class="bulk-toolbar-right">
            <button type="button" class="bulk-delete-btn" id="bulkDeletePre" disabled
                    data-bulk-open="eliminar_preliminares"
                    data-title="Eliminar preliminares seleccionadas"
                    data-message="Se eliminarán las inspecciones preliminares seleccionadas. Los informes finales vinculados se conservarán, pero quedarán desvinculados de esas preliminares.">Eliminar seleccionadas</button>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th class="bulk-select-col"><span class="muted">✓</span></th>
                <th>Fecha preliminar</th>
                <th>Estado</th>
                <th>Técnico</th>
                <th>Cliente / reporte</th>
                <th>Equipo</th>
                <th>Datos registrados</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($preliminares as $p):
              $estadoPre = trim((string)($p['estado'] ?? 'abierto')) ?: 'abierto';
              $tokenPre = trim((string)($p['token_continuacion'] ?? ''));
            ?>
              <tr>
                <td class="bulk-select-col"><input type="checkbox" class="bulk-row-check bulk-pre-check" value="<?= (int)$p['id'] ?>" aria-label="Seleccionar preliminar <?= (int)$p['id'] ?>"></td>
                <td style="white-space:nowrap"><?= fechaHora($p['creado_en'] ?? '') ?></td>
                <td>
                  <span class="pre-status-badge <?= e(estadoPreliminarClass($estadoPre)) ?>"><?= e($estadoPre) ?></span>
                  <?php if ($estadoPre === 'abierto' && trim((string)($p['borrador_actualizado_en'] ?? '')) !== ''): ?>
                    <div style="margin-top:7px;font-size:11px;font-weight:850;color:#176b3a;white-space:nowrap">💾 Avance guardado</div>
                    <div class="muted" style="font-size:10px;white-space:nowrap"><?= fechaHora($p['borrador_actualizado_en'] ?? '') ?></div>
                  <?php endif; ?>
                </td>
                <td><?= valorCorto($p['tecnico_nombre'] ?? '') ?></td>
                <td>
                  <b><?= valorCorto($p['cliente'] ?? '') ?></b><br>
                  <span class="muted">Reporte:</span> <?= valorCorto($p['cotizacion'] ?? '') ?>
                </td>
                <td>
                  <span class="tag"><?= valorCorto($p['numero_equipo'] ?? '') ?></span><br>
                  <span class="muted">Tipo:</span> <?= valorCorto($p['tipo_equipo'] ?? '') ?><br>
                  <?php if (strcasecmp(trim((string)($p['tipo_equipo'] ?? '')), 'Genset') !== 0): ?>
                    <span class="muted">Tamaño:</span> <?= valorCorto($p['tamano_contenedor'] ?? '') ?><br>
                  <?php endif; ?>
                  <span class="muted">Serie:</span> <?= valorCorto($p['serie_unidad'] ?? '') ?>
                </td>
                <td>
                  <details class="pre-details">
                    <summary>👁️ Ver preliminar</summary>
                    <div class="pre-grid">
                      <div class="pre-item"><b>Trabajo previsto</b><span><?= valorCorto($p['trabajo'] ?? '') ?></span></div>
                      <div class="pre-item"><b>Tipo / tamaño</b><span><?= valorCorto($p['tipo_equipo'] ?? '') ?><?= strcasecmp(trim((string)($p['tipo_equipo'] ?? '')), 'Genset') !== 0 ? ' · ' . e($p['tamano_contenedor'] ?? '') : '' ?></span></div>
                      <div class="pre-item"><b>Estado inicial</b><span><?= valorCorto($p['estado_inicial'] ?? '') ?></span></div>
                      <div class="pre-item"><b>Alarma encontrada</b><span><?= valorCorto($p['alarma_encontrada'] ?? '') ?></span></div>
                      <div class="pre-item"><b>Marca / controlador</b><span><?= valorCorto($p['marca_equipo'] ?? '') ?> / <?= valorCorto($p['controlador'] ?? '') ?></span></div>
                      <div class="pre-item"><b>Refrigerante</b><span><?= valorCorto($p['refrigerante'] ?? '') ?></span></div>
                      <?php if (strcasecmp(trim((string)($p['tipo_equipo'] ?? '')), 'Genset') === 0): ?>
                        <div class="pre-item full"><b>Parámetros del genset</b><span>
                          Horómetro: <?= valorCorto($p['genset_horometro_inicial'] ?? '') ?> h ·
                          Batería: <?= valorCorto($p['genset_voltaje_bateria_inicial'] ?? '') ?> ·
                          Combustible: <?= valorCorto($p['genset_nivel_combustible_inicial'] ?? '') ?> ·
                          Aceite: <?= valorCorto($p['genset_nivel_aceite_inicial'] ?? '') ?> ·
                          Refrigerante motor: <?= valorCorto($p['genset_refrigerante_motor_inicial'] ?? '') ?> ·
                          Arranque: <?= valorCorto($p['genset_arranque_inicial'] ?? '') ?> ·
                          Frecuencia: <?= valorCorto($p['genset_frecuencia_inicial'] ?? '') ?> Hz ·
                          Presión aceite: <?= valorCorto($p['genset_presion_aceite_inicial'] ?? '') ?>
                        </span></div>
                      <?php else: ?>
                      <div class="pre-item full"><b>Temperaturas</b><span class="pre-temp"><span>Amb: <?= tempCorta($p['temperatura_ambiente'] ?? '') ?></span><span>Ret: <?= tempCorta($p['retorno_aire'] ?? '') ?></span><span>Sum: <?= tempCorta($p['suministro_aire'] ?? '') ?></span><span>Set: <?= tempCorta($p['set_point'] ?? '') ?></span></span></div>
                      <div class="pre-item full"><b>Presiones</b><span>Alta: <?= valorCorto($p['presion_alta'] ?? '') ?> · Baja: <?= valorCorto($p['presion_baja'] ?? '') ?></span></div>
                      <?php endif; ?>
                      <div class="pre-item full"><b>Voltajes</b><span>L1-L2: <?= valorCorto($p['voltaje_l1_l2'] ?? '') ?> · L2-L3: <?= valorCorto($p['voltaje_l2_l3'] ?? '') ?> · L1-L3: <?= valorCorto($p['voltaje_l1_l3'] ?? '') ?></span></div>
                      <div class="pre-item full"><b>Ubicación</b><span><?= valorCorto($p['ubicacion_texto'] ?? '') ?></span></div>
                      <div class="pre-item full"><b>Observación inicial</b><span><?= valorCorto($p['observacion_inicial'] ?? '') ?></span></div>
                    </div>
                  </details>
                </td>
                <td style="text-align:right">
                  <div class="report-actions" style="justify-content:flex-end">
                    <?php if ((int)($p['informe_id'] ?? 0) > 0): ?>
                      <a class="dl edit-report" href="index.php?modo=editar_informe&id=<?= (int)$p['informe_id'] ?>">✏ Editar todo</a>
                    <?php else: ?>
                      <a class="dl edit-report" href="index.php?modo=editar_preliminar&id=<?= (int)$p['id'] ?>">✏ Editar preliminar</a>
                    <?php endif; ?>
                    <?php if ($estadoPre === 'abierto' && $tokenPre !== ''): ?>
                      <form method="get" action="index.php" style="display:inline;margin:0">
                        <input type="hidden" name="token" value="<?= e($tokenPre) ?>">
                        <button type="submit" class="dl" style="font:inherit;cursor:pointer"><?= trim((string)($p['borrador_actualizado_en'] ?? '')) !== '' ? 'Continuar avance' : 'Continuar servicio' ?></button>
                      </form>
                    <?php else: ?>
                      <span class="muted">Cerrado</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </details>

  <details class="card fold-card" id="historialTecnicosPanel">
    <summary>
      <div class="fold-title">
        <div class="fold-icon">👷</div>
        <div>
          <span class="fold-kicker">Historial</span>
          <div class="fold-main">Técnicos e informes</div>
          <div class="fold-sub">Presiona aquí para ver la lista de técnicos. Luego selecciona un técnico para desplegar sus informes.</div>
        </div>
      </div>
      <div class="fold-right">
        <span class="fold-count"><?= count($tecnicosMostrar) ?> técnico(s) · <?= count($rows) ?> informe(s)</span>
        <span class="fold-arrow">⌄</span>
      </div>
    </summary>

    <div class="fold-body">
      <?php if (empty($tecnicos)): ?>
        <p class="empty">Aún no hay técnicos. Agrega uno con el formulario de arriba.</p>
      <?php elseif ($hayFiltros && empty($tecnicosMostrar)): ?>
        <div class="no-results">No se encontraron informes con esos filtros. Cambia el técnico, trabajo o rango de fechas.</div>
      <?php else: ?>
        <div class="bulk-toolbar">
          <div class="bulk-toolbar-left">
            <label class="bulk-check-label"><input type="checkbox" id="bulkReportSelectAll"> Seleccionar todos los informes visibles</label>
            <span class="bulk-selected-count" id="bulkReportCount">0 seleccionados</span>
          </div>
          <div class="bulk-toolbar-right">
            <button type="button" class="bulk-delete-btn" id="bulkDeleteReports" disabled
                    data-bulk-open="eliminar_informes"
                    data-title="Eliminar informes seleccionados"
                    data-message="Se eliminarán los informes finales seleccionados, sus PDF y las preliminares que dieron origen a esos informes.">Eliminar seleccionados</button>
          </div>
        </div>
      <?php foreach ($tecnicosMostrar as $t): $lista = $porTecnico[$t['id']] ?? []; $tecActivo = (int)($t['activo'] ?? 1) === 1; ?>
        <details class="tech-accordion">
          <summary>
            <div class="tech-summary-left">
              <div class="tec-avatar"><?= e(inicial($t['nombre'])) ?></div>
              <div class="tech-summary-text">
                <div class="tech-summary-name"><?= e($t['nombre']) ?><?php if (!$tecActivo): ?><span class="tag muted-tag">Eliminado</span><?php endif; ?></div>
                <div class="tech-summary-count"><?= count($lista) ?> informe(s)</div>
              </div>
            </div>
            <div class="tech-summary-action">
              <span>Ver informes</span>
              <span class="fold-arrow">⌄</span>
            </div>
          </summary>
          <div class="tech-body">
            <?php if (empty($lista)): ?>
              <p class="empty">Sin informes todavía.</p>
            <?php else: ?>
              <div class="tech-bulk-row">
                <label class="bulk-check-label">
                  <input type="checkbox" class="bulk-tech-select" data-tech="<?= (int)$t['id'] ?>">
                  Seleccionar todos los informes de <?= e($t['nombre']) ?>
                </label>
              </div>
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr><th class="bulk-select-col"><span class="muted">✓</span></th><th>Registrado</th><th>F. servicio</th><th>Reporte</th><th>Cliente</th><th>Equipo</th><th>Horario del servicio</th><th>Trabajos</th><th>Odoo</th><th></th></tr>
                  </thead>
                  <tbody>
                  <?php foreach ($lista as $inf): ?>
                    <tr>
                      <td class="bulk-select-col"><input type="checkbox" class="bulk-row-check bulk-report-check" data-tech="<?= (int)$t['id'] ?>" value="<?= (int)$inf['id'] ?>" aria-label="Seleccionar informe <?= (int)$inf['id'] ?>"></td>
                      <td style="white-space:nowrap"><?= fechaHora($inf['creado_en']) ?></td>
                      <td><?= fechaBonita($inf['fecha']) ?></td>
                      <td><?= $inf['orden'] !== '' ? e($inf['orden']) : '<span class="muted">—</span>' ?></td>
                      <td><?= $inf['cliente'] !== '' ? e($inf['cliente']) : '<span class="muted">—</span>' ?></td>
                      <td>
                        <span class="tag"><?= valorCorto($inf['tipo_equipo'] ?? '') ?></span>
                        <?php if (strcasecmp(trim((string)($inf['tipo_equipo'] ?? '')), 'Genset') !== 0 && trim((string)($inf['tamano_contenedor'] ?? '')) !== ''): ?>
                          <br><span class="muted"><?= e($inf['tamano_contenedor']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td class="service-time-cell">
                        <b>Inicio</b><span><?= horarioServicioPanel($inf['hora_inicio_servicio'] ?? '') ?></span>
                        <b>Fin</b><span><?= horarioServicioPanel($inf['hora_fin_servicio'] ?? '') ?></span>
                      </td>
                      <td>
                        <?php foreach (separarTrabajosInforme($inf['trabajos'] ?? '') as $w): ?>
                          <span class="tag"><?= e($w) ?></span>
                        <?php endforeach; ?>
                      </td>
                      <?php
                        $odooEstado = trim((string)($inf['odoo_estado'] ?? 'pendiente')) ?: 'pendiente';
                        $odooOk = $odooEstado === 'sincronizado';
                        $odooError = trim((string)($inf['odoo_error'] ?? ''));
                      ?>
                      <td class="odoo-cell" data-odoo-cell="<?= (int)$inf['id'] ?>">
                        <span class="odoo-badge <?= $odooOk ? 'ok' : ($odooEstado === 'enviando' ? 'sending' : 'pending') ?>"
                              title="<?= e($odooError) ?>">
                          <?= $odooOk ? '✓ Sincronizado' : ($odooEstado === 'enviando' ? '↻ Enviando' : '⚠ ' . e(str_replace('_', ' ', $odooEstado))) ?>
                        </span>
                        <?php if ($odooOk): ?>
                          <small>Ticket <?= e($inf['odoo_ticket_ref'] ?? $inf['orden']) ?><br>Adjunto #<?= (int)($inf['odoo_attachment_id'] ?? 0) ?></small>
                        <?php elseif ($odooError !== ''): ?>
                          <small class="odoo-error-text"><?= e($odooError) ?></small>
                        <?php endif; ?>
                        <button type="button" class="odoo-retry-btn"
                                data-odoo-retry="<?= (int)$inf['id'] ?>"
                                data-csrf="<?= e($PANEL_CSRF) ?>">
                          <?= $odooOk ? 'Actualizar en Odoo' : 'Enviar / reintentar' ?>
                        </button>
                      </td>
                      <td style="text-align:right">
                        <div class="report-actions">
                          <a class="dl edit-report" href="index.php?modo=editar_informe&id=<?= (int)$inf['id'] ?>">✏ Editar todo</a>
                          <a class="dl edit-time" href="index.php?modo=editar_informe&id=<?= (int)$inf['id'] ?>&editar_horario=1#finalControlCard">🕒 Editar horario</a>
                          <a class="dl" href="informes/<?= e($inf['archivo']) ?>" target="_blank">⬇ PDF</a>
                          <form method="post" class="inline-delete" onsubmit="return confirmarEliminarInforme(this);">
                            <input type="hidden" name="eliminar_informe_id" value="<?= (int)$inf['id'] ?>">
                            <input type="hidden" data-info="1" value="Reporte: <?= e($inf['orden'] ?: '—') ?> | Cliente: <?= e($inf['cliente'] ?: '—') ?>">
                            <button type="submit" class="dl danger">🗑 Eliminar</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </details>
      <?php endforeach; endif; ?>
    </div>
  </details>



  <details class="bulk-emergency-details" id="panelEmergencyOptions">
    <summary>⚙️ Opciones de emergencia</summary>
    <div class="bulk-emergency-content">
      <div>
        <b>Eliminar todos los datos de prueba</b>
        <p>Úsalo únicamente antes de iniciar el uso real. Elimina informes finales, preliminares y PDF, pero conserva técnicos, clientes, reportes, equipos, trabajos y materiales.</p>
      </div>
      <button type="button" class="bulk-danger-btn bulk-danger-btn-compact" data-bulk-open="eliminar_todo_pruebas"
              data-title="Eliminar todos los datos de prueba"
              data-message="Se eliminarán todos los informes finales, todas las inspecciones preliminares y sus PDF. Esta acción no se puede deshacer."
              data-require-text="ELIMINAR TODO">Eliminar datos de prueba</button>
    </div>
  </details>

  <form method="post" id="panelBulkForm" style="display:none">
    <input type="hidden" name="panel_bulk_action" id="panelBulkAction">
    <input type="hidden" name="panel_csrf" value="<?= e($PANEL_CSRF) ?>">
    <input type="hidden" name="confirmacion" id="panelBulkConfirmation">
    <div id="panelBulkIds"></div>
  </form>

  <div class="bulk-modal" id="panelBulkModal" role="dialog" aria-modal="true" aria-labelledby="panelBulkModalTitle">
    <div class="bulk-modal-box">
      <div class="bulk-modal-head">
        <div class="bulk-modal-icon">⚠️</div>
        <h3 id="panelBulkModalTitle">Confirmar eliminación</h3>
      </div>
      <div class="bulk-modal-body">
        <p id="panelBulkModalMessage"></p>
        <p style="margin-top:10px"><strong>La eliminación no se puede deshacer.</strong></p>
        <div class="bulk-confirm-wrap" id="panelBulkConfirmWrap">
          <label for="panelBulkConfirmInput">Escribe ELIMINAR TODO para continuar</label>
          <input type="text" id="panelBulkConfirmInput" autocomplete="off" placeholder="ELIMINAR TODO">
        </div>
      </div>
      <div class="bulk-modal-actions">
        <button type="button" class="bulk-cancel" id="panelBulkCancel">Cancelar</button>
        <button type="button" class="bulk-confirm" id="panelBulkConfirm">Sí, eliminar</button>
      </div>
    </div>
  </div>

</main>

<script>
const TRABAJOS_ADMIN = <?= json_encode($trabajosGestion, JSON_UNESCAPED_UNICODE) ?>;
const adminSearch = document.getElementById('trabajoAdminSearch');
const adminResults = document.getElementById('trabajoAdminResults');
const selectedCard = document.getElementById('selectedTrabajoCard');
const selectedName = document.getElementById('selectedTrabajoName');
const selectedId = document.getElementById('desactivarTrabajoId');
const deleteBtn = document.getElementById('deleteTrabajoBtn');

function normPanel(s){
  return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
}
function renderTrabajoResults(){
  const q = normPanel(adminSearch.value.trim());
  adminResults.innerHTML = '';
  const items = TRABAJOS_ADMIN.filter(t => !q || normPanel(t.nombre).includes(q)).slice(0, 10);
  if(!items.length){
    adminResults.innerHTML = '<div class="empty-result">No se encontró ese trabajo.</div>';
    adminResults.classList.add('show');
    return;
  }
  items.forEach(t => {
    const div = document.createElement('div');
    div.className = 'smart-item';
    div.innerHTML = '<strong></strong><small>Seleccionar</small>';
    div.querySelector('strong').textContent = t.nombre;
    div.onmousedown = (e) => { e.preventDefault(); seleccionarTrabajo(t); };
    adminResults.appendChild(div);
  });
  adminResults.classList.add('show');
}
function seleccionarTrabajo(t){
  adminSearch.value = t.nombre;
  selectedId.value = t.id;
  selectedName.textContent = t.nombre;
  selectedCard.classList.add('show');
  deleteBtn.disabled = false;
  adminResults.classList.remove('show');
}

function confirmarEliminarInforme(form) {
  return confirm('¿Eliminar este informe técnico?');
}

function confirmarQuitarTrabajo(){
  const id = selectedId.value;
  const name = selectedName.textContent || 'este trabajo';
  if(!id){ alert('Primero selecciona un trabajo.'); return false; }
  return confirm('¿Quitar "' + name + '" de la lista?\n\nNo se borrarán los informes ya guardados.');
}
adminSearch.addEventListener('focus', renderTrabajoResults);
adminSearch.addEventListener('input', () => {
  selectedId.value = '';
  selectedName.textContent = '—';
  deleteBtn.disabled = true;
  selectedCard.classList.remove('show');
  renderTrabajoResults();
});
adminSearch.addEventListener('blur', () => setTimeout(()=>adminResults.classList.remove('show'), 160));



function confirmarEliminarTecnicoSelect(){
  const sel = document.getElementById('eliminar_tecnico_id');
  if(!sel || !sel.value){ alert('Primero selecciona un técnico.'); return false; }
  const name = sel.options[sel.selectedIndex]?.text || 'este técnico';
  return confirm('¿Eliminar a "' + name + '" de la lista?\n\nNo se borrarán sus informes ni preliminares guardadas.');
}

document.querySelectorAll('[data-toggle-rows]').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.getAttribute('data-toggle-rows');
    const rows = Array.from(document.querySelectorAll(target));
    if(!rows.length) return;
    const open = rows.some(r => !r.classList.contains('show'));
    rows.forEach(r => r.classList.toggle('show', open));
    btn.textContent = open ? (btn.dataset.close || 'Ocultar') : (btn.dataset.open || 'Ver todo');
  });
});

// =========================================================================
//  ACCESO A NOTIFICACIONES POR TELEGRAM
//  - El switch NO redirige automáticamente.
//  - Primero muestra el botón “UNIRSE AL GRUPO”.
//  - El grupo se abre en otra pestaña para conservar el panel abierto.
// =========================================================================
const pushToggle = document.getElementById('pushToggle');
const pushDot = document.getElementById('pushDot');
const pushStatus = document.getElementById('pushStatus');
const pushText = document.getElementById('pushText');
const telegramJoinBtn = document.getElementById('telegramJoinBtn');
const telegramJoinModal = document.getElementById('telegramJoinModal');
const telegramModalJoinBtn = document.getElementById('telegramModalJoinBtn');
const telegramCancelBtn = document.getElementById('telegramCancelBtn');
const telegramGroupLink = <?= json_encode($TG_GROUP_INVITE_LINK_PANEL, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
const tgLocalKey = 'zgroup_telegram_notificaciones_activas';

function linkTelegramConfigurado() {
  return telegramGroupLink &&
    !telegramGroupLink.includes('PEGA_AQUI') &&
    /^(https?:\/\/|tg:\/\/)/i.test(telegramGroupLink.trim());
}

function prepararBotonesTelegram() {
  if(!linkTelegramConfigurado()) return false;
  const link = telegramGroupLink.trim();
  if(telegramJoinBtn) telegramJoinBtn.href = link;
  if(telegramModalJoinBtn) telegramModalJoinBtn.href = link;
  return true;
}

function mostrarModalTelegram() {
  if(!linkTelegramConfigurado()) {
    alert('Falta configurar el enlace del grupo. En Telegram abre el grupo Supervisores ZGROUP, copia el enlace de invitación y pégalo en TG_GROUP_INVITE_LINK dentro de telegram_config.php.');
    return false;
  }
  prepararBotonesTelegram();
  if(telegramJoinModal) {
    telegramJoinModal.classList.add('show');
    telegramJoinModal.setAttribute('aria-hidden', 'false');
  }
  return true;
}

function cerrarModalTelegram() {
  if(telegramJoinModal) {
    telegramJoinModal.classList.remove('show');
    telegramJoinModal.setAttribute('aria-hidden', 'true');
  }
}

function setTelegramUi(on) {
  if(pushToggle) {
    pushToggle.classList.toggle('on', !!on);
    pushToggle.setAttribute('aria-checked', on ? 'true' : 'false');
  }
  if(pushDot) pushDot.classList.toggle('on', !!on);
  if(pushStatus) pushStatus.textContent = on ? 'Activado' : 'Desactivado';

  if(telegramJoinBtn) {
    telegramJoinBtn.style.display = on && linkTelegramConfigurado() ? 'inline-flex' : 'none';
    if(linkTelegramConfigurado()) telegramJoinBtn.href = telegramGroupLink.trim();
  }

  if(pushText) {
    if(on && linkTelegramConfigurado()) {
      pushText.textContent = 'Notificaciones activadas. Presiona “Unirse al grupo” para abrir Telegram en otra pestaña y recibir las alertas.';
    } else if(on && !linkTelegramConfigurado()) {
      pushText.textContent = 'Falta pegar el enlace del grupo en TG_GROUP_INVITE_LINK dentro de telegram_config.php.';
    } else {
      pushText.textContent = 'Activa el switch y luego presiona “Unirse al grupo” para abrir Telegram en otra pestaña.';
    }
  }
}

function activarTelegram() {
  const estabaActivo = localStorage.getItem(tgLocalKey) === '1';
  const nuevoEstado = !estabaActivo;

  if(nuevoEstado) {
    if(!linkTelegramConfigurado()) {
      mostrarModalTelegram();
      return;
    }
    localStorage.setItem(tgLocalKey, '1');
    setTelegramUi(true);
    mostrarModalTelegram();
  } else {
    localStorage.setItem(tgLocalKey, '0');
    setTelegramUi(false);
    cerrarModalTelegram();
  }
}

if(pushToggle) pushToggle.addEventListener('click', activarTelegram);

if(telegramJoinBtn) telegramJoinBtn.addEventListener('click', () => {
  localStorage.setItem(tgLocalKey, '1');
  setTelegramUi(true);
});

if(telegramModalJoinBtn) telegramModalJoinBtn.addEventListener('click', () => {
  localStorage.setItem(tgLocalKey, '1');
  setTelegramUi(true);
  // Se cierra el modal, pero el panel queda abierto porque el enlace usa target="_blank".
  setTimeout(cerrarModalTelegram, 250);
});

if(telegramCancelBtn) telegramCancelBtn.addEventListener('click', cerrarModalTelegram);

if(telegramJoinModal) telegramJoinModal.addEventListener('click', (e) => {
  if(e.target === telegramJoinModal) cerrarModalTelegram();
});

window.addEventListener('load', () => {
  prepararBotonesTelegram();
  setTelegramUi(localStorage.getItem(tgLocalKey) === '1');
});



document.querySelectorAll('[data-jump]').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.getAttribute('data-jump');
    const el = document.getElementById(id);
    if(!el) return;
    if(el.tagName && el.tagName.toLowerCase() === 'details') el.open = true;
    el.scrollIntoView({behavior:'smooth', block:'start'});
  });
});

(function setupClienteCatalogoPanel(){
  const form = document.getElementById('clienteCatalogoForm');
  const idEdit = document.getElementById('cliente_id_edit');
  const nombre = document.getElementById('nuevo_cliente_catalogo');
  const submit = document.getElementById('clienteSubmitBtn');
  const cancel = document.getElementById('clienteCancelEditBtn');
  const hint = document.getElementById('clienteEditingHint');

  function limpiarEdicionCliente(){
    if(idEdit) idEdit.value = '';
    if(nombre) nombre.value = '';
    if(submit) submit.textContent = 'Guardar cliente';
    if(cancel) cancel.style.display = 'none';
    if(hint) hint.classList.remove('on');
  }

  document.querySelectorAll('[data-edit-cliente]').forEach(btn => {
    btn.addEventListener('click', () => {
      if(idEdit) idEdit.value = btn.dataset.id || '';
      if(nombre) nombre.value = btn.dataset.nombre || '';
      if(submit) submit.textContent = 'Actualizar cliente';
      if(cancel) cancel.style.display = 'inline-flex';
      if(hint) hint.classList.add('on');
      if(form) form.scrollIntoView({behavior:'smooth', block:'center'});
      if(nombre) nombre.focus();
    });
  });

  if(cancel) cancel.addEventListener('click', limpiarEdicionCliente);
})();

(function setupCatalogoPanel(){
  const form = document.getElementById('cotizacionCatalogoForm');
  const idEdit = document.getElementById('cotizacion_id_edit');
  const reporte = document.getElementById('nueva_cotizacion_catalogo');
  const ticket = document.getElementById('odoo_servicio_id_reporte');
  const submit = document.getElementById('cotizacionSubmitBtn');
  const cancel = document.getElementById('cotizacionCancelEditBtn');
  const hint = document.getElementById('cotizacionEditingHint');

  if(reporte){
    reporte.addEventListener('input', () => {
      const limpio = reporte.value.replace(/\D+/g,'').slice(0,15);
      if(reporte.value !== limpio) reporte.value = limpio;
    });
  }

  function aplicarTicketSeleccionado(){
    if(!ticket || !reporte || !idEdit)return;
    const option = ticket.options[ticket.selectedIndex];
    if(!option || !option.value)return;

    const reporteActual = option.dataset.report || '';
    const catalogId = option.dataset.catalogId || '';

    if(!idEdit.value){
      reporte.value = reporteActual;
      idEdit.value = catalogId;
      if(submit)submit.textContent = reporteActual ? 'Actualizar reporte' : 'Guardar reporte';
      if(cancel)cancel.style.display = reporteActual ? 'inline-flex' : 'none';
      if(hint)hint.classList.toggle('on', Boolean(reporteActual));
    }
  }

  function limpiarEdicion(){
    if(idEdit) idEdit.value = '';
    if(reporte) reporte.value = '';
    if(ticket) ticket.value = '';
    if(submit) submit.textContent = 'Guardar reporte';
    if(cancel) cancel.style.display = 'none';
    if(hint) hint.classList.remove('on');
  }

  if(ticket)ticket.addEventListener('change', aplicarTicketSeleccionado);

  document.querySelectorAll('[data-edit-cotizacion]').forEach(btn => {
    btn.addEventListener('click', () => {
      if(idEdit) idEdit.value = btn.dataset.id || '';
      if(reporte) reporte.value = btn.dataset.cotizacion || '';

      if(ticket){
        const ref = String(btn.dataset.ticketRef || '');
        const opt = Array.from(ticket.options).find(o => String(o.dataset.ticketRef || '') === ref);
        ticket.value = opt ? opt.value : '';
      }

      if(submit) submit.textContent = 'Actualizar reporte';
      if(cancel) cancel.style.display = 'inline-flex';
      if(hint) hint.classList.add('on');
      if(form) form.scrollIntoView({behavior:'smooth', block:'center'});
      if(reporte) reporte.focus();
    });
  });

  if(cancel) cancel.addEventListener('click', limpiarEdicion);
})();


(function setupContenedorCatalogoPanel(){
  const form = document.getElementById('contenedorCatalogoForm');
  const idEdit = document.getElementById('contenedor_id_edit');
  const numero = document.getElementById('nuevo_contenedor_catalogo');
  const desc = document.getElementById('contenedor_descripcion');
  const submit = document.getElementById('contenedorSubmitBtn');
  const cancel = document.getElementById('contenedorCancelEditBtn');
  const hint = document.getElementById('contenedorEditingHint');

  function limpiar(txt){ return String(txt || '').toUpperCase().replace(/[^A-Z0-9\-_.\/]/g,'').slice(0,60); }
  if(numero){ numero.addEventListener('input', () => { const limpio = limpiar(numero.value); if(numero.value !== limpio) numero.value = limpio; }); }

  function limpiarEdicion(){
    if(idEdit) idEdit.value = '';
    if(numero) numero.value = '';
    if(desc) desc.value = '';
    if(submit) submit.textContent = 'Guardar contenedor';
    if(cancel) cancel.style.display = 'none';
    if(hint) hint.classList.remove('on');
  }

  document.querySelectorAll('[data-edit-contenedor]').forEach(btn => {
    btn.addEventListener('click', () => {
      if(idEdit) idEdit.value = btn.dataset.id || '';
      if(numero) numero.value = btn.dataset.numero || '';
      if(desc) desc.value = btn.dataset.descripcion || '';
      if(submit) submit.textContent = 'Actualizar contenedor';
      if(cancel) cancel.style.display = 'inline-flex';
      if(hint) hint.classList.add('on');
      if(form) form.scrollIntoView({behavior:'smooth', block:'center'});
      if(numero) numero.focus();
    });
  });
  if(cancel) cancel.addEventListener('click', limpiarEdicion);
})();

(function setupGeneradoresRegistradosV19(){
  const form=document.getElementById('generadorUnidadCatalogoForm');
  const id=document.getElementById('generador_unidad_id_edit');
  const numero=document.getElementById('generador_unidad_numero');
  const serial=document.getElementById('generador_unidad_serial');
  const marca=document.getElementById('generador_unidad_marca');
  const controlador=document.getElementById('generador_unidad_controlador');
  const submit=document.getElementById('generadorUnidadSubmitBtn');
  const cancel=document.getElementById('generadorUnidadCancelEditBtn');
  const hint=document.getElementById('generadorUnidadEditingHint');
  function limpiarCodigo(v,max){return String(v||'').toUpperCase().replace(/[^A-Z0-9\-_.\/]/g,'').slice(0,max);}
  [numero,serial].forEach(function(x){if(x)x.addEventListener('input',function(){const v=limpiarCodigo(x.value,x===numero?60:100);if(x.value!==v)x.value=v;});});
  function reset(){if(id)id.value='';if(numero)numero.value='';if(serial)serial.value='';if(marca)marca.value='THERMO KING';if(controlador)controlador.value='';if(submit)submit.textContent='Guardar generador registrado';if(cancel)cancel.style.display='none';if(hint)hint.classList.remove('on');}
  document.querySelectorAll('[data-edit-generador-unidad]').forEach(function(b){b.addEventListener('click',function(){if(id)id.value=b.dataset.id||'';if(numero)numero.value=b.dataset.numero||'';if(serial)serial.value=b.dataset.serial||'';if(marca)marca.value=b.dataset.marca||'THERMO KING';if(controlador)controlador.value=b.dataset.controlador||'';if(submit)submit.textContent='Actualizar generador';if(cancel)cancel.style.display='inline-flex';if(hint)hint.classList.add('on');if(form)form.scrollIntoView({behavior:'smooth',block:'center'});if(numero)numero.focus();});});
  if(cancel)cancel.addEventListener('click',reset);
})();

(function setupSerialesReeferV51(){
  const form=document.getElementById('serialReeferCatalogoForm');
  const id=document.getElementById('maquina_reefer_serial_id_edit');
  const serial=document.getElementById('maquina_reefer_serial');
  const marca=document.getElementById('maquina_reefer_serial_marca');
  const modelo=document.getElementById('maquina_reefer_serial_modelo');
  const modeloList=document.getElementById('serialReeferModelosList');
  const controlador=document.getElementById('maquina_reefer_serial_controlador');
  const controladorList=document.getElementById('serialReeferControladoresList');
  const anio=document.getElementById('maquina_reefer_serial_anio');
  const refrigerante=document.getElementById('maquina_reefer_serial_refrigerante');
  const submit=document.getElementById('serialReeferSubmitBtn');
  const cancel=document.getElementById('serialReeferCancelEditBtn');
  const hint=document.getElementById('serialReeferEditingHint');

  const CONFIG={
    'THERMO KING':{modelos:['MAGNUM PLUS'],controladores:['MP3000','MP4000','MP5000'],refrigerante:'R404A'},
    'CARRIER':{modelos:['MICROLINK 2','MICROLINK 3'],controladores:['MICROLINK 2','MICROLINK 3'],refrigerante:'R134A'},
    'STAR COOL':{modelos:['CIM 5','CIM 6'],controladores:['CIM5','CIM6'],refrigerante:'R134A'},
    'DAIKIN':{modelos:['DAIKIN'],controladores:['DAIKIN'],refrigerante:'R134A'},
    'OTRO':{modelos:[],controladores:[],refrigerante:''}
  };
  function limpiarCodigo(v){return String(v||'').toUpperCase().replace(/[^A-Z0-9\-_.\/]/g,'').slice(0,100);}
  if(serial) serial.addEventListener('input',function(){const v=limpiarCodigo(serial.value);if(serial.value!==v)serial.value=v;});

  function fillList(list,values){
    if(!list)return;list.innerHTML='';
    (values||[]).forEach(function(v){const o=document.createElement('option');o.value=v;list.appendChild(o);});
  }
  function syncMarca(preserve){
    const cfg=CONFIG[(marca&&marca.value)||'']||CONFIG.OTRO;
    fillList(modeloList,cfg.modelos);fillList(controladorList,cfg.controladores);
    if(refrigerante)refrigerante.value=cfg.refrigerante||refrigerante.value||'';
    if(!preserve){
      if(modelo)modelo.value=cfg.modelos.length===1?cfg.modelos[0]:'';
      if(controlador)controlador.value=cfg.controladores.length===1?cfg.controladores[0]:'';
    }else{
      if(modelo&&cfg.modelos.length===1&&!modelo.value)modelo.value=cfg.modelos[0];
      if(controlador&&cfg.controladores.length===1&&!controlador.value)controlador.value=cfg.controladores[0];
    }
    if(modelo)modelo.placeholder=cfg.modelos.length?'Opciones: '+cfg.modelos.join(' / '):'Escribe el modelo';
    if(controlador)controlador.placeholder=cfg.controladores.length?'Opciones: '+cfg.controladores.join(' / '):'Escribe el controlador';
  }
  if(marca)marca.addEventListener('change',function(){syncMarca(false);});
  if(modelo)modelo.addEventListener('change',function(){
    const m=(marca&&marca.value)||'';
    if(m==='CARRIER'&&['MICROLINK 2','MICROLINK 3'].includes(modelo.value)&&controlador)controlador.value=modelo.value;
    if(m==='STAR COOL'&&['CIM 5','CIM 6'].includes(modelo.value)&&controlador)controlador.value=modelo.value.replace(' ','');
  });

  function reset(){
    if(id)id.value='';
    if(serial)serial.value='';
    if(marca)marca.value='';
    if(modelo)modelo.value='';
    if(controlador)controlador.value='';
    if(anio)anio.value='';
    if(refrigerante)refrigerante.value='';
    fillList(modeloList,[]);fillList(controladorList,[]);
    if(submit)submit.textContent='Guardar serie reefer';
    if(cancel)cancel.style.display='none';
    if(hint)hint.classList.remove('on');
  }

  document.querySelectorAll('[data-edit-serial-reefer]').forEach(function(btn){
    btn.addEventListener('click',function(){
      if(id)id.value=btn.dataset.id||'';
      if(serial)serial.value=btn.dataset.serial||'';
      if(marca)marca.value=btn.dataset.marca||'';
      syncMarca(true);
      if(modelo)modelo.value=btn.dataset.modelo||'';
      if(controlador)controlador.value=btn.dataset.controlador||'';
      if(anio)anio.value=btn.dataset.anio||'';
      if(refrigerante)refrigerante.value=(btn.dataset.refrigerante||'').toUpperCase();
      if(submit)submit.textContent='Actualizar serie reefer';
      if(cancel)cancel.style.display='inline-flex';
      if(hint)hint.classList.add('on');
      if(form)form.scrollIntoView({behavior:'smooth',block:'center'});
      if(serial)serial.focus();
    });
  });

  if(cancel)cancel.addEventListener('click',reset);
  syncMarca(true);
})();

(function setupCatalogosEquiposV12(){
  const clean=v=>String(v||'').trim();
  function bindModel(formId,idId,brandId,ctrlId,buttonId,cancelId,hintId,selector,defaultBrand,defaultButton){
    const form=document.getElementById(formId),id=document.getElementById(idId),brand=document.getElementById(brandId),ctrl=document.getElementById(ctrlId),btn=document.getElementById(buttonId),cancel=document.getElementById(cancelId),hint=document.getElementById(hintId);
    function clear(){if(id)id.value='';if(brand)brand.value=defaultBrand||'';if(ctrl)ctrl.value='';if(btn)btn.textContent=defaultButton;if(cancel)cancel.style.display='none';if(hint)hint.classList.remove('on');}
    document.querySelectorAll(selector).forEach(b=>b.addEventListener('click',()=>{if(id)id.value=b.dataset.id||'';if(brand)brand.value=b.dataset.marca||defaultBrand||'';if(ctrl)ctrl.value=b.dataset.controlador||'';if(btn)btn.textContent='Actualizar';if(cancel)cancel.style.display='inline-flex';if(hint)hint.classList.add('on');form&&form.scrollIntoView({behavior:'smooth',block:'center'});ctrl&&ctrl.focus();}));
    cancel&&cancel.addEventListener('click',clear);
  }
  bindModel('maquinaCatalogoForm','maquina_id_edit','maquina_marca','maquina_controlador','maquinaSubmitBtn','maquinaCancelEditBtn','maquinaEditingHint','[data-edit-maquina]','','Guardar máquina reefer');
  bindModel('generadorCatalogoForm','generador_id_edit','generador_marca','generador_controlador','generadorSubmitBtn','generadorCancelEditBtn','generadorEditingHint','[data-edit-generador]','THERMO KING','Guardar generador');

  const rid=document.getElementById('repuesto_id_edit'),rm=document.getElementById('repuesto_reefer_marca'),rc=document.getElementById('repuesto_reefer_controlador'),rk=document.getElementById('nuevo_repuesto_codigo'),rd=document.getElementById('nuevo_repuesto_detalle'),ru=document.getElementById('nuevo_repuesto_unidad'),rb=document.getElementById('repuestoSubmitBtn'),rx=document.getElementById('repuestoCancelEditBtn'),rh=document.getElementById('repuestoEditingHint'),rf=document.getElementById('repuestoCatalogoForm');
  function clearR(){if(rid)rid.value='';if(rm)rm.value='';if(rc)rc.value='';if(rk)rk.value='';if(rd)rd.value='';if(ru)ru.value='und';if(rb)rb.textContent='Guardar material reefer';if(rx)rx.style.display='none';if(rh)rh.classList.remove('on');}
  document.querySelectorAll('[data-edit-repuesto]').forEach(b=>b.addEventListener('click',()=>{rid.value=b.dataset.id||'';rm.value=b.dataset.marca||'';rc.value=b.dataset.controlador||'';rk.value=b.dataset.codigo||'';rd.value=b.dataset.detalle||'';ru.value=b.dataset.unidad||'und';rb.textContent='Actualizar material reefer';rx.style.display='inline-flex';rh.classList.add('on');rf&&rf.scrollIntoView({behavior:'smooth',block:'center'});rd&&rd.focus();}));rx&&rx.addEventListener('click',clearR);
})();

</script>

<script id="zg-panel-bulk-v9-js">
(function(){
  'use strict';
  const q = (s, root=document) => root.querySelector(s);
  const qa = (s, root=document) => Array.from(root.querySelectorAll(s));
  const preAll = q('#bulkPreSelectAll');
  const reportAll = q('#bulkReportSelectAll');
  const preCount = q('#bulkPreCount');
  const reportCount = q('#bulkReportCount');
  const delPre = q('#bulkDeletePre');
  const delReports = q('#bulkDeleteReports');
  const modal = q('#panelBulkModal');
  const modalTitle = q('#panelBulkModalTitle');
  const modalMessage = q('#panelBulkModalMessage');
  const confirmWrap = q('#panelBulkConfirmWrap');
  const confirmInput = q('#panelBulkConfirmInput');
  const confirmBtn = q('#panelBulkConfirm');
  const cancelBtn = q('#panelBulkCancel');
  const form = q('#panelBulkForm');
  const actionInput = q('#panelBulkAction');
  const confirmationInput = q('#panelBulkConfirmation');
  const idsBox = q('#panelBulkIds');
  let pending = null;

  function selected(selector){
    return qa(selector + ':checked').map(x => x.value).filter(Boolean);
  }
  function setIndeterminate(master, boxes){
    if(!master) return;
    const checked = boxes.filter(x => x.checked).length;
    master.checked = boxes.length > 0 && checked === boxes.length;
    master.indeterminate = checked > 0 && checked < boxes.length;
  }
  function update(){
    const preBoxes = qa('.bulk-pre-check');
    const reportBoxes = qa('.bulk-report-check');
    const nPre = preBoxes.filter(x=>x.checked).length;
    const nReports = reportBoxes.filter(x=>x.checked).length;
    if(preCount) preCount.textContent = nPre + (nPre === 1 ? ' seleccionada' : ' seleccionadas');
    if(reportCount) reportCount.textContent = nReports + (nReports === 1 ? ' seleccionado' : ' seleccionados');
    if(delPre) delPre.disabled = nPre === 0;
    if(delReports) delReports.disabled = nReports === 0;
    setIndeterminate(preAll, preBoxes);
    setIndeterminate(reportAll, reportBoxes);

    qa('.bulk-tech-select').forEach(master => {
      const boxes = qa('.bulk-report-check[data-tech="'+master.dataset.tech+'"]');
      setIndeterminate(master, boxes);
    });
  }

  if(preAll) preAll.addEventListener('change', () => {
    qa('.bulk-pre-check').forEach(x => x.checked = preAll.checked);
    update();
  });
  if(reportAll) reportAll.addEventListener('change', () => {
    qa('.bulk-report-check').forEach(x => x.checked = reportAll.checked);
    update();
  });
  qa('.bulk-tech-select').forEach(master => {
    master.addEventListener('change', () => {
      qa('.bulk-report-check[data-tech="'+master.dataset.tech+'"]').forEach(x => x.checked = master.checked);
      update();
    });
  });
  document.addEventListener('change', e => {
    if(e.target.matches('.bulk-pre-check,.bulk-report-check')) update();
  });

  function openModal(btn){
    const action = btn.dataset.bulkOpen || '';
    let ids = [];
    if(action === 'eliminar_preliminares') ids = selected('.bulk-pre-check');
    if(action === 'eliminar_informes') ids = selected('.bulk-report-check');
    if((action === 'eliminar_preliminares' || action === 'eliminar_informes') && !ids.length){
      alert('Selecciona al menos un registro.');
      return;
    }

    pending = {
      action,
      ids,
      requireText: btn.dataset.requireText || '',
    };
    modalTitle.textContent = btn.dataset.title || 'Confirmar eliminación';
    modalMessage.textContent = btn.dataset.message || '¿Deseas continuar con la eliminación?';
    confirmInput.value = '';
    confirmationInput.value = '';
    confirmWrap.classList.toggle('show', !!pending.requireText);
    confirmBtn.disabled = !!pending.requireText;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    setTimeout(() => pending.requireText ? confirmInput.focus() : confirmBtn.focus(), 30);
  }

  qa('[data-bulk-open]').forEach(btn => btn.addEventListener('click', () => openModal(btn)));

  function closeModal(){
    modal.classList.remove('show');
    document.body.style.overflow = '';
    pending = null;
  }
  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', e => { if(e.target === modal) closeModal(); });
  document.addEventListener('keydown', e => { if(e.key === 'Escape' && modal.classList.contains('show')) closeModal(); });

  confirmInput.addEventListener('input', () => {
    if(!pending) return;
    const ok = confirmInput.value.trim().toUpperCase() === pending.requireText.toUpperCase();
    confirmBtn.disabled = !ok;
  });

  confirmBtn.addEventListener('click', () => {
    if(!pending || confirmBtn.disabled) return;
    actionInput.value = pending.action;
    confirmationInput.value = pending.requireText ? confirmInput.value.trim().toUpperCase() : '';
    idsBox.innerHTML = '';
    pending.ids.forEach(id => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = id;
      idsBox.appendChild(input);
    });
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Eliminando…';
    form.submit();
  });

  update();
})();
</script>

</body>
</html>

<style id="zg-panel-emergency-v10-style">
.bulk-emergency-details{margin:30px 0 8px;border-top:1px solid #d8e2ed;padding-top:14px;color:#718096}
.bulk-emergency-details summary{width:max-content;cursor:pointer;font-size:12px;font-weight:800;color:#718096;list-style:none;user-select:none}
.bulk-emergency-details summary::-webkit-details-marker{display:none}
.bulk-emergency-details summary:hover{color:#334e68}
.bulk-emergency-content{margin-top:10px;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 14px;background:#f8fafc;border:1px solid #e3e9f0;border-radius:14px}
.bulk-emergency-content b{display:block;color:#526275;font-size:13px;margin-bottom:3px}.bulk-emergency-content p{font-size:11.5px;color:#8290a3;font-weight:650;max-width:720px}
.bulk-danger-btn-compact{background:#fff!important;color:#b42318!important;border:1px solid #efb5b0!important;box-shadow:none!important;padding:9px 12px!important;font-size:12px!important;white-space:nowrap}
@media(max-width:700px){.bulk-emergency-content{align-items:flex-start;flex-direction:column}.bulk-danger-btn-compact{width:100%}}
</style>


<style id="zg-panel-generadores-v11-style">
#generadoresCatalogoPanel{border:1.5px solid #bfd9f4;background:linear-gradient(180deg,#fff,#f5faff)}
#generadoresCatalogoPanel .eyebrow{background:#e5f1ff;color:#155293}
</style>
<script id="zg-panel-generadores-v12-js">
(function(){
  const rid=document.getElementById('genset_repuesto_id_edit'),rc=document.getElementById('nuevo_genset_repuesto_controlador'),rk=document.getElementById('nuevo_genset_repuesto_codigo'),rd=document.getElementById('nuevo_genset_repuesto_detalle'),ru=document.getElementById('nuevo_genset_repuesto_unidad'),rb=document.getElementById('gensetRepuestoSubmitBtn'),rx=document.getElementById('gensetRepuestoCancelEditBtn'),rh=document.getElementById('gensetRepuestoEditingHint'),rf=document.getElementById('gensetRepuestoCatalogoForm');
  function clearR(){if(rid)rid.value='';if(rc)rc.value='SG-3000';if(rk)rk.value='';if(rd)rd.value='';if(ru)ru.value='und';if(rb)rb.textContent='Guardar material SG';if(rx)rx.style.display='none';if(rh)rh.classList.remove('on');}
  document.querySelectorAll('[data-edit-genset-repuesto]').forEach(b=>b.addEventListener('click',()=>{rid.value=b.dataset.id||'';rc.value=String(b.dataset.controlador||'SG-3000').replace(/^ZG-/i,'SG-');rk.value=b.dataset.codigo||'';rd.value=b.dataset.detalle||'';ru.value=b.dataset.unidad||'und';rb.textContent='Actualizar material SG';rx.style.display='inline-flex';rh.classList.add('on');rf&&rf.scrollIntoView({behavior:'smooth',block:'center'});rd&&rd.focus();}));rx&&rx.addEventListener('click',clearR);
})();
</script>


<style id="zg-odoo-panel-style">
.odoo-cell{min-width:175px;max-width:250px;vertical-align:top}
.odoo-badge{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:6px 9px;font-size:10.5px;font-weight:900;text-transform:capitalize;background:#fff4d6;color:#8a5a00;border:1px solid #ffe08a}
.odoo-badge.ok{background:#eaf8ef;color:#176b34;border-color:#bfe8cd}
.odoo-badge.sending{background:#eaf4ff;color:#155293;border-color:#bdd9f5}
.odoo-cell small{display:block;margin-top:6px;color:#60738a;font-size:10.5px;line-height:1.35;font-weight:700}
.odoo-error-text{max-height:43px;overflow:hidden;display:-webkit-box!important;-webkit-line-clamp:2;-webkit-box-orient:vertical;color:#9a6700!important}
.odoo-retry-btn{margin-top:7px;border:1px solid #bdd9f5;background:#eef6ff;color:#155293;border-radius:9px;padding:7px 9px;font:inherit;font-size:10.5px;font-weight:900;cursor:pointer}
.odoo-retry-btn:hover{background:#dfeeff}.odoo-retry-btn:disabled{opacity:.55;cursor:wait}
</style>
<script id="zg-odoo-panel-js">
(function(){
  document.addEventListener('click', async function(ev){
    const btn = ev.target.closest('[data-odoo-retry]');
    if(!btn) return;
    ev.preventDefault();
    const id = btn.dataset.odooRetry || '';
    const csrf = btn.dataset.csrf || '';
    if(!id) return;
    const previous = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Enviando…';
    try{
      const fd = new FormData();
      fd.append('informe_id', id);
      fd.append('csrf', csrf);
      const response = await fetch('sincronizar_odoo.php', {method:'POST', body:fd, credentials:'same-origin'});
      const out = await response.json();
      if(!out.ok) throw new Error(out.error || 'No se pudo sincronizar con Odoo.');
      alert('PDF sincronizado con Odoo.\nTicket: ' + (out.ticket_ref || '-') + '\nAdjunto ID: ' + (out.attachment_id || '-'));
      location.reload();
    }catch(error){
      alert('El informe continúa guardado en la web, pero Odoo respondió: ' + error.message);
      location.reload();
    }finally{
      btn.disabled = false;
      btn.textContent = previous;
    }
  });
})();
</script>
