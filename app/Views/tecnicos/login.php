<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acceso técnicos — ZGROUP</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Manrope:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<?php require APP_ROOT . '/app/Views/tecnicos/partials/base_css.php'; ?>
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
      <?php
      $loginAction = 'index.php';
      $redirectToken = trim((string)($redirect_token ?? ''));
      $redirectModo = trim((string)($redirect_modo ?? ''));
      if ($redirectToken !== '') {
          $loginAction .= '?token=' . rawurlencode($redirectToken);
      } elseif ($redirectModo !== '') {
          $loginAction .= '?modo=' . rawurlencode($redirectModo);
      }
      ?>
      <form method="post" action="<?= htmlspecialchars($loginAction, ENT_QUOTES, 'UTF-8') ?>" class="zg-form" autocomplete="off">
        <?php if ($redirectToken !== ''): ?>
        <input type="hidden" name="redirect_token" value="<?= htmlspecialchars($redirectToken, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <?php if ($redirectModo !== ''): ?>
        <input type="hidden" name="redirect_modo" value="<?= htmlspecialchars($redirectModo, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
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
