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

