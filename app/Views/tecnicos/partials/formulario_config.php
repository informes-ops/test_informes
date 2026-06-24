<?php
/**
 * Configuración dinámica PHP → JavaScript para el formulario técnico.
 * Debe cargarse ANTES de los scripts en public/assets/js/tecnicos/
 */
declare(strict_types=1);

if (!defined('ZGROUP_PUSH_TRIGGER_TOKEN')) {
    @include_once APP_ROOT . '/push_config.php';
}
?>
<script>
window.ZGROUP = window.ZGROUP || {};
Object.assign(window.ZGROUP, {
  version: 'V56',
  preEsGenset: <?= json_encode(!empty($preEsGenset)) ?>,
});

const WORK_TYPES = <?= json_encode($workTypes, JSON_UNESCAPED_UNICODE) ?>;
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
const ZG_EDIT_MODE = <?= !empty($modo_editar_informe) ? 'true' : 'false' ?>;
const ZG_PRE_EDIT_MODE = <?= !empty($modo_editar_preliminar) ? 'true' : 'false' ?>;
const ZG_PRE_EDIT_ID = <?= (int)($preliminarEdicionId ?? 0) ?>;
const ZG_EDIT_REPORT = <?= json_encode($informeEdicionPayload ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null' ?>;
const ZFIX_PRE = PREINSPECCION;
const SALIDAS_TECNICAS_SUPERVISION = <?= json_encode($salidasSupervision ?? [], JSON_UNESCAPED_UNICODE) ?>;
</script>
