<?php
/*
 * Configuración ZGROUP -> Odoo por XML-RPC.
 * Endpoints utilizados:
 *   /xmlrpc/2/common
 *   /xmlrpc/2/object
 *
 * Genera una API Key NUEVA en Odoo y colócala únicamente aquí.
 * No compartas ni publiques este archivo.
 */

if (!defined('ODOO_ENABLED')) {
    define('ODOO_ENABLED', true);
}

if (!defined('ODOO_URL')) {
    define('ODOO_URL', getenv('ODOO_URL') ?: 'https://zgroup.odoo.com');
}

if (!defined('ODOO_DB')) {
    define('ODOO_DB', getenv('ODOO_DB') ?: 'odoo-ps-psus-zgroup-production-6037046');
}

if (!defined('ODOO_USERNAME')) {
    define('ODOO_USERNAME', getenv('ODOO_USERNAME') ?: 'desarrollo@zgroup.com.pe');
}

if (!defined('ODOO_API_KEY')) {
    // Pega aquí una API Key NUEVA. No uses la clave que ya fue compartida.
    define('ODOO_API_KEY', getenv('ODOO_API_KEY') ?: '9e85265668469de55ac0c349b99ac5d94e767cff');
}

if (!defined('ODOO_TICKET_MODEL')) {
    define('ODOO_TICKET_MODEL', 'helpdesk.ticket');
}

if (!defined('ODOO_TICKET_REF_FIELD')) {
    // El código Python proporcionado usa exactamente ticket_ref.
    define('ODOO_TICKET_REF_FIELD', 'ticket_ref');
}

if (!defined('ODOO_TIMEOUT_SECONDS')) {
    define('ODOO_TIMEOUT_SECONDS', 35);
}

if (!defined('ODOO_ATTACHMENT_PREFIX')) {
    define('ODOO_ATTACHMENT_PREFIX', 'Informe técnico ZGROUP');
}
