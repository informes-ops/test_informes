<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\JsonResponse;
use App\Core\SchemaHelper;
use App\Models\InformeModel;

/**
 * API: sincronización manual de un informe con Odoo.
 * Punto de entrada legacy: sincronizar_odoo.php
 */
class OdooSyncController extends Controller
{
    public function sincronizar(): void
    {
        $this->requireMethod('POST');
        $this->requirePanelSession();
        $this->validateCsrf((string)($_POST['csrf'] ?? ''));

        require_once APP_ROOT . '/odoo_lib.php';

        $pdo = \App\Core\Database::getInstance()->getConnection();
        SchemaHelper::asegurarColumnasOdoo($pdo);

        $id = (int)($_POST['informe_id'] ?? 0);
        if ($id <= 0) {
            JsonResponse::fail('Informe no válido.', 400);
        }

        $model = new InformeModel($pdo);
        $inf = $model->findById($id);
        if (!$inf) {
            JsonResponse::fail('El informe ya no existe.', 404);
        }

        $archivo = basename((string)$inf['archivo']);
        if ($archivo === '' || !preg_match('/^[A-Za-z0-9._-]+\.pdf$/i', $archivo)) {
            JsonResponse::fail('El nombre del PDF no es seguro.', 400);
        }

        $path = APP_ROOT . '/informes/' . $archivo;
        $ticketRef = trim((string)($inf['odoo_ticket_ref'] ?? '')) !== ''
            ? (string)$inf['odoo_ticket_ref']
            : (string)$inf['orden'];

        try {
            $result = zgOdooSyncInforme($pdo, $id, $ticketRef, $path, $archivo);
            JsonResponse::ok($result);
        } catch (\Throwable $e) {
            JsonResponse::fail('Error sincronizando con Odoo: ' . $e->getMessage(), 500);
        }
    }
}
