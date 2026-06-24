(function(){
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
