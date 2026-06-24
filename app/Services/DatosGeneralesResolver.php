<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\PreinspeccionModel;

/**
 * Puente PHP del formulario técnico existente (formulario.php + applyPreinspeccionContinuacion).
 * Lee las mismas columnas que zgroupin_zgroupinformes.sql → inspecciones_preliminares
 * y enriquece con odoo_servicios_catalogo cuando hay ticket/cotización.
 */
class DatosGeneralesResolver
{
    /** @var array<string, string> Columna BD → id del input/select en formulario.php */
    private const MAPA_PREINSPECCION = [
        'cotizacion' => 'orden',
        'cliente' => 'cliente',
        'modalidad_comercial' => 'zgModalidadComercial',
        'tipo_instalacion' => 'zgTipoInstalacion',
        'tipo_equipo' => 'zgTipoEquipo',
        'tamano_contenedor' => 'zgTamanoContenedor',
        'numero_equipo' => 'equipoNo',
        'serie_unidad' => 'serialUnidad',
        'marca_equipo' => 'marcaEquipo',
        'modelo_equipo' => 'modeloEquipo',
        'controlador' => 'controladorEquipo',
        'anio_fabricacion' => 'anioFabricacion',
        'refrigerante' => 'refrigerante',
        'set_point' => 'setPoint',
        'temperatura_ambiente' => 'temperaturaAmbiente',
        'retorno_aire' => 'retornoAire',
        'suministro_aire' => 'suministroAire',
        'presion_alta' => 'presionAlta',
        'presion_baja' => 'presionBaja',
        'voltaje_l1_l2' => 'voltajeL1L2',
        'voltaje_l2_l3' => 'voltajeL2L3',
        'voltaje_l1_l3' => 'voltajeL1L3',
        'estado_inicial' => 'estadoInicial',
        'alarma_encontrada' => 'alarmaEncontrada',
        'genset_horometro_inicial' => 'gensetHorometroInicial',
        'genset_voltaje_bateria_inicial' => 'gensetVoltajeBateriaInicial',
        'genset_nivel_combustible_inicial' => 'gensetNivelCombustibleInicial',
        'genset_nivel_aceite_inicial' => 'gensetNivelAceiteInicial',
        'genset_refrigerante_motor_inicial' => 'gensetRefrigeranteMotorInicial',
        'genset_arranque_inicial' => 'gensetArranqueInicial',
        'genset_frecuencia_inicial' => 'gensetFrecuenciaInicial',
        'genset_presion_aceite_inicial' => 'gensetPresionAceiteInicial',
        'odoo_ticket_ref' => 'odooTicketRef',
    ];

    /** @var array<string, string> Columna odoo_servicios_catalogo → id del formulario */
    private const MAPA_ODOO = [
        'numero_reporte' => 'orden',
        'cotizacion' => 'odooCotizacion',
        'cliente_nombre' => 'cliente',
        'ticket_ref' => 'odooTicketRef',
        'direccion' => 'direccion',
        'fecha_servicio' => 'fecha',
        'modalidad_comercial' => 'zgModalidadComercial',
        'tipo_instalacion' => 'zgTipoInstalacion',
        'tipo_equipo' => 'zgTipoEquipo',
        'tamano_contenedor' => 'zgTamanoContenedor',
        'numero_equipo' => 'equipoNo',
        'serie_unidad' => 'serialUnidad',
        'marca_equipo' => 'marcaEquipo',
        'modelo_equipo' => 'modeloEquipo',
        'controlador' => 'controladorEquipo',
        'anio_fabricacion' => 'anioFabricacion',
        'refrigerante' => 'refrigerante',
    ];

    /**
     * @param array<int, array<string, mixed>> $serviciosOdoo
     * @return array<string, string>
     */
    public function resolver(?array $preinspeccion, ?array $informeEdicion, array $serviciosOdoo): array
    {
        $out = $this->valoresVacios();

        if (is_array($informeEdicion)) {
            $this->aplicarInformeEdicion($out, $informeEdicion);
        }

        if (is_array($preinspeccion)) {
            $this->aplicarPreinspeccion($out, $preinspeccion);
        }

        $svc = $this->buscarServicioOdoo(
            $serviciosOdoo,
            $out['orden'],
            $out['odooTicketRef']
        );
        if ($svc !== null) {
            $this->aplicarOdoo($out, $svc);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function preinspeccionParaJs(?array $preinspeccion): ?array
    {
        return is_array($preinspeccion) ? $preinspeccion : null;
    }

    /**
     * @param array<int, array<string, mixed>> $serviciosOdoo
     */
    public function buscarServicioOdoo(array $serviciosOdoo, string $orden, string $ticketRef): ?array
    {
        $ordenDigits = preg_replace('/\D+/', '', $orden) ?? '';
        $ticket = trim($ticketRef);

        foreach ($serviciosOdoo as $svc) {
            if (!is_array($svc)) {
                continue;
            }
            if ($ticket !== '' && trim((string)($svc['ticket_ref'] ?? '')) === $ticket) {
                return $svc;
            }
        }

        if ($ordenDigits === '') {
            return null;
        }

        foreach ($serviciosOdoo as $svc) {
            if (!is_array($svc)) {
                continue;
            }
            $nro = preg_replace('/\D+/', '', (string)($svc['numero_reporte'] ?? '')) ?? '';
            $cot = preg_replace('/\D+/', '', (string)($svc['cotizacion'] ?? '')) ?? '';
            if (($nro !== '' && $nro === $ordenDigits) || ($cot !== '' && $cot === $ordenDigits)) {
                return $svc;
            }
        }

        return null;
    }

    /** @return array<string, string> Valores vacíos para ids del formulario existente. */
    public function valoresVaciosPublicos(): array
    {
        return $this->valoresVacios();
    }

    /** @return array<string, string> */
    private function valoresVacios(): array
    {
        return [
            'orden' => '',
            'cliente' => '',
            'fecha' => '',
            'direccion' => '',
            'direccion_coords' => '',
            'tecnico_id' => '',
            'tecnico_nombre' => '',
            'odooTicketRef' => '',
            'odooTicketRefDisplay' => '',
            'odooCotizacion' => '',
            'odooCotizacionDisplay' => '',
            'zgTipoEquipo' => '',
            'zgModalidadComercial' => '',
            'zgTipoInstalacion' => '',
            'zgTamanoContenedor' => '',
            'equipoNo' => '',
            'serialUnidad' => '',
            'marcaEquipo' => '',
            'modeloEquipo' => '',
            'controladorEquipo' => '',
            'anioFabricacion' => '',
            'refrigerante' => '',
            'setPoint' => '',
            'temperaturaAmbiente' => '',
            'retornoAire' => '',
            'suministroAire' => '',
            'presionAlta' => '',
            'presionBaja' => '',
            'voltajeL1L2' => '',
            'voltajeL2L3' => '',
            'voltajeL1L3' => '',
            'estadoInicial' => '',
            'estadoEncendido' => '',
            'estadoEnergia' => '',
            'estadoAlarma' => '',
            'alarmaEncontrada' => '',
            'observacionInicial' => '',
            'gensetHorometroInicial' => '',
            'gensetVoltajeBateriaInicial' => '',
            'gensetNivelCombustibleInicial' => '',
            'gensetNivelAceiteInicial' => '',
            'gensetRefrigeranteMotorInicial' => '',
            'gensetArranqueInicial' => '',
            'gensetFrecuenciaInicial' => '',
            'gensetPresionAceiteInicial' => '',
        ];
    }

    /** @param array<string, string> $out */
    private function aplicarPreinspeccion(array &$out, array $pre): void
    {
        foreach (self::MAPA_PREINSPECCION as $col => $fieldId) {
            if ($out[$fieldId] !== '') {
                continue;
            }
            $val = $this->formatearValor($col, $pre[$col] ?? null);
            if ($val !== '') {
                $out[$fieldId] = $val;
            }
        }

        if ($out['fecha'] === '') {
            $out['fecha'] = $this->normalizarFecha((string)($pre['creado_en'] ?? ''));
        }
        if ($out['direccion'] === '') {
            $out['direccion'] = trim((string)($pre['ubicacion_texto'] ?? ''));
        }
        if ($out['direccion_coords'] === ''
            && trim((string)($pre['latitud'] ?? '')) !== ''
            && trim((string)($pre['longitud'] ?? '')) !== '') {
            $out['direccion_coords'] = trim((string)$pre['latitud']) . ', ' . trim((string)$pre['longitud']);
        }
        if ($out['tecnico_id'] === '') {
            $out['tecnico_id'] = trim((string)($pre['tecnico_id'] ?? ''));
        }
        $out['tecnico_nombre'] = trim((string)($pre['tecnico_nombre'] ?? ''));

        if ($out['zgTipoEquipo'] === '') {
            $out['zgTipoEquipo'] = PreinspeccionModel::resolverTipoEquipo($pre);
        }

        $estado = $this->parseEstadoInicial($out['estadoInicial']);
        foreach ($estado as $k => $v) {
            if ($out[$k] === '' && $v !== '') {
                $out[$k] = $v;
            }
        }

        if ($out['observacionInicial'] === '') {
            $out['observacionInicial'] = $this->limpiarObservacion((string)($pre['observacion_inicial'] ?? ''));
        }

        if ($out['odooTicketRefDisplay'] === '' && $out['odooTicketRef'] !== '') {
            $out['odooTicketRefDisplay'] = $out['odooTicketRef'];
        }
    }

    /** @param array<string, string> $out */
    private function aplicarInformeEdicion(array &$out, array $inf): void
    {
        $map = [
            'orden' => 'orden',
            'cliente' => 'cliente',
            'fecha' => 'fecha',
            'direccion' => 'direccion',
            'tecnico_id' => 'tecnico_id',
            'odoo_ticket_ref' => 'odooTicketRef',
        ];
        foreach ($map as $col => $fieldId) {
            $val = trim((string)($inf[$col] ?? ''));
            if ($fieldId === 'fecha') {
                $val = $this->normalizarFecha($val);
            }
            if ($val !== '') {
                $out[$fieldId] = $val;
            }
        }
        if ($out['odooTicketRef'] !== '') {
            $out['odooTicketRefDisplay'] = $out['odooTicketRef'];
        }
        $out['tecnico_nombre'] = trim((string)($inf['tecnico_nombre'] ?? ''));
    }

    /** @param array<string, string> $out */
    private function aplicarOdoo(array &$out, array $svc): void
    {
        foreach (self::MAPA_ODOO as $col => $fieldId) {
            if ($out[$fieldId] !== '') {
                continue;
            }
            $val = trim((string)($svc[$col] ?? ''));
            if ($col === 'direccion' && ($val === '' || preg_match('/virtual\s+locations|production|wh\/stock|inventory/i', $val))) {
                continue;
            }
            if ($col === 'fecha_servicio') {
                $val = $this->normalizarFecha($val);
            }
            if ($col === 'numero_reporte' && $val === '') {
                continue;
            }
            if ($val !== '') {
                $out[$fieldId] = $val;
            }
        }

        $cot = trim((string)($svc['cotizacion'] ?? ''));
        if ($cot !== '') {
            $out['odooCotizacion'] = $cot;
            $out['odooCotizacionDisplay'] = $cot;
        }

        if ($out['odooTicketRef'] !== '') {
            $out['odooTicketRefDisplay'] = $out['odooTicketRef'];
        }
    }

    private function formatearValor(string $columna, mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }
        if (in_array($columna, ['set_point', 'temperatura_ambiente', 'retorno_aire', 'suministro_aire', 'genset_horometro_inicial', 'genset_frecuencia_inicial'], true)) {
            $num = is_numeric($valor) ? (float)$valor : null;
            if ($num !== null) {
                return sprintf('%.2f', $num);
            }
        }
        return trim((string)$valor);
    }

    /** @return array<string, string> */
    private function parseEstadoInicial(string $raw): array
    {
        $v = mb_strtolower(trim($raw));
        $v = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $v) ?: $v;
        $v = preg_replace('/\s+/', ' ', $v) ?? $v;

        $out = ['estadoEncendido' => '', 'estadoEnergia' => '', 'estadoAlarma' => ''];
        if (str_contains($v, 'apagado')) {
            $out['estadoEncendido'] = 'Apagado';
        } elseif (str_contains($v, 'encendido')) {
            $out['estadoEncendido'] = 'Encendido';
        }
        if (str_contains($v, 'sin suministro') || str_contains($v, 'sin energia')) {
            $out['estadoEnergia'] = 'Sin suministro eléctrico';
        } elseif (str_contains($v, 'con suministro') || str_contains($v, 'con energia') || str_contains($v, 'suministro')) {
            $out['estadoEnergia'] = 'Con suministro eléctrico';
        }
        if (str_contains($v, 'sin alarma')) {
            $out['estadoAlarma'] = 'Sin alarma';
        } elseif (str_contains($v, 'con alarma') || str_contains($v, 'alarma')) {
            $out['estadoAlarma'] = 'Con alarma';
        }
        return $out;
    }

    private function limpiarObservacion(string $texto): string
    {
        return trim((string)preg_replace('/\s*\[\[ZG_META:[A-Za-z0-9+\/=_-]+\]\]\s*/', ' ', $texto));
    }

    private function normalizarFecha(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $m)) {
            return $m[1];
        }
        $ts = strtotime($raw);
        return $ts !== false ? date('Y-m-d', $ts) : '';
    }
}
