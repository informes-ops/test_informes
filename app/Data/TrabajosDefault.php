<?php
declare(strict_types=1);

namespace App\Data;

/**
 * Trabajos por defecto para el formulario técnico (Reefer y Genset).
 */
final class TrabajosDefault
{
    public static function all(): array
    {
        return [
            ['slug' => 'asistencia_tecnica', 'nombre' => 'ASISTENCIA TÉCNICA'],
            ['slug' => 'mantenimiento_correctivo', 'nombre' => 'MANTENIMIENTO CORRECTIVO'],
            ['slug' => 'mantenimiento_productivo', 'nombre' => 'MANTENIMIENTO PREVENTIVO'],
            ['slug' => 'instalacion_luminarias', 'nombre' => 'INSTALACIÓN DE LUMINARIAS'],
            ['slug' => 'deshielo_contenedor', 'nombre' => 'DESHIELO DE CONTENEDOR'],
            ['slug' => 'instalacion_reefer', 'nombre' => 'INSTALACIÓN DE REEFER'],
            ['slug' => 'genset_mantenimiento_preventivo', 'nombre' => 'MANTENIMIENTO PREVENTIVO DE GENSET'],
            ['slug' => 'genset_mantenimiento_correctivo', 'nombre' => 'MANTENIMIENTO CORRECTIVO DE GENSET'],
        ];
    }
}
