<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Autenticación de técnicos en campo.
 */
class TechAuthService
{
    private string $password;

    public function __construct(?string $password = null)
    {
        $config = require APP_ROOT . '/app/Config/app.php';
        $this->password = $password ?? (string)($config['auth']['tech_password'] ?? 'tecnicos');
    }

    public function isLoggedIn(): bool
    {
        return !empty($_SESSION['zgroup_tecnicos_ok']);
    }

    public function handleLogout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public function attemptLogin(string $clave): bool
    {
        if (hash_equals($this->password, trim($clave))) {
            $_SESSION['zgroup_tecnicos_ok'] = true;
            return true;
        }
        return false;
    }

    public function requirePanelForEdit(string $modo): bool
    {
        if ($modo !== 'editar_informe' && $modo !== 'editar_preliminar') {
            return false;
        }
        if (empty($_SESSION['panel_ok'])) {
            header('Location: panel.php');
            exit;
        }
        return true;
    }
}
