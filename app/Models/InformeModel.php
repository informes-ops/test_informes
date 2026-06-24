<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\SchemaHelper;
use PDO;

/**
 * Acceso a datos de informes técnicos finales.
 */
class InformeModel extends Model
{
    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        SchemaHelper::asegurarColumnasInformes($this->pdo);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM informes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByPreinspeccion(int $preinspeccionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM informes WHERE preinspeccion_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$preinspeccionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorTecnico(int $tecnicoId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, t.nombre AS tecnico_nombre
             FROM informes i
             LEFT JOIN tecnicos t ON t.id = i.tecnico_id
             WHERE i.tecnico_id = ?
             ORDER BY i.creado_en DESC'
        );
        $stmt->execute([$tecnicoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarRecientes(int $desdeId = 0, int $limite = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, t.nombre AS tecnico_nombre
             FROM informes i
             LEFT JOIN tecnicos t ON t.id = i.tecnico_id
             WHERE i.id > ?
             ORDER BY i.id ASC
             LIMIT ?'
        );
        $stmt->bindValue(1, $desdeId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarPorIds(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn($id) => $id > 0));
        if ($ids === []) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM informes WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }

    public function actualizarOdooEstado(int $id, array $datos): void
    {
        $campos = [];
        $valores = [];
        foreach ($datos as $col => $val) {
            $campos[] = "`$col` = ?";
            $valores[] = $val;
        }
        $valores[] = $id;
        $sql = 'UPDATE informes SET ' . implode(', ', $campos) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($valores);
    }

    public function findWithTecnico(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, t.nombre AS tecnico_nombre FROM informes i
             LEFT JOIN tecnicos t ON t.id = i.tecnico_id WHERE i.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return string[] */
    public function listarSnapshotsRecientes(int $limite = 300): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT datos_json FROM informes WHERE datos_json IS NOT NULL AND datos_json <> ''
             ORDER BY id DESC LIMIT ?"
        );
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'datos_json');
    }

    /**
     * Carga informe para edición desde panel con snapshot y repuestos.
     * @return array{informe: ?array, snapshot: ?array, error: string}
     */
    public function cargarParaEdicion(int $id): array
    {
        $error = '';
        if ($id <= 0) {
            return ['informe' => null, 'snapshot' => null, 'error' => 'No se recibió un informe válido para editar.'];
        }

        try {
            SchemaHelper::asegurarColumnasInformes($this->pdo);
            $informe = $this->findWithTecnico($id);
            if (!$informe) {
                return ['informe' => null, 'snapshot' => null, 'error' => 'El informe solicitado no existe o fue eliminado.'];
            }

            $snapshot = null;
            $raw = trim((string)($informe['datos_json'] ?? ''));
            if ($raw !== '') {
                $tmp = json_decode($raw, true);
                if (is_array($tmp)) {
                    $snapshot = $tmp;
                }
            }

            $repuestosGuardados = trim((string)($informe['repuestos_manual'] ?? ''));
            if ($repuestosGuardados !== '') {
                if (!is_array($snapshot)) {
                    $snapshot = ['version' => 4, 'fields' => [], 'state' => []];
                }
                if (!isset($snapshot['fields']) || !is_array($snapshot['fields'])) {
                    $snapshot['fields'] = [];
                }
                $repSnap = trim((string)($snapshot['fields']['repuestosManual']['value'] ?? ''));
                if ($repSnap === '') {
                    $snapshot['fields']['repuestosManual'] = [
                        'type' => 'textarea', 'value' => $repuestosGuardados, 'checked' => false,
                    ];
                }
                $snapshot['fields']['requiereRepuesto'] = ['type' => 'hidden', 'value' => 'si', 'checked' => false];
            } elseif (is_array($snapshot)) {
                $repSnap = trim((string)($snapshot['fields']['repuestosManual']['value'] ?? ''));
                if ($repSnap !== '') {
                    $snapshot['fields']['requiereRepuesto'] = ['type' => 'hidden', 'value' => 'si', 'checked' => false];
                }
            }

            return ['informe' => $informe, 'snapshot' => $snapshot, 'error' => ''];
        } catch (\Throwable $e) {
            return ['informe' => null, 'snapshot' => null, 'error' => 'No se pudo abrir el informe para edición: ' . $e->getMessage()];
        }
    }
}
