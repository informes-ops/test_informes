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

    public static function tablaExiste(PDO $pdo, string $tabla): bool
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $stmt->execute([$tabla]);
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

    /** Aplica migraciones incrementales alineadas con zgroupin_zgroupinformes.sql. */
    public static function asegurarEsquemaCompleto(PDO $pdo): void
    {
        self::asegurarColumnasInformes($pdo);
        self::asegurarColumnasPreinspecciones($pdo);
        self::asegurarColumnasCatalogos($pdo);
        self::asegurarTablasOpcionesTecnicas($pdo);
        self::asegurarTablasSalidasTecnicas($pdo);
        self::asegurarTablasTelegram($pdo);
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
        if (!self::tablaExiste($pdo, 'informes')) {
            return;
        }

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

    public static function asegurarColumnasPreinspecciones(PDO $pdo): void
    {
        if (!self::tablaExiste($pdo, 'inspecciones_preliminares')) {
            return;
        }

        $columnas = [
            'modalidad_comercial'              => 'VARCHAR(40) DEFAULT NULL',
            'tipo_instalacion'                 => 'VARCHAR(80) DEFAULT NULL',
            'tipo_equipo'                      => 'VARCHAR(30) DEFAULT NULL',
            'tamano_contenedor'                => 'VARCHAR(60) DEFAULT NULL',
            'presion_alta'                     => 'VARCHAR(50) DEFAULT NULL',
            'presion_baja'                     => 'VARCHAR(50) DEFAULT NULL',
            'alarma_encontrada'                => 'VARCHAR(180) DEFAULT NULL',
            'genset_horometro_inicial'         => 'DECIMAL(12,1) DEFAULT NULL',
            'genset_voltaje_bateria_inicial'   => 'VARCHAR(50) DEFAULT NULL',
            'genset_nivel_combustible_inicial' => 'VARCHAR(40) DEFAULT NULL',
            'genset_nivel_aceite_inicial'      => 'VARCHAR(50) DEFAULT NULL',
            'genset_refrigerante_motor_inicial'=> 'VARCHAR(60) DEFAULT NULL',
            'genset_arranque_inicial'          => 'VARCHAR(80) DEFAULT NULL',
            'genset_frecuencia_inicial'        => 'DECIMAL(8,2) DEFAULT NULL',
            'genset_presion_aceite_inicial'    => 'VARCHAR(50) DEFAULT NULL',
            'evidencias_json'                  => 'LONGTEXT DEFAULT NULL',
            'actualizado_en'                   => 'DATETIME DEFAULT NULL',
            'hora_inicio_servicio'             => 'DATETIME DEFAULT NULL',
            'hora_fin_servicio'                => 'DATETIME DEFAULT NULL',
            'modelo_equipo'                    => 'VARCHAR(100) DEFAULT NULL',
            'anio_fabricacion'                 => 'VARCHAR(4) DEFAULT NULL',
            'odoo_ticket_ref'                  => 'VARCHAR(120) DEFAULT NULL',
        ];

        foreach ($columnas as $nombre => $def) {
            self::agregarColumnaSiFalta($pdo, 'inspecciones_preliminares', $nombre, $def);
        }
    }

    public static function asegurarColumnasCatalogos(PDO $pdo): void
    {
        if (self::tablaExiste($pdo, 'clientes_catalogo')) {
            foreach ([
                'ruc'       => 'VARCHAR(30) DEFAULT NULL',
                'contacto'  => 'VARCHAR(160) DEFAULT NULL',
                'telefono'  => 'VARCHAR(80) DEFAULT NULL',
                'correo'    => 'VARCHAR(180) DEFAULT NULL',
                'direccion' => 'VARCHAR(255) DEFAULT NULL',
                'origen'    => 'VARCHAR(30) DEFAULT NULL',
            ] as $col => $def) {
                self::agregarColumnaSiFalta($pdo, 'clientes_catalogo', $col, $def);
            }
        }

        if (self::tablaExiste($pdo, 'cotizaciones_catalogo')) {
            foreach ([
                'ticket_ref'      => 'VARCHAR(30) DEFAULT NULL',
                'cotizacion_odoo' => 'VARCHAR(80) DEFAULT NULL',
                'origen'          => 'VARCHAR(30) DEFAULT NULL',
            ] as $col => $def) {
                self::agregarColumnaSiFalta($pdo, 'cotizaciones_catalogo', $col, $def);
            }
        }

        if (self::tablaExiste($pdo, 'contenedores_catalogo')) {
            foreach ([
                'modelo_equipo'       => 'VARCHAR(100) DEFAULT NULL',
                'anio_fabricacion'    => 'SMALLINT UNSIGNED DEFAULT NULL',
                'tamano_contenedor'   => 'VARCHAR(60) DEFAULT NULL',
                'modalidad_comercial' => 'VARCHAR(40) DEFAULT NULL',
                'tipo_equipo'         => 'VARCHAR(30) DEFAULT NULL',
                'ticket_ref'          => 'VARCHAR(30) DEFAULT NULL',
                'cliente_nombre'      => 'VARCHAR(180) DEFAULT NULL',
                'origen'              => 'VARCHAR(30) DEFAULT NULL',
            ] as $col => $def) {
                self::agregarColumnaSiFalta($pdo, 'contenedores_catalogo', $col, $def);
            }
        }

        if (self::tablaExiste($pdo, 'maquinas_catalogo')) {
            foreach ([
                'modelo_equipo'    => 'VARCHAR(100) DEFAULT NULL',
                'anio_fabricacion' => 'SMALLINT UNSIGNED DEFAULT NULL',
                'numero_equipo'    => 'VARCHAR(60) DEFAULT NULL',
                'ticket_ref'       => 'VARCHAR(30) DEFAULT NULL',
                'cliente_nombre'   => 'VARCHAR(180) DEFAULT NULL',
                'origen'           => 'VARCHAR(30) DEFAULT NULL',
            ] as $col => $def) {
                self::agregarColumnaSiFalta($pdo, 'maquinas_catalogo', $col, $def);
            }
        }

        foreach (['modelos_reefer_catalogo', 'modelos_genset_catalogo'] as $tabla) {
            if (self::tablaExiste($pdo, $tabla)) {
                self::agregarColumnaSiFalta($pdo, $tabla, 'creado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
            }
        }
    }

    public static function asegurarTablasOpcionesTecnicas(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS opciones_tecnicas_personalizadas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo_equipo VARCHAR(20) NOT NULL,
            categoria VARCHAR(30) NOT NULL,
            texto VARCHAR(220) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_por_tecnico_id INT DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            actualizado_en DATETIME DEFAULT NULL,
            UNIQUE KEY uq_opcion_tecnica (tipo_equipo, categoria, texto)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS opciones_tecnicas_por_trabajo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo_equipo VARCHAR(20) NOT NULL,
            trabajo_clave VARCHAR(100) NOT NULL,
            trabajo_nombre VARCHAR(180) DEFAULT NULL,
            categoria VARCHAR(30) NOT NULL,
            texto VARCHAR(220) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_por_tecnico_id INT DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            actualizado_en DATETIME DEFAULT NULL,
            UNIQUE KEY uq_opcion_por_trabajo (tipo_equipo, trabajo_clave, categoria, texto)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        self::agregarColumnaSiFalta($pdo, 'opciones_tecnicas_personalizadas', 'actualizado_en', 'DATETIME DEFAULT NULL');
        self::agregarColumnaSiFalta($pdo, 'opciones_tecnicas_por_trabajo', 'actualizado_en', 'DATETIME DEFAULT NULL');
        self::agregarColumnaSiFalta($pdo, 'opciones_tecnicas_por_trabajo', 'creado_por_tecnico_id', 'INT DEFAULT NULL');
    }

    public static function asegurarTablasSalidasTecnicas(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS salidas_tecnicas (
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

        $pdo->exec("CREATE TABLE IF NOT EXISTS salidas_tecnicas_materiales (
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

    public static function asegurarTablasTelegram(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tg_sesion (
            chat_id BIGINT NOT NULL,
            sesion_id VARCHAR(40) NOT NULL,
            estado VARCHAR(20) NOT NULL DEFAULT 'abierta',
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (chat_id, sesion_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS tg_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            chat_id BIGINT NOT NULL,
            sesion_id VARCHAR(40) NOT NULL,
            tipo VARCHAR(10) NOT NULL,
            valor TEXT DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tg_items_sesion (chat_id, sesion_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
