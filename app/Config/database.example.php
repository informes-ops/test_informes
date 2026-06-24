<?php
/**
 * Ejemplo de configuración de base de datos.
 * Copia este archivo como app/Config/database.local.php o usa variables de entorno.
 */
return [
    'host'    => 'localhost',
    'dbname'  => 'nombre_base_datos',
    'user'    => 'usuario_mysql',
    'pass'    => 'contraseña_segura',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
