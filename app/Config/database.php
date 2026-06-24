<?php
/**
 * Configuración de conexión MySQL.
 * En producción, preferir variables de entorno sobre valores por defecto.
 */
return [
    'host'    => getenv('DB_HOST') ?: 'localhost',
    'dbname'  => getenv('DB_NAME') ?: 'zgroupin_zgroupinformes',
    'user'    => getenv('DB_USER') ?: 'zgroupin_zgroupuser',
    'pass'    => getenv('DB_PASS') ?: 'ZGROUP_2026',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
