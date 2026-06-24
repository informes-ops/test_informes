<?php
/* ============================================================
   ZGROUP V53 - redacción técnica con Claude, opciones y memoria por trabajo
   - Usa la API de Anthropic mediante Messages API.
   - No usa plantillas locales ni texto de respaldo.
   - Si Claude falla, devuelve el error y conserva el texto original.
   - La clave permanece solo en el servidor.
   ============================================================ */

session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function zgIaResponder(array $data, int $status = 200): void {
    while (ob_get_level()) ob_end_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function zgIaTexto($value, int $max = 3000): string {
    $value = trim((string)$value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max);
}
function zgIaLista($value, int $maxItems = 30, int $maxLen = 180): array {
    if (!is_array($value)) return [];
    $out = [];
    foreach (array_slice($value, 0, $maxItems) as $item) {
        $item = zgIaTexto($item, $maxLen);
        if ($item !== '') $out[] = $item;
    }
    return array_values(array_unique($out));
}
function zgIaMateriales($value, int $maxItems = 40): array {
    if (!is_array($value)) return [];
    $out = [];
    $seen = [];
    foreach (array_slice($value, 0, $maxItems) as $item) {
        if (is_string($item)) {
            $detalle = zgIaTexto($item, 260);
            $codigo = '';
            $cantidad = '1';
            $unidad = 'und';
        } elseif (is_array($item)) {
            $codigo = zgIaTexto($item['codigo'] ?? $item['code'] ?? '', 80);
            $detalle = zgIaTexto($item['detalle'] ?? $item['material'] ?? $item['nombre'] ?? $item['descripcion'] ?? '', 260);
            $cantidad = zgIaTexto($item['cantidad'] ?? $item['qty'] ?? '1', 30);
            $unidad = zgIaTexto($item['unidad'] ?? $item['unit'] ?? 'und', 40);
        } else {
            continue;
        }
        if ($detalle === '') continue;
        if (preg_match('/^sin c[oó]digo$/iu', $codigo)) $codigo = '';
        if ($cantidad === '') $cantidad = '1';
        if ($unidad === '') $unidad = 'und';
        $key = mb_strtoupper($codigo . '|' . $detalle . '|' . $cantidad . '|' . $unidad, 'UTF-8');
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $out[] = ['codigo'=>$codigo,'detalle'=>$detalle,'cantidad'=>$cantidad,'unidad'=>$unidad];
    }
    return $out;
}
function zgIaExtraerTextoClaude(array $json): string {
    $parts = [];
    foreach (($json['content'] ?? []) as $content) {
        if (!is_array($content)) continue;
        if (($content['type'] ?? '') === 'text' && isset($content['text'])) {
            $parts[] = (string)$content['text'];
        }
    }
    return trim(implode("\n", $parts));
}
function zgIaLog(string $message, array $extra = []): void {
    // Nunca registrar la clave ni el texto completo del técnico.
    $row = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($extra) $row .= ' | ' . json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @file_put_contents(__DIR__ . '/claude_ia_debug.log', $row . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    zgIaResponder(['ok'=>false,'error'=>'Método no permitido.'],405);
}
$raw = file_get_contents('php://input');
$input = json_decode((string)$raw, true);
if (!is_array($input)) zgIaResponder(['ok'=>false,'error'=>'Solicitud inválida.'],400);

$preIdAuth = (int)($input['preinspeccion_id'] ?? 0);
$tokenAuth = trim((string)($input['token_continuacion'] ?? ''));
$autorizadoIa = !empty($_SESSION['zgroup_tecnicos_ok']) || !empty($_SESSION['panel_ok']);
if (!$autorizadoIa && ($preIdAuth > 0 || $tokenAuth !== '')) {
    try {
        require __DIR__ . '/db.php';
        if (isset($pdo) && $pdo instanceof PDO) {
            $sqlAuth = "SELECT id FROM inspecciones_preliminares WHERE 1=1";
            $paramsAuth = [];
            if ($preIdAuth > 0) { $sqlAuth .= " AND id = ?"; $paramsAuth[] = $preIdAuth; }
            if ($tokenAuth !== '') { $sqlAuth .= " AND token_continuacion = ?"; $paramsAuth[] = $tokenAuth; }
            $sqlAuth .= " LIMIT 1";
            $stAuth = $pdo->prepare($sqlAuth);
            $stAuth->execute($paramsAuth);
            $autorizadoIa = (bool)$stAuth->fetchColumn();
        }
    } catch (Throwable $e) {}
}
if (!$autorizadoIa) zgIaResponder(['ok'=>false,'error'=>'La sesión venció o el enlace del servicio no es válido.'],403);

$modo = strtolower(zgIaTexto($input['modo'] ?? 'detalle_tecnico', 60));
$categoriaOpcion = strtolower(zgIaTexto($input['categoria'] ?? '', 40));
$trabajoClave = zgIaTexto($input['trabajo_clave'] ?? '', 100);
$memoriaTrabajo = zgIaLista($input['memoria_trabajo'] ?? [], 40, 220);
$texto = zgIaTexto($input['texto'] ?? '', 3000);
$etiqueta = zgIaTexto($input['etiqueta'] ?? 'Observación técnica', 180);
$tipoEquipoRaw = zgIaTexto($input['tipo_equipo'] ?? '', 80);
$trabajo = zgIaTexto($input['trabajo'] ?? '', 180);
$actividades = zgIaLista($input['actividades'] ?? []);
$hallazgos = zgIaLista($input['hallazgos'] ?? []);
$acciones = zgIaLista($input['acciones'] ?? []);
$materiales = zgIaMateriales($input['materiales'] ?? []);
$antecedentes = zgIaLista($input['antecedentes'] ?? [], 80, 320);

$textoLen = function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
$hayContextoTecnico = !empty($actividades) || !empty($hallazgos) || !empty($acciones) || !empty($materiales) || !empty($antecedentes) || !empty($memoriaTrabajo);
if ($texto === '' && !$hayContextoTecnico) {
    zgIaResponder(['ok'=>false,'error'=>'Registra una nota o completa datos técnicos para elaborar la redacción.'],422);
}
$minTexto = ($modo === 'opcion_tecnica') ? 3 : 5;
if ($texto !== '' && $textoLen < $minTexto && !$hayContextoTecnico) {
    zgIaResponder(['ok'=>false,'error'=>'Agrega una precisión breve o selecciona datos técnicos para preparar la redacción.'],422);
}

$tipoNormal = function_exists('mb_strtolower') ? mb_strtolower($tipoEquipoRaw,'UTF-8') : strtolower($tipoEquipoRaw);
$esGenerador = str_contains($tipoNormal,'genset') || str_contains($tipoNormal,'generador');
$tipoEquipo = $esGenerador ? 'GENERADOR / GENSET' : 'MÁQUINA REEFER / CONTENEDOR REFRIGERADO';

$configPath = __DIR__ . '/claude_config.php';
if (is_file($configPath)) require $configPath;
$apiKey = defined('ANTHROPIC_API_KEY') ? trim((string)ANTHROPIC_API_KEY) : trim((string)getenv('ANTHROPIC_API_KEY'));
$model = defined('ANTHROPIC_MODEL') ? trim((string)ANTHROPIC_MODEL) : trim((string)getenv('ANTHROPIC_MODEL'));
if ($model === '') $model = 'claude-haiku-4-5';

if ($apiKey === '' || str_contains($apiKey,'PEGA_AQUI') || str_contains($apiKey,'TU_CLAVE')) {
    zgIaResponder(['ok'=>false,'error'=>'La clave de Claude no está configurada en claude_config.php.'],503);
}
if (!str_starts_with($apiKey, 'sk-ant-')) {
    zgIaResponder(['ok'=>false,'error'=>'La clave configurada no parece ser una clave válida de Anthropic.'],503);
}
if (!function_exists('curl_init')) {
    zgIaResponder(['ok'=>false,'error'=>'El hosting no tiene habilitada la extensión cURL de PHP.'],500);
}

// Protección básica del consumo por sesión.
$ahora=time(); $ventana=600; $maxSolicitudes=30;
$historial=$_SESSION['zg_ia_redaccion_usos'] ?? [];
$historial=array_values(array_filter((array)$historial, static fn($t)=>is_numeric($t)&&(int)$t>$ahora-$ventana));
if(count($historial)>=$maxSolicitudes) zgIaResponder(['ok'=>false,'error'=>'Se alcanzó el límite temporal de mejoras. Intenta nuevamente en unos minutos.'],429);
$historial[]=$ahora; $_SESSION['zg_ia_redaccion_usos']=$historial;

$contexto = [
    'TIPO DE EQUIPO: ' . $tipoEquipo,
    'CAMPO: ' . $etiqueta,
];
if ($trabajo !== '') $contexto[] = 'TRABAJO: ' . $trabajo;
if ($trabajoClave !== '') $contexto[] = 'CLAVE INTERNA DEL TRABAJO: ' . $trabajoClave;
if ($actividades) $contexto[] = 'ACTIVIDADES MARCADAS (solo contexto): ' . implode(', ', $actividades);
if ($hallazgos) $contexto[] = 'HALLAZGOS MARCADOS (solo contexto): ' . implode(', ', $hallazgos);
if ($acciones) $contexto[] = 'ACCIONES MARCADAS (solo contexto): ' . implode(', ', $acciones);
if ($memoriaTrabajo) {
    $contexto[] = 'ANTECEDENTES DE REDACCIÓN USADOS ANTERIORMENTE EN ESTE MISMO TIPO DE TRABAJO (solo vocabulario y estilo; no asumir que ocurrieron hoy):';
    foreach ($memoriaTrabajo as $dato) $contexto[] = '- ' . $dato;
}
if ($antecedentes) {
    $contexto[] = 'DATOS REGISTRADOS PREVIAMENTE EN EL SERVICIO (usar solo los que sean pertinentes):';
    foreach ($antecedentes as $dato) $contexto[] = '- ' . $dato;
}
if ($materiales) {
    $contexto[] = 'MATERIALES / REPUESTOS REGISTRADOS PARA ESTE TRABAJO:';
    foreach ($materiales as $m) {
        $partes = [];
        if ($m['codigo'] !== '') $partes[] = 'Código: ' . $m['codigo'];
        $partes[] = 'Descripción: ' . $m['detalle'];
        $partes[] = 'Cantidad: ' . $m['cantidad'];
        $partes[] = 'Unidad: ' . $m['unidad'];
        $contexto[] = '- ' . implode(' | ', $partes);
    }
}

$esOpcionTecnica = ($modo === 'opcion_tecnica');
if ($esOpcionTecnica) {
    $categoriaFinal = $categoriaOpcion === 'hallazgos' ? 'HALLAZGO ENCONTRADO' : 'ACTIVIDAD REALIZADA';
    $system = <<<'TXT'
Actúa como asistente de redacción técnica para mantenimiento industrial.
Debes mejorar una sola opción breve que el técnico desea agregar a “Actividades realizadas” o “Hallazgos encontrados”.

Reglas obligatorias:
- Mantén el tipo de equipo y el trabajo indicado. Nunca mezcles máquina reefer con generador/genset.
- No inventes fallas, mediciones, repuestos, causas, resultados ni acciones.
- Usa exclusivamente la idea escrita por el técnico; los antecedentes del mismo trabajo sirven solo para usar vocabulario consistente.
- Para ACTIVIDAD REALIZADA, redacta una acción concreta ya ejecutada, preferentemente iniciando con “Se realizó”, “Se ajustó”, “Se reemplazó”, “Se limpió”, “Se reparó” u otra forma técnica adecuada.
- Para HALLAZGO ENCONTRADO, redacta una condición observada, sin afirmar que fue corregida.
- Corrige ortografía y vuelve la frase clara y específica.
- Devuelve una sola frase de 6 a 28 palabras.
- No devuelvas párrafos, listas, títulos, comillas ni explicaciones.
- Si el texto no contiene una idea técnica comprensible, responde exactamente: NECESITO_MAS_DETALLE
TXT;
    $userInput = implode("\n", $contexto)
        . "\n\nCATEGORÍA A REDACTAR: " . $categoriaFinal
        . "\nNOTA BREVE DEL TÉCNICO:\n" . ($texto !== '' ? $texto : 'Sin información suficiente.');
    $maxTokens = 140;
} else {
    $system = <<<'TXT'
Actúa como redactor de informes técnicos de mantenimiento industrial.
Tu tarea es elaborar o mejorar un único detalle técnico usando las notas del técnico y los datos ya registrados en el servicio.

Reglas obligatorias:
- Mantén el tipo de equipo indicado. Nunca mezcles máquina reefer con generador o genset.
- Conserva todos los hechos del texto original y no inventes fallas, mediciones, repuestos, causas, trabajos ni resultados.
- Corrige ortografía y ordena las ideas con vocabulario técnico claro para el cliente.
- Las actividades, hallazgos, acciones, materiales, parámetros preliminares y resultados de la lista de inspección son contexto técnico disponible.
- Los antecedentes del mismo trabajo sirven para mantener vocabulario consistente, pero nunca significan que esas acciones ocurrieron en el servicio actual.
- Para Asistencia técnica o Mantenimiento correctivo, integra en un solo párrafo la condición atendida, las acciones ejecutadas, las mediciones relevantes y el resultado, únicamente cuando esos datos estén registrados.
- Si el campo está vacío pero los antecedentes del servicio actual son suficientes, redacta directamente el detalle técnico.
- No menciones que usaste antecedentes, datos previos, formulario, checklist o contexto.
- Si las notas indican que se cambió, reemplazó, instaló, utilizó o revisó un componente, identifica el componente usando su código y descripción en la lista de materiales.
- Conserva los códigos y referencias exactos.
- No afirmes que un material fue reemplazado únicamente porque aparece en la lista. Debe existir una nota o una actividad marcada que lo indique.
- No agregues frases genéricas repetitivas.
- Devuelve un único párrafo breve, natural y profesional, de 1 a 4 oraciones.
- Devuelve solamente la redacción final, sin títulos, comillas, explicaciones ni viñetas.
- Si las notas no son comprensibles o no contienen información suficiente, responde exactamente: NECESITO_MAS_DETALLE
TXT;
    $userInput = implode("\n", $contexto) . "\n\nNOTAS ORIGINALES DEL TÉCNICO:\n" . ($texto !== '' ? $texto : 'Sin nota adicional; elaborar el detalle únicamente con los datos registrados.');
    $maxTokens = 520;
}
$payload = [
    'model' => $model,
    'max_tokens' => $maxTokens,
    'temperature' => 0.2,
    'system' => $system,
    'messages' => [[
        'role' => 'user',
        'content' => $userInput,
    ]],
];

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch,[
    CURLOPT_POST=>true,
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_CONNECTTIMEOUT=>15,
    CURLOPT_TIMEOUT=>60,
    CURLOPT_HTTPHEADER=>[
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
    ],
    CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
]);
$responseBody=curl_exec($ch);
$curlError=curl_error($ch);
$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
curl_close($ch);

if($responseBody===false || $curlError!==''){
    zgIaLog('Error cURL',['error'=>$curlError]);
    zgIaResponder(['ok'=>false,'error'=>'No se pudo conectar con Claude desde el hosting. Revisa cURL, SSL o la conexión saliente.'],502);
}
$json=json_decode((string)$responseBody,true);
if(!is_array($json)){
    zgIaLog('Respuesta no JSON',['status'=>$status,'body'=>substr((string)$responseBody,0,300)]);
    zgIaResponder(['ok'=>false,'error'=>'Claude devolvió una respuesta inválida.'],502);
}
if($status<200 || $status>=300){
    $apiMessage=zgIaTexto($json['error']['message'] ?? '',300);
    $apiCode=zgIaTexto($json['error']['type'] ?? '',80);
    zgIaLog('Error API',['status'=>$status,'code'=>$apiCode,'message'=>$apiMessage,'model'=>$model]);
    $public='No se pudo usar Claude.';
    if($status===401) $public='La clave de Claude es inválida o fue revocada.';
    elseif($status===429) $public='La cuenta de Claude no tiene créditos disponibles o alcanzó su límite temporal.';
    elseif($status===403) $public='La clave no tiene permiso para usar este modelo o espacio de trabajo.';
    elseif($status===404) $public='El modelo de Claude configurado no está disponible para esta cuenta.';
    elseif($apiMessage!=='') $public=$apiMessage;
    zgIaResponder(['ok'=>false,'error'=>$public,'api_status'=>$status],502);
}

$mejorado=zgIaTexto(zgIaExtraerTextoClaude($json), $esOpcionTecnica ? 500 : 3500);
if($mejorado==='NECESITO_MAS_DETALLE'){
    zgIaResponder(['ok'=>false,'error'=>'Escribe un poco más de detalle para que Claude pueda elaborar una redacción útil.'],422);
}
if($mejorado===''){
    zgIaLog('Salida vacía',['status'=>$status,'model'=>$model,'response_id'=>$json['id'] ?? '']);
    zgIaResponder(['ok'=>false,'error'=>'Claude no devolvió texto. Intenta nuevamente.'],502);
}

zgIaLog('Solicitud correcta',['status'=>$status,'model'=>$model,'response_id'=>$json['id'] ?? '']);
zgIaResponder([
    'ok'=>true,
    'source'=>'anthropic',
    'texto'=>$mejorado,
    'model'=>$model,
    'response_id'=>$json['id'] ?? null,
]);
