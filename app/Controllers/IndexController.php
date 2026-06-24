<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Services\IndexFormDataService;
use App\Services\TechAuthService;

/**
 * Controlador principal del área técnica (index.php).
 * Enruta login → menú → formulario según funcionamiento.md §4.1
 */
class IndexController extends Controller
{
    private TechAuthService $auth;
    private string $loginError = '';

    public function __construct(?TechAuthService $auth = null)
    {
        $this->auth = $auth ?? new TechAuthService();
    }

    public function dispatch(): void
    {
        $this->initSession();
        $this->handleLogout();
        $this->handleLoginPost();

        $modo = trim((string)($_GET['modo'] ?? ''));
        $token = trim((string)($_GET['token'] ?? ''));

        $modoEditarInforme = ($modo === 'editar_informe');
        $modoEditarPreliminar = ($modo === 'editar_preliminar');

        if ($modoEditarInforme || $modoEditarPreliminar) {
            $this->auth->requirePanelForEdit($modo);
            $_SESSION['zgroup_tecnicos_ok'] = true;
            $modo = 'cliente';
        }

        $logueado = $this->auth->isLoggedIn() || $modoEditarInforme || $modoEditarPreliminar;

        if (!$logueado) {
            View::render('tecnicos/login', [
                'login_error' => $this->loginError,
                'redirect_token' => $token,
                'redirect_modo' => $modo,
            ]);
            return;
        }

        if ($token === '' && $modo === '') {
            View::render('tecnicos/menu');
            return;
        }

        if ($token === '' && $modo === 'base') {
            View::render('tecnicos/base_proceso');
            return;
        }

        $this->renderFormulario([
            'token' => $token,
            'modo_editar_informe' => $modoEditarInforme,
            'modo_editar_preliminar' => $modoEditarPreliminar,
            'informe_id' => (int)($_GET['id'] ?? 0),
            'preliminar_id' => (int)($_GET['id'] ?? 0),
        ]);
    }

    private function initSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        date_default_timezone_set(
            (require APP_ROOT . '/app/Config/app.php')['timezone'] ?? 'America/Lima'
        );
    }

    private function handleLogout(): void
    {
        if (!isset($_GET['salir'])) {
            return;
        }
        $this->auth->handleLogout();
        header('Location: index.php');
        exit;
    }

    private function handleLoginPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['tech_access_password'])) {
            return;
        }
        if ($this->auth->attemptLogin((string)$_POST['tech_access_password'])) {
            header('Location: ' . $this->loginRedirectUrl());
            exit;
        }
        $this->loginError = 'Contraseña incorrecta. Intenta nuevamente.';
    }

    private function loginRedirectUrl(): string
    {
        $token = trim((string)($_POST['redirect_token'] ?? $_GET['token'] ?? ''));
        if ($token !== '') {
            return 'index.php?token=' . rawurlencode($token);
        }

        $modo = trim((string)($_POST['redirect_modo'] ?? $_GET['modo'] ?? ''));
        $id = (int)($_POST['redirect_id'] ?? $_GET['id'] ?? 0);
        if ($modo === 'editar_informe' && $id > 0) {
            return 'index.php?modo=editar_informe&id=' . $id;
        }
        if ($modo === 'editar_preliminar' && $id > 0) {
            return 'index.php?modo=editar_preliminar&id=' . $id;
        }
        if ($modo !== '') {
            return 'index.php?modo=' . rawurlencode($modo);
        }

        $uri = (string)($_SERVER['REQUEST_URI'] ?? 'index.php');
        return $uri !== '' ? $uri : 'index.php';
    }

    private function renderFormulario(array $contexto): void
    {
        $data = (new IndexFormDataService())->preparar($contexto);
        $data['modo_editar_informe'] = !empty($contexto['modo_editar_informe']);
        $data['modo_editar_preliminar'] = !empty($contexto['modo_editar_preliminar']);
        $data['preliminarEdicionId'] = !empty($contexto['modo_editar_preliminar'])
            ? (int)($contexto['preliminar_id'] ?? 0) : 0;
        extract($data, EXTR_SKIP);
        require APP_ROOT . '/app/Views/tecnicos/formulario.php';
    }
}
