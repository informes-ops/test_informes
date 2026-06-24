<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\BorradorServicioModel;
use App\Models\InformeModel;
use App\Models\PreinspeccionModel;
use PDO;

/**
 * Memoria técnica: opciones personalizadas y histórico por equipo/trabajo.
 */
class OpcionTecnicaService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    /** @return array{reefer: array, genset: array} */
    public function cargarOpcionesPersonalizadas(): array
    {
        $banco = [
            'reefer' => ['actividades' => [], 'hallazgos' => []],
            'genset' => ['actividades' => [], 'hallazgos' => []],
        ];

        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS opciones_tecnicas_personalizadas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo_equipo VARCHAR(20) NOT NULL, categoria VARCHAR(30) NOT NULL,
                texto VARCHAR(220) NOT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
                creado_por_tecnico_id INT DEFAULT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_opcion_tecnica (tipo_equipo, categoria, texto)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $stmt = $this->pdo->query(
                'SELECT tipo_equipo, categoria, texto FROM opciones_tecnicas_personalizadas
                 WHERE activo = 1 ORDER BY texto ASC'
            );
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $op) {
                $tipo = strtolower(trim((string)($op['tipo_equipo'] ?? '')));
                $cat = strtolower(trim((string)($op['categoria'] ?? '')));
                $txt = trim((string)($op['texto'] ?? ''));
                if (isset($banco[$tipo][$cat]) && $txt !== '') {
                    $banco[$tipo][$cat][] = $txt;
                }
            }

            $this->cargarMemoriaDesdeInformes($banco);
            $this->cargarMemoriaDesdeBorradores($banco);
        } catch (\Throwable $e) {
        }

        return $banco;
    }

    /** @return array{reefer: array, genset: array} */
    public function cargarOpcionesPorTrabajo(): array
    {
        $banco = ['reefer' => [], 'genset' => []];

        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS opciones_tecnicas_por_trabajo (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo_equipo VARCHAR(20) NOT NULL, trabajo_clave VARCHAR(100) NOT NULL,
                trabajo_nombre VARCHAR(180) DEFAULT NULL, categoria VARCHAR(30) NOT NULL,
                texto VARCHAR(220) NOT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY uq_opcion_por_trabajo (tipo_equipo, trabajo_clave, categoria, texto)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $rows = $this->pdo->query(
                'SELECT tipo_equipo, trabajo_clave, categoria, texto FROM opciones_tecnicas_por_trabajo
                 WHERE activo = 1 ORDER BY actualizado_en DESC, texto ASC'
            )->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $this->agregarOpcionTrabajo(
                    $banco,
                    (string)($row['tipo_equipo'] ?? ''),
                    (string)($row['trabajo_clave'] ?? ''),
                    (string)($row['categoria'] ?? ''),
                    (string)($row['texto'] ?? '')
                );
            }

            $informeModel = new InformeModel($this->pdo);
            foreach ($informeModel->listarSnapshotsRecientes(400) as $json) {
                $snap = json_decode($json, true);
                if (is_array($snap)) {
                    $this->cargarMemoriaTrabajoDesdeSnapshot($banco, $snap);
                }
            }

            $borradorModel = new BorradorServicioModel($this->pdo);
            foreach ($borradorModel->listarParaMemoria(400) as $row) {
                $snap = json_decode((string)($row['datos_json'] ?? ''), true);
                if (is_array($snap)) {
                    $this->cargarMemoriaTrabajoDesdeSnapshot($banco, $snap);
                }
            }
        } catch (\Throwable $e) {
        }

        return $banco;
    }

    private function cargarMemoriaDesdeInformes(array &$banco): void
    {
        $rows = $this->pdo->query(
            "SELECT datos_json FROM informes WHERE datos_json IS NOT NULL AND datos_json <> ''
             ORDER BY id DESC LIMIT 300"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $snap = json_decode((string)($row['datos_json'] ?? ''), true);
            if (is_array($snap)) {
                $this->cargarMemoriaDesdeSnapshot($banco, $snap);
            }
        }
    }

    private function cargarMemoriaDesdeBorradores(array &$banco): void
    {
        $borradorModel = new BorradorServicioModel($this->pdo);
        foreach ($borradorModel->listarParaMemoria(300) as $row) {
            $snap = json_decode((string)($row['datos_json'] ?? ''), true);
            if (is_array($snap)) {
                $this->cargarMemoriaDesdeSnapshot($banco, $snap);
            }
        }
    }

    private function cargarMemoriaDesdeSnapshot(array &$banco, array $snapshot): void
    {
        $tipo = $this->tipoEquipoSnapshot($snapshot);
        $seleccionados = $snapshot['state']['selected'] ?? [];
        if (!is_array($seleccionados)) {
            return;
        }
        foreach ($seleccionados as $trabajo) {
            if (!is_array($trabajo)) {
                continue;
            }
            $auto = $trabajo['auto'] ?? [];
            if (!is_array($auto)) {
                continue;
            }
            foreach (['actividades', 'acciones'] as $campo) {
                foreach (($auto[$campo] ?? []) as $item) {
                    $this->agregarOpcionMemoria($banco, $tipo, 'actividades', (string)$item);
                }
            }
            foreach (($auto['hallazgos'] ?? []) as $item) {
                $this->agregarOpcionMemoria($banco, $tipo, 'hallazgos', (string)$item);
            }
        }
    }

    private function cargarMemoriaTrabajoDesdeSnapshot(array &$banco, array $snapshot): void
    {
        $tipo = $this->tipoEquipoSnapshot($snapshot);
        foreach (($snapshot['state']['selected'] ?? []) as $trabajo) {
            if (!is_array($trabajo)) {
                continue;
            }
            $clave = $this->claveTrabajo($trabajo);
            $auto = $trabajo['auto'] ?? [];
            if (!is_array($auto)) {
                continue;
            }
            foreach (['actividades', 'acciones'] as $campo) {
                foreach (($auto[$campo] ?? []) as $item) {
                    $this->agregarOpcionTrabajo($banco, $tipo, $clave, 'actividades', (string)$item);
                }
            }
            foreach (($auto['hallazgos'] ?? []) as $item) {
                $this->agregarOpcionTrabajo($banco, $tipo, $clave, 'hallazgos', (string)$item);
            }
        }
    }

    private function agregarOpcionMemoria(array &$banco, string $tipo, string $categoria, string $texto): void
    {
        $tipo = strtolower(trim($tipo));
        $categoria = strtolower(trim($categoria));
        $texto = $this->normalizar($texto);
        if ($texto === '' || !isset($banco[$tipo][$categoria])) {
            return;
        }
        $clave = mb_strtolower($texto, 'UTF-8');
        foreach ($banco[$tipo][$categoria] as $existente) {
            if (mb_strtolower(trim((string)$existente), 'UTF-8') === $clave) {
                return;
            }
        }
        $banco[$tipo][$categoria][] = $texto;
    }

    private function agregarOpcionTrabajo(array &$banco, string $tipo, string $trabajoClave, string $categoria, string $texto): void
    {
        $tipo = strtolower(trim($tipo));
        $trabajoClave = strtolower(trim($trabajoClave));
        $categoria = strtolower(trim($categoria));
        $texto = $this->normalizar($texto);
        if ($texto === '' || !in_array($tipo, ['reefer', 'genset'], true)) {
            return;
        }
        if ($trabajoClave === '') {
            $trabajoClave = 'trabajo_general';
        }
        if (!isset($banco[$tipo][$trabajoClave])) {
            $banco[$tipo][$trabajoClave] = ['actividades' => [], 'hallazgos' => []];
        }
        $clave = mb_strtolower($texto, 'UTF-8');
        foreach ($banco[$tipo][$trabajoClave][$categoria] ?? [] as $existente) {
            if (mb_strtolower(trim((string)$existente), 'UTF-8') === $clave) {
                return;
            }
        }
        $banco[$tipo][$trabajoClave][$categoria][] = $texto;
    }

    private function tipoEquipoSnapshot(array $snapshot): string
    {
        $tipo = trim((string)($snapshot['fields']['zgTipoEquipo']['value'] ?? ''));
        $tipoNorm = strtolower($tipo);
        if (str_contains($tipoNorm, 'genset') || str_contains($tipoNorm, 'generador')) {
            return 'genset';
        }
        foreach (($snapshot['state']['selected'] ?? []) as $trabajo) {
            if (!is_array($trabajo)) {
                continue;
            }
            $id = strtolower((string)($trabajo['id'] ?? ''));
            $nombre = strtolower((string)($trabajo['nombre'] ?? ''));
            if (str_starts_with($id, 'genset_') || str_contains($nombre, 'genset') || str_contains($nombre, 'generador')) {
                return 'genset';
            }
        }
        return 'reefer';
    }

    private function claveTrabajo(array $trabajo): string
    {
        $id = strtolower(trim((string)($trabajo['id'] ?? '')));
        if ($id !== '') {
            $id = preg_replace('/[^a-z0-9_]+/', '_', $id) ?? $id;
            return trim($id, '_') ?: 'trabajo_general';
        }
        $nombre = strtolower(trim((string)($trabajo['nombre'] ?? '')));
        if (function_exists('iconv')) {
            $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);
            if (is_string($tmp) && $tmp !== '') {
                $nombre = $tmp;
            }
        }
        $nombre = preg_replace('/[^a-z0-9]+/', '_', $nombre) ?? $nombre;
        return trim($nombre, '_') ?: 'trabajo_general';
    }

    private function normalizar(string $texto): string
    {
        $texto = trim(preg_replace('/\s+/u', ' ', strip_tags($texto)) ?? $texto);
        return function_exists('mb_substr') ? trim(mb_substr($texto, 0, 220, 'UTF-8')) : trim(substr($texto, 0, 220));
    }
}
