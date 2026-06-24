<?php
/**
 * Bootstrap MVC — carga autoload, configuración y helpers comunes.
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = APP_ROOT . '/app/' . $relative . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$appConfig = require APP_ROOT . '/app/Config/app.php';
date_default_timezone_set($appConfig['timezone']);

require_once APP_ROOT . '/app/Helpers/helpers.php';
