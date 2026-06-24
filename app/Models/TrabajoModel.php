<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Data\TrabajosDefault;
use PDO;

/**
 * CRUD de tipos de trabajo (trabajos_realizados).
 */
class TrabajoModel extends Model
{
    public function asegurarTabla(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS trabajos_realizados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(90) NOT NULL UNIQUE,
            nombre VARCHAR(180) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function sembrarSiVacio(): void
    {
        $count = (int)$this->pdo->query('SELECT COUNT(*) FROM trabajos_realizados')->fetchColumn();
        if ($count > 0) {
            return;
        }
        $stmt = $this->pdo->prepare('INSERT INTO trabajos_realizados (slug, nombre, activo) VALUES (?, ?, 1)');
        foreach (TrabajosDefault::all() as $w) {
            $stmt->execute([$w['slug'], $w['nombre']]);
        }
    }

    public function sincronizarV9(): void
    {
        $stmtExiste = $this->pdo->prepare('SELECT id FROM trabajos_realizados WHERE slug = ? LIMIT 1');
        $stmtUpdate = $this->pdo->prepare('UPDATE trabajos_realizados SET nombre = ?, activo = 1 WHERE slug = ?');
        $stmtInsert = $this->pdo->prepare('INSERT INTO trabajos_realizados (slug, nombre, activo) VALUES (?, ?, 1)');

        foreach (TrabajosDefault::all() as $w) {
            $stmtExiste->execute([$w['slug']]);
            if ($stmtExiste->fetchColumn()) {
                $stmtUpdate->execute([$w['nombre'], $w['slug']]);
            } else {
                $stmtInsert->execute([$w['slug'], $w['nombre']]);
            }
        }

        $this->pdo->exec("UPDATE trabajos_realizados SET activo = 0 WHERE slug IN (
            'genset_inspeccion_diagnostico','genset_cambio_aceite_filtros',
            'genset_sistema_electrico','genset_prueba_carga',
            'ingreso_new_genset','reparacion_genset'
        )");
    }

    public function listarActivos(): array
    {
        $rows = $this->pdo
            ->query("SELECT slug AS id, nombre FROM trabajos_realizados WHERE activo = 1 ORDER BY nombre")
            ->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            return $rows;
        }

        return array_map(
            static fn(array $w) => ['id' => $w['slug'], 'nombre' => $w['nombre']],
            TrabajosDefault::all()
        );
    }
}
