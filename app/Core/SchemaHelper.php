<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Utilidades para migraciones incrementales de esquema MySQL.
 * Centraliza la lógica que antes estaba duplicada en guardar.php, panel.php, etc.
 */
final class SchemaHelper
{
    public static function columnaExiste(PDO $pdo, string $tabla, string $columna): bool
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$tabla, $columna]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function agregarColumnaSiFalta(
        PDO $pdo,
        string $tabla,
        string $columna,
        string $definicion
    ): void {
        if (!self::columnaExiste($pdo, $tabla, $columna)) {
            $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$columna` $definicion");
        }
    }

    /** Columnas Odoo en tabla informes. */
    public static function asegurarColumnasOdoo(PDO $pdo): void
    {
        $columnas = [
            'odoo_estado'           => "VARCHAR(40) NOT NULL DEFAULT 'pendiente'",
            'odoo_ticket_ref'       => 'VARCHAR(120) DEFAULT NULL',
            'odoo_ticket_id'        => 'BIGINT DEFAULT NULL',
            'odoo_attachment_id'    => 'BIGINT DEFAULT NULL',
            'odoo_nombre_adjunto'   => 'VARCHAR(255) DEFAULT NULL',
            'odoo_error'            => 'TEXT DEFAULT NULL',
            'odoo_intentos'         => 'INT NOT NULL DEFAULT 0',
            'odoo_ultimo_intento_en'=> 'DATETIME DEFAULT NULL',
            'odoo_sincronizado_en'  => 'DATETIME DEFAULT NULL',
        ];

        foreach ($columnas as $nombre => $def) {
            self::agregarColumnaSiFalta($pdo, 'informes', $nombre, $def);
        }
    }

    /** Columnas extendidas de informes finales. */
    public static function asegurarColumnasInformes(PDO $pdo): void
    {
        $columnas = [
            'preinspeccion_id'      => 'INT DEFAULT NULL',
            'datos_json'            => 'LONGTEXT DEFAULT NULL',
            'repuestos_manual'      => 'LONGTEXT DEFAULT NULL',
            'tipo_equipo'           => 'VARCHAR(30) DEFAULT NULL',
            'tamano_contenedor'     => 'VARCHAR(60) DEFAULT NULL',
            'actualizado_en'        => 'DATETIME DEFAULT NULL',
            'hora_inicio_servicio'  => 'DATETIME DEFAULT NULL',
            'hora_fin_servicio'     => 'DATETIME DEFAULT NULL',
        ];

        foreach ($columnas as $nombre => $def) {
            self::agregarColumnaSiFalta($pdo, 'informes', $nombre, $def);
        }

        self::asegurarColumnasOdoo($pdo);
    }
}
