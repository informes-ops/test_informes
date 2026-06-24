<?php
/**
 * Wrapper MVC para sincronizar_odoo.php (compatibilidad con rutas existentes).
 */
session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
date_default_timezone_set('America/Lima');

require_once __DIR__ . '/app/bootstrap.php';

(new App\Controllers\OdooSyncController())->sincronizar();
