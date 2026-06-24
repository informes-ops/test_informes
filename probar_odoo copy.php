<?php

// =========================================================================
// 1. CONFIGURACIÓN DE CREDENCIALES (Odoo 17)
// =========================================================================
$url = "https://zgroup.odoo.com"; 
$db = "odoo-ps-psus-zgroup-production-6037046";
$username = "informes@zgroup.com.pe"; 
$password = "08bc0a84048db3f0efa91ee97ab268ba88f23900"; 

// =========================================================================
// 2. CONFIGURACIÓN DEL ARCHIVO Y TICKET
// =========================================================================
$ticket_id = 1732; 
$ruta_pdf = __DIR__ . "/reporte.pdf"; 

if (!file_exists($ruta_pdf)) {
    die("Error local: El archivo PDF de prueba no existe en la ruta especificada ($ruta_pdf).");
}

$pdf_binario = file_get_contents($ruta_pdf);
$pdf_base64 = base64_encode($pdf_binario);

// =========================================================================
// 3. FUNCIÓN AUXILIAR PARA LA CONEXIÓN JSON-RPC
// =========================================================================
function odoo_request($base_url, $service, $method, $args) {
    $payload = json_encode([
        "jsonrpc" => "2.0",
        "method" => "call",
        "params" => [
            "service" => $service,
            "method" => $method,
            "args" => $args
        ],
        "id" => rand()
    ]);

    $ch = curl_init($base_url . "/jsonrpc");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    
    if (curl_errno($ch)) {
        die("Error de conexión de red (cURL): " . curl_error($ch));
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// =========================================================================
// 4. FLUJO DE EJECUCIÓN
// =========================================================================

echo "Iniciando proceso de vinculación automática...<br>";

// Paso A: Autenticación
$auth_res = odoo_request($url, "common", "authenticate", array($db, $username, $password, array()));
$uid = $auth_res['result'] ?? null;
if (!$uid) { die("Error de autenticación."); }

// Paso B: Identificar creador y su Partner ID
$ticket_data_res = odoo_request($url, "object", "execute_kw", array(
    $db, $uid, $password,
    'helpdesk.ticket', 'read',
    array(array($ticket_id), array('create_uid'))
));

$ticket_info = $ticket_data_res['result'][0] ?? null;
if (!$ticket_info) { die("Error al leer el ticket."); }

$creator_user_id = $ticket_info['create_uid'][0]; 
$creator_name    = $ticket_info['create_uid'][1];

$user_data_res = odoo_request($url, "object", "execute_kw", array(
    $db, $uid, $password,
    'res.users', 'read',
    array(array($creator_user_id), array('partner_id'))
));

$partner_id_to_notify = $user_data_res['result'][0]['partner_id'][0] ?? null;
if (!$partner_id_to_notify) { die("Error al recuperar el Partner ID."); }

// Paso C: Subir PDF a ir.attachment
$attachment_data = array(
    'name' => 'reporte_desde_php.pdf',
    'type' => 'binary',
    'datas' => $pdf_base64,
    'res_model' => 'helpdesk.ticket',
    'res_id' => $ticket_id,
    'mimetype' => 'application/pdf'
);

$create_res = odoo_request($url, "object", "execute_kw", array(
    $db, $uid, $password,
    'ir.attachment', 'create',
    array($attachment_data)
));
$attachment_id = $create_res['result'] ?? null;
if (!$attachment_id) { die("Error al subir el archivo."); }

// Paso D: Crear el mensaje en el Chatter
$html_body = "<p>Se ha adjuntado automáticamente un reporte desde el aplicativo externo. </p>";
$html_body .= "<p>Atención <a href='#model=res.partner&id=" . $partner_id_to_notify . "' class='o_channel_redirect' data-oe-id='" . $partner_id_to_notify . "' data-oe-model='res.partner'>@" . $creator_name . "</a> favor de revisar el archivo adjunto.</p>";

$message_data = array(
    'body' => $html_body,
    'model' => 'helpdesk.ticket',
    'res_id' => $ticket_id,
    'message_type' => 'comment',
    'subtype_id' => 2, // Comentario público / Discusión
    'attachment_ids' => array(array(6, 0, array($attachment_id))),
    'partner_ids' => array(array(6, 0, array($partner_id_to_notify)))
);

$message_res = odoo_request($url, "object", "execute_kw", array(
    $db, $uid, $password,
    'mail.message', 'create',
    array($message_data)
));
$message_id = $message_res['result'] ?? null;

if (!$message_id) {
    die("Error al registrar el mensaje en el Chatter.");
}

// =========================================================================
// PASO EXTRA CRÍTICO: Forzar el envío del correo saltándose las reglas del Chatter
// =========================================================================
echo "Forzando la cola de correo saliente en Odoo...<br>";

// Ejecutamos la función de notificación del sistema enviando directamente el correo al partner
$notify_res = odoo_request($url, "object", "execute_kw", array(
    $db, $uid, $password,
    'mail.message', '_notify_thread',
    array(array($message_id)), // ID del mensaje creado
    array(
        'context' => array(
            'mail_notify_force_send' => true, // Envío inmediato sin esperar crons extendidos
            'mail_notify_author' => true      // REGLA CLAVE: Permite que le llegue el correo aunque el autor sea el mismo usuario
        )
    )
));

echo "<h2>¡Proceso Completado!</h2>";
echo "1. Archivo enlazado correctamente.<br>";
echo "2. Mensaje publicado en pantalla.<br>";
echo "3. Orden de envío procesada. Revisa ahora en <b>Ajustes > Técnico > Correos electrónicos</b>; el correo debe figurar creado.";