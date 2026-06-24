<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\BorradorServicioModel;
use App\Models\InformeModel;
use App\Models\PreinspeccionModel;
use App\Models\SalidaTecnicaModel;
use App\Models\TecnicoModel;
use App\Models\TrabajoModel;
use PDO;

/**
 * Orquesta la carga de todos los datos necesarios para el formulario técnico.
 */
class IndexFormDataService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    /**
     * @return array Variables listas para extract() en la vista formulario.php
     */
    public function preparar(array $contexto): array
    {
        $preModel = new PreinspeccionModel($this->pdo);
        $infModel = new InformeModel($this->pdo);
        $borradorModel = new BorradorServicioModel($this->pdo);
        $borradorModel->asegurarTabla();

        $token = trim((string)($contexto['token'] ?? ''));
        $modoEditarInforme = !empty($contexto['modo_editar_informe']);
        $modoEditarPreliminar = !empty($contexto['modo_editar_preliminar']);

        $preinspeccion = null;
        $preinspeccionError = '';

        if ($token !== '') {
            $preinspeccion = $preModel->findByToken($token);
            if (!$preinspeccion) {
                $preinspeccionError = 'No se encontró una inspección preliminar con este enlace.';
            }
        }

        if ($modoEditarPreliminar) {
            $preId = (int)($contexto['preliminar_id'] ?? 0);
            if ($preId <= 0) {
                $preinspeccionError = 'No se recibió una inspección preliminar válida para editar.';
            } else {
                $preinspeccion = $preModel->findWithTecnicoById($preId);
                if (!$preinspeccion) {
                    $preinspeccionError = 'La inspección preliminar solicitada no existe o fue eliminada.';
                } else {
                    $token = trim((string)($preinspeccion['token_continuacion'] ?? ''));
                }
            }
        }

        $borradorServicio = null;
        $borradorServicioError = '';
        if (is_array($preinspeccion) && (int)($preinspeccion['id'] ?? 0) > 0) {
            try {
                $borradorServicio = $borradorModel->findByPreinspeccion((int)$preinspeccion['id']);
            } catch (\Throwable $e) {
                $borradorServicioError = $e->getMessage();
            }
        }

        $preTipoEquipo = PreinspeccionModel::resolverTipoEquipo($preinspeccion);
        $preEsGenset = PreinspeccionModel::esGenset($preinspeccion);

        $informeEdicion = null;
        $informeEdicionSnapshot = null;
        $informeEdicionError = '';

        if ($modoEditarInforme) {
            $edicion = $infModel->cargarParaEdicion((int)($contexto['informe_id'] ?? 0));
            $informeEdicion = $edicion['informe'];
            $informeEdicionSnapshot = $edicion['snapshot'];
            $informeEdicionError = $edicion['error'];

            if ($informeEdicion) {
                $preId = (int)($informeEdicion['preinspeccion_id'] ?? 0);
                if ($preId > 0) {
                    $preEdit = $preModel->findWithTecnicoById($preId);
                    if ($preEdit) {
                        $preinspeccion = $preEdit;
                        $preTipoEquipo = PreinspeccionModel::resolverTipoEquipo($preinspeccion);
                        $preEsGenset = PreinspeccionModel::esGenset($preinspeccion);
                    }
                }
            }
        }

        $trabajoModel = new TrabajoModel($this->pdo);
        $trabajoModel->asegurarTabla();
        $trabajoModel->sembrarSiVacio();
        $trabajoModel->sincronizarV9();
        $workTypes = $trabajoModel->listarActivos();

        $catalogos = (new CatalogoIndexService($this->pdo))->cargarTodos();
        $opcionesTecnicasPersonalizadas = (new OpcionTecnicaService($this->pdo))->cargarOpcionesPersonalizadas();
        $opcionesTecnicasPorTrabajo = (new OpcionTecnicaService($this->pdo))->cargarOpcionesPorTrabajo();

        $salidaModel = new SalidaTecnicaModel($this->pdo);
        try {
            $salidaModel->asegurarTablas();
            $salidasSupervision = $salidaModel->listarActivasConMateriales();
        } catch (\Throwable $e) {
            $salidasSupervision = [];
        }

        $tecnicos = (new TecnicoModel($this->pdo))->findActivos();

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
                'error' => $informeEdicionError,
            ];
        } elseif ($modoEditarInforme) {
            $informeEdicionPayload = ['id' => 0, 'error' => $informeEdicionError ?: 'No se pudo cargar el informe.'];
        }

        $resolver = new DatosGeneralesResolver();
        try {
            $datosGenerales = $resolver->resolver(
                is_array($preinspeccion) ? $preinspeccion : null,
                is_array($informeEdicion) ? $informeEdicion : null,
                $catalogos['serviciosOdoo']
            );
        } catch (\Throwable $e) {
            $datosGenerales = (new DatosGeneralesResolver())->valoresVaciosPublicos();
        }

        $preinspeccionJs = $resolver->preinspeccionParaJs(
            is_array($preinspeccion) ? $preinspeccion : null
        );
        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

        return array_merge($catalogos, [
            'datosGenerales' => $datosGenerales,
            'preinspeccion' => $preinspeccion,
            'preinspeccionError' => $preinspeccionError,
            'token_continuacion' => $token,
            'borradorServicio' => $borradorServicio,
            'borradorServicioError' => $borradorServicioError,
            'preTipoEquipo' => $preTipoEquipo,
            'preEsGenset' => $preEsGenset,
            'informeEdicion' => $informeEdicion,
            'informeEdicionSnapshot' => $informeEdicionSnapshot,
            'informeEdicionError' => $informeEdicionError,
            'informeEdicionPayload' => $informeEdicionPayload,
            'workTypes' => $workTypes,
            'tecnicos' => $tecnicos,
            'salidasSupervision' => $salidasSupervision,
            'opcionesTecnicasPersonalizadas' => $opcionesTecnicasPersonalizadas,
            'opcionesTecnicasPorTrabajo' => $opcionesTecnicasPorTrabajo,
            'clientesCatalogo' => $catalogos['clientes'],
            'cotizacionesCatalogo' => $catalogos['cotizaciones'],
            'contenedoresCatalogo' => $catalogos['contenedores'],
            'maquinasCatalogo' => $catalogos['maquinas'],
            'generadoresCatalogo' => $catalogos['generadores'],
            'repuestosCatalogo' => $catalogos['repuestos'],
            'repuestosGensetCatalogo' => $catalogos['repuestosGenset'],
            'repuestosReeferCatalogo' => $catalogos['repuestosReefer'],
            'modelosReeferCatalogo' => $catalogos['modelosReefer'],
            'modelosGensetCatalogo' => $catalogos['modelosGenset'],
            'serviciosOdooCatalogo' => $catalogos['serviciosOdoo'],
            'preinspeccionJson' => json_encode($preinspeccionJs, $jsonFlags),
            'preinspeccionErrorJson' => json_encode($preinspeccionError, JSON_UNESCAPED_UNICODE),
            'tokenContinuacionJson' => json_encode($token, JSON_UNESCAPED_UNICODE),
            'borradorServicioJson' => json_encode($borradorServicio, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
