<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Acceso a datos de técnicos.
 */
class TecnicoModel extends Model
{
    public function findActivos(): array
    {
        return $this->pdo
            ->query('SELECT * FROM tecnicos WHERE activo = 1 ORDER BY nombre')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tecnicos WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getNombre(int $id, string $default = 'Técnico'): string
    {
        $row = $this->findById($id);
        return ($row && !empty($row['nombre'])) ? (string)$row['nombre'] : $default;
    }

    public function crear(string $nombre): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO tecnicos (nombre) VALUES (?)');
        $stmt->execute([trim($nombre)]);
        return (int)$this->pdo->lastInsertId();
    }
}
