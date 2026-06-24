<?php
/**
 * Configuración general de la aplicación ZGROUP Informes.
 */
return [
    'name'     => 'ZGROUP Informes Técnicos',
    'version'  => 'V53',
    'timezone' => 'America/Lima',
    'paths'    => [
        'root'     => dirname(__DIR__, 2),
        'informes' => dirname(__DIR__, 2) . '/informes',
        'logs'     => dirname(__DIR__, 2),
    ],
    'auth' => [
        'panel_password' => getenv('ZGROUP_PANEL_PASS') ?: '123456',
        'tech_password'  => getenv('ZGROUP_TECH_PASS') ?: 'tecnicos',
    ],
];
