<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Controlador base con utilidades HTTP comunes.
 */
abstract class Controller
{
    protected function requireMethod(string $method): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
            JsonResponse::fail('Método no permitido.', 405);
        }
    }

    protected function requirePanelSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['panel_ok'])) {
            JsonResponse::fail('La sesión del panel venció. Vuelve a ingresar.', 403);
        }
    }

    protected function validateCsrf(string $token): void
    {
        $sessionCsrf = (string)($_SESSION['panel_csrf'] ?? '');
        if ($sessionCsrf === '' || !hash_equals($sessionCsrf, $token)) {
            JsonResponse::fail('Token de seguridad no válido. Recarga el panel.', 403);
        }
    }
}
