<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Renderizado de vistas PHP con extract de variables.
 */
final class View
{
    public static function render(string $view, array $data = []): void
    {
        $path = APP_ROOT . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("Vista no encontrada: $view ($path)");
        }
        extract($data, EXTR_SKIP);
        require $path;
    }

    public static function partial(string $partial, array $data = []): void
    {
        self::render('tecnicos/partials/' . $partial, $data);
    }
}
