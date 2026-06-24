<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Acceso a datos de inspecciones preliminares.
 */
class PreinspeccionModel extends Model
{
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM inspecciones_preliminares WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ip.*, t.nombre AS tecnico_nombre
             FROM inspecciones_preliminares ip
             LEFT JOIN tecnicos t ON t.id = ip.tecnico_id
             WHERE ip.token_continuacion = ?
             LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findAbiertasPorTecnico(int $tecnicoId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM inspecciones_preliminares
             WHERE tecnico_id = ? AND estado = 'abierto'
             ORDER BY creado_en DESC"
        );
        $stmt->execute([$tecnicoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cerrarConInforme(int $id, int $informeId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE inspecciones_preliminares
             SET estado = 'cerrado', informe_id = ?, finalizado_en = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$informeId, $id]);
    }

    public function eliminarPorIds(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn($id) => $id > 0));
        if ($ids === []) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM inspecciones_preliminares WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }

    public function findWithTecnicoById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ip.*, t.nombre AS tecnico_nombre FROM inspecciones_preliminares ip
             LEFT JOIN tecnicos t ON t.id = ip.tecnico_id WHERE ip.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Determina si la preinspección corresponde a Genset.
     */
    public static function esGenset(?array $preinspeccion): bool
    {
        if (!is_array($preinspeccion)) {
            return false;
        }
        $tipo = trim((string)($preinspeccion['tipo_equipo'] ?? ''));
        $ctrl = strtoupper(trim((string)($preinspeccion['controlador'] ?? '')));
        $tieneDatoGenset = trim((string)($preinspeccion['genset_horometro_inicial'] ?? '')) !== ''
            || trim((string)($preinspeccion['genset_voltaje_bateria_inicial'] ?? '')) !== ''
            || trim((string)($preinspeccion['genset_frecuencia_inicial'] ?? '')) !== '';

        return strcasecmp($tipo, 'Genset') === 0
            || (bool)preg_match('/^SG[- ]?(3000|5000)$/i', $ctrl)
            || $tieneDatoGenset;
    }

    public static function resolverTipoEquipo(?array $preinspeccion): string
    {
        if (!is_array($preinspeccion)) {
            return '';
        }
        $tipo = trim((string)($preinspeccion['tipo_equipo'] ?? ''));
        if ($tipo === '') {
            return self::esGenset($preinspeccion) ? 'Genset' : 'Reefer';
        }
        return $tipo;
    }
}
