<?php
/**
 * Instalador CLI de la base de datos ZGROUP Informes.
 *
 * Uso:
 *   php database/install.php              # importa solo si la BD está vacía
 *   php database/install.php --verify     # verifica conexión y tablas
 *   php database/install.php --force      # reimporta el dump (destructivo)
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\DatabaseInstaller;

$verify = in_array('--verify', $argv ?? [], true);
$force = in_array('--force', $argv ?? [], true);

$installer = DatabaseInstaller::fromConfigFile();

if ($verify) {
    $result = $installer->verificar();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit($result['ok'] ? 0 : 1);
}

try {
    $result = $installer->instalarSiVacia(null, $force);
    echo $result['message'] . PHP_EOL;
    if (!empty($result['details'])) {
        echo json_encode($result['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
