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
