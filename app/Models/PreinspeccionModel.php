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
}
