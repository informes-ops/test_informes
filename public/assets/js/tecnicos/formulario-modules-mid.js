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

