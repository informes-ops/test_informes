<?php
declare(strict_types=1);

/**
 * Funciones auxiliares compartidas por la capa MVC y los scripts legacy.
 */

function zgroup_e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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

function zgroup_informes_dir(): string
{
    $config = require APP_ROOT . '/app/Config/app.php';
    return $config['paths']['informes'];
}
