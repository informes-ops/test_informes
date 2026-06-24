<?php
/* ZGROUP V52: tabla técnica reefer mejorada y unidad dinámica para OK/voltaje */
/* ZGROUP V51: catálogo reefer con modelo/año/refrigerante y tabla de parámetros eléctricos */
/* ZGROUP V48: instalación reefer + checklist compacto + unidades corregidas + estado genset simplificado */
/* ZGROUP V46: corrige renderizado de paneles de Asistencia Técnica y Mantenimiento Correctivo */
/* ZGROUP V44: creación/edición de preliminar separadas por la URL y respaldo de servidor */
/* ZGROUP V43: evita edición de preliminar sin ID válido */
/* ZGROUP V42: modelo/año reefer + lista de inspección técnica sin dividir en el PDF */
/* ZGROUP V33: carga seriales reefer creados en el panel */
/* ZGROUP V31: IA real sin fallback local */
/* ZGROUP V29: bloque de recomendaciones completo en una sola página */
/* ZGROUP V30: deshielo simplificado + asistente real de redacción técnica */
/* ZGROUP V27: horario automático de inicio y fin del servicio */
/* ZGROUP V26: edición de fecha protegida contra restauración de la preliminar */
/* ZGROUP V22: edición completa de preliminares e informes desde panel */
/* ZGROUP V18: deshielo de contenedor agregado con 2 campos */
/* ZGROUP V15: MP400 eliminado; solo MP3000, MP4000 y MP5000 */
/* ZGROUP BUILD: CORREGIDO_REAL_v9_PANEL_REAL_20260620
   Cambios visibles V9:
   - tamaño de contenedor y tipo de equipo (Reefer / Genset),
   - trabajos y control final independientes para genset,
   - instalación de luminarias ampliada,
   - conserva las mejoras V7 de alarma, limpieza y navegación táctil,
   - guarda tipo/tamaño/genset directamente en la preliminar y el informe. */
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Evita que el navegador reutilice una versión anterior del formulario.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Evita que el navegador muestre una versión anterior del formulario después de actualizar index.php.
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

/* ============================================================
   Acceso general para técnicos
   Contraseña solicitada: tecnicos
   - Sin sesión: muestra pantalla de acceso.
   - Con sesión y sin modo/token: muestra menú general.
   - modo=cliente o token=...: carga el formulario técnico.
   - modo=base: muestra página en proceso.
   ============================================================ */

$ZGROUP_TECH_PASS = 'tecnicos';
$login_error = '';

if (isset($_GET['salir'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tech_access_password'])) {
    $clave = trim((string)$_POST['tech_access_password']);
    if (hash_equals($ZGROUP_TECH_PASS, $clave)) {
        $_SESSION['zgroup_tecnicos_ok'] = true;
        header('Location: ' . ($_SERVER['REQUEST_URI'] ?? 'index.php'));
        exit;
    }
    $login_error = 'Contraseña incorrecta. Intenta nuevamente.';
}

$tecnico_logueado = !empty($_SESSION['zgroup_tecnicos_ok']);
$modo_general = trim((string)($_GET['modo'] ?? ''));
$token_general = trim((string)($_GET['token'] ?? ''));

$modo_editar_informe = ($modo_general === 'editar_informe');
$modo_editar_preliminar = ($modo_general === 'editar_preliminar');
if ($modo_editar_informe || $modo_editar_preliminar) {
    // Toda edición administrativa se abre únicamente desde una sesión válida del panel.
    if (empty($_SESSION['panel_ok'])) {
        header('Location: panel.php');
        exit;
    }
    $tecnico_logueado = true;
    $modo_general = 'cliente';
}

function zgroup_html_base_css() {
    return "
    <style>
    :root{--bg:#eef2f7;--ink:#10213a;--muted:#66748a;--accent:#1f6fc4;--accent2:#23a6d5;--line:#dbe5f0;--card:#ffffff;--danger:#dc2626;--ok:#16a34a;}
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{min-height:100%;font-family:Manrope,system-ui,-apple-system,Segoe UI,sans-serif;background:var(--bg);color:var(--ink)}
    .zg-screen{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px;position:relative;overflow:hidden;background:linear-gradient(180deg,rgba(7,18,34,.58),rgba(7,18,34,.72)),url('zgroup-tec.jpg') center/cover no-repeat;}
    .zg-screen:after{content:'';position:absolute;inset:auto 0 0;height:180px;background:linear-gradient(0deg,var(--bg),transparent);pointer-events:none}
    .zg-card{position:relative;z-index:2;width:min(980px,100%);background:rgba(255,255,255,.94);border:1px solid rgba(255,255,255,.72);border-radius:28px;box-shadow:0 28px 70px rgba(0,0,0,.28);overflow:hidden;backdrop-filter:blur(12px)}
    .zg-top{padding:28px 30px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:18px;justify-content:space-between;background:linear-gradient(180deg,#fff,#f7fbff)}
    .zg-brand{display:flex;align-items:center;gap:16px}.zg-brand img{height:54px;width:auto;background:#fff;border-radius:16px;padding:8px;box-shadow:0 8px 22px rgba(16,33,58,.12)}
    .zg-kicker{display:inline-flex;align-items:center;gap:7px;background:#e8f2ff;color:#13579b;border:1px solid #cbe1fa;border-radius:999px;padding:6px 11px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;font-size:11px}.zg-kicker:before{content:'●';color:#f5b21b}
    .zg-title{font-family:Archivo,system-ui,sans-serif;font-size:clamp(28px,4vw,44px);line-height:1;margin:10px 0 6px;letter-spacing:-.04em}.zg-sub{color:var(--muted);font-weight:700;font-size:14px;line-height:1.45}
    .zg-body{padding:30px}.zg-actions{display:grid;grid-template-columns:1fr 1fr;gap:18px}.zg-option{display:flex;min-height:190px;flex-direction:column;justify-content:space-between;text-decoration:none;color:inherit;border:1.5px solid var(--line);border-radius:24px;background:linear-gradient(180deg,#fff,#f8fbff);padding:24px;transition:.18s;box-shadow:0 12px 34px rgba(16,33,58,.08)}
    .zg-option:hover{transform:translateY(-3px);border-color:#9fc8f3;box-shadow:0 20px 45px rgba(31,111,196,.18)}.zg-option.disabled:hover{transform:none;border-color:var(--line)}
    .zg-icon{width:52px;height:52px;border-radius:18px;display:grid;place-items:center;background:#e8f2ff;font-size:25px;margin-bottom:16px}.zg-option h2{font-family:Archivo,system-ui,sans-serif;font-size:24px;letter-spacing:-.02em;margin-bottom:7px}.zg-option p{color:var(--muted);font-weight:700;font-size:14px}.zg-go{margin-top:22px;display:inline-flex;align-items:center;justify-content:center;width:max-content;background:var(--accent);color:#fff;border-radius:999px;padding:11px 16px;font-weight:900;font-size:13px}.zg-muted-btn{background:#e9eef5;color:#10213a}.zg-logout{color:#567;align-self:flex-start;text-decoration:none;font-weight:900;background:#eef4fb;border:1px solid #d7e5f5;border-radius:999px;padding:9px 13px;font-size:12px}.zg-form{max-width:480px;margin:0 auto}.zg-input{width:100%;border:1.5px solid var(--line);border-radius:16px;padding:15px 16px;font:inherit;font-size:16px;background:#f8fbff;outline:none}.zg-input:focus{border-color:var(--accent);box-shadow:0 0 0 4px #e5f1ff}.zg-btn{width:100%;border:none;background:var(--accent);color:#fff;border-radius:16px;padding:15px 16px;font:inherit;font-weight:900;font-size:16px;cursor:pointer;margin-top:14px}.zg-error{margin-top:12px;background:#fff1f1;color:#b91c1c;border:1px solid #fecaca;border-radius:14px;padding:12px 14px;font-weight:800;font-size:13px}.zg-note{margin-top:14px;color:var(--muted);font-weight:700;font-size:13px;text-align:center}.zg-process{display:grid;grid-template-columns:1.1fr .9fr;gap:24px;align-items:center}.zg-anim{height:260px;border-radius:24px;background:radial-gradient(circle at 30% 25%,#dff1ff,transparent 34%),linear-gradient(145deg,#f8fbff,#eaf3ff);border:1px solid var(--line);display:grid;place-items:center;overflow:hidden}.zg-gear{font-size:84px;animation:zgSpin 3.5s linear infinite;filter:drop-shadow(0 14px 18px rgba(16,33,58,.2))}@keyframes zgSpin{to{transform:rotate(360deg)}}.zg-back{display:inline-flex;margin-top:18px;text-decoration:none;background:var(--accent);color:white;border-radius:999px;padding:12px 17px;font-weight:900}.zg-progress{height:12px;background:#e0e8f2;border-radius:999px;overflow:hidden;margin-top:18px}.zg-progress span{display:block;height:100%;width:48%;background:linear-gradient(90deg,var(--accent),var(--accent2));border-radius:999px;animation:zgLoad 1.8s ease-in-out infinite}@keyframes zgLoad{0%,100%{transform:translateX(-30%)}50%{transform:translateX(130%)}}
    @media(max-width:720px){.zg-screen{padding:14px;align-items:flex-start}.zg-card{border-radius:22px;margin-top:18px}.zg-top{flex-direction:column;align-items:flex-start;padding:22px}.zg-body{padding:22px}.zg-actions{grid-template-columns:1fr}.zg-option{min-height:160px}.zg-process{grid-template-columns:1fr}.zg-anim{height:190px}.zg-logout{align-self:flex-end}}
    
/* ---------- Botón volver al menú general con aviso ---------- */
.menu-home-float{
  position:fixed;
  top:18px;
  left:18px;
  z-index:120;
  display:inline-flex;
  align-items:center;
  gap:8px;
  border:1px solid rgba(255,255,255,.28);
  background:rgba(16,33,58,.82);
  color:#fff;
  border-radius:999px;
  padding:10px 14px;
  font-family:'Archivo',system-ui,sans-serif;
  font-size:13px;
  font-weight:900;
  cursor:pointer;
  box-shadow:0 14px 32px rgba(10,21,38,.22);
  backdrop-filter:blur(12px);
}
.menu-home-float:hover{background:rgba(31,111,196,.92);transform:translateY(-1px)}
.leave-modal{position:fixed;inset:0;z-index:250;display:none;align-items:center;justify-content:center;background:rgba(10,21,38,.58);padding:18px;backdrop-filter:blur(7px)}
.leave-modal.show{display:flex}
.leave-box{width:min(520px,100%);background:#fff;border-radius:24px;border:1px solid #dbe6f3;box-shadow:0 24px 70px rgba(10,21,38,.32);overflow:hidden}
.leave-head{display:flex;align-items:center;gap:12px;padding:18px 20px;background:linear-gradient(135deg,#f8fbff,#eef5ff);border-bottom:1px solid #dbe6f3}
.leave-ico{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:#fff3cd;color:#8a5a00;font-size:22px;border:1px solid #ffe29a}
.leave-head h3{font-family:'Archivo',system-ui,sans-serif;margin:0;color:#10213a;font-size:19px;letter-spacing:-.02em}
.leave-body{padding:18px 20px;color:#5d6d83;font-weight:700;line-height:1.45}
.leave-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px;flex-wrap:wrap}
.leave-btn{border:none;border-radius:14px;padding:12px 15px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}
.leave-keep{background:#eaf1f8;color:#10213a}
.leave-exit{background:#1f6fc4;color:#fff;box-shadow:0 10px 22px rgba(31,111,196,.22)}
.leave-keep:hover,.leave-exit:hover{filter:brightness(.97);transform:translateY(-1px)}
@media(max-width:640px){.menu-home-float{top:10px;left:10px;padding:9px 11px;font-size:12px}.leave-actions{flex-direction:column-reverse}.leave-btn{width:100%}}

</style>";
}

function zgroup_render_login($login_error = '') {
    ?><!-- ZGROUP FIX V7 2026-06-20: alarma inicial + limpieza de cierre + navegación táctil -->
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acceso técnicos — ZGROUP</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Manrope:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<?= zgroup_html_base_css() ?>
</head>
<body>

<section class="zg-screen">
  <div class="zg-card">
    <div class="zg-top">
      <div class="zg-brand">
        <img src="zgroup-logo.png" alt="ZGROUP">
        <div>
          <span class="zg-kicker">Acceso técnico</span>
          <h1 class="zg-title">Área técnica en campo</h1>
          <p class="zg-sub">Ingresa la contraseña autorizada para registrar servicios, inspecciones y evidencias.</p>
        </div>
      </div>
    </div>
    <div class="zg-body">
      <form method="post" class="zg-form" autocomplete="off">
        <input class="zg-input" type="password" name="tech_access_password" placeholder="Escribir contraseña" autofocus required>
        <button class="zg-btn" type="submit">Ingresar</button>
        <?php if ($login_error !== ''): ?><div class="zg-error"><?= htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <p class="zg-note">Acceso reservado para personal autorizado.</p>
      </form>
    </div>
  </div>
</section>



<script>
/* Fix rápido del selector de ubicación */
(function(){
  const PE_CENTER = [-12.0464, -77.0428];
  let zgMap = null;
  let zgMarker = null;
  let zgCurrent = null;
  let zgSelected = null;
  let zgLoadingLeaflet = false;

  function el(id){ return document.getElementById(id); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

  function ensureLeaflet(done){
    if(window.L && typeof L.map === 'function'){ done(); return; }
    if(!document.querySelector('link[data-zg-leaflet]')){
      const link=document.createElement('link');
      link.rel='stylesheet';
      link.href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      link.setAttribute('data-zg-leaflet','1');
      document.head.appendChild(link);
    }
    if(zgLoadingLeaflet){ setTimeout(function(){ ensureLeaflet(done); }, 250); return; }
    zgLoadingLeaflet = true;
    const sc=document.createElement('script');
    sc.src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    sc.onload=function(){ zgLoadingLeaflet=false; done(); };
    sc.onerror=function(){
      zgLoadingLeaflet=false;
      const addr=el('pickAddr');
      if(addr) addr.innerHTML='<b>No se pudo cargar el mapa.</b> Revisa internet y vuelve a intentar.';
    };
    document.head.appendChild(sc);
  }

  function pinIcon(){
    return L.divIcon({
      className:'',
      html:'<div style="width:26px;height:26px;background:#e03131;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px rgba(0,0,0,.38)"></div>',
      iconSize:[26,26],
      iconAnchor:[13,26]
    });
  }

  function blueIcon(){
    return L.divIcon({
      className:'',
      html:'<div style="width:16px;height:16px;background:#1f6fc4;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 6px rgba(31,111,196,.20),0 3px 10px rgba(0,0,0,.30)"></div>',
      iconSize:[16,16],
      iconAnchor:[8,8]
    });
  }

  function prepareBox(){
    const box=el('pickMap');
    if(!box) return null;
    box.style.display='block';
    box.style.position='relative';
    box.style.background='#e9eef3';
    box.style.minHeight = window.innerWidth <= 780 ? '46vh' : '360px';
    box.style.height = window.innerWidth <= 780 ? 'calc(100dvh - 245px)' : '420px';
    return box;
  }

  function openFixedMap(){
    const modal=el('mapModal');
    const addr=el('pickAddr');
    const box=prepareBox();
    if(!modal || !box) return;
    modal.classList.add('show');
    if(addr && !zgSelected) addr.innerHTML='Toca el mapa o arrastra el pin para elegir el punto exacto…';

    ensureLeaflet(function(){
      setTimeout(function(){
        try{
          if(zgMap){
            zgMap.invalidateSize(true);
            return;
          }

          // Limpia cualquier intento anterior que haya quedado a medias.
          if(box._leaflet_id){ box._leaflet_id = null; box.innerHTML = ''; }

          zgMap = L.map(box, { zoomControl:true, attributionControl:true }).setView(PE_CENTER, 12);
          L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom:19,
            attribution:'&copy; OpenStreetMap'
          }).addTo(zgMap);

          zgMap.on('click', function(ev){ setSelected(ev.latlng.lat, ev.latlng.lng, true); });
          setTimeout(function(){ zgMap.invalidateSize(true); }, 80);
          setTimeout(function(){ zgMap.invalidateSize(true); }, 350);
          setTimeout(function(){ zgMap.invalidateSize(true); }, 800);

          if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(function(pos){
              const lat=pos.coords.latitude;
              const lng=pos.coords.longitude;
              if(zgCurrent) zgCurrent.setLatLng([lat,lng]);
              else zgCurrent = L.marker([lat,lng], {icon:blueIcon(), interactive:false}).addTo(zgMap);
              zgMap.setView([lat,lng], 17);
              if(!zgSelected) setSelected(lat,lng,true);
            }, function(){}, {enableHighAccuracy:true, timeout:10000, maximumAge:0});
          }
        }catch(err){
          console.error('Mapa ZGROUP:', err);
          if(addr) addr.innerHTML='<b>No se pudo iniciar el mapa.</b> Recarga la página e intenta otra vez.';
        }
      }, 160);
    });
  }

  function setSelected(lat,lng,doReverse){
    if(!zgMap || !window.L) return;
    zgSelected = {lat:Number(lat), lng:Number(lng), addr:''};
    if(zgMarker) zgMarker.setLatLng([lat,lng]);
    else{
      zgMarker = L.marker([lat,lng], {icon:pinIcon(), draggable:true}).addTo(zgMap);
      zgMarker.on('dragend', function(){
        const p=zgMarker.getLatLng();
        setSelected(p.lat, p.lng, true);
      });
    }
    const addr=el('pickAddr');
    if(addr) addr.innerHTML='<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'<br><small>Buscando dirección exacta…</small>';
    updateRoute();
    if(doReverse) reverseAddress(lat,lng);
  }

  async function reverseAddress(lat,lng){
    const addr=el('pickAddr');
    try{
      const r=await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+encodeURIComponent(lat)+'&lon='+encodeURIComponent(lng)+'&zoom=18&addressdetails=1&accept-language=es');
      const d=await r.json();
      const text=d.display_name || '';
      if(zgSelected) zgSelected.addr=text;
      if(addr){
        addr.innerHTML = text
          ? '<b>Dirección:</b> '+esc(text)+'<br><small>Coordenadas: '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'</small>'
          : '<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6);
      }
    }catch(e){
      if(addr) addr.innerHTML='<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'<br><small>No se pudo obtener la calle; se guardarán las coordenadas.</small>';
    }
    updateRoute();
  }

  async function searchAddress(){
    const q=(el('mapSearch') && el('mapSearch').value || '').trim();
    const addr=el('pickAddr');
    const sug=el('mapSug');
    if(!q) return;
    if(addr) addr.textContent='Buscando…';
    try{
      const url='https://nominatim.openstreetmap.org/search?format=jsonv2&q='+encodeURIComponent(q)+'&limit=8&addressdetails=1&countrycodes=pe&accept-language=es';
      const r=await fetch(url);
      const arr=await r.json();
      if(sug){ sug.innerHTML=''; sug.classList.remove('show'); }
      if(!arr || !arr.length){
        if(addr) addr.innerHTML='No se encontró ese lugar. Escribe calle, distrito y ciudad, o toca el mapa para marcar el punto.';
        return;
      }
      if(sug){
        arr.forEach(function(it){
          const div=document.createElement('div');
          div.className='sug-item';
          div.innerHTML='<div class="sug-main">'+esc((it.display_name||'').split(',')[0])+'</div><div class="sug-sub">'+esc(it.display_name||'')+'</div>';
          div.addEventListener('mousedown', function(ev){
            ev.preventDefault();
            pickSearchItem(it);
            sug.classList.remove('show');
          });
          sug.appendChild(div);
        });
        sug.classList.add('show');
      }
      pickSearchItem(arr[0]);
    }catch(e){
      if(addr) addr.innerHTML='No se pudo buscar. Revisa internet o marca el punto en el mapa.';
    }
  }

  function pickSearchItem(it){
    const lat=parseFloat(it.lat), lng=parseFloat(it.lon);
    if(!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    const text=it.display_name || '';
    if(el('mapSearch')) el('mapSearch').value=text;
    if(zgMap) zgMap.setView([lat,lng],18);
    setSelected(lat,lng,false);
    if(zgSelected) zgSelected.addr=text;
    if(el('pickAddr')) el('pickAddr').innerHTML='<b>Dirección:</b> '+esc(text)+'<br><small>Coordenadas: '+lat.toFixed(6)+', '+lng.toFixed(6)+'</small>';
    updateRoute();
  }

  function updateRoute(){
    const route=el('mapRoute');
    if(!route || !zgSelected){ return; }
    route.href='https://www.google.com/maps/dir/?api=1&destination='+zgSelected.lat+','+zgSelected.lng;
    route.style.display='inline-flex';
  }

  function confirmFixedMap(){
    if(!zgSelected){
      if(typeof toast === 'function') toast('Elige un punto en el mapa primero');
      return;
    }
    const lat=Number(zgSelected.lat), lng=Number(zgSelected.lng);
    const direccion=el('direccion');
    const coords=el('direccionCoords');
    if(direccion) direccion.value = zgSelected.addr || (lat.toFixed(6)+', '+lng.toFixed(6));
    if(coords) coords.value = lat.toFixed(6)+', '+lng.toFixed(6);
    const origen=el('direccionOrigenOdoo'); if(origen) origen.value='';
    const modal=el('mapModal');
    if(modal) modal.classList.remove('show');
    if(typeof toast === 'function') toast('Ubicación seleccionada');
    if(typeof validateField === 'function') validateField('direccion');
  }

  // Captura antes de cualquier manejador anterior para evitar que se quede el mapa en blanco.
  document.addEventListener('click', function(e){
    if(e.target.closest('#dirPick') || e.target.closest('#direccion')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      openFixedMap();
      return false;
    }
    if(e.target.closest('#mapSearchBtn')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      searchAddress();
      return false;
    }
    if(e.target.closest('#mapConfirm')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      confirmFixedMap();
      return false;
    }
  }, true);

  document.addEventListener('keydown', function(e){
    if(e.target && e.target.id === 'mapSearch' && e.key === 'Enter'){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      searchAddress();
      return false;
    }
  }, true);

  window.openMap = openFixedMap;
})();
</script>



<style>
/* ============================================================
   ZGROUP - tabla real de repuestos seleccionados
   ============================================================ */
#repuestosSelectedList.zg-table-ready{display:block!important;margin-top:12px!important}
.zg-repuestos-table-wrap{border:1.5px solid #cfe0f4;border-radius:18px;overflow:hidden;background:#fff;box-shadow:0 14px 30px rgba(16,33,58,.10);margin-top:10px}
.zg-repuestos-table{width:100%;border-collapse:collapse;font-size:13px;background:#fff}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#eef6ff,#e7f0fb);color:#155293;font-family:'Archivo',system-ui,sans-serif;font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;text-align:left;padding:11px 12px;border-bottom:1px solid #d7e5f5;border-right:1px solid #d7e5f5}
.zg-repuestos-table thead th:last-child{border-right:none;text-align:center}
.zg-repuestos-table td{padding:10px 12px;border-bottom:1px solid #edf3fa;border-right:1px solid #edf3fa;vertical-align:middle}
.zg-repuestos-table td:last-child{border-right:none;text-align:center}.zg-repuestos-table tr:last-child td{border-bottom:none}
.zg-rep-code2{font-family:'Archivo',system-ui,sans-serif;font-weight:900;color:#1f6fc4;word-break:break-word}.zg-rep-code2.empty{color:#8a5a00}
.zg-rep-detail2{width:100%;border:1.5px solid #d7e5f5;background:#f8fbff;border-radius:12px;padding:10px 11px;font:inherit;font-weight:850;color:#10213a;outline:none}.zg-rep-detail2:focus{border-color:#1f6fc4;background:#fff;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-qty2{width:96px;border:1.5px solid #d7e5f5;background:#fff;border-radius:12px;padding:10px 8px;font:inherit;font-family:'Archivo',system-ui,sans-serif;font-weight:900;text-align:center;color:#10213a;outline:none}.zg-rep-qty2:focus{border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-del2{width:36px;height:36px;border:none;border-radius:12px;background:#fff1f1;color:#b91c1c;font-size:18px;font-weight:900;cursor:pointer}.zg-rep-del2:hover{filter:brightness(.96);transform:translateY(-1px)}
.zg-rep-count2{display:inline-flex!important;align-items:center;gap:8px;background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:900;margin-top:10px}.zg-rep-count2:before{content:'✓'}
.zg-rep-tip2{font-size:11.5px;color:#66758a;font-weight:800;margin-top:4px}.zg-rep-tip2 b{color:#10213a}
#repuestosEmpty{margin-top:12px!important}
.repuesto-smart .smart-menu{z-index:99999!important;pointer-events:auto!important}.repuesto-smart .smart-option{pointer-events:auto!important;cursor:pointer!important}.repuesto-smart .smart-option *{pointer-events:none!important}
@media(max-width:720px){.zg-repuestos-table thead{display:none}.zg-repuestos-table,.zg-repuestos-table tbody,.zg-repuestos-table tr,.zg-repuestos-table td{display:block;width:100%}.zg-repuestos-table tr{border-bottom:1px solid #edf3fa}.zg-repuestos-table td{border-right:none}.zg-repuestos-table td::before{display:block;margin-bottom:5px;font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;color:#66758a;font-family:'Archivo';font-weight:900}.zg-repuestos-table td[data-label="Código"]::before{content:'Código'}.zg-repuestos-table td[data-label="Material"]::before{content:'Material / repuesto'}.zg-repuestos-table td[data-label="Cantidad"]::before{content:'Cantidad'}.zg-rep-qty2{width:130px}.zg-rep-del2{width:100%}}
</style>
<script>
/* ============================================================
   ZGROUP - selección real de repuestos + tabla + PDF
   - Reemplaza la lista anterior sin depender de los handlers viejos.
   ============================================================ */
(function(){
  function el(id){ return document.getElementById(id); }
  function clean(s){ return String(s || '').replace(/\s+/g,' ').trim(); }
  function norm(s){ return clean(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  function toast2(t){ try{ if(typeof toast === 'function') toast(t); else console.log(t); }catch(e){ console.log(t); } }
  function catalogo(){ try{ return Array.isArray(REPUESTOS_CATALOGO) ? REPUESTOS_CATALOGO : []; }catch(e){ return []; } }

  let materiales = [];

  function parseLinea(linea){
    linea = clean(linea); if(!linea) return null;
    const p = linea.split('|').map(clean);
    let codigo='', detalle='', cantidad='1', unidad='';

    /*
       Formato actual: CODIGO | MATERIAL | CANTIDAD | UNIDAD
       Formato anterior: CODIGO | MATERIAL | CANTIDAD
       Se reconocen ambos para no perder cantidades al agregar otro material.
    */
    if(p.length >= 4){
      codigo = p[0] === '-' ? '' : p[0];
      cantidad = p[p.length - 2] || '1';
      unidad = p[p.length - 1] || '';
      detalle = p.slice(1, -2).join(' | ') || p[1] || '';
    } else if(p.length === 3){
      codigo = p[0] === '-' ? '' : p[0];
      detalle = p[1] || '';
      cantidad = p[2] || '1';
    } else if(p.length === 2){
      codigo = p[0] === '-' ? '' : p[0];
      detalle = p[1] || '';
    } else {
      detalle = linea;
    }

    detalle = clean(detalle.replace(/\s*\|\s*/g,' '));
    cantidad = String(cantidad || '1').replace(/[^0-9]/g,'').slice(0,4) || '1';
    if(!detalle) return null;
    return {codigo, detalle, cantidad, unidad};
  }
  function cargarDesdeTextarea(){
    const ta = el('repuestosManual');
    const txt = ta ? ta.value : '';
    const arr = txt.split(/\r?\n/).map(parseLinea).filter(Boolean);
    if(arr.length) materiales = arr;
    return materiales;
  }
  function linea(x){ return (clean(x.codigo)||'-') + ' | ' + clean(x.detalle) + ' | ' + (String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1'); }
  function guardar(){
    const ta = el('repuestosManual');
    if(ta){
      ta.value = materiales.filter(x=>clean(x.detalle)).map(linea).join('\n');
      ta.classList.remove('input-error');
    }
    try{ repuestosSeleccionados = materiales.map(x => ({codigo:clean(x.codigo), detalle:clean(x.detalle), cantidad:String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1', nuevo:!clean(x.codigo)})); }catch(e){}
    const err = el('repuestosManualError');
    if(err){ err.textContent=''; err.classList.remove('show'); }
    try{ if(window.zgroupMarcarCambio) window.zgroupMarcarCambio(); }catch(e){}
  }
  function pintar(){
    const box = el('repuestosSelectedList');
    const empty = el('repuestosEmpty');
    if(!box) return;
    box.classList.add('zg-table-ready');
    box.innerHTML = '';
    if(empty) empty.classList.toggle('show', materiales.length === 0);
    if(!materiales.length){ guardar(); return; }

    const count = document.createElement('div');
    count.className = 'zg-rep-count2';
    count.textContent = materiales.length + ' material(es) seleccionado(s)';
    box.appendChild(count);

    const wrap = document.createElement('div');
    wrap.className = 'zg-repuestos-table-wrap';
    const table = document.createElement('table');
    table.className = 'zg-repuestos-table';
    table.innerHTML = '<thead><tr><th style="width:145px">Código</th><th>Material / repuesto</th><th style="width:120px">Cantidad</th><th style="width:56px"></th></tr></thead><tbody></tbody>';
    const tbody = table.querySelector('tbody');

    materiales.forEach(function(it, idx){
      const tr = document.createElement('tr');
      const cod = clean(it.codigo) || 'Sin código';
      tr.innerHTML =
        '<td data-label="Código"><div class="zg-rep-code2 '+(!clean(it.codigo)?'empty':'')+'">'+esc(cod)+'</div></td>'+ 
        '<td data-label="Material"><input class="zg-rep-detail2" type="text" value="'+esc(it.detalle)+'" placeholder="Nombre del material"><div class="zg-rep-tip2">Puedes corregir el nombre antes de generar el PDF.</div></td>'+ 
        '<td data-label="Cantidad"><input class="zg-rep-qty2" type="text" inputmode="numeric" value="'+esc(it.cantidad||'1')+'"></td>'+ 
        '<td><button type="button" class="zg-rep-del2" title="Quitar">×</button></td>';
      const det = tr.querySelector('.zg-rep-detail2');
      const qty = tr.querySelector('.zg-rep-qty2');
      const del = tr.querySelector('.zg-rep-del2');
      det.addEventListener('input', function(){ materiales[idx].detalle = clean(det.value); guardar(); });
      qty.addEventListener('input', function(){ let v=String(qty.value||'').replace(/[^0-9]/g,'').slice(0,4); qty.value=v; materiales[idx].cantidad=v; guardar(); });
      qty.addEventListener('focus', function(){ setTimeout(function(){ try{ qty.select(); }catch(e){} }, 30); });
      qty.addEventListener('blur', function(){ if(!String(qty.value||'').trim()){ qty.value='1'; materiales[idx].cantidad='1'; guardar(); } });
      del.addEventListener('click', function(){ materiales.splice(idx,1); guardar(); pintar(); });
      tbody.appendChild(tr);
    });
    wrap.appendChild(table);
    box.appendChild(wrap);
    guardar();
  }
  function activarSi(){
    const h=el('requiereRepuesto'); if(h) h.value='si';
    const card=el('repuestosCard'); if(card) card.classList.remove('is-hidden');
    const si=el('repuestoSiBtn'), no=el('repuestoNoBtn'); if(si) si.classList.add('on'); if(no) no.classList.remove('on');
  }
  function existe(item){
    const d=norm(item.detalle), c=norm(item.codigo);
    return materiales.some(x => (c && norm(x.codigo)===c) || (d && norm(x.detalle)===d));
  }
  function agregar(item, aviso){
    item = item || {};
    const obj = {codigo:clean(item.codigo||''), detalle:clean(item.detalle||''), cantidad:String(item.cantidad||'1').replace(/[^0-9]/g,'').slice(0,4)||'1'};
    if(!obj.detalle) return false;
    activarSi();
    cargarDesdeTextarea();
    if(existe(obj)){ if(aviso!==false) toast2('Ya está seleccionado: '+obj.detalle); pintar(); return false; }
    materiales.push(obj);
    const input=el('repuestoSearch'); if(input) input.value='';
    const menu=el('repuestoSuggest'); if(menu) menu.classList.remove('show');
    pintar();
    if(aviso!==false) toast2('Agregado a la tabla: '+obj.detalle+' · Cantidad '+obj.cantidad);
    return true;
  }
  function buscar(det){
    const d=norm(det); if(!d) return null;
    return catalogo().find(r=>norm(r.detalle||'')===d) || catalogo().find(r=>norm(r.detalle||'').includes(d)) || null;
  }
  function agregarManual(){
    const input=el('repuestoSearch');
    const det=clean(input?input.value:'');
    if(!det){ if(input) input.focus(); toast2('Escribe o selecciona un material'); return false; }
    const cat=buscar(det);
    return agregar(cat ? {codigo:cat.codigo||'', detalle:cat.detalle||det, cantidad:'1'} : {codigo:'', detalle:det, cantidad:'1'}, true);
  }
  function mostrarMenuPropio(menuId, items){
    const menu = el(menuId); if(!menu) return;
    menu.innerHTML='';
    if(!items || !items.length){ menu.classList.remove('show'); return; }
    items.forEach(function(item){
      const raw = item.raw || item;
      const b = document.createElement('button');
      b.type='button'; b.className='smart-option';
      b.innerHTML='<div><span class="smart-main">'+esc(item.main||raw.detalle||'')+'</span><span class="smart-sub">'+esc(item.sub || (raw.codigo ? 'Código: '+raw.codigo : 'Pendiente de revisión'))+'</span></div><span class="smart-badge">usar</span>';
      b.addEventListener('click', function(ev){ ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); agregar({codigo:raw.codigo||'', detalle:raw.detalle||item.main||'', cantidad:'1'}, true); return false; }, true);
      menu.appendChild(b);
    });
    menu.classList.add('show');
  }

  // Reemplaza el render del menú solo para repuestos. Los otros catálogos siguen igual.
  try{
    const oldRender = renderSmartMenu;
    renderSmartMenu = function(menuId, items, onPick){
      if(menuId === 'repuestoSuggest') return mostrarMenuPropio(menuId, items || []);
      return oldRender(menuId, items, onPick);
    };
  }catch(e){}

  // Captura adicional por si el menú viejo ya quedó pintado.
  document.addEventListener('click', function(ev){
    const btn = ev.target && ev.target.closest ? ev.target.closest('#repuestoSuggest .smart-option') : null;
    if(btn){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
      const main = clean(btn.querySelector('.smart-main') ? btn.querySelector('.smart-main').textContent : btn.textContent.replace(/usar\s*$/i,''));
      const cat = buscar(main);
      agregar(cat ? {codigo:cat.codigo||'', detalle:cat.detalle||main, cantidad:'1'} : {codigo:'', detalle:main, cantidad:'1'}, true);
      return false;
    }
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoAddManual')){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); agregarManual(); return false;
    }
    if(ev.target && ev.target.closest && (ev.target.closest('#pdfBtn') || ev.target.closest('#preBtn'))){ guardar(); }
  }, true);

  document.addEventListener('keydown', function(ev){
    if(ev.target && ev.target.id === 'repuestoSearch' && ev.key === 'Enter'){
      ev.preventDefault(); ev.stopPropagation(); agregarManual(); return false;
    }
  }, true);

  try{ insertarRepuestoLinea = function(r){ return agregar({codigo:r&&r.codigo||'', detalle:r&&r.detalle||'', cantidad:'1'}, true); }; }catch(e){}
  try{ agregarRepuestoObjeto = function(r,cantidad,aviso){ return agregar({codigo:r&&r.codigo||'', detalle:r&&r.detalle||'', cantidad:cantidad||'1'}, aviso!==false); }; }catch(e){}
  try{ agregarRepuestoManual = agregarManual; }catch(e){}
  try{ renderRepuestosSeleccionados = pintar; }catch(e){}
  try{ syncRepuestosManual = guardar; }catch(e){}
  try{ validarRepuestos = function(){ if(clean(el('repuestoSearch')?el('repuestoSearch').value:'')) agregarManual(); guardar(); if((el('requiereRepuesto')&&el('requiereRepuesto').value==='si') && !clean(el('repuestosManual')?el('repuestosManual').value:'')){ toast2('Agrega al menos un material en la tabla'); return false; } return true; }; }catch(e){}
  try{ registrarRepuestosTecnico = async function(){ guardar(); const txt=clean(el('repuestosManual')?el('repuestosManual').value:''); if(!txt) return true; try{ const fd=new FormData(); fd.append('repuestos', txt); await fetch('registrar_repuestos_tecnico.php',{method:'POST', body:fd}); }catch(e){} return true; }; }catch(e){}

  window.zgRepuestosTablaFinal = {agregar, pintar, guardar, materiales:function(){return materiales;}};
  window.addEventListener('load', function(){ cargarDesdeTextarea(); pintar(); const hint=el('repuestoHint'); if(hint) hint.textContent='Selecciona “usar” y aparecerá abajo en una tabla con cantidad.'; });
  document.addEventListener('DOMContentLoaded', function(){ cargarDesdeTextarea(); pintar(); });
})();
</script>


<style>
/* Ajuste final: tabla de repuestos más clara y cantidad editable sin quedarse pegada al 1 */
.zg-repuestos-table-wrap{border:1.5px solid #c6dcf5!important;border-radius:18px!important;overflow:hidden!important;background:#fff!important;box-shadow:0 14px 34px rgba(16,33,58,.12)!important}
.zg-repuestos-table{font-size:13.2px!important;background:#fff!important}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#18345c,#10213a)!important;color:#fff!important;padding:12px 12px!important;border-right:1px solid rgba(255,255,255,.18)!important;text-align:left!important}
.zg-repuestos-table thead th:nth-child(3),.zg-repuestos-table thead th:nth-child(4){text-align:center!important}
.zg-repuestos-table td{background:#fff!important;padding:11px 12px!important;border-bottom:1px solid #edf3fa!important;border-right:1px solid #edf3fa!important}
.zg-repuestos-table tbody tr:nth-child(even) td{background:#f8fbff!important}
.zg-rep-code2{font-size:12.8px!important;color:#1f6fc4!important}.zg-rep-code2.empty{color:#9b6a00!important}
.zg-rep-detail2{background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;min-height:42px!important}
.zg-rep-detail2:focus,.zg-rep-qty2:focus{border-color:#1f6fc4!important;box-shadow:0 0 0 3px #e7f0fb!important}
.zg-rep-qty2{width:92px!important;background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;font-size:15px!important;color:#10213a!important;text-align:center!important;caret-color:#1f6fc4!important}
.zg-rep-del2{background:#fff1f1!important;color:#c92a2a!important;border:1px solid #ffd6d6!important}
.zg-rep-count2{margin-bottom:9px!important;background:#eaf7ef!important;color:#176b34!important;border:1px solid #bfe8cc!important}
</style>


<style id="zg-estado-firma-reco-final">
/* Estado inicial dividido en 3 condiciones */
.estado-inicial-pro{background:#f8fbff;border:1px solid #dbe8f6;border-radius:16px;padding:12px!important}
.estado-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:6px}
.estado-box{background:#fff;border:1px solid #d9e7f5;border-radius:14px;padding:9px;box-shadow:0 6px 16px rgba(16,33,58,.04)}
.estado-label{display:block;color:#5f7189;font-size:11.5px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px}
.estado-box select{width:100%;min-height:44px!important;background:#fff!important;font-weight:800;color:#10213a}
@media(max-width:720px){.estado-grid{grid-template-columns:1fr}.estado-box{padding:10px}.estado-box select{min-height:48px!important;font-size:15px!important}}

/* Firma más limpia para celular */
.firma-box{background:linear-gradient(180deg,#ffffff,#f7fbff)!important;border:1.5px solid #cfe0f3!important;border-radius:18px!important;padding:13px!important}
.firma-title{align-items:flex-start!important;margin-bottom:10px!important}
.firma-title b{font-size:15px!important;color:#10213a!important}
.firma-title span{font-size:11.8px!important;color:#53677f!important;line-height:1.25!important}
.firma-canvas{
  height:190px!important;
  border:2px solid #b7d3f2!important;
  background-color:#fff!important;
  background-image:linear-gradient(to bottom, transparent 72%, rgba(31,111,196,.18) 72%, rgba(31,111,196,.18) 73%, transparent 73%), radial-gradient(circle at 50% 55%, rgba(31,111,196,.045), transparent 40%)!important;
  border-radius:18px!important;
  box-shadow:inset 0 0 0 1px rgba(255,255,255,.9),0 10px 24px rgba(16,33,58,.08)!important;
}
.firma-box::after{content:'Dibuje la firma sobre la línea guía';top:50%!important;transform:translateY(-50%);font-size:13px!important;color:#90a6bf!important;font-weight:900!important}
.firma-box.firmado::after{display:none!important}
.firma-helper-zg{display:flex;align-items:center;gap:8px;background:#eef6ff;border:1px solid #cfe2f7;color:#48637f;border-radius:12px;padding:8px 10px;margin:8px 0 10px;font-size:12px;font-weight:800;line-height:1.25}
.firma-helper-zg:before{content:'✍️';font-size:17px}
.firma-clear{background:#edf4fb!important;border:1px solid #cfe0f3!important;color:#17385d!important}
@media(max-width:520px){.firma-canvas{height:220px!important}.firma-helper-zg{font-size:12.5px}.firma-title{flex-direction:column!important;gap:3px!important}.firma-title span{text-align:left!important}}
</style>

<script id="zg-estado-firma-final-js">
(function(){
  function $(id){return document.getElementById(id);}
  function clean(s){return String(s||'').replace(/\s+/g,' ').trim();}
  function syncEstadoInicial(){
    const a=$('estadoEncendido'), b=$('estadoEnergia'), c=$('estadoAlarma'), h=$('estadoInicial');
    if(!a || !b || !c || !h) return;
    if(a.value && b.value && c.value){
      h.value = a.value + ' / ' + b.value + ' / ' + c.value;
    } else if(a.value || b.value || c.value) {
      h.value = '';
    }
  }
  function cargarEstadoEnSelectores(){
    const h=$('estadoInicial'), a=$('estadoEncendido'), b=$('estadoEnergia'), c=$('estadoAlarma');
    if(!h || !a || !b || !c) return;
    const v=clean(h.value).toLowerCase();
    if(!v) return;
    if(v.includes('apag')) a.value='Apagado'; else if(v.includes('encend')) a.value='Encendido';
    if(v.includes('sin suministro eléctrico') || v.includes('sin suministro electrico') || v.includes('sin energía') || v.includes('sin energia')) b.value='Sin suministro eléctrico'; else if(v.includes('suministro eléctrico') || v.includes('suministro electrico') || v.includes('energ')) b.value='Con suministro eléctrico';
    if(v.includes('sin alarma')) c.value='Sin alarma'; else if(v.includes('alarma')) c.value='Con alarma';
    syncEstadoInicial();
  }
  function initEstadoInicial(){
    ['estadoEncendido','estadoEnergia','estadoAlarma'].forEach(function(id){ const el=$(id); if(el) el.addEventListener('change', syncEstadoInicial); });
    setTimeout(cargarEstadoEnSelectores, 250);
    setTimeout(cargarEstadoEnSelectores, 900);
    setTimeout(cargarEstadoEnSelectores, 1800);
  }
  function initFirmaHelpers(){
    document.querySelectorAll('.firma-box').forEach(function(box){
      if(box.querySelector('.firma-helper-zg')) return;
      const canvas = box.querySelector('canvas');
      const div = document.createElement('div');
      div.className = 'firma-helper-zg';
      div.textContent = 'Use el dedo o mouse y firme despacio sobre la línea guía. Si no sale bien, presione “Limpiar firma” y vuelva a intentar.';
      if(canvas) box.insertBefore(div, canvas);
    });
  }
  window.addEventListener('load', function(){ initEstadoInicial(); initFirmaHelpers(); });
})();
</script>


<script id="zg-fix-estado-inicial-obligatorio">
(function(){
  function byId(id){ return document.getElementById(id); }
  function val(id){ const x=byId(id); return x ? String(x.value||'').trim() : ''; }
  function sync(){
    const h=byId('estadoInicial');
    const a=val('estadoEncendido'), b=val('estadoEnergia'), c=val('estadoAlarma');
    if(h){
      if(a && b && c) h.value = a + ' / ' + b + ' / ' + c;
      else if(a || b || c) h.value = '';
    }
  }
  function bind(){
    ['estadoEncendido','estadoEnergia','estadoAlarma'].forEach(function(id){
      const x=byId(id);
      if(!x || x.dataset.zgSyncOk) return;
      x.dataset.zgSyncOk='1';
      x.addEventListener('change', sync);
      x.addEventListener('input', sync);
    });
    sync();
  }
  document.addEventListener('DOMContentLoaded', bind);
  window.addEventListener('load', bind);
  document.addEventListener('click', function(ev){
    if(ev.target && ev.target.id === 'preBtn') sync();
  }, true);
  window.zgSyncEstadoInicial = sync;
})();
</script>



<style id="zg-ajuste-final-repuestos-observacion">
/* Oculta el resumen automático largo de actividades/hallazgos en la web */
.auto-summary{display:none!important;}
/* Menú de repuestos más limpio para técnico */
#repuestoSuggest .smart-option{align-items:center!important;}
#repuestoSuggest .smart-main{font-size:15px!important;font-weight:900!important;color:#10213a!important;}
#repuestoSuggest .smart-sub{font-size:12px!important;color:#66758a!important;}
</style>
<script id="zg-ajuste-final-repuestos-pdf">
(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(s){ return String(s == null ? '' : s).replace(/\s+/g,' ').trim(); }
  function esc(s){ return clean(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  function esCodigo(s){
    s = clean(s);
    if(!s) return false;
    var digits = (s.match(/\d/g)||[]).length;
    var letters = (s.match(/[A-Za-z]/g)||[]).length;
    return digits >= 3 || /^[-A-Z0-9]{5,}$/i.test(s) || /^INDND\d+/i.test(s) || /^\d{5,}/.test(s);
  }
  function partesRepuesto(raw, fallback){
    var source = clean((raw && (raw.detalle || raw.main || raw.nombre)) || fallback || '');
    var codigo = clean(raw && raw.codigo ? raw.codigo : '');
    var detalle = source;
    if(source.indexOf('|') >= 0){
      var parts = source.split('|').map(clean).filter(Boolean);
      if(parts.length){
        if(esCodigo(parts[0])){
          codigo = codigo || parts[0];
          detalle = clean(parts[1] || parts[parts.length-1] || source);
        }else{
          detalle = parts[0];
          // Si el segundo bloque parece contener código, lo dejamos solo como referencia interna.
          if(!codigo){
            for(var i=1;i<parts.length;i++){
              var m = parts[i].match(/([A-Z]{2,}\d{3,}|\d{5,})/i);
              if(m){ codigo = m[1]; break; }
            }
          }
        }
      }
    }
    detalle = clean(detalle.replace(/^\d+\s+/, ''));
    if(!detalle && source) detalle = source;
    return {codigo: codigo, detalle: detalle};
  }
  function renderRepuestoMenuLimpio(menuId, items){
    var menu = byId(menuId);
    if(!menu) return;
    menu.innerHTML = '';
    if(!items || !items.length){ menu.classList.remove('show'); return; }
    items.forEach(function(item){
      var raw = item.raw || item || {};
      var p = partesRepuesto(raw, item.main || '');
      if(!p.detalle) return;
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'smart-option';
      b.innerHTML = '<div><span class="smart-main">'+esc(p.detalle)+'</span><span class="smart-sub">'+esc(p.codigo ? 'Código: '+p.codigo : 'Registrado en panel')+'</span></div><span class="smart-badge">usar</span>';
      function usar(ev){
        ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
        var obj = {codigo:p.codigo || '', detalle:p.detalle || '', cantidad:'1'};
        try{
          if(typeof agregarRepuestoObjeto === 'function') agregarRepuestoObjeto(obj, '1', true);
          else if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.agregar === 'function') window.zgRepuestosTablaFinal.agregar(obj, true);
        }catch(e){ console.warn('No se pudo agregar repuesto', e); }
        menu.classList.remove('show');
        var input = byId('repuestoSearch'); if(input) input.value = '';
        return false;
      }
      b.addEventListener('click', usar, true);
      menu.appendChild(b);
    });
    menu.classList.add('show');
  }
  function instalarRender(){
    if(typeof window.renderSmartMenu === 'function' && !window.renderSmartMenu.__zgLimpio){
      var old = window.renderSmartMenu;
      var nuevo = function(menuId, items, onPick){
        if(menuId === 'repuestoSuggest') return renderRepuestoMenuLimpio(menuId, items || []);
        return old.apply(this, arguments);
      };
      nuevo.__zgLimpio = true;
      window.renderSmartMenu = nuevo;
    }
  }
  document.addEventListener('click', function(ev){
    var btn = ev.target && ev.target.closest ? ev.target.closest('#repuestoSuggest .smart-option') : null;
    if(!btn) return;
    var main = clean(btn.querySelector('.smart-main') ? btn.querySelector('.smart-main').textContent : '');
    var sub = clean(btn.querySelector('.smart-sub') ? btn.querySelector('.smart-sub').textContent : '');
    var codigo = sub.replace(/^Código:\s*/i,'');
    ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
    try{
      var obj = {codigo: /^Registrado/i.test(sub) ? '' : codigo, detalle:main, cantidad:'1'};
      if(typeof agregarRepuestoObjeto === 'function') agregarRepuestoObjeto(obj, '1', true);
      else if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.agregar === 'function') window.zgRepuestosTablaFinal.agregar(obj, true);
    }catch(e){}
    var menu = byId('repuestoSuggest'); if(menu) menu.classList.remove('show');
    var input = byId('repuestoSearch'); if(input) input.value = '';
    return false;
  }, true);
  window.addEventListener('load', function(){ instalarRender(); setTimeout(instalarRender, 500); setTimeout(instalarRender, 1500); });
  instalarRender();
})();
</script>



<script id="zg-meta-oculto-definitivo">
(function(){
  const META_ALL=/\s*\[\[ZG_META:[A-Za-z0-9+\/_=-]+\]\]\s*/g;
  function strip(v){
    return String(v == null ? '' : v)
      .replace(META_ALL,'\n')
      .replace(/\n{3,}/g,'\n\n')
      .trim();
  }
  window.zgStripMetaFromText=strip;

  function limpiarCampo(){
    const obs=document.getElementById('observacionInicial');
    if(obs){
      const limpio=strip(obs.value);
      if(obs.value!==limpio) obs.value=limpio;
    }
    try{
      if(typeof PREINSPECCION!=='undefined' && PREINSPECCION && PREINSPECCION.observacion_inicial){
        PREINSPECCION.observacion_inicial=strip(PREINSPECCION.observacion_inicial);
      }
    }catch(e){}
  }

  // Evita que cualquier autocompletado tardío vuelva a mostrar la metadata.
  [0,80,250,600,1200,2200,4000,7000].forEach(ms=>setTimeout(limpiarCampo,ms));
  document.addEventListener('DOMContentLoaded',limpiarCampo);
  window.addEventListener('load',limpiarCampo);
  document.addEventListener('focusin',function(ev){
    if(ev.target && ev.target.id==='observacionInicial') limpiarCampo();
  },true);
  document.addEventListener('input',function(ev){
    if(ev.target && ev.target.id==='observacionInicial') limpiarCampo();
  },true);
  document.addEventListener('click',function(ev){
    if(ev.target && ev.target.closest && (ev.target.closest('#preBtn') || ev.target.closest('#pdfBtn'))){
      limpiarCampo();
      // Se limpia otra vez luego de los manejadores anteriores, antes de construir el PDF.
      setTimeout(limpiarCampo,0);
      setTimeout(limpiarCampo,60);
    }
  },true);

  // Observa cambios de la interfaz y vuelve a limpiar si otro módulo recarga la preliminar.
  const mo=new MutationObserver(limpiarCampo);
  function observar(){
    const obs=document.getElementById('observacionInicial');
    if(obs) mo.observe(obs,{childList:true,subtree:true,characterData:true,attributes:true});
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',observar); else observar();
})();
</script>


<style id="zg-materiales-por-trabajo-css">
#repuestoQuestionCard,#repuestosCard,#zgPostRepuestoMaintenanceCard{display:none!important}
.zg-work-materials,.zg-work-followup{margin:14px 0 16px;border:1.5px solid #d5e5f6;border-radius:18px;background:linear-gradient(180deg,#fff,#f8fbff);padding:14px;box-shadow:0 8px 22px rgba(16,33,58,.06)}
.zg-work-materials-head,.zg-work-followup-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px}
.zg-work-materials h4,.zg-work-followup h4{margin:0;color:#10213a;font-family:Archivo,system-ui,sans-serif;font-size:16px}
.zg-work-materials p,.zg-work-followup p{margin:3px 0 0;color:#66758a;font-size:12px;font-weight:750;line-height:1.35}
.zg-work-material-search{position:relative}
.zg-work-material-search input{width:100%;min-height:46px;border:1.5px solid #cfe0f3;border-radius:13px;padding:10px 12px;font:inherit;background:#fff;color:#10213a}
.zg-work-material-search input:focus{outline:none;border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}
.zg-work-material-menu{display:none;position:absolute;z-index:45;left:0;right:0;top:calc(100% + 5px);max-height:250px;overflow:auto;background:#fff;border:1px solid #d6e4f3;border-radius:14px;box-shadow:0 18px 36px rgba(16,33,58,.18);padding:5px}
.zg-work-material-menu.show{display:block}
.zg-work-material-option{width:100%;border:0;background:#fff;border-radius:10px;padding:9px 10px;display:flex;justify-content:space-between;align-items:center;gap:10px;text-align:left;cursor:pointer;color:#10213a}
.zg-work-material-option:hover,.zg-work-material-option:focus{background:#eaf3ff;outline:none}
.zg-work-material-option strong{display:block;font-size:12.5px}.zg-work-material-option small{display:block;color:#687b92;font-weight:750;margin-top:2px}.zg-work-material-option span{color:#1f6fc4;font-size:11px;font-weight:900}
.zg-work-material-empty{padding:10px;color:#66758a;font-size:12px;font-weight:750}
.zg-work-material-table-wrap{margin-top:11px;border:1px solid #d8e6f5;border-radius:14px;overflow:hidden;background:#fff}
.zg-work-material-table{width:100%;border-collapse:collapse;font-size:12px}
.zg-work-material-table th{background:#10213a;color:#fff;padding:9px 8px;text-align:left;font-family:Archivo,system-ui,sans-serif;font-size:10.5px;letter-spacing:.04em;text-transform:uppercase}
.zg-work-material-table td{padding:8px;border-top:1px solid #e7eef7;vertical-align:middle}.zg-work-material-table tbody tr:nth-child(even) td{background:#f7faff}
.zg-work-material-table input{width:100%;min-height:38px;border:1px solid #d2e0ef;border-radius:10px;padding:7px 8px;font:inherit;color:#10213a;background:#fff}
.zg-work-material-table .qty{width:74px;text-align:center;font-weight:900}.zg-work-material-table .unit{font-weight:900;color:#155293;white-space:nowrap}.zg-work-material-table .del{border:0;background:#fff0f0;color:#b91c1c;border-radius:10px;width:32px;height:32px;font-weight:900;cursor:pointer}
.zg-work-material-count{display:inline-flex;margin-top:10px;background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;border-radius:999px;padding:6px 10px;font-size:11px;font-weight:900}
.zg-work-followup-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.zg-work-followup .field{margin:0}.zg-work-followup select{width:100%;min-height:44px;border:1.5px solid #cfe0f3;border-radius:12px;padding:8px 10px;background:#fff;font:inherit;color:#10213a}
@media(max-width:720px){.zg-work-followup-grid{grid-template-columns:1fr}.zg-work-material-table thead{display:none}.zg-work-material-table,.zg-work-material-table tbody,.zg-work-material-table tr,.zg-work-material-table td{display:block;width:100%}.zg-work-material-table tr{border-top:1px solid #e7eef7;padding:7px}.zg-work-material-table td{border:0;padding:5px;background:#fff!important}.zg-work-material-table td:before{display:block;color:#6a7c91;font-size:9.5px;font-weight:900;text-transform:uppercase;margin-bottom:3px}.zg-work-material-table td[data-label="Código"]:before{content:'Código'}.zg-work-material-table td[data-label="Material"]:before{content:'Material / repuesto'}.zg-work-material-table td[data-label="Cantidad"]:before{content:'Cantidad'}.zg-work-material-table td[data-label="Unidad"]:before{content:'Unidad'}.zg-work-material-table .qty{width:100%}.zg-work-material-table .del{width:100%}}
</style>
<script id="zg-materiales-por-trabajo-js">
(function(){
  function byId(id){return document.getElementById(id)}
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim()}
  function norm(v){return clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[\s_-]+/g,' ')}
  function esc(v){return clean(v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]})}
  function infer(det,cod){try{return clean(window.zgInferUnidadMaterial?window.zgInferUnidadMaterial(det,cod):'und')||'und'}catch(e){return 'und'}}
  function ensureState(s){if(!Array.isArray(s.repuestosTrabajo))s.repuestosTrabajo=[];if(!s.campos||typeof s.campos!=='object')s.campos={};if(Object.prototype.hasOwnProperty.call(s.campos,'resuelto'))delete s.campos.resuelto;if(!s.mantenimientoAdicional||typeof s.mantenimientoAdicional!=='object')s.mantenimientoAdicional={requiere:'',tipo:''}}
  function controllerForWork(s){
    let marca=clean(byId('marcaEquipo')&&byId('marcaEquipo').value), ctrl=clean(byId('controladorEquipo')&&byId('controladorEquipo').value)
    const target=clean(s&&s.maquinaAsignada)
    if(target&&/^M[1-5]$/.test(target)){
      const i=target.slice(1); marca=clean(byId('zgMachineBrand'+i)&&byId('zgMachineBrand'+i).value)||marca;ctrl=clean(byId('zgMachineController'+i)&&byId('zgMachineController'+i).value)||ctrl
    }
    const joined=norm(marca+' '+ctrl)
    if((window.zgGetEquipmentType&&window.zgGetEquipmentType()==='Genset')||joined.includes('SG 3000')||joined.includes('SG 5000')){
      if(joined.includes('SG 3000'))return 'GENSET SG-3000';
      if(joined.includes('SG 5000'))return 'GENSET SG-5000';
    }
    if(joined.includes('STAR COOL')&&(joined.includes('CIM 6')||joined.includes('CIM6')))return 'STAR COOL CIM 6'
    if(joined.includes('STAR COOL')&&(joined.includes('CIM 5')||joined.includes('CIM5')))return 'STAR COOL CIM 5'
    if(joined.includes('MP5000')||joined.includes('MP 5000'))return 'TK MP5000'
    if(joined.includes('MP4000')||joined.includes('MP 4000'))return 'TK MP4000'
    if(joined.includes('CARRIER'))return 'CARRIER'
    if(joined.includes('DAIKIN'))return 'DAIKIN'
    return ''
  }
  function catalogForWork(s){
    const key=controllerForWork(s), src=key&&window.ZG_CATALOGOS_POR_CONTROLADOR?(window.ZG_CATALOGOS_POR_CONTROLADOR[key]||[]):[]
    const seen=new Set(), out=[]
    src.forEach(function(r){const codigo=clean(r.codigo),detalle=clean(r.detalle);if(!detalle)return;const k=(codigo+'|'+detalle).toUpperCase();if(seen.has(k))return;seen.add(k);out.push({codigo:codigo,detalle:detalle,unidad:clean(r.unidad)||infer(detalle,codigo)})})
    return {key:key,items:out}
  }
  function addMaterial(s,r){ensureState(s);const k=(clean(r.codigo)+'|'+clean(r.detalle)).toUpperCase();const found=s.repuestosTrabajo.find(function(x){return (clean(x.codigo)+'|'+clean(x.detalle)).toUpperCase()===k});if(found){found.cantidad=String((parseInt(found.cantidad,10)||0)+1);try{toast('Cantidad actualizada: '+r.detalle)}catch(e){}}else{s.repuestosTrabajo.push({codigo:clean(r.codigo),detalle:clean(r.detalle),cantidad:'1',unidad:clean(r.unidad)||infer(r.detalle,r.codigo)});try{toast('Material agregado a '+s.nombre)}catch(e){}}renderPanels()}
  window.zgBuildWorkMaterials=function(s){
    ensureState(s)
    const box=document.createElement('section');box.className='zg-work-materials';
    const cat=catalogForWork(s)
    box.innerHTML='<div class="zg-work-materials-head"><div><h4>🧰 Materiales / repuestos de este trabajo</h4><p>Selecciona únicamente los materiales utilizados en <b>'+esc(s.nombre)+'</b>. La cantidad se puede ajustar en la tabla.</p></div></div>'
    const search=document.createElement('div');search.className='zg-work-material-search';
    const input=document.createElement('input');input.type='search';input.autocomplete='off';input.placeholder=cat.key?'Buscar por código o nombre · '+cat.key:'Selecciona la marca y el controlador del equipo';
    const menu=document.createElement('div');menu.className='zg-work-material-menu';search.append(input,menu);box.appendChild(search)
    function renderMenu(){const q=norm(input.value),fresh=catalogForWork(s);menu.innerHTML='';if(!fresh.key){menu.innerHTML='<div class="zg-work-material-empty">Selecciona primero la marca y el controlador de la máquina atendida.</div>';menu.classList.add('show');return}const rows=fresh.items.filter(function(r){return !q||norm(r.codigo).includes(q)||norm(r.detalle).includes(q)}).slice(0,70);if(!rows.length){menu.innerHTML='<div class="zg-work-material-empty">No hay coincidencias en el catálogo de '+esc(fresh.key)+'.</div>';menu.classList.add('show');return}rows.forEach(function(r){const b=document.createElement('button');b.type='button';b.className='zg-work-material-option';b.innerHTML='<div><strong>'+esc(r.detalle)+'</strong><small>'+(r.codigo?'Código: '+esc(r.codigo):'Sin código')+' · '+esc(r.unidad||'und')+'</small></div><span>usar</span>';b.addEventListener('click',function(ev){ev.preventDefault();addMaterial(s,r)});menu.appendChild(b)});menu.classList.add('show')}
    input.addEventListener('focus',renderMenu);input.addEventListener('input',renderMenu);input.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();renderMenu()}})
    document.addEventListener('click',function(ev){if(!box.contains(ev.target))menu.classList.remove('show')},{once:true,capture:true})
    if(s.repuestosTrabajo.length){const c=document.createElement('div');c.className='zg-work-material-count';c.textContent=s.repuestosTrabajo.length+' material(es) en este trabajo';box.appendChild(c)}
    const wrap=document.createElement('div');wrap.className='zg-work-material-table-wrap';const table=document.createElement('table');table.className='zg-work-material-table';table.innerHTML='<thead><tr><th style="width:120px">Código</th><th>Material / repuesto</th><th style="width:92px">Cantidad</th><th style="width:78px">Unidad</th><th style="width:48px"></th></tr></thead><tbody></tbody>';const tbody=table.querySelector('tbody')
    if(!s.repuestosTrabajo.length){const tr=document.createElement('tr');tr.innerHTML='<td colspan="5" class="zg-work-material-empty">Aún no se agregaron materiales para este trabajo.</td>';tbody.appendChild(tr)}else{s.repuestosTrabajo.forEach(function(r,idx){r.unidad=clean(r.unidad)||infer(r.detalle,r.codigo);const tr=document.createElement('tr');tr.innerHTML='<td data-label="Código"><b>'+esc(r.codigo||'Sin código')+'</b></td><td data-label="Material"><input class="detail" value="'+esc(r.detalle)+'"></td><td data-label="Cantidad"><input class="qty" inputmode="numeric" value="'+esc(r.cantidad||'1')+'"></td><td data-label="Unidad"><span class="unit">'+esc(r.unidad)+'</span></td><td><button class="del" type="button" title="Quitar">×</button></td>';const d=tr.querySelector('.detail'),q=tr.querySelector('.qty');d.addEventListener('input',function(){r.detalle=clean(d.value);r.unidad=infer(r.detalle,r.codigo)});q.addEventListener('focus',function(){setTimeout(function(){q.select()},20)});q.addEventListener('input',function(){q.value=String(q.value||'').replace(/[^0-9.]/g,'').slice(0,7);r.cantidad=q.value});q.addEventListener('blur',function(){if(!clean(q.value)){q.value='1';r.cantidad='1'}});tr.querySelector('.del').addEventListener('click',function(){s.repuestosTrabajo.splice(idx,1);renderPanels()});tbody.appendChild(tr)})}
    wrap.appendChild(table);box.appendChild(wrap);return box
  }
  window.zgBuildWorkMaintenanceFollowup=function(){ return null; }
  window.zgWorkResolutionChanged=function(s){ensureState(s)}
  function migrateOld(){
    try{
      Object.values(state.selected||{}).forEach(ensureState)
      const any=Object.values(state.selected||{}).some(function(s){return s.repuestosTrabajo&&s.repuestosTrabajo.length})
      const ta=byId('repuestosManual');if(any||!ta||!clean(ta.value))return
      const first=Object.values(state.selected||{})[0];if(!first)return;ensureState(first)
      first.repuestosTrabajo=clean(ta.value).split(/\r?\n/).filter(Boolean).map(function(line){const p=line.split('|').map(clean);return {codigo:p[0]==='-'?'':(p[0]||''),detalle:p[1]||line,cantidad:p[2]||'1',unidad:p[3]||infer(p[1],p[0])}})
    }catch(e){console.warn(e)}
  }
  function neutralizeGlobal(){ /* La decisión global se conserva en Control final. */ }
  try{window.validarRepuestos=function(){return true};window.registrarRepuestosTecnico=async function(){return true}}catch(e){}
  document.addEventListener('DOMContentLoaded',function(){[80,300,800,1500].forEach(function(ms){setTimeout(function(){migrateOld();neutralizeGlobal();renderPanels()},ms)})})
  window.addEventListener('load',function(){setTimeout(function(){migrateOld();neutralizeGlobal();renderPanels()},500)})
  document.addEventListener('click',function(ev){if(ev.target&&ev.target.closest&&ev.target.closest('#pdfBtn'))neutralizeGlobal()},true)

  // Mantiene sincronizada la respuesta de mantenimiento aunque los paneles se reconstruyan.
  document.addEventListener('change',function(ev){
    const el=ev.target;
    if(!el || !el.id) return;
    let m=el.id.match(/^zg_work_maint_req_(.+)$/);
    let isType=false;
    if(!m){ m=el.id.match(/^zg_work_maint_type_(.+)$/); isType=true; }
    if(!m) return;
    try{
      const workId=m[1];
      const work=(window.state&&window.state.selected)?window.state.selected[workId]:null;
      if(!work) return;
      ensureState(work);
      if(isType) work.mantenimientoAdicional.tipo=clean(el.value);
      else {
        work.mantenimientoAdicional.requiere=clean(el.value);
        if(clean(el.value)!=='Sí') work.mantenimientoAdicional.tipo='';
      }
    }catch(e){ console.warn('No se pudo sincronizar mantenimiento del trabajo',e); }
  },true);
})();
</script>

\n






<!-- ZGROUP V21: la decisión final “No requiere repuesto” elimina toda tabla del PDF -->
<script id="zg-v21-no-materials-final-override">
(function(){
  'use strict';
  function el(id){return document.getElementById(id);}
  function noSeleccionado(){
    const h=el('requiereRepuesto'), no=el('repuestoNoBtn'), si=el('repuestoSiBtn');
    if(no && no.classList.contains('on')) return true;
    if(si && si.classList.contains('on')) return false;
    return !h || String(h.value||'').toLowerCase()!=='si';
  }
  function purgar(){
    if(!noSeleccionado()) return false;
    const h=el('requiereRepuesto'), ta=el('repuestosManual');
    if(h) h.value='no';
    if(ta) ta.value='';
    window.ZG_WORK_MATERIALS_PDF={};
    try{ window.repuestosSeleccionados=[]; }catch(e){}
    try{
      if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function'){
        const a=window.zgRepuestosTablaFinal.materiales();
        if(Array.isArray(a)) a.splice(0,a.length);
      }
    }catch(e){}
    try{
      Object.values((typeof state!=='undefined' && state.selected)?state.selected:{}).forEach(function(s){
        if(!s)return;
        ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(function(k){s[k]=Array.isArray(s[k])?[]:'';});
        if(s.campos) ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(function(k){s.campos[k]=Array.isArray(s.campos[k])?[]:'';});
      });
    }catch(e){}
    return true;
  }
  document.addEventListener('click',function(ev){
    const t=ev.target&&ev.target.closest?ev.target.closest('#repuestoNoBtn,#pdfBtn'):null;
    if(!t)return;
    if(t.id==='repuestoNoBtn'){setTimeout(purgar,0);setTimeout(purgar,60);setTimeout(purgar,180);}
    else if(t.id==='pdfBtn') purgar();
  },true);
  document.addEventListener('submit',purgar,true);
  window.zgPurgarMaterialesSiNoRequiere=purgar;
})();
</script>

<script id="zg-v24-fecha-edicion-final">
(function(){
  if(typeof ZG_EDIT_MODE === 'undefined' || !ZG_EDIT_MODE) return;
  const fecha = document.getElementById('fecha');
  if(!fecha) return;
  let usuarioCambioFecha = false;
  fecha.addEventListener('input', function(){ usuarioCambioFecha = true; window.ZG_FECHA_PDF_ACTUAL = String(fecha.value || '').trim(); }, true);
  fecha.addEventListener('change', function(){ usuarioCambioFecha = true; window.ZG_FECHA_PDF_ACTUAL = String(fecha.value || '').trim(); }, true);

  // Algunos módulos antiguos recargan datos de la preliminar con retardos.
  // Una vez que el supervisor cambia la fecha, se conserva su elección.
  let fechaElegida = '';
  const recordar = function(){
    if(usuarioCambioFecha){
      fechaElegida = String(fecha.value || '').trim();
      window.ZG_FECHA_PDF_ACTUAL = fechaElegida;
    }else if(fechaElegida && fecha.value !== fechaElegida){
      fecha.value = fechaElegida;
    }
  };
  setInterval(recordar, 250);
})();
</script>


</body>
</html><?php
}

function zgroup_render_menu() {
    ?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Área técnica — ZGROUP</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Manrope:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<?= zgroup_html_base_css() ?>
</head>
<body>
<section class="zg-screen">
  <div class="zg-card">
    <div class="zg-top">
      <div class="zg-brand">
        <img src="zgroup-logo.png" alt="ZGROUP">
        <div>
          <span class="zg-kicker">Área técnica</span>
          <h1 class="zg-title">Selecciona el tipo de trabajo</h1>
          <p class="zg-sub">Elige si el servicio se realizará en cliente o en base.</p>
        </div>
      </div>
      <a class="zg-logout" href="?salir=1">Cerrar acceso</a>
    </div>
    <div class="zg-body">
      <div class="zg-actions">
        <a class="zg-option" href="index.php?modo=cliente">
          <div>
            <div class="zg-icon">📍</div>
            <h2>Trabajo en cliente</h2>
            <p>Registra inspección preliminar, ubicación, evidencias, trabajos realizados y genera el informe final.</p>
          </div>
          <span class="zg-go">Ingresar al registro</span>
        </a>
        <a class="zg-option" href="index.php?modo=base">
          <div>
            <div class="zg-icon">🏭</div>
            <h2>Trabajo en base</h2>
            <p>Módulo reservado para servicios internos, mantenimiento en patio o trabajos realizados dentro de base.</p>
          </div>
          <span class="zg-go zg-muted-btn">Ver módulo</span>
        </a>
      </div>
    </div>
  </div>
</section>


<script>
/* Fix rápido del selector de ubicación */
(function(){
  const PE_CENTER = [-12.0464, -77.0428];
  let zgMap = null;
  let zgMarker = null;
  let zgCurrent = null;
  let zgSelected = null;
  let zgLoadingLeaflet = false;

  function el(id){ return document.getElementById(id); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

  function ensureLeaflet(done){
    if(window.L && typeof L.map === 'function'){ done(); return; }
    if(!document.querySelector('link[data-zg-leaflet]')){
      const link=document.createElement('link');
      link.rel='stylesheet';
      link.href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      link.setAttribute('data-zg-leaflet','1');
      document.head.appendChild(link);
    }
    if(zgLoadingLeaflet){ setTimeout(function(){ ensureLeaflet(done); }, 250); return; }
    zgLoadingLeaflet = true;
    const sc=document.createElement('script');
    sc.src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    sc.onload=function(){ zgLoadingLeaflet=false; done(); };
    sc.onerror=function(){
      zgLoadingLeaflet=false;
      const addr=el('pickAddr');
      if(addr) addr.innerHTML='<b>No se pudo cargar el mapa.</b> Revisa internet y vuelve a intentar.';
    };
    document.head.appendChild(sc);
  }

  function pinIcon(){
    return L.divIcon({
      className:'',
      html:'<div style="width:26px;height:26px;background:#e03131;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px rgba(0,0,0,.38)"></div>',
      iconSize:[26,26],
      iconAnchor:[13,26]
    });
  }

  function blueIcon(){
    return L.divIcon({
      className:'',
      html:'<div style="width:16px;height:16px;background:#1f6fc4;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 6px rgba(31,111,196,.20),0 3px 10px rgba(0,0,0,.30)"></div>',
      iconSize:[16,16],
      iconAnchor:[8,8]
    });
  }

  function prepareBox(){
    const box=el('pickMap');
    if(!box) return null;
    box.style.display='block';
    box.style.position='relative';
    box.style.background='#e9eef3';
    box.style.minHeight = window.innerWidth <= 780 ? '46vh' : '360px';
    box.style.height = window.innerWidth <= 780 ? 'calc(100dvh - 245px)' : '420px';
    return box;
  }

  function openFixedMap(){
    const modal=el('mapModal');
    const addr=el('pickAddr');
    const box=prepareBox();
    if(!modal || !box) return;
    modal.classList.add('show');
    if(addr && !zgSelected) addr.innerHTML='Toca el mapa o arrastra el pin para elegir el punto exacto…';

    ensureLeaflet(function(){
      setTimeout(function(){
        try{
          if(zgMap){
            zgMap.invalidateSize(true);
            return;
          }

          // Limpia cualquier intento anterior que haya quedado a medias.
          if(box._leaflet_id){ box._leaflet_id = null; box.innerHTML = ''; }

          zgMap = L.map(box, { zoomControl:true, attributionControl:true }).setView(PE_CENTER, 12);
          L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom:19,
            attribution:'&copy; OpenStreetMap'
          }).addTo(zgMap);

          zgMap.on('click', function(ev){ setSelected(ev.latlng.lat, ev.latlng.lng, true); });
          setTimeout(function(){ zgMap.invalidateSize(true); }, 80);
          setTimeout(function(){ zgMap.invalidateSize(true); }, 350);
          setTimeout(function(){ zgMap.invalidateSize(true); }, 800);

          if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(function(pos){
              const lat=pos.coords.latitude;
              const lng=pos.coords.longitude;
              if(zgCurrent) zgCurrent.setLatLng([lat,lng]);
              else zgCurrent = L.marker([lat,lng], {icon:blueIcon(), interactive:false}).addTo(zgMap);
              zgMap.setView([lat,lng], 17);
              if(!zgSelected) setSelected(lat,lng,true);
            }, function(){}, {enableHighAccuracy:true, timeout:10000, maximumAge:0});
          }
        }catch(err){
          console.error('Mapa ZGROUP:', err);
          if(addr) addr.innerHTML='<b>No se pudo iniciar el mapa.</b> Recarga la página e intenta otra vez.';
        }
      }, 160);
    });
  }

  function setSelected(lat,lng,doReverse){
    if(!zgMap || !window.L) return;
    zgSelected = {lat:Number(lat), lng:Number(lng), addr:''};
    if(zgMarker) zgMarker.setLatLng([lat,lng]);
    else{
      zgMarker = L.marker([lat,lng], {icon:pinIcon(), draggable:true}).addTo(zgMap);
      zgMarker.on('dragend', function(){
        const p=zgMarker.getLatLng();
        setSelected(p.lat, p.lng, true);
      });
    }
    const addr=el('pickAddr');
    if(addr) addr.innerHTML='<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'<br><small>Buscando dirección exacta…</small>';
    updateRoute();
    if(doReverse) reverseAddress(lat,lng);
  }

  async function reverseAddress(lat,lng){
    const addr=el('pickAddr');
    try{
      const r=await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+encodeURIComponent(lat)+'&lon='+encodeURIComponent(lng)+'&zoom=18&addressdetails=1&accept-language=es');
      const d=await r.json();
      const text=d.display_name || '';
      if(zgSelected) zgSelected.addr=text;
      if(addr){
        addr.innerHTML = text
          ? '<b>Dirección:</b> '+esc(text)+'<br><small>Coordenadas: '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'</small>'
          : '<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6);
      }
    }catch(e){
      if(addr) addr.innerHTML='<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'<br><small>No se pudo obtener la calle; se guardarán las coordenadas.</small>';
    }
    updateRoute();
  }

  async function searchAddress(){
    const q=(el('mapSearch') && el('mapSearch').value || '').trim();
    const addr=el('pickAddr');
    const sug=el('mapSug');
    if(!q) return;
    if(addr) addr.textContent='Buscando…';
    try{
      const url='https://nominatim.openstreetmap.org/search?format=jsonv2&q='+encodeURIComponent(q)+'&limit=8&addressdetails=1&countrycodes=pe&accept-language=es';
      const r=await fetch(url);
      const arr=await r.json();
      if(sug){ sug.innerHTML=''; sug.classList.remove('show'); }
      if(!arr || !arr.length){
        if(addr) addr.innerHTML='No se encontró ese lugar. Escribe calle, distrito y ciudad, o toca el mapa para marcar el punto.';
        return;
      }
      if(sug){
        arr.forEach(function(it){
          const div=document.createElement('div');
          div.className='sug-item';
          div.innerHTML='<div class="sug-main">'+esc((it.display_name||'').split(',')[0])+'</div><div class="sug-sub">'+esc(it.display_name||'')+'</div>';
          div.addEventListener('mousedown', function(ev){
            ev.preventDefault();
            pickSearchItem(it);
            sug.classList.remove('show');
          });
          sug.appendChild(div);
        });
        sug.classList.add('show');
      }
      pickSearchItem(arr[0]);
    }catch(e){
      if(addr) addr.innerHTML='No se pudo buscar. Revisa internet o marca el punto en el mapa.';
    }
  }

  function pickSearchItem(it){
    const lat=parseFloat(it.lat), lng=parseFloat(it.lon);
    if(!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    const text=it.display_name || '';
    if(el('mapSearch')) el('mapSearch').value=text;
    if(zgMap) zgMap.setView([lat,lng],18);
    setSelected(lat,lng,false);
    if(zgSelected) zgSelected.addr=text;
    if(el('pickAddr')) el('pickAddr').innerHTML='<b>Dirección:</b> '+esc(text)+'<br><small>Coordenadas: '+lat.toFixed(6)+', '+lng.toFixed(6)+'</small>';
    updateRoute();
  }

  function updateRoute(){
    const route=el('mapRoute');
    if(!route || !zgSelected){ return; }
    route.href='https://www.google.com/maps/dir/?api=1&destination='+zgSelected.lat+','+zgSelected.lng;
    route.style.display='inline-flex';
  }

  function confirmFixedMap(){
    if(!zgSelected){
      if(typeof toast === 'function') toast('Elige un punto en el mapa primero');
      return;
    }
    const lat=Number(zgSelected.lat), lng=Number(zgSelected.lng);
    const direccion=el('direccion');
    const coords=el('direccionCoords');
    if(direccion) direccion.value = zgSelected.addr || (lat.toFixed(6)+', '+lng.toFixed(6));
    if(coords) coords.value = lat.toFixed(6)+', '+lng.toFixed(6);
    const origen=el('direccionOrigenOdoo'); if(origen) origen.value='';
    const modal=el('mapModal');
    if(modal) modal.classList.remove('show');
    if(typeof toast === 'function') toast('Ubicación seleccionada');
    if(typeof validateField === 'function') validateField('direccion');
  }

  // Captura antes de cualquier manejador anterior para evitar que se quede el mapa en blanco.
  document.addEventListener('click', function(e){
    if(e.target.closest('#dirPick') || e.target.closest('#direccion')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      openFixedMap();
      return false;
    }
    if(e.target.closest('#mapSearchBtn')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      searchAddress();
      return false;
    }
    if(e.target.closest('#mapConfirm')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      confirmFixedMap();
      return false;
    }
  }, true);

  document.addEventListener('keydown', function(e){
    if(e.target && e.target.id === 'mapSearch' && e.key === 'Enter'){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      searchAddress();
      return false;
    }
  }, true);

  window.openMap = openFixedMap;
})();
</script>



<style>
/* ============================================================
   ZGROUP - tabla real de repuestos seleccionados
   ============================================================ */
#repuestosSelectedList.zg-table-ready{display:block!important;margin-top:12px!important}
.zg-repuestos-table-wrap{border:1.5px solid #cfe0f4;border-radius:18px;overflow:hidden;background:#fff;box-shadow:0 14px 30px rgba(16,33,58,.10);margin-top:10px}
.zg-repuestos-table{width:100%;border-collapse:collapse;font-size:13px;background:#fff}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#eef6ff,#e7f0fb);color:#155293;font-family:'Archivo',system-ui,sans-serif;font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;text-align:left;padding:11px 12px;border-bottom:1px solid #d7e5f5;border-right:1px solid #d7e5f5}
.zg-repuestos-table thead th:last-child{border-right:none;text-align:center}
.zg-repuestos-table td{padding:10px 12px;border-bottom:1px solid #edf3fa;border-right:1px solid #edf3fa;vertical-align:middle}
.zg-repuestos-table td:last-child{border-right:none;text-align:center}.zg-repuestos-table tr:last-child td{border-bottom:none}
.zg-rep-code2{font-family:'Archivo',system-ui,sans-serif;font-weight:900;color:#1f6fc4;word-break:break-word}.zg-rep-code2.empty{color:#8a5a00}
.zg-rep-detail2{width:100%;border:1.5px solid #d7e5f5;background:#f8fbff;border-radius:12px;padding:10px 11px;font:inherit;font-weight:850;color:#10213a;outline:none}.zg-rep-detail2:focus{border-color:#1f6fc4;background:#fff;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-qty2{width:96px;border:1.5px solid #d7e5f5;background:#fff;border-radius:12px;padding:10px 8px;font:inherit;font-family:'Archivo',system-ui,sans-serif;font-weight:900;text-align:center;color:#10213a;outline:none}.zg-rep-qty2:focus{border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-del2{width:36px;height:36px;border:none;border-radius:12px;background:#fff1f1;color:#b91c1c;font-size:18px;font-weight:900;cursor:pointer}.zg-rep-del2:hover{filter:brightness(.96);transform:translateY(-1px)}
.zg-rep-count2{display:inline-flex!important;align-items:center;gap:8px;background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:900;margin-top:10px}.zg-rep-count2:before{content:'✓'}
.zg-rep-tip2{font-size:11.5px;color:#66758a;font-weight:800;margin-top:4px}.zg-rep-tip2 b{color:#10213a}
#repuestosEmpty{margin-top:12px!important}
.repuesto-smart .smart-menu{z-index:99999!important;pointer-events:auto!important}.repuesto-smart .smart-option{pointer-events:auto!important;cursor:pointer!important}.repuesto-smart .smart-option *{pointer-events:none!important}
@media(max-width:720px){.zg-repuestos-table thead{display:none}.zg-repuestos-table,.zg-repuestos-table tbody,.zg-repuestos-table tr,.zg-repuestos-table td{display:block;width:100%}.zg-repuestos-table tr{border-bottom:1px solid #edf3fa}.zg-repuestos-table td{border-right:none}.zg-repuestos-table td::before{display:block;margin-bottom:5px;font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;color:#66758a;font-family:'Archivo';font-weight:900}.zg-repuestos-table td[data-label="Código"]::before{content:'Código'}.zg-repuestos-table td[data-label="Material"]::before{content:'Material / repuesto'}.zg-repuestos-table td[data-label="Cantidad"]::before{content:'Cantidad'}.zg-rep-qty2{width:130px}.zg-rep-del2{width:100%}}
</style>
<script>
/* ============================================================
   ZGROUP - selección real de repuestos + tabla + PDF
   - Reemplaza la lista anterior sin depender de los handlers viejos.
   ============================================================ */
(function(){
  function el(id){ return document.getElementById(id); }
  function clean(s){ return String(s || '').replace(/\s+/g,' ').trim(); }
  function norm(s){ return clean(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  function toast2(t){ try{ if(typeof toast === 'function') toast(t); else console.log(t); }catch(e){ console.log(t); } }
  function catalogo(){ try{ return Array.isArray(REPUESTOS_CATALOGO) ? REPUESTOS_CATALOGO : []; }catch(e){ return []; } }

  let materiales = [];

  function parseLinea(linea){
    linea = clean(linea); if(!linea) return null;
    const p = linea.split('|').map(clean);
    let codigo='', detalle='', cantidad='1', unidad='';

    /*
       Formato actual: CODIGO | MATERIAL | CANTIDAD | UNIDAD
       Formato anterior: CODIGO | MATERIAL | CANTIDAD
       Se reconocen ambos para no perder cantidades al agregar otro material.
    */
    if(p.length >= 4){
      codigo = p[0] === '-' ? '' : p[0];
      cantidad = p[p.length - 2] || '1';
      unidad = p[p.length - 1] || '';
      detalle = p.slice(1, -2).join(' | ') || p[1] || '';
    } else if(p.length === 3){
      codigo = p[0] === '-' ? '' : p[0];
      detalle = p[1] || '';
      cantidad = p[2] || '1';
    } else if(p.length === 2){
      codigo = p[0] === '-' ? '' : p[0];
      detalle = p[1] || '';
    } else {
      detalle = linea;
    }

    detalle = clean(detalle.replace(/\s*\|\s*/g,' '));
    cantidad = String(cantidad || '1').replace(/[^0-9]/g,'').slice(0,4) || '1';
    if(!detalle) return null;
    return {codigo, detalle, cantidad, unidad};
  }
  function cargarDesdeTextarea(){
    const ta = el('repuestosManual');
    const txt = ta ? ta.value : '';
    const arr = txt.split(/\r?\n/).map(parseLinea).filter(Boolean);
    if(arr.length) materiales = arr;
    return materiales;
  }
  function linea(x){ return (clean(x.codigo)||'-') + ' | ' + clean(x.detalle) + ' | ' + (String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1'); }
  function guardar(){
    const ta = el('repuestosManual');
    if(ta){
      ta.value = materiales.filter(x=>clean(x.detalle)).map(linea).join('\n');
      ta.classList.remove('input-error');
    }
    try{ repuestosSeleccionados = materiales.map(x => ({codigo:clean(x.codigo), detalle:clean(x.detalle), cantidad:String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1', nuevo:!clean(x.codigo)})); }catch(e){}
    const err = el('repuestosManualError');
    if(err){ err.textContent=''; err.classList.remove('show'); }
    try{ if(window.zgroupMarcarCambio) window.zgroupMarcarCambio(); }catch(e){}
  }
  function pintar(){
    const box = el('repuestosSelectedList');
    const empty = el('repuestosEmpty');
    if(!box) return;
    box.classList.add('zg-table-ready');
    box.innerHTML = '';
    if(empty) empty.classList.toggle('show', materiales.length === 0);
    if(!materiales.length){ guardar(); return; }

    const count = document.createElement('div');
    count.className = 'zg-rep-count2';
    count.textContent = materiales.length + ' material(es) seleccionado(s)';
    box.appendChild(count);

    const wrap = document.createElement('div');
    wrap.className = 'zg-repuestos-table-wrap';
    const table = document.createElement('table');
    table.className = 'zg-repuestos-table';
    table.innerHTML = '<thead><tr><th style="width:145px">Código</th><th>Material / repuesto</th><th style="width:120px">Cantidad</th><th style="width:56px"></th></tr></thead><tbody></tbody>';
    const tbody = table.querySelector('tbody');

    materiales.forEach(function(it, idx){
      const tr = document.createElement('tr');
      const cod = clean(it.codigo) || 'Sin código';
      tr.innerHTML =
        '<td data-label="Código"><div class="zg-rep-code2 '+(!clean(it.codigo)?'empty':'')+'">'+esc(cod)+'</div></td>'+ 
        '<td data-label="Material"><input class="zg-rep-detail2" type="text" value="'+esc(it.detalle)+'" placeholder="Nombre del material"><div class="zg-rep-tip2">Puedes corregir el nombre antes de generar el PDF.</div></td>'+ 
        '<td data-label="Cantidad"><input class="zg-rep-qty2" type="text" inputmode="numeric" value="'+esc(it.cantidad||'1')+'"></td>'+ 
        '<td><button type="button" class="zg-rep-del2" title="Quitar">×</button></td>';
      const det = tr.querySelector('.zg-rep-detail2');
      const qty = tr.querySelector('.zg-rep-qty2');
      const del = tr.querySelector('.zg-rep-del2');
      det.addEventListener('input', function(){ materiales[idx].detalle = clean(det.value); guardar(); });
      qty.addEventListener('input', function(){ let v=String(qty.value||'').replace(/[^0-9]/g,'').slice(0,4); qty.value=v; materiales[idx].cantidad=v; guardar(); });
      qty.addEventListener('focus', function(){ setTimeout(function(){ try{ qty.select(); }catch(e){} }, 30); });
      qty.addEventListener('blur', function(){ if(!String(qty.value||'').trim()){ qty.value='1'; materiales[idx].cantidad='1'; guardar(); } });
      del.addEventListener('click', function(){ materiales.splice(idx,1); guardar(); pintar(); });
      tbody.appendChild(tr);
    });
    wrap.appendChild(table);
    box.appendChild(wrap);
    guardar();
  }
  function activarSi(){
    const h=el('requiereRepuesto'); if(h) h.value='si';
    const card=el('repuestosCard'); if(card) card.classList.remove('is-hidden');
    const si=el('repuestoSiBtn'), no=el('repuestoNoBtn'); if(si) si.classList.add('on'); if(no) no.classList.remove('on');
  }
  function existe(item){
    const d=norm(item.detalle), c=norm(item.codigo);
    return materiales.some(x => (c && norm(x.codigo)===c) || (d && norm(x.detalle)===d));
  }
  function agregar(item, aviso){
    item = item || {};
    const obj = {codigo:clean(item.codigo||''), detalle:clean(item.detalle||''), cantidad:String(item.cantidad||'1').replace(/[^0-9]/g,'').slice(0,4)||'1'};
    if(!obj.detalle) return false;
    activarSi();
    cargarDesdeTextarea();
    if(existe(obj)){ if(aviso!==false) toast2('Ya está seleccionado: '+obj.detalle); pintar(); return false; }
    materiales.push(obj);
    const input=el('repuestoSearch'); if(input) input.value='';
    const menu=el('repuestoSuggest'); if(menu) menu.classList.remove('show');
    pintar();
    if(aviso!==false) toast2('Agregado a la tabla: '+obj.detalle+' · Cantidad '+obj.cantidad);
    return true;
  }
  function buscar(det){
    const d=norm(det); if(!d) return null;
    return catalogo().find(r=>norm(r.detalle||'')===d) || catalogo().find(r=>norm(r.detalle||'').includes(d)) || null;
  }
  function agregarManual(){
    const input=el('repuestoSearch');
    const det=clean(input?input.value:'');
    if(!det){ if(input) input.focus(); toast2('Escribe o selecciona un material'); return false; }
    const cat=buscar(det);
    return agregar(cat ? {codigo:cat.codigo||'', detalle:cat.detalle||det, cantidad:'1'} : {codigo:'', detalle:det, cantidad:'1'}, true);
  }
  function mostrarMenuPropio(menuId, items){
    const menu = el(menuId); if(!menu) return;
    menu.innerHTML='';
    if(!items || !items.length){ menu.classList.remove('show'); return; }
    items.forEach(function(item){
      const raw = item.raw || item;
      const b = document.createElement('button');
      b.type='button'; b.className='smart-option';
      b.innerHTML='<div><span class="smart-main">'+esc(item.main||raw.detalle||'')+'</span><span class="smart-sub">'+esc(item.sub || (raw.codigo ? 'Código: '+raw.codigo : 'Pendiente de revisión'))+'</span></div><span class="smart-badge">usar</span>';
      b.addEventListener('click', function(ev){ ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); agregar({codigo:raw.codigo||'', detalle:raw.detalle||item.main||'', cantidad:'1'}, true); return false; }, true);
      menu.appendChild(b);
    });
    menu.classList.add('show');
  }

  // Reemplaza el render del menú solo para repuestos. Los otros catálogos siguen igual.
  try{
    const oldRender = renderSmartMenu;
    renderSmartMenu = function(menuId, items, onPick){
      if(menuId === 'repuestoSuggest') return mostrarMenuPropio(menuId, items || []);
      return oldRender(menuId, items, onPick);
    };
  }catch(e){}

  // Captura adicional por si el menú viejo ya quedó pintado.
  document.addEventListener('click', function(ev){
    const btn = ev.target && ev.target.closest ? ev.target.closest('#repuestoSuggest .smart-option') : null;
    if(btn){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
      const main = clean(btn.querySelector('.smart-main') ? btn.querySelector('.smart-main').textContent : btn.textContent.replace(/usar\s*$/i,''));
      const cat = buscar(main);
      agregar(cat ? {codigo:cat.codigo||'', detalle:cat.detalle||main, cantidad:'1'} : {codigo:'', detalle:main, cantidad:'1'}, true);
      return false;
    }
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoAddManual')){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); agregarManual(); return false;
    }
    if(ev.target && ev.target.closest && (ev.target.closest('#pdfBtn') || ev.target.closest('#preBtn'))){ guardar(); }
  }, true);

  document.addEventListener('keydown', function(ev){
    if(ev.target && ev.target.id === 'repuestoSearch' && ev.key === 'Enter'){
      ev.preventDefault(); ev.stopPropagation(); agregarManual(); return false;
    }
  }, true);

  try{ insertarRepuestoLinea = function(r){ return agregar({codigo:r&&r.codigo||'', detalle:r&&r.detalle||'', cantidad:'1'}, true); }; }catch(e){}
  try{ agregarRepuestoObjeto = function(r,cantidad,aviso){ return agregar({codigo:r&&r.codigo||'', detalle:r&&r.detalle||'', cantidad:cantidad||'1'}, aviso!==false); }; }catch(e){}
  try{ agregarRepuestoManual = agregarManual; }catch(e){}
  try{ renderRepuestosSeleccionados = pintar; }catch(e){}
  try{ syncRepuestosManual = guardar; }catch(e){}
  try{ validarRepuestos = function(){ if(clean(el('repuestoSearch')?el('repuestoSearch').value:'')) agregarManual(); guardar(); if((el('requiereRepuesto')&&el('requiereRepuesto').value==='si') && !clean(el('repuestosManual')?el('repuestosManual').value:'')){ toast2('Agrega al menos un material en la tabla'); return false; } return true; }; }catch(e){}
  try{ registrarRepuestosTecnico = async function(){ guardar(); const txt=clean(el('repuestosManual')?el('repuestosManual').value:''); if(!txt) return true; try{ const fd=new FormData(); fd.append('repuestos', txt); await fetch('registrar_repuestos_tecnico.php',{method:'POST', body:fd}); }catch(e){} return true; }; }catch(e){}

  window.zgRepuestosTablaFinal = {agregar, pintar, guardar, materiales:function(){return materiales;}};
  window.addEventListener('load', function(){ cargarDesdeTextarea(); pintar(); const hint=el('repuestoHint'); if(hint) hint.textContent='Selecciona “usar” y aparecerá abajo en una tabla con cantidad.'; });
  document.addEventListener('DOMContentLoaded', function(){ cargarDesdeTextarea(); pintar(); });
})();
</script>


<style>
/* Ajuste final: tabla de repuestos más clara y cantidad editable sin quedarse pegada al 1 */
.zg-repuestos-table-wrap{border:1.5px solid #c6dcf5!important;border-radius:18px!important;overflow:hidden!important;background:#fff!important;box-shadow:0 14px 34px rgba(16,33,58,.12)!important}
.zg-repuestos-table{font-size:13.2px!important;background:#fff!important}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#18345c,#10213a)!important;color:#fff!important;padding:12px 12px!important;border-right:1px solid rgba(255,255,255,.18)!important;text-align:left!important}
.zg-repuestos-table thead th:nth-child(3),.zg-repuestos-table thead th:nth-child(4){text-align:center!important}
.zg-repuestos-table td{background:#fff!important;padding:11px 12px!important;border-bottom:1px solid #edf3fa!important;border-right:1px solid #edf3fa!important}
.zg-repuestos-table tbody tr:nth-child(even) td{background:#f8fbff!important}
.zg-rep-code2{font-size:12.8px!important;color:#1f6fc4!important}.zg-rep-code2.empty{color:#9b6a00!important}
.zg-rep-detail2{background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;min-height:42px!important}
.zg-rep-detail2:focus,.zg-rep-qty2:focus{border-color:#1f6fc4!important;box-shadow:0 0 0 3px #e7f0fb!important}
.zg-rep-qty2{width:92px!important;background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;font-size:15px!important;color:#10213a!important;text-align:center!important;caret-color:#1f6fc4!important}
.zg-rep-del2{background:#fff1f1!important;color:#c92a2a!important;border:1px solid #ffd6d6!important}
.zg-rep-count2{margin-bottom:9px!important;background:#eaf7ef!important;color:#176b34!important;border:1px solid #bfe8cc!important}
</style>
</body>
</html><?php
}

function zgroup_render_base_en_proceso() {
    ?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trabajo en base — En proceso</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Manrope:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<?= zgroup_html_base_css() ?>
</head>
<body>
<section class="zg-screen">
  <div class="zg-card">
    <div class="zg-top">
      <div class="zg-brand">
        <img src="zgroup-logo.png" alt="ZGROUP">
        <div>
          <span class="zg-kicker">Trabajo en base</span>
          <h1 class="zg-title">Módulo en proceso</h1>
          <p class="zg-sub">Estamos preparando esta sección para registrar trabajos en base.</p>
        </div>
      </div>
      <a class="zg-logout" href="?salir=1">Cerrar acceso</a>
    </div>
    <div class="zg-body zg-process">
      <div>
        <h2 style="font-family:Archivo,system-ui,sans-serif;font-size:30px;letter-spacing:-.03em;margin-bottom:10px">Página en desarrollo</h2>
        <p class="zg-sub">Próximamente podrás registrar trabajos en base, controlar tiempos, evidencias y cierre técnico desde este módulo.</p>
        <div class="zg-progress"><span></span></div>
        <a class="zg-back" href="index.php">Volver al menú general</a>
      </div>
      <div class="zg-anim"><div class="zg-gear">⚙️</div></div>
    </div>
  </div>
</section>


<script>
/* Fix rápido del selector de ubicación */
(function(){
  const PE_CENTER = [-12.0464, -77.0428];
  let zgMap = null;
  let zgMarker = null;
  let zgCurrent = null;
  let zgSelected = null;
  let zgLoadingLeaflet = false;

  function el(id){ return document.getElementById(id); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

  function ensureLeaflet(done){
    if(window.L && typeof L.map === 'function'){ done(); return; }
    if(!document.querySelector('link[data-zg-leaflet]')){
      const link=document.createElement('link');
      link.rel='stylesheet';
      link.href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      link.setAttribute('data-zg-leaflet','1');
      document.head.appendChild(link);
    }
    if(zgLoadingLeaflet){ setTimeout(function(){ ensureLeaflet(done); }, 250); return; }
    zgLoadingLeaflet = true;
    const sc=document.createElement('script');
    sc.src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    sc.onload=function(){ zgLoadingLeaflet=false; done(); };
    sc.onerror=function(){
      zgLoadingLeaflet=false;
      const addr=el('pickAddr');
      if(addr) addr.innerHTML='<b>No se pudo cargar el mapa.</b> Revisa internet y vuelve a intentar.';
    };
    document.head.appendChild(sc);
  }

  function pinIcon(){
    return L.divIcon({
      className:'',
      html:'<div style="width:26px;height:26px;background:#e03131;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px rgba(0,0,0,.38)"></div>',
      iconSize:[26,26],
      iconAnchor:[13,26]
    });
  }

  function blueIcon(){
    return L.divIcon({
      className:'',
      html:'<div style="width:16px;height:16px;background:#1f6fc4;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 6px rgba(31,111,196,.20),0 3px 10px rgba(0,0,0,.30)"></div>',
      iconSize:[16,16],
      iconAnchor:[8,8]
    });
  }

  function prepareBox(){
    const box=el('pickMap');
    if(!box) return null;
    box.style.display='block';
    box.style.position='relative';
    box.style.background='#e9eef3';
    box.style.minHeight = window.innerWidth <= 780 ? '46vh' : '360px';
    box.style.height = window.innerWidth <= 780 ? 'calc(100dvh - 245px)' : '420px';
    return box;
  }

  function openFixedMap(){
    const modal=el('mapModal');
    const addr=el('pickAddr');
    const box=prepareBox();
    if(!modal || !box) return;
    modal.classList.add('show');
    if(addr && !zgSelected) addr.innerHTML='Toca el mapa o arrastra el pin para elegir el punto exacto…';

    ensureLeaflet(function(){
      setTimeout(function(){
        try{
          if(zgMap){
            zgMap.invalidateSize(true);
            return;
          }

          // Limpia cualquier intento anterior que haya quedado a medias.
          if(box._leaflet_id){ box._leaflet_id = null; box.innerHTML = ''; }

          zgMap = L.map(box, { zoomControl:true, attributionControl:true }).setView(PE_CENTER, 12);
          L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom:19,
            attribution:'&copy; OpenStreetMap'
          }).addTo(zgMap);

          zgMap.on('click', function(ev){ setSelected(ev.latlng.lat, ev.latlng.lng, true); });
          setTimeout(function(){ zgMap.invalidateSize(true); }, 80);
          setTimeout(function(){ zgMap.invalidateSize(true); }, 350);
          setTimeout(function(){ zgMap.invalidateSize(true); }, 800);

          if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(function(pos){
              const lat=pos.coords.latitude;
              const lng=pos.coords.longitude;
              if(zgCurrent) zgCurrent.setLatLng([lat,lng]);
              else zgCurrent = L.marker([lat,lng], {icon:blueIcon(), interactive:false}).addTo(zgMap);
              zgMap.setView([lat,lng], 17);
              if(!zgSelected) setSelected(lat,lng,true);
            }, function(){}, {enableHighAccuracy:true, timeout:10000, maximumAge:0});
          }
        }catch(err){
          console.error('Mapa ZGROUP:', err);
          if(addr) addr.innerHTML='<b>No se pudo iniciar el mapa.</b> Recarga la página e intenta otra vez.';
        }
      }, 160);
    });
  }

  function setSelected(lat,lng,doReverse){
    if(!zgMap || !window.L) return;
    zgSelected = {lat:Number(lat), lng:Number(lng), addr:''};
    if(zgMarker) zgMarker.setLatLng([lat,lng]);
    else{
      zgMarker = L.marker([lat,lng], {icon:pinIcon(), draggable:true}).addTo(zgMap);
      zgMarker.on('dragend', function(){
        const p=zgMarker.getLatLng();
        setSelected(p.lat, p.lng, true);
      });
    }
    const addr=el('pickAddr');
    if(addr) addr.innerHTML='<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'<br><small>Buscando dirección exacta…</small>';
    updateRoute();
    if(doReverse) reverseAddress(lat,lng);
  }

  async function reverseAddress(lat,lng){
    const addr=el('pickAddr');
    try{
      const r=await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+encodeURIComponent(lat)+'&lon='+encodeURIComponent(lng)+'&zoom=18&addressdetails=1&accept-language=es');
      const d=await r.json();
      const text=d.display_name || '';
      if(zgSelected) zgSelected.addr=text;
      if(addr){
        addr.innerHTML = text
          ? '<b>Dirección:</b> '+esc(text)+'<br><small>Coordenadas: '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'</small>'
          : '<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6);
      }
    }catch(e){
      if(addr) addr.innerHTML='<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'<br><small>No se pudo obtener la calle; se guardarán las coordenadas.</small>';
    }
    updateRoute();
  }

  async function searchAddress(){
    const q=(el('mapSearch') && el('mapSearch').value || '').trim();
    const addr=el('pickAddr');
    const sug=el('mapSug');
    if(!q) return;
    if(addr) addr.textContent='Buscando…';
    try{
      const url='https://nominatim.openstreetmap.org/search?format=jsonv2&q='+encodeURIComponent(q)+'&limit=8&addressdetails=1&countrycodes=pe&accept-language=es';
      const r=await fetch(url);
      const arr=await r.json();
      if(sug){ sug.innerHTML=''; sug.classList.remove('show'); }
      if(!arr || !arr.length){
        if(addr) addr.innerHTML='No se encontró ese lugar. Escribe calle, distrito y ciudad, o toca el mapa para marcar el punto.';
        return;
      }
      if(sug){
        arr.forEach(function(it){
          const div=document.createElement('div');
          div.className='sug-item';
          div.innerHTML='<div class="sug-main">'+esc((it.display_name||'').split(',')[0])+'</div><div class="sug-sub">'+esc(it.display_name||'')+'</div>';
          div.addEventListener('mousedown', function(ev){
            ev.preventDefault();
            pickSearchItem(it);
            sug.classList.remove('show');
          });
          sug.appendChild(div);
        });
        sug.classList.add('show');
      }
      pickSearchItem(arr[0]);
    }catch(e){
      if(addr) addr.innerHTML='No se pudo buscar. Revisa internet o marca el punto en el mapa.';
    }
  }

  function pickSearchItem(it){
    const lat=parseFloat(it.lat), lng=parseFloat(it.lon);
    if(!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    const text=it.display_name || '';
    if(el('mapSearch')) el('mapSearch').value=text;
    if(zgMap) zgMap.setView([lat,lng],18);
    setSelected(lat,lng,false);
    if(zgSelected) zgSelected.addr=text;
    if(el('pickAddr')) el('pickAddr').innerHTML='<b>Dirección:</b> '+esc(text)+'<br><small>Coordenadas: '+lat.toFixed(6)+', '+lng.toFixed(6)+'</small>';
    updateRoute();
  }

  function updateRoute(){
    const route=el('mapRoute');
    if(!route || !zgSelected){ return; }
    route.href='https://www.google.com/maps/dir/?api=1&destination='+zgSelected.lat+','+zgSelected.lng;
    route.style.display='inline-flex';
  }

  function confirmFixedMap(){
    if(!zgSelected){
      if(typeof toast === 'function') toast('Elige un punto en el mapa primero');
      return;
    }
    const lat=Number(zgSelected.lat), lng=Number(zgSelected.lng);
    const direccion=el('direccion');
    const coords=el('direccionCoords');
    if(direccion) direccion.value = zgSelected.addr || (lat.toFixed(6)+', '+lng.toFixed(6));
    if(coords) coords.value = lat.toFixed(6)+', '+lng.toFixed(6);
    const origen=el('direccionOrigenOdoo'); if(origen) origen.value='';
    const modal=el('mapModal');
    if(modal) modal.classList.remove('show');
    if(typeof toast === 'function') toast('Ubicación seleccionada');
    if(typeof validateField === 'function') validateField('direccion');
  }

  // Captura antes de cualquier manejador anterior para evitar que se quede el mapa en blanco.
  document.addEventListener('click', function(e){
    if(e.target.closest('#dirPick') || e.target.closest('#direccion')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      openFixedMap();
      return false;
    }
    if(e.target.closest('#mapSearchBtn')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      searchAddress();
      return false;
    }
    if(e.target.closest('#mapConfirm')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      confirmFixedMap();
      return false;
    }
  }, true);

  document.addEventListener('keydown', function(e){
    if(e.target && e.target.id === 'mapSearch' && e.key === 'Enter'){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      searchAddress();
      return false;
    }
  }, true);

  window.openMap = openFixedMap;
})();
</script>



<style>
/* ============================================================
   ZGROUP - tabla real de repuestos seleccionados
   ============================================================ */
#repuestosSelectedList.zg-table-ready{display:block!important;margin-top:12px!important}
.zg-repuestos-table-wrap{border:1.5px solid #cfe0f4;border-radius:18px;overflow:hidden;background:#fff;box-shadow:0 14px 30px rgba(16,33,58,.10);margin-top:10px}
.zg-repuestos-table{width:100%;border-collapse:collapse;font-size:13px;background:#fff}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#eef6ff,#e7f0fb);color:#155293;font-family:'Archivo',system-ui,sans-serif;font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;text-align:left;padding:11px 12px;border-bottom:1px solid #d7e5f5;border-right:1px solid #d7e5f5}
.zg-repuestos-table thead th:last-child{border-right:none;text-align:center}
.zg-repuestos-table td{padding:10px 12px;border-bottom:1px solid #edf3fa;border-right:1px solid #edf3fa;vertical-align:middle}
.zg-repuestos-table td:last-child{border-right:none;text-align:center}.zg-repuestos-table tr:last-child td{border-bottom:none}
.zg-rep-code2{font-family:'Archivo',system-ui,sans-serif;font-weight:900;color:#1f6fc4;word-break:break-word}.zg-rep-code2.empty{color:#8a5a00}
.zg-rep-detail2{width:100%;border:1.5px solid #d7e5f5;background:#f8fbff;border-radius:12px;padding:10px 11px;font:inherit;font-weight:850;color:#10213a;outline:none}.zg-rep-detail2:focus{border-color:#1f6fc4;background:#fff;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-qty2{width:96px;border:1.5px solid #d7e5f5;background:#fff;border-radius:12px;padding:10px 8px;font:inherit;font-family:'Archivo',system-ui,sans-serif;font-weight:900;text-align:center;color:#10213a;outline:none}.zg-rep-qty2:focus{border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-del2{width:36px;height:36px;border:none;border-radius:12px;background:#fff1f1;color:#b91c1c;font-size:18px;font-weight:900;cursor:pointer}.zg-rep-del2:hover{filter:brightness(.96);transform:translateY(-1px)}
.zg-rep-count2{display:inline-flex!important;align-items:center;gap:8px;background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:900;margin-top:10px}.zg-rep-count2:before{content:'✓'}
.zg-rep-tip2{font-size:11.5px;color:#66758a;font-weight:800;margin-top:4px}.zg-rep-tip2 b{color:#10213a}
#repuestosEmpty{margin-top:12px!important}
.repuesto-smart .smart-menu{z-index:99999!important;pointer-events:auto!important}.repuesto-smart .smart-option{pointer-events:auto!important;cursor:pointer!important}.repuesto-smart .smart-option *{pointer-events:none!important}
@media(max-width:720px){.zg-repuestos-table thead{display:none}.zg-repuestos-table,.zg-repuestos-table tbody,.zg-repuestos-table tr,.zg-repuestos-table td{display:block;width:100%}.zg-repuestos-table tr{border-bottom:1px solid #edf3fa}.zg-repuestos-table td{border-right:none}.zg-repuestos-table td::before{display:block;margin-bottom:5px;font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;color:#66758a;font-family:'Archivo';font-weight:900}.zg-repuestos-table td[data-label="Código"]::before{content:'Código'}.zg-repuestos-table td[data-label="Material"]::before{content:'Material / repuesto'}.zg-repuestos-table td[data-label="Cantidad"]::before{content:'Cantidad'}.zg-rep-qty2{width:130px}.zg-rep-del2{width:100%}}
</style>
<script>
/* ============================================================
   ZGROUP - selección real de repuestos + tabla + PDF
   - Reemplaza la lista anterior sin depender de los handlers viejos.
   ============================================================ */
(function(){
  function el(id){ return document.getElementById(id); }
  function clean(s){ return String(s || '').replace(/\s+/g,' ').trim(); }
  function norm(s){ return clean(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  function toast2(t){ try{ if(typeof toast === 'function') toast(t); else console.log(t); }catch(e){ console.log(t); } }
  function catalogo(){ try{ return Array.isArray(REPUESTOS_CATALOGO) ? REPUESTOS_CATALOGO : []; }catch(e){ return []; } }

  let materiales = [];

  function parseLinea(linea){
    linea = clean(linea); if(!linea) return null;
    const p = linea.split('|').map(clean);
    let codigo='', detalle='', cantidad='1', unidad='';

    /*
       Formato actual: CODIGO | MATERIAL | CANTIDAD | UNIDAD
       Formato anterior: CODIGO | MATERIAL | CANTIDAD
       Se reconocen ambos para no perder cantidades al agregar otro material.
    */
    if(p.length >= 4){
      codigo = p[0] === '-' ? '' : p[0];
      cantidad = p[p.length - 2] || '1';
      unidad = p[p.length - 1] || '';
      detalle = p.slice(1, -2).join(' | ') || p[1] || '';
    } else if(p.length === 3){
      codigo = p[0] === '-' ? '' : p[0];
      detalle = p[1] || '';
      cantidad = p[2] || '1';
    } else if(p.length === 2){
      codigo = p[0] === '-' ? '' : p[0];
      detalle = p[1] || '';
    } else {
      detalle = linea;
    }

    detalle = clean(detalle.replace(/\s*\|\s*/g,' '));
    cantidad = String(cantidad || '1').replace(/[^0-9]/g,'').slice(0,4) || '1';
    if(!detalle) return null;
    return {codigo, detalle, cantidad, unidad};
  }
  function cargarDesdeTextarea(){
    const ta = el('repuestosManual');
    const txt = ta ? ta.value : '';
    const arr = txt.split(/\r?\n/).map(parseLinea).filter(Boolean);
    if(arr.length) materiales = arr;
    return materiales;
  }
  function linea(x){ return (clean(x.codigo)||'-') + ' | ' + clean(x.detalle) + ' | ' + (String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1'); }
  function guardar(){
    const ta = el('repuestosManual');
    if(ta){
      ta.value = materiales.filter(x=>clean(x.detalle)).map(linea).join('\n');
      ta.classList.remove('input-error');
    }
    try{ repuestosSeleccionados = materiales.map(x => ({codigo:clean(x.codigo), detalle:clean(x.detalle), cantidad:String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1', nuevo:!clean(x.codigo)})); }catch(e){}
    const err = el('repuestosManualError');
    if(err){ err.textContent=''; err.classList.remove('show'); }
    try{ if(window.zgroupMarcarCambio) window.zgroupMarcarCambio(); }catch(e){}
  }
  function pintar(){
    const box = el('repuestosSelectedList');
    const empty = el('repuestosEmpty');
    if(!box) return;
    box.classList.add('zg-table-ready');
    box.innerHTML = '';
    if(empty) empty.classList.toggle('show', materiales.length === 0);
    if(!materiales.length){ guardar(); return; }

    const count = document.createElement('div');
    count.className = 'zg-rep-count2';
    count.textContent = materiales.length + ' material(es) seleccionado(s)';
    box.appendChild(count);

    const wrap = document.createElement('div');
    wrap.className = 'zg-repuestos-table-wrap';
    const table = document.createElement('table');
    table.className = 'zg-repuestos-table';
    table.innerHTML = '<thead><tr><th style="width:145px">Código</th><th>Material / repuesto</th><th style="width:120px">Cantidad</th><th style="width:56px"></th></tr></thead><tbody></tbody>';
    const tbody = table.querySelector('tbody');

    materiales.forEach(function(it, idx){
      const tr = document.createElement('tr');
      const cod = clean(it.codigo) || 'Sin código';
      tr.innerHTML =
        '<td data-label="Código"><div class="zg-rep-code2 '+(!clean(it.codigo)?'empty':'')+'">'+esc(cod)+'</div></td>'+ 
        '<td data-label="Material"><input class="zg-rep-detail2" type="text" value="'+esc(it.detalle)+'" placeholder="Nombre del material"><div class="zg-rep-tip2">Puedes corregir el nombre antes de generar el PDF.</div></td>'+ 
        '<td data-label="Cantidad"><input class="zg-rep-qty2" type="text" inputmode="numeric" value="'+esc(it.cantidad||'1')+'"></td>'+ 
        '<td><button type="button" class="zg-rep-del2" title="Quitar">×</button></td>';
      const det = tr.querySelector('.zg-rep-detail2');
      const qty = tr.querySelector('.zg-rep-qty2');
      const del = tr.querySelector('.zg-rep-del2');
      det.addEventListener('input', function(){ materiales[idx].detalle = clean(det.value); guardar(); });
      qty.addEventListener('input', function(){ let v=String(qty.value||'').replace(/[^0-9]/g,'').slice(0,4); qty.value=v; materiales[idx].cantidad=v; guardar(); });
      qty.addEventListener('focus', function(){ setTimeout(function(){ try{ qty.select(); }catch(e){} }, 30); });
      qty.addEventListener('blur', function(){ if(!String(qty.value||'').trim()){ qty.value='1'; materiales[idx].cantidad='1'; guardar(); } });
      del.addEventListener('click', function(){ materiales.splice(idx,1); guardar(); pintar(); });
      tbody.appendChild(tr);
    });
    wrap.appendChild(table);
    box.appendChild(wrap);
    guardar();
  }
  function activarSi(){
    const h=el('requiereRepuesto'); if(h) h.value='si';
    const card=el('repuestosCard'); if(card) card.classList.remove('is-hidden');
    const si=el('repuestoSiBtn'), no=el('repuestoNoBtn'); if(si) si.classList.add('on'); if(no) no.classList.remove('on');
  }
  function existe(item){
    const d=norm(item.detalle), c=norm(item.codigo);
    return materiales.some(x => (c && norm(x.codigo)===c) || (d && norm(x.detalle)===d));
  }
  function agregar(item, aviso){
    item = item || {};
    const obj = {codigo:clean(item.codigo||''), detalle:clean(item.detalle||''), cantidad:String(item.cantidad||'1').replace(/[^0-9]/g,'').slice(0,4)||'1'};
    if(!obj.detalle) return false;
    activarSi();
    cargarDesdeTextarea();
    if(existe(obj)){ if(aviso!==false) toast2('Ya está seleccionado: '+obj.detalle); pintar(); return false; }
    materiales.push(obj);
    const input=el('repuestoSearch'); if(input) input.value='';
    const menu=el('repuestoSuggest'); if(menu) menu.classList.remove('show');
    pintar();
    if(aviso!==false) toast2('Agregado a la tabla: '+obj.detalle+' · Cantidad '+obj.cantidad);
    return true;
  }
  function buscar(det){
    const d=norm(det); if(!d) return null;
    return catalogo().find(r=>norm(r.detalle||'')===d) || catalogo().find(r=>norm(r.detalle||'').includes(d)) || null;
  }
  function agregarManual(){
    const input=el('repuestoSearch');
    const det=clean(input?input.value:'');
    if(!det){ if(input) input.focus(); toast2('Escribe o selecciona un material'); return false; }
    const cat=buscar(det);
    return agregar(cat ? {codigo:cat.codigo||'', detalle:cat.detalle||det, cantidad:'1'} : {codigo:'', detalle:det, cantidad:'1'}, true);
  }
  function mostrarMenuPropio(menuId, items){
    const menu = el(menuId); if(!menu) return;
    menu.innerHTML='';
    if(!items || !items.length){ menu.classList.remove('show'); return; }
    items.forEach(function(item){
      const raw = item.raw || item;
      const b = document.createElement('button');
      b.type='button'; b.className='smart-option';
      b.innerHTML='<div><span class="smart-main">'+esc(item.main||raw.detalle||'')+'</span><span class="smart-sub">'+esc(item.sub || (raw.codigo ? 'Código: '+raw.codigo : 'Pendiente de revisión'))+'</span></div><span class="smart-badge">usar</span>';
      b.addEventListener('click', function(ev){ ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); agregar({codigo:raw.codigo||'', detalle:raw.detalle||item.main||'', cantidad:'1'}, true); return false; }, true);
      menu.appendChild(b);
    });
    menu.classList.add('show');
  }

  // Reemplaza el render del menú solo para repuestos. Los otros catálogos siguen igual.
  try{
    const oldRender = renderSmartMenu;
    renderSmartMenu = function(menuId, items, onPick){
      if(menuId === 'repuestoSuggest') return mostrarMenuPropio(menuId, items || []);
      return oldRender(menuId, items, onPick);
    };
  }catch(e){}

  // Captura adicional por si el menú viejo ya quedó pintado.
  document.addEventListener('click', function(ev){
    const btn = ev.target && ev.target.closest ? ev.target.closest('#repuestoSuggest .smart-option') : null;
    if(btn){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
      const main = clean(btn.querySelector('.smart-main') ? btn.querySelector('.smart-main').textContent : btn.textContent.replace(/usar\s*$/i,''));
      const cat = buscar(main);
      agregar(cat ? {codigo:cat.codigo||'', detalle:cat.detalle||main, cantidad:'1'} : {codigo:'', detalle:main, cantidad:'1'}, true);
      return false;
    }
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoAddManual')){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); agregarManual(); return false;
    }
    if(ev.target && ev.target.closest && (ev.target.closest('#pdfBtn') || ev.target.closest('#preBtn'))){ guardar(); }
  }, true);

  document.addEventListener('keydown', function(ev){
    if(ev.target && ev.target.id === 'repuestoSearch' && ev.key === 'Enter'){
      ev.preventDefault(); ev.stopPropagation(); agregarManual(); return false;
    }
  }, true);

  try{ insertarRepuestoLinea = function(r){ return agregar({codigo:r&&r.codigo||'', detalle:r&&r.detalle||'', cantidad:'1'}, true); }; }catch(e){}
  try{ agregarRepuestoObjeto = function(r,cantidad,aviso){ return agregar({codigo:r&&r.codigo||'', detalle:r&&r.detalle||'', cantidad:cantidad||'1'}, aviso!==false); }; }catch(e){}
  try{ agregarRepuestoManual = agregarManual; }catch(e){}
  try{ renderRepuestosSeleccionados = pintar; }catch(e){}
  try{ syncRepuestosManual = guardar; }catch(e){}
  try{ validarRepuestos = function(){ if(clean(el('repuestoSearch')?el('repuestoSearch').value:'')) agregarManual(); guardar(); if((el('requiereRepuesto')&&el('requiereRepuesto').value==='si') && !clean(el('repuestosManual')?el('repuestosManual').value:'')){ toast2('Agrega al menos un material en la tabla'); return false; } return true; }; }catch(e){}
  try{ registrarRepuestosTecnico = async function(){ guardar(); const txt=clean(el('repuestosManual')?el('repuestosManual').value:''); if(!txt) return true; try{ const fd=new FormData(); fd.append('repuestos', txt); await fetch('registrar_repuestos_tecnico.php',{method:'POST', body:fd}); }catch(e){} return true; }; }catch(e){}

  window.zgRepuestosTablaFinal = {agregar, pintar, guardar, materiales:function(){return materiales;}};
  window.addEventListener('load', function(){ cargarDesdeTextarea(); pintar(); const hint=el('repuestoHint'); if(hint) hint.textContent='Selecciona “usar” y aparecerá abajo en una tabla con cantidad.'; });
  document.addEventListener('DOMContentLoaded', function(){ cargarDesdeTextarea(); pintar(); });
})();
</script>


<style>
/* Ajuste final: tabla de repuestos más clara y cantidad editable sin quedarse pegada al 1 */
.zg-repuestos-table-wrap{border:1.5px solid #c6dcf5!important;border-radius:18px!important;overflow:hidden!important;background:#fff!important;box-shadow:0 14px 34px rgba(16,33,58,.12)!important}
.zg-repuestos-table{font-size:13.2px!important;background:#fff!important}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#18345c,#10213a)!important;color:#fff!important;padding:12px 12px!important;border-right:1px solid rgba(255,255,255,.18)!important;text-align:left!important}
.zg-repuestos-table thead th:nth-child(3),.zg-repuestos-table thead th:nth-child(4){text-align:center!important}
.zg-repuestos-table td{background:#fff!important;padding:11px 12px!important;border-bottom:1px solid #edf3fa!important;border-right:1px solid #edf3fa!important}
.zg-repuestos-table tbody tr:nth-child(even) td{background:#f8fbff!important}
.zg-rep-code2{font-size:12.8px!important;color:#1f6fc4!important}.zg-rep-code2.empty{color:#9b6a00!important}
.zg-rep-detail2{background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;min-height:42px!important}
.zg-rep-detail2:focus,.zg-rep-qty2:focus{border-color:#1f6fc4!important;box-shadow:0 0 0 3px #e7f0fb!important}
.zg-rep-qty2{width:92px!important;background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;font-size:15px!important;color:#10213a!important;text-align:center!important;caret-color:#1f6fc4!important}
.zg-rep-del2{background:#fff1f1!important;color:#c92a2a!important;border:1px solid #ffd6d6!important}
.zg-rep-count2{margin-bottom:9px!important;background:#eaf7ef!important;color:#176b34!important;border:1px solid #bfe8cc!important}
</style>
</body>
</html><?php
}

if (!$tecnico_logueado) {
    zgroup_render_login($login_error);
    exit;
}

if ($token_general === '' && $modo_general === '') {
    zgroup_render_menu();
    exit;
}

if ($token_general === '' && $modo_general === 'base') {
    zgroup_render_base_en_proceso();
    exit;
}

?>
<?php
require __DIR__ . '/db.php';

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/* ============================================================
   Modo continuación de servicio:
   Si el técnico entra con ?token=..., se carga la preinspección
   guardada previamente y se bloquean sus datos iniciales.
   ============================================================ */
$preinspeccion = null;
$preinspeccionError = '';
$token_continuacion = trim((string)($_GET['token'] ?? ''));

if ($token_continuacion !== '') {
    try {
        $stmtPre = $pdo->prepare("
            SELECT ip.*, t.nombre AS tecnico_nombre
            FROM inspecciones_preliminares ip
            LEFT JOIN tecnicos t ON t.id = ip.tecnico_id
            WHERE ip.token_continuacion = ?
            LIMIT 1
        ");
        $stmtPre->execute([$token_continuacion]);
        $preinspeccion = $stmtPre->fetch(PDO::FETCH_ASSOC);

        if (!$preinspeccion) {
            $preinspeccionError = 'No se encontró una inspección preliminar con este enlace.';
        }
    } catch (Throwable $e) {
        $preinspeccionError = 'No se pudo cargar la inspección preliminar: ' . $e->getMessage();
    }
}


/* ============================================================
   Edición administrativa de una inspección preliminar.
   Se carga por ID desde el panel y se mantiene editable.
   ============================================================ */
$preliminarEdicionId = 0;
if ($modo_editar_preliminar) {
    $preliminarEdicionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($preliminarEdicionId <= 0) {
        $preinspeccionError = 'No se recibió una inspección preliminar válida para editar.';
    } else {
        try {
            $stmtPreEdit = $pdo->prepare("
                SELECT ip.*, t.nombre AS tecnico_nombre
                FROM inspecciones_preliminares ip
                LEFT JOIN tecnicos t ON t.id = ip.tecnico_id
                WHERE ip.id = ?
                LIMIT 1
            ");
            $stmtPreEdit->execute([$preliminarEdicionId]);
            $preinspeccion = $stmtPreEdit->fetch(PDO::FETCH_ASSOC) ?: null;
            if (!$preinspeccion) {
                $preinspeccionError = 'La inspección preliminar solicitada no existe o fue eliminada.';
            } else {
                $token_continuacion = trim((string)($preinspeccion['token_continuacion'] ?? ''));
            }
        } catch (Throwable $e) {
            $preinspeccionError = 'No se pudo abrir la inspección preliminar para edición: ' . $e->getMessage();
        }
    }
}

/* ============================================================
   Borrador de la segunda etapa del servicio.
   Guarda trabajos, materiales, control final, evidencias y firmas para
   que el técnico pueda continuar aunque cierre la página sin generar PDF.
   ============================================================ */
$borradorServicio = null;
$borradorServicioError = '';
if (is_array($preinspeccion) && (int)($preinspeccion['id'] ?? 0) > 0) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS borradores_servicio (
            preinspeccion_id INT NOT NULL PRIMARY KEY,
            token_continuacion VARCHAR(120) DEFAULT NULL,
            datos_json LONGTEXT NOT NULL,
            actualizado_en DATETIME NOT NULL,
            INDEX idx_borrador_token (token_continuacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $stmtDraft = $pdo->prepare('SELECT datos_json, actualizado_en FROM borradores_servicio WHERE preinspeccion_id = ? LIMIT 1');
        $stmtDraft->execute([(int)$preinspeccion['id']]);
        $draftRow = $stmtDraft->fetch(PDO::FETCH_ASSOC);
        if ($draftRow) {
            $draftDecoded = json_decode((string)($draftRow['datos_json'] ?? ''), true);
            if (is_array($draftDecoded)) {
                $borradorServicio = [
                    'snapshot' => $draftDecoded,
                    'actualizado_en' => (string)($draftRow['actualizado_en'] ?? '')
                ];
            }
        }
    } catch (Throwable $e) {
        $borradorServicioError = $e->getMessage();
    }
}

/* Determinar el tipo real de la preliminar para mostrar la plantilla correcta. */
$preTipoEquipo = '';
$preEsGenset = false;
if (is_array($preinspeccion)) {
    $preTipoEquipo = trim((string)($preinspeccion['tipo_equipo'] ?? ''));
    $ctrlPre = strtoupper(trim((string)($preinspeccion['controlador'] ?? '')));
    $tieneDatoGenset = trim((string)($preinspeccion['genset_horometro_inicial'] ?? '')) !== ''
        || trim((string)($preinspeccion['genset_voltaje_bateria_inicial'] ?? '')) !== ''
        || trim((string)($preinspeccion['genset_frecuencia_inicial'] ?? '')) !== '';
    $preEsGenset = strcasecmp($preTipoEquipo, 'Genset') === 0
        || preg_match('/^SG[- ]?(3000|5000)$/i', $ctrlPre)
        || $tieneDatoGenset;
    if ($preTipoEquipo === '') $preTipoEquipo = $preEsGenset ? 'Genset' : 'Reefer';
}


/* ============================================================
   Edición de informe desde el panel
   - Reutiliza el mismo formulario y el mismo generador de PDF.
   - Los informes nuevos guardan una instantánea completa en datos_json.
   - Para informes anteriores se cargan los datos disponibles en MySQL.
   ============================================================ */
$informeEdicion = null;
$informeEdicionSnapshot = null;
$informeEdicionError = '';

function zgColumnaExisteIndex(PDO $pdo, string $tabla, string $columna): bool {
    $st = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $st->execute([$tabla, $columna]);
    return (int)$st->fetchColumn() > 0;
}
function zgAgregarColumnaIndex(PDO $pdo, string $tabla, string $columna, string $definicion): void {
    if (!zgColumnaExisteIndex($pdo, $tabla, $columna)) {
        $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$columna` $definicion");
    }
}

if ($modo_editar_informe) {
    $informeIdEdicion = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($informeIdEdicion <= 0) {
        $informeEdicionError = 'No se recibió un informe válido para editar.';
    } else {
        try {
            zgAgregarColumnaIndex($pdo, 'informes', 'datos_json', 'LONGTEXT DEFAULT NULL');
            zgAgregarColumnaIndex($pdo, 'informes', 'repuestos_manual', 'LONGTEXT DEFAULT NULL');
            zgAgregarColumnaIndex($pdo, 'informes', 'actualizado_en', 'DATETIME DEFAULT NULL');
            zgAgregarColumnaIndex($pdo, 'informes', 'hora_inicio_servicio', 'DATETIME DEFAULT NULL');
            zgAgregarColumnaIndex($pdo, 'informes', 'hora_fin_servicio', 'DATETIME DEFAULT NULL');
            zgAgregarColumnaIndex($pdo, 'inspecciones_preliminares', 'hora_inicio_servicio', 'DATETIME DEFAULT NULL');
            zgAgregarColumnaIndex($pdo, 'inspecciones_preliminares', 'hora_fin_servicio', 'DATETIME DEFAULT NULL');
            $stInf = $pdo->prepare("SELECT i.*, t.nombre AS tecnico_nombre FROM informes i LEFT JOIN tecnicos t ON t.id = i.tecnico_id WHERE i.id = ? LIMIT 1");
            $stInf->execute([$informeIdEdicion]);
            $informeEdicion = $stInf->fetch(PDO::FETCH_ASSOC) ?: null;
            if (!$informeEdicion) {
                $informeEdicionError = 'El informe solicitado no existe o fue eliminado.';
            } else {
                $rawSnapshot = trim((string)($informeEdicion['datos_json'] ?? ''));
                if ($rawSnapshot !== '') {
                    $tmpSnapshot = json_decode($rawSnapshot, true);
                    if (is_array($tmpSnapshot)) $informeEdicionSnapshot = $tmpSnapshot;
                }

                // Respaldo independiente de materiales para que una edición nunca los pierda.
                $repuestosGuardados = trim((string)($informeEdicion['repuestos_manual'] ?? ''));
                if ($repuestosGuardados !== '') {
                    if (!is_array($informeEdicionSnapshot)) {
                        $informeEdicionSnapshot = ['version' => 4, 'fields' => [], 'state' => []];
                    }
                    if (!isset($informeEdicionSnapshot['fields']) || !is_array($informeEdicionSnapshot['fields'])) {
                        $informeEdicionSnapshot['fields'] = [];
                    }
                    $repuestosSnapshot = trim((string)($informeEdicionSnapshot['fields']['repuestosManual']['value'] ?? ''));
                    if ($repuestosSnapshot === '') {
                        $informeEdicionSnapshot['fields']['repuestosManual'] = [
                            'type' => 'textarea',
                            'value' => $repuestosGuardados,
                            'checked' => false
                        ];
                    }
                    $informeEdicionSnapshot['fields']['requiereRepuesto'] = [
                        'type' => 'hidden',
                        'value' => 'si',
                        'checked' => false
                    ];
                } elseif (is_array($informeEdicionSnapshot)) {
                    $repuestosSnapshot = trim((string)($informeEdicionSnapshot['fields']['repuestosManual']['value'] ?? ''));
                    if ($repuestosSnapshot !== '') {
                        $informeEdicionSnapshot['fields']['requiereRepuesto'] = [
                            'type' => 'hidden',
                            'value' => 'si',
                            'checked' => false
                        ];
                    }
                }
                $preId = (int)($informeEdicion['preinspeccion_id'] ?? 0);
                if ($preId > 0) {
                    $stPreEdit = $pdo->prepare("SELECT ip.*, t.nombre AS tecnico_nombre FROM inspecciones_preliminares ip LEFT JOIN tecnicos t ON t.id = ip.tecnico_id WHERE ip.id = ? LIMIT 1");
                    $stPreEdit->execute([$preId]);
                    $preEdit = $stPreEdit->fetch(PDO::FETCH_ASSOC);
                    if ($preEdit) $preinspeccion = $preEdit;
                }
            }
        } catch (Throwable $e) {
            $informeEdicionError = 'No se pudo abrir el informe para edición: ' . $e->getMessage();
        }
    }
}

function defaultTrabajosRealizados() {
    return [
        // Trabajos para contenedor / máquina reefer
        ['slug' => 'asistencia_tecnica', 'nombre' => 'ASISTENCIA TÉCNICA'],
        ['slug' => 'mantenimiento_correctivo', 'nombre' => 'MANTENIMIENTO CORRECTIVO'],
        ['slug' => 'mantenimiento_productivo', 'nombre' => 'MANTENIMIENTO PREVENTIVO'],
        ['slug' => 'instalacion_luminarias', 'nombre' => 'INSTALACIÓN DE LUMINARIAS'],
        ['slug' => 'deshielo_contenedor', 'nombre' => 'DESHIELO DE CONTENEDOR'],
        ['slug' => 'instalacion_reefer', 'nombre' => 'INSTALACIÓN DE REEFER'],

        // Trabajos exclusivos para generadores GENSET
        ['slug' => 'genset_mantenimiento_preventivo', 'nombre' => 'MANTENIMIENTO PREVENTIVO DE GENSET'],
        ['slug' => 'genset_mantenimiento_correctivo', 'nombre' => 'MANTENIMIENTO CORRECTIVO DE GENSET']];
}

function asegurarTrabajosV9(PDO $pdo) {
    // Agrega/actualiza los trabajos nuevos, pero NO desactiva los trabajos existentes del panel.
    $permitidos = defaultTrabajosRealizados();
    $stmtExiste = $pdo->prepare('SELECT id FROM trabajos_realizados WHERE slug = ? LIMIT 1');
    $stmtUpdate = $pdo->prepare('UPDATE trabajos_realizados SET nombre = ?, activo = 1 WHERE slug = ?');
    $stmtInsert = $pdo->prepare('INSERT INTO trabajos_realizados (slug, nombre, activo) VALUES (?, ?, 1)');
    foreach ($permitidos as $w) {
        $stmtExiste->execute([$w['slug']]);
        if ($stmtExiste->fetchColumn()) $stmtUpdate->execute([$w['nombre'], $w['slug']]);
        else $stmtInsert->execute([$w['slug'], $w['nombre']]);
    }
    $pdo->exec("UPDATE trabajos_realizados SET activo = 0 WHERE slug IN ('genset_inspeccion_diagnostico','genset_cambio_aceite_filtros','genset_sistema_electrico','genset_prueba_carga','ingreso_new_genset','reparacion_genset')");
}

function slugTrabajoRealizado($s) {
    $s = trim((string)$s);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($ascii !== false) $s = $ascii;
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '_', $s);
    $s = trim($s, '_');
    return $s !== '' ? $s : 'trabajo';
}

function asegurarTablaTrabajos($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS trabajos_realizados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(90) NOT NULL UNIQUE,
        nombre VARCHAR(180) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $count = (int)$pdo->query('SELECT COUNT(*) FROM trabajos_realizados')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO trabajos_realizados (slug, nombre, activo) VALUES (?, ?, 1)');
        foreach (defaultTrabajosRealizados() as $w) {
            $stmt->execute([$w['slug'], $w['nombre']]);
        }
    }
}

try {
    asegurarTablaTrabajos($pdo);
    asegurarTrabajosV9($pdo);
    // Conserva todos los trabajos activos creados desde el panel.
    // En el navegador se separan los trabajos de reefer y los trabajos exclusivos de genset.
    $stmtTrabajos = $pdo->query("SELECT slug AS id, nombre FROM trabajos_realizados WHERE activo = 1 ORDER BY nombre");
    $workTypes = $stmtTrabajos->fetchAll(PDO::FETCH_ASSOC);
    if (!$workTypes) {
        $workTypes = array_map(function($w) { return ['id' => $w['slug'], 'nombre' => $w['nombre']]; }, defaultTrabajosRealizados());
    }
} catch (Throwable $e) {
    $workTypes = array_map(function($w) { return ['id' => $w['slug'], 'nombre' => $w['nombre']]; }, defaultTrabajosRealizados());
}


function zgroupMaterialesSG3000(): array {
    return [
        ['119300','ELEMENT-AIR CLEANER 119300','und'],
        ['INDND0910','FILTER FUEL 119342','und'],
        ['119182','OIL FILTER 119182','und'],
        ['RNDND0700','ACEITE DE MOTOR 15W/40 (MOBIL / SHELL RIMULA R4X)','L'],
        ['RNDND0289','LÍQUIDO PARA RADIADOR VISTONY ROJO','L'],
        ['INDND0017','TRAPO INDUSTRIAL SUELTO','und'],
        ['INDND0078','AFLOJATODO','und'],
        ['INDND0079','LIMPIA CONTACTO','und'],
        ['INDND6960','ARANDELA DE COBRE #14 X 18MM X 1.3MM','und'],
        ['INDND0779','DETERGENTE INDUSTRIAL','und'],
        ['INDND4603','FILTRO DE COMBUSTIBLE','und'],
        ['INDND0334','SODA CÁUSTICA','kg'],
        ['INDND1735','FILTRO DE GASOLINA DG1074','und'],
        ['INDND1603','41-3404 RECEPTACLE 480 V, 32A PN 413404','und'],
        ['114607','TANQUE DE RESERVA DE AGUA','und'],
        ['449298','SENSOR RPM 449298','und'],
        ['416539','SENSOR ASSY ENGINE WATER 416539','und'],
        ['41-8283','SENSOR DE PRESIÓN DE ACEITE - 418283 SWITCH-PRESS OIL','L'],
        ['41-4470','SENSOR NIVEL DE ACEITE - 414470 SENSOR OIL LEVEL','L'],
        ['411818','SENSOR DE NIVEL DE AGUA - 41-1818 SENSOR WTR LEV','und'],
        ['401311','KIT MODULE ECOPOWER RETROFIT 401311','und'],
        ['401129','KIT MODULE ECOPOWER RETROFIT 401129','und'],
        ['333878','GASKET - FUEL SENSOR','und'],
        ['987219','DOOR - CURBSIDE','und'],
        ['987221','DOOR - ROADSIDE','und'],
        ['422342','MODULE - OPTO COUPLER','und'],
        ['401130','SENSOR DE COMBUSTIBLE','und'],
        ['8101381','MOTOR - ENGINE NEW TK486VG2 INTER 8101381','und'],
        ['401332','ALTERNADOR 401332','und'],
        ['452177','ARRANCADOR - STARTER TK486V 12V PN 45-2177','und'],
        ['132268','BOMBA DE AGUA - PUMP WATER','und'],
        ['INDND1605','420100 SOLENOID STOP FUEL 41-9100','und'],
        ['RNDND0616','41-7841 KEYPAD-GENSET SG 3000 PN 417841','und'],
        ['120810TKA','RADIADOR','und'],
        ['452830','45-2830 CONTROLLER SG + 1.5 PN 452830','und'],
        ['452554','CONTROLLER GENSET 45-2554 / 8452554','und'],
        ['130972','STRAINER FUEL 130972','und'],
        ['132929','PULLEY - WATER PUMP 132929','und'],
        ['771426','PULLEY WATER PUMP PN 771426','und'],
        ['INDND0829','BATERÍA BOSCH M27 MF 17 PLACAS','und'],
        ['559828','STUD 559828','und'],
        ['443345','44-3345 REGULATOR VOLTAGE 443345','und'],
        ['781968','BELT 781968','und'],
        ['421257','REGULATOR DSR 421257','und'],
        ['118718','GAUGE - FUEL','und'],
        ['120991','TANK FUEL 50 GAL ALUMINUM 120991','und'],
        ['421276','DIODOS - DIODE ASSY 421276','und'],
        ['412147','HEATER','und'],
        ['130669','GAUGE-FUEL 130669','und'],
        ['120790','TANQUE DE COMBUSTIBLE - 80 GALONES','und'],
        ['RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67','und'],
        ['931061','NAMEPLATE FOIL 931061','und'],
        ['INDND0443','FUSIBLE TIPO UÑA 30 AMP','und'],
        ['INDND2439','ELEMENTO FILTRO COMBUSTIBLE AB39/9176/AC','und'],
        ['INDND0820','ABRAZADERA 3/8','und'],
        ['INDND1008','CINTILLO DE AMARRE 350MM','und'],
        ['INDND0619','CEBADOR DE COMBUSTIBLE BOSCH','und'],
        ['130388','INYECTORES','und'],
        ['INDND3648','PROTECTOR BATIENTE PARA TAPA DE COMBUSTIBLE 3MM','und'],
        ['INDND1013','BORNERA DE BATERÍA NEGATIVO (-)','und'],
        ['INDND0874','BORNERA DE BATERÍA POSITIVO (+)','und'],
        ['TB-37-33-4088','RETÉN DE CIGÜEÑAL DELANTERO - SEAL OIL CRANK FRONT','und'],
        ['INDND0106','PINTURA EN SPRAY BLANCO BRILLANTE','und'],
        ['INDND0107','PINTURA EN SPRAY NEGRO BRILLANTE','und'],
        ['RNDND0408','PAÑO DE FRANELA AMARILLA','und'],
        ['41-7904','RELAY 1PDT','und'],
        ['42-1787','421787 RELAY QUAD','und'],
        ['417904','417904 RELAY 1PDT','und'],
        ['INDND2875','FILTRO DE GASOLINA LFG-120','und'],
        ['INDND0968','FILTRO DE ACEITE LF3349','L'],
        ['INDND1569','FILTRO DE ACEITE LF699','L'],
        ['RNDND0393','FILTRO DE PETRÓLEO F1110','und'],
        ['INDND1577','FAJA A28','und'],
        ['INDND1002','PERNO HEX INOX 1/4 X 1','und'],
        ['INDND1390','TUERCA HEX INOX 1/4','und'],
        ['INDND1391','ARANDELA PLANA INOX 1/4','und'],
        ['INDND5055','HARNESS ASSY MAIN SG 417837','und'],
        ['INDND5056','HARNESS - EXCITER CM SYSTEMS 423280','und'],
        ['935685','BRACKET - OPTO ISOLATOR MOUNT','und'],
        ['RNDND0501','CINTILLO DE AMARRE 300 MM','und'],
        ['INDND1004','ARANDELA DE PRESIÓN INOX 1/4','und'],
        ['INDND0907','TOMA EMPOTRABLE 32AMP 3P+T 440V ROJO 3H IP67','und'],
        ['987816','BOX CONTROL','und'],
        ['118048','CAP-FUEL TANK (STEEL)','und'],
        ['923341','BRACKET-AIR CLEANER','und'],
        ['414240','CABLE DE BATERÍA POSITIVO Y NEGATIVO','m'],
        ['INDND2087','ALICATE DE PRESIÓN 6 STANLEY','und'],
        ['INDND4993','CABLE FLEXIBLE AUTOMOTRIZ GPT 0.3KV 14 AWG ROJO','m'],
        ['INDND5052','TERMINAL TIPO UÑA AZUL 14-16AWG','und'],
        ['INDND0134','BROCA DE COBALTO HSS 3/8','und'],
        ['INDND2268','LIJA DE AGUA N.º 1000','und'],
        ['INDND4509','CABLE PASACORRIENTE CON TERMINALES COCODRILO INDUSTRIAL X 2 M','und'],
        ['INDND5591','MANGUERA NEOPRENE 3/8 X 0.60 CONO Y GUÍA','m'],
        ['INDND5533','MANGUERA SYNFLEX 3/8 X 61 CM CON CONECTORES','m'],
        ['GL-0001','COMBUSTIBLE DIÉSEL','L'],
        ['RNDND0313','MEGAGREY SILICONA PARA EMPAQUETADURAS','und'],
        ['INDND0890','ACEITE PARA MOTOR SHELL SAE 5W-30','L'],
        ['118887','TUBE - FUEL FEED','und'],
        ['INDND5747','TAPAS SUPERIORES PARA GENERADOR EN ACERO GALVANIZADO 1.5 MM','und'],
        ['INDND5551','RELAY AUTOMOTRIZ 5P 12V BOSCH 0332209150','und'],
        ['INDND6930','FUEL METERING VALVE','und'],
        ['INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG','und']];
}

function zgroupAsegurarCatalogoGeneradores(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS generadores_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero VARCHAR(60) NOT NULL UNIQUE,
        serial_unidad VARCHAR(100) DEFAULT NULL,
        marca_equipo VARCHAR(100) NOT NULL DEFAULT 'THERMO KING',
        controlador VARCHAR(40) NOT NULL,
        descripcion VARCHAR(220) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_generadores_serial (serial_unidad),
        INDEX idx_generadores_controlador (controlador)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_genset_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        controlador VARCHAR(40) NOT NULL,
        codigo VARCHAR(60) DEFAULT NULL,
        detalle VARCHAR(220) NOT NULL,
        unidad VARCHAR(40) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_genset_rep_controlador (controlador),
        INDEX idx_genset_rep_codigo (codigo),
        INDEX idx_genset_rep_detalle (detalle)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $st = $pdo->prepare("SELECT COUNT(*) FROM repuestos_genset_catalogo WHERE controlador = 'SG-3000'");
    $st->execute();
    if ((int)$st->fetchColumn() === 0) {
        $ins = $pdo->prepare('INSERT INTO repuestos_genset_catalogo (controlador, codigo, detalle, unidad, activo) VALUES (?, ?, ?, ?, 1)');
        foreach (zgroupMaterialesSG3000() as $r) $ins->execute(['SG-3000', $r[0] ?: null, $r[1], $r[2]]);
    }
}


function zgroupMaterialesReeferV12(): array {
    return [
        ['STAR COOL','CIM6','818770B','2 PIN CONNECTOR (3.81mm/90°) (5 pcs)',''],
        ['STAR COOL','CIM6','818270C','AIR EXCHANGE MODULE (75CMH)',''],
        ['STAR COOL','CIM6','818522F','AUXILIARY CONTACT (WHITE DOT 10PCS)',''],
        ['STAR COOL','CIM6','811537D','BRACKET, EVAPORATOR FAN MOTOR',''],
        ['STAR COOL','CIM6','818329A','BUTT SPLICE',''],
        ['STAR COOL','CIM6','818202B','CABLE ADAPTER KIT, FAN MOTOR (10 pcs)',''],
        ['STAR COOL','CIM6','815505D','CABLE ROOM COVER',''],
        ['STAR COOL','CIM6','818561B','CABLE SET (X1, X2, X3), CIM 5',''],
        ['STAR COOL','CIM6','814247C','CABLE, FC (1.0 AND 1.1) TO COMPRESSOR',''],
        ['STAR COOL','CIM6','819526B','COIL CONDENSER',''],
        ['STAR COOL','CIM6','818658B','COMPRESSOR',''],
        ['STAR COOL','CIM6','818760B','CONNECTOR PLUG, SOLENOID COIL (5PCS)',''],
        ['STAR COOL','CIM6','818521B','CONTACTOR',''],
        ['STAR COOL','CIM6','818310C','CONTROLLER DOOR, CIM 6',''],
        ['STAR COOL','CIM6','868510D','CONTROLLER MODULE, CIM 6.0',''],
        ['STAR COOL','CIM6','818925A','CONTROLLER MODULE, USB CIM 6.2',''],
        ['STAR COOL','CIM6','818510E','CONTROLLER MODULE, USB CIM 6.2 REMAN',''],
        ['STAR COOL','CIM6','815209B','COVER PLATE (1715MM), CONDENSER',''],
        ['STAR COOL','CIM6','818250E','DAMPER, AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM6','881523A','DEFROST HEATER, EVAPORATOR (25PCS)',''],
        ['STAR COOL','CIM6','814667F','ECONOMIZER',''],
        ['STAR COOL','CIM6','819737D','ECONOMIZER VALVE, R134A',''],
        ['STAR COOL','CIM6','881527A','EVAPORATOR COIL',''],
        ['STAR COOL','CIM6','819543B','FAN BLADE, CONDENSER',''],
        ['STAR COOL','CIM6','819542C','FAN BLADE, EVAPORATOR',''],
        ['STAR COOL','CIM6','818965B','FREQUENCY CONVERTER 2.1',''],
        ['STAR COOL','CIM6','818274C','FRONT PART, AIR EXCHANGE MODULE (75 CMH)',''],
        ['STAR COOL','CIM6','818530A','FUSE 10A',''],
        ['STAR COOL','CIM6','818534A','FUSE HOLDER 0.4A',''],
        ['STAR COOL','CIM6','818656B','GASKET, COMPRESSOR STOP VALVE',''],
        ['STAR COOL','CIM6','818661B','GASKET, SERVICE VALVE LP',''],
        ['STAR COOL','CIM6','819501A','HIGH PRESSURE SWITCH',''],
        ['STAR COOL','CIM6','814644C','HINGE PIN',''],
        ['STAR COOL','CIM6','889740C','HOT GAS VALVE',''],
        ['STAR COOL','CIM6','818537A','HUMIDITY SENSOR, CIM 6',''],
        ['STAR COOL','CIM6','818523C','INTERLOCK, CONTACTOR',''],
        ['STAR COOL','CIM6','818236B','MELT FUSE IT',''],
        ['STAR COOL','CIM6','818275A','MOTOR, AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM6','818792A','MOTOR, CONDENSER FAN',''],
        ['STAR COOL','CIM6','818783A','MOTOR, EVAPORATOR FAN',''],
        ['STAR COOL','CIM6','818525C','ON/OFF SWITCH CIM 6',''],
        ['STAR COOL','CIM6','881550A','PLUG, EVAPORATOR SERVICE HOLE',''],
        ['STAR COOL','CIM6','818905A','POWER MEASUREMENT MODULE, CIM 6.2',''],
        ['STAR COOL','CIM6','819504D','PRESSURE TRANSMITTER HP NSK',''],
        ['STAR COOL','CIM6','819503D','PRESSURE TRANSMITTER LP NSK',''],
        ['STAR COOL','CIM6','814540B','RECEIVER',''],
        ['STAR COOL','CIM6','818739A','RECEIVER, WATER COOLED CONDENSER',''],
        ['STAR COOL','CIM6','818276A','SENSOR AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM6','818623C','SERVICE VALVE, COMPRESSOR LP',''],
        ['STAR COOL','CIM6','818235B','SIGHT GLASS RECEIVER KIT',''],
        ['STAR COOL','CIM6','818553A','SOLENOID COIL 14W 24VDC CIM 5',''],
        ['STAR COOL','CIM6','818554A','SOLENOID COIL 18W 24VDC CIM 5',''],
        ['STAR COOL','CIM6','886554B','SOLENOID COIL 11W 24VAC',''],
        ['STAR COOL','CIM6','814541C','SQUARE FAN GRILLE, CONDENSER',''],
        ['STAR COOL','CIM6','819500C','STOP VALVE RECEIVER',''],
        ['STAR COOL','CIM6','818940A','TEMPERATURE SENSOR 0.35M',''],
        ['STAR COOL','CIM6','818943B','TEMPERATURE SENSOR INCL. CABLE GLAND (3M)',''],
        ['STAR COOL','CIM6','818639B','TERMINAL BLOCK, COMPRESSOR',''],
        ['STAR COOL','CIM6','818518C','TRANSFORMER 105 VA CIM 6',''],
        ['STAR COOL','CIM6','886513A','USER PANEL, CIM 6.1',''],
        ['STAR COOL','CIM6','INDND0078','ACEITE AFLOJATODO',''],
        ['STAR COOL','CIM6','INDND0411','ACEITE POLYOLESTER BVA',''],
        ['STAR COOL','CIM6','INDND3242','BROCA DE COBALTO HSS 3/16',''],
        ['STAR COOL','CIM6','INDND0259','CABLE VULCANIZADO 4 X10',''],
        ['STAR COOL','CIM6','RNDND0254','CINTA AISLANTE 3M',''],
        ['STAR COOL','CIM6','INDND0432','CINTA FOAM 1/8 X 2 X 9.14M',''],
        ['STAR COOL','CIM6','INDND0433','CINTILLO DE AMARRE 150MM',''],
        ['STAR COOL','CIM6','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['STAR COOL','CIM6','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['STAR COOL','CIM6','INDND1911','EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M',''],
        ['STAR COOL','CIM6','INDND0126','ESTAÑO 0.8',''],
        ['STAR COOL','CIM6','INDND2552','FILTRO SECADOR QDM-164 1/2 - QUALITY',''],
        ['STAR COOL','CIM6','INDND0024','FILTRO SEC. FIJO DE 1/2 FLARE - EK 164 STD',''],
        ['STAR COOL','CIM6','INDND2905','FORMADOR EMPAQUETADURA AVIACION 3H',''],
        ['STAR COOL','CIM6','INDND2237','FUNDENTE',''],
        ['STAR COOL','CIM6','INDND1545','MINI FUSIBLE DE VIDRIO 15 AMP',''],
        ['STAR COOL','CIM6','INDND1542','FUSIBLE DE VIDRIO 10 AMP',''],
        ['STAR COOL','CIM6','RNDND0318','FUSIBLE DE VIDRIO 20 AMP',''],
        ['STAR COOL','CIM6','INDND0120','GAS REFRIGERANTE R-134A X 13.60KG',''],
        ['STAR COOL','CIM6','RNDND0438','JABON LIQUIDO',''],
        ['STAR COOL','CIM6','INDND0016','LIJA FIERRO #40 ASA',''],
        ['STAR COOL','CIM6','INDND0079','LIMPIA CONTACTO',''],
        ['STAR COOL','CIM6','INDND0086','NITROGENO INDUSTRIAL 10 M3',''],
        ['STAR COOL','CIM6','INDND3074','MANGA TERMOCONTRAIBLE 15MM',''],
        ['STAR COOL','CIM6','INDND3322','MANGA TERMOCONTRAIBLE 20MM',''],
        ['STAR COOL','CIM6','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['STAR COOL','CIM6','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['STAR COOL','CIM6','INDND1555','PERNO HEX. RC. INOX 304 M16X50',''],
        ['STAR COOL','CIM6','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['STAR COOL','CIM6','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['STAR COOL','CIM6','INDND2768','RODAJE 6201 2RSH/C3',''],
        ['STAR COOL','CIM6','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['STAR COOL','CIM6','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['STAR COOL','CIM6','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['STAR COOL','CIM6','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['STAR COOL','CIM6','INDND5576','TERMINAL TUBULAR SOBREMOLDEADO ROJO 4MM 12AWG',''],
        ['STAR COOL','CIM6','INDND2711','TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG',''],
        ['STAR COOL','CIM6','INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG',''],
        ['STAR COOL','CIM6','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['STAR COOL','CIM6','INDND0087','SOLVENTE DIELECTRICO SDL-25',''],
        ['STAR COOL','CIM6','INDND1412','VALVULA DE ACCESO 1/4 X 7 CM',''],
        ['STAR COOL','CIM5','818270C','AIR EXCHANGE MODULE (75CMH)',''],
        ['STAR COOL','CIM5','819747B','AIR RELEASE VALVE, RECEIVER',''],
        ['STAR COOL','CIM5','818522F','AUXILIARY CONTACT (WHITE DOT 10PCS)',''],
        ['STAR COOL','CIM5','818522C','AUXILIARY CONTACT',''],
        ['STAR COOL','CIM5','818536B','BATTERY PACK CIM5',''],
        ['STAR COOL','CIM5','811537D','BRACKET, EVAPORATOR FAN MOTOR',''],
        ['STAR COOL','CIM5','818202B','CABLE ADAPTER KIT, FAN MOTOR',''],
        ['STAR COOL','CIM5','815505D','CABLE ROOM COVER',''],
        ['STAR COOL','CIM5','818561B','CABLE SET (X1, X2, X3), CIM 5',''],
        ['STAR COOL','CIM5','814247C','CABLE, FC (1.0 AND 1.1) TO COMPRESSOR',''],
        ['STAR COOL','CIM5','819526B','COIL CONDENSER',''],
        ['STAR COOL','CIM5','818658B','COMPRESSOR',''],
        ['STAR COOL','CIM5','818521B','CONTACTOR',''],
        ['STAR COOL','CIM5','818310B','CONTROLLER DOOR, CIM 5',''],
        ['STAR COOL','CIM5','818320B','CONTROLLER DOOR, COMPLETE CIM 5',''],
        ['STAR COOL','CIM5','818512A','CONTROLLER MODULE, CA',''],
        ['STAR COOL','CIM5','868255C','CONTROLLER MODULE, CIM 5',''],
        ['STAR COOL','CIM5','818255C','CONTROLLER MODULE, CIM 5',''],
        ['STAR COOL','CIM5','818209D','COVER PLATE (2100MM) CONDENSER SCI',''],
        ['STAR COOL','CIM5','818250E','DAMPER, AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM5','811522B','DEFROST HEATER ELEMENT, TRAY',''],
        ['STAR COOL','CIM5','818515A','DISPLAY PCB, CIM 5',''],
        ['STAR COOL','CIM5','814667F','ECONOMIZER',''],
        ['STAR COOL','CIM5','819737D','ECONOMIZER VALVE, R134A',''],
        ['STAR COOL','CIM5','881527A','EVAPORATOR COIL',''],
        ['STAR COOL','CIM5','819543B','FAN BLADE, CONDENSER',''],
        ['STAR COOL','CIM5','819542C','FAN BLADE, EVAPORATOR',''],
        ['STAR COOL','CIM5','819506A','FILTER DRYER, R134A AND R513A',''],
        ['STAR COOL','CIM5','818738A','FILTER DRYER, R134A AND R513A (12 PCS)',''],
        ['STAR COOL','CIM5','818965B','FREQUENCY CONVERTER 2.1',''],
        ['STAR COOL','CIM5','818274C','FRONT PART, AIR EXCHANGE MODULE (75 CMH)',''],
        ['STAR COOL','CIM5','818656B','GASKET, COMPRESSOR STOP VALVE',''],
        ['STAR COOL','CIM5','818661B','GASKET, SERVICE VALVE LP',''],
        ['STAR COOL','CIM5','819501A','HIGH PRESSURE SWITCH',''],
        ['STAR COOL','CIM5','889740C','HOT GAS VALVE',''],
        ['STAR COOL','CIM5','819740B','HOT GAS VALVE, CIM 5',''],
        ['STAR COOL','CIM5','818551A','HUMIDITY SENSOR',''],
        ['STAR COOL','CIM5','814571B','INSULATION, ECONOMIZER',''],
        ['STAR COOL','CIM5','818523C','INTERLOCK, CONTACTOR',''],
        ['STAR COOL','CIM5','818527B','KEY PAD CIM 5',''],
        ['STAR COOL','CIM5','818517A','LED PCB, CIM 5',''],
        ['STAR COOL','CIM5','818906A','MAIN CIRCUIT BREAKER, CIM 5',''],
        ['STAR COOL','CIM5','818236B','MELT FUSE IT',''],
        ['STAR COOL','CIM5','818792A','MOTOR, CONDENSER FAN',''],
        ['STAR COOL','CIM5','818783A','MOTOR, EVAPORATOR FAN',''],
        ['STAR COOL','CIM5','814538D','MOUNTING RING, FILTER',''],
        ['STAR COOL','CIM5','818525B','ON/OFF CIM5',''],
        ['STAR COOL','CIM5','818652A','PERMANENT MAGNET',''],
        ['STAR COOL','CIM5','881550A','PLUG, EVAPORATOR SERVICE HOLE',''],
        ['STAR COOL','CIM5','819541C','PLUG, WATER INLET COUPLING',''],
        ['STAR COOL','CIM5','819540C','PLUG, WATER OUTLET COUPLING',''],
        ['STAR COOL','CIM5','818511B','POWER MEASUREMENT PCB, CIM 5',''],
        ['STAR COOL','CIM5','819503D','PRESSURE TRANSMITTER LP NSK',''],
        ['STAR COOL','CIM5','819504D','PRESSURE TRANSMITTER HP DST',''],
        ['STAR COOL','CIM5','814540B','RECEIVER',''],
        ['STAR COOL','CIM5','818739A','RECEIVER, WATER COOLED CONDENSER',''],
        ['STAR COOL','CIM5','819693D','SCREW, CONTROLLER DOOR CIM 6',''],
        ['STAR COOL','CIM5','818276A','SENSOR AIR EXCHANGE MODULE',''],
        ['STAR COOL','CIM5','818675B','SERVICE KIT, HOT GAS VALVE CIM 5',''],
        ['STAR COOL','CIM5','818623C','SERVICE VALVE, COMPRESSOR LP',''],
        ['STAR COOL','CIM5','818235B','SIGHT GLASS KIT, RECEIVER',''],
        ['STAR COOL','CIM5','818554A','SOLENOID COIL 18W 24VDC CIM 5',''],
        ['STAR COOL','CIM5','818553A','SOLENOID COIL 14W 24VDC CIM 5',''],
        ['STAR COOL','CIM5','886554B','SOLENOID COIL 11W 24VAC',''],
        ['STAR COOL','CIM5','818526C','TERMINAL BLOCK PCB, CIM 5',''],
        ['STAR COOL','CIM5','818639B','TERMINAL BLOCK, COMPRESSOR',''],
        ['STAR COOL','CIM5','818676B','TOOL, HOT GAS VALVE',''],
        ['STAR COOL','CIM5','818518B','TRANSFORMER 145 VA, CIM 5',''],
        ['STAR COOL','CIM5','818267B','WING SCREW KIT',''],
        ['STAR COOL','CIM5','INDND0078','ACEITE AFLOJATODO',''],
        ['STAR COOL','CIM5','INDND0411','ACEITE POLYOLESTER BVA',''],
        ['STAR COOL','CIM5','INDND3242','BROCA DE COBALTO HSS 3/16',''],
        ['STAR COOL','CIM5','INDND0259','CABLE VULCANIZADO 4 X10',''],
        ['STAR COOL','CIM5','RNDND0254','CINTA AISLANTE 3M',''],
        ['STAR COOL','CIM5','INDND0432','CINTA FOAM 1/8 X 2 X 9.14M',''],
        ['STAR COOL','CIM5','INDND0433','CINTILLO DE AMARRE 150MM',''],
        ['STAR COOL','CIM5','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['STAR COOL','CIM5','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['STAR COOL','CIM5','INDND1911','EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M',''],
        ['STAR COOL','CIM5','INDND0126','ESTAÑO 0.8',''],
        ['STAR COOL','CIM5','INDND2552','FILTRO SECADOR QDM-164 1/2 - QUALITY',''],
        ['STAR COOL','CIM5','INDND0024','FILTRO SEC. FIJO DE 1/2 FLARE - EK 164 STD',''],
        ['STAR COOL','CIM5','INDND2905','FORMADOR EMPAQUETADURA AVIACION 3H',''],
        ['STAR COOL','CIM5','INDND2237','FUNDENTE',''],
        ['STAR COOL','CIM5','INDND1545','MINI FUSIBLE DE VIDRIO 15 AMP',''],
        ['STAR COOL','CIM5','INDND1542','FUSIBLE DE VIDRIO 10 AMP',''],
        ['STAR COOL','CIM5','RNDND0318','FUSIBLE DE VIDRIO 20 AMP',''],
        ['STAR COOL','CIM5','INDND0120','GAS REFRIGERANTE R-134A X 13.60KG',''],
        ['STAR COOL','CIM5','RNDND0438','JABON LIQUIDO',''],
        ['STAR COOL','CIM5','INDND0016','LIJA FIERRO #40 ASA',''],
        ['STAR COOL','CIM5','INDND0079','LIMPIA CONTACTO',''],
        ['STAR COOL','CIM5','INDND0086','NITROGENO INDUSTRIAL 10 M3',''],
        ['STAR COOL','CIM5','INDND3074','MANGA TERMOCONTRAIBLE 15MM',''],
        ['STAR COOL','CIM5','INDND3322','MANGA TERMOCONTRAIBLE 20MM',''],
        ['STAR COOL','CIM5','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['STAR COOL','CIM5','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['STAR COOL','CIM5','INDND1555','PERNO HEX. RC. INOX 304 M16X50',''],
        ['STAR COOL','CIM5','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['STAR COOL','CIM5','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['STAR COOL','CIM5','INDND2768','RODAJE 6201 2RSH/C3',''],
        ['STAR COOL','CIM5','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['STAR COOL','CIM5','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['STAR COOL','CIM5','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['STAR COOL','CIM5','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['STAR COOL','CIM5','INDND5576','TERMINAL TUBULAR SOBREMOLDEADO ROJO 4MM 12AWG',''],
        ['STAR COOL','CIM5','INDND2711','TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG',''],
        ['STAR COOL','CIM5','INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG',''],
        ['STAR COOL','CIM5','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['STAR COOL','CIM5','INDND0087','SOLVENTE DIELECTRICO SDL-25',''],
        ['STAR COOL','CIM5','INDND1412','VALVULA DE ACCESO 1/4 X 7 CM',''],
        ['THERMO KING','MP5000','672454','COIL - CONDENSER (ALUMINUM FINS)',''],
        ['THERMO KING','MP5000','781924','FAN - CONDENSER',''],
        ['THERMO KING','MP5000','1040858','MOTOR - CONDENSER FAN',''],
        ['THERMO KING','MP5000','970599','HOUSING - EVAPORATOR (2 FANS)',''],
        ['THERMO KING','MP5000','427333','SENSOR - RETURN AIR',''],
        ['THERMO KING','MP5000','782096','FAN - EVAPORATOR 355MM 7 BLADES',''],
        ['THERMO KING','MP5000','941887','BRACKET - MOTOR',''],
        ['THERMO KING','MP5000','1040894','MOTOR - FAN',''],
        ['THERMO KING','MP5000','673471','COIL - EVAPORATOR',''],
        ['THERMO KING','MP5000','427334','SENSOR - DEFROST',''],
        ['THERMO KING','MP5000','422659','SENSOR - HUMIDITY',''],
        ['THERMO KING','MP5000','427338','SENSOR - CO2 RS485',''],
        ['THERMO KING','MP5000','420374','CABLE - SUPPLY RS485',''],
        ['THERMO KING','MP5000','612477','TUBE - VALVE TO COIL',''],
        ['THERMO KING','MP5000','672787','TANK - RECEIVER STANDARD',''],
        ['THERMO KING','MP5000','610786','DEHYDRATOR',''],
        ['THERMO KING','MP5000','671889','HEAT EXCHANGER - ECONOMIZER',''],
        ['THERMO KING','MP5000','618684','VALVE - SOLENOID VAPOR INJECTION',''],
        ['THERMO KING','MP5000','415460','COIL - VALVE',''],
        ['THERMO KING','MP5000','600731','KIT - TXV EXPANSION VALVE',''],
        ['THERMO KING','MP5000','612465','VALVE - BALL',''],
        ['THERMO KING','MP5000','617758','VALVE PWM',''],
        ['THERMO KING','MP5000','421423','SWITCH - LPCO',''],
        ['THERMO KING','MP5000','672853','TANK - RECEIVER WITH SHUT-OFF VALVE',''],
        ['THERMO KING','MP5000','425968','TRANSDUCER - SUCTION',''],
        ['THERMO KING','MP5000','610443','VALVE - EXPANSION',''],
        ['THERMO KING','MP5000','1020795','COMPRESSOR - SCROLL',''],
        ['THERMO KING','MP5000','919021','COVER - TERMINAL BOX',''],
        ['THERMO KING','MP5000','401377','KIT - THERMISTOR',''],
        ['THERMO KING','MP5000','414004','SWITCH - HPCO',''],
        ['THERMO KING','MP5000','612118','VALVE - SUCTION',''],
        ['THERMO KING','MP5000','612119','VALVE - DISCHARGE',''],
        ['THERMO KING','MP5000','335215','GASKET - VALVE SERVICE',''],
        ['THERMO KING','MP5000','400782','KIT - POWER CORD',''],
        ['THERMO KING','MP5000','401044','SENSOR KIT DEFROST/AMBIENT/RETURN/SUPPLY/COIL',''],
        ['THERMO KING','MP5000','451992','CABLE - POWER 19.2 METERS',''],
        ['THERMO KING','MP5000','452889','HEATER 1360W',''],
        ['THERMO KING','MP5000','453031','BASE - CONTROL BOX MP-5000',''],
        ['THERMO KING','MP5000','413595','SWITCH - ON/OFF',''],
        ['THERMO KING','MP5000','426427','TRANSFORMER',''],
        ['THERMO KING','MP5000','426424','BATTERY - MP-5000',''],
        ['THERMO KING','MP5000','426423','CONTROLLER - MP-5000',''],
        ['THERMO KING','MP5000','427238','BUSBAR COMB 63A 3 TAP-OFFS',''],
        ['THERMO KING','MP5000','427239','BUSBAR COMB 63A 4 TAP-OFFS',''],
        ['THERMO KING','MP5000','423820','CONTACTOR AC LC1D 3P 25A',''],
        ['THERMO KING','MP5000','426428','TRANSFORMER CURRENT MP-5000',''],
        ['THERMO KING','MP5000','415104','BREAKER CIRCUIT 25A',''],
        ['THERMO KING','MP5000','940841','DOOR - CONTROLLER MP-5000',''],
        ['THERMO KING','MP5000','426430','KEYPAD - CONTROLLER',''],
        ['THERMO KING','MP5000','427072','DISPLAY - LARGE',''],
        ['THERMO KING','MP5000','426752','MODULE - MP-5000',''],
        ['THERMO KING','MP5000','1021428','COMPRESSOR ASSEMBLY WITH MOTOR',''],
        ['THERMO KING','MP5000','221473','FILTER - COMPRESSOR SUCTION',''],
        ['THERMO KING','MP5000','941897','COVER - COMPRESSOR',''],
        ['THERMO KING','MP5000','427160','SENSOR - O2 RS485',''],
        ['THERMO KING','MP5000','427161','SENSOR - CO2 RS485',''],
        ['THERMO KING','MP5000','918252TKA','VENT - AIR COMPLETE',''],
        ['THERMO KING','MP5000','929522','BRACKET - AIR VENT',''],
        ['THERMO KING','MP5000','417238TKA','ACTUATOR',''],
        ['THERMO KING','MP5000','925687','DOOR - AFAM',''],
        ['THERMO KING','MP5000','937326','GRILLE - FRESH AIR',''],
        ['THERMO KING','MP5000','925661','LABEL - AFAM DOOR POSITION',''],
        ['THERMO KING','MP5000','INDND0411','ACEITE POLYOLESTER',''],
        ['THERMO KING','MP5000','INDND0078','AFLOJATODO',''],
        ['THERMO KING','MP5000','INDND4464','AGENTE LIMPIADOR DE SISTEMAS OPTEON SF FLUSH X 4.54 KG',''],
        ['THERMO KING','MP5000','INDND3876','ANILLO DE TEFLON 1/16 D 15 X 10 MM',''],
        ['THERMO KING','MP5000','INDND3565','ARANDELA DE PRESION INOX M6',''],
        ['THERMO KING','MP5000','INDND1391','ARANDELA PLANA INOX 1/4',''],
        ['THERMO KING','MP5000','INDND2543','BOMBA DE VACIO CPS VP6D 1/2HP',''],
        ['THERMO KING','MP5000','INDND0259','CABLE VULCANIZADO 4X10',''],
        ['THERMO KING','MP5000','RNDND0254','CINTA AISLANTE 3M',''],
        ['THERMO KING','MP5000','INDND0432','CINTA FOAM 1/8 X 2 X 9.4 M',''],
        ['THERMO KING','MP5000','INDND4185','CINTA PARA DUCTO 10MX48MM',''],
        ['THERMO KING','MP5000','INDND2786','CINTA VULCANIZANTE SCOTCH 23 3/4',''],
        ['THERMO KING','MP5000','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['THERMO KING','MP5000','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['THERMO KING','MP5000','INDND1718','CONTACTOR SCHNEIDER 32A 440V LC1D32R7',''],
        ['THERMO KING','MP5000','INDND4864','CONTACTOR TESYS DECA 3P AC-3 12A BOBINA 24VAC LC1D12B7',''],
        ['THERMO KING','MP5000','INDND1911','EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M',''],
        ['THERMO KING','MP5000','RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67',''],
        ['THERMO KING','MP5000','INDND0126','ESTAÑO 0.8',''],
        ['THERMO KING','MP5000','818738A','FILTER DRYER, R134A AND R513A (12 PCS)',''],
        ['THERMO KING','MP5000','RNDND0318','FUSIBLE DE VIDRIO 20 AMP',''],
        ['THERMO KING','MP5000','INDND2144','GAS MAP PRO',''],
        ['THERMO KING','MP5000','INDND0022','GAS REFRIGERANTE R-404A X 10.90KG',''],
        ['THERMO KING','MP5000','RNDND0438','JABON LIQUIDO',''],
        ['THERMO KING','MP5000','INDND1184','LIJA FIERRO #100',''],
        ['THERMO KING','MP5000','INDND0016','LIJA FIERRO #40',''],
        ['THERMO KING','MP5000','INDND0079','LIMPIA CONTACTO',''],
        ['THERMO KING','MP5000','INDND2300','MANGA TERMOCONTRAIBLE 25MM',''],
        ['THERMO KING','MP5000','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['THERMO KING','MP5000','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['THERMO KING','MP5000','INDND0738','MANGUERA CORRUGADA 1/2',''],
        ['THERMO KING','MP5000','INDND0279','MANGUERA CORRUGADA 3/8',''],
        ['THERMO KING','MP5000','INDND2111','MANGUERA CORRUGADA DE 1 PULGADA',''],
        ['THERMO KING','MP5000','INDND4520','ORING VITON 3-023',''],
        ['THERMO KING','MP5000','INDND3649','ORING VITON 2-014',''],
        ['THERMO KING','MP5000','INDND1104','PEGAMENTO SUPERFLEX INDUSTRIAL',''],
        ['THERMO KING','MP5000','INDND1417','PERNO HEX ZINC 1/4 X 1',''],
        ['THERMO KING','MP5000','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['THERMO KING','MP5000','INDND2789','PRENSA ESTOPA 1 NPT',''],
        ['THERMO KING','MP5000','INDND2838','PRENSA ESTOPA 3/8 PG11',''],
        ['THERMO KING','MP5000','INDND3078','RELE PROTECTOR DE FASE TIPO GALLETA GRV8-03',''],
        ['THERMO KING','MP5000','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['THERMO KING','MP5000','INDND2265','RIEL DIN PERFORADO',''],
        ['THERMO KING','MP5000','INDND0260','RODAMIENTO 6203',''],
        ['THERMO KING','MP5000','INDND0081','RODAMIENTO 6205',''],
        ['THERMO KING','MP5000','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['THERMO KING','MP5000','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['THERMO KING','MP5000','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['THERMO KING','MP5000','INDND1962','TERMINAL AISLADO TIPO BALA HEMBRA AZUL',''],
        ['THERMO KING','MP5000','INDND1097','TERMINAL AISLADO TIPO BALA MACHO AZUL',''],
        ['THERMO KING','MP5000','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['THERMO KING','MP5000','INDND0885','TERMINAL OJO VF5.5-6S 1/4',''],
        ['THERMO KING','MP5000','INDND2711','TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG',''],
        ['THERMO KING','MP5000','INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG',''],
        ['THERMO KING','MP5000','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['THERMO KING','MP5000','RNDND0802','TUBERIA DE COBRE 1/2',''],
        ['THERMO KING','MP5000','INDND0754','TUBERIA DE COBRE 1/4',''],
        ['THERMO KING','MP5000','INDND0357','TUBERIA DE COBRE 3/8',''],
        ['THERMO KING','MP5000','RNDND0440','TUBO DE COBRE 1/8 X 15M',''],
        ['THERMO KING','MP5000','INDND0169','TUERCA HEXAGONAL 1/4 ZINCADO',''],
        ['THERMO KING','MP5000','INDND1536','UNION SOLDABLE 1/4',''],
        ['THERMO KING','MP4000','417238TKA','ACTUATOR',''],
        ['THERMO KING','MP4000','918252','AIR VENT',''],
        ['THERMO KING','MP4000','418717','BATTERY LITHIUM MP-4000',''],
        ['THERMO KING','MP4000','413596','BLOCK - TERMINAL 4 POLE',''],
        ['THERMO KING','MP4000','413598','BLOCK - TERMINAL 8 POLE',''],
        ['THERMO KING','MP4000','918466','BRACKET MOTOR',''],
        ['THERMO KING','MP4000','RNDND0724','BREAKER CIRCUIT 25A',''],
        ['THERMO KING','MP4000','418716','CABLE SERIAL CM-4000A0 / PM4000',''],
        ['THERMO KING','MP4000','INDND5049','CABLE SUPPLY 420374',''],
        ['THERMO KING','MP4000','91-9331','CHANNEL - FRESH AIR',''],
        ['THERMO KING','MP4000','671923','COIL EVAPORATOR',''],
        ['THERMO KING','MP4000','415460','COIL VALVE LIQ',''],
        ['THERMO KING','MP4000','INDND1604','COMPRESSOR - SCROLL',''],
        ['THERMO KING','MP4000','69NT4320220','CONDENSER COIL',''],
        ['THERMO KING','MP4000','421636','CONNECTOR 10-PIN J2/J17',''],
        ['THERMO KING','MP4000','412446','CONTACTOR 25A',''],
        ['THERMO KING','MP4000','RNDND0064','CONTACTOR 30 AMP',''],
        ['THERMO KING','MP4000','100043106','CONTACTOR 12AMP',''],
        ['THERMO KING','MP4000','452295','CONTROLLER MP4000',''],
        ['THERMO KING','MP4000','418718','COVER EXPANSION BOARD',''],
        ['THERMO KING','MP4000','937354','DECAL R404A',''],
        ['THERMO KING','MP4000','418723','DOOR FRONT MP-4000 WHITE',''],
        ['THERMO KING','MP4000','610156','DRIER UNIVERSAL CONTAINER',''],
        ['THERMO KING','MP4000','818738A','FILTER DRYER, R134A AND R513A (12 PCS)',''],
        ['THERMO KING','MP4000','781683','EVAPORATOR FAN',''],
        ['THERMO KING','MP4000','78-1684','FAN - CONDENSER',''],
        ['THERMO KING','MP4000','781924','FAN CONDENSER ASSEMBLY',''],
        ['THERMO KING','MP4000','669842','FITTING FOR LPCO',''],
        ['THERMO KING','MP4000','559485','FLATWASHER',''],
        ['THERMO KING','MP4000','RNDND0624','FUSE HOLDER BLK MP4000',''],
        ['THERMO KING','MP4000','332510','GASKET - VALVE PLATE',''],
        ['THERMO KING','MP4000','332805','GASKET DISCHARGE',''],
        ['THERMO KING','MP4000','988244','GRILLE - EVAPORATOR',''],
        ['THERMO KING','MP4000','452889','HEATER ELEMENT 1360W BROWN',''],
        ['THERMO KING','MP4000','45-2451','HEATER ELEMENT 2000W',''],
        ['THERMO KING','MP4000','3504979','HEATER ELEMENT 750W 230V',''],
        ['THERMO KING','MP4000','422659','HUMIDITY SENSOR',''],
        ['THERMO KING','MP4000','INDND4844','KIT - POWER CORD',''],
        ['THERMO KING','MP4000','401044','KIT SENSOR MP4000',''],
        ['THERMO KING','MP4000','900331TKA','KIT SPACER FAN',''],
        ['THERMO KING','MP4000','INDND4843','KIT THERMISTOR THK',''],
        ['THERMO KING','MP4000','INDND1609','KIT TXV ECONOMIZER',''],
        ['THERMO KING','MP4000','420353','MODULE - AFAM+',''],
        ['THERMO KING','MP4000','418719','MODULE POWER MP4000',''],
        ['THERMO KING','MP4000','104-759','MOTOR CONDENSADOR TK',''],
        ['THERMO KING','MP4000','104691','MOTOR EVAPORADOR',''],
        ['THERMO KING','MP4000','47225','MP4000 CONTROL BOX',''],
        ['THERMO KING','MP4000','330727','O RING',''],
        ['THERMO KING','MP4000','927635','RAIL - DIN',''],
        ['THERMO KING','MP4000','421595','SENSOR CO2 RS485',''],
        ['THERMO KING','MP4000','RNDND0562','SENSOR USDA 2.5MM',''],
        ['THERMO KING','MP4000','781737','SHROUD FAN',''],
        ['THERMO KING','MP4000','414004','SWITCH HPCO',''],
        ['THERMO KING','MP4000','INDND1606','SWITCH LPCO',''],
        ['THERMO KING','MP4000','418763','TRANSFORMER MP4000',''],
        ['THERMO KING','MP4000','618179','TX VALVE ECONOMIZER',''],
        ['THERMO KING','MP4000','669900','VALVE EXPANSION ECONOMIZER',''],
        ['THERMO KING','MP4000','RNDND0130','VALVE EXPANSION',''],
        ['THERMO KING','MP4000','612470','VALVE SOLENOID',''],
        ['THERMO KING','MP4000','617758','VALVE DIGITAL',''],
        ['THERMO KING','MP4000','INDND0411','ACEITE POLYOLESTER',''],
        ['THERMO KING','MP4000','INDND0078','AFLOJATODO',''],
        ['THERMO KING','MP4000','INDND4464','AGENTE LIMPIADOR DE SISTEMAS OPTEON SF FLUSH X 4.54 KG',''],
        ['THERMO KING','MP4000','INDND3876','ANILLO DE TEFLON 1/16 D 15 X 10 MM',''],
        ['THERMO KING','MP4000','INDND3565','ARANDELA DE PRESION INOX M6',''],
        ['THERMO KING','MP4000','INDND1391','ARANDELA PLANA INOX 1/4',''],
        ['THERMO KING','MP4000','INDND2543','BOMBA DE VACIO CPS VP6D 1/2HP',''],
        ['THERMO KING','MP4000','INDND0259','CABLE VULCANIZADO 4X10',''],
        ['THERMO KING','MP4000','RNDND0254','CINTA AISLANTE 3M',''],
        ['THERMO KING','MP4000','INDND0432','CINTA FOAM 1/8 X 2 X 9.4 M',''],
        ['THERMO KING','MP4000','INDND4185','CINTA PARA DUCTO 10MX48MM',''],
        ['THERMO KING','MP4000','INDND2786','CINTA VULCANIZANTE SCOTCH 23 3/4',''],
        ['THERMO KING','MP4000','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['THERMO KING','MP4000','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['THERMO KING','MP4000','INDND1718','CONTACTOR SCHNEIDER 32A 440V LC1D32R7',''],
        ['THERMO KING','MP4000','INDND4864','CONTACTOR TESYS DECA 3P AC-3 12A BOBINA 24VAC LC1D12B7',''],
        ['THERMO KING','MP4000','INDND1911','EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M',''],
        ['THERMO KING','MP4000','RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67',''],
        ['THERMO KING','MP4000','INDND0126','ESTAÑO 0.8',''],
        ['THERMO KING','MP4000','RNDND0318','FUSIBLE DE VIDRIO 20 AMP',''],
        ['THERMO KING','MP4000','INDND2144','GAS MAP PRO',''],
        ['THERMO KING','MP4000','INDND0022','GAS REFRIGERANTE R-404A X 10.90KG',''],
        ['THERMO KING','MP4000','RNDND0438','JABON LIQUIDO',''],
        ['THERMO KING','MP4000','INDND1184','LIJA FIERRO #100',''],
        ['THERMO KING','MP4000','INDND0016','LIJA FIERRO #40',''],
        ['THERMO KING','MP4000','INDND0079','LIMPIA CONTACTO',''],
        ['THERMO KING','MP4000','INDND2300','MANGA TERMOCONTRAIBLE 25MM',''],
        ['THERMO KING','MP4000','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['THERMO KING','MP4000','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['THERMO KING','MP4000','INDND0738','MANGUERA CORRUGADA 1/2',''],
        ['THERMO KING','MP4000','INDND0279','MANGUERA CORRUGADA 3/8',''],
        ['THERMO KING','MP4000','INDND2111','MANGUERA CORRUGADA DE 1 PULGADA',''],
        ['THERMO KING','MP4000','INDND4520','ORING VITON 3-023',''],
        ['THERMO KING','MP4000','INDND3649','ORING VITON 2-014',''],
        ['THERMO KING','MP4000','INDND1104','PEGAMENTO SUPERFLEX INDUSTRIAL',''],
        ['THERMO KING','MP4000','INDND1417','PERNO HEX ZINC 1/4 X 1',''],
        ['THERMO KING','MP4000','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['THERMO KING','MP4000','INDND2789','PRENSA ESTOPA 1 NPT',''],
        ['THERMO KING','MP4000','INDND2838','PRENSA ESTOPA 3/8 PG11',''],
        ['THERMO KING','MP4000','INDND3078','RELE PROTECTOR DE FASE TIPO GALLETA GRV8-03',''],
        ['THERMO KING','MP4000','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['THERMO KING','MP4000','INDND2265','RIEL DIN PERFORADO',''],
        ['THERMO KING','MP4000','INDND0260','RODAMIENTO 6203',''],
        ['THERMO KING','MP4000','INDND0081','RODAMIENTO 6205',''],
        ['THERMO KING','MP4000','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['THERMO KING','MP4000','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['THERMO KING','MP4000','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['THERMO KING','MP4000','INDND1962','TERMINAL AISLADO TIPO BALA HEMBRA AZUL',''],
        ['THERMO KING','MP4000','INDND1097','TERMINAL AISLADO TIPO BALA MACHO AZUL',''],
        ['THERMO KING','MP4000','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['THERMO KING','MP4000','INDND0885','TERMINAL OJO VF5.5-6S 1/4',''],
        ['THERMO KING','MP4000','INDND2711','TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG',''],
        ['THERMO KING','MP4000','INDND2936','TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG',''],
        ['THERMO KING','MP4000','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['THERMO KING','MP4000','RNDND0802','TUBERIA DE COBRE 1/2',''],
        ['THERMO KING','MP4000','INDND0754','TUBERIA DE COBRE 1/4',''],
        ['THERMO KING','MP4000','INDND0357','TUBERIA DE COBRE 3/8',''],
        ['THERMO KING','MP4000','RNDND0440','TUBO DE COBRE 1/8 X 15M',''],
        ['THERMO KING','MP4000','INDND0169','TUERCA HEXAGONAL 1/4 ZINCADO',''],
        ['THERMO KING','MP4000','INDND1536','UNION SOLDABLE 1/4',''],
        ['CARRIER','TODOS','10-00439-01','AMPERIMETRO',''],
        ['CARRIER','TODOS','22-50088-01','CAPACITOR 15UF',''],
        ['CARRIER','TODOS','22-50088-00','CAPACITOR 20UF',''],
        ['CARRIER','TODOS','INDND0431','CAPACITOR DE 5UF',''],
        ['CARRIER','TODOS','66U1-7842-13','CIRCUIT BREAKER 460VAC 25AMP',''],
        ['CARRIER','TODOS','14-00247-20','COIL',''],
        ['CARRIER','TODOS','14-00393-10','COIL 2010-2012',''],
        ['CARRIER','TODOS','76-00748-00','COIL EVAPORATOR',''],
        ['CARRIER','TODOS','14-00393-10','COIL EVV',''],
        ['CARRIER','TODOS','14-00247-20','COIL VALVE EXPANSION 2008-2010',''],
        ['CARRIER','TODOS','14-00230-24SV','COIL SOLENOID',''],
        ['CARRIER','TODOS','14-01091-02','COIL SOLENOID 24V',''],
        ['CARRIER','TODOS','18-10134-23','COMPRESSOR SCROLL AZUL',''],
        ['CARRIER','TODOS','18-10178-20','COMPRESSOR SCROLL PLOMO',''],
        ['CARRIER','TODOS','18-10129-20SV','COMPRESSOR CONT 41CFM',''],
        ['CARRIER','TODOS','69NT43-202-20','CONDENSOR COIL',''],
        ['CARRIER','TODOS','100043106','CONTACTOR 12AMP 10-00431-06',''],
        ['CARRIER','TODOS','RNDND0064','CONTACTOR 30 AMP 10-00431-07',''],
        ['CARRIER','TODOS','120057900','MICROLINK 3',''],
        ['CARRIER','TODOS','1256002','MICROLINK 2I',''],
        ['CARRIER','TODOS','400052000','COUPLING M13 LOW',''],
        ['CARRIER','TODOS','400052001','COUPLING M15 HIGH',''],
        ['CARRIER','TODOS','69NT20-2083','COVER JUNCTION BOX',''],
        ['CARRIER','TODOS','12-00433-03RP','DISPLAY',''],
        ['CARRIER','TODOS','14-00393-00SV','EEV 2010-2012',''],
        ['CARRIER','TODOS','38-00585-00','FAN CONDENSER',''],
        ['CARRIER','TODOS','38-00599-00','FAN EVAPORATOR',''],
        ['CARRIER','TODOS','INDND1585','THERMISTOR TEMP SENSOR',''],
        ['CARRIER','TODOS','3504979','HEATER BAR 750W',''],
        ['CARRIER','TODOS','296660300','HEATER 750V 230V',''],
        ['CARRIER','TODOS','14-00221-04','INDICATOR SIGHTGLASS R134A',''],
        ['CARRIER','TODOS','79-66669-02','KEYPAD ASSY',''],
        ['CARRIER','TODOS','INDND2389','KIT EMPAQUETADURA CARRIER',''],
        ['CARRIER','TODOS','12-00495-02SV','KIT AMBIENT/DEFROST SENSOR',''],
        ['CARRIER','TODOS','12-00425-00','MODULE CONTROLLER MICRO-LINK 2i',''],
        ['CARRIER','TODOS','INDND0904','MOTOR EVAPORADOR TRIFASICO',''],
        ['CARRIER','TODOS','54-00586-20','MOTOR CONDENSER',''],
        ['CARRIER','TODOS','54-00585-20','MOTOR EVAPORADOR MONOFASICO',''],
        ['CARRIER','TODOS','30-00407-02SV','PACK BATTERY DATACORDER',''],
        ['CARRIER','TODOS','10-00388-00','POWERPACK STEPPER MOTOR',''],
        ['CARRIER','TODOS','12-00500-01SV','SENSOR COMBINATION RETURN',''],
        ['CARRIER','TODOS','12-00745-00SV','SENSOR HUMIDITY W/BRACKET',''],
        ['CARRIER','TODOS','12-00395-01SV','SENSOR THERMISTOR SUPPLY',''],
        ['CARRIER','TODOS','12-00309-06','SWITCH HIGH PRESSURE HPS',''],
        ['CARRIER','TODOS','RNDND0131','SWITCH THERMOSTAT',''],
        ['CARRIER','TODOS','65-00185-03','TANQUE RECIBIDOR',''],
        ['CARRIER','TODOS','17-40075-05','TERMINAL PLATE',''],
        ['CARRIER','TODOS','12-00352-00','TRANSDUCER PRESSURE HIGH',''],
        ['CARRIER','TODOS','12-00352-07SV','TRANSDUCER PRESSURE LOW',''],
        ['CARRIER','TODOS','INDND2612','TRANSFORMER ELECTRIC CONTROL 440/24V',''],
        ['CARRIER','TODOS','12-00655-01','TRANSDUCER PRIME LINE',''],
        ['CARRIER','TODOS','14-00247-01','VALVE',''],
        ['CARRIER','TODOS','140027308','VALVE HERMETIC TXV THINLINE',''],
        ['CARRIER','TODOS','14-00204-04','VALVE DISCHARGE DPRV',''],
        ['CARRIER','TODOS','14-00247-01','VALVE EVAPORATOR EXPANSION',''],
        ['CARRIER','TODOS','14-00232-33','VALVE EXPANSION',''],
        ['CARRIER','TODOS','14-00206-00','VALVE SERVICE',''],
        ['CARRIER','TODOS','14-00206-01','VALVE SERVICE',''],
        ['CARRIER','TODOS','14-00353-04','VALVE STEPPER MOTOR',''],
        ['CARRIER','TODOS','810147200','TUBE ASSY DISCHARGE',''],
        ['CARRIER','TODOS','14-00232-03','VALVE TXV',''],
        ['CARRIER','TODOS','INDND2543','BOMBA DE VACIO CPS VP6D',''],
        ['CARRIER','TODOS','RNDND0293','CHISPEROS',''],
        ['CARRIER','TODOS','INDND0411','ACEITE POLYOLESTER',''],
        ['CARRIER','TODOS','INDND0078','AFLOJATODO',''],
        ['CARRIER','TODOS','IND0672','ARANDELA DE PRESION ZINC 1/4',''],
        ['CARRIER','TODOS','INDND0175','BROCA DE COBALTO HSS 1/4',''],
        ['CARRIER','TODOS','INDND3242','BROCA DE COBALTO HSS 3/16',''],
        ['CARRIER','TODOS','INDND0134','BROCA DE COBALTO HSS 3/8',''],
        ['CARRIER','TODOS','INDND1448','CABLE FLEXIBLE AUTOMOTRIZ GTP 10AWG',''],
        ['CARRIER','TODOS','RNDND0558','CABLE TW-80 N° 14 AWG',''],
        ['CARRIER','TODOS','INDND0199','CABLE VULCANIZADO 3X16',''],
        ['CARRIER','TODOS','INDND0259','CABLE VULCANIZADO 4X10',''],
        ['CARRIER','TODOS','INDND1589','CAÑA DE SOLDAR',''],
        ['CARRIER','TODOS','RNDND0254','CINTA AISLANTE 3M',''],
        ['CARRIER','TODOS','INDND0432','CINTA FOAM 1/8 X 2 X 9.4 M',''],
        ['CARRIER','TODOS','INDND0434','CINTILLO DE AMARRE 250MM',''],
        ['CARRIER','TODOS','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['CARRIER','TODOS','INDND1911','EMPAQUE DE ASBESTO',''],
        ['CARRIER','TODOS','RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67',''],
        ['CARRIER','TODOS','INDND5002','EXTENSION CORRIENTE 3X16 30MTS',''],
        ['CARRIER','TODOS','818738A','FILTER DRYER, R134A AND R513A (12 PCS)',''],
        ['CARRIER','TODOS','INDND0194','FUSIBLE TIPO UÑA 10 AMP',''],
        ['CARRIER','TODOS','INDND0193','FUSIBLE TIPO UÑA 5 AMP',''],
        ['CARRIER','TODOS','INDND0648','FUSIBLE TIPO UÑA 7.5 AMP',''],
        ['CARRIER','TODOS','INDND2144','GAS MAP PRO',''],
        ['CARRIER','TODOS','INDND0120','GAS REFRIGERANTE R-134A X 13.60KG',''],
        ['CARRIER','TODOS','RNDND0423','GRASA GRA LGMT 3/1',''],
        ['CARRIER','TODOS','RNDND0438','JABON LIQUIDO',''],
        ['CARRIER','TODOS','INDND0054','LIJA FIERRO #120',''],
        ['CARRIER','TODOS','INDND0079','LIMPIA CONTACTO',''],
        ['CARRIER','TODOS','INDND2300','MANGA TERMOCONTRAIBLE 25MM',''],
        ['CARRIER','TODOS','INDND3075','MANGA TERMOCONTRAIBLE 3MM',''],
        ['CARRIER','TODOS','INDND2974','MANGA TERMOCONTRAIBLE 5MM',''],
        ['CARRIER','TODOS','INDND1417','PERNO HEX ZINC 1/4 X 1',''],
        ['CARRIER','TODOS','INDND0108','PINTURA EN SPRAY ALUMINIO',''],
        ['CARRIER','TODOS','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['CARRIER','TODOS','INDND1547','PORTA FUSIBLE AEREO',''],
        ['CARRIER','TODOS','RNDND0100','RELAY 24V 720W',''],
        ['CARRIER','TODOS','INDND0171','REMACHE POP DE ALUMINIO 3/16X1',''],
        ['CARRIER','TODOS','INDND0173','REMACHE POP DE ALUMINIO 3/16X1/2',''],
        ['CARRIER','TODOS','RNDND0260','RODAMIENTO 6203',''],
        ['CARRIER','TODOS','INDND0081','RODAMIENTO 6205',''],
        ['CARRIER','TODOS','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['CARRIER','TODOS','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['CARRIER','TODOS','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['CARRIER','TODOS','INDND0087','SOLVENTE DIELECTRICO SDL-25',''],
        ['CARRIER','TODOS','INDND1962','TERMINAL AISLADO TIPO BALA HEMBRA AZUL',''],
        ['CARRIER','TODOS','INDND1097','TERMINAL AISLADO TIPO BALA MACHO AZUL',''],
        ['CARRIER','TODOS','INDND0017','TRAPO INDUSTRIAL SUELTO',''],
        ['CARRIER','TODOS','RNDND0802','TUBERIA DE COBRE 1/2',''],
        ['CARRIER','TODOS','INDND0754','TUBERIA DE COBRE 1/4',''],
        ['CARRIER','TODOS','INDND0169','TUERCA HEXAGONAL 1/4 ZINCADO',''],
        ['DAIKIN','DAIKIN','1612576','ACCESS PANEL EVAPORADOR',''],
        ['DAIKIN','DAIKIN','1387173','AIR COOLED CONDENSER',''],
        ['DAIKIN','DAIKIN','1588349','AIR DISCHARGE GRILLE',''],
        ['DAIKIN','DAIKIN','0954633','BOARD PTCT BOBINA DE CORRIENTE',''],
        ['DAIKIN','DAIKIN','1270408','BODY SMV',''],
        ['DAIKIN','DAIKIN','1270390','COIL SMV',''],
        ['DAIKIN','DAIKIN','1266290','COIL SOLENOID VALVE',''],
        ['DAIKIN','DAIKIN','1315426','COMPRESOR',''],
        ['DAIKIN','DAIKIN','0954936','CONDENSER FAN',''],
        ['DAIKIN','DAIKIN','1787494','CONTROL BOX COMPLETO',''],
        ['DAIKIN','DAIKIN','11739318','CONTROL BOX COVER WELDING',''],
        ['DAIKIN','DAIKIN','1295553','CONTROL PANEL',''],
        ['DAIKIN','DAIKIN','1010815','DISPLAY',''],
        ['DAIKIN','DAIKIN','1241385','DRIER ASSY',''],
        ['DAIKIN','DAIKIN','1381120','EARTH LEAKAGE CIRCUIT BREAKER',''],
        ['DAIKIN','DAIKIN','1254538','ELECTRONIC EXPANSION VALVE BODY ASSY',''],
        ['DAIKIN','DAIKIN','138143J','ELECTRONIC EXPANSION VALVE COIL',''],
        ['DAIKIN','DAIKIN','1787470','EVAPORATOR ASSY',''],
        ['DAIKIN','DAIKIN','0777519','FAN EVAPORADOR',''],
        ['DAIKIN','DAIKIN','0980618','FANBLADE OUTSIDE',''],
        ['DAIKIN','DAIKIN','INDND2552','FILTRO SECADOR QDM-164 1/2 QUALITY',''],
        ['DAIKIN','DAIKIN','INDND0024','FILTRO SEC. FIJO DE 1/2 FLARE EK 164 STD',''],
        ['DAIKIN','DAIKIN','1787456','FRONT PLATE',''],
        ['DAIKIN','DAIKIN','003065J','FUSE CONTROLLER',''],
        ['DAIKIN','DAIKIN','1241378','HIGH PRESSURE SWITCH',''],
        ['DAIKIN','DAIKIN','1587959','HIGH PRESSURE TRANSDUCER HPT',''],
        ['DAIKIN','DAIKIN','1679A30','KIT BATTERY',''],
        ['DAIKIN','DAIKIN','1561796','LOW FREQUENCY TRANSFORMER',''],
        ['DAIKIN','DAIKIN','1587942','LOW PRESSURE TRANSDUCER LPT',''],
        ['DAIKIN','DAIKIN','119891J','MAGNETIC CONTACTOR COMPRESSOR',''],
        ['DAIKIN','DAIKIN','119893J','MAGNETIC CONTACTOR FANS',''],
        ['DAIKIN','DAIKIN','124149J','MAGNETIC CONTACTOR PHASE CORRECTION',''],
        ['DAIKIN','DAIKIN','0955333','MOTOR EVAPORADOR',''],
        ['DAIKIN','DAIKIN','2089294','NEW COIL VALVE EVV',''],
        ['DAIKIN','DAIKIN','2075473','NEW VALVE EXP EVV',''],
        ['DAIKIN','DAIKIN','098333J','SENSOR COMP SUCTION TEMP',''],
        ['DAIKIN','DAIKIN','156282J','SENSOR EIS',''],
        ['DAIKIN','DAIKIN','156283J','SENSOR EOS',''],
        ['DAIKIN','DAIKIN','0798321','SENSOR AMBIENT AIR TEMP',''],
        ['DAIKIN','DAIKIN','098332J','SENSOR DISCHARGE PIPE TEMP',''],
        ['DAIKIN','DAIKIN','1787247','SOLENOID VALVE BODY',''],
        ['DAIKIN','DAIKIN','1256116','TERMINAL STRIP VER. 1',''],
        ['DAIKIN','DAIKIN','1679137','TERMINAL STRIP VER. 2',''],
        ['DAIKIN','DAIKIN','2346269','CONTROL BOX COVER WELDING ASSY',''],
        ['DAIKIN','DAIKIN','1780309','BUSHING',''],
        ['DAIKIN','DAIKIN','2272856','HEXAGON HEAD BOLT',''],
        ['DAIKIN','DAIKIN','1938968','ROLLE',''],
        ['DAIKIN','DAIKIN','1136539','CLAMP',''],
        ['DAIKIN','DAIKIN','112894J','PACKING',''],
        ['DAIKIN','DAIKIN','1938944','CONTROL PANEL WITH SHEET KEY',''],
        ['DAIKIN','DAIKIN','0907062','SEAL WASHER',''],
        ['DAIKIN','DAIKIN','2272863','PAN HEAD MACHINE SCREW',''],
        ['DAIKIN','DAIKIN','INDND0078','ACEITE AFLOJATODO',''],
        ['DAIKIN','DAIKIN','IND411','ACEITE POE 68',''],
        ['DAIKIN','DAIKIN','INDND0405','ADHESIVO POLIURETANO 540 GRIS 600ML',''],
        ['DAIKIN','DAIKIN','INDND4837','SILICON SEALANT 590ML COLOR BLANCO',''],
        ['DAIKIN','DAIKIN','INDND4836','SILICON SEALANT 590ML COLOR GREY',''],
        ['DAIKIN','DAIKIN','INDND2543','BOMBA DE VACIO CPS VP6D 1/2HP',''],
        ['DAIKIN','DAIKIN','INDND0175','BROCA 1/4',''],
        ['DAIKIN','DAIKIN','INDND0176','BROCA 3/16',''],
        ['DAIKIN','DAIKIN','INDND0259','CABLE VULCANIZADO 4 X10',''],
        ['DAIKIN','DAIKIN','RNDND0254','CINTA AISLANTE 3M',''],
        ['DAIKIN','DAIKIN','INDND0432','CINTA FOAM 1/8 X 2 X 9.4 M',''],
        ['DAIKIN','DAIKIN','INDND1008','CINTILLO DE AMARRE 350MM',''],
        ['DAIKIN','DAIKIN','RNDND0198','ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67',''],
        ['DAIKIN','DAIKIN','30650','FUSIBLE 10A',''],
        ['DAIKIN','DAIKIN','INDND0120','GAS REFRIGERANTE R-134A DE 13.600KG',''],
        ['DAIKIN','DAIKIN','INDN0120','GAS R134A',''],
        ['DAIKIN','DAIKIN','RNDND0438','JABON LIQUIDO',''],
        ['DAIKIN','DAIKIN','INDND0054','LIJA FIERRO #120',''],
        ['DAIKIN','DAIKIN','INDND0079','LIMPIA CONTACTO',''],
        ['DAIKIN','DAIKIN','INDND3136','PERNO HEX. INOX. M6 X 24',''],
        ['DAIKIN','DAIKIN','INDND0107','PINTURA EN SPRAY NEGRO',''],
        ['DAIKIN','DAIKIN','INDND0170','REMACHE POP DE ALUMINIO 1/4X1',''],
        ['DAIKIN','DAIKIN','INDND0171','REMACHE POP DE ALUMINIO 3/16X1',''],
        ['DAIKIN','DAIKIN','INDND0260','RODAMIENTO 6203',''],
        ['DAIKIN','DAIKIN','INDND0081','RODAMIENTO 6205',''],
        ['DAIKIN','DAIKIN','INDND0121','SOLDADURA DE PLATA 0%',''],
        ['DAIKIN','DAIKIN','INDND2553','SOLDADURA DE PLATA 15%',''],
        ['DAIKIN','DAIKIN','INDND2901','SOLDADURA DE PLATA 5%',''],
        ['DAIKIN','DAIKIN','INDND0265','TERMINAL OJO 5.5-5 / 12-10',''],
        ['DAIKIN','DAIKIN','INDND0882','TERMINAL OJO 5.5-8 / 12-10',''],
        ['DAIKIN','DAIKIN','INDND0017','TRAPO INDUSTRIAL',''],
        ['DAIKIN','DAIKIN','INDND3344','TUBERIA CONDUIT FLEXIBLE C/F PVC 3/8','']
    ];
}

function zgroupAsegurarCatalogosV12(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS modelos_reefer_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        marca_equipo VARCHAR(100) NOT NULL,
        controlador VARCHAR(100) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_modelo_reefer (marca_equipo, controlador),
        INDEX idx_modelo_reefer_marca (marca_equipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // MP400 no es un controlador válido. Se mantiene desactivado aunque exista de versiones anteriores.
    try {
        $pdo->exec("UPDATE modelos_reefer_catalogo SET activo = 0 WHERE UPPER(TRIM(marca_equipo)) = 'THERMO KING' AND UPPER(REPLACE(TRIM(controlador), ' ', '')) = 'MP400'");
    } catch (Throwable $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS modelos_genset_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        marca_equipo VARCHAR(100) NOT NULL,
        controlador VARCHAR(100) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_modelo_genset (marca_equipo, controlador),
        INDEX idx_modelo_genset_marca (marca_equipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_reefer_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        marca_equipo VARCHAR(100) NOT NULL,
        controlador VARCHAR(100) NOT NULL,
        codigo VARCHAR(60) DEFAULT NULL,
        detalle VARCHAR(220) NOT NULL,
        unidad VARCHAR(40) DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_rep_reefer_modelo (marca_equipo, controlador),
        INDEX idx_rep_reefer_codigo (codigo),
        INDEX idx_rep_reefer_detalle (detalle)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS zgroup_config (clave VARCHAR(100) PRIMARY KEY, valor VARCHAR(220) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $seedStmt = $pdo->prepare("SELECT valor FROM zgroup_config WHERE clave = 'catalogos_v12_sembrados' LIMIT 1");
    $seedStmt->execute();
    $yaSembrado = (string)$seedStmt->fetchColumn() !== '';
    if (!$yaSembrado) {
        $modelosReefer = [
            ['THERMO KING','MP3000'],['THERMO KING','MP4000'],['THERMO KING','MP5000'],
            ['CARRIER','MICROLINK 2I'],['CARRIER','MICROLINK 3'],['CARRIER','MICROLINK 5'],
            ['STAR COOL','CIM5'],['STAR COOL','CIM6'],['DAIKIN','DAIKIN']
        ];
        $insR = $pdo->prepare("INSERT INTO modelos_reefer_catalogo (marca_equipo, controlador, activo) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE activo = VALUES(activo)");
        foreach ($modelosReefer as $m) $insR->execute($m);
        $modelosGenset = [['THERMO KING','SG-3000'],['THERMO KING','SG-5000']];
        $insG = $pdo->prepare("INSERT INTO modelos_genset_catalogo (marca_equipo, controlador, activo) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE activo = VALUES(activo)");
        foreach ($modelosGenset as $m) $insG->execute($m);
        $count = (int)$pdo->query("SELECT COUNT(*) FROM repuestos_reefer_catalogo")->fetchColumn();
        if ($count === 0) {
            $ins = $pdo->prepare('INSERT INTO repuestos_reefer_catalogo (marca_equipo, controlador, codigo, detalle, unidad, activo) VALUES (?, ?, ?, ?, ?, 1)');
            foreach (zgroupMaterialesReeferV12() as $r) $ins->execute([$r[0], $r[1], $r[2] !== '' ? $r[2] : null, $r[3], $r[4]]);
        }
        $pdo->prepare("INSERT INTO zgroup_config (clave,valor) VALUES ('catalogos_v12_sembrados',?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)")->execute([date('Y-m-d H:i:s')]);
    }
}

function asegurarCatalogoClientesCotizaciones(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS clientes_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(180) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_clientes_catalogo_nombre (nombre)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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

    foreach ([
        'modelo_equipo' => "VARCHAR(100) DEFAULT NULL",
        'anio_fabricacion' => "SMALLINT UNSIGNED DEFAULT NULL",
        'tamano_contenedor' => "VARCHAR(60) DEFAULT NULL",
        'modalidad_comercial' => "VARCHAR(40) DEFAULT NULL",
        'tipo_equipo' => "VARCHAR(30) DEFAULT NULL",
        'ticket_ref' => "VARCHAR(30) DEFAULT NULL",
        'cliente_nombre' => "VARCHAR(180) DEFAULT NULL",
        'origen' => "VARCHAR(30) DEFAULT NULL"
    ] as $zgCol => $zgDef) {
        try { $pdo->exec("ALTER TABLE contenedores_catalogo ADD COLUMN `$zgCol` $zgDef"); } catch (Throwable $e) {}
    }

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
    try { $pdo->exec("ALTER TABLE maquinas_catalogo ADD COLUMN modelo_equipo VARCHAR(100) DEFAULT NULL AFTER marca_equipo"); } catch (Throwable $e) {}
    try { $pdo->exec("ALTER TABLE maquinas_catalogo ADD COLUMN anio_fabricacion SMALLINT UNSIGNED DEFAULT NULL AFTER controlador"); } catch (Throwable $e) {}

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
        INDEX idx_odoo_servicio_reporte (numero_reporte)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");


    $pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(60) DEFAULT NULL,
        detalle VARCHAR(220) NOT NULL,
        unidad VARCHAR(40) DEFAULT NULL,
        pendiente_revision TINYINT(1) NOT NULL DEFAULT 0,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_repuestos_catalogo_codigo (codigo),
        INDEX idx_repuestos_catalogo_detalle (detalle)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    try { $pdo->exec("ALTER TABLE repuestos_catalogo MODIFY codigo VARCHAR(60) NULL DEFAULT NULL"); } catch (Throwable $e) {}
    try { $pdo->exec("ALTER TABLE repuestos_catalogo ADD COLUMN pendiente_revision TINYINT(1) NOT NULL DEFAULT 0"); } catch (Throwable $e) {}
    try { $pdo->exec("ALTER TABLE repuestos_catalogo DROP INDEX codigo"); } catch (Throwable $e) {}
    try { $pdo->exec("CREATE INDEX idx_repuestos_catalogo_codigo ON repuestos_catalogo (codigo)"); } catch (Throwable $e) {}
    zgroupAsegurarCatalogoGeneradores($pdo);
    zgroupAsegurarCatalogosV12($pdo);
}


$clientesCatalogo = [];
$cotizacionesCatalogo = [];
$contenedoresCatalogo = [];
$maquinasCatalogo = [];
$generadoresCatalogo = [];
$repuestosCatalogo = [];
$repuestosGensetCatalogo = [];
$modelosReeferCatalogo = [];
$modelosGensetCatalogo = [];
$repuestosReeferCatalogo = [];
$serviciosOdooCatalogo = [];
try {
    asegurarCatalogoClientesCotizaciones($pdo);
    $clientesCatalogo = $pdo->query("
        SELECT id, nombre,
               COALESCE(ruc,'') AS ruc,
               COALESCE(contacto,'') AS contacto,
               COALESCE(telefono,'') AS telefono,
               COALESCE(correo,'') AS correo,
               COALESCE(direccion,'') AS direccion,
               COALESCE(origen,'') AS origen
        FROM clientes_catalogo
        WHERE activo = 1
        ORDER BY nombre
    ")->fetchAll(PDO::FETCH_ASSOC);
    $cotizacionesCatalogo = $pdo->query("
        SELECT c.id, c.cotizacion, c.cliente_id,
               COALESCE(NULLIF(c.cliente_nombre,''), cl.nombre, '') AS cliente_nombre,
               COALESCE(c.descripcion, '') AS descripcion,
               COALESCE(c.ticket_ref,'') AS ticket_ref,
               COALESCE(c.cotizacion_odoo,'') AS cotizacion_odoo,
               COALESCE(c.origen,'') AS origen
        FROM cotizaciones_catalogo c
        LEFT JOIN clientes_catalogo cl ON cl.id = c.cliente_id
        WHERE c.activo = 1
        ORDER BY c.cotizacion DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $serviciosOdooCatalogo = $pdo->query("
        SELECT id, ticket_ref, COALESCE(numero_reporte,'') AS numero_reporte,
               COALESCE(cotizacion,'') AS cotizacion, cliente_id,
               COALESCE(cliente_nombre,'') AS cliente_nombre,
               COALESCE(ruc,'') AS ruc, COALESCE(contacto,'') AS contacto,
               COALESCE(telefono,'') AS telefono, COALESCE(correo,'') AS correo,
               COALESCE(direccion,'') AS direccion, COALESCE(fecha_servicio,'') AS fecha_servicio,
               COALESCE(modalidad_comercial,'') AS modalidad_comercial,
               COALESCE(tipo_instalacion,'') AS tipo_instalacion,
               COALESCE(tipo_equipo,'') AS tipo_equipo,
               COALESCE(tamano_contenedor,'') AS tamano_contenedor,
               COALESCE(numero_equipo,'') AS numero_equipo,
               COALESCE(serie_unidad,'') AS serie_unidad,
               COALESCE(marca_equipo,'') AS marca_equipo,
               COALESCE(modelo_equipo,'') AS modelo_equipo,
               COALESCE(controlador,'') AS controlador,
               COALESCE(anio_fabricacion,'') AS anio_fabricacion,
               COALESCE(refrigerante,'') AS refrigerante,
               COALESCE(titulo_ticket,'') AS titulo_ticket,
               actualizado_en
        FROM odoo_servicios_catalogo
        WHERE activo=1
        ORDER BY COALESCE(fecha_servicio,'1900-01-01') DESC, actualizado_en DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $contenedoresCatalogo = $pdo->query("
        SELECT id, numero,
               COALESCE(serial_unidad,'') AS serial_unidad, COALESCE(marca_equipo,'') AS marca_equipo,
               COALESCE(modelo_equipo,'') AS modelo_equipo, COALESCE(controlador,'') AS controlador,
               COALESCE(anio_fabricacion,'') AS anio_fabricacion, COALESCE(refrigerante,'') AS refrigerante,
               COALESCE(tamano_contenedor,'') AS tamano_contenedor, COALESCE(modalidad_comercial,'') AS modalidad_comercial,
               COALESCE(tipo_equipo,'') AS tipo_equipo, COALESCE(ticket_ref,'') AS ticket_ref,
               COALESCE(cliente_nombre,'') AS cliente_nombre, COALESCE(descripcion,'') AS descripcion
        FROM contenedores_catalogo
        WHERE activo = 1
          AND numero NOT IN (SELECT numero FROM generadores_catalogo WHERE activo = 1)
        ORDER BY creado_en DESC, numero ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $maquinasCatalogo = $pdo->query("
        SELECT id, serial_unidad,
               COALESCE(marca_equipo,'') AS marca_equipo,
               COALESCE(modelo_equipo,'') AS modelo_equipo,
               COALESCE(controlador,'') AS controlador,
               COALESCE(anio_fabricacion,'') AS anio_fabricacion,
               COALESCE(refrigerante,'') AS refrigerante,
               COALESCE(descripcion,'') AS descripcion
        FROM maquinas_catalogo
        WHERE activo = 1
          AND UPPER(COALESCE(marca_equipo,'')) <> 'GENSET'
          AND UPPER(COALESCE(controlador,'')) NOT IN ('SG-3000','SG-5000','ZG-3000','ZG-5000')
          AND serial_unidad NOT IN (SELECT COALESCE(serial_unidad,'') FROM generadores_catalogo WHERE activo = 1)
        ORDER BY creado_en DESC, serial_unidad ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $generadoresCatalogo = $pdo->query("
        SELECT id, numero, COALESCE(serial_unidad,'') AS serial_unidad,
               COALESCE(marca_equipo,'THERMO KING') AS marca_equipo,
               COALESCE(controlador,'') AS controlador, COALESCE(descripcion,'') AS descripcion
        FROM generadores_catalogo
        WHERE activo = 1
        ORDER BY creado_en DESC, numero ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $repuestosGensetCatalogo = $pdo->query("
        SELECT id, controlador, COALESCE(codigo,'') AS codigo, detalle, COALESCE(unidad,'und') AS unidad
        FROM repuestos_genset_catalogo
        WHERE activo = 1
        ORDER BY controlador, detalle
    ")->fetchAll(PDO::FETCH_ASSOC);
    $modelosReeferCatalogo = $pdo->query("SELECT id, marca_equipo, controlador FROM modelos_reefer_catalogo WHERE activo=1 AND NOT (UPPER(TRIM(marca_equipo))='THERMO KING' AND UPPER(REPLACE(TRIM(controlador),' ',''))='MP400') ORDER BY marca_equipo, controlador")->fetchAll(PDO::FETCH_ASSOC);
    $modelosGensetCatalogo = $pdo->query("SELECT id, marca_equipo, controlador FROM modelos_genset_catalogo WHERE activo=1 ORDER BY marca_equipo, controlador")->fetchAll(PDO::FETCH_ASSOC);
    $repuestosReeferCatalogo = $pdo->query("SELECT id, marca_equipo, controlador, COALESCE(codigo,'') AS codigo, detalle, COALESCE(unidad,'und') AS unidad FROM repuestos_reefer_catalogo WHERE activo=1 ORDER BY marca_equipo, controlador, detalle")->fetchAll(PDO::FETCH_ASSOC);
    // Los seriales reefer activos se cargan desde su catálogo independiente del panel.

    $repuestosCatalogo = $pdo->query("
        SELECT id, COALESCE(codigo,'') AS codigo, detalle, COALESCE(unidad,'') AS unidad, COALESCE(pendiente_revision,0) AS pendiente_revision
        FROM repuestos_catalogo
        WHERE activo = 1
        ORDER BY creado_en DESC, codigo ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $clientesCatalogo = [];
    $cotizacionesCatalogo = [];
    $contenedoresCatalogo = [];
    $maquinasCatalogo = [];
    $generadoresCatalogo = [];
    $repuestosCatalogo = [];
    $repuestosGensetCatalogo = [];
    $modelosReeferCatalogo = [];
    $modelosGensetCatalogo = [];
    $repuestosReeferCatalogo = [];
}


/* ============================================================
   Opciones técnicas personalizadas
   - Cuando un técnico escribe una actividad o hallazgo nuevo,
     se guarda por separado para reefer o generador.
   - En los siguientes servicios vuelve a aparecer en el buscador.
   ============================================================ */
function zgroupAsegurarOpcionesTecnicasPersonalizadas(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS opciones_tecnicas_personalizadas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_equipo VARCHAR(20) NOT NULL,
        categoria VARCHAR(30) NOT NULL,
        texto VARCHAR(220) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_por_tecnico_id INT DEFAULT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        actualizado_en DATETIME DEFAULT NULL,
        UNIQUE KEY uq_opcion_tecnica (tipo_equipo, categoria, texto),
        INDEX idx_opcion_tipo_categoria (tipo_equipo, categoria, activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

$opcionesTecnicasPersonalizadas = [
    'reefer' => ['actividades' => [], 'hallazgos' => []],
    'genset' => ['actividades' => [], 'hallazgos' => []],
];
try {
    zgroupAsegurarOpcionesTecnicasPersonalizadas($pdo);
    $stmtOpcionesTec = $pdo->query("SELECT tipo_equipo, categoria, texto
        FROM opciones_tecnicas_personalizadas
        WHERE activo = 1
        ORDER BY texto ASC");
    foreach ($stmtOpcionesTec->fetchAll(PDO::FETCH_ASSOC) as $opTec) {
        $tipoTec = strtolower(trim((string)($opTec['tipo_equipo'] ?? '')));
        $catTec = strtolower(trim((string)($opTec['categoria'] ?? '')));
        $txtTec = trim((string)($opTec['texto'] ?? ''));
        if (isset($opcionesTecnicasPersonalizadas[$tipoTec][$catTec]) && $txtTec !== '') {
            $opcionesTecnicasPersonalizadas[$tipoTec][$catTec][] = $txtTec;
        }
    }
} catch (Throwable $e) {
    // El formulario sigue funcionando aunque el hosting no permita crear la tabla en ese momento.
}

/* ============================================================
   Memoria técnica histórica
   - Recupera actividades y hallazgos guardados en informes y borradores.
   - Mantiene separados reefer y generador.
   - Así, lo escrito anteriormente vuelve a aparecer como opción en
     Asistencia técnica, Mantenimiento correctivo y Preventivo.
   ============================================================ */
function zgNormalizarOpcionTecnica(string $texto): string {
    $texto = trim(preg_replace('/\s+/u', ' ', strip_tags($texto)) ?? $texto);
    if (function_exists('mb_substr')) return trim(mb_substr($texto, 0, 220, 'UTF-8'));
    return trim(substr($texto, 0, 220));
}
function zgAgregarOpcionMemoria(array &$banco, string $tipo, string $categoria, string $texto): void {
    $tipo = strtolower(trim($tipo));
    $categoria = strtolower(trim($categoria));
    $texto = zgNormalizarOpcionTecnica($texto);
    if ($texto === '' || !isset($banco[$tipo][$categoria])) return;
    $clave = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
    foreach ($banco[$tipo][$categoria] as $existente) {
        $k = function_exists('mb_strtolower') ? mb_strtolower(trim((string)$existente), 'UTF-8') : strtolower(trim((string)$existente));
        if ($k === $clave) return;
    }
    $banco[$tipo][$categoria][] = $texto;
}
function zgTipoEquipoSnapshot(array $snapshot): string {
    $tipo = trim((string)($snapshot['fields']['zgTipoEquipo']['value'] ?? ''));
    $tipoNorm = strtolower($tipo);
    if (strpos($tipoNorm, 'genset') !== false || strpos($tipoNorm, 'generador') !== false) return 'genset';
    $seleccionados = $snapshot['state']['selected'] ?? [];
    if (is_array($seleccionados)) {
        foreach ($seleccionados as $trabajo) {
            $id = strtolower((string)($trabajo['id'] ?? ''));
            $nombre = strtolower((string)($trabajo['nombre'] ?? ''));
            if (strpos($id, 'genset_') === 0 || strpos($nombre, 'genset') !== false || strpos($nombre, 'generador') !== false) return 'genset';
        }
    }
    return 'reefer';
}
function zgCargarMemoriaDesdeSnapshot(array &$banco, array $snapshot): void {
    $tipo = zgTipoEquipoSnapshot($snapshot);
    $seleccionados = $snapshot['state']['selected'] ?? [];
    if (!is_array($seleccionados)) return;
    foreach ($seleccionados as $trabajo) {
        if (!is_array($trabajo)) continue;
        $auto = $trabajo['auto'] ?? [];
        if (!is_array($auto)) continue;
        foreach (['actividades', 'acciones'] as $campo) {
            $items = $auto[$campo] ?? [];
            if (!is_array($items)) continue;
            foreach ($items as $item) zgAgregarOpcionMemoria($banco, $tipo, 'actividades', (string)$item);
        }
        $hallazgos = $auto['hallazgos'] ?? [];
        if (is_array($hallazgos)) {
            foreach ($hallazgos as $item) zgAgregarOpcionMemoria($banco, $tipo, 'hallazgos', (string)$item);
        }
    }
}
try {
    // Últimos informes finales: conserva las opciones realmente usadas en campo.
    $rowsMemoria = $pdo->query("SELECT datos_json FROM informes WHERE datos_json IS NOT NULL AND datos_json <> '' ORDER BY id DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsMemoria as $rowMemoria) {
        $snapMemoria = json_decode((string)($rowMemoria['datos_json'] ?? ''), true);
        if (is_array($snapMemoria)) zgCargarMemoriaDesdeSnapshot($opcionesTecnicasPersonalizadas, $snapMemoria);
    }
} catch (Throwable $e) {}
try {
    // Borradores: también recuerda lo registrado aunque aún no se haya generado el PDF.
    $rowsBorradorMemoria = $pdo->query("SELECT datos_json FROM borradores_servicio WHERE datos_json IS NOT NULL AND datos_json <> '' ORDER BY actualizado_en DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsBorradorMemoria as $rowBorrador) {
        $snapBorrador = json_decode((string)($rowBorrador['datos_json'] ?? ''), true);
        if (is_array($snapBorrador)) zgCargarMemoriaDesdeSnapshot($opcionesTecnicasPersonalizadas, $snapBorrador);
    }
} catch (Throwable $e) {}


/* ============================================================
   Memoria técnica por trabajo (V53)
   - Guarda y recupera actividades/hallazgos según el trabajo exacto.
   - Ej.: mantenimiento preventivo, asistencia técnica, correctivo,
     instalación de reefer, etc. No mezcla trabajos distintos.
   ============================================================ */
$opcionesTecnicasPorTrabajo = [
    'reefer' => [],
    'genset' => [],
];

function zgClaveTrabajoTecnico(array $trabajo): string {
    $id = strtolower(trim((string)($trabajo['id'] ?? '')));
    if ($id !== '') {
        $id = preg_replace('/[^a-z0-9_]+/', '_', $id) ?? $id;
        return trim($id, '_') ?: 'trabajo_general';
    }
    $nombre = strtolower(trim((string)($trabajo['nombre'] ?? '')));
    if (function_exists('iconv')) {
        $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);
        if (is_string($tmp) && $tmp !== '') $nombre = $tmp;
    }
    $nombre = preg_replace('/[^a-z0-9]+/', '_', $nombre) ?? $nombre;
    return trim($nombre, '_') ?: 'trabajo_general';
}
function zgAgregarOpcionTrabajo(array &$banco, string $tipo, string $trabajoClave, string $categoria, string $texto): void {
    $tipo = strtolower(trim($tipo));
    $trabajoClave = strtolower(trim($trabajoClave));
    $categoria = strtolower(trim($categoria));
    $texto = zgNormalizarOpcionTecnica($texto);
    if ($texto === '' || !in_array($tipo, ['reefer','genset'], true) || !in_array($categoria, ['actividades','hallazgos'], true)) return;
    if ($trabajoClave === '') $trabajoClave = 'trabajo_general';
    if (!isset($banco[$tipo][$trabajoClave])) $banco[$tipo][$trabajoClave] = ['actividades'=>[], 'hallazgos'=>[]];
    $clave = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
    foreach ($banco[$tipo][$trabajoClave][$categoria] as $existente) {
        $k = function_exists('mb_strtolower') ? mb_strtolower(trim((string)$existente), 'UTF-8') : strtolower(trim((string)$existente));
        if ($k === $clave) return;
    }
    $banco[$tipo][$trabajoClave][$categoria][] = $texto;
}
function zgCargarMemoriaTrabajoDesdeSnapshot(array &$banco, array $snapshot): void {
    $tipo = zgTipoEquipoSnapshot($snapshot);
    $seleccionados = $snapshot['state']['selected'] ?? [];
    if (!is_array($seleccionados)) return;
    foreach ($seleccionados as $trabajo) {
        if (!is_array($trabajo)) continue;
        $trabajoClave = zgClaveTrabajoTecnico($trabajo);
        $auto = $trabajo['auto'] ?? [];
        if (!is_array($auto)) continue;
        foreach (['actividades','acciones'] as $campo) {
            $items = $auto[$campo] ?? [];
            if (!is_array($items)) continue;
            foreach ($items as $item) zgAgregarOpcionTrabajo($banco, $tipo, $trabajoClave, 'actividades', (string)$item);
        }
        $hallazgos = $auto['hallazgos'] ?? [];
        if (is_array($hallazgos)) {
            foreach ($hallazgos as $item) zgAgregarOpcionTrabajo($banco, $tipo, $trabajoClave, 'hallazgos', (string)$item);
        }
    }
}
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS opciones_tecnicas_por_trabajo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo_equipo VARCHAR(20) NOT NULL,
        trabajo_clave VARCHAR(100) NOT NULL,
        trabajo_nombre VARCHAR(180) DEFAULT NULL,
        categoria VARCHAR(30) NOT NULL,
        texto VARCHAR(220) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_por_tecnico_id INT DEFAULT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        actualizado_en DATETIME DEFAULT NULL,
        UNIQUE KEY uq_opcion_por_trabajo (tipo_equipo, trabajo_clave, categoria, texto),
        INDEX idx_opcion_trabajo (tipo_equipo, trabajo_clave, categoria, activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $rowsTrabajo = $pdo->query("SELECT tipo_equipo, trabajo_clave, categoria, texto
        FROM opciones_tecnicas_por_trabajo
        WHERE activo = 1
        ORDER BY actualizado_en DESC, texto ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsTrabajo as $rowTrabajo) {
        zgAgregarOpcionTrabajo(
            $opcionesTecnicasPorTrabajo,
            (string)($rowTrabajo['tipo_equipo'] ?? ''),
            (string)($rowTrabajo['trabajo_clave'] ?? ''),
            (string)($rowTrabajo['categoria'] ?? ''),
            (string)($rowTrabajo['texto'] ?? '')
        );
    }
} catch (Throwable $e) {}

try {
    $rowsMemoriaTrabajo = $pdo->query("SELECT datos_json FROM informes WHERE datos_json IS NOT NULL AND datos_json <> '' ORDER BY id DESC LIMIT 400")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsMemoriaTrabajo as $rowMemoriaTrabajo) {
        $snap = json_decode((string)($rowMemoriaTrabajo['datos_json'] ?? ''), true);
        if (is_array($snap)) zgCargarMemoriaTrabajoDesdeSnapshot($opcionesTecnicasPorTrabajo, $snap);
    }
} catch (Throwable $e) {}
try {
    $rowsBorradorTrabajo = $pdo->query("SELECT datos_json FROM borradores_servicio WHERE datos_json IS NOT NULL AND datos_json <> '' ORDER BY actualizado_en DESC LIMIT 400")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsBorradorTrabajo as $rowBorradorTrabajo) {
        $snap = json_decode((string)($rowBorradorTrabajo['datos_json'] ?? ''), true);
        if (is_array($snap)) zgCargarMemoriaTrabajoDesdeSnapshot($opcionesTecnicasPorTrabajo, $snap);
    }
} catch (Throwable $e) {}


/* ============================================================
   Salidas técnicas preparadas por supervisión
   - Permite mostrar al técnico los materiales asignados y el equipo de apoyo.
   - Se enlaza por N° de reporte / cotización.
   ============================================================ */
function asegurarTablasSalidasTecnicas(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS salidas_tecnicas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cotizacion VARCHAR(100) NOT NULL,
        cliente VARCHAR(180) DEFAULT NULL,
        equipo VARCHAR(100) DEFAULT NULL,
        tecnico_responsable_id INT DEFAULT NULL,
        tecnico_responsable_nombre VARCHAR(180) DEFAULT NULL,
        tecnicos_apoyo TEXT DEFAULT NULL,
        observacion TEXT DEFAULT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_salidas_cotizacion (cotizacion),
        INDEX idx_salidas_activo (activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS salidas_tecnicas_materiales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        salida_id INT NOT NULL,
        repuesto_id INT DEFAULT NULL,
        codigo VARCHAR(60) DEFAULT NULL,
        detalle VARCHAR(220) NOT NULL,
        cantidad VARCHAR(40) DEFAULT NULL,
        unidad VARCHAR(60) DEFAULT NULL,
        observacion VARCHAR(220) DEFAULT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_salida_material_salida (salida_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

$salidasSupervision = [];
try {
    asegurarTablasSalidasTecnicas($pdo);
    $stmtSal = $pdo->query("SELECT * FROM salidas_tecnicas WHERE activo = 1 ORDER BY creado_en DESC LIMIT 200");
    $salidasTmp = $stmtSal->fetchAll(PDO::FETCH_ASSOC);
    $stmtMat = $pdo->prepare("SELECT repuesto_id, COALESCE(codigo,'') AS codigo, detalle, COALESCE(cantidad,'') AS cantidad, COALESCE(unidad,'') AS unidad, COALESCE(observacion,'') AS observacion FROM salidas_tecnicas_materiales WHERE salida_id = ? ORDER BY id ASC");
    foreach ($salidasTmp as $sal) {
        $stmtMat->execute([(int)$sal['id']]);
        $mats = $stmtMat->fetchAll(PDO::FETCH_ASSOC);
        $apoyo = [];
        if (!empty($sal['tecnicos_apoyo'])) {
            $dec = json_decode((string)$sal['tecnicos_apoyo'], true);
            if (is_array($dec)) $apoyo = $dec;
        }
        $salidasSupervision[] = [
            'id' => (int)$sal['id'],
            'cotizacion' => (string)$sal['cotizacion'],
            'cliente' => (string)($sal['cliente'] ?? ''),
            'equipo' => (string)($sal['equipo'] ?? ''),
            'tecnico_responsable' => (string)($sal['tecnico_responsable_nombre'] ?? ''),
            'tecnicos_apoyo' => $apoyo,
            'observacion' => (string)($sal['observacion'] ?? ''),
            'materiales' => $mats,
            'creado_en' => (string)($sal['creado_en'] ?? '')
        ];
    }
} catch (Throwable $e) {
    $salidasSupervision = [];
}

$tecnicos = $pdo->query('SELECT id, nombre FROM tecnicos WHERE activo = 1 ORDER BY nombre')->fetchAll();


$informeEdicionPayload = null;
if ($informeEdicion) {
    $informeEdicionPayload = [
        'id' => (int)$informeEdicion['id'],
        'tecnico_id' => (int)($informeEdicion['tecnico_id'] ?? 0),
        'tecnico_nombre' => (string)($informeEdicion['tecnico_nombre'] ?? ''),
        'orden' => (string)($informeEdicion['orden'] ?? ''),
        'cliente' => (string)($informeEdicion['cliente'] ?? ''),
        'direccion' => (string)($informeEdicion['direccion'] ?? ''),
        'fecha' => (string)($informeEdicion['fecha'] ?? ''),
        'trabajos' => (string)($informeEdicion['trabajos'] ?? ''),
        'archivo' => (string)($informeEdicion['archivo'] ?? ''),
        'preinspeccion_id' => (int)($informeEdicion['preinspeccion_id'] ?? 0),
        'hora_inicio_servicio' => (string)($informeEdicion['hora_inicio_servicio'] ?? ''),
        'hora_fin_servicio' => (string)($informeEdicion['hora_fin_servicio'] ?? ''),
        'repuestos_manual' => (string)($informeEdicion['repuestos_manual'] ?? ''),
        'snapshot' => $informeEdicionSnapshot,
        'preinspeccion' => $preinspeccion,
        'error' => $informeEdicionError];
} elseif ($modo_editar_informe) {
    $informeEdicionPayload = ['id'=>0, 'error'=>$informeEdicionError ?: 'No se pudo cargar el informe.'];
}

$preinspeccionJson = json_encode($preinspeccion ?: null, JSON_UNESCAPED_UNICODE);
$preinspeccionErrorJson = json_encode($preinspeccionError, JSON_UNESCAPED_UNICODE);
$tokenContinuacionJson = json_encode($token_continuacion, JSON_UNESCAPED_UNICODE);
$borradorServicioJson = json_encode($borradorServicio, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Informe Técnico — Evidencias</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<style>
:root{
  --bg:#eef2f7;
  --surface:#ffffff;
  --ink:#16263f;
  --ink-soft:#5a6b80;
  --ink-faint:#97a3b3;
  --line:#dde4ec;
  --accent:#1f6fc4;
  --accent-soft:#e7f0fb;
  --accent-ink:#155293;
  --ok:#2f9e44;
  --danger:#e03131;
  --danger-soft:#ffeaea;
  --radius:14px;
  --shadow:0 1px 2px rgba(22,38,63,.05), 0 8px 24px rgba(22,38,63,.07);
}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
html,body{background:var(--bg);color:var(--ink);font-family:'Manrope',system-ui,sans-serif;line-height:1.5;-webkit-font-smoothing:antialiased}
body{padding-bottom:120px}

/* ---------- Header ---------- */
.hero{
  position:relative;
  overflow:hidden;
  text-align:center;
  color:#fff;
  min-height:430px;
  padding:70px 20px 98px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:
    linear-gradient(180deg,rgba(6,16,31,.56) 0%,rgba(8,22,41,.62) 52%,rgba(8,18,34,.92) 100%),
    radial-gradient(circle at 50% 10%,rgba(31,111,196,.34),transparent 44%),
    url('zgroup-tec.jpg') center 46%/cover no-repeat;
}
.hero::before{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background:
    linear-gradient(90deg,rgba(3,12,25,.78) 0%,rgba(3,12,25,.24) 28%,rgba(3,12,25,.24) 72%,rgba(3,12,25,.78) 100%),
    radial-gradient(circle at 50% 58%,transparent 0%,transparent 34%,rgba(0,0,0,.30) 100%);
}
.hero::after{
  content:"";
  position:absolute;
  left:0;
  right:0;
  bottom:-1px;
  height:115px;
  pointer-events:none;
  background:linear-gradient(0deg,var(--bg) 0%,rgba(238,242,247,.86) 34%,rgba(238,242,247,.22) 72%,transparent 100%);
}
.clock{position:absolute;top:16px;right:18px;z-index:3;display:flex;flex-direction:column;align-items:flex-end;color:#fff;line-height:1.1;background:rgba(8,20,38,.38);border:1px solid rgba(255,255,255,.28);padding:9px 14px;border-radius:13px;backdrop-filter:blur(10px);box-shadow:0 12px 28px rgba(0,0,0,.20)}
.clock .time{font-family:'Archivo',sans-serif;font-weight:800;font-size:19px;letter-spacing:.03em;font-variant-numeric:tabular-nums}
.clock .date{font-size:10px;color:#d9e7f7;font-weight:600;margin-top:2px}
.hero-inner{position:relative;z-index:2;max-width:900px;margin:0 auto;display:flex;flex-direction:column;align-items:center;width:100%}
.hero-copy{display:flex;flex-direction:column;align-items:center;min-width:0;width:100%}
.brand-plate{background:rgba(255,255,255,.96);border:1px solid rgba(255,255,255,.75);border-radius:20px;padding:16px 30px;box-shadow:0 20px 46px rgba(0,0,0,.34);display:inline-flex;margin-bottom:18px;backdrop-filter:blur(10px)}
.brand-plate img{height:58px;width:auto;display:block}
.kicker{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:900;letter-spacing:.18em;text-transform:uppercase;color:#eaf5ff;background:rgba(31,111,196,.34);border:1px solid rgba(157,207,255,.55);padding:7px 14px;border-radius:999px;backdrop-filter:blur(10px);margin-bottom:14px;box-shadow:0 9px 24px rgba(31,111,196,.18)}
.kicker .dot{width:8px;height:8px;border-radius:50%;background:#8fc0f2;box-shadow:0 0 0 5px rgba(143,192,242,.18)}
.hero h1{font-family:'Archivo',sans-serif;font-weight:800;font-size:clamp(36px,7vw,62px);line-height:.94;margin:0 0 12px;letter-spacing:-.04em;text-shadow:0 15px 32px rgba(0,0,0,.42)}
.hero-sub{max-width:710px;color:#f0f7ff;font-size:16px;font-weight:650;line-height:1.55;margin-bottom:20px;text-shadow:0 5px 16px rgba(0,0,0,.42)}
.hero-pills{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
.hero-pill{display:inline-flex;align-items:center;gap:7px;background:rgba(9,23,42,.42);border:1px solid rgba(255,255,255,.28);color:#fff;border-radius:999px;padding:9px 14px;font-weight:900;font-size:12px;backdrop-filter:blur(10px);box-shadow:0 12px 28px rgba(0,0,0,.18)}
.hero-panel{display:none}
.ops-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:13px;margin-bottom:18px;margin-top:-58px;position:relative;z-index:3}
.ops-strip.no-overlap{margin-top:0;margin-bottom:22px}
.ops-mini{background:linear-gradient(180deg,rgba(255,255,255,.97),#f7fafd);border:1px solid rgba(221,228,236,.92);border-radius:18px;box-shadow:0 14px 34px rgba(22,38,63,.12);padding:16px 16px;display:flex;gap:12px;align-items:center}
.ops-icon{flex:none;width:42px;height:42px;border-radius:15px;background:var(--accent-soft);display:grid;place-items:center;font-size:20px}
.ops-mini strong{display:block;font-family:'Archivo';font-size:14px;color:var(--ink);line-height:1.15}
.ops-mini span{font-size:11.8px;color:var(--ink-soft);font-weight:650}
@media(max-width:860px){
  .hero{min-height:420px;padding:76px 16px 92px;background-position:center center}
  .hero-sub{text-align:center;font-size:15px}
}
@media(max-width:620px){
  .clock{position:relative;top:auto;right:auto;margin:0 auto 18px;width:max-content;align-items:center}
  .hero{padding:22px 16px 86px;min-height:390px}
  .brand-plate{padding:12px 20px;border-radius:16px}
  .brand-plate img{height:44px}
  .hero h1{font-size:38px}
  .hero-pills{gap:8px}
  .hero-pill{font-size:11px;padding:8px 11px}
  .ops-strip{grid-template-columns:1fr;margin-top:-42px}
}

/* ---------- Layout ---------- */
.wrap{max-width:880px;margin:0 auto;padding:20px 16px 0}
.card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;margin-bottom:18px}
.card-head{display:flex;align-items:center;gap:12px;margin-bottom:18px}
.step{flex:none;width:30px;height:30px;border-radius:9px;background:var(--ink);color:#fff;font-family:'Archivo';font-weight:800;font-size:14px;display:grid;place-items:center}
.card-head h2{font-family:'Archivo',sans-serif;font-weight:700;font-size:18px;letter-spacing:-.01em}
.card-head .sub{font-size:12.5px;color:var(--ink-soft);font-weight:500;margin-top:1px}

/* ---------- Datos generales desplegable cuando ya existe preliminar ---------- */
.datos-generales-card{overflow:hidden;transition:box-shadow .18s,border-color .18s}
.datos-generales-card.datos-collapsed{padding-bottom:18px}
.datos-toggle{width:100%;border:0;background:transparent;padding:0;margin:0;display:flex;align-items:center;justify-content:space-between;gap:12px;text-align:left;cursor:pointer;color:inherit}
.datos-toggle .card-head{margin-bottom:0;flex:1}
.datos-toggle:hover h2{color:var(--accent-ink)}
.datos-side{display:flex;align-items:center;gap:10px;flex:none}
.datos-pill{font-family:'Archivo';font-size:12px;font-weight:800;color:var(--accent-ink);background:var(--accent-soft);border:1px solid #cfe0f4;border-radius:999px;padding:8px 12px;white-space:nowrap}
.datos-arrow{width:34px;height:34px;border-radius:12px;background:var(--ink);color:#fff;display:grid;place-items:center;font-family:'Archivo';font-weight:900;transition:transform .18s,background .18s}
.datos-generales-card:not(.datos-collapsed) .datos-arrow{transform:rotate(180deg);background:var(--accent)}
.datos-body{margin-top:18px;overflow:hidden;transition:max-height .22s ease,opacity .18s ease}
.datos-generales-card.datos-collapsed .datos-body{max-height:0!important;opacity:0;margin-top:0;pointer-events:none}
.datos-generales-card:not(.datos-collapsed) .datos-body{max-height:none;opacity:1}
@media(max-width:620px){.datos-toggle{align-items:flex-start}.datos-side{flex-direction:column;gap:7px;align-items:flex-end}.datos-pill{font-size:11px;padding:7px 10px}.datos-arrow{width:30px;height:30px;border-radius:10px}}

/* ---------- Forms ---------- */
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.field{display:flex;flex-direction:column;gap:6px}
.field.full{grid-column:1/-1}
label{font-size:12px;font-weight:700;color:var(--ink-soft);letter-spacing:.02em}
label .opt{color:var(--ink-faint);font-weight:500;text-transform:none;letter-spacing:0}
input[type=text],input[type=date],textarea,select{
  font-family:inherit;font-size:15px;color:var(--ink);
  background:#f7fafd;border:1.5px solid var(--line);border-radius:10px;
  padding:12px 13px;width:100%;transition:border-color .15s,box-shadow .15s;
}
input:focus,textarea:focus,select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);background:#fff}
textarea{resize:vertical;min-height:80px;line-height:1.5}
select{appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%235b6675' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:38px;cursor:pointer}
input[type=number]{font-family:inherit;font-size:15px;color:var(--ink);background:#f7fafd;border:1.5px solid var(--line);border-radius:10px;padding:12px 13px;width:100%;transition:border-color .15s,box-shadow .15s}
input[type=number]:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);background:#fff}

.input-error{border-color:var(--danger)!important;background:#fff7f7!important;box-shadow:0 0 0 3px var(--danger-soft)!important}
.field-error{display:none;font-size:12px;color:var(--danger);font-weight:700;margin-top:2px;line-height:1.35}
.field-error.show{display:block}

/* ---------- Buscador de técnico (autocompletar) ---------- */
.autocomplete{position:relative}
.ac-list{position:absolute;left:0;right:0;top:calc(100% + 5px);background:#fff;border:1.5px solid var(--line);border-radius:11px;box-shadow:var(--shadow);max-height:230px;overflow-y:auto;z-index:30;display:none}
.ac-list.show{display:block}
.ac-item{padding:11px 14px;font-size:14.5px;cursor:pointer;border-bottom:1px solid var(--line)}
.ac-item:last-child{border-bottom:none}
.ac-item:hover,.ac-item.active{background:var(--accent-soft);color:var(--accent-ink)}
.ac-empty{padding:11px 14px;font-size:13.5px;color:var(--ink-faint);font-style:italic}
.ac-input.ok{border-color:var(--ok);background:#f0faf3}
.zg-tech-autocomplete{z-index:45}
.zg-tech-source{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important;left:0!important;bottom:0!important;padding:0!important;border:0!important}
.zg-tech-list{z-index:80;max-height:270px;overscroll-behavior:contain}
.zg-tech-list .ac-item{display:block;width:100%;border:0;border-bottom:1px solid var(--line);background:#fff;text-align:left;font-family:inherit;color:var(--ink)}
.zg-tech-list .ac-item:last-child{border-bottom:0}
.zg-tech-list .ac-item:hover,.zg-tech-list .ac-item.active{background:var(--accent-soft);color:var(--accent-ink)}
@media(max-width:620px){.zg-tech-list{max-height:240px}.zg-tech-list .ac-item{padding:13px 14px}}

/* ---------- Campos por trabajo ---------- */
.campos{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.campos .field.full{grid-column:1/-1}
@media(max-width:520px){.campos{grid-template-columns:1fr}}

/* ---------- Campo de ubicación (abre mapa) ---------- */
.dir-pick{position:relative;cursor:pointer}
.dir-pick svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;stroke:var(--accent);fill:none;stroke-width:2;pointer-events:none}
.dir-pick input{padding-left:40px!important;cursor:pointer;background:#f7fafd}
.dir-pick:hover input{border-color:var(--accent)}

/* ---------- Modal de mapa ---------- */
.map-modal{position:fixed;inset:0;z-index:200;background:rgba(13,22,38,.55);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;padding:14px}
.map-modal.show{display:flex}
.map-box{background:#fff;border-radius:18px;box-shadow:0 24px 70px rgba(0,0,0,.45);width:100%;max-width:680px;height:min(86vh,720px);display:flex;flex-direction:column;overflow:hidden}
.map-head{display:flex;align-items:center;justify-content:space-between;padding:15px 18px;background:var(--ink);color:#fff}
.map-head .map-title{font-family:'Archivo';font-weight:700;font-size:15px}
.map-close{border:none;background:rgba(255,255,255,.12);color:#fff;width:30px;height:30px;border-radius:9px;font-size:20px;line-height:1;cursor:pointer}
.map-close:hover{background:var(--accent)}
.map-search{display:flex;gap:8px;padding:12px 14px;border-bottom:1px solid var(--line);position:relative}
.map-search input{flex:1}
.map-search button{font-family:'Archivo';font-weight:700;font-size:13.5px;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:0 16px;cursor:pointer}
.pick-map{flex:1;min-height:360px;background:#e9eef3}
.map-foot{padding:13px 16px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:11px}
.map-addr{font-size:13px;color:var(--ink-soft);line-height:1.4;min-height:18px}
.map-addr b{color:var(--ink)}
.map-foot .btn{width:100%}
.cur-dot{position:relative}
.cur-dot .d{position:absolute;left:50%;top:50%;width:15px;height:15px;margin:-7.5px 0 0 -7.5px;background:#1f6fc4;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 2px rgba(31,111,196,.4),0 2px 8px rgba(0,0,0,.35)}
.cur-dot .r{position:absolute;left:50%;top:50%;width:15px;height:15px;margin:-7.5px 0 0 -7.5px;border-radius:50%;background:rgba(31,111,196,.4);animation:pulse 2s ease-out infinite}
@keyframes pulse{0%{transform:scale(1);opacity:.6}100%{transform:scale(5);opacity:0}}
.sel-pin .p{width:24px;height:24px;background:var(--danger);border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 9px rgba(0,0,0,.45)}

/* ---------- Buscador de trabajos ---------- */
.work-search{width:100%;margin-bottom:14px}
.work-none{grid-column:1/-1;padding:16px;text-align:center;color:var(--ink-faint);font-size:13.5px;font-style:italic}

/* ---------- Sugerencias del buscador de mapa ---------- */
.map-sug{position:absolute;top:calc(100% - 3px);left:14px;right:14px;background:#fff;border:1.5px solid var(--line);border-radius:12px;box-shadow:0 14px 34px rgba(0,0,0,.2);max-height:320px;overflow-y:auto;z-index:1200;display:none}
.map-sug.show{display:block}
.sug-item{padding:11px 13px;font-size:13px;color:var(--ink);cursor:pointer;border-bottom:1px solid var(--line);line-height:1.35}
.sug-item:last-child{border-bottom:none}
.sug-item:hover{background:var(--accent-soft);color:var(--accent-ink)}
.sug-main{font-weight:800;color:var(--ink);font-size:13.5px}
.sug-sub{font-size:12px;color:var(--ink-soft);margin-top:2px}
.sug-tag{display:inline-flex;align-items:center;gap:4px;margin-top:5px;font-size:11px;color:var(--accent-ink);font-weight:700}
.sug-empty{padding:11px 13px;font-size:12.5px;color:var(--ink-faint);font-style:italic}
.map-route{width:100%;text-decoration:none}

/* ---------- Logo upload ---------- */
.logo-row{display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.logo-box{flex:none;width:96px;height:64px;border:1.5px dashed var(--line);border-radius:10px;display:grid;place-items:center;background:#fbfbf9;cursor:pointer;overflow:hidden;color:var(--ink-faint);font-size:11px;text-align:center;padding:6px}
.logo-box img{max-width:100%;max-height:100%;object-fit:contain}
.logo-box:hover{border-color:var(--accent);color:var(--accent)}

/* ---------- Work type cards ---------- */
.work-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:9px}
@media(min-width:560px){.work-grid{grid-template-columns:repeat(3,1fr)}}
@media(min-width:840px){.work-grid{grid-template-columns:repeat(4,1fr)}}
.work-card{
  position:relative;border:1.5px solid var(--line);border-radius:11px;background:#f7fafd;
  padding:11px 32px 11px 13px;cursor:pointer;transition:all .14s;text-align:left;font-family:inherit;
  display:flex;align-items:center;min-height:56px;
}
.work-card:hover{border-color:#bccddf;background:#fff;transform:translateY(-1px)}
.work-card .nm{font-weight:600;font-size:12.5px;color:var(--ink);line-height:1.22}
.work-card.on{border-color:var(--accent);background:var(--accent-soft);box-shadow:0 0 0 3px rgba(31,111,196,.12)}
.work-card .check{position:absolute;top:50%;right:9px;transform:translateY(-50%);width:19px;height:19px;border-radius:50%;border:1.8px solid var(--line);background:#fff;display:grid;place-items:center;transition:all .14s}
.work-card.on .check{background:var(--accent);border-color:var(--accent)}
.work-card .check svg{width:11px;height:11px;stroke:#fff;stroke-width:3;fill:none;opacity:0;transition:opacity .14s}
.work-card.on .check svg{opacity:1}
.add-work{border-style:dashed;color:var(--ink-soft);justify-content:center;text-align:center;font-weight:700;font-size:13px}
.add-work .ic{font-size:18px;color:var(--accent);margin-right:5px}

/* ---------- Panels (selected work) ---------- */
#panels{margin-top:18px;display:flex;flex-direction:column;gap:16px}
.panel{border:1.5px solid var(--line);border-radius:12px;overflow:hidden;background:#fff;animation:pop .2s ease}
@keyframes pop{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.panel-head{display:flex;align-items:center;gap:10px;padding:13px 15px;background:var(--accent-soft);border-bottom:1.5px solid #cfe1f7}
.panel-head .ic{font-size:18px}
.panel-head .ttl{font-family:'Archivo';font-weight:700;font-size:15.5px;color:var(--accent-ink);flex:1}
.panel-head .rm{flex:none;border:none;background:rgba(0,0,0,.06);color:var(--ink-soft);width:28px;height:28px;border-radius:8px;cursor:pointer;font-size:18px;line-height:1;display:grid;place-items:center}
.panel-head .rm:hover{background:var(--danger-soft);color:var(--danger)}
.panel-body{padding:15px}
.panel-body .field{margin-bottom:14px}

/* ---------- Dropzone + thumbs ---------- */
.drop{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;border:1.5px dashed var(--line);border-radius:11px;background:#f7fafd;padding:20px;text-align:center;cursor:pointer;transition:all .14s;color:var(--ink-soft)}
.drop:hover{border-color:var(--accent);background:var(--accent-soft);color:var(--accent-ink)}
.drop .big{font-weight:700;font-size:14px;color:var(--ink)}
.drop .ic{font-size:26px;margin-bottom:3px}
.drop small{font-size:11.5px;color:var(--ink-faint)}
.thumbs{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-top:14px}
.thumb{border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff;display:flex;flex-direction:column}
.thumb .ph{position:relative;aspect-ratio:4/3;background:#f0efe9}
.thumb .ph img{width:100%;height:100%;object-fit:cover;display:block}
.thumb .x{position:absolute;top:6px;right:6px;width:26px;height:26px;border-radius:50%;border:none;background:rgba(22,32,46,.72);color:#fff;cursor:pointer;font-size:16px;line-height:1;display:grid;place-items:center;backdrop-filter:blur(2px)}
.thumb .x:hover{background:var(--danger)}
.thumb input{border:none;border-top:1px solid var(--line);border-radius:0;background:#fff;font-size:12.5px;padding:9px 10px}
.thumb input:focus{box-shadow:none;border-top-color:var(--accent)}
.empty-hint{color:var(--ink-faint);font-size:13px;text-align:center;padding:8px 0}

/* ---------- Action bar ---------- */
.actionbar{position:fixed;left:0;right:0;bottom:0;background:rgba(255,255,255,.85);backdrop-filter:blur(12px);border-top:1px solid var(--line);padding:13px 16px;z-index:20}
.actionbar-inner{max-width:880px;margin:0 auto;display:flex;gap:11px;align-items:center}
.btn{font-family:'Archivo',sans-serif;font-weight:700;font-size:15px;border:none;border-radius:11px;padding:14px 18px;cursor:pointer;transition:transform .12s,filter .12s,opacity .12s;display:inline-flex;align-items:center;justify-content:center;gap:8px}
.btn:active{transform:scale(.98)}
.btn-primary{background:var(--accent);color:#fff;flex:1;box-shadow:0 6px 16px rgba(31,111,196,.32)}
.btn-primary:hover{filter:brightness(1.05)}
.btn-primary svg{width:18px;height:18px;stroke:#fff;stroke-width:2.2;fill:none}
.btn-ghost{background:#e6ebf2;color:var(--ink-soft)}
.btn-ghost:hover{background:#d9e0ea}
.count-pill{font-family:'Manrope';font-size:12px;font-weight:600;color:var(--ink-soft)}

/* ---------- Privacy note ---------- */
.privacy{max-width:880px;margin:6px auto 30px;padding:0 16px;text-align:center;color:var(--ink-faint);font-size:12px}
.privacy svg{width:13px;height:13px;vertical-align:-2px;stroke:currentColor;fill:none;stroke-width:2;margin-right:4px}

/* ---------- Overlay ---------- */
.overlay{position:fixed;inset:0;background:rgba(243,242,238,.82);backdrop-filter:blur(4px);display:none;place-items:center;z-index:50}
.overlay.show{display:grid}
.spinner{width:42px;height:42px;border:4px solid var(--accent-soft);border-top-color:var(--accent);border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 14px}
@keyframes spin{to{transform:rotate(360deg)}}
.overlay .msg{font-family:'Archivo';font-weight:700;color:var(--ink);text-align:center}

/* ---------- Toast ---------- */
.toast{position:fixed;bottom:96px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--ink);color:#fff;padding:12px 18px;border-radius:11px;font-size:13.5px;font-weight:600;opacity:0;pointer-events:none;transition:all .25s;z-index:60;box-shadow:0 10px 30px rgba(0,0,0,.25)}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

/* ---------- Confirmación de guardado ---------- */
.savedbox{display:none;margin-top:4px}
.savedbox.show{display:block;animation:pop .25s ease}
.saved-card{display:flex;align-items:center;gap:14px;background:#eaf7ee;border:1.5px solid #b6e3c4;border-radius:14px;padding:16px 18px;margin-bottom:12px}
.saved-ic{flex:none;width:38px;height:38px;border-radius:50%;background:var(--ok);color:#fff;display:grid;place-items:center;font-size:20px;font-weight:800}
.saved-txt{display:flex;flex-direction:column;gap:1px}
.saved-txt strong{font-family:'Archivo';font-size:16px;color:#1c6b34}
.saved-txt span{font-size:13px;color:#3c7a4f}
.saved-actions{display:flex;flex-wrap:wrap;gap:10px}
.saved-actions .btn{flex:1;min-width:150px;text-decoration:none}


/* =========================================================
   OPTIMIZACIÓN MÓVIL PARA TÉCNICOS EN CAMPO
   Pantallas pequeñas, uso con dedo, cámara y carga rápida.
   ========================================================= */
button,input,textarea,select{font-size:16px;touch-action:manipulation}
button,.work-card,.drop,.dir-pick,.btn{touch-action:manipulation}

@media(max-width:780px){
  body{padding-bottom:172px;background:#eef3f8;overflow-x:hidden}
  .hero{
    min-height:380px;
    padding:20px 14px 92px;
    align-items:flex-end;
    background:
      linear-gradient(180deg,rgba(5,15,30,.18) 0%,rgba(7,20,38,.58) 45%,rgba(7,17,32,.96) 100%),
      radial-gradient(circle at 50% 18%,rgba(31,111,196,.25),transparent 42%),
      url('zgroup-tec.jpg') center 38%/cover no-repeat;
  }
  .hero::before{
    background:linear-gradient(90deg,rgba(3,12,25,.40),rgba(3,12,25,.06),rgba(3,12,25,.40));
  }
  .hero::after{height:92px}
  .clock{
    position:absolute;top:12px;right:12px;margin:0;
    padding:7px 10px;border-radius:12px;align-items:flex-end;
    background:rgba(8,20,38,.48);
  }
  .clock .time{font-size:16px}
  .clock .date{font-size:9px}
  .hero-inner{max-width:100%;align-items:flex-start;text-align:left}
  .hero-copy{align-items:flex-start;text-align:left}
  .brand-plate{padding:10px 16px;border-radius:17px;margin-bottom:12px;box-shadow:0 16px 34px rgba(0,0,0,.34)}
  .brand-plate img{height:39px;max-width:220px}
  .kicker{font-size:10px;padding:6px 10px;margin-bottom:9px;letter-spacing:.13em}
  .hero h1{font-size:38px;line-height:.96;margin-bottom:9px;letter-spacing:-.035em;max-width:310px}
  .hero-sub{font-size:14px;line-height:1.42;margin-bottom:13px;max-width:330px;text-align:left;color:#f5f9ff}
  .hero-pills{
    width:100%;display:flex;flex-wrap:nowrap;justify-content:flex-start;gap:8px;
    overflow-x:auto;padding-bottom:4px;scroll-snap-type:x mandatory;
    -webkit-overflow-scrolling:touch;
  }
  .hero-pills::-webkit-scrollbar{display:none}
  .hero-pill{flex:0 0 auto;scroll-snap-align:start;font-size:11px;padding:8px 11px;background:rgba(9,23,42,.62)}

  .wrap{padding:0 12px 0;max-width:100%;}
  .ops-strip{
    display:flex;gap:10px;overflow-x:auto;margin-top:-58px;margin-bottom:14px;padding:0 1px 6px;
    scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;
  }
  .ops-strip::-webkit-scrollbar{display:none}
  .ops-mini{min-width:230px;scroll-snap-align:start;border-radius:17px;padding:13px 14px;box-shadow:0 12px 28px rgba(22,38,63,.13)}
  .ops-icon{width:38px;height:38px;border-radius:14px;font-size:18px}
  .ops-mini strong{font-size:13px}
  .ops-mini span{font-size:11.5px}

  .card{border-radius:18px;padding:16px 14px;margin-bottom:14px;box-shadow:0 1px 2px rgba(22,38,63,.04),0 8px 22px rgba(22,38,63,.08)}
  .card-head{gap:10px;margin-bottom:14px;align-items:flex-start}
  .step{width:31px;height:31px;border-radius:10px;font-size:13px;margin-top:1px}
  .card-head h2{font-size:17px}
  .card-head .sub{font-size:12px;line-height:1.35}

  .grid2{grid-template-columns:1fr;gap:12px}
  .field.full{grid-column:auto}
  label{font-size:12px}
  input[type=text],input[type=date],input[type=number],textarea,select{
    min-height:52px;border-radius:13px;padding:13px 14px;background:#f8fbff;
  }
  textarea{min-height:96px}
  .dir-pick input{padding-left:43px!important;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}
  .dir-pick svg{left:14px;width:19px;height:19px}
  .ac-list{max-height:48vh;border-radius:13px;z-index:80}
  .ac-item{padding:13px 14px;font-size:15px}

  .work-search{min-height:54px;border-radius:14px;margin-bottom:12px}
  .work-grid{grid-template-columns:1fr;gap:10px}
  .work-card{min-height:62px;border-radius:14px;padding:14px 42px 14px 14px;background:#fbfdff}
  .work-card .nm{font-size:13.5px;font-weight:800}
  .work-card .check{right:13px;width:22px;height:22px}
  .add-work{justify-content:flex-start;text-align:left;color:var(--accent-ink);background:var(--accent-soft)}

  #panels{gap:13px;margin-top:14px}
  .panel{border-radius:16px}
  .panel-head{padding:13px 14px}
  .panel-head .ttl{font-size:14.5px;line-height:1.2}
  .panel-head .rm{width:34px;height:34px;border-radius:10px;font-size:20px}
  .panel-body{padding:14px}
  .campos{grid-template-columns:1fr;gap:11px;margin-bottom:12px}
  .panel-body .field{margin-bottom:12px}

  .drop{min-height:124px;border-radius:15px;padding:18px 14px;background:linear-gradient(180deg,#f8fbff,#eef6ff)}
  .drop .ic{font-size:30px}
  .drop .big{font-size:15px}
  .drop small{font-size:12px;max-width:250px}
  .thumbs{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
  .thumb{border-radius:13px}
  .thumb input{font-size:13px;min-height:44px}

  .actionbar{padding:10px 12px calc(10px + env(safe-area-inset-bottom));background:rgba(255,255,255,.94);box-shadow:0 -10px 30px rgba(22,38,63,.10)}
  .actionbar-inner{display:grid;grid-template-columns:104px 1fr;gap:9px;max-width:100%}
  .count-pill{grid-column:1/-1;text-align:center;font-size:12px;background:#f1f5fa;border:1px solid var(--line);border-radius:999px;padding:6px 10px;color:var(--ink-soft)}
  .btn{min-height:52px;border-radius:14px;padding:13px 12px;font-size:14px}
  .btn-primary{box-shadow:0 8px 18px rgba(31,111,196,.30)}
  .btn-primary svg{width:17px;height:17px}
  #clearBtn{width:100%}
  #pdfBtn{width:100%}
  .toast{bottom:148px;width:calc(100% - 28px);text-align:center;border-radius:14px;font-size:13px;padding:12px 14px}

  .map-modal{padding:0;align-items:stretch;justify-content:stretch;background:rgba(13,22,38,.72)}
  .map-box{height:100dvh;max-height:none;max-width:none;border-radius:0;width:100%}
  .map-head{padding:13px 14px}
  .map-head .map-title{font-size:14px}
  .map-close{width:38px;height:38px;border-radius:12px;font-size:24px}
  .map-search{display:grid;grid-template-columns:1fr 96px;gap:8px;padding:10px 12px}
  .map-search input{min-width:0;min-height:50px;border-radius:13px}
  .map-search button{min-height:50px;border-radius:13px;padding:0 10px;font-size:13px}
  .map-sug{left:12px;right:12px;top:calc(100% - 2px);max-height:45vh;border-radius:14px}
  .sug-item{padding:12px 13px}
  .pick-map{min-height:46vh}
  .map-foot{padding:11px 12px calc(12px + env(safe-area-inset-bottom));gap:9px}
  .map-addr{font-size:12.5px;max-height:84px;overflow:auto}
  .map-foot .btn{width:100%}

  .saved-card{padding:14px;border-radius:16px;align-items:flex-start}
  .saved-actions{display:grid;grid-template-columns:1fr;gap:9px}
  .saved-actions .btn{min-width:0;width:100%}
}

@media(max-width:380px){
  .hero{min-height:365px;padding-left:12px;padding-right:12px}
  .brand-plate img{height:34px;max-width:190px}
  .hero h1{font-size:33px}
  .hero-sub{font-size:13px}
  .hero-pill{font-size:10.5px;padding:7px 10px}
  .card{padding:14px 12px}
  .thumbs{grid-template-columns:1fr}
  .actionbar-inner{grid-template-columns:92px 1fr}
  .btn{font-size:13.5px}
}


/* ---------- Asistente rápido para técnicos ---------- */
.quick-assistant{background:linear-gradient(180deg,#f7fbff,#eef6ff);border:1px solid #d6e7fb;border-radius:16px;padding:14px;margin:0 0 14px;box-shadow:0 8px 22px rgba(31,111,196,.06)}
.quick-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px}
.quick-head strong{font-family:'Archivo';font-size:14px;color:var(--ink)}
.quick-head small{display:block;color:var(--ink-soft);font-size:11.5px;font-weight:700;margin-top:2px}.quick-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end}.quick-reset{border:none;background:#fff;color:var(--accent-ink);font-weight:900;border-radius:999px;padding:7px 10px;box-shadow:0 1px 3px rgba(22,38,63,.10);cursor:pointer}.quick-reset:hover{filter:brightness(1.04);transform:translateY(-1px)}
.quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.quick-group{background:#fff;border:1px solid #e1eaf4;border-radius:14px;padding:11px}.quick-group.full{grid-column:1/-1}.quick-title{font-size:11.5px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:#5d6d82;margin-bottom:8px}.chip-row{display:flex;flex-wrap:wrap;gap:7px}.qchip{border:1px solid #cfdae8;background:#fff;color:#243852;border-radius:999px;padding:7px 10px;font-size:12px;font-weight:800;cursor:pointer;line-height:1.15;transition:.15s}.qchip:hover{border-color:var(--accent);background:#f1f7ff}.qchip.on{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 7px 16px rgba(31,111,196,.20)}.qchip.bad.on{background:#e03131;border-color:#e03131}.qchip.warn.on{background:#f59f00;border-color:#f59f00;color:#10213a}.qchip.ok.on{background:#2f9e44;border-color:#2f9e44}.template-row{display:flex;gap:8px;overflow:auto;padding-bottom:3px;margin-bottom:10px}.template-btn{white-space:nowrap;border:1px solid #cfe0f3;background:#fff;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:900;color:var(--accent-ink);cursor:pointer}.template-btn:hover,.template-btn.on{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 7px 16px rgba(31,111,196,.20)}.auto-summary{display:none!important}.mini-equipo{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;background:#f7fafd;border:1px solid var(--line);border-radius:14px;padding:12px;margin-top:4px}.mini-equipo .field input,.mini-equipo .field select{padding:10px 11px;font-size:13.5px}.pti-box{margin-top:10px;background:#fff;border:1px solid #e1eaf4;border-radius:14px;padding:11px}.pti-tools{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:9px}.pti-tools button{border:none;border-radius:999px;padding:7px 10px;font-weight:900;font-size:11.5px;cursor:pointer;background:#edf4fb;color:#155293}.pti-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px}.pti-item{display:grid;grid-template-columns:1fr auto;gap:7px;align-items:center;border:1px solid #e4ebf3;border-radius:10px;padding:8px;background:#fbfdff}.pti-item span{font-size:11.5px;font-weight:750;color:#31455f}.pti-state{display:flex;gap:4px}.pti-state button{border:1px solid #cdd8e5;background:#fff;border-radius:8px;padding:5px 6px;font-size:10.5px;font-weight:900;cursor:pointer;color:#52657d}.pti-state button.on.ok{background:#2f9e44;color:#fff;border-color:#2f9e44}.pti-state button.on.warn{background:#f59f00;color:#10213a;border-color:#f59f00}.pti-state button.on.na{background:#7b8794;color:#fff;border-color:#7b8794}
@media(max-width:720px){.quick-grid{grid-template-columns:1fr}.mini-equipo{grid-template-columns:1fr 1fr}.pti-grid{grid-template-columns:1fr}}
@media(max-width:460px){.mini-equipo{grid-template-columns:1fr}.quick-head{flex-direction:column}.quick-reset{align-self:flex-start}}


/* ---------- Inspección preliminar profesional ---------- */
.mini-equipo.preinspeccion{grid-template-columns:repeat(4,1fr)}
.mini-equipo .field.full{grid-column:1/-1}
.mini-equipo .field textarea{padding:10px 11px;font-size:13.5px;min-height:76px}
.preinspeccion-actions{grid-column:1/-1;display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:2px}
.btn-pre{border:none;background:#1f6fc4;color:#fff;border-radius:12px;padding:11px 14px;font-weight:900;cursor:pointer;box-shadow:0 7px 18px rgba(31,111,196,.22)}
.btn-pre:hover{filter:brightness(1.05)}
.pre-status{font-size:12px;color:#5a6b80;font-weight:800}
@media(max-width:720px){.mini-equipo.preinspeccion{grid-template-columns:1fr 1fr}.preinspeccion-actions{flex-direction:column;align-items:stretch}.btn-pre{width:100%}}
@media(max-width:460px){.mini-equipo.preinspeccion{grid-template-columns:1fr}}


.preloaded-card{border:1.5px solid #cfe1f7!important;background:linear-gradient(180deg,#ffffff,#f8fbff)!important;box-shadow:0 18px 42px rgba(22,38,63,.10)}
.preloaded-card .card-head{align-items:flex-start}
.preloaded-card .step{background:#10213a;color:#fff;border-radius:13px}
.service-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px 18px;font-size:13.5px;color:#16263f;margin-top:12px}
.service-summary p{padding:8px 10px;background:#fff;border:1px solid #e1e8f0;border-radius:12px;min-height:38px}
.service-summary p.full{grid-column:1/-1}
.service-summary b{color:#0f2340}
@media(max-width:720px){.service-summary{grid-template-columns:1fr}.service-summary p.full{grid-column:auto}}



/* ---------- Repuestos a considerar ---------- */
.repuestos-card{margin-top:18px;border:1.5px solid #d7e5f5;background:linear-gradient(180deg,#ffffff,#f8fbff);border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(22,38,63,.06)}
.repuestos-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}
.repuestos-head h3{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:19px;margin:0 0 4px;letter-spacing:-.01em}
.repuestos-head p{margin:0;color:#66758a;font-size:13.5px;font-weight:700;line-height:1.35}
.repuestos-card textarea.tall{min-height:110px}
.repuesto-question-card{margin-top:18px;border:1.5px solid #d7e5f5;background:linear-gradient(180deg,#ffffff,#f7fbff);border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(22,38,63,.06)}
.repuesto-question-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}.repuesto-question-head h3{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:19px;margin:0 0 4px;letter-spacing:-.01em}.repuesto-question-head p{margin:0;color:#66758a;font-size:13.5px;font-weight:700;line-height:1.35}
.repuesto-choice{display:flex;flex-wrap:wrap;gap:10px}.repuesto-choice button{border:1.5px solid #cfddeb;background:#fff;color:#203854;border-radius:999px;padding:10px 14px;font-family:'Archivo',system-ui,sans-serif;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 5px 14px rgba(22,38,63,.06)}.repuesto-choice button.on{background:#2f9e44;color:#fff;border-color:#2f9e44;box-shadow:0 10px 22px rgba(47,158,68,.20)}.repuesto-choice button[data-repuesto="no"].on{background:#edf4fb;color:#17385d;border-color:#bcd4ef;box-shadow:none}
.repuestos-card.is-hidden{display:none}.repuesto-tools{display:grid;grid-template-columns:1fr;gap:10px;margin:10px 0 12px}.repuesto-helper{color:#66758a;font-size:12.5px;font-weight:750;line-height:1.35}.repuesto-helper.ok{color:#155293}.repuesto-helper.warn{color:#8a5a00}.repuesto-add-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:10px}.repuesto-add-btn{border:none;background:#1f6fc4;color:#fff;border-radius:14px;padding:11px 14px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer;box-shadow:0 9px 20px rgba(31,111,196,.18)}.repuesto-add-btn:hover{filter:brightness(.97);transform:translateY(-1px)}.repuesto-selected-list{display:grid;gap:9px;margin:12px 0 4px}.repuesto-selected-item{display:grid;grid-template-columns:1fr 84px 34px;gap:8px;align-items:center;border:1px solid #d7e5f5;background:#fff;border-radius:16px;padding:10px 10px;box-shadow:0 5px 14px rgba(22,38,63,.05)}.repuesto-selected-title{font-weight:900;color:#10213a;line-height:1.25}.repuesto-selected-name{width:100%;border:1.5px solid #d7e5f5;border-radius:12px;padding:9px 10px;font:inherit;font-weight:850;color:#10213a;background:#fff}.repuesto-selected-name:focus{outline:none;border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}.repuesto-selected-sub{font-size:11.5px;color:#66758a;font-weight:800;margin-top:3px}.repuesto-qty{width:100%;border:1.5px solid #d7e5f5;border-radius:12px;padding:9px 8px;font:inherit;font-weight:900;text-align:center}.repuesto-remove{border:none;background:#fff1f1;color:#b91c1c;border-radius:12px;width:34px;height:34px;font-weight:900;cursor:pointer}.repuesto-empty{display:none;border:1px dashed #c7d9ee;background:#f8fbff;border-radius:16px;padding:12px;color:#66758a;font-weight:800}.repuesto-empty.show{display:block}.is-technical-store{display:none!important}.repuesto-error{display:none;color:#c92a2a;font-size:12px;font-weight:850;margin-top:7px}.repuesto-error.show{display:block}@media(max-width:640px){.repuesto-selected-item{grid-template-columns:1fr 74px 34px}}



/* ---------- Firmas de conformidad ---------- */
.firmas-card{margin-top:18px;border:1.5px solid #d7e5f5;background:linear-gradient(180deg,#ffffff,#f8fbff);border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(22,38,63,.06)}
.firmas-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}.firmas-head h3{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:19px;margin:0 0 4px;letter-spacing:-.01em}.firmas-head p{margin:0;color:#66758a;font-size:13.5px;font-weight:700;line-height:1.35}
.firmas-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.firmas-grid .field.full{grid-column:1/-1}.firma-box{background:#fff;border:1px solid #d7e5f5;border-radius:16px;padding:12px;box-shadow:0 5px 14px rgba(22,38,63,.05)}.firma-title{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:8px}.firma-title b{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:14px}.firma-title span{font-size:11.5px;color:#66758a;font-weight:800;text-align:right}.firma-canvas{width:100%;height:150px;background:#fff;border:1.5px dashed #bcd4ef;border-radius:14px;touch-action:none;display:block}.firma-actions{display:flex;justify-content:flex-end;margin-top:8px}.firma-clear{border:1px solid #d7e5f5;background:#edf4fb;color:#17385d;border-radius:999px;padding:8px 11px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}.firma-clear:hover{filter:brightness(.98);transform:translateY(-1px)}@media(max-width:720px){.firmas-grid{grid-template-columns:1fr}.firma-canvas{height:130px}}

/* ---------- Informe manual profesional (estilos heredados no visibles) ---------- */
.manual-report-card{margin-top:18px;border:1.5px solid #cfe1f7;background:linear-gradient(180deg,#ffffff,#f8fbff);border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(22,38,63,.06)}
.manual-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:14px}
.manual-head h3{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:19px;margin:0 0 4px;letter-spacing:-.01em}
.manual-head p{margin:0;color:#66758a;font-size:13.5px;font-weight:700;line-height:1.35}
.manual-badge{display:inline-flex;align-items:center;gap:7px;background:#e7f0fb;color:#155293;border:1px solid #cfe1f7;border-radius:999px;padding:8px 11px;font-family:'Archivo',system-ui,sans-serif;font-size:12px;font-weight:900;white-space:nowrap}
.manual-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.manual-grid .field.full{grid-column:1/-1}
.manual-grid .field.half{grid-column:span 2}
.param-scroll{overflow-x:auto;border:1px solid #d9e4f1;border-radius:16px;background:#fff}
.param-table{width:100%;min-width:720px;border-collapse:collapse;font-size:13px}
.param-table th{background:#18345c;color:#fff;font-family:'Archivo',system-ui,sans-serif;text-transform:uppercase;letter-spacing:.04em;font-size:11px;padding:9px 8px;text-align:center;border-right:1px solid rgba(255,255,255,.18)}
.param-table th:first-child{text-align:left;width:190px}
.param-table td{border-top:1px solid #d9e4f1;border-right:1px solid #edf2f8;padding:7px;background:#fff}
.param-table td:first-child{font-weight:900;color:#10213a;background:#f7fafd}
.param-table input{width:100%;border:1px solid #d9e4f1;border-radius:10px;padding:9px 10px;background:#f9fbfe;font:inherit;color:#10213a;text-align:center}
.param-table input:focus{outline:none;border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb;background:#fff}
.manual-help{font-size:12.5px;color:#6b7c91;font-weight:700;margin-top:7px}
.manual-report-card textarea{min-height:92px}
.manual-report-card textarea.tall{min-height:128px}
@media(max-width:860px){.manual-grid{grid-template-columns:1fr}.manual-grid .field.half{grid-column:1/-1}.manual-head{flex-direction:column}.manual-badge{white-space:normal}}


/* ---------- Autocompletado cotización / cliente ---------- */
.smart-ac{position:relative}
.smart-menu{position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:70;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 16px 38px rgba(22,38,63,.16);padding:6px;display:none;max-height:260px;overflow:auto}
.smart-menu.show{display:block}
.smart-option{width:100%;border:none;background:#fff;border-radius:11px;padding:11px 12px;text-align:left;cursor:pointer;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;font-family:inherit;color:var(--ink)}
.smart-option:hover,.smart-option.active{background:var(--accent-soft)}
.smart-main{font-family:'Archivo';font-weight:900;color:var(--ink);font-size:14px;line-height:1.15}
.smart-sub{display:block;color:var(--ink-soft);font-size:12.5px;font-weight:750;margin-top:2px;line-height:1.25}
.smart-badge{background:#eef4fb;color:var(--accent);border:1px solid #cfe1f7;border-radius:999px;padding:4px 8px;font-size:11px;font-weight:900;white-space:nowrap}
.field-hint{margin-top:6px;font-size:11.5px;color:var(--ink-faint);font-weight:750;line-height:1.25}
.field-hint.ok{color:#2f9e44}
.field-hint.warn{color:#b77900}

</style>
</head>
<body>

<style>
.client-menu-home-float{position:fixed;top:18px;left:18px;z-index:120;display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.32);background:rgba(16,33,58,.86);color:#fff;border-radius:999px;padding:10px 14px;font-family:'Archivo',system-ui,sans-serif;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 14px 32px rgba(10,21,38,.22);backdrop-filter:blur(12px);transition:.15s}
.client-menu-home-float:hover{background:rgba(31,111,196,.94);transform:translateY(-1px)}
.client-leave-modal{position:fixed;inset:0;z-index:250;display:none;align-items:center;justify-content:center;background:rgba(10,21,38,.58);padding:18px;backdrop-filter:blur(7px)}
.client-leave-modal.show{display:flex}.client-leave-box{width:min(520px,100%);background:#fff;border-radius:24px;border:1px solid #dbe6f3;box-shadow:0 24px 70px rgba(10,21,38,.32);overflow:hidden}.client-leave-head{display:flex;align-items:center;gap:12px;padding:18px 20px;background:linear-gradient(135deg,#f8fbff,#eef5ff);border-bottom:1px solid #dbe6f3}.client-leave-ico{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:#fff3cd;color:#8a5a00;font-size:22px;border:1px solid #ffe29a}.client-leave-head h3{font-family:'Archivo',system-ui,sans-serif;margin:0;color:#10213a;font-size:19px;letter-spacing:-.02em}.client-leave-body{padding:18px 20px;color:#5d6d83;font-weight:700;line-height:1.45}.client-leave-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px;flex-wrap:wrap}.client-leave-btn{border:none;border-radius:14px;padding:12px 15px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}.client-leave-keep{background:#eaf1f8;color:#10213a}.client-leave-exit{background:#1f6fc4;color:#fff;box-shadow:0 10px 22px rgba(31,111,196,.22)}.client-leave-keep:hover,.client-leave-exit:hover{filter:brightness(.97);transform:translateY(-1px)}
@media(max-width:640px){.client-menu-home-float{top:10px;left:10px;padding:9px 11px;font-size:12px}.client-leave-actions{flex-direction:column-reverse}.client-leave-btn{width:100%}}
</style>
<button type="button" class="client-menu-home-float" id="btnClientMenuPrincipal" title="Volver al menú principal">← Menú principal</button>
<div class="client-leave-modal" id="clientLeaveMenuModal" aria-hidden="true"><div class="client-leave-box" role="dialog" aria-modal="true" aria-labelledby="clientLeaveTitle"><div class="client-leave-head"><div class="client-leave-ico">⚠️</div><div><h3 id="clientLeaveTitle">¿Volver al menú principal?</h3></div></div><div class="client-leave-body">Hay datos llenados en Trabajo en cliente. Si vuelves al menú principal sin guardar, podrías perder la información ingresada.</div><div class="client-leave-actions"><button type="button" class="client-leave-btn client-leave-keep" id="btnClientStayHere">Mantenerme aquí</button><button type="button" class="client-leave-btn client-leave-exit" id="btnClientExitMenu">Volver al menú principal</button></div></div></div>
<script>
(function(){
  /*
    Advertencia de salida corregida:
    - NO revisa si los campos tienen valor inicial, porque Fecha, datos cargados
      por token o catálogos pueden venir llenos automáticamente.
    - Solo avisa si el técnico realmente modifica algo después de cargar la página.
  */
  let zgroupTieneCambios = false;
  let zgroupIgnorarSalida = false;
  let zgroupPantallaLista = false;

  const btnMenu = document.getElementById('btnClientMenuPrincipal');
  const modal = document.getElementById('clientLeaveMenuModal');
  const btnStay = document.getElementById('btnClientStayHere');
  const btnExit = document.getElementById('btnClientExitMenu');

  function esElementoControl(el){
    return el && el.matches && el.matches('input, textarea, select, .qchip, .template-btn, .work-card, .photo-remove, .work-remove');
  }

  function ignorarClick(el){
    return el && el.closest && (
      el.closest('.client-leave-modal') ||
      el.closest('.client-menu-home-float') ||
      el.closest('.smart-menu')
    );
  }

  function marcarCambio(e){
    if (!zgroupPantallaLista) return;
    const el = e && e.target;
    if (!el || ignorarClick(el)) return;
    if (esElementoControl(el)) zgroupTieneCambios = true;
  }

  /* Activamos la vigilancia recién cuando la página terminó de cargar.
     Así no cuenta como cambio lo que el sistema rellena al inicio. */
  window.addEventListener('load', function(){
    setTimeout(function(){ zgroupPantallaLista = true; }, 350);
  });

  document.addEventListener('input', marcarCambio, true);
  document.addEventListener('change', marcarCambio, true);
  document.addEventListener('click', marcarCambio, true);

  function abrirModal(){
    if(modal){
      modal.classList.add('show');
      modal.setAttribute('aria-hidden','false');
    }
  }

  function cerrarModal(){
    if(modal){
      modal.classList.remove('show');
      modal.setAttribute('aria-hidden','true');
    }
  }

  if(btnMenu) btnMenu.addEventListener('click', function(e){
    e.preventDefault();
    if(zgroupTieneCambios) abrirModal();
    else window.location.href = 'index.php';
  });

  if(btnStay) btnStay.addEventListener('click', cerrarModal);
  if(modal) modal.addEventListener('click', function(e){ if(e.target === modal) cerrarModal(); });
  if(btnExit) btnExit.addEventListener('click', function(){
    zgroupIgnorarSalida = true;
    window.location.href = 'index.php';
  });

  window.addEventListener('beforeunload', function(e){
    if(zgroupTieneCambios && !zgroupIgnorarSalida){
      e.preventDefault();
      e.returnValue = '';
    }
  });

  window.zgroupMarcarGuardado = function(){
    zgroupTieneCambios = false;
    zgroupIgnorarSalida = true;
  };
})();
</script>


<header class="hero">
  <div class="clock"><span class="time" id="clockTime">--:--:--</span><span class="date" id="clockDate"></span></div>
  <div class="hero-inner">
    <div class="hero-copy">
      <div class="brand-plate"><img id="brandLogo" src="zgroup-logo.png" alt="ZGROUP"></div>
      <span class="kicker"><span class="dot"></span> Área técnica en campo</span>
      <h1>Informe Técnico</h1>
      <p class="hero-sub">Registra N° de reporte, cliente, ubicación exacta, trabajos realizados y evidencias fotográficas del servicio técnico.</p>
      <div class="hero-pills">
        <span class="hero-pill">🧰 Diagnóstico</span>
        <span class="hero-pill"><?= $preEsGenset ? '⚡ Generador' : '❄️ Reefer' ?></span>
        <span class="hero-pill">⚡ Sistema eléctrico</span>
        <span class="hero-pill">📍 Ubicación GPS</span>
      </div>
    </div>
  </div>
</header>

<main class="wrap">

  <?php if ($preinspeccion): ?>
  <section class="card preloaded-card <?= $preEsGenset ? 'preloaded-genset' : 'preloaded-reefer' ?>">
    <div class="card-head">
      <div class="step"><?= $preEsGenset ? '⚡' : '✓' ?></div>
      <div>
        <h2><?= $preEsGenset ? 'Servicio de generador iniciado con inspección preliminar' : 'Servicio reefer iniciado con inspección preliminar' ?></h2>
        <div class="sub">
          Los datos iniciales ya fueron registrados. Ahora completa solo el cierre del trabajo y genera el informe final.
        </div>
      </div>
    </div>

    <div class="service-summary">
      <p><b>Técnico:</b> <?= e($preinspeccion['tecnico_nombre'] ?? '') ?></p>
      <p><b>Estado:</b> <?= e(strtoupper($preinspeccion['estado'] ?? 'abierto')) ?></p>
      <p><b>Cliente:</b> <?= e($preinspeccion['cliente'] ?? '') ?></p>
      <p><b>Reporte:</b> <?= e($preinspeccion['cotizacion'] ?? '') ?></p>
      <p><b>Tipo de equipo:</b> <?= $preEsGenset ? 'Generador (genset)' : 'Contenedor / máquina reefer' ?></p>
      <p><b>Trabajo previsto:</b> <?= e($preinspeccion['trabajo'] ?? '') ?></p>
      <p><b><?= $preEsGenset ? 'Generador' : 'Contenedor / equipo' ?>:</b> <?= e($preinspeccion['numero_equipo'] ?? '') ?></p>
      <p><b>Serial:</b> <?= e($preinspeccion['serie_unidad'] ?? '') ?></p>
      <p><b>Marca / controlador:</b> <?= e($preinspeccion['marca_equipo'] ?? '') ?> / <?= e($preinspeccion['controlador'] ?? '') ?></p>
      <?php if (!$preEsGenset): ?>
        <p><b>Modelo:</b> <?= e($preinspeccion['modelo_equipo'] ?? '-') ?></p>
        <p><b>Año de fabricación:</b> <?= e($preinspeccion['anio_fabricacion'] ?? '-') ?></p>
      <?php endif; ?>
      <p><b>Estado inicial:</b> <?= e($preinspeccion['estado_inicial'] ?? '') ?></p>
      <p><b>Fecha preliminar:</b> <?= e($preinspeccion['creado_en'] ?? '') ?></p>

      <?php if ($preEsGenset): ?>
        <p><b>Horómetro inicial:</b> <?= e($preinspeccion['genset_horometro_inicial'] ?? '-') ?> h</p>
        <p><b>Voltaje de batería:</b> <?= e($preinspeccion['genset_voltaje_bateria_inicial'] ?? '-') ?></p>
        <p><b>Nivel de combustible:</b> <?= e($preinspeccion['genset_nivel_combustible_inicial'] ?? '-') ?></p>
        <p><b>Nivel de aceite:</b> <?= e($preinspeccion['genset_nivel_aceite_inicial'] ?? '-') ?></p>
        <p><b>Refrigerante del motor:</b> <?= e($preinspeccion['genset_refrigerante_motor_inicial'] ?? '-') ?></p>
        <p><b>Prueba de arranque:</b> <?= e($preinspeccion['genset_arranque_inicial'] ?? '-') ?></p>
        <p class="full"><b>Voltajes:</b>
          L1-L2: <?= e($preinspeccion['voltaje_l1_l2'] ?? '-') ?> |
          L2-L3: <?= e($preinspeccion['voltaje_l2_l3'] ?? '-') ?> |
          L1-L3: <?= e($preinspeccion['voltaje_l1_l3'] ?? '-') ?>
        </p>
      <?php else: ?>
        <p><b>Tamaño del contenedor:</b> <?= e($preinspeccion['tamano_contenedor'] ?? '-') ?></p>
        <p><b>Refrigerante:</b> <?= e($preinspeccion['refrigerante'] ?? '-') ?></p>
        <p class="full"><b>Temperaturas:</b>
          Amb: <?= e($preinspeccion['temperatura_ambiente'] ?? '-') ?> °C |
          Ret: <?= e($preinspeccion['retorno_aire'] ?? '-') ?> °C |
          Sum: <?= e($preinspeccion['suministro_aire'] ?? '-') ?> °C |
          Set: <?= e($preinspeccion['set_point'] ?? '-') ?> °C
        </p>
        <p class="full"><b>Presiones:</b>
          Alta: <?= e($preinspeccion['presion_alta'] ?? '-') ?> |
          Baja: <?= e($preinspeccion['presion_baja'] ?? '-') ?>
        </p>
      <?php endif; ?>

      <?php if (trim((string)($preinspeccion['alarma_encontrada'] ?? '')) !== ''): ?>
        <p class="full"><b>Alarma encontrada:</b> <?= e($preinspeccion['alarma_encontrada'] ?? '') ?></p>
      <?php endif; ?>
      <p class="full"><b>Observación inicial:</b> <?= e(trim((string)preg_replace('/\s*\[\[ZG_META:[A-Za-z0-9+\/= _-]+\]\]\s*/x', ' ', (string)($preinspeccion['observacion_inicial'] ?? '')))) ?></p>
    </div>
  </section>
  <?php elseif ($preinspeccionError !== ''): ?>
  <section class="card" style="border:2px solid #ffd6d6;background:#fff7f7;">
    <div class="card-head">
      <div class="step">!</div>
      <div>
        <h2>No se pudo cargar el servicio iniciado</h2>
        <div class="sub"><?= e($preinspeccionError) ?></div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="ops-strip <?= $preinspeccion ? 'no-overlap' : '' ?>" aria-label="Resumen visual del área técnica">
    <div class="ops-mini"><div class="ops-icon"><?= $preEsGenset ? '⚡' : '🧊' ?></div><div><strong><?= $preEsGenset ? 'Generadores' : 'Equipos reefer' ?></strong><span><?= $preEsGenset ? 'Mantenimiento preventivo y correctivo' : 'Inspección, instalación y reparación' ?></span></div></div>
    <div class="ops-mini"><div class="ops-icon">📷</div><div><strong>Evidencias claras</strong><span>Fotos ordenadas por trabajo</span></div></div>
    <div class="ops-mini"><div class="ops-icon">📄</div><div><strong>PDF automático</strong><span>Informe listo para supervisión</span></div></div>
  </section>

  <!-- 1. Datos generales -->
  <section class="card datos-generales-card <?= $preinspeccion ? 'datos-collapsed' : '' ?>" id="datosGeneralesCard">
    <button type="button" class="datos-toggle" id="datosGeneralesToggle" aria-expanded="<?= $preinspeccion ? 'false' : 'true' ?>">
      <div class="card-head datos-head">
        <div class="step">1</div>
        <div><h2>Datos generales</h2><div class="sub"><?= $preinspeccion ? 'Presiona para ver los datos usados en la preliminar.' : 'Información del servicio' ?></div></div>
      </div>
      <div class="datos-side">
        <span class="datos-pill" id="datosGeneralesPill"><?= $preinspeccion ? 'Ver datos' : 'Ocultar datos' ?></span>
        <span class="datos-arrow">⌄</span>
      </div>
    </button>
    <div class="datos-body" id="datosGeneralesBody">
    <div class="grid2">
      <div class="field"><label for="orden">N° de reporte</label>
        <div class="smart-ac">
          <input type="text" id="orden" placeholder="Escribe el inicio. Ej. 12" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
          <div class="smart-menu" id="ordenSuggest"></div>
        </div>
        <div class="field-hint" id="ordenHint">Escribe los primeros números y elige el reporte correcto.</div>
        <div class="field-error" id="ordenError"></div>
      </div>
      <div class="field"><label for="odooTicketRefDisplay">Ticket Odoo</label>
        <input type="text" id="odooTicketRefDisplay" value="<?= e($preinspeccion['odoo_ticket_ref'] ?? ($informeEdicion['odoo_ticket_ref'] ?? '')) ?>" placeholder="Automático" readonly>
        <input type="hidden" id="odooTicketRef" value="<?= e($preinspeccion['odoo_ticket_ref'] ?? ($informeEdicion['odoo_ticket_ref'] ?? '')) ?>">
        <div class="field-error" id="odooTicketRefError"></div>
      </div>
      <input type="hidden" id="odooCotizacionDisplay" value="">
      <input type="hidden" id="odooCotizacion" value="">
      <div class="field"><label for="fecha">Fecha</label><input type="date" id="fecha"></div>
      <div class="field"><label for="cliente">Cliente</label>
        <input type="text" id="cliente" placeholder="Se completa al elegir el reporte" readonly>
        <div class="field-error" id="clienteError"></div>
      </div>
      <div class="field"><label for="tecnicoSearch">Técnico</label>
        <div class="autocomplete zg-tech-autocomplete">
          <input type="text" id="tecnicoSearch" class="ac-input" placeholder="Selecciona técnico registrado" autocomplete="off" spellcheck="false">
          <div class="ac-list zg-tech-list" id="tecnicoSuggest" role="listbox"></div>
          <select id="tecnicoInput" class="zg-tech-source" tabindex="-1" aria-hidden="true">
            <option value="">Selecciona técnico registrado</option>
            <?php foreach ($tecnicos as $tec): ?>
              <option value="<?= e($tec['id'] ?? $tec[0] ?? '') ?>"><?= e($tec['nombre'] ?? $tec[1] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" id="tecnicoId" value="">
          <div class="field-error" id="tecnicoInputError"></div>
        </div>
      </div>
      <div class="field full"><label for="direccion">Dirección / ubicación</label>
        <div class="dir-pick" id="dirPick" title="Elegir en el mapa">
          <svg viewBox="0 0 24 24"><path d="M12 21s-7-6.3-7-11a7 7 0 0 1 14 0c0 4.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
          <input type="text" id="direccion" placeholder="Toca para elegir en el mapa…" readonly>
        </div>
        <input type="hidden" id="direccionCoords" value="">
        <input type="hidden" id="direccionOrigenOdoo" value="">
        <div class="field-error" id="direccionError"></div>
      </div>

      <div class="field full"><label>Inspección preliminar del equipo <span class="opt">(antes de realizar el trabajo técnico)</span></label>

        <div class="zg-service-config" id="zgServiceConfig">
          <div class="zg-service-config-head">
            <div>
              <span class="zg-config-kicker">Configuración del servicio</span>
              <h3>Tipo de equipo y configuración del servicio</h3>
              <p>Selecciona primero si se atenderá una máquina reefer o un generador.</p>
            </div>
          </div>
          <div class="zg-service-config-grid zg-type-first-grid">
            <div class="field zg-type-first-field">
              <label for="zgTipoEquipo">¿Qué equipo se atenderá?</label>
              <select id="zgTipoEquipo">
                <option value="">Seleccionar</option>
                <option value="Reefer">Contenedor / máquina reefer</option>
                <option value="Genset">Generador (genset)</option>
              </select>
              <div class="field-hint">Selecciona primero el tipo de equipo. Luego aparecerán únicamente los datos que correspondan.</div>
              <div class="field-error" id="zgTipoEquipoError"></div>
            </div>
          </div>

          <div class="zg-equipment-config is-hidden" id="zgCommonServiceConfig">
            <div class="zg-service-config-grid zg-common-config-grid">
              <div class="field">
                <label for="zgModalidadComercial">Modalidad comercial</label>
                <select id="zgModalidadComercial">
                  <option value="">Seleccionar</option>
                  <option value="Alquiler">Alquiler</option>
                  <option value="Venta">Venta</option>
                </select>
                <div class="field-error" id="zgModalidadComercialError"></div>
              </div>
            </div>
          </div>

          <div class="zg-equipment-config is-hidden" id="zgReeferConfig">
            <div class="zg-equipment-config-head">
              <b>❄️ Configuración de la máquina reefer</b>
              <span>Estos campos se muestran únicamente para contenedores o máquinas reefer.</span>
            </div>
            <div class="zg-service-config-grid zg-reefer-config-grid">
              <div class="field">
                <label for="zgTipoInstalacion">Tipo de instalación</label>
                <select id="zgTipoInstalacion">
                  <option value="">Seleccionar</option>
                  <option value="Unidad individual">Unidad individual</option>
                  <option value="Túnel">Túnel</option>
                  <option value="Atmósfera controlada">Atmósfera controlada</option>
                  <option value="Madurador">Madurador</option>
                </select>
                <div class="field-error" id="zgTipoInstalacionError"></div>
              </div>
              <div class="field" id="zgTamanoContenedorWrap">
                <label for="zgTamanoContenedor">Tamaño del contenedor</label>
                <select id="zgTamanoContenedor">
                  <option value="">Seleccionar</option>
                  <option value="10 pies">10 pies</option>
                  <option value="20 pies">20 pies</option>
                  <option value="40 pies">40 pies</option>
                  <option value="45 pies">45 pies</option>
                </select>
                <div class="field-error" id="zgTamanoContenedorError"></div>
              </div>
            </div>
          </div>

          <div class="zg-tunnel-config is-hidden" id="zgTunnelConfig">
            <div class="zg-tunnel-head">
              <div>
                <span class="zg-config-kicker">Túnel de cinco máquinas</span>
                <h4>Registra la marca, el controlador y el número de serie de cada máquina</h4>
              </div>
              <div class="field zg-target-machine">
                <label for="zgMaquinaPreliminarObjetivo">Máquina de referencia en la preliminar</label>
                <select id="zgMaquinaPreliminarObjetivo">
                  <option value="">Seleccionar</option>
                  <option value="M1">Máquina 1</option>
                  <option value="M2">Máquina 2</option>
                  <option value="M3">Máquina 3</option>
                  <option value="M4">Máquina 4</option>
                  <option value="M5">Máquina 5</option>
                </select>
                <div class="field-error" id="zgMaquinaPreliminarObjetivoError"></div>
              </div>
            </div>
            <div class="zg-machines-grid" id="zgMachinesGrid"></div>
            <div class="zg-tunnel-note">La máquina elegida como referencia completará automáticamente los campos de marca, controlador y serie de la inspección preliminar. En cada trabajo realizado podrás indicar cuál máquina fue atendida.</div>
          </div>
        </div>

        <div class="mini-equipo preinspeccion is-hidden" id="zgEquipmentDetails">
          <div class="field"><label for="equipoNo">Contenedor / equipo</label>
            <div class="smart-ac">
              <input type="text" id="equipoNo" placeholder="Escribe o selecciona contenedor. Ej. ZGRU01220-7" autocomplete="off">
              <div class="smart-menu" id="contenedorSuggest"></div>
            </div>
            <div class="field-hint" id="contenedorHint"></div>
            <div class="field-error" id="equipoNoError"></div>
          </div>
          <div class="field"><label for="serialUnidad">Serial unidad</label>
            <div class="smart-ac">
              <input type="text" id="serialUnidad" placeholder="Escribe o selecciona serial. Ej. E0G4005148" autocomplete="off">
              <div class="smart-menu" id="maquinaSuggest"></div>
            </div>
            <div class="field-hint" id="maquinaHint"></div>
            <div class="field-error" id="serialUnidadError"></div>
          </div>
          <div class="field"><label for="marcaEquipo">Marca del equipo</label><select id="marcaEquipo"><option value="">—</option><option>THERMO KING</option><option>CARRIER</option><option>STAR COOL</option><option>DAIKIN</option><option>OTRO</option></select></div>
          <div class="field zg-reefer-pre-field"><label for="modeloEquipo">Modelo</label><input type="text" id="modeloEquipo" list="modeloEquipoOpciones" placeholder="Selecciona o escribe el modelo" autocomplete="off"><datalist id="modeloEquipoOpciones"></datalist><div class="field-error" id="modeloEquipoError"></div></div>
          <div class="field"><label for="controladorEquipo">Controlador</label><input type="text" id="controladorEquipo" list="controladorOpciones" placeholder="Selecciona o escribe controlador" autocomplete="off"><datalist id="controladorOpciones"></datalist><div class="field-hint" id="controladorHint">Las opciones cambian según la marca seleccionada.</div><div class="field-error" id="controladorEquipoError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="anioFabricacion">Año de fabricación</label><input type="number" id="anioFabricacion" min="1980" max="2100" step="1" inputmode="numeric" placeholder="Ej. 2022"><div class="field-error" id="anioFabricacionError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="refrigerante">Refrigerante</label><select id="refrigerante"><option value="">—</option><option>R404A</option><option>R134a</option><option>R513A</option><option>R452A</option><option>No aplica</option><option>Otro</option></select></div>
          <div class="field zg-reefer-pre-field"><label for="setPoint">Set point °C</label><input type="text" id="setPoint" placeholder="Ej. -18" inputmode="decimal" step="0.1"><div class="field-error" id="setPointError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="temperaturaAmbiente">Temp. ambiente °C</label><input type="text" id="temperaturaAmbiente" placeholder="Ej. 24" inputmode="decimal" step="0.1"><div class="field-error" id="temperaturaAmbienteError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="retornoAire">Retorno de aire °C</label><input type="text" id="retornoAire" placeholder="Ej. 5" inputmode="decimal" step="0.1"><div class="field-error" id="retornoAireError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="suministroAire">Suministro de aire °C</label><input type="text" id="suministroAire" placeholder="Ej. -2" inputmode="decimal" step="0.1"><div class="field-error" id="suministroAireError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="presionAlta">Presión alta</label><input type="text" id="presionAlta" placeholder="Ej. 250 PSI" inputmode="decimal" autocomplete="off"><div class="field-error" id="presionAltaError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="presionBaja">Presión baja</label><input type="text" id="presionBaja" placeholder="Ej. 18 PSI" inputmode="decimal" autocomplete="off"><div class="field-error" id="presionBajaError"></div></div>
          <div class="field"><label for="voltajeL1L2">Voltaje L1-L2</label><input type="text" id="voltajeL1L2" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeL1L2Error"></div></div>
          <div class="field"><label for="voltajeL2L3">Voltaje L2-L3</label><input type="text" id="voltajeL2L3" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeL2L3Error"></div></div>
          <div class="field"><label for="voltajeL1L3">Voltaje L1-L3</label><input type="text" id="voltajeL1L3" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeL1L3Error"></div></div>
          <div class="field full zg-genset-pre-card is-hidden" id="zgGensetPreCard">
            <div class="zg-genset-card-head">
              <div><b>⚡ Parámetros iniciales del generador</b><span>Registra cómo se encontró el generador antes de intervenirlo.</span></div>
            </div>
            <div class="zg-genset-grid">
              <div class="field"><label for="gensetHorometroInicial">Horómetro inicial (h)</label><input type="number" id="gensetHorometroInicial" min="0" step="0.1" inputmode="decimal" placeholder="Ej. 1250.5"><div class="field-error" id="gensetHorometroInicialError"></div></div>
              <div class="field"><label for="gensetVoltajeBateriaInicial">Voltaje de batería inicial</label><input type="text" id="gensetVoltajeBateriaInicial" inputmode="decimal" placeholder="Ej. 12.6 V"><div class="field-error" id="gensetVoltajeBateriaInicialError"></div></div>
              <div class="field"><label for="gensetNivelCombustibleInicial">Nivel de combustible</label><select id="gensetNivelCombustibleInicial"><option value="">Seleccionar</option><option>Lleno</option><option>3/4</option><option>1/2</option><option>1/4</option><option>Reserva</option><option>Vacío</option></select><div class="field-error" id="gensetNivelCombustibleInicialError"></div></div>
              <div class="field"><label for="gensetNivelAceiteInicial">Nivel de aceite</label><select id="gensetNivelAceiteInicial"><option value="">Seleccionar</option><option>Correcto</option><option>Bajo</option><option>Sobre el nivel</option><option>No se pudo verificar</option></select><div class="field-error" id="gensetNivelAceiteInicialError"></div></div>
              <div class="field"><label for="gensetRefrigeranteMotorInicial">Refrigerante del motor</label><select id="gensetRefrigeranteMotorInicial"><option value="">Seleccionar</option><option>Nivel correcto</option><option>Nivel bajo</option><option>Con fuga visible</option><option>No se pudo verificar</option></select><div class="field-error" id="gensetRefrigeranteMotorInicialError"></div></div>
              <div class="field"><label for="gensetArranqueInicial">Prueba de arranque inicial</label><select id="gensetArranqueInicial"><option value="">Seleccionar</option><option>Arranca normalmente</option><option>Arranque dificultoso</option><option>No arranca</option><option>No se realizó por seguridad</option></select><div class="field-error" id="gensetArranqueInicialError"></div></div>
            </div>
          </div>

          <div class="field full estado-inicial-pro">
            <label>Estado inicial del equipo</label>
            <input type="hidden" id="estadoInicial" value="">
            <div class="estado-grid">
              <div class="estado-box">
                <span class="estado-label">Funcionamiento</span>
                <select id="estadoEncendido"><option value="">Seleccionar</option><option value="Encendido">Encendido</option><option value="Apagado">Apagado</option></select>
              </div>
              <div class="estado-box">
                <span class="estado-label">Suministro eléctrico</span>
                <select id="estadoEnergia"><option value="">Seleccionar</option><option value="Con suministro eléctrico">Con suministro eléctrico</option><option value="Sin suministro eléctrico">Sin suministro eléctrico</option></select>
              </div>
              <div class="estado-box">
                <span class="estado-label">Alarma</span>
                <select id="estadoAlarma"><option value="">Seleccionar</option><option value="Con alarma">Con alarma</option><option value="Sin alarma">Sin alarma</option></select>
              </div>
            </div>
            <div class="zg-alarm-found is-hidden" id="zgAlarmaEncontradaWrap" hidden>
              <label for="alarmaEncontrada">Código o número de alarma</label>
              <input type="text" id="alarmaEncontrada" placeholder="Escribe exactamente lo que aparece en la pantalla. Ej. AL15" autocomplete="off">
              <div class="field-error" id="alarmaEncontradaError"></div>
            </div>
            <div class="field-hint">Selecciona las 3 condiciones del equipo antes de guardar la inspección preliminar.</div>
          </div>
          <div class="field full"><label for="observacionInicial">Observación inicial</label><textarea id="observacionInicial" placeholder="Describe cómo se encontró el equipo antes de intervenirlo."></textarea><div class="field-error" id="observacionInicialError"></div></div>

          <div class="field full">
            <div class="pre-evidence-card" id="preEvidenceCard">
              <div class="pre-evidence-head">
                <div>
                  <h3>📸 Evidencias antes de la intervención</h3>
                  <p>Sube fotos que respalden el estado inicial descrito por el técnico. Estas evidencias aparecerán en el informe final.</p>
                </div>
                <span class="pre-evidence-count" id="preEvidenceCount">0 foto(s)</span>
              </div>
              <div class="pre-evidence-source-actions">
                <button type="button" class="pre-evidence-drop" id="preEvidenceCameraBtn">
                  <span class="pre-evidence-ic">📷</span>
                  <span><b>Tomar foto</b><small>Abre directamente la cámara posterior</small></span>
                </button>
                <button type="button" class="pre-evidence-drop" id="preEvidenceGalleryBtn">
                  <span class="pre-evidence-ic">🖼️</span>
                  <span><b>Elegir de la galería</b><small>Selecciona una o varias imágenes guardadas</small></span>
                </button>
              </div>
              <input type="file" id="preEvidenceCameraInput" accept="image/*" capture="environment" hidden>
              <input type="file" id="preEvidenceInput" accept="image/*" multiple hidden>
              <div class="pre-evidence-grid" id="preEvidenceGrid">
                <div class="pre-evidence-empty">Aún no hay evidencias preliminares.</div>
              </div>
            </div>
          </div>

          <input type="hidden" id="preinspeccionId" value="<?= e($preinspeccion['id'] ?? '') ?>">
          <input type="hidden" id="tokenContinuacion" value="<?= e($token_continuacion) ?>">
          <div class="preinspeccion-actions">
            <button type="button" class="btn-pre" id="preBtn">Guardar inspección preliminar</button>
            <span class="pre-status" id="preStatus">Pendiente de preguardado</span>
          </div>
        </div>
      </div>
      <input type="hidden" id="obs" value="">
    </div>
    </div>
  </section>

  <!-- 2. Trabajos realizados -->
  <section class="card" id="trabajosServicioCard">
    <div class="card-head">
      <div class="step">2</div>
      <div><h2>Trabajos realizados</h2><div class="sub">Selecciona los que apliquen. Cada uno abre su propia sección.</div></div>
    </div>
    <input type="text" id="workSearch" class="work-search" placeholder="Buscar trabajo…  (ej. instalación, reparación)" autocomplete="off">
    <div class="work-grid" id="workGrid"></div>
    <div id="panels"></div>

    <div class="repuesto-question-card" id="repuestoQuestionCard">
      <div class="repuesto-question-head">
        <div>
          <h3>🧩 ¿Se necesita repuesto?</h3>
          <p>Marca “Sí” únicamente cuando el servicio queda pendiente por material o cuando se debe solicitar un componente.</p>
        </div>
      </div>
      <input type="hidden" id="requiereRepuesto" value="no">
      <div class="repuesto-choice" role="group" aria-label="¿Se necesita repuesto?">
        <button type="button" data-repuesto="no" id="repuestoNoBtn" class="on">No requiere repuesto</button>
        <button type="button" data-repuesto="si" id="repuestoSiBtn">Sí, requiere repuesto</button>
      </div>
    </div>

    <div class="repuestos-card is-hidden" id="repuestosCard">
      <div class="repuestos-head">
        <div>
          <h3>🧩 Repuestos a considerar</h3>
          <p>Escribe el material. Si ya está en la lista, selecciónalo; si es nuevo, se guardará para que el supervisor lo complete en el panel.</p>
        </div>
      </div>
      <div class="repuesto-tools">
        <div class="field full">
          <label for="repuestoSearch">Material o repuesto</label>
          <div class="smart-ac repuesto-smart">
            <input type="text" id="repuestoSearch" placeholder="Ej. contactor, filter dryer, relay, sensor" autocomplete="off">
            <div class="smart-menu" id="repuestoSuggest"></div>
          </div>
          <div class="repuesto-add-row">
            <button type="button" class="repuesto-add-btn" id="repuestoAddManual">Agregar escrito</button>
            <div class="repuesto-helper" id="repuestoHint">Selecciona “usar” o agrega escrito. Luego ajusta la cantidad en la tabla.</div>
          </div>
        </div>
      </div>
      <div class="field full">
        <label>Materiales seleccionados</label>
        <div class="repuesto-empty show" id="repuestosEmpty">Aún no agregaste repuestos.</div>
        <div class="repuesto-selected-list" id="repuestosSelectedList"></div>
        <textarea id="repuestosManual" class="is-technical-store"></textarea>
        <div class="repuesto-error" id="repuestosManualError"></div>
      </div>
    </div>

    <div class="final-control-card" id="finalControlCard">
      <div class="final-control-head">
        <div>
          <h3>✅ Control final del equipo</h3>
          <p>Registra cómo queda el equipo después del trabajo. Esta parte confirma funcionamiento y parámetros finales.</p>
        </div>
      </div>
      <div class="final-grid">
        <div class="field zg-reefer-final-field"><label for="estadoFinalEquipo">Estado final del equipo</label><select id="estadoFinalEquipo"><option value="">Seleccionar estado</option><option>Equipo operativo</option><option>Equipo operativo con observación</option><option>Equipo pendiente por repuesto</option><option>Equipo requiere seguimiento</option><option>Equipo no operativo</option></select></div>
        <div class="field zg-reefer-final-field"><label for="setPointFinal">Set point final °C</label><input type="text" id="setPointFinal" placeholder="Ej. -18" inputmode="decimal" step="0.1"><div class="field-error" id="setPointFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="retornoFinal">Retorno de aire final °C</label><input type="text" id="retornoFinal" placeholder="Ej. 5" inputmode="decimal" step="0.1"><div class="field-error" id="retornoFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="suministroFinal">Suministro de aire final °C</label><input type="text" id="suministroFinal" placeholder="Ej. -2" inputmode="decimal" step="0.1"><div class="field-error" id="suministroFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="voltajeFinalL1L2">Voltaje final L1-L2</label><input type="text" id="voltajeFinalL1L2" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeFinalL1L2Error"></div></div>
        <div class="field zg-reefer-final-field"><label for="voltajeFinalL2L3">Voltaje final L2-L3</label><input type="text" id="voltajeFinalL2L3" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeFinalL2L3Error"></div></div>
        <div class="field zg-reefer-final-field"><label for="voltajeFinalL1L3">Voltaje final L1-L3</label><input type="text" id="voltajeFinalL1L3" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeFinalL1L3Error"></div></div>
        <div class="field zg-reefer-final-field"><label for="tempAmbienteFinal">Temp. ambiente final °C</label><input type="text" id="tempAmbienteFinal" placeholder="Ej. 24" inputmode="decimal" step="0.1"><div class="field-error" id="tempAmbienteFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="presionAltaFinal">Presión alta final</label><input type="text" id="presionAltaFinal" placeholder="Ej. 250 PSI" inputmode="decimal" autocomplete="off"><div class="field-error" id="presionAltaFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="presionBajaFinal">Presión baja final</label><input type="text" id="presionBajaFinal" placeholder="Ej. 18 PSI" inputmode="decimal" autocomplete="off"><div class="field-error" id="presionBajaFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetEstadoFinal">Estado final del generador</label><select id="gensetEstadoFinal"><option value="">Seleccionar estado</option><option>Operativo</option><option>Operativo con observación</option><option>Pendiente por repuesto</option><option>Requiere seguimiento</option><option>No operativo</option></select><div class="field-error" id="gensetEstadoFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetHorometroFinal">Horómetro final (h)</label><input type="number" id="gensetHorometroFinal" min="0" step="0.1" inputmode="decimal" placeholder="Ej. 1252.0"><div class="field-error" id="gensetHorometroFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetArranqueFinal">Prueba de arranque final</label><select id="gensetArranqueFinal"><option value="">Seleccionar</option><option>Arranque normal</option><option>Arranque con observación</option><option>No arranca</option><option>No se realizó</option></select><div class="field-error" id="gensetArranqueFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetVoltajeBateriaFinal">Voltaje de batería final</label><input type="text" id="gensetVoltajeBateriaFinal" inputmode="decimal" placeholder="Ej. 13.8 V"><div class="field-error" id="gensetVoltajeBateriaFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetVoltajeSalidaL1L2">Voltaje de salida L1-L2</label><input type="text" id="gensetVoltajeSalidaL1L2" inputmode="decimal" placeholder="Ej. 440 V"><div class="field-error" id="gensetVoltajeSalidaL1L2Error"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetVoltajeSalidaL2L3">Voltaje de salida L2-L3</label><input type="text" id="gensetVoltajeSalidaL2L3" inputmode="decimal" placeholder="Ej. 440 V"><div class="field-error" id="gensetVoltajeSalidaL2L3Error"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetVoltajeSalidaL1L3">Voltaje de salida L1-L3</label><input type="text" id="gensetVoltajeSalidaL1L3" inputmode="decimal" placeholder="Ej. 440 V"><div class="field-error" id="gensetVoltajeSalidaL1L3Error"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetTemperaturaMotorFinal">Temperatura del motor final °C</label><input type="number" id="gensetTemperaturaMotorFinal" step="0.1" inputmode="decimal" placeholder="Ej. 82"><div class="field-error" id="gensetTemperaturaMotorFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetNivelCombustibleFinal">Nivel de combustible final</label><select id="gensetNivelCombustibleFinal"><option value="">Seleccionar</option><option>Lleno</option><option>3/4</option><option>1/2</option><option>1/4</option><option>Reserva</option><option>Vacío</option></select><div class="field-error" id="gensetNivelCombustibleFinalError"></div></div>

        <div class="field zg-service-time-field">
          <label for="horaInicioServicio">Hora de inicio del servicio</label>
          <input type="datetime-local" id="horaInicioServicio" readonly aria-readonly="true">
          <div class="field-hint zg-service-time-hint">Se registra automáticamente al guardar la inspección preliminar.</div>
        </div>
        <div class="field zg-service-time-field">
          <label for="horaFinServicio">Hora de finalización del servicio</label>
          <input type="datetime-local" id="horaFinServicio" readonly aria-readonly="true">
          <div class="field-hint zg-service-time-hint">Se registra automáticamente al generar el primer informe final.</div>
        </div>

        <div class="field full zg-final-maint-question">
          <label for="zgRequiereOtroMantenimiento">¿Requiere otro mantenimiento?</label>
          <select id="zgRequiereOtroMantenimiento">
            <option value="">Seleccionar</option>
            <option value="No">No</option>
            <option value="Sí">Sí</option>
          </select>
          <div class="field-error" id="zgRequiereOtroMantenimientoError"></div>
        </div>
        <div class="field full zg-maintenance-type is-hidden" id="zgTipoMantenimientoWrap">
          <label for="zgTipoOtroMantenimiento">Tipo de mantenimiento requerido</label>
          <select id="zgTipoOtroMantenimiento">
            <option value="">Seleccionar</option>
            <option value="Preventivo">Preventivo</option>
            <option value="Correctivo">Correctivo</option>
            <option value="Preventivo y correctivo">Preventivo y correctivo</option>
          </select>
          <div class="field-error" id="zgTipoOtroMantenimientoError"></div>
        </div>
        <div class="field full zg-maintenance-reason is-hidden" id="zgMotivoMantenimientoWrap">
          <label for="zgMotivoOtroMantenimiento">Razón del mantenimiento requerido</label>
          <textarea id="zgMotivoOtroMantenimiento" maxlength="700" placeholder="Ej. Se recomienda un mantenimiento preventivo porque se detectó desgaste en los componentes y conviene atenderlo antes de que genere una falla."></textarea>
          <div class="field-hint">Explícalo con palabras claras para que el cliente entienda qué se encontró, por qué debe atenderse y qué podría ocurrir si se posterga.</div>
          <div class="field-error" id="zgMotivoOtroMantenimientoError"></div>
        </div>
      </div>
      <div class="final-note">Estos datos quedan en el informe final para comparar cómo se encontró el equipo, cómo quedó y si requiere otra intervención.</div>
    </div>

    <div class="firmas-card" id="firmasCard">
      <div class="firmas-head">
        <div>
          <h3>✍️ Conformidad del servicio</h3>
          <p>Registra el nombre del responsable del cliente y las firmas de conformidad al finalizar el trabajo.</p>
        </div>
      </div>
      <div class="firmas-grid">
        <div class="field full"><label for="adminTiendaNombre">Nombre del administrador / responsable del cliente</label><input type="text" id="adminTiendaNombre" placeholder="Ej. Juan Pérez" autocomplete="off"><div class="field-hint">Este nombre aparecerá en el informe junto a su firma.</div></div>
        <div class="field full"><label for="adminTiendaCargo">Cargo del responsable del cliente</label><input type="text" id="adminTiendaCargo" placeholder="Ej. Administrador de tienda / Supervisor / Encargado" autocomplete="off"><div class="field-hint">El cargo aparecerá debajo del nombre en el informe.</div></div>
        <div class="firma-box">
          <div class="firma-title"><b>Firma del técnico</b><span id="firmaTecnicoNombre">Se usará el técnico seleccionado</span></div>
          <canvas id="firmaTecnicoCanvas" class="firma-canvas"></canvas>
          <div class="firma-actions"><button type="button" class="firma-clear" id="limpiarFirmaTecnico">Limpiar firma</button></div>
          <input type="hidden" id="firmaTecnico" value="">
        </div>
        <div class="firma-box">
          <div class="firma-title"><b>Firma del responsable</b><span>Cliente / tienda</span></div>
          <canvas id="firmaAdminCanvas" class="firma-canvas"></canvas>
          <div class="firma-actions"><button type="button" class="firma-clear" id="limpiarFirmaAdmin">Limpiar firma</button></div>
          <input type="hidden" id="firmaAdmin" value="">
        </div>
      </div>
    </div>
  </section>

  <div id="savedBox" class="savedbox"></div>

</main>

<div class="actionbar">
  <div class="actionbar-inner">
    <span class="count-pill" id="counter">0 trabajos · 0 fotos</span>
    <button class="btn btn-ghost" id="clearBtn">Limpiar</button>
    <button class="btn btn-primary" id="pdfBtn">
      <svg viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
      Generar y guardar
    </button>
  </div>
</div>

<div class="overlay" id="overlay">
  <div>
    <div class="spinner"></div>
    <div class="msg">Generando informe...</div>
  </div>
</div>

<div class="toast" id="toast"></div>

<!-- imágenes ocultas que usa el PDF -->
<img id="pdfBg" src="zgroup-bg.jpg" alt="" style="display:none">

<!-- Modal de mapa para elegir ubicación -->
<div class="map-modal" id="mapModal">
  <div class="map-box">
    <div class="map-head">
      <span class="map-title">📍 Elige la ubicación del servicio</span>
      <button class="map-close" id="mapClose" type="button">×</button>
    </div>
    <div class="map-search">
      <input type="text" id="mapSearch" placeholder="Buscar dirección o lugar…" autocomplete="off">
      <button id="mapSearchBtn" type="button">Buscar</button>
      <div class="map-sug" id="mapSug"></div>
    </div>
    <div id="pickMap" class="pick-map"></div>
    <div class="map-foot">
      <div class="map-addr" id="pickAddr">Toca el mapa o arrastra el pin para elegir el punto exacto…</div>
      <button class="btn btn-primary" id="mapConfirm" type="button">Usar esta ubicación</button>
      <a class="btn btn-ghost map-route" id="mapRoute" href="#" target="_blank" rel="noopener" style="display:none">🧭 Cómo llegar en Google Maps</a>
    </div>
  </div>
</div>

<script>
/* =========================================================================
   TRABAJOS DE ZGROUP  ←  edita/agrega aquí cuando necesites.
   Cada item: { id (único, sin espacios), nombre }
   ========================================================================= */
const WORK_TYPES = <?= json_encode($workTypes, JSON_UNESCAPED_UNICODE) ?>;
/* ========================================================================= */

/* =========================================================================
   CAMPOS POR TRABAJO (mini-reporte). Edita/agrega los ítems que quieras.
   tipo: 'text' | 'num' | 'area' (texto largo) | 'sel' (lista de opciones)
   ========================================================================= */
const _estado = ['Operativo','Pendiente','Requiere seguimiento'];
const _sino = ['Sí','No'];
const _okfalla = ['OK','Falla'];
const CAMPOS = {
  asistencia_online: [
    {id:'medio', label:'Medio de contacto', tipo:'text'},
    {id:'problema', label:'Problema reportado', tipo:'area'},
    {id:'solucion', label:'Solución aplicada', tipo:'area'},
  ],
  asistencia_tecnica: [
    {id:'detalle_tecnico', label:'Detalle técnico de la asistencia técnica', tipo:'area'},
  ],
  mantenimiento_correctivo: [
    {id:'detalle_tecnico', label:'Detalle técnico del mantenimiento correctivo', tipo:'area'},
  ],
  mantenimiento_productivo: [
    {id:'condicion', label:'Condición general del equipo', tipo:'sel', opciones:['Normal','Operativo con observación','Requiere limpieza','Requiere seguimiento','Requiere correctivo']},
    {id:'accion', label:'Acción realizada', tipo:'area'},
  ],
  ingreso_new_genset: [
    {id:'ngenset', label:'N° de genset', tipo:'text'},
    {id:'modelo', label:'Marca / modelo', tipo:'text'},
    {id:'horas', label:'Horas de uso', tipo:'num'},
    {id:'estado', label:'Estado de ingreso', tipo:'text'},
  ],
  ingreso_new_reefer: [    {id:'modelo', label:'Modelo de máquina', tipo:'text'},
    {id:'serie', label:'N° de serie', tipo:'text'},
    {id:'estado', label:'Estado de ingreso', tipo:'text'},
  ],
  ingreso_almacenaje: [    {id:'ubic', label:'Ubicación asignada', tipo:'text'},
    {id:'estado', label:'Estado', tipo:'text'},
  ],
  ingreso_reentrega: [    {id:'motivo', label:'Motivo de reentrega', tipo:'area'},
    {id:'estado', label:'Estado', tipo:'text'},
  ],
  ingreso_devolucion: [    {id:'tipo', label:'Tipo', tipo:'sel', opciones:['Ingreso','Devolución']},
    {id:'estado', label:'Estado', tipo:'text'},
  ],
  instalacion: [
    {id:'equipo', label:'Equipo instalado', tipo:'text'},
    {id:'ubic', label:'Ubicación', tipo:'text'},
    {id:'pruebas', label:'Pruebas realizadas', tipo:'area'},
    {id:'resultado', label:'Resultado', tipo:'sel', opciones:_estado},
  ],
  instalacion_accesorios: [
    {id:'acc', label:'Accesorio(s) instalado(s)', tipo:'text'},
    {id:'cant', label:'Cantidad', tipo:'num'},
    {id:'ubic', label:'Ubicación', tipo:'text'},
    {id:'resultado', label:'Resultado', tipo:'sel', opciones:_estado},
  ],
  instalacion_luminarias: [
    {id:'tipo', label:'Tipo / modelo de luminaria', tipo:'text'},
    {id:'cant', label:'Cantidad de luminarias instaladas', tipo:'num'},
    {id:'ubic', label:'Ubicación de instalación', tipo:'text'},
    {id:'resultado', label:'Prueba de encendido y estado final', tipo:'sel', opciones:_estado},
  ],
  deshielo_contenedor: [
    {id:'zona_hielo', label:'Zona, superficie o sensor con acumulación de hielo', tipo:'text'},
    {id:'resultado', label:'Resultado del deshielo', tipo:'sel', opciones:['Hielo retirado','Deshielo parcial / requiere seguimiento']},
  ],
  instalacion_reefer: [
    {id:'detalle_tecnico', label:'Detalle técnico de la instalación de reefer', tipo:'area'},
  ],
  instalacion_humidificacion: [
    {id:'equipo', label:'Equipo', tipo:'text'},
    {id:'ubic', label:'Ubicación', tipo:'text'},
    {id:'hum', label:'Humedad objetivo (%)', tipo:'num'},
    {id:'prueba', label:'Prueba realizada', tipo:'sel', opciones:_okfalla},
  ],
  reparacion_bomba: [
    {id:'tipo', label:'Tipo de bomba', tipo:'text'},
    {id:'falla', label:'Falla detectada', tipo:'area'},
    {id:'repuestos', label:'Repuestos cambiados', tipo:'area'},
    {id:'estado', label:'Estado final', tipo:'sel', opciones:_estado},
  ],
  reparacion_carreta: [
    {id:'ncarreta', label:'N° de carreta', tipo:'text'},
    {id:'falla', label:'Falla detectada', tipo:'area'},
    {id:'trabajo', label:'Trabajo realizado', tipo:'area'},
    {id:'estado', label:'Estado final', tipo:'sel', opciones:_estado},
  ],
  reparacion_estructural: [
    {id:'zona', label:'Zona afectada', tipo:'text'},
    {id:'dano', label:'Daño detectado', tipo:'area'},
    {id:'reparacion', label:'Reparación realizada', tipo:'area'},
    {id:'estado', label:'Estado final', tipo:'sel', opciones:_estado},
  ],
  reparacion_genset: [
    {id:'ngenset', label:'N° de genset', tipo:'text'},
    {id:'falla', label:'Falla detectada', tipo:'area'},
    {id:'repuestos', label:'Repuestos cambiados', tipo:'area'},
    {id:'horas', label:'Horas de uso', tipo:'num'},
    {id:'estado', label:'Estado final', tipo:'sel', opciones:_estado},
  ],
  reparacion_reefer: [    {id:'modelo', label:'Modelo de máquina', tipo:'text'},
    {id:'falla', label:'Falla detectada', tipo:'area'},
    {id:'diagnostico', label:'Diagnóstico', tipo:'area'},
    {id:'repuestos', label:'Repuestos cambiados', tipo:'area'},
    {id:'temp', label:'Temp. tras reparación (°C)', tipo:'num'},
    {id:'estado', label:'Estado final', tipo:'sel', opciones:_estado},
  ],
  reparacion_trailer: [
    {id:'ntrailer', label:'N° de trailer', tipo:'text'},
    {id:'falla', label:'Falla detectada', tipo:'area'},
    {id:'trabajo', label:'Trabajo realizado', tipo:'area'},
    {id:'estado', label:'Estado final', tipo:'sel', opciones:_estado},
  ],
  retiro_piezas: [
    {id:'piezas', label:'Pieza(s) retirada(s)', tipo:'area'},
    {id:'cant', label:'Cantidad', tipo:'num'},
    {id:'motivo', label:'Motivo', tipo:'text'},
    {id:'destino', label:'Destino', tipo:'text'},
  ],
  revision_tecnica: [    {id:'tset', label:'Temp. seteada (°C)', tipo:'num'},
    {id:'tact', label:'Temp. actual (°C)', tipo:'num'},
    {id:'volt', label:'Voltaje (V)', tipo:'num'},
    {id:'estado', label:'Estado general', tipo:'sel', opciones:_estado},
    {id:'recom', label:'Recomendaciones', tipo:'area'},
  ],
  revision_prueba_motor: [
    {id:'equipo', label:'N° motor / equipo', tipo:'text'},
    {id:'horas', label:'Horas de uso', tipo:'num'},
    {id:'presion', label:'Presión de aceite', tipo:'text'},
    {id:'tempop', label:'Temp. de operación (°C)', tipo:'num'},
    {id:'resultado', label:'Resultado de prueba', tipo:'sel', opciones:_okfalla},
  ],
  genset_inspeccion_diagnostico: [
    {id:'reporte', label:'Falla o condición reportada', tipo:'area'},
    {id:'horometro', label:'Horómetro registrado (h)', tipo:'num'},
    {id:'diagnostico', label:'Diagnóstico técnico del genset', tipo:'area'},
    {id:'motor', label:'Condición del motor', tipo:'area'},
    {id:'alternador', label:'Condición del alternador', tipo:'area'},
    {id:'resultado', label:'Resultado de la inspección', tipo:'sel', opciones:['Operativo','Operativo con observación','Requiere mantenimiento','No operativo']},
  ],
  genset_mantenimiento_preventivo: [
    {id:'detalle_tecnico', label:'Detalle técnico del mantenimiento preventivo', tipo:'area'},
  ],
  genset_mantenimiento_correctivo: [
    {id:'detalle_tecnico', label:'Detalle técnico del mantenimiento correctivo', tipo:'area'},
  ],
  genset_cambio_aceite_filtros: [
    {id:'horometro', label:'Horómetro (h)', tipo:'num'},
    {id:'aceite_tipo', label:'Tipo y viscosidad del aceite', tipo:'text'},
    {id:'aceite_cantidad', label:'Cantidad de aceite utilizada', tipo:'text'},
    {id:'filtro_aceite', label:'Código del filtro de aceite', tipo:'text'},
    {id:'filtro_combustible', label:'Código del filtro de combustible', tipo:'text'},
    {id:'filtro_aire', label:'Código / condición del filtro de aire', tipo:'text'},
    {id:'purga', label:'Purga y cebado del sistema de combustible', tipo:'sel', opciones:['Realizado','No requerido','Pendiente']},
    {id:'prueba', label:'Prueba posterior al servicio', tipo:'sel', opciones:_okfalla},
  ],
  genset_sistema_electrico: [
    {id:'bateria', label:'Voltaje y condición de batería', tipo:'text'},
    {id:'arranque', label:'Motor de arranque', tipo:'area'},
    {id:'alternador_carga', label:'Alternador de carga de batería', tipo:'area'},
    {id:'alternador_salida', label:'Alternador de generación / salida', tipo:'area'},
    {id:'protecciones', label:'Protecciones, cableado y conexiones', tipo:'area'},
    {id:'controlador', label:'Controlador y alarmas', tipo:'area'},
    {id:'resultado', label:'Resultado eléctrico', tipo:'sel', opciones:['Conforme','Con observación','Pendiente','No conforme']},
  ],
  genset_prueba_carga: [
    {id:'horometro', label:'Horómetro al iniciar la prueba (h)', tipo:'num'},
    {id:'voltajes', label:'Voltajes medidos durante la prueba', tipo:'area'},
    {id:'frecuencia', label:'Frecuencia medida (Hz)', tipo:'num'},
    {id:'corriente', label:'Corriente / carga aplicada', tipo:'area'},
    {id:'presion_aceite', label:'Presión de aceite', tipo:'text'},
    {id:'temperatura', label:'Temperatura de operación °C', tipo:'num'},
    {id:'tiempo', label:'Tiempo de prueba', tipo:'text'},
    {id:'resultado', label:'Resultado de la prueba bajo carga', tipo:'sel', opciones:['Satisfactoria','Satisfactoria con observación','No satisfactoria','Prueba parcial']},
  ],
  trabajos_sistema_electrico: [
    {id:'comp', label:'Componente intervenido', tipo:'text'},
    {id:'falla', label:'Falla detectada', tipo:'area'},
    {id:'trabajo', label:'Trabajo realizado', tipo:'area'},
    {id:'volt', label:'Voltaje medido (V)', tipo:'num'},
    {id:'estado', label:'Estado final', tipo:'sel', opciones:_estado},
  ],
};

// Lista de técnicos (viene de la base de datos) para el buscador
const TECNICOS = <?= json_encode($tecnicos, JSON_UNESCAPED_UNICODE) ?>;
const CLIENTES_CATALOGO = <?= json_encode($clientesCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const SERVICIOS_ODOO_CATALOGO = <?= json_encode($serviciosOdooCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const COTIZACIONES_CATALOGO = <?= json_encode($cotizacionesCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const CONTENEDORES_CATALOGO = <?= json_encode($contenedoresCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const MAQUINAS_CATALOGO = <?= json_encode($maquinasCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const GENERADORES_CATALOGO = <?= json_encode($generadoresCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const REPUESTOS_CATALOGO = <?= json_encode($repuestosCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const REPUESTOS_GENSET_CATALOGO = <?= json_encode($repuestosGensetCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const MODELOS_REEFER_CATALOGO = <?= json_encode($modelosReeferCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const MODELOS_GENSET_CATALOGO = <?= json_encode($modelosGensetCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const REPUESTOS_REEFER_CATALOGO = <?= json_encode($repuestosReeferCatalogo, JSON_UNESCAPED_UNICODE) ?>;
const OPCIONES_TECNICAS_PERSONALIZADAS = <?= json_encode($opcionesTecnicasPersonalizadas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const OPCIONES_TECNICAS_POR_TRABAJO = <?= json_encode($opcionesTecnicasPorTrabajo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const PREINSPECCION = <?= $preinspeccionJson ?: 'null' ?>;
const PREINSPECCION_ERROR = <?= $preinspeccionErrorJson ?: '""' ?>;
const TOKEN_CONTINUACION = <?= $tokenContinuacionJson ?: '""' ?>;
const ZG_SERVICE_DRAFT = <?= $borradorServicioJson ?: 'null' ?>;
const PUSH_TRIGGER_TOKEN = <?= json_encode(defined('ZGROUP_PUSH_TRIGGER_TOKEN') ? ZGROUP_PUSH_TRIGGER_TOKEN : '') ?>;
const ZG_EDIT_MODE = <?= $modo_editar_informe ? 'true' : 'false' ?>;
const ZG_PRE_EDIT_MODE = <?= $modo_editar_preliminar ? 'true' : 'false' ?>;
const ZG_PRE_EDIT_ID = <?= (int)$preliminarEdicionId ?>;
const ZG_EDIT_REPORT = <?= json_encode($informeEdicionPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null' ?>;

// ---- Estado (única fuente de verdad) ----
const state = {
  selected: {},   // id -> { nombre, custom, campos:{}, detalle, photos:[], auto:{} }
  tecnicoId: '',
  tecnicoNombre: '',
  customSeq: 0,
};

const QUICK_BANK = {
  actividades: [
    'Inspección visual','Revisión eléctrica','Prueba funcional','Prueba PTI','Limpieza técnica','Toma de parámetros','Descarga de datos','Verificación de fugas','Cambio de componente','Ajuste de conexiones',
    'Revisión de ventiladores','Revisión de contactores','Verificación de protecciones','Revisión de controlador','Verificación de refrigerante','Medición de presiones','Revisión de filtro deshidratador','Revisión de compresor','Prueba de aislamiento','Limpieza de evaporador','Limpieza de condensador','Revisión de serpentines','Verificación de drenajes','Revisión de puertas y empaques','Registro fotográfico'
  ],
  hallazgos: [
    'Sin novedad','Conexión floja','Componente recalentado','Fuga detectada','Filtro obstruido','Bajo aislamiento','Alto consumo','Ruido anormal','Equipo sin energía','Refrigerante bajo',
    'Sin suministro eléctrico','Contactores dañados','Ausencia de refrigerante','Suciedad acumulada','Serpentín obstruido','Corrosión visible','Rastros de aceite','Motor con consumo elevado','Parámetros inestables','Drenaje obstruido','Empaque de puerta desgastado','Serpentín con suciedad','Equipo requiere correctivo'
  ],
  acciones: [
    'Se corrigió conexión','Se reemplazó componente','Se realizó limpieza','Se realizaron pruebas','Se verificó operación','Se dejó operativo','Se deja pendiente por repuesto','Se recomienda evaluación',
    'Se realizó medición eléctrica','Se tomó registro fotográfico','Se descargaron datos del controlador','Se verificó nivel de refrigerante','Se verificaron presiones','Se revisaron protecciones','Se limpió evaporador','Se limpió condensador','Se verificaron drenajes','Se revisaron puertas y empaques','Se validó funcionamiento final'
  ],
  recomendaciones: [
    'Realizar mantenimiento preventivo','Monitorear consumo eléctrico','Revisar suministro eléctrico','Cambiar repuesto indicado','Realizar limpieza periódica','Verificar fugas antes de cargar refrigerante','No manipular sin personal técnico','Programar seguimiento',
    'Reparar fugas detectadas','Realizar vacío técnico','Cargar refrigerante según especificación','Sustituir filtro deshidratador','Evaluar reemplazo de motor','Evaluar cambio de contactores','Realizar limpieza integral','Verificar cierre de puertas','Apagar unidad durante carga y descarga','Respetar límites de carga','Considerar cambio total del equipo'
  ],
  estados: ['Operativo','Operativo con observación','Pendiente','Requiere repuesto','Requiere seguimiento','No operativo']
};

const PRESETS = {
  'Revisión técnica': {
    actividades:['Inspección visual','Revisión eléctrica','Toma de parámetros','Descarga de datos','Verificación de fugas','Revisión de ventiladores','Revisión de contactores','Verificación de protecciones','Revisión de controlador','Registro fotográfico'],
    hallazgos:['Sin novedad'],
    acciones:['Se realizaron pruebas','Se verificó operación','Se tomó registro fotográfico','Se descargaron datos del controlador'],
    recomendaciones:['Programar seguimiento','Monitorear consumo eléctrico','Realizar mantenimiento preventivo'],
    estado:'Operativo con observación'
  },
  'Mantenimiento preventivo': {
    actividades:['Inspección visual','Limpieza técnica','Revisión eléctrica','Prueba funcional','Toma de parámetros','Verificación de fugas','Revisión de ventiladores','Revisión de filtro deshidratador','Registro fotográfico'],
    hallazgos:['Sin novedad','Suciedad acumulada'],
    acciones:['Se realizó limpieza','Se realizaron pruebas','Se verificó operación','Se dejó operativo','Se tomó registro fotográfico','Se validó funcionamiento final'],
    recomendaciones:['Realizar mantenimiento preventivo','Monitorear consumo eléctrico','Realizar limpieza periódica'],
    estado:'Operativo'
  },
  'Mantenimiento correctivo': {
    actividades:['Inspección visual','Revisión eléctrica','Cambio de componente','Prueba funcional','Toma de parámetros','Revisión de contactores','Prueba de aislamiento','Registro fotográfico'],
    hallazgos:['Componente recalentado','Conexión floja'],
    acciones:['Se reemplazó componente','Se corrigió conexión','Se realizaron pruebas','Se verificó operación','Se tomó registro fotográfico'],
    recomendaciones:['Cambiar repuesto indicado','Programar seguimiento','Evaluar cambio de contactores','Monitorear consumo eléctrico'],
    estado:'Requiere seguimiento'
  },
  'Mantenimiento productivo': {
    actividades:['Inspección visual','Limpieza técnica','Limpieza de evaporador','Limpieza de condensador','Revisión de serpentines','Revisión de ventiladores','Verificación de drenajes','Revisión de puertas y empaques','Toma de parámetros','Verificación de refrigerante','Registro fotográfico'],
    hallazgos:['Sin novedad','Suciedad acumulada','Serpentín con suciedad'],
    acciones:['Se realizó limpieza','Se verificó operación','Se tomaron parámetros','Se verificaron drenajes','Se validó funcionamiento final','Se tomó registro fotográfico'],
    recomendaciones:['Cumplir programa de mantenimiento preventivo','Realizar limpieza integral','Verificar cierre de puertas','Respetar límites de carga','Monitorear consumo eléctrico'],
    estado:'Operativo con observación'
  },
  'PTI / Run test': {
    actividades:['Prueba PTI','Toma de parámetros','Descarga de datos','Prueba funcional','Verificación de protecciones','Verificación de fugas','Registro fotográfico'],
    hallazgos:['Sin novedad'],
    acciones:['Se realizaron pruebas','Se verificó operación','Se descargaron datos del controlador','Se validó funcionamiento final'],
    recomendaciones:['Monitorear consumo eléctrico','Programar seguimiento'],
    estado:'Operativo'
  },
  'Solo evidencia': {
    actividades:['Inspección visual','Registro fotográfico'],
    acciones:['Se tomó registro fotográfico','Se verificó operación'],
    estado:'Operativo con observación'
  }
};


const MANUAL_REPORT_IDS = ['repuestosManual'];

const PTI_ITEMS = ['Power cable','Plug / receptacle','Circuit breaker','Control 440/24 V','Power 380/440 V','Power 220/440 V','Voltaje L1-L2-L3','Main contactors','Relay','Controller','RMM','ID correcto en controlador','Air sensor','Defrost sensor','Evap fan motor 1','Evap fan motor 2','Evap fan motor 3','Cond. fan motor','Compressor refrigeration','Heaters','Controller type','Software','Data corder','Condenser','Evaporator','Drenaje evaporador','Tuberías / fugas','Compresor y ventiladores sin ruido anormal','Modulator valve','Expansion valve','Nivel refrigerante','Nivel aceite compresor','Filtro deshidratador','Defrost manual','Low pressure','High pressure','Etiquetas / stickers','PTI sticker','PTI','Mantenimiento preventivo','Mantenimiento correctivo'];

function emptyAuto(){ return { actividades:[], hallazgos:[], acciones:[], recomendaciones:[], estado:'', pti:{}, plantilla:'', limpiado:false }; }


// ---- Refs ----
const $ = id => document.getElementById(id);
const workGrid = $('workGrid');
const panelsEl = $('panels');
const toastEl = $('toast');
let workQuery = '';   // texto del buscador de trabajos (debe declararse antes del init)


// ---- Datos automáticos según la marca reefer seleccionada ----
const CONTROLADORES_POR_MARCA = {
  'STAR COOL': ['CIM5','CIM6'],
  'THERMO KING': ['MP3000','MP4000','MP5000'],
  'DAIKIN': ['DAIKIN'],
  'CARRIER': ['MICROLINK 2','MICROLINK 3'],
};
const MODELOS_POR_MARCA = {
  'THERMO KING': ['MAGNUM PLUS'],
  'CARRIER': ['MICROLINK 2','MICROLINK 3'],
  'STAR COOL': ['CIM 5','CIM 6'],
  'DAIKIN': ['DAIKIN'],
};
function normalizarRefrigeranteCatalogo(v){
  const n=String(v||'').toUpperCase().replace(/[^A-Z0-9]/g,'');
  if(n.includes('404')) return 'R404A';
  if(n.includes('134')) return 'R134a';
  if(n.includes('513')) return 'R513A';
  if(n.includes('452')) return 'R452A';
  return String(v||'');
}
function actualizarOpcionesModelo(force=false){
  const marca = getVal('marcaEquipo').toUpperCase();
  const input = $('modeloEquipo');
  const list = $('modeloEquipoOpciones');
  if(!input || !list) return;
  const opciones = MODELOS_POR_MARCA[marca] || [];
  const actual = input.value.trim().toUpperCase();
  list.innerHTML = '';
  opciones.forEach(v => { const op=document.createElement('option'); op.value=v; list.appendChild(op); });
  input.placeholder = opciones.length ? ('Opciones: ' + opciones.join(' / ')) : 'Selecciona o escribe el modelo';
  if(force){
    if(opciones.length === 1) input.value = opciones[0];
    else if(actual && opciones.map(x=>x.toUpperCase()).includes(actual)) input.value = input.value;
    else if(opciones.length) input.value = '';
  }
}
function actualizarOpcionesControlador(force=false){
  const marca = getVal('marcaEquipo').toUpperCase();
  const input = $('controladorEquipo');
  const list = $('controladorOpciones');
  if(!input || !list) return;
  const opciones = CONTROLADORES_POR_MARCA[marca] || [];
  const actual = input.value.trim().toUpperCase();
  list.innerHTML = '';
  opciones.forEach(v => { const op=document.createElement('option'); op.value=v; list.appendChild(op); });
  input.placeholder = opciones.length ? ('Opciones: ' + opciones.join(' / ')) : 'Selecciona o escribe controlador';
  if(force){
    if(opciones.length === 1) input.value = opciones[0];
    else if(actual && opciones.map(x=>x.toUpperCase()).includes(actual)) input.value = input.value;
    else if(opciones.length) input.value = '';
  }
  const hint = $('controladorHint');
  if(hint){
    hint.textContent = opciones.length ? ('Opciones sugeridas para ' + (getVal('marcaEquipo') || 'la marca') + ': ' + opciones.join(', ')) : '';
  }
}
function aplicarRefrigerantePorMarca(){
  const marca=getVal('marcaEquipo').toUpperCase();
  const ref=$('refrigerante');
  if(!ref) return;
  if(marca==='THERMO KING') ref.value='R404A';
  else if(['CARRIER','STAR COOL','DAIKIN'].includes(marca)) ref.value='R134a';
}
function setupControladorPorMarca(){
  const marca = $('marcaEquipo');
  const modelo = $('modeloEquipo');
  if(marca){
    marca.addEventListener('change', () => {
      actualizarOpcionesModelo(true);
      actualizarOpcionesControlador(true);
      aplicarRefrigerantePorMarca();
      clearFieldError('marcaEquipo');
      clearFieldError('controladorEquipo');
      clearFieldError('modeloEquipo');
    });
  }
  if(modelo){
    modelo.addEventListener('change',()=>{
      const m=getVal('marcaEquipo').toUpperCase();
      const v=getVal('modeloEquipo').toUpperCase();
      if(m==='CARRIER'&&['MICROLINK 2','MICROLINK 3'].includes(v)) setVal('controladorEquipo',v);
      if(m==='STAR COOL'&&['CIM 5','CIM 6'].includes(v)) setVal('controladorEquipo',v.replace(' ',''));
      if(m==='DAIKIN') setVal('controladorEquipo','DAIKIN');
    });
  }
  actualizarOpcionesModelo(false);
  actualizarOpcionesControlador(false);
  aplicarRefrigerantePorMarca();
  setTimeout(() => {actualizarOpcionesModelo(false);actualizarOpcionesControlador(false);}, 400);
}

// ---- Firmas digitales ----
function setupFirmaCanvas(canvasId, hiddenId, clearId){
  const canvas = $(canvasId);
  const hidden = $(hiddenId);
  const clear = $(clearId);
  if(!canvas || !hidden) return;
  const ctx = canvas.getContext('2d');
  let drawing = false;
  let last = null;

  function resize(){
    const data = hidden.value;
    const rect = canvas.getBoundingClientRect();
    const ratio = window.devicePixelRatio || 1;
    canvas.width = Math.max(1, Math.round(rect.width * ratio));
    canvas.height = Math.max(1, Math.round(rect.height * ratio));
    ctx.setTransform(ratio,0,0,ratio,0,0);
    ctx.lineCap='round'; ctx.lineJoin='round'; ctx.lineWidth=2.2; ctx.strokeStyle='#10213a';
    if(data){
      const img = new Image();
      img.onload = () => ctx.drawImage(img, 0, 0, rect.width, rect.height);
      img.src = data;
    }
  }
  function pos(ev){
    const r = canvas.getBoundingClientRect();
    const p = ev.touches && ev.touches[0] ? ev.touches[0] : ev;
    return {x:p.clientX-r.left, y:p.clientY-r.top};
  }
  function save(){
    try{ hidden.value = canvas.toDataURL('image/png'); }catch(e){}
    if(window.zgroupMarcarCambio) window.zgroupMarcarCambio();
  }
  function start(ev){ ev.preventDefault(); drawing=true; last=pos(ev); }
  function move(ev){
    if(!drawing) return;
    ev.preventDefault();
    const p = pos(ev);
    ctx.beginPath(); ctx.moveTo(last.x,last.y); ctx.lineTo(p.x,p.y); ctx.stroke();
    last = p; save();
  }
  function end(ev){ if(drawing){ drawing=false; save(); } }
  canvas.addEventListener('mousedown', start);
  canvas.addEventListener('mousemove', move);
  window.addEventListener('mouseup', end);
  canvas.addEventListener('touchstart', start, {passive:false});
  canvas.addEventListener('touchmove', move, {passive:false});
  canvas.addEventListener('touchend', end);
  if(clear){ clear.addEventListener('click', () => { ctx.clearRect(0,0,canvas.width,canvas.height); hidden.value=''; if(window.zgroupMarcarCambio) window.zgroupMarcarCambio(); }); }
  setTimeout(resize, 80);
  window.addEventListener('resize', () => setTimeout(resize, 120));
}
function actualizarNombreFirmaTecnico(){
  const span = $('firmaTecnicoNombre');
  if(span) span.textContent = state.tecnicoNombre ? ('Técnico: ' + state.tecnicoNombre) : 'Se usará el técnico seleccionado';
}
function setupFirmasServicio(){
  setupFirmaCanvas('firmaTecnicoCanvas','firmaTecnico','limpiarFirmaTecnico');
  setupFirmaCanvas('firmaAdminCanvas','firmaAdmin','limpiarFirmaAdmin');
  const tec = $('tecnicoInput');
  if(tec) tec.addEventListener('change', () => setTimeout(actualizarNombreFirmaTecnico, 80));
  actualizarNombreFirmaTecnico();
}

// ---- Init ----
(function init(){
  // fecha por defecto = hoy
  const t = new Date();
  $('fecha').value = `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}`;
  renderWorkCards();
  $('pdfBtn').addEventListener('click', generatePDF);
  $('clearBtn').addEventListener('click', clearAll);
  updateCounter();
  startClock();
  setupTecnico();
  setupCatalogoCotizacionesClientes();
  setupDatosGeneralesDesplegable();
  setupMapPicker();
  setupRequiredFields();
  setupPreinspeccion();
  setupControlFinal();
  setupControladorPorMarca();
  setupRepuestosControl();
  setupFirmasServicio();
  applyPreinspeccionContinuacion();
  actualizarOpcionesControlador(false);
  cargarTrabajoPrevistoDesdePreinspeccion();
  $('workSearch').addEventListener('input', e => { workQuery = e.target.value; renderWorkCards(); });
})();


// ---- Datos generales desplegable ----
function setupDatosGeneralesDesplegable(){
  const card = $('datosGeneralesCard');
  const btn = $('datosGeneralesToggle');
  const pill = $('datosGeneralesPill');
  if(!card || !btn) return;

  const setOpen = (open) => {
    card.classList.toggle('datos-collapsed', !open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if(pill) pill.textContent = open ? 'Ocultar datos' : 'Ver datos';
  };

  btn.addEventListener('click', () => {
    setOpen(card.classList.contains('datos-collapsed'));
  });

  // Cuando el servicio viene de una preliminar, se muestra compacto al inicio.
  // Si es un registro nuevo, queda abierto para que el técnico llene normalmente.
  setOpen(!PREINSPECCION);
}


// ---- Autocompletado de reportes asignados a tickets Odoo ----
function normBusca(txt){
  return String(txt || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').trim();
}
function soloDigitos(txt){ return String(txt || '').replace(/\D+/g,''); }
function cotizacionExacta(valor){
  const v=soloDigitos(valor||'');
  const svc=(SERVICIOS_ODOO_CATALOGO||[]).find(s=>soloDigitos(s.numero_reporte||'')===v && v!=='');
  if(!svc)return null;
  return {cotizacion:svc.numero_reporte,cliente_nombre:svc.cliente_nombre,ticket_ref:svc.ticket_ref,cotizacion_odoo:svc.cotizacion,servicio:svc};
}
function clienteExacto(valor){
  const v = normBusca(valor);
  return (CLIENTES_CATALOGO || []).find(c => normBusca(c.nombre) === v) || null;
}

function servicioPorReporte(numero){
  const n=soloDigitos(numero||'');
  if(!n)return null;
  return (SERVICIOS_ODOO_CATALOGO||[]).find(s=>soloDigitos(s.numero_reporte||'')===n) || null;
}
function normalizarValorSelect(id,value){
  let v=String(value||'').trim();
  const n=normBusca(v);
  if(id==='zgTipoEquipo'){
    if(/genset|generador|grupo electrogeno/.test(n)) return 'Genset';
    if(/reefer|contenedor|refriger/.test(n)) return 'Reefer';
  }
  if(id==='zgModalidadComercial'){
    if(n.includes('alquiler')) return 'Alquiler';
    if(n.includes('venta')) return 'Venta';
  }
  if(id==='zgTipoInstalacion'){
    if(n.includes('tunel')) return 'Túnel';
    if(n.includes('atmosfera controlada')) return 'Atmósfera controlada';
    if(n.includes('madurador')) return 'Madurador';
    if(n.includes('unidad individual')) return 'Unidad individual';
  }
  if(id==='zgTamanoContenedor'){
    const m=n.match(/\b(10|20|40|45)\b/);
    if(m) return m[1]+' pies';
  }
  return v;
}
function setSelectCatalogo(id,value){
  value=normalizarValorSelect(id,value);
  if(!value)return;
  const el=$(id); if(!el)return;
  const normal=normBusca(value);
  const option=Array.from(el.options||[]).find(o=>normBusca(o.value)===normal||normBusca(o.textContent)===normal);
  if(option) el.value=option.value;
  try{el.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){}
}
function limpiarServicioAutomatico(){
  ['odooTicketRef','odooTicketRefDisplay','odooCotizacion','odooCotizacionDisplay','cliente','direccion','equipoNo'].forEach(id=>setVal(id,''));
  ['zgTipoEquipo','zgModalidadComercial','zgTipoInstalacion','zgTamanoContenedor'].forEach(id=>{
    const el=$(id);
    if(el){
      el.value='';
      try{el.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){}
    }
  });
}

let zgOdooGeocodeSeq=0;
async function zgGeocodificarDireccionOdoo(direccion){
  const texto=String(direccion||'').trim();
  if(!texto)return;
  const seq=++zgOdooGeocodeSeq;
  try{
    const url='https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=pe&accept-language=es&q='+encodeURIComponent(texto);
    const r=await fetch(url,{headers:{'Accept':'application/json'}});
    if(!r.ok)return;
    const rows=await r.json();
    if(seq!==zgOdooGeocodeSeq || !Array.isArray(rows) || !rows.length)return;
    const lat=parseFloat(rows[0].lat), lng=parseFloat(rows[0].lon);
    if(!Number.isFinite(lat)||!Number.isFinite(lng))return;

    const direccionActual=getVal('direccion');
    if(!direccionActual || normBusca(direccionActual)!==normBusca(texto))return;

    setVal('direccionCoords',lat.toFixed(6)+', '+lng.toFixed(6));
    setVal('direccionOrigenOdoo','1');
  }catch(e){
    // La dirección textual sigue siendo válida aunque no se obtengan coordenadas.
  }
}

function aplicarServicioImportado(s){
  if(!s)return;
  const safeAddress=v=>{
    const x=String(v||'').trim();
    return x && !/virtual\s+locations|production|wh\/stock|inventory/i.test(x) ? x : '';
  };
  const setIf=(id,value)=>{
    if(String(value||'').trim()!=='') setVal(id,value);
  };

  setVal('orden',s.numero_reporte||'');
  setVal('odooTicketRef',s.ticket_ref||'');
  setVal('odooTicketRefDisplay',s.ticket_ref||'');
  setVal('odooCotizacion',s.cotizacion||'');
  setVal('odooCotizacionDisplay',s.cotizacion||'');
  setVal('cliente',s.cliente_nombre||'');

  if(s.fecha_servicio)setVal('fecha',s.fecha_servicio);
  const dir=safeAddress(s.direccion);
  if(dir){
    setVal('direccion',dir);
    setVal('direccionOrigenOdoo','1');
    setVal('direccionCoords','');
    zgGeocodificarDireccionOdoo(dir);
  }

  let tipo=String(s.tipo_equipo||'').trim();
  const contexto=normBusca([
    s.titulo_ticket,
    s.tipo_servicio,
    s.numero_equipo,
    s.serie_unidad
  ].filter(Boolean).join(' '));

  if(!tipo){
    if(/genset|generador|grupo electrogeno/.test(contexto)) tipo='Genset';
    else if(/reefer|contenedor|refriger/.test(contexto)) tipo='Reefer';
  }

  if(tipo)setSelectCatalogo('zgTipoEquipo',tipo);

  // Espera a que el cambio de tipo muestre los campos Reefer/Genset.
  setTimeout(()=>{
    if(s.modalidad_comercial)setSelectCatalogo('zgModalidadComercial',s.modalidad_comercial);
    if(s.tipo_instalacion)setSelectCatalogo('zgTipoInstalacion',s.tipo_instalacion);
    if(s.tamano_contenedor)setSelectCatalogo('zgTamanoContenedor',s.tamano_contenedor);

    // Dato básico disponible desde Odoo. Los demás datos técnicos quedan para el técnico.
    setIf('equipoNo',s.numero_equipo);

    ['orden','cliente','direccion','odooTicketRef','zgTipoEquipo','zgModalidadComercial','zgTamanoContenedor','equipoNo'].forEach(id=>{
      try{if(typeof clearFieldError==='function')clearFieldError(id);}catch(e){}
    });
  },120);
}
function setCatalogHint(id, msg, kind){
  const el = $(id);
  if(!el) return;
  el.textContent = msg || '';
  el.classList.remove('ok','warn');
  if(kind) el.classList.add(kind);
}
function closeSmartMenus(){
  document.querySelectorAll('.smart-menu.show').forEach(m => m.classList.remove('show'));
}
function renderSmartMenu(menuId, items, onPick){
  const menu = $(menuId);
  if(!menu) return;
  menu.innerHTML = '';
  if(!items.length){ menu.classList.remove('show'); return; }

  // Muestra todas las opciones en un panel con scroll.
  // Así el técnico puede escribir solo "4" y ver todas las cotizaciones que contengan 4,
  // sin tener que escribir el prefijo repetido 1002025 / 1002026.
  items.forEach(item => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'smart-option';
    b.innerHTML = `<div><span class="smart-main">${escapeHtml(item.main || '')}</span><span class="smart-sub">${escapeHtml(item.sub || '')}</span></div><span class="smart-badge">usar</span>`;
    b.addEventListener('mousedown', ev => ev.preventDefault());
    b.addEventListener('click', () => { onPick(item.raw); menu.classList.remove('show'); });
    menu.appendChild(b);
  });
  menu.classList.add('show');
}

function cotizacionesFiltradas(q){
  const query=soloDigitos(q||'');
  return (SERVICIOS_ODOO_CATALOGO||[])
    .filter(s=>{
      const nro=soloDigitos(s.numero_reporte||'');
      return nro!=='' && (query==='' || nro.startsWith(query));
    })
    .sort((a,b)=>String(b.numero_reporte||'').localeCompare(String(a.numero_reporte||''),undefined,{numeric:true}));
}

function mostrarCotizaciones(){
  const orden=$('orden');
  if(!orden)return;

  const items=cotizacionesFiltradas(orden.value)
    .slice(0,50)
    .map(s=>({
      raw:s,
      main:s.numero_reporte||'',
      sub:[
        s.ticket_ref ? 'Ticket #'+s.ticket_ref : '',
        s.cliente_nombre||'',
        s.tipo_equipo||'',
        s.tamano_contenedor||'',
        s.modalidad_comercial||''
      ].filter(Boolean).join(' · ') || 'Reporte asignado desde el panel'
    }));

  renderSmartMenu('ordenSuggest',items,s=>{
    aplicarServicioImportado(s);
    setCatalogHint(
      'ordenHint',
      [
        s.ticket_ref ? 'Ticket #'+s.ticket_ref : '',
        s.cliente_nombre||'',
        s.tipo_equipo||'',
        s.tamano_contenedor||''
      ].filter(Boolean).join(' · '),
      'ok'
    );
    try{if(typeof clearFieldError==='function')clearFieldError('orden');}catch(e){}
  });
}

function contenedoresFiltrados(q){
  const query = normBusca(q || '');
  return (CONTENEDORES_CATALOGO || [])
    .filter(c => {
      const numero = normBusca(c.numero || '');
      const serial = normBusca(c.serial_unidad || '');
      const marca = normBusca(c.marca_equipo || '');
      return query === '' || numero.includes(query) || serial.includes(query) || marca.includes(query);
    })
    .sort((a,b) => String(a.numero || '').localeCompare(String(b.numero || ''), 'es', {numeric:true, sensitivity:'base'}));
}

function mostrarContenedores(){
  const equipo = $('equipoNo');
  const q = equipo?.value || '';
  const items = contenedoresFiltrados(q).map(c => ({
    raw:c,
    main:c.numero || '',
    sub:[
      c.serial_unidad ? 'Serie: '+c.serial_unidad : '',
      c.marca_equipo ? 'Marca: '+c.marca_equipo : '',
      c.controlador ? 'Ctrl: '+c.controlador : '',
      c.refrigerante ? 'Ref: '+c.refrigerante : ''
    ].filter(Boolean).join(' · ') || 'Contenedor registrado en panel'
  }));

  renderSmartMenu('contenedorSuggest', items, c => {
    setVal('equipoNo', c.numero || '');
    if(c.serial_unidad) setVal('serialUnidad', c.serial_unidad);
    if(c.marca_equipo) setVal('marcaEquipo', c.marca_equipo);
    if(c.controlador) setVal('controladorEquipo', c.controlador);
    if(c.refrigerante) setVal('refrigerante', c.refrigerante);
    setCatalogHint('contenedorHint','Contenedor seleccionado desde el panel.','ok');
    clearFieldError('equipoNo');
  });

  if(items.length){
    const msg = normBusca(q) === ''
      ? 'Selecciona un contenedor creado en el panel.'
      : `Mostrando ${items.length} contenedor(es) coincidente(s).`;
    setCatalogHint('contenedorHint', msg, normBusca(q) === '' ? '' : 'ok');
  } else {
    setCatalogHint('contenedorHint','No hay coincidencias.Se guardará automáticamente al crear la preliminar.', 'warn');
  }
}


function maquinasFiltradas(q){
  const query = normBusca(q || '');
  return (MAQUINAS_CATALOGO || [])
    .filter(m => {
      const serial = normBusca(m.serial_unidad || '');
      const marca = normBusca(m.marca_equipo || '');
      const modelo = normBusca(m.modelo_equipo || '');
      const controlador = normBusca(m.controlador || '');
      const anio = normBusca(m.anio_fabricacion || '');
      const refrigerante = normBusca(m.refrigerante || '');
      return query === '' || serial.includes(query) || marca.includes(query) || modelo.includes(query)
        || controlador.includes(query) || anio.includes(query) || refrigerante.includes(query);
    })
    .sort((a,b) => String(a.serial_unidad || '').localeCompare(String(b.serial_unidad || ''), 'es', {numeric:true, sensitivity:'base'}));
}

function mostrarMaquinas(){
  const serial = $('serialUnidad');
  const q = serial?.value || '';
  const items = maquinasFiltradas(q).map(m => ({
    raw:m,
    main:m.serial_unidad || '',
    sub:[
      m.marca_equipo ? 'Marca: '+m.marca_equipo : '',
      m.modelo_equipo ? 'Modelo: '+m.modelo_equipo : '',
      m.controlador ? 'Ctrl: '+m.controlador : '',
      m.anio_fabricacion ? 'Año: '+m.anio_fabricacion : '',
      m.refrigerante ? 'Ref: '+m.refrigerante : '',
      m.descripcion ? m.descripcion : ''
    ].filter(Boolean).join(' · ') || 'Máquina registrada en panel'
  }));

  renderSmartMenu('maquinaSuggest', items, m => {
    setVal('serialUnidad', m.serial_unidad || '');
    if(m.marca_equipo) setVal('marcaEquipo', m.marca_equipo);
    if(typeof actualizarOpcionesControlador === 'function') actualizarOpcionesControlador(false);
    if(m.modelo_equipo) setVal('modeloEquipo', m.modelo_equipo);
    if(m.controlador) setVal('controladorEquipo', m.controlador);
    if(m.anio_fabricacion) setVal('anioFabricacion', m.anio_fabricacion);
    if(m.refrigerante) setVal('refrigerante', normalizarRefrigeranteCatalogo(m.refrigerante));
    setCatalogHint('maquinaHint','Máquina seleccionada desde el panel. Los datos técnicos fueron completados automáticamente.','ok');
    clearFieldError('serialUnidad');
  });

  if(items.length){
    const msg = normBusca(q) === ''
      ? 'Selecciona una máquina creada en el panel.'
      : `Mostrando ${items.length} máquina(s) coincidente(s).`;
    setCatalogHint('maquinaHint', msg, normBusca(q) === '' ? '' : 'ok');
  } else {
    setCatalogHint('maquinaHint','No hay coincidencias. Se guardará automáticamente al crear la preliminar.', 'warn');
  }
}

let repuestosSeleccionados = [];

function repuestosFiltrados(q){
  const query = normBusca(q || '');
  return (REPUESTOS_CATALOGO || [])
    .filter(r => {
      const codigo = normBusca(r.codigo || '');
      const detalle = normBusca(r.detalle || '');
      const unidad = normBusca(r.unidad || '');
      return query === '' || codigo.includes(query) || detalle.includes(query) || unidad.includes(query);
    })
    .sort((a,b) => {
      const da = normBusca(a.detalle || '');
      const db = normBusca(b.detalle || '');
      if(query){
        const aStarts = da.startsWith(query) ? 0 : 1;
        const bStarts = db.startsWith(query) ? 0 : 1;
        if(aStarts !== bStarts) return aStarts - bStarts;
      }
      return String(a.detalle || '').localeCompare(String(b.detalle || ''), 'es', {numeric:true, sensitivity:'base'});
    });
}

function normalizarDetalleRepuesto(txt){
  return String(txt || '')
    .replace(/\s*\|\s*/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function lineaRepuesto(r, cantidad='1'){
  const codigo = String(r?.codigo || '').trim();
  const detalle = normalizarDetalleRepuesto(r?.detalle || '');
  const cant = String(cantidad || '1').trim() || '1';
  if(!detalle) return '';
  return `${codigo || '-'} | ${detalle} | ${cant}`;
}

function existeRepuestoSeleccionado(detalle, codigo=''){
  const d = normBusca(detalle || '');
  const c = normBusca(codigo || '');
  return repuestosSeleccionados.some(x => {
    const xd = normBusca(x.detalle || '');
    const xc = normBusca(x.codigo || '');
    return (c && xc && c === xc) || (d && xd === d);
  });
}

function syncRepuestosManual(){
  const ta = $('repuestosManual');
  if(!ta) return;
  ta.value = repuestosSeleccionados.map(r => lineaRepuesto(r, r.cantidad || '1')).filter(Boolean).join('\n');
}

function renderRepuestosSeleccionados(){
  const box = $('repuestosSelectedList');
  const empty = $('repuestosEmpty');
  if(!box) return;
  box.innerHTML = '';
  if(empty) empty.classList.toggle('show', repuestosSeleccionados.length === 0);

  repuestosSeleccionados.forEach((r, idx) => {
    const row = document.createElement('div');
    row.className = 'repuesto-selected-item';

    const text = document.createElement('div');
    const name = document.createElement('input');
    name.type = 'text';
    name.className = 'repuesto-selected-name';
    name.value = r.detalle || '';
    name.placeholder = 'Nombre del material';
    name.title = 'Puedes corregir el nombre antes de generar el informe';
    name.addEventListener('input', () => {
      repuestosSeleccionados[idx].detalle = normalizarDetalleRepuesto(name.value || '');
      syncRepuestosManual();
      if(window.zgroupMarcarCambio) window.zgroupMarcarCambio();
    });

    const sub = document.createElement('div');
    sub.className = 'repuesto-selected-sub';
    sub.textContent = r.codigo ? ('Código: ' + r.codigo) : 'Nuevo material.';
    text.append(name, sub);

    const qty = document.createElement('input');
    qty.type = 'text';
    qty.inputMode = 'numeric';
    qty.className = 'repuesto-qty';
    qty.value = r.cantidad || '1';
    qty.title = 'Cantidad';
    qty.addEventListener('input', () => {
      const val = String(qty.value || '').replace(/[^0-9]/g,'').slice(0,4);
      qty.value = val;
      repuestosSeleccionados[idx].cantidad = val;
      syncRepuestosManual();
      if(window.zgroupMarcarCambio) window.zgroupMarcarCambio();
    });
    qty.addEventListener('blur', () => {
      if(!String(qty.value || '').trim()){ qty.value = '1'; repuestosSeleccionados[idx].cantidad = '1'; syncRepuestosManual(); }
    });

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'repuesto-remove';
    remove.textContent = '×';
    remove.title = 'Quitar';
    remove.addEventListener('click', () => {
      repuestosSeleccionados.splice(idx, 1);
      syncRepuestosManual();
      renderRepuestosSeleccionados();
      clearRepuestoError();
      if(window.zgroupMarcarCambio) window.zgroupMarcarCambio();
    });

    row.append(text, qty, remove);
    box.appendChild(row);
  });
  syncRepuestosManual();
}

function agregarRepuestoObjeto(r, cantidad='1', aviso=true){
  const detalle = normalizarDetalleRepuesto(r?.detalle || '');
  const codigo = String(r?.codigo || '').trim();
  if(!detalle) return false;
  if(existeRepuestoSeleccionado(detalle, codigo)){
    if(aviso) toast('Ese material ya está agregado');
    return false;
  }
  repuestosSeleccionados.push({
    codigo,
    detalle,
    unidad: String(r?.unidad || '').trim(),
    cantidad: String(cantidad || '1').replace(/[^0-9]/g,'') || '1',
    nuevo: !codigo
  });
  setRequiereRepuesto(true);
  renderRepuestosSeleccionados();
  clearRepuestoError();
  if(window.zgroupMarcarCambio) window.zgroupMarcarCambio();
  if(aviso) toast('Material agregado: ' + detalle);
  return true;
}

function insertarRepuestoLinea(r){
  agregarRepuestoObjeto(r, '1', true);
  const input = $('repuestoSearch');
  if(input) input.value = '';
  mostrarRepuestos();
}

function agregarRepuestoManual(){
  const input = $('repuestoSearch');
  const detalle = normalizarDetalleRepuesto(input?.value || '');
  if(!detalle){
    if(input) input.focus();
    toast('Escribe el material');
    return false;
  }

  const exacto = (REPUESTOS_CATALOGO || []).find(r => normBusca(r.detalle || '') === normBusca(detalle));
  if(exacto){
    agregarRepuestoObjeto(exacto, '1', true);
  } else {
    agregarRepuestoObjeto({codigo:'', detalle, unidad:''}, '1', true);
  }
  if(input){ input.value = ''; input.focus(); }
  closeSmartMenus();
  mostrarRepuestos();
  return true;
}

function mostrarRepuestos(){
  const input = $('repuestoSearch');
  const q = input?.value || '';
  const items = repuestosFiltrados(q).slice(0, 12).map(r => ({
    raw:r,
    main:String(r.detalle || ''),
    sub:[r.unidad ? 'Unidad: '+r.unidad : ''].filter(Boolean).join(' · ') || 'Registrado en panel'
  }));
  renderSmartMenu('repuestoSuggest', items, r => insertarRepuestoLinea(r));
  const hint = $('repuestoHint');
  if(hint){
    if(normBusca(q) === ''){
      hint.textContent = 'Selecciona “usar” o agrega escrito. Luego ajusta la cantidad en la tabla.';
      hint.classList.remove('warn'); hint.classList.remove('ok');
    } else if(items.length){
      hint.textContent = `Hay ${items.length} opción(es). Puedes seleccionar una o agregar lo escrito.`;
      hint.classList.remove('warn'); hint.classList.add('ok');
    } else {
      hint.textContent = 'No está en la lista. Agrégalo escrito y quedará pendiente para revisión en el panel.';
      hint.classList.remove('ok'); hint.classList.add('warn');
    }
  }
}

function renderRepuestosQuickList(){
  renderRepuestosSeleccionados();
}

function clearRepuestoError(){
  const ta = $('repuestosManual');
  const err = $('repuestosManualError');
  if(ta) ta.classList.remove('input-error');
  if(err){ err.textContent = ''; err.classList.remove('show'); }
}

function setRequiereRepuesto(si){
  const requiere = !!si;
  const hidden = $('requiereRepuesto');
  const card = $('repuestosCard');
  if(hidden) hidden.value = requiere ? 'si' : 'no';
  if(card) card.classList.toggle('is-hidden', !requiere);
  const btnSi = $('repuestoSiBtn');
  const btnNo = $('repuestoNoBtn');
  if(btnSi) btnSi.classList.toggle('on', requiere);
  if(btnNo) btnNo.classList.toggle('on', !requiere);
  if(!requiere){
    repuestosSeleccionados = [];
    try{
      if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function'){
        const lista=window.zgRepuestosTablaFinal.materiales();
        if(Array.isArray(lista)) lista.splice(0, lista.length);
      }
    }catch(e){}
    try{
      Object.values((typeof state!=='undefined' && state.selected) ? state.selected : {}).forEach(function(s){
        if(!s) return;
        ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(function(k){
          if(Array.isArray(s[k])) s[k]=[];
          else if(typeof s[k]==='string') s[k]='';
        });
        if(s.campos && typeof s.campos==='object'){
          ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(function(k){
            if(Array.isArray(s.campos[k])) s.campos[k]=[];
            else if(typeof s.campos[k]==='string') s.campos[k]='';
          });
        }
      });
      window.ZG_WORK_MATERIALS_PDF={};
    }catch(e){}
    const ta=$('repuestosManual'); if(ta) ta.value='';
    syncRepuestosManual();
    renderRepuestosSeleccionados();
    try{ if(typeof renderPanels==='function') renderPanels(); }catch(e){}
  }
  clearRepuestoError();
}

function setupRepuestosControl(){
  const btnSi = $('repuestoSiBtn');
  const btnNo = $('repuestoNoBtn');
  if(btnSi) btnSi.addEventListener('click', () => { setRequiereRepuesto(true); renderRepuestosSeleccionados(); const i=$('repuestoSearch'); if(i) setTimeout(()=>i.focus(), 120); });
  if(btnNo) btnNo.addEventListener('click', () => setRequiereRepuesto(false));
  const input = $('repuestoSearch');
  if(input){
    input.addEventListener('input', () => { mostrarRepuestos(); clearRepuestoError(); });
    input.addEventListener('focus', () => mostrarRepuestos());
    input.addEventListener('keydown', ev => {
      if(ev.key === 'Enter'){
        ev.preventDefault();
        agregarRepuestoManual();
      }
    });
  }
  const add = $('repuestoAddManual');
  if(add) add.addEventListener('click', agregarRepuestoManual);
  renderRepuestosSeleccionados();
  setRequiereRepuesto(false);
}

function validarRepuestos(){
  if(getVal('requiereRepuesto') !== 'si') return true;
  syncRepuestosManual();
  const ta = $('repuestosManual');
  const err = $('repuestosManualError');
  if(ta && ta.value.trim() !== '') return true;
  if(ta) ta.classList.add('input-error');
  if(err){ err.textContent = 'Agrega al menos un material requerido.'; err.classList.add('show'); }
  toast('Agrega el material requerido');
  const card = $('repuestosCard');
  if(card) card.scrollIntoView({behavior:'smooth', block:'center'});
  setTimeout(() => { try{ const input=$('repuestoSearch'); if(input) input.focus(); }catch(e){} }, 250);
  return false;
}

async function registrarRepuestosTecnico(){
  if(getVal('requiereRepuesto') !== 'si') return;
  syncRepuestosManual();
  const txt = getVal('repuestosManual');
  if(!txt.trim()) return;
  try{
    const fd = new FormData();
    fd.append('repuestos', txt);
    const res = await fetch('registrar_repuestos_tecnico.php', {method:'POST', body:fd});
    const out = await res.json().catch(() => null);
    if(!out || !out.ok) console.warn('No se registraron repuestos nuevos', out);
  }catch(e){
    console.warn('No se registraron repuestos nuevos', e);
  }
}

function setupCatalogoCotizacionesClientes(){
  const orden=$('orden');
  if(orden){
    orden.addEventListener('input',()=>{
      const limpio=soloDigitos(orden.value).slice(0,15);
      if(orden.value!==limpio)orden.value=limpio;
      clearFieldError('orden');
      limpiarServicioAutomatico();
      mostrarCotizaciones();
    });
    orden.addEventListener('focus',mostrarCotizaciones);
    orden.addEventListener('keydown',ev=>{
      if(ev.key!=='Enter')return;
      const exacto=servicioPorReporte(orden.value);
      const opciones=cotizacionesFiltradas(orden.value);
      const elegido=exacto || (opciones.length===1 ? opciones[0] : null);
      if(elegido){
        ev.preventDefault();
        aplicarServicioImportado(elegido);
        const menu=$('ordenSuggest');
        if(menu)menu.classList.remove('show');
      }
    });
    orden.addEventListener('blur',()=>{
      setTimeout(()=>{
        const svc=servicioPorReporte(orden.value);
        if(svc)aplicarServicioImportado(svc);
      },140);
    });
  }

  setTimeout(()=>{
    if(typeof PREINSPECCION!=='undefined' && PREINSPECCION)return;
    if(typeof ZG_EDIT_MODE!=='undefined' && ZG_EDIT_MODE)return;
    if(typeof ZG_PRE_EDIT_MODE!=='undefined' && ZG_PRE_EDIT_MODE)return;
    const svc=servicioPorReporte(getVal('orden'));
    if(svc)aplicarServicioImportado(svc);
  },220);

  const equipoNo = $('equipoNo');
  if(equipoNo){
    equipoNo.addEventListener('input', () => {
      clearFieldError('equipoNo');
      mostrarContenedores();
    });
    equipoNo.addEventListener('focus', () => {
      mostrarContenedores();
    });
  }

  const serialUnidad = $('serialUnidad');
  if(serialUnidad){
    serialUnidad.addEventListener('input', () => {
      clearFieldError('serialUnidad');
      mostrarMaquinas();
    });
    serialUnidad.addEventListener('focus', () => {
      mostrarMaquinas();
    });
  }


  document.addEventListener('click', (ev) => {
    if(!ev.target.closest('.smart-ac')) closeSmartMenus();
  });
}


// ---- Validación de campos obligatorios ----
function setupRequiredFields(){
  const ids = [
    'orden','cliente','direccion','tecnicoInput',
    'equipoNo','serialUnidad','controladorEquipo','setPoint','temperaturaAmbiente','retornoAire','suministroAire','presionAlta','presionBaja',
    'voltajeL1L2','voltajeL2L3','voltajeL1L3','estadoInicial','observacionInicial',
    'estadoFinalEquipo','setPointFinal','tempAmbienteFinal','retornoFinal','suministroFinal',
    'voltajeFinalL1L2','voltajeFinalL2L3','voltajeFinalL1L3','zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal','repuestosManual'
  ];
  ids.forEach(id => {
    const el = $(id);
    if(!el) return;
    const evt = el.tagName === 'SELECT' ? 'change' : 'input';
    el.addEventListener(evt, () => clearFieldError(id));
  });

  // Reporte: solo números. Si escriben letras o símbolos, se limpian al instante.
  const orden = $('orden');
  if(orden){
    orden.addEventListener('input', () => {
      const limpio = orden.value.replace(/\D+/g, '').slice(0, 15);
      if(orden.value !== limpio) orden.value = limpio;
    });
  }
}

function setFieldError(id, msg){
  const el = $(id);
  const err = $(id + 'Error');
  if(el) el.classList.add('input-error');
  if(err){ err.textContent = msg; err.classList.add('show'); }
}

function clearFieldError(id){
  const el = $(id);
  const err = $(id + 'Error');
  if(el) el.classList.remove('input-error');
  if(err){ err.textContent = ''; err.classList.remove('show'); }
}

function fieldMsg(id, msg){
  setFieldError(id, msg);
  const el = $(id);
  if(el){
    el.scrollIntoView({behavior:'smooth', block:'center'});
    setTimeout(()=>{ try{ el.focus(); }catch(e){} }, 200);
  }
  toast(msg);
  return false;
}

function soloLetrasNumerosBasico(txt){
  return /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9 .,&()\-\/°#]+$/.test(String(txt || ''));
}

function normalizarDecimalTexto(raw){
  return String(raw || '').trim().replace(',', '.').replace(/\s+/g, ' ');
}

function validarCotizacion(){
  clearFieldError('orden');
  const orden = getVal('orden');
  if(!orden) return fieldMsg('orden', 'Ingresa el N° de reporte.');
  if(!/^\d+$/.test(orden)) return fieldMsg('orden', 'El reporte debe contener solo números.');
  if(orden.length < 6 || orden.length > 15){
    return fieldMsg('orden', 'El reporte parece incorrecto. Debe tener entre 6 y 15 números. Ej. 10020261054.');
  }
  const c = cotizacionExacta(orden);
  if(!c || !c.servicio){
    return fieldMsg('orden','Selecciona un N° de reporte asignado a un ticket desde el panel.');
  }
  aplicarServicioImportado(c.servicio);
  return true;
}

function validarCliente(){
  clearFieldError('cliente');
  const cliente = getVal('cliente');
  if(!cliente) return fieldMsg('cliente', 'Ingresa el nombre del cliente.');
  if(cliente.length < 3) return fieldMsg('cliente', 'El cliente es demasiado corto. Escribe el nombre real del cliente.');
  if(!/[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]/.test(cliente)) return fieldMsg('cliente', 'El cliente debe contener letras, no solo números o símbolos.');
  if(!soloLetrasNumerosBasico(cliente)) return fieldMsg('cliente', 'El cliente contiene símbolos no permitidos. Revisa el texto.');
  if(!clienteExacto(cliente)) return fieldMsg('cliente', 'Selecciona un cliente registrado desde el panel.');
  return true;
}

function validarTecnicoSeleccionado(){
  clearFieldError('tecnicoInput');
  if(!state.tecnicoId){
    return fieldMsg('tecnicoInput', 'Selecciona un técnico registrado de la lista.');
  }
  return true;
}

function validarDireccion(){
  clearFieldError('direccion');
  const direccion=getVal('direccion');
  if(!direccion){
    setFieldError('direccion', 'Elige la dirección/ubicación del servicio en el mapa.');
    toast('Falta elegir la dirección/ubicación');
    $('dirPick').scrollIntoView({ behavior:'smooth', block:'center' });
    return false;
  }
  // Si proviene de Odoo, puede guardarse aunque la geocodificación no haya devuelto coordenadas.
  // Si fue elegida manualmente, direccionCoords se completa desde el mapa.
  return true;
}

function validarTicketOdoo(){
  const ref=getVal('odooTicketRef');
  if(!/^\d{1,15}$/.test(ref)){toast('Selecciona un N° de reporte asignado desde el panel.');return false;}
  return true;
}

function validateGeneralRequired(){
  return validarCotizacion() && validarTicketOdoo() && validarCliente() && validarTecnicoSeleccionado() && validarDireccion();
}

function clearWorkErrors(){
  document.querySelectorAll('#panels .input-error').forEach(el => el.classList.remove('input-error'));
  document.querySelectorAll('#panels .field-error.show').forEach(el => { el.textContent=''; el.classList.remove('show'); });
}

function markWorkError(inputId, msg){
  const el = $(inputId);
  const err = $(inputId + 'Error');
  if(el){
    el.classList.add('input-error');
    el.scrollIntoView({ behavior:'smooth', block:'center' });
    setTimeout(() => { try{ el.focus(); }catch(e){} }, 250);
  }
  if(err){ err.textContent = msg; err.classList.add('show'); }
  toast(msg);
}

function validateWorkSections(sections){
  clearWorkErrors();
  if(window.zgValidarAsignacionTrabajos && !window.zgValidarAsignacionTrabajos(sections)) return false;
  if(!sections || !sections.length){ toast('Selecciona al menos un trabajo realizado'); return false; }

  for(const s of sections){
    const camposLlenos = Object.values(s.campos || {}).some(v => String(v || '').trim() !== '');
    const auto = s.auto || {};
    const autoLleno = ['actividades','hallazgos','acciones','recomendaciones'].some(k => Array.isArray(auto[k]) && auto[k].length)
      || !!(auto.pti && Object.keys(auto.pti).length);
    const tieneAlgo = camposLlenos || autoLleno || String(s.detalle || '').trim() || (s.photos && s.photos.length);
    if(!tieneAlgo){
      markWorkError(`detalle_${s.id}`, `Marca opciones rápidas, agrega una nota o sube una foto en ${s.nombre}`);
      return false;
    }
  }
  return true;
}

// ---- Inspección preliminar: validación y preguardado ----
function getVal(id){ const el=$(id); return el ? el.value.trim() : ''; }
function setVal(id, value){
  const el = $(id);
  if(el) el.value = value == null ? '' : String(value);
}

function zgroupCargarEstadoInicialSelectores(texto){
  const raw = String(texto == null ? '' : texto).trim();
  if(!raw) return false;
  const v = raw.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/\s+/g,' ');

  let funcionamiento = '';
  let suministro = '';
  let alarma = '';

  if(v.includes('apagado') || v.includes('apagada')) funcionamiento = 'Apagado';
  else if(v.includes('encendido') || v.includes('encendida')) funcionamiento = 'Encendido';

  if(v.includes('sin suministro electrico') || v.includes('sin energia')) suministro = 'Sin suministro eléctrico';
  else if(v.includes('con suministro electrico') || v.includes('con energia') || v.includes('suministro electrico') || v.includes('energia')) suministro = 'Con suministro eléctrico';

  if(v.includes('sin alarma')) alarma = 'Sin alarma';
  else if(v.includes('con alarma') || v.includes('alarma')) alarma = 'Con alarma';

  if(funcionamiento) setVal('estadoEncendido', funcionamiento);
  if(suministro) setVal('estadoEnergia', suministro);
  if(alarma) setVal('estadoAlarma', alarma);

  if(funcionamiento && suministro && alarma){
    setVal('estadoInicial', funcionamiento + ' / ' + suministro + ' / ' + alarma);
    return true;
  }
  setVal('estadoInicial', raw);
  return false;
}
function lockField(id){
  const el = $(id);
  if(!el) return;
  if(el.tagName === 'SELECT') el.disabled = true;
  else el.readOnly = true;
  el.style.background = '#eef3f8';
}
function applyPreinspeccionContinuacion(){
  if(!PREINSPECCION) return;

  const p = PREINSPECCION;

  // Datos generales: se autocompletan desde el registro preliminar.
  setVal('orden', p.cotizacion || '');
  setVal('odooTicketRef', p.odoo_ticket_ref || '');
  setVal('odooTicketRefDisplay', p.odoo_ticket_ref || '');
  setVal('cliente', p.cliente || '');
  setVal('direccion', p.ubicacion_texto || '');
  if(p.latitud && p.longitud) setVal('direccionCoords', `${p.latitud}, ${p.longitud}`);
  if(p.creado_en && !getVal('fecha')) setVal('fecha', String(p.creado_en).slice(0,10));

  // Técnico seleccionado.
  state.tecnicoId = String(p.tecnico_id || '');
  state.tecnicoNombre = p.tecnico_nombre || '';
  setVal('tecnicoId', state.tecnicoId);
  if($('tecnicoInput')) {
    $('tecnicoInput').value = state.tecnicoId;
    $('tecnicoInput').classList.add('ok');
    if(window.zgTecnicoSetById) window.zgTecnicoSetById(state.tecnicoId);
  }

  // Datos de inspección preliminar.
  setVal('preinspeccionId', p.id || '');
  setVal('zgModalidadComercial', p.modalidad_comercial || '');
  setVal('zgTipoInstalacion', p.tipo_instalacion || '');
  setVal('zgTipoEquipo', p.tipo_equipo || (/^SG[- ]?(3000|5000)$/i.test(String(p.controlador || '')) || String(p.genset_horometro_inicial ?? '').trim() !== '' ? 'Genset' : 'Reefer'));
  setVal('zgTamanoContenedor', p.tamano_contenedor || (String(p.tipo_equipo || '')==='Genset' ? 'No aplica' : ''));
  setVal('equipoNo', p.numero_equipo || '');
  setVal('serialUnidad', p.serie_unidad || '');
  setVal('marcaEquipo', p.marca_equipo || '');
  setVal('modeloEquipo', p.modelo_equipo || '');
  setVal('controladorEquipo', p.controlador || '');
  setVal('anioFabricacion', p.anio_fabricacion || '');
  setVal('refrigerante', p.refrigerante || '');
  setVal('setPoint', p.set_point ?? '');
  setVal('temperaturaAmbiente', p.temperatura_ambiente ?? '');
  setVal('retornoAire', p.retorno_aire ?? '');
  setVal('suministroAire', p.suministro_aire ?? '');
  setVal('presionAlta', p.presion_alta ?? p.presionAlta ?? '');
  setVal('presionBaja', p.presion_baja ?? p.presionBaja ?? '');
  setVal('voltajeL1L2', p.voltaje_l1_l2 || '');
  setVal('voltajeL2L3', p.voltaje_l2_l3 || '');
  setVal('voltajeL1L3', p.voltaje_l1_l3 || '');
  setVal('estadoInicial', p.estado_inicial || '');
  zgroupCargarEstadoInicialSelectores(p.estado_inicial || '');
  setVal('alarmaEncontrada', p.alarma_encontrada || '');
  setVal('gensetHorometroInicial', p.genset_horometro_inicial || '');
  setVal('gensetVoltajeBateriaInicial', p.genset_voltaje_bateria_inicial || '');
  setVal('gensetNivelCombustibleInicial', p.genset_nivel_combustible_inicial || '');
  setVal('gensetNivelAceiteInicial', p.genset_nivel_aceite_inicial || '');
  setVal('gensetRefrigeranteMotorInicial', p.genset_refrigerante_motor_inicial || '');
  setVal('gensetArranqueInicial', p.genset_arranque_inicial || '');
  setVal('gensetFrecuenciaInicial', p.genset_frecuencia_inicial || '');
  setVal('gensetPresionAceiteInicial', p.genset_presion_aceite_inicial || '');
  setVal('observacionInicial', window.zgStripMetaFromText ? window.zgStripMetaFromText(p.observacion_inicial || '') : String(p.observacion_inicial || '').replace(/\s*\[\[ZG_META:[A-Za-z0-9+\/=_-]+\]\]\s*/g, ' ').trim());
  try{
    const evRaw = p.evidencias_json || p.evidencias_preliminares_json || '';
    const evArr = typeof evRaw === 'string' ? JSON.parse(evRaw || '[]') : evRaw;
    if(Array.isArray(evArr) && evArr.length){
      window.ZG_PRE_EVIDENCIAS = evArr;
      window.__zgEvidenceLoadedFromServer = true;
    }
  }catch(e){}
  try{ setTimeout(function(){ document.getElementById('zgTipoEquipo')?.dispatchEvent(new Event('change',{bubbles:true})); },0); }catch(e){}

  const adminEditando = (typeof ZG_EDIT_MODE !== 'undefined' && ZG_EDIT_MODE) || (typeof ZG_PRE_EDIT_MODE !== 'undefined' && ZG_PRE_EDIT_MODE);
  const btn = $('preBtn');
  const st = $('preStatus');

  if(!adminEditando){
    // En la continuación normal se protege la preliminar para evitar cambios accidentales.
    [
      'orden','cliente','direccion','tecnicoInput',
      'zgModalidadComercial','zgTipoInstalacion','zgTipoEquipo','zgTamanoContenedor',
      'equipoNo','serialUnidad','marcaEquipo','modeloEquipo','controladorEquipo','anioFabricacion','refrigerante',
      'setPoint','temperaturaAmbiente','retornoAire','suministroAire','presionAlta','presionBaja',
      'voltajeL1L2','voltajeL2L3','voltajeL1L3','estadoInicial','estadoEncendido','estadoEnergia','estadoAlarma','alarmaEncontrada',
      'gensetHorometroInicial','gensetVoltajeBateriaInicial','gensetNivelCombustibleInicial','gensetNivelAceiteInicial',
      'gensetRefrigeranteMotorInicial','gensetArranqueInicial','gensetFrecuenciaInicial','gensetPresionAceiteInicial',
      'observacionInicial'
    ].forEach(lockField);

    if($('dirPick')) $('dirPick').style.pointerEvents = 'none';
    if(btn){
      btn.disabled = true;
      btn.textContent = 'Inspección preliminar ya guardada';
      btn.style.opacity = '.75';
    }
    if(st){
      st.textContent = 'Servicio abierto · completa el informe final';
      st.style.color = '#155293';
    }
    if($('pdfBtn')) $('pdfBtn').innerHTML = `
      <svg viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
      Generar informe final
    `;
    toast('Servicio iniciado cargado. Completa solo el cierre del trabajo.');
  }else{
    // Desde supervisión todos los datos preliminares permanecen editables.
    document.querySelectorAll('#datosGeneralesCard input, #datosGeneralesCard select, #datosGeneralesCard textarea').forEach(function(el){
      if(el.type !== 'hidden'){
        el.readOnly = false;
        el.disabled = false;
        el.style.background = '';
      }
    });
    if($('dirPick')) $('dirPick').style.pointerEvents = '';
    if(typeof ZG_PRE_EDIT_MODE !== 'undefined' && ZG_PRE_EDIT_MODE && btn){
      btn.disabled = false;
      btn.textContent = 'Guardar cambios de preliminar';
      btn.style.opacity = '1';
    }
    if(st){
      st.textContent = (typeof ZG_PRE_EDIT_MODE !== 'undefined' && ZG_PRE_EDIT_MODE)
        ? 'Edición administrativa habilitada'
        : 'Datos preliminares habilitados para corrección';
      st.style.color = '#155293';
    }
  }
}


function separarTextoTrabajosPrevistos(txt){
  return String(txt || '')
    .split(/\s*(?:\||\n|,)\s*/u)
    .map(x => x.trim())
    .filter(Boolean)
    .filter(x => !/servicio tecnico pendiente/i.test(normaliza(x)));
}

function buscarWorkPorNombre(nombre){
  const n = normaliza(nombre);
  if(!n) return null;

  // 1) coincidencia exacta normalizada
  let w = WORK_TYPES.find(x => normaliza(x.nombre) === n);
  if(w) return w;

  // 2) coincidencia por inclusión
  w = WORK_TYPES.find(x => normaliza(x.nombre).includes(n) || n.includes(normaliza(x.nombre)));
  if(w) return w;

  // 3) palabras clave comunes para que el técnico no tenga que buscar otra vez
  if(n.includes('luminaria')) return WORK_TYPES.find(x => x.id === 'instalacion_luminarias') || null;
  if(n.includes('deshielo') || n.includes('descongel')) return WORK_TYPES.find(x => x.id === 'deshielo_contenedor') || null;
  if(n.includes('reefer') && n.includes('instal')) return WORK_TYPES.find(x => x.id === 'instalacion_reefer') || null;
  if(n.includes('reparacion') && n.includes('reefer')) return WORK_TYPES.find(x => x.id === 'reparacion_reefer') || null;
  if(n.includes('revision')) return WORK_TYPES.find(x => x.id === 'revision_tecnica') || null;
  if(n.includes('sistema electrico') || n.includes('electrico')) return WORK_TYPES.find(x => x.id === 'trabajos_sistema_electrico') || null;
  if(n.includes('instalacion')) return WORK_TYPES.find(x => x.id === 'instalacion') || null;
  if(n.includes('asistencia')) return WORK_TYPES.find(x => x.id === 'asistencia_tecnica') || null;

  return null;
}

function crearSeleccionTrabajo(w){
  const s = { id:w.id, nombre:w.nombre, custom:!!w.custom, campos:{}, detalle:'', photos:[], auto:emptyAuto(), maquinaAsignada:'', repuestosTrabajo:[], mantenimientoAdicional:{requiere:'',tipo:''} };
  // Para facilitar el trabajo en campo, cada servicio inicia con sugerencias marcadas.
  // El técnico puede desmarcar o cambiar lo que no corresponda antes de generar el PDF.
  aplicarPresetSilencioso(s, presetSugeridoPorTrabajo(w.nombre));
  return s;
}

function asegurarTrabajoSeleccionado(nombre){
  const w = buscarWorkPorNombre(nombre);
  if(w){
    if(!state.selected[w.id]){
      state.selected[w.id] = crearSeleccionTrabajo(w);
    }
    return state.selected[w.id];
  }

  const nombreLimpio = String(nombre || '').trim().toUpperCase();
  if(!nombreLimpio) return null;
  const ya = Object.values(state.selected).find(x => normaliza(x.nombre) === normaliza(nombreLimpio));
  if(ya) return ya;

  state.customSeq++;
  const id = 'pre_' + state.customSeq;
  state.selected[id] = crearSeleccionTrabajo({ id, nombre:nombreLimpio, custom:true });
  return state.selected[id];
}

function cargarTrabajoPrevistoDesdePreinspeccion(){
  if(!PREINSPECCION) return;

  const trabajos = separarTextoTrabajosPrevistos(PREINSPECCION.trabajo || '');
  trabajos.forEach(asegurarTrabajoSeleccionado);

  // Si la preliminar no trae trabajo previsto válido, dejamos que el técnico elija manualmente.
  renderWorkCards();
  renderPanels();
  updateCounter();
}

function presetSugeridoPorTrabajo(nombre){
  const n = normaliza(nombre);
  if(n.includes('productivo')) return 'Mantenimiento productivo';
  if(n.includes('preventivo') || n.includes('mantenimiento preventivo')) return 'Mantenimiento preventivo';
  if(n.includes('correctivo') || n.includes('reparacion') || n.includes('falla')) return 'Mantenimiento correctivo';
  if(n.includes('pti') || n.includes('run test') || n.includes('prueba')) return 'PTI / Run test';
  if(n.includes('deshielo') || n.includes('descongel')) return 'Revisión técnica';
  if(n.includes('instalacion') || n.includes('luminaria') || n.includes('ingreso')) return 'Revisión técnica';
  if(n.includes('revision') || n.includes('asistencia') || n.includes('diagnostico')) return 'Revisión técnica';
  return 'Revisión técnica';
}

function aplicarPresetSilencioso(s, nombrePlantilla){
  const nombre = nombrePlantilla || presetSugeridoPorTrabajo(s.nombre || '');
  const preset = PRESETS[nombre] || PRESETS['Revisión técnica'];

  // Reinicia y copia arrays reales para que las pastillas queden marcadas siempre,
  // incluso cuando el servicio viene desde una inspección preliminar.
  s.auto = emptyAuto();
  s.auto.plantilla = nombre;

  ['actividades','hallazgos','acciones','recomendaciones'].forEach(k => {
    s.auto[k] = Array.isArray(preset[k]) ? preset[k].slice() : [];
  });

  // Si el técnico no escribió una nota manual, se arma una observación base.
  s.detalle = generarTextoAutomaticoTrabajo(s, nombre);
}

function validarNumeroRango(id, min, max, nombre, unidad, requerido=false){
  const el = $(id);
  if(!el) return true;
  clearFieldError(id);
  const raw = normalizarDecimalTexto(el.value);
  if(raw === ''){
    return requerido ? fieldMsg(id, `${nombre} es obligatorio.`) : true;
  }
  if(!/^-?\d+(\.\d+)?$/.test(raw)){
    return fieldMsg(id, `${nombre} debe ser numérico. No escribas letras ni símbolos.`);
  }
  const val = Number(raw);
  if(!Number.isFinite(val) || val < min || val > max){
    return fieldMsg(id, `${nombre} parece incoherente. Debe estar entre ${min} y ${max}${unidad || ''}.`);
  }
  el.value = raw;
  return true;
}

function validarTemp(id, min, max, nombre, requerido=false){
  return validarNumeroRango(id, min, max, nombre, ' °C', requerido);
}

function validarVoltajeCampo(id, nombre, requerido=false){
  const el = $(id);
  if(!el) return true;
  clearFieldError(id);
  let raw = normalizarDecimalTexto(el.value);
  if(raw === '') return requerido ? fieldMsg(id, `${nombre} es obligatorio.`) : true;

  // Permitimos escribir 220, 220.5 o 220 V, pero no letras sueltas, @, comas raras ni negativos.
  const limpio = raw.replace(/\s*v$/i, '').trim();
  if(!/^\d+(\.\d+)?$/.test(limpio)){
    return fieldMsg(id, `${nombre} debe ser un voltaje válido. Ej. 220 o 480. No uses letras, @ ni valores negativos.`);
  }
  const val = Number(limpio);
  if(!Number.isFinite(val) || val <= 0 || val > 600){
    return fieldMsg(id, `${nombre} parece incoherente. Debe ser mayor a 0 y menor o igual a 600 V.`);
  }
  el.value = limpio;
  return true;
}

function validarTextoCampo(id, nombre, min=2, max=80, requerido=false, patron=/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9 .,_()\-\/°#]+$/){
  const el = $(id);
  if(!el) return true;
  clearFieldError(id);
  const raw = getVal(id);
  if(raw === '') return requerido ? fieldMsg(id, `${nombre} es obligatorio.`) : true;
  if(raw.length < min) return fieldMsg(id, `${nombre} es demasiado corto. Revisa el dato.`);
  if(raw.length > max) return fieldMsg(id, `${nombre} es demasiado largo. Revisa el dato.`);
  if(!/[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9]/.test(raw)) return fieldMsg(id, `${nombre} no puede contener solo símbolos.`);
  if(!patron.test(raw)) return fieldMsg(id, `${nombre} contiene símbolos no permitidos.`);
  return true;
}

function zgroupEstadoInicialCompuesto(){
  const a = $('estadoEncendido') ? getVal('estadoEncendido') : '';
  const b = $('estadoEnergia') ? getVal('estadoEnergia') : '';
  const c = $('estadoAlarma') ? getVal('estadoAlarma') : '';
  const h = $('estadoInicial');
  if(a && b && c){
    const v = a + ' / ' + b + ' / ' + c;
    if(h) h.value = v;
    return v;
  }
  if(h && getVal('estadoInicial')) return getVal('estadoInicial');
  return '';
}

function validarEstadoInicialTriple(){
  const a = $('estadoEncendido'), b = $('estadoEnergia'), c = $('estadoAlarma');
  if(a || b || c){
    if(!getVal('estadoEncendido')) return fieldMsg('estadoEncendido', 'Selecciona si el equipo estaba encendido o apagado.');
    if(!getVal('estadoEnergia')) return fieldMsg('estadoEnergia', 'Selecciona si el equipo tenía suministro eléctrico o no.');
    if(!getVal('estadoAlarma')) return fieldMsg('estadoAlarma', 'Selecciona si el equipo tenía alarma o no.');
    zgroupEstadoInicialCompuesto();
    return true;
  }
  return !!getVal('estadoInicial') || fieldMsg('estadoInicial', 'Selecciona cómo se encontró el equipo.');
}

function validarInspeccionPreliminar(){
  if(window.zgValidarConfiguracionServicio && !window.zgValidarConfiguracionServicio()) return false;
  zgroupEstadoInicialCompuesto();
  const okTexto =
    validarTextoCampo('equipoNo', 'Contenedor / equipo', 3, 60, true, /^[A-Za-z0-9\-_.\/]+$/) &&
    validarTextoCampo('serialUnidad', 'Serial unidad', 3, 80, true, /^[A-Za-z0-9\-_.\/]+$/) &&
    validarTextoCampo('controladorEquipo', 'Controlador', 2, 60, false);
  if(!okTexto) return false;

  if(getVal('modeloEquipo')) validarTextoCampo('modeloEquipo', 'Modelo', 2, 100, false);
  const anioReefer = getVal('anioFabricacion');
  if(anioReefer && !/^(19|20)\d{2}$/.test(anioReefer)) return fieldMsg('anioFabricacion', 'Ingresa un año de fabricación válido con 4 dígitos.');
  if(!getVal('marcaEquipo')) return fieldMsg('marcaEquipo', 'Selecciona la marca del equipo.');
  if(!validarEstadoInicialTriple()) return false;

  const okTemp = validarTemp('setPoint', -35, 30, 'Set point') &&
                 validarTemp('temperaturaAmbiente', -10, 60, 'Temperatura ambiente') &&
                 validarTemp('retornoAire', -40, 60, 'Retorno de aire') &&
                 validarTemp('suministroAire', -50, 60, 'Suministro de aire');
  if(!okTemp) return false;

  return validarVoltajeCampo('voltajeL1L2', 'Voltaje L1-L2') &&
         validarVoltajeCampo('voltajeL2L3', 'Voltaje L2-L3') &&
         validarVoltajeCampo('voltajeL1L3', 'Voltaje L1-L3') &&
         validarTextoCampo('observacionInicial', 'Observación inicial', 3, 600, false, /^[\s\S]+$/);
}

function setupControlFinal(){
  ['setPointFinal','tempAmbienteFinal','retornoFinal','suministroFinal','presionAltaFinal','presionBajaFinal',
   'voltajeFinalL1L2','voltajeFinalL2L3','voltajeFinalL1L3','estadoFinalEquipo',
   'zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento'].forEach(id=>{
    const el=$(id);
    if(el){
      const evt = el.tagName === 'SELECT' ? 'change' : 'input';
      el.addEventListener(evt, ()=>clearFieldError(id));
    }
  });
}

function validarControlFinal(){
  if(!getVal('estadoFinalEquipo')) return fieldMsg('estadoFinalEquipo', 'Selecciona el estado final del equipo.');

  const okTemp = validarTemp('setPointFinal', -35, 30, 'Set point final') &&
                 validarTemp('tempAmbienteFinal', -10, 60, 'Temperatura ambiente final') &&
                 validarTemp('retornoFinal', -40, 60, 'Retorno de aire final') &&
                 validarTemp('suministroFinal', -50, 60, 'Suministro de aire final');
  if(!okTemp) return false;

  const okVolt = validarVoltajeCampo('voltajeFinalL1L2', 'Voltaje final L1-L2') &&
                 validarVoltajeCampo('voltajeFinalL2L3', 'Voltaje final L2-L3') &&
                 validarVoltajeCampo('voltajeFinalL1L3', 'Voltaje final L1-L3');
  if(!okVolt) return false;

  const requiere = getVal('zgRequiereOtroMantenimiento');
  if(!requiere) return fieldMsg('zgRequiereOtroMantenimiento', 'Indica si el equipo requiere otro mantenimiento.');

  if(requiere === 'Sí'){
    if(!getVal('zgTipoOtroMantenimiento')) return fieldMsg('zgTipoOtroMantenimiento', 'Selecciona el tipo de mantenimiento requerido.');
    if(!validarTextoCampo('zgMotivoOtroMantenimiento', 'Razón del mantenimiento requerido', 10, 700, true, /^[\s\S]+$/)) return false;
  }
  return true;
}

function datosControlFinal(){
  return {
    estadoFinalEquipo:getVal('estadoFinalEquipo'),
    setPointFinal:getVal('setPointFinal'),
    tempAmbienteFinal:getVal('tempAmbienteFinal'),
    presionAltaFinal:getVal('presionAltaFinal'),
    presionBajaFinal:getVal('presionBajaFinal'),
    retornoFinal:getVal('retornoFinal'),
    suministroFinal:getVal('suministroFinal'),
    voltajeFinalL1L2:getVal('voltajeFinalL1L2'),
    voltajeFinalL2L3:getVal('voltajeFinalL2L3'),
    voltajeFinalL1L3:getVal('voltajeFinalL1L3'),
    requiereOtroMantenimiento:getVal('zgRequiereOtroMantenimiento'),
    tipoOtroMantenimiento:getVal('zgTipoOtroMantenimiento'),
    motivoOtroMantenimiento:getVal('zgMotivoOtroMantenimiento')
  };
}

function datosPreinspeccion(){
  return {
    modalidadComercial:getVal('zgModalidadComercial'),
    tipoInstalacion:getVal('zgTipoInstalacion'),
    tipoEquipo:getVal('zgTipoEquipo'),
    tamanoContenedor:getVal('zgTamanoContenedor'),
    equipoNo:getVal('equipoNo'), serialUnidad:getVal('serialUnidad'), marcaEquipo:getVal('marcaEquipo'),
    modeloEquipo:getVal('modeloEquipo'), controladorEquipo:getVal('controladorEquipo'), anioFabricacion:getVal('anioFabricacion'),
    refrigerante:getVal('refrigerante'), setPoint:getVal('setPoint'),
    temperaturaAmbiente:getVal('temperaturaAmbiente'), retornoAire:getVal('retornoAire'), suministroAire:getVal('suministroAire'),
    presionAlta:getVal('presionAlta'), presionBaja:getVal('presionBaja'),
    voltajeL1L2:getVal('voltajeL1L2'), voltajeL2L3:getVal('voltajeL2L3'), voltajeL1L3:getVal('voltajeL1L3'),
    alarmaEncontrada:getVal('alarmaEncontrada'),
    gensetHorometroInicial:getVal('gensetHorometroInicial'),
    gensetVoltajeBateriaInicial:getVal('gensetVoltajeBateriaInicial'),
    gensetNivelCombustibleInicial:getVal('gensetNivelCombustibleInicial'),
    gensetNivelAceiteInicial:getVal('gensetNivelAceiteInicial'),
    gensetRefrigeranteMotorInicial:getVal('gensetRefrigeranteMotorInicial'),
    gensetArranqueInicial:getVal('gensetArranqueInicial'),
    gensetFrecuenciaInicial:getVal('gensetFrecuenciaInicial'),
    gensetPresionAceiteInicial:getVal('gensetPresionAceiteInicial'),
    estadoInicial:(typeof zgroupEstadoInicialCompuesto==='function'?zgroupEstadoInicialCompuesto():getVal('estadoInicial')),
    observacionInicial:(window.zgStripMetaFromText?window.zgStripMetaFromText(getVal('observacionInicial')):getVal('observacionInicial'))
  };
}

function setupPreinspeccion(){
  ['setPoint','temperaturaAmbiente','retornoAire','suministroAire','presionAlta','presionBaja'].forEach(id=>{
    const el=$(id); if(el) el.addEventListener('input', ()=>el.classList.remove('input-error'));
  });
  const btn=$('preBtn');
  if(btn) btn.addEventListener('click', guardarPreinspeccion);
}

async function guardarPreinspeccion(){
  if(!validateGeneralRequired()) return;
  if(!state.tecnicoId){ toast('Primero elige tu nombre de la lista'); ($('tecnicoSearch') || $('tecnicoInput')).focus(); return; }
  if(!validarInspeccionPreliminar()) return;

  const p = datosPreinspeccion();
  const tieneDatos = Object.values(p).some(v => String(v || '').trim() !== '');
  if(!tieneDatos){ toast('Llena al menos un dato de inspección preliminar'); return; }

  const fd = new FormData();
  // V44: la decisión de CREAR o EDITAR se toma únicamente desde la URL actual.
  // No se usan valores restaurados del navegador, localStorage ni el campo oculto,
  // porque podrían contener el ID de un servicio anterior.
  const zgPreParams = new URLSearchParams(window.location.search || '');
  const zgPreModoUrl = String(zgPreParams.get('modo') || '').trim().toLowerCase();
  const zgPreIdUrl = Number.parseInt(String(zgPreParams.get('id') || '0'), 10) || 0;
  const isPreEditRequest = (zgPreModoUrl === 'editar_preliminar' && zgPreIdUrl > 0);
  const preEditIdActual = isPreEditRequest ? zgPreIdUrl : 0;

  fd.append('accion_preliminar', isPreEditRequest ? 'actualizar' : 'crear');
  if(isPreEditRequest){
    fd.append('preinspeccion_id', String(preEditIdActual));
  }else{
    // En un servicio nuevo se descarta cualquier ID antiguo restaurado por el navegador.
    if($('preinspeccionId')) $('preinspeccionId').value = '';
    try{
      localStorage.removeItem('zgroup_preinspeccion_id');
      localStorage.removeItem('zgroup_preinspeccion_token');
    }catch(e){}
  }
  fd.append('tecnico_id', state.tecnicoId);
  fd.append('cliente', getVal('cliente'));
  fd.append('cotizacion', getVal('orden'));
  fd.append('odoo_ticket_ref', getVal('odooTicketRef'));
  fd.append('odoo_cotizacion', getVal('odooCotizacion'));

  const trabajosPrevistos = Object.values(state.selected).map(s=>s.nombre).filter(Boolean).join(' | ');
  fd.append('trabajo', trabajosPrevistos || 'Servicio técnico pendiente de cierre');
  fd.append('modalidad_comercial', p.modalidadComercial || '');
  fd.append('tipo_instalacion', p.tipoInstalacion || '');
  fd.append('tipo_equipo', p.tipoEquipo || 'Reefer');
  fd.append('tamano_contenedor', p.tipoEquipo === 'Genset' ? 'No aplica' : (p.tamanoContenedor || ''));
  fd.append('alarma_encontrada', p.alarmaEncontrada || '');
  fd.append('genset_horometro_inicial', p.gensetHorometroInicial || '');
  fd.append('genset_voltaje_bateria_inicial', p.gensetVoltajeBateriaInicial || '');
  fd.append('genset_nivel_combustible_inicial', p.gensetNivelCombustibleInicial || '');
  fd.append('genset_nivel_aceite_inicial', p.gensetNivelAceiteInicial || '');
  fd.append('genset_refrigerante_motor_inicial', p.gensetRefrigeranteMotorInicial || '');
  fd.append('genset_arranque_inicial', p.gensetArranqueInicial || '');
  fd.append('genset_frecuencia_inicial', p.gensetFrecuenciaInicial || '');
  fd.append('genset_presion_aceite_inicial', p.gensetPresionAceiteInicial || '');

  fd.append('numero_equipo', p.equipoNo);
  fd.append('serie_unidad', p.serialUnidad);
  fd.append('marca_equipo', p.marcaEquipo);
  fd.append('modelo_equipo', p.tipoEquipo === 'Genset' ? '' : (p.modeloEquipo || ''));
  fd.append('controlador', p.controladorEquipo);
  fd.append('anio_fabricacion', p.tipoEquipo === 'Genset' ? '' : (p.anioFabricacion || ''));
  fd.append('refrigerante', p.refrigerante);
  fd.append('set_point', p.setPoint);
  fd.append('temperatura_ambiente', p.temperaturaAmbiente);
  fd.append('retorno_aire', p.retornoAire);
  fd.append('suministro_aire', p.suministroAire);
  fd.append('presion_alta', p.presionAlta || '');
  fd.append('presion_baja', p.presionBaja || '');
  fd.append('voltaje_l1_l2', p.voltajeL1L2);
  fd.append('voltaje_l2_l3', p.voltajeL2L3);
  fd.append('voltaje_l1_l3', p.voltajeL1L3);
  fd.append('estado_inicial', (typeof zgroupEstadoInicialCompuesto==='function'?zgroupEstadoInicialCompuesto():p.estadoInicial));
  fd.append('observacion_inicial', window.zgObservacionInicialConMeta ? window.zgObservacionInicialConMeta(p.observacionInicial) : p.observacionInicial);
  fd.append('ubicacion_texto', getVal('direccion'));
  try{
    fd.append('evidencias_preliminares_json', JSON.stringify(Array.isArray(window.ZG_PRE_EVIDENCIAS) ? window.ZG_PRE_EVIDENCIAS : []));
  }catch(e){ fd.append('evidencias_preliminares_json', '[]'); }

  const coords = getVal('direccionCoords');
  if(coords.includes(',')){
    const parts = coords.split(',').map(x=>x.trim());
    fd.append('latitud', parts[0] || '');
    fd.append('longitud', parts[1] || '');
  }

  const btn=$('preBtn'), st=$('preStatus');
  try{
    if(btn) btn.disabled = true;
    if(st) st.textContent = isPreEditRequest ? 'Guardando cambios de la inspección preliminar...' : 'Guardando inspección preliminar e iniciando servicio...';

    const endpointPreliminar = isPreEditRequest ? 'actualizar_preinspeccion.php?v=44' : 'guardar_preinspeccion.php?v=44';
    const res = await fetch(endpointPreliminar, {method:'POST', body:fd});
    const out = await res.json();

    if(!out.ok) throw new Error(out.error || 'No se pudo guardar la inspección preliminar');

    if($('preinspeccionId')) $('preinspeccionId').value = out.pre_id || '';
    if($('tokenContinuacion')) $('tokenContinuacion').value = out.token || '';

    if(out.token) localStorage.setItem('zgroup_preinspeccion_token', out.token);
    if(out.pre_id) localStorage.setItem('zgroup_preinspeccion_id', out.pre_id);
    if(st) st.textContent = isPreEditRequest ? 'Cambios de la preliminar guardados correctamente' : 'Inspección preliminar guardada · servicio iniciado';
    if(window.zgroupMarcarGuardado) window.zgroupMarcarGuardado();

    if(isPreEditRequest){
      toast('Inspección preliminar actualizada. Ahora continúa con el servicio.');
      if(btn){ btn.disabled = false; btn.textContent = 'Actualizar preliminar y continuar servicio'; }
      const destino = out.continuar_url || (out.token ? ('index.php?token=' + encodeURIComponent(out.token)) : 'panel.php');
      const separador = destino.includes('?') ? '&' : '?';
      setTimeout(() => { window.location.href = destino + separador + 'desde_edicion=1'; }, 650);
      return;
    }

    toast('Preliminar guardada. Se abrirá el modo de cierre del informe.');

    // Redirige al enlace de continuación para que el técnico pueda cerrar y volver luego.
    if(out.continuar_url){
      setTimeout(() => { window.location.href = out.continuar_url; }, 850);
    }

  }catch(err){
    if(st) st.textContent = 'No se pudo guardar. Revisa conexión o PHP.';
    alert('Error en inspección preliminar: ' + err.message);
    if(btn) btn.disabled = false;
  }
}

// ---- Buscador de técnico (búsqueda instantánea) ----
function setupTecnico(){
  const select = $('tecnicoInput');
  const input = $('tecnicoSearch');
  const menu = $('tecnicoSuggest');
  const hidden = $('tecnicoId');
  if(!select || !input || !menu) return;

  const tecnicos = Array.from(select.options)
    .filter(opt => String(opt.value || '').trim() !== '')
    .map(opt => ({ id:String(opt.value), nombre:String(opt.textContent || '').trim(), option:opt }));

  let activeIndex = -1;
  let visible = [];

  function normalizar(v){
    return String(v || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/\s+/g,' ').trim();
  }

  function cerrar(){
    menu.classList.remove('show');
    menu.innerHTML = '';
    activeIndex = -1;
    visible = [];
  }

  function limpiarSeleccion(){
    select.value = '';
    state.tecnicoId = '';
    state.tecnicoNombre = '';
    if(hidden) hidden.value = '';
    input.classList.remove('ok');
    select.classList.remove('ok');
    actualizarNombreFirmaTecnico();
  }

  function seleccionar(item, cerrarMenu = true){
    if(!item) return;
    select.value = item.id;
    state.tecnicoId = item.id;
    state.tecnicoNombre = item.nombre;
    if(hidden) hidden.value = item.id;
    input.value = item.nombre;
    input.classList.add('ok');
    select.classList.add('ok');
    clearFieldError('tecnicoInput');
    if(cerrarMenu) cerrar();
    select.dispatchEvent(new Event('change', {bubbles:true}));
    actualizarNombreFirmaTecnico();
    try{ if(window.zgroupMarcarCambio) window.zgroupMarcarCambio(); }catch(e){}
  }

  function render(filtro){
    const q = normalizar(filtro);
    visible = tecnicos.filter(t => !q || normalizar(t.nombre).includes(q));
    menu.innerHTML = '';
    activeIndex = -1;

    if(!visible.length){
      const empty = document.createElement('div');
      empty.className = 'ac-empty';
      empty.textContent = 'Sin coincidencias';
      menu.appendChild(empty);
      menu.classList.add('show');
      return;
    }

    visible.forEach((tec, index) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ac-item';
      btn.setAttribute('role','option');
      btn.textContent = tec.nombre;
      btn.addEventListener('mousedown', ev => ev.preventDefault());
      btn.addEventListener('click', () => seleccionar(tec));
      menu.appendChild(btn);
    });
    menu.classList.add('show');
  }

  function marcarActivo(nuevo){
    const items = Array.from(menu.querySelectorAll('.ac-item'));
    if(!items.length) return;
    activeIndex = Math.max(0, Math.min(nuevo, items.length - 1));
    items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
    items[activeIndex].scrollIntoView({block:'nearest'});
  }

  function pickById(id){
    const item = tecnicos.find(t => t.id === String(id || ''));
    if(item) seleccionar(item);
    else limpiarSeleccion();
  }

  input.addEventListener('focus', () => render(input.value));
  input.addEventListener('click', () => render(input.value));
  input.addEventListener('input', () => {
    const actual = tecnicos.find(t => t.id === String(state.tecnicoId || ''));
    if(!actual || normalizar(input.value) !== normalizar(actual.nombre)) limpiarSeleccion();
    render(input.value);
  });
  input.addEventListener('keydown', ev => {
    if(ev.key === 'ArrowDown'){
      ev.preventDefault();
      if(!menu.classList.contains('show')) render(input.value);
      marcarActivo(activeIndex + 1);
    }else if(ev.key === 'ArrowUp'){
      ev.preventDefault();
      marcarActivo(activeIndex <= 0 ? visible.length - 1 : activeIndex - 1);
    }else if(ev.key === 'Enter'){
      if(menu.classList.contains('show') && visible.length){
        ev.preventDefault();
        seleccionar(visible[activeIndex >= 0 ? activeIndex : 0]);
      }
    }else if(ev.key === 'Escape'){
      cerrar();
    }
  });
  input.addEventListener('blur', () => setTimeout(cerrar, 140));

  select.addEventListener('change', () => {
    const item = tecnicos.find(t => t.id === String(select.value || ''));
    if(item){
      state.tecnicoId = item.id;
      state.tecnicoNombre = item.nombre;
      if(hidden) hidden.value = item.id;
      input.value = item.nombre;
      input.classList.add('ok');
      select.classList.add('ok');
      clearFieldError('tecnicoInput');
      actualizarNombreFirmaTecnico();
    }else{
      limpiarSeleccion();
      input.value = '';
    }
  });

  document.addEventListener('click', ev => {
    if(!ev.target.closest('.zg-tech-autocomplete')) cerrar();
  });

  window.zgTecnicoSetById = pickById;
  window.zgTecnicoLimpiar = function(){ limpiarSeleccion(); input.value=''; cerrar(); };

  if(select.value) pickById(select.value);
  else if(state.tecnicoId) pickById(state.tecnicoId);
}

// =========================================================================
//  SELECTOR DE UBICACIÓN EN MAPA (Leaflet + geolocalización)
//  Mejorado: autocomplete tipo Google con Photon + respaldo Nominatim
// =========================================================================
let _pickMap=null, _curMarker=null, _selMarker=null, _selLatLng=null, _geoTimer=0, _sugTimer=null;
let _userLatLng=null, _lastMapResults=[];
const PE_CENTER = { lat:-12.0464, lng:-77.0428 }; // Lima como respaldo si aún no hay GPS
const _pinIcon = () => L.divIcon({className:'sel-pin', html:'<div class="p"></div>', iconSize:[24,24], iconAnchor:[12,24]});
const _curIcon = () => L.divIcon({className:'cur-dot', html:'<div class="r"></div><div class="d"></div>', iconSize:[15,15], iconAnchor:[8,8]});

function setupMapPicker(){
  const modal=$('mapModal'), inp=$('mapSearch'), sug=$('mapSug');
  $('dirPick').addEventListener('click', openMap);
  $('mapClose').addEventListener('click', ()=>modal.classList.remove('show'));
  modal.addEventListener('click', e=>{ if(e.target===modal) modal.classList.remove('show'); });
  $('mapConfirm').addEventListener('click', confirmMap);
  $('mapSearchBtn').addEventListener('click', doSearch);

  // Sugerencias mientras escribe: desde 2 letras y más resultados
  inp.addEventListener('input', ()=>{
    clearTimeout(_sugTimer);
    const q=inp.value.trim();
    if(q.length<2){ sug.classList.remove('show'); sug.innerHTML=''; return; }
    _sugTimer=setTimeout(()=>fetchSug(q), 320);
  });
  inp.addEventListener('keydown', e=>{
    if(e.key==='Enter'){
      e.preventDefault();
      sug.classList.remove('show');
      doSearch();
    }
  });
  inp.addEventListener('blur', ()=>setTimeout(()=>sug.classList.remove('show'), 220));
}

// Busca primero en Photon, que trabaja mejor como autocompletado.
// Si no alcanza, usa Nominatim de respaldo para mostrar más opciones.
async function buscarLugares(q, limit=12){
  const out=[];
  const seen=new Set();

  function pushItem(item){
    const lat=Number(item.lat), lng=Number(item.lng);
    if(!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    const key = `${lat.toFixed(5)},${lng.toFixed(5)}:${normaliza(item.title||item.addr||'')}`;
    if(seen.has(key)) return;
    seen.add(key);
    out.push({
      lat, lng,
      title: item.title || 'Ubicación encontrada',
      subtitle: item.subtitle || '',
      addr: item.addr || item.title || 'Ubicación encontrada',
      source: item.source || ''
    });
  }

  // 1) Photon: sugerencias rápidas con sesgo a tu ubicación real
  try{
    const bias = _userLatLng || PE_CENTER;
    const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(q)}&limit=${limit}&lang=es&lat=${bias.lat}&lon=${bias.lng}`;
    const r = await fetch(url);
    const data = await r.json();

    (data.features || []).forEach(f=>{
      const p = f.properties || {};
      const coords = f.geometry && f.geometry.coordinates ? f.geometry.coordinates : null;
      if(!coords) return;

      const lng = coords[0], lat = coords[1];
      const name = cleanTxt(p.name);
      const street = cleanTxt([p.street, p.housenumber].filter(Boolean).join(' '));
      const title = name || street || cleanTxt(p.city) || q;
      const parts = uniq([
        street && street !== title ? street : '',
        p.district, p.city, p.county, p.state, p.country
      ].map(cleanTxt).filter(Boolean));
      const subtitle = parts.join(' · ');
      const addr = buildAddr(title, parts);

      pushItem({lat,lng,title,subtitle,addr,source:'Photon'});
    });
  }catch(e){
    console.warn('Photon no respondió:', e);
  }

  // 2) Nominatim: respaldo con Perú y datos de dirección
  if(out.length < limit){
    try{
      const bias = _userLatLng || PE_CENTER;
      const delta = 0.35; // zona aproximada alrededor del técnico
      const viewbox = `${bias.lng-delta},${bias.lat+delta},${bias.lng+delta},${bias.lat-delta}`;
      const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(q)}&limit=${limit}&addressdetails=1&dedupe=1&countrycodes=pe&accept-language=es&bounded=0&viewbox=${encodeURIComponent(viewbox)}`;
      const r = await fetch(url);
      const arr = await r.json();

      (arr || []).forEach(it=>{
        const label = it.display_name || '';
        const split = splitLabel(label);
        pushItem({
          lat: parseFloat(it.lat),
          lng: parseFloat(it.lon),
          title: split.title || q,
          subtitle: split.subtitle,
          addr: label || split.title || q,
          source:'Nominatim'
        });
      });
    }catch(e){
      console.warn('Nominatim no respondió:', e);
    }
  }

  return out.slice(0, limit);
}

function cleanTxt(v){ return String(v || '').replace(/\s+/g,' ').trim(); }
function uniq(arr){
  const res=[], seen=new Set();
  arr.forEach(v=>{
    const k=normaliza(v);
    if(v && !seen.has(k)){ seen.add(k); res.push(v); }
  });
  return res;
}
function buildAddr(title, parts){
  const all = uniq([title, ...parts].filter(Boolean));
  return all.join(', ');
}
function splitLabel(label){
  const parts = String(label||'').split(',').map(s=>s.trim()).filter(Boolean);
  return { title: parts[0] || '', subtitle: parts.slice(1).join(' · ') };
}

// Sugerencias tipo Google Maps
async function fetchSug(q){
  const sug=$('mapSug');
  sug.innerHTML = '<div class="sug-empty">Buscando ubicaciones…</div>';
  sug.classList.add('show');

  try{
    const arr = await buscarLugares(q, 12);
    _lastMapResults = arr;
    sug.innerHTML='';

    if(!arr.length){
      sug.innerHTML = `
        <div class="sug-empty">
          Sin resultados exactos. Escribe calle + distrito, o toca el mapa para marcar el punto.
        </div>
        <div class="sug-item" onmousedown="openGoogleSearch(event, '${escapeAttr(q)}')">
          <div class="sug-main">Buscar "${escapeHtml(q)}" en Google Maps</div>
          <div class="sug-sub">Útil si es un negocio que OpenStreetMap no tiene registrado.</div>
        </div>`;
      return;
    }

    arr.forEach(it=>{
      const d=document.createElement('div');
      d.className='sug-item';
      d.innerHTML = `
        <div class="sug-main">${escapeHtml(it.title)}</div>
        <div class="sug-sub">${escapeHtml(it.subtitle || it.addr)}</div>
        <div class="sug-tag">📍 ${escapeHtml(it.source || 'Mapa')}</div>`;
      d.onmousedown=(e)=>{ e.preventDefault(); pickSug(it); };
      sug.appendChild(d);
    });

    // Opción extra para negocios que no aparezcan en OSM/Photon
    const g=document.createElement('div');
    g.className='sug-item';
    g.innerHTML = `
      <div class="sug-main">Buscar "${escapeHtml(q)}" en Google Maps</div>
      <div class="sug-sub">Abrir búsqueda externa si no aparece en la lista.</div>`;
    g.onmousedown=(e)=>openGoogleSearch(e,q);
    sug.appendChild(g);

    sug.classList.add('show');
  }catch(e){
    sug.innerHTML='<div class="sug-empty">No se pudo buscar. Revisa internet o marca el punto en el mapa.</div>';
  }
}

function escapeAttr(s){
  return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function openGoogleSearch(e,q){
  if(e) e.preventDefault();
  const query = q || $('mapSearch').value.trim();
  if(!query) return;
  window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`, '_blank', 'noopener');
}

function pickSug(it){ applyPlace(it); }

function applyPlace(it){
  const lat = parseFloat(it.lat), lng = parseFloat(it.lng);
  if(!Number.isFinite(lat) || !Number.isFinite(lng)) return;

  $('mapSearch').value = it.addr || it.title || '';
  $('mapSug').classList.remove('show');

  if(_pickMap){
    _pickMap.setView([lat,lng],18);
    setSel(lat,lng,false);
  }
  if(_selLatLng) _selLatLng.addr = it.addr || it.title || '';
  $('pickAddr').innerHTML = '<b>Dirección:</b> ' + escapeHtml(it.addr || it.title || '') +
    `<br><small>Coordenadas: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>`;
  updateRouteLink();
}

function openMap(){
  const modal=$('mapModal');
  modal.classList.add('show');
  if(typeof L === 'undefined'){ $('pickAddr').innerHTML = '<b>No se pudo cargar el mapa.</b> Revisa conexión a internet y recarga la página.'; return; }
  setTimeout(()=>{
    if(!_pickMap){
      _pickMap = L.map('pickMap',{zoomControl:true}).setView([PE_CENTER.lat,PE_CENTER.lng],12);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(_pickMap);
      _pickMap.on('click', e=>setSel(e.latlng.lat, e.latlng.lng, true));
    }
    _pickMap.invalidateSize();
    locateMe();
  }, 130);
}

// Tu posición real → bolita azul
function locateMe(){
  if(!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(pos=>{
    const lat=pos.coords.latitude, lng=pos.coords.longitude;
    _userLatLng = {lat,lng};

    if(_curMarker) _curMarker.setLatLng([lat,lng]);
    else _curMarker = L.marker([lat,lng],{icon:_curIcon(),interactive:false,zIndexOffset:-100}).addTo(_pickMap);

    _pickMap.setView([lat,lng],17);

    // Solo pone el pin inicial si todavía no eligió un punto
    if(!_selMarker) setSel(lat,lng,true);
  }, ()=>{}, {enableHighAccuracy:true,timeout:10000,maximumAge:0});
}

// Punto elegido → pin rojo arrastrable
function setSel(lat,lng,geocode){
  _selLatLng={lat,lng,addr:(_selLatLng && _selLatLng.addr) ? _selLatLng.addr : ''};

  if(_selMarker){ _selMarker.setLatLng([lat,lng]); }
  else{
    _selMarker = L.marker([lat,lng],{icon:_pinIcon(),draggable:true}).addTo(_pickMap);
    _selMarker.on('dragend', ()=>{
      const ll=_selMarker.getLatLng();
      setSel(ll.lat,ll.lng,true);
    });
  }

  $('pickAddr').innerHTML = `<b>Punto seleccionado:</b> ${lat.toFixed(6)}, ${lng.toFixed(6)}<br><small>Buscando dirección exacta…</small>`;
  updateRouteLink();

  if(geocode) revGeo(lat,lng);
}

async function revGeo(lat,lng){
  const now=Date.now();
  if(now-_geoTimer<950) await new Promise(r=>setTimeout(r,950));
  _geoTimer=Date.now();

  try{
    const r=await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=es`);
    const d=await r.json();
    const addr = d.display_name || '';
    if(_selLatLng){ _selLatLng.addr = addr; }
    $('pickAddr').innerHTML = addr
      ? '<b>Dirección:</b> '+escapeHtml(addr)+`<br><small>Coordenadas: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>`
      : `<b>Punto seleccionado:</b> ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    updateRouteLink();
  }catch(e){
    $('pickAddr').innerHTML=`<b>Punto seleccionado:</b> ${lat.toFixed(6)}, ${lng.toFixed(6)}<br><small>No se pudo obtener la calle; se guardarán las coordenadas.</small>`;
    updateRouteLink();
  }
}

async function doSearch(){
  const q=$('mapSearch').value.trim();
  if(!q) return;

  $('pickAddr').textContent='Buscando…';
  const arr = await buscarLugares(q, 12);
  _lastMapResults = arr;

  if(arr.length){
    applyPlace(arr[0]); // al presionar Buscar usa el primer resultado
    // y deja visibles las demás opciones para corregir si no era el punto
    fetchSug(q);
  } else {
    $('pickAddr').innerHTML='No se encontró ese lugar. Prueba con: nombre + distrito + ciudad, o toca el mapa para marcar el punto exacto.';
    fetchSug(q);
  }
}

function updateRouteLink(){
  const route = $('mapRoute');
  if(!route || !_selLatLng){ return; }
  const lat = Number(_selLatLng.lat), lng = Number(_selLatLng.lng);
  if(!Number.isFinite(lat) || !Number.isFinite(lng)){ route.style.display='none'; return; }
  route.href = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
  route.style.display = 'inline-flex';
}

function confirmMap(){
  if(!_selLatLng){ toast('Elige un punto en el mapa primero'); return; }

  const lat = Number(_selLatLng.lat), lng = Number(_selLatLng.lng);
  const coords = Number.isFinite(lat) && Number.isFinite(lng) ? `${lat.toFixed(6)}, ${lng.toFixed(6)}` : '';
  const addr = _selLatLng.addr ? _selLatLng.addr : 'Ubicación marcada en el mapa';

  $('direccion').value = coords ? `${addr} | Coordenadas: ${coords}` : addr;
  $('direccionCoords').value = coords;
  clearFieldError('direccion');
  $('mapModal').classList.remove('show');
  toast('Ubicación guardada');
}

// ---- Reloj en vivo (con segundos) ----
function startClock(){
  const upd = () => {
    const now = new Date();
    $('clockTime').textContent = now.toLocaleTimeString('es-PE', { hour12: false });
    $('clockDate').textContent = now.toLocaleDateString('es-PE', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
  };
  upd();
  setInterval(upd, 1000);
}

// ---- Tarjetas de trabajo ----
function normaliza(s){ return String(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }

function renderWorkCards(){
  workGrid.innerHTML = '';
  const q = normaliza(workQuery.trim());
  const customs = Object.values(state.selected).filter(s => s.custom && !WORK_TYPES.find(w=>w.id===s.id));
  const todos = [...WORK_TYPES, ...customs];
  let mostrados = 0;
  todos.forEach(w => {
    if(!q || normaliza(w.nombre).includes(q)){ workGrid.appendChild(makeWorkCard(w)); mostrados++; }
  });
  // Mensaje si no hay coincidencias
  if(q && mostrados === 0){
    const none = document.createElement('div'); none.className = 'work-none';
    none.textContent = `Sin trabajos que coincidan con "${workQuery.trim()}". Usa "Otro trabajo" para crearlo.`;
    workGrid.appendChild(none);
  }
  // Botón agregar
  const add = document.createElement('button');
  add.className = 'work-card add-work';
  add.innerHTML = '<span class="ic">＋</span><span>Otro trabajo</span>';
  add.onclick = addCustomWork;
  workGrid.appendChild(add);
}

function makeWorkCard(w){
  const on = !!state.selected[w.id];
  const c = document.createElement('button');
  c.className = 'work-card' + (on ? ' on' : '');
  c.innerHTML = `
    <span class="check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
    <span class="nm">${escapeHtml(w.nombre)}</span>`;
  c.onclick = () => toggleWork(w);
  return c;
}

function toggleWork(w){
  if(state.selected[w.id]){
    const s = state.selected[w.id];
    if(s.photos.length || s.detalle || Object.keys(s.campos||{}).length){
      if(!confirm(`¿Quitar "${s.nombre}"? Se borrarán sus datos y fotos.`)) return;
    }
    delete state.selected[w.id];
  } else {
    state.selected[w.id] = crearSeleccionTrabajo(w);
  }
  renderWorkCards();
  renderPanels();
  updateCounter();
}

function addCustomWork(){
  const nombre = prompt('Nombre del trabajo:');
  if(!nombre || !nombre.trim()) return;
  state.customSeq++;
  const w = { id:'custom_'+state.customSeq, nombre:nombre.trim().toUpperCase(), custom:true };
  state.selected[w.id] = crearSeleccionTrabajo(w);
  renderWorkCards();
  renderPanels();
  updateCounter();
}

// ---- Paneles (creados con DOM para evitar problemas de escape) ----
function renderPanels(){
  panelsEl.innerHTML = '';
  const ids = Object.keys(state.selected);
  ids.forEach(id => panelsEl.appendChild(makePanel(state.selected[id])));
}

function makePanel(s){
  const panel = document.createElement('div');
  panel.className = 'panel';
  panel.dataset.zgWorkId = String((s && s.id) || '');
  panel.__zgWorkState = s;

  // Cabecera
  const head = document.createElement('div');
  head.className = 'panel-head';
  const ttl = document.createElement('span'); ttl.className='ttl'; ttl.textContent = s.nombre;
  const rm = document.createElement('button'); rm.className='rm'; rm.innerHTML='×'; rm.title='Quitar';
  rm.onclick = () => toggleWork(s);
  head.append(ttl, rm);

  // Cuerpo
  const body = document.createElement('div');
  body.className = 'panel-body';

  if(typeof window.zgBuildMachineAssignmentField === 'function'){
    const machineField = window.zgBuildMachineAssignmentField(s);
    if(machineField) body.appendChild(machineField);
  }

  // Detalle
  const fld = document.createElement('div'); fld.className='field';
  const lbl = document.createElement('label'); lbl.textContent='Observaciones / notas adicionales';
  const ta = document.createElement('textarea');
  ta.id = `detalle_${s.id}`;
  ta.placeholder = 'Describe lo realizado, hallazgos, materiales, etc.';
  ta.value = s.detalle;
  const derr = document.createElement('div'); derr.className='field-error'; derr.id = `detalle_${s.id}Error`;
  ta.oninput = () => { s.detalle = ta.value; ta.classList.remove('input-error'); derr.textContent=''; derr.classList.remove('show'); };
  fld.append(lbl, ta, derr);

  // Fotos: cámara directa y galería por separado.
  const drop = document.createElement('div');
  drop.className='drop zg-photo-source-box';
  drop.innerHTML = '<span class="ic">📷</span><span class="big">Agregar fotos</span><small>Elige cómo deseas agregar la evidencia</small>';

  const sourceActions = document.createElement('div');
  sourceActions.className = 'zg-photo-source-actions';
  const cameraBtn = document.createElement('button');
  cameraBtn.type = 'button';
  cameraBtn.className = 'zg-photo-source-btn primary';
  cameraBtn.textContent = 'Tomar foto';
  const galleryBtn = document.createElement('button');
  galleryBtn.type = 'button';
  galleryBtn.className = 'zg-photo-source-btn';
  galleryBtn.textContent = 'Elegir galería';

  const cameraInp = document.createElement('input');
  cameraInp.type='file'; cameraInp.accept='image/*'; cameraInp.setAttribute('capture','environment'); cameraInp.hidden=true;
  cameraInp.onchange = (e) => handleFiles(s, e.target.files, cameraInp);

  const galleryInp = document.createElement('input');
  galleryInp.type='file'; galleryInp.accept='image/*'; galleryInp.multiple=true; galleryInp.hidden=true;
  galleryInp.onchange = (e) => handleFiles(s, e.target.files, galleryInp);

  cameraBtn.onclick = () => cameraInp.click();
  galleryBtn.onclick = () => galleryInp.click();
  sourceActions.append(cameraBtn, galleryBtn);
  drop.append(sourceActions, cameraInp, galleryInp);

  // Grid de fotos
  const thumbs = document.createElement('div');
  thumbs.className='thumbs';
  if(s.photos.length === 0){
    const hint = document.createElement('div'); hint.className='empty-hint';
    hint.textContent='Aún no hay fotos en esta sección.';
    thumbs.appendChild(hint);
  } else {
    s.photos.forEach(p => thumbs.appendChild(makeThumb(s, p)));
  }

  const assistant = buildQuickAssistant(s);
  if(assistant) body.append(assistant);
  const campos = buildCampos(s);
  if(campos) body.append(campos);
  if(typeof window.zgBuildWorkMaterials === 'function'){
    const workMaterials = window.zgBuildWorkMaterials(s);
    if(workMaterials) body.appendChild(workMaterials);
  }
  body.append(drop, thumbs);
  if(typeof window.zgBuildWorkMaintenanceFollowup === 'function'){
    const followup = window.zgBuildWorkMaintenanceFollowup(s);
    if(followup) body.appendChild(followup);
  }
  panel.append(head, body);
  return panel;
}


function buildQuickAssistant(s){
  if(!s.auto) s.auto = emptyAuto();
  const tieneSugerencias = ['actividades','hallazgos','acciones','recomendaciones'].some(k => Array.isArray(s.auto[k]) && s.auto[k].length);
  // Solo aplica sugerencias automáticas la primera vez.
  // Si el técnico presiona "Limpiar opciones", se respeta y queda todo vacío.
  if(!tieneSugerencias && !(s.auto && s.auto.limpiado)){
    aplicarPresetSilencioso(s, presetSugeridoPorTrabajo(s.nombre || ''));
  }
  const box = document.createElement('div'); box.className='quick-assistant';
  const head = document.createElement('div'); head.className='quick-head';
  const htxt = document.createElement('div'); htxt.innerHTML = '<strong>Sugerencias del servicio</strong><small>Opciones sugeridas. El técnico puede desmarcar o ajustar lo que no corresponda.</small>';
  const actions = document.createElement('div'); actions.className='quick-actions';
  const reset = document.createElement('button'); reset.className='quick-reset'; reset.type='button'; reset.textContent='Limpiar opciones';
  reset.onclick = () => {
    s.auto = emptyAuto();
    s.auto.limpiado = true;
    s.detalle = '';
    renderPanels();
    toast('Opciones limpiadas. Puedes seleccionar desde cero.');
  };
  actions.appendChild(reset);
  head.append(htxt, actions); box.appendChild(head);

  const presets = document.createElement('div'); presets.className='template-row';
  Object.keys(PRESETS).forEach(name => {
    const b = document.createElement('button');
    b.type='button';
    b.className='template-btn' + ((s.auto && s.auto.plantilla === name) ? ' on' : '');
    b.textContent = name;
    b.onclick = () => applyPreset(s, name, PRESETS[name]);
    presets.appendChild(b);
  });
  box.appendChild(presets);

  const grid = document.createElement('div'); grid.className='quick-grid';
  grid.appendChild(chipGroup(s,'actividades','Actividades realizadas', QUICK_BANK.actividades));
  grid.appendChild(chipGroup(s,'hallazgos','Observaciones', QUICK_BANK.hallazgos, 'warn'));
  grid.appendChild(chipGroup(s,'acciones','Acciones ejecutadas', QUICK_BANK.acciones, 'ok'));
  // Recomendaciones del técnico ocultas: el PDF usa solo recomendaciones generales fijas.
  box.appendChild(grid);

  if(s.id === 'revision_tecnica' || s.nombre.toUpperCase().includes('REVISION') || s.nombre.toUpperCase().includes('PTI')){
    box.appendChild(buildPtiBox(s));
  }

  // No se muestra resumen automático en la web.
  // Las actividades, hallazgos, acciones y recomendaciones se conservan internamente para el PDF.
  return box;
}

function applyPreset(s, nombrePlantilla){
  aplicarPresetSilencioso(s, nombrePlantilla || presetSugeridoPorTrabajo(s.nombre || ''));
  toast('Sugerencias aplicadas: revisa y ajusta si corresponde');
  renderPanels();
}

function generarTextoAutomaticoTrabajo(s, nombrePlantilla){
  const a = s.auto || emptyAuto();
  const lineas = [];
  lineas.push('Plantilla aplicada: ' + (nombrePlantilla || 'servicio técnico') + '.');

  if(a.actividades && a.actividades.length){
    lineas.push('Actividades realizadas: ' + a.actividades.join(', ') + '.');
  }
  if(a.hallazgos && a.hallazgos.length){
    lineas.push('Observaciones: ' + a.hallazgos.join(', ') + '.');
  }
  if(a.acciones && a.acciones.length){
    lineas.push('Acciones ejecutadas: ' + a.acciones.join(', ') + '.');
  }
  // Recomendaciones del técnico no se muestran: se usan recomendaciones generales fijas.
  return lineas.join('\n');
}

function chipGroup(s,key,title,items,kind){
  const g = document.createElement('div'); g.className='quick-group';
  const t = document.createElement('div'); t.className='quick-title'; t.textContent=title;
  const row = document.createElement('div'); row.className='chip-row';
  items.forEach(txt => {
    const b = document.createElement('button'); b.type='button'; b.className='qchip ' + (kind||'');
    const on = (s.auto && s.auto[key] || []).includes(txt);
    if(on) b.classList.add('on');
    b.textContent = txt;
    b.onclick = () => { toggleAutoArray(s,key,txt); renderPanels(); };
    row.appendChild(b);
  });
  g.append(t,row); return g;
}

function statusGroup(s){
  const g = document.createElement('div'); g.className='quick-group full';
  const t = document.createElement('div'); t.className='quick-title'; t.textContent='Estado final del servicio';
  const row = document.createElement('div'); row.className='chip-row';
  QUICK_BANK.estados.forEach(txt => {
    const b=document.createElement('button'); b.type='button'; b.className='qchip ok';
    if((s.auto && s.auto.estado) === txt) b.classList.add('on');
    b.textContent=txt;
    b.onclick=()=>{ if(!s.auto) s.auto=emptyAuto(); s.auto.limpiado=false; s.auto.estado = (s.auto.estado===txt?'':txt); s.auto.plantilla=''; renderPanels(); };
    row.appendChild(b);
  });
  g.append(t,row); return g;
}

function toggleAutoArray(s,key,txt){
  if(!s.auto) s.auto = emptyAuto();
  s.auto.limpiado = false;
  if(!Array.isArray(s.auto[key])) s.auto[key] = [];
  const i = s.auto[key].indexOf(txt);
  if(i >= 0) s.auto[key].splice(i,1); else s.auto[key].push(txt);
  s.auto.plantilla = ''; // si edita manualmente, ya no marcamos una plantilla exacta
}

function buildPtiBox(s){
  const box = document.createElement('div'); box.className='pti-box';
  const title = document.createElement('div'); title.className='quick-title'; title.textContent='Checklist PTI / Run Test';
  const tools = document.createElement('div'); tools.className='pti-tools';
  [['Todo OK','OK'],['Todo N/A','NA'],['Limpiar checklist','']].forEach(([txt,val]) => {
    const b=document.createElement('button'); b.type='button'; b.textContent=txt;
    b.onclick=()=>{ if(!s.auto) s.auto=emptyAuto(); s.auto.limpiado=false; PTI_ITEMS.forEach(item => { if(val) s.auto.pti[item]=val; else delete s.auto.pti[item]; }); renderPanels(); };
    tools.appendChild(b);
  });
  const grid = document.createElement('div'); grid.className='pti-grid';
  PTI_ITEMS.forEach(item => {
    const it=document.createElement('div'); it.className='pti-item';
    const lab=document.createElement('span'); lab.textContent=item;
    const st=document.createElement('div'); st.className='pti-state';
    [['OK','ok'],['REV','warn'],['N/A','na']].forEach(([val,cls])=>{
      const b=document.createElement('button'); b.type='button'; b.textContent=val; b.className=cls;
      if(s.auto && s.auto.pti && s.auto.pti[item]===val) b.classList.add('on');
      b.onclick=()=>{ if(!s.auto) s.auto=emptyAuto(); s.auto.limpiado=false; if(s.auto.pti[item]===val) delete s.auto.pti[item]; else s.auto.pti[item]=val; renderPanels(); };
      st.appendChild(b);
    });
    it.append(lab,st); grid.appendChild(it);
  });
  box.append(title,tools,grid); return box;
}

function makeAutoPreview(s){
  return '';
}

function autoCaption(s, idx){
  return 'Evidencia ' + (idx + 1);
}

// Construye los campos propios del tipo de trabajo (mini-reporte)
function buildCampos(s){
  // Conserva la información de informes antiguos al pasar de varios campos a un solo detalle técnico.
  if(s && s.campos && typeof s.campos === 'object'){
    if(s.id === 'asistencia_tecnica' && !cleanTxt(s.campos.detalle_tecnico)){
      s.campos.detalle_tecnico = [s.campos.problema, s.campos.diagnostico, s.campos.solucion].map(cleanTxt).filter(Boolean).join(' ');
    }
    if(s.id === 'mantenimiento_correctivo' && !cleanTxt(s.campos.detalle_tecnico)){
      s.campos.detalle_tecnico = [s.campos.falla_diagnostico, s.campos.trabajo_resultado].map(cleanTxt).filter(Boolean).join(' ');
    }
  }
  const defs = CAMPOS[s.id];
  if(!defs || !defs.length) return null;
  const grid = document.createElement('div');
  grid.className = 'campos';
  defs.forEach(def => {
    const f = document.createElement('div');
    f.className = 'field' + (def.tipo === 'area' ? ' full' : '');
    const lbl = document.createElement('label'); lbl.textContent = def.label;
    let inp;
    if(def.tipo === 'area'){
      inp = document.createElement('textarea');
      inp.style.minHeight = '60px';
    } else if(def.tipo === 'sel'){
      inp = document.createElement('select');
      const o0 = document.createElement('option'); o0.value=''; o0.textContent='—'; inp.appendChild(o0);
      (def.opciones||[]).forEach(o => { const op=document.createElement('option'); op.value=o; op.textContent=o; inp.appendChild(op); });
    } else {
      inp = document.createElement('input');
      inp.type = def.tipo === 'num' ? 'number' : 'text';
    }
    inp.id = `campo_${s.id}_${def.id}`;
    inp.value = s.campos[def.id] || '';
    const err = document.createElement('div'); err.className='field-error'; err.id = `campo_${s.id}_${def.id}Error`;
    const upd = () => { s.campos[def.id] = inp.value; inp.classList.remove('input-error'); err.textContent=''; err.classList.remove('show'); };
    inp.oninput = upd; inp.onchange = upd;
    f.append(lbl, inp, err);
    grid.appendChild(f);
  });
  return grid;
}

function makeThumb(s, p){
  const t = document.createElement('div'); t.className='thumb';
  const ph = document.createElement('div'); ph.className='ph';
  const img = document.createElement('img'); img.src = p.dataUrl; img.alt='evidencia';
  const x = document.createElement('button'); x.className='x'; x.innerHTML='×'; x.title='Eliminar';
  x.onclick = () => { s.photos = s.photos.filter(q => q.id !== p.id); renderPanels(); updateCounter(); };
  ph.append(img, x);
  const cap = document.createElement('input');
  cap.type='text'; cap.placeholder='Descripción de la foto...'; cap.value = p.caption || '';
  cap.oninput = () => { p.caption = cap.value; };
  t.append(ph, cap);
  return t;
}

// ---- Carga de fotos (redimensiona al subir) ----
async function handleFiles(s, fileList, inputEl){
  const files = Array.from(fileList || []).filter(f => f.type.startsWith('image/'));
  if(!files.length) return;
  showOverlay('Procesando fotos...');
  for(const f of files){
    try{
      const raw = await readFile(f);
      const r = await resizeImage(raw, 1200, 0.7);
      s.photos.push({ id: 'p'+Date.now()+Math.random().toString(36).slice(2,6), dataUrl:r.dataUrl, w:r.w, h:r.h, caption:autoCaption(s, s.photos.length) });
    }catch(err){ console.error('Error con imagen', err); }
  }
  if(inputEl) inputEl.value = '';
  hideOverlay();
  renderPanels();
  updateCounter();
  toast(`${files.length} foto(s) agregada(s)`);
}

function readFile(file){
  return new Promise((res, rej) => {
    const r = new FileReader();
    r.onload = () => res(r.result);
    r.onerror = rej;
    r.readAsDataURL(file);
  });
}

function resizeImage(dataUrl, maxDim, quality){
  return new Promise((res, rej) => {
    const img = new Image();
    img.onload = () => {
      let w = img.naturalWidth, h = img.naturalHeight;
      if(w > maxDim || h > maxDim){
        if(w >= h){ h = Math.round(h * maxDim / w); w = maxDim; }
        else { w = Math.round(w * maxDim / h); h = maxDim; }
      }
      const cv = document.createElement('canvas');
      cv.width = w; cv.height = h;
      const ctx = cv.getContext('2d');
      ctx.fillStyle = '#fff'; ctx.fillRect(0,0,w,h);
      ctx.drawImage(img, 0, 0, w, h);
      res({ dataUrl: cv.toDataURL('image/jpeg', quality), w, h });
    };
    img.onerror = rej;
    img.src = dataUrl;
  });
}

// ---- Contador / utilidades UI ----
function updateCounter(){
  const works = Object.keys(state.selected).length;
  const photos = Object.values(state.selected).reduce((a,s)=>a+s.photos.length,0);
  $('counter').textContent = `${works} trabajo(s) · ${photos} foto(s)`;
}
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function showOverlay(msg){ $('overlay').querySelector('.msg').textContent = msg || 'Generando...'; $('overlay').classList.add('show'); }
function hideOverlay(){ $('overlay').classList.remove('show'); }
var toastTimer = null;
function toast(msg){ toastEl.textContent = msg; toastEl.classList.add('show'); clearTimeout(toastTimer); toastTimer = setTimeout(()=>toastEl.classList.remove('show'), 2200); }

function clearPressureDraftStorage(){
  // Las presiones preliminares se guardaban temporalmente para continuar un servicio.
  // Al iniciar un informe realmente nuevo, se eliminan todas esas copias para que
  // ningún valor del informe anterior vuelva a aparecer.
  try{
    [window.sessionStorage, window.localStorage].forEach(storage => {
      const keys = [];
      for(let i = 0; i < storage.length; i++){
        const k = storage.key(i);
        if(k && k.indexOf('zg_presiones_pre_') === 0) keys.push(k);
      }
      keys.forEach(k => storage.removeItem(k));
    });
  }catch(e){}
}
window.zgClearPressureDraftStorage = clearPressureDraftStorage;

function hardClearFields(ids){
  (ids || []).forEach(function(id){
    const campo = document.getElementById(id);
    if(!campo) return;
    try{
      if(campo.tagName === 'SELECT'){
        campo.selectedIndex = 0;
        campo.value = '';
      }else{
        campo.value = '';
        campo.defaultValue = '';
        campo.removeAttribute('value');
        campo.setAttribute('autocomplete','off');
      }
      campo.classList.remove('input-error','ok');
      campo.dispatchEvent(new Event('input',{bubbles:true}));
      campo.dispatchEvent(new Event('change',{bubbles:true}));
    }catch(e){}
    const err = document.getElementById(id + 'Error');
    if(err){ err.textContent=''; err.classList.remove('show'); }
  });
}

function clearFinalControlFields(){
  window.__zgBlockPressureRestore = true;
  clearPressureDraftStorage();
  if(window.zgClearMaintenanceStorage) window.zgClearMaintenanceStorage();
  hardClearFields([
    'estadoFinalEquipo','setPointFinal','tempAmbienteFinal','retornoFinal','suministroFinal',
    'presionAltaFinal','presionBajaFinal','voltajeFinalL1L2','voltajeFinalL2L3',
    'voltajeFinalL1L3','zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal','repuestosManual'
  ]);
  if(window.zgActualizarMantenimientoFinal) window.zgActualizarMantenimientoFinal();
}
window.zgClearFinalControlFields = clearFinalControlFields;

function clearAll(){
  if(PREINSPECCION){
    if(!confirm('Este servicio ya tiene preinspección guardada. ¿Limpiar solo el cierre del informe?')) return;
    $('savedBox').classList.remove('show');
    setVal('obs', '');
    clearFinalControlFields();
    MANUAL_REPORT_IDS.forEach(id => { if($(id)) $(id).value=''; });
    state.selected = {};
    state.customSeq = 0;
    renderWorkCards();
    renderPanels();
    updateCounter();
    toast('Se limpió solo el cierre. La preinspección se conserva.');
    return;
  }

  if(!confirm('¿Limpiar todo el formulario?')) return;
  $('savedBox').classList.remove('show');
  clearPressureDraftStorage();
  ['orden','odooTicketRef','odooTicketRefDisplay','odooCotizacion','odooCotizacionDisplay','cliente','tecnicoInput','tecnicoSearch','direccion','obs','equipoNo','serialUnidad','modeloEquipo','controladorEquipo','anioFabricacion','temperaturaAmbiente','setPoint','retornoAire','suministroAire','presionAlta','presionBaja','voltajeL1L2','voltajeL2L3','voltajeL1L3','alarmaEncontrada','observacionInicial','preinspeccionId','tokenContinuacion','zgTipoEquipo','zgTamanoContenedor','gensetHorometroInicial','gensetVoltajeBateriaInicial','gensetNivelCombustibleInicial','gensetNivelAceiteInicial','gensetRefrigeranteMotorInicial','gensetArranqueInicial','gensetFrecuenciaInicial','gensetPresionAceiteInicial','setPointFinal','tempAmbienteFinal','retornoFinal','suministroFinal','presionAltaFinal','presionBajaFinal','voltajeFinalL1L2','voltajeFinalL2L3','voltajeFinalL1L3','zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal','repuestosManual','adminTiendaNombre','adminTiendaCargo','firmaTecnico','firmaAdmin'].forEach(id => { if($(id)) $(id).value=''; });
  hardClearFields(['presionAlta','presionBaja','presionAltaFinal','presionBajaFinal']);
  MANUAL_REPORT_IDS.forEach(id => { if($(id)) $(id).value=''; });
  ['marcaEquipo','refrigerante','estadoInicial','estadoEncendido','estadoEnergia','estadoAlarma','alarmaEncontrada','estadoFinalEquipo','ptiFuncTest','ptiAfa','ptiBrief','ptiProbe','ptiResultado'].forEach(id => { if($(id)) $(id).value=''; });
  setRequiereRepuesto(false);
  if($('preStatus')) $('preStatus').textContent='Pendiente de preguardado';
  clearFieldError('orden'); clearFieldError('cliente'); clearFieldError('direccion');
  $('tecnicoInput').classList.remove('ok'); $('tecnicoId').value=''; if($('tecnicoSearch')){ $('tecnicoSearch').value=''; $('tecnicoSearch').classList.remove('ok'); }
  $('direccionCoords').value='';
  state.tecnicoId=''; state.tecnicoNombre='';
  if(_selMarker && _pickMap){ _pickMap.removeLayer(_selMarker); _selMarker=null; } _selLatLng=null;
  const t=new Date(); $('fecha').value=`${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}`;
  state.selected = {}; state.customSeq = 0;
  try{ window.ZG_PRE_EVIDENCIAS = []; if(typeof renderPreEvidenceGrid === 'function') renderPreEvidenceGrid(); }catch(e){}
  renderWorkCards(); renderPanels(); updateCounter();
  if(window.zgClearMaintenanceStorage) window.zgClearMaintenanceStorage();
  if(window.zgActualizarMantenimientoFinal) window.zgActualizarMantenimientoFinal();
  toast('Formulario limpio');
}


async function avisarSupervisoresNuevoInforme(info){
  try{
    const body = new URLSearchParams({
      token: PUSH_TRIGGER_TOKEN || '',
      tipo: 'nuevo_informe',
      titulo: 'Nuevo informe técnico',
      detalle: `${info.tecnico || 'Técnico'} registró ${info.trabajo || 'un trabajo'} · Reporte ${info.orden || ''} · Cliente ${info.cliente || ''}`,
      url: 'panel.php'
    });
    await fetch('notificar_supervisores_push.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body
    });
  }catch(e){
    console.warn('No se pudo avisar a supervisores:', e);
  }
}

// Sincroniza los materiales visibles de cada trabajo antes de generar el PDF.
// Esto evita que el PDF use una copia antigua o una lista vacía del estado.
function zgSyncWorkMaterialsForPdf(){
  const selected = Object.values((typeof state !== 'undefined' && state.selected) ? state.selected : {});
  const panels = Array.from(document.querySelectorAll('#panels .panel'));
  const snapshot = {};
  const clean = v => String(v == null ? '' : v).replace(/\s+/g,' ').trim();

  selected.forEach((work, idx) => {
    if(!work) return;
    if(!Array.isArray(work.repuestosTrabajo)) work.repuestosTrabajo = [];
    const panel = panels[idx];
    if(panel){
      const rows = Array.from(panel.querySelectorAll('.zg-work-material-table tbody tr')).map(tr => {
        const cells = tr.querySelectorAll('td');
        if(cells.length < 4 || tr.querySelector('.zg-work-material-empty')) return null;
        const codeNode = cells[0].querySelector('b');
        const detailNode = cells[1].querySelector('input');
        const qtyNode = cells[2].querySelector('input');
        const unitNode = cells[3].querySelector('.unit');
        const detalle = clean(detailNode ? detailNode.value : cells[1].textContent);
        if(!detalle) return null;
        return {
          codigo: clean(codeNode ? codeNode.textContent.replace(/^Sin código$/i,'') : cells[0].textContent),
          detalle,
          cantidad: clean(qtyNode ? qtyNode.value : cells[2].textContent) || '1',
          unidad: clean(unitNode ? unitNode.textContent : cells[3].textContent) || 'und'
        };
      }).filter(Boolean);
      if(rows.length || panel.querySelector('.zg-work-material-table')) work.repuestosTrabajo = rows;
    }
    snapshot[work.id || String(idx)] = JSON.parse(JSON.stringify(work.repuestosTrabajo || []));
  });

  window.ZG_WORK_MATERIALS_PDF = snapshot;
  return selected;
}
window.zgSyncWorkMaterialsForPdf = zgSyncWorkMaterialsForPdf;

// =========================================================================
//  GENERACIÓN DEL PDF
// =========================================================================
async function generatePDF(){
  // Captura la fecha inmediatamente al pulsar el botón, antes de que cualquier
  // validación, renderizado o autocompletado tardío pueda restaurar la fecha anterior.
  const fechaCapturadaAlIniciar = $('fecha') ? String($('fecha').value || '').trim() : '';
  if(fechaCapturadaAlIniciar){
    window.ZG_FECHA_PDF_ACTUAL = fechaCapturadaAlIniciar;
    window.ZG_FECHA_EDICION_CONFIRMADA = fechaCapturadaAlIniciar;
  }
  if(!validateGeneralRequired()) return;
  if(!validarInspeccionPreliminar()) return;
  if(typeof window.zgSyncWorkMaterialsForPdf === 'function') window.zgSyncWorkMaterialsForPdf();
  const sections = Object.values(state.selected);
  if(!state.tecnicoId){ toast('Primero elige tu nombre de la lista'); ($('tecnicoSearch') || $('tecnicoInput')).focus(); return; }

  if(!ZG_EDIT_MODE && (!$('preinspeccionId') || !$('preinspeccionId').value.trim())){
    toast('Primero guarda la inspección preliminar');
    alert('Antes de generar el informe final debes guardar la inspección preliminar.\n\nEsto deja constancia de cómo se encontró el equipo antes de intervenirlo.');
    if($('preBtn')) $('preBtn').scrollIntoView({behavior:'smooth', block:'center'});
    return;
  }

  const _informeKeyFinal = 'zgroup_informe_final_' + (($('preinspeccionId') && $('preinspeccionId').value.trim()) || ($('tokenContinuacion') && $('tokenContinuacion').value.trim()) || (location.pathname + location.search));
  if(!ZG_EDIT_MODE){
    try{
      if((window.__zgroupInformeFinalGenerado === true || localStorage.getItem(_informeKeyFinal) === '1') && !confirm('Este servicio ya tiene un informe final generado.\n\n¿Estás seguro de generar otro informe final? Se creará un nuevo PDF para el mismo servicio.')){
        return;
      }
    }catch(e){
      if(window.__zgroupInformeFinalGenerado === true && !confirm('Este servicio ya tiene un informe final generado.\n\n¿Estás seguro de generar otro informe final?')) return;
    }
  }

  if(!validateWorkSections(sections)) return;
  if(!validarRepuestos()) return;
  if(!validarControlFinal()) return;
  if(window.zgPrepareServiceTimesForFinalPdf) window.zgPrepareServiceTimesForFinalPdf();
  await registrarRepuestosTecnico();

  $('savedBox').classList.remove('show');
  const orden = $('orden').value.trim();
  const cliente = $('cliente').value.trim();
  const direccion = $('direccion').value.trim();
  const fechaInputActual = $('fecha');
  // En edición se usa la fecha capturada al iniciar el guardado. Así, aunque un
  // módulo antiguo intente restaurar la fecha original durante las validaciones,
  // el PDF y la base de datos reciben la fecha elegida por el supervisor.
  const fechaVisibleAhora = fechaInputActual ? String(fechaInputActual.value || '').trim() : '';
  const fecha = (ZG_EDIT_MODE && fechaCapturadaAlIniciar) ? fechaCapturadaAlIniciar : fechaVisibleAhora;
  window.ZG_FECHA_PDF_ACTUAL = fecha;
  window.ZG_FECHA_EDICION_CONFIRMADA = fecha;
  if(fechaInputActual){
    fechaInputActual.value = fecha;
    fechaInputActual.setAttribute('value', fecha);
  }
  const trabajosResumen = sections.map(s => s.nombre).filter(Boolean).join(' | ');

  try{
    // Un solo PDF para todo el servicio, aunque el técnico registre 2 o más trabajos.
    showOverlay(sections.length > 1 ? `Generando un solo informe con ${sections.length} trabajos…` : 'Generando informe técnico…');
    await new Promise(r => setTimeout(r, 30));

    const doc = buildPDF(sections);
    const blob = doc.output('blob');
    const fname = `Informe_${(orden || 'sin-reporte').replace(/[^\w-]/g,'_')}_SERVICIO_TECNICO_${fecha || 'fecha'}.pdf`;

    const fd = new FormData();
    fd.append('pdf', blob, fname);
    fd.append('tecnico_id', state.tecnicoId);
    fd.append('orden', orden);
    fd.append('odoo_ticket_ref', getVal('odooTicketRef'));
    fd.append('odoo_cotizacion', getVal('odooCotizacion'));
    fd.append('cliente', cliente);
    fd.append('direccion', direccion);
    fd.append('direccion_coords', $('direccionCoords').value.trim());
    fd.append('fecha', fecha);
    fd.append('trabajos', trabajosResumen);
    fd.append('tipo_equipo', getVal('zgTipoEquipo') || (getVal('marcaEquipo').toUpperCase()==='GENSET' ? 'Genset' : 'Reefer'));
    fd.append('tamano_contenedor', getVal('zgTipoEquipo')==='Genset' ? 'No aplica' : getVal('zgTamanoContenedor'));
    fd.append('hora_inicio_servicio', getVal('horaInicioServicio'));
    fd.append('hora_fin_servicio', getVal('horaFinServicio'));
    if($('preinspeccionId')) fd.append('preinspeccion_id', $('preinspeccionId').value.trim());
    if($('tokenContinuacion')) fd.append('token_continuacion', $('tokenContinuacion').value.trim());
    try{
      const snapshot = window.zgCollectReportSnapshot ? window.zgCollectReportSnapshot() : null;
      if(snapshot){
        if(!snapshot.fields || typeof snapshot.fields !== 'object') snapshot.fields = {};
        // La instantánea también debe conservar la fecha recién editada.
        snapshot.fields.fecha = {type:'date', value:fecha, checked:false};
        fd.append('datos_json', JSON.stringify(snapshot));
      }
    }catch(e){ console.warn('No se pudo preparar la instantánea del informe', e); }
    // Reafirma la fecha al final, después de preparar la instantánea completa.
    fd.set('fecha', fecha);
    if(ZG_EDIT_MODE && ZG_EDIT_REPORT && ZG_EDIT_REPORT.id){
      fd.append('informe_id', String(ZG_EDIT_REPORT.id));
      fd.append('repuestos_manual', (($('repuestosManual') && $('repuestosManual').value) || '').trim());
      fd.append('repuestos_actualizados', '1');
    }

    const res = await fetch(ZG_EDIT_MODE ? 'actualizar_informe.php' : 'guardar.php', { method:'POST', body: fd });
    const out = await res.json();
    if(!out.ok) throw new Error(out.error || 'No se pudo guardar el informe');

    if(ZG_EDIT_MODE){
      hideOverlay();
      const fechaConfirmada = String(out.fecha_guardada || fecha || '').trim();
      const fechaCampo = $('fecha');
      if(fechaCampo && fechaConfirmada){
        fechaCampo.value = fechaConfirmada;
        fechaCampo.setAttribute('value', fechaConfirmada);
      }
      window.ZG_FECHA_PDF_ACTUAL = fechaConfirmada;
      window.ZG_FECHA_EDICION_CONFIRMADA = fechaConfirmada;
      if(out.hora_inicio_servicio && $('horaInicioServicio')) $('horaInicioServicio').value = window.zgSqlToLocalDateTime ? window.zgSqlToLocalDateTime(out.hora_inicio_servicio) : out.hora_inicio_servicio;
      if(out.hora_fin_servicio && $('horaFinServicio')) $('horaFinServicio').value = window.zgSqlToLocalDateTime ? window.zgSqlToLocalDateTime(out.hora_fin_servicio) : out.hora_fin_servicio;
      // El informe ya quedó guardado: no debe aparecer la advertencia de pérdida de datos.
      try{ if(typeof window.zgroupMarcarGuardado === 'function') window.zgroupMarcarGuardado(); }catch(e){}
      try{
        window.ZG_EDIT_REPORT.fecha = fechaConfirmada;
        if(window.ZG_EDIT_REPORT.snapshot){
          if(!window.ZG_EDIT_REPORT.snapshot.fields) window.ZG_EDIT_REPORT.snapshot.fields = {};
          window.ZG_EDIT_REPORT.snapshot.fields.fecha = {type:'date', value:fechaConfirmada, checked:false};
        }
      }catch(e){}
      const odooEdit = out.odoo || {};
      const odooEditMsg = odooEdit.ok
        ? '\n\nOdoo: PDF actualizado en el ticket ' + (odooEdit.ticket_ref || orden) + '.'
        : '\n\nOdoo pendiente: ' + (odooEdit.error || 'No se pudo sincronizar en este momento. Puedes reintentar desde el panel.');
      alert('Informe actualizado correctamente.' + odooEditMsg);
      return;
    }

    // Aviso para supervisión: se envía una sola alerta por informe generado.
    avisarSupervisoresNuevoInforme({
      tecnico: state.tecnicoNombre,
      trabajo: trabajosResumen,
      orden,
      cliente,
      fecha
    });

    hideOverlay();
    showSaved([{ nombre: sections.length > 1 ? `${sections.length} trabajos en un solo PDF` : (sections[0]?.nombre || 'Informe técnico'), fname, url: URL.createObjectURL(blob), odoo: out.odoo || null }], sections.length);
    try{ window.__zgroupInformeFinalGenerado = true; localStorage.setItem(_informeKeyFinal, '1'); }catch(e){}
  } catch(err){
    hideOverlay();
    console.error(err);
    alert('Ocurrió un error: ' + err.message + '\n\nRevisa tu conexión a internet e inténtalo de nuevo.');
  }
}

// Confirmación: un solo PDF con todos los trabajos del servicio.
function showSaved(results, totalTrabajos){
  const box = $('savedBox');
  const r = results[0];
  const total = Number(totalTrabajos || 1);
  const detalle = total > 1
    ? `Se generó un solo PDF con ${total} trabajos registrados.`
    : 'Se generó un PDF para el servicio registrado.';
  const odoo = r.odoo || {};
  const odooDetalle = odoo.ok
    ? 'Odoo: PDF adjuntado al ticket ' + escapeHtml(String(odoo.ticket_ref || '')) + '.'
    : 'Odoo pendiente: ' + escapeHtml(String(odoo.error || 'No se pudo sincronizar en este momento. El informe sí quedó guardado en la web.'));
  const odooStyle = odoo.ok
    ? 'display:block;margin-top:7px;color:#176b34;font-weight:850'
    : 'display:block;margin-top:7px;color:#9a6700;font-weight:850';
  const html =
    '<div class="saved-card">' +
      '<div class="saved-ic">✓</div>' +
      '<div class="saved-txt"><strong>1 informe guardado</strong>' +
      '<span>' + escapeHtml(detalle) + '</span>' +
      '<span style="' + odooStyle + '">' + odooDetalle + '</span></div>' +
    '</div>' +
    '<div class="saved-actions">' +
      '<a class="btn btn-ghost" href="' + r.url + '" download="' + r.fname + '">⬇ Descargar informe técnico</a>' +
      '<button class="btn btn-primary" type="button" id="newReportBtn">＋ Generar nuevo informe</button>' +
    '</div>';
  box.innerHTML = html;

  const newReportBtn = document.getElementById('newReportBtn');
  if(newReportBtn){
    newReportBtn.addEventListener('click', function(){
      // Evita la alerta de "datos sin guardar" porque el informe ya fue guardado.
      if(window.zgroupMarcarGuardado) window.zgroupMarcarGuardado();
      // El nuevo informe debe comenzar completamente vacío, incluidas las presiones.
      if(window.zgClearPressureDraftStorage) window.zgClearPressureDraftStorage();
      if(window.zgClearFinalControlFields) window.zgClearFinalControlFields();
      hardClearFields(['presionAlta','presionBaja','presionAltaFinal','presionBajaFinal']);
      try{
        sessionStorage.setItem('zg_force_new_report','1');
        sessionStorage.removeItem('zg_presiones_pre_pendiente');
        localStorage.removeItem('zgroup_preinspeccion_token');
        localStorage.removeItem('zgroup_preinspeccion_id');
      }catch(e){}
      // Entra nuevamente al formulario inicial, sin token de continuación y sin caché del formulario anterior.
      window.location.replace('index.php?modo=cliente&nuevo=1&_=' + Date.now());
    });
  }

  box.classList.add('show');
  box.scrollIntoView({ behavior:'smooth', block:'center' });
  toast('✓ Informe guardado en un solo documento');
}

// Fondo difuminado del PDF (se calcula una sola vez y se reutiliza)
var _fadedBg = null;
function makeFadedBg(){
  if(_fadedBg) return _fadedBg;
  const bg = $('pdfBg');
  if(!(bg && bg.complete && bg.naturalWidth > 0)) return null;
  const cw = 595, ch = 842; // proporción A4 vertical
  const cv = document.createElement('canvas'); cv.width = cw; cv.height = ch;
  const ctx = cv.getContext('2d');
  ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, cw, ch);
  ctx.globalAlpha = 0.13; // tenue pero claramente visible
  const ar = bg.naturalWidth / bg.naturalHeight;
  const w = cw, h = w / ar, yy = (ch - h) / 2;
  ctx.drawImage(bg, 0, yy, w, h);
  ctx.globalAlpha = 1;
  _fadedBg = cv.toDataURL('image/jpeg', 0.82);
  return _fadedBg;
}

function buildPDF(sections){
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ unit:'mm', format:'a4' });

  // Colores ZGROUP
  const NAVY=[22,38,63], ACCENT=[31,111,196], GRAY=[90,107,128], FAINT=[151,163,179], LIGHT=[239,243,248], LINE=[221,228,236];

  const PW=210, PH=297, M=15, CW=PW-2*M, FOOT=18;
  let y = M;

  const data = {
    orden: $('orden').value.trim(),
    fecha: formatDate(window.ZG_FECHA_PDF_ACTUAL || ($('fecha') ? $('fecha').value : '')),
    cliente: $('cliente').value.trim(),
    tecnico: state.tecnicoNombre || '',
    direccion: $('direccion').value.trim(),
    coords: $('direccionCoords').value.trim(),
    obs: $('obs').value.trim(),
    equipoNo: getVal('equipoNo'),
    serialUnidad: getVal('serialUnidad'),
    marcaEquipo: getVal('marcaEquipo'),
    modeloEquipo: getVal('modeloEquipo'),
    controladorEquipo: getVal('controladorEquipo'),
    anioFabricacion: getVal('anioFabricacion'),
    refrigerante: getVal('refrigerante'),
    setPoint: getVal('setPoint'),
    temperaturaAmbiente: getVal('temperaturaAmbiente'),
    retornoAire: getVal('retornoAire'),
    suministroAire: getVal('suministroAire'),
    presionAlta: getVal('presionAlta'),
    presionBaja: getVal('presionBaja'),
    voltajeL1L2: getVal('voltajeL1L2'),
    voltajeL2L3: getVal('voltajeL2L3'),
    voltajeL1L3: getVal('voltajeL1L3'),
    estadoInicial: getVal('estadoInicial'),
    observacionInicial: (window.zgStripMetaFromText ? window.zgStripMetaFromText(getVal('observacionInicial')) : getVal('observacionInicial')),
    estadoFinalEquipo: getVal('estadoFinalEquipo'),
    setPointFinal: getVal('setPointFinal'),
    tempAmbienteFinal: getVal('tempAmbienteFinal'),
    presionAltaFinal: getVal('presionAltaFinal'),
    presionBajaFinal: getVal('presionBajaFinal'),
    retornoFinal: getVal('retornoFinal'),
    suministroFinal: getVal('suministroFinal'),
    voltajeFinalL1L2: getVal('voltajeFinalL1L2'),
    voltajeFinalL2L3: getVal('voltajeFinalL2L3'),
    voltajeFinalL1L3: getVal('voltajeFinalL1L3'),
    requiereOtroMantenimiento: getVal('zgRequiereOtroMantenimiento'),
    tipoOtroMantenimiento: getVal('zgTipoOtroMantenimiento'),
    motivoOtroMantenimiento: getVal('zgMotivoOtroMantenimiento'),
    horaInicioServicio: (window.zgFormatServiceDateTime ? window.zgFormatServiceDateTime(getVal('horaInicioServicio')) : getVal('horaInicioServicio')),
    horaFinServicio: (window.zgFormatServiceDateTime ? window.zgFormatServiceDateTime(getVal('horaFinServicio')) : getVal('horaFinServicio')),
    adminTiendaNombre: getVal('adminTiendaNombre'),
    adminTiendaCargo: getVal('adminTiendaCargo'),
    firmaTecnico: getVal('firmaTecnico'),
    firmaAdmin: getVal('firmaAdmin'),
    actividadesManual: getVal('actividadesManual'),
    alarmasManual: getVal('alarmasManual'),
    ptiFuncTest: getVal('ptiFuncTest'),
    ptiAfa: getVal('ptiAfa'),
    ptiBrief: getVal('ptiBrief'),
    ptiProbe: getVal('ptiProbe'),
    ptiResultado: getVal('ptiResultado'),
    mVoltL1: getVal('mVoltL1'), mVoltL2: getVal('mVoltL2'), mVoltL3: getVal('mVoltL3'), mVoltMeg: getVal('mVoltMeg'),
    mCorrL1: getVal('mCorrL1'), mCorrL2: getVal('mCorrL2'), mCorrL3: getVal('mCorrL3'), mCorrMeg: getVal('mCorrMeg'),
    mEvap1L1: getVal('mEvap1L1'), mEvap1L2: getVal('mEvap1L2'), mEvap1L3: getVal('mEvap1L3'), mEvap1Meg: getVal('mEvap1Meg'),
    mEvap2L1: getVal('mEvap2L1'), mEvap2L2: getVal('mEvap2L2'), mEvap2L3: getVal('mEvap2L3'), mEvap2Meg: getVal('mEvap2Meg'),
    mCondL1: getVal('mCondL1'), mCondL2: getVal('mCondL2'), mCondL3: getVal('mCondL3'), mCondMeg: getVal('mCondMeg'),
    mCompL1: getVal('mCompL1'), mCompL2: getVal('mCompL2'), mCompL3: getVal('mCompL3'), mCompMeg: getVal('mCompMeg'),
    mResL1: getVal('mResL1'), mResL2: getVal('mResL2'), mResL3: getVal('mResL3'), mResMeg: getVal('mResMeg'),
    observacionesManual: getVal('observacionesManual'),
    recomendacionesManual: getVal('recomendacionesManual'),
    repuestosManual: getVal('requiereRepuesto') === 'si' ? getVal('repuestosManual') : '',
  };

  // Fondo de contenedores: imagen ya difuminada (horneada en canvas) → siempre se ve
  function drawBg(){
    const f = makeFadedBg();
    if(f){ try{ doc.addImage(f, 'JPEG', 0, 0, PW, PH); }catch(e){} }
  }

  function ensure(h){ if(y + h > PH - FOOT){ doc.addPage(); y = M; drawBg(); } }

  drawBg(); // fondo de la primera página

  // -------- Encabezado --------
  const logoEl = $('brandLogo');
  let logoH = 0;
  if(logoEl && logoEl.complete && logoEl.naturalWidth > 0){
    const maxW=48, maxH=15, ar = logoEl.naturalWidth/logoEl.naturalHeight;
    let lw = maxW, lh = lw/ar;
    if(lh > maxH){ lh = maxH; lw = lh*ar; }
    try{ doc.addImage(logoEl, 'PNG', M, y, lw, lh); logoH = lh; }catch(e){}
  }
  doc.setFont('helvetica','bold'); doc.setFontSize(16); doc.setTextColor(...NAVY);
  doc.text('INFORME TÉCNICO', PW-M, y+5.5, { align:'right' });
  doc.setFont('helvetica','normal'); doc.setFontSize(9.5); doc.setTextColor(...ACCENT);
  doc.text('ZGROUP S.A.C. · Área técnica', PW-M, y+11, { align:'right' });
  y += Math.max(logoH, 13) + 4;
  doc.setDrawColor(...ACCENT); doc.setLineWidth(0.9); doc.line(M, y, PW-M, y);
  y += 8;

  // -------- Caja de datos generales --------
  const colW = (CW/2) - 4;
  const addrLines = doc.splitTextToSize(data.direccion || '—', CW-12);
  const coordLines = data.coords ? doc.splitTextToSize('Coordenadas: ' + data.coords, CW-12) : null;
  const obsLines = data.obs ? doc.splitTextToSize(data.obs, CW-12) : null;

  let bh = 7;                          // pad top
  bh += 11;                            // fila 1 (orden / fecha)
  bh += 11;                            // fila 2 (cliente / tecnico)
  bh += 5 + addrLines.length*4.6 + 3;  // dirección
  if(coordLines){ bh += coordLines.length*4.2 + 2; }
  if(obsLines){ bh += 5 + obsLines.length*4.6 + 3; }
  bh += 4;                             // pad bottom

  ensure(bh);
  doc.setFillColor(...LIGHT); doc.setDrawColor(...LINE); doc.setLineWidth(0.3);
  doc.roundedRect(M, y, CW, bh, 3, 3, 'FD');

  let yy = y + 8;
  drawField(M+6,           yy, colW, 'N° DE REPORTE', data.orden || '—');
  drawField(M+6+CW/2,      yy, colW, 'FECHA',       data.fecha || '—');
  yy += 11;
  drawField(M+6,           yy, colW, 'CLIENTE',     data.cliente || '—');
  drawField(M+6+CW/2,      yy, colW, 'TÉCNICO',     data.tecnico || '—');
  yy += 11;
  // Dirección (ancho completo)
  doc.setFont('helvetica','bold'); doc.setFontSize(7.5); doc.setTextColor(...ACCENT);
  doc.text('DIRECCIÓN / UBICACIÓN', M+6, yy);
  doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(...NAVY);
  doc.text(addrLines, M+6, yy+4.6);
  yy += 5 + addrLines.length*4.6 + 1;
  if(coordLines){
    doc.setFont('helvetica','bold'); doc.setFontSize(8); doc.setTextColor(...GRAY);
    doc.text(coordLines, M+6, yy+3.8);
    yy += coordLines.length*4.2 + 2;
  }
  yy += 2;
  // Observaciones
  if(obsLines){
    doc.setFont('helvetica','bold'); doc.setFontSize(7.5); doc.setTextColor(...ACCENT);
    doc.text('OBSERVACIONES GENERALES', M+6, yy);
    doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(...NAVY);
    doc.text(obsLines, M+6, yy+4.6);
  }
  y += bh + 9;

  function drawNavyPdfTitle(title){
    ensure(13);
    doc.setFillColor(...NAVY);
    doc.setDrawColor(...NAVY);
    doc.roundedRect(M, y, CW, 8.8, 1.4, 1.4, 'F');
    doc.setDrawColor(...ACCENT);
    doc.setLineWidth(0.8);
    doc.line(M, y+8.8, M+CW, y+8.8);
    doc.setFont('helvetica','bold');
    doc.setFontSize(10.4);
    doc.setTextColor(255,255,255);
    doc.text(String(title || '').toUpperCase(), M+4, y+5.9);
    y += 12.5;
  }

  // Inspección preliminar del equipo, si el técnico la llenó
  const equipoPairs = [
    ['Contenedor / equipo', data.equipoNo], ['Serial unidad', data.serialUnidad], ['Marca', data.marcaEquipo], ['Modelo', data.modeloEquipo],
    ['Controlador', data.controladorEquipo], ['Año de fabricación', data.anioFabricacion], ['Refrigerante', data.refrigerante], ['Set point', data.setPoint ? data.setPoint + ' °C' : ''], ['Temp. ambiente', data.temperaturaAmbiente ? data.temperaturaAmbiente + ' °C' : ''], ['Retorno aire', data.retornoAire ? data.retornoAire + ' °C' : ''],
    ['Suministro aire', data.suministroAire ? data.suministroAire + ' °C' : ''], ['Voltaje L1-L2', data.voltajeL1L2], ['Voltaje L2-L3', data.voltajeL2L3], ['Voltaje L1-L3', data.voltajeL1L3],
    ['Estado inicial', data.estadoInicial]
  ].filter(p => p[1]);

  function drawPreliminarRow(left, right){
    const half = CW / 2;
    const labelW = 33;
    const valW = half - labelW;
    const leftLines = doc.splitTextToSize(String(left && left[1] ? left[1] : '—'), valW-5);
    const rightLines = right ? doc.splitTextToSize(String(right[1] || '—'), valW-5) : [];
    const h = Math.max(8.5, leftLines.length*4.2 + 4, rightLines.length*4.2 + 4);
    ensure(h);
    let x = M;
    doc.setLineWidth(0.25);
    doc.setDrawColor(180, 196, 216);

    // label izquierdo
    doc.setFillColor(...NAVY);
    doc.rect(x, y, labelW, h, 'FD');
    doc.setFont('helvetica','bold');
    doc.setFontSize(7.2);
    doc.setTextColor(255,255,255);
    doc.text(doc.splitTextToSize(String(left[0]).toUpperCase(), labelW-4), x+2, y+4.9);
    x += labelW;

    // valor izquierdo
    doc.setFillColor(255,255,255);
    doc.rect(x, y, valW, h, 'FD');
    doc.setFont('helvetica','normal');
    doc.setFontSize(8.6);
    doc.setTextColor(...NAVY);
    doc.text(leftLines, x+2.5, y+5);
    x += valW;

    if(right){
      doc.setFillColor(...NAVY);
      doc.rect(x, y, labelW, h, 'FD');
      doc.setFont('helvetica','bold');
      doc.setFontSize(7.2);
      doc.setTextColor(255,255,255);
      doc.text(doc.splitTextToSize(String(right[0]).toUpperCase(), labelW-4), x+2, y+4.9);
      x += labelW;
      doc.setFillColor(255,255,255);
      doc.rect(x, y, valW, h, 'FD');
      doc.setFont('helvetica','normal');
      doc.setFontSize(8.6);
      doc.setTextColor(...NAVY);
      doc.text(rightLines, x+2.5, y+5);
    }else{
      doc.setFillColor(255,255,255);
      doc.rect(x, y, half, h, 'FD');
    }
    y += h;
  }

  function drawPreliminarFullRow(label, value){
    const labelW = 34;
    const lines = doc.splitTextToSize(String(value || '—'), CW-labelW-6);
    const h = Math.max(9, lines.length*4.2 + 4.5);
    ensure(h);
    doc.setDrawColor(180, 196, 216);
    doc.setLineWidth(0.25);
    doc.setFillColor(...NAVY);
    doc.rect(M, y, labelW, h, 'FD');
    doc.setFont('helvetica','bold');
    doc.setFontSize(7.2);
    doc.setTextColor(255,255,255);
    doc.text(doc.splitTextToSize(String(label).toUpperCase(), labelW-4), M+2, y+5);
    doc.setFillColor(255,255,255);
    doc.rect(M+labelW, y, CW-labelW, h, 'FD');
    doc.setFont('helvetica','normal');
    doc.setFontSize(8.6);
    doc.setTextColor(...NAVY);
    doc.text(lines, M+labelW+3, y+5);
    y += h;
  }

  if(equipoPairs.length || data.observacionInicial){
    drawNavyPdfTitle('Inspección preliminar del equipo');

    // Tabla tipo checklist: etiquetas en azul marino y valores limpios.
    for(let i=0;i<equipoPairs.length;i+=2){
      drawPreliminarRow(equipoPairs[i], equipoPairs[i+1]);
    }
    if(data.observacionInicial){
      drawPreliminarFullRow('Observación inicial', window.zgStripMetaFromText?window.zgStripMetaFromText(data.observacionInicial):data.observacionInicial);
    }
    y += 7;
  }

  function drawField(x, baseY, w, label, value){
    doc.setFont('helvetica','bold'); doc.setFontSize(7.5); doc.setTextColor(...ACCENT);
    doc.text(label, x, baseY);
    doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(...NAVY);
    const v = doc.splitTextToSize(value, w);
    doc.text(v[0] || '—', x, baseY+4.6);
  }

  function drawBulletSection(title, arr){
    if(!arr || !arr.length) return;
    ensure(8);
    doc.setFont('helvetica','bold'); doc.setFontSize(9.5); doc.setTextColor(...ACCENT);
    doc.text(title, M, y+3.5); y += 5.5;
    arr.forEach((txt) => {
      const lines = doc.splitTextToSize(String(txt), CW-6);
      ensure(lines.length*4.3+1.2);
      doc.setFont('helvetica','normal'); doc.setFontSize(9.2); doc.setTextColor(...NAVY);
      doc.text('•', M, y+3.3); doc.text(lines, M+5, y+3.3);
      y += lines.length*4.3 + 1.2;
    });
    y += 2;
  }

  function drawPtiTable(auto){
    if(!auto || !auto.pti || !Object.keys(auto.pti).length) return;
    ensure(12);
    doc.setFont('helvetica','bold'); doc.setFontSize(9.5); doc.setTextColor(...ACCENT);
    doc.text('CHECKLIST PTI / RUN TEST', M, y+3.5); y += 6;
    const entries = Object.entries(auto.pti);
    const colW = (CW-4)/2;
    for(let i=0;i<entries.length;i+=2){
      ensure(7);
      [0,1].forEach(j=>{
        const e=entries[i+j]; if(!e) return;
        const x=M+j*(colW+4);
        doc.setDrawColor(...LINE); doc.setFillColor(247,250,253); doc.rect(x,y,colW,6.4,'FD');
        doc.setFont('helvetica','normal'); doc.setFontSize(7.2); doc.setTextColor(...NAVY);
        doc.text(doc.splitTextToSize(e[0], colW-14)[0], x+2, y+4.2);
        doc.setFont('helvetica','bold'); doc.setFontSize(7.2); doc.setTextColor(...ACCENT);
        doc.text(e[1], x+colW-3, y+4.2, {align:'right'});
      });
      y += 6.4;
    }
    y += 5;
  }


  function splitManualList(txt){
    return String(txt || '')
      .split(/\r?\n|;/)
      .map(s => s.replace(/^\s*[-•\d.)]+\s*/u, '').trim())
      .filter(Boolean);
  }

  function parseRepuestos(txt){
    return String(txt || '')
      .split(/\r?\n/)
      .map(line => line.trim())
      .filter(Boolean)
      .map(line => {
        let p = line.split('|').map(x => x.trim());
        if(p.length < 3) p = line.split(';').map(x => x.trim());
        if(p.length < 3) p = line.split(',').map(x => x.trim());
        if(p.length >= 3) return {codigo:p[0], detalle:p.slice(1,-1).join(' | '), cant:p[p.length-1]};
        if(p.length === 2) return {codigo:p[0], detalle:p[1], cant:''};
        return {codigo:'', detalle:line, cant:''};
      });
  }

  function hasManualParams(){
    const ids = ['mVoltL1','mVoltL2','mVoltL3','mVoltMeg','mCorrL1','mCorrL2','mCorrL3','mCorrMeg','mEvap1L1','mEvap1L2','mEvap1L3','mEvap1Meg','mEvap2L1','mEvap2L2','mEvap2L3','mEvap2Meg','mCondL1','mCondL2','mCondL3','mCondMeg','mCompL1','mCompL2','mCompL3','mCompMeg','mResL1','mResL2','mResL3','mResMeg'];
    return ids.some(k => String(data[k] || '').trim() !== '');
  }

  function drawPtiResumenManual(){
    const pruebas = [
      ['FUNC TEST', data.ptiFuncTest], ['AFA M PTI', data.ptiAfa], ['BRIEF PTI', data.ptiBrief], ['PROBE TEST', data.ptiProbe], ['RESULTADO PTI', data.ptiResultado]
    ].filter(p => p[1]);
    if(!data.alarmasManual && !pruebas.length) return;
    ensure(16);
    doc.setFont('helvetica','bold'); doc.setFontSize(10.5); doc.setTextColor(...ACCENT);
    doc.text('ALARMAS / PRUEBAS PTI', M, y); y += 4;
    if(data.alarmasManual){
      const al = doc.splitTextToSize(data.alarmasManual, CW-6);
      ensure(8 + al.length*4.2);
      doc.setDrawColor(...LINE); doc.setFillColor(247,250,253); doc.rect(M, y, CW, 8 + al.length*4.2, 'FD');
      doc.setFont('helvetica','bold'); doc.setFontSize(7.4); doc.setTextColor(...GRAY); doc.text('Alarmas', M+3, y+4.8);
      doc.setFont('helvetica','normal'); doc.setFontSize(8.7); doc.setTextColor(...NAVY); doc.text(al, M+34, y+4.8);
      y += 8 + al.length*4.2;
    }
    if(pruebas.length){
      const cols = pruebas.length;
      const cw = CW / cols;
      ensure(13);
      pruebas.forEach((p,i)=>{
        const x = M + i*cw;
        doc.setDrawColor(...LINE); doc.setFillColor(24,52,92); doc.rect(x, y, cw, 6, 'FD');
        doc.setFont('helvetica','bold'); doc.setFontSize(6.7); doc.setTextColor(255,255,255); doc.text(doc.splitTextToSize(p[0], cw-4)[0], x+cw/2, y+3.9, {align:'center'});
        doc.setFillColor(255,255,255); doc.rect(x, y+6, cw, 7, 'FD');
        doc.setFont('helvetica','bold'); doc.setFontSize(8.2); doc.setTextColor(...NAVY); doc.text(String(p[1]), x+cw/2, y+10.7, {align:'center'});
      });
      y += 16;
    }
    y += 3;
  }

  function drawManualParamTable(){
    if(!hasManualParams()) return;
    const rows = [
      ['VOLTAJE', data.mVoltL1, data.mVoltL2, data.mVoltL3, data.mVoltMeg],
      ['CORRIENTE', data.mCorrL1, data.mCorrL2, data.mCorrL3, data.mCorrMeg],
      ['MOTOR EVAP1', data.mEvap1L1, data.mEvap1L2, data.mEvap1L3, data.mEvap1Meg],
      ['MOTOR EVAP2', data.mEvap2L1, data.mEvap2L2, data.mEvap2L3, data.mEvap2Meg],
      ['MOTOR COND', data.mCondL1, data.mCondL2, data.mCondL3, data.mCondMeg],
      ['COMPRESOR', data.mCompL1, data.mCompL2, data.mCompL3, data.mCompMeg],
      ['RESISTENCIAS', data.mResL1, data.mResL2, data.mResL3, data.mResMeg]
    ];
    ensure(16);
    doc.setFont('helvetica','bold'); doc.setFontSize(10.5); doc.setTextColor(...ACCENT);
    doc.text('PARÁMETROS ELÉCTRICOS Y AISLAMIENTO', M, y); y += 4;
    const widths = [44, 30, 30, 30, CW-134];
    const headers = ['PARÁMETRO','L1','L2','L3','MΩ / OBS.'];
    ensure(8);
    let x = M;
    headers.forEach((h,i)=>{ doc.setDrawColor(...LINE); doc.setFillColor(24,52,92); doc.rect(x,y,widths[i],7,'FD'); doc.setFont('helvetica','bold'); doc.setFontSize(7.2); doc.setTextColor(255,255,255); doc.text(h, x+widths[i]/2, y+4.6, {align:'center'}); x += widths[i]; });
    y += 7;
    rows.forEach(r => {
      const has = r.slice(1).some(v => String(v || '').trim() !== '');
      if(!has) return;
      ensure(7.2);
      x = M;
      r.forEach((v,i)=>{
        doc.setDrawColor(...LINE); doc.setFillColor(i===0 ? 247 : 255, i===0 ? 250 : 255, i===0 ? 253 : 255); doc.rect(x,y,widths[i],7.2,'FD');
        doc.setFont('helvetica', i===0 ? 'bold' : 'normal'); doc.setFontSize(i===0 ? 7.4 : 8.3); doc.setTextColor(...NAVY);
        doc.text(String(v || '-'), x+widths[i]/2, y+4.8, {align:'center'});
        x += widths[i];
      });
      y += 7.2;
    });
    y += 6;
  }

  function drawRepuestosManual(rows){
    rows = (rows || []).map(r => ({
      codigo: String(r.codigo || '').trim() || '-',
      detalle: String(r.detalle || '').trim(),
      cant: String(r.cant || r.cantidad || '').trim() || '1'
    })).filter(r => r.detalle !== '' || r.codigo !== '-');
    if(!rows.length) return;

    ensure(18);
    doc.setFont('helvetica','bold');
    doc.setFontSize(11.2);
    doc.setTextColor(...ACCENT);
    doc.text('REPUESTOS A CONSIDERAR', M, y);
    y += 6;

    const widths = [34, CW-52, 18];
    const headers = ['CÓDIGO','DETALLE','CANT.'];

    ensure(8);
    let x = M;
    doc.setLineWidth(0.25);
    headers.forEach((h,i)=>{
      doc.setDrawColor(210, 222, 238);
      doc.setFillColor(24,52,92);
      doc.rect(x,y,widths[i],7.5,'FD');
      doc.setFont('helvetica','bold');
      doc.setFontSize(7.4);
      doc.setTextColor(255,255,255);
      doc.text(h, x+widths[i]/2, y+5, {align:'center'});
      x += widths[i];
    });
    y += 7.5;

    rows.forEach((r, idx) => {
      const codeLines = doc.splitTextToSize(r.codigo || '-', widths[0]-4);
      const detLines = doc.splitTextToSize(r.detalle || '-', widths[1]-5);
      const cantText = String(r.cant || '1');
      const h = Math.max(9, codeLines.length*4.2 + 4.2, detLines.length*4.2 + 4.2);
      ensure(h);

      const bg = (idx % 2 === 0) ? [255,255,255] : [247,250,254];
      x = M;
      doc.setLineWidth(0.25);
      doc.setDrawColor(210, 222, 238);

      // Código
      doc.setFillColor(...bg);
      doc.rect(x,y,widths[0],h,'FD');
      doc.setFont('helvetica','bold');
      doc.setFontSize(8.1);
      doc.setTextColor(...NAVY);
      doc.text(codeLines, x+2, y+5);
      x += widths[0];

      // Detalle
      doc.setFillColor(...bg);
      doc.rect(x,y,widths[1],h,'FD');
      doc.setFont('helvetica','normal');
      doc.setFontSize(8.1);
      doc.setTextColor(...NAVY);
      doc.text(detLines, x+2.5, y+5);
      x += widths[1];

      // Cantidad
      doc.setFillColor(...bg);
      doc.rect(x,y,widths[2],h,'FD');
      doc.setFont('helvetica','bold');
      doc.setFontSize(8.3);
      doc.setTextColor(...NAVY);
      doc.text(cantText, x+widths[2]/2, y+5, {align:'center'});
      y += h;
    });
    y += 7;
  }

  function collectWorkMaterialsLegacy(s, idx){
    const clean = v => String(v == null ? '' : v).replace(/\s+/g,' ').trim();
    const normalize = arr => (Array.isArray(arr) ? arr : []).map(r => ({
      codigo: clean(r && (r.codigo || r.code || r.cod || '')),
      detalle: clean(r && (r.detalle || r.material || r.nombre || r.descripcion || '')),
      cantidad: clean(r && (r.cantidad || r.qty || r.cant || '1')) || '1',
      unidad: clean(r && (r.unidad || r.und || r.unit || 'und')) || 'und'
    })).filter(r => r.detalle);
    const snap = window.ZG_WORK_MATERIALS_PDF || {};
    const candidates = [
      snap[s && s.id ? s.id : String(idx)],
      s && s.repuestosTrabajo,
      s && s.materialesTrabajo,
      s && s.materiales,
      s && s.repuestos,
      s && s.campos && s.campos.repuestosTrabajo,
      s && s.campos && s.campos.materialesTrabajo
    ];
    for(const c of candidates){
      const rows = normalize(c);
      if(rows.length) return rows;
    }
    return [];
  }

  function drawWorkMaterialsLegacy(rows, workName){
    rows = Array.isArray(rows) ? rows : [];
    if(!rows.length) return;
    drawNavyPdfTitle('Materiales / repuestos - ' + (workName || 'trabajo'));
    const widths = [32, CW-74, 20, 22];
    const heads = ['CÓDIGO','DETALLE','CANT.','UND.'];
    let x = M;
    ensure(8);
    heads.forEach((h,i)=>{
      doc.setDrawColor(210,222,238); doc.setFillColor(...NAVY); doc.rect(x,y,widths[i],7.5,'FD');
      doc.setFont('helvetica','bold'); doc.setFontSize(7.1); doc.setTextColor(255,255,255);
      doc.text(h,x+widths[i]/2,y+5,{align:'center'}); x+=widths[i];
    });
    y += 7.5;
    rows.forEach((r,i)=>{
      const cells=[
        doc.splitTextToSize(r.codigo || 'Sin código', widths[0]-4),
        doc.splitTextToSize(r.detalle || '-', widths[1]-5),
        [String(r.cantidad || '1')],
        [String(r.unidad || 'und')]
      ];
      const h=Math.max(9,cells[0].length*4.1+4,cells[1].length*4.1+4);
      ensure(h); x=M;
      cells.forEach((cell,j)=>{
        doc.setDrawColor(210,222,238); doc.setFillColor(i%2?247:255,i%2?250:255,i%2?254:255); doc.rect(x,y,widths[j],h,'FD');
        doc.setFont('helvetica',j===1?'normal':'bold'); doc.setFontSize(7.9); doc.setTextColor(...NAVY);
        doc.text(cell, x+(j>=2?widths[j]/2:2.4), y+5, {align:j>=2?'center':'left'}); x+=widths[j];
      });
      y += h;
    });
    y += 6;
  }

  // -------- Secciones de trabajo --------
  sections.forEach((s, idx) => {
    ensure(16);
    // barra de título
    doc.setFillColor(...ACCENT); doc.roundedRect(M, y, 3, 8.5, 1.5, 1.5, 'F');
    doc.setFillColor(231,240,251); doc.setDrawColor(197,219,245); doc.setLineWidth(0.3);
    doc.roundedRect(M+4, y, CW-4, 8.5, 1.5, 1.5, 'FD');
    doc.setFont('helvetica','bold'); doc.setFontSize(11.5); doc.setTextColor(...[21,82,147]);
    doc.text(`${idx+1}.  ${s.nombre}`, M+8, y+5.7);
    y += 8.5 + 5;

    // Resumen automático marcado por el técnico
    if(s.auto){
      drawBulletSection('ACTIVIDADES REALIZADAS', s.auto.actividades);
      drawBulletSection('HALLAZGOS', s.auto.hallazgos);
      drawBulletSection('ACCIONES EJECUTADAS', s.auto.acciones);
      // Recomendaciones del técnico omitidas del PDF.
      drawPtiTable(s.auto);
    }

    // campos del trabajo (label: valor)
    const defs = CAMPOS[s.id] || [];
    const llenos = defs.filter(d => (s.campos[d.id] || '').toString().trim() !== '');
    llenos.forEach(d => {
      const val = s.campos[d.id].toString().trim();
      doc.setFont('helvetica','bold'); doc.setFontSize(9); doc.setTextColor(...GRAY);
      const lblTxt = d.label + ':  ';
      const lblW = doc.getTextWidth(lblTxt);
      // valor (puede ser multilínea)
      doc.setFont('helvetica','normal'); doc.setFontSize(9.5); doc.setTextColor(...NAVY);
      const valLines = doc.splitTextToSize(val, CW - lblW);
      ensure(valLines.length * 4.4 + 1.5);
      doc.setFont('helvetica','bold'); doc.setFontSize(9); doc.setTextColor(...GRAY);
      doc.text(lblTxt, M, y + 3.4);
      doc.setFont('helvetica','normal'); doc.setFontSize(9.5); doc.setTextColor(...NAVY);
      doc.text(valLines, M + lblW, y + 3.4);
      y += valLines.length * 4.4 + 1.5;
    });
    if(llenos.length) y += 2;

    // observaciones / notas adicionales
    if(s.detalle && s.detalle.trim()){
      const dl = doc.splitTextToSize(s.detalle.trim(), CW);
      doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(...NAVY);
      for(const line of dl){
        ensure(5.2);
        doc.text(line, M, y+3.6);
        y += 5.2;
      }
      y += 3;
    }

    // Materiales del trabajo: siempre antes de sus evidencias fotográficas.
    const materialesDelTrabajo = collectWorkMaterialsLegacy(s, idx);
    if(materialesDelTrabajo.length) drawWorkMaterialsLegacy(materialesDelTrabajo, s.nombre);

    // fotos en cuadrícula 2 columnas
    if(s.photos.length){
      const gap = 6;
      const cw = (CW - gap) / 2;
      const maxImgH = 62;
      for(let i=0; i<s.photos.length; i+=2){
        const left = s.photos[i];
        const right = s.photos[i+1];
        const cells = [left, right].filter(Boolean).map(p => {
          const ar = p.w / p.h;
          let dw = cw, dh = dw / ar;
          if(dh > maxImgH){ dh = maxImgH; dw = dh * ar; }
          const capLines = (p.caption && p.caption.trim())
            ? doc.splitTextToSize(p.caption.trim(), cw) : [];
          return { p, dw, dh, capLines, blockH: dh + (capLines.length ? 2 + capLines.length*3.6 : 0) };
        });
        const rowH = Math.max(...cells.map(c => c.blockH)) + 6;
        ensure(rowH);
        cells.forEach((c, j) => {
          const x = M + j*(cw + gap);
          const offX = x + (cw - c.dw)/2;   // centrar si es vertical
          try{ doc.addImage(c.p.dataUrl, 'JPEG', offX, y, c.dw, c.dh); }catch(e){}
          doc.setDrawColor(...LINE); doc.setLineWidth(0.3);
          doc.rect(offX, y, c.dw, c.dh);
          if(c.capLines.length){
            doc.setFont('helvetica','italic'); doc.setFontSize(8); doc.setTextColor(...GRAY);
            doc.text(c.capLines, x, y + c.dh + 4.2);
          }
        });
        y += rowH;
      }
    } else {
      doc.setFont('helvetica','italic'); doc.setFontSize(9); doc.setTextColor(...FAINT);
      ensure(6); doc.text('(Sin fotos en esta sección)', M, y+3.6); y += 6;
    }
    y += 5;
  });


  function salidaSupervisionParaPDF(){
    try{
      if(typeof window.getSalidaAsignadaActual === 'function') return window.getSalidaAsignadaActual();
    }catch(e){}
    return null;
  }

  function drawSalidaSupervisionInline(salida){
    if(!salida) return;
    const materiales = Array.isArray(salida.materiales) ? salida.materiales : [];
    const apoyo = Array.isArray(salida.tecnicos_apoyo) ? salida.tecnicos_apoyo.map(t => (t && (t.nombre || t.name)) ? (t.nombre || t.name) : String(t || '')).filter(Boolean) : [];
    ensure(24);
    drawNavyPdfTitle('Materiales e insumos asignados por supervisión');

    const info = [
      ['N° reporte', salida.cotizacion || data.orden || '-'],
      ['Técnico responsable', salida.tecnico_responsable || data.tecnico || '-'],
      ['Equipo / unidad', salida.equipo || data.equipoNo || '-'],
      ['Técnicos de apoyo', apoyo.length ? apoyo.join(', ') : 'Sin apoyo asignado']
    ];
    for(let i=0;i<info.length;i+=2){
      drawPreliminarRow(info[i], info[i+1]);
    }
    y += 3;

    if(materiales.length){
      const widths = [30, 82, 18, 22, CW-30-82-18-22];
      const headers = ['CÓDIGO','MATERIAL / REPUESTO','CANT.','UNIDAD','OBS.'];
      ensure(8);
      let x = M;
      headers.forEach((h,i)=>{
        doc.setFillColor(...NAVY);
        doc.setDrawColor(180,196,216);
        doc.rect(x,y,widths[i],7.4,'FD');
        doc.setFont('helvetica','bold');
        doc.setFontSize(6.9);
        doc.setTextColor(255,255,255);
        doc.text(doc.splitTextToSize(h, widths[i]-3)[0], x+widths[i]/2, y+4.8, {align:'center'});
        x += widths[i];
      });
      y += 7.4;
      materiales.forEach((m,idx)=>{
        const vals = [m.codigo || '-', m.detalle || '-', m.cantidad || '1', m.unidad || 'und', m.observacion || '-'];
        const wrapped = vals.map((v,i)=>doc.splitTextToSize(String(v), widths[i]-4));
        const h = Math.max(9, ...wrapped.map(w=>w.length*4.1 + 4));
        ensure(h);
        x = M;
        wrapped.forEach((lines,i)=>{
          const bg = idx % 2 === 0 ? [255,255,255] : [247,250,254];
          doc.setFillColor(...bg);
          doc.setDrawColor(210,222,238);
          doc.rect(x,y,widths[i],h,'FD');
          doc.setFont('helvetica', i===0 ? 'bold' : 'normal');
          doc.setFontSize(7.8);
          doc.setTextColor(...NAVY);
          doc.text(lines, x+2, y+5);
          x += widths[i];
        });
        y += h;
      });
      y += 6;
    } else {
      drawPreliminarFullRow('Materiales', 'No se registraron materiales preparados para esta salida.');
      y += 4;
    }

    if(salida.observacion){
      drawPreliminarFullRow('Nota supervisión', salida.observacion);
      y += 4;
    }
  }

  // -------- Materiales / insumos antes del control final --------
  drawSalidaSupervisionInline(salidaSupervisionParaPDF());
  drawRepuestosManual(parseRepuestos(data.repuestosManual));


  // -------- Control final del equipo --------
  const finalPairs = [
    ['Hora de inicio del servicio', data.horaInicioServicio],
    ['Hora de finalización del servicio', data.horaFinServicio],
    ['Estado final', data.estadoFinalEquipo],
    ['Set point final', data.setPointFinal ? data.setPointFinal + ' °C' : ''],
    ['Temp. ambiente final', data.tempAmbienteFinal ? data.tempAmbienteFinal + ' °C' : ''],
    ['Retorno final', data.retornoFinal ? data.retornoFinal + ' °C' : ''],
    ['Suministro final', data.suministroFinal ? data.suministroFinal + ' °C' : ''],
    ['Voltaje final L1-L2', data.voltajeFinalL1L2],
    ['Voltaje final L2-L3', data.voltajeFinalL2L3],
    ['Voltaje final L1-L3', data.voltajeFinalL1L3]
  ].filter(p => p[1]);

  if(finalPairs.length || data.requiereOtroMantenimiento){
    ensure(18);
    doc.setFont('helvetica','bold'); doc.setFontSize(10.5); doc.setTextColor(...ACCENT);
    doc.text('CONTROL FINAL DEL EQUIPO', M, y); y += 4;
    const rowH = 7.5, c1 = CW/4;
    for(let i=0;i<finalPairs.length;i+=2){
      ensure(rowH+1);
      const a=finalPairs[i], b=finalPairs[i+1];
      doc.setDrawColor(...LINE); doc.setLineWidth(.25);
      doc.setFillColor(247,250,253); doc.rect(M, y, CW, rowH, 'FD');
      doc.setFont('helvetica','bold'); doc.setFontSize(7.3); doc.setTextColor(...GRAY); doc.text(a[0], M+3, y+4.7);
      doc.setFont('helvetica','normal'); doc.setFontSize(8.5); doc.setTextColor(...NAVY); doc.text(String(a[1]), M+c1, y+4.7);
      if(b){ doc.setFont('helvetica','bold'); doc.setFontSize(7.3); doc.setTextColor(...GRAY); doc.text(b[0], M+CW/2+3, y+4.7); doc.setFont('helvetica','normal'); doc.setFontSize(8.5); doc.setTextColor(...NAVY); doc.text(String(b[1]), M+CW/2+c1, y+4.7); }
      y += rowH;
    }
    if(data.requiereOtroMantenimiento){
      const textoMant = data.requiereOtroMantenimiento === 'Sí'
        ? 'Sí · ' + (data.tipoOtroMantenimiento || 'Tipo no indicado') + '. ' + (data.motivoOtroMantenimiento || '')
        : 'No';
      const fl = doc.splitTextToSize(textoMant, CW-6);
      ensure(8 + fl.length*4.4);
      doc.setDrawColor(...LINE); doc.setFillColor(247,250,253); doc.rect(M, y, CW, 8 + fl.length*4.4, 'FD');
      doc.setFont('helvetica','bold'); doc.setFontSize(7.3); doc.setTextColor(...GRAY); doc.text('¿Requiere otro mantenimiento?', M+3, y+4.7);
      doc.setFont('helvetica','normal'); doc.setFontSize(8.5); doc.setTextColor(...NAVY); doc.text(fl, M+52, y+4.7);
      y += 8 + fl.length*4.4;
    }
    y += 6;
  }


  // -------- Conformidad y firmas --------
  function drawFirmasServicio(){
    // La conformidad usa una página exclusiva para que el título y las firmas
    // nunca se separen, aunque la página anterior todavía tenga algo de espacio.
    doc.addPage();
    y = M;
    drawBg();

    const logo = $('brandLogo');
    if(logo && logo.complete && logo.naturalWidth > 0){
      try{ doc.addImage(logo,'PNG',M,y,38,12); }catch(e){}
    }
    doc.setFont('helvetica','bold'); doc.setFontSize(14); doc.setTextColor(...NAVY);
    doc.text('INFORME TÉCNICO DE SERVICIO', PW-M, y+5.5, {align:'right'});
    doc.setFont('helvetica','normal'); doc.setFontSize(8.2); doc.setTextColor(...GRAY);
    doc.text('ZGROUP S.A.C. · Área técnica', PW-M, y+11, {align:'right'});
    y += 18;
    doc.setFillColor(...ACCENT); doc.rect(0,y,PW,1.5,'F'); y += 12;

    drawNavyPdfTitle('Conformidad del servicio');
    doc.setFont('helvetica','normal'); doc.setFontSize(8.6); doc.setTextColor(...GRAY);
    const conf = doc.splitTextToSize('Las firmas registran la conformidad de la atención técnica y la recepción del servicio por parte del cliente.', CW);
    doc.text(conf, M, y+3.5);
    y += Math.max(10, conf.length*4.2 + 4);

    const tecnicoNombre = data.tecnico || 'Técnico responsable';
    const adminNombre = data.adminTiendaNombre || 'Responsable del cliente';
    const gap = 8;
    const boxW = (CW-gap)/2;
    const boxH = 44;
    const boxes = [
      {x:M, title:'TÉCNICO RESPONSABLE', name:tecnicoNombre, cargo:'Técnico de servicio', firma:data.firmaTecnico},
      {x:M+boxW+gap, title:'RESPONSABLE DEL CLIENTE', name:adminNombre, cargo:data.adminTiendaCargo || 'Cargo no registrado', firma:data.firmaAdmin}
    ];
    boxes.forEach(b => {
      doc.setDrawColor(...LINE); doc.setFillColor(250,252,255); doc.roundedRect(b.x, y, boxW, boxH, 2, 2, 'FD');
      doc.setFont('helvetica','bold'); doc.setFontSize(7.4); doc.setTextColor(...ACCENT); doc.text(b.title, b.x+4, y+5);
      if(b.firma && /^data:image\//.test(b.firma)){
        try{ doc.addImage(b.firma, 'PNG', b.x+10, y+8, boxW-20, 16); }catch(e){}
      }
      doc.setDrawColor(85,102,126); doc.setLineWidth(0.25); doc.line(b.x+10, y+27, b.x+boxW-10, y+27);
      doc.setFont('helvetica','bold'); doc.setFontSize(8.2); doc.setTextColor(...NAVY);
      doc.text(doc.splitTextToSize(b.name || '—', boxW-12)[0] || '—', b.x+boxW/2, y+33, {align:'center'});
      doc.setFont('helvetica','normal'); doc.setFontSize(7.3); doc.setTextColor(...GRAY);
      doc.text(doc.splitTextToSize(b.cargo || '—', boxW-12)[0] || '—', b.x+boxW/2, y+38, {align:'center'});
    });
    y += boxH + 6;
  }

  drawFirmasServicio();

  // -------- Pie de página en todas las páginas --------
  const stamp = new Date().toLocaleString('es-PE');
  const total = doc.internal.getNumberOfPages();
  for(let p=1; p<=total; p++){
    doc.setPage(p);
    doc.setDrawColor(...LINE); doc.setLineWidth(0.3);
    doc.line(M, PH-12, PW-M, PH-12);
    doc.setFont('helvetica','normal'); doc.setFontSize(8); doc.setTextColor(...FAINT);
    doc.text('Generado el ' + stamp, M, PH-7.5);
    doc.text(`Página ${p} de ${total}`, PW-M, PH-7.5, { align:'right' });
  }

  return doc;
}

function formatDate(iso){
  if(!iso) return '';
  const [y,m,d] = iso.split('-');
  return `${d}/${m}/${y}`;
}
</script>


<script>
/* Fix rápido del selector de ubicación */
(function(){
  const PE_CENTER = [-12.0464, -77.0428];
  let zgMap = null;
  let zgMarker = null;
  let zgCurrent = null;
  let zgSelected = null;
  let zgLoadingLeaflet = false;

  function el(id){ return document.getElementById(id); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

  function ensureLeaflet(done){
    if(window.L && typeof L.map === 'function'){ done(); return; }
    if(!document.querySelector('link[data-zg-leaflet]')){
      const link=document.createElement('link');
      link.rel='stylesheet';
      link.href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      link.setAttribute('data-zg-leaflet','1');
      document.head.appendChild(link);
    }
    if(zgLoadingLeaflet){ setTimeout(function(){ ensureLeaflet(done); }, 250); return; }
    zgLoadingLeaflet = true;
    const sc=document.createElement('script');
    sc.src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    sc.onload=function(){ zgLoadingLeaflet=false; done(); };
    sc.onerror=function(){
      zgLoadingLeaflet=false;
      const addr=el('pickAddr');
      if(addr) addr.innerHTML='<b>No se pudo cargar el mapa.</b> Revisa internet y vuelve a intentar.';
    };
    document.head.appendChild(sc);
  }

  function pinIcon(){
    return L.divIcon({
      className:'',
      html:'<div style="width:26px;height:26px;background:#e03131;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px rgba(0,0,0,.38)"></div>',
      iconSize:[26,26],
      iconAnchor:[13,26]
    });
  }

  function blueIcon(){
    return L.divIcon({
      className:'',
      html:'<div style="width:16px;height:16px;background:#1f6fc4;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 6px rgba(31,111,196,.20),0 3px 10px rgba(0,0,0,.30)"></div>',
      iconSize:[16,16],
      iconAnchor:[8,8]
    });
  }

  function prepareBox(){
    const box=el('pickMap');
    if(!box) return null;
    box.style.display='block';
    box.style.position='relative';
    box.style.background='#e9eef3';
    box.style.minHeight = window.innerWidth <= 780 ? '46vh' : '360px';
    box.style.height = window.innerWidth <= 780 ? 'calc(100dvh - 245px)' : '420px';
    return box;
  }

  function openFixedMap(){
    const modal=el('mapModal');
    const addr=el('pickAddr');
    const box=prepareBox();
    if(!modal || !box) return;
    modal.classList.add('show');
    if(addr && !zgSelected) addr.innerHTML='Toca el mapa o arrastra el pin para elegir el punto exacto…';

    ensureLeaflet(function(){
      setTimeout(function(){
        try{
          if(zgMap){
            zgMap.invalidateSize(true);
            return;
          }

          // Limpia cualquier intento anterior que haya quedado a medias.
          if(box._leaflet_id){ box._leaflet_id = null; box.innerHTML = ''; }

          zgMap = L.map(box, { zoomControl:true, attributionControl:true }).setView(PE_CENTER, 12);
          L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom:19,
            attribution:'&copy; OpenStreetMap'
          }).addTo(zgMap);

          zgMap.on('click', function(ev){ setSelected(ev.latlng.lat, ev.latlng.lng, true); });
          setTimeout(function(){ zgMap.invalidateSize(true); }, 80);
          setTimeout(function(){ zgMap.invalidateSize(true); }, 350);
          setTimeout(function(){ zgMap.invalidateSize(true); }, 800);

          if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(function(pos){
              const lat=pos.coords.latitude;
              const lng=pos.coords.longitude;
              if(zgCurrent) zgCurrent.setLatLng([lat,lng]);
              else zgCurrent = L.marker([lat,lng], {icon:blueIcon(), interactive:false}).addTo(zgMap);
              zgMap.setView([lat,lng], 17);
              if(!zgSelected) setSelected(lat,lng,true);
            }, function(){}, {enableHighAccuracy:true, timeout:10000, maximumAge:0});
          }
        }catch(err){
          console.error('Mapa ZGROUP:', err);
          if(addr) addr.innerHTML='<b>No se pudo iniciar el mapa.</b> Recarga la página e intenta otra vez.';
        }
      }, 160);
    });
  }

  function setSelected(lat,lng,doReverse){
    if(!zgMap || !window.L) return;
    zgSelected = {lat:Number(lat), lng:Number(lng), addr:''};
    if(zgMarker) zgMarker.setLatLng([lat,lng]);
    else{
      zgMarker = L.marker([lat,lng], {icon:pinIcon(), draggable:true}).addTo(zgMap);
      zgMarker.on('dragend', function(){
        const p=zgMarker.getLatLng();
        setSelected(p.lat, p.lng, true);
      });
    }
    const addr=el('pickAddr');
    if(addr) addr.innerHTML='<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'<br><small>Buscando dirección exacta…</small>';
    updateRoute();
    if(doReverse) reverseAddress(lat,lng);
  }

  async function reverseAddress(lat,lng){
    const addr=el('pickAddr');
    try{
      const r=await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+encodeURIComponent(lat)+'&lon='+encodeURIComponent(lng)+'&zoom=18&addressdetails=1&accept-language=es');
      const d=await r.json();
      const text=d.display_name || '';
      if(zgSelected) zgSelected.addr=text;
      if(addr){
        addr.innerHTML = text
          ? '<b>Dirección:</b> '+esc(text)+'<br><small>Coordenadas: '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'</small>'
          : '<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6);
      }
    }catch(e){
      if(addr) addr.innerHTML='<b>Punto seleccionado:</b> '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6)+'<br><small>No se pudo obtener la calle; se guardarán las coordenadas.</small>';
    }
    updateRoute();
  }

  async function searchAddress(){
    const q=(el('mapSearch') && el('mapSearch').value || '').trim();
    const addr=el('pickAddr');
    const sug=el('mapSug');
    if(!q) return;
    if(addr) addr.textContent='Buscando…';
    try{
      const url='https://nominatim.openstreetmap.org/search?format=jsonv2&q='+encodeURIComponent(q)+'&limit=8&addressdetails=1&countrycodes=pe&accept-language=es';
      const r=await fetch(url);
      const arr=await r.json();
      if(sug){ sug.innerHTML=''; sug.classList.remove('show'); }
      if(!arr || !arr.length){
        if(addr) addr.innerHTML='No se encontró ese lugar. Escribe calle, distrito y ciudad, o toca el mapa para marcar el punto.';
        return;
      }
      if(sug){
        arr.forEach(function(it){
          const div=document.createElement('div');
          div.className='sug-item';
          div.innerHTML='<div class="sug-main">'+esc((it.display_name||'').split(',')[0])+'</div><div class="sug-sub">'+esc(it.display_name||'')+'</div>';
          div.addEventListener('mousedown', function(ev){
            ev.preventDefault();
            pickSearchItem(it);
            sug.classList.remove('show');
          });
          sug.appendChild(div);
        });
        sug.classList.add('show');
      }
      pickSearchItem(arr[0]);
    }catch(e){
      if(addr) addr.innerHTML='No se pudo buscar. Revisa internet o marca el punto en el mapa.';
    }
  }

  function pickSearchItem(it){
    const lat=parseFloat(it.lat), lng=parseFloat(it.lon);
    if(!Number.isFinite(lat) || !Number.isFinite(lng)) return;
    const text=it.display_name || '';
    if(el('mapSearch')) el('mapSearch').value=text;
    if(zgMap) zgMap.setView([lat,lng],18);
    setSelected(lat,lng,false);
    if(zgSelected) zgSelected.addr=text;
    if(el('pickAddr')) el('pickAddr').innerHTML='<b>Dirección:</b> '+esc(text)+'<br><small>Coordenadas: '+lat.toFixed(6)+', '+lng.toFixed(6)+'</small>';
    updateRoute();
  }

  function updateRoute(){
    const route=el('mapRoute');
    if(!route || !zgSelected){ return; }
    route.href='https://www.google.com/maps/dir/?api=1&destination='+zgSelected.lat+','+zgSelected.lng;
    route.style.display='inline-flex';
  }

  function confirmFixedMap(){
    if(!zgSelected){
      if(typeof toast === 'function') toast('Elige un punto en el mapa primero');
      return;
    }
    const lat=Number(zgSelected.lat), lng=Number(zgSelected.lng);
    const direccion=el('direccion');
    const coords=el('direccionCoords');
    if(direccion) direccion.value = zgSelected.addr || (lat.toFixed(6)+', '+lng.toFixed(6));
    if(coords) coords.value = lat.toFixed(6)+', '+lng.toFixed(6);
    const origen=el('direccionOrigenOdoo'); if(origen) origen.value='';
    const modal=el('mapModal');
    if(modal) modal.classList.remove('show');
    if(typeof toast === 'function') toast('Ubicación seleccionada');
    if(typeof validateField === 'function') validateField('direccion');
  }

  // Captura antes de cualquier manejador anterior para evitar que se quede el mapa en blanco.
  document.addEventListener('click', function(e){
    if(e.target.closest('#dirPick') || e.target.closest('#direccion')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      openFixedMap();
      return false;
    }
    if(e.target.closest('#mapSearchBtn')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      searchAddress();
      return false;
    }
    if(e.target.closest('#mapConfirm')){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      confirmFixedMap();
      return false;
    }
  }, true);

  document.addEventListener('keydown', function(e){
    if(e.target && e.target.id === 'mapSearch' && e.key === 'Enter'){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      searchAddress();
      return false;
    }
  }, true);

  window.openMap = openFixedMap;
})();
</script>



<script id="zfix-preliminar-y-materiales">
(function(){
  const ZFIX_PRE = <?= json_encode($preinspeccion ?: null, JSON_UNESCAPED_UNICODE) ?>;

  function el(id){ return document.getElementById(id); }
  function str(v){ return (v === null || v === undefined) ? '' : String(v); }
  function clean(v){ return str(v).trim(); }
  function first(obj, keys){
    obj = obj || {};
    for(const k of keys){
      if(Object.prototype.hasOwnProperty.call(obj, k) && clean(obj[k]) !== '') return obj[k];
    }
    return '';
  }
  function setInput(id, value, force){
    const x = el(id);
    if(!x) return;
    const v = clean(value);
    if(v === '' && !force) return;
    if(force || clean(x.value) === '') x.value = v;
  }
  function setSelect(id, value){
    const x = el(id);
    if(!x) return;
    const v = clean(value);
    if(v === '') return;
    let matched = false;
    Array.from(x.options || []).forEach(opt => {
      if(clean(opt.value).toLowerCase() === v.toLowerCase() || clean(opt.textContent).toLowerCase() === v.toLowerCase()){
        x.value = opt.value;
        matched = true;
      }
    });
    if(!matched && x.tagName === 'SELECT'){
      const opt = document.createElement('option');
      opt.value = v;
      opt.textContent = v;
      x.appendChild(opt);
      x.value = v;
    }
  }
  function unlock(id){
    const x = el(id);
    if(!x) return;
    x.readOnly = false;
    x.disabled = false;
    x.style.background = '';
    x.style.pointerEvents = '';
  }
  function norm(txt){
    return clean(txt).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/\s+/g,' ');
  }

  function autocompletarDesdePreliminar(){
    const p = ZFIX_PRE || (typeof PREINSPECCION !== 'undefined' ? PREINSPECCION : null);
    if(!p) return;
    const editandoInforme = (typeof ZG_EDIT_MODE !== 'undefined' && ZG_EDIT_MODE);

    setInput('preinspeccionId', first(p, ['id','pre_id','preinspeccion_id']), true);

    /*
     * En la edición del informe, los datos generales pertenecen al informe final
     * y no deben volver a copiarse desde la preliminar. La preliminar conserva su
     * fecha original, pero la fecha del informe puede corregirse por separado.
     */
    if(!editandoInforme){
      setInput('orden', first(p, ['cotizacion','orden','nro_cotizacion','numero_cotizacion']), true);
      setInput('cliente', first(p, ['cliente','cliente_nombre','nombre_cliente']), true);

      const fecha = first(p, ['fecha','creado_en','fecha_preliminar','created_at']);
      if(fecha) setInput('fecha', String(fecha).slice(0,10), true);

      const direccion = first(p, ['ubicacion_texto','direccion','ubicacion','direccion_servicio','lugar']);
      setInput('direccion', direccion || 'Ubicación registrada en preliminar', true);
    }

    const lat = first(p, ['latitud','lat','latitude']);
    const lng = first(p, ['longitud','lng','lon','longitude']);
    if(lat && lng) setInput('direccionCoords', clean(lat)+', '+clean(lng), true);

    const tecnicoId = first(p, ['tecnico_id','id_tecnico']);
    if(tecnicoId){
      setSelect('tecnicoInput', tecnicoId);
      setInput('tecnicoId', tecnicoId, true);
      try{ state.tecnicoId = clean(tecnicoId); }catch(e){}
      if(window.zgTecnicoSetById) window.zgTecnicoSetById(tecnicoId);
    }
    const tecnicoNombre = first(p, ['tecnico_nombre','tecnico','nombre_tecnico']);
    if(tecnicoNombre){ try{ state.tecnicoNombre = clean(tecnicoNombre); }catch(e){} }

    setSelect('zgModalidadComercial', first(p, ['modalidad_comercial']));
    setSelect('zgTipoInstalacion', first(p, ['tipo_instalacion']));
    setSelect('zgTipoEquipo', first(p, ['tipo_equipo']) || (/^SG[- ]?(3000|5000)$/i.test(String(first(p,['controlador','controlador_equipo']))) || String(first(p,['genset_horometro_inicial'])).trim() !== '' ? 'Genset' : 'Reefer'));
    setSelect('zgTamanoContenedor', first(p, ['tamano_contenedor','tamaño_contenedor']));
    setInput('equipoNo', first(p, ['numero_equipo','equipo','contenedor','contenedor_equipo','numero_contenedor']), true);
    setInput('serialUnidad', first(p, ['serie_unidad','serial_unidad','serial','serie']), true);
    setSelect('marcaEquipo', first(p, ['marca_equipo','marca']));
    setInput('modeloEquipo', first(p, ['modelo_equipo','modelo']), true);
    setInput('controladorEquipo', first(p, ['controlador','controlador_equipo']), true);
    setInput('anioFabricacion', first(p, ['anio_fabricacion','año_fabricacion','anio']), true);
    setSelect('refrigerante', first(p, ['refrigerante']));
    setInput('setPoint', first(p, ['set_point','setpoint']), true);
    setInput('temperaturaAmbiente', first(p, ['temperatura_ambiente','temp_ambiente']), true);
    setInput('retornoAire', first(p, ['retorno_aire','temp_retorno']), true);
    setInput('suministroAire', first(p, ['suministro_aire','temp_suministro']), true);
    setInput('presionAlta', first(p, ['presion_alta','presionAlta']), true);
    setInput('presionBaja', first(p, ['presion_baja','presionBaja']), true);
    setInput('alarmaEncontrada', first(p, ['alarma_encontrada','alarmaEncontrada']), true);
    setInput('gensetHorometroInicial', first(p, ['genset_horometro_inicial']), true);
    setInput('gensetVoltajeBateriaInicial', first(p, ['genset_voltaje_bateria_inicial']), true);
    setSelect('gensetNivelCombustibleInicial', first(p, ['genset_nivel_combustible_inicial']));
    setSelect('gensetNivelAceiteInicial', first(p, ['genset_nivel_aceite_inicial']));
    setSelect('gensetRefrigeranteMotorInicial', first(p, ['genset_refrigerante_motor_inicial']));
    setSelect('gensetArranqueInicial', first(p, ['genset_arranque_inicial']));
    setInput('gensetFrecuenciaInicial', first(p, ['genset_frecuencia_inicial']), true);
    setInput('gensetPresionAceiteInicial', first(p, ['genset_presion_aceite_inicial']), true);
    setInput('voltajeL1L2', first(p, ['voltaje_l1_l2','voltajeL1L2']), true);
    setInput('voltajeL2L3', first(p, ['voltaje_l2_l3','voltajeL2L3']), true);
    setInput('voltajeL1L3', first(p, ['voltaje_l1_l3','voltajeL1L3']), true);
    const estadoGuardado = first(p, ['estado_inicial','estado_equipo_inicial']);
    setInput('estadoInicial', estadoGuardado, true);
    if(typeof zgroupCargarEstadoInicialSelectores === 'function') zgroupCargarEstadoInicialSelectores(estadoGuardado);
    setInput('observacionInicial', window.zgStripMetaFromText ? window.zgStripMetaFromText(first(p, ['observacion_inicial','observaciones_iniciales','observacion'])) : first(p, ['observacion_inicial','observaciones_iniciales','observacion']), true);

    ['orden','fecha','cliente','tecnicoInput','direccion','zgModalidadComercial','zgTipoInstalacion','zgTipoEquipo','zgTamanoContenedor',
     'equipoNo','serialUnidad','marcaEquipo','modeloEquipo','controladorEquipo','anioFabricacion','refrigerante','setPoint','temperaturaAmbiente','retornoAire','suministroAire',
     'presionAlta','presionBaja','voltajeL1L2','voltajeL2L3','voltajeL1L3','estadoInicial','estadoEncendido','estadoEnergia','estadoAlarma','alarmaEncontrada',
     'gensetHorometroInicial','gensetVoltajeBateriaInicial','gensetNivelCombustibleInicial','gensetNivelAceiteInicial','gensetRefrigeranteMotorInicial',
     'gensetArranqueInicial','gensetFrecuenciaInicial','gensetPresionAceiteInicial','observacionInicial'].forEach(unlock);
    const dirPick = el('dirPick');
    if(dirPick) dirPick.style.pointerEvents = '';

    const btn = el('preBtn');
    if(btn){
      btn.disabled = true;
      btn.textContent = 'Preliminar cargada';
      btn.style.opacity = '.75';
    }
    const st = el('preStatus');
    if(st){
      st.textContent = 'Datos cargados desde la preliminar';
      st.style.color = '#155293';
    }
    const sub = document.querySelector('#datosGeneralesCard .sub');
    if(sub) sub.textContent = 'Datos cargados desde la preliminar. Puedes corregirlos antes de generar el informe final.';
    try{ setTimeout(function(){ el('zgTipoEquipo')?.dispatchEvent(new Event('change',{bubbles:true})); },50); }catch(e){}
  }

  function detalleDeLinea(linea){
    const s = clean(linea);
    if(!s) return '';
    const parts = s.split('|').map(x => clean(x)).filter(Boolean);
    if(parts.length >= 2) return parts[1];
    return s;
  }
  function escribirTextareaRepuestos(){
    const ta = el('repuestosManual');
    if(!ta) return;
    try{
      if(typeof syncRepuestosManual === 'function') syncRepuestosManual();
    }catch(e){}
    if(clean(ta.value) !== '') return;
    try{
      if(typeof repuestosSeleccionados !== 'undefined' && Array.isArray(repuestosSeleccionados) && repuestosSeleccionados.length){
        ta.value = repuestosSeleccionados.map(r => {
          const codigo = clean(r.codigo) || '-';
          const detalle = clean(r.detalle);
          const cantidad = clean(r.cantidad) || '1';
          return detalle ? `${codigo} | ${detalle} | ${cantidad}` : '';
        }).filter(Boolean).join('\n');
      }
    }catch(e){}
  }
  function agregarMaterialEscrito(){
    const requiere = el('requiereRepuesto');
    if(requiere && requiere.value !== 'si') return true;
    const input = el('repuestoSearch');
    const detalle = clean(input ? input.value : '');
    if(!detalle){ escribirTextareaRepuestos(); return true; }

    try{
      if(typeof setRequiereRepuesto === 'function') setRequiereRepuesto(true);
    }catch(e){}

    let yaExiste = false;
    try{
      if(typeof repuestosSeleccionados !== 'undefined' && Array.isArray(repuestosSeleccionados)){
        yaExiste = repuestosSeleccionados.some(r => norm(r.detalle) === norm(detalle));
        if(!yaExiste){
          repuestosSeleccionados.push({codigo:'', detalle:detalle, unidad:'', cantidad:'1', nuevo:true});
        }
      }
    }catch(e){}

    const ta = el('repuestosManual');
    if(ta){
      const actuales = clean(ta.value).split(/\n+/).map(x => clean(x)).filter(Boolean);
      const existeEnTexto = actuales.some(x => norm(detalleDeLinea(x)) === norm(detalle));
      if(!existeEnTexto) actuales.push('- | '+detalle+' | 1');
      ta.value = actuales.join('\n');
      ta.classList.remove('input-error');
    }

    try{ if(typeof renderRepuestosSeleccionados === 'function') renderRepuestosSeleccionados(); }catch(e){}
    try{
      const empty = el('repuestosEmpty');
      const box = el('repuestosSelectedList');
      if(empty && clean(ta ? ta.value : '') !== '') empty.classList.remove('show');
      if(box && box.children.length === 0 && clean(ta ? ta.value : '') !== ''){
        const lineas = clean(ta.value).split(/\n+/).map(x=>clean(x)).filter(Boolean);
        lineas.forEach(function(linea){
          const det = detalleDeLinea(linea);
          if(!det) return;
          const row = document.createElement('div');
          row.className = 'repuesto-selected-item';
          const name = document.createElement('input');
          name.type = 'text'; name.className = 'repuesto-selected-name'; name.value = det;
          name.addEventListener('input', function(){
            const nuevas = Array.from(box.querySelectorAll('.repuesto-selected-name')).map(i => '- | '+clean(i.value)+' | 1').filter(x => clean(detalleDeLinea(x)));
            if(ta) ta.value = nuevas.join('\n');
          });
          const wrap = document.createElement('div');
          const sub = document.createElement('div'); sub.className='repuesto-selected-sub'; sub.textContent='Nuevo material para revisión en panel';
          wrap.append(name, sub);
          const qty = document.createElement('input'); qty.type='text'; qty.inputMode='numeric'; qty.value='1'; qty.className='repuesto-qty';
          const rem = document.createElement('button'); rem.type='button'; rem.className='repuesto-remove'; rem.textContent='×'; rem.onclick=function(){ row.remove(); const nuevas = Array.from(box.querySelectorAll('.repuesto-selected-name')).map(i => '- | '+clean(i.value)+' | 1').filter(x => clean(detalleDeLinea(x))); if(ta) ta.value=nuevas.join('\n'); if(empty) empty.classList.toggle('show', !nuevas.length); };
          row.append(wrap, qty, rem); box.appendChild(row);
        });
      }
    }catch(e){}
    try{ if(typeof clearRepuestoError === 'function') clearRepuestoError(); }catch(e){}
    const err = el('repuestosManualError');
    if(err){ err.textContent=''; err.classList.remove('show'); }
    if(input) input.value = '';
    return true;
  }

  function prepararAntesDeGenerar(){
    /* No restaurar la preliminar durante una actualización administrativa. */
    if(!(typeof ZG_EDIT_MODE !== 'undefined' && ZG_EDIT_MODE)) autocompletarDesdePreliminar();
    agregarMaterialEscrito();
    escribirTextareaRepuestos();
  }

  const oldAdd = (typeof agregarRepuestoManual === 'function') ? agregarRepuestoManual : null;
  try{
    agregarRepuestoManual = function(){
      const before = clean(el('repuestoSearch') ? el('repuestoSearch').value : '');
      if(oldAdd){
        try{
          const ok = oldAdd();
          if(ok) return true;
        }catch(e){}
      }
      if(before && el('repuestoSearch')) el('repuestoSearch').value = before;
      return agregarMaterialEscrito();
    };
  }catch(e){}

  try{
    validarRepuestos = function(){
      agregarMaterialEscrito();
      escribirTextareaRepuestos();
      const ta = el('repuestosManual');
      if(ta) ta.classList.remove('input-error');
      const err = el('repuestosManualError');
      if(err){ err.textContent=''; err.classList.remove('show'); }
      return true;
    };
  }catch(e){}

  try{
    const oldGeneral = (typeof validateGeneralRequired === 'function') ? validateGeneralRequired : null;
    validateGeneralRequired = function(){
      if(!(typeof ZG_EDIT_MODE !== 'undefined' && ZG_EDIT_MODE)) autocompletarDesdePreliminar();
      if((ZFIX_PRE || (typeof PREINSPECCION !== 'undefined' && PREINSPECCION)) && clean(el('preinspeccionId') ? el('preinspeccionId').value : '')) return true;
      return oldGeneral ? oldGeneral() : true;
    };
  }catch(e){}

  try{
    const oldPreVal = (typeof validarInspeccionPreliminar === 'function') ? validarInspeccionPreliminar : null;
    validarInspeccionPreliminar = function(){
      if(!(typeof ZG_EDIT_MODE !== 'undefined' && ZG_EDIT_MODE)) autocompletarDesdePreliminar();
      if((ZFIX_PRE || (typeof PREINSPECCION !== 'undefined' && PREINSPECCION)) && clean(el('preinspeccionId') ? el('preinspeccionId').value : '')) return true;
      return oldPreVal ? oldPreVal() : true;
    };
  }catch(e){}

  try{
    registrarRepuestosTecnico = async function(){
      agregarMaterialEscrito();
      escribirTextareaRepuestos();
      const ta = el('repuestosManual');
      const txt = clean(ta ? ta.value : '');
      if(!txt) return;
      try{
        const fd = new FormData();
        fd.append('repuestos', txt);
        const res = await fetch('registrar_repuestos_tecnico.php', {method:'POST', body:fd});
        await res.json().catch(() => null);
      }catch(e){ console.warn('No se pudo guardar el material para panel', e); }
    };
  }catch(e){}

  document.addEventListener('click', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoAddManual')){
      ev.preventDefault();
      ev.stopPropagation();
      agregarMaterialEscrito();
      try{ const _m=(el('repuestosManual')&&el('repuestosManual').value||'').split(/\n/).filter(Boolean).pop()||''; const _d=detalleDeLinea(_m)||clean(el('repuestoSearch')?el('repuestoSearch').value:''); if(typeof toast === 'function') toast(_d ? ('Material agregado: '+_d) : 'Material agregado'); }catch(e){}
    }
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')){
      prepararAntesDeGenerar();
    }
  }, true);

  document.addEventListener('keydown', function(ev){
    if(ev.target && ev.target.id === 'repuestoSearch' && ev.key === 'Enter'){
      ev.preventDefault();
      ev.stopPropagation();
      agregarMaterialEscrito();
      try{ const _m=(el('repuestosManual')&&el('repuestosManual').value||'').split(/\n/).filter(Boolean).pop()||''; const _d=detalleDeLinea(_m)||clean(el('repuestoSearch')?el('repuestoSearch').value:''); if(typeof toast === 'function') toast(_d ? ('Material agregado: '+_d) : 'Material agregado'); }catch(e){}
    }
  }, true);

  window.zgroupFixPreliminarMateriales = prepararAntesDeGenerar;
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', () => setTimeout(autocompletarDesdePreliminar, 80));
  }else{
    setTimeout(autocompletarDesdePreliminar, 80);
  }
  setTimeout(autocompletarDesdePreliminar, 400);
  setTimeout(autocompletarDesdePreliminar, 1200);
})();
</script>


<style>
/* ============================================================
   ZGROUP - tabla real de repuestos seleccionados
   ============================================================ */
#repuestosSelectedList.zg-table-ready{display:block!important;margin-top:12px!important}
.zg-repuestos-table-wrap{border:1.5px solid #cfe0f4;border-radius:18px;overflow:hidden;background:#fff;box-shadow:0 14px 30px rgba(16,33,58,.10);margin-top:10px}
.zg-repuestos-table{width:100%;border-collapse:collapse;font-size:13px;background:#fff}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#eef6ff,#e7f0fb);color:#155293;font-family:'Archivo',system-ui,sans-serif;font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;text-align:left;padding:11px 12px;border-bottom:1px solid #d7e5f5;border-right:1px solid #d7e5f5}
.zg-repuestos-table thead th:last-child{border-right:none;text-align:center}
.zg-repuestos-table td{padding:10px 12px;border-bottom:1px solid #edf3fa;border-right:1px solid #edf3fa;vertical-align:middle}
.zg-repuestos-table td:last-child{border-right:none;text-align:center}.zg-repuestos-table tr:last-child td{border-bottom:none}
.zg-rep-code2{font-family:'Archivo',system-ui,sans-serif;font-weight:900;color:#1f6fc4;word-break:break-word}.zg-rep-code2.empty{color:#8a5a00}
.zg-rep-detail2{width:100%;border:1.5px solid #d7e5f5;background:#f8fbff;border-radius:12px;padding:10px 11px;font:inherit;font-weight:850;color:#10213a;outline:none}.zg-rep-detail2:focus{border-color:#1f6fc4;background:#fff;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-qty2{width:96px;border:1.5px solid #d7e5f5;background:#fff;border-radius:12px;padding:10px 8px;font:inherit;font-family:'Archivo',system-ui,sans-serif;font-weight:900;text-align:center;color:#10213a;outline:none}.zg-rep-qty2:focus{border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-del2{width:36px;height:36px;border:none;border-radius:12px;background:#fff1f1;color:#b91c1c;font-size:18px;font-weight:900;cursor:pointer}.zg-rep-del2:hover{filter:brightness(.96);transform:translateY(-1px)}
.zg-rep-count2{display:inline-flex!important;align-items:center;gap:8px;background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:900;margin-top:10px}.zg-rep-count2:before{content:'✓'}
.zg-rep-tip2{font-size:11.5px;color:#66758a;font-weight:800;margin-top:4px}.zg-rep-tip2 b{color:#10213a}
#repuestosEmpty{margin-top:12px!important}
.repuesto-smart .smart-menu{z-index:99999!important;pointer-events:auto!important}.repuesto-smart .smart-option{pointer-events:auto!important;cursor:pointer!important}.repuesto-smart .smart-option *{pointer-events:none!important}
@media(max-width:720px){.zg-repuestos-table thead{display:none}.zg-repuestos-table,.zg-repuestos-table tbody,.zg-repuestos-table tr,.zg-repuestos-table td{display:block;width:100%}.zg-repuestos-table tr{border-bottom:1px solid #edf3fa}.zg-repuestos-table td{border-right:none}.zg-repuestos-table td::before{display:block;margin-bottom:5px;font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;color:#66758a;font-family:'Archivo';font-weight:900}.zg-repuestos-table td[data-label="Código"]::before{content:'Código'}.zg-repuestos-table td[data-label="Material"]::before{content:'Material / repuesto'}.zg-repuestos-table td[data-label="Cantidad"]::before{content:'Cantidad'}.zg-rep-qty2{width:130px}.zg-rep-del2{width:100%}}
</style>
<script>
/* ============================================================
   ZGROUP - selección real de repuestos + tabla + PDF
   - Reemplaza la lista anterior sin depender de los handlers viejos.
   ============================================================ */
(function(){
  function el(id){ return document.getElementById(id); }
  function clean(s){ return String(s || '').replace(/\s+/g,' ').trim(); }
  function norm(s){ return clean(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  function toast2(t){ try{ if(typeof toast === 'function') toast(t); else console.log(t); }catch(e){ console.log(t); } }
  function catalogo(){ try{ return Array.isArray(REPUESTOS_CATALOGO) ? REPUESTOS_CATALOGO : []; }catch(e){ return []; } }

  let materiales = [];

  function parseLinea(linea){
    linea = clean(linea); if(!linea) return null;
    const p = linea.split('|').map(clean);
    let codigo='', detalle='', cantidad='1', unidad='';

    /*
       Formato actual: CODIGO | MATERIAL | CANTIDAD | UNIDAD
       Formato anterior: CODIGO | MATERIAL | CANTIDAD
       Se reconocen ambos para no perder cantidades al agregar otro material.
    */
    if(p.length >= 4){
      codigo = p[0] === '-' ? '' : p[0];
      cantidad = p[p.length - 2] || '1';
      unidad = p[p.length - 1] || '';
      detalle = p.slice(1, -2).join(' | ') || p[1] || '';
    } else if(p.length === 3){
      codigo = p[0] === '-' ? '' : p[0];
      detalle = p[1] || '';
      cantidad = p[2] || '1';
    } else if(p.length === 2){
      codigo = p[0] === '-' ? '' : p[0];
      detalle = p[1] || '';
    } else {
      detalle = linea;
    }

    detalle = clean(detalle.replace(/\s*\|\s*/g,' '));
    cantidad = String(cantidad || '1').replace(/[^0-9]/g,'').slice(0,4) || '1';
    if(!detalle) return null;
    return {codigo, detalle, cantidad, unidad};
  }
  function cargarDesdeTextarea(){
    const ta = el('repuestosManual');
    const txt = ta ? ta.value : '';
    const arr = txt.split(/\r?\n/).map(parseLinea).filter(Boolean);
    if(arr.length) materiales = arr;
    return materiales;
  }
  function linea(x){ return (clean(x.codigo)||'-') + ' | ' + clean(x.detalle) + ' | ' + (String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1'); }
  function guardar(){
    const ta = el('repuestosManual');
    if(ta){
      ta.value = materiales.filter(x=>clean(x.detalle)).map(linea).join('\n');
      ta.classList.remove('input-error');
    }
    try{ repuestosSeleccionados = materiales.map(x => ({codigo:clean(x.codigo), detalle:clean(x.detalle), cantidad:String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1', nuevo:!clean(x.codigo)})); }catch(e){}
    const err = el('repuestosManualError');
    if(err){ err.textContent=''; err.classList.remove('show'); }
    try{ if(window.zgroupMarcarCambio) window.zgroupMarcarCambio(); }catch(e){}
  }
  function pintar(){
    const box = el('repuestosSelectedList');
    const empty = el('repuestosEmpty');
    if(!box) return;
    box.classList.add('zg-table-ready');
    box.innerHTML = '';
    if(empty) empty.classList.toggle('show', materiales.length === 0);
    if(!materiales.length){ guardar(); return; }

    const count = document.createElement('div');
    count.className = 'zg-rep-count2';
    count.textContent = materiales.length + ' material(es) seleccionado(s)';
    box.appendChild(count);

    const wrap = document.createElement('div');
    wrap.className = 'zg-repuestos-table-wrap';
    const table = document.createElement('table');
    table.className = 'zg-repuestos-table';
    table.innerHTML = '<thead><tr><th style="width:145px">Código</th><th>Material / repuesto</th><th style="width:120px">Cantidad</th><th style="width:56px"></th></tr></thead><tbody></tbody>';
    const tbody = table.querySelector('tbody');

    materiales.forEach(function(it, idx){
      const tr = document.createElement('tr');
      const cod = clean(it.codigo) || 'Sin código';
      tr.innerHTML =
        '<td data-label="Código"><div class="zg-rep-code2 '+(!clean(it.codigo)?'empty':'')+'">'+esc(cod)+'</div></td>'+ 
        '<td data-label="Material"><input class="zg-rep-detail2" type="text" value="'+esc(it.detalle)+'" placeholder="Nombre del material"><div class="zg-rep-tip2">Puedes corregir el nombre antes de generar el PDF.</div></td>'+ 
        '<td data-label="Cantidad"><input class="zg-rep-qty2" type="text" inputmode="numeric" value="'+esc(it.cantidad||'1')+'"></td>'+ 
        '<td><button type="button" class="zg-rep-del2" title="Quitar">×</button></td>';
      const det = tr.querySelector('.zg-rep-detail2');
      const qty = tr.querySelector('.zg-rep-qty2');
      const del = tr.querySelector('.zg-rep-del2');
      det.addEventListener('input', function(){ materiales[idx].detalle = clean(det.value); guardar(); });
      qty.addEventListener('input', function(){ let v=String(qty.value||'').replace(/[^0-9]/g,'').slice(0,4); qty.value=v; materiales[idx].cantidad=v; guardar(); });
      qty.addEventListener('focus', function(){ setTimeout(function(){ try{ qty.select(); }catch(e){} }, 30); });
      qty.addEventListener('blur', function(){ if(!String(qty.value||'').trim()){ qty.value='1'; materiales[idx].cantidad='1'; guardar(); } });
      del.addEventListener('click', function(){ materiales.splice(idx,1); guardar(); pintar(); });
      tbody.appendChild(tr);
    });
    wrap.appendChild(table);
    box.appendChild(wrap);
    guardar();
  }
  function activarSi(){
    const h=el('requiereRepuesto'); if(h) h.value='si';
    const card=el('repuestosCard'); if(card) card.classList.remove('is-hidden');
    const si=el('repuestoSiBtn'), no=el('repuestoNoBtn'); if(si) si.classList.add('on'); if(no) no.classList.remove('on');
  }
  function existe(item){
    const d=norm(item.detalle), c=norm(item.codigo);
    return materiales.some(x => (c && norm(x.codigo)===c) || (d && norm(x.detalle)===d));
  }
  function agregar(item, aviso){
    item = item || {};
    const obj = {codigo:clean(item.codigo||''), detalle:clean(item.detalle||''), cantidad:String(item.cantidad||'1').replace(/[^0-9]/g,'').slice(0,4)||'1'};
    if(!obj.detalle) return false;
    activarSi();
    cargarDesdeTextarea();
    if(existe(obj)){ if(aviso!==false) toast2('Ya está seleccionado: '+obj.detalle); pintar(); return false; }
    materiales.push(obj);
    const input=el('repuestoSearch'); if(input) input.value='';
    const menu=el('repuestoSuggest'); if(menu) menu.classList.remove('show');
    pintar();
    if(aviso!==false) toast2('Agregado a la tabla: '+obj.detalle+' · Cantidad '+obj.cantidad);
    return true;
  }
  function buscar(det){
    const d=norm(det); if(!d) return null;
    return catalogo().find(r=>norm(r.detalle||'')===d) || catalogo().find(r=>norm(r.detalle||'').includes(d)) || null;
  }
  function agregarManual(){
    const input=el('repuestoSearch');
    const det=clean(input?input.value:'');
    if(!det){ if(input) input.focus(); toast2('Escribe o selecciona un material'); return false; }
    const cat=buscar(det);
    return agregar(cat ? {codigo:cat.codigo||'', detalle:cat.detalle||det, cantidad:'1'} : {codigo:'', detalle:det, cantidad:'1'}, true);
  }
  function mostrarMenuPropio(menuId, items){
    const menu = el(menuId); if(!menu) return;
    menu.innerHTML='';
    if(!items || !items.length){ menu.classList.remove('show'); return; }
    items.forEach(function(item){
      const raw = item.raw || item;
      const b = document.createElement('button');
      b.type='button'; b.className='smart-option';
      b.innerHTML='<div><span class="smart-main">'+esc(item.main||raw.detalle||'')+'</span><span class="smart-sub">'+esc(item.sub || (raw.codigo ? 'Código: '+raw.codigo : 'Pendiente de revisión'))+'</span></div><span class="smart-badge">usar</span>';
      b.addEventListener('click', function(ev){ ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); agregar({codigo:raw.codigo||'', detalle:raw.detalle||item.main||'', cantidad:'1'}, true); return false; }, true);
      menu.appendChild(b);
    });
    menu.classList.add('show');
  }

  // Reemplaza el render del menú solo para repuestos. Los otros catálogos siguen igual.
  try{
    const oldRender = renderSmartMenu;
    renderSmartMenu = function(menuId, items, onPick){
      if(menuId === 'repuestoSuggest') return mostrarMenuPropio(menuId, items || []);
      return oldRender(menuId, items, onPick);
    };
  }catch(e){}

  // Captura adicional por si el menú viejo ya quedó pintado.
  document.addEventListener('click', function(ev){
    const btn = ev.target && ev.target.closest ? ev.target.closest('#repuestoSuggest .smart-option') : null;
    if(btn){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
      const main = clean(btn.querySelector('.smart-main') ? btn.querySelector('.smart-main').textContent : btn.textContent.replace(/usar\s*$/i,''));
      const cat = buscar(main);
      agregar(cat ? {codigo:cat.codigo||'', detalle:cat.detalle||main, cantidad:'1'} : {codigo:'', detalle:main, cantidad:'1'}, true);
      return false;
    }
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoAddManual')){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); agregarManual(); return false;
    }
    if(ev.target && ev.target.closest && (ev.target.closest('#pdfBtn') || ev.target.closest('#preBtn'))){ guardar(); }
  }, true);

  document.addEventListener('keydown', function(ev){
    if(ev.target && ev.target.id === 'repuestoSearch' && ev.key === 'Enter'){
      ev.preventDefault(); ev.stopPropagation(); agregarManual(); return false;
    }
  }, true);

  try{ insertarRepuestoLinea = function(r){ return agregar({codigo:r&&r.codigo||'', detalle:r&&r.detalle||'', cantidad:'1'}, true); }; }catch(e){}
  try{ agregarRepuestoObjeto = function(r,cantidad,aviso){ return agregar({codigo:r&&r.codigo||'', detalle:r&&r.detalle||'', cantidad:cantidad||'1'}, aviso!==false); }; }catch(e){}
  try{ agregarRepuestoManual = agregarManual; }catch(e){}
  try{ renderRepuestosSeleccionados = pintar; }catch(e){}
  try{ syncRepuestosManual = guardar; }catch(e){}
  try{ validarRepuestos = function(){ if(clean(el('repuestoSearch')?el('repuestoSearch').value:'')) agregarManual(); guardar(); if((el('requiereRepuesto')&&el('requiereRepuesto').value==='si') && !clean(el('repuestosManual')?el('repuestosManual').value:'')){ toast2('Agrega al menos un material en la tabla'); return false; } return true; }; }catch(e){}
  try{ registrarRepuestosTecnico = async function(){ guardar(); const txt=clean(el('repuestosManual')?el('repuestosManual').value:''); if(!txt) return true; try{ const fd=new FormData(); fd.append('repuestos', txt); await fetch('registrar_repuestos_tecnico.php',{method:'POST', body:fd}); }catch(e){} return true; }; }catch(e){}

  window.zgRepuestosTablaFinal = {agregar, pintar, guardar, materiales:function(){return materiales;}};
  window.addEventListener('load', function(){ cargarDesdeTextarea(); pintar(); const hint=el('repuestoHint'); if(hint) hint.textContent='Selecciona “usar” y aparecerá abajo en una tabla con cantidad.'; });
  document.addEventListener('DOMContentLoaded', function(){ cargarDesdeTextarea(); pintar(); });
})();
</script>


<style>
/* Ajuste final: tabla de repuestos más clara y cantidad editable sin quedarse pegada al 1 */
.zg-repuestos-table-wrap{border:1.5px solid #c6dcf5!important;border-radius:18px!important;overflow:hidden!important;background:#fff!important;box-shadow:0 14px 34px rgba(16,33,58,.12)!important}
.zg-repuestos-table{font-size:13.2px!important;background:#fff!important}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#18345c,#10213a)!important;color:#fff!important;padding:12px 12px!important;border-right:1px solid rgba(255,255,255,.18)!important;text-align:left!important}
.zg-repuestos-table thead th:nth-child(3),.zg-repuestos-table thead th:nth-child(4){text-align:center!important}
.zg-repuestos-table td{background:#fff!important;padding:11px 12px!important;border-bottom:1px solid #edf3fa!important;border-right:1px solid #edf3fa!important}
.zg-repuestos-table tbody tr:nth-child(even) td{background:#f8fbff!important}
.zg-rep-code2{font-size:12.8px!important;color:#1f6fc4!important}.zg-rep-code2.empty{color:#9b6a00!important}
.zg-rep-detail2{background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;min-height:42px!important}
.zg-rep-detail2:focus,.zg-rep-qty2:focus{border-color:#1f6fc4!important;box-shadow:0 0 0 3px #e7f0fb!important}
.zg-rep-qty2{width:92px!important;background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;font-size:15px!important;color:#10213a!important;text-align:center!important;caret-color:#1f6fc4!important}
.zg-rep-del2{background:#fff1f1!important;color:#c92a2a!important;border:1px solid #ffd6d6!important}
.zg-rep-count2{margin-bottom:9px!important;background:#eaf7ef!important;color:#176b34!important;border:1px solid #bfe8cc!important}
</style>

<style>
/* ============================================================
   ZGROUP - mejora móvil, evidencias preliminares y firmas
   ============================================================ */
.pre-evidence-card{border:1.5px solid #d7e5f5;background:linear-gradient(180deg,#fff,#f8fbff);border-radius:18px;padding:14px;box-shadow:0 8px 22px rgba(22,38,63,.06);margin-top:4px}
.pre-evidence-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}
.pre-evidence-head h3{font-family:'Archivo',system-ui,sans-serif;font-size:17px;line-height:1.1;color:#10213a;margin:0 0 4px}
.pre-evidence-head p{font-size:12.8px;color:#66758a;font-weight:750;line-height:1.35;margin:0}
.pre-evidence-count{display:inline-flex;align-items:center;background:#e8f2ff;color:#155293;border:1px solid #cbe1fa;border-radius:999px;padding:7px 10px;font-family:'Archivo';font-size:12px;font-weight:900;white-space:nowrap}
.pre-evidence-drop{width:100%;border:1.5px dashed #bcd4ef;background:#f7fbff;border-radius:16px;min-height:74px;display:flex;align-items:center;justify-content:center;gap:12px;color:#10213a;cursor:pointer;text-align:left;padding:13px 16px;transition:.16s}
.pre-evidence-drop:hover{background:#eef6ff;border-color:#82b7ef;transform:translateY(-1px)}
.pre-evidence-ic{font-size:26px;width:44px;height:44px;border-radius:14px;background:#e8f2ff;display:grid;place-items:center;flex:none}
.pre-evidence-drop b{display:block;font-family:'Archivo';font-size:14px}.pre-evidence-drop small{display:block;color:#66758a;font-size:12px;font-weight:800;margin-top:2px}
.pre-evidence-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px}.pre-evidence-empty{grid-column:1/-1;border:1px dashed #c9d9ec;background:#fff;border-radius:14px;padding:12px;color:#66758a;font-weight:800;font-size:12.5px;text-align:center}
.pre-ev-item{border:1px solid #d7e5f5;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 6px 16px rgba(22,38,63,.07)}.pre-ev-item img{width:100%;height:88px;object-fit:cover;display:block}.pre-ev-body{padding:8px}.pre-ev-body input{width:100%;border:1px solid #d7e5f5;border-radius:10px;padding:8px;font-size:12px;font-weight:700;color:#10213a}.pre-ev-actions{display:flex;justify-content:space-between;align-items:center;margin-top:7px;font-size:11px;color:#66758a;font-weight:800}.pre-ev-del{border:none;background:#fff1f1;color:#b91c1c;border-radius:999px;padding:5px 8px;font-family:'Archivo';font-weight:900;cursor:pointer}
.firma-box{position:relative}.firma-canvas{height:170px!important;background:linear-gradient(180deg,#fff,#fbfdff)!important;border:2px dashed #9fc4ee!important;border-radius:16px!important;touch-action:none!important;user-select:none!important;cursor:crosshair}.firma-box::after{content:'Firme aquí con el dedo o mouse';position:absolute;left:24px;right:24px;top:86px;text-align:center;color:#9aabc0;font-weight:900;font-size:12px;pointer-events:none}.firma-box.firmado::after{display:none}.firma-clear{min-height:38px}
@media(max-width:820px){body{font-size:15px}.wrap{width:100%!important;padding:0 10px 95px!important}.card{border-radius:18px!important;padding:14px!important}.hero{min-height:240px!important}.mini-equipo.preinspeccion,.final-grid{grid-template-columns:1fr!important}.field input,.field select,.field textarea,.smart-ac input{min-height:46px!important;font-size:15px!important}.pre-evidence-grid{grid-template-columns:1fr 1fr}.pre-ev-item img{height:105px}.sticky{padding:10px!important}.sticky .btn{min-height:50px!important;font-size:14px!important}.firmas-grid{grid-template-columns:1fr!important}.firma-canvas{height:185px!important}.repuestos-card,.final-control-card,.firmas-card{padding:14px!important;border-radius:18px!important}}
@media(max-width:480px){.pre-evidence-grid{grid-template-columns:1fr}.pre-evidence-head{flex-direction:column}.pre-evidence-count{width:max-content}.pre-ev-item img{height:160px}.pre-evidence-drop{justify-content:flex-start}.firma-canvas{height:200px!important}}
</style>

<script>
/* ============================================================
   ZGROUP - evidencias preliminares + firmas táctiles + PDF cliente
   ============================================================ */
(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(s){ return String(s || '').replace(/\s+/g,' ').trim(); }
  function esc(s){ return String(s || '').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
  window.ZG_PRE_EVIDENCIAS = Array.isArray(window.ZG_PRE_EVIDENCIAS) ? window.ZG_PRE_EVIDENCIAS : [];

  function limpiarEvidenciasLocalesAntiguas(){
    try{
      Object.keys(localStorage).forEach(function(k){
        if(k.indexOf('zgroup_pre_evidencias_') === 0 || k === 'zgroup_pre_evidencias_token_actual'){
          localStorage.removeItem(k);
        }
      });
    }catch(e){}
  }
  function savePreEvidenceLocal(){
    // No se guarda en localStorage. Así, si se recarga o abre otra pestaña, no aparece una evidencia antigua.
  }
  function loadPreEvidenceLocal(){
    limpiarEvidenciasLocalesAntiguas();
    if(window.__zgEvidenceLoadedFromServer && Array.isArray(window.ZG_PRE_EVIDENCIAS)) return;
    window.ZG_PRE_EVIDENCIAS = [];
  }

  window.renderPreEvidenceGrid = function(){
    const grid = byId('preEvidenceGrid');
    const count = byId('preEvidenceCount');
    if(!grid) return;
    const arr = window.ZG_PRE_EVIDENCIAS || [];
    if(count) count.textContent = arr.length + ' foto(s)';
    grid.innerHTML = '';
    if(!arr.length){
      grid.innerHTML = '<div class="pre-evidence-empty">Aún no hay evidencias preliminares.</div>';
      return;
    }
    arr.forEach(function(p, idx){
      const div = document.createElement('div');
      div.className = 'pre-ev-item';
      div.innerHTML = '<img alt="Evidencia preliminar"><div class="pre-ev-body"><input type="text" placeholder="Descripción de la evidencia" value="'+esc(p.caption || ('Evidencia '+(idx+1)))+'"><div class="pre-ev-actions"><span>Antes del servicio</span><button type="button" class="pre-ev-del">Quitar</button></div></div>';
      div.querySelector('img').src = p.dataUrl;
      div.querySelector('input').addEventListener('input', function(){ window.ZG_PRE_EVIDENCIAS[idx].caption = this.value; savePreEvidenceLocal(); });
      div.querySelector('.pre-ev-del').addEventListener('click', function(){ window.ZG_PRE_EVIDENCIAS.splice(idx,1); savePreEvidenceLocal(); window.renderPreEvidenceGrid(); try{ updateCounter(); }catch(e){} });
      grid.appendChild(div);
    });
  };

  async function addPreEvidenceFiles(files){
    files = Array.from(files || []).filter(f => /^image\//.test(f.type));
    if(!files.length) return;
    try{ showOverlay('Procesando evidencias preliminares...'); }catch(e){}
    for(const f of files){
      try{
        const raw = await (typeof readFile === 'function' ? readFile(f) : new Promise((res,rej)=>{const r=new FileReader();r.onload=()=>res(r.result);r.onerror=rej;r.readAsDataURL(f);}));
        const r = await (typeof resizeImage === 'function' ? resizeImage(raw, 1300, 0.72) : Promise.resolve({dataUrl:raw,w:1200,h:800}));
        window.ZG_PRE_EVIDENCIAS.push({ id:'pre'+Date.now()+Math.random().toString(36).slice(2,6), dataUrl:r.dataUrl, w:r.w, h:r.h, caption:'Evidencia '+(window.ZG_PRE_EVIDENCIAS.length+1) });
      }catch(err){ console.warn('No se pudo procesar evidencia', err); }
    }
    try{ hideOverlay(); }catch(e){}
    savePreEvidenceLocal(); window.renderPreEvidenceGrid();
    try{ updateCounter(); toast(files.length + ' evidencia(s) agregada(s)'); }catch(e){}
  }

  function setupPreEvidence(){
    loadPreEvidenceLocal(); window.renderPreEvidenceGrid();
    const cameraBtn = byId('preEvidenceCameraBtn');
    const galleryBtn = byId('preEvidenceGalleryBtn');
    const cameraInput = byId('preEvidenceCameraInput');
    const input = byId('preEvidenceInput');
    if(cameraBtn && cameraInput){
      cameraBtn.addEventListener('click', () => cameraInput.click());
      cameraInput.addEventListener('change', async function(){ await addPreEvidenceFiles(cameraInput.files); cameraInput.value=''; });
    }
    if(galleryBtn && input){
      galleryBtn.addEventListener('click', () => input.click());
      input.addEventListener('change', async function(){ await addPreEvidenceFiles(input.files); input.value=''; });
    }
    try{
      const oldUpdate = updateCounter;
      updateCounter = function(){
        oldUpdate();
        const c = byId('counter');
        const n = (window.ZG_PRE_EVIDENCIAS || []).length;
        if(c && n){ c.textContent += ' · ' + n + ' evidencia(s) prelim.'; }
      };
      updateCounter();
    }catch(e){}
  }

  function setupFirmaCanvasPro(canvasId, hiddenId, clearId){
    const canvas = byId(canvasId), hidden = byId(hiddenId), clear = byId(clearId);
    if(!canvas || !hidden) return;
    const box = canvas.closest('.firma-box');
    const ctx = canvas.getContext('2d');
    let drawing = false, last = null, hasInk = !!hidden.value;
    function size(){
      const old = hidden.value;
      const rect = canvas.getBoundingClientRect();
      const ratio = window.devicePixelRatio || 1;
      canvas.width = Math.max(2, Math.round(rect.width * ratio));
      canvas.height = Math.max(2, Math.round(rect.height * ratio));
      ctx.setTransform(ratio,0,0,ratio,0,0);
      ctx.fillStyle = '#fff'; ctx.fillRect(0,0,rect.width,rect.height);
      ctx.strokeStyle = '#0f2746'; ctx.lineWidth = 2.15; ctx.lineCap='round'; ctx.lineJoin='round';
      if(old){ const img = new Image(); img.onload = () => { ctx.drawImage(img,0,0,rect.width,rect.height); }; img.src = old; if(box) box.classList.add('firmado'); }
    }
    function point(ev){ const r=canvas.getBoundingClientRect(); return {x:ev.clientX-r.left, y:ev.clientY-r.top}; }
    function save(){ try{ hidden.value = canvas.toDataURL('image/png'); hasInk=true; if(box) box.classList.add('firmado'); }catch(e){} }
    function start(ev){ ev.preventDefault(); canvas.setPointerCapture && canvas.setPointerCapture(ev.pointerId); drawing=true; last=point(ev); }
    function move(ev){
      if(!drawing) return;
      ev.preventDefault();
      const p = point(ev);
      const mid = {x:(last.x+p.x)/2, y:(last.y+p.y)/2};
      ctx.beginPath();
      ctx.moveTo(last.x,last.y);
      ctx.quadraticCurveTo(last.x,last.y,mid.x,mid.y);
      ctx.stroke();
      last = p;
      save();
    }
    function end(ev){ if(!drawing) return; ev.preventDefault(); drawing=false; save(); }
    canvas.style.touchAction = 'none';
    canvas.addEventListener('pointerdown', start, {passive:false});
    canvas.addEventListener('pointermove', move, {passive:false});
    canvas.addEventListener('pointerup', end, {passive:false});
    canvas.addEventListener('pointercancel', end, {passive:false});
    if(clear){ clear.addEventListener('click', function(){ const r=canvas.getBoundingClientRect(); ctx.fillStyle='#fff'; ctx.fillRect(0,0,r.width,r.height); hidden.value=''; hasInk=false; if(box) box.classList.remove('firmado'); }); }
    setTimeout(size, 180); window.addEventListener('resize', () => setTimeout(size, 180));
  }
  function setupFirmasPro(){
    setupFirmaCanvasPro('firmaTecnicoCanvas','firmaTecnico','limpiarFirmaTecnico');
    setupFirmaCanvasPro('firmaAdminCanvas','firmaAdmin','limpiarFirmaAdmin');
  }

  function getValSafe(id){ try{ return typeof getVal === 'function' ? getVal(id) : clean(byId(id) ? byId(id).value : ''); }catch(e){ return clean(byId(id) ? byId(id).value : ''); } }
  function parseRepuestosPro(txt){
    return String(txt || '').split(/\r?\n/).map(l => l.trim()).filter(Boolean).map(l => {
      const p = l.split('|').map(x=>x.trim());
      if(p.length >= 4) return {codigo:p[0] === '-' ? '' : p[0], detalle:p[1], cantidad:p[2] || '1', unidad:p[3] || 'und'};
      if(p.length >= 3) return {codigo:p[0] === '-' ? '' : p[0], detalle:p.slice(1,-1).join(' | '), cantidad:p[p.length-1] || '1', unidad:'und'};
      if(p.length === 2) return {codigo:p[0] === '-' ? '' : p[0], detalle:p[1], cantidad:'1'};
      return {codigo:'', detalle:l, cantidad:'1'};
    }).filter(r => r.detalle);
  }

  window.buildPDF = function(sections){
    if(typeof window.zgSyncWorkMaterialsForPdf === 'function') window.zgSyncWorkMaterialsForPdf();
    sections = Object.values((typeof state !== 'undefined' && state.selected) ? state.selected : {}).length ? Object.values(state.selected) : (sections || []);
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({unit:'mm', format:'a4'});
    const PW=210, PH=297, M=14, CW=PW-2*M, FOOT=16;
    const navy=[16,33,58], blue=[31,111,196], soft=[247,250,254], line=[215,226,240], gray=[94,110,130], lightBlue=[232,242,255];
    let y=14;
    const data={
      orden:getValSafe('orden'), fecha: typeof formatDate==='function'?formatDate(window.ZG_FECHA_PDF_ACTUAL || getValSafe('fecha')):(window.ZG_FECHA_PDF_ACTUAL || getValSafe('fecha')), cliente:getValSafe('cliente'), tecnico:(typeof state!=='undefined'?(state.tecnicoNombre||''):'') || getValSafe('tecnicoInput'), direccion:String(getValSafe('direccion')).replace(/\s*\|\s*Coordenadas\s*:.*/i,''), coords:getValSafe('direccionCoords'),
      tipoEquipo:getValSafe('zgTipoEquipo'), tamanoContenedor:getValSafe('zgTamanoContenedor'),
      equipoNo:getValSafe('equipoNo'), serialUnidad:getValSafe('serialUnidad'), marcaEquipo:getValSafe('marcaEquipo'), modeloEquipo:getValSafe('modeloEquipo'), controladorEquipo:getValSafe('controladorEquipo'), anioFabricacion:getValSafe('anioFabricacion'), refrigerante:getValSafe('refrigerante'), setPoint:getValSafe('setPoint'), temperaturaAmbiente:getValSafe('temperaturaAmbiente'), retornoAire:getValSafe('retornoAire'), suministroAire:getValSafe('suministroAire'), presionAlta:getValSafe('presionAlta'), presionBaja:getValSafe('presionBaja'), voltajeL1L2:getValSafe('voltajeL1L2'), voltajeL2L3:getValSafe('voltajeL2L3'), voltajeL1L3:getValSafe('voltajeL1L3'), estadoInicial:getValSafe('estadoInicial'), observacionInicial:(window.zgStripMetaFromText?window.zgStripMetaFromText(getValSafe('observacionInicial')):getValSafe('observacionInicial')),
      gensetHorometroInicial:getValSafe('gensetHorometroInicial'), gensetVoltajeBateriaInicial:getValSafe('gensetVoltajeBateriaInicial'), gensetNivelCombustibleInicial:getValSafe('gensetNivelCombustibleInicial'), gensetNivelAceiteInicial:getValSafe('gensetNivelAceiteInicial'), gensetRefrigeranteMotorInicial:getValSafe('gensetRefrigeranteMotorInicial'), gensetArranqueInicial:getValSafe('gensetArranqueInicial'), gensetFrecuenciaInicial:getValSafe('gensetFrecuenciaInicial'), gensetPresionAceiteInicial:getValSafe('gensetPresionAceiteInicial'),
      estadoFinalEquipo:getValSafe('estadoFinalEquipo'), setPointFinal:getValSafe('setPointFinal'), tempAmbienteFinal:getValSafe('tempAmbienteFinal'), retornoFinal:getValSafe('retornoFinal'), suministroFinal:getValSafe('suministroFinal'), presionAltaFinal:getValSafe('presionAltaFinal'), presionBajaFinal:getValSafe('presionBajaFinal'), voltajeFinalL1L2:getValSafe('voltajeFinalL1L2'), voltajeFinalL2L3:getValSafe('voltajeFinalL2L3'), voltajeFinalL1L3:getValSafe('voltajeFinalL1L3'),
      gensetEstadoFinal:getValSafe('gensetEstadoFinal'), gensetHorometroFinal:getValSafe('gensetHorometroFinal'), gensetArranqueFinal:getValSafe('gensetArranqueFinal'), gensetPruebaCargaFinal:getValSafe('gensetPruebaCargaFinal'), gensetVoltajeBateriaFinal:getValSafe('gensetVoltajeBateriaFinal'), gensetFrecuenciaFinal:getValSafe('gensetFrecuenciaFinal'), gensetVoltajeSalidaL1L2:getValSafe('gensetVoltajeSalidaL1L2'), gensetVoltajeSalidaL2L3:getValSafe('gensetVoltajeSalidaL2L3'), gensetVoltajeSalidaL1L3:getValSafe('gensetVoltajeSalidaL1L3'), gensetPresionAceiteFinal:getValSafe('gensetPresionAceiteFinal'), gensetTemperaturaMotorFinal:getValSafe('gensetTemperaturaMotorFinal'), gensetNivelCombustibleFinal:getValSafe('gensetNivelCombustibleFinal'),
      requiereOtroMantenimiento:getValSafe('zgRequiereOtroMantenimiento'), tipoOtroMantenimiento:getValSafe('zgTipoOtroMantenimiento'), motivoOtroMantenimiento:getValSafe('zgMotivoOtroMantenimiento'),
      horaInicioServicio:(window.zgFormatServiceDateTime?window.zgFormatServiceDateTime(getValSafe('horaInicioServicio')):getValSafe('horaInicioServicio')),
      horaFinServicio:(window.zgFormatServiceDateTime?window.zgFormatServiceDateTime(getValSafe('horaFinServicio')):getValSafe('horaFinServicio')),
      repuestosManual:getValSafe('requiereRepuesto')==='si'?getValSafe('repuestosManual'):'', adminTiendaNombre:getValSafe('adminTiendaNombre'), adminTiendaCargo:getValSafe('adminTiendaCargo'), firmaTecnico:getValSafe('firmaTecnico'), firmaAdmin:getValSafe('firmaAdmin')
    };
    const zgMeta = window.zgCollectServiceMeta ? window.zgCollectServiceMeta() : {modalidad:'',tipoInstalacion:'',requiereOtroMantenimiento:'',tipoOtroMantenimiento:'',maquinaObjetivo:'',maquinas:[]};
    function pressureText(value){
      const v = clean(value);
      if(!v) return '-';
      return /(?:PSI|BAR|KPA|MPA|KG\/CM|KGCM)/i.test(v) ? v : (v + ' PSI');
    }
    function addHeader(){
      doc.setFillColor(...navy); doc.rect(0,0,PW,25,'F');
      doc.setFillColor(...blue); doc.rect(0,25,PW,2,'F');
      const logo=byId('brandLogo');
      if(logo && logo.complete){ try{ doc.addImage(logo,'PNG',M,6,42,13); }catch(e){} }
      doc.setFont('helvetica','bold'); doc.setFontSize(15); doc.setTextColor(255,255,255); doc.text('INFORME TÉCNICO DE SERVICIO', PW-M, 12, {align:'right'});
      doc.setFont('helvetica','normal'); doc.setFontSize(8.2); doc.setTextColor(210,225,245); doc.text('ZGROUP S.A.C. · Área técnica', PW-M, 18, {align:'right'});
      y=36;
    }
    function footer(){
      const total=doc.internal.getNumberOfPages(); const stamp=new Date().toLocaleString('es-PE');
      for(let p=1;p<=total;p++){ doc.setPage(p); doc.setDrawColor(...line); doc.line(M,PH-11,PW-M,PH-11); doc.setFont('helvetica','normal'); doc.setFontSize(7.5); doc.setTextColor(150,163,180); doc.text('Documento generado por ZGROUP S.A.C.', M, PH-6.8); doc.text('Página '+p+' de '+total, PW-M, PH-6.8, {align:'right'}); }
    }
    function ensure(h){ if(y+h > PH-FOOT){ doc.addPage(); addHeader(); } }
    function section(title, minContentH){
      const minH = Number(minContentH || 28);
      ensure(13 + minH);
      doc.setFillColor(...navy);
      doc.setDrawColor(...navy);
      doc.roundedRect(M,y,CW,9,1.8,1.8,'FD');
      doc.setFillColor(...blue);
      doc.rect(M,y+8,CW,1.1,'F');
      doc.setFont('helvetica','bold');
      doc.setFontSize(10.4);
      doc.setTextColor(255,255,255);
      doc.text(title.toUpperCase(), M+4, y+6);
      y+=13;
    }
    function infoBox(rows){
      const colW=CW/2, rowH=11; ensure(Math.ceil(rows.length/2)*rowH+4); let x=M, yy=y;
      for(let i=0;i<rows.length;i++){ const c=i%2, r=Math.floor(i/2); x=M+c*colW; yy=y+r*rowH; doc.setDrawColor(...line); doc.setFillColor(i%4<2?250:247,252,255); doc.rect(x,yy,colW,rowH,'FD'); doc.setFont('helvetica','bold'); doc.setFontSize(7); doc.setTextColor(...gray); doc.text(String(rows[i][0]).toUpperCase(), x+3, yy+4); doc.setFont('helvetica','normal'); doc.setFontSize(8.4); doc.setTextColor(...navy); const lines=doc.splitTextToSize(rows[i][1]||'-', colW-6); doc.text(lines.slice(0,2), x+3, yy+8); }
      y += Math.ceil(rows.length/2)*rowH + 6;
    }
    function serviceInfoBox(){
      function drawCell(x,w,label,value,h,fill){
        doc.setDrawColor(...line);
        doc.setFillColor(...fill);
        doc.rect(x,y,w,h,'FD');
        doc.setFont('helvetica','bold');
        doc.setFontSize(7.6);
        doc.setTextColor(...gray);
        doc.text(String(label).toUpperCase(), x+4, y+4.8);
        doc.setFont('helvetica','normal');
        doc.setFontSize(9.2);
        doc.setTextColor(...navy);
        const lines = doc.splitTextToSize(clean(value) || '-', w-8);
        doc.text(lines.slice(0, Math.max(1, Math.floor((h-6)/4.2))), x+4, y+9.4);
      }
      function drawFull(label,value){
        const lines = doc.splitTextToSize(clean(value) || '-', CW-8);
        const h = Math.max(14, 9 + lines.length*4.5);
        ensure(h+1);
        doc.setDrawColor(...line);
        doc.setFillColor(248,251,255);
        doc.rect(M,y,CW,h,'FD');
        doc.setFont('helvetica','bold');
        doc.setFontSize(7.8);
        doc.setTextColor(...gray);
        doc.text(String(label).toUpperCase(), M+4, y+4.9);
        doc.setFont('helvetica','normal');
        doc.setFontSize(9.2);
        doc.setTextColor(...navy);
        doc.text(lines, M+4, y+9.6);
        y += h;
      }
      const colW = CW/2;
      ensure(56);
      let h=12;
      drawCell(M,colW,'N° de reporte',data.orden,h,[250,252,255]);
      drawCell(M+colW,colW,'Fecha',data.fecha,h,[250,252,255]);
      y+=h;
      drawCell(M,colW,'Cliente',data.cliente,h,[245,250,255]);
      drawCell(M+colW,colW,'Técnico',data.tecnico,h,[245,250,255]);
      y+=h;
      drawFull('Dirección / ubicación', data.direccion);
      // Coordenadas ocultas en el PDF: la ubicación se muestra solo como dirección.
      y+=7;
    }
    function kvTable(rows){
      rows = (rows || []).filter(r => r && clean(r[1]));
      if(!rows.length) return;

      // Tabla compacta: conserva dos datos por fila sin ocupar demasiado alto.
      const labelW = 36;
      const valueW = (CW - (labelW * 2)) / 2;
      const labelFill = navy;
      const valueFillA = [255,255,255];
      const valueFillB = [247,250,254];
      let i = 0;

      function drawLabelCell(x, yy, w, h, label){
        doc.setDrawColor(...line);
        doc.setFillColor(...labelFill);
        doc.rect(x, yy, w, h, 'FD');
        doc.setFont('helvetica','bold');
        doc.setFontSize(6.25);
        doc.setTextColor(255,255,255);
        const lns = doc.splitTextToSize(String(label || '').toUpperCase(), w - 4.5);
        const lineH = 3.05;
        const blockH = lns.length * lineH;
        const ty = yy + Math.max(3.9, (h - blockH) / 2 + 2.55);
        doc.text(lns, x + 2.4, ty);
      }

      function drawValueCell(x, yy, w, h, value, rowIndex){
        doc.setDrawColor(...line);
        const fill = rowIndex % 2 ? valueFillB : valueFillA;
        doc.setFillColor(fill[0], fill[1], fill[2]);
        doc.rect(x, yy, w, h, 'FD');
        doc.setFont('helvetica','bold');
        doc.setFontSize(7.75);
        doc.setTextColor(...navy);
        const lns = doc.splitTextToSize(String(value || '-'), w - 5.5);
        const lineH = 3.7;
        const blockH = lns.length * lineH;
        const ty = yy + Math.max(4.8, (h - blockH) / 2 + 3.1);
        doc.text(lns, x + 3, ty);
      }

      while(i < rows.length){
        const r1 = rows[i];
        const r2 = rows[i+1];
        const label1 = String(r1[0] || '');
        const val1 = String(r1[1] || '-');
        const fullRow = /observaci[oó]n|raz[oó]n del mantenimiento/i.test(label1) || val1.length > 82;

        if(fullRow){
          const valLines = doc.splitTextToSize(val1, CW - labelW - 7);
          const h = Math.max(10.5, valLines.length * 3.75 + 5.5);
          ensure(h);
          drawLabelCell(M, y, labelW, h, label1);
          doc.setDrawColor(...line);
          doc.setFillColor(255,255,255);
          doc.rect(M + labelW, y, CW - labelW, h, 'FD');
          doc.setFont('helvetica','normal');
          doc.setFontSize(7.65);
          doc.setTextColor(...navy);
          doc.text(valLines, M + labelW + 3.2, y + 4.5);
          y += h;
          i += 1;
          continue;
        }

        const pair = [r1, r2];
        const labelLines1 = doc.splitTextToSize(String(pair[0]?.[0] || '').toUpperCase(), labelW - 4.5);
        const valueLines1 = doc.splitTextToSize(String(pair[0]?.[1] || '-'), valueW - 5.5);
        const labelLines2 = pair[1] ? doc.splitTextToSize(String(pair[1][0] || '').toUpperCase(), labelW - 4.5) : [];
        const valueLines2 = pair[1] ? doc.splitTextToSize(String(pair[1][1] || '-'), valueW - 5.5) : [];
        const h = Math.max(
          10,
          labelLines1.length * 3.05 + 4.4,
          valueLines1.length * 3.7 + 4.4,
          labelLines2.length * 3.05 + 4.4,
          valueLines2.length * 3.7 + 4.4
        );
        ensure(h);

        let x = M;
        drawLabelCell(x, y, labelW, h, pair[0][0]); x += labelW;
        drawValueCell(x, y, valueW, h, pair[0][1], Math.floor(i/2)); x += valueW;

        if(pair[1]){
          drawLabelCell(x, y, labelW, h, pair[1][0]); x += labelW;
          drawValueCell(x, y, valueW, h, pair[1][1], Math.floor(i/2));
        } else {
          doc.setDrawColor(...line);
          doc.setFillColor(255,255,255);
          doc.rect(x, y, labelW + valueW, h, 'FD');
        }
        y += h;
        i += 2;
      }
      y += 5;
    }
    function bullets(title, arr){ arr=Array.isArray(arr)?arr.filter(Boolean):[]; if(!arr.length) return; section(title); arr.forEach(b=>{ const lines=doc.splitTextToSize(String(b), CW-8); ensure(lines.length*4.2+2); doc.setFont('helvetica','normal'); doc.setFontSize(8.5); doc.setTextColor(...navy); doc.text('•', M+2, y+3.2); doc.text(lines, M+7, y+3.2); y+=lines.length*4.2+1; }); y+=3; }
    function paragraph(title, txt){ txt=clean(txt); if(!txt) return; section(title); const lines=doc.splitTextToSize(txt, CW); lines.forEach(line=>{ ensure(5); doc.setFont('helvetica','normal'); doc.setFontSize(8.7); doc.setTextColor(...navy); doc.text(line,M,y+3.2); y+=4.3; }); y+=4; }
    function repuestosTable(rows,title){ rows=Array.isArray(rows)?rows:[]; if(!rows.length) return; section(title || 'Repuestos a considerar', 35); const widths=[32,CW-72,18,22]; const heads=['CÓDIGO','DETALLE','CANT.','UND.']; let x=M; ensure(9); heads.forEach((h,i)=>{ doc.setFillColor(...navy); doc.setDrawColor(255,255,255); doc.rect(x,y,widths[i],8,'FD'); doc.setFont('helvetica','bold'); doc.setFontSize(7.2); doc.setTextColor(255,255,255); doc.text(h,x+widths[i]/2,y+5.2,{align:'center'}); x+=widths[i]; }); y+=8; rows.forEach((r,i)=>{ const det=doc.splitTextToSize(r.detalle||'-',widths[1]-5); const cod=doc.splitTextToSize(r.codigo||'Sin código',widths[0]-5); const und=String(r.unidad||r.und||'und'); const h=Math.max(9,det.length*4.3+5,cod.length*4.3+5); ensure(h); x=M; [cod,det,[String(r.cantidad||'1')],[und]].forEach((cell,j)=>{ doc.setDrawColor(...line); doc.setFillColor(i%2?255:247,250,254); doc.rect(x,y,widths[j],h,'FD'); doc.setFont('helvetica',j===1?'normal':'bold'); doc.setFontSize(8.0); doc.setTextColor(...navy); doc.text(cell,x+(j>=2?widths[j]/2:2.5),y+5,{align:j>=2?'center':'left'}); x+=widths[j]; }); y+=h; }); y+=6; }
    function photos(title, arr){
      arr=Array.isArray(arr)?arr:[];
      if(!arr.length) return;
      section(title, 78);
      const gap=6, cellW=(CW-gap)/2, maxH=58;
      for(let i=0;i<arr.length;i+=2){
        const row=arr.slice(i,i+2);
        ensure(maxH+14);
        row.forEach((p,j)=>{
          const x=M+j*(cellW+gap);
          const ar=(p.w&&p.h)?p.w/p.h:1.4;
          let w=cellW, h=w/ar;
          if(h>maxH){ h=maxH; w=h*ar; }
          const xx=x+(cellW-w)/2;
          try{ doc.addImage(p.dataUrl,'JPEG',xx,y,w,h); }catch(e){}
          doc.setDrawColor(...line);
          doc.setFillColor(255,255,255);
          doc.rect(xx,y,w,h,'S');
          doc.setFont('helvetica','italic');
          doc.setFontSize(8);
          doc.setTextColor(...gray);
          const label='Evidencia '+(i+j+1);
          doc.text(label, x, y+h+4.5);
        });
        y+=maxH+13;
      }
    }
    function collectWorkMaterialsForPDF(s, idx, allSections){
      function normRow(r){
        if(!r) return null;
        const codigo = clean(r.codigo || r.code || r.cod || '');
        const detalle = clean(r.detalle || r.material || r.nombre || r.descripcion || '');
        const cantidad = clean(r.cantidad || r.qty || r.cant || '1') || '1';
        const unidad = clean(r.unidad || r.und || r.unit || '') || 'und';
        if(!detalle) return null;
        return {codigo:codigo, detalle:detalle, cantidad:cantidad, unidad:unidad};
      }
      function normalizeArray(arr){
        if(!Array.isArray(arr)) return [];
        return arr.map(normRow).filter(Boolean);
      }
      function parseText(txt){
        return parseRepuestosPro(String(txt || '')).map(normRow).filter(Boolean);
      }

      const sources = [];
      try{
        const snap = window.ZG_WORK_MATERIALS_PDF || {};
        const fromSnap = snap[s && s.id ? s.id : String(idx)];
        if(Array.isArray(fromSnap)) sources.push(fromSnap);
      }catch(e){}
      if(s){
        sources.push(s.repuestosTrabajo, s.materialesTrabajo, s.materiales, s.repuestos);
        if(s.campos){
          sources.push(s.campos.repuestosTrabajo, s.campos.materialesTrabajo, s.campos.materiales, s.campos.repuestos);
        }
      }
      try{
        const live = (typeof state !== 'undefined' && state.selected && s && state.selected[s.id]) ? state.selected[s.id] : null;
        if(live && live !== s){
          sources.unshift(live.repuestosTrabajo, live.materialesTrabajo, live.materiales, live.repuestos);
        }
      }catch(e){}

      for(const src of sources){
        if(Array.isArray(src)){
          const rows = normalizeArray(src);
          if(rows.length) return rows;
        }else if(typeof src === 'string' && clean(src)){
          const rows = parseText(src);
          if(rows.length) return rows;
        }
      }

      // Respaldo visual: toma lo que el técnico ve en la tabla del trabajo.
      try{
        const panels = Array.from(document.querySelectorAll('#panels .panel'));
        const panel = panels[idx];
        if(panel){
          const rows = Array.from(panel.querySelectorAll('.zg-work-material-table tbody tr')).map(function(tr){
            const cells = tr.querySelectorAll('td');
            if(cells.length < 4) return null;
            const codeNode = cells[0].querySelector('b');
            const detailNode = cells[1].querySelector('input');
            const qtyNode = cells[2].querySelector('input');
            const unitNode = cells[3].querySelector('.unit');
            return normRow({
              codigo: codeNode ? codeNode.textContent.replace(/^Sin código$/i,'') : '',
              detalle: detailNode ? detailNode.value : '',
              cantidad: qtyNode ? qtyNode.value : '1',
              unidad: unitNode ? unitNode.textContent : 'und'
            });
          }).filter(Boolean);
          if(rows.length) return rows;
        }
      }catch(e){}

      // Compatibilidad con informes anteriores que guardaban una sola lista general.
      // Se coloca una sola vez, en el primer trabajo, para evitar duplicados.
      if(Number(idx) === 0 && clean(data.repuestosManual)){
        return parseText(data.repuestosManual);
      }
      return [];
    }

    function recomendacionesTecnicas(){
      // Las recomendaciones dependen únicamente del tipo de equipo seleccionado.
      // Se reconoce tanto "Genset" como "Generador (genset)" para evitar cruces entre plantillas.
      const tipoRecomendacion = clean(data.tipoEquipo || zgMeta.tipoEquipo || getValSafe('zgTipoEquipo')).toLowerCase();
      const esGenset = tipoRecomendacion.includes('genset') || tipoRecomendacion.includes('generador');
      const recomendaciones = esGenset ? [
        'Cumplir el mantenimiento preventivo del generador según el horómetro y el programa establecido por la empresa.',
        'Verificar antes de cada operación los niveles de aceite, refrigerante del motor y combustible, además de revisar la presencia de fugas.',
        'Mantener limpios y ajustados los bornes de batería, conexiones eléctricas y elementos de protección; corregir cualquier señal de sulfatación o recalentamiento.',
        'Realizar pruebas periódicas de arranque y operación, confirmando que el voltaje de salida, la temperatura del motor y la carga de batería se mantengan estables.',
        'Conservar libres de obstrucciones el radiador, la admisión de aire y la zona de ventilación del generador para evitar sobrecalentamiento y pérdida de rendimiento.'
      ] : [
        'Se recomienda ejecutar los mantenimientos preventivos en las fechas programadas.',
        'Se recomienda revisar visualmente el sistema de refrigeración para verificar si presenta fugas u obstrucciones por falta de mantenimiento.',
        'Se recomienda realizar una inspección visual periódica del sistema eléctrico para validar que todo se encuentre correctamente instalado y sin observaciones.',
        'Se recomienda mantener y suministrar los niveles de potencia indicados en la placa del equipo, a fin de conservar los componentes eléctricos en correcto funcionamiento y prolongar su vida útil.',
        'Se recomienda asegurar el cierre correcto de las puertas del equipo para evitar daños en los componentes, así como la acumulación de hielo y condensación interna.',
        'Se recomienda respetar los límites de carga para garantizar una correcta ventilación dentro del equipo.',
        'Se recomienda apagar la unidad durante cada operación de ingreso o retiro de producto.'
      ];
      /*
       * El bloque completo se mide antes de dibujarse.
       * Si no entra entero en el espacio restante, pasa completo a la página siguiente.
       * No se usa ensure() dentro del recorrido para impedir que las recomendaciones
       * queden divididas entre dos páginas.
       */
      const recommendationFontSize = 8.25;
      const recommendationLineHeight = 3.85;
      const recommendationRowGap = 1.15;
      const recommendationTitleHeight = 13;

      doc.setFont('helvetica','normal');
      doc.setFontSize(recommendationFontSize);

      const recommendationRows = recomendaciones.map((txt, i) => {
        const lines = doc.splitTextToSize(txt, CW - 13);
        const h = Math.max(6.7, lines.length * recommendationLineHeight + 2.25);
        return {number:String(i + 1) + '.', lines, h};
      });

      const recommendationContentHeight = recommendationRows.reduce((sum, row) => sum + row.h + recommendationRowGap, 0) + 3;
      const recommendationBlockHeight = recommendationTitleHeight + recommendationContentHeight;

      // Reserva una página completa para el bloque cuando el espacio actual no alcanza.
      if(y + recommendationBlockHeight > PH - FOOT){
        doc.addPage();
        addHeader();
      }

      section('8. Recomendaciones generales', recommendationContentHeight);
      doc.setFont('helvetica','normal');
      doc.setFontSize(recommendationFontSize);
      doc.setTextColor(...navy);

      recommendationRows.forEach((row, i) => {
        doc.setDrawColor(...line);
        doc.setFillColor(i % 2 ? 255 : 247,250,254);
        doc.roundedRect(M, y, CW, row.h, 1.5, 1.5, 'FD');
        doc.setFont('helvetica','bold');
        doc.setTextColor(...blue);
        doc.text(row.number, M + 3, y + 4.35);
        doc.setFont('helvetica','normal');
        doc.setTextColor(...navy);
        doc.text(row.lines, M + 10, y + 4.35);
        y += row.h + recommendationRowGap;
      });
      y += 3;
    }

    function firmas(){
      // Página exclusiva: evita que el encabezado de conformidad quede en una
      // página y las firmas en la siguiente.
      doc.addPage();
      addHeader();
      section('Conformidad del servicio', 60);
      doc.setFont('helvetica','normal');
      doc.setFontSize(8.4);
      doc.setTextColor(...gray);
      const txtConf = 'Las firmas registran la conformidad de la atención técnica y la recepción del servicio por parte del cliente.';
      const confLines = doc.splitTextToSize(txtConf, CW);
      doc.text(confLines, M, y);
      y += Math.max(9, confLines.length*4.1 + 3);

      const gap = 8;
      const boxW = (CW-gap)/2;
      const boxH = 44;
      const boxes = [
        {title:'TÉCNICO RESPONSABLE', name:data.tecnico||'Técnico responsable', cargo:'Técnico de servicio', firma:data.firmaTecnico},
        {title:'RESPONSABLE DEL CLIENTE', name:data.adminTiendaNombre||'Responsable del cliente', cargo:data.adminTiendaCargo||'Cargo no registrado', firma:data.firmaAdmin}
      ];
      boxes.forEach((b,i)=>{
        const x = M+i*(boxW+gap);
        doc.setDrawColor(...line);
        doc.setFillColor(250,252,255);
        doc.roundedRect(x,y,boxW,boxH,2,2,'FD');
        doc.setFont('helvetica','bold');
        doc.setFontSize(7.4);
        doc.setTextColor(...blue);
        doc.text(b.title,x+4,y+5);
        if(b.firma && /^data:image\//.test(b.firma)){
          try{ doc.addImage(b.firma,'PNG',x+10,y+8,boxW-20,16); }catch(e){}
        }
        doc.setDrawColor(85,102,126);
        doc.line(x+10,y+27,x+boxW-10,y+27);
        doc.setFont('helvetica','bold');
        doc.setFontSize(8.2);
        doc.setTextColor(...navy);
        const nameLine = doc.splitTextToSize(b.name, boxW-10)[0] || '-';
        doc.text(nameLine, x+boxW/2, y+33, {align:'center'});
        doc.setFont('helvetica','normal');
        doc.setFontSize(7.3);
        doc.setTextColor(...gray);
        const cargoLine = doc.splitTextToSize(b.cargo || '-', boxW-10)[0] || '-';
        doc.text(cargoLine, x+boxW/2, y+38, {align:'center'});
      });
      y += boxH+6;
    }


    function zgPdfChecklistItemsForWork(s){
      try{if(typeof window.zgGetReeferChecklistItemsForWork==='function')return window.zgGetReeferChecklistItemsForWork(s)||[];}catch(e){}
      return [];
    }
    function zgPdfChecklistRaw(s,d){
      const values=(s&&s.reeferChecklist&&typeof s.reeferChecklist==='object')?s.reeferChecklist:{};
      return values[String(d.key)] ?? values[d.key] ?? values[String(d.n)] ?? values[d.n] ?? '';
    }
    function zgPdfChecklistValue(s,d){
      const raw=zgPdfChecklistRaw(s,d);
      if(d.kind==='three'){
        const v=raw&&typeof raw==='object'?raw:{};
        return {l1:clean(v.l1)||'—',l2:clean(v.l2)||'—',l3:clean(v.l3)||'—',unit:d.unit||'—'};
      }
      if(d.kind==='okvolt'){
        const v=raw&&typeof raw==='object'?raw:{};
        const mode=clean(v.modo).toUpperCase();
        if(mode==='OK')return {value:'OK',unit:'—'};
        if(mode==='V'){
          const value=clean(v.valor).replace(/\s*v\s*$/i,'');
          return {value:value||'—',unit:value?'V':'—'};
        }
        const legacy=clean(raw&&typeof raw==='object'?raw.valor:raw);
        if(/^ok(?:\s*v)?$/i.test(legacy))return {value:'OK',unit:'—'};
        const value=legacy.replace(/\s*v\s*$/i,'');
        return {value:value||'—',unit:value?'V':'—'};
      }
      const value=clean(raw&&typeof raw==='object'?raw.valor:raw)||'—';
      return {value:value,unit:(value==='—'?'—':(d.unit||'—'))};
    }
    function zgPdfReeferChecklist(s){
      const defs=zgPdfChecklistItemsForWork(s);if(!defs.length)return;
      const simple=defs.filter(d=>d.kind!=='three');
      const three=defs.filter(d=>d.kind==='three');
      const gap=4;
      const colW=(CW-gap)/2;
      const lineH=3.05;

      function pairRows(rows){
        const half=Math.ceil(rows.length/2),out=[];
        for(let i=0;i<half;i++)out.push([rows[i]||null,rows[i+half]||null]);
        return out;
      }
      const simpleRows=simple.map(function(d){return {d,cell:zgPdfChecklistValue(s,d)};});
      const threeRows=three.map(function(d){return {d,cell:zgPdfChecklistValue(s,d)};});
      const simpleWidths=[7,colW-7-23-10,23,10];
      const threeWidths=[7,colW-7-11-11-11-10,11,11,11,10];

      function preparedPairs(rows,widths,isThree){
        return pairRows(rows).map(function(pair){
          let maxH=8;
          const ready=pair.map(function(r){
            if(!r)return null;
            const labelLines=doc.splitTextToSize(String(r.d.label||'—'),widths[1]-3);
            let valueLines=[];
            if(!isThree)valueLines=doc.splitTextToSize(String(r.cell.value||'—'),widths[2]-3);
            const h=Math.max(8,Math.max(labelLines.length,valueLines.length||1)*lineH+3.2);
            maxH=Math.max(maxH,h);
            return Object.assign({},r,{labelLines,valueLines});
          });
          return {items:ready,h:maxH};
        });
      }
      const simplePairs=preparedPairs(simpleRows,simpleWidths,false);
      const threePairs=preparedPairs(threeRows,threeWidths,true);
      const simpleH=simplePairs.length?(6+7+simplePairs.reduce((a,r)=>a+r.h,0)):0;
      const threeH=threePairs.length?(6+7+threePairs.reduce((a,r)=>a+r.h,0)):0;
      const between=(simplePairs.length&&threePairs.length)?4:0;
      const contentH=simpleH+between+threeH+4;
      if(y+13+contentH>PH-FOOT){doc.addPage();addHeader();}
      section('Lista de inspección técnica reefer',contentH);

      function subTitle(title){
        doc.setDrawColor(...line);doc.setFillColor(232,242,255);doc.roundedRect(M,y,CW,6,1,1,'FD');
        doc.setFont('helvetica','bold');doc.setFontSize(7.2);doc.setTextColor(...blue);doc.text(title,M+3,y+4.2);y+=6;
      }
      function tableHeaderAt(x0,widths,heads){
        let x=x0;
        heads.forEach(function(h,i){
          doc.setFillColor(...navy);doc.setDrawColor(255,255,255);doc.rect(x,y,widths[i],7,'FD');
          doc.setFont('helvetica','bold');doc.setFontSize(5.8);doc.setTextColor(255,255,255);
          doc.text(h,x+widths[i]/2,y+4.7,{align:'center'});x+=widths[i];
        });
      }
      function textAt(lines,x,w,rowY,rowH,align,bold,size){
        const arr=Array.isArray(lines)?lines:[String(lines)];
        const blockH=arr.length*lineH;
        const ty=rowY+Math.max(3.7,(rowH-blockH)/2+3.05);
        doc.setFont('helvetica',bold?'bold':'normal');doc.setFontSize(size||6.15);doc.setTextColor(...navy);
        doc.text(arr,align==='center'?x+w/2:x+1.5,ty,{align:align||'left'});
      }
      function unitAt(value,x,w,rowY,rowH){
        const v=String(value||'—').trim();
        if(v==='Ω'){
          const fonts=typeof doc.getFontList==='function'?doc.getFontList():{};
          const symbolName=Object.keys(fonts||{}).find(k=>String(k).toLowerCase()==='symbol');
          if(symbolName){
            doc.setFont(symbolName,'normal');doc.setFontSize(7.2);doc.setTextColor(...navy);
            doc.text('W',x+w/2,rowY+rowH/2+2,{align:'center'});
            doc.setFont('helvetica','normal');
          }else{
            textAt('Ohm',x,w,rowY,rowH,'center',false,5.8);
          }
        }else textAt(v,x,w,rowY,rowH,'center',false,6.1);
      }
      function drawMiniRow(item,x0,widths,rowY,rowH,isThree,index){
        if(!item)return;
        let x=x0;
        const fill=index%2?[247,250,254]:[255,255,255];
        const cells=isThree
          ? [String(item.d.n),item.labelLines,item.cell.l1,item.cell.l2,item.cell.l3,item.cell.unit||'—']
          : [String(item.d.n),item.labelLines,item.valueLines,String(item.cell.unit||'—')];
        cells.forEach(function(cell,j){
          doc.setDrawColor(...line);doc.setFillColor(...fill);doc.rect(x,rowY,widths[j],rowH,'FD');
          if(j===widths.length-1)unitAt(cell,x,widths[j],rowY,rowH);
          else textAt(cell,x,widths[j],rowY,rowH,(j===0||(isThree&&j>=2))?'center':'left',j===0||j===1,isThree?5.8:6.1);
          x+=widths[j];
        });
      }

      if(simplePairs.length){
        subTitle('RESULTADOS GENERALES');
        tableHeaderAt(M,simpleWidths,['N°','PUNTO','RESULTADO','UND.']);
        tableHeaderAt(M+colW+gap,simpleWidths,['N°','PUNTO','RESULTADO','UND.']);
        y+=7;
        simplePairs.forEach(function(pair,i){
          const rowY=y;
          drawMiniRow(pair.items[0],M,simpleWidths,rowY,pair.h,false,i);
          drawMiniRow(pair.items[1],M+colW+gap,simpleWidths,rowY,pair.h,false,i);
          y+=pair.h;
        });
      }

      if(threePairs.length){
        if(simplePairs.length)y+=between;
        subTitle('MEDICIONES POR FASE');
        tableHeaderAt(M,threeWidths,['N°','PUNTO','L1','L2','L3','UND.']);
        tableHeaderAt(M+colW+gap,threeWidths,['N°','PUNTO','L1','L2','L3','UND.']);
        y+=7;
        threePairs.forEach(function(pair,i){
          const rowY=y;
          drawMiniRow(pair.items[0],M,threeWidths,rowY,pair.h,true,i);
          drawMiniRow(pair.items[1],M+colW+gap,threeWidths,rowY,pair.h,true,i);
          y+=pair.h;
        });
      }
      y+=5;
    }

    function zgPdfParametrosReefer(s){
      const tipo=clean(data.tipoEquipo||zgMeta.tipoEquipo).toLowerCase();
      if(tipo.includes('genset')||tipo.includes('generador'))return;
      const id=clean(s&&s.id).toLowerCase();
      const nombre=clean(s&&s.nombre).toLowerCase();
      const aplica=id==='instalacion_reefer'||id==='mantenimiento_productivo'
        ||(nombre.includes('instalacion')&&nombre.includes('reefer'))
        ||nombre.includes('mantenimiento preventivo');
      if(!aplica)return;
      const rows=(window.ZG_REEFER_PARAMETROS_ROWS||[
        {key:'corriente',label:'Corriente de línea',unit:'A'},
        {key:'motor_evap1',label:'8. Motor ventilador del evaporador 1',unit:'Ω'},
        {key:'motor_evap2',label:'9. Motor ventilador del evaporador 2',unit:'Ω'},
        {key:'motor_cond',label:'10. Motor ventilador del condensador',unit:'Ω'},
        {key:'compresor',label:'11. Compresor de refrigeración',unit:'Ω'},
        {key:'resistencias',label:'12. Resistencias de calefacción / heaters',unit:'Ω'}
      ]);
      const values=(s&&s.parametrosReefer&&typeof s.parametrosReefer==='object')?s.parametrosReefer:{};
      const totalH=13+7+(rows.length*7)+5;
      if(y+totalH>PH-FOOT){doc.addPage();addHeader();}
      section('Parámetros eléctricos reefer',totalH-13);
      const widths=[72,31,31,31,23],heads=['PARÁMETRO','L1','L2','L3','UND.'];let x=M;
      heads.forEach(function(h,i){
        doc.setFillColor(...navy);doc.setDrawColor(255,255,255);doc.rect(x,y,widths[i],7,'FD');
        doc.setFont('helvetica','bold');doc.setFontSize(6.8);doc.setTextColor(255,255,255);
        doc.text(h,x+widths[i]/2,y+4.8,{align:'center'});x+=widths[i];
      });
      y+=7;
      rows.forEach(function(r,i){
        const v=(values[r.key]&&typeof values[r.key]==='object')?values[r.key]:{};
        x=M;
        const cells=[r.label,clean(v.l1)||'—',clean(v.l2)||'—',clean(v.l3)||'—',r.unit||'—'];
        cells.forEach(function(cell,j){
          doc.setDrawColor(...line);doc.setFillColor(i%2?247:255,i%2?250:255,i%2?254:255);doc.rect(x,y,widths[j],7,'FD');
          doc.setFont('helvetica',j===0?'bold':'normal');doc.setFontSize(6.8);doc.setTextColor(...navy);
          const lines=doc.splitTextToSize(String(cell),widths[j]-3);
          doc.text(lines,x+(j===0?1.5:widths[j]/2),y+4.6,{align:j===0?'left':'center'});
          x+=widths[j];
        });
        y+=7;
      });
      y+=5;
    }

    addHeader();
    serviceInfoBox();
    if(zgMeta.modalidad || zgMeta.tipoInstalacion){
      section('Configuración del servicio', 18);
      kvTable([
        ['Modalidad comercial', zgMeta.modalidad || '-'],
        ['Tipo de instalación', zgMeta.tipoInstalacion || '-'],
        ['Tipo de equipo', data.tipoEquipo || zgMeta.tipoEquipo || '-'],
        ['Tamaño del contenedor', (data.tipoEquipo === 'Genset' ? 'No aplica' : (data.tamanoContenedor || zgMeta.tamanoContenedor || '-'))]
      ]);
    }
    if(zgMeta.tipoInstalacion === 'Túnel' && Array.isArray(zgMeta.maquinas) && zgMeta.maquinas.length){
      section('Máquinas del túnel', 36);
      kvTable(zgMeta.maquinas.map(function(m, i){
        const ref = zgMeta.maquinaObjetivo === m.id ? ' · Referencia preliminar' : '';
        return ['Máquina '+(i+1), [m.marca, m.controlador, m.serie ? ('Serie '+m.serie) : ''].filter(Boolean).join(' · ')+ref];
      }));
    }
    section((data.tipoEquipo === 'Genset' ? 'Inspección preliminar del genset' : 'Inspección preliminar del equipo reefer'), 82);
    const prelimRows = data.tipoEquipo === 'Genset' ? [
      ['N° de genset / equipo',data.equipoNo],['Serial unidad',data.serialUnidad],['Marca',data.marcaEquipo],['Controlador',data.controladorEquipo],['Horómetro inicial',data.gensetHorometroInicial?data.gensetHorometroInicial+' h':''],['Voltaje batería inicial',data.gensetVoltajeBateriaInicial],['Nivel combustible',data.gensetNivelCombustibleInicial],['Nivel de aceite',data.gensetNivelAceiteInicial],['Refrigerante del motor',data.gensetRefrigeranteMotorInicial],['Prueba de arranque',data.gensetArranqueInicial],['Voltaje L1-L2',data.voltajeL1L2],['Voltaje L2-L3',data.voltajeL2L3],['Voltaje L1-L3',data.voltajeL1L3],['Estado inicial',data.estadoInicial],['Observación inicial',(window.zgStripMetaFromText?window.zgStripMetaFromText(data.observacionInicial):data.observacionInicial)]
    ] : [
      ['Contenedor / equipo',data.equipoNo],['Tamaño del contenedor',data.tamanoContenedor],['Serial unidad',data.serialUnidad],['Marca',data.marcaEquipo],['Modelo',data.modeloEquipo],['Controlador',data.controladorEquipo],['Año de fabricación',data.anioFabricacion],['Refrigerante',data.refrigerante||'No registrado'],['Set point',data.setPoint?data.setPoint+' °C':''],['Temp. ambiente',data.temperaturaAmbiente?data.temperaturaAmbiente+' °C':''],['Retorno aire',data.retornoAire?data.retornoAire+' °C':''],['Suministro aire',data.suministroAire?data.suministroAire+' °C':''],['Presión alta',pressureText(data.presionAlta)],['Presión baja',pressureText(data.presionBaja)],['Voltaje L1-L2',data.voltajeL1L2],['Voltaje L2-L3',data.voltajeL2L3],['Voltaje L1-L3',data.voltajeL1L3],['Estado inicial',data.estadoInicial],['Observación inicial',(window.zgStripMetaFromText?window.zgStripMetaFromText(data.observacionInicial):data.observacionInicial)]
    ];
    kvTable(prelimRows.filter(r=>clean(r[1])));
    photos('Evidencias preliminares antes de la intervención', window.ZG_PRE_EVIDENCIAS || []);
    (sections||[]).forEach((s,idx)=>{
      section((idx+1)+'. '+(s.nombre||'Trabajo realizado'), 82);
      if(zgMeta.tipoInstalacion === 'Túnel' && s.maquinaAsignada){
        const maq = (zgMeta.maquinas || []).find(function(m){ return m.id === s.maquinaAsignada; });
        if(maq) kvTable([['Máquina atendida', [maq.id.replace('M','Máquina '), maq.marca, maq.controlador, maq.serie ? ('Serie '+maq.serie) : ''].filter(Boolean).join(' · ')]]);
      }
      if(s.auto){ bullets('Actividades realizadas',s.auto.actividades); bullets('Observaciones',s.auto.hallazgos); bullets('Acciones ejecutadas',s.auto.acciones); }
      zgPdfReeferChecklist(s);
      zgPdfParametrosReefer(s);
      if(s.campos){ Object.keys(s.campos).forEach(k=>{
        // Campos retirados del mantenimiento preventivo. Se ignoran también en informes antiguos.
        if(k === 'resuelto') return;
        if(s.id === 'asistencia_tecnica' && ['problema','diagnostico','solucion'].includes(k)) return;
        if(s.id === 'mantenimiento_correctivo' && ['falla_diagnostico','trabajo_resultado'].includes(k)) return;
        if(s.id === 'mantenimiento_productivo' && ['tipo','zonas','parametros','recom'].includes(k)) return;
        if(clean(s.campos[k])) paragraph(k.replace(/_/g,' '), s.campos[k]);
      }); }
      const materialesTrabajoPDF = getValSafe('requiereRepuesto') === 'si'
        ? collectWorkMaterialsForPDF(s, idx, sections || [])
        : [];
      if(materialesTrabajoPDF.length){
        // Los materiales de cada trabajo siempre se dibujan antes de sus evidencias.
        repuestosTable(materialesTrabajoPDF, (clean(zgMeta.modalidad || '').toLowerCase().includes('venta') ? 'Repuestos requeridos para cotización futura en ' : 'Materiales / repuestos registrados en ')+(s.nombre || 'el trabajo'));
      }
      photos('Evidencias fotográficas del trabajo', s.photos || []);
    });

    const zgModalidadRepuesto = clean(zgMeta.modalidad || '').toLowerCase();
    if(getValSafe('requiereRepuesto') === 'si'){
      if(zgModalidadRepuesto.includes('venta')){
        paragraph('Condición comercial del repuesto', 'Se requerirá de una cotización para el repuesto indicado. El cambio se realizará en un trabajo futuro, mediante una nueva asistencia técnica, mantenimiento preventivo, mantenimiento correctivo o instalación, según corresponda.');
      }else if(zgModalidadRepuesto.includes('alquiler')){
        paragraph('Condición comercial del repuesto', 'Por tratarse de un servicio de alquiler, el repuesto indicado debe ser reemplazado. Se requiere programar o completar la intervención correspondiente.');
      }
    }

    if((data.tipoEquipo || zgMeta.tipoEquipo) === 'Genset'){
      section('Control final del generador', 78);
      kvTable([
        ['Hora de inicio del servicio',data.horaInicioServicio],['Hora de finalización del servicio',data.horaFinServicio],
        ['Estado final',data.gensetEstadoFinal],['Horómetro final',data.gensetHorometroFinal?data.gensetHorometroFinal+' h':''],['Prueba de arranque',data.gensetArranqueFinal],['Voltaje batería',data.gensetVoltajeBateriaFinal],['Voltaje salida L1-L2',data.gensetVoltajeSalidaL1L2],['Voltaje salida L2-L3',data.gensetVoltajeSalidaL2L3],['Voltaje salida L1-L3',data.gensetVoltajeSalidaL1L3],['Temperatura motor',data.gensetTemperaturaMotorFinal?data.gensetTemperaturaMotorFinal+' °C':''],['Nivel combustible',data.gensetNivelCombustibleFinal],['¿Requiere otro mantenimiento?',data.requiereOtroMantenimiento],['Tipo de mantenimiento requerido',data.requiereOtroMantenimiento==='Sí'?data.tipoOtroMantenimiento:''],['Razón del mantenimiento requerido',data.requiereOtroMantenimiento==='Sí'?data.motivoOtroMantenimiento:'']
      ].filter(r=>clean(r[1])));
    }else{
      section('Control final del equipo reefer', 68);
      kvTable([
        ['Hora de inicio del servicio',data.horaInicioServicio],['Hora de finalización del servicio',data.horaFinServicio],
        ['Estado final',data.estadoFinalEquipo],['Set point final',data.setPointFinal?data.setPointFinal+' °C':''],['Temp. ambiente final',data.tempAmbienteFinal?data.tempAmbienteFinal+' °C':''],['Presión alta final',pressureText(data.presionAltaFinal)],['Presión baja final',pressureText(data.presionBajaFinal)],['Retorno final',data.retornoFinal?data.retornoFinal+' °C':''],['Suministro final',data.suministroFinal?data.suministroFinal+' °C':''],['Voltaje final L1-L2',data.voltajeFinalL1L2],['Voltaje final L2-L3',data.voltajeFinalL2L3],['Voltaje final L1-L3',data.voltajeFinalL1L3],['¿Requiere otro mantenimiento?',data.requiereOtroMantenimiento],['Tipo de mantenimiento requerido',data.requiereOtroMantenimiento==='Sí'?data.tipoOtroMantenimiento:''],['Razón del mantenimiento requerido',data.requiereOtroMantenimiento==='Sí'?data.motivoOtroMantenimiento:'']
      ].filter(r=>clean(r[1])));
    }
    recomendacionesTecnicas();
    firmas(); footer(); return doc;
  };

  window.addEventListener('load', function(){ setupPreEvidence(); setupFirmasPro(); });
})();
</script>



<style id="zg-professional-ui-final">
/* Ajuste visual final: plataforma más limpia para uso en celular y entrega profesional */
body{background:linear-gradient(180deg,#eef4fb 0%,#f6f9fd 45%,#eef3f8 100%)!important;}
.wrap,.container,.main-wrap{max-width:1040px!important;}
.panel,.card,.soft,.step-card,.form-card{border:1px solid #d9e7f5!important;border-radius:22px!important;box-shadow:0 18px 45px rgba(16,33,58,.08)!important;background:rgba(255,255,255,.97)!important;}
.step-head,.panel-head,.card-head-line{border-bottom:1px solid #e4edf7!important;background:linear-gradient(180deg,#ffffff,#f7fbff)!important;}
input,select,textarea{border-radius:13px!important;border-color:#d5e2f0!important;background:#f8fbff!important;}
input:focus,select:focus,textarea:focus{border-color:#1f6fc4!important;box-shadow:0 0 0 4px rgba(31,111,196,.12)!important;background:#fff!important;}
button,.btn,#pdfBtn,#preBtn{border-radius:14px!important;font-weight:900!important;}
.work-card{border-radius:16px!important;background:linear-gradient(180deg,#fff,#f7fbff)!important;border-color:#dbe8f6!important;}
.work-card.on{border-color:#1f6fc4!important;background:linear-gradient(180deg,#eaf4ff,#ffffff)!important;box-shadow:0 12px 28px rgba(31,111,196,.14)!important;}
@media(max-width:720px){
  body{font-size:15px!important;}
  .wrap,.container,.main-wrap{width:100%!important;padding:10px!important;}
  .panel,.card,.soft,.step-card,.form-card{border-radius:18px!important;margin-bottom:14px!important;}
  input,select,textarea{min-height:48px!important;font-size:15px!important;}
  textarea{min-height:96px!important;}
  .bottom-bar,.sticky-actions{padding:9px 10px!important;gap:8px!important;}
  #pdfBtn,#preBtn{min-height:48px!important;font-size:15px!important;}
}
</style>


<style id="zg-firma-suave-bloqueo-trabajos-final">
/* ============================================================
   Firma más profesional + bloqueo de trabajos hasta preliminar
   ============================================================ */
.firma-box{
  position:relative!important;
  background:linear-gradient(180deg,#ffffff,#f8fbff)!important;
  border:1.5px solid #cfe0f3!important;
  border-radius:20px!important;
  padding:14px!important;
}
.firma-title{margin-bottom:8px!important;}
.firma-title b{font-size:15px!important;color:#10213a!important;}
.firma-title span{font-size:12px!important;color:#5f7189!important;}
.firma-helper-zg{
  background:#f2f7fd!important;
  border:1px solid #d6e6f8!important;
  color:#405a76!important;
  border-radius:13px!important;
  padding:8px 10px!important;
  margin:6px 0 10px!important;
  font-size:12.5px!important;
  font-weight:800!important;
}
.firma-canvas{
  height:205px!important;
  border:2px solid #18345c!important;
  border-radius:18px!important;
  background-color:#ffffff!important;
  background-image:
    linear-gradient(to bottom, transparent 72%, rgba(24,52,92,.34) 72%, rgba(24,52,92,.34) calc(72% + 1.5px), transparent calc(72% + 1.5px)),
    radial-gradient(circle at 50% 40%, rgba(31,111,196,.045), transparent 38%)!important;
  box-shadow:inset 0 0 0 1px rgba(255,255,255,.9),0 12px 26px rgba(16,33,58,.08)!important;
  cursor:crosshair!important;
  touch-action:none!important;
  user-select:none!important;
}
.firma-box::after{
  content:'Firme despacio sobre la línea'!important;
  position:absolute!important;
  left:24px!important;
  right:24px!important;
  top:58%!important;
  transform:translateY(-50%)!important;
  text-align:center!important;
  color:#9aacbf!important;
  font-size:13px!important;
  font-weight:900!important;
  pointer-events:none!important;
}
.firma-box.firmado::after{display:none!important;}
.firma-clear{
  background:#edf4fb!important;
  border:1px solid #cfe0f3!important;
  color:#17385d!important;
  border-radius:999px!important;
  min-height:38px!important;
}
@media(max-width:720px){
  .firma-canvas{height:235px!important;}
  .firma-helper-zg{font-size:12.8px!important;line-height:1.3!important;}
}

/* Bloqueo visual de la sección 2 */
.zg-work-locked{
  position:relative!important;
  opacity:.74!important;
  filter:grayscale(.08)!important;
}
.zg-work-locked input,
.zg-work-locked select,
.zg-work-locked textarea,
.zg-work-locked button{
  cursor:not-allowed!important;
}
.zg-work-locked .work-grid,
.zg-work-locked #panels,
.zg-work-locked #repuestoQuestionCard,
.zg-work-locked #repuestosCard,
.zg-work-locked #finalControlCard,
.zg-work-locked #firmasCard{
  pointer-events:none!important;
}
.zg-work-locked .work-card,
.zg-work-locked .repuesto-question-card,
.zg-work-locked .repuestos-card,
.zg-work-locked .final-control-card,
.zg-work-locked .firmas-card{
  background:#f4f8fc!important;
  border-color:#d8e5f3!important;
}
</style>

<script id="zg-firma-suave-y-bloqueo-trabajos-js">
(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(s){ return String(s||'').replace(/\s+/g,' ').trim(); }

  function mejorarTextoAyudaFirmas(){
    document.querySelectorAll('.firma-helper-zg').forEach(function(x){
      x.textContent = 'Para una firma más limpia, firme despacio con el dedo o mouse. Puede limpiar y volver a firmar si no queda bien.';
    });
  }

  function prepararCanvasFirma(canvasId, hiddenId, clearId){
    const oldCanvas = byId(canvasId);
    const hidden = byId(hiddenId);
    const oldClear = byId(clearId);
    if(!oldCanvas || !hidden) return;

    // Reemplaza canvas y botón para eliminar manejadores anteriores y evitar trazos dobles.
    const canvas = oldCanvas.cloneNode(true);
    oldCanvas.parentNode.replaceChild(canvas, oldCanvas);
    let clear = oldClear;
    if(oldClear){
      clear = oldClear.cloneNode(true);
      oldClear.parentNode.replaceChild(clear, oldClear);
    }

    const box = canvas.closest('.firma-box');
    const ctx = canvas.getContext('2d');
    let drawing = false;
    let points = [];
    let savedImage = hidden.value || '';

    function resize(){
      const rect = canvas.getBoundingClientRect();
      if(rect.width < 10 || rect.height < 10) return;
      const ratio = window.devicePixelRatio || 1;
      canvas.width = Math.round(rect.width * ratio);
      canvas.height = Math.round(rect.height * ratio);
      ctx.setTransform(ratio,0,0,ratio,0,0);
      ctx.clearRect(0,0,rect.width,rect.height);
      ctx.strokeStyle = '#0b1f38';
      ctx.lineWidth = 2.35;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.imageSmoothingEnabled = true;
      if(savedImage){
        const img = new Image();
        img.onload = function(){
          try{ ctx.drawImage(img,0,0,rect.width,rect.height); if(box) box.classList.add('firmado'); }catch(e){}
        };
        img.src = savedImage;
      }
    }

    function getPoint(ev){
      const r = canvas.getBoundingClientRect();
      return {x: ev.clientX - r.left, y: ev.clientY - r.top};
    }
    function save(){
      try{ hidden.value = canvas.toDataURL('image/png'); savedImage = hidden.value; if(box) box.classList.add('firmado'); }catch(e){}
      if(window.zgroupMarcarCambio) window.zgroupMarcarCambio();
    }
    function drawSmooth(){
      if(points.length < 2) return;
      ctx.beginPath();
      const p0 = points[0];
      ctx.moveTo(p0.x, p0.y);
      for(let i=1;i<points.length-2;i++){
        const xc = (points[i].x + points[i+1].x) / 2;
        const yc = (points[i].y + points[i+1].y) / 2;
        ctx.quadraticCurveTo(points[i].x, points[i].y, xc, yc);
      }
      const n = points.length;
      if(n > 2){
        ctx.quadraticCurveTo(points[n-2].x, points[n-2].y, points[n-1].x, points[n-1].y);
      } else {
        ctx.lineTo(points[n-1].x, points[n-1].y);
      }
      ctx.stroke();
    }
    function start(ev){
      ev.preventDefault();
      canvas.setPointerCapture && canvas.setPointerCapture(ev.pointerId);
      drawing = true;
      points = [getPoint(ev)];
    }
    function move(ev){
      if(!drawing) return;
      ev.preventDefault();
      points.push(getPoint(ev));
      if(points.length > 4){
        const keep = points.slice(-4);
        drawSmooth();
        points = keep;
      } else {
        drawSmooth();
      }
    }
    function end(ev){
      if(!drawing) return;
      ev.preventDefault();
      drawing = false;
      drawSmooth();
      points = [];
      save();
    }
    function clearFirma(){
      const rect = canvas.getBoundingClientRect();
      ctx.clearRect(0,0,rect.width,rect.height);
      hidden.value = '';
      savedImage = '';
      if(box) box.classList.remove('firmado');
      if(window.zgroupMarcarCambio) window.zgroupMarcarCambio();
    }

    canvas.addEventListener('pointerdown', start, {passive:false});
    canvas.addEventListener('pointermove', move, {passive:false});
    canvas.addEventListener('pointerup', end, {passive:false});
    canvas.addEventListener('pointercancel', end, {passive:false});
    if(clear) clear.addEventListener('click', clearFirma);
    setTimeout(resize, 120);
    setTimeout(resize, 650);
    window.addEventListener('resize', function(){ setTimeout(resize, 180); });
  }

  function setupFirmasSuaves(){
    mejorarTextoAyudaFirmas();
    prepararCanvasFirma('firmaTecnicoCanvas','firmaTecnico','limpiarFirmaTecnico');
    prepararCanvasFirma('firmaAdminCanvas','firmaAdmin','limpiarFirmaAdmin');
  }

  function tienePreliminarGuardada(){
    const pre = byId('preinspeccionId');
    const token = new URLSearchParams(location.search).get('token');
    return !!(clean(pre && pre.value) || clean(token));
  }

  function getWorkSection(){
    const grid = byId('workGrid');
    return grid ? grid.closest('section.card') : null;
  }

  function asegurarNotaBloqueo(section){
    // Sin aviso visual. La sección se mantiene congelada hasta guardar la preliminar.
    if(!section) return;
  }

  function bloquearControles(section, bloquear){
    if(!section) return;
    section.classList.toggle('zg-work-locked', !!bloquear);
    section.querySelectorAll('input, select, textarea, button').forEach(function(el){
      // Los ocultos pueden conservar su valor, pero igual se evita interacción visible.
      el.disabled = !!bloquear;
    });
  }

  function aplicarBloqueoTrabajos(){
    const section = getWorkSection();
    if(!section) return;
    asegurarNotaBloqueo(section);
    bloquearControles(section, !tienePreliminarGuardada());
  }

  function observarPreliminar(){
    aplicarBloqueoTrabajos();
    setTimeout(aplicarBloqueoTrabajos, 400);
    setTimeout(aplicarBloqueoTrabajos, 1200);
    setInterval(aplicarBloqueoTrabajos, 1500);
    const preBtn = byId('preBtn');
    if(preBtn){
      preBtn.addEventListener('click', function(){ setTimeout(aplicarBloqueoTrabajos, 900); setTimeout(aplicarBloqueoTrabajos, 1800); }, true);
    }
  }

  window.addEventListener('load', function(){
    setTimeout(setupFirmasSuaves, 450);
    setTimeout(observarPreliminar, 550);
  });
})();
</script>


<style id="zg-firma-modal-cargo-css">
/* Firma grande profesional */
.firma-box{border:1.5px solid #cfddec!important;border-radius:18px!important;background:#fff!important;box-shadow:0 10px 24px rgba(16,33,58,.06)!important;position:relative!important}
.firma-canvas{border:1.5px solid #12345a!important;border-radius:16px!important;background:#fff!important;touch-action:none!important;pointer-events:none!important;cursor:default!important}
.firma-box::after{content:''!important;display:none!important}
.firma-actions{gap:8px!important;flex-wrap:wrap!important;justify-content:space-between!important;align-items:center!important}
.firma-open-big{border:none;background:#1f6fc4;color:#fff;border-radius:999px;padding:9px 13px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer;box-shadow:0 8px 18px rgba(31,111,196,.20)}
.firma-open-big:hover{filter:brightness(.98);transform:translateY(-1px)}
.firma-status{font-size:12px;font-weight:900;color:#56708c;background:#edf5ff;border:1px solid #cfe1f5;border-radius:999px;padding:7px 10px}
.firma-status.ok{color:#087f3d;background:#eafaf0;border-color:#b7ebc6}
.zg-sign-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;background:rgba(8,20,38,.72);padding:14px;backdrop-filter:blur(8px)}
.zg-sign-modal.show{display:flex}
.zg-sign-box{width:min(980px,100%);height:min(82vh,680px);background:#fff;border-radius:24px;box-shadow:0 30px 90px rgba(0,0,0,.38);overflow:hidden;display:flex;flex-direction:column;border:1px solid #d8e4f2}
.zg-sign-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:16px 18px;background:linear-gradient(135deg,#10213a,#1f6fc4);color:#fff}
.zg-sign-head h3{margin:0;font-family:'Archivo',system-ui,sans-serif;font-size:22px;letter-spacing:-.02em}.zg-sign-head p{margin:4px 0 0;color:#dcecff;font-size:13px;font-weight:800}.zg-sign-close{border:none;background:rgba(255,255,255,.18);color:#fff;border-radius:12px;width:42px;height:42px;font-size:20px;font-weight:900;cursor:pointer}
.zg-sign-body{flex:1;padding:16px;background:#f4f8fd;display:flex;flex-direction:column;gap:10px;min-height:0}.zg-sign-area{position:relative;flex:1;min-height:300px;background:#fff;border:2px solid #10213a;border-radius:18px;overflow:hidden;box-shadow:inset 0 0 0 1px #eef4fb}.zg-sign-area canvas{width:100%;height:100%;display:block;touch-action:none;background:#fff}.zg-sign-line{position:absolute;left:7%;right:7%;bottom:22%;height:2px;background:#10213a;opacity:.65;pointer-events:none}.zg-sign-help{position:absolute;left:0;right:0;bottom:24%;text-align:center;color:#8296ad;font-weight:900;font-size:14px;pointer-events:none}.zg-sign-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}.zg-sign-tip{font-size:12.5px;color:#60738a;font-weight:800}.zg-sign-actions{display:flex;gap:10px;flex-wrap:wrap}.zg-sign-btn{border:none;border-radius:14px;padding:12px 16px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}.zg-sign-btn.clear{background:#eaf1f8;color:#17385d}.zg-sign-btn.cancel{background:#f1f5f9;color:#334155}.zg-sign-btn.save{background:#1f6fc4;color:#fff;box-shadow:0 9px 20px rgba(31,111,196,.25)}
@media(max-width:700px){.zg-sign-box{height:88vh;border-radius:20px}.zg-sign-head h3{font-size:18px}.zg-sign-area{min-height:360px}.zg-sign-footer{align-items:stretch}.zg-sign-actions{width:100%;display:grid;grid-template-columns:1fr 1fr}.zg-sign-btn.save{grid-column:1/-1}.firma-actions{justify-content:flex-start!important}.firma-open-big,.firma-clear{min-height:42px}}
</style>
<script id="zg-firma-modal-cargo-js">
(function(){
  function byId(id){return document.getElementById(id)}
  function clean(s){return String(s||'').trim()}
  function ensureCargoField(){
    if(byId('adminTiendaCargo')) return;
    const nombre=byId('adminTiendaNombre');
    if(!nombre) return;
    const wrap=nombre.closest('.field');
    if(!wrap) return;
    const div=document.createElement('div');
    div.className='field full';
    div.innerHTML='<label for="adminTiendaCargo">Cargo del responsable del cliente</label><input type="text" id="adminTiendaCargo" placeholder="Ej. Administrador de tienda / Supervisor / Encargado" autocomplete="off"><div class="field-hint">El cargo aparecerá debajo del nombre en el informe.</div>';
    wrap.insertAdjacentElement('afterend', div);
  }
  function ensureModal(){
    let modal=byId('zgSignModal');
    if(modal) return modal;
    modal=document.createElement('div');
    modal.id='zgSignModal';
    modal.className='zg-sign-modal';
    modal.innerHTML='<div class="zg-sign-box"><div class="zg-sign-head"><div><h3 id="zgSignTitle">Firma digital</h3><p id="zgSignSub">Firme despacio con el dedo o mouse.</p></div><button type="button" class="zg-sign-close" id="zgSignClose">×</button></div><div class="zg-sign-body"><div class="zg-sign-area"><canvas id="zgSignCanvas"></canvas><div class="zg-sign-line"></div><div class="zg-sign-help">Firme sobre la línea</div></div><div class="zg-sign-footer"><div class="zg-sign-tip">Para mejor resultado: firme lento, usando todo el espacio disponible.</div><div class="zg-sign-actions"><button type="button" class="zg-sign-btn cancel" id="zgSignCancel">Cancelar</button><button type="button" class="zg-sign-btn clear" id="zgSignClear">Limpiar</button><button type="button" class="zg-sign-btn save" id="zgSignSave">Guardar firma</button></div></div></div></div>';
    document.body.appendChild(modal);
    return modal;
  }
  let current={canvasId:'',hiddenId:'',title:'',sub:''};
  let modalCanvas, mctx, drawing=false, points=[], hasInk=false;
  function sizeModalCanvas(){
    modalCanvas=byId('zgSignCanvas');
    if(!modalCanvas) return;
    const r=modalCanvas.getBoundingClientRect();
    const dpr=Math.max(1, window.devicePixelRatio||1);
    const old=current.hiddenId && byId(current.hiddenId) ? byId(current.hiddenId).value : '';
    modalCanvas.width=Math.max(1, Math.floor(r.width*dpr));
    modalCanvas.height=Math.max(1, Math.floor(r.height*dpr));
    mctx=modalCanvas.getContext('2d');
    mctx.setTransform(dpr,0,0,dpr,0,0);
    mctx.clearRect(0,0,r.width,r.height);
    mctx.fillStyle='#fff'; mctx.fillRect(0,0,r.width,r.height);
    mctx.lineCap='round'; mctx.lineJoin='round'; mctx.strokeStyle='#0f2139'; mctx.lineWidth=2.8; mctx.imageSmoothingEnabled=true;
    if(old && /^data:image\//.test(old)){
      const img=new Image();
      img.onload=function(){ mctx.drawImage(img,0,0,r.width,r.height); hasInk=true; };
      img.src=old;
    } else { hasInk=false; }
  }
  function p(ev){ const r=modalCanvas.getBoundingClientRect(); return {x:ev.clientX-r.left,y:ev.clientY-r.top}; }
  function draw(){
    if(points.length<2) return;
    mctx.beginPath();
    mctx.moveTo(points[0].x, points[0].y);
    for(let i=1;i<points.length-2;i++){
      const xc=(points[i].x+points[i+1].x)/2, yc=(points[i].y+points[i+1].y)/2;
      mctx.quadraticCurveTo(points[i].x, points[i].y, xc, yc);
    }
    const n=points.length;
    if(n>2) mctx.quadraticCurveTo(points[n-2].x, points[n-2].y, points[n-1].x, points[n-1].y);
    else mctx.lineTo(points[n-1].x, points[n-1].y);
    mctx.stroke();
  }
  function start(ev){ ev.preventDefault(); drawing=true; points=[p(ev)]; modalCanvas.setPointerCapture&&modalCanvas.setPointerCapture(ev.pointerId); }
  function move(ev){ if(!drawing) return; ev.preventDefault(); points.push(p(ev)); draw(); if(points.length>6) points=points.slice(-4); }
  function end(ev){ if(!drawing) return; ev.preventDefault(); drawing=false; draw(); points=[]; hasInk=true; }
  function renderPreview(canvasId, dataUrl){
    const c=byId(canvasId); if(!c) return;
    const r=c.getBoundingClientRect();
    if(r.width<10 || r.height<10){ setTimeout(function(){renderPreview(canvasId,dataUrl)},120); return; }
    const dpr=Math.max(1, window.devicePixelRatio||1);
    c.width=Math.floor(r.width*dpr); c.height=Math.floor(r.height*dpr);
    const ctx=c.getContext('2d'); ctx.setTransform(dpr,0,0,dpr,0,0); ctx.clearRect(0,0,r.width,r.height); ctx.fillStyle='#fff'; ctx.fillRect(0,0,r.width,r.height);
    ctx.strokeStyle='#10213a'; ctx.lineWidth=1.3; ctx.beginPath(); ctx.moveTo(18,r.height-36); ctx.lineTo(r.width-18,r.height-36); ctx.stroke();
    if(dataUrl && /^data:image\//.test(dataUrl)){ const img=new Image(); img.onload=function(){ ctx.drawImage(img,0,0,r.width,r.height); }; img.src=dataUrl; }
  }
  function updateStatuses(){
    [['firmaTecnico','firmaTecnicoCanvas','firmaTecnicoStatus'],['firmaAdmin','firmaAdminCanvas','firmaAdminStatus']].forEach(function(a){
      const h=byId(a[0]), s=byId(a[2]); if(!h||!s) return;
      const ok=!!clean(h.value); s.textContent=ok?'Firma registrada':'Pendiente de firma'; s.classList.toggle('ok',ok); renderPreview(a[1], h.value);
    });
  }
  function openSigner(canvasId, hiddenId, title, sub){
    current={canvasId,hiddenId,title,sub};
    const modal=ensureModal();
    byId('zgSignTitle').textContent=title;
    byId('zgSignSub').textContent=sub||'Firme despacio con el dedo o mouse.';
    modal.classList.add('show');
    setTimeout(sizeModalCanvas,80);
    setTimeout(sizeModalCanvas,280);
  }
  function wireModal(){
    ensureModal(); modalCanvas=byId('zgSignCanvas');
    modalCanvas.addEventListener('pointerdown',start,{passive:false});
    modalCanvas.addEventListener('pointermove',move,{passive:false});
    modalCanvas.addEventListener('pointerup',end,{passive:false});
    modalCanvas.addEventListener('pointercancel',end,{passive:false});
    byId('zgSignClose').onclick=byId('zgSignCancel').onclick=function(){ byId('zgSignModal').classList.remove('show'); };
    byId('zgSignClear').onclick=function(){ sizeModalCanvas(); const h=byId(current.hiddenId); if(h) h.value=''; hasInk=false; };
    byId('zgSignSave').onclick=function(){
      if(!hasInk){ alert('Primero dibuja la firma.'); return; }
      const h=byId(current.hiddenId); if(h){ h.value=modalCanvas.toDataURL('image/png'); h.dispatchEvent(new Event('change',{bubbles:true})); }
      byId('zgSignModal').classList.remove('show'); updateStatuses();
      if(window.zgroupMarcarCambio) window.zgroupMarcarCambio();
    };
    window.addEventListener('resize',function(){ if(byId('zgSignModal')&&byId('zgSignModal').classList.contains('show')) setTimeout(sizeModalCanvas,120); setTimeout(updateStatuses,150); });
  }
  function improveCards(){
    const tec=byId('firmaTecnicoCanvas'), adm=byId('firmaAdminCanvas');
    if(tec && !byId('abrirFirmaTecnico')){
      const actions=byId('limpiarFirmaTecnico')&&byId('limpiarFirmaTecnico').closest('.firma-actions');
      if(actions){
        actions.insertAdjacentHTML('afterbegin','<span class="firma-status" id="firmaTecnicoStatus">Pendiente de firma</span><button type="button" class="firma-open-big" id="abrirFirmaTecnico">Firmar en grande</button>');
        byId('abrirFirmaTecnico').onclick=function(){openSigner('firmaTecnicoCanvas','firmaTecnico','Firma del técnico','Use el dedo para registrar la firma del técnico responsable.');};
        const clear=byId('limpiarFirmaTecnico'); if(clear) clear.addEventListener('click',function(){ setTimeout(updateStatuses,70); });
      }
    }
    if(adm && !byId('abrirFirmaAdmin')){
      const actions=byId('limpiarFirmaAdmin')&&byId('limpiarFirmaAdmin').closest('.firma-actions');
      if(actions){
        actions.insertAdjacentHTML('afterbegin','<span class="firma-status" id="firmaAdminStatus">Pendiente de firma</span><button type="button" class="firma-open-big" id="abrirFirmaAdmin">Firmar en grande</button>');
        byId('abrirFirmaAdmin').onclick=function(){openSigner('firmaAdminCanvas','firmaAdmin','Firma del responsable del cliente','Use el dedo para registrar la firma del responsable o administrador de la tienda.');};
        const clear=byId('limpiarFirmaAdmin'); if(clear) clear.addEventListener('click',function(){ setTimeout(updateStatuses,70); });
      }
    }
    updateStatuses(); setTimeout(updateStatuses,500);
  }
  window.addEventListener('load',function(){ ensureCargoField(); wireModal(); setTimeout(improveCards,500); setTimeout(improveCards,1300); });
})();
</script>


<style id="zg-salidas-supervision-css">
/* Preparación de salida técnica asignada por supervisor */
.salida-supervision-card{border:1.5px solid #cfe0f5!important;background:linear-gradient(180deg,#ffffff,#f7fbff)!important;border-radius:24px!important;box-shadow:0 16px 42px rgba(16,33,58,.09)!important;overflow:hidden!important;margin-top:18px!important}
.salida-supervision-card .salida-head{display:flex;gap:14px;align-items:flex-start;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #dde8f6;background:linear-gradient(135deg,#10213a,#1f6fc4);color:white}
.salida-supervision-card .salida-title{display:flex;gap:12px;align-items:flex-start}.salida-ico{width:42px;height:42px;border-radius:14px;background:rgba(255,255,255,.14);display:grid;place-items:center;font-size:22px;border:1px solid rgba(255,255,255,.24)}
.salida-supervision-card h3{font-family:'Archivo',system-ui,sans-serif;font-size:19px;margin:0 0 4px;color:#fff;letter-spacing:-.02em}.salida-supervision-card p{margin:0;color:#dcecff;font-weight:750;font-size:13px;line-height:1.35}.salida-pill{background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;padding:7px 10px;border-radius:999px;font-family:'Archivo';font-size:12px;font-weight:900;white-space:nowrap}
.salida-body{padding:18px 20px}.salida-empty{border:1.4px dashed #cfe0f5;background:#f8fbff;color:#66748a;border-radius:18px;padding:16px 18px;font-weight:800;line-height:1.4}.salida-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px}.salida-info{border:1px solid #dbe6f3;background:#fff;border-radius:16px;padding:12px}.salida-info span{display:block;color:#697891;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px}.salida-info b{display:block;color:#10213a;font-size:14px;line-height:1.25}.salida-apoyo{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.salida-chip{display:inline-flex;align-items:center;background:#e8f2ff;color:#13579b;border:1px solid #cbe1fa;border-radius:999px;padding:6px 10px;font-weight:900;font-size:12px}.salida-table-wrap{border:1px solid #d6e3f3;border-radius:18px;overflow:hidden;background:#fff;margin-top:12px}.salida-table{width:100%;border-collapse:collapse;font-size:13px}.salida-table th{background:#10213a;color:#fff;text-align:left;padding:10px 12px;font-family:'Archivo';font-size:12px;letter-spacing:.04em;text-transform:uppercase}.salida-table td{border-top:1px solid #edf3fa;padding:10px 12px;color:#10213a;font-weight:750;vertical-align:top}.salida-table tr:nth-child(even) td{background:#f8fbff}.salida-table .code{color:#1f6fc4;font-weight:950}.salida-note{margin-top:12px;background:#fff8e6;border:1px solid #ffe1a6;color:#745300;border-radius:16px;padding:11px 13px;font-weight:850;font-size:12.5px}
@media(max-width:760px){.salida-supervision-card .salida-head{flex-direction:column;padding:16px}.salida-body{padding:15px}.salida-grid{grid-template-columns:1fr}.salida-table{min-width:620px}.salida-table-wrap{overflow-x:auto}.salida-pill{align-self:flex-start}}
</style>

<script id="zg-salidas-supervision-js">
const SALIDAS_TECNICAS_SUPERVISION = <?= json_encode($salidasSupervision, JSON_UNESCAPED_UNICODE) ?>;
(function(){
  function byId(id){ return document.getElementById(id); }
  function norm(v){ return String(v || '').replace(/\D+/g,'').trim(); }
  function esc(v){ return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
  function salidaActual(){
    const cot = norm(byId('orden') && byId('orden').value);
    if(!cot) return null;
    return (SALIDAS_TECNICAS_SUPERVISION || []).find(s => norm(s.cotizacion) === cot) || null;
  }
  window.getSalidaAsignadaActual = salidaActual;

  function ensureCard(){
    if(byId('salidaSupervisionCard')) return byId('salidaSupervisionCard');
    const datos = byId('datosGeneralesCard');
    if(!datos) return null;
    const card = document.createElement('section');
    card.className = 'salida-supervision-card';
    card.id = 'salidaSupervisionCard';
    card.innerHTML = `
      <div class="salida-head">
        <div class="salida-title"><div class="salida-ico">📦</div><div><h3>Preparación de salida técnica</h3><p>Materiales, repuestos y técnicos de apoyo asignados por supervisión antes del servicio.</p></div></div>
        <div class="salida-pill" id="salidaSupervisionEstado">Sin asignación</div>
      </div>
      <div class="salida-body" id="salidaSupervisionBody">
        <div class="salida-empty">Cuando el supervisor prepare materiales para este N° de reporte, aquí aparecerá el detalle. El técnico no edita esta parte; solo verifica lo asignado.</div>
      </div>`;
    datos.insertAdjacentElement('afterend', card);
    return card;
  }
  function materialRows(mats){
    if(!mats || !mats.length) return '<div class="salida-empty">No se registraron materiales preparados para esta salida.</div>';
    return `<div class="salida-table-wrap"><table class="salida-table"><thead><tr><th>Código</th><th>Material / repuesto</th><th>Cant.</th><th>Unidad</th><th>Obs.</th></tr></thead><tbody>${mats.map(m => `
      <tr><td class="code">${esc(m.codigo || '-')}</td><td>${esc(m.detalle || '-')}</td><td>${esc(m.cantidad || '1')}</td><td>${esc(m.unidad || 'und')}</td><td>${esc(m.observacion || '-')}</td></tr>`).join('')}</tbody></table></div>`;
  }
  function renderSalida(){
    const card = ensureCard(); if(!card) return;
    const body = byId('salidaSupervisionBody'); const estado = byId('salidaSupervisionEstado');
    const s = salidaActual();
    if(!s){
      if(estado) estado.textContent = 'Sin asignación';
      body.innerHTML = '<div class="salida-empty">Aún no hay preparación registrada para este N° de reporte. Cuando el supervisor asigne materiales, aparecerán aquí automáticamente.</div>';
      return;
    }
    if(estado) estado.textContent = 'Asignado por supervisión';
    const apoyo = Array.isArray(s.tecnicos_apoyo) ? s.tecnicos_apoyo : [];
    body.innerHTML = `
      <div class="salida-grid">
        <div class="salida-info"><span>N° de reporte</span><b>${esc(s.cotizacion || '-')}</b></div>
        <div class="salida-info"><span>Técnico responsable</span><b>${esc(s.tecnico_responsable || 'No definido')}</b></div>
        <div class="salida-info"><span>Equipo / unidad</span><b>${esc(s.equipo || byId('equipoNo')?.value || '-')}</b></div>
      </div>
      <div class="salida-info"><span>Técnicos de apoyo</span><div class="salida-apoyo">${apoyo.length ? apoyo.map(t => `<span class="salida-chip">👷 ${esc(t.nombre || t)}</span>`).join('') : '<span class="salida-chip">Sin apoyo asignado</span>'}</div></div>
      ${materialRows(s.materiales || [])}
      ${s.observacion ? `<div class="salida-note"><b>Nota de supervisión:</b> ${esc(s.observacion)}</div>` : ''}`;
  }
  window.renderSalidaSupervision = renderSalida;
  window.addEventListener('load', function(){ renderSalida(); setTimeout(renderSalida,600); setTimeout(renderSalida,1400); });
  document.addEventListener('input', function(e){ if(e.target && (e.target.id === 'orden' || e.target.id === 'equipoNo')) renderSalida(); }, true);
  document.addEventListener('change', function(e){ if(e.target && (e.target.id === 'orden' || e.target.id === 'equipoNo')) renderSalida(); }, true);

  function addSalidaToPDF(doc, salida){
    if(!doc || !salida) return;
    const PW=210, PH=297, M=16, CW=PW-2*M;
    doc.addPage();
    let y=18;
    doc.setFillColor(16,33,58); doc.rect(0,0,PW,30,'F');
    doc.setTextColor(255,255,255); doc.setFont('helvetica','bold'); doc.setFontSize(15);
    doc.text('PREPARACIÓN DE SALIDA TÉCNICA', M, 18);
    doc.setTextColor(218,232,251); doc.setFont('helvetica','normal'); doc.setFontSize(9);
    doc.text('Materiales asignados por supervisión antes del servicio', M, 24);
    y=42;
    function box(x,w,t,v){ doc.setDrawColor(210,222,238); doc.setFillColor(248,251,255); doc.roundedRect(x,y,w,18,2,2,'FD'); doc.setFont('helvetica','bold'); doc.setFontSize(7.5); doc.setTextColor(90,107,128); doc.text(t,x+3,y+6); doc.setFont('helvetica','bold'); doc.setFontSize(9.5); doc.setTextColor(16,33,58); doc.text(doc.splitTextToSize(String(v||'-'),w-6),x+3,y+12); }
    box(M,58,'N° REPORTE',salida.cotizacion); box(M+62,58,'TÉCNICO RESPONSABLE',salida.tecnico_responsable||'-'); box(M+124,54,'EQUIPO / UNIDAD',salida.equipo||'-'); y+=25;
    const apoyo = Array.isArray(salida.tecnicos_apoyo) ? salida.tecnicos_apoyo.map(t=>t.nombre||t).join(', ') : '';
    box(M,CW,'TÉCNICOS DE APOYO',apoyo || 'Sin apoyo asignado'); y+=27;
    doc.setFont('helvetica','bold'); doc.setFontSize(11); doc.setTextColor(31,111,196); doc.text('MATERIALES Y REPUESTOS ASIGNADOS',M,y); y+=6;
    const widths=[34,82,18,26, CW-34-82-18-26]; const headers=['CÓDIGO','DETALLE','CANT.','UNIDAD','OBS.']; let x=M;
    headers.forEach((h,i)=>{ doc.setFillColor(16,33,58); doc.setDrawColor(210,222,238); doc.rect(x,y,widths[i],8,'FD'); doc.setTextColor(255,255,255); doc.setFont('helvetica','bold'); doc.setFontSize(7.2); doc.text(h,x+2,y+5.2); x+=widths[i]; }); y+=8;
    (salida.materiales||[]).forEach((m,idx)=>{
      const det=doc.splitTextToSize(String(m.detalle||'-'),widths[1]-4); const obs=doc.splitTextToSize(String(m.observacion||'-'),widths[4]-4);
      const h=Math.max(9, det.length*4.2+4, obs.length*4.2+4); if(y+h>280){ doc.addPage(); y=18; }
      x=M; const vals=[m.codigo||'-', det, m.cantidad||'1', m.unidad||'und', obs];
      vals.forEach((v,i)=>{ doc.setFillColor(idx%2?248:255, idx%2?251:255, idx%2?255:255); doc.setDrawColor(222,232,242); doc.rect(x,y,widths[i],h,'FD'); doc.setTextColor(16,33,58); doc.setFont('helvetica', i===0?'bold':'normal'); doc.setFontSize(8); if(Array.isArray(v)) doc.text(v,x+2,y+5); else doc.text(String(v),x+2,y+5); x+=widths[i]; });
      y+=h;
    });
    if(salida.observacion){ y+=6; doc.setFont('helvetica','bold'); doc.setTextColor(90,107,128); doc.setFontSize(8); doc.text('NOTA DE SUPERVISIÓN',M,y); y+=5; doc.setFont('helvetica','normal'); doc.setTextColor(16,33,58); doc.setFontSize(9); doc.text(doc.splitTextToSize(String(salida.observacion),CW),M,y); }
  }
  try{
    const oldBuildPDF = window.buildPDF || buildPDF;
    buildPDF = function(sections){
      const doc = oldBuildPDF(sections);
      const s = salidaActual();
      // La salida técnica ya se dibuja dentro del PDF, antes del control final.
      // Evitamos duplicarla como página final.
      if(false && s) addSalidaToPDF(doc, s);
      return doc;
    };
  }catch(e){ console.warn('No se pudo acoplar salidas al PDF', e); }
})();
</script>


<style id="zg-reporte-presiones-layout-fix">
/* Ajuste final solicitado: reporte, presiones y PDF más ordenado */
#presionAlta,#presionBaja,#presionAltaFinal,#presionBajaFinal{font-weight:400!important;}
@media(max-width:720px){
  #presionAlta,#presionBaja,#presionAltaFinal,#presionBajaFinal{min-height:48px!important;}
}
</style>
<script id="zg-reporte-presiones-ui-fix">
(function(){
  function byId(id){return document.getElementById(id);}
  function setTextContains(sel, from, to){
    document.querySelectorAll(sel).forEach(function(el){
      if(el && el.childNodes && el.childNodes.length===1 && el.textContent.indexOf(from)>=0){ el.textContent = el.textContent.replace(from,to); }
    });
  }
  function fixLabels(){
    const orden = document.querySelector('label[for="orden"]');
    if(orden) orden.textContent = 'N° de reporte';
  }
  window.addEventListener('load', function(){ setTimeout(fixLabels, 120); setTimeout(fixLabels, 900); });
})();
</script>

<!-- ZGROUP final: catálogo por controlador, unidades, redacción y evidencias -->
<script id="zg-catalogo-controlador-only-js-final">
(function(){
  const ZG_CATALOGOS_POR_CONTROLADOR = {"STAR COOL CIM 6":[{"codigo":"818770B","detalle":"2 PIN CONNECTOR (3.81mm/90°) (5 pcs)"},{"codigo":"818270C","detalle":"AIR EXCHANGE MODULE (75CMH)"},{"codigo":"818522F","detalle":"AUXILIARY CONTACT (WHITE DOT 10PCS)"},{"codigo":"811537D","detalle":"BRACKET, EVAPORATOR FAN MOTOR"},{"codigo":"818329A","detalle":"BUTT SPLICE"},{"codigo":"818202B","detalle":"CABLE ADAPTER KIT, FAN MOTOR (10 pcs)"},{"codigo":"815505D","detalle":"CABLE ROOM COVER"},{"codigo":"818561B","detalle":"CABLE SET (X1, X2, X3), CIM 5"},{"codigo":"814247C","detalle":"CABLE, FC (1.0 AND 1.1) TO COMPRESSOR"},{"codigo":"819526B","detalle":"COIL CONDENSER"},{"codigo":"818658B","detalle":"COMPRESSOR"},{"codigo":"818760B","detalle":"CONNECTOR PLUG, SOLENOID COIL (5PCS)"},{"codigo":"818521B","detalle":"CONTACTOR"},{"codigo":"818310C","detalle":"CONTROLLER DOOR, CIM 6"},{"codigo":"868510D","detalle":"CONTROLLER MODULE, CIM 6.0"},{"codigo":"818925A","detalle":"CONTROLLER MODULE, USB CIM 6.2"},{"codigo":"818510E","detalle":"CONTROLLER MODULE, USB CIM 6.2 REMAN"},{"codigo":"815209B","detalle":"COVER PLATE (1715MM), CONDENSER"},{"codigo":"818250E","detalle":"DAMPER, AIR EXCHANGE MODULE"},{"codigo":"881523A","detalle":"DEFROST HEATER, EVAPORATOR (25PCS)"},{"codigo":"814667F","detalle":"ECONOMIZER"},{"codigo":"819737D","detalle":"ECONOMIZER VALVE, R134A"},{"codigo":"881527A","detalle":"EVAPORATOR COIL"},{"codigo":"819543B","detalle":"FAN BLADE, CONDENSER"},{"codigo":"819542C","detalle":"FAN BLADE, EVAPORATOR"},{"codigo":"818965B","detalle":"FREQUENCY CONVERTER 2.1"},{"codigo":"818274C","detalle":"FRONT PART, AIR EXCHANGE MODULE (75 CMH)"},{"codigo":"818530A","detalle":"FUSE 10A"},{"codigo":"818534A","detalle":"FUSE HOLDER 0.4A"},{"codigo":"818656B","detalle":"GASKET, COMPRESSOR STOP VALVE"},{"codigo":"818661B","detalle":"GASKET, SERVICE VALVE LP"},{"codigo":"819501A","detalle":"HIGH PRESSURE SWITCH"},{"codigo":"814644C","detalle":"HINGE PIN"},{"codigo":"889740C","detalle":"HOT GAS VALVE"},{"codigo":"818537A","detalle":"HUMIDITY SENSOR, CIM 6"},{"codigo":"818523C","detalle":"INTERLOCK, CONTACTOR"},{"codigo":"818236B","detalle":"MELT FUSE IT"},{"codigo":"818275A","detalle":"MOTOR, AIR EXCHANGE MODULE"},{"codigo":"818792A","detalle":"MOTOR, CONDENSER FAN"},{"codigo":"818783A","detalle":"MOTOR, EVAPORATOR FAN"},{"codigo":"818525C","detalle":"ON/OFF SWITCH CIM 6"},{"codigo":"881550A","detalle":"PLUG, EVAPORATOR SERVICE HOLE"},{"codigo":"818905A","detalle":"POWER MEASUREMENT MODULE, CIM 6.2"},{"codigo":"819504D","detalle":"PRESSURE TRANSMITTER HP NSK"},{"codigo":"819503D","detalle":"PRESSURE TRANSMITTER LP NSK"},{"codigo":"814540B","detalle":"RECEIVER"},{"codigo":"818739A","detalle":"RECEIVER, WATER COOLED CONDENSER"},{"codigo":"818276A","detalle":"SENSOR AIR EXCHANGE MODULE"},{"codigo":"818623C","detalle":"SERVICE VALVE, COMPRESSOR LP"},{"codigo":"818235B","detalle":"SIGHT GLASS RECEIVER KIT"},{"codigo":"818553A","detalle":"SOLENOID COIL 14W 24VDC CIM 5"},{"codigo":"818554A","detalle":"SOLENOID COIL 18W 24VDC CIM 5"},{"codigo":"886554B","detalle":"SOLENOID COIL 11W 24VAC"},{"codigo":"814541C","detalle":"SQUARE FAN GRILLE, CONDENSER"},{"codigo":"819500C","detalle":"STOP VALVE RECEIVER"},{"codigo":"818940A","detalle":"TEMPERATURE SENSOR 0.35M"},{"codigo":"818943B","detalle":"TEMPERATURE SENSOR INCL. CABLE GLAND (3M)"},{"codigo":"818639B","detalle":"TERMINAL BLOCK, COMPRESSOR"},{"codigo":"818518C","detalle":"TRANSFORMER 105 VA CIM 6"},{"codigo":"886513A","detalle":"USER PANEL, CIM 6.1"},{"codigo":"INDND0078","detalle":"ACEITE AFLOJATODO"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER BVA"},{"codigo":"INDND3242","detalle":"BROCA DE COBALTO HSS 3/16"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4 X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.14M"},{"codigo":"INDND0433","detalle":"CINTILLO DE AMARRE 150MM"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1911","detalle":"EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M"},{"codigo":"INDND0126","detalle":"ESTAÑO 0.8"},{"codigo":"INDND2552","detalle":"FILTRO SECADOR QDM-164 1/2 - QUALITY"},{"codigo":"INDND0024","detalle":"FILTRO SEC. FIJO DE 1/2 FLARE - EK 164 STD"},{"codigo":"INDND2905","detalle":"FORMADOR EMPAQUETADURA AVIACION 3H"},{"codigo":"INDND2237","detalle":"FUNDENTE"},{"codigo":"INDND1545","detalle":"MINI FUSIBLE DE VIDRIO 15 AMP"},{"codigo":"INDND1542","detalle":"FUSIBLE DE VIDRIO 10 AMP"},{"codigo":"RNDND0318","detalle":"FUSIBLE DE VIDRIO 20 AMP"},{"codigo":"INDND0120","detalle":"GAS REFRIGERANTE R-134A X 13.60KG"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND0016","detalle":"LIJA FIERRO #40 ASA"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND0086","detalle":"NITROGENO INDUSTRIAL 10 M3"},{"codigo":"INDND3074","detalle":"MANGA TERMOCONTRAIBLE 15MM"},{"codigo":"INDND3322","detalle":"MANGA TERMOCONTRAIBLE 20MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND1555","detalle":"PERNO HEX. RC. INOX 304 M16X50"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"INDND2768","detalle":"RODAJE 6201 2RSH/C3"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND5576","detalle":"TERMINAL TUBULAR SOBREMOLDEADO ROJO 4MM 12AWG"},{"codigo":"INDND2711","detalle":"TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG"},{"codigo":"INDND2936","detalle":"TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"INDND0087","detalle":"SOLVENTE DIELECTRICO SDL-25"},{"codigo":"INDND1412","detalle":"VALVULA DE ACCESO 1/4 X 7 CM"}],"STAR COOL CIM 5":[{"codigo":"818270C","detalle":"AIR EXCHANGE MODULE (75CMH)"},{"codigo":"819747B","detalle":"AIR RELEASE VALVE, RECEIVER"},{"codigo":"818522F","detalle":"AUXILIARY CONTACT (WHITE DOT 10PCS)"},{"codigo":"818522C","detalle":"AUXILIARY CONTACT"},{"codigo":"818536B","detalle":"BATTERY PACK CIM5"},{"codigo":"811537D","detalle":"BRACKET, EVAPORATOR FAN MOTOR"},{"codigo":"818202B","detalle":"CABLE ADAPTER KIT, FAN MOTOR"},{"codigo":"815505D","detalle":"CABLE ROOM COVER"},{"codigo":"818561B","detalle":"CABLE SET (X1, X2, X3), CIM 5"},{"codigo":"814247C","detalle":"CABLE, FC (1.0 AND 1.1) TO COMPRESSOR"},{"codigo":"819526B","detalle":"COIL CONDENSER"},{"codigo":"818658B","detalle":"COMPRESSOR"},{"codigo":"818521B","detalle":"CONTACTOR"},{"codigo":"818310B","detalle":"CONTROLLER DOOR, CIM 5"},{"codigo":"818320B","detalle":"CONTROLLER DOOR, COMPLETE CIM 5"},{"codigo":"818512A","detalle":"CONTROLLER MODULE, CA"},{"codigo":"868255C","detalle":"CONTROLLER MODULE, CIM 5"},{"codigo":"818255C","detalle":"CONTROLLER MODULE, CIM 5"},{"codigo":"818209D","detalle":"COVER PLATE (2100MM) CONDENSER SCI"},{"codigo":"818250E","detalle":"DAMPER, AIR EXCHANGE MODULE"},{"codigo":"811522B","detalle":"DEFROST HEATER ELEMENT, TRAY"},{"codigo":"818515A","detalle":"DISPLAY PCB, CIM 5"},{"codigo":"814667F","detalle":"ECONOMIZER"},{"codigo":"819737D","detalle":"ECONOMIZER VALVE, R134A"},{"codigo":"881527A","detalle":"EVAPORATOR COIL"},{"codigo":"819543B","detalle":"FAN BLADE, CONDENSER"},{"codigo":"819542C","detalle":"FAN BLADE, EVAPORATOR"},{"codigo":"819506A","detalle":"FILTER DRYER, R134A AND R513A"},{"codigo":"818738A","detalle":"FILTER DRYER, R134A AND R513A (12 PCS)"},{"codigo":"818965B","detalle":"FREQUENCY CONVERTER 2.1"},{"codigo":"818274C","detalle":"FRONT PART, AIR EXCHANGE MODULE (75 CMH)"},{"codigo":"818656B","detalle":"GASKET, COMPRESSOR STOP VALVE"},{"codigo":"818661B","detalle":"GASKET, SERVICE VALVE LP"},{"codigo":"819501A","detalle":"HIGH PRESSURE SWITCH"},{"codigo":"889740C","detalle":"HOT GAS VALVE"},{"codigo":"819740B","detalle":"HOT GAS VALVE, CIM 5"},{"codigo":"818551A","detalle":"HUMIDITY SENSOR"},{"codigo":"814571B","detalle":"INSULATION, ECONOMIZER"},{"codigo":"818523C","detalle":"INTERLOCK, CONTACTOR"},{"codigo":"818527B","detalle":"KEY PAD CIM 5"},{"codigo":"818517A","detalle":"LED PCB, CIM 5"},{"codigo":"818906A","detalle":"MAIN CIRCUIT BREAKER, CIM 5"},{"codigo":"818236B","detalle":"MELT FUSE IT"},{"codigo":"818792A","detalle":"MOTOR, CONDENSER FAN"},{"codigo":"818783A","detalle":"MOTOR, EVAPORATOR FAN"},{"codigo":"814538D","detalle":"MOUNTING RING, FILTER"},{"codigo":"818525B","detalle":"ON/OFF CIM5"},{"codigo":"818652A","detalle":"PERMANENT MAGNET"},{"codigo":"881550A","detalle":"PLUG, EVAPORATOR SERVICE HOLE"},{"codigo":"819541C","detalle":"PLUG, WATER INLET COUPLING"},{"codigo":"819540C","detalle":"PLUG, WATER OUTLET COUPLING"},{"codigo":"818511B","detalle":"POWER MEASUREMENT PCB, CIM 5"},{"codigo":"819503D","detalle":"PRESSURE TRANSMITTER LP NSK"},{"codigo":"819504D","detalle":"PRESSURE TRANSMITTER HP DST"},{"codigo":"814540B","detalle":"RECEIVER"},{"codigo":"818739A","detalle":"RECEIVER, WATER COOLED CONDENSER"},{"codigo":"819693D","detalle":"SCREW, CONTROLLER DOOR CIM 6"},{"codigo":"818276A","detalle":"SENSOR AIR EXCHANGE MODULE"},{"codigo":"818675B","detalle":"SERVICE KIT, HOT GAS VALVE CIM 5"},{"codigo":"818623C","detalle":"SERVICE VALVE, COMPRESSOR LP"},{"codigo":"818235B","detalle":"SIGHT GLASS KIT, RECEIVER"},{"codigo":"818554A","detalle":"SOLENOID COIL 18W 24VDC CIM 5"},{"codigo":"818553A","detalle":"SOLENOID COIL 14W 24VDC CIM 5"},{"codigo":"886554B","detalle":"SOLENOID COIL 11W 24VAC"},{"codigo":"818526C","detalle":"TERMINAL BLOCK PCB, CIM 5"},{"codigo":"818639B","detalle":"TERMINAL BLOCK, COMPRESSOR"},{"codigo":"818676B","detalle":"TOOL, HOT GAS VALVE"},{"codigo":"818518B","detalle":"TRANSFORMER 145 VA, CIM 5"},{"codigo":"818267B","detalle":"WING SCREW KIT"},{"codigo":"INDND0078","detalle":"ACEITE AFLOJATODO"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER BVA"},{"codigo":"INDND3242","detalle":"BROCA DE COBALTO HSS 3/16"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4 X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.14M"},{"codigo":"INDND0433","detalle":"CINTILLO DE AMARRE 150MM"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1911","detalle":"EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M"},{"codigo":"INDND0126","detalle":"ESTAÑO 0.8"},{"codigo":"INDND2552","detalle":"FILTRO SECADOR QDM-164 1/2 - QUALITY"},{"codigo":"INDND0024","detalle":"FILTRO SEC. FIJO DE 1/2 FLARE - EK 164 STD"},{"codigo":"INDND2905","detalle":"FORMADOR EMPAQUETADURA AVIACION 3H"},{"codigo":"INDND2237","detalle":"FUNDENTE"},{"codigo":"INDND1545","detalle":"MINI FUSIBLE DE VIDRIO 15 AMP"},{"codigo":"INDND1542","detalle":"FUSIBLE DE VIDRIO 10 AMP"},{"codigo":"RNDND0318","detalle":"FUSIBLE DE VIDRIO 20 AMP"},{"codigo":"INDND0120","detalle":"GAS REFRIGERANTE R-134A X 13.60KG"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND0016","detalle":"LIJA FIERRO #40 ASA"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND0086","detalle":"NITROGENO INDUSTRIAL 10 M3"},{"codigo":"INDND3074","detalle":"MANGA TERMOCONTRAIBLE 15MM"},{"codigo":"INDND3322","detalle":"MANGA TERMOCONTRAIBLE 20MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND1555","detalle":"PERNO HEX. RC. INOX 304 M16X50"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"INDND2768","detalle":"RODAJE 6201 2RSH/C3"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND5576","detalle":"TERMINAL TUBULAR SOBREMOLDEADO ROJO 4MM 12AWG"},{"codigo":"INDND2711","detalle":"TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG"},{"codigo":"INDND2936","detalle":"TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"INDND0087","detalle":"SOLVENTE DIELECTRICO SDL-25"},{"codigo":"INDND1412","detalle":"VALVULA DE ACCESO 1/4 X 7 CM"}],"TK MP5000":[{"codigo":"672454","detalle":"COIL - CONDENSER (ALUMINUM FINS)"},{"codigo":"781924","detalle":"FAN - CONDENSER"},{"codigo":"1040858","detalle":"MOTOR - CONDENSER FAN"},{"codigo":"970599","detalle":"HOUSING - EVAPORATOR (2 FANS)"},{"codigo":"427333","detalle":"SENSOR - RETURN AIR"},{"codigo":"782096","detalle":"FAN - EVAPORATOR 355MM 7 BLADES"},{"codigo":"941887","detalle":"BRACKET - MOTOR"},{"codigo":"1040894","detalle":"MOTOR - FAN"},{"codigo":"673471","detalle":"COIL - EVAPORATOR"},{"codigo":"427334","detalle":"SENSOR - DEFROST"},{"codigo":"422659","detalle":"SENSOR - HUMIDITY"},{"codigo":"427338","detalle":"SENSOR - CO2 RS485"},{"codigo":"420374","detalle":"CABLE - SUPPLY RS485"},{"codigo":"612477","detalle":"TUBE - VALVE TO COIL"},{"codigo":"672787","detalle":"TANK - RECEIVER STANDARD"},{"codigo":"610786","detalle":"DEHYDRATOR"},{"codigo":"671889","detalle":"HEAT EXCHANGER - ECONOMIZER"},{"codigo":"618684","detalle":"VALVE - SOLENOID VAPOR INJECTION"},{"codigo":"415460","detalle":"COIL - VALVE"},{"codigo":"600731","detalle":"KIT - TXV EXPANSION VALVE"},{"codigo":"612465","detalle":"VALVE - BALL"},{"codigo":"617758","detalle":"VALVE PWM"},{"codigo":"421423","detalle":"SWITCH - LPCO"},{"codigo":"672853","detalle":"TANK - RECEIVER WITH SHUT-OFF VALVE"},{"codigo":"425968","detalle":"TRANSDUCER - SUCTION"},{"codigo":"610443","detalle":"VALVE - EXPANSION"},{"codigo":"1020795","detalle":"COMPRESSOR - SCROLL"},{"codigo":"919021","detalle":"COVER - TERMINAL BOX"},{"codigo":"401377","detalle":"KIT - THERMISTOR"},{"codigo":"414004","detalle":"SWITCH - HPCO"},{"codigo":"612118","detalle":"VALVE - SUCTION"},{"codigo":"612119","detalle":"VALVE - DISCHARGE"},{"codigo":"335215","detalle":"GASKET - VALVE SERVICE"},{"codigo":"400782","detalle":"KIT - POWER CORD"},{"codigo":"401044","detalle":"SENSOR KIT DEFROST/AMBIENT/RETURN/SUPPLY/COIL"},{"codigo":"451992","detalle":"CABLE - POWER 19.2 METERS"},{"codigo":"452889","detalle":"HEATER 1360W"},{"codigo":"453031","detalle":"BASE - CONTROL BOX MP-5000"},{"codigo":"413595","detalle":"SWITCH - ON/OFF"},{"codigo":"426427","detalle":"TRANSFORMER"},{"codigo":"426424","detalle":"BATTERY - MP-5000"},{"codigo":"426423","detalle":"CONTROLLER - MP-5000"},{"codigo":"427238","detalle":"BUSBAR COMB 63A 3 TAP-OFFS"},{"codigo":"427239","detalle":"BUSBAR COMB 63A 4 TAP-OFFS"},{"codigo":"423820","detalle":"CONTACTOR AC LC1D 3P 25A"},{"codigo":"426428","detalle":"TRANSFORMER CURRENT MP-5000"},{"codigo":"415104","detalle":"BREAKER CIRCUIT 25A"},{"codigo":"940841","detalle":"DOOR - CONTROLLER MP-5000"},{"codigo":"426430","detalle":"KEYPAD - CONTROLLER"},{"codigo":"427072","detalle":"DISPLAY - LARGE"},{"codigo":"426752","detalle":"MODULE - MP-5000"},{"codigo":"1021428","detalle":"COMPRESSOR ASSEMBLY WITH MOTOR"},{"codigo":"221473","detalle":"FILTER - COMPRESSOR SUCTION"},{"codigo":"941897","detalle":"COVER - COMPRESSOR"},{"codigo":"427160","detalle":"SENSOR - O2 RS485"},{"codigo":"427161","detalle":"SENSOR - CO2 RS485"},{"codigo":"918252TKA","detalle":"VENT - AIR COMPLETE"},{"codigo":"929522","detalle":"BRACKET - AIR VENT"},{"codigo":"417238TKA","detalle":"ACTUATOR"},{"codigo":"925687","detalle":"DOOR - AFAM"},{"codigo":"937326","detalle":"GRILLE - FRESH AIR"},{"codigo":"925661","detalle":"LABEL - AFAM DOOR POSITION"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER"},{"codigo":"INDND0078","detalle":"AFLOJATODO"},{"codigo":"INDND4464","detalle":"AGENTE LIMPIADOR DE SISTEMAS OPTEON SF FLUSH X 4.54 KG"},{"codigo":"INDND3876","detalle":"ANILLO DE TEFLON 1/16 D 15 X 10 MM"},{"codigo":"INDND3565","detalle":"ARANDELA DE PRESION INOX M6"},{"codigo":"INDND1391","detalle":"ARANDELA PLANA INOX 1/4"},{"codigo":"INDND2543","detalle":"BOMBA DE VACIO CPS VP6D 1/2HP"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.4 M"},{"codigo":"INDND4185","detalle":"CINTA PARA DUCTO 10MX48MM"},{"codigo":"INDND2786","detalle":"CINTA VULCANIZANTE SCOTCH 23 3/4"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1718","detalle":"CONTACTOR SCHNEIDER 32A 440V LC1D32R7"},{"codigo":"INDND4864","detalle":"CONTACTOR TESYS DECA 3P AC-3 12A BOBINA 24VAC LC1D12B7"},{"codigo":"INDND1911","detalle":"EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M"},{"codigo":"RNDND0198","detalle":"ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67"},{"codigo":"INDND0126","detalle":"ESTAÑO 0.8"},{"codigo":"818738A","detalle":"FILTER DRYER, R134A AND R513A (12 PCS)"},{"codigo":"RNDND0318","detalle":"FUSIBLE DE VIDRIO 20 AMP"},{"codigo":"INDND2144","detalle":"GAS MAP PRO"},{"codigo":"INDND0022","detalle":"GAS REFRIGERANTE R-404A X 10.90KG"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND1184","detalle":"LIJA FIERRO #100"},{"codigo":"INDND0016","detalle":"LIJA FIERRO #40"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND2300","detalle":"MANGA TERMOCONTRAIBLE 25MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND0738","detalle":"MANGUERA CORRUGADA 1/2"},{"codigo":"INDND0279","detalle":"MANGUERA CORRUGADA 3/8"},{"codigo":"INDND2111","detalle":"MANGUERA CORRUGADA DE 1 PULGADA"},{"codigo":"INDND4520","detalle":"ORING VITON 3-023"},{"codigo":"INDND3649","detalle":"ORING VITON 2-014"},{"codigo":"INDND1104","detalle":"PEGAMENTO SUPERFLEX INDUSTRIAL"},{"codigo":"INDND1417","detalle":"PERNO HEX ZINC 1/4 X 1"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND2789","detalle":"PRENSA ESTOPA 1 NPT"},{"codigo":"INDND2838","detalle":"PRENSA ESTOPA 3/8 PG11"},{"codigo":"INDND3078","detalle":"RELE PROTECTOR DE FASE TIPO GALLETA GRV8-03"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"INDND2265","detalle":"RIEL DIN PERFORADO"},{"codigo":"INDND0260","detalle":"RODAMIENTO 6203"},{"codigo":"INDND0081","detalle":"RODAMIENTO 6205"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND1962","detalle":"TERMINAL AISLADO TIPO BALA HEMBRA AZUL"},{"codigo":"INDND1097","detalle":"TERMINAL AISLADO TIPO BALA MACHO AZUL"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND0885","detalle":"TERMINAL OJO VF5.5-6S 1/4"},{"codigo":"INDND2711","detalle":"TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG"},{"codigo":"INDND2936","detalle":"TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"RNDND0802","detalle":"TUBERIA DE COBRE 1/2"},{"codigo":"INDND0754","detalle":"TUBERIA DE COBRE 1/4"},{"codigo":"INDND0357","detalle":"TUBERIA DE COBRE 3/8"},{"codigo":"RNDND0440","detalle":"TUBO DE COBRE 1/8 X 15M"},{"codigo":"INDND0169","detalle":"TUERCA HEXAGONAL 1/4 ZINCADO"},{"codigo":"INDND1536","detalle":"UNION SOLDABLE 1/4"}],"TK MP4000":[{"codigo":"417238TKA","detalle":"ACTUATOR"},{"codigo":"918252","detalle":"AIR VENT"},{"codigo":"418717","detalle":"BATTERY LITHIUM MP-4000"},{"codigo":"413596","detalle":"BLOCK - TERMINAL 4 POLE"},{"codigo":"413598","detalle":"BLOCK - TERMINAL 8 POLE"},{"codigo":"918466","detalle":"BRACKET MOTOR"},{"codigo":"RNDND0724","detalle":"BREAKER CIRCUIT 25A"},{"codigo":"418716","detalle":"CABLE SERIAL CM-4000A0 / PM4000"},{"codigo":"INDND5049","detalle":"CABLE SUPPLY 420374"},{"codigo":"91-9331","detalle":"CHANNEL - FRESH AIR"},{"codigo":"671923","detalle":"COIL EVAPORATOR"},{"codigo":"415460","detalle":"COIL VALVE LIQ"},{"codigo":"INDND1604","detalle":"COMPRESSOR - SCROLL"},{"codigo":"69NT4320220","detalle":"CONDENSER COIL"},{"codigo":"421636","detalle":"CONNECTOR 10-PIN J2/J17"},{"codigo":"412446","detalle":"CONTACTOR 25A"},{"codigo":"RNDND0064","detalle":"CONTACTOR 30 AMP"},{"codigo":"100043106","detalle":"CONTACTOR 12AMP"},{"codigo":"452295","detalle":"CONTROLLER MP4000"},{"codigo":"418718","detalle":"COVER EXPANSION BOARD"},{"codigo":"937354","detalle":"DECAL R404A"},{"codigo":"418723","detalle":"DOOR FRONT MP-4000 WHITE"},{"codigo":"610156","detalle":"DRIER UNIVERSAL CONTAINER"},{"codigo":"818738A","detalle":"FILTER DRYER, R134A AND R513A (12 PCS)"},{"codigo":"781683","detalle":"EVAPORATOR FAN"},{"codigo":"78-1684","detalle":"FAN - CONDENSER"},{"codigo":"781924","detalle":"FAN CONDENSER ASSEMBLY"},{"codigo":"669842","detalle":"FITTING FOR LPCO"},{"codigo":"559485","detalle":"FLATWASHER"},{"codigo":"RNDND0624","detalle":"FUSE HOLDER BLK MP4000"},{"codigo":"332510","detalle":"GASKET - VALVE PLATE"},{"codigo":"332805","detalle":"GASKET DISCHARGE"},{"codigo":"988244","detalle":"GRILLE - EVAPORATOR"},{"codigo":"452889","detalle":"HEATER ELEMENT 1360W BROWN"},{"codigo":"45-2451","detalle":"HEATER ELEMENT 2000W"},{"codigo":"3504979","detalle":"HEATER ELEMENT 750W 230V"},{"codigo":"422659","detalle":"HUMIDITY SENSOR"},{"codigo":"INDND4844","detalle":"KIT - POWER CORD"},{"codigo":"401044","detalle":"KIT SENSOR MP4000"},{"codigo":"900331TKA","detalle":"KIT SPACER FAN"},{"codigo":"INDND4843","detalle":"KIT THERMISTOR THK"},{"codigo":"INDND1609","detalle":"KIT TXV ECONOMIZER"},{"codigo":"420353","detalle":"MODULE - AFAM+"},{"codigo":"418719","detalle":"MODULE POWER MP4000"},{"codigo":"104-759","detalle":"MOTOR CONDENSADOR TK"},{"codigo":"104691","detalle":"MOTOR EVAPORADOR"},{"codigo":"47225","detalle":"MP4000 CONTROL BOX"},{"codigo":"330727","detalle":"O RING"},{"codigo":"927635","detalle":"RAIL - DIN"},{"codigo":"421595","detalle":"SENSOR CO2 RS485"},{"codigo":"RNDND0562","detalle":"SENSOR USDA 2.5MM"},{"codigo":"781737","detalle":"SHROUD FAN"},{"codigo":"414004","detalle":"SWITCH HPCO"},{"codigo":"INDND1606","detalle":"SWITCH LPCO"},{"codigo":"418763","detalle":"TRANSFORMER MP4000"},{"codigo":"618179","detalle":"TX VALVE ECONOMIZER"},{"codigo":"669900","detalle":"VALVE EXPANSION ECONOMIZER"},{"codigo":"RNDND0130","detalle":"VALVE EXPANSION"},{"codigo":"612470","detalle":"VALVE SOLENOID"},{"codigo":"617758","detalle":"VALVE DIGITAL"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER"},{"codigo":"INDND0078","detalle":"AFLOJATODO"},{"codigo":"INDND4464","detalle":"AGENTE LIMPIADOR DE SISTEMAS OPTEON SF FLUSH X 4.54 KG"},{"codigo":"INDND3876","detalle":"ANILLO DE TEFLON 1/16 D 15 X 10 MM"},{"codigo":"INDND3565","detalle":"ARANDELA DE PRESION INOX M6"},{"codigo":"INDND1391","detalle":"ARANDELA PLANA INOX 1/4"},{"codigo":"INDND2543","detalle":"BOMBA DE VACIO CPS VP6D 1/2HP"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.4 M"},{"codigo":"INDND4185","detalle":"CINTA PARA DUCTO 10MX48MM"},{"codigo":"INDND2786","detalle":"CINTA VULCANIZANTE SCOTCH 23 3/4"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1718","detalle":"CONTACTOR SCHNEIDER 32A 440V LC1D32R7"},{"codigo":"INDND4864","detalle":"CONTACTOR TESYS DECA 3P AC-3 12A BOBINA 24VAC LC1D12B7"},{"codigo":"INDND1911","detalle":"EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M"},{"codigo":"RNDND0198","detalle":"ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67"},{"codigo":"INDND0126","detalle":"ESTAÑO 0.8"},{"codigo":"RNDND0318","detalle":"FUSIBLE DE VIDRIO 20 AMP"},{"codigo":"INDND2144","detalle":"GAS MAP PRO"},{"codigo":"INDND0022","detalle":"GAS REFRIGERANTE R-404A X 10.90KG"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND1184","detalle":"LIJA FIERRO #100"},{"codigo":"INDND0016","detalle":"LIJA FIERRO #40"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND2300","detalle":"MANGA TERMOCONTRAIBLE 25MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND0738","detalle":"MANGUERA CORRUGADA 1/2"},{"codigo":"INDND0279","detalle":"MANGUERA CORRUGADA 3/8"},{"codigo":"INDND2111","detalle":"MANGUERA CORRUGADA DE 1 PULGADA"},{"codigo":"INDND4520","detalle":"ORING VITON 3-023"},{"codigo":"INDND3649","detalle":"ORING VITON 2-014"},{"codigo":"INDND1104","detalle":"PEGAMENTO SUPERFLEX INDUSTRIAL"},{"codigo":"INDND1417","detalle":"PERNO HEX ZINC 1/4 X 1"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND2789","detalle":"PRENSA ESTOPA 1 NPT"},{"codigo":"INDND2838","detalle":"PRENSA ESTOPA 3/8 PG11"},{"codigo":"INDND3078","detalle":"RELE PROTECTOR DE FASE TIPO GALLETA GRV8-03"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"INDND2265","detalle":"RIEL DIN PERFORADO"},{"codigo":"INDND0260","detalle":"RODAMIENTO 6203"},{"codigo":"INDND0081","detalle":"RODAMIENTO 6205"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND1962","detalle":"TERMINAL AISLADO TIPO BALA HEMBRA AZUL"},{"codigo":"INDND1097","detalle":"TERMINAL AISLADO TIPO BALA MACHO AZUL"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND0885","detalle":"TERMINAL OJO VF5.5-6S 1/4"},{"codigo":"INDND2711","detalle":"TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG"},{"codigo":"INDND2936","detalle":"TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"RNDND0802","detalle":"TUBERIA DE COBRE 1/2"},{"codigo":"INDND0754","detalle":"TUBERIA DE COBRE 1/4"},{"codigo":"INDND0357","detalle":"TUBERIA DE COBRE 3/8"},{"codigo":"RNDND0440","detalle":"TUBO DE COBRE 1/8 X 15M"},{"codigo":"INDND0169","detalle":"TUERCA HEXAGONAL 1/4 ZINCADO"},{"codigo":"INDND1536","detalle":"UNION SOLDABLE 1/4"}],"CARRIER":[{"codigo":"10-00439-01","detalle":"AMPERIMETRO"},{"codigo":"22-50088-01","detalle":"CAPACITOR 15UF"},{"codigo":"22-50088-00","detalle":"CAPACITOR 20UF"},{"codigo":"INDND0431","detalle":"CAPACITOR DE 5UF"},{"codigo":"66U1-7842-13","detalle":"CIRCUIT BREAKER 460VAC 25AMP"},{"codigo":"14-00247-20","detalle":"COIL"},{"codigo":"14-00393-10","detalle":"COIL 2010-2012"},{"codigo":"76-00748-00","detalle":"COIL EVAPORATOR"},{"codigo":"14-00393-10","detalle":"COIL EVV"},{"codigo":"14-00247-20","detalle":"COIL VALVE EXPANSION 2008-2010"},{"codigo":"14-00230-24SV","detalle":"COIL SOLENOID"},{"codigo":"14-01091-02","detalle":"COIL SOLENOID 24V"},{"codigo":"18-10134-23","detalle":"COMPRESSOR SCROLL AZUL"},{"codigo":"18-10178-20","detalle":"COMPRESSOR SCROLL PLOMO"},{"codigo":"18-10129-20SV","detalle":"COMPRESSOR CONT 41CFM"},{"codigo":"69NT43-202-20","detalle":"CONDENSOR COIL"},{"codigo":"100043106","detalle":"CONTACTOR 12AMP 10-00431-06"},{"codigo":"RNDND0064","detalle":"CONTACTOR 30 AMP 10-00431-07"},{"codigo":"120057900","detalle":"MICROLINK 3"},{"codigo":"1256002","detalle":"MICROLINK 2I"},{"codigo":"400052000","detalle":"COUPLING M13 LOW"},{"codigo":"400052001","detalle":"COUPLING M15 HIGH"},{"codigo":"69NT20-2083","detalle":"COVER JUNCTION BOX"},{"codigo":"12-00433-03RP","detalle":"DISPLAY"},{"codigo":"14-00393-00SV","detalle":"EEV 2010-2012"},{"codigo":"38-00585-00","detalle":"FAN CONDENSER"},{"codigo":"38-00599-00","detalle":"FAN EVAPORATOR"},{"codigo":"INDND1585","detalle":"THERMISTOR TEMP SENSOR"},{"codigo":"3504979","detalle":"HEATER BAR 750W"},{"codigo":"296660300","detalle":"HEATER 750V 230V"},{"codigo":"14-00221-04","detalle":"INDICATOR SIGHTGLASS R134A"},{"codigo":"79-66669-02","detalle":"KEYPAD ASSY"},{"codigo":"INDND2389","detalle":"KIT EMPAQUETADURA CARRIER"},{"codigo":"12-00495-02SV","detalle":"KIT AMBIENT/DEFROST SENSOR"},{"codigo":"12-00425-00","detalle":"MODULE CONTROLLER MICRO-LINK 2i"},{"codigo":"INDND0904","detalle":"MOTOR EVAPORADOR TRIFASICO"},{"codigo":"54-00586-20","detalle":"MOTOR CONDENSER"},{"codigo":"54-00585-20","detalle":"MOTOR EVAPORADOR MONOFASICO"},{"codigo":"30-00407-02SV","detalle":"PACK BATTERY DATACORDER"},{"codigo":"10-00388-00","detalle":"POWERPACK STEPPER MOTOR"},{"codigo":"12-00500-01SV","detalle":"SENSOR COMBINATION RETURN"},{"codigo":"12-00745-00SV","detalle":"SENSOR HUMIDITY W/BRACKET"},{"codigo":"12-00395-01SV","detalle":"SENSOR THERMISTOR SUPPLY"},{"codigo":"12-00309-06","detalle":"SWITCH HIGH PRESSURE HPS"},{"codigo":"RNDND0131","detalle":"SWITCH THERMOSTAT"},{"codigo":"65-00185-03","detalle":"TANQUE RECIBIDOR"},{"codigo":"17-40075-05","detalle":"TERMINAL PLATE"},{"codigo":"12-00352-00","detalle":"TRANSDUCER PRESSURE HIGH"},{"codigo":"12-00352-07SV","detalle":"TRANSDUCER PRESSURE LOW"},{"codigo":"INDND2612","detalle":"TRANSFORMER ELECTRIC CONTROL 440/24V"},{"codigo":"12-00655-01","detalle":"TRANSDUCER PRIME LINE"},{"codigo":"14-00247-01","detalle":"VALVE"},{"codigo":"140027308","detalle":"VALVE HERMETIC TXV THINLINE"},{"codigo":"14-00204-04","detalle":"VALVE DISCHARGE DPRV"},{"codigo":"14-00247-01","detalle":"VALVE EVAPORATOR EXPANSION"},{"codigo":"14-00232-33","detalle":"VALVE EXPANSION"},{"codigo":"14-00206-00","detalle":"VALVE SERVICE"},{"codigo":"14-00206-01","detalle":"VALVE SERVICE"},{"codigo":"14-00353-04","detalle":"VALVE STEPPER MOTOR"},{"codigo":"810147200","detalle":"TUBE ASSY DISCHARGE"},{"codigo":"14-00232-03","detalle":"VALVE TXV"},{"codigo":"INDND2543","detalle":"BOMBA DE VACIO CPS VP6D"},{"codigo":"RNDND0293","detalle":"CHISPEROS"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER"},{"codigo":"INDND0078","detalle":"AFLOJATODO"},{"codigo":"IND0672","detalle":"ARANDELA DE PRESION ZINC 1/4"},{"codigo":"INDND0175","detalle":"BROCA DE COBALTO HSS 1/4"},{"codigo":"INDND3242","detalle":"BROCA DE COBALTO HSS 3/16"},{"codigo":"INDND0134","detalle":"BROCA DE COBALTO HSS 3/8"},{"codigo":"INDND1448","detalle":"CABLE FLEXIBLE AUTOMOTRIZ GTP 10AWG"},{"codigo":"RNDND0558","detalle":"CABLE TW-80 N° 14 AWG"},{"codigo":"INDND0199","detalle":"CABLE VULCANIZADO 3X16"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4X10"},{"codigo":"INDND1589","detalle":"CAÑA DE SOLDAR"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.4 M"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1911","detalle":"EMPAQUE DE ASBESTO"},{"codigo":"RNDND0198","detalle":"ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67"},{"codigo":"INDND5002","detalle":"EXTENSION CORRIENTE 3X16 30MTS"},{"codigo":"818738A","detalle":"FILTER DRYER, R134A AND R513A (12 PCS)"},{"codigo":"INDND0194","detalle":"FUSIBLE TIPO UÑA 10 AMP"},{"codigo":"INDND0193","detalle":"FUSIBLE TIPO UÑA 5 AMP"},{"codigo":"INDND0648","detalle":"FUSIBLE TIPO UÑA 7.5 AMP"},{"codigo":"INDND2144","detalle":"GAS MAP PRO"},{"codigo":"INDND0120","detalle":"GAS REFRIGERANTE R-134A X 13.60KG"},{"codigo":"RNDND0423","detalle":"GRASA GRA LGMT 3/1"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND0054","detalle":"LIJA FIERRO #120"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND2300","detalle":"MANGA TERMOCONTRAIBLE 25MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND1417","detalle":"PERNO HEX ZINC 1/4 X 1"},{"codigo":"INDND0108","detalle":"PINTURA EN SPRAY ALUMINIO"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND1547","detalle":"PORTA FUSIBLE AEREO"},{"codigo":"RNDND0100","detalle":"RELAY 24V 720W"},{"codigo":"INDND0171","detalle":"REMACHE POP DE ALUMINIO 3/16X1"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"RNDND0260","detalle":"RODAMIENTO 6203"},{"codigo":"INDND0081","detalle":"RODAMIENTO 6205"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND0087","detalle":"SOLVENTE DIELECTRICO SDL-25"},{"codigo":"INDND1962","detalle":"TERMINAL AISLADO TIPO BALA HEMBRA AZUL"},{"codigo":"INDND1097","detalle":"TERMINAL AISLADO TIPO BALA MACHO AZUL"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"RNDND0802","detalle":"TUBERIA DE COBRE 1/2"},{"codigo":"INDND0754","detalle":"TUBERIA DE COBRE 1/4"},{"codigo":"INDND0169","detalle":"TUERCA HEXAGONAL 1/4 ZINCADO"}],"DAIKIN":[{"codigo":"1612576","detalle":"ACCESS PANEL EVAPORADOR"},{"codigo":"1387173","detalle":"AIR COOLED CONDENSER"},{"codigo":"1588349","detalle":"AIR DISCHARGE GRILLE"},{"codigo":"0954633","detalle":"BOARD PTCT BOBINA DE CORRIENTE"},{"codigo":"1270408","detalle":"BODY SMV"},{"codigo":"1270390","detalle":"COIL SMV"},{"codigo":"1266290","detalle":"COIL SOLENOID VALVE"},{"codigo":"1315426","detalle":"COMPRESOR"},{"codigo":"0954936","detalle":"CONDENSER FAN"},{"codigo":"1787494","detalle":"CONTROL BOX COMPLETO"},{"codigo":"11739318","detalle":"CONTROL BOX COVER WELDING"},{"codigo":"1295553","detalle":"CONTROL PANEL"},{"codigo":"1010815","detalle":"DISPLAY"},{"codigo":"1241385","detalle":"DRIER ASSY"},{"codigo":"1381120","detalle":"EARTH LEAKAGE CIRCUIT BREAKER"},{"codigo":"1254538","detalle":"ELECTRONIC EXPANSION VALVE BODY ASSY"},{"codigo":"138143J","detalle":"ELECTRONIC EXPANSION VALVE COIL"},{"codigo":"1787470","detalle":"EVAPORATOR ASSY"},{"codigo":"0777519","detalle":"FAN EVAPORADOR"},{"codigo":"0980618","detalle":"FANBLADE OUTSIDE"},{"codigo":"INDND2552","detalle":"FILTRO SECADOR QDM-164 1/2 QUALITY"},{"codigo":"INDND0024","detalle":"FILTRO SEC. FIJO DE 1/2 FLARE EK 164 STD"},{"codigo":"1787456","detalle":"FRONT PLATE"},{"codigo":"003065J","detalle":"FUSE CONTROLLER"},{"codigo":"1241378","detalle":"HIGH PRESSURE SWITCH"},{"codigo":"1587959","detalle":"HIGH PRESSURE TRANSDUCER HPT"},{"codigo":"1679A30","detalle":"KIT BATTERY"},{"codigo":"1561796","detalle":"LOW FREQUENCY TRANSFORMER"},{"codigo":"1587942","detalle":"LOW PRESSURE TRANSDUCER LPT"},{"codigo":"119891J","detalle":"MAGNETIC CONTACTOR COMPRESSOR"},{"codigo":"119893J","detalle":"MAGNETIC CONTACTOR FANS"},{"codigo":"124149J","detalle":"MAGNETIC CONTACTOR PHASE CORRECTION"},{"codigo":"0955333","detalle":"MOTOR EVAPORADOR"},{"codigo":"2089294","detalle":"NEW COIL VALVE EVV"},{"codigo":"2075473","detalle":"NEW VALVE EXP EVV"},{"codigo":"098333J","detalle":"SENSOR COMP SUCTION TEMP"},{"codigo":"156282J","detalle":"SENSOR EIS"},{"codigo":"156283J","detalle":"SENSOR EOS"},{"codigo":"0798321","detalle":"SENSOR AMBIENT AIR TEMP"},{"codigo":"098332J","detalle":"SENSOR DISCHARGE PIPE TEMP"},{"codigo":"1787247","detalle":"SOLENOID VALVE BODY"},{"codigo":"1256116","detalle":"TERMINAL STRIP VER. 1"},{"codigo":"1679137","detalle":"TERMINAL STRIP VER. 2"},{"codigo":"2346269","detalle":"CONTROL BOX COVER WELDING ASSY"},{"codigo":"1780309","detalle":"BUSHING"},{"codigo":"2272856","detalle":"HEXAGON HEAD BOLT"},{"codigo":"1938968","detalle":"ROLLE"},{"codigo":"1136539","detalle":"CLAMP"},{"codigo":"112894J","detalle":"PACKING"},{"codigo":"1938944","detalle":"CONTROL PANEL WITH SHEET KEY"},{"codigo":"0907062","detalle":"SEAL WASHER"},{"codigo":"2272863","detalle":"PAN HEAD MACHINE SCREW"},{"codigo":"INDND0078","detalle":"ACEITE AFLOJATODO"},{"codigo":"IND411","detalle":"ACEITE POE 68"},{"codigo":"INDND0405","detalle":"ADHESIVO POLIURETANO 540 GRIS 600ML"},{"codigo":"INDND4837","detalle":"SILICON SEALANT 590ML COLOR BLANCO"},{"codigo":"INDND4836","detalle":"SILICON SEALANT 590ML COLOR GREY"},{"codigo":"INDND2543","detalle":"BOMBA DE VACIO CPS VP6D 1/2HP"},{"codigo":"INDND0175","detalle":"BROCA 1/4"},{"codigo":"INDND0176","detalle":"BROCA 3/16"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4 X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.4 M"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"RNDND0198","detalle":"ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67"},{"codigo":"30650","detalle":"FUSIBLE 10A"},{"codigo":"INDND0120","detalle":"GAS REFRIGERANTE R-134A DE 13.600KG"},{"codigo":"INDN0120","detalle":"GAS R134A"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND0054","detalle":"LIJA FIERRO #120"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND3136","detalle":"PERNO HEX. INOX. M6 X 24"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND0170","detalle":"REMACHE POP DE ALUMINIO 1/4X1"},{"codigo":"INDND0171","detalle":"REMACHE POP DE ALUMINIO 3/16X1"},{"codigo":"INDND0260","detalle":"RODAMIENTO 6203"},{"codigo":"INDND0081","detalle":"RODAMIENTO 6205"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND0882","detalle":"TERMINAL OJO 5.5-8 / 12-10"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL"},{"codigo":"INDND3344","detalle":"TUBERIA CONDUIT FLEXIBLE C/F PVC 3/8"}]};
  function byId(id){return document.getElementById(id);}
  function clean(s){return String(s==null?'':s).replace(/\s+/g,' ').trim();}
  function norm(s){return clean(s).toUpperCase().normalize('NFD').replace(/[̀-ͯ]/g,'').replace(/[\s_\-]+/g,' ');}
  function esc(s){return clean(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function toastSafe(t){try{ if(typeof toast==='function') toast(t); else console.log(t); }catch(e){console.log(t);}}
  function controladorKey(){
    const marca = norm(byId('marcaEquipo') && byId('marcaEquipo').value || '');
    const ctrl = norm(byId('controladorEquipo') && byId('controladorEquipo').value || '');
    const joined = marca + ' ' + ctrl;
    if((window.zgGetEquipmentType&&window.zgGetEquipmentType()==='Genset') || joined.includes('SG-3000') || joined.includes('SG 3000') || joined.includes('SG-5000') || joined.includes('SG 5000')){
      if(joined.includes('SG-3000') || joined.includes('SG 3000')) return 'GENSET SG-3000';
      if(joined.includes('SG-5000') || joined.includes('SG 5000')) return 'GENSET SG-5000';
    }
    if(joined.includes('STAR COOL') && (joined.includes('CIM 6') || joined.includes('CIM6'))) return 'STAR COOL CIM 6';
    if(joined.includes('STAR COOL') && (joined.includes('CIM 5') || joined.includes('CIM5'))) return 'STAR COOL CIM 5';
    if(joined.includes('MP5000') || joined.includes('MP 5000')) return 'TK MP5000';
    if(joined.includes('MP4000') || joined.includes('MP 4000')) return 'TK MP4000';
    if(joined.includes('CARRIER')) return 'CARRIER';
    if(joined.includes('DAIKIN')) return 'DAIKIN';
    return '';
  }
  function limpiarPanelItem(r){
    let codigo = clean(r && r.codigo || '');
    let detalle = clean(r && r.detalle || '');
    if(detalle.includes('|')){
      const parts = detalle.split('|').map(clean).filter(Boolean);
      // Si alguien pegó varias cosas en un solo material del panel, mostramos solo el primer nombre limpio.
      if(parts.length) detalle = parts[0].replace(/^[-\d]+\s*/, '').trim();
      for(const p of parts){ const m = p.match(/([A-Z]{2,}\d{3,}|\d{5,})/i); if(!codigo && m){codigo=m[1]; break;} }
    }
    return {codigo, detalle, fuente:'panel'};
  }
  function panelCatalogoLimpio(){
    try{
      return (Array.isArray(REPUESTOS_CATALOGO)?REPUESTOS_CATALOGO:[])
        .map(limpiarPanelItem)
        .filter(x=>x.detalle && !/pendiente de revision/i.test(x.detalle));
    }catch(e){ return []; }
  }
  function catalogoActivo(){
    const key = controladorKey();
    let arr = key ? (ZG_CATALOGOS_POR_CONTROLADOR[key] || []) : [];
    // También se respetan los repuestos creados en panel, pero limpios y sin opción de crear desde técnico.
    // Catálogo del técnico: solo materiales definidos por marca/controlador.
    // Los materiales nuevos o cambios se gestionan desde el panel, no desde esta pantalla.
    const seen = new Set();
    const out = [];
    arr.forEach(x=>{
      const codigo=clean(x.codigo||''); const detalle=clean(x.detalle||'');
      if(!detalle) return;
      const k=(codigo+'|'+detalle).toUpperCase();
      const kd=('DET|'+detalle).toUpperCase();
      if(seen.has(k) || (!codigo && seen.has(kd))) return;
      seen.add(k); seen.add(kd); out.push({codigo, detalle, unidad:'', pendiente_revision:0});
    });
    return out;
  }
  function repuestosFiltradosNuevo(q){
    const query = norm(q||'');
    const arr = catalogoActivo();
    return arr.filter(r=>{
      const c=norm(r.codigo||''), d=norm(r.detalle||'');
      return !query || c.includes(query) || d.includes(query);
    }).sort((a,b)=>{
      const da=norm(a.detalle), db=norm(b.detalle);
      if(query){
        const ax=da.startsWith(query)?0:1, bx=db.startsWith(query)?0:1;
        if(ax!==bx) return ax-bx;
      }
      return String(a.detalle).localeCompare(String(b.detalle),'es',{numeric:true,sensitivity:'base'});
    });
  }
  function renderMenu(items){
    const menu=byId('repuestoSuggest'); if(!menu) return;
    menu.innerHTML='';
    const key = controladorKey();
    if(!key){
      menu.innerHTML='<div class="smart-option" style="cursor:default"><div><span class="smart-main">Selecciona primero la marca y el controlador del equipo.</span><span class="smart-sub">Ejemplo: STAR COOL + CIM 6, THERMO KING + MP5000, CARRIER o DAIKIN.</span></div></div>';
      menu.classList.add('show'); return;
    }
    if(!items.length){
      menu.innerHTML='<div class="smart-option" style="cursor:default"><div><span class="smart-main">No hay coincidencias para este controlador.</span><span class="smart-sub">El técnico no puede crear materiales. Agrégalo desde el panel si corresponde.</span></div></div>';
      menu.classList.add('show'); return;
    }
    items.slice(0,80).forEach(r=>{
      const b=document.createElement('button'); b.type='button'; b.className='smart-option';
      b.innerHTML='<div><span class="smart-main">'+esc(r.detalle)+'</span><span class="smart-sub">'+esc(r.codigo ? 'Código: '+r.codigo : 'Registrado en panel')+'</span></div><span class="smart-badge">usar</span>';
      function usar(ev){
        ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
        try{
          if(typeof agregarRepuestoObjeto==='function') agregarRepuestoObjeto({codigo:r.codigo||'',detalle:r.detalle||'',unidad:''}, '1', true);
          else if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.agregar==='function') window.zgRepuestosTablaFinal.agregar({codigo:r.codigo||'',detalle:r.detalle||'',cantidad:'1'}, true);
        }catch(e){console.warn(e);}
        const input=byId('repuestoSearch'); if(input) input.value='';
        menu.classList.remove('show');
        return false;
      }
      b.addEventListener('click', usar, true);
      menu.appendChild(b);
    });
    menu.classList.add('show');
  }
  function mostrarRepuestosNuevo(){
    const input=byId('repuestoSearch');
    const q=input ? input.value : '';
    const items=repuestosFiltradosNuevo(q);
    renderMenu(items);
    const hint=byId('repuestoHint');
    if(hint){
      const key=controladorKey();
      if(!key) hint.textContent='Selecciona marca y controlador para cargar el catálogo correcto. El técnico solo podrá elegir de la lista.';
      else hint.textContent='Catálogo cargado para '+key+'. Selecciona “usar”; no se permite crear materiales desde el técnico.';
    }
  }
  function bloquearManual(){
    const btn=byId('repuestoAddManual'); if(btn){btn.style.display='none'; btn.disabled=true; btn.setAttribute('aria-hidden','true');}
    const input=byId('repuestoSearch');
    if(input){
      input.placeholder='Buscar material según controlador seleccionado';
      input.removeEventListener('input', window.__zgMatInput||(()=>{}));
      window.__zgMatInput = function(){mostrarRepuestosNuevo();};
      input.addEventListener('input', window.__zgMatInput, true);
      input.addEventListener('focus', mostrarRepuestosNuevo, true);
    }
  }
  try{
    window.repuestosFiltrados = repuestosFiltradosNuevo;
    window.mostrarRepuestos = mostrarRepuestosNuevo;
    window.agregarRepuestoManual = function(){toastSafe('El técnico solo puede seleccionar materiales del catálogo. Agrega nuevos desde el panel.'); mostrarRepuestosNuevo(); return false;};
    window.registrarRepuestosTecnico = async function(){ return true; };
  }catch(e){}
  document.addEventListener('click', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoAddManual')){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
      toastSafe('Esta opción fue deshabilitada. Los materiales nuevos se agregan desde el panel.'); return false;
    }
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoSearch')){ setTimeout(mostrarRepuestosNuevo, 10); }
  }, true);
  document.addEventListener('keydown', function(ev){
    if(ev.target && ev.target.id==='repuestoSearch' && ev.key==='Enter'){ ev.preventDefault(); mostrarRepuestosNuevo(); return false; }
  }, true);
  ['controladorEquipo','marcaEquipo'].forEach(id=>{
    document.addEventListener('change', function(ev){ if(ev.target && ev.target.id===id) setTimeout(mostrarRepuestosNuevo,80); }, true);
    document.addEventListener('input', function(ev){ if(ev.target && ev.target.id===id) setTimeout(mostrarRepuestosNuevo,120); }, true);
  });
  window.addEventListener('load', function(){ bloquearManual(); setTimeout(bloquearManual,700); setTimeout(bloquearManual,1800); });
  document.addEventListener('DOMContentLoaded', bloquearManual);
  window.ZG_CATALOGOS_POR_CONTROLADOR = ZG_CATALOGOS_POR_CONTROLADOR;
})();
</script>


<style id="zg-unidades-materiales-css-final">
  .zg-rep-unit2{display:inline-flex;align-items:center;justify-content:center;min-width:62px;border:1.5px solid #cfe0f3;background:#eef6ff;color:#17385d;border-radius:999px;padding:8px 10px;font-family:'Archivo',system-ui,sans-serif;font-size:12px;font-weight:900;white-space:nowrap}
  .zg-rep-unit-help{display:block;margin-top:5px;color:#6b7d92;font-size:11px;font-weight:800;line-height:1.2}
  #repuestoSuggest .smart-sub .unit-pill{display:inline-flex;margin-left:6px;background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;border-radius:999px;padding:2px 7px;font-size:11px;font-weight:900}
  @media(max-width:720px){.zg-rep-unit2{justify-content:flex-start;min-width:90px}.zg-rep-unit-help{font-size:11.5px}}
</style>
<script id="zg-unidades-materiales-js-final">
(function(){
  function byId(id){return document.getElementById(id);}
  function clean(s){return String(s==null?'':s).replace(/\s+/g,' ').trim();}
  function norm(s){return clean(s).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');}
  function esc(s){return clean(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function inferUnidad(detalle, codigo){
    const d = norm((detalle||'')+' '+(codigo||''));
    if(/\bM3\b|M³|10\s*M3/.test(d)) return 'm³';
    if(/\bM2\b|M²/.test(d) || /EMPAQUE|ASBESTO|PLANCHA|LAMINA|PLATE/.test(d) && /\d+(?:[\.,]\d+)?\s*X\s*\d+(?:[\.,]\d+)?\s*M\b/.test(d)) return 'm²';
    if(/\bKG\b|KGS|KILOS?|13[\.,]60KG|4[\.,]54\s*KG/.test(d)) return 'kg';
    if(/\bML\b|590ML|600ML|540\s*GR/.test(d)) return 'ml';
    if(/LITRO|LITROS|\b\d+[\.,]?\d*\s*L\b/.test(d)) return 'L';
    if(/CABLE|TUBERIA|TUBO DE COBRE|MANGUERA|TERMOCONTRAIBLE|RIEL DIN|CINTA FOAM|CINTA VULCANIZANTE|CINTA PARA DUCTO|EXTENSION CORRIENTE|CAÑA DE SOLDAR/.test(d)) return 'm';
    if(/REFRIGERANTE|NITROGENO/.test(d)){
      if(/M3|M³/.test(d)) return 'm³';
      if(/KG/.test(d)) return 'kg';
      return 'und';
    }
    if(/ACEITE|SOLVENTE|LIMPIA CONTACTO|JABON LIQUIDO|THINNER|PINTURA|SPRAY|ADHESIVO|SILICON|FUNDENTE|FORMADOR|GRASA/.test(d)) return 'und';
    return 'und';
  }
  window.zgInferUnidadMaterial = inferUnidad;

  function materiales(){
    try{return (window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function') ? window.zgRepuestosTablaFinal.materiales() : [];}catch(e){return [];}
  }
  function linea(m){
    const cantidad = String(m.cantidad||'1').replace(/[^0-9]/g,'').slice(0,4) || '1';
    const unidad = clean(m.unidad || inferUnidad(m.detalle,m.codigo) || 'und');
    return (clean(m.codigo)||'-')+' | '+clean(m.detalle)+' | '+cantidad+' | '+unidad;
  }
  function repararCodigosMateriales(arr){
    arr=Array.isArray(arr)?arr:[];
    let catalogo=[];
    try{ catalogo=catalogoActivo(); }catch(e){ catalogo=[]; }
    const key=function(v){return norm(v||'').replace(/\s+/g,' ').trim();};
    arr.forEach(function(m){
      if(!m) return;
      const detalleKey=key(m.detalle);
      let found=catalogo.find(function(r){return key(r.detalle)===detalleKey;});
      if(!found && detalleKey){
        found=catalogo.find(function(r){
          const rk=key(r.detalle);
          return rk && (detalleKey.includes(rk) || rk.includes(detalleKey));
        });
      }
      if(found){
        m.codigo=clean(found.codigo||m.codigo||'');
        m.detalle=clean(found.detalle||m.detalle||'');
        m.unidad=clean(found.unidad||m.unidad||inferUnidad(m.detalle,m.codigo)||'und');
      }else if(!clean(m.codigo)){
        const candidates=(Array.isArray(REPUESTOS_GENSET_CATALOGO)?REPUESTOS_GENSET_CATALOGO:[]);
        const alt=candidates.find(function(r){return key(r.detalle)===detalleKey;});
        if(alt){m.codigo=clean(alt.codigo||'');m.unidad=clean(alt.unidad||m.unidad||'und');}
      }
    });
    return arr;
  }
  window.zgRepararCodigosMateriales=repararCodigosMateriales;
  function guardarConUnidad(){
    const arr = repararCodigosMateriales(materiales());
    arr.forEach(m=>{m.unidad = clean(m.unidad || inferUnidad(m.detalle,m.codigo) || 'und');});
    const ta=byId('repuestosManual');
    if(ta){ta.value = arr.filter(x=>clean(x.detalle)).map(linea).join('\n'); ta.classList.remove('input-error');}
    try{window.repuestosSeleccionados = arr.map(x=>({codigo:clean(x.codigo), detalle:clean(x.detalle), cantidad:String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1', unidad:clean(x.unidad||inferUnidad(x.detalle,x.codigo)), nuevo:!clean(x.codigo)}));}catch(e){}
  }
  function pintarConUnidad(){
    const arr = repararCodigosMateriales(materiales());
    const box=byId('repuestosSelectedList');
    const empty=byId('repuestosEmpty');
    if(!box) return;
    arr.forEach(m=>{m.unidad = clean(m.unidad || inferUnidad(m.detalle,m.codigo) || 'und');});
    box.classList.add('zg-table-ready');
    box.innerHTML='';
    if(empty) empty.classList.toggle('show', arr.length===0);
    if(!arr.length){guardarConUnidad(); return;}
    const count=document.createElement('div');
    count.className='zg-rep-count2';
    count.textContent=arr.length+' material(es) seleccionado(s)';
    box.appendChild(count);
    const wrap=document.createElement('div'); wrap.className='zg-repuestos-table-wrap';
    const table=document.createElement('table'); table.className='zg-repuestos-table';
    table.innerHTML='<thead><tr><th style="width:135px">Código</th><th>Material / repuesto</th><th style="width:105px">Cantidad</th><th style="width:100px">Unidad</th><th style="width:56px"></th></tr></thead><tbody></tbody>';
    const tbody=table.querySelector('tbody');
    arr.forEach(function(it,idx){
      const tr=document.createElement('tr');
      const cod=clean(it.codigo)||'Sin código';
      const unidad=clean(it.unidad||inferUnidad(it.detalle,it.codigo));
      tr.innerHTML='<td data-label="Código"><div class="zg-rep-code2 '+(!clean(it.codigo)?'empty':'')+'">'+esc(cod)+'</div></td>'+
        '<td data-label="Material"><input class="zg-rep-detail2" type="text" value="'+esc(it.detalle)+'" placeholder="Nombre del material"><div class="zg-rep-tip2">Puedes corregir el nombre antes de generar el PDF.</div></td>'+
        '<td data-label="Cantidad"><input class="zg-rep-qty2" type="text" inputmode="numeric" value="'+esc(it.cantidad||'1')+'"></td>'+
        '<td data-label="Unidad"><span class="zg-rep-unit2">'+esc(unidad)+'</span><span class="zg-rep-unit-help">Asignado según material</span></td>'+
        '<td><button type="button" class="zg-rep-del2" title="Quitar">×</button></td>';
      const det=tr.querySelector('.zg-rep-detail2'); const qty=tr.querySelector('.zg-rep-qty2'); const del=tr.querySelector('.zg-rep-del2');
      det.addEventListener('input',function(){arr[idx].detalle=clean(det.value); arr[idx].unidad=inferUnidad(arr[idx].detalle,arr[idx].codigo); guardarConUnidad(); pintarConUnidad();});
      qty.addEventListener('input',function(){let v=String(qty.value||'').replace(/[^0-9]/g,'').slice(0,4); qty.value=v; arr[idx].cantidad=v; guardarConUnidad();});
      qty.addEventListener('focus',function(){setTimeout(function(){try{qty.select();}catch(e){}},30);});
      qty.addEventListener('blur',function(){if(!String(qty.value||'').trim()){qty.value='1'; arr[idx].cantidad='1'; guardarConUnidad();}});
      del.addEventListener('click',function(){arr.splice(idx,1); guardarConUnidad(); pintarConUnidad();});
      tbody.appendChild(tr);
    });
    wrap.appendChild(table); box.appendChild(wrap); guardarConUnidad();
  }
  function keyControlador(){
    const marca=norm(byId('marcaEquipo')&&byId('marcaEquipo').value||'');
    const ctrl=norm(byId('controladorEquipo')&&byId('controladorEquipo').value||'');
    const joined=marca+' '+ctrl;
    if((window.zgGetEquipmentType&&window.zgGetEquipmentType()==='Genset') || joined.includes('SG-3000') || joined.includes('SG 3000') || joined.includes('SG-5000') || joined.includes('SG 5000')){
      if(joined.includes('SG-3000') || joined.includes('SG 3000')) return 'GENSET SG-3000';
      if(joined.includes('SG-5000') || joined.includes('SG 5000')) return 'GENSET SG-5000';
    }
    if(joined.includes('STAR COOL') && (joined.includes('CIM 6') || joined.includes('CIM6'))) return 'STAR COOL CIM 6';
    if(joined.includes('STAR COOL') && (joined.includes('CIM 5') || joined.includes('CIM5'))) return 'STAR COOL CIM 5';
    if(joined.includes('MP5000') || joined.includes('MP 5000')) return 'TK MP5000';
    if(joined.includes('MP4000') || joined.includes('MP 4000')) return 'TK MP4000';
    if(joined.includes('CARRIER')) return 'CARRIER';
    if(joined.includes('DAIKIN')) return 'DAIKIN';
    return '';
  }
  function panelCatalogo(){
    try{return (Array.isArray(window.REPUESTOS_CATALOGO)?window.REPUESTOS_CATALOGO:[]).map(r=>({codigo:clean(r.codigo||''), detalle:clean(r.detalle||''), unidad:clean(r.unidad||'')})).filter(r=>r.detalle);}catch(e){return [];}
  }
  function catalogoActivo(){
    const k=keyControlador();
    let arr=k && window.ZG_CATALOGOS_POR_CONTROLADOR ? (window.ZG_CATALOGOS_POR_CONTROLADOR[k]||[]) : [];
    // No mezclamos el catálogo general del panel aquí para evitar repuestos duplicados o pegados.
    // Esta pantalla carga solo por marca/controlador seleccionado.
    const seen=new Set(); const out=[];
    arr.forEach(x=>{const codigo=clean(x.codigo||''); const detalle=clean(x.detalle||''); if(!detalle) return; const unidad=clean(x.unidad||inferUnidad(detalle,codigo)); const key=(codigo+'|'+detalle).toUpperCase(); if(seen.has(key)) return; seen.add(key); out.push({codigo,detalle,unidad});});
    return out;
  }
  function renderMenuUnidad(items){
    const menu=byId('repuestoSuggest'); if(!menu) return;
    menu.innerHTML='';
    const k=keyControlador();
    if(!k){menu.innerHTML='<div class="smart-option" style="cursor:default"><div><span class="smart-main">Selecciona primero la marca y el controlador del equipo.</span><span class="smart-sub">Luego aparecerán materiales con su unidad de medida.</span></div></div>'; menu.classList.add('show'); return;}
    if(!items.length){menu.innerHTML='<div class="smart-option" style="cursor:default"><div><span class="smart-main">No hay coincidencias para este controlador.</span><span class="smart-sub">El técnico solo puede seleccionar del catálogo.</span></div></div>'; menu.classList.add('show'); return;}
    items.slice(0,80).forEach(r=>{
      const unidad=clean(r.unidad||inferUnidad(r.detalle,r.codigo));
      const b=document.createElement('button'); b.type='button'; b.className='smart-option';
      b.innerHTML='<div><span class="smart-main">'+esc(r.detalle)+'</span><span class="smart-sub">'+esc(r.codigo?'Código: '+r.codigo:'Registrado en panel')+' <span class="unit-pill">'+esc(unidad)+'</span></span></div><span class="smart-badge">usar</span>';
      function usar(ev){ev.preventDefault();ev.stopPropagation();if(ev.stopImmediatePropagation)ev.stopImmediatePropagation(); try{window.agregarRepuestoObjeto({codigo:r.codigo||'',detalle:r.detalle||'',unidad:unidad},'1',true);}catch(e){} menu.classList.remove('show'); const input=byId('repuestoSearch'); if(input) input.value=''; setTimeout(pintarConUnidad,30); return false;} b.addEventListener('click',usar,true); menu.appendChild(b);
    });
    menu.classList.add('show');
  }
  function mostrarConUnidad(){
    const q=norm(byId('repuestoSearch')&&byId('repuestoSearch').value||'');
    const items=catalogoActivo().filter(r=>!q || norm(r.codigo).includes(q) || norm(r.detalle).includes(q)).sort((a,b)=>String(a.detalle).localeCompare(String(b.detalle),'es',{numeric:true,sensitivity:'base'}));
    renderMenuUnidad(items);
    const hint=byId('repuestoHint'); if(hint) hint.textContent='Catálogo con unidad automática. Selecciona “usar” y ajusta solo la cantidad.';
  }
  function instalar(){
    if(window.ZG_CATALOGOS_POR_CONTROLADOR){Object.keys(window.ZG_CATALOGOS_POR_CONTROLADOR).forEach(k=>{(window.ZG_CATALOGOS_POR_CONTROLADOR[k]||[]).forEach(r=>{r.unidad=clean(r.unidad||inferUnidad(r.detalle,r.codigo));});});}
    const oldAdd=window.agregarRepuestoObjeto;
    if(typeof oldAdd==='function' && !oldAdd.__zgUnidad){
      const nuevo=function(r,cantidad,aviso){
        r=r||{}; r.unidad=clean(r.unidad||inferUnidad(r.detalle,r.codigo));
        const res=oldAdd.call(this,r,cantidad,aviso);
        setTimeout(function(){materiales().forEach(m=>{m.unidad=clean(m.unidad||inferUnidad(m.detalle,m.codigo));}); pintarConUnidad();},30);
        return res;
      };
      nuevo.__zgUnidad=true; window.agregarRepuestoObjeto=nuevo;
    }
    if(window.zgRepuestosTablaFinal){window.zgRepuestosTablaFinal.pintar=pintarConUnidad; window.zgRepuestosTablaFinal.guardar=guardarConUnidad;}
    window.renderRepuestosSeleccionados=pintarConUnidad;
    window.syncRepuestosManual=guardarConUnidad;
    window.mostrarRepuestos=mostrarConUnidad;
    window.repuestosFiltrados=function(q){const nq=norm(q||''); return catalogoActivo().filter(r=>!nq||norm(r.codigo).includes(nq)||norm(r.detalle).includes(nq));};
    const input=byId('repuestoSearch');
    if(input && !input.dataset.zgUnidadOk){input.dataset.zgUnidadOk='1'; input.placeholder='Buscar material según controlador seleccionado'; input.addEventListener('focus',function(){setTimeout(mostrarConUnidad,60);},true); input.addEventListener('input',function(){setTimeout(mostrarConUnidad,60);},true);}
    pintarConUnidad();
  }
  document.addEventListener('click',function(ev){
    if(ev.target && ev.target.closest && (ev.target.closest('#pdfBtn') || ev.target.closest('#preBtn'))){guardarConUnidad();}
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoSearch')) setTimeout(mostrarConUnidad,80);
  },true);
  document.addEventListener('keydown',function(ev){if(ev.target && ev.target.id==='repuestoSearch' && ev.key==='Enter'){ev.preventDefault(); mostrarConUnidad(); return false;}},true);
  window.addEventListener('load',function(){instalar(); setTimeout(instalar,700); setTimeout(instalar,1800);});
  document.addEventListener('DOMContentLoaded',instalar);
})();
</script>

<style id="zg-final-cleanup-redaccion-evidencias">
  .salida-supervision-card,#salidaSupervisionCard{display:none!important;}
  #repuestoAddManual{display:none!important;}
  #repuestoHint{font-weight:800;color:#5c6e84;}
  .zg-redactor-help{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:8px;background:#f4f9ff;border:1px solid #d6e7fb;border-radius:14px;padding:9px 10px;color:#40576f;font-size:12px;font-weight:800;}
  .zg-redactor-help label{display:inline-flex;align-items:center;gap:6px;margin:0!important;color:#17385d;font-weight:900;font-size:12px;}
  .zg-redactor-help input{width:auto!important;min-height:0!important;}
  .zg-redactor-help button{border:none;background:#1f6fc4;color:#fff;border-radius:999px;padding:8px 12px;font-family:Archivo,system-ui,sans-serif;font-weight:900;font-size:12px;cursor:pointer;box-shadow:0 8px 18px rgba(31,111,196,.18)}
  .zg-redactor-help button:disabled{opacity:.55;cursor:not-allowed;box-shadow:none;}
  .zg-redactor-help small{color:#6c7f96;font-weight:800;line-height:1.25;}
  @media(max-width:640px){.zg-redactor-help{align-items:flex-start}.zg-redactor-help button{width:100%;min-height:42px}}
</style>
<script id="zg-final-cleanup-redaccion-evidencias-js">
(function(){
  function byId(id){return document.getElementById(id);}
  function clean(s){return String(s==null?'':s).replace(/\s+/g,' ').trim();}
  function toastSafe(t){try{ if(typeof toast==='function') toast(t); else console.log(t); }catch(e){console.log(t);}}

  // Quitar la tarjeta de preparación técnica de esta pantalla. Esa parte será otro módulo.
  function quitarSalida(){
    document.querySelectorAll('.salida-supervision-card,#salidaSupervisionCard').forEach(function(x){x.remove();});
  }
  window.renderSalidaSupervision = function(){};
  document.addEventListener('DOMContentLoaded', quitarSalida);
  window.addEventListener('load', function(){quitarSalida(); setTimeout(quitarSalida,600); setTimeout(quitarSalida,1600);});

  // Evidencias preliminares: conservarlas al pasar de modo=cliente al enlace con token.
  function tokenActual(){
    const url = new URL(window.location.href);
    return clean(url.searchParams.get('token') || (byId('tokenContinuacion')&&byId('tokenContinuacion').value) || '');
  }
  function reporteActual(){return clean((byId('orden')&&byId('orden').value) || '');}
  function evKeyToken(t){return 'zg_pre_evidencias_token_'+t;}
  function evKeyReporte(r){return 'zg_pre_evidencias_reporte_'+r;}
  function readStored(){
    const t=tokenActual(), r=reporteActual();
    let raw='';
    if(!t && !r) return [];
    try{
      if(t) raw=localStorage.getItem(evKeyToken(t))||'';
      if(!raw && r) raw=localStorage.getItem(evKeyReporte(r))||'';
      if(!raw) raw=sessionStorage.getItem('zg_pre_evidencias_pending')||'';
      const arr=raw?JSON.parse(raw):[];
      return Array.isArray(arr)?arr:[];
    }catch(e){return [];}
  }
  function saveStored(){
    const arr = Array.isArray(window.ZG_PRE_EVIDENCIAS) ? window.ZG_PRE_EVIDENCIAS : [];
    if(!arr.length) return;
    try{
      const data=JSON.stringify(arr);
      sessionStorage.setItem('zg_pre_evidencias_pending', data);
      const r=reporteActual(); if(r) localStorage.setItem(evKeyReporte(r), data);
      const t=tokenActual(); if(t) localStorage.setItem(evKeyToken(t), data);
    }catch(e){console.warn('No se pudieron guardar evidencias preliminares en el navegador', e);}
  }
  function restoreStored(){
    const arr=readStored();
    if(arr.length){
      window.ZG_PRE_EVIDENCIAS = arr;
      if(typeof window.renderPreEvidenceGrid==='function') window.renderPreEvidenceGrid();
      try{ if(typeof updateCounter==='function') updateCounter(); }catch(e){}
    }
    window.__zgPreEvidenceRestored = true;
  }
  function wrapEvidenceRender(){
    if(typeof window.renderPreEvidenceGrid==='function' && !window.renderPreEvidenceGrid.__zgPersist){
      const old=window.renderPreEvidenceGrid;
      window.renderPreEvidenceGrid=function(){
        const r=old.apply(this,arguments);
        if(window.__zgPreEvidenceRestored) saveStored();
        return r;
      };
      window.renderPreEvidenceGrid.__zgPersist=true;
    }
  }
  function wrapGuardarPreliminar(){
    const fn = window.guardarPreinspeccion || (typeof guardarPreinspeccion==='function' ? guardarPreinspeccion : null);
    if(typeof fn==='function' && !fn.__zgEvPersist){
      const nuevo = async function(){
        saveStored();
        const p1=setTimeout(saveStored,300), p2=setTimeout(saveStored,800), p3=setTimeout(saveStored,1200);
        try{return await fn.apply(this,arguments);} finally {setTimeout(saveStored,450); setTimeout(saveStored,900);}
      };
      nuevo.__zgEvPersist=true;
      window.guardarPreinspeccion = nuevo;
      try{ guardarPreinspeccion = nuevo; }catch(e){}
    }
  }
  document.addEventListener('click',function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#preBtn')){ saveStored(); setTimeout(saveStored,500); setTimeout(saveStored,1000); }
  },true);
  document.addEventListener('change',function(ev){
    if(ev.target && ev.target.id==='preEvidenceInput'){ setTimeout(saveStored,700); setTimeout(saveStored,1800); }
    if(ev.target && ev.target.id==='orden'){ setTimeout(saveStored,100); }
  },true);

  // Ayuda de redacción para campos técnicos. Solo actúa cuando el técnico la activa y presiona el botón.
  function etiquetaDeCampo(el){
    const id=el.id;
    if(id){ const lab=document.querySelector('label[for="'+CSS.escape(id)+'"]'); if(lab) return clean(lab.textContent); }
    const f=el.closest('.field'); if(f){ const lab=f.querySelector('label'); if(lab) return clean(lab.textContent); }
    return 'Observación técnica';
  }
  function capitalizar(s){return s ? s.charAt(0).toUpperCase()+s.slice(1) : s;}
  function normalizarTextoBase(txt){
    let s=clean(txt)
      .replace(/\bq\b/gi,'que').replace(/\bxq\b/gi,'porque').replace(/\bpq\b/gi,'porque')
      .replace(/\bsta\b/gi,'está').replace(/\beq\b/gi,'equipo')
      .replace(/\btemp\b/gi,'temperatura').replace(/\brefri\b/gi,'refrigerante')
      .replace(/\s*([,.;:])\s*/g,'$1 ');
    s=s.replace(/\s+/g,' ').trim();
    if(s && !/[.!?]$/.test(s)) s+='.';
    return capitalizar(s);
  }
  function zgIaMaterialNormalizado(raw){
    raw=raw||{};
    let codigo=clean(raw.codigo||raw.code||'');
    let detalle=clean(raw.detalle||raw.material||raw.nombre||raw.descripcion||'');
    let cantidad=clean(raw.cantidad||raw.qty||'1')||'1';
    let unidad=clean(raw.unidad||raw.unit||'und')||'und';
    if(/^sin código$/i.test(codigo)) codigo='';
    if(!detalle) return null;
    return {codigo,detalle,cantidad,unidad};
  }
  function zgIaAgregarMaterial(out,seen,raw){
    const m=zgIaMaterialNormalizado(raw);
    if(!m) return;
    const key=(m.codigo+'|'+m.detalle+'|'+m.cantidad+'|'+m.unidad).toUpperCase();
    if(seen.has(key)) return;
    seen.add(key); out.push(m);
  }
  function zgIaMaterialesDelTexto(txt,out,seen){
    String(txt||'').split(/\r?\n/).forEach(function(linea){
      const p=linea.split('|').map(clean);
      if(p.length>=4){
        zgIaAgregarMaterial(out,seen,{codigo:p[0]==='-'?'':p[0],detalle:p.slice(1,-2).join(' | '),cantidad:p[p.length-2],unidad:p[p.length-1]});
      }else if(p.length===3){
        zgIaAgregarMaterial(out,seen,{codigo:p[0]==='-'?'':p[0],detalle:p[1],cantidad:p[2],unidad:'und'});
      }else if(p.length===2){
        zgIaAgregarMaterial(out,seen,{codigo:p[0]==='-'?'':p[0],detalle:p[1],cantidad:'1',unidad:'und'});
      }else if(p.length===1 && p[0]){
        zgIaAgregarMaterial(out,seen,{detalle:p[0],cantidad:'1',unidad:'und'});
      }
    });
  }
  function materialesSeleccionadosParaIa(el){
    const out=[],seen=new Set();
    const panel=el && el.closest ? el.closest('.panel') : null;

    // Fuente principal: estado exacto del trabajo que contiene el campo de texto.
    try{
      const st=panel && panel.__zgWorkState;
      if(st && Array.isArray(st.repuestosTrabajo)) st.repuestosTrabajo.forEach(function(m){zgIaAgregarMaterial(out,seen,m);});
      ['materialesTrabajo','materiales','repuestos'].forEach(function(k){
        if(st && Array.isArray(st[k])) st[k].forEach(function(m){zgIaAgregarMaterial(out,seen,m);});
      });
    }catch(e){}

    // Respaldo visual: tabla de materiales dentro del mismo trabajo.
    if(panel){
      panel.querySelectorAll('.zg-work-material-table tbody tr').forEach(function(tr){
        if(tr.querySelector('.zg-work-material-empty')) return;
        const codigo=clean(tr.querySelector('[data-label="Código"] b,[data-label="Código"],td:nth-child(1)')?.textContent||'');
        const detalle=clean(tr.querySelector('.detail,.zg-rep-detail2,[data-label="Material"] input,td:nth-child(2) input')?.value || tr.querySelector('[data-label="Material"],td:nth-child(2)')?.textContent || '');
        const cantidad=clean(tr.querySelector('.qty,.zg-rep-qty2,[data-label="Cantidad"] input,td:nth-child(3) input')?.value || tr.querySelector('[data-label="Cantidad"],td:nth-child(3)')?.textContent || '1');
        const unidad=clean(tr.querySelector('.unit,[data-label="Unidad"],td:nth-child(4)')?.textContent||'und');
        zgIaAgregarMaterial(out,seen,{codigo,detalle,cantidad,unidad});
      });
    }

    // Respaldo general: tabla global y textarea técnico de materiales.
    document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr').forEach(function(tr){
      const codigo=clean(tr.querySelector('.zg-rep-code2,[data-label="Código"],td:nth-child(1)')?.textContent||'');
      const detalle=clean(tr.querySelector('.zg-rep-detail2,[data-label="Material"] input,td:nth-child(2) input')?.value || tr.querySelector('[data-label="Material"],td:nth-child(2)')?.textContent || '');
      const cantidad=clean(tr.querySelector('.zg-rep-qty2,[data-label="Cantidad"] input,td:nth-child(3) input')?.value || tr.querySelector('[data-label="Cantidad"],td:nth-child(3)')?.textContent || '1');
      const unidad=clean(tr.querySelector('[data-label="Unidad"],td:nth-child(4)')?.textContent||'und');
      zgIaAgregarMaterial(out,seen,{codigo,detalle,cantidad,unidad});
    });
    const ta=document.getElementById('repuestosManual');
    if(ta) zgIaMaterialesDelTexto(ta.value,out,seen);
    return out.slice(0,40);
  }
  function datosAnterioresParaIa(el){
    const out=[];
    const add=function(label,value){
      const v=clean(value);
      if(v) out.push(label+': '+v);
    };
    const val=function(id){return clean(document.getElementById(id)?.value||'');};
    add('N° de reporte',val('orden'));
    add('Cliente',val('cliente'));
    add('Tipo de equipo',val('zgTipoEquipo'));
    add('Contenedor o equipo',val('equipoNo'));
    add('Serie',val('serialUnidad'));
    add('Marca',val('marcaEquipo'));
    add('Modelo',val('modeloEquipo'));
    add('Controlador',val('controladorEquipo'));
    add('Año de fabricación',val('anioFabricacion'));
    add('Refrigerante',val('refrigerante'));
    add('Set point',val('setPoint'));
    add('Temperatura ambiente',val('temperaturaAmbiente'));
    add('Retorno de aire',val('retornoAire'));
    add('Suministro de aire',val('suministroAire'));
    add('Presión alta',val('presionAlta'));
    add('Presión baja',val('presionBaja'));
    add('Voltaje L1-L2',val('voltajeL1L2'));
    add('Voltaje L2-L3',val('voltajeL2L3'));
    add('Voltaje L1-L3',val('voltajeL1L3'));
    add('Estado inicial',val('estadoInicial'));
    add('Alarma encontrada',val('alarmaEncontrada'));
    add('Observación inicial',val('observacionInicial'));

    const panel=el && el.closest ? el.closest('.panel') : null;
    const st=panel && panel.__zgWorkState;
    if(st){
      add('Trabajo actual',st.nombre);
      const currentId=String(el && el.id || '');
      Object.entries(st.campos||{}).forEach(function(entry){
        const k=entry[0],v=entry[1];
        if(currentId && currentId===('campo_'+st.id+'_'+k)) return;
        add('Dato previo del trabajo - '+k.replace(/_/g,' '),v);
      });
      const defs=(typeof window.zgGetReeferChecklistItemsForWork==='function')?(window.zgGetReeferChecklistItemsForWork(st)||[]):[];
      const values=(st.reeferChecklist&&typeof st.reeferChecklist==='object')?st.reeferChecklist:{};
      defs.forEach(function(d){
        const key=String(d.key ?? d.n);
        const raw=values[key] ?? values[String(d.n)] ?? '';
        let result='';
        if(d.kind==='three'){
          const v=raw&&typeof raw==='object'?raw:{};
          result=[clean(v.l1)?('L1 '+clean(v.l1)+' '+(d.unit||'')):'',clean(v.l2)?('L2 '+clean(v.l2)+' '+(d.unit||'')):'',clean(v.l3)?('L3 '+clean(v.l3)+' '+(d.unit||'')):''].filter(Boolean).join(', ');
        }else if(d.kind==='okvolt'){
          const v=raw&&typeof raw==='object'?raw:{};
          result=clean(v.modo)==='OK'?'OK':(clean(v.modo)==='V'&&clean(v.valor)?clean(v.valor)+' V':clean(raw&&typeof raw==='object'?raw.valor:raw));
        }else result=clean(raw&&typeof raw==='object'?raw.valor:raw);
        if(result) add('Inspección '+d.n+' - '+d.label,result+(d.unit&&d.kind!=='three'&&d.kind!=='okvolt'&&!result.toLowerCase().includes(String(d.unit).toLowerCase())?' '+d.unit:''));
      });
    }
    return out.slice(0,80);
  }
  function contextoSeleccionado(el){
    const panel=el && el.closest ? el.closest('.panel') : null;
    const titulo=clean(panel?.querySelector('.panel-head .ttl')?.textContent || '');
    const grupos={actividades:[],hallazgos:[],acciones:[]};
    if(panel){
      panel.querySelectorAll('.quick-group').forEach(function(g){
        const nombre=clean(g.querySelector('.quick-title')?.textContent || '').toLowerCase();
        const valores=Array.from(g.querySelectorAll('.qchip.on')).map(function(x){return clean(x.textContent);}).filter(Boolean);
        if(nombre.includes('actividad')) grupos.actividades=valores;
        else if(nombre.includes('hallazgo')) grupos.hallazgos=valores;
        else if(nombre.includes('acción') || nombre.includes('accion')) grupos.acciones=valores;
      });
    }
    let memoriaTrabajo=[];
    try{
      if(st&&typeof window.zgOpcionesTecnicasTrabajo==='function'){
        memoriaTrabajo=[].concat(
          window.zgOpcionesTecnicasTrabajo('actividades',st)||[],
          window.zgOpcionesTecnicasTrabajo('hallazgos',st)||[]
        ).slice(0,40);
      }
    }catch(e){}
    return {titulo,grupos,materiales:materialesSeleccionadosParaIa(el),antecedentes:datosAnterioresParaIa(el),memoriaTrabajo};
  }
  function fraseLista(prefijo, arr){
    if(!Array.isArray(arr) || !arr.length) return '';
    return prefijo+' '+arr.join(', ')+'.';
  }
  function mejorarSegunCampo(label, txt, el){
    const l=label.toLowerCase();
    const base=normalizarTextoBase(txt);
    if(!base) return '';
    const ctx=contextoSeleccionado(el);
    const partes=[];
    const esPreventivo=l.includes('mantenimiento preventivo') || ctx.titulo.toLowerCase().includes('preventivo');
    const esCorrectivo=l.includes('mantenimiento correctivo') || ctx.titulo.toLowerCase().includes('correctivo');

    if(esPreventivo || esCorrectivo){
      const tipoEquipo=clean(document.getElementById('zgTipoEquipo')?.value || '').toLowerCase();
      const equipoTexto=(tipoEquipo.includes('genset') || tipoEquipo.includes('generador')) ? 'del generador' : 'de la máquina reefer';
      partes.push('Durante el '+(esPreventivo?'mantenimiento preventivo':'mantenimiento correctivo')+' '+equipoTexto+' se registró la intervención técnica correspondiente.');
      const a=fraseLista('Las actividades ejecutadas fueron:',ctx.grupos.actividades); if(a) partes.push(a);
      const h=fraseLista('Durante la revisión se identificaron los siguientes hallazgos:',ctx.grupos.hallazgos); if(h) partes.push(h);
      const ac=fraseLista('Las acciones realizadas fueron:',ctx.grupos.acciones); if(ac) partes.push(ac);
      partes.push('Como detalle complementario, '+base.charAt(0).toLowerCase()+base.slice(1));
      return partes.join(' ');
    }
    if(l.includes('problema')) return 'El cliente reportó la siguiente condición: '+base;
    if(l.includes('diagn')) return 'Durante la evaluación técnica se identificó la siguiente condición: '+base;
    if(l.includes('soluci') || l.includes('acción') || l.includes('accion')) return 'Como parte de la intervención se ejecutó la siguiente acción técnica: '+base;
    if(l.includes('observación inicial') || l.includes('observacion inicial')) return 'Antes de iniciar la intervención, el equipo fue encontrado en la siguiente condición: '+base;
    if((l.includes('razón') || l.includes('razon') || l.includes('motivo')) && l.includes('mantenimiento')) return 'Se recomienda programar una intervención adicional debido a la siguiente condición técnica: '+base;
    if(l.includes('observación final') || l.includes('observacion final') || l.includes('funcionamiento')) return 'Al concluir el servicio, se registró la siguiente condición operativa: '+base;
    if(l.includes('recomend')) return 'Como recomendación técnica se indica lo siguiente: '+base;
    if(l.includes('hallazgo')) return 'Durante la inspección se registró el siguiente hallazgo técnico: '+base;
    if(l.includes('detalle técnico') || l.includes('detalle tecnico')) return 'Como detalle técnico de la intervención se registró lo siguiente: '+base;
    return 'Durante la intervención técnica se registró lo siguiente: '+base;
  }
  function instalarAyudaTextos(){
    document.querySelectorAll('textarea').forEach(function(ta){
      if(!ta || ta.dataset.zgRedactorOk) return;
      if(ta.id==='repuestosManual') return;
      if(ta.closest('.pre-ev-item')) return;
      const field=ta.closest('.field');
      if(!field) return;
      ta.dataset.zgRedactorOk='1';
      const wrap=document.createElement('div');
      wrap.className='zg-redactor-help';
      wrap.dataset.zgFor = ta.id || '';
      wrap.innerHTML='<label><input type="checkbox"> Activar ayuda</label><button type="button" disabled>Mejorar redacción</button><small>Organiza tus apuntes en una redacción técnica clara y completa.</small>';
      const chk=wrap.querySelector('input');
      const btn=wrap.querySelector('button');
      chk.addEventListener('change',function(){btn.disabled=!chk.checked;});
      btn.addEventListener('click',async function(){
        if(!chk.checked || btn.dataset.loading==='1') return;
        const original=clean(ta.value);
        const etiqueta=etiquetaDeCampo(ta);
        const ctx=contextoSeleccionado(ta);
        const hayContexto=(ctx.grupos.actividades.length||ctx.grupos.hallazgos.length||ctx.grupos.acciones.length||ctx.materiales.length||ctx.antecedentes.length);
        if(!original && !hayContexto){ ta.focus(); toastSafe('Registra una nota o completa datos técnicos para elaborar el detalle.'); return; }
        const tipoEquipo=clean(document.getElementById('zgTipoEquipo')?.value || '');
        const textoBtn=btn.textContent;
        btn.dataset.loading='1';
        btn.disabled=true;
        btn.textContent='Procesando...';

        try{
          const respuesta=await fetch('mejorar_texto_ia.php',{
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json'},
            credentials:'same-origin',
            body:JSON.stringify({
              texto:original,
              etiqueta:etiqueta,
              tipo_equipo:tipoEquipo,
              trabajo:ctx.titulo,
              actividades:ctx.grupos.actividades,
              hallazgos:ctx.grupos.hallazgos,
              acciones:ctx.grupos.acciones,
              materiales:ctx.materiales,
              antecedentes:ctx.antecedentes,
              memoria_trabajo:ctx.memoriaTrabajo||[],
              preinspeccion_id:String(document.getElementById('preinspeccionId')?.value||window.PREINSPECCION?.id||window.PREINSPECCION?.pre_id||''),
              token_continuacion:String(document.getElementById('tokenContinuacion')?.value||window.TOKEN_CONTINUACION||'')
            })
          });
          let data={};
          try{ data=await respuesta.json(); }catch(e){}
          if(!respuesta.ok || !data.ok || data.source!=='anthropic' || !clean(data.texto)){
            throw new Error(clean(data.error) || 'La respuesta no provino del asistente de IA.');
          }
          ta.value=clean(data.texto);
          ta.dispatchEvent(new Event('input',{bubbles:true}));
          try{ if(window.zgroupMarcarCambio) window.zgroupMarcarCambio(); }catch(e){}
          toastSafe('Redacción técnica mejorada');
        }catch(error){
          // No se aplica ninguna plantilla local. Si la API falla, se conserva exactamente
          // lo escrito por el técnico para no hacer pasar una regla fija como si fuera IA.
          ta.value=original;
          const mensaje=(error && error.message) ? error.message : 'No se pudo conectar con el asistente de redacción.';
          toastSafe(mensaje);
          console.error('ZGROUP IA:', error);
        }finally{
          btn.dataset.loading='0';
          btn.textContent=textoBtn;
          btn.disabled=!chk.checked;
        }
      });
      ta.insertAdjacentElement('afterend', wrap);
    });
  }
  function limpiarAyudasHuerfanas(){
    document.querySelectorAll('.zg-redactor-help').forEach(function(help){
      const targetId=String(help.dataset.zgFor||'').trim();
      const target=targetId ? document.getElementById(targetId) : null;
      const field=target && target.closest ? target.closest('.field') : null;
      if(!target || !field || help.closest('.field')!==field){
        if(target) delete target.dataset.zgRedactorOk;
        help.remove();
        return;
      }
      const hidden=!!(target.disabled || target.hidden || target.closest('[hidden],.is-hidden'));
      help.hidden=hidden;
    });
  }
  const mo=new MutationObserver(function(){instalarAyudaTextos(); limpiarAyudasHuerfanas(); quitarSalida();});
  function init(){
    wrapEvidenceRender();
    wrapGuardarPreliminar();
    setTimeout(restoreStored,250);
    setTimeout(restoreStored,900);
    instalarAyudaTextos();
    limpiarAyudasHuerfanas();
    quitarSalida();
    try{mo.observe(document.body,{childList:true,subtree:true});}catch(e){}
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init); else init();
  window.addEventListener('load',function(){wrapEvidenceRender(); wrapGuardarPreliminar(); setTimeout(restoreStored,650); setTimeout(function(){instalarAyudaTextos(); limpiarAyudasHuerfanas();},750); quitarSalida();});
})();
</script>



<!-- ZGROUP ajuste final solicitado: sin recomendaciones del técnico en la web -->
<style id="zg-hide-tech-recomendaciones-final">
  .quick-group:has(.quick-title){ }
</style>
<script id="zg-hide-tech-recomendaciones-final-js">
(function(){
  function limpiarRecomendacionesUI(){
    document.querySelectorAll('.quick-group').forEach(function(g){
      var t = g.querySelector('.quick-title');
      if(t && String(t.textContent || '').trim().toLowerCase() === 'recomendaciones'){
        g.remove();
      }
    });
  }
  document.addEventListener('DOMContentLoaded', limpiarRecomendacionesUI);
  window.addEventListener('load', function(){ limpiarRecomendacionesUI(); setTimeout(limpiarRecomendacionesUI, 500); });
})();
</script>

<style id="zg-service-config-style">
.zg-service-config{grid-column:1/-1;border:1.5px solid #cfe0f3;background:linear-gradient(145deg,#ffffff,#f5f9ff);border-radius:20px;padding:16px;margin:4px 0 16px;box-shadow:0 10px 26px rgba(16,33,58,.07)}
.zg-service-config-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:14px}.zg-service-config-head h3{font-family:Archivo,system-ui,sans-serif;font-size:19px;color:#10213a;margin:4px 0}.zg-service-config-head p{color:#66758a;font-size:12.5px;font-weight:750}.zg-config-kicker{display:inline-flex;background:#e8f2ff;color:#14599d;border:1px solid #cbe1fa;border-radius:999px;padding:5px 9px;font-size:10px;font-family:Archivo,system-ui,sans-serif;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
.zg-service-config-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:11px}.zg-service-config .field{margin:0}.zg-service-config select,.zg-service-config input{min-height:45px;background:#fff;font-weight:800}.zg-service-config .input-error{border-color:#dc2626!important;box-shadow:0 0 0 3px rgba(220,38,38,.10)!important}
.zg-maintenance-type.is-hidden,.zg-tunnel-config.is-hidden{display:none!important}.zg-tunnel-config{margin-top:15px;border-top:1px solid #dbe7f4;padding-top:15px}.zg-tunnel-head{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:12px}.zg-tunnel-head h4{font-family:Archivo,system-ui,sans-serif;font-size:16px;color:#10213a;margin-top:5px}.zg-target-machine{width:min(330px,100%)}
.zg-machines-grid{display:grid;gap:9px}.zg-machine-row{display:grid;grid-template-columns:100px 1fr 1fr 1fr;gap:9px;align-items:end;background:#fff;border:1px solid #d8e6f5;border-radius:16px;padding:11px}.zg-machine-badge{align-self:stretch;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:12px;background:linear-gradient(145deg,#17365f,#1f6fc4);color:#fff;font-family:Archivo,system-ui,sans-serif;font-weight:900}.zg-machine-badge small{font-size:10px;opacity:.75;margin-bottom:2px}.zg-machine-row .field label{font-size:11px}.zg-machine-row select,.zg-machine-row input{min-height:41px;font-size:13px}.zg-tunnel-note{margin-top:10px;background:#eef6ff;border:1px solid #d1e4fa;color:#526a84;border-radius:12px;padding:9px 11px;font-size:11.5px;font-weight:800;line-height:1.35}.zg-main-machine-synced{background:#eef4fa!important;color:#35506f!important}
.zg-work-machine-field{margin-bottom:13px!important;background:linear-gradient(145deg,#eef6ff,#f8fbff);border:1px solid #cfe1f5;border-radius:15px;padding:11px}.zg-work-machine-field label{display:flex!important;align-items:center;gap:7px;color:#17385d!important;font-family:Archivo,system-ui,sans-serif!important;font-weight:900!important}.zg-work-machine-field label:before{content:'⚙️'}.zg-work-machine-field select{background:#fff!important;min-height:46px!important;font-weight:900!important}.zg-work-machine-field .field-hint{margin-top:5px}
@media(max-width:980px){.zg-service-config-grid{grid-template-columns:1fr 1fr}.zg-machine-row{grid-template-columns:90px 1fr 1fr}.zg-machine-row .zg-machine-serial{grid-column:2/-1}}
@media(max-width:640px){.zg-service-config{padding:13px;border-radius:17px}.zg-service-config-grid{grid-template-columns:1fr}.zg-tunnel-head{align-items:stretch;flex-direction:column}.zg-target-machine{width:100%}.zg-machine-row{grid-template-columns:1fr;padding:10px}.zg-machine-badge{min-height:44px;flex-direction:row;gap:6px}.zg-machine-row .zg-machine-serial{grid-column:auto}.zg-machine-row select,.zg-machine-row input{min-height:48px;font-size:15px}}
</style>
<script id="zg-service-config-script">
(function(){
  function byId(id){return document.getElementById(id);}
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim();}
  function toastSafe(t){try{if(typeof toast==='function')toast(t);else alert(t);}catch(e){alert(t);}}
  const META_RE=/\[\[ZG_META:([A-Za-z0-9+\/=_-]+)\]\]/;
  const META_RE_ALL=/\s*\[\[ZG_META:[A-Za-z0-9+\/=_-]+\]\]\s*/g;
  const BRANDS=['THERMO KING','CARRIER','STAR COOL','DAIKIN','OTRO'];
  const CONTROLLERS={
    'STAR COOL':['CIM5','CIM6'],
    'THERMO KING':['MP3000','MP4000','MP5000'],
    'CARRIER':['MICROLINK 2I','MICROLINK 3','MICROLINK 5'],
    'DAIKIN':['DAIKIN'],
      'OTRO':[]
  };
  let loadedMeta=null;

  function b64Encode(obj){
    try{return btoa(unescape(encodeURIComponent(JSON.stringify(obj))));}catch(e){return '';}
  }
  function b64Decode(str){
    try{return JSON.parse(decodeURIComponent(escape(atob(str))));}catch(e){return null;}
  }
  function compactMeta(meta){
    return {
      m:meta.modalidad||'',
      t:meta.tipoInstalacion||'',
      e:meta.tipoEquipo||'',
      z:meta.tamanoContenedor||'',
      gi:meta.gensetInicial||{},
      r:meta.requiereOtroMantenimiento||'',
      mt:meta.tipoOtroMantenimiento||'',
      o:meta.maquinaObjetivo||'',
      pa:meta.presionAlta||'',
      pb:meta.presionBaja||'',
      q:(meta.maquinas||[]).map(x=>[x.id||'',x.marca||'',x.controlador||'',x.serie||''])
    };
  }
  function expandMeta(raw){
    raw=raw||{};
    return {
      modalidad:raw.modalidad||raw.m||'',
      tipoInstalacion:raw.tipoInstalacion||raw.t||'',
      tipoEquipo:raw.tipoEquipo||raw.e||'',
      tamanoContenedor:raw.tamanoContenedor||raw.z||'',
      gensetInicial:raw.gensetInicial||raw.gi||{},
      requiereOtroMantenimiento:raw.requiereOtroMantenimiento||raw.r||'',
      tipoOtroMantenimiento:raw.tipoOtroMantenimiento||raw.mt||'',
      maquinaObjetivo:raw.maquinaObjetivo||raw.o||'',
      presionAlta:raw.presionAlta||raw.pa||'',
      presionBaja:raw.presionBaja||raw.pb||'',
      maquinas:Array.isArray(raw.maquinas)?raw.maquinas:(Array.isArray(raw.q)?raw.q.map(a=>({id:a[0]||'',marca:a[1]||'',controlador:a[2]||'',serie:a[3]||''})):[])
    };
  }
  function parseMetaText(txt){const m=String(txt||'').match(META_RE); return m?expandMeta(b64Decode(m[1])):null;}
  window.zgStripMetaFromText=function(txt){return String(txt||'').replace(META_RE_ALL,'\n').replace(/\n{3,}/g,'\n\n').trim();};

  function machineRows(){
    const out=[];
    for(let i=1;i<=5;i++){
      out.push({id:'M'+i,marca:clean(byId('zgMachineBrand'+i)?.value),controlador:clean(byId('zgMachineController'+i)?.value),serie:clean(byId('zgMachineSerial'+i)?.value)});
    }
    return out;
  }
  window.zgCollectServiceMeta=function(){
    return {
      modalidad:clean(byId('zgModalidadComercial')?.value),
      tipoInstalacion:clean(byId('zgTipoInstalacion')?.value),
      tipoEquipo:clean(byId('zgTipoEquipo')?.value),
      tamanoContenedor:clean(byId('zgTamanoContenedor')?.value),
      gensetInicial:{horometro:clean(byId('gensetHorometroInicial')?.value),bateria:clean(byId('gensetVoltajeBateriaInicial')?.value),combustible:clean(byId('gensetNivelCombustibleInicial')?.value),aceite:clean(byId('gensetNivelAceiteInicial')?.value),refrigeranteMotor:clean(byId('gensetRefrigeranteMotorInicial')?.value),arranque:clean(byId('gensetArranqueInicial')?.value),frecuencia:clean(byId('gensetFrecuenciaInicial')?.value),presionAceite:clean(byId('gensetPresionAceiteInicial')?.value)},
      requiereOtroMantenimiento:clean(byId('zgRequiereOtroMantenimiento')?.value),
      tipoOtroMantenimiento:clean(byId('zgTipoOtroMantenimiento')?.value),
      maquinaObjetivo:clean(byId('zgMaquinaPreliminarObjetivo')?.value),
      presionAlta:clean(byId('presionAlta')?.value),
      presionBaja:clean(byId('presionBaja')?.value),
      maquinas:machineRows()
    };
  };
  window.zgObservacionInicialConMeta=function(obs){
    const cleanObs=window.zgStripMetaFromText(obs||'');
    const encoded=b64Encode(compactMeta(window.zgCollectServiceMeta()));
    return cleanObs+(encoded?'\n[[ZG_META:'+encoded+']]':'');
  };

  function fillControllerList(i,brand,keep){
    const list=byId('zgMachineControllerList'+i), input=byId('zgMachineController'+i);
    if(!list||!input)return;
    list.innerHTML='';
    (CONTROLLERS[brand]||[]).forEach(v=>{const o=document.createElement('option');o.value=v;list.appendChild(o);});
    input.placeholder=(CONTROLLERS[brand]||[]).length?'Selecciona o escribe controlador':'Escribe controlador';
    if(!keep && !(CONTROLLERS[brand]||[]).includes(input.value)) input.value='';
  }
  function renderMachineRows(){
    const grid=byId('zgMachinesGrid'); if(!grid||grid.children.length)return;
    for(let i=1;i<=5;i++){
      const row=document.createElement('div'); row.className='zg-machine-row';
      row.innerHTML='<div class="zg-machine-badge"><small>TÚNEL</small>Máquina '+i+'</div>'+ 
        '<div class="field"><label for="zgMachineBrand'+i+'">Marca</label><select id="zgMachineBrand'+i+'"><option value="">Seleccionar</option>'+BRANDS.map(b=>'<option value="'+b+'">'+b+'</option>').join('')+'</select><div class="field-error" id="zgMachineBrand'+i+'Error"></div></div>'+ 
        '<div class="field"><label for="zgMachineController'+i+'">Controlador</label><input id="zgMachineController'+i+'" list="zgMachineControllerList'+i+'" autocomplete="off" placeholder="Controlador"><datalist id="zgMachineControllerList'+i+'"></datalist><div class="field-error" id="zgMachineController'+i+'Error"></div></div>'+ 
        '<div class="field zg-machine-serial"><label for="zgMachineSerial'+i+'">N° de serie</label><input id="zgMachineSerial'+i+'" autocomplete="off" placeholder="Serie de la máquina"><div class="field-error" id="zgMachineSerial'+i+'Error"></div></div>';
      grid.appendChild(row);
      const brand=byId('zgMachineBrand'+i), ctrl=byId('zgMachineController'+i), serial=byId('zgMachineSerial'+i);
      brand.addEventListener('change',()=>{fillControllerList(i,brand.value,false);syncTargetMachine();refreshPanels();});
      ctrl.addEventListener('input',()=>{syncTargetMachine();refreshPanels();});
      serial.addEventListener('input',()=>{syncTargetMachine();refreshPanels();});
    }
  }
  function refreshPanels(){try{if(typeof renderPanels==='function')renderPanels();}catch(e){}}
  function markError(id,msg){const x=byId(id),e=byId(id+'Error');if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});setTimeout(()=>{try{x.focus();}catch(_){}},180);}if(e){e.textContent=msg;e.classList.add('show');}toastSafe(msg);return false;}
  function clearError(id){const x=byId(id),e=byId(id+'Error');if(x)x.classList.remove('input-error');if(e){e.textContent='';e.classList.remove('show');}}
  function selectedMachine(){const id=clean(byId('zgMaquinaPreliminarObjetivo')?.value);return machineRows().find(m=>m.id===id)||null;}
  function setMainSynced(on){let hasPre=false;try{hasPre=typeof PREINSPECCION!=='undefined'&&!!PREINSPECCION;}catch(e){}['marcaEquipo','controladorEquipo','serialUnidad'].forEach(id=>{const x=byId(id);if(!x)return;x.classList.toggle('zg-main-machine-synced',on);if(hasPre&&!on)return;if(x.tagName==='SELECT')x.disabled=!!on;else x.readOnly=!!on;});}
  function syncTargetMachine(){
    const tunnel=clean(byId('zgTipoInstalacion')?.value)==='Túnel';
    setMainSynced(tunnel);
    if(!tunnel)return;
    const m=selectedMachine(); if(!m)return;
    const brand=byId('marcaEquipo'),ctrl=byId('controladorEquipo'),ser=byId('serialUnidad');
    if(brand)brand.value=m.marca||''; if(ctrl)ctrl.value=m.controlador||''; if(ser)ser.value=m.serie||'';
    try{if(typeof actualizarOpcionesControlador==='function')actualizarOpcionesControlador(false);}catch(e){}
  }
  function updateVisibility(){
    const req=clean(byId('zgRequiereOtroMantenimiento')?.value),type=clean(byId('zgTipoInstalacion')?.value);
    byId('zgTipoMantenimientoWrap')?.classList.toggle('is-hidden',req!=='Sí');
    byId('zgTunnelConfig')?.classList.toggle('is-hidden',type!=='Túnel');
    if(type!=='Túnel')setMainSynced(false); else syncTargetMachine();
    refreshPanels();
  }
  function applyMeta(meta){
    meta=expandMeta(meta||{}); loadedMeta=meta;
    if(byId('zgModalidadComercial'))byId('zgModalidadComercial').value=meta.modalidad||'';
    if(byId('zgTipoInstalacion'))byId('zgTipoInstalacion').value=meta.tipoInstalacion||'';
    if(byId('zgTipoEquipo'))byId('zgTipoEquipo').value=meta.tipoEquipo||'';
    if(byId('zgTamanoContenedor'))byId('zgTamanoContenedor').value=meta.tamanoContenedor||'';
    const gi=meta.gensetInicial||{};
    [['gensetHorometroInicial','horometro'],['gensetVoltajeBateriaInicial','bateria'],['gensetNivelCombustibleInicial','combustible'],['gensetNivelAceiteInicial','aceite'],['gensetRefrigeranteMotorInicial','refrigeranteMotor'],['gensetArranqueInicial','arranque']].forEach(function(p){if(byId(p[0]))byId(p[0]).value=gi[p[1]]||'';});
    if(byId('zgRequiereOtroMantenimiento'))byId('zgRequiereOtroMantenimiento').value=meta.requiereOtroMantenimiento||'';
    if(byId('zgTipoOtroMantenimiento'))byId('zgTipoOtroMantenimiento').value=meta.tipoOtroMantenimiento||'';
    if(byId('zgMaquinaPreliminarObjetivo'))byId('zgMaquinaPreliminarObjetivo').value=meta.maquinaObjetivo||'';
    // Las presiones preliminares se guardan dentro de la metadata del servicio.
    // Esto permite recuperarlas al continuar con token aunque la tabla antigua
    // de preinspección todavía no tenga columnas propias para presión.
    if(byId('presionAlta') && meta.presionAlta) byId('presionAlta').value=meta.presionAlta;
    if(byId('presionBaja') && meta.presionBaja) byId('presionBaja').value=meta.presionBaja;
    (meta.maquinas||[]).forEach((m,idx)=>{const i=idx+1;if(byId('zgMachineBrand'+i))byId('zgMachineBrand'+i).value=m.marca||'';fillControllerList(i,m.marca||'',true);if(byId('zgMachineController'+i))byId('zgMachineController'+i).value=m.controlador||'';if(byId('zgMachineSerial'+i))byId('zgMachineSerial'+i).value=m.serie||'';});
    updateVisibility();syncTargetMachine();refreshPanels();
  }
  window.zgValidarConfiguracionServicio=function(){
    ['zgModalidadComercial','zgTipoInstalacion','zgTipoEquipo','zgTamanoContenedor','zgMaquinaPreliminarObjetivo'].forEach(clearError);
    if(!clean(byId('zgModalidadComercial')?.value))return markError('zgModalidadComercial','Selecciona si el servicio corresponde a alquiler o venta.');
    const type=clean(byId('zgTipoInstalacion')?.value);if(!type)return markError('zgTipoInstalacion','Selecciona el tipo de instalación.');
    const equipmentType=clean(byId('zgTipoEquipo')?.value);if(!equipmentType)return markError('zgTipoEquipo','Selecciona si se atenderá un reefer, un genset u otro equipo.');
    if(equipmentType!=='Genset' && !clean(byId('zgTamanoContenedor')?.value))return markError('zgTamanoContenedor','Selecciona el tamaño del contenedor o indica que no aplica.');
    if(equipmentType==='Genset'){
      const req=[['gensetHorometroInicial','horómetro inicial'],['gensetVoltajeBateriaInicial','voltaje de batería'],['gensetNivelCombustibleInicial','nivel de combustible'],['gensetNivelAceiteInicial','nivel de aceite'],['gensetRefrigeranteMotorInicial','nivel de refrigerante del motor'],['gensetArranqueInicial','resultado de la prueba de arranque']];
      for(const p of req){if(!clean(byId(p[0])?.value))return markError(p[0],'Completa '+p[1]+' del genset.');}
    }
    if(type==='Túnel' && equipmentType!=='Genset'){
      for(let i=1;i<=5;i++){
        for(const [suffix,label] of [['Brand','la marca'],['Controller','el controlador'],['Serial','el número de serie']]){const id='zgMachine'+suffix+i;if(!clean(byId(id)?.value))return markError(id,'Completa '+label+' de la máquina '+i+'.');}
      }
      if(!clean(byId('zgMaquinaPreliminarObjetivo')?.value))return markError('zgMaquinaPreliminarObjetivo','Selecciona la máquina que se usará como referencia en la inspección preliminar.');
      syncTargetMachine();
    }
    return true;
  };
  window.zgGetConfiguredMachines=function(){return window.zgCollectServiceMeta().maquinas.filter(m=>m.marca||m.controlador||m.serie);};
  window.zgBuildMachineAssignmentField=function(s){
    if(clean(byId('zgTipoInstalacion')?.value)!=='Túnel')return null;
    const f=document.createElement('div');f.className='field full zg-work-machine-field';
    const l=document.createElement('label');l.textContent='Máquina atendida en este trabajo';
    const sel=document.createElement('select');sel.id='zgWorkMachine_'+s.id;
    sel.innerHTML='<option value="">Seleccionar máquina</option>';
    window.zgGetConfiguredMachines().forEach((m,idx)=>{const op=document.createElement('option');op.value=m.id;op.textContent='Máquina '+(idx+1)+' · '+[m.marca,m.controlador,m.serie?('Serie '+m.serie):''].filter(Boolean).join(' · ');sel.appendChild(op);});
    sel.value=s.maquinaAsignada||'';
    const hint=document.createElement('div');hint.className='field-hint';hint.textContent='Esta selección aparecerá dentro del trabajo correspondiente en el informe final.';
    const err=document.createElement('div');err.className='field-error';err.id='zgWorkMachine_'+s.id+'Error';
    sel.addEventListener('change',()=>{s.maquinaAsignada=sel.value;sel.classList.remove('input-error');err.textContent='';err.classList.remove('show');});
    f.append(l,sel,hint,err);return f;
  };
  window.zgValidarAsignacionTrabajos=function(sections){
    if(clean(byId('zgTipoInstalacion')?.value)!=='Túnel')return true;
    for(const s of (sections||[])){
      if(!clean(s.maquinaAsignada)){
        const id='zgWorkMachine_'+s.id,x=byId(id),e=byId(id+'Error');
        if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});}
        if(e){e.textContent='Selecciona la máquina atendida en este trabajo.';e.classList.add('show');}
        toastSafe('Selecciona la máquina atendida en '+s.nombre+'.');return false;
      }
    }
    return true;
  };

  function clearNewReportEvidence(){
    const url=new URL(location.href),token=clean(url.searchParams.get('token')),pre=clean(byId('preinspeccionId')?.value);
    let hasPre=false;try{hasPre=typeof PREINSPECCION!=='undefined'&&!!PREINSPECCION;}catch(e){}
    if(token||pre||hasPre)return;
    try{sessionStorage.removeItem('zg_pre_evidencias_pending');localStorage.removeItem('zgroup_preinspeccion_token');localStorage.removeItem('zgroup_preinspeccion_id');}catch(e){}
    window.ZG_PRE_EVIDENCIAS=[];
    try{if(typeof window.renderPreEvidenceGrid==='function')window.renderPreEvidenceGrid();if(typeof updateCounter==='function')updateCounter();}catch(e){}
  }
  window.zgClearDraftCache=clearNewReportEvidence;

  function lockNewFieldsIfContinuation(){
    let hasPre=false;try{hasPre=typeof PREINSPECCION!=='undefined'&&!!PREINSPECCION;}catch(e){}
    if(!hasPre)return;
    ['zgModalidadComercial','zgTipoInstalacion','zgMaquinaPreliminarObjetivo'].forEach(id=>{const x=byId(id);if(x)x.disabled=true;});
    for(let i=1;i<=5;i++)['zgMachineBrand','zgMachineController','zgMachineSerial'].forEach(p=>{const x=byId(p+i);if(x){if(x.tagName==='SELECT')x.disabled=true;else x.readOnly=true;}});
  }
  function init(){
    renderMachineRows();
    ['zgModalidadComercial','zgTipoInstalacion','zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMaquinaPreliminarObjetivo'].forEach(id=>{const x=byId(id);if(x)x.addEventListener('change',()=>{clearError(id);updateVisibility();syncTargetMachine();});});
    let raw='';try{raw=(typeof PREINSPECCION!=='undefined'&&PREINSPECCION)?PREINSPECCION.observacion_inicial||'':'';}catch(e){}
    const meta=parseMetaText(raw);if(meta)applyMeta(meta);else updateVisibility();
    const obs=byId('observacionInicial');if(obs)obs.value=window.zgStripMetaFromText(obs.value);
    clearNewReportEvidence();
    setTimeout(clearNewReportEvidence,700);
    lockNewFieldsIfContinuation();
    setTimeout(()=>{syncTargetMachine();refreshPanels();},300);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',()=>{setTimeout(clearNewReportEvidence,100);setTimeout(()=>{syncTargetMachine();refreshPanels();},500);});
  document.addEventListener('change',ev=>{if(ev.target&&ev.target.id==='preEvidenceInput')window.__zgFreshPreEvidence=true;},true);
  document.addEventListener('click',ev=>{if(ev.target&&ev.target.closest&&ev.target.closest('#newReportBtn')){try{sessionStorage.removeItem('zg_pre_evidencias_pending');localStorage.removeItem('zgroup_preinspeccion_token');localStorage.removeItem('zgroup_preinspeccion_id');if(window.zgClearPressureDraftStorage)window.zgClearPressureDraftStorage();}catch(e){}}},true);
})();
</script>

<script id="zg-presiones-preliminar-persistencia-extra">
(function(){
  // Las presiones ya no se guardan ni se restauran desde sessionStorage/localStorage.
  // Una continuación válida sigue cargando sus valores únicamente desde PREINSPECCION.
  function limpiarCachePresiones(){
    try{
      [window.sessionStorage, window.localStorage].forEach(function(storage){
        const keys=[];
        for(let i=0;i<storage.length;i++){
          const k=storage.key(i);
          if(k && k.indexOf('zg_presiones_pre_')===0) keys.push(k);
        }
        keys.forEach(function(k){ storage.removeItem(k); });
      });
    }catch(e){}
  }
  limpiarCachePresiones();
  window.zgClearPressureDraftStorage = limpiarCachePresiones;
})();
</script>


<style id="zg-fix-mantenimiento-posicion-meta">
/* Mantenimiento adicional integrado dentro de Control final */
.final-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.final-grid .field{margin:0}
.final-grid .field.full{grid-column:1/-1}
#zgTipoMantenimientoWrap.is-hidden,#zgMotivoMantenimientoWrap.is-hidden{display:none!important}
.zg-final-maint-question,#zgTipoMantenimientoWrap,#zgMotivoMantenimientoWrap{
  border:1.5px solid #cfe0f3;border-radius:15px;background:linear-gradient(180deg,#fff,#f7fbff);padding:12px;
}
.zg-final-maint-question label,#zgTipoMantenimientoWrap label,#zgMotivoMantenimientoWrap label{color:#17385d;font-weight:800}
.zg-final-maint-question select,#zgTipoMantenimientoWrap select{background:#fff!important;font-weight:400!important}
#zgMotivoOtroMantenimiento{min-height:118px!important;background:#fff!important;font-weight:400!important;line-height:1.5}
#zgMotivoMantenimientoWrap .field-hint{margin:7px 1px 0;color:#60748c;font-size:11.8px;font-weight:750;line-height:1.4}
#zgMotivoMantenimientoWrap .zg-redactor-help{margin-top:9px}
#zgPostRepuestoMaintenanceCard{display:none!important}
@media(max-width:700px){.final-grid{grid-template-columns:1fr}.final-grid .field.full{grid-column:auto}}
</style>

<script id="zg-fix-mantenimiento-posicion-meta-js">
(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(v){ return String(v == null ? '' : v).replace(/\s+/g,' ').trim(); }
  var META_RE=/\[\[ZG_META:[A-Za-z0-9+\/=_-]+\]\]/;
  var META_RE_ALL=/\s*\[\[ZG_META:[A-Za-z0-9+\/=_-]+\]\]\s*/g;
  function stripMeta(v){ return String(v || '').replace(META_RE_ALL,'\n').replace(/\n{3,}/g,'\n\n').trim(); }

  function limpiarMetaVisible(){
    var obs=byId('observacionInicial');
    if(obs && META_RE.test(String(obs.value||''))) obs.value=stripMeta(obs.value);
    try{ if(typeof PREINSPECCION!=='undefined' && PREINSPECCION) PREINSPECCION.observacion_inicial=stripMeta(PREINSPECCION.observacion_inicial||''); }catch(e){}
  }

  function reportIdentity(){
    try{
      var u=new URL(location.href);
      return clean(u.searchParams.get('token') || (byId('tokenContinuacion')&&byId('tokenContinuacion').value) || (byId('preinspeccionId')&&byId('preinspeccionId').value));
    }catch(e){ return ''; }
  }
  function storageKey(){ var id=reportIdentity(); return id ? 'zg_post_maint_'+id : ''; }
  function clearMaintenanceStorage(){
    try{
      [sessionStorage,localStorage].forEach(function(st){
        var keys=[]; for(var i=0;i<st.length;i++){var k=st.key(i);if(k&&k.indexOf('zg_post_maint_')===0)keys.push(k);} keys.forEach(function(k){st.removeItem(k);});
      });
    }catch(e){}
  }
  window.zgClearMaintenanceStorage=clearMaintenanceStorage;

  function saveMaintenance(){
    var key=storageKey(); if(!key) return;
    try{sessionStorage.setItem(key,JSON.stringify({r:clean(byId('zgRequiereOtroMantenimiento')?.value),t:clean(byId('zgTipoOtroMantenimiento')?.value),m:clean(byId('zgMotivoOtroMantenimiento')?.value)}));}catch(e){}
  }
  function restoreMaintenance(){
    var key=storageKey(); if(!key) return;
    try{
      var raw=sessionStorage.getItem(key); if(!raw)return; var d=JSON.parse(raw)||{};
      var r=byId('zgRequiereOtroMantenimiento'),t=byId('zgTipoOtroMantenimiento'),m=byId('zgMotivoOtroMantenimiento');
      if(r&&!r.value&&d.r)r.value=d.r;if(t&&!t.value&&d.t)t.value=d.t;if(m&&!m.value&&d.m)m.value=d.m;
    }catch(e){}
  }

  function clearError(id){var x=byId(id),e=byId(id+'Error');if(x)x.classList.remove('input-error');if(e){e.textContent='';e.classList.remove('show');}}
  function markError(id,msg){
    var x=byId(id),e=byId(id+'Error');if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});setTimeout(function(){try{x.focus();}catch(_){}},150);}if(e){e.textContent=msg;e.classList.add('show');}
    try{if(typeof toast==='function')toast(msg);else alert(msg);}catch(err){alert(msg);}return false;
  }

  function updateVisibility(){
    var req=clean(byId('zgRequiereOtroMantenimiento')?.value),show=req==='Sí';
    var typeWrap=byId('zgTipoMantenimientoWrap'),reasonWrap=byId('zgMotivoMantenimientoWrap');
    var type=byId('zgTipoOtroMantenimiento'),reason=byId('zgMotivoOtroMantenimiento');
    [typeWrap,reasonWrap].forEach(function(w){if(w){w.classList.toggle('is-hidden',!show);w.hidden=!show;w.style.display=show?'':'none';}});
    if(type){type.disabled=!show;type.required=show;if(!show)type.value='';}
    if(reason){reason.disabled=!show;reason.required=show;if(!show)reason.value='';}
    if(!show){clearError('zgTipoOtroMantenimiento');clearError('zgMotivoOtroMantenimiento');}
    saveMaintenance();
  }
  window.zgActualizarMantenimientoFinal=updateVisibility;

  function validarMantenimientoFinal(){
    ['zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento'].forEach(clearError);
    var req=clean(byId('zgRequiereOtroMantenimiento')?.value);
    if(!req)return markError('zgRequiereOtroMantenimiento','Indica si el equipo requiere otro mantenimiento.');
    if(req==='Sí'){
      if(!clean(byId('zgTipoOtroMantenimiento')?.value))return markError('zgTipoOtroMantenimiento','Selecciona el tipo de mantenimiento requerido.');
      var motivo=clean(byId('zgMotivoOtroMantenimiento')?.value);
      if(motivo.length<10)return markError('zgMotivoOtroMantenimiento','Explica la razón del mantenimiento con al menos 10 caracteres para que el cliente pueda entenderla.');
    }
    saveMaintenance();return true;
  }
  window.zgValidarMantenimientoFinal=validarMantenimientoFinal;

  function instalarValidacionPreliminar(){
    window.zgValidarConfiguracionServicio=function(){
      ['zgModalidadComercial','zgTipoInstalacion','zgMaquinaPreliminarObjetivo'].forEach(clearError);
      if(!clean(byId('zgModalidadComercial')?.value))return markError('zgModalidadComercial','Selecciona si el servicio corresponde a alquiler o venta.');
      var tipo=clean(byId('zgTipoInstalacion')?.value);if(!tipo)return markError('zgTipoInstalacion','Selecciona el tipo de instalación.');
      if(tipo==='Túnel'){
        for(var i=1;i<=5;i++){
          var campos=[['Brand','la marca'],['Controller','el controlador'],['Serial','el número de serie']];
          for(var j=0;j<campos.length;j++){var id='zgMachine'+campos[j][0]+i;if(!clean(byId(id)?.value))return markError(id,'Completa '+campos[j][1]+' de la máquina '+i+'.');}
        }
        if(!clean(byId('zgMaquinaPreliminarObjetivo')?.value))return markError('zgMaquinaPreliminarObjetivo','Selecciona la máquina que se usará como referencia en la inspección preliminar.');
        try{if(typeof syncTargetMachine==='function')syncTargetMachine();}catch(e){}
      }
      return true;
    };
  }

  function personalizarAyuda(){
    var ta=byId('zgMotivoOtroMantenimiento');if(!ta)return;
    var help=ta.nextElementSibling;
    while(help && !help.classList.contains('zg-redactor-help')) help=help.nextElementSibling;
    if(!help)return;
    var btn=help.querySelector('button'),small=help.querySelector('small');
    if(btn)btn.textContent='Mejorar explicación';
    if(small)small.textContent='Convierte la razón técnica en una explicación clara y profesional para el cliente.';
  }

  function bind(){
    var req=byId('zgRequiereOtroMantenimiento'),type=byId('zgTipoOtroMantenimiento'),reason=byId('zgMotivoOtroMantenimiento');
    if(req&&!req.dataset.zgMaintBound){req.dataset.zgMaintBound='1';req.addEventListener('change',function(){clearError(req.id);updateVisibility();});}
    if(type&&!type.dataset.zgMaintBound){type.dataset.zgMaintBound='1';type.addEventListener('change',function(){clearError(type.id);saveMaintenance();});}
    if(reason&&!reason.dataset.zgMaintBound){reason.dataset.zgMaintBound='1';reason.addEventListener('input',function(){clearError(reason.id);saveMaintenance();});}
    restoreMaintenance();updateVisibility();personalizarAyuda();
  }

  function init(){limpiarMetaVisible();instalarValidacionPreliminar();bind();[80,250,650,1300,2400].forEach(function(ms){setTimeout(function(){limpiarMetaVisible();instalarValidacionPreliminar();bind();personalizarAyuda();},ms);});}
  document.addEventListener('click',function(ev){
    if(ev.target&&ev.target.closest&&ev.target.closest('#pdfBtn')&&!validarMantenimientoFinal()){ev.preventDefault();ev.stopPropagation();if(ev.stopImmediatePropagation)ev.stopImmediatePropagation();return false;}
    if(ev.target&&ev.target.closest&&(ev.target.closest('#newReportBtn')||ev.target.closest('#clearBtn'))){setTimeout(clearMaintenanceStorage,0);}
  },true);
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',function(){setTimeout(init,100);});
})();
</script>


<style id="zg-panel-editor-style">
.zg-edit-banner{position:sticky;top:0;z-index:140;margin:0 auto 14px;width:min(980px,calc(100% - 24px));display:none;align-items:center;justify-content:space-between;gap:14px;background:linear-gradient(135deg,#10213a,#1f6fc4);color:#fff;border-radius:0 0 18px 18px;padding:13px 17px;box-shadow:0 12px 30px rgba(16,33,58,.25)}
.zg-edit-banner.show{display:flex}.zg-edit-banner b{font-family:'Archivo',system-ui,sans-serif;font-size:15px}.zg-edit-banner span{display:block;color:#dceafb;font-size:12px;font-weight:750;margin-top:2px}.zg-edit-back{background:#fff;color:#12345a;text-decoration:none;border-radius:999px;padding:9px 13px;font-family:'Archivo';font-weight:900;font-size:12px;white-space:nowrap}
.zg-supervisor-upload{margin-top:10px;padding:11px;border:1px solid #cfe0f3;border-radius:14px;background:#f4f9ff}.zg-supervisor-upload b{display:block;color:#12345a;font-size:12px;margin-bottom:4px}.zg-supervisor-upload small{display:block;color:#66758a;font-weight:700;line-height:1.35;margin-bottom:9px}.zg-supervisor-upload-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.zg-supervisor-file{max-width:100%;font-size:12px}.zg-supervisor-upload-btn{border:none;border-radius:999px;background:#1f6fc4;color:#fff;padding:9px 12px;font-family:'Archivo';font-weight:900;cursor:pointer}.zg-supervisor-status{font-size:12px;font-weight:850;color:#176b34}
body.zg-editing #clearBtn{display:none!important}body.zg-editing #preBtn{display:none!important}body.zg-editing .menu-home-float{display:none!important}
body.zg-pre-editing .menu-home-float{display:none!important}
body.zg-pre-editing .actionbar{display:none!important}
body.zg-pre-editing #trabajosServicioCard,
body.zg-pre-editing #finalControlCard,
body.zg-pre-editing #firmasCard{display:none!important}
body.zg-pre-editing #datosGeneralesCard{margin-bottom:40px}
@media(max-width:700px){.zg-edit-banner{align-items:flex-start;flex-direction:column}.zg-edit-back{width:100%;text-align:center}}
</style>
<div id="zgEditBanner" class="zg-edit-banner"><div><b id="zgEditBannerTitle">✏️ Edición de informe desde supervisión</b><span id="zgEditBannerText">Corrige los datos y presiona “Actualizar informe y PDF”.</span></div><a class="zg-edit-back" href="panel.php">Volver al panel</a></div>
<script id="zg-panel-editor-script">
(function(){
  function byId(id){ return document.getElementById(id); }
  function clone(v){ try{return JSON.parse(JSON.stringify(v));}catch(e){return v;} }
  function fire(el){ if(!el)return; try{el.dispatchEvent(new Event('input',{bubbles:true}));el.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){} }

  window.zgCollectReportSnapshot = function(){
    const fields = {};
    document.querySelectorAll('input[id],select[id],textarea[id]').forEach(function(el){
      if(el.type === 'file') return;
      fields[el.id] = {type:el.type || el.tagName.toLowerCase(), value:el.value == null ? '' : String(el.value), checked:!!el.checked};
    });
    return {
      version: 3,
      fields: fields,
      state: {
        tecnicoId: String(state.tecnicoId || ''),
        tecnicoNombre: String(state.tecnicoNombre || ''),
        customSeq: Number(state.customSeq || 0),
        selected: clone(state.selected || {})
      },
      preEvidence: clone(window.ZG_PRE_EVIDENCIAS || []),
      savedAt: new Date().toISOString()
    };
  };

  function setField(id, value, checked){
    const el=byId(id); if(!el) return;
    if((el.type==='checkbox'||el.type==='radio') && checked !== undefined) el.checked=!!checked;
    if(value !== undefined && value !== null) el.value=String(value);
    fire(el);
  }

  function drawSignature(canvasId, hiddenId, dataUrl){
    if(!dataUrl) return;
    const canvas=byId(canvasId), hidden=byId(hiddenId); if(hidden) hidden.value=dataUrl;
    if(!canvas) return;
    const img=new Image();
    img.onload=function(){
      const rect=canvas.getBoundingClientRect();
      const dpr=Math.max(1,window.devicePixelRatio||1);
      canvas.width=Math.max(500,Math.round(rect.width*dpr));
      canvas.height=Math.max(220,Math.round(rect.height*dpr));
      const ctx=canvas.getContext('2d');
      ctx.fillStyle='#fff';ctx.fillRect(0,0,canvas.width,canvas.height);
      const pad=Math.round(18*dpr), aw=canvas.width-pad*2, ah=canvas.height-pad*2;
      const sc=Math.min(aw/img.width,ah/img.height);
      const w=img.width*sc,h=img.height*sc;
      ctx.drawImage(img,(canvas.width-w)/2,(canvas.height-h)/2,w,h);
      const box=canvas.closest('.firma-box');if(box)box.classList.add('firmado');
    };
    img.src=dataUrl;
  }
  window.zgDrawStoredSignature=drawSignature;

  function restoreSnapshot(snapshot){
    if(!snapshot || typeof snapshot!=='object') return false;
    const fields=snapshot.fields||{};
    Object.keys(fields).forEach(function(id){ const f=fields[id]||{}; setField(id,f.value,f.checked); });
    if(snapshot.state){
      state.tecnicoId=String(snapshot.state.tecnicoId||state.tecnicoId||'');
      state.tecnicoNombre=String(snapshot.state.tecnicoNombre||state.tecnicoNombre||'');
      state.customSeq=Number(snapshot.state.customSeq||0);
      if(snapshot.state.selected && typeof snapshot.state.selected==='object') state.selected=clone(snapshot.state.selected);
    }
    try{renderWorkCards();renderPanels();updateCounter();}catch(e){}
    // Segunda pasada: los paneles dinámicos ya existen.
    Object.keys(fields).forEach(function(id){ const f=fields[id]||{}; setField(id,f.value,f.checked); });
    if(Array.isArray(snapshot.preEvidence)){
      window.ZG_PRE_EVIDENCIAS=clone(snapshot.preEvidence);
      try{renderPreEvidenceGrid();}catch(e){}
    }
    drawSignature('firmaTecnicoCanvas','firmaTecnico',(fields.firmaTecnico||{}).value||'');
    drawSignature('firmaAdminCanvas','firmaAdmin',(fields.firmaAdmin||{}).value||'');
    try{if(window.zgSyncEstadoInicial)window.zgSyncEstadoInicial();}catch(e){}
    try{
      if(typeof window.zgLoadEditMaterials === 'function') window.zgLoadEditMaterials(snapshot);
      else if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.pintar === 'function') window.zgRepuestosTablaFinal.pintar();
    }catch(e){console.warn('No se pudieron restaurar los materiales del informe',e);}
    return true;
  }
  window.zgRestoreReportSnapshot = restoreSnapshot;

  function restoreLegacy(report){
    if(!report) return;
    const p=report.preinspeccion||{};
    const vals={
      orden:report.orden, cliente:report.cliente, direccion:report.direccion, fecha:report.fecha,
      tecnicoId:report.tecnico_id, tecnicoInput:report.tecnico_nombre, preinspeccionId:report.preinspeccion_id,
      equipoNo:p.numero_equipo, serialUnidad:p.serie_unidad, marcaEquipo:p.marca_equipo, modeloEquipo:p.modelo_equipo,
      controladorEquipo:p.controlador, anioFabricacion:p.anio_fabricacion, refrigerante:p.refrigerante, setPoint:p.set_point,
      temperaturaAmbiente:p.temperatura_ambiente, retornoAire:p.retorno_aire, suministroAire:p.suministro_aire,
      presionAlta:p.presion_alta, presionBaja:p.presion_baja, voltajeL1L2:p.voltaje_l1_l2,
      voltajeL2L3:p.voltaje_l2_l3, voltajeL1L3:p.voltaje_l1_l3, estadoInicial:p.estado_inicial,
      observacionInicial:p.observacion_inicial, direccionCoords:(p.latitud&&p.longitud)?(p.latitud+', '+p.longitud):''
    };
    Object.keys(vals).forEach(function(id){ if(vals[id]!==undefined&&vals[id]!==null)setField(id,vals[id]); });
    state.tecnicoId=String(report.tecnico_id||'');state.tecnicoNombre=String(report.tecnico_nombre||'');
    if(window.zgTecnicoSetById) window.zgTecnicoSetById(state.tecnicoId);
    String(report.trabajos||'').split(/\s*\|\s*|\s*,\s*/).filter(Boolean).forEach(function(n){try{asegurarTrabajoSeleccionado(n);}catch(e){}});
    try{renderWorkCards();renderPanels();updateCounter();}catch(e){}
  }

  function installSupervisorUpload(){
    const canvas=byId('firmaAdminCanvas');if(!canvas)return;
    const box=canvas.closest('.firma-box');if(!box||box.querySelector('.zg-supervisor-upload'))return;
    const title=box.querySelector('.firma-title b');if(title)title.textContent='Firma del supervisor / responsable';
    const wrap=document.createElement('div');wrap.className='zg-supervisor-upload';
    wrap.innerHTML='<b>Subir firma en imagen</b><small>Úsalo cuando el supervisor o responsable no estuvo presente al cerrar el servicio. Acepta JPG o PNG con fondo claro.</small><div class="zg-supervisor-upload-row"><input class="zg-supervisor-file" id="zgSupervisorFirmaFile" type="file" accept="image/png,image/jpeg,image/webp"><button class="zg-supervisor-upload-btn" id="zgSupervisorFirmaBtn" type="button">Usar imagen</button><span class="zg-supervisor-status" id="zgSupervisorFirmaStatus"></span></div>';
    box.appendChild(wrap);
    const input=byId('zgSupervisorFirmaFile'),btn=byId('zgSupervisorFirmaBtn'),status=byId('zgSupervisorFirmaStatus');
    btn.onclick=function(){ if(!input.files||!input.files[0]){alert('Selecciona primero una imagen de la firma.');return;} const file=input.files[0]; if(file.size>6*1024*1024){alert('La imagen supera 6 MB. Usa una foto más ligera.');return;} const fr=new FileReader(); fr.onload=function(){ const img=new Image(); img.onload=function(){ const c=document.createElement('canvas');c.width=1400;c.height=520;const ctx=c.getContext('2d');ctx.fillStyle='#fff';ctx.fillRect(0,0,c.width,c.height);const pad=35,sc=Math.min((c.width-pad*2)/img.width,(c.height-pad*2)/img.height);const w=img.width*sc,h=img.height*sc;ctx.drawImage(img,(c.width-w)/2,(c.height-h)/2,w,h);const data=c.toDataURL('image/jpeg',0.90);const hidden=byId('firmaAdmin');if(hidden)hidden.value=data;drawSignature('firmaAdminCanvas','firmaAdmin',data);status.textContent='Firma cargada';};img.src=String(fr.result||'');};fr.readAsDataURL(file); };
  }

  function abrirDatosGenerales(){
    const card=byId('datosGeneralesCard');
    if(card) card.classList.remove('datos-collapsed');
    const toggle=byId('datosGeneralesToggle');
    if(toggle) toggle.setAttribute('aria-expanded','true');
    const pill=byId('datosGeneralesPill');
    if(pill) pill.textContent='Ocultar datos';
  }
  function desbloquearFormularioCompleto(){
    document.querySelectorAll('main input, main select, main textarea, main button').forEach(function(el){
      if(el.type==='hidden') return;
      if(el.id==='pdfBtn' || el.id==='clearBtn') return;
      el.disabled=false;
      if('readOnly' in el) el.readOnly=false;
      if(el.style) el.style.background='';
    });
    const pick=byId('dirPick');if(pick)pick.style.pointerEvents='';
  }
  function initEditMode(){
    if(!ZG_EDIT_MODE)return;
    document.body.classList.add('zg-editing');
    const banner=byId('zgEditBanner');if(banner)banner.classList.add('show');
    abrirDatosGenerales();
    desbloquearFormularioCompleto();
    const pdfBtn=byId('pdfBtn');if(pdfBtn){pdfBtn.lastChild.textContent=' Actualizar informe y PDF';pdfBtn.title='Guardar todos los cambios y reemplazar el PDF anterior';}
    installSupervisorUpload();
    if(!ZG_EDIT_REPORT||!ZG_EDIT_REPORT.id){alert((ZG_EDIT_REPORT&&ZG_EDIT_REPORT.error)||'No se pudo cargar el informe.');location.href='panel.php';return;}
    const ok=restoreSnapshot(ZG_EDIT_REPORT.snapshot);
    if(!ok)restoreLegacy(ZG_EDIT_REPORT);
    setTimeout(function(){abrirDatosGenerales();desbloquearFormularioCompleto();installSupervisorUpload();if(ZG_EDIT_REPORT.snapshot)restoreSnapshot(ZG_EDIT_REPORT.snapshot);else restoreLegacy(ZG_EDIT_REPORT);desbloquearFormularioCompleto();},800);
    setTimeout(function(){abrirDatosGenerales();desbloquearFormularioCompleto();installSupervisorUpload();},1800);
  }
  function initPreEditMode(){
    if(!ZG_PRE_EDIT_MODE)return;
    document.body.classList.add('zg-pre-editing');
    const banner=byId('zgEditBanner');if(banner)banner.classList.add('show');
    const title=byId('zgEditBannerTitle');if(title)title.textContent='✏️ Edición de inspección preliminar';
    const txt=byId('zgEditBannerText');if(txt)txt.textContent='Corrige primero la inspección preliminar. Al actualizarla se abrirá la continuación del servicio.';
    abrirDatosGenerales();
    desbloquearFormularioCompleto();
    const btn=byId('preBtn');if(btn){btn.disabled=false;btn.textContent='Actualizar preliminar y continuar servicio';btn.style.opacity='1';}
    const st=byId('preStatus');if(st)st.textContent='Edición administrativa habilitada';
    setTimeout(function(){abrirDatosGenerales();desbloquearFormularioCompleto();},500);
  }
  window.addEventListener('load',function(){initEditMode();initPreEditMode();});
})();
</script>



<style id="zg-edit-materials-fix-style">
body.zg-editing #repuestoQuestionCard .choice-btn.on{box-shadow:0 0 0 3px rgba(31,111,196,.12)!important}
</style>
<script id="zg-edit-materials-persistence-fix">
(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(s){ return String(s == null ? '' : s).replace(/\s+/g,' ').trim(); }
  function isEdit(){ try{return typeof ZG_EDIT_MODE !== 'undefined' && !!ZG_EDIT_MODE;}catch(e){return document.body.classList.contains('zg-editing');} }
  function inferUnit(detail, code){
    try{ if(typeof window.zgInferUnidadMaterial === 'function') return clean(window.zgInferUnidadMaterial(detail,code)||'und'); }catch(e){}
    return 'und';
  }
  function parseLine(line){
    const p=String(line||'').split('|').map(clean);
    if(!p.length || !p.join('')) return null;
    let code='',detail='',qty='1',unit='und';
    if(p.length>=4){
      code=p[0]==='-'?'':p[0];
      unit=p[p.length-1]||'und';
      qty=(p[p.length-2]||'1').replace(/[^0-9]/g,'')||'1';
      detail=p.slice(1,-2).join(' | ');
    }else if(p.length===3){
      code=p[0]==='-'?'':p[0]; detail=p[1]; qty=(p[2]||'1').replace(/[^0-9]/g,'')||'1'; unit=inferUnit(detail,code);
    }else if(p.length===2){
      code=p[0]==='-'?'':p[0]; detail=p[1]; unit=inferUnit(detail,code);
    }else{ detail=p[0]; unit=inferUnit(detail,''); }
    if(!detail) return null;
    return {codigo:code,detalle:detail,cantidad:qty,unidad:unit};
  }
  function itemsFromTextarea(){
    const ta=byId('repuestosManual');
    return String(ta?ta.value:'').split(/\r?\n/).map(parseLine).filter(Boolean);
  }
  function itemsFromTable(){
    const rows=[...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')];
    return rows.map(function(row){
      const code=clean(row.querySelector('.zg-rep-code2')?.textContent||'').replace(/^Sin código$/i,'');
      const detail=clean(row.querySelector('.zg-rep-detail2')?.value||'');
      const qty=String(row.querySelector('.zg-rep-qty2')?.value||'1').replace(/[^0-9]/g,'')||'1';
      const unit=clean(row.querySelector('.zg-rep-unit2')?.textContent||inferUnit(detail,code)||'und');
      return detail?{codigo:code,detalle:detail,cantidad:qty,unidad:unit}:null;
    }).filter(Boolean);
  }
  function serialize(items){
    return items.map(function(x){
      return (clean(x.codigo)||'-')+' | '+clean(x.detalle)+' | '+(String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1')+' | '+clean(x.unidad||inferUnit(x.detalle,x.codigo)||'und');
    }).join('\n');
  }
  function selectedYes(){
    const hidden=byId('requiereRepuesto');
    const yes=byId('repuestoSiBtn');
    const no=byId('repuestoNoBtn');
    // La decisión final del técnico manda sobre cualquier material agregado antes.
    // Si “No requiere repuesto” está activo, nunca se reactiva automáticamente por tener filas antiguas.
    if(no && no.classList.contains('on')) return false;
    if(yes && yes.classList.contains('on')) return true;
    return !!(hidden && String(hidden.value||'').toLowerCase()==='si');
  }
  function markRequired(hasMaterials){
    const hidden=byId('requiereRepuesto');
    const card=byId('repuestosCard');
    const yes=byId('repuestoSiBtn');
    const no=byId('repuestoNoBtn');
    // Solo se marca “Sí” si el técnico realmente dejó seleccionada esa opción.
    if(hasMaterials && selectedYes()){
      if(hidden) hidden.value='si';
      if(card) card.classList.remove('is-hidden');
      if(yes) yes.classList.add('on');
      if(no) no.classList.remove('on');
    }
  }
  function updateInternal(items, repaint){
    try{
      if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function'){
        const arr=window.zgRepuestosTablaFinal.materiales();
        if(Array.isArray(arr)){
          arr.splice(0,arr.length);
          items.forEach(function(x){arr.push({codigo:x.codigo,detalle:x.detalle,cantidad:x.cantidad,unidad:x.unidad});});
          if(repaint && typeof window.zgRepuestosTablaFinal.pintar==='function') window.zgRepuestosTablaFinal.pintar();
          else if(typeof window.zgRepuestosTablaFinal.guardar==='function') window.zgRepuestosTablaFinal.guardar();
        }
      }
      window.repuestosSeleccionados=items.map(function(x){return {codigo:x.codigo,detalle:x.detalle,cantidad:x.cantidad,unidad:x.unidad,nuevo:!x.codigo};});
    }catch(e){console.warn('No se pudo sincronizar la tabla de materiales',e);}
  }
  function clearAllMaterialsForFinalDecision(){
    const hidden=byId('requiereRepuesto');
    const ta=byId('repuestosManual');
    const card=byId('repuestosCard');
    const yes=byId('repuestoSiBtn');
    const no=byId('repuestoNoBtn');
    if(hidden) hidden.value='no';
    if(ta) ta.value='';
    if(card) card.classList.add('is-hidden');
    if(yes) yes.classList.remove('on');
    if(no) no.classList.add('on');

    try{
      if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function'){
        const arr=window.zgRepuestosTablaFinal.materiales();
        if(Array.isArray(arr)) arr.splice(0,arr.length);
        if(typeof window.zgRepuestosTablaFinal.pintar==='function') window.zgRepuestosTablaFinal.pintar();
        else if(typeof window.zgRepuestosTablaFinal.guardar==='function') window.zgRepuestosTablaFinal.guardar();
      }
    }catch(e){}
    try{ window.repuestosSeleccionados=[]; }catch(e){}
    try{
      Object.values((typeof state!=='undefined' && state.selected) ? state.selected : {}).forEach(function(s){
        if(!s) return;
        ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(function(k){
          if(Array.isArray(s[k])) s[k]=[];
          else if(typeof s[k]==='string') s[k]='';
        });
        if(s.campos && typeof s.campos==='object'){
          ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(function(k){
            if(Array.isArray(s.campos[k])) s.campos[k]=[];
            else if(typeof s.campos[k]==='string') s.campos[k]='';
          });
        }
      });
    }catch(e){}
    window.ZG_WORK_MATERIALS_PDF={};
    return [];
  }
  function syncBeforeSave(){
    // Si la decisión final es “No”, se descartan todas las selecciones anteriores.
    if(!selectedYes()) return clearAllMaterialsForFinalDecision();

    let items=itemsFromTable();
    if(!items.length) items=itemsFromTextarea();
    const ta=byId('repuestosManual');
    if(items.length){
      if(ta) ta.value=serialize(items);
      markRequired(true);
      updateInternal(items,false);
    }
    return items;
  }
  let materialsRestored=false;
  function sourceFromReport(snapshot){
    let txt='';
    try{ txt=String((snapshot&&snapshot.fields&&snapshot.fields.repuestosManual&&snapshot.fields.repuestosManual.value)||'').trim(); }catch(e){}
    if(!txt){
      try{ txt=String((ZG_EDIT_REPORT&&ZG_EDIT_REPORT.repuestos_manual)||'').trim(); }catch(e){}
    }
    if(!txt){
      try{ txt=String((ZG_EDIT_REPORT&&ZG_EDIT_REPORT.snapshot&&ZG_EDIT_REPORT.snapshot.fields&&ZG_EDIT_REPORT.snapshot.fields.repuestosManual&&ZG_EDIT_REPORT.snapshot.fields.repuestosManual.value)||'').trim(); }catch(e){}
    }
    return txt;
  }
  function restoreMaterials(snapshot){
    if(!isEdit() || materialsRestored) return;
    const ta=byId('repuestosManual');
    const source=sourceFromReport(snapshot);
    if(ta && source) ta.value=source;
    const items=itemsFromTextarea();
    if(!items.length) return;
    markRequired(true);
    updateInternal(items,true);
    if(ta) ta.value=serialize(items);
    const apiReady=!!(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function');
    const rows=document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr').length;
    if(apiReady && (rows>0 || items.length>0)) materialsRestored=true;
  }
  window.zgLoadEditMaterials=function(snapshot){ restoreMaterials(snapshot||null); };
  function wrapSnapshot(){
    const old=window.zgCollectReportSnapshot;
    if(typeof old!=='function' || old.__zgMaterialsFix) return;
    const next=function(){
      const requiere=selectedYes();
      const items=requiere ? syncBeforeSave() : clearAllMaterialsForFinalDecision();
      const snap=old.apply(this,arguments)||{};
      snap.fields=snap.fields||{};
      const txt=requiere ? serialize(items) : '';
      snap.fields.repuestosManual={type:'textarea',value:txt,checked:false};
      snap.fields.requiereRepuesto={type:'hidden',value:requiere?'si':'no',checked:false};
      if(!requiere && snap.state && snap.state.selected){
        Object.values(snap.state.selected).forEach(function(s){
          if(!s) return;
          s.repuestosTrabajo=[]; s.materialesTrabajo=[]; s.materiales=[]; s.repuestos=[];
          if(s.campos){
            s.campos.repuestosTrabajo=[]; s.campos.materialesTrabajo=[];
            s.campos.materiales=[]; s.campos.repuestos=[];
          }
        });
      }
      return snap;
    };
    next.__zgMaterialsFix=true;
    window.zgCollectReportSnapshot=next;
  }
  function wrapGenerate(){
    let old=null;
    try{ old=window.generatePDF || generatePDF; }catch(e){ old=window.generatePDF; }
    if(typeof old!=='function' || old.__zgMaterialsFix) return;
    const next=async function(){
      if(selectedYes()) syncBeforeSave();
      else clearAllMaterialsForFinalDecision();
      return await old.apply(this,arguments);
    };
    next.__zgMaterialsFix=true;
    window.generatePDF=next;
    try{ generatePDF=next; }catch(e){}
  }
  function install(){
    wrapSnapshot(); wrapGenerate();
    if(isEdit()) restoreMaterials((typeof ZG_EDIT_REPORT!=='undefined'&&ZG_EDIT_REPORT)?ZG_EDIT_REPORT.snapshot:null);
  }
  document.addEventListener('click',function(ev){
    if(!ev.target || !ev.target.closest) return;
    if(ev.target.closest('#repuestoNoBtn')){
      // Se ejecuta después de los manejadores anteriores para que “No” sea definitivo.
      setTimeout(clearAllMaterialsForFinalDecision,0);
      setTimeout(clearAllMaterialsForFinalDecision,80);
      return;
    }
    if(ev.target.closest('#pdfBtn')){
      if(selectedYes()) syncBeforeSave();
      else clearAllMaterialsForFinalDecision();
    }
  },true);
  document.addEventListener('input',function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#repuestosSelectedList')) setTimeout(syncBeforeSave,0);
  },true);
  document.addEventListener('DOMContentLoaded',function(){[80,300,800,1200,1800,2600,4200].forEach(ms=>setTimeout(install,ms));});
  window.addEventListener('load',function(){[100,500,900,1300,2000,3200,5000].forEach(ms=>setTimeout(install,ms));});
  window.zgSyncEditedMaterials=syncBeforeSave;
})();
</script>


<!-- ZGROUP FIX V9 2026-06-20: tamaño contenedor + flujo GENSET + luminarias + guardado real -->
<style id="zg-fix-v8-style">
.zg-genset-pre-card{border:1.5px solid #c9ddf4;border-radius:18px;background:linear-gradient(180deg,#f7fbff,#eef6ff);padding:14px!important;margin-top:6px}
.zg-genset-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}.zg-genset-card-head b{display:block;font-family:Archivo,system-ui,sans-serif;color:#10213a;font-size:16px}.zg-genset-card-head span{display:block;color:#637790;font-size:12px;font-weight:750;margin-top:3px}
.zg-genset-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.zg-genset-grid .field{margin:0}
.zg-genset-final-field{background:linear-gradient(180deg,#fff,#f7fbff);border:1px solid #d7e6f5;border-radius:14px;padding:10px}
body.zg-mode-genset #finalControlCard{border-color:#b9d5f3!important;background:linear-gradient(180deg,#ffffff,#f4f9ff)!important}
body.zg-mode-genset #finalControlCard .final-control-head h3{color:#164f88}
body.zg-mode-genset #refrigerante{pointer-events:none}
@media(max-width:720px){.zg-genset-grid{grid-template-columns:1fr}.zg-genset-final-field{padding:8px}.zg-service-config-grid{grid-template-columns:1fr!important}}
</style>
<script id="zg-fix-v8-script">
(function(){
  'use strict';
  function byId(id){return document.getElementById(id)}
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim()}
  function equipmentType(){
    const explicit=clean(byId('zgTipoEquipo')?.value);
    if(explicit)return explicit;
    return clean(byId('marcaEquipo')?.value).toUpperCase()==='GENSET'?'Genset':'Reefer';
  }
  window.zgGetEquipmentType=equipmentType;
  const GENSET_WORKS=new Set(['genset_inspeccion_diagnostico','genset_mantenimiento_preventivo','genset_mantenimiento_correctivo','genset_cambio_aceite_filtros','genset_sistema_electrico','genset_prueba_carga']);
  function allowed(w){
    if(w&&w.custom)return true;
    const t=equipmentType();
    if(t==='Genset')return GENSET_WORKS.has(w.id);
    // Para reefer y otros equipos se conservan los trabajos existentes del panel,
    // excepto los trabajos exclusivos del generador.
    return !GENSET_WORKS.has(w.id);
  }
  function renderCardsV8(){
    if(typeof workGrid==='undefined'||!workGrid)return;
    workGrid.innerHTML='';
    const q=typeof normaliza==='function'?normaliza(String(workQuery||'').trim()):String(workQuery||'').toLowerCase();
    const customs=Object.values(state.selected||{}).filter(s=>s.custom&&!WORK_TYPES.find(w=>w.id===s.id));
    const todos=[...WORK_TYPES,...customs].filter(allowed);
    let shown=0;
    todos.forEach(function(w){if(!q||(typeof normaliza==='function'?normaliza(w.nombre):w.nombre.toLowerCase()).includes(q)){workGrid.appendChild(makeWorkCard(w));shown++;}});
    if(q&&!shown){const n=document.createElement('div');n.className='work-none';n.textContent='Sin trabajos que coincidan con "'+String(workQuery||'').trim()+'" para este tipo de equipo.';workGrid.appendChild(n);}
    const add=document.createElement('button');add.type='button';add.className='work-card add-work';add.innerHTML='<span class="ic">＋</span><span>Otro trabajo</span>';add.onclick=addCustomWork;workGrid.appendChild(add);
  }
  renderCardsV8.__zgV8=true;

  function markModeFields(){
    const type=equipmentType(), isG=type==='Genset';
    document.body.classList.toggle('zg-mode-genset',isG);
    document.querySelectorAll('.zg-reefer-pre-field').forEach(el=>{el.classList.toggle('is-hidden',isG);el.querySelectorAll('input,select,textarea').forEach(x=>x.disabled=isG);});
    const gp=byId('zgGensetPreCard');if(gp){gp.classList.toggle('is-hidden',!isG);gp.querySelectorAll('input,select,textarea').forEach(x=>x.disabled=!isG);}
    const sizeWrap=byId('zgTamanoContenedorWrap');if(sizeWrap)sizeWrap.classList.toggle('is-hidden',isG);
    const size=byId('zgTamanoContenedor');if(size){size.disabled=isG;if(isG)size.value='No aplica';}
    document.querySelectorAll('.zg-reefer-final-field').forEach(el=>{el.classList.toggle('is-hidden',isG);el.querySelectorAll('input,select,textarea').forEach(x=>x.disabled=isG);});
    document.querySelectorAll('.zg-genset-final-field').forEach(el=>{el.classList.toggle('is-hidden',!isG);el.querySelectorAll('input,select,textarea').forEach(x=>x.disabled=!isG);});
    const title=document.querySelector('#finalControlCard .final-control-head h3');if(title)title.textContent=isG?'⚡ Control final del generador':'✅ Control final del equipo reefer';
    const desc=document.querySelector('#finalControlCard .final-control-head p');if(desc)desc.textContent=isG?'Registra la condición del motor, la generación eléctrica y las pruebas finales del genset.':'Registra cómo queda el equipo después del trabajo. Esta parte confirma funcionamiento y parámetros finales.';
    const eqLabel=document.querySelector('label[for="equipoNo"]');if(eqLabel)eqLabel.textContent=isG?'N° de genset / equipo':'Contenedor / equipo';
    const eq=byId('equipoNo');if(eq)eq.placeholder=isG?'Escribe o selecciona el N° de genset':'Escribe o selecciona contenedor. Ej. ZGRU01220-7';
    const brand=byId('marcaEquipo');if(isG&&brand){brand.value='GENSET';try{brand.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){}}
    const ref=byId('refrigerante');if(isG&&ref)ref.value='No aplica';
    const obs=byId('observacionInicial');if(obs)obs.placeholder=isG?'Describe cómo se encontró el genset: motor, batería, combustible, fugas, alarmas y condición general.':'Describe cómo se encontró el equipo antes de intervenirlo.';
    try{renderWorkCards=renderCardsV8;}catch(e){window.renderWorkCards=renderCardsV8;}
    try{renderCardsV8();}catch(e){}
  }

  function clearIncompatibleSelected(){
    const bad=Object.values(state.selected||{}).filter(s=>!allowed(s));
    if(!bad.length)return true;
    const hasData=bad.some(s=>(s.photos&&s.photos.length)||clean(s.detalle)||Object.keys(s.campos||{}).some(k=>clean(s.campos[k])));
    if(hasData&&!confirm('Al cambiar el tipo de equipo se quitarán '+bad.length+' trabajo(s) que no corresponden. ¿Continuar?'))return false;
    bad.forEach(s=>delete state.selected[s.id]);
    try{renderPanels();updateCounter();}catch(e){}
    return true;
  }

  function onTypeChange(ev){
    const sel=byId('zgTipoEquipo');if(!sel)return;
    const old=sel.dataset.previousType||'';
    if(!clearIncompatibleSelected()){sel.value=old;return;}
    sel.dataset.previousType=sel.value;
    markModeFields();
  }

  function err(id,text){
    try{if(typeof fieldMsg==='function')return fieldMsg(id,text);}catch(e){}
    const x=byId(id),e=byId(id+'Error');if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});}if(e){e.textContent=text;e.classList.add('show');}try{toast(text);}catch(_){}return false;
  }
  function value(id){return clean(byId(id)?.value)}
  function validateGensetPre(){
    if(equipmentType()!=='Genset') return true;
    const req=[
      ['gensetHorometroInicial','Registra el horómetro inicial del genset.'],
      ['gensetVoltajeBateriaInicial','Registra el voltaje inicial de la batería.'],
      ['gensetNivelCombustibleInicial','Selecciona el nivel inicial de combustible.'],
      ['gensetNivelAceiteInicial','Selecciona el nivel inicial de aceite.'],
      ['gensetRefrigeranteMotorInicial','Selecciona el estado del refrigerante del motor.'],
      ['gensetArranqueInicial','Selecciona el resultado de la prueba de arranque inicial.']
    ];
    for(const p of req){ if(!value(p[0])) return err(p[0],p[1]); }
    return true;
  }

  function validateGensetFinal(){
    const req=[['gensetEstadoFinal','Selecciona el estado final del genset.'],['gensetHorometroFinal','Registra el horómetro final.'],['gensetArranqueFinal','Selecciona el resultado de la prueba de arranque.'],['gensetVoltajeBateriaFinal','Registra el voltaje final de la batería.'],['gensetVoltajeSalidaL1L2','Registra el voltaje de salida L1-L2.'],['gensetVoltajeSalidaL2L3','Registra el voltaje de salida L2-L3.'],['gensetVoltajeSalidaL1L3','Registra el voltaje de salida L1-L3.'],['gensetTemperaturaMotorFinal','Registra la temperatura final del motor.'],['gensetNivelCombustibleFinal','Selecciona el nivel final de combustible.']];
    for(const p of req){if(!value(p[0]))return err(p[0],p[1]);}
    const r=value('zgRequiereOtroMantenimiento');if(!r)return err('zgRequiereOtroMantenimiento','Indica si el genset requiere otro mantenimiento.');
    if(r==='Sí'){if(!value('zgTipoOtroMantenimiento'))return err('zgTipoOtroMantenimiento','Selecciona el tipo de mantenimiento requerido.');if(value('zgMotivoOtroMantenimiento').length<10)return err('zgMotivoOtroMantenimiento','Explica la razón del mantenimiento requerido con al menos 10 caracteres.');}
    return true;
  }
  function dataFinalV8(){
    if(equipmentType()!=='Genset'){
      return {estadoFinalEquipo:value('estadoFinalEquipo'),setPointFinal:value('setPointFinal'),tempAmbienteFinal:value('tempAmbienteFinal'),presionAltaFinal:value('presionAltaFinal'),presionBajaFinal:value('presionBajaFinal'),retornoFinal:value('retornoFinal'),suministroFinal:value('suministroFinal'),voltajeFinalL1L2:value('voltajeFinalL1L2'),voltajeFinalL2L3:value('voltajeFinalL2L3'),voltajeFinalL1L3:value('voltajeFinalL1L3'),requiereOtroMantenimiento:value('zgRequiereOtroMantenimiento'),tipoOtroMantenimiento:value('zgTipoOtroMantenimiento'),motivoOtroMantenimiento:value('zgMotivoOtroMantenimiento'),tipoEquipo:equipmentType()};
    }
    return {tipoEquipo:'Genset',gensetEstadoFinal:value('gensetEstadoFinal'),gensetHorometroFinal:value('gensetHorometroFinal'),gensetArranqueFinal:value('gensetArranqueFinal'),gensetPruebaCargaFinal:value('gensetPruebaCargaFinal'),gensetVoltajeBateriaFinal:value('gensetVoltajeBateriaFinal'),gensetFrecuenciaFinal:value('gensetFrecuenciaFinal'),gensetVoltajeSalidaL1L2:value('gensetVoltajeSalidaL1L2'),gensetVoltajeSalidaL2L3:value('gensetVoltajeSalidaL2L3'),gensetVoltajeSalidaL1L3:value('gensetVoltajeSalidaL1L3'),gensetPresionAceiteFinal:value('gensetPresionAceiteFinal'),gensetTemperaturaMotorFinal:value('gensetTemperaturaMotorFinal'),gensetNivelCombustibleFinal:value('gensetNivelCombustibleFinal'),requiereOtroMantenimiento:value('zgRequiereOtroMantenimiento'),tipoOtroMantenimiento:value('zgTipoOtroMantenimiento'),motivoOtroMantenimiento:value('zgMotivoOtroMantenimiento')};
  }
  function installOverrides(){
    try{
      if(typeof validarInspeccionPreliminar==='function'&&!validarInspeccionPreliminar.__zgV9){
        const oldPre=validarInspeccionPreliminar;
        const fnPre=function(){ return oldPre() && validateGensetPre(); };
        fnPre.__zgV9=true;
        validarInspeccionPreliminar=fnPre;
      }
      if(typeof validarControlFinal==='function'&&!validarControlFinal.__zgV8){const old=validarControlFinal;const fn=function(){return equipmentType()==='Genset'?validateGensetFinal():old();};fn.__zgV8=true;validarControlFinal=fn;}
      if(typeof datosControlFinal==='function'&&!datosControlFinal.__zgV8){const fn=dataFinalV8;fn.__zgV8=true;datosControlFinal=fn;}
    }catch(e){console.warn('V9 overrides',e)}
  }

  function clearV8Final(){
    ['gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal'].forEach(id=>{const x=byId(id);if(x){x.value='';x.classList.remove('input-error');}const e=byId(id+'Error');if(e){e.textContent='';e.classList.remove('show');}});
  }
  const oldClearFinal=window.zgClearFinalControlFields;
  window.zgClearFinalControlFields=function(){try{if(typeof oldClearFinal==='function')oldClearFinal();}finally{clearV8Final();}};

  function init(){
    const sel=byId('zgTipoEquipo');if(sel&&!sel.dataset.zgV8){sel.dataset.zgV8='1';sel.dataset.previousType=sel.value||'';sel.addEventListener('change',onTypeChange);}
    const brand=byId('marcaEquipo');if(brand&&!brand.dataset.zgV8){brand.dataset.zgV8='1';brand.addEventListener('change',function(){if(clean(brand.value).toUpperCase()==='GENSET'&&sel&&sel.value!=='Genset'){sel.value='Genset';onTypeChange();}});}
    installOverrides();markModeFields();
    setTimeout(function(){installOverrides();markModeFields();},300);
    setTimeout(function(){installOverrides();markModeFields();},1000);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
  window.addEventListener('load',init,{once:true});
})();
</script>
<!-- ZGROUP V12: alarmas libres + catálogos editables separados por equipo -->
<style id="zg-v12-catalog-style">
#alarmaEncontrada{font-weight:400!important}
</style>
<script id="zg-v12-catalog-script">
(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  const unique=a=>Array.from(new Set(a.filter(Boolean)));
  const type=()=>clean(byId('zgTipoEquipo')?.value);
  const models=()=>type()==='Genset'?(Array.isArray(MODELOS_GENSET_CATALOGO)?MODELOS_GENSET_CATALOGO:[]): (Array.isArray(MODELOS_REEFER_CATALOGO)?MODELOS_REEFER_CATALOGO:[]);

  function controllersFor(brand){return unique(models().filter(m=>norm(m.marca_equipo)===norm(brand)).map(m=>clean(m.controlador)));}
  function refreshMain(){
    const brand=byId('marcaEquipo'),ctrl=byId('controladorEquipo'),list=byId('controladorOpciones'),hint=byId('controladorHint');if(!brand||!ctrl||!list)return;
    const oldBrand=clean(brand.value),oldCtrl=clean(ctrl.value);
    const brands=unique(models().map(m=>clean(m.marca_equipo)));
    brand.innerHTML='<option value="">Seleccionar</option>'+brands.map(b=>'<option value="'+b.replace(/"/g,'&quot;')+'">'+b+'</option>').join('');
    if(brands.includes(oldBrand))brand.value=oldBrand;else if(type()==='Genset'&&brands.length===1)brand.value=brands[0];
    const ctrls=controllersFor(brand.value);list.innerHTML='';ctrls.forEach(c=>{const o=document.createElement('option');o.value=c;list.appendChild(o);});
    if(!ctrls.includes(oldCtrl))ctrl.value=ctrls.length===1?ctrls[0]:'';else ctrl.value=oldCtrl;
    ctrl.placeholder=ctrls.length?'Selecciona o escribe: '+ctrls.join(' / '):'Escribe controlador';
    if(hint)hint.textContent=ctrls.length?'Controladores registrados en el panel: '+ctrls.join(', '):'Crea la marca y su controlador en el panel.';
  }
  function refreshTunnel(){
    for(let i=1;i<=5;i++){
      const brand=byId('zgMachineBrand'+i),ctrl=byId('zgMachineController'+i),list=byId('zgMachineControllerList'+i);if(!brand||!ctrl||!list)continue;
      const oldB=clean(brand.value),oldC=clean(ctrl.value),brands=unique((Array.isArray(MODELOS_REEFER_CATALOGO)?MODELOS_REEFER_CATALOGO:[]).map(m=>clean(m.marca_equipo)));
      brand.innerHTML='<option value="">Seleccionar</option>'+brands.map(b=>'<option>'+b+'</option>').join('');if(brands.includes(oldB))brand.value=oldB;
      const cs=unique((Array.isArray(MODELOS_REEFER_CATALOGO)?MODELOS_REEFER_CATALOGO:[]).filter(m=>norm(m.marca_equipo)===norm(brand.value)).map(m=>clean(m.controlador)));list.innerHTML='';cs.forEach(c=>{const o=document.createElement('option');o.value=c;list.appendChild(o);});if(!cs.includes(oldC))ctrl.value='';
      if(!brand.dataset.zgV12){brand.dataset.zgV12='1';brand.addEventListener('change',refreshTunnel);}
    }
  }
  function keyFor(marca,ctrl){const b=norm(marca),c=norm(ctrl).replace(/\s+/g,'');if(b==='STAR COOL'&&c.includes('CIM6'))return 'STAR COOL CIM 6';if(b==='STAR COOL'&&c.includes('CIM5'))return 'STAR COOL CIM 5';if(b==='THERMO KING'&&c.includes('MP5000'))return 'TK MP5000';if(b==='THERMO KING'&&c.includes('MP4000'))return 'TK MP4000';if(b==='CARRIER')return 'CARRIER';if(b==='DAIKIN')return 'DAIKIN';return '';}
  function installReeferMaterials(){
    if(!window.ZG_CATALOGOS_POR_CONTROLADOR)return;
    const all=Array.isArray(REPUESTOS_REEFER_CATALOGO)?REPUESTOS_REEFER_CATALOGO:[];
    ['STAR COOL CIM 6','STAR COOL CIM 5','TK MP5000','TK MP4000','CARRIER','DAIKIN'].forEach(k=>window.ZG_CATALOGOS_POR_CONTROLADOR[k]=[]);
    const groups={};
    all.forEach(r=>{if(norm(r.controlador)==='TODOS')return;const key=keyFor(r.marca_equipo,r.controlador);if(!key)return;(groups[key]||(groups[key]=[])).push({codigo:r.codigo||'',detalle:r.detalle||'',unidad:r.unidad||''});});
    // Materiales marcados TODOS aplican a cualquier controlador de la marca.
    all.filter(r=>norm(r.controlador)==='TODOS').forEach(r=>{const b=norm(r.marca_equipo);const keys=b==='CARRIER'?['CARRIER']:b==='DAIKIN'?['DAIKIN']:b==='THERMO KING'?['TK MP5000','TK MP4000']:b==='STAR COOL'?['STAR COOL CIM 5','STAR COOL CIM 6']:[];keys.forEach(k=>(groups[k]||(groups[k]=[])).push({codigo:r.codigo||'',detalle:r.detalle||'',unidad:r.unidad||''}));});
    Object.keys(groups).forEach(k=>window.ZG_CATALOGOS_POR_CONTROLADOR[k]=groups[k]);
  }
  function genericAlarm(){const a=byId('alarmaEncontrada');if(!a)return;a.removeAttribute('list');a.placeholder='Escribe exactamente lo que aparece en la pantalla. Ej. AL15';const dl=byId('zgAlarmasOpciones');if(dl)dl.remove();}
  function bind(){
    genericAlarm();installReeferMaterials();refreshMain();refreshTunnel();
    const brand=byId('marcaEquipo');if(brand&&!brand.dataset.zgV12){brand.dataset.zgV12='1';brand.addEventListener('change',()=>setTimeout(refreshMain,0));}
    const t=byId('zgTipoEquipo');if(t&&!t.dataset.zgCatalogV12){t.dataset.zgCatalogV12='1';t.addEventListener('change',()=>setTimeout(()=>{refreshMain();refreshTunnel();},20));}
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',bind);else bind();
  window.addEventListener('load',()=>{bind();[300,900,1800].forEach(ms=>setTimeout(bind,ms));});
})();
</script>

<!-- ZGROUP V14: buscador instantáneo de técnicos -->

<script id="zg-v15-remove-invalid-mp400">
(function(){
  'use strict';
  function clean(v){ return String(v == null ? '' : v).replace(/\s+/g,'').toUpperCase(); }
  function purge(){
    document.querySelectorAll('option').forEach(function(op){
      if(clean(op.value || op.textContent) === 'MP400') op.remove();
    });
    ['controladorEquipo','zgMachineController1','zgMachineController2','zgMachineController3','zgMachineController4','zgMachineController5'].forEach(function(id){
      var el=document.getElementById(id);
      if(el && clean(el.value)==='MP400'){ el.value=''; el.dispatchEvent(new Event('change',{bubbles:true})); }
    });
  }
  document.addEventListener('DOMContentLoaded',function(){ purge(); setTimeout(purge,250); setTimeout(purge,900); });
  window.addEventListener('load',purge);
  new MutationObserver(purge).observe(document.documentElement,{childList:true,subtree:true});
})();
</script>
<script id="zg-v16-final-cleanup">
(function(){
  function apply(){
    var s=document.getElementById('zgTamanoContenedor');
    if(s){
      var current=String(s.value||'').trim();
      var allowed=['','10 pies','20 pies','40 pies'];
      Array.from(s.options).forEach(function(o){if(!allowed.includes(o.value))o.remove();});
      if(!Array.from(s.options).some(function(o){return o.value==='10 pies';})){
        [['10 pies','10 pies'],['20 pies','20 pies'],['40 pies','40 pies']].forEach(function(x){var o=document.createElement('option');o.value=x[0];o.textContent=x[1];s.appendChild(o);});
      }
      if(!allowed.includes(current))s.value='';
    }
  }
  document.addEventListener('DOMContentLoaded',apply);
  window.addEventListener('load',apply,{once:true});
})();
</script>


<!-- ZGROUP V20: repuestos coherentes, códigos visibles, recomendaciones por equipo y ayudas sin elementos sueltos -->
<style id="zg-v20-final-fixes-style">
  body > .zg-redactor-help,
  .bottom-bar ~ .zg-redactor-help,
  .sticky-actions ~ .zg-redactor-help{display:none!important;}
</style>
<script id="zg-v20-final-fixes-script">
(function(){
  'use strict';
  function byId(id){return document.getElementById(id);}
  function limpiarAyudas(){
    document.querySelectorAll('.zg-redactor-help').forEach(function(help){
      const id=String(help.dataset.zgFor||'').trim();
      const target=id?byId(id):null;
      const field=target&&target.closest?target.closest('.field'):null;
      if(!target||!field||help.closest('.field')!==field){
        if(target) delete target.dataset.zgRedactorOk;
        help.remove();
        return;
      }
      help.hidden=!!(target.disabled||target.hidden||target.closest('[hidden],.is-hidden'));
    });
  }
  function repararTodo(){
    try{
      if(typeof window.zgRepararCodigosMateriales==='function' && window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function')
        window.zgRepararCodigosMateriales(window.zgRepuestosTablaFinal.materiales());
      if(typeof window.syncRepuestosManual==='function') window.syncRepuestosManual();
      if(typeof window.renderRepuestosSeleccionados==='function') window.renderRepuestosSeleccionados();
    }catch(e){}
  }
  document.addEventListener('click',function(ev){
    if(ev.target&&ev.target.closest&&ev.target.closest('#repuestoNoBtn')){
      setTimeout(function(){repararTodo(); limpiarAyudas();},40);
    }
    if(ev.target&&ev.target.closest&&(ev.target.closest('#pdfBtn')||ev.target.closest('#preBtn'))){
      repararTodo(); limpiarAyudas();
    }
  },true);
  const mo=new MutationObserver(function(){limpiarAyudas();});
  function init(){
    limpiarAyudas();
    repararTodo();
    try{mo.observe(document.body,{childList:true,subtree:true});}catch(e){}
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
  window.addEventListener('load',function(){setTimeout(init,300);setTimeout(repararTodo,1000);},{once:true});
})();
</script>


<!-- ZGROUP V25: captura definitiva de la fecha editada antes de generar el PDF -->
<script id="zg-v25-fecha-edicion-definitiva">
(function(){
  if(typeof ZG_EDIT_MODE === 'undefined' || !ZG_EDIT_MODE) return;
  const fecha = document.getElementById('fecha');
  if(!fecha) return;
  function guardarFechaElegida(){
    const valor = String(fecha.value || '').trim();
    if(!valor) return;
    window.ZG_FECHA_PDF_ACTUAL = valor;
    window.ZG_FECHA_EDICION_CONFIRMADA = valor;
  }
  fecha.addEventListener('input', guardarFechaElegida, true);
  fecha.addEventListener('change', guardarFechaElegida, true);
  // pointerdown/click en captura se ejecuta antes del manejador que genera el PDF.
  document.addEventListener('pointerdown', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')) guardarFechaElegida();
  }, true);
  document.addEventListener('click', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')) guardarFechaElegida();
  }, true);
})();
</script>


<!-- ZGROUP V26: la fecha del informe final nunca se restaura desde la preliminar al guardar -->
<script id="zg-v26-bloqueo-fecha-informe">
(function(){
  if(typeof ZG_EDIT_MODE === 'undefined' || !ZG_EDIT_MODE) return;
  const campo = document.getElementById('fecha');
  if(!campo) return;
  let elegida = String(campo.value || '').trim();
  function guardar(){
    const v = String(campo.value || '').trim();
    if(v){
      elegida = v;
      window.ZG_FECHA_PDF_ACTUAL = v;
      window.ZG_FECHA_EDICION_CONFIRMADA = v;
    }
  }
  campo.addEventListener('input', guardar, true);
  campo.addEventListener('change', guardar, true);
  document.addEventListener('pointerdown', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')) guardar();
  }, true);
  document.addEventListener('click', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')){
      if(elegida){
        campo.value = elegida;
        campo.setAttribute('value', elegida);
        window.ZG_FECHA_PDF_ACTUAL = elegida;
        window.ZG_FECHA_EDICION_CONFIRMADA = elegida;
      }
    }
  }, true);
})();
</script>


<!-- ZGROUP V27: horario automático del servicio -->
<style id="zg-v27-service-time-style">
#finalControlCard .zg-service-time-field{background:linear-gradient(180deg,#f7fbff,#eef6ff);border:1px solid #cfe1f4;border-radius:14px;padding:10px}
#finalControlCard .zg-service-time-field input{font-weight:800;color:#17385d;background:#eef5fc!important;cursor:default}
#finalControlCard .zg-service-time-field input.zg-time-supervisor-edit{background:#fff!important;border-color:#1f6fc4!important;cursor:text}
#finalControlCard .zg-service-time-hint{font-size:11px;color:#65788f;margin-top:5px;font-weight:750}
body.zg-editing #finalControlCard .zg-service-time-field{box-shadow:0 0 0 2px rgba(31,111,196,.08)}
@media(max-width:720px){#finalControlCard .zg-service-time-field{grid-column:1/-1}}
</style>
<script id="zg-v27-service-time-script">
(function(){
  function byId(id){return document.getElementById(id)}
  function clean(v){return String(v==null?'':v).trim()}
  function pad(n){return String(n).padStart(2,'0')}
  function sqlToLocal(value){
    value=clean(value); if(!value)return '';
    const m=value.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
    return m ? `${m[1]}-${m[2]}-${m[3]}T${m[4]}:${m[5]}` : value.slice(0,16).replace(' ','T');
  }
  function nowLocal(){const d=new Date();return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`}
  function formatHuman(value){
    value=clean(value); if(!value)return '';
    const m=value.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
    return m ? `${m[3]}/${m[2]}/${m[1]} ${m[4]}:${m[5]}` : value;
  }
  window.zgSqlToLocalDateTime=sqlToLocal;
  window.zgFormatServiceDateTime=formatHuman;
  function report(){try{return (typeof ZG_EDIT_REPORT!=='undefined'&&ZG_EDIT_REPORT)||null}catch(e){return null}}
  function preliminary(){try{return (typeof PREINSPECCION!=='undefined'&&PREINSPECCION)||null}catch(e){return null}}
  function isEdit(){try{return typeof ZG_EDIT_MODE!=='undefined'&&!!ZG_EDIT_MODE}catch(e){return false}}
  function init(){
    const ini=byId('horaInicioServicio'), fin=byId('horaFinServicio'); if(!ini||!fin)return;
    const r=report(), p=preliminary();
    const start=clean((r&&r.hora_inicio_servicio)||(p&&p.hora_inicio_servicio)||(p&&p.creado_en));
    const end=clean((r&&r.hora_fin_servicio)||(p&&p.hora_fin_servicio)||(p&&p.finalizado_en));
    if(start&&!clean(ini.value))ini.value=sqlToLocal(start);
    if(end&&!clean(fin.value))fin.value=sqlToLocal(end);
    if(isEdit()){
      [ini,fin].forEach(function(el){el.readOnly=false;el.removeAttribute('aria-readonly');el.classList.add('zg-time-supervisor-edit')});
      document.querySelectorAll('.zg-service-time-hint').forEach(function(x){x.textContent='Editable únicamente desde supervisión. Al actualizar se reemplazará el PDF.'});
      if(new URLSearchParams(location.search).get('editar_horario')==='1') setTimeout(function(){byId('finalControlCard')?.scrollIntoView({behavior:'smooth',block:'start'});ini.focus()},350);
    }else{
      [ini,fin].forEach(function(el){el.readOnly=true;el.setAttribute('aria-readonly','true')});
    }
  }
  window.zgPrepareServiceTimesForFinalPdf=function(){
    const ini=byId('horaInicioServicio'), fin=byId('horaFinServicio');
    const p=preliminary();
    if(ini&&!clean(ini.value)) ini.value=sqlToLocal(clean(p&&p.hora_inicio_servicio)||clean(p&&p.creado_en)||nowLocal());
    if(fin&&!clean(fin.value)&&!isEdit()) fin.value=nowLocal();
    return {inicio:ini?ini.value:'',fin:fin?fin.value:''};
  };
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',function(){setTimeout(init,150);setTimeout(init,800)});
})();
</script>

<style id="zg-v39-iphone-camera-sign-css">
/* V39: ingreso de temperaturas negativas y cámara directa en móviles */
.zg-signed-input-wrap{display:flex;align-items:stretch;gap:7px;width:100%}
.zg-signed-input-wrap>input{min-width:0;flex:1}
.zg-sign-toggle{width:48px;min-width:48px;border:1.5px solid #b8d3f2;border-radius:13px;background:#eaf4ff;color:#155293;font-family:'Archivo',system-ui,sans-serif;font-size:24px;font-weight:900;cursor:pointer;line-height:1}
.zg-sign-toggle:hover,.zg-sign-toggle:focus{background:#dceeff;border-color:#1f6fc4;outline:none;box-shadow:0 0 0 3px rgba(31,111,196,.13)}
.zg-sign-toggle.is-negative{background:#1f6fc4;color:#fff;border-color:#1f6fc4}
.pre-evidence-source-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.zg-photo-source-box{cursor:default!important;flex-direction:column!important;text-align:center!important}
.zg-photo-source-actions{display:flex;gap:9px;justify-content:center;width:100%;margin-top:8px}
.zg-photo-source-btn{min-height:42px;border:1.5px solid #bfd7f1;border-radius:12px;background:#fff;color:#17385d;padding:10px 15px;font:inherit;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}
.zg-photo-source-btn.primary{background:#1f6fc4;color:#fff;border-color:#1f6fc4}
.zg-photo-source-btn:active{transform:scale(.98)}
@media(max-width:640px){
  .pre-evidence-source-actions{grid-template-columns:1fr}
  .pre-evidence-source-actions .pre-evidence-drop{min-height:68px}
  .zg-photo-source-actions{display:grid;grid-template-columns:1fr 1fr}
  .zg-photo-source-btn{width:100%;padding:11px 8px}
  .zg-sign-toggle{width:52px;min-width:52px;font-size:26px}
}
</style>
<script id="zg-v39-iphone-camera-sign-js">
(function(){
  const signedIds=['setPoint','temperaturaAmbiente','retornoAire','suministroAire','setPointFinal','tempAmbienteFinal','retornoFinal','suministroFinal'];
  function normalizeDecimal(v){ return String(v==null?'':v).replace(/,/g,'.').replace(/[^0-9.\-]/g,''); }
  function refresh(btn,input){ btn.classList.toggle('is-negative', String(input.value||'').trim().startsWith('-')); }
  function installSignedInput(id){
    const input=document.getElementById(id);
    if(!input || input.dataset.zgSignedReady==='1') return;
    input.dataset.zgSignedReady='1';
    input.type='text';
    input.setAttribute('inputmode','decimal');
    input.setAttribute('autocomplete','off');
    input.setAttribute('autocorrect','off');
    input.setAttribute('spellcheck','false');
    const parent=input.parentNode;
    const wrap=document.createElement('div');
    wrap.className='zg-signed-input-wrap';
    parent.insertBefore(wrap,input);
    wrap.appendChild(input);
    const btn=document.createElement('button');
    btn.type='button';
    btn.className='zg-sign-toggle';
    btn.textContent='−';
    btn.title='Cambiar entre valor positivo y negativo';
    btn.setAttribute('aria-label','Cambiar signo del valor');
    wrap.appendChild(btn);
    btn.addEventListener('click',function(){
      let v=normalizeDecimal(input.value).trim();
      if(v.startsWith('-')) v=v.slice(1);
      else v='-'+v;
      input.value=v;
      refresh(btn,input);
      input.dispatchEvent(new Event('input',{bubbles:true}));
      input.dispatchEvent(new Event('change',{bubbles:true}));
      input.focus({preventScroll:true});
      try{ input.setSelectionRange(input.value.length,input.value.length); }catch(e){}
    });
    input.addEventListener('input',function(){
      const before=input.value;
      const neg=before.trim().startsWith('-');
      let cleaned=normalizeDecimal(before);
      cleaned=cleaned.replace(/(?!^)-/g,'');
      const firstDot=cleaned.indexOf('.');
      if(firstDot>=0) cleaned=cleaned.slice(0,firstDot+1)+cleaned.slice(firstDot+1).replace(/\./g,'');
      if(neg && !cleaned.startsWith('-')) cleaned='-'+cleaned.replace(/-/g,'');
      if(cleaned!==before) input.value=cleaned;
      refresh(btn,input);
    });
    refresh(btn,input);
  }
  function install(){ signedIds.forEach(installSignedInput); }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',install); else install();
  window.addEventListener('load',install);
  // Algunos bloques se vuelven a dibujar al cambiar el tipo de equipo.
  new MutationObserver(install).observe(document.documentElement,{childList:true,subtree:true});
})();
</script>




<style id="zg-panel-catalog-silent">#ordenHint,#clienteHint,#contenedorHint,#maquinaHint,#controladorHint{display:none!important}#orden[readonly],#odooTicketRefDisplay[readonly],#odooCotizacionDisplay[readonly]{background:#eef4fa;color:#17385d;font-weight:800}</style>
</body>
</html>

<!-- ZGROUP: ocultar la ayuda de redacción después de generar el informe -->
<style id="zg-hide-redactor-after-save-style">
  body.zg-informe-guardado .zg-redactor-help{display:none!important;}
</style>
<script id="zg-hide-redactor-after-save-script">
(function(){
  function actualizarAyudaDespuesDeGuardar(){
    var box=document.getElementById('savedBox');
    var guardado=!!(box && box.classList.contains('show'));
    document.body.classList.toggle('zg-informe-guardado',guardado);
    if(guardado){
      document.querySelectorAll('.zg-redactor-help').forEach(function(el){
        el.setAttribute('aria-hidden','true');
      });
    }
  }
  function iniciar(){
    var box=document.getElementById('savedBox');
    if(box){
      try{
        new MutationObserver(actualizarAyudaDespuesDeGuardar).observe(box,{attributes:true,attributeFilter:['class'],childList:true,subtree:true});
      }catch(e){}
    }
    actualizarAyudaDespuesDeGuardar();
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',iniciar); else iniciar();
  window.addEventListener('load',actualizarAyudaDespuesDeGuardar);
})();
</script>


<!-- ZGROUP: limpieza definitiva de informe nuevo y control final -->
<style id="zg-pressure-font-final-v2">
#presionAlta,#presionBaja,#presionAltaFinal,#presionBajaFinal{
  font-family:'Manrope',system-ui,-apple-system,'Segoe UI',sans-serif!important;
  font-weight:400!important;
  font-style:normal!important;
  letter-spacing:normal!important;
}
#presionAlta:-webkit-autofill,#presionBaja:-webkit-autofill,
#presionAltaFinal:-webkit-autofill,#presionBajaFinal:-webkit-autofill{
  -webkit-text-fill-color:#10213a!important;
  font-family:'Manrope',system-ui,-apple-system,'Segoe UI',sans-serif!important;
  font-weight:400!important;
}
</style>
<script id="zg-fresh-report-reset-v2">
(function(){
  const initialPressureIds=['presionAlta','presionBaja'];
  const finalControlIds=[
    'estadoFinalEquipo','setPointFinal','tempAmbienteFinal','retornoFinal','suministroFinal',
    'presionAltaFinal','presionBajaFinal','voltajeFinalL1L2','voltajeFinalL2L3',
    'voltajeFinalL1L3','zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal','repuestosManual'
  ];
  const freshReportIds=[
    'orden','cliente','tecnicoInput','tecnicoSearch','tecnicoId','direccion','direccionCoords','direccionOrigenOdoo','obs',
    'equipoNo','serialUnidad','marcaEquipo','modeloEquipo','controladorEquipo','anioFabricacion','refrigerante',
    'setPoint','temperaturaAmbiente','retornoAire','suministroAire','presionAlta','presionBaja',
    'voltajeL1L2','voltajeL2L3','voltajeL1L3','estadoInicial','estadoEncendido','estadoEnergia','estadoAlarma','alarmaEncontrada',
    'observacionInicial','preinspeccionId','tokenContinuacion','zgModalidadComercial','zgTipoInstalacion',
    'zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','zgMaquinaPreliminarObjetivo',
    'adminTiendaNombre','adminTiendaCargo','firmaTecnico','firmaAdmin'
  ].concat(finalControlIds);

  function clearStorage(){
    try{
      [window.sessionStorage,window.localStorage].forEach(function(storage){
        const keys=[];
        for(let i=0;i<storage.length;i++){
          const k=storage.key(i);
          if(k && (k.indexOf('zg_presiones_pre_')===0 || k.indexOf('zg_post_maint_')===0 || k==='zg_pre_evidencias_pending')) keys.push(k);
        }
        keys.forEach(function(k){storage.removeItem(k);});
      });
      localStorage.removeItem('zgroup_preinspeccion_token');
      localStorage.removeItem('zgroup_preinspeccion_id');
    }catch(e){}
  }

  function hardClear(ids){
    (ids||[]).forEach(function(id){
      const el=document.getElementById(id);
      if(!el) return;
      try{
        if(el.tagName==='SELECT'){
          el.selectedIndex=0;
          el.value='';
        }else{
          el.value='';
          el.defaultValue='';
          el.removeAttribute('value');
          el.setAttribute('autocomplete','off');
        }
        el.classList.remove('input-error','ok');
        const err=document.getElementById(id+'Error');
        if(err){err.textContent='';err.classList.remove('show');}
      }catch(e){}
    });
  }

  function clearFinalOnly(){
    window.__zgBlockPressureRestore=true;
    clearStorage();
    hardClear(finalControlIds);
    if(window.zgActualizarMantenimientoFinal) window.zgActualizarMantenimientoFinal();
  }
  window.zgClearFinalControlFields=clearFinalOnly;

  function isFreshReport(){
    let fresh=false;
    try{
      const u=new URL(location.href);
      fresh=u.searchParams.get('nuevo')==='1' || sessionStorage.getItem('zg_force_new_report')==='1';
    }catch(e){}
    return fresh;
  }

  function clearFreshReport(){
    if(!isFreshReport()) return;
    window.__zgBlockPressureRestore=true;
    clearStorage();
    hardClear(freshReportIds);
    if(window.zgActualizarMantenimientoFinal) window.zgActualizarMantenimientoFinal();
    try{
      if(typeof state==='object' && state){ state.selected={}; state.customSeq=0; }
      if(typeof renderWorkCards==='function') renderWorkCards();
      if(typeof renderPanels==='function') renderPanels();
      if(typeof updateCounter==='function') updateCounter();
      if(typeof setRequiereRepuesto==='function') setRequiereRepuesto(false);
    }catch(e){}
  }

  function finishFreshMode(){
    if(!isFreshReport()) return;
    clearFreshReport();
    try{
      sessionStorage.removeItem('zg_force_new_report');
      const u=new URL(location.href);
      u.searchParams.delete('nuevo');
      u.searchParams.delete('_');
      history.replaceState(null,'',u.pathname+(u.searchParams.toString()?'?'+u.searchParams.toString():'')+u.hash);
    }catch(e){}
  }

  // Limpia inmediatamente y nuevamente después de las cargas tardías de la interfaz.
  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',function(){
      clearFreshReport();
      [50,220,600].forEach(function(ms){setTimeout(clearFreshReport,ms);});
      setTimeout(finishFreshMode,850);
    });
  }else{
    clearFreshReport();
    [50,220,600].forEach(function(ms){setTimeout(clearFreshReport,ms);});
    setTimeout(finishFreshMode,850);
  }
  window.addEventListener('pageshow',function(ev){ if(ev.persisted) clearFreshReport(); });

})();
</script>


<!-- ============================================================
     ZGROUP - CORRECCIÓN DEFINITIVA
     - La pregunta de mantenimiento reemplaza a Observación final.
     - Elimina tarjetas repetidas fuera de Control final.
     - Elimina ayudas de redacción huérfanas.
     ============================================================ -->
<style id="zg-maintenance-final-definitive-style">
  /* La decisión de mantenimiento forma parte del Control final, no una tarjeta separada. */
  #finalControlCard .zg-final-maint-question,
  #finalControlCard #zgTipoMantenimientoWrap,
  #finalControlCard #zgMotivoMantenimientoWrap{
    grid-column:1 / -1!important;
    width:100%!important;
    max-width:none!important;
    margin:0!important;
  }
  #finalControlCard .zg-final-maint-question{
    margin-top:4px!important;
    padding:14px!important;
    border:1.5px solid #cfe0f3!important;
    border-radius:15px!important;
    background:linear-gradient(180deg,#fff,#f7fbff)!important;
  }
  #finalControlCard .zg-final-maint-question label,
  #finalControlCard #zgTipoMantenimientoWrap label,
  #finalControlCard #zgMotivoMantenimientoWrap label{
    display:block!important;
    margin:0 0 7px!important;
    color:#17385d!important;
    font-family:'Manrope',system-ui,-apple-system,'Segoe UI',sans-serif!important;
    font-size:12px!important;
    font-weight:700!important;
  }
  #finalControlCard #zgRequiereOtroMantenimiento,
  #finalControlCard #zgTipoOtroMantenimiento,
  #finalControlCard #zgMotivoOtroMantenimiento{
    width:100%!important;
    max-width:none!important;
    font-family:'Manrope',system-ui,-apple-system,'Segoe UI',sans-serif!important;
    font-weight:400!important;
  }
  #finalControlCard #zgMotivoOtroMantenimiento{min-height:120px!important;line-height:1.5!important;}
  #zgTipoMantenimientoWrap.is-hidden,
  #zgMotivoMantenimientoWrap.is-hidden{display:none!important;}

  /* Nunca mostrar módulos antiguos/repetidos. */
  #zgPostRepuestoMaintenanceCard,
  .zg-work-followup,
  [data-zg-legacy-maintenance="1"]{display:none!important;}

  /* Una ayuda solo se ve dentro del mismo campo de su textarea. */
  .zg-redactor-help.zg-orphan-help,
  body.zg-informe-guardado .zg-redactor-help,
  body.zg-preliminar-guardada .zg-redactor-help{display:none!important;}
  .field > .zg-redactor-help{width:100%!important;position:static!important;inset:auto!important;}

  @media(max-width:700px){
    #finalControlCard .zg-final-maint-question,
    #finalControlCard #zgTipoMantenimientoWrap,
    #finalControlCard #zgMotivoMantenimientoWrap{grid-column:auto!important;}
  }
</style>
<script id="zg-maintenance-final-definitive-script">
(function(){
  'use strict';
  function byId(id){ return document.getElementById(id); }
  function clean(v){ return String(v == null ? '' : v).replace(/\s+/g,' ').trim(); }
  function normalize(v){ return clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function fieldWrapFromElement(el){ return el && el.closest ? (el.closest('.field') || el.parentElement) : null; }

  function removeLegacyFinalObservation(){
    const found=[];
    document.querySelectorAll('#observacionFinalEquipo, textarea[name="observacionFinalEquipo"]').forEach(function(el){
      const wrap=fieldWrapFromElement(el); if(wrap) found.push(wrap);
    });
    document.querySelectorAll('#finalControlCard .field').forEach(function(field){
      const label=field.querySelector('label');
      const txt=normalize(label ? label.textContent : '');
      if(txt.includes('observacion final de funcionamiento') || txt==='observacion final') found.push(field);
    });
    Array.from(new Set(found)).forEach(function(field){ field.remove(); });
  }

  function removeLegacyMaintenanceCards(){
    document.querySelectorAll('#zgPostRepuestoMaintenanceCard,.zg-work-followup').forEach(function(x){ x.remove(); });
    document.querySelectorAll('h2,h3,h4').forEach(function(title){
      if(!normalize(title.textContent).includes('requiere otro mantenimiento')) return;
      if(title.closest('#finalControlCard')) return;
      const box=title.closest('section,.card,.zg-post-maint-card,.zg-work-followup,div');
      if(box) box.remove();
    });
  }

  function improveText(txt){
    let s=clean(txt)
      .replace(/\bq\b/gi,'que').replace(/\bxq\b/gi,'porque').replace(/\bpq\b/gi,'porque')
      .replace(/\bsta\b/gi,'está').replace(/\beq\b/gi,'equipo')
      .replace(/\btemp\b/gi,'temperatura').replace(/\brefri\b/gi,'refrigerante');
    if(s && !/[.!?]$/.test(s)) s+='.';
    if(s) s=s.charAt(0).toUpperCase()+s.slice(1);
    return s ? 'Se recomienda programar un mantenimiento adicional debido a lo siguiente: '+s : '';
  }

  function installReasonHelp(){
    const ta=byId('zgMotivoOtroMantenimiento');
    if(!ta) return;
    const field=ta.closest('.field');
    if(!field) return;
    document.querySelectorAll('.zg-redactor-help[data-zg-for="zgMotivoOtroMantenimiento"]').forEach(function(help){
      if(help.closest('.field') !== field) help.remove();
    });
    if(field.querySelector('.zg-redactor-help[data-zg-for="zgMotivoOtroMantenimiento"]')) return;
    const wrap=document.createElement('div');
    wrap.className='zg-redactor-help';
    wrap.dataset.zgFor='zgMotivoOtroMantenimiento';
    wrap.innerHTML='<label><input type="checkbox"> Activar ayuda</label><button type="button" disabled>Mejorar explicación</button><small>Convierte la razón técnica en una explicación clara y profesional para el cliente.</small>';
    const chk=wrap.querySelector('input');
    const btn=wrap.querySelector('button');
    chk.addEventListener('change',function(){ btn.disabled=!chk.checked; });
    btn.addEventListener('click',function(){
      if(!chk.checked) return;
      if(!clean(ta.value)){
        ta.focus();
        try{ if(typeof toast==='function') toast('Escribe primero la razón del mantenimiento.'); }catch(e){}
        return;
      }
      ta.value=improveText(ta.value);
      ta.dispatchEvent(new Event('input',{bubbles:true}));
      try{ if(typeof toast==='function') toast('Explicación mejorada'); }catch(e){}
    });
    const hint=field.querySelector('.field-hint');
    if(hint) hint.insertAdjacentElement('afterend',wrap);
    else ta.insertAdjacentElement('afterend',wrap);
  }

  function setupMaintenanceFields(){
    const card=byId('finalControlCard');
    const grid=card && card.querySelector('.final-grid');
    const req=byId('zgRequiereOtroMantenimiento');
    const type=byId('zgTipoOtroMantenimiento');
    const reason=byId('zgMotivoOtroMantenimiento');
    const reqWrap=req && (req.closest('.zg-final-maint-question') || req.closest('.field'));
    const typeWrap=byId('zgTipoMantenimientoWrap') || (type && type.closest('.field'));
    const reasonWrap=byId('zgMotivoMantenimientoWrap') || (reason && reason.closest('.field'));
    if(!card || !grid || !req || !type || !reason || !reqWrap || !typeWrap || !reasonWrap) return;

    reqWrap.id='zgRequiereOtroMantenimientoWrap';
    reqWrap.classList.add('field','full','zg-final-maint-question');
    typeWrap.id='zgTipoMantenimientoWrap';
    typeWrap.classList.add('field','full','zg-maintenance-type');
    reasonWrap.id='zgMotivoMantenimientoWrap';
    reasonWrap.classList.add('field','full','zg-maintenance-reason');
    if(reqWrap.parentElement !== grid) grid.appendChild(reqWrap);
    if(typeWrap.parentElement !== grid) grid.appendChild(typeWrap);
    if(reasonWrap.parentElement !== grid) grid.appendChild(reasonWrap);

    function setVisibility(clearWhenNo){
      const value=clean(req.value);
      const show=value==='Sí';
      typeWrap.classList.toggle('is-hidden',!show);
      reasonWrap.classList.toggle('is-hidden',!show);
      typeWrap.hidden=!show;
      reasonWrap.hidden=!show;
      type.disabled=!show;
      reason.disabled=!show;
      type.required=show;
      reason.required=show;
      if(clearWhenNo && value==='No'){
        type.value='';
        reason.value='';
      }
      if(show) installReasonHelp();
    }

    if(req.dataset.zgStableMaintenanceBound!=='1'){
      req.dataset.zgStableMaintenanceBound='1';
      req.addEventListener('change',function(){ setVisibility(true); });
    }
    setVisibility(false);
    installReasonHelp();
    card.dataset.zgStableMaintenanceReady='1';
  }

  function cleanupOrphanHelpers(){
    document.querySelectorAll('.zg-redactor-help').forEach(function(help){
      const targetId=help.dataset.zgFor || '';
      if(!targetId) return;
      const target=byId(targetId);
      if(!target || !target.isConnected || target.closest('.field')!==help.closest('.field')) help.remove();
    });
  }

  function run(){
    removeLegacyFinalObservation();
    removeLegacyMaintenanceCards();
    setupMaintenanceFields();
    cleanupOrphanHelpers();
  }

  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',run,{once:true});
  else run();
  window.addEventListener('load',function(){
    run();
    setTimeout(run,300);
    setTimeout(run,1200);
  },{once:true});
  window.zgActualizarMantenimientoFinal=function(){ setupMaintenanceFields(); };
})();
</script>


<!-- ============================================================
     ZGROUP FIX V7 2026-06-20
     1) Detalle obligatorio de alarma inicial cuando se marca "Con alarma".
     2) Limpiar conserva la preliminar y borra completamente el cierre.
     3) Menú de materiales móvil: desplazar no selecciona; se elige al tocar.
     ============================================================ -->
<style id="zg-fix-v7-style">
  #zgAlarmaEncontradaWrap{
    margin-top:12px;
    padding:12px;
    border:1px solid #d9e7f5;
    border-radius:14px;
    background:#fff;
  }
  #zgAlarmaEncontradaWrap.is-hidden,
  #zgAlarmaEncontradaWrap[hidden]{display:none!important;}
  #zgAlarmaEncontradaWrap label{
    display:block;
    margin-bottom:7px;
    color:#5f7189;
    font-size:11.5px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.04em;
  }
  #alarmaEncontrada{
    width:100%;
    min-height:46px;
    border:1.5px solid #d4e2f3;
    border-radius:12px;
    padding:10px 12px;
    background:#fff;
    color:#10213a;
    font:inherit;
    font-weight:400!important;
    outline:none;
  }
  #alarmaEncontrada:focus{
    border-color:#1f6fc4;
    box-shadow:0 0 0 3px #e7f0fb;
  }

  /* En celular el listado se puede recorrer verticalmente sin elegir al arrastrar. */
  #repuestoSuggest,
  .zg-work-material-menu{
    touch-action:pan-y!important;
    overscroll-behavior:contain;
    -webkit-overflow-scrolling:touch;
  }
  #repuestoSuggest .smart-option,
  .zg-work-material-menu .zg-work-material-option{
    touch-action:pan-y!important;
    -webkit-tap-highlight-color:transparent;
  }
  @media(max-width:720px){
    #repuestoSuggest,
    .zg-work-material-menu{max-height:52vh!important;overflow-y:auto!important;}
  }
</style>
<script id="zg-fix-v7-script">
(function(){
  'use strict';
  function byId(id){ return document.getElementById(id); }
  function clean(v){ return String(v == null ? '' : v).replace(/\s+/g,' ').trim(); }
  function hasPreliminary(){ try{return typeof PREINSPECCION!=='undefined' && !!PREINSPECCION;}catch(e){return false;} }
  function msg(id,text){
    try{ if(typeof fieldMsg==='function') return fieldMsg(id,text); }catch(e){}
    const el=byId(id), err=byId(id+'Error');
    if(el){ el.classList.add('input-error'); try{el.focus();}catch(e){} }
    if(err){err.textContent=text;err.classList.add('show');}
    try{if(typeof toast==='function')toast(text);}catch(e){}
    return false;
  }

  function alarmPartsFromState(raw){
    raw=clean(raw);
    let detail='';
    const m=raw.match(/(?:con\s+alarma)\s*[:\-–]\s*(.+)$/i);
    if(m) detail=clean(m[1]);
    return {detail:detail};
  }

  function updateAlarmVisibility(clearWhenNo){
    const sel=byId('estadoAlarma');
    const wrap=byId('zgAlarmaEncontradaWrap');
    const input=byId('alarmaEncontrada');
    if(!sel || !wrap || !input) return;
    const show=clean(sel.value)==='Con alarma';
    wrap.hidden=!show;
    wrap.classList.toggle('is-hidden',!show);
    input.disabled=!show;
    input.required=show;
    if(!show && clearWhenNo){
      input.value='';
      input.classList.remove('input-error');
      const err=byId('alarmaEncontradaError'); if(err){err.textContent='';err.classList.remove('show');}
    }
  }

  function composeInitialState(){
    const a=clean(byId('estadoEncendido')?.value);
    const b=clean(byId('estadoEnergia')?.value);
    const c=clean(byId('estadoAlarma')?.value);
    const d=clean(byId('alarmaEncontrada')?.value);
    const hidden=byId('estadoInicial');
    if(a && b && c){
      const alarmText=(c==='Con alarma' && d) ? (c+': '+d) : c;
      const value=a+' / '+b+' / '+alarmText;
      if(hidden) hidden.value=value;
      return value;
    }
    return hidden ? clean(hidden.value) : '';
  }

  function restoreAlarmDetail(){
    const hidden=byId('estadoInicial');
    const input=byId('alarmaEncontrada');
    const sel=byId('estadoAlarma');
    if(!hidden || !input || !sel) return;
    const parsed=alarmPartsFromState(hidden.value);
    if(parsed.detail && !clean(input.value)) input.value=parsed.detail;
    if(parsed.detail && !clean(sel.value)) sel.value='Con alarma';
    updateAlarmVisibility(false);
    composeInitialState();
    if(hasPreliminary()){
      input.readOnly=true;
      input.style.background='#eef3f8';
    }
  }

  function installAlarmFeature(){
    const sel=byId('estadoAlarma');
    const input=byId('alarmaEncontrada');
    if(!sel || !input) return;
    if(sel.dataset.zgAlarmV7!=='1'){
      sel.dataset.zgAlarmV7='1';
      sel.addEventListener('change',function(){ updateAlarmVisibility(true); composeInitialState(); });
    }
    if(input.dataset.zgAlarmV7!=='1'){
      input.dataset.zgAlarmV7='1';
      input.addEventListener('input',function(){
        input.classList.remove('input-error');
        const err=byId('alarmaEncontradaError'); if(err){err.textContent='';err.classList.remove('show');}
        composeInitialState();
      });
      input.addEventListener('change',composeInitialState);
    }
    restoreAlarmDetail();

    /* Las funciones originales siguen siendo usadas al guardar y generar PDF.
       Se reemplazan para que el detalle de alarma forme parte del estado inicial. */
    try{ zgroupEstadoInicialCompuesto=composeInitialState; }catch(e){ window.zgroupEstadoInicialCompuesto=composeInitialState; }
    try{
      if(typeof validarEstadoInicialTriple==='function' && !validarEstadoInicialTriple.__zgAlarmV7){
        const old=validarEstadoInicialTriple;
        const next=function(){
          if(!old()) return false;
          if(clean(byId('estadoAlarma')?.value)==='Con alarma' && !clean(byId('alarmaEncontrada')?.value)){
            return msg('alarmaEncontrada','Selecciona o escribe la alarma encontrada en el equipo.');
          }
          composeInitialState();
          return true;
        };
        next.__zgAlarmV7=true;
        validarEstadoInicialTriple=next;
      }
    }catch(e){}
  }

  function clearCanvas(id){
    const canvas=byId(id); if(!canvas) return;
    try{
      const ctx=canvas.getContext('2d');
      ctx.clearRect(0,0,canvas.width,canvas.height);
      ctx.fillStyle='#fff';ctx.fillRect(0,0,canvas.width,canvas.height);
    }catch(e){}
    const box=canvas.closest('.firma-box'); if(box) box.classList.remove('firmado');
  }

  function clearGlobalMaterials(){
    const ta=byId('repuestosManual'); if(ta) ta.value='';
    try{
      const api=window.zgRepuestosTablaFinal;
      if(api && typeof api.materiales==='function'){
        const arr=api.materiales(); if(Array.isArray(arr)) arr.splice(0,arr.length);
        if(typeof api.guardar==='function') api.guardar();
        if(typeof api.pintar==='function') api.pintar();
      }
    }catch(e){}
    try{window.repuestosSeleccionados=[];}catch(e){}
    try{if(typeof setRequiereRepuesto==='function')setRequiereRepuesto(false);}catch(e){}
    const search=byId('repuestoSearch'); if(search) search.value='';
    const menu=byId('repuestoSuggest'); if(menu){menu.innerHTML='';menu.classList.remove('show');}
  }
  window.zgClearAllSelectedMaterials=clearGlobalMaterials;

  function clearPostPreliminary(){
    const saved=byId('savedBox'); if(saved) saved.classList.remove('show');
    try{if(typeof setVal==='function')setVal('obs','');else if(byId('obs'))byId('obs').value='';}catch(e){}
    try{if(typeof window.zgClearFinalControlFields==='function')window.zgClearFinalControlFields();}catch(e){}
    try{
      if(typeof MANUAL_REPORT_IDS!=='undefined') MANUAL_REPORT_IDS.forEach(function(id){const el=byId(id);if(el)el.value='';});
    }catch(e){}
    clearGlobalMaterials();

    ['adminTiendaNombre','adminTiendaCargo','firmaTecnico','firmaAdmin'].forEach(function(id){const el=byId(id);if(el)el.value='';});
    clearCanvas('firmaTecnicoCanvas');
    clearCanvas('firmaAdminCanvas');

    try{
      if(typeof state==='object' && state){ state.selected={}; state.customSeq=0; }
      if(typeof renderWorkCards==='function')renderWorkCards();
      if(typeof renderPanels==='function')renderPanels();
      if(typeof updateCounter==='function')updateCounter();
    }catch(e){}
    const workSearch=byId('workSearch'); if(workSearch) workSearch.value='';
    try{if(window.zgClearMaintenanceStorage)window.zgClearMaintenanceStorage();}catch(e){}
    try{window.__zgroupInformeFinalGenerado=false;}catch(e){}
    document.body.classList.remove('zg-informe-guardado');
    try{if(typeof toast==='function')toast('Se limpió todo el cierre. La inspección preliminar se conserva.');}catch(e){}
  }

  /* Se ejecuta antes del listener antiguo del botón. Conserva exclusivamente la preliminar. */
  document.addEventListener('click',function(ev){
    const btn=ev.target && ev.target.closest ? ev.target.closest('#clearBtn') : null;
    if(!btn || !hasPreliminary()) return;
    ev.preventDefault();
    ev.stopPropagation();
    if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
    if(!confirm('¿Limpiar todos los trabajos, materiales y datos del cierre?\n\nLa inspección preliminar se conservará.')) return false;
    clearPostPreliminary();
    return false;
  },true);

  function init(){
    installAlarmFeature();
    [120,450,1000,1900].forEach(function(ms){setTimeout(installAlarmFeature,ms);});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
  window.addEventListener('load',function(){installAlarmFeature();setTimeout(installAlarmFeature,700);},{once:true});
})();
</script>


<!-- ZGROUP V10 2026-06-20: selección inicial Reefer/Generador, formularios exclusivos y advertencia segura -->
<style id="zg-equipment-flow-v10-style">
.zg-type-first-grid{grid-template-columns:minmax(280px,520px)!important}
.zg-type-first-field select{font-size:15px!important;min-height:50px!important;border-width:2px!important}
.zg-equipment-config{margin-top:13px;border-top:1px solid #dbe7f4;padding-top:13px}
.zg-equipment-config.is-hidden,#zgEquipmentDetails.is-hidden,#finalControlCard.zg-type-hidden{display:none!important}
.zg-equipment-config-head{display:flex;flex-direction:column;gap:3px;margin-bottom:10px}
.zg-equipment-config-head b{font-family:Archivo,system-ui,sans-serif;color:#10213a;font-size:15px}
.zg-equipment-config-head span{color:#66758a;font-size:12px;font-weight:750}
.zg-common-config-grid{grid-template-columns:minmax(240px,360px)!important}
.zg-reefer-config-grid{grid-template-columns:repeat(2,minmax(220px,1fr))!important}
body:not(.zg-mode-reefer):not(.zg-mode-genset) #zgEquipmentDetails,
body:not(.zg-mode-reefer):not(.zg-mode-genset) #finalControlCard{display:none!important}
body.zg-mode-genset .zg-reefer-pre-field,
body.zg-mode-genset .zg-reefer-final-field,
body.zg-mode-genset #zgReeferConfig,
body.zg-mode-genset #zgTunnelConfig{display:none!important}
body.zg-mode-reefer #zgGensetPreCard,
body.zg-mode-reefer .zg-genset-final-field{display:none!important}
body.zg-mode-genset #zgGensetPreCard{display:block!important}
body.zg-mode-genset .zg-genset-final-field{display:block!important}
body.zg-mode-reefer .zg-reefer-pre-field,
body.zg-mode-reefer .zg-reefer-final-field{display:block!important}
.zg-type-switch-modal{position:fixed;inset:0;z-index:12000;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(9,24,44,.64);backdrop-filter:blur(7px)}
.zg-type-switch-modal.show{display:flex}
.zg-type-switch-box{width:min(530px,100%);background:#fff;border:1px solid #d9e5f2;border-radius:22px;box-shadow:0 30px 90px rgba(0,0,0,.34);overflow:hidden}
.zg-type-switch-head{display:flex;align-items:center;gap:12px;padding:18px 20px;background:linear-gradient(135deg,#eaf4ff,#ffffff);border-bottom:1px solid #c9def5}
.zg-type-switch-icon{width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#dcecff;color:#1f6fc4;font-size:22px}
.zg-type-switch-head h3{font-family:Archivo,system-ui,sans-serif;color:#155293;font-size:19px}
.zg-type-switch-body{padding:18px 20px;color:#53677f;font-weight:700;line-height:1.5}
.zg-type-switch-body strong{color:#10213a}
.zg-type-switch-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px}
.zg-type-switch-actions button{border:0;border-radius:12px;padding:11px 15px;font-family:Archivo,system-ui,sans-serif;font-weight:900;cursor:pointer}
.zg-type-switch-cancel{background:#eaf0f6;color:#10213a}.zg-type-switch-confirm{background:#1f6fc4;color:#fff;box-shadow:0 10px 24px rgba(31,111,196,.24)}
@media(max-width:640px){.zg-reefer-config-grid{grid-template-columns:1fr!important}.zg-type-switch-actions{flex-direction:column-reverse}.zg-type-switch-actions button{width:100%}}
</style>
<script id="zg-equipment-flow-v10-script">
(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const REEFER_BRANDS=['THERMO KING','CARRIER','STAR COOL','DAIKIN','OTRO'];
  const REEFER_CONTROLLERS={
    'THERMO KING':['MP3000','MP4000','MP5000'],
    'CARRIER':['MICROLINK 2I','MICROLINK 3','MICROLINK 5'],
    'STAR COOL':['CIM5','CIM6'],
    'DAIKIN':['DAIKIN'],
    'OTRO':[]
  };
  const GENERATOR_BRANDS=['THERMO KING'];
  const GENERATOR_CONTROLLERS=['SG-3000','SG-5000'];
  const GENERATOR_WORKS=new Set(['genset_mantenimiento_preventivo','genset_mantenimiento_correctivo']);
  let previousType='';
  let pendingType='';
  let typeSelect=null;

  function equipmentType(){return clean(byId('zgTipoEquipo')?.value);}
  window.zgGetEquipmentType=equipmentType;

  function labelType(v){return v==='Genset'?'generador (genset)':v==='Reefer'?'máquina reefer':'tipo de equipo';}
  function clearError(id){
    const x=byId(id),e=byId(id+'Error');
    if(x)x.classList.remove('input-error','ok');
    if(e){e.textContent='';e.classList.remove('show');}
  }
  function markError(id,msg){
    try{if(typeof fieldMsg==='function')return fieldMsg(id,msg);}catch(e){}
    const x=byId(id),er=byId(id+'Error');
    if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});setTimeout(()=>{try{x.focus()}catch(_){}},120);}
    if(er){er.textContent=msg;er.classList.add('show');}
    try{if(typeof toast==='function')toast(msg);else alert(msg);}catch(e){alert(msg)}
    return false;
  }
  function setOptions(select,values,current){
    if(!select)return;
    const keep=clean(current||select.value);
    select.innerHTML='<option value="">Seleccionar</option>';
    values.forEach(v=>{const o=document.createElement('option');o.value=v;o.textContent=v;select.appendChild(o);});
    select.value=values.includes(keep)?keep:'';
  }
  function controllerOptions(){
    const type=equipmentType();
    if(type==='Genset')return GENERATOR_CONTROLLERS.slice();
    return (REEFER_CONTROLLERS[clean(byId('marcaEquipo')?.value).toUpperCase()]||[]).slice();
  }
  function actualizarControladorV10(force=false){
    const input=byId('controladorEquipo'),list=byId('controladorOpciones'),hint=byId('controladorHint');
    if(!input||!list)return;
    const options=controllerOptions(),actual=clean(input.value).toUpperCase();
    list.innerHTML='';options.forEach(v=>{const o=document.createElement('option');o.value=v;list.appendChild(o);});
    input.placeholder=options.length?'Selecciona: '+options.join(' / '):'Selecciona o escribe controlador';
    if(force && actual && !options.includes(actual))input.value='';
    if(force && options.length===1)input.value=options[0];
    if(hint){
      hint.textContent=equipmentType()==='Genset'
        ? 'Controladores disponibles para generador: SG-3000 y SG-5000.'
        : (options.length?'Opciones sugeridas para '+(clean(byId('marcaEquipo')?.value)||'la marca')+': '+options.join(', '):'Selecciona la marca para ver controladores sugeridos.');
    }
  }
  window.actualizarOpcionesControlador=actualizarControladorV10;
  try{actualizarOpcionesControlador=actualizarControladorV10;}catch(e){}

  function configureBrandController(){
    const type=equipmentType(),brand=byId('marcaEquipo');
    if(!brand)return;
    const current=clean(brand.value).toUpperCase();
    if(type==='Genset'){
      setOptions(brand,GENERATOR_BRANDS,current);
      if(!brand.value)brand.value='THERMO KING';
    }else if(type==='Reefer'){
      setOptions(brand,REEFER_BRANDS,current==='GENSET'?'':current);
    }else setOptions(brand,REEFER_BRANDS,'');
    actualizarControladorV10(true);
  }

  function allowedWork(w){
    if(w&&w.custom)return !!equipmentType();
    const id=clean(w&&w.id);
    if(!equipmentType())return false;
    if(equipmentType()==='Genset')return GENERATOR_WORKS.has(id);
    return !GENERATOR_WORKS.has(id) && !/genset/i.test(id);
  }
  function renderCardsV10(){
    try{
      if(typeof workGrid==='undefined'||!workGrid)return;
      workGrid.innerHTML='';
      if(!equipmentType()){
        const n=document.createElement('div');n.className='work-none';n.textContent='Selecciona primero si se atenderá una máquina reefer o un generador.';workGrid.appendChild(n);return;
      }
      const q=typeof normaliza==='function'?normaliza(String(workQuery||'').trim()):String(workQuery||'').toLowerCase();
      const customs=Object.values(state.selected||{}).filter(s=>s.custom&&!WORK_TYPES.find(w=>w.id===s.id));
      const todos=[...WORK_TYPES,...customs].filter(allowedWork);
      let shown=0;
      todos.forEach(w=>{if(!q||(typeof normaliza==='function'?normaliza(w.nombre):String(w.nombre).toLowerCase()).includes(q)){workGrid.appendChild(makeWorkCard(w));shown++;}});
      if(q&&!shown){const n=document.createElement('div');n.className='work-none';n.textContent='No hay trabajos que coincidan para este tipo de equipo.';workGrid.appendChild(n);}
      const add=document.createElement('button');add.type='button';add.className='work-card add-work';add.innerHTML='<span class="ic">＋</span><span>Otro trabajo</span>';add.onclick=addCustomWork;workGrid.appendChild(add);
    }catch(e){console.warn('renderCardsV10',e)}
  }

  function setDisabled(selector,disabled){document.querySelectorAll(selector).forEach(el=>{el.disabled=!!disabled;});}
  function applyMode(){
    const type=equipmentType(),isR=type==='Reefer',isG=type==='Genset',chosen=isR||isG;
    if(!previousType&&chosen)previousType=type;
    document.body.classList.toggle('zg-mode-reefer',isR);
    document.body.classList.toggle('zg-mode-genset',isG);
    byId('zgCommonServiceConfig')?.classList.toggle('is-hidden',!chosen);
    byId('zgReeferConfig')?.classList.toggle('is-hidden',!isR);
    const details=byId('zgEquipmentDetails');if(details)details.classList.toggle('is-hidden',!chosen);
    const finalCard=byId('finalControlCard');if(finalCard)finalCard.classList.toggle('zg-type-hidden',!chosen);
    setDisabled('#zgEquipmentDetails input,#zgEquipmentDetails select,#zgEquipmentDetails textarea',!chosen);
    if(chosen){
      setDisabled('.zg-reefer-pre-field input,.zg-reefer-pre-field select,.zg-reefer-pre-field textarea',!isR);
      setDisabled('#zgGensetPreCard input,#zgGensetPreCard select,#zgGensetPreCard textarea',!isG);
      setDisabled('.zg-reefer-final-field input,.zg-reefer-final-field select,.zg-reefer-final-field textarea',!isR);
      setDisabled('.zg-genset-final-field input,.zg-genset-final-field select,.zg-genset-final-field textarea',!isG);
      ['zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento'].forEach(id=>{const x=byId(id);if(x)x.disabled=false;});
    }
    const common=byId('zgModalidadComercial');if(common)common.disabled=!chosen;
    const install=byId('zgTipoInstalacion');if(install)install.disabled=!isR;
    const size=byId('zgTamanoContenedor');if(size){size.disabled=!isR;if(isG)size.value='No aplica';}
    const tunnel=isR&&clean(install?.value)==='Túnel';
    byId('zgTunnelConfig')?.classList.toggle('is-hidden',!tunnel);
    const eqLabel=document.querySelector('label[for="equipoNo"]');if(eqLabel)eqLabel.textContent=isG?'N° de generador / equipo':'Contenedor / equipo';
    const eq=byId('equipoNo');if(eq)eq.placeholder=isG?'Escribe o selecciona el N° del generador':'Escribe o selecciona contenedor. Ej. ZGRU01220-7';
    const title=document.querySelector('#finalControlCard .final-control-head h3');if(title)title.textContent=isG?'⚡ Control final del generador':'✅ Control final de la máquina reefer';
    const desc=document.querySelector('#finalControlCard .final-control-head p');if(desc)desc.textContent=isG?'Registra la condición del motor, la generación eléctrica y las pruebas finales del generador.':'Registra cómo queda la máquina reefer después del trabajo.';
    const obs=byId('observacionInicial');if(obs)obs.placeholder=isG?'Describe cómo se encontró el generador: motor, batería, combustible, fugas, alarmas y condición general.':'Describe cómo se encontró la máquina reefer antes de intervenirla.';
    configureBrandController();
    try{renderWorkCards=renderCardsV10;}catch(e){window.renderWorkCards=renderCardsV10;}
    renderCardsV10();
    try{if(typeof renderPanels==='function')renderPanels();if(typeof updateCounter==='function')updateCounter();}catch(e){}
  }

  const dataIds=[
    'zgModalidadComercial','zgTipoInstalacion','zgTamanoContenedor','zgMaquinaPreliminarObjetivo',
    'equipoNo','serialUnidad','marcaEquipo','modeloEquipo','controladorEquipo','anioFabricacion','refrigerante','setPoint','temperaturaAmbiente','retornoAire','suministroAire','presionAlta','presionBaja','voltajeL1L2','voltajeL2L3','voltajeL1L3',
    'estadoEncendido','estadoEnergia','estadoAlarma','alarmaEncontrada','estadoInicial','observacionInicial',
    'gensetHorometroInicial','gensetVoltajeBateriaInicial','gensetNivelCombustibleInicial','gensetNivelAceiteInicial','gensetRefrigeranteMotorInicial','gensetArranqueInicial','gensetFrecuenciaInicial','gensetPresionAceiteInicial',
    'estadoFinalEquipo','setPointFinal','retornoFinal','suministroFinal','voltajeFinalL1L2','voltajeFinalL2L3','voltajeFinalL1L3','tempAmbienteFinal','presionAltaFinal','presionBajaFinal',
    'gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal',
    'zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','repuestosManual'
  ];
  function hasTypeData(){
    if(dataIds.some(id=>clean(byId(id)?.value)))return true;
    for(let i=1;i<=5;i++)if(['Brand','Controller','Serial'].some(k=>clean(byId('zgMachine'+k+i)?.value)))return true;
    try{if(typeof state==='object'&&state&&Object.keys(state.selected||{}).length)return true;}catch(e){}
    try{if(Array.isArray(window.ZG_PRE_EVIDENCIAS)&&window.ZG_PRE_EVIDENCIAS.length)return true;}catch(e){}
    return false;
  }
  function clearField(id){const x=byId(id);if(!x)return;if(x.type==='checkbox'||x.type==='radio')x.checked=false;else x.value='';clearError(id);}
  function clearTypeData(){
    dataIds.forEach(clearField);
    for(let i=1;i<=5;i++)['Brand','Controller','Serial'].forEach(k=>clearField('zgMachine'+k+i));
    try{if(typeof state==='object'&&state){state.selected={};state.customSeq=0;}}catch(e){}
    try{if(typeof window.zgClearAllSelectedMaterials==='function')window.zgClearAllSelectedMaterials();}catch(e){}
    try{window.ZG_PRE_EVIDENCIAS=[];if(typeof window.renderPreEvidenceGrid==='function')window.renderPreEvidenceGrid();}catch(e){}
    try{if(typeof window.zgClearFinalControlFields==='function')window.zgClearFinalControlFields();}catch(e){}
    ['firmaTecnico','firmaAdmin','adminTiendaNombre','adminTiendaCargo'].forEach(clearField);
    try{if(typeof renderPanels==='function')renderPanels();if(typeof updateCounter==='function')updateCounter();}catch(e){}
    const search=byId('workSearch');if(search)search.value='';
  }

  function ensureSwitchModal(){
    let modal=byId('zgTypeSwitchModal');if(modal)return modal;
    modal=document.createElement('div');modal.id='zgTypeSwitchModal';modal.className='zg-type-switch-modal';
    modal.innerHTML='<div class="zg-type-switch-box"><div class="zg-type-switch-head"><div class="zg-type-switch-icon">⚠️</div><h3>Cambiar el tipo de equipo</h3></div><div class="zg-type-switch-body"><p id="zgTypeSwitchText"></p><p style="margin-top:10px"><strong>Se borrarán los datos técnicos, trabajos, materiales y control final que ya se hayan completado.</strong></p></div><div class="zg-type-switch-actions"><button type="button" class="zg-type-switch-cancel" id="zgTypeSwitchCancel">Cancelar</button><button type="button" class="zg-type-switch-confirm" id="zgTypeSwitchConfirm">Sí, cambiar equipo</button></div></div>';
    document.body.appendChild(modal);
    byId('zgTypeSwitchCancel').addEventListener('click',()=>{pendingType='';modal.classList.remove('show');if(typeSelect)typeSelect.value=previousType;});
    byId('zgTypeSwitchConfirm').addEventListener('click',()=>{const next=pendingType;pendingType='';modal.classList.remove('show');clearTypeData();if(typeSelect)typeSelect.value=next;previousType=next;applyMode();try{if(typeof toast==='function')toast('Tipo de equipo cambiado. Se limpiaron los datos incompatibles.');}catch(e){}});
    modal.addEventListener('click',e=>{if(e.target===modal)byId('zgTypeSwitchCancel').click();});
    return modal;
  }
  function requestTypeChange(next){
    if(!next){if(typeSelect)typeSelect.value=previousType;return;}
    if(previousType&&next!==previousType&&hasTypeData()){
      pendingType=next;if(typeSelect)typeSelect.value=previousType;
      const modal=ensureSwitchModal(),txt=byId('zgTypeSwitchText');
      if(txt)txt.textContent='Estás por cambiar de '+labelType(previousType)+' a '+labelType(next)+'. ¿Deseas continuar?';
      modal.classList.add('show');return;
    }
    previousType=next;applyMode();
  }

  function validateConfigV10(){
    ['zgTipoEquipo','zgModalidadComercial','zgTipoInstalacion','zgTamanoContenedor','zgMaquinaPreliminarObjetivo'].forEach(clearError);
    const type=equipmentType();if(!type)return markError('zgTipoEquipo','Selecciona primero si se atenderá una máquina reefer o un generador.');
    if(!clean(byId('zgModalidadComercial')?.value))return markError('zgModalidadComercial','Selecciona si el servicio corresponde a alquiler o venta.');
    if(type==='Reefer'){
      const inst=clean(byId('zgTipoInstalacion')?.value);if(!inst)return markError('zgTipoInstalacion','Selecciona el tipo de instalación de la máquina reefer.');
      if(!clean(byId('zgTamanoContenedor')?.value))return markError('zgTamanoContenedor','Selecciona el tamaño del contenedor o indica que no aplica.');
      if(inst==='Túnel'){
        for(let i=1;i<=5;i++)for(const [k,l] of [['Brand','marca'],['Controller','controlador'],['Serial','número de serie']]){const id='zgMachine'+k+i;if(!clean(byId(id)?.value))return markError(id,'Completa '+l+' de la máquina '+i+'.');}
        if(!clean(byId('zgMaquinaPreliminarObjetivo')?.value))return markError('zgMaquinaPreliminarObjetivo','Selecciona la máquina de referencia para la preliminar.');
      }
    }
    return true;
  }
  window.zgValidarConfiguracionServicio=validateConfigV10;

  function validatePreV10(){
    if(!validateConfigV10())return false;
    try{if(typeof zgroupEstadoInicialCompuesto==='function')zgroupEstadoInicialCompuesto();}catch(e){}
    const type=equipmentType(),eqLabel=type==='Genset'?'N° de generador / equipo':'Contenedor / equipo';
    if(typeof validarTextoCampo==='function'){
      if(!validarTextoCampo('equipoNo',eqLabel,3,60,true,/^[A-Za-z0-9\-_.\/]+$/))return false;
      if(!validarTextoCampo('serialUnidad','Serial unidad',3,80,true,/^[A-Za-z0-9\-_.\/]+$/))return false;
      if(!validarTextoCampo('controladorEquipo','Controlador',2,60,false))return false;
    }
    const brand=clean(byId('marcaEquipo')?.value).toUpperCase();if(!brand)return markError('marcaEquipo','Selecciona la marca del equipo.');
    if(type==='Genset'&&brand!=='THERMO KING')return markError('marcaEquipo','Para generadores selecciona la marca THERMO KING.');
    if(type==='Genset'&&!GENERATOR_CONTROLLERS.includes(clean(byId('controladorEquipo')?.value).toUpperCase()))return markError('controladorEquipo','Selecciona SG-3000 o SG-5000 como controlador del generador.');
    try{if(typeof validarEstadoInicialTriple==='function'&&!validarEstadoInicialTriple())return false;}catch(e){}
    if(type==='Reefer'){
      if(typeof validarTemp==='function'&&!(validarTemp('setPoint',-35,30,'Set point')&&validarTemp('temperaturaAmbiente',-10,60,'Temperatura ambiente')&&validarTemp('retornoAire',-40,60,'Retorno de aire')&&validarTemp('suministroAire',-50,60,'Suministro de aire')))return false;
    }else{
      const req=[['gensetHorometroInicial','Registra el horómetro inicial del generador.'],['gensetVoltajeBateriaInicial','Registra el voltaje inicial de la batería.'],['gensetNivelCombustibleInicial','Selecciona el nivel inicial de combustible.'],['gensetNivelAceiteInicial','Selecciona el nivel inicial de aceite.'],['gensetRefrigeranteMotorInicial','Selecciona el estado del refrigerante del motor.'],['gensetArranqueInicial','Selecciona el resultado de la prueba de arranque inicial.']];
      for(const [id,msg] of req)if(!clean(byId(id)?.value))return markError(id,msg);
    }
    if(typeof validarVoltajeCampo==='function'&&!(validarVoltajeCampo('voltajeL1L2','Voltaje L1-L2')&&validarVoltajeCampo('voltajeL2L3','Voltaje L2-L3')&&validarVoltajeCampo('voltajeL1L3','Voltaje L1-L3')))return false;
    if(typeof validarTextoCampo==='function'&&!validarTextoCampo('observacionInicial','Observación inicial',3,600,false,/^[\s\S]+$/))return false;
    return true;
  }

  function validateMaintenance(){
    const req=clean(byId('zgRequiereOtroMantenimiento')?.value);if(!req)return markError('zgRequiereOtroMantenimiento','Indica si el equipo requiere otro mantenimiento.');
    if(req==='Sí'){
      if(!clean(byId('zgTipoOtroMantenimiento')?.value))return markError('zgTipoOtroMantenimiento','Selecciona el tipo de mantenimiento requerido.');
      if(clean(byId('zgMotivoOtroMantenimiento')?.value).length<10)return markError('zgMotivoOtroMantenimiento','Explica la razón del mantenimiento con al menos 10 caracteres.');
    }
    return true;
  }
  function validateFinalV10(){
    if(equipmentType()==='Genset'){
      const req=[['gensetEstadoFinal','Selecciona el estado final del generador.'],['gensetHorometroFinal','Registra el horómetro final.'],['gensetArranqueFinal','Selecciona el resultado de la prueba de arranque.'],['gensetVoltajeBateriaFinal','Registra el voltaje final de la batería.'],['gensetVoltajeSalidaL1L2','Registra el voltaje de salida L1-L2.'],['gensetVoltajeSalidaL2L3','Registra el voltaje de salida L2-L3.'],['gensetVoltajeSalidaL1L3','Registra el voltaje de salida L1-L3.'],['gensetTemperaturaMotorFinal','Registra la temperatura final del motor.'],['gensetNivelCombustibleFinal','Selecciona el nivel final de combustible.']];
      for(const [id,msg] of req)if(!clean(byId(id)?.value))return markError(id,msg);
      return validateMaintenance();
    }
    if(!clean(byId('estadoFinalEquipo')?.value))return markError('estadoFinalEquipo','Selecciona el estado final de la máquina reefer.');
    if(typeof validarTemp==='function'&&!(validarTemp('setPointFinal',-35,30,'Set point final')&&validarTemp('tempAmbienteFinal',-10,60,'Temperatura ambiente final')&&validarTemp('retornoFinal',-40,60,'Retorno de aire final')&&validarTemp('suministroFinal',-50,60,'Suministro de aire final')))return false;
    if(typeof validarVoltajeCampo==='function'&&!(validarVoltajeCampo('voltajeFinalL1L2','Voltaje final L1-L2')&&validarVoltajeCampo('voltajeFinalL2L3','Voltaje final L2-L3')&&validarVoltajeCampo('voltajeFinalL1L3','Voltaje final L1-L3')))return false;
    return validateMaintenance();
  }
  function dataFinalV10(){
    const m={requiereOtroMantenimiento:clean(byId('zgRequiereOtroMantenimiento')?.value),tipoOtroMantenimiento:clean(byId('zgTipoOtroMantenimiento')?.value),motivoOtroMantenimiento:clean(byId('zgMotivoOtroMantenimiento')?.value),tipoEquipo:equipmentType()};
    if(equipmentType()==='Genset')return Object.assign(m,{gensetEstadoFinal:clean(byId('gensetEstadoFinal')?.value),gensetHorometroFinal:clean(byId('gensetHorometroFinal')?.value),gensetArranqueFinal:clean(byId('gensetArranqueFinal')?.value),gensetPruebaCargaFinal:clean(byId('gensetPruebaCargaFinal')?.value),gensetVoltajeBateriaFinal:clean(byId('gensetVoltajeBateriaFinal')?.value),gensetFrecuenciaFinal:clean(byId('gensetFrecuenciaFinal')?.value),gensetVoltajeSalidaL1L2:clean(byId('gensetVoltajeSalidaL1L2')?.value),gensetVoltajeSalidaL2L3:clean(byId('gensetVoltajeSalidaL2L3')?.value),gensetVoltajeSalidaL1L3:clean(byId('gensetVoltajeSalidaL1L3')?.value),gensetPresionAceiteFinal:clean(byId('gensetPresionAceiteFinal')?.value),gensetTemperaturaMotorFinal:clean(byId('gensetTemperaturaMotorFinal')?.value),gensetNivelCombustibleFinal:clean(byId('gensetNivelCombustibleFinal')?.value)});
    return Object.assign(m,{estadoFinalEquipo:clean(byId('estadoFinalEquipo')?.value),setPointFinal:clean(byId('setPointFinal')?.value),tempAmbienteFinal:clean(byId('tempAmbienteFinal')?.value),presionAltaFinal:clean(byId('presionAltaFinal')?.value),presionBajaFinal:clean(byId('presionBajaFinal')?.value),retornoFinal:clean(byId('retornoFinal')?.value),suministroFinal:clean(byId('suministroFinal')?.value),voltajeFinalL1L2:clean(byId('voltajeFinalL1L2')?.value),voltajeFinalL2L3:clean(byId('voltajeFinalL2L3')?.value),voltajeFinalL1L3:clean(byId('voltajeFinalL1L3')?.value)});
  }

  function replaceListeners(){
    const old=byId('zgTipoEquipo');if(!old)return;
    const clone=old.cloneNode(true);clone.value=old.value;clone.dataset.zgV8='1';old.replaceWith(clone);typeSelect=clone;
    previousType=clean(clone.value);
    clone.addEventListener('change',()=>requestTypeChange(clean(clone.value)));
    const brandOld=byId('marcaEquipo');if(brandOld){const b=brandOld.cloneNode(true);b.value=brandOld.value;b.dataset.zgV8='1';brandOld.replaceWith(b);b.addEventListener('change',()=>{actualizarControladorV10(true);clearError('marcaEquipo');clearError('controladorEquipo');});}
    const inst=byId('zgTipoInstalacion');if(inst&&!inst.dataset.zgV10){inst.dataset.zgV10='1';inst.addEventListener('change',()=>applyMode());}
    const clearBtn=byId('clearBtn');if(clearBtn&&!clearBtn.dataset.zgTypeV10){clearBtn.dataset.zgTypeV10='1';clearBtn.addEventListener('click',()=>setTimeout(()=>{previousType=equipmentType();applyMode();},120));}
  }
  function installOverrides(){
    window.zgValidarConfiguracionServicio=validateConfigV10;
    window.validarInspeccionPreliminar=validatePreV10;try{validarInspeccionPreliminar=validatePreV10}catch(e){}
    window.validarControlFinal=validateFinalV10;try{validarControlFinal=validateFinalV10}catch(e){}
    window.datosControlFinal=dataFinalV10;try{datosControlFinal=dataFinalV10}catch(e){}
  }
  function init(){
    replaceListeners();installOverrides();ensureSwitchModal();applyMode();
    [250,800,1600,3000].forEach(ms=>setTimeout(()=>{installOverrides();applyMode();},ms));
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
  window.addEventListener('load',()=>{installOverrides();applyMode();},{once:true});
})();
</script>


<!-- ZGROUP V11 2026-06-20: catálogos separados, SG-3000/SG-5000 y sugerencias reales de generador -->
<style id="zg-v11-blue-and-genset-style">
.zg-type-switch-head{background:linear-gradient(135deg,#e8f3ff,#fff)!important;border-bottom-color:#c9def5!important}
.zg-type-switch-icon{background:#dcecff!important;color:#1f6fc4!important}
.zg-type-switch-head h3{color:#155293!important}
.zg-type-switch-confirm{background:#1f6fc4!important;color:#fff!important;box-shadow:0 10px 24px rgba(31,111,196,.24)!important}
body.zg-mode-genset .quick-assistant{background:linear-gradient(180deg,#f5faff,#eaf4ff)!important;border-color:#bfd9f4!important}
body.zg-mode-genset .template-btn.on,body.zg-mode-genset .qchip.on{background:#1f6fc4!important;border-color:#1f6fc4!important;color:#fff!important}
</style>
<script id="zg-v11-separated-catalogs-script">
(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  const isGenset=()=>clean(byId('zgTipoEquipo')?.value)==='Genset';
  const escape=v=>String(v==null?'':v).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  // Catálogo de materiales exclusivo para generadores.
  if(window.ZG_CATALOGOS_POR_CONTROLADOR){
    window.ZG_CATALOGOS_POR_CONTROLADOR['GENSET SG-3000']=(Array.isArray(REPUESTOS_GENSET_CATALOGO)?REPUESTOS_GENSET_CATALOGO:[]).filter(r=>norm(r.controlador)==='SG-3000');
    window.ZG_CATALOGOS_POR_CONTROLADOR['GENSET SG-5000']=(Array.isArray(REPUESTOS_GENSET_CATALOGO)?REPUESTOS_GENSET_CATALOGO:[]).filter(r=>norm(r.controlador)==='SG-5000');
  }

  function generatorRows(q){
    const query=norm(q);
    return (Array.isArray(GENERADORES_CATALOGO)?GENERADORES_CATALOGO:[]).filter(g=>{
      const hay=[g.numero,g.serial_unidad,g.marca_equipo,g.controlador].some(v=>norm(v).includes(query));
      return !query||hay;
    }).sort((a,b)=>String(a.numero||'').localeCompare(String(b.numero||''),'es',{numeric:true,sensitivity:'base'}));
  }
  function reeferContainers(q){
    const query=norm(q);
    const genNums=new Set((Array.isArray(GENERADORES_CATALOGO)?GENERADORES_CATALOGO:[]).map(g=>norm(g.numero)));
    return (Array.isArray(CONTENEDORES_CATALOGO)?CONTENEDORES_CATALOGO:[]).filter(c=>{
      if(genNums.has(norm(c.numero)))return false;
      return !query||[c.numero,c.serial_unidad,c.marca_equipo].some(v=>norm(v).includes(query));
    }).sort((a,b)=>String(a.numero||'').localeCompare(String(b.numero||''),'es',{numeric:true,sensitivity:'base'}));
  }
  function reeferMachines(q){
    const query=norm(q);
    const genSerials=new Set((Array.isArray(GENERADORES_CATALOGO)?GENERADORES_CATALOGO:[]).map(g=>norm(g.serial_unidad)).filter(Boolean));
    return (Array.isArray(MAQUINAS_CATALOGO)?MAQUINAS_CATALOGO:[]).filter(m=>{
      if(genSerials.has(norm(m.serial_unidad)))return false;
      const joined=norm((m.marca_equipo||'')+' '+(m.controlador||''));
      if(joined.includes('GENSET')||joined.includes('SG-3000')||joined.includes('SG 3000')||joined.includes('SG-5000')||joined.includes('SG 5000'))return false;
      return !query||[m.serial_unidad,m.marca_equipo,m.controlador,m.refrigerante].some(v=>norm(v).includes(query));
    }).sort((a,b)=>String(a.serial_unidad||'').localeCompare(String(b.serial_unidad||''),'es',{numeric:true,sensitivity:'base'}));
  }

  window.contenedoresFiltrados=function(q){return isGenset()?generatorRows(q):reeferContainers(q)};
  window.maquinasFiltradas=function(q){return isGenset()?generatorRows(q):reeferMachines(q)};

  window.mostrarContenedores=function(){
    const input=byId('equipoNo'),q=input?.value||'';
    const rows=window.contenedoresFiltrados(q);
    const items=rows.map(r=>isGenset()?{
      raw:r,main:r.numero||'',sub:['Generador registrado en panel',r.serial_unidad?'Serial: '+r.serial_unidad:'',r.controlador?'Ctrl: '+r.controlador:''].filter(Boolean).join(' · ')
    }:{raw:r,main:r.numero||'',sub:'Contenedor registrado en panel'});
    renderSmartMenu('contenedorSuggest',items,r=>{
      setVal('equipoNo',r.numero||'');
      if(isGenset()){
        if(r.serial_unidad)setVal('serialUnidad',r.serial_unidad);
        setVal('marcaEquipo','THERMO KING');
        if(r.controlador)setVal('controladorEquipo',String(r.controlador).replace(/^ZG-/i,'SG-'));
        try{if(typeof actualizarOpcionesControlador==='function')actualizarOpcionesControlador(false)}catch(e){}
        setCatalogHint('contenedorHint','Generador seleccionado desde el catálogo exclusivo de gensets.','ok');
      }else setCatalogHint('contenedorHint','Contenedor seleccionado desde el panel.','ok');
      clearFieldError('equipoNo');
    });
    if(items.length)setCatalogHint('contenedorHint',isGenset()?'Selecciona un generador creado en el apartado Generadores del panel.':'Selecciona un contenedor creado en el panel.',q?'ok':'');
    else setCatalogHint('contenedorHint',isGenset()?'No hay generadores coincidentes. Créalo en el apartado Generadores del panel.':'No hay contenedores coincidentes.','warn');
  };

  window.mostrarMaquinas=function(){
    const input=byId('serialUnidad'),q=input?.value||'';
    const rows=window.maquinasFiltradas(q);
    const items=rows.map(r=>isGenset()?{
      raw:r,main:r.serial_unidad||r.numero||'',sub:['Generador: '+(r.numero||'-'),'Marca: THERMO KING',r.controlador?'Ctrl: '+r.controlador:''].filter(Boolean).join(' · ')
    }:{raw:r,main:r.serial_unidad||'',sub:[r.marca_equipo?'Marca: '+r.marca_equipo:'',r.controlador?'Ctrl: '+r.controlador:'',r.refrigerante?'Ref: '+r.refrigerante:''].filter(Boolean).join(' · ')||'Máquina reefer registrada'});
    renderSmartMenu('maquinaSuggest',items,r=>{
      setVal('serialUnidad',r.serial_unidad||'');
      if(isGenset()){
        if(r.numero)setVal('equipoNo',r.numero);
        setVal('marcaEquipo','THERMO KING');
        if(r.controlador)setVal('controladorEquipo',String(r.controlador).replace(/^ZG-/i,'SG-'));
        try{if(typeof actualizarOpcionesControlador==='function')actualizarOpcionesControlador(false)}catch(e){}
        setCatalogHint('maquinaHint','Serial seleccionado desde el catálogo exclusivo de generadores.','ok');
      }else{
        if(r.marca_equipo)setVal('marcaEquipo',r.marca_equipo);
        if(r.controlador)setVal('controladorEquipo',r.controlador);
        if(r.refrigerante)setVal('refrigerante',r.refrigerante);
        setCatalogHint('maquinaHint','Máquina reefer seleccionada desde el panel.','ok');
      }
      clearFieldError('serialUnidad');
    });
    if(items.length)setCatalogHint('maquinaHint',isGenset()?'Selecciona el serial del generador.':'Selecciona una máquina reefer creada en el panel.',q?'ok':'');
    else setCatalogHint('maquinaHint',isGenset()?'No hay seriales de generadores coincidentes.':'No hay máquinas reefer coincidentes.','warn');
  };

  const GENSET_BANK={
    actividades:[
      'Inspección visual del motor','Verificación de nivel de aceite','Verificación de refrigerante del motor','Revisión de fugas de aceite','Revisión de fugas de combustible','Revisión de fugas de refrigerante','Revisión del filtro de aire','Revisión del filtro de combustible','Revisión del filtro de aceite','Revisión de batería y bornes','Medición de voltaje de batería','Revisión del motor de arranque','Revisión del alternador','Revisión de fajas y poleas','Revisión de radiador','Revisión de sensores del motor','Revisión de relés y fusibles','Revisión de cableado y conexiones','Revisión del controlador SG','Prueba de arranque','Prueba sin carga','Prueba bajo carga','Medición de voltajes de salida','Medición de frecuencia','Medición de presión de aceite','Medición de temperatura del motor','Limpieza técnica del generador','Registro fotográfico'
    ],
    hallazgos:[
      'Sin novedad','Aceite de motor bajo','Aceite de motor degradado','Fuga de aceite','Combustible bajo','Fuga de combustible','Filtro de combustible saturado','Filtro de aceite saturado','Filtro de aire sucio','Refrigerante del motor bajo','Fuga de refrigerante','Radiador obstruido','Batería descargada','Bornes sulfatados','Motor de arranque con falla','Alternador sin generación','Voltaje de salida fuera de rango','Frecuencia fuera de rango','Presión de aceite baja','Temperatura de motor elevada','Faja desgastada o floja','Sensor con falla','Relé o fusible dañado','Cableado o conexión floja','Alarma activa en controlador','Solenoide de combustible con falla','Ruido o vibración anormal','Generador no arranca','Generador se apaga bajo carga'
    ],
    acciones:[
      'Se cambió aceite de motor','Se reemplazó filtro de aceite','Se reemplazó filtro de combustible','Se reemplazó filtro de aire','Se completó refrigerante del motor','Se corrigió fuga de aceite','Se corrigió fuga de combustible','Se corrigió fuga de refrigerante','Se limpiaron bornes de batería','Se ajustaron conexiones de batería','Se reemplazó batería','Se ajustó o reemplazó faja','Se limpió radiador','Se reparó cableado','Se ajustaron terminales eléctricos','Se reemplazó sensor','Se reemplazó relé o fusible','Se intervino motor de arranque','Se intervino alternador','Se revisó controlador SG','Se borraron y verificaron alarmas','Se realizó prueba de arranque','Se realizó prueba sin carga','Se realizó prueba bajo carga','Se verificaron voltajes y frecuencia','Se verificó presión de aceite','Se verificó temperatura del motor','Se dejó operativo','Se deja pendiente por repuesto','Se tomó registro fotográfico'
    ]
  };
  GENSET_BANK.estados=['Operativo','Operativo con observación','Pendiente por repuesto','Requiere seguimiento','No operativo'];
  const GENSET_PRESETS={
    'Mantenimiento preventivo SG':{
      actividades:['Inspección visual del motor','Verificación de nivel de aceite','Verificación de refrigerante del motor','Revisión del filtro de aire','Revisión del filtro de combustible','Revisión del filtro de aceite','Revisión de batería y bornes','Revisión de fajas y poleas','Revisión de radiador','Revisión de cableado y conexiones','Prueba de arranque','Prueba sin carga','Medición de voltajes de salida','Medición de frecuencia','Medición de presión de aceite','Registro fotográfico'],
      hallazgos:['Sin novedad'],
      acciones:['Se realizó prueba de arranque','Se realizó prueba sin carga','Se verificaron voltajes y frecuencia','Se verificó presión de aceite','Se dejó operativo','Se tomó registro fotográfico'],recomendaciones:[],estado:'Operativo'
    },
    'Mantenimiento correctivo SG':{
      actividades:['Inspección visual del motor','Revisión de batería y bornes','Revisión del motor de arranque','Revisión del alternador','Revisión de sensores del motor','Revisión de relés y fusibles','Revisión de cableado y conexiones','Revisión del controlador SG','Prueba de arranque','Prueba bajo carga','Medición de voltajes de salida','Medición de frecuencia','Medición de presión de aceite','Registro fotográfico'],
      hallazgos:['Alarma activa en controlador'],
      acciones:['Se reparó cableado','Se ajustaron terminales eléctricos','Se borraron y verificaron alarmas','Se realizó prueba de arranque','Se realizó prueba bajo carga','Se verificaron voltajes y frecuencia','Se tomó registro fotográfico'],recomendaciones:[],estado:'Operativo con observación'
    }
  };

  const oldPresetSuggested=window.presetSugeridoPorTrabajo||presetSugeridoPorTrabajo;
  window.presetSugeridoPorTrabajo=presetSugeridoPorTrabajo=function(nombre){
    if(isGenset())return norm(nombre).includes('CORRECTIVO')?'Mantenimiento correctivo SG':'Mantenimiento preventivo SG';
    return oldPresetSuggested(nombre);
  };
  const oldApplySilent=window.aplicarPresetSilencioso||aplicarPresetSilencioso;
  window.aplicarPresetSilencioso=aplicarPresetSilencioso=function(s,nombre){
    if(!isGenset())return oldApplySilent(s,nombre);
    const key=nombre&&GENSET_PRESETS[nombre]?nombre:(norm(s.nombre).includes('CORRECTIVO')?'Mantenimiento correctivo SG':'Mantenimiento preventivo SG');
    const p=GENSET_PRESETS[key];
    s.auto=emptyAuto();s.auto.plantilla=key;
    ['actividades','hallazgos','acciones','recomendaciones'].forEach(k=>s.auto[k]=Array.isArray(p[k])?p[k].slice():[]);
    s.auto.estado=p.estado||'';
    s.detalle=generarTextoAutomaticoTrabajo(s,key);
  };
  window.buildQuickAssistant=buildQuickAssistant=function(s){
    if(!s.auto)s.auto=emptyAuto();
    const genset=isGenset();
    const bank=genset?GENSET_BANK:QUICK_BANK;
    const presetsMap=genset?GENSET_PRESETS:PRESETS;
    const tiene=['actividades','hallazgos','acciones','recomendaciones'].some(k=>Array.isArray(s.auto[k])&&s.auto[k].length);
    if(!tiene&&!(s.auto&&s.auto.limpiado))aplicarPresetSilencioso(s,presetSugeridoPorTrabajo(s.nombre||''));
    const box=document.createElement('div');box.className='quick-assistant';
    const head=document.createElement('div');head.className='quick-head';
    const htxt=document.createElement('div');htxt.innerHTML='<strong>'+(genset?'Sugerencias para generador':'Sugerencias del servicio')+'</strong><small>'+(genset?'Opciones reales de mantenimiento de motor y generación eléctrica.':'Opciones sugeridas. El técnico puede desmarcar o ajustar lo que no corresponda.')+'</small>';
    const actions=document.createElement('div');actions.className='quick-actions';
    const reset=document.createElement('button');reset.className='quick-reset';reset.type='button';reset.textContent='Limpiar opciones';reset.onclick=()=>{s.auto=emptyAuto();s.auto.limpiado=true;s.detalle='';renderPanels();toast('Opciones limpiadas. Puedes seleccionar desde cero.');};
    actions.appendChild(reset);head.append(htxt,actions);box.appendChild(head);
    const templates=document.createElement('div');templates.className='template-row';
    Object.keys(presetsMap).forEach(name=>{const b=document.createElement('button');b.type='button';b.className='template-btn'+((s.auto&&s.auto.plantilla===name)?' on':'');b.textContent=name;b.onclick=()=>applyPreset(s,name,presetsMap[name]);templates.appendChild(b);});
    box.appendChild(templates);
    const grid=document.createElement('div');grid.className='quick-grid';
    grid.appendChild(chipGroup(s,'actividades','Actividades realizadas',bank.actividades));
    grid.appendChild(chipGroup(s,'hallazgos','Observaciones',bank.hallazgos,'warn'));
    grid.appendChild(chipGroup(s,'acciones','Acciones ejecutadas',bank.acciones,'ok'));
    box.appendChild(grid);
    if(!genset&&(s.id==='revision_tecnica'||norm(s.nombre).includes('REVISION')||norm(s.nombre).includes('PTI')))box.appendChild(buildPtiBox(s));
    return box;
  };

  function refreshOnType(){
    setTimeout(()=>{
      if(isGenset()){
        try{Object.values(state.selected||{}).forEach(s=>{if(/^genset_/.test(s.id)&&(!s.auto||!String(s.auto.plantilla||'').includes(' SG')))aplicarPresetSilencioso(s,presetSugeridoPorTrabajo(s.nombre));});}catch(e){}
      }
      try{if(typeof renderPanels==='function')renderPanels()}catch(e){}
    },80);
  }
  document.addEventListener('change',e=>{if(e.target&&e.target.id==='zgTipoEquipo')refreshOnType();},true);
  window.addEventListener('load',refreshOnType,{once:true});
})();
</script>


<!-- ZGROUP V19: catálogo de generadores y formularios técnicos simplificados -->
<script id="zg-v19-maintenance-placeholders">
(function(){
  function aplicar(){
    document.querySelectorAll('textarea[id^="campo_genset_mantenimiento_preventivo_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe una precisión relevante del mantenimiento, la condición encontrada o el resultado obtenido.';
    });
    document.querySelectorAll('textarea[id^="campo_genset_mantenimiento_correctivo_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe la falla atendida, la corrección aplicada y la condición final del generador.';
    });
  }
  const mo=new MutationObserver(aplicar);
  document.addEventListener('DOMContentLoaded',function(){aplicar();try{mo.observe(document.body,{childList:true,subtree:true});}catch(e){}});
  window.addEventListener('load',aplicar);
})();
</script>

<!-- ZGROUP V38: opciones nuevas persistentes por tipo de equipo -->
<style id="zg-v35-smart-entry-css">
.zg-v35-assistant{margin:14px 0 16px;border:1.5px solid #d5e5f6;border-radius:18px;background:linear-gradient(180deg,#fff,#f8fbff);padding:14px;box-shadow:0 8px 22px rgba(16,33,58,.06);overflow:visible!important}
.zg-v35-grid,.zg-v35-group,.zg-v35-search,.zg-v35-search-wrap{overflow:visible!important}
.zg-v35-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}
.zg-v35-head strong{display:block;font-family:Archivo,system-ui,sans-serif;color:#10213a;font-size:16px}
.zg-v35-head small{display:block;margin-top:3px;color:#66758a;font-size:12px;font-weight:750;line-height:1.35}
.zg-v35-clear{border:1px solid #cfe0f3;background:#eef5fc;color:#17385d;border-radius:999px;padding:8px 11px;font:inherit;font-size:11.5px;font-weight:900;cursor:pointer;white-space:nowrap}
.zg-v35-clear:hover{background:#e1effd}
.zg-v35-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.zg-v35-group{position:relative;border:1px solid #d8e6f5;background:#fff;border-radius:15px;padding:11px;min-width:0}
.zg-v35-group .quick-title{font-family:Archivo,system-ui,sans-serif;font-size:12px;font-weight:900;color:#17385d;margin-bottom:7px}
.zg-v35-search{display:flex;gap:7px;align-items:stretch}
.zg-v35-search-wrap{position:relative;flex:1;min-width:0}
.zg-v35-input{width:100%;min-height:44px;border:1.5px solid #cfe0f3;border-radius:12px;padding:9px 11px;font:inherit;color:#10213a;background:#fff;outline:none}
.zg-v35-input:focus{border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}
.zg-v35-add{border:0;background:#1f6fc4;color:#fff;border-radius:12px;padding:0 13px;font-family:Archivo,system-ui,sans-serif;font-weight:900;cursor:pointer;box-shadow:0 8px 18px rgba(31,111,196,.18)}
.zg-v35-add:hover{filter:brightness(1.04)}
.zg-v53-ai{border:1px solid #9fc8f3;background:#e8f3ff;color:#155293;border-radius:12px;padding:0 12px;font-family:Archivo,system-ui,sans-serif;font-weight:900;cursor:pointer;white-space:nowrap}
.zg-v53-ai:hover{background:#dcecff}
.zg-v53-ai:disabled{opacity:.6;cursor:wait}
.zg-v53-ai-note{margin-top:6px;color:#667991;font-size:10.5px;font-weight:750;line-height:1.35}

.zg-v35-menu{display:none;position:absolute;z-index:100;left:0;right:0;top:calc(100% + 5px);max-height:230px;overflow:auto;background:#fff;border:1px solid #d5e3f2;border-radius:13px;box-shadow:0 18px 38px rgba(16,33,58,.18);padding:5px}
.zg-v35-menu.show{display:block}
.zg-v35-option{width:100%;border:0;background:#fff;border-radius:9px;padding:9px 10px;text-align:left;color:#10213a;font:inherit;font-size:12.5px;font-weight:800;cursor:pointer}
.zg-v35-option:hover,.zg-v35-option.active{background:#eaf3ff;color:#155293}
.zg-v35-empty{padding:10px;color:#66758a;font-size:12px;font-weight:750;line-height:1.35}
.zg-v35-tags{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px;min-height:10px}
.zg-v35-tags .qchip.on{position:relative;border:1px solid #9fc8f3;background:#e8f3ff;color:#155293;border-radius:999px;padding:7px 27px 7px 10px;font:inherit;font-size:11.5px;font-weight:900;cursor:pointer;text-align:left;line-height:1.2}
.zg-v35-tags .qchip.on::after{content:'×';position:absolute;right:9px;top:50%;transform:translateY(-50%);font-size:15px;line-height:1;color:#4d739d}
.zg-v35-group.warn .zg-v35-tags .qchip.on{border-color:#f5cf7c;background:#fff6db;color:#8a5a00}
.zg-v35-group.warn .zg-v35-tags .qchip.on::after{color:#9d7217}
.zg-v35-tip{margin-top:7px;color:#7b8a9d;font-size:10.8px;font-weight:750;line-height:1.35}
@media(max-width:760px){.zg-v35-grid{grid-template-columns:1fr}.zg-v35-head{align-items:stretch;flex-direction:column}.zg-v35-clear{align-self:flex-start}.zg-v35-search{flex-wrap:wrap}.zg-v35-search-wrap{flex-basis:100%}.zg-v35-add,.zg-v53-ai{min-height:42px;flex:1;padding:0 12px}}
</style>
<script id="zg-v35-smart-entry-js">
(function(){
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim();}
  function norm(v){return clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');}
  function unique(arr){
    const out=[], seen=new Set();
    (Array.isArray(arr)?arr:[]).forEach(function(v){
      v=clean(v); if(!v) return;
      const k=norm(v); if(seen.has(k)) return;
      seen.add(k); out.push(v);
    });
    return out;
  }
  function markChanged(){try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio();}catch(e){}}
  function isGenset(){
    const v=norm(document.getElementById('zgTipoEquipo')?.value||'');
    return v.includes('genset')||v.includes('generador');
  }

  function tipoEquipoActual(){return isGenset()?'genset':'reefer';}
  function claveTrabajo(s){
    const raw=clean((s&&s.id)||(s&&s.nombre)||'trabajo_general').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
    return raw.replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'')||'trabajo_general';
  }
  function opcionesPersonalizadas(key,s){
    try{
      const tipo=tipoEquipoActual();
      const trabajo=claveTrabajo(s);
      const tipoBanco=OPCIONES_TECNICAS_POR_TRABAJO&&OPCIONES_TECNICAS_POR_TRABAJO[tipo];
      const grupo=tipoBanco&&tipoBanco[trabajo];
      return unique(grupo&&Array.isArray(grupo[key])?grupo[key]:[]);
    }catch(e){return [];}
  }
  function asegurarBancoTrabajo(tipo,trabajo){
    const banco=typeof OPCIONES_TECNICAS_POR_TRABAJO!=='undefined'?OPCIONES_TECNICAS_POR_TRABAJO:null;
    if(!banco)return null;
    if(!banco[tipo])banco[tipo]={};
    if(!banco[tipo][trabajo])banco[tipo][trabajo]={actividades:[],hallazgos:[]};
    return banco[tipo][trabajo];
  }
  window.zgOpcionesTecnicasTrabajo=function(key,s){return opcionesPersonalizadas(key,s);};
  async function guardarOpcionPersonalizada(valor,key,s){
    valor=clean(valor);
    if(!valor||!['actividades','hallazgos'].includes(key))return;
    const tipo=tipoEquipoActual();
    const trabajo=claveTrabajo(s);
    const grupo=asegurarBancoTrabajo(tipo,trabajo);
    if(grupo){
      if(!Array.isArray(grupo[key]))grupo[key]=[];
      grupo[key]=unique(grupo[key].concat(valor));
    }

    try{
      const fd=new FormData();
      fd.append('tipo_equipo',tipo);
      fd.append('categoria',key);
      fd.append('texto',valor);
      fd.append('trabajo_clave',trabajo);
      fd.append('trabajo_nombre',clean((s&&s.nombre)||''));
      fd.append('preinspeccion_id',String(document.getElementById('preinspeccionId')?.value||window.PREINSPECCION?.id||window.PREINSPECCION?.pre_id||''));
      fd.append('token_continuacion',String(document.getElementById('tokenContinuacion')?.value||window.TOKEN_CONTINUACION||''));
      fd.append('tecnico_id',String((window.state&&window.state.tecnicoId)||''));
      const r=await fetch('registrar_opcion_tecnica.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});
      let d={};
      try{d=await r.json();}catch(e){throw new Error('El servidor no devolvió una respuesta válida.');}
      if(!r.ok||!d.ok)throw new Error((d&&d.error)||'No se pudo guardar la opción');
      if(grupo)grupo[key]=unique(grupo[key].concat(d.texto||valor));
      try{if(typeof toast==='function')toast('Opción guardada para este tipo de trabajo.');}catch(e){}
    }catch(err){
      console.warn('No se pudo registrar la opción técnica personalizada:',err);
      try{if(typeof toast==='function')toast('La opción quedó en este informe, pero no se guardó para futuros servicios: '+(err.message||err));}catch(e){}
    }
  }


  const GEN_ACTIVIDADES=[
    'Se realizó inspección visual general del generador',
    'Se realizó limpieza exterior del generador',
    'Se limpió el compartimiento del motor',
    'Se limpió el radiador',
    'Se limpió la zona de admisión de aire',
    'Se limpió el tanque de combustible',
    'Se drenó agua del sistema de combustible',
    'Se purgó el sistema de combustible',
    'Se cambió aceite de motor',
    'Se completó nivel de aceite de motor',
    'Se reemplazó filtro de aceite',
    'Se reemplazó filtro de combustible',
    'Se reemplazó filtro de aire',
    'Se completó refrigerante del motor',
    'Se reemplazó refrigerante del motor',
    'Se corrigió fuga de aceite',
    'Se corrigió fuga de combustible',
    'Se corrigió fuga de refrigerante',
    'Se ajustaron abrazaderas y mangueras',
    'Se reemplazó manguera de combustible',
    'Se reemplazó manguera de refrigerante',
    'Se ajustó la faja del motor',
    'Se reemplazó la faja del motor',
    'Se reemplazó polea de bomba de agua',
    'Se reemplazó bomba de agua',
    'Se limpió el sistema de ventilación',
    'Se limpiaron bornes de batería',
    'Se ajustaron conexiones de batería',
    'Se realizó carga de batería',
    'Se reemplazó batería',
    'Se reparó cableado eléctrico',
    'Se ajustaron terminales eléctricos',
    'Se reemplazó terminal eléctrico',
    'Se reemplazó fusible',
    'Se reemplazó relé',
    'Se reemplazó regulador de voltaje',
    'Se reemplazó sensor de RPM',
    'Se reemplazó sensor de temperatura del motor',
    'Se reemplazó sensor de presión de aceite',
    'Se reemplazó sensor de nivel de aceite',
    'Se reemplazó sensor de nivel de refrigerante',
    'Se reemplazó sensor de combustible',
    'Se reemplazó solenoide de combustible',
    'Se intervino el motor de arranque',
    'Se reemplazó el motor de arranque',
    'Se intervino el alternador',
    'Se reemplazó el alternador',
    'Se reparó el arnés principal',
    'Se reemplazó el arnés principal',
    'Se reparó la caja de control',
    'Se reemplazó el controlador SG',
    'Se reemplazó el teclado del controlador SG',
    'Se configuraron parámetros del controlador SG',
    'Se descargaron datos del controlador SG',
    'Se borraron alarmas del controlador SG',
    'Se realizó prueba de arranque',
    'Se realizó prueba sin carga',
    'Se realizó prueba bajo carga',
    'Se midió voltaje de batería',
    'Se midieron voltajes de salida',
    'Se midió frecuencia de salida',
    'Se midió presión de aceite',
    'Se midió temperatura del motor',
    'Se ajustó el voltaje de salida',
    'Se instaló receptáculo de salida 480 V',
    'Se reemplazó enchufe de salida 32 A',
    'Se reparó la conexión de salida eléctrica',
    'Se realizó limpieza técnica del generador',
    'Se realizó lubricación de componentes móviles',
    'Se aplicó tratamiento anticorrosivo',
    'Se realizó ajuste general de pernos y soportes',
    'Se reparó la tapa o estructura del generador',
    'Se dejó el generador operativo',
    'Se dejó el generador pendiente por repuesto',
    'Se tomó registro fotográfico'
  ];

  const REEFER_ACTIVIDADES=[
    'Se realizó inspección visual general de la unidad reefer',
    'Se realizó limpieza exterior de la unidad reefer',
    'Se limpió el evaporador',
    'Se limpió el condensador',
    'Se limpió la bandeja de drenaje',
    'Se limpiaron las líneas de drenaje',
    'Se destapó el drenaje del evaporador',
    'Se realizó deshielo manual del equipo',
    'Se retiró hielo acumulado',
    'Se limpió el sensor de retorno de aire',
    'Se limpió el sensor de suministro de aire',
    'Se reemplazó el sensor de retorno de aire',
    'Se reemplazó el sensor de suministro de aire',
    'Se reemplazó el sensor de temperatura ambiente',
    'Se ajustaron conexiones del sensor',
    'Se reparó cableado del sistema de control',
    'Se reemplazó cableado dañado',
    'Se ajustaron terminales eléctricos',
    'Se reemplazó fusible',
    'Se reemplazó relé',
    'Se reemplazó contactor',
    'Se reemplazó capacitor',
    'Se reemplazó protector térmico',
    'Se reemplazó teclado del controlador',
    'Se reemplazó pantalla del controlador',
    'Se reemplazó controlador de la unidad',
    'Se configuraron parámetros del controlador',
    'Se descargaron datos del controlador',
    'Se borraron alarmas del controlador',
    'Se ajustó el set point',
    'Se midió temperatura de retorno de aire',
    'Se midió temperatura de suministro de aire',
    'Se midió temperatura ambiente',
    'Se midió presión de alta',
    'Se midió presión de baja',
    'Se midieron voltajes de alimentación',
    'Se realizó prueba de aislamiento eléctrico',
    'Se realizó prueba de continuidad eléctrica',
    'Se realizó prueba de fugas de refrigerante',
    'Se corrigió fuga de refrigerante',
    'Se realizó carga de refrigerante',
    'Se recuperó refrigerante del sistema',
    'Se realizó vacío al sistema de refrigeración',
    'Se reemplazó filtro deshidratador',
    'Se reemplazó válvula de expansión',
    'Se reemplazó válvula solenoide',
    'Se reemplazó válvula de servicio',
    'Se reemplazó presostato de alta',
    'Se reemplazó presostato de baja',
    'Se intervino el compresor',
    'Se reemplazó el compresor',
    'Se completó aceite del compresor',
    'Se reemplazó motor del ventilador del evaporador',
    'Se reemplazó motor del ventilador del condensador',
    'Se reemplazó ventilador del evaporador',
    'Se reemplazó ventilador del condensador',
    'Se limpió la hélice del ventilador',
    'Se ajustó la hélice del ventilador',
    'Se reemplazó resistencia de deshielo',
    'Se reemplazó resistencia de drenaje',
    'Se reparó el circuito de deshielo',
    'Se reemplazó calefactor de puerta',
    'Se reemplazó empaque de puerta',
    'Se ajustaron bisagras de puerta',
    'Se ajustó el cierre de puerta',
    'Se reparó aislamiento térmico',
    'Se sellaron puntos de ingreso de aire',
    'Se reparó conexión de alimentación eléctrica',
    'Se reemplazó enchufe de alimentación',
    'Se instaló luminaria interior',
    'Se reemplazó luminaria interior',
    'Se instaló interruptor de luminaria',
    'Se reparó cableado de luminarias',
    'Se realizó prueba de encendido de luminarias',
    'Se realizó limpieza técnica del equipo',
    'Se realizó prueba funcional del equipo',
    'Se realizó prueba de enfriamiento',
    'Se realizó prueba de calentamiento',
    'Se ejecutó prueba PTI',
    'Se ejecutó prueba Run Test',
    'Se realizó prueba de operación final',
    'Se realizó verificación visual del ventilador condensador',
    'Se realizó verificación de los ventiladores evaporadores',
    'Se inspeccionaron los contactos eléctricos',
    'Se inspeccionaron las conexiones eléctricas en busca de conexiones sueltas o dañadas',
    'Se verificaron los circuitos de protección',
    'Se descargaron los datos del controlador',
    'Se verificó la presencia de fugas',
    'Se verificó el nivel de refrigerante',
    'Se verificaron las presiones de descarga y succión',
    'Se verificó el filtro deshidratador',
    'Se verificó la válvula digital',
    'Se inspeccionó el equipo en busca de piezas dañadas o rotas',
    'Se revisaron y ajustaron los tornillos de montaje del motor condensador',
    'Se revisaron y ajustaron los tornillos de montaje de los motores evaporadores',
    'Se revisaron y ajustaron los tornillos de montaje del compresor',
    'Se realizó limpieza interna del reefer',
    'Se dejó la unidad reefer operativa',
    'Se dejó la unidad reefer pendiente por repuesto',
    'Se tomó registro fotográfico'
  ];

  const GEN_HALLAZGOS=[
    'Sin novedad','Aceite de motor bajo','Aceite de motor degradado','Fuga de aceite','Combustible bajo','Fuga de combustible','Filtro de combustible saturado','Filtro de aceite saturado','Filtro de aire sucio','Refrigerante del motor bajo','Fuga de refrigerante','Radiador obstruido','Batería descargada','Bornes sulfatados','Motor de arranque con falla','Alternador sin generación','Voltaje de salida fuera de rango','Frecuencia fuera de rango','Presión de aceite baja','Temperatura de motor elevada','Faja desgastada o floja','Sensor con falla','Relé o fusible dañado','Cableado o conexión floja','Alarma activa en controlador','Solenoide de combustible con falla','Ruido o vibración anormal','Generador no arranca','Generador se apaga bajo carga'
  ];

  const REEFER_HALLAZGOS_ADICIONALES=[
    'Se encontró el compresor escarchado; se requiere reemplazar la válvula de expansión',
    'Se requiere actualizar el software del controlador',
    'Se realizó el mantenimiento preventivo de la unidad reefer'
  ];

  const REEFER_PREVENTIVO_PRIORITARIO=[
    'Se realizó verificación visual del ventilador condensador',
    'Se realizó verificación de los ventiladores evaporadores',
    'Se inspeccionaron los contactos eléctricos',
    'Se inspeccionaron las conexiones eléctricas en busca de conexiones sueltas o dañadas',
    'Se verificaron los circuitos de protección',
    'Se descargaron los datos del controlador',
    'Se verificó la presencia de fugas',
    'Se verificó el nivel de refrigerante',
    'Se verificaron las presiones de descarga y succión',
    'Se verificó el filtro deshidratador',
    'Se verificó la válvula digital',
    'Se inspeccionó el equipo en busca de piezas dañadas o rotas',
    'Se revisaron y ajustaron los tornillos de montaje del motor condensador',
    'Se revisaron y ajustaron los tornillos de montaje de los motores evaporadores',
    'Se revisaron y ajustaron los tornillos de montaje del compresor',
    'Se realizó limpieza interna del reefer'
  ];

  function reeferActivities(){
    return unique(REEFER_ACTIVIDADES);
  }
  function actividadesParaTrabajo(s,genset){
    const propias=opcionesPersonalizadas('actividades',s);
    if(genset)return unique(GEN_ACTIVIDADES.concat(propias));
    const nombre=norm((s&&s.nombre)||'');
    // En mantenimiento preventivo se muestran primero las actividades entregadas
    // por el usuario; el resto del catálogo y la memoria histórica quedan detrás.
    if(nombre.includes('preventivo'))return unique(REEFER_PREVENTIVO_PRIORITARIO.concat(REEFER_ACTIVIDADES,propias));
    return unique(REEFER_ACTIVIDADES.concat(propias));
  }
  function reeferFindings(){
    try{return unique((QUICK_BANK.hallazgos||[]).concat(REEFER_HALLAZGOS_ADICIONALES));}
    catch(e){return unique(['Sin novedad','Conexión floja','Fuga detectada','Suciedad acumulada','Refrigerante bajo','Equipo requiere correctivo'].concat(REEFER_HALLAZGOS_ADICIONALES));}
  }
  function ensureAuto(s){
    if(!s.auto||typeof s.auto!=='object')s.auto=emptyAuto();
    if(!Array.isArray(s.auto.actividades))s.auto.actividades=[];
    if(!Array.isArray(s.auto.hallazgos))s.auto.hallazgos=[];
    if(!Array.isArray(s.auto.acciones))s.auto.acciones=[];
    // Compatibilidad con reportes anteriores: las acciones pasan a formar parte
    // de la única lista de actividades realizadas, sin duplicarlas.
    s.auto.actividades=unique(s.auto.actividades.concat(s.auto.acciones));
    s.auto.hallazgos=unique(s.auto.hallazgos);
    s.auto.acciones=[];
    return s.auto;
  }

  function smartEntry(s,key,title,suggestions,kind){
    ensureAuto(s);
    suggestions=unique(suggestions.concat(opcionesPersonalizadas(key,s)));
    const group=document.createElement('div');
    group.className='quick-group zg-v35-group '+(kind||'');
    const ttl=document.createElement('div');ttl.className='quick-title';ttl.textContent=title;
    const search=document.createElement('div');search.className='zg-v35-search';
    const wrap=document.createElement('div');wrap.className='zg-v35-search-wrap';
    const input=document.createElement('input');input.type='text';input.className='zg-v35-input';input.autocomplete='off';input.placeholder=key==='hallazgos'?'Escribe o busca un hallazgo':'Escribe o busca una actividad realizada';
    const menu=document.createElement('div');menu.className='zg-v35-menu';
    const ai=document.createElement('button');ai.type='button';ai.className='zg-v53-ai';ai.textContent='Detallar con IA';ai.title='Organiza lo escrito sin inventar información';
    const add=document.createElement('button');add.type='button';add.className='zg-v35-add';add.textContent='Agregar';
    const tags=document.createElement('div');tags.className='zg-v35-tags';
    const tip=document.createElement('div');tip.className='zg-v35-tip';tip.textContent='Toca el cuadro para ver las opciones. También puedes escribir una nueva y presionar Enter.';
    const aiNote=document.createElement('div');aiNote.className='zg-v53-ai-note';aiNote.textContent='La IA usa el tipo de equipo, el trabajo actual y la memoria registrada para ese mismo trabajo.';
    wrap.append(input,menu);search.append(wrap,ai,add);group.append(ttl,search,tags,tip,aiNote);
    let active=-1;

    function values(){return s.auto[key];}
    function setValues(v){s.auto[key]=unique(v);s.auto.limpiado=true;markChanged();}
    function refreshSuggestions(){suggestions=unique(suggestions.concat(opcionesPersonalizadas(key,s)));}
    function renderTags(){
      tags.innerHTML='';
      values().forEach(function(v){
        const b=document.createElement('button');b.type='button';b.className='qchip on';b.textContent=v;b.title='Quitar';
        b.addEventListener('click',function(){setValues(values().filter(function(x){return norm(x)!==norm(v);}));renderTags();renderMenu();});
        tags.appendChild(b);
      });
    }
    function matches(){
      refreshSuggestions();
      const q=norm(input.value);
      const selected=new Set(values().map(norm));
      return suggestions.filter(function(v){return !selected.has(norm(v))&&(!q||norm(v).includes(q));}).slice(0,18);
    }
    function renderMenu(){
      const rows=matches();menu.innerHTML='';active=-1;
      if(!rows.length){
        const empty=document.createElement('div');empty.className='zg-v35-empty';empty.textContent=clean(input.value)?'No hay coincidencias. Presiona Enter para conservar lo escrito.':'No quedan opciones disponibles para este trabajo.';menu.appendChild(empty);
      }else rows.forEach(function(v){
        const b=document.createElement('button');b.type='button';b.className='zg-v35-option';b.textContent=v;
        b.addEventListener('click',function(){addValue(v);});menu.appendChild(b);
      });
      menu.classList.add('show');
    }
    function addValue(raw){
      let v=clean(raw||input.value);if(!v){input.focus();return;}
      refreshSuggestions();
      const exact=suggestions.find(function(x){return norm(x)===norm(v);});
      const esNueva=!exact;
      if(exact)v=exact;
      if(!values().some(function(x){return norm(x)===norm(v);})){
        setValues(values().concat(v));
        if(esNueva){
          suggestions=unique(suggestions.concat(v));
          guardarOpcionPersonalizada(v,key,s);
        }
      }
      input.value='';renderTags();renderMenu();input.focus();
    }
    async function detallarConIa(){
      const original=clean(input.value);
      if(!original){
        input.focus();
        try{toast('Escribe primero una actividad o hallazgo breve.');}catch(e){}
        return;
      }
      const old=ai.textContent;ai.disabled=true;ai.textContent='Procesando...';
      try{
        const r=await fetch('mejorar_texto_ia.php',{
          method:'POST',
          headers:{'Content-Type':'application/json','Accept':'application/json'},
          credentials:'same-origin',
          body:JSON.stringify({
            modo:'opcion_tecnica',
            categoria:key,
            texto:original,
            etiqueta:title,
            tipo_equipo:document.getElementById('zgTipoEquipo')?.value||'',
            trabajo:clean(s.nombre||''),
            trabajo_clave:claveTrabajo(s),
            actividades:s.auto.actividades||[],
            hallazgos:s.auto.hallazgos||[],
            memoria_trabajo:opcionesPersonalizadas(key,s).slice(0,35),
            preinspeccion_id:String(document.getElementById('preinspeccionId')?.value||window.PREINSPECCION?.id||window.PREINSPECCION?.pre_id||''),
            token_continuacion:String(document.getElementById('tokenContinuacion')?.value||window.TOKEN_CONTINUACION||'')
          })
        });
        let d={};try{d=await r.json();}catch(e){}
        if(!r.ok||!d.ok||d.source!=='anthropic'||!clean(d.texto))throw new Error(clean(d.error)||'No se pudo obtener la redacción.');
        input.value=clean(d.texto);
        renderMenu();input.focus();
        try{toast('Texto detallado con IA. Revísalo y presiona Agregar.');}catch(e){}
      }catch(err){
        try{toast(err.message||'No se pudo usar la ayuda de IA.');}catch(e){}
      }finally{ai.disabled=false;ai.textContent=old;}
    }
    function move(step){
      const opts=Array.from(menu.querySelectorAll('.zg-v35-option'));if(!opts.length)return;
      active=(active+step+opts.length)%opts.length;opts.forEach(function(o,i){o.classList.toggle('active',i===active);});opts[active].scrollIntoView({block:'nearest'});
    }
    input.addEventListener('focus',renderMenu);
    input.addEventListener('input',renderMenu);
    input.addEventListener('keydown',function(e){
      if(e.key==='ArrowDown'){e.preventDefault();move(1);}
      else if(e.key==='ArrowUp'){e.preventDefault();move(-1);}
      else if(e.key==='Enter'||e.key===','){
        e.preventDefault();const opts=Array.from(menu.querySelectorAll('.zg-v35-option'));if(active>=0&&opts[active])addValue(opts[active].textContent);else addValue(input.value);
      }else if(e.key==='Escape'){menu.classList.remove('show');}
    });
    ai.addEventListener('click',detallarConIa);
    add.addEventListener('click',function(){addValue(input.value);});
    input.addEventListener('blur',function(){setTimeout(function(){menu.classList.remove('show');},180);});
    renderTags();
    return group;
  }

  // Mismo formato de detalle técnico para mantenimiento preventivo y correctivo,
  // tanto en reefer como en generador.
  try{
    CAMPOS.asistencia_tecnica=[{id:'detalle_tecnico',label:'Detalle técnico de la asistencia técnica',tipo:'area'}];
    CAMPOS.mantenimiento_productivo=[{id:'detalle_tecnico',label:'Detalle técnico del mantenimiento preventivo',tipo:'area'}];
    CAMPOS.mantenimiento_correctivo=[{id:'detalle_tecnico',label:'Detalle técnico del mantenimiento correctivo',tipo:'area'}];
    CAMPOS.instalacion_reefer=[{id:'detalle_tecnico',label:'Detalle técnico de la instalación de reefer',tipo:'area'}];
    CAMPOS.genset_mantenimiento_preventivo=[{id:'detalle_tecnico',label:'Detalle técnico del mantenimiento preventivo',tipo:'area'}];
    CAMPOS.genset_mantenimiento_correctivo=[{id:'detalle_tecnico',label:'Detalle técnico del mantenimiento correctivo',tipo:'area'}];
  }catch(e){}

  // Evita que los trabajos nuevos se llenen con decenas de opciones por defecto.
  window.aplicarPresetSilencioso=aplicarPresetSilencioso=function(s,nombre){
    if(!s.auto||typeof s.auto!=='object')s.auto=emptyAuto();
    ensureAuto(s);s.auto.plantilla=clean(nombre||'');
  };
  window.crearSeleccionTrabajo=crearSeleccionTrabajo=function(w){
    return {id:w.id,nombre:w.nombre,custom:!!w.custom,campos:{},detalle:'',photos:[],auto:emptyAuto(),maquinaAsignada:'',repuestosTrabajo:[],mantenimientoAdicional:{requiere:'',tipo:''}};
  };

  window.buildQuickAssistant=buildQuickAssistant=function(s){
    ensureAuto(s);
    const genset=isGenset();
    const activities=actividadesParaTrabajo(s,genset);
    const findings=unique((genset?GEN_HALLAZGOS:reeferFindings()).concat(opcionesPersonalizadas('hallazgos',s)));
    const box=document.createElement('div');box.className='quick-assistant zg-v35-assistant';
    const head=document.createElement('div');head.className='zg-v35-head';
    const text=document.createElement('div');
    text.innerHTML='<strong>Registro técnico del servicio</strong><small>Registra las acciones ejecutadas y los observaciones. También puedes escribir una opción propia.</small>';
    const clear=document.createElement('button');clear.type='button';clear.className='zg-v35-clear';clear.textContent='Limpiar datos';
    clear.addEventListener('click',function(){
      s.auto.actividades=[];s.auto.hallazgos=[];s.auto.acciones=[];s.auto.limpiado=true;markChanged();renderPanels();
      try{toast('Actividades y hallazgos limpiados.');}catch(e){}
    });
    head.append(text,clear);box.appendChild(head);
    const grid=document.createElement('div');grid.className='zg-v35-grid';
    grid.appendChild(smartEntry(s,'actividades','Actividades realizadas',activities,''));
    grid.appendChild(smartEntry(s,'hallazgos','Observaciones',findings,'warn'));
    box.appendChild(grid);
    if(!genset&&(s.id==='revision_tecnica'||norm(s.nombre).includes('revision')||norm(s.nombre).includes('pti'))){
      try{box.appendChild(buildPtiBox(s));}catch(e){}
    }
    return box;
  };

  function placeholders(){
    document.querySelectorAll('textarea[id*="mantenimiento_productivo_detalle_tecnico"],textarea[id*="genset_mantenimiento_preventivo_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe las acciones ejecutadas, los componentes intervenidos y la condición final del equipo.';
    });
    document.querySelectorAll('textarea[id*="mantenimiento_correctivo_detalle_tecnico"],textarea[id*="genset_mantenimiento_correctivo_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe la falla atendida, la intervención ejecutada y la condición final del equipo.';
    });
    document.querySelectorAll('textarea[id*="instalacion_reefer_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe la instalación ejecutada, las conexiones realizadas, las mediciones registradas y la condición final del reefer.';
    });
  }
  const observer=new MutationObserver(placeholders);
  function init(){
    placeholders();
    try{observer.observe(document.body,{childList:true,subtree:true});}catch(e){}
    try{Object.values(state.selected||{}).forEach(ensureAuto);renderPanels();}catch(e){}
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',function(){setTimeout(init,120);});
})();
</script>


<style id="zg-service-draft-style">
.zg-draft-tools{display:none;align-items:center;gap:8px;min-width:0;flex-wrap:wrap}
.zg-draft-tools.show{display:flex}
.zg-draft-save{border:1px solid #bcd7f2;background:#eef6ff;color:#155a9c;border-radius:999px;padding:8px 12px;font-family:'Archivo',system-ui,sans-serif;font-size:12px;font-weight:900;cursor:pointer;white-space:nowrap}
.zg-draft-save:hover{background:#dfeeff}
.zg-draft-status{font-size:11px;font-weight:800;color:#60758d;white-space:nowrap}
.zg-draft-status.saving{color:#9a6700}.zg-draft-status.saved{color:#16713c}.zg-draft-status.error{color:#b42318}
@media(max-width:700px){.zg-draft-tools{width:100%;justify-content:center}.zg-draft-status{white-space:normal;text-align:center}}
</style>
<script id="zg-service-draft-script">
(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(v){ return String(v == null ? '' : v).trim(); }
  function enabled(){
    try{
      return !!PREINSPECCION && !ZG_PRE_EDIT_MODE && !ZG_EDIT_MODE && clean(PREINSPECCION.estado || 'abierto').toLowerCase() === 'abierto';
    }catch(e){ return false; }
  }
  if(!enabled()) return;

  const preId = String((PREINSPECCION && (PREINSPECCION.id || PREINSPECCION.pre_id)) || byId('preinspeccionId')?.value || '');
  const token = String(TOKEN_CONTINUACION || byId('tokenContinuacion')?.value || '');
  if(!preId) return;

  let dirty = false;
  let saving = false;
  let restoring = false;
  let timer = null;
  let lastSaved = '';

  function fmtDate(raw){
    if(!raw) return '';
    const d = new Date(String(raw).replace(' ','T'));
    if(Number.isNaN(d.getTime())) return String(raw);
    return d.toLocaleString('es-PE',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
  }

  function installUi(){
    const bar = document.querySelector('.actionbar-inner');
    if(!bar || byId('zgDraftTools')) return;
    const wrap = document.createElement('div');
    wrap.id='zgDraftTools';wrap.className='zg-draft-tools show';
    wrap.innerHTML='<button type="button" class="zg-draft-save" id="zgDraftSaveBtn">Guardar avance</button><span class="zg-draft-status" id="zgDraftStatus">El avance se guardará automáticamente</span>';
    const clear = byId('clearBtn');
    bar.insertBefore(wrap, clear || bar.firstChild);
    byId('zgDraftSaveBtn')?.addEventListener('click',function(){ saveDraft(true); });
  }

  function setStatus(text, cls){
    const el=byId('zgDraftStatus'); if(!el) return;
    el.textContent=text; el.className='zg-draft-status'+(cls?' '+cls:'');
  }

  function collectDraft(){
    if(typeof window.zgCollectReportSnapshot !== 'function') return null;
    const snap = window.zgCollectReportSnapshot();
    if(!snap || typeof snap !== 'object') return null;
    snap.version = 41;
    snap.kind = 'service_draft';
    snap.preinspeccionId = preId;
    snap.savedAt = new Date().toISOString();
    snap.preEvidence = [];
    if(snap.fields && typeof snap.fields === 'object'){
      Object.keys(snap.fields).forEach(function(id){
        const el=byId(id);
        if(el && el.closest && el.closest('#datosGeneralesCard')) delete snap.fields[id];
      });
    }
    return snap;
  }

  async function saveDraft(manual){
    if(saving || restoring) return false;
    const snap=collectDraft(); if(!snap) return false;
    saving=true; clearTimeout(timer);
    setStatus(manual?'Guardando avance...':'Guardando automáticamente...','saving');
    const fd=new FormData();
    fd.append('preinspeccion_id',preId);
    fd.append('token_continuacion',token);
    fd.append('datos_json',JSON.stringify(snap));
    try{
      const res=await fetch('guardar_borrador_servicio.php',{method:'POST',body:fd,credentials:'same-origin'});
      const out=await res.json();
      if(!res.ok || !out.ok) throw new Error(out.error || 'No se pudo guardar el avance');
      dirty=false; lastSaved=out.actualizado_en || '';
      setStatus('Avance guardado'+(lastSaved?' · '+fmtDate(lastSaved):''),'saved');
      if(manual && typeof toast==='function') toast('Avance del servicio guardado');
      return true;
    }catch(e){
      setStatus('No se pudo guardar el avance','error');
      if(manual) alert('No se pudo guardar el avance: '+e.message);
      return false;
    }finally{ saving=false; }
  }

  function schedule(){
    if(restoring) return;
    dirty=true; clearTimeout(timer);
    setStatus('Cambios pendientes de guardar','saving');
    timer=setTimeout(function(){ saveDraft(false); },4500);
  }

  function restoreDraft(){
    const pack = (typeof ZG_SERVICE_DRAFT!=='undefined' && ZG_SERVICE_DRAFT) ? ZG_SERVICE_DRAFT : null;
    if(!pack || !pack.snapshot || typeof window.zgRestoreReportSnapshot!=='function') return;
    restoring=true;
    try{
      window.zgRestoreReportSnapshot(pack.snapshot);
      lastSaved=pack.actualizado_en || '';
      setStatus('Avance recuperado'+(lastSaved?' · '+fmtDate(lastSaved):''),'saved');
      if(typeof toast==='function') toast('Se recuperó el avance guardado del servicio');
    }catch(e){ console.warn('No se pudo recuperar el borrador del servicio',e); }
    setTimeout(function(){restoring=false;dirty=false;},700);
  }

  function isSecondStageTarget(target){
    return !!(target && target.closest && target.closest('#trabajosServicioCard, #finalControlCard, #firmasCard'));
  }

  document.addEventListener('input',function(ev){ if(isSecondStageTarget(ev.target)) schedule(); },true);
  document.addEventListener('change',function(ev){ if(isSecondStageTarget(ev.target)) schedule(); },true);
  document.addEventListener('click',function(ev){
    if(!isSecondStageTarget(ev.target)) return;
    if(ev.target.closest('#zgDraftSaveBtn')) return;
    setTimeout(schedule,120);
  },true);

  window.addEventListener('load',function(){
    installUi();
    setTimeout(restoreDraft,1700);
    setTimeout(function(){
      if(new URLSearchParams(location.search).get('desde_edicion')==='1' && typeof toast==='function'){
        toast('Preliminar actualizada. Continúa llenando el servicio.');
      }
    },900);
  });
})();
</script>


<!-- ZGROUP V50: memoria técnica persistente + sugerencias históricas + preventivo reefer completo -->
<!-- ZGROUP V48: checklist reefer compacto, instalación reefer y unidades corregidas -->
<style id="zg-v48-reefer-checklist-style">
.zg-v48-checklist{margin:14px 0 16px;border:1px solid #cfe0f3;border-radius:18px;background:#f8fbff;overflow:hidden}
.zg-v48-checklist summary{cursor:pointer;list-style:none;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;background:linear-gradient(180deg,#fff,#f1f7fe);font-family:Archivo,system-ui,sans-serif;font-weight:900;color:#10213a}
.zg-v48-checklist summary::-webkit-details-marker{display:none}
.zg-v48-checklist summary small{font-family:Manrope,system-ui,sans-serif;color:#65768d;font-weight:700}
.zg-v48-checklist-body{padding:12px}
.zg-v48-simple-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
.zg-v48-simple-card{border:1px solid #dbe6f3;border-radius:14px;background:#fff;overflow:hidden}
.zg-v48-simple-card:nth-child(even){background:#f8fbff}
.zg-v48-simple-head{display:flex;align-items:flex-start;gap:8px;padding:8px 10px 5px;font-size:11px;font-weight:900;color:#213752}
.zg-v48-num{flex:0 0 24px;width:24px;height:24px;display:grid;place-items:center;border-radius:8px;background:#e8f2ff;color:#1f6fc4;font-family:Archivo,system-ui,sans-serif}
.zg-v48-simple-value{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:7px;align-items:center;padding:5px 9px 9px}
.zg-v48-simple-value input,.zg-v48-simple-value select{width:100%;min-width:0;min-height:38px!important;padding:8px 10px!important;font-size:13px!important}
.zg-v48-unit{display:inline-flex;min-width:46px;justify-content:center;border-radius:999px;background:#e8f2ff;color:#155293;padding:7px 8px;font-weight:900;white-space:nowrap;font-size:11px}
.zg-v48-okvolt{display:grid;grid-template-columns:minmax(120px,.7fr) minmax(120px,1fr);gap:7px}
.zg-v48-three-wrap{margin-top:12px;overflow:auto;border:1px solid #dbe6f3;border-radius:14px;background:#fff}
.zg-v48-three-table{width:100%;border-collapse:collapse;min-width:690px;font-size:11.5px}
.zg-v48-three-table th{background:#10213a;color:#fff;padding:8px;border-right:1px solid rgba(255,255,255,.18);text-align:center;font-family:Archivo,system-ui,sans-serif}
.zg-v48-three-table td{padding:7px;border-right:1px solid #dbe6f3;border-bottom:1px solid #dbe6f3;vertical-align:middle;background:#fff}
.zg-v48-three-table tbody tr:nth-child(even) td{background:#f5f9fe}
.zg-v48-three-table .n{width:42px;text-align:center;font-weight:900;color:#1f6fc4}
.zg-v48-three-table .item{min-width:240px;font-weight:800;color:#213752}
.zg-v48-three-table input{width:100%;min-width:80px;min-height:36px!important;padding:7px 8px!important;font-size:12px!important;text-align:center}
.zg-v48-three-table .unit{text-align:center;font-weight:900;color:#155293}
@media(max-width:760px){.zg-v48-checklist summary{align-items:flex-start;flex-direction:column}.zg-v48-simple-grid{grid-template-columns:1fr}.zg-v48-checklist-body{padding:8px}.zg-v48-okvolt{grid-template-columns:1fr}.zg-v48-three-table{font-size:11px}}
</style>
<script id="zg-v48-reefer-checklist-js">
(function(){
  'use strict';
  const COMMON=[
    {key:1,n:1,label:'Cable de alimentación',kind:'text',unit:'m'},
    {key:2,n:2,label:'Enchufe o receptáculo',kind:'text'},
    {key:3,n:3,label:'Interruptor termomagnético',kind:'text',unit:'A'},
    {key:4,n:4,label:'Transformador de control 440/24 V',kind:'okvolt',unit:'V'},
    {key:5,n:5,label:'Transformador de potencia 380/440 V',kind:'okvolt',unit:'V'},
    {key:6,n:6,label:'Transformador de potencia 220/440 V',kind:'okvolt',unit:'V'},
    {key:9,n:7,label:'Contactores principales',kind:'text'},
    {key:19,n:8,label:'Motor ventilador del evaporador 1',kind:'three',unit:'Ω'},
    {key:20,n:9,label:'Motor ventilador del evaporador 2',kind:'three',unit:'Ω'},
    {key:22,n:10,label:'Motor ventilador del condensador',kind:'three',unit:'Ω'},
    {key:23,n:11,label:'Compresor de refrigeración',kind:'three',unit:'Ω'},
    {key:24,n:12,label:'Resistencias de calefacción / heaters',kind:'three',unit:'Ω'},
    {key:26,n:13,label:'Número o versión de software',kind:'text'},
    {key:27,n:14,label:'Registrador de datos (DataCORDER)',kind:'select',options:['Sí','No']},
    {key:35,n:15,label:'Válvula moduladora',kind:'select',options:['25','50','75','100'],unit:'%'},
    {key:36,n:16,label:'Válvula de expansión',kind:'select',options:['Sí','No']},
    {key:37,n:17,label:'Nivel de refrigerante',kind:'select',options:['Lleno','A la mitad','Vacío']}
  ];
  const INSTALL=[
    {key:1,n:1,label:'Cable de alimentación',kind:'text',unit:'m'},
    {key:2,n:2,label:'Enchufe o receptáculo',kind:'text'},
    {key:19,n:3,label:'Motor ventilador del evaporador 1',kind:'three',unit:'Ω'},
    {key:20,n:4,label:'Motor ventilador del evaporador 2',kind:'three',unit:'Ω'},
    {key:21,n:5,label:'Motor ventilador del evaporador 3',kind:'three',unit:'Ω'},
    {key:22,n:6,label:'Motor ventilador del condensador',kind:'three',unit:'Ω'},
    {key:23,n:7,label:'Compresor de refrigeración',kind:'three',unit:'Ω'},
    {key:24,n:8,label:'Resistencias de calefacción / heaters',kind:'three',unit:'Ω'}
  ];
  window.ZG_REEFER_CHECKLIST_ITEMS=COMMON;
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim()}
  function norm(v){return clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')}
  function isGenset(){const v=norm(document.getElementById('zgTipoEquipo')?.value);return v.includes('genset')||v.includes('generador')}
  function modeFor(s){
    if(!s||isGenset())return '';
    const id=norm(s.id),name=norm(s.nombre);
    if(id==='instalacion_reefer'||(name.includes('instalacion')&&name.includes('reefer')))return 'install';
    if(id==='asistencia_tecnica'||id==='mantenimiento_correctivo'||name.includes('asistencia tecnica')||name.includes('mantenimiento correctivo'))return 'common';
    return '';
  }
  function itemsFor(s){return modeFor(s)==='install'?INSTALL:(modeFor(s)==='common'?COMMON:[])}
  window.zgGetReeferChecklistItemsForWork=itemsFor;
  function ensureState(s){if(!s.reeferChecklist||typeof s.reeferChecklist!=='object')s.reeferChecklist={};return s.reeferChecklist}
  function mark(){try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio()}catch(e){}}
  function addOption(select,label,value){const o=document.createElement('option');o.value=value;o.textContent=label;select.appendChild(o)}
  function simpleControl(d,values,key){
    const wrap=document.createElement('div');wrap.className='zg-v48-simple-value';
    const raw=values[key];
    const unit=document.createElement('span');unit.className='zg-v48-unit';
    function syncUnit(mode){
      if(d.kind==='okvolt') unit.textContent=mode==='V'?'V':'—';
      else unit.textContent=d.unit||'—';
    }
    if(d.kind==='select'){
      const sel=document.createElement('select');addOption(sel,'Seleccionar','');(d.options||[]).forEach(v=>addOption(sel,d.unit==='%'?v+' %':v,v));
      sel.value=clean(raw&&typeof raw==='object'?raw.valor:raw);
      sel.addEventListener('change',function(){values[key]=sel.value;mark()});
      wrap.appendChild(sel);syncUnit(sel.value);
    }else if(d.kind==='okvolt'){
      const box=document.createElement('div');box.className='zg-v48-okvolt';
      const sel=document.createElement('select');addOption(sel,'Seleccionar','');addOption(sel,'OK','OK');addOption(sel,'Ingresar voltaje','V');
      const inp=document.createElement('input');inp.type='text';inp.inputMode='decimal';inp.placeholder='Voltios';
      let obj=(raw&&typeof raw==='object')?raw:{};
      if(typeof raw==='string'&&clean(raw)){
        if(/^ok(?:\s*v)?$/i.test(clean(raw)))obj={modo:'OK',valor:''};
        else obj={modo:'V',valor:clean(raw).replace(/\s*v\s*$/i,'')};
      }
      sel.value=clean(obj.modo);inp.value=clean(obj.valor);inp.hidden=sel.value!=='V';inp.disabled=sel.value!=='V';syncUnit(sel.value);
      const save=function(){values[key]={modo:sel.value,valor:inp.value};syncUnit(sel.value);mark()};
      sel.addEventListener('change',function(){inp.hidden=sel.value!=='V';inp.disabled=sel.value!=='V';if(sel.value!=='V')inp.value='';save();if(sel.value==='V')setTimeout(()=>inp.focus(),0)});
      inp.addEventListener('input',save);box.append(sel,inp);wrap.appendChild(box);
    }else{
      const inp=document.createElement('input');inp.type='text';inp.autocomplete='off';inp.placeholder=d.unit?'Ingresa solo el valor':'Escribe el resultado u observación';inp.value=clean(raw&&typeof raw==='object'?raw.valor:raw);
      inp.addEventListener('input',function(){values[key]=inp.value;mark()});wrap.appendChild(inp);syncUnit();
    }
    wrap.appendChild(unit);return wrap;
  }
  function build(s){
    const items=itemsFor(s);if(!items.length)return null;const values=ensureState(s);
    const details=document.createElement('details');details.className='zg-v48-checklist';details.open=true;
    const summary=document.createElement('summary');summary.innerHTML='<span>📋 Lista de inspección técnica reefer</span><small>'+(modeFor(s)==='install'?'Instalación de reefer':'Asistencia técnica / mantenimiento correctivo')+'</small>';
    const body=document.createElement('div');body.className='zg-v48-checklist-body';
    const simple=items.filter(d=>d.kind!=='three'),three=items.filter(d=>d.kind==='three');
    if(simple.length){const grid=document.createElement('div');grid.className='zg-v48-simple-grid';simple.forEach(function(d){const key=String(d.key);const card=document.createElement('div');card.className='zg-v48-simple-card';const head=document.createElement('div');head.className='zg-v48-simple-head';head.innerHTML='<span class="zg-v48-num">'+d.n+'</span><span>'+d.label+'</span>';card.append(head,simpleControl(d,values,key));grid.appendChild(card)});body.appendChild(grid)}
    if(three.length){const wrap=document.createElement('div');wrap.className='zg-v48-three-wrap';const table=document.createElement('table');table.className='zg-v48-three-table';table.innerHTML='<thead><tr><th>N°</th><th>Punto de inspección</th><th>L1</th><th>L2</th><th>L3</th><th>Unidad</th></tr></thead>';const tbody=document.createElement('tbody');three.forEach(function(d){const key=String(d.key);const val=(values[key]&&typeof values[key]==='object')?values[key]:{};const tr=document.createElement('tr');const n=document.createElement('td');n.className='n';n.textContent=d.n;const it=document.createElement('td');it.className='item';it.textContent=d.label;tr.append(n,it);['l1','l2','l3'].forEach(function(k){const td=document.createElement('td');const inp=document.createElement('input');inp.type='text';inp.inputMode='decimal';inp.placeholder=d.unit;inp.value=clean(val[k]);inp.addEventListener('input',function(){if(!values[key]||typeof values[key]!=='object')values[key]={};values[key][k]=inp.value;delete values[key].meg;mark()});td.appendChild(inp);tr.appendChild(td)});const u=document.createElement('td');u.className='unit';u.textContent=d.unit||'—';tr.appendChild(u);tbody.appendChild(tr)});table.appendChild(tbody);wrap.appendChild(table);body.appendChild(wrap)}
    details.append(summary,body);return details;
  }
  const old=window.makePanel||makePanel;
  window.makePanel=makePanel=function(s){const panel=old(s);try{panel.querySelectorAll('.zg-v42-checklist,.zg-v48-checklist').forEach(x=>x.remove());const card=build(s);if(card){const body=panel.querySelector('.panel-body');const assistant=body&&body.querySelector('.quick-assistant,.zg-v35-box,.zg-v38-box');if(assistant&&assistant.parentNode)assistant.insertAdjacentElement('afterend',card);else if(body)body.insertBefore(card,body.firstChild)}}catch(e){console.warn('Checklist reefer V48:',e)}return panel};
  document.addEventListener('change',function(ev){if(ev.target&&ev.target.id==='zgTipoEquipo')setTimeout(function(){try{renderPanels()}catch(e){}},80)},true);
})();
</script>

<!-- ZGROUP V45: checklist reefer correlativo + detalle técnico único + IA con antecedentes -->
<!-- ZGROUP V48: estado inicial de generador sin suministro eléctrico -->
<style id="zg-v48-genset-initial-state-style">
body.zg-mode-genset .zg-energy-state-box{display:none!important}
body.zg-mode-genset .estado-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}
@media(max-width:640px){body.zg-mode-genset .estado-grid{grid-template-columns:1fr!important}}
</style>
<script id="zg-v48-genset-initial-state-js">
(function(){
  'use strict';
  const byId=id=>document.getElementById(id),clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  function isG(){const v=clean(byId('zgTipoEquipo')?.value).toLowerCase();return v.includes('genset')||v.includes('generador')}
  function energyBox(){const e=byId('estadoEnergia');return e&&e.closest('.estado-box')}
  function syncView(){const box=energyBox(),e=byId('estadoEnergia'),hint=document.querySelector('.estado-inicial-pro .field-hint');if(box)box.classList.toggle('zg-energy-state-box',isG());if(e){e.disabled=isG();if(isG())e.value='';}if(hint)hint.textContent=isG()?'Selecciona el funcionamiento y la condición de alarma del generador antes de guardar la inspección preliminar.':'Selecciona las 3 condiciones del equipo antes de guardar la inspección preliminar.';compose()}
  function compose(){const a=clean(byId('estadoEncendido')?.value),b=clean(byId('estadoEnergia')?.value),c=clean(byId('estadoAlarma')?.value),d=clean(byId('alarmaEncontrada')?.value),h=byId('estadoInicial');const alarm=(c==='Con alarma'&&d)?c+': '+d:c;let value='';if(isG()){if(a&&c)value=a+' / '+alarm;}else if(a&&b&&c)value=a+' / '+b+' / '+alarm;if(h&&value)h.value=value;return value||(h?clean(h.value):'')}
  function msg(id,t){try{if(typeof fieldMsg==='function')return fieldMsg(id,t)}catch(e){}const x=byId(id);if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'})}try{toast(t)}catch(e){alert(t)}return false}
  function validate(){const a=clean(byId('estadoEncendido')?.value),b=clean(byId('estadoEnergia')?.value),c=clean(byId('estadoAlarma')?.value),d=clean(byId('alarmaEncontrada')?.value);if(!a)return msg('estadoEncendido','Selecciona si el equipo estaba encendido o apagado.');if(!isG()&&!b)return msg('estadoEnergia','Selecciona si el equipo tenía suministro eléctrico o no.');if(!c)return msg('estadoAlarma','Selecciona si el equipo tenía alarma o no.');if(c==='Con alarma'&&!d)return msg('alarmaEncontrada','Escribe el código o número de alarma encontrado.');compose();return true}
  try{window.zgroupEstadoInicialCompuesto=compose;zgroupEstadoInicialCompuesto=compose}catch(e){}
  try{window.validarEstadoInicialTriple=validate;validarEstadoInicialTriple=validate}catch(e){}
  document.addEventListener('change',function(e){if(e.target&&['zgTipoEquipo','estadoEncendido','estadoEnergia','estadoAlarma'].includes(e.target.id))setTimeout(syncView,0)},true);
  document.addEventListener('input',function(e){if(e.target&&e.target.id==='alarmaEncontrada')compose()},true);
  const init=()=>{syncView();try{window.zgroupEstadoInicialCompuesto=compose;zgroupEstadoInicialCompuesto=compose;window.validarEstadoInicialTriple=validate;validarEstadoInicialTriple=validate}catch(e){}};
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();window.addEventListener('load',()=>setTimeout(init,200));new MutationObserver(()=>setTimeout(syncView,0)).observe(document.documentElement,{childList:true,subtree:true});
})();
</script>


<!-- ZGROUP V51: parámetros eléctricos compactos para instalación y mantenimiento preventivo reefer -->
<style id="zg-v51-parametros-reefer-style">
.zg-v51-parametros{margin:14px 0 16px;border:1px solid #cfe0f3;border-radius:16px;background:#fff;overflow:hidden}
.zg-v51-parametros-head{padding:11px 14px;background:linear-gradient(180deg,#f8fbff,#edf5ff);font-family:Archivo,system-ui,sans-serif;font-weight:900;color:#10213a;border-bottom:1px solid #dbe6f3}
.zg-v51-parametros-sub{display:block;margin-top:3px;font-family:Manrope,system-ui,sans-serif;font-size:11px;font-weight:700;color:#667991}
.zg-v51-parametros-wrap{overflow:auto}
.zg-v51-parametros-table{width:100%;border-collapse:collapse;min-width:610px;font-size:11.5px}
.zg-v51-parametros-table th{background:#10213a;color:#fff;padding:8px;border-right:1px solid rgba(255,255,255,.18);font-family:Archivo,system-ui,sans-serif;text-align:center}
.zg-v51-parametros-table td{padding:7px;border-right:1px solid #dbe6f3;border-bottom:1px solid #dbe6f3;background:#fff}
.zg-v51-parametros-table tbody tr:nth-child(even) td{background:#f6f9fd}
.zg-v51-parametros-table .param{font-weight:900;color:#213752;min-width:180px}
.zg-v51-parametros-table input{width:100%;min-width:80px;min-height:36px!important;padding:7px 8px!important;text-align:center;font-size:12px!important}
.zg-v51-parametros-table .unit{text-align:center;font-weight:900;color:#155293;width:65px}
@media(max-width:760px){.zg-v51-parametros{border-radius:13px}.zg-v51-parametros-table{font-size:11px}}
</style>
<script id="zg-v51-parametros-reefer-js">
(function(){
  'use strict';
  const ROWS=[
    {key:'corriente',label:'Corriente de línea',unit:'A'},
    {key:'motor_evap1',label:'8. Motor ventilador del evaporador 1',unit:'Ω'},
    {key:'motor_evap2',label:'9. Motor ventilador del evaporador 2',unit:'Ω'},
    {key:'motor_cond',label:'10. Motor ventilador del condensador',unit:'Ω'},
    {key:'compresor',label:'11. Compresor de refrigeración',unit:'Ω'},
    {key:'resistencias',label:'12. Resistencias de calefacción / heaters',unit:'Ω'}
  ];
  window.ZG_REEFER_PARAMETROS_ROWS=ROWS;
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim()}
  function norm(v){return clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')}
  function isGenset(){const v=norm(document.getElementById('zgTipoEquipo')?.value);return v.includes('genset')||v.includes('generador')}
  function applies(s){
    if(!s||isGenset())return false;
    const id=norm(s.id),name=norm(s.nombre);
    return id==='instalacion_reefer'||id==='mantenimiento_productivo'
      ||(name.includes('instalacion')&&name.includes('reefer'))
      ||name.includes('mantenimiento preventivo');
  }
  function ensure(s){
    if(!s.parametrosReefer||typeof s.parametrosReefer!=='object')s.parametrosReefer={};
    return s.parametrosReefer;
  }
  function mark(){try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio()}catch(e){}}
  function build(s){
    if(!applies(s))return null;
    const values=ensure(s);
    const box=document.createElement('div');box.className='zg-v51-parametros';
    const head=document.createElement('div');head.className='zg-v51-parametros-head';
    head.innerHTML='⚡ Corriente de línea y resistencias del reefer<span class="zg-v51-parametros-sub">La corriente de línea se registra en A. Los motores, compresor y heaters se registran en Ω por L1, L2 y L3.</span>';
    const wrap=document.createElement('div');wrap.className='zg-v51-parametros-wrap';
    const table=document.createElement('table');table.className='zg-v51-parametros-table';
    table.innerHTML='<thead><tr><th>PARÁMETRO</th><th>L1</th><th>L2</th><th>L3</th><th>UNIDAD</th></tr></thead>';
    const tbody=document.createElement('tbody');
    ROWS.forEach(function(r){
      const row=values[r.key]&&typeof values[r.key]==='object'?values[r.key]:{};
      const tr=document.createElement('tr');
      const tdP=document.createElement('td');tdP.className='param';tdP.textContent=r.label;tr.appendChild(tdP);
      ['l1','l2','l3'].forEach(function(k){
        const td=document.createElement('td');
        const inp=document.createElement('input');inp.type='text';inp.inputMode='decimal';inp.placeholder=r.unit;inp.value=clean(row[k]);
        inp.addEventListener('input',function(){
          if(!values[r.key]||typeof values[r.key]!=='object')values[r.key]={};
          values[r.key][k]=inp.value;mark();
        });
        td.appendChild(inp);tr.appendChild(td);
      });
      const tdU=document.createElement('td');tdU.className='unit';tdU.textContent=r.unit;tr.appendChild(tdU);
      tbody.appendChild(tr);
    });
    table.appendChild(tbody);wrap.appendChild(table);box.append(head,wrap);return box;
  }
  const old=window.makePanel||makePanel;
  window.makePanel=makePanel=function(s){
    const panel=old(s);
    try{
      panel.querySelectorAll('.zg-v51-parametros').forEach(x=>x.remove());
      const card=build(s);
      if(card){
        const body=panel.querySelector('.panel-body');
        const checklist=body&&body.querySelector('.zg-v48-checklist');
        const assistant=body&&body.querySelector('.quick-assistant,.zg-v35-assistant');
        if(checklist)checklist.insertAdjacentElement('afterend',card);
        else if(assistant)assistant.insertAdjacentElement('afterend',card);
        else if(body)body.insertBefore(card,body.firstChild);
      }
    }catch(e){console.warn('Parámetros reefer V51:',e)}
    return panel;
  };
  document.addEventListener('change',function(ev){
    if(ev.target&&ev.target.id==='zgTipoEquipo')setTimeout(function(){try{renderPanels()}catch(e){}},80);
  },true);
})();
</script>

<!-- ZGROUP V48 FINAL -->


<!-- ZGROUP V53: tabla reefer en dos columnas + Ohm PDF + IA en actividades/hallazgos + memoria por trabajo -->

<style id="zg-odoo-final-ui">
.zg-client-odoo-grid{padding:12px;border:1px solid #dce8f4;border-radius:16px;background:#f7fbff}.zg-client-odoo-grid .field{margin:0}.zg-client-odoo-grid input[readonly]{background:#fff;color:#17385d;font-weight:750}#zgServicioImportadoWrap{margin-top:2px}#zgServicioImportadoWrap label{font-size:12px;color:#60738a}#zgServicioImportado{min-height:44px}
</style>


<!-- ZGROUP V54: corriente de línea + resistencias y regla de repuesto por modalidad -->
<style id="zg-v54-modalidad-repuesto-style">
.zg-work-commercial-rule{margin:0 0 11px;padding:10px 12px;border-radius:13px;font-size:12px;font-weight:850;line-height:1.45}
.zg-work-commercial-rule.alquiler{background:#fff7e8;border:1px solid #f0d39f;color:#6b4816}
.zg-work-commercial-rule.venta{background:#eef8ff;border:1px solid #beddf4;color:#174f79}
#repuestoNoBtn.zg-disabled-rule{opacity:.55;cursor:not-allowed!important;filter:grayscale(.15)}
.zg-v51-parametros-table .param{min-width:270px!important}
@media(max-width:760px){.zg-v51-parametros-table .param{min-width:235px!important}}
</style>
<script id="zg-v54-modalidad-repuesto-js">
(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');

  function selectedWorks(){
    try{return Object.values((typeof state!=='undefined'&&state.selected)?state.selected:{})}catch(e){return []}
  }
  function isReefer(){
    const t=norm(byId('zgTipoEquipo')?.value);
    return !(t.includes('genset')||t.includes('generador'));
  }
  function relevantWork(s){
    return !!s;
  }
  function relevantWorks(){return isReefer()?selectedWorks().filter(relevantWork):[]}
  function modality(){return norm(byId('zgModalidadComercial')?.value)}
  function rentalMandatory(){return relevantWorks().length>0&&modality().includes('alquiler')}
  function saleRule(){return relevantWorks().length>0&&modality().includes('venta')}
  function materialsCount(onlyRelevant){
    const list=onlyRelevant?relevantWorks():selectedWorks();
    return list.reduce(function(total,s){return total+(Array.isArray(s&&s.repuestosTrabajo)?s.repuestosTrabajo.filter(function(r){return clean(r&&r.detalle)}).length:0)},0)
  }
  function setDecision(value){
    const h=byId('requiereRepuesto'),yes=byId('repuestoSiBtn'),no=byId('repuestoNoBtn');
    if(h)h.value=value?'si':'no';
    if(yes)yes.classList.toggle('on',!!value);
    if(no)no.classList.toggle('on',!value);
  }
  function ensureHiddenNote(){
    let hidden=byId('zgNotaRepuestoComercial');
    if(!hidden){hidden=document.createElement('input');hidden.type='hidden';hidden.id='zgNotaRepuestoComercial';(byId('trabajosServicioCard')||document.body).appendChild(hidden)}
    return hidden;
  }
  function decoratePanels(){
    const works=selectedWorks(),panels=[...document.querySelectorAll('#panels .panel')];
    panels.forEach(function(panel,idx){
      let note=panel.querySelector('.zg-work-commercial-rule');
      const s=works[idx],applies=relevantWork(s)&&isReefer();
      if(!applies){if(note)note.remove();return}
      if(!note){note=document.createElement('div');note.className='zg-work-commercial-rule';const materials=panel.querySelector('.zg-work-materials');if(materials)materials.insertBefore(note,materials.firstChild);else panel.querySelector('.panel-body')?.prepend(note)}
      if(modality().includes('alquiler')){
        note.className='zg-work-commercial-rule alquiler';
        note.textContent='Alquiler: debe registrarse la pieza que será reemplazada como parte de la atención.';
      }else if(modality().includes('venta')){
        note.className='zg-work-commercial-rule venta';
        note.textContent='Venta: los repuestos seleccionados quedan pendientes de cotización y se atenderán en un trabajo futuro; no se registran como reemplazados en este servicio.';
      }else note.remove();
    });
  }
  function syncRule(){
    const no=byId('repuestoNoBtn'),hiddenNote=ensureHiddenNote();
    const relevantMaterials=materialsCount(true),allMaterials=materialsCount(false);
    if(rentalMandatory()){
      setDecision(true);
      if(no){no.disabled=true;no.classList.add('zg-disabled-rule');no.setAttribute('aria-disabled','true')}
      hiddenNote.value='Por tratarse de un servicio de alquiler, debe registrarse la pieza que será reemplazada.';
    }else{
      if(no){no.disabled=false;no.classList.remove('zg-disabled-rule');no.removeAttribute('aria-disabled')}
      if(saleRule()){
        setDecision(relevantMaterials>0);
        hiddenNote.value=relevantMaterials>0?'Se requerirá de una cotización para el repuesto indicado. El cambio se realizará en un trabajo futuro.':'';
      }else{
        if(allMaterials>0)setDecision(true);
        hiddenNote.value='';
      }
    }
    decoratePanels();
  }
  function syncVisibleMaterials(){
    try{if(typeof window.zgSyncWorkMaterialsForPdf==='function')window.zgSyncWorkMaterialsForPdf()}catch(e){}
  }
  function firstRelevantMaterialsBox(){
    const works=selectedWorks(),panels=[...document.querySelectorAll('#panels .panel')];
    for(let i=0;i<works.length;i++)if(relevantWork(works[i]))return panels[i]?.querySelector('.zg-work-materials')||panels[i];
    return document.querySelector('#panels .zg-work-materials');
  }
  function showError(msg){
    const box=firstRelevantMaterialsBox();
    if(box){box.style.boxShadow='0 0 0 3px rgba(191,70,70,.18)';box.scrollIntoView({behavior:'smooth',block:'center'});setTimeout(function(){box.style.boxShadow=''},1600);setTimeout(function(){try{box.querySelector('.zg-work-material-search input')?.focus()}catch(e){}},220)}
    try{if(typeof toast==='function')toast(msg);else alert(msg)}catch(e){alert(msg)}
    return false;
  }
  function validation(){
    syncVisibleMaterials();syncRule();
    if(rentalMandatory()&&materialsCount(true)<1)return showError('En alquiler debes registrar la pieza que será reemplazada en el servicio reefer.');
    return true;
  }
  try{window.validarRepuestos=validation;validarRepuestos=validation}catch(e){}

  document.addEventListener('click',function(ev){
    const no=ev.target&&ev.target.closest?ev.target.closest('#repuestoNoBtn'):null;
    if(no&&rentalMandatory()){
      ev.preventDefault();ev.stopImmediatePropagation();setDecision(true);syncRule();
      try{if(typeof toast==='function')toast('En alquiler debes registrar la pieza reemplazada.')}catch(e){}
    }
  },true);
  document.addEventListener('change',function(ev){
    if(ev.target&&['zgModalidadComercial','zgTipoEquipo'].includes(ev.target.id))setTimeout(syncRule,20);
  },true);
  document.addEventListener('input',function(ev){
    if(ev.target&&ev.target.closest&&ev.target.closest('.zg-work-material-table'))setTimeout(syncRule,20);
  },true);
  document.addEventListener('click',function(ev){
    if(ev.target&&ev.target.closest&&ev.target.closest('.work-card,.zg-work-material-option,.zg-work-material-table .del'))setTimeout(syncRule,80);
  },true);
  const init=function(){syncRule();[250,700,1400,2600].forEach(function(ms){setTimeout(syncRule,ms)})};
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',init);
})();
</script>


<!-- ZGROUP V55: interfaz limpia, códigos automáticos y seguimiento comercial reefer -->
<style id="zg-v55-final-rules-style">
.zg-v35-tip,.zg-v53-ai-note{display:none!important}
.zg-work-commercial-rule{white-space:normal}
.zg-v55-sale-summary{margin:12px 0;padding:12px 14px;border:1px solid #b9d9f2;border-radius:14px;background:#eef8ff;color:#174f79;font-size:12px;font-weight:850;line-height:1.5}
.zg-v55-rental-summary{margin:12px 0;padding:12px 14px;border:1px solid #efd29d;border-radius:14px;background:#fff7e8;color:#6b4816;font-size:12px;font-weight:850;line-height:1.5}
</style>
<script id="zg-v55-final-rules-js">
(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^A-Z0-9]+/g,' ').trim();
  let busy=false;

  function works(){
    try{return Object.values((typeof state!=='undefined'&&state.selected)?state.selected:{})}catch(e){return []}
  }
  function reefer(){
    const t=norm(byId('zgTipoEquipo')?.value);
    return !(t.includes('GENSET')||t.includes('GENERADOR'));
  }
  function modality(){return norm(byId('zgModalidadComercial')?.value)}
  function isSale(){return modality().includes('VENTA')}
  function isRental(){return modality().includes('ALQUILER')}
  function inferUnit(detail,code){
    try{return clean(window.zgInferUnidadMaterial?window.zgInferUnidadMaterial(detail,code):'und')||'und'}catch(e){return 'und'}
  }
  function controllerKey(work){
    let brand=clean(byId('marcaEquipo')?.value),controller=clean(byId('controladorEquipo')?.value);
    const target=clean(work&&work.maquinaAsignada);
    if(/^M[1-5]$/.test(target)){
      const i=target.slice(1);
      brand=clean(byId('zgMachineBrand'+i)?.value)||brand;
      controller=clean(byId('zgMachineController'+i)?.value)||controller;
    }
    const joined=norm(brand+' '+controller);
    if(joined.includes('STAR COOL')&&joined.includes('CIM 6'))return 'STAR COOL CIM 6';
    if(joined.includes('STAR COOL')&&joined.includes('CIM 5'))return 'STAR COOL CIM 5';
    if(joined.includes('MP5000')||joined.includes('MP 5000'))return 'TK MP5000';
    if(joined.includes('MP4000')||joined.includes('MP 4000'))return 'TK MP4000';
    if(joined.includes('CARRIER'))return 'CARRIER';
    if(joined.includes('DAIKIN'))return 'DAIKIN';
    return '';
  }
  function exactMatch(detail,work){
    const target=norm(detail);if(!target||!window.ZG_CATALOGOS_POR_CONTROLADOR)return null;
    const catalogs=window.ZG_CATALOGOS_POR_CONTROLADOR;
    const key=controllerKey(work);
    const active=key&&Array.isArray(catalogs[key])?catalogs[key]:[];
    const inActive=active.find(r=>norm(r&&r.detalle)===target&&clean(r&&r.codigo));
    if(inActive)return {codigo:clean(inActive.codigo),detalle:clean(inActive.detalle),unidad:clean(inActive.unidad)||inferUnit(inActive.detalle,inActive.codigo)};
    const unique=new Map();
    Object.values(catalogs).forEach(arr=>(Array.isArray(arr)?arr:[]).forEach(r=>{
      if(norm(r&&r.detalle)!==target||!clean(r&&r.codigo))return;
      const code=clean(r.codigo);unique.set(norm(code),{codigo:code,detalle:clean(r.detalle),unidad:clean(r.unidad)||inferUnit(r.detalle,code)});
    }));
    return unique.size===1?[...unique.values()][0]:null;
  }
  function reconcileItem(item,work){
    if(!item||!clean(item.detalle))return false;
    const found=exactMatch(item.detalle,work);if(!found)return false;
    let changed=false;
    if(clean(item.codigo)!==found.codigo){item.codigo=found.codigo;changed=true}
    if(!clean(item.unidad)){item.unidad=found.unidad;changed=true}
    return changed;
  }
  function reconcileWorkTables(){
    const list=works(),panels=[...document.querySelectorAll('#panels .panel')];
    list.forEach((work,idx)=>{
      if(!Array.isArray(work.repuestosTrabajo))work.repuestosTrabajo=[];
      const panel=panels[idx];
      work.repuestosTrabajo.forEach((item,rowIdx)=>{
        const changed=reconcileItem(item,work);
        const row=panel?.querySelectorAll('.zg-work-material-table tbody tr')[rowIdx];
        if(row&&!row.querySelector('.zg-work-material-empty')){
          const codeNode=row.querySelector('td[data-label="Código"] b,td:first-child b');
          if(codeNode&&clean(item.codigo)){codeNode.textContent=item.codigo;codeNode.classList.remove('empty')}
          const unitNode=row.querySelector('.unit');if(unitNode&&clean(item.unidad))unitNode.textContent=item.unidad;
        }
        if(changed){try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio()}catch(e){}}
      });
    });
  }
  function reconcileFinalTable(){
    const tableRows=[...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')];
    let internal=[];
    try{internal=window.zgRepuestosTablaFinal&&typeof window.zgRepuestosTablaFinal.materiales==='function'?window.zgRepuestosTablaFinal.materiales():[]}catch(e){}
    let changed=false;
    tableRows.forEach((row,idx)=>{
      const detail=clean(row.querySelector('.zg-rep-detail2')?.value);
      const found=exactMatch(detail,null);if(!found)return;
      const codeNode=row.querySelector('.zg-rep-code2');
      if(codeNode&&clean(codeNode.textContent).replace(/^Sin código$/i,'')!==found.codigo){codeNode.textContent=found.codigo;codeNode.classList.remove('empty');changed=true}
      const unitNode=row.querySelector('.zg-rep-unit2');if(unitNode&&!clean(unitNode.textContent)){unitNode.textContent=found.unidad;changed=true}
      if(Array.isArray(internal)&&internal[idx]){
        if(clean(internal[idx].codigo)!==found.codigo){internal[idx].codigo=found.codigo;changed=true}
        if(!clean(internal[idx].unidad))internal[idx].unidad=found.unidad;
      }
    });
    if(Array.isArray(window.repuestosSeleccionados))window.repuestosSeleccionados.forEach(x=>{if(reconcileItem(x,null))changed=true});
    if(changed){
      try{if(window.zgRepuestosTablaFinal&&typeof window.zgRepuestosTablaFinal.guardar==='function')window.zgRepuestosTablaFinal.guardar()}catch(e){}
      try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio()}catch(e){}
    }
  }
  function countMaterials(){
    return works().reduce((n,w)=>n+(Array.isArray(w&&w.repuestosTrabajo)?w.repuestosTrabajo.filter(x=>clean(x&&x.detalle)).length:0),0)
      + [...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')].filter(r=>clean(r.querySelector('.zg-rep-detail2')?.value)).length;
  }
  function dispatch(el,type='change'){
    if(!el)return;try{el.dispatchEvent(new Event(type,{bubbles:true}))}catch(e){}
  }
  function setFollowup(){
    if(!reefer()||countMaterials()<1)return;
    const req=byId('zgRequiereOtroMantenimiento'),type=byId('zgTipoOtroMantenimiento'),reason=byId('zgMotivoOtroMantenimiento');
    if(req&&req.value!=='Sí'){req.value='Sí';dispatch(req)}
    if(type&&!clean(type.value)){type.value='Correctivo';dispatch(type)}
    const msg=isSale()
      ?'Se requerirá de una cotización para el repuesto indicado. El cambio se realizará en un trabajo futuro, mediante una nueva asistencia técnica, mantenimiento preventivo, mantenimiento correctivo o instalación, según corresponda.'
      :'Por tratarse de un servicio de alquiler, el repuesto indicado debe ser reemplazado. Se requiere programar o completar la intervención correspondiente.';
    if(reason&&clean(reason.value)!==msg){reason.value=msg;dispatch(reason,'input')}
    const hidden=byId('requiereRepuesto');if(hidden)hidden.value='si';
    byId('repuestoSiBtn')?.classList.add('on');byId('repuestoNoBtn')?.classList.remove('on');
  }
  function updateMaterialHeadings(){
    document.querySelectorAll('#panels .zg-work-materials').forEach(box=>{
      const h=box.querySelector('h4'),p=box.querySelector('.zg-work-materials-head p');
      if(isSale()){
        if(h)h.textContent='🧰 Repuestos requeridos para cotización futura';
        if(p)p.textContent='Los repuestos seleccionados no se registran como reemplazados en este servicio. Se cotizarán y atenderán en un trabajo futuro.';
      }else if(isRental()){
        if(h)h.textContent='🧰 Materiales / repuestos para reemplazo';
        if(p)p.textContent='Registra la pieza que debe ser reemplazada en el servicio de alquiler y ajusta la cantidad en la tabla.';
      }
    });
    document.querySelectorAll('#panels .zg-work-commercial-rule').forEach(note=>{
      if(isSale()){
        note.className='zg-work-commercial-rule venta';
        note.textContent='Venta: se requerirá una cotización para el repuesto indicado y el cambio se realizará en un trabajo futuro.';
      }else if(isRental()){
        note.className='zg-work-commercial-rule alquiler';
        note.textContent='Alquiler: la pieza indicada debe ser reemplazada como parte de la atención.';
      }
    });
  }
  function syncAll(){
    if(busy)return;busy=true;
    try{reconcileWorkTables();reconcileFinalTable();updateMaterialHeadings();setFollowup();try{if(typeof window.zgSyncWorkMaterialsForPdf==='function')window.zgSyncWorkMaterialsForPdf()}catch(e){}}finally{busy=false}
  }
  window.zgActualizarCodigosMateriales=syncAll;
  document.addEventListener('input',ev=>{
    if(ev.target?.matches?.('.zg-rep-detail2,.zg-work-material-table .detail'))setTimeout(syncAll,80);
  },true);
  document.addEventListener('change',ev=>{
    if(ev.target&&['zgModalidadComercial','zgTipoEquipo','marcaEquipo','controladorEquipo'].includes(ev.target.id))setTimeout(syncAll,30);
  },true);
  document.addEventListener('click',ev=>{
    if(ev.target?.closest?.('.zg-work-material-option,.zg-work-material-table .del,.zg-rep-del2,#repuestoSiBtn,#repuestoNoBtn,.work-card'))setTimeout(syncAll,120);
  },true);
  const observer=new MutationObserver(()=>setTimeout(syncAll,80));
  function init(){
    const root=byId('panels')||document.body;observer.observe(root,{childList:true,subtree:true});
    [50,250,700,1400,2600].forEach(ms=>setTimeout(syncAll,ms));
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',()=>setTimeout(syncAll,120));
})();
</script>


<!-- ZGROUP V56: recuperación definitiva de códigos de materiales -->
<style id="zg-v56-codigos-materiales-style">
#repuestosSelectedList .zg-rep-code2.empty,
.zg-work-material-table td[data-label="Código"] b:empty{color:#9a5c00}
.zg-v56-code-pending{color:#9a5c00!important}
</style>
<script id="zg-v56-codigos-materiales-js">
(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^A-Z0-9]+/g,' ').trim();
  const compact=v=>norm(v).replace(/\s+/g,'');
  let running=false, queued=false, observerStarted=false;

  function readConst(name){
    try{
      if(name==='REPUESTOS_CATALOGO' && typeof REPUESTOS_CATALOGO!=='undefined') return REPUESTOS_CATALOGO;
      if(name==='REPUESTOS_REEFER_CATALOGO' && typeof REPUESTOS_REEFER_CATALOGO!=='undefined') return REPUESTOS_REEFER_CATALOGO;
      if(name==='REPUESTOS_GENSET_CATALOGO' && typeof REPUESTOS_GENSET_CATALOGO!=='undefined') return REPUESTOS_GENSET_CATALOGO;
    }catch(e){}
    return [];
  }
  function inferUnit(detail,code){
    try{return clean(window.zgInferUnidadMaterial?window.zgInferUnidadMaterial(detail,code):'und')||'und';}catch(e){return 'und';}
  }
  function controllerKey(work){
    let brand=clean(byId('marcaEquipo')&&byId('marcaEquipo').value);
    let controller=clean(byId('controladorEquipo')&&byId('controladorEquipo').value);
    const target=clean(work&&work.maquinaAsignada);
    if(/^M[1-5]$/i.test(target)){
      const i=target.slice(1);
      brand=clean(byId('zgMachineBrand'+i)&&byId('zgMachineBrand'+i).value)||brand;
      controller=clean(byId('zgMachineController'+i)&&byId('zgMachineController'+i).value)||controller;
    }
    const joined=norm(brand+' '+controller);
    if(joined.includes('STAR COOL')&&joined.includes('CIM 6'))return 'STAR COOL CIM 6';
    if(joined.includes('STAR COOL')&&joined.includes('CIM 5'))return 'STAR COOL CIM 5';
    if(joined.includes('MP5000')||joined.includes('MP 5000'))return 'TK MP5000';
    if(joined.includes('MP4000')||joined.includes('MP 4000'))return 'TK MP4000';
    if(joined.includes('CARRIER'))return 'CARRIER';
    if(joined.includes('DAIKIN'))return 'DAIKIN';
    if(joined.includes('SG 3000'))return 'GENSET SG-3000';
    if(joined.includes('SG 5000'))return 'GENSET SG-5000';
    return '';
  }
  function keyFromDetail(detail){
    const d=norm(detail);
    if(d.includes('CIM 6'))return 'STAR COOL CIM 6';
    if(d.includes('CIM 5'))return 'STAR COOL CIM 5';
    if(d.includes('MP5000')||d.includes('MP 5000'))return 'TK MP5000';
    if(d.includes('MP4000')||d.includes('MP 4000'))return 'TK MP4000';
    if(d.includes('SG 3000'))return 'GENSET SG-3000';
    if(d.includes('SG 5000'))return 'GENSET SG-5000';
    return '';
  }
  function maps(){
    try{return window.ZG_CATALOGOS_POR_CONTROLADOR&&typeof window.ZG_CATALOGOS_POR_CONTROLADOR==='object'?window.ZG_CATALOGOS_POR_CONTROLADOR:{};}catch(e){return {};}
  }
  function entry(raw,source,priority){
    if(!raw)return null;
    const codigo=clean(raw.codigo||raw.code||'');
    const detalle=clean(raw.detalle||raw.nombre||raw.material||raw.descripcion||'');
    if(!codigo||!detalle)return null;
    return {codigo,detalle,unidad:clean(raw.unidad||'')||inferUnit(detalle,codigo),source:source||'',priority:priority||9};
  }
  function addEntries(dst,arr,source,priority){
    (Array.isArray(arr)?arr:[]).forEach(r=>{const e=entry(r,source,priority);if(e)dst.push(e);});
  }
  function allEntries(work,detail){
    const out=[];
    const catalogs=maps();
    const active=controllerKey(work)||keyFromDetail(detail);
    if(active&&Array.isArray(catalogs[active]))addEntries(out,catalogs[active],active,0);
    Object.keys(catalogs).forEach(k=>{if(k!==active)addEntries(out,catalogs[k],k,3);});
    addEntries(out,readConst('REPUESTOS_REEFER_CATALOGO'),'BD REEFER',4);
    addEntries(out,readConst('REPUESTOS_CATALOGO'),'CATÁLOGO GENERAL',5);
    addEntries(out,readConst('REPUESTOS_GENSET_CATALOGO'),'BD GENSET',5);
    const seen=new Set(),dedup=[];
    out.sort((a,b)=>a.priority-b.priority).forEach(e=>{
      const k=norm(e.codigo)+'|'+norm(e.detalle);
      if(seen.has(k))return;
      seen.add(k);dedup.push(e);
    });
    return {active,items:dedup};
  }
  function uniqueByCode(items){
    const map=new Map();
    items.forEach(x=>map.set(norm(x.codigo),x));
    return map.size===1?[...map.values()][0]:null;
  }
  function tokenScore(a,b){
    const A=new Set(norm(a).split(' ').filter(x=>x.length>1));
    const B=new Set(norm(b).split(' ').filter(x=>x.length>1));
    if(!A.size||!B.size)return 0;
    let common=0;A.forEach(x=>{if(B.has(x))common++;});
    return (2*common)/(A.size+B.size);
  }
  function resolve(detail,work,currentCode){
    detail=clean(detail);if(!detail)return null;
    const data=allEntries(work,detail),items=data.items;
    const target=norm(detail),targetCompact=compact(detail);
    if(clean(currentCode)){
      const byCode=items.find(x=>norm(x.codigo)===norm(currentCode));
      if(byCode)return byCode;
    }
    let found=items.filter(x=>norm(x.detalle)===target);
    if(found.length){
      const active=found.filter(x=>x.priority===0);
      return active[0]||uniqueByCode(found);
    }
    found=items.filter(x=>compact(x.detalle)===targetCompact);
    if(found.length){
      const active=found.filter(x=>x.priority===0);
      return active[0]||uniqueByCode(found);
    }
    // Solo se acepta coincidencia aproximada cuando es clara y no puede asignar un código equivocado.
    const scored=items.map(x=>({x,score:tokenScore(detail,x.detalle)})).filter(z=>z.score>=0.92).sort((a,b)=>b.score-a.score||a.x.priority-b.x.priority);
    if(scored.length){
      const best=scored[0].score;
      const bestItems=scored.filter(z=>Math.abs(z.score-best)<0.0001).map(z=>z.x);
      const active=bestItems.filter(x=>x.priority===0);
      return active[0]||uniqueByCode(bestItems);
    }
    return null;
  }
  function repairItem(item,work){
    if(!item||!clean(item.detalle))return false;
    const r=resolve(item.detalle,work,item.codigo);
    if(!r)return false;
    let changed=false;
    if(clean(item.codigo)!==r.codigo){item.codigo=r.codigo;changed=true;}
    if(!clean(item.unidad)){item.unidad=r.unidad;changed=true;}
    return changed;
  }
  function parseLine(line){
    const p=String(line||'').split('|').map(clean);
    if(!p.join(''))return null;
    let codigo='',detalle='',cantidad='1',unidad='';
    if(p.length>=4){codigo=p[0]==='-'?'':p[0];cantidad=p[p.length-2]||'1';unidad=p[p.length-1]||'';detalle=p.slice(1,-2).join(' | ');}
    else if(p.length===3){codigo=p[0]==='-'?'':p[0];detalle=p[1]||'';cantidad=p[2]||'1';}
    else if(p.length===2){codigo=p[0]==='-'?'':p[0];detalle=p[1]||'';}
    else detalle=p[0]||'';
    if(!detalle)return null;
    return {codigo,detalle,cantidad:String(cantidad||'1').replace(/[^0-9]/g,'')||'1',unidad:unidad||inferUnit(detalle,codigo)};
  }
  function serialize(items){
    return items.filter(x=>clean(x&&x.detalle)).map(x=>(clean(x.codigo)||'-')+' | '+clean(x.detalle)+' | '+(String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1')+' | '+clean(x.unidad||inferUnit(x.detalle,x.codigo)||'und')).join('\n');
  }
  function repairTextarea(){
    const ta=byId('repuestosManual');if(!ta||!clean(ta.value))return false;
    const items=String(ta.value).split(/\r?\n/).map(parseLine).filter(Boolean);
    let changed=false;items.forEach(x=>{if(repairItem(x,null))changed=true;});
    const next=serialize(items);
    if(next!==ta.value){ta.value=next;changed=true;}
    return changed;
  }
  function repairFinalTable(){
    let changed=false,internal=[];
    try{internal=window.zgRepuestosTablaFinal&&typeof window.zgRepuestosTablaFinal.materiales==='function'?window.zgRepuestosTablaFinal.materiales():[];}catch(e){internal=[];}
    if(Array.isArray(internal))internal.forEach(x=>{if(repairItem(x,null))changed=true;});
    const rows=[...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')];
    rows.forEach((row,idx)=>{
      const detail=clean(row.querySelector('.zg-rep-detail2')&&row.querySelector('.zg-rep-detail2').value);
      const current=clean(row.querySelector('.zg-rep-code2')&&row.querySelector('.zg-rep-code2').textContent).replace(/^Sin código$/i,'');
      const r=resolve(detail,null,(internal[idx]&&internal[idx].codigo)||current);if(!r)return;
      const codeNode=row.querySelector('.zg-rep-code2');
      if(codeNode&&clean(codeNode.textContent).replace(/^Sin código$/i,'')!==r.codigo){codeNode.textContent=r.codigo;codeNode.classList.remove('empty','zg-v56-code-pending');changed=true;}
      const unitNode=row.querySelector('.zg-rep-unit2');if(unitNode&&!clean(unitNode.textContent)){unitNode.textContent=r.unidad;changed=true;}
      if(internal[idx]){if(clean(internal[idx].codigo)!==r.codigo){internal[idx].codigo=r.codigo;changed=true;}if(!clean(internal[idx].unidad))internal[idx].unidad=r.unidad;}
    });
    if(Array.isArray(window.repuestosSeleccionados))window.repuestosSeleccionados.forEach(x=>{if(repairItem(x,null))changed=true;});
    return changed;
  }
  function workList(){
    try{return Object.values((typeof state!=='undefined'&&state.selected)?state.selected:{});}catch(e){return [];}
  }
  function repairWorkMaterials(){
    let changed=false;
    const works=workList();
    works.forEach((work,widx)=>{
      ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(k=>{
        if(Array.isArray(work&&work[k]))work[k].forEach(x=>{if(repairItem(x,work))changed=true;});
      });
      if(work&&work.campos&&typeof work.campos==='object'){
        ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(k=>{if(Array.isArray(work.campos[k]))work.campos[k].forEach(x=>{if(repairItem(x,work))changed=true;});});
      }
    });
    const panels=[...document.querySelectorAll('#panels .panel')];
    works.forEach((work,widx)=>{
      const arr=Array.isArray(work&&work.repuestosTrabajo)?work.repuestosTrabajo:[];
      const rows=[...(panels[widx]?panels[widx].querySelectorAll('.zg-work-material-table tbody tr'):[])];
      rows.forEach((row,idx)=>{
        if(row.querySelector('.zg-work-material-empty'))return;
        const item=arr[idx];
        const detail=clean(row.querySelector('.detail')&&row.querySelector('.detail').value)||(item&&item.detalle)||'';
        const r=resolve(detail,work,item&&item.codigo);if(!r)return;
        if(item&&clean(item.codigo)!==r.codigo){item.codigo=r.codigo;changed=true;}
        const codeNode=row.querySelector('td[data-label="Código"] b,td:first-child b');if(codeNode&&clean(codeNode.textContent).replace(/^Sin código$/i,'')!==r.codigo){codeNode.textContent=r.codigo;changed=true;}
        const unitNode=row.querySelector('.unit');if(unitNode&&!clean(unitNode.textContent)){unitNode.textContent=r.unidad;changed=true;}
      });
    });
    return changed;
  }
  function persist(){
    try{if(window.zgRepuestosTablaFinal&&typeof window.zgRepuestosTablaFinal.guardar==='function')window.zgRepuestosTablaFinal.guardar();}catch(e){}
    try{if(typeof window.zgSyncWorkMaterialsForPdf==='function')window.zgSyncWorkMaterialsForPdf();}catch(e){}
    try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio();}catch(e){}
  }
  function sync(){
    if(running){queued=true;return false;}
    running=true;let changed=false;
    try{
      changed=repairTextarea()||changed;
      changed=repairFinalTable()||changed;
      changed=repairWorkMaterials()||changed;
      if(changed){persist();repairTextarea();}
    }finally{
      running=false;
      if(queued){queued=false;setTimeout(sync,30);}
    }
    return changed;
  }
  function schedule(ms){setTimeout(sync,ms||0);}
  function wrapApis(){
    const api=window.zgRepuestosTablaFinal;
    if(api&&typeof api.pintar==='function'&&!api.pintar.__zgV56){
      const old=api.pintar;const next=function(){const r=old.apply(this,arguments);schedule(20);return r;};next.__zgV56=true;api.pintar=next;
    }
    if(typeof window.zgLoadEditMaterials==='function'&&!window.zgLoadEditMaterials.__zgV56){
      const old=window.zgLoadEditMaterials;const next=function(){const r=old.apply(this,arguments);[20,120,350,900].forEach(schedule);return r;};next.__zgV56=true;window.zgLoadEditMaterials=next;
    }
    if(typeof window.renderPanels==='function'&&!window.renderPanels.__zgV56){
      const old=window.renderPanels;const next=function(){const r=old.apply(this,arguments);schedule(30);return r;};next.__zgV56=true;window.renderPanels=next;
    }
  }
  function startObserver(){
    if(observerStarted||!document.body)return;observerStarted=true;
    const obs=new MutationObserver(muts=>{
      const relevant=muts.some(m=>{
        const t=m.target&&m.target.nodeType===1?m.target:m.target&&m.target.parentElement;
        return t&&t.closest&&t.closest('#repuestosSelectedList,#panels,#repuestosCard');
      });
      if(relevant)schedule(40);
    });
    obs.observe(document.body,{childList:true,subtree:true});
  }
  function init(){
    wrapApis();startObserver();
    [20,100,300,700,1300,2200,3500,5200,7500,10000].forEach(schedule);
    [1200,3000,6000].forEach(ms=>setTimeout(wrapApis,ms));
  }
  ['pointerdown','mousedown','touchstart'].forEach(type=>document.addEventListener(type,ev=>{if(ev.target&&ev.target.closest&&ev.target.closest('#pdfBtn,#preBtn'))sync();},true));
  document.addEventListener('submit',sync,true);
  document.addEventListener('input',ev=>{if(ev.target&&ev.target.matches&&ev.target.matches('.zg-rep-detail2,.zg-work-material-table .detail,#repuestosManual'))schedule(100);},true);
  document.addEventListener('change',ev=>{if(ev.target&&['marcaEquipo','controladorEquipo','zgTipoEquipo'].includes(ev.target.id))schedule(20);},true);
  window.zgCorregirTodosLosCodigosMateriales=sync;
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',()=>{wrapApis();[50,250,800,1800,4000,8000].forEach(schedule);});
})();
</script>
