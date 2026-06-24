<?php
require_once __DIR__ . '/push_lib.php';
header('Content-Type: application/json; charset=utf-8');

$token = $_POST['token'] ?? $_GET['token'] ?? '';
if (!defined('ZGROUP_PUSH_TRIGGER_TOKEN') || !hash_equals(ZGROUP_PUSH_TRIGGER_TOKEN, (string)$token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit;
}

$tipo = trim((string)($_POST['tipo'] ?? 'novedad'));
$titulo = trim((string)($_POST['titulo'] ?? 'Nueva novedad técnica'));
$detalle = trim((string)($_POST['detalle'] ?? 'Revisa el panel de informes técnicos.'));
$url = trim((string)($_POST['url'] ?? 'panel.php'));

$out = zgroup_enviar_push($titulo, $detalle, $url, ['tipo' => $tipo]);
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
