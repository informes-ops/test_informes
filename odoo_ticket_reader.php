<?php
/**
 * Lector reutilizable de tickets Odoo para el panel ZGROUP.
 * Devuelve solo campos operativos; no imprime HTML ni JSON.
 */
function zgClean($value): string {
    $value = html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/[\x{00A0}\t ]+/u', ' ', $value);
    $value = preg_replace('/\r\n?|\n/u', "\n", $value);
    $value = preg_replace('/\n{3,}/u', "\n\n", $value);
    return trim($value);
}
function zgNorm($value): string {
    $value = zgClean($value);
    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    if (function_exists('iconv')) {
        $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($tmp !== false) $value = $tmp;
    }
    $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
    return trim(preg_replace('/\s+/', ' ', $value));
}
function zgM2OId($value): int {
    if (is_array($value)) {
        if (isset($value[0]) && is_numeric($value[0])) return (int)$value[0];
        if (isset($value['0']) && is_numeric($value['0'])) return (int)$value['0'];
    }
    return 0;
}
function zgRelationIds($value, string $type): array {
    if ($type === 'many2one') {
        $id = zgM2OId($value);
        return $id > 0 ? [$id] : [];
    }
    if (!is_array($value)) return [];
    $ids = [];
    foreach ($value as $item) {
        if (is_numeric($item) && (int)$item > 0) $ids[] = (int)$item;
        elseif (is_array($item)) {
            $id = zgM2OId($item);
            if ($id > 0) $ids[] = $id;
        }
    }
    return array_values(array_unique($ids));
}
function zgReadableValue($value, array $meta = []): string {
    if ($value === false || $value === null) return '';
    if (is_bool($value)) return $value ? 'Sí' : 'No';
    if (is_scalar($value)) return zgClean((string)$value);
    if (is_array($value)) {
        $type = (string)($meta['type'] ?? '');
        if ($type === 'many2one') return zgClean((string)($value[1] ?? $value['1'] ?? ''));
        $parts = [];
        foreach ($value as $item) {
            if (is_scalar($item)) $parts[] = (string)$item;
            elseif (is_array($item)) $parts[] = (string)($item[1] ?? $item['1'] ?? '');
        }
        return zgClean(implode(', ', array_filter($parts, fn($v) => trim((string)$v) !== '')));
    }
    return '';
}
function zgFieldBest(array $record, array $fields, array $phrases, array $excludes = []): array {
    $best = ['value' => '', 'field' => '', 'label' => '', 'score' => 0, 'source' => ''];
    foreach ($fields as $name => $meta) {
        if (!array_key_exists($name, $record)) continue;
        $value = zgReadableValue($record[$name], is_array($meta) ? $meta : []);
        if ($value === '') continue;
        $hay = zgNorm($name . ' ' . (string)($meta['string'] ?? ''));
        $skip = false;
        foreach ($excludes as $bad) {
            if ($bad !== '' && str_contains($hay, zgNorm($bad))) { $skip = true; break; }
        }
        if ($skip) continue;
        $score = 0;
        foreach ($phrases as $i => $phrase) {
            $needle = zgNorm($phrase);
            if ($needle === '') continue;
            if ($hay === $needle) $score = max($score, 600 - $i);
            elseif (preg_match('/(^| )' . preg_quote($needle, '/') . '( |$)/', $hay)) $score = max($score, 430 - $i);
            elseif (str_contains($hay, $needle)) $score = max($score, 300 - $i);
        }
        if ($score > $best['score']) {
            $best = [
                'value' => $value,
                'field' => $name,
                'label' => (string)($meta['string'] ?? $name),
                'score' => $score,
                'source' => (string)($meta['_source'] ?? 'Ticket'),
            ];
        }
    }
    return $best;
}
function zgLineValue(string $text, array $labels): string {
    $lines = preg_split('/\n+/u', str_replace(['<br>', '<br/>', '<br />'], "\n", $text));
    foreach ($lines ?: [] as $line) {
        $line = trim((string)$line, " \t\n\r\0\x0B•-*;");
        if ($line === '') continue;
        foreach ($labels as $label) {
            $pattern = '/^\s*' . preg_quote($label, '/') . '\s*[:\-]\s*(.+)$/iu';
            if (preg_match($pattern, $line, $m)) return zgClean($m[1]);
        }
    }
    return '';
}

function zgInlineLabeledValue(string $text, array $labels, array $stopLabels = []): string {
    $clean = zgClean(str_ireplace(
        ['</p>','</div>','<br>','<br/>','<br />','</li>','</tr>'],
        "\n",
        $text
    ));
    foreach ($labels as $label) {
        $labelPattern = preg_quote($label, '/');
        $stopParts = [];
        foreach ($stopLabels as $stop) {
            $stopParts[] = preg_quote($stop, '/');
        }
        $stopPattern = $stopParts
            ? '(?=\s+(?:' . implode('|', $stopParts) . ')\s*[:\-]|\n|$)'
            : '(?=\n|$)';
        $pattern = '/(?:^|\n|\s)' . $labelPattern . '\s*[:\-]\s*(.+?)' . $stopPattern . '/iu';
        if (preg_match($pattern, $clean, $m)) {
            $value = zgClean($m[1] ?? '');
            if ($value !== '') return $value;
        }
    }
    return '';
}

function zgDigitsReport(string $text, string $ticketRef): string {
    preg_match_all('/(?<!\d)\d{6,15}(?!\d)/', $text, $m);
    $numbers = array_values(array_unique($m[0] ?? []));
    $numbers = array_values(array_filter($numbers, fn($n) => ltrim((string)$n, '0') !== ltrim($ticketRef, '0')));
    usort($numbers, function($a, $b) {
        if (strlen($a) !== strlen($b)) return strlen($b) <=> strlen($a);
        return strcmp($a, $b);
    });
    return (string)($numbers[0] ?? '');
}
function zgBestReportNumber(string $title, string $allText, string $ticketRef, string $vat = ''): string {
    $candidates = [];
    foreach ([['text'=>$title,'title'=>true], ['text'=>$allText,'title'=>false]] as $source) {
        preg_match_all('/(?<!\d)\d{6,15}(?!\d)/', (string)$source['text'], $m);
        foreach (array_values(array_unique($m[0] ?? [])) as $number) {
            $plain = ltrim((string)$number, '0');
            if ($plain === ltrim($ticketRef, '0')) continue;
            if ($vat !== '' && $plain === ltrim(preg_replace('/\D+/', '', $vat), '0')) continue;
            $len = strlen($number);
            $score = 0;
            if ($source['title']) $score += 80;
            if (str_starts_with($number, '100')) $score += 120;
            if ($len >= 9 && $len <= 12) $score += 35;
            if ($len === 11) $score += 18;
            if ($len >= 13) $score -= 45;
            if (preg_match('/^(19|20)\d{6}$/', $number)) $score -= 70;
            if (preg_match('/^(9|51)\d{7,11}$/', $number)) $score -= 25;
            $key = $number;
            if (!isset($candidates[$key]) || $score > $candidates[$key]) $candidates[$key] = $score;
        }
    }
    if (!$candidates) return '';
    arsort($candidates);
    return (string)array_key_first($candidates);
}
function zgDateIso(string $value): string {
    $value = trim($value);
    if ($value === '') return '';
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $m)) return "$m[1]-$m[2]-$m[3]";
    if (preg_match('/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})\b/', $value, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
    }
    return '';
}
function zgKnownBrand(string $text): string {
    $n = zgNorm($text);
    foreach (['THERMO KING', 'CARRIER', 'STAR COOL', 'DAIKIN'] as $brand) {
        if (str_contains($n, zgNorm($brand))) return $brand;
    }
    return '';
}
function zgKnownController(string $text): string {
    $patterns = [
        '/\bMP\s*-?\s*3000\b/i' => 'MP3000',
        '/\bMP\s*-?\s*4000\b/i' => 'MP4000',
        '/\bMP\s*-?\s*5000\b/i' => 'MP5000',
        '/\bMICROLINK\s*-?\s*2\b/i' => 'MICROLINK 2',
        '/\bMICROLINK\s*-?\s*3\b/i' => 'MICROLINK 3',
        '/\bCIM\s*-?\s*5\b/i' => 'CIM5',
        '/\bCIM\s*-?\s*6\b/i' => 'CIM6',
        '/\bSG\s*-?\s*3000\b/i' => 'SG-3000',
        '/\bSG\s*-?\s*5000\b/i' => 'SG-5000',
    ];
    foreach ($patterns as $regex => $name) if (preg_match($regex, $text)) return $name;
    return '';
}
function zgContainerFromText(string $text): string {
    $patterns = [
        '/\b[A-Z]{4}\d{4,7}(?:-\d)?\b/i',
        '/\b(?:CONTENEDOR|CONTAINER|UNIDAD|EQUIPO)\s*(?:N[°ºO.]*)?\s*[:\-]?\s*([A-Z0-9][A-Z0-9_.\/-]{2,40})/iu',
    ];
    foreach ($patterns as $regex) {
        if (preg_match($regex, $text, $m)) return strtoupper(zgClean($m[1] ?? $m[0]));
    }
    return '';
}
function zgNormalizeSize(string $text): string {
    $n = strtoupper(zgClean($text));
    if (preg_match('/\b(10|20|40|45)\s*(?:FT|PIES|PIE|\'|FOOT)?\b/', $n, $m)) return $m[1] . ' pies';
    if (str_contains($n, 'NO APLICA')) return 'No aplica';
    return '';
}
function zgLooksUsefulRelation(string $name, array $meta): bool {
    $hay = zgNorm($name . ' ' . (string)($meta['string'] ?? '') . ' ' . (string)($meta['relation'] ?? ''));
    foreach (['task','tarea','project','proyecto','equipment','equipo','machine','maquina','container','contenedor','unit','unidad','asset','activo','serial','serie','product','producto','sale','venta','order','orden','service','servicio','maintenance','mantenimiento'] as $token) {
        if (str_contains($hay, zgNorm($token))) return true;
    }
    return false;
}
function zgCandidateFields(array $fields, array $tokens, int $limit = 100): array {
    $allowed = ['char','text','html','selection','date','datetime','integer','float','many2one','boolean'];
    $out = [];
    foreach (['name','display_name','description','note','partner_id','phone','mobile','email'] as $base) {
        if (isset($fields[$base])) $out[] = $base;
    }
    foreach ($fields as $name => $meta) {
        if (!is_array($meta) || !in_array((string)($meta['type'] ?? ''), $allowed, true)) continue;
        $hay = zgNorm($name . ' ' . (string)($meta['string'] ?? ''));
        foreach ($tokens as $token) {
            if (str_contains($hay, zgNorm($token))) { $out[] = $name; break; }
        }
        if (count($out) >= $limit) break;
    }
    return array_values(array_unique($out));
}
function zgFlatten(array &$bagRecord, array &$bagFields, string $prefix, string $source, array $record, array $fields): void {
    foreach ($record as $field => $value) {
        if ($field === 'id' || !isset($fields[$field]) || !is_array($fields[$field])) continue;
        $meta = $fields[$field];
        $type = (string)($meta['type'] ?? '');
        if (in_array($type, ['one2many','many2many'], true)) continue;
        $readable = zgReadableValue($value, $meta);
        if ($readable === '') continue;
        $key = $prefix . '__' . $field;
        $bagRecord[$key] = $value;
        $meta['_source'] = $source;
        $meta['string'] = trim($source . ' · ' . (string)($meta['string'] ?? $field), ' ·');
        $bagFields[$key] = $meta;
    }
}
function zgAddDetail(array &$details, array &$seen, string $label, string $value): void {
    $label = zgClean($label);
    $value = zgClean($value);
    if ($label === '' || $value === '') return;
    $fingerprint = zgNorm($label . '|' . $value);
    if ($fingerprint === '' || isset($seen[$fingerprint])) return;
    $seen[$fingerprint] = true;
    $details[] = ['label' => $label, 'value' => $value];
}


function zgUsefulAddress($value): string {
    $value = zgClean((string)$value);
    if ($value === '') return '';
    if (preg_match('/virtual\s+locations|production|wh\/stock|inventory|ubicaciones\s+virtuales/i', $value)) return '';
    return $value;
}
function zgEquipmentNumber($value, string $ticketRef = '', string $report = ''): string {
    $value = strtoupper(zgClean((string)$value));
    if ($value === '') return '';

    if (preg_match('/\b[A-Z]{4}\d{7}\b/', $value, $m)) return $m[0];

    $plain = trim(preg_replace('/\s+/', ' ', $value));
    if ($plain === $ticketRef || $plain === $report) return '';
    if (strlen($plain) > 40) return '';
    if (preg_match('/\b(ALQUILER|VENTA|REEFER|SERVICIO|CLIENTE|TICKET|REPORTE|MANTENIMIENTO|TRANSPORTE)\b/i', $plain)) return '';
    if (substr_count($plain, ' ') > 2) return '';

    if (preg_match('/^[A-Z0-9][A-Z0-9._\/-]{4,39}$/', $plain)) return $plain;
    return '';
}

function zgoLeerTicket(string $ticketRef): array {
    $ticketRef = trim($ticketRef);
    if (!preg_match('/^\d{1,15}$/', $ticketRef)) throw new InvalidArgumentException('Ticket inválido.');
    require_once __DIR__ . '/odoo_config.php';
        require_once __DIR__ . '/odoo_lib.php';
        if (!defined('ODOO_ENABLED') || !ODOO_ENABLED) throw new RuntimeException('Integración desactivada.');
        if (!defined('ODOO_API_KEY') || trim((string)ODOO_API_KEY) === '') throw new RuntimeException('API Key no configurada.');

        $uid = zgOdooAuthenticate();
        $model = (string)ODOO_TICKET_MODEL;
        $refField = (string)ODOO_TICKET_REF_FIELD;

        $fields = zgOdooExecuteKw($uid, $model, 'fields_get', [], [
            'attributes' => ['string', 'type', 'relation', 'selection'],
        ]);
        if (!is_array($fields) || !isset($fields[$refField])) throw new RuntimeException('Campo de referencia no disponible.');

        $tokens = [
            'name','description','note','partner','customer','cliente','contact','contacto','direccion','address','ubicacion','location',
            'ticket','reference','referencia','report','reporte','cotizacion','orden','service','servicio','fecha','date',
            'container','contenedor','equipment','equipo','machine','maquina','unit','unidad','asset','activo','serial','serie',
            'brand','marca','model','modelo','controller','controlador','refrigerant','refrigerante','size','tamano','tamaño',
            'type','tipo','condition','condicion','modalidad','alquiler','venta','team','stage','phone','telefono','mobile','correo','email',
            'task','tarea','project','proyecto','product','producto','maintenance','mantenimiento'
        ];
        $initialTypes = ['char','text','html','selection','date','datetime','integer','float','many2one','boolean','one2many','many2many'];
        $readFields = [$refField];
        foreach (['name','description','partner_id','team_id','stage_id','user_id','ticket_type_id','type_id','create_date','write_date'] as $f) {
            if (isset($fields[$f])) $readFields[] = $f;
        }
        foreach ($fields as $name => $meta) {
            if (!is_array($meta) || !in_array((string)($meta['type'] ?? ''), $initialTypes, true)) continue;
            $hay = zgNorm($name . ' ' . (string)($meta['string'] ?? '') . ' ' . (string)($meta['relation'] ?? ''));
            foreach ($tokens as $token) {
                if (str_contains($hay, zgNorm($token))) { $readFields[] = $name; break; }
            }
            if (count($readFields) >= 180) break;
        }
        $readFields = array_values(array_unique($readFields));

        $rows = zgOdooExecuteKw($uid, $model, 'search_read', [[[$refField, '=', $ticketRef]]], [
            'fields' => $readFields,
            'limit' => 1,
        ]);
        if (!is_array($rows) || empty($rows[0]) || !is_array($rows[0])) {
            throw new RuntimeException('Ticket no encontrado.');
        }
        $r = $rows[0];
        $ticketId = (int)($r['id'] ?? 0);

        $bagRecord = [];
        $bagFields = [];
        zgFlatten($bagRecord, $bagFields, 'ticket', 'Ticket', $r, $fields);

        $relatedTexts = [];
        $relationsRead = 0;
        foreach ($fields as $fieldName => $meta) {
            if ($relationsRead >= 10 || !array_key_exists($fieldName, $r) || !is_array($meta)) continue;
            $type = (string)($meta['type'] ?? '');
            $relation = trim((string)($meta['relation'] ?? ''));
            if (!in_array($type, ['many2one','one2many','many2many'], true) || $relation === '' || !zgLooksUsefulRelation($fieldName, $meta)) continue;
            $ids = array_slice(zgRelationIds($r[$fieldName], $type), 0, 12);
            if (!$ids) continue;
            try {
                $relFields = zgOdooExecuteKw($uid, $relation, 'fields_get', [], ['attributes' => ['string','type','relation','selection']]);
                if (!is_array($relFields)) continue;
                $relRead = zgCandidateFields($relFields, $tokens, 110);
                if (!$relRead) continue;
                $relRows = zgOdooExecuteKw($uid, $relation, 'read', [$ids], ['fields' => $relRead]);
                if (!is_array($relRows)) continue;
                $sourceBase = zgClean((string)($meta['string'] ?? $fieldName));
                foreach ($relRows as $idx => $relRow) {
                    if (!is_array($relRow)) continue;
                    $source = $sourceBase;
                    $display = zgReadableValue($relRow['display_name'] ?? $relRow['name'] ?? '', $relFields['display_name'] ?? $relFields['name'] ?? []);
                    if ($display !== '') $source .= ' · ' . $display;
                    zgFlatten($bagRecord, $bagFields, 'rel' . $relationsRead . '_' . $idx, $source, $relRow, $relFields);
                    foreach (['name','display_name','description','note'] as $textField) {
                        if (isset($relRow[$textField])) {
                            $txt = zgReadableValue($relRow[$textField], $relFields[$textField] ?? []);
                            if ($txt !== '') $relatedTexts[] = $txt;
                        }
                    }
                }
                $relationsRead++;
            } catch (Throwable $ignored) {
                // Una relación sin permisos no debe impedir leer el ticket.
            }
        }

        // Respaldo: algunos Odoo enlazan la tarea al ticket desde project.task.
        if ($ticketId > 0 && $relationsRead < 10) {
            try {
                $taskFields = zgOdooExecuteKw($uid, 'project.task', 'fields_get', [], ['attributes' => ['string','type','relation','selection']]);
                if (is_array($taskFields)) {
                    $ticketLink = '';
                    foreach ($taskFields as $fieldName => $meta) {
                        if (!is_array($meta)) continue;
                        if (($meta['type'] ?? '') === 'many2one' && ($meta['relation'] ?? '') === $model) { $ticketLink = $fieldName; break; }
                        $hay = zgNorm($fieldName . ' ' . (string)($meta['string'] ?? ''));
                        if (($meta['type'] ?? '') === 'many2one' && (str_contains($hay, 'helpdesk') || str_contains($hay, 'ticket'))) $ticketLink = $fieldName;
                    }
                    if ($ticketLink !== '') {
                        $taskRead = zgCandidateFields($taskFields, $tokens, 120);
                        $taskRows = zgOdooExecuteKw($uid, 'project.task', 'search_read', [[[$ticketLink, '=', $ticketId]]], ['fields' => $taskRead, 'limit' => 10]);
                        if (is_array($taskRows)) {
                            foreach ($taskRows as $idx => $taskRow) {
                                if (!is_array($taskRow)) continue;
                                $display = zgReadableValue($taskRow['display_name'] ?? $taskRow['name'] ?? '', $taskFields['display_name'] ?? $taskFields['name'] ?? []);
                                $source = 'Tarea' . ($display !== '' ? ' · ' . $display : '');
                                zgFlatten($bagRecord, $bagFields, 'task_' . $idx, $source, $taskRow, $taskFields);
                                foreach (['name','display_name','description','note'] as $textField) {
                                    if (isset($taskRow[$textField])) {
                                        $txt = zgReadableValue($taskRow[$textField], $taskFields[$textField] ?? []);
                                        if ($txt !== '') $relatedTexts[] = $txt;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (Throwable $ignored) {}
        }

        $name = zgReadableValue($r['name'] ?? '', $fields['name'] ?? []);
        $descriptionRaw = (string)($r['description'] ?? '');
        $description = zgClean(str_ireplace(['</p>','</div>','<br>','<br/>','<br />','</li>'], "\n", $descriptionRaw));
        $combined = trim($name . "\n" . $description . "\n" . implode("\n", $relatedTexts) . "\n" . implode("\n", array_map(fn($v) => zgReadableValue($v), $bagRecord)));

        $partnerName = zgReadableValue($r['partner_id'] ?? '', $fields['partner_id'] ?? []);
        $partnerAddress = '';
        $partnerPhone = '';
        $partnerEmail = '';
        $partnerVat = '';
        $partnerId = zgM2OId($r['partner_id'] ?? null);
        if ($partnerId > 0) {
            try {
                $partnerMeta = zgOdooExecuteKw($uid, 'res.partner', 'fields_get', [], ['attributes' => ['string','type']]);
                $partnerWanted = ['name','street','street2','city','zip','state_id','country_id','contact_address_complete','phone','mobile','email','vat','function','parent_id'];
                $partnerFields = array_values(array_filter($partnerWanted, fn($f) => is_array($partnerMeta) && isset($partnerMeta[$f])));
                $partnerRows = zgOdooExecuteKw($uid, 'res.partner', 'read', [[$partnerId]], ['fields' => $partnerFields]);
                if (is_array($partnerRows) && isset($partnerRows[0]) && is_array($partnerRows[0])) {
                    $p = $partnerRows[0];
                    $partnerName = zgClean((string)($p['name'] ?? $partnerName));
                    $partnerAddress = zgUsefulAddress((string)($p['contact_address_complete'] ?? ''));
                    if ($partnerAddress === '') {
                        $state = zgReadableValue($p['state_id'] ?? '', ['type'=>'many2one']);
                        $country = zgReadableValue($p['country_id'] ?? '', ['type'=>'many2one']);
                        $partnerAddress = zgUsefulAddress(implode(', ', array_filter([$p['street'] ?? '', $p['street2'] ?? '', $p['city'] ?? '', $state, $p['zip'] ?? '', $country], fn($v) => trim((string)$v) !== '')));
                    }
                    $partnerPhone = zgClean((string)($p['mobile'] ?? $p['phone'] ?? ''));
                    $partnerEmail = zgClean((string)($p['email'] ?? ''));
                    $partnerVat = zgClean((string)($p['vat'] ?? ''));
                }
            } catch (Throwable $ignored) {}
        }

        $defs = [
            'reporte' => ['numero de reporte','n de reporte','reporte','orden de servicio','orden de trabajo','service order','report number'],
            'cotizacion' => ['numero de cotizacion','n de cotizacion','cotizacion','quotation','presupuesto','sale order','orden de venta'],
            'direccion' => ['direccion del servicio','direccion','ubicacion del servicio','ubicacion','location','service address','address'],
            'fecha' => ['fecha de servicio','fecha servicio','service date','scheduled date','fecha programada'],
            'contenedor' => ['numero de contenedor','n de contenedor','contenedor','container number','container','numero de equipo','equipo numero','unidad'],
            'serial' => ['serial unidad','numero de serie','n de serie','serie unidad','serial','serie'],
            'marca' => ['marca del equipo','marca equipo','brand','marca'],
            'modelo' => ['modelo del equipo','modelo equipo','model','modelo'],
            'controlador' => ['controlador del equipo','controller','controlador'],
            'refrigerante' => ['refrigerante','refrigerant'],
            'tamano' => ['tamano del contenedor','tamaño del contenedor','container size','tamano','tamaño'],
            'modalidad' => ['modalidad comercial','condicion de los equipos','condición de los equipos','alquiler o venta','modalidad'],
            'tipo_equipo' => ['tipo de equipo','equipment type','clase de equipo'],
            'tipo_servicio' => ['tipo de servicio','tipo de ticket','ticket type','service type','tipo'],
            'anio' => ['ano de fabricacion','año de fabricación','manufacturing year','year'],
            'contacto' => ['contacto en planta','persona de contacto','contacto','contact person'],
            'telefono' => ['telefono de contacto','teléfono de contacto','telefono','teléfono','phone','mobile'],
            'correo' => ['correo de contacto','correo','email','e mail'],
        ];
        $picked = [];
        foreach ($defs as $key => $phrases) {
            $picked[$key] = zgFieldBest($bagRecord, $bagFields, $phrases, ['equipo de soporte','support team','asignado','cantidad','create date','write date']);
        }

        // El N° de reporte se asigna manualmente desde el panel.
        // La referencia numérica larga encontrada en el ticket se usa como cotización Odoo.
        $referenciaLarga = preg_replace('/\D+/', '', $picked['reporte']['value']);
        if (strlen($referenciaLarga) < 6 || strlen($referenciaLarga) > 15) $referenciaLarga = zgBestReportNumber($name, $combined, $ticketRef, $partnerVat);
        if ($referenciaLarga === '') $referenciaLarga = zgDigitsReport($combined, $ticketRef);
        $cotizacionOdoo = zgClean($picked['cotizacion']['value'] ?? '');
        if ($cotizacionOdoo === '') $cotizacionOdoo = zgLineValue($combined, ['N° de cotización','Nº de cotización','Número de cotización','Numero de cotizacion','Cotización','Cotizacion','Presupuesto']);
        if ($cotizacionOdoo === '' && $referenciaLarga !== '') $cotizacionOdoo = $referenciaLarga;
        $reporte = '';

        $direccion = $picked['direccion']['value'];
        if ($direccion === '') {
            $direccion = zgLineValue($combined, [
                'Dirección del servicio','Dirección','Direccion',
                'Ubicación del servicio','Ubicación','Ubicacion',
                'Lugar del servicio','Lugar'
            ]);
        }
        if ($direccion === '') {
            $direccion = zgInlineLabeledValue(
                $combined,
                [
                    'Dirección del servicio','Dirección','Direccion',
                    'Ubicación del servicio','Ubicación','Ubicacion',
                    'Lugar del servicio','Lugar'
                ],
                [
                    'Fecha de servicio','Fecha servicio','Hora de llegada',
                    'Contacto en planta','Cliente','Condición de los equipos',
                    'Tipo','Teléfono','Correo','Equipo','Contenedor',
                    'Número de reporte','N° de reporte','Cotización'
                ]
            );
        }
        if ($direccion === '') $direccion = $partnerAddress;
        $direccion = zgUsefulAddress($direccion);

        $fechaRaw = $picked['fecha']['value'];
        if ($fechaRaw === '') $fechaRaw = zgLineValue($combined, ['Fecha de servicio','Fecha servicio','Fecha programada']);
        $fecha = zgDateIso($fechaRaw !== '' ? $fechaRaw : $combined);

        $contenedor = strtoupper(zgClean($picked['contenedor']['value']));
        if ($contenedor === '') $contenedor = strtoupper(zgLineValue($combined, ['N° de contenedor','Nº de contenedor','Número de contenedor','Numero de contenedor','Contenedor','Equipo','Unidad']));
        if ($contenedor === '') $contenedor = zgContainerFromText($combined);
        $contenedor = zgEquipmentNumber($contenedor, $ticketRef, $reporte);
        if ($contenedor === '') {
            $detectado = zgContainerFromText($combined);
            $contenedor = zgEquipmentNumber($detectado, $ticketRef, $reporte);
        }

        $serial = strtoupper(zgClean($picked['serial']['value']));
        if ($serial === '') $serial = strtoupper(zgLineValue($combined, ['Serial','Serie','Serial unidad','Número de serie','Numero de serie']));

        $marca = strtoupper(zgClean($picked['marca']['value']));
        if ($marca === '') $marca = zgKnownBrand($combined);

        $controlador = strtoupper(zgClean($picked['controlador']['value']));
        if ($controlador === '') $controlador = zgKnownController($combined);

        $modeloEquipo = strtoupper(zgClean($picked['modelo']['value']));
        if ($modeloEquipo === '') $modeloEquipo = strtoupper(zgLineValue($combined, ['Modelo','Modelo del equipo']));

        $refrigerante = strtoupper(zgClean($picked['refrigerante']['value']));
        if ($refrigerante === '') $refrigerante = strtoupper(zgLineValue($combined, ['Refrigerante','Tipo de refrigerante']));

        $anio = preg_replace('/\D+/', '', $picked['anio']['value']);
        if (!preg_match('/^(19|20)\d{2}$/', $anio)) {
            $line = zgLineValue($combined, ['Año de fabricación','Ano de fabricacion','Año','Ano']);
            $anio = preg_match('/\b(19|20)\d{2}\b/', $line, $m) ? $m[0] : '';
        }

        $tamano = zgNormalizeSize($picked['tamano']['value']);
        if ($tamano === '') $tamano = zgNormalizeSize(zgLineValue($combined, ['Tamaño del contenedor','Tamano del contenedor','Tamaño','Tamano']));
        if ($tamano === '') $tamano = zgNormalizeSize($combined);

        $modalidadText = $picked['modalidad']['value'] . ' ' . $combined;
        $modalidad = '';
        if (preg_match('/\balquiler\b/iu', $modalidadText)) $modalidad = 'Alquiler';
        elseif (preg_match('/\bventa\b/iu', $modalidadText)) $modalidad = 'Venta';

        $typeText = $picked['tipo_equipo']['value'] . ' ' . $picked['tipo_servicio']['value'] . ' ' . $combined;
        $tipoEquipo = '';
        if (preg_match('/\b(genset|generador|grupo electrogeno|grupo electrógeno)\b/iu', $typeText)) $tipoEquipo = 'Genset';
        elseif (preg_match('/\b(reefer|contenedor refrigerado|refrigeracion|refrigeración)\b/iu', $typeText)) $tipoEquipo = 'Reefer';
        elseif ($contenedor !== '' || $serial !== '' || $marca !== '' || $modeloEquipo !== '' || $controlador !== '') $tipoEquipo = 'Reefer';

        $tipoInstalacion = '';
        if (preg_match('/\btunel\b|\btúnel\b/iu', $combined)) $tipoInstalacion = 'Túnel';
        elseif (preg_match('/\batmosfera controlada\b|\batmósfera controlada\b/iu', $combined)) $tipoInstalacion = 'Atmósfera controlada';
        elseif (preg_match('/\bmadurador\b/iu', $combined)) $tipoInstalacion = 'Madurador';
        elseif (preg_match('/\bunidad individual\b/iu', $combined)) $tipoInstalacion = 'Unidad individual';

        $team = zgReadableValue($r['team_id'] ?? '', $fields['team_id'] ?? []);
        $stage = zgReadableValue($r['stage_id'] ?? '', $fields['stage_id'] ?? []);
        $assigned = zgReadableValue($r['user_id'] ?? '', $fields['user_id'] ?? []);
        $tipoServicio = $picked['tipo_servicio']['value'];
        $contacto = $picked['contacto']['value'];
        if ($contacto === '') $contacto = zgLineValue($combined, ['Contacto en planta','Persona de contacto','Contacto']);
        $telefono = $picked['telefono']['value'] ?: $partnerPhone;
        if ($telefono === '') $telefono = zgLineValue($combined, ['Teléfono','Telefono','Celular','Móvil','Movil']);
        $correo = $picked['correo']['value'] ?: $partnerEmail;
        if ($correo === '' && preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', $combined, $mail)) $correo = $mail[0];

        $data = [
            'ticket_ref' => $ticketRef,
            'ticket_id' => $ticketId,
            'titulo_ticket' => $name,
            'descripcion_ticket' => $description,
            'equipo_soporte' => $team,
            'etapa' => $stage,
            'asignado_a' => $assigned,
            'tipo_servicio' => $tipoServicio,
            'reporte' => $reporte,
            'cotizacion' => $cotizacionOdoo,
            'cliente' => $partnerName,
            'contacto' => $contacto,
            'telefono' => zgClean($telefono),
            'correo' => zgClean($correo),
            'ruc' => $partnerVat,
            'direccion' => $direccion,
            'fecha' => $fecha,
            'tipo_equipo' => $tipoEquipo,
            'modalidad_comercial' => $modalidad,
            'tipo_instalacion' => $tipoInstalacion,
            'tamano_contenedor' => $tamano,
            'numero_equipo' => $contenedor,
            'serie_unidad' => $serial,
            'marca_equipo' => $marca,
            'modelo_equipo' => $modeloEquipo,
            'controlador' => $controlador,
            'anio_fabricacion' => $anio,
            'refrigerante' => $refrigerante,
        ];

        $details = [];
        $seen = [];
        $detailMap = [
            'Asunto del ticket' => $name,
            'Cliente' => $partnerName,
            'RUC / documento' => $partnerVat,
            'Contacto' => $contacto,
            'Teléfono' => $data['telefono'],
            'Correo' => $data['correo'],
            'Dirección' => $direccion,
            'N.° de reporte' => $reporte,
            'Fecha de servicio' => $fecha,
            'Equipo de soporte' => $team,
            'Asignado a' => $assigned,
            'Etapa' => $stage,
            'Tipo de servicio' => $tipoServicio,
            'Tipo de equipo' => $tipoEquipo,
            'Modalidad comercial' => $modalidad,
            'Tipo de instalación' => $tipoInstalacion,
            'Tamaño del contenedor' => $tamano,
            'Contenedor / equipo' => $contenedor,
            'Serial de unidad' => $serial,
            'Marca' => $marca,
            'Modelo' => $modeloEquipo,
            'Controlador' => $controlador,
            'Año de fabricación' => $anio,
            'Refrigerante' => $refrigerante,
        ];
        foreach ($detailMap as $label => $value) zgAddDetail($details, $seen, $label, (string)$value);

        $skipTokens = ['id','create','write','message','activity','follower','attachment','access','rating','sequence','color','company','active','website','kanban','sla'];
        $standardTicketKeys = ['ticket__ticket_ref','ticket__name','ticket__description','ticket__partner_id','ticket__team_id','ticket__stage_id','ticket__user_id'];
        foreach ($bagFields as $key => $meta) {
            if (count($details) >= 45) break;
            if (in_array($key, $standardTicketKeys, true)) continue;
            $value = zgReadableValue($bagRecord[$key] ?? '', is_array($meta) ? $meta : []);
            if ($value === '' || strlen($value) > 350) continue;
            $rawLabel = (string)($meta['string'] ?? $key);
            $normLabel = zgNorm($rawLabel);
            $skip = false;
            foreach ($skipTokens as $token) if (str_contains($normLabel, $token)) { $skip = true; break; }
            if ($skip) continue;
            zgAddDetail($details, $seen, $rawLabel, $value);
        }


    return $data;
}
