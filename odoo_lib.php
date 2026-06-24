<?php
/**
 * Integración ZGROUP -> Odoo usando XML-RPC.
 *
 * Replica el mismo flujo del ejemplo Python:
 *   1) POST /xmlrpc/2/common  -> authenticate
 *   2) POST /xmlrpc/2/object  -> execute_kw
 *   3) helpdesk.ticket.search por ticket_ref
 *   4) ir.attachment.create/write con el PDF en Base64
 *
 * No requiere Composer ni la extensión PHP xmlrpc. Construye y procesa
 * XML-RPC directamente usando DOMDocument y cURL/streams HTTPS.
 */

final class ZgOdooXmlRpcStruct
{
    public array $value;

    public function __construct(array $value = [])
    {
        $this->value = $value;
    }
}

if (!function_exists('zgOdooEnsureColumns')) {
    function zgOdooEnsureColumns(PDO $pdo): void
    {
        $columns = [
            'odoo_estado' => "VARCHAR(40) NOT NULL DEFAULT 'pendiente'",
            'odoo_ticket_ref' => 'VARCHAR(120) DEFAULT NULL',
            'odoo_ticket_id' => 'BIGINT DEFAULT NULL',
            'odoo_attachment_id' => 'BIGINT DEFAULT NULL',
            'odoo_nombre_adjunto' => 'VARCHAR(255) DEFAULT NULL',
            'odoo_error' => 'TEXT DEFAULT NULL',
            'odoo_intentos' => 'INT NOT NULL DEFAULT 0',
            'odoo_ultimo_intento_en' => 'DATETIME DEFAULT NULL',
            'odoo_sincronizado_en' => 'DATETIME DEFAULT NULL',
        ];

        foreach ($columns as $name => $definition) {
            $st = $pdo->prepare(
                'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS '
                . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $st->execute(['informes', $name]);
            if ((int)$st->fetchColumn() === 0) {
                $pdo->exec("ALTER TABLE `informes` ADD COLUMN `$name` $definition");
            }
        }
    }
}

if (!function_exists('zgOdooShortError')) {
    function zgOdooShortError(Throwable $e, int $max = 1800): string
    {
        $msg = trim((string)preg_replace('/\s+/u', ' ', $e->getMessage()));
        return function_exists('mb_substr')
            ? mb_substr($msg, 0, $max, 'UTF-8')
            : substr($msg, 0, $max);
    }
}

if (!function_exists('zgOdooXmlEscape')) {
    function zgOdooXmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

if (!function_exists('zgOdooIsListArray')) {
    function zgOdooIsListArray(array $value): bool
    {
        if ($value === []) return true;
        return array_keys($value) === range(0, count($value) - 1);
    }
}

if (!function_exists('zgOdooXmlEncodeValue')) {
    function zgOdooXmlEncodeValue($value): string
    {
        if ($value instanceof ZgOdooXmlRpcStruct) {
            $members = '';
            foreach ($value->value as $name => $memberValue) {
                $members .= '<member><name>' . zgOdooXmlEscape((string)$name) . '</name>'
                    . zgOdooXmlEncodeValue($memberValue) . '</member>';
            }
            return '<value><struct>' . $members . '</struct></value>';
        }

        if ($value === null) return '<value><nil/></value>';
        if (is_bool($value)) return '<value><boolean>' . ($value ? '1' : '0') . '</boolean></value>';
        if (is_int($value)) return '<value><int>' . $value . '</int></value>';
        if (is_float($value)) return '<value><double>' . sprintf('%.14G', $value) . '</double></value>';
        if (is_string($value)) return '<value><string>' . zgOdooXmlEscape($value) . '</string></value>';

        if (is_array($value)) {
            if (!zgOdooIsListArray($value)) {
                return zgOdooXmlEncodeValue(new ZgOdooXmlRpcStruct($value));
            }
            $items = '';
            foreach ($value as $item) $items .= zgOdooXmlEncodeValue($item);
            return '<value><array><data>' . $items . '</data></array></value>';
        }

        throw new RuntimeException('Tipo de dato no compatible con XML-RPC: ' . gettype($value));
    }
}

if (!function_exists('zgOdooHttpPostXml')) {
    function zgOdooHttpPostXml(string $url, string $body, int $timeout): string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: text/xml; charset=UTF-8',
                    'Accept: text/xml',
                    'User-Agent: ZGROUP-Odoo-XMLRPC/1.0',
                ],
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_CONNECTTIMEOUT => min(12, $timeout),
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $response = curl_exec($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $errno !== 0) {
                throw new RuntimeException('No se pudo conectar con Odoo: ' . ($error ?: 'error de red'));
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: text/xml; charset=UTF-8\r\nAccept: text/xml\r\nUser-Agent: ZGROUP-Odoo-XMLRPC/1.0\r\n",
                    'content' => $body,
                    'timeout' => $timeout,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                throw new RuntimeException('No se pudo conectar con Odoo. El hosting debe permitir conexiones HTTPS salientes.');
            }
            $status = 0;
            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $headerLine) {
                    if (preg_match('#^HTTP/\S+\s+(\d{3})#i', $headerLine, $m)) {
                        $status = (int)$m[1];
                        break;
                    }
                }
            }
        }

        if ($status > 0 && ($status < 200 || $status >= 300)) {
            $snippet = trim(substr((string)$response, 0, 700));
            throw new RuntimeException('Odoo respondió HTTP ' . $status . ($snippet !== '' ? ': ' . strip_tags($snippet) : '.'));
        }

        return (string)$response;
    }
}

if (!function_exists('zgOdooXmlParseNode')) {
    /**
     * Parser XML mínimo y seguro para respuestas XML-RPC.
     * No depende de DOM, SimpleXML ni de la extensión xmlrpc.
     */
    function zgOdooXmlParseNode(array $tokens, int &$index): ?array
    {
        $count = count($tokens);
        while ($index < $count) {
            $token = $tokens[$index];
            if (trim($token) === '' || str_starts_with($token, '<?') || str_starts_with($token, '<!--') || str_starts_with($token, '<!DOCTYPE')) {
                $index++;
                continue;
            }
            break;
        }
        if ($index >= $count) return null;

        $open = $tokens[$index++];
        if (!str_starts_with($open, '<') || str_starts_with($open, '</')) {
            throw new RuntimeException('Respuesta XML-RPC mal formada cerca de: ' . substr(trim($open), 0, 80));
        }

        if (preg_match('/^<\s*([A-Za-z0-9_.:-]+)(?:\s[^>]*)?\/\s*>$/s', $open, $m)) {
            return ['name' => $m[1], 'text' => '', 'children' => []];
        }
        if (!preg_match('/^<\s*([A-Za-z0-9_.:-]+)(?:\s[^>]*)?>$/s', $open, $m)) {
            throw new RuntimeException('Etiqueta XML-RPC no reconocida: ' . substr(trim($open), 0, 100));
        }

        $name = $m[1];
        $node = ['name' => $name, 'text' => '', 'children' => []];

        while ($index < $count) {
            $token = $tokens[$index];

            if (preg_match('/^<\/\s*' . preg_quote($name, '/') . '\s*>$/', $token)) {
                $index++;
                return $node;
            }

            if (str_starts_with($token, '<![CDATA[')) {
                $node['text'] .= substr($token, 9, -3);
                $index++;
                continue;
            }

            if (str_starts_with($token, '<')) {
                $child = zgOdooXmlParseNode($tokens, $index);
                if ($child !== null) $node['children'][] = $child;
                continue;
            }

            $node['text'] .= html_entity_decode($token, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $index++;
        }

        throw new RuntimeException("La etiqueta XML-RPC <$name> no fue cerrada correctamente.");
    }
}

if (!function_exists('zgOdooXmlParseDocument')) {
    function zgOdooXmlParseDocument(string $xml): array
    {
        $tokens = preg_split(
            '/(<\?[^>]*\?>|<!--.*?-->|<!\[CDATA\[.*?\]\]>|<!DOCTYPE[^>]*>|<[^>]+>)/s',
            $xml,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        if (!is_array($tokens) || !$tokens) {
            throw new RuntimeException('Odoo devolvió una respuesta XML-RPC vacía.');
        }
        $index = 0;
        $root = zgOdooXmlParseNode($tokens, $index);
        if (!is_array($root)) throw new RuntimeException('No se pudo interpretar la respuesta XML-RPC de Odoo.');
        return $root;
    }
}

if (!function_exists('zgOdooXmlChild')) {
    function zgOdooXmlChild(array $node, string $name): ?array
    {
        foreach ($node['children'] ?? [] as $child) {
            if (($child['name'] ?? '') === $name) return $child;
        }
        return null;
    }
}

if (!function_exists('zgOdooXmlDecodeParsedValue')) {
    function zgOdooXmlDecodeParsedValue(array $valueNode)
    {
        $children = $valueNode['children'] ?? [];
        if (!$children) return (string)($valueNode['text'] ?? '');
        $typed = $children[0];
        $name = (string)($typed['name'] ?? '');
        $text = trim((string)($typed['text'] ?? ''));

        switch ($name) {
            case 'string': return (string)($typed['text'] ?? '');
            case 'int':
            case 'i4':
            case 'i8': return (int)$text;
            case 'double': return (float)$text;
            case 'boolean': return $text === '1';
            case 'nil': return null;
            case 'dateTime.iso8601': return (string)($typed['text'] ?? '');
            case 'base64': return base64_decode($text, false);

            case 'array':
                $out = [];
                $data = zgOdooXmlChild($typed, 'data');
                if ($data) {
                    foreach ($data['children'] ?? [] as $child) {
                        if (($child['name'] ?? '') === 'value') $out[] = zgOdooXmlDecodeParsedValue($child);
                    }
                }
                return $out;

            case 'struct':
                $out = [];
                foreach ($typed['children'] ?? [] as $member) {
                    if (($member['name'] ?? '') !== 'member') continue;
                    $nameNode = zgOdooXmlChild($member, 'name');
                    $memberValue = zgOdooXmlChild($member, 'value');
                    if ($nameNode !== null && $memberValue !== null) {
                        $out[(string)($nameNode['text'] ?? '')] = zgOdooXmlDecodeParsedValue($memberValue);
                    }
                }
                return $out;
        }

        return (string)($typed['text'] ?? '');
    }
}

if (!function_exists('zgOdooXmlRpcCall')) {
    function zgOdooXmlRpcCall(string $endpoint, string $methodName, array $params)
    {
        $paramXml = '';
        foreach ($params as $param) {
            $paramXml .= '<param>' . zgOdooXmlEncodeValue($param) . '</param>';
        }
        $request = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<methodCall><methodName>' . zgOdooXmlEscape($methodName) . '</methodName>'
            . '<params>' . $paramXml . '</params></methodCall>';

        $url = rtrim((string)ODOO_URL, '/') . $endpoint;
        $timeout = defined('ODOO_TIMEOUT_SECONDS') ? max(10, (int)ODOO_TIMEOUT_SECONDS) : 35;
        $response = zgOdooHttpPostXml($url, $request, $timeout);
        $root = zgOdooXmlParseDocument($response);

        if (($root['name'] ?? '') !== 'methodResponse') {
            throw new RuntimeException('Odoo devolvió un XML que no es una respuesta XML-RPC válida.');
        }

        $faultNode = zgOdooXmlChild($root, 'fault');
        if ($faultNode !== null) {
            $faultValue = zgOdooXmlChild($faultNode, 'value');
            $fault = $faultValue ? zgOdooXmlDecodeParsedValue($faultValue) : [];
            $message = is_array($fault)
                ? (string)($fault['faultString'] ?? $fault['faultCode'] ?? 'Error XML-RPC desconocido')
                : (string)$fault;
            throw new RuntimeException('Error de Odoo: ' . trim($message));
        }

        $paramsNode = zgOdooXmlChild($root, 'params');
        $paramNode = $paramsNode ? zgOdooXmlChild($paramsNode, 'param') : null;
        $valueNode = $paramNode ? zgOdooXmlChild($paramNode, 'value') : null;
        if ($valueNode === null) {
            throw new RuntimeException('Odoo respondió sin un valor XML-RPC válido.');
        }
        return zgOdooXmlDecodeParsedValue($valueNode);
    }
}

if (!function_exists('zgOdooAuthenticate')) {
    function zgOdooAuthenticate(): int
    {
        $uid = zgOdooXmlRpcCall('/xmlrpc/2/common', 'authenticate', [
            (string)ODOO_DB,
            (string)ODOO_USERNAME,
            (string)ODOO_API_KEY,
            new ZgOdooXmlRpcStruct([]),
        ]);
        $uid = (int)$uid;
        if ($uid <= 0) {
            throw new RuntimeException('Odoo rechazó la autenticación. Revisa la base, el usuario y la API Key.');
        }
        return $uid;
    }
}

if (!function_exists('zgOdooExecuteKw')) {
    function zgOdooExecuteKw(int $uid, string $model, string $method, array $args = [], array $kwargs = [])
    {
        return zgOdooXmlRpcCall('/xmlrpc/2/object', 'execute_kw', [
            (string)ODOO_DB,
            $uid,
            (string)ODOO_API_KEY,
            $model,
            $method,
            $args,
            new ZgOdooXmlRpcStruct($kwargs),
        ]);
    }
}

if (!function_exists('zgOdooUpdateInformeState')) {
    function zgOdooUpdateInformeState(PDO $pdo, int $informeId, array $values): void
    {
        if ($informeId <= 0 || !$values) return;
        $allowed = [
            'odoo_estado', 'odoo_ticket_ref', 'odoo_ticket_id', 'odoo_attachment_id',
            'odoo_nombre_adjunto', 'odoo_error', 'odoo_intentos',
            'odoo_ultimo_intento_en', 'odoo_sincronizado_en',
        ];
        $sets = [];
        $params = [];
        foreach ($values as $column => $value) {
            if (!in_array($column, $allowed, true)) continue;
            if ($value instanceof DateTimeInterface) $value = $value->format('Y-m-d H:i:s');
            $sets[] = "`$column` = ?";
            $params[] = $value;
        }
        if (!$sets) return;
        $params[] = $informeId;
        $st = $pdo->prepare('UPDATE informes SET ' . implode(', ', $sets) . ' WHERE id = ? LIMIT 1');
        $st->execute($params);
    }
}

if (!function_exists('zgOdooSyncInforme')) {
    function zgOdooSyncInforme(
        PDO $pdo,
        int $informeId,
        string $ticketRef,
        string $pdfPath,
        string $originalFilename = '',
        int $knownAttachmentId = 0
    ): array {
        zgOdooEnsureColumns($pdo);
        $ticketRef = trim($ticketRef);
        $now = date('Y-m-d H:i:s');

        $pdo->prepare(
            "UPDATE informes SET odoo_estado = 'enviando', odoo_ticket_ref = ?, "
            . "odoo_intentos = COALESCE(odoo_intentos,0) + 1, odoo_ultimo_intento_en = ?, odoo_error = NULL "
            . 'WHERE id = ? LIMIT 1'
        )->execute([$ticketRef, $now, $informeId]);

        try {
            $configPath = __DIR__ . '/odoo_config.php';
            if (!file_exists($configPath)) throw new RuntimeException('Falta el archivo odoo_config.php.');
            require_once $configPath;

            if (!defined('ODOO_ENABLED') || !ODOO_ENABLED) {
                zgOdooUpdateInformeState($pdo, $informeId, [
                    'odoo_estado' => 'desactivado',
                    'odoo_error' => 'La sincronización con Odoo está desactivada.',
                ]);
                return ['ok' => false, 'skipped' => true, 'estado' => 'desactivado', 'error' => 'Odoo está desactivado.'];
            }
            foreach (['ODOO_URL', 'ODOO_DB', 'ODOO_USERNAME', 'ODOO_API_KEY'] as $constant) {
                if (!defined($constant) || trim((string)constant($constant)) === '') {
                    throw new RuntimeException('Falta configurar ' . $constant . ' en odoo_config.php.');
                }
            }
            if ($ticketRef === '') throw new RuntimeException('El N.° de reporte/ticket está vacío.');
            if (!is_file($pdfPath) || !is_readable($pdfPath)) throw new RuntimeException('No se puede leer el PDF local para enviarlo a Odoo.');

            $pdf = file_get_contents($pdfPath);
            if ($pdf === false || $pdf === '') throw new RuntimeException('El PDF local está vacío o no se pudo leer.');

            // Equivalente a common.authenticate(...) del código Python.
            $uid = zgOdooAuthenticate();
            $ticketModel = defined('ODOO_TICKET_MODEL') ? (string)ODOO_TICKET_MODEL : 'helpdesk.ticket';
            $refField = defined('ODOO_TICKET_REF_FIELD') ? (string)ODOO_TICKET_REF_FIELD : 'ticket_ref';

            // Comprueba que ticket_ref sea realmente visible para el usuario de integración.
            $fields = zgOdooExecuteKw($uid, $ticketModel, 'fields_get', [], [
                'attributes' => ['string', 'type'],
            ]);
            if (!is_array($fields) || !array_key_exists($refField, $fields)) {
                throw new RuntimeException(
                    "El campo '$refField' no existe o no es visible en $ticketModel. "
                    . 'Cambia ODOO_TICKET_REF_FIELD por el nombre técnico correcto del campo.'
                );
            }

            // Equivalente a:
            // models.execute_kw(..., 'helpdesk.ticket', 'search', [[['ticket_ref', '=', codigo_ticket]]])
            $ticketIds = zgOdooExecuteKw($uid, $ticketModel, 'search', [
                [[$refField, '=', $ticketRef]],
            ], ['limit' => 2]);

            if (!is_array($ticketIds) || count($ticketIds) === 0) {
                zgOdooUpdateInformeState($pdo, $informeId, [
                    'odoo_estado' => 'ticket_no_encontrado',
                    'odoo_error' => "No existe un ticket con $refField = $ticketRef.",
                ]);
                return [
                    'ok' => false,
                    'estado' => 'ticket_no_encontrado',
                    'ticket_ref' => $ticketRef,
                    'error' => 'No se encontró el ticket en Odoo con esa referencia.',
                ];
            }
            if (count($ticketIds) > 1) {
                throw new RuntimeException("Hay más de un ticket con $refField = $ticketRef. No se adjuntó el PDF para evitar asociarlo al ticket equivocado.");
            }
            $ticketId = (int)$ticketIds[0];

            $prefix = defined('ODOO_ATTACHMENT_PREFIX') ? trim((string)ODOO_ATTACHMENT_PREFIX) : 'Informe técnico ZGROUP';
            $safeRef = preg_replace('/[^A-Za-z0-9_.-]/', '_', $ticketRef);
            $attachmentName = ($prefix !== '' ? $prefix . ' ' : '') . $safeRef . '.pdf';

            $attachmentId = max(0, $knownAttachmentId);
            if ($attachmentId > 0) {
                $validIds = zgOdooExecuteKw($uid, 'ir.attachment', 'search', [[
                    ['id', '=', $attachmentId],
                    ['res_model', '=', $ticketModel],
                    ['res_id', '=', $ticketId],
                ]], ['limit' => 1]);
                if (!is_array($validIds) || count($validIds) === 0) $attachmentId = 0;
            }

            if ($attachmentId <= 0) {
                $existing = zgOdooExecuteKw($uid, 'ir.attachment', 'search', [[
                    ['res_model', '=', $ticketModel],
                    ['res_id', '=', $ticketId],
                    ['name', '=', $attachmentName],
                ]], ['limit' => 1, 'order' => 'id desc']);
                if (is_array($existing) && count($existing) > 0) $attachmentId = (int)$existing[0];
            }

            // Equivalente a ir.attachment.create(...) del código Python.
            $attachmentData = [
                'name' => $attachmentName,
                'type' => 'binary',
                'datas' => base64_encode($pdf),
                'res_model' => $ticketModel,
                'res_id' => $ticketId,
                'mimetype' => 'application/pdf',
            ];

            $action = 'creado';
            if ($attachmentId > 0) {
                $ok = zgOdooExecuteKw($uid, 'ir.attachment', 'write', [[$attachmentId], $attachmentData]);
                if (!$ok) throw new RuntimeException('Odoo no confirmó la actualización del adjunto.');
                $action = 'actualizado';
            } else {
                $attachmentId = (int)zgOdooExecuteKw($uid, 'ir.attachment', 'create', [$attachmentData]);
                if ($attachmentId <= 0) throw new RuntimeException('Odoo no devolvió un ID válido para el adjunto.');
            }

            $messageId = 0;
            $notifyError = null;
            if (function_exists('zgOdooNotifyTicketAttachment')) {
                try {
                    $notify = zgOdooNotifyTicketAttachment($uid, $ticketId, $attachmentId);
                    $messageId = (int)($notify['message_id'] ?? 0);
                } catch (Throwable $notifyErr) {
                    $notifyError = zgOdooShortError($notifyErr);
                    @file_put_contents(
                        __DIR__ . '/odoo_debug.log',
                        '[' . date('Y-m-d H:i:s') . '] Notificación chatter ticket ' . $ticketId . ': ' . $notifyError . PHP_EOL,
                        FILE_APPEND
                    );
                }
            }

            zgOdooUpdateInformeState($pdo, $informeId, [
                'odoo_estado' => 'sincronizado',
                'odoo_ticket_ref' => $ticketRef,
                'odoo_ticket_id' => $ticketId,
                'odoo_attachment_id' => $attachmentId,
                'odoo_nombre_adjunto' => $attachmentName,
                'odoo_error' => $notifyError,
                'odoo_sincronizado_en' => date('Y-m-d H:i:s'),
            ]);

            return [
                'ok' => true,
                'estado' => 'sincronizado',
                'accion' => $action,
                'ticket_ref' => $ticketRef,
                'ticket_id' => $ticketId,
                'attachment_id' => $attachmentId,
                'attachment_name' => $attachmentName,
                'message_id' => $messageId,
                'notify_error' => $notifyError,
                'protocolo' => 'XML-RPC',
            ];
        } catch (Throwable $e) {
            $error = zgOdooShortError($e);
            zgOdooUpdateInformeState($pdo, $informeId, [
                'odoo_estado' => 'error',
                'odoo_error' => $error,
            ]);
            @file_put_contents(
                __DIR__ . '/odoo_debug.log',
                '[' . date('Y-m-d H:i:s') . '] Informe ID ' . $informeId . ' / ticket ' . $ticketRef . ': ' . $error . PHP_EOL,
                FILE_APPEND
            );
            return ['ok' => false, 'estado' => 'error', 'ticket_ref' => $ticketRef, 'error' => $error, 'protocolo' => 'XML-RPC'];
        }
    }
}

if (!function_exists('zgOdooNotifyTicketAttachment')) {
    /**
     * Publica comentario en el chatter del ticket y notifica al creador.
     * Equivalente al Paso D de probar_odoo_conexion.php.
     */
    function zgOdooNotifyTicketAttachment(int $uid, int $ticketId, int $attachmentId): array
    {
        if ($ticketId <= 0 || $attachmentId <= 0) {
            throw new RuntimeException('Ticket o adjunto inválido para la notificación.');
        }

        $ticketModel = defined('ODOO_TICKET_MODEL') ? (string)ODOO_TICKET_MODEL : 'helpdesk.ticket';
        $ticketRows = zgOdooExecuteKw($uid, $ticketModel, 'read', [[$ticketId], ['create_uid']]);
        if (!is_array($ticketRows) || empty($ticketRows[0])) {
            throw new RuntimeException('No se pudo leer el ticket en Odoo.');
        }

        $createUid = $ticketRows[0]['create_uid'] ?? null;
        $creatorUserId = is_array($createUid) ? (int)($createUid[0] ?? 0) : (int)$createUid;
        $creatorName = is_array($createUid) ? trim((string)($createUid[1] ?? '')) : '';
        if ($creatorName === '') {
            $creatorName = 'responsable';
        }
        if ($creatorUserId <= 0) {
            throw new RuntimeException('El ticket no tiene un creador identificable en Odoo.');
        }

        $userRows = zgOdooExecuteKw($uid, 'res.users', 'read', [[$creatorUserId], ['partner_id']]);
        $partnerId = 0;
        if (is_array($userRows) && !empty($userRows[0]['partner_id'])) {
            $partner = $userRows[0]['partner_id'];
            $partnerId = is_array($partner) ? (int)($partner[0] ?? 0) : (int)$partner;
        }
        if ($partnerId <= 0) {
            throw new RuntimeException('No se pudo obtener el Partner ID del creador del ticket.');
        }

        $textBody = "Se ha adjuntado automáticamente un reporte desde el aplicativo externo.\n\n"
            . 'Atención @' . $creatorName . ' favor de revisar el archivo adjunto.';

        $messageId = zgOdooExecuteKw($uid, $ticketModel, 'message_post', [[$ticketId]], [
            'body' => $textBody,
            'message_type' => 'comment',
            'subtype_xmlid' => 'mail.mt_comment',
            'attachment_ids' => [$attachmentId],
            'partner_ids' => [$partnerId],
            'context' => [
                'mail_notify_force_send' => true,
                'mail_notify_author' => true,
                'mail_post_autofollow' => true,
            ],
        ]);

        return [
            'message_id' => (int)$messageId,
            'partner_id' => $partnerId,
            'creator_name' => $creatorName,
        ];
    }
}
