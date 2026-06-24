<?php
session_start();
ob_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
date_default_timezone_set('America/Lima');

function zgImpJson(array $data, int $status = 200): void {
    while (ob_get_level()) ob_end_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function zgImpClean($value, int $max = 255): string {
    $value = trim(preg_replace('/\s+/u', ' ', (string)$value));
    return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max);
}
function zgImpCol(PDO $pdo, string $table, string $column): bool {
    $st=$pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    $st->execute([$table,$column]); return (int)$st->fetchColumn()>0;
}
function zgImpAdd(PDO $pdo, string $table, string $column, string $definition): void {
    if(!zgImpCol($pdo,$table,$column)) $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
}
function zgImpEnsure(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS clientes_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(180) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_clientes_catalogo_nombre (nombre)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    foreach([
        'ruc'=>"VARCHAR(30) DEFAULT NULL",
        'contacto'=>"VARCHAR(160) DEFAULT NULL",
        'telefono'=>"VARCHAR(80) DEFAULT NULL",
        'correo'=>"VARCHAR(180) DEFAULT NULL",
        'direccion'=>"VARCHAR(255) DEFAULT NULL",
        'origen'=>"VARCHAR(30) DEFAULT NULL"
    ] as $c=>$d) zgImpAdd($pdo,'clientes_catalogo',$c,$d);

    $pdo->exec("CREATE TABLE IF NOT EXISTS cotizaciones_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cotizacion VARCHAR(30) NOT NULL UNIQUE,
        cliente_id INT DEFAULT NULL,
        cliente_nombre VARCHAR(180) DEFAULT NULL,
        descripcion VARCHAR(220) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_cotizaciones_catalogo_cotizacion (cotizacion),
        INDEX idx_cotizaciones_catalogo_cliente (cliente_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    foreach([
        'ticket_ref'=>"VARCHAR(30) DEFAULT NULL",
        'cotizacion_odoo'=>"VARCHAR(80) DEFAULT NULL",
        'origen'=>"VARCHAR(30) DEFAULT NULL"
    ] as $c=>$d) zgImpAdd($pdo,'cotizaciones_catalogo',$c,$d);

    $pdo->exec("CREATE TABLE IF NOT EXISTS contenedores_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero VARCHAR(60) NOT NULL UNIQUE,
        serial_unidad VARCHAR(100) DEFAULT NULL,
        marca_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,
        refrigerante VARCHAR(50) DEFAULT NULL,
        descripcion VARCHAR(220) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_contenedores_catalogo_numero (numero)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    foreach([
        'modelo_equipo'=>"VARCHAR(100) DEFAULT NULL",
        'anio_fabricacion'=>"SMALLINT UNSIGNED DEFAULT NULL",
        'tamano_contenedor'=>"VARCHAR(60) DEFAULT NULL",
        'modalidad_comercial'=>"VARCHAR(40) DEFAULT NULL",
        'tipo_equipo'=>"VARCHAR(30) DEFAULT NULL",
        'ticket_ref'=>"VARCHAR(30) DEFAULT NULL",
        'cliente_nombre'=>"VARCHAR(180) DEFAULT NULL",
        'origen'=>"VARCHAR(30) DEFAULT NULL"
    ] as $c=>$d) zgImpAdd($pdo,'contenedores_catalogo',$c,$d);

    $pdo->exec("CREATE TABLE IF NOT EXISTS maquinas_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serial_unidad VARCHAR(100) NOT NULL UNIQUE,
        marca_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,
        refrigerante VARCHAR(50) DEFAULT NULL,
        descripcion VARCHAR(220) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_maquinas_catalogo_serial (serial_unidad)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    foreach([
        'modelo_equipo'=>"VARCHAR(100) DEFAULT NULL",
        'anio_fabricacion'=>"SMALLINT UNSIGNED DEFAULT NULL",
        'numero_equipo'=>"VARCHAR(60) DEFAULT NULL",
        'ticket_ref'=>"VARCHAR(30) DEFAULT NULL",
        'cliente_nombre'=>"VARCHAR(180) DEFAULT NULL",
        'origen'=>"VARCHAR(30) DEFAULT NULL"
    ] as $c=>$d) zgImpAdd($pdo,'maquinas_catalogo',$c,$d);

    $pdo->exec("CREATE TABLE IF NOT EXISTS odoo_servicios_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_ref VARCHAR(30) NOT NULL UNIQUE,
        odoo_ticket_id INT DEFAULT NULL,
        numero_reporte VARCHAR(30) DEFAULT NULL,
        cotizacion VARCHAR(80) DEFAULT NULL,
        cliente_id INT DEFAULT NULL,
        cliente_nombre VARCHAR(180) DEFAULT NULL,
        ruc VARCHAR(30) DEFAULT NULL,
        contacto VARCHAR(160) DEFAULT NULL,
        telefono VARCHAR(80) DEFAULT NULL,
        correo VARCHAR(180) DEFAULT NULL,
        direccion VARCHAR(255) DEFAULT NULL,
        fecha_servicio DATE DEFAULT NULL,
        equipo_soporte VARCHAR(120) DEFAULT NULL,
        asignado_a VARCHAR(160) DEFAULT NULL,
        tipo_servicio VARCHAR(160) DEFAULT NULL,
        modalidad_comercial VARCHAR(40) DEFAULT NULL,
        tipo_instalacion VARCHAR(80) DEFAULT NULL,
        tipo_equipo VARCHAR(30) DEFAULT NULL,
        tamano_contenedor VARCHAR(60) DEFAULT NULL,
        numero_equipo VARCHAR(60) DEFAULT NULL,
        serie_unidad VARCHAR(100) DEFAULT NULL,
        marca_equipo VARCHAR(100) DEFAULT NULL,
        modelo_equipo VARCHAR(100) DEFAULT NULL,
        controlador VARCHAR(100) DEFAULT NULL,
        anio_fabricacion SMALLINT UNSIGNED DEFAULT NULL,
        refrigerante VARCHAR(50) DEFAULT NULL,
        titulo_ticket VARCHAR(255) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        importado_en DATETIME NOT NULL,
        actualizado_en DATETIME NOT NULL,
        INDEX idx_odoo_servicio_cliente (cliente_id),
        INDEX idx_odoo_servicio_reporte (numero_reporte),
        INDEX idx_odoo_servicio_equipo (numero_equipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function zgImpClient(PDO $pdo, array $d): int {
    $name=zgImpClean($d['cliente']??'',180); if($name==='') throw new RuntimeException('El ticket no tiene un cliente utilizable.');
    $st=$pdo->prepare('SELECT id FROM clientes_catalogo WHERE LOWER(nombre)=LOWER(?) LIMIT 1'); $st->execute([$name]);
    $id=(int)$st->fetchColumn();
    if($id>0){
        $up=$pdo->prepare('UPDATE clientes_catalogo SET nombre=?,ruc=?,contacto=?,telefono=?,correo=?,direccion=?,origen=\'odoo\',activo=1 WHERE id=?');
        $up->execute([$name,zgImpClean($d['ruc']??'',30)?:null,zgImpClean($d['contacto']??'',160)?:null,zgImpClean($d['telefono']??'',80)?:null,zgImpClean($d['correo']??'',180)?:null,zgImpClean($d['direccion']??'',255)?:null,$id]);
        return $id;
    }
    $ins=$pdo->prepare('INSERT INTO clientes_catalogo(nombre,ruc,contacto,telefono,correo,direccion,origen,activo) VALUES(?,?,?,?,?,?,\'odoo\',1)');
    $ins->execute([$name,zgImpClean($d['ruc']??'',30)?:null,zgImpClean($d['contacto']??'',160)?:null,zgImpClean($d['telefono']??'',80)?:null,zgImpClean($d['correo']??'',180)?:null,zgImpClean($d['direccion']??'',255)?:null]);
    return (int)$pdo->lastInsertId();
}
function zgImpSave(PDO $pdo, array $d): array {
    $clientId=zgImpClient($pdo,$d);
    $client=zgImpClean($d['cliente']??'',180);
    $ticket=preg_replace('/\D+/','',(string)($d['ticket_ref']??''));
    $existingReport='';
    if($ticket!==''){
        $stExisting=$pdo->prepare("SELECT COALESCE(numero_reporte,'') FROM odoo_servicios_catalogo WHERE ticket_ref=? LIMIT 1");
        $stExisting->execute([$ticket]);
        $existingReport=preg_replace('/\D+/','',(string)$stExisting->fetchColumn());
    }
    // El número de reporte lo asigna manualmente el supervisor desde panel.php.
    $report=$existingReport;
    $quote=zgImpClean($d['cotizacion']??'',80);
    $number=zgImpClean($d['numero_equipo']??'',60);
    $serial=zgImpClean($d['serie_unidad']??'',100);
    $brand=zgImpClean($d['marca_equipo']??'',100);
    $model=zgImpClean($d['modelo_equipo']??'',100);
    $controller=zgImpClean($d['controlador']??'',100);
    $ref=zgImpClean($d['refrigerante']??'',50);
    $year=preg_match('/^(19|20)\d{2}$/',(string)($d['anio_fabricacion']??''))?(int)$d['anio_fabricacion']:null;

    if($report!==''){
        $st=$pdo->prepare("INSERT INTO cotizaciones_catalogo(cotizacion,cliente_id,cliente_nombre,descripcion,ticket_ref,cotizacion_odoo,origen,activo)
            VALUES(?,?,?,'Importado desde Odoo',?,?, 'odoo',1)
            ON DUPLICATE KEY UPDATE cliente_id=VALUES(cliente_id),cliente_nombre=VALUES(cliente_nombre),ticket_ref=VALUES(ticket_ref),cotizacion_odoo=VALUES(cotizacion_odoo),origen='odoo',activo=1");
        $st->execute([$report,$clientId,$client,$ticket?:null,$quote?:null]);
    }
    if($number!=='' && strcasecmp((string)($d['tipo_equipo']??''),'Genset')!==0){
        $st=$pdo->prepare("INSERT INTO contenedores_catalogo(numero,serial_unidad,marca_equipo,modelo_equipo,controlador,anio_fabricacion,refrigerante,tamano_contenedor,modalidad_comercial,tipo_equipo,ticket_ref,cliente_nombre,descripcion,origen,activo)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,'odoo',1)
            ON DUPLICATE KEY UPDATE serial_unidad=COALESCE(NULLIF(VALUES(serial_unidad),''),serial_unidad),marca_equipo=COALESCE(NULLIF(VALUES(marca_equipo),''),marca_equipo),modelo_equipo=COALESCE(NULLIF(VALUES(modelo_equipo),''),modelo_equipo),controlador=COALESCE(NULLIF(VALUES(controlador),''),controlador),anio_fabricacion=COALESCE(VALUES(anio_fabricacion),anio_fabricacion),refrigerante=COALESCE(NULLIF(VALUES(refrigerante),''),refrigerante),tamano_contenedor=COALESCE(NULLIF(VALUES(tamano_contenedor),''),tamano_contenedor),modalidad_comercial=COALESCE(NULLIF(VALUES(modalidad_comercial),''),modalidad_comercial),tipo_equipo=COALESCE(NULLIF(VALUES(tipo_equipo),''),tipo_equipo),ticket_ref=VALUES(ticket_ref),cliente_nombre=VALUES(cliente_nombre),origen='odoo',activo=1");
        $st->execute([$number,$serial?:null,$brand?:null,$model?:null,$controller?:null,$year,$ref?:null,zgImpClean($d['tamano_contenedor']??'',60)?:null,zgImpClean($d['modalidad_comercial']??'',40)?:null,zgImpClean($d['tipo_equipo']??'',30)?:null,$ticket?:null,$client,'Importado desde Odoo']);
    }
    if($serial!==''){
        $st=$pdo->prepare("INSERT INTO maquinas_catalogo(serial_unidad,marca_equipo,modelo_equipo,controlador,anio_fabricacion,refrigerante,numero_equipo,ticket_ref,cliente_nombre,descripcion,origen,activo)
            VALUES(?,?,?,?,?,?,?,?,?,?,'odoo',1)
            ON DUPLICATE KEY UPDATE marca_equipo=COALESCE(NULLIF(VALUES(marca_equipo),''),marca_equipo),modelo_equipo=COALESCE(NULLIF(VALUES(modelo_equipo),''),modelo_equipo),controlador=COALESCE(NULLIF(VALUES(controlador),''),controlador),anio_fabricacion=COALESCE(VALUES(anio_fabricacion),anio_fabricacion),refrigerante=COALESCE(NULLIF(VALUES(refrigerante),''),refrigerante),numero_equipo=COALESCE(NULLIF(VALUES(numero_equipo),''),numero_equipo),ticket_ref=VALUES(ticket_ref),cliente_nombre=VALUES(cliente_nombre),origen='odoo',activo=1");
        $st->execute([$serial,$brand?:null,$model?:null,$controller?:null,$year,$ref?:null,$number?:null,$ticket?:null,$client,'Importado desde Odoo']);
    }

    $sql="INSERT INTO odoo_servicios_catalogo(ticket_ref,odoo_ticket_id,numero_reporte,cotizacion,cliente_id,cliente_nombre,ruc,contacto,telefono,correo,direccion,fecha_servicio,equipo_soporte,asignado_a,tipo_servicio,modalidad_comercial,tipo_instalacion,tipo_equipo,tamano_contenedor,numero_equipo,serie_unidad,marca_equipo,modelo_equipo,controlador,anio_fabricacion,refrigerante,titulo_ticket,activo,importado_en,actualizado_en)
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,NOW(),NOW())
        ON DUPLICATE KEY UPDATE odoo_ticket_id=VALUES(odoo_ticket_id),numero_reporte=COALESCE(NULLIF(odoo_servicios_catalogo.numero_reporte,''),VALUES(numero_reporte)),cotizacion=VALUES(cotizacion),cliente_id=VALUES(cliente_id),cliente_nombre=VALUES(cliente_nombre),ruc=VALUES(ruc),contacto=VALUES(contacto),telefono=VALUES(telefono),correo=VALUES(correo),direccion=VALUES(direccion),fecha_servicio=VALUES(fecha_servicio),equipo_soporte=VALUES(equipo_soporte),asignado_a=VALUES(asignado_a),tipo_servicio=VALUES(tipo_servicio),modalidad_comercial=VALUES(modalidad_comercial),tipo_instalacion=VALUES(tipo_instalacion),tipo_equipo=VALUES(tipo_equipo),tamano_contenedor=VALUES(tamano_contenedor),numero_equipo=VALUES(numero_equipo),serie_unidad=VALUES(serie_unidad),marca_equipo=VALUES(marca_equipo),modelo_equipo=VALUES(modelo_equipo),controlador=VALUES(controlador),anio_fabricacion=VALUES(anio_fabricacion),refrigerante=VALUES(refrigerante),titulo_ticket=VALUES(titulo_ticket),activo=1,actualizado_en=NOW()";
    $st=$pdo->prepare($sql);
    $st->execute([
        $ticket,(int)($d['ticket_id']??0)?:null,$report?:null,$quote?:null,$clientId,$client,
        zgImpClean($d['ruc']??'',30)?:null,zgImpClean($d['contacto']??'',160)?:null,zgImpClean($d['telefono']??'',80)?:null,zgImpClean($d['correo']??'',180)?:null,zgImpClean($d['direccion']??'',255)?:null,
        preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)($d['fecha']??''))?$d['fecha']:null,
        zgImpClean($d['equipo_soporte']??'',120)?:null,zgImpClean($d['asignado_a']??'',160)?:null,zgImpClean($d['tipo_servicio']??'',160)?:null,
        zgImpClean($d['modalidad_comercial']??'',40)?:null,zgImpClean($d['tipo_instalacion']??'',80)?:null,zgImpClean($d['tipo_equipo']??'',30)?:null,zgImpClean($d['tamano_contenedor']??'',60)?:null,
        $number?:null,$serial?:null,$brand?:null,$model?:null,$controller?:null,$year,$ref?:null,zgImpClean($d['titulo_ticket']??'',255)?:null
    ]);
    return ['ticket_ref'=>$ticket,'numero_reporte'=>$report,'cotizacion'=>$quote,'cliente'=>$client,'numero_equipo'=>$number];
}

if(empty($_SESSION['panel_ok'])){
    if($_SERVER['REQUEST_METHOD']==='POST') zgImpJson(['ok'=>false,'error'=>'Sesión del panel vencida.'],403);
    header('Location: panel.php'); exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        require __DIR__.'/db.php';
        require_once __DIR__.'/odoo_ticket_reader.php';
        if(!isset($pdo)||!($pdo instanceof PDO)) throw new RuntimeException('No se encontró la conexión MySQL.');
        $ticket=trim((string)($_POST['ticket_ref']??''));
        $data=zgoLeerTicket($ticket);
        zgImpEnsure($pdo);
        $pdo->beginTransaction();
        $saved=zgImpSave($pdo,$data);
        if ($pdo->inTransaction()) $pdo->commit();
        zgImpJson(['ok'=>true,'data'=>$saved]);
    }catch(Throwable $e){
        if(isset($pdo)&&$pdo instanceof PDO&&$pdo->inTransaction())$pdo->rollBack();
        zgImpJson(['ok'=>false,'error'=>$e->getMessage()],400);
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Importar ticket Odoo</title>
<style>*{box-sizing:border-box}body{margin:0;background:linear-gradient(180deg,#eef3f8 0,#e8f0f8 100%);color:#10213a;font-family:Manrope,Arial,sans-serif;padding:24px}.box{max-width:780px;margin:auto;background:#fff;border-radius:24px;padding:28px;box-shadow:0 18px 50px #10213a22}.k{color:#1f6fc4;font-weight:900;text-transform:uppercase;font-size:11px;letter-spacing:.09em}h1{margin:6px 0 8px;font-size:42px;line-height:1.05;font-family:Archivo,Manrope,Arial,sans-serif}p{margin:0;color:#4b6078;line-height:1.55}.tip{margin-top:14px;padding:12px 14px;border-radius:14px;background:#f5f9ff;border:1px solid #d6e4f2;color:#38526c;font-size:14px}.row{display:grid;grid-template-columns:1fr auto;gap:10px;margin-top:18px}input,button{font:inherit;border-radius:14px;min-height:52px}input{border:1.5px solid #cbd9e8;padding:12px 14px;font-size:17px}button{border:0;background:linear-gradient(135deg,#1f6fc4,#125299);color:#fff;font-weight:900;padding:0 20px;cursor:pointer;box-shadow:0 12px 26px rgba(31,111,196,.24)}button:hover{transform:translateY(-1px)}.status{margin-top:14px;font-weight:800}.status.err{color:#b42318}.status.ok{color:#18733b}.back{display:inline-flex;margin-top:18px;color:#1f6fc4;font-weight:900;text-decoration:none}@media(max-width:560px){h1{font-size:34px}.row{grid-template-columns:1fr}button{min-height:48px}}</style></head><body><div class="box"><div class="k">Panel de supervisión</div><h1>Importar ticket desde Odoo</h1><p>Escribe el número corto del ticket. Se guardarán únicamente los datos útiles para el técnico y los catálogos del panel.</p><div class="tip">Se importarán solo los datos relevantes del ticket: cliente, cotización, ubicación, modalidad y tipo de equipo. El N° de reporte se asigna luego desde el panel.</div><form id="f" class="row"><input id="ticket" name="ticket_ref" inputmode="numeric" pattern="[0-9]*" placeholder="Ej. 1730" required><button id="b">Importar y guardar</button></form><div id="s" class="status"></div><a class="back" href="panel.php">← Volver al panel</a></div><script>const f=document.getElementById('f'),s=document.getElementById('s'),b=document.getElementById('b');f.addEventListener('submit',async e=>{e.preventDefault();s.textContent='';s.className='status';b.disabled=true;try{const fd=new FormData(f),r=await fetch('odoo_importar_ticket.php',{method:'POST',body:fd,credentials:'same-origin'}),j=await r.json();if(!r.ok||!j.ok)throw new Error(j.error||'No se pudo importar.');location.replace('panel.php#odooTicketAssignments')}catch(err){s.textContent=err.message;s.className='status err'}finally{b.disabled=false}});</script></body></html>
