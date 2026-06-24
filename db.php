<?php
/**
 * Punto de compatibilidad: expone $pdo para scripts legacy.
 * La configuración real está en app/Config/database.php
 */
require_once __DIR__ . '/app/bootstrap.php';

try {
    $pdo = App\Core\Database::getInstance()->getConnection();
} catch (Throwable $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
