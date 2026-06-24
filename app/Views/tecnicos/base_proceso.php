<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trabajo en base — En proceso</title>
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
