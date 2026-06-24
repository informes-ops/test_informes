<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Borradores de servicio (segunda etapa del formulario).
 */
class BorradorServicioModel extends Model
{
    public function asegurarTabla(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS borradores_servicio (
            preinspeccion_id INT NOT NULL PRIMARY KEY,
            token_continuacion VARCHAR(120) DEFAULT NULL,
            datos_json LONGTEXT NOT NULL,
            actualizado_en DATETIME NOT NULL,
            INDEX idx_borrador_token (token_continuacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function findByPreinspeccion(int $preinspeccionId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT datos_json, actualizado_en FROM borradores_servicio WHERE preinspeccion_id = ? LIMIT 1'
        );
        $stmt->execute([$preinspeccionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $decoded = json_decode((string)($row['datos_json'] ?? ''), true);
        if (!is_array($decoded)) {
            return null;
        }

        return [
            'snapshot' => $decoded,
            'actualizado_en' => (string)($row['actualizado_en'] ?? ''),
        ];
    }

    /** @return array<int, array{datos_json: string}> */
    public function listarParaMemoria(int $limite = 300): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT datos_json FROM borradores_servicio
             WHERE datos_json IS NOT NULL AND datos_json <> \'\'
             ORDER BY actualizado_en DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
