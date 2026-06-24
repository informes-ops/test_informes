<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Acceso a catálogos (clientes, contenedores, máquinas, repuestos, etc.).
 */
class CatalogoModel extends Model
{
    public function clientesActivos(): array
    {
        return $this->pdo
            ->query('SELECT id, nombre FROM clientes_catalogo WHERE activo = 1 ORDER BY nombre')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contenedoresActivos(): array
    {
        return $this->pdo
            ->query('SELECT id, numero, descripcion FROM contenedores_catalogo WHERE activo = 1 ORDER BY numero')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function maquinasActivas(): array
    {
        return $this->pdo
            ->query('SELECT id, serial_unidad, marca_equipo, controlador FROM maquinas_catalogo WHERE activo = 1 ORDER BY serial_unidad')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trabajosActivos(): array
    {
        return $this->pdo
            ->query('SELECT id, slug, nombre FROM trabajos_realizados WHERE activo = 1 ORDER BY nombre')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function upsertCliente(string $nombre): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO clientes_catalogo (nombre, activo) VALUES (?, 1)
             ON DUPLICATE KEY UPDATE activo = 1'
        );
        $stmt->execute([trim($nombre)]);
        return (int)$this->pdo->lastInsertId();
    }
}
