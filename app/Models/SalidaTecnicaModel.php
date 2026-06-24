<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Salidas técnicas preparadas por supervisión.
 */
class SalidaTecnicaModel extends Model
{
    public function asegurarTablas(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS salidas_tecnicas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cotizacion VARCHAR(100) NOT NULL,
            cliente VARCHAR(180) DEFAULT NULL,
            equipo VARCHAR(100) DEFAULT NULL,
            tecnico_responsable_id INT DEFAULT NULL,
            tecnico_responsable_nombre VARCHAR(180) DEFAULT NULL,
            tecnicos_apoyo TEXT DEFAULT NULL,
            observacion TEXT DEFAULT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_salidas_cotizacion (cotizacion),
            INDEX idx_salidas_activo (activo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS salidas_tecnicas_materiales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            salida_id INT NOT NULL,
            repuesto_id INT DEFAULT NULL,
            codigo VARCHAR(60) DEFAULT NULL,
            detalle VARCHAR(220) NOT NULL,
            cantidad VARCHAR(40) DEFAULT NULL,
            unidad VARCHAR(60) DEFAULT NULL,
            observacion VARCHAR(220) DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_salida_material_salida (salida_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function listarActivasConMateriales(int $limite = 200): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM salidas_tecnicas WHERE activo = 1 ORDER BY creado_en DESC LIMIT $limite"
        );
        $salidasTmp = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtMat = $this->pdo->prepare(
            "SELECT repuesto_id, COALESCE(codigo,'') AS codigo, detalle,
                    COALESCE(cantidad,'') AS cantidad, COALESCE(unidad,'') AS unidad,
                    COALESCE(observacion,'') AS observacion
             FROM salidas_tecnicas_materiales WHERE salida_id = ? ORDER BY id ASC"
        );

        $result = [];
        foreach ($salidasTmp as $sal) {
            $stmtMat->execute([(int)$sal['id']]);
            $mats = $stmtMat->fetchAll(PDO::FETCH_ASSOC);
            $apoyo = [];
            if (!empty($sal['tecnicos_apoyo'])) {
                $dec = json_decode((string)$sal['tecnicos_apoyo'], true);
                if (is_array($dec)) {
                    $apoyo = $dec;
                }
            }
            $result[] = [
                'id' => (int)$sal['id'],
                'cotizacion' => (string)$sal['cotizacion'],
                'cliente' => (string)($sal['cliente'] ?? ''),
                'equipo' => (string)($sal['equipo'] ?? ''),
                'tecnico_responsable' => (string)($sal['tecnico_responsable_nombre'] ?? ''),
                'tecnicos_apoyo' => $apoyo,
                'observacion' => (string)($sal['observacion'] ?? ''),
                'materiales' => $mats,
                'creado_en' => (string)($sal['creado_en'] ?? ''),
            ];
        }
        return $result;
    }
}
