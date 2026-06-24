<?php
declare(strict_types=1);

/**
 * Funciones auxiliares compartidas por la capa MVC y los scripts legacy.
 */

/** Escape HTML — alias usado en vistas (formulario.php, panel.php, etc.). */
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

function zgroup_e($value): string
{
    return e($value);
}

if (!function_exists('fechaHora')) {
    function fechaHora($dt): string
    {
        if (!$dt) {
            return '—';
        }
        $ts = strtotime((string)$dt);
        return $ts ? date('d/m/Y · H:i:s', $ts) : e($dt);
    }
}

function zgroup_hora_sql(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $value = str_replace('T', ' ', $value);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
        $value .= ':00';
    }
    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value) ? $value : null;
}

function zgroup_safe_pdf_name(string $orden): string
{
    $base = preg_replace('/[^A-Za-z0-9_-]/', '_', $orden !== '' ? $orden : 'sin_reporte');
    return 'informe_' . $base . '_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(5)), 0, 8) . '.pdf';
}

function zgroup_snapshot_field(array $snapshot, string $id, string $default = ''): string
{
    if (isset($snapshot['fields'][$id]['value'])) {
        return trim((string)$snapshot['fields'][$id]['value']);
    }
    return $default;
}

function zgroup_selected(string $optionValue, string $current): string
{
    return $optionValue === $current ? ' selected' : '';
}

function zgroup_campo(array $campos, string $id, string $default = ''): string
{
    return (string)($campos[$id] ?? $default);
}

function zgroup_informes_dir(): string
{
    $config = require APP_ROOT . '/app/Config/app.php';
    return $config['paths']['informes'];
}

/** URL pública de un archivo en public/assets/ (sin barra inicial). */
function asset_url(string $path): string
{
    return 'assets/' . ltrim(str_replace('\\', '/', $path), '/');
}

function zgroup_is_reefer_equipo(array $snapshot, string $tipoEquipo = ''): bool
{
    if ($tipoEquipo === '') {
        $tipoEquipo = zgroup_snapshot_field($snapshot, 'zgTipoEquipo', '');
    }
    if ($tipoEquipo === '') {
        $marca = strtoupper(zgroup_snapshot_field($snapshot, 'marcaEquipo', ''));
        $tipoEquipo = $marca === 'GENSET' ? 'Genset' : 'Reefer';
    }
    return strcasecmp($tipoEquipo, 'Genset') !== 0;
}

function zgroup_count_repuestos_snapshot(array $snapshot, string $repuestosManual = ''): int
{
    $count = 0;
    if ($repuestosManual === '') {
        $repuestosManual = trim(zgroup_snapshot_field($snapshot, 'repuestosManual', ''));
    }
    if ($repuestosManual !== '') {
        foreach (preg_split('/\R/', $repuestosManual) as $line) {
            if (trim($line) !== '') {
                $count++;
            }
        }
    }
    $selected = $snapshot['state']['selected'] ?? [];
    if (!is_array($selected)) {
        return $count;
    }
    foreach ($selected as $work) {
        if (!is_array($work)) {
            continue;
        }
        foreach (['repuestosTrabajo', 'materialesTrabajo', 'materiales', 'repuestos'] as $key) {
            if (!isset($work[$key]) || !is_array($work[$key])) {
                continue;
            }
            foreach ($work[$key] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $det = trim((string)($item['detalle'] ?? $item['material'] ?? $item['nombre'] ?? ''));
                if ($det !== '') {
                    $count++;
                }
            }
        }
    }
    return $count;
}

/** Devuelve mensaje de error o null si la validación pasa. */
function zgroup_validate_condicion_comercial_repuestos(
    array $snapshot,
    string $repuestosManual = '',
    string $tipoEquipo = '',
    string $trabajos = ''
): ?string {
    if (!zgroup_is_reefer_equipo($snapshot, $tipoEquipo)) {
        return null;
    }
    $selected = $snapshot['state']['selected'] ?? [];
    $hasWorks = trim($trabajos) !== '' || (is_array($selected) && count($selected) > 0);
    if (!$hasWorks) {
        return null;
    }
    $modalidad = strtolower(zgroup_snapshot_field($snapshot, 'zgModalidadComercial', ''));
    if (strpos($modalidad, 'alquiler') === false && strpos($modalidad, 'venta') === false) {
        return null;
    }
    if (zgroup_count_repuestos_snapshot($snapshot, $repuestosManual) >= 1) {
        return null;
    }
    if (strpos($modalidad, 'alquiler') !== false) {
        return 'En alquiler debe registrarse la pieza que será reemplazada en el servicio reefer.';
    }
    return 'En venta debe registrarse al menos un repuesto pendiente de cotización.';
}
