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
