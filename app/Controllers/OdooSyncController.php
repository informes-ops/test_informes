<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\JsonResponse;
use App\Models\InformeModel;

/**
 * Sincroniza un informe técnico con Odoo desde el panel (botón "Actualizar en Odoo").
 */
class OdooSyncController extends Controller
{
    public function sincronizar(): void
    {
        $this->requireMethod('POST');
        $this->requirePanelSession();
        $this->validateCsrf(trim((string)($_POST['csrf'] ?? '')));

        $informeId = (int)($_POST['informe_id'] ?? 0);
        if ($informeId <= 0) {
            JsonResponse::fail('Informe inválido.');
        }

        require_once APP_ROOT . '/db.php';
        require_once APP_ROOT . '/odoo_lib.php';

        if (!isset($pdo) || !($pdo instanceof \PDO)) {
            JsonResponse::fail('No se pudo conectar a la base de datos.', 500);
        }

        $model = new InformeModel($pdo);
        $informe = $model->findById($informeId);
        if (!$informe) {
            JsonResponse::fail('El informe no existe.', 404);
        }

        $archivo = basename((string)($informe['archivo'] ?? ''));
        if ($archivo === '' || !preg_match('/^[A-Za-z0-9._-]+\.pdf$/i', $archivo)) {
            JsonResponse::fail('El PDF del informe no tiene un nombre válido.');
        }

        $pdfPath = APP_ROOT . '/informes/' . $archivo;
        if (!is_file($pdfPath) || !is_readable($pdfPath)) {
            JsonResponse::fail('No se encontró el PDF del informe en el servidor.');
        }

        $ticketRef = preg_replace('/\D+/', '', trim((string)($informe['odoo_ticket_ref'] ?? '')));
        if ($ticketRef === '') {
            $ticketRef = preg_replace('/\D+/', '', trim((string)($informe['orden'] ?? '')));
        }
        if ($ticketRef === '') {
            JsonResponse::fail('El informe no tiene ticket Odoo asociado (referencia vacía).');
        }

        $knownAttachment = (int)($informe['odoo_attachment_id'] ?? 0);

        $result = zgOdooSyncInforme(
            $pdo,
            $informeId,
            $ticketRef,
            $pdfPath,
            $archivo,
            $knownAttachment
        );

        if (empty($result['ok'])) {
            JsonResponse::fail($result['error'] ?? 'No se pudo sincronizar con Odoo.');
        }

        $msg = 'PDF ' . ($result['accion'] ?? 'enviado') . ' en Odoo.';
        if (!empty($result['notify_error'])) {
            $msg .= ' Adjunto OK, pero la notificación al creador falló: ' . $result['notify_error'];
        } elseif (!empty($result['message_id'])) {
            $msg .= ' Notificación enviada al creador del ticket.';
        }

        JsonResponse::ok([
            'mensaje' => $msg,
            'ticket_ref' => $result['ticket_ref'] ?? $ticketRef,
            'ticket_id' => $result['ticket_id'] ?? null,
            'attachment_id' => $result['attachment_id'] ?? null,
            'message_id' => $result['message_id'] ?? null,
            'accion' => $result['accion'] ?? '',
        ]);
    }
}
