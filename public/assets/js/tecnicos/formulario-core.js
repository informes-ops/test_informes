/* =========================================================================
   TRABAJOS DE ZGROUP  ←  edita/agrega aquí cuando necesites.
   Cada item: { id (único, sin espacios), nombre }
   ========================================================================= */
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
let repuestosSeleccionados = [];


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
function reporteDigits(s){
  const nro = soloDigitos(s?.numero_reporte || '');
  if (nro !== '') return nro;
  return soloDigitos(s?.cotizacion || '');
}
function cotizacionExacta(valor){
  const v=soloDigitos(valor||'');
  const svc=(SERVICIOS_ODOO_CATALOGO||[]).find(s=>reporteDigits(s)===v && v!=='');
  if(!svc)return null;
  return {cotizacion:svc.numero_reporte||svc.cotizacion,cliente_nombre:svc.cliente_nombre,ticket_ref:svc.ticket_ref,cotizacion_odoo:svc.cotizacion,servicio:svc};
}
function clienteExacto(valor){
  const v = normBusca(valor);
  return (CLIENTES_CATALOGO || []).find(c => normBusca(c.nombre) === v) || null;
}

function servicioPorReporte(numero){
  const n=soloDigitos(numero||'');
  if(!n)return null;
  return (SERVICIOS_ODOO_CATALOGO||[]).find(s=>reporteDigits(s)===n) || null;
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

  setVal('orden',s.numero_reporte||s.cotizacion||'');
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
      const nro=reporteDigits(s);
      return nro!=='' && (query==='' || nro.startsWith(query));
    })
    .sort((a,b)=>String(reporteDigits(b)).localeCompare(String(reporteDigits(a)),undefined,{numeric:true}));
}

function mostrarCotizaciones(){
  const orden=$('orden');
  if(!orden)return;

  const items=cotizacionesFiltradas(orden.value)
    .slice(0,50)
    .map(s=>({
      raw:s,
      main:s.numero_reporte||s.cotizacion||'',
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
      if(typeof PREINSPECCION!=='undefined' && PREINSPECCION) return;
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
  // Enlazar cotización Odoo desde catálogo SQL (odoo_servicios_catalogo).
  const svcPre = servicioPorReporte(p.cotizacion || '')
    || (p.odoo_ticket_ref ? (SERVICIOS_ODOO_CATALOGO||[]).find(s => String(s.ticket_ref||'') === String(p.odoo_ticket_ref||'')) : null)
    || null;
  if(svcPre){
    if(svcPre.cotizacion){
      setVal('odooCotizacion', svcPre.cotizacion);
      setVal('odooCotizacionDisplay', svcPre.cotizacion);
    }
    if(!getVal('orden') && svcPre.numero_reporte) setVal('orden', svcPre.numero_reporte);
  }
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
  const dirPick=$('dirPick');
  if(!modal || !inp || !sug || !dirPick) return;
  dirPick.addEventListener('click', openMap);
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
    if(!blob || !blob.size) throw new Error('No se pudo generar el archivo PDF.');
    const pdfMb = blob.size / (1024 * 1024);
    if(pdfMb > 30) throw new Error('El PDF pesa ' + pdfMb.toFixed(1) + ' MB (máx. ~30 MB). Reduce fotos o resolución e inténtalo de nuevo.');
    const fname = `Informe_${(orden || 'sin-reporte').replace(/[^\w-]/g,'_')}_SERVICIO_TECNICO_${fecha || 'fecha'}.pdf`;

    const fd = new FormData();
    const pdfFile = (typeof File !== 'undefined')
      ? new File([blob], fname, { type: 'application/pdf', lastModified: Date.now() })
      : blob;
    fd.append('pdf', pdfFile, fname);
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

    const res = await fetch(ZG_EDIT_MODE ? 'actualizar_informe.php' : 'guardar.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    let out = null;
    try { out = await res.json(); } catch(e) { out = null; }
    if(!out || typeof out !== 'object'){
      throw new Error('Respuesta inválida del servidor (HTTP ' + res.status + '). Si el informe tiene muchas fotos, puede superar el límite de subida PHP.');
    }
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
      if(out.pdf_url){
        try{
          if(out.archivo && window.ZG_EDIT_REPORT) window.ZG_EDIT_REPORT.archivo = out.archivo;
          showSaved([{
            nombre: trabajosResumen || 'Informe actualizado',
            fname: out.archivo || fname,
            url: out.pdf_url,
            odoo: odooEdit
          }], sections.length);
        }catch(e){
          alert('Informe actualizado correctamente.' + odooEditMsg);
        }
      }else{
        alert('Informe actualizado correctamente.' + odooEditMsg);
      }
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

// ---- Init (al final del archivo: los scripts usan defer y las variables let deben existir antes) ----
(function init(){
  const fechaEl = $('fecha');
  if (fechaEl && !fechaEl.value.trim()) {
    const t = new Date();
    fechaEl.value = `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}`;
  }
  renderWorkCards();
  if($('pdfBtn')) $('pdfBtn').addEventListener('click', generatePDF);
  if($('clearBtn')) $('clearBtn').addEventListener('click', clearAll);
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
  if($('workSearch')) $('workSearch').addEventListener('input', e => { workQuery = e.target.value; renderWorkCards(); });
  window.repuestosSeleccionados = repuestosSeleccionados;
})();
