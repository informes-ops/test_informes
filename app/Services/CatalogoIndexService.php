<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\SchemaHelper;
use App\Data\MaterialesGensetSG3000;
use App\Data\MaterialesReeferV12;
use PDO;

/**
 * CRUD y sembrado de catálogos usados en el formulario técnico (index).
 */
class CatalogoIndexService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }
    public function asegurarEsquemaCompleto(): void
    {
        SchemaHelper::asegurarEsquemaCompleto($this->pdo);
        $this->asegurarCatalogoClientesCotizaciones();
        $this->asegurarCatalogoGeneradores();
        $this->asegurarCatalogosV12();
    }

    /** @return array<string, array> */
    public function cargarTodos(): array
    {
        $vacios = [
            'clientes' => [], 'cotizaciones' => [], 'contenedores' => [], 'maquinas' => [],
            'generadores' => [], 'repuestos' => [], 'repuestosGenset' => [], 'repuestosReefer' => [],
            'modelosReefer' => [], 'modelosGenset' => [], 'serviciosOdoo' => [],
        ];

        try {
            $this->asegurarEsquemaCompleto();
            return [
                'clientes' => $this->listarClientes(),
                'cotizaciones' => $this->listarCotizaciones(),
                'contenedores' => $this->listarContenedores(),
                'maquinas' => $this->listarMaquinas(),
                'generadores' => $this->listarGeneradores(),
                'repuestos' => $this->listarRepuestosGenerales(),
                'repuestosGenset' => $this->listarRepuestosGenset(),
                'repuestosReefer' => $this->listarRepuestosReefer(),
                'modelosReefer' => $this->listarModelosReefer(),
                'modelosGenset' => $this->listarModelosGenset(),
                'serviciosOdoo' => $this->listarServiciosOdoo(),
            ];
        } catch (\Throwable $e) {
            return $vacios;
        }
    }

    private function asegurarCatalogoGeneradores(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS generadores_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero VARCHAR(60) NOT NULL UNIQUE,
            serial_unidad VARCHAR(100) DEFAULT NULL,
            marca_equipo VARCHAR(100) NOT NULL DEFAULT 'THERMO KING',
            controlador VARCHAR(40) NOT NULL,
            descripcion VARCHAR(220) DEFAULT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_generadores_serial (serial_unidad),
            INDEX idx_generadores_controlador (controlador)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_genset_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            controlador VARCHAR(40) NOT NULL,
            codigo VARCHAR(60) DEFAULT NULL,
            detalle VARCHAR(220) NOT NULL,
            unidad VARCHAR(40) DEFAULT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_genset_rep_controlador (controlador)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $st = $this->pdo->prepare("SELECT COUNT(*) FROM repuestos_genset_catalogo WHERE controlador = 'SG-3000'");
        $st->execute();
        if ((int)$st->fetchColumn() === 0) {
            $ins = $this->pdo->prepare(
                'INSERT INTO repuestos_genset_catalogo (controlador, codigo, detalle, unidad, activo) VALUES (?, ?, ?, ?, 1)'
            );
            foreach (MaterialesGensetSG3000::all() as $r) {
                $ins->execute(['SG-3000', $r[0] ?: null, $r[1], $r[2]]);
            }
        }
    }

    private function asegurarCatalogosV12(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS modelos_reefer_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            marca_equipo VARCHAR(100) NOT NULL,
            controlador VARCHAR(100) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_modelo_reefer (marca_equipo, controlador)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        try {
            $this->pdo->exec("UPDATE modelos_reefer_catalogo SET activo = 0
                WHERE UPPER(TRIM(marca_equipo)) = 'THERMO KING'
                  AND UPPER(REPLACE(TRIM(controlador), ' ', '')) = 'MP400'");
        } catch (\Throwable $e) {
        }

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS modelos_genset_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            marca_equipo VARCHAR(100) NOT NULL,
            controlador VARCHAR(100) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_modelo_genset (marca_equipo, controlador)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_reefer_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            marca_equipo VARCHAR(100) NOT NULL,
            controlador VARCHAR(100) NOT NULL,
            codigo VARCHAR(60) DEFAULT NULL,
            detalle VARCHAR(220) NOT NULL,
            unidad VARCHAR(40) DEFAULT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            INDEX idx_rep_reefer_modelo (marca_equipo, controlador)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS zgroup_config (
            clave VARCHAR(100) PRIMARY KEY, valor VARCHAR(220) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $seedStmt = $this->pdo->prepare("SELECT valor FROM zgroup_config WHERE clave = 'catalogos_v12_sembrados' LIMIT 1");
        $seedStmt->execute();
        if ((string)$seedStmt->fetchColumn() !== '') {
            return;
        }

        $modelosReefer = [
            ['THERMO KING', 'MP3000'], ['THERMO KING', 'MP4000'], ['THERMO KING', 'MP5000'],
            ['CARRIER', 'MICROLINK 2I'], ['CARRIER', 'MICROLINK 3'], ['CARRIER', 'MICROLINK 5'],
            ['STAR COOL', 'CIM5'], ['STAR COOL', 'CIM6'], ['DAIKIN', 'DAIKIN'],
        ];
        $insR = $this->pdo->prepare(
            "INSERT INTO modelos_reefer_catalogo (marca_equipo, controlador, activo) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE activo = VALUES(activo)"
        );
        foreach ($modelosReefer as $m) {
            $insR->execute($m);
        }

        $insG = $this->pdo->prepare(
            "INSERT INTO modelos_genset_catalogo (marca_equipo, controlador, activo) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE activo = VALUES(activo)"
        );
        foreach ([['THERMO KING', 'SG-3000'], ['THERMO KING', 'SG-5000']] as $m) {
            $insG->execute($m);
        }

        $count = (int)$this->pdo->query('SELECT COUNT(*) FROM repuestos_reefer_catalogo')->fetchColumn();
        if ($count === 0) {
            $ins = $this->pdo->prepare(
                'INSERT INTO repuestos_reefer_catalogo (marca_equipo, controlador, codigo, detalle, unidad, activo) VALUES (?, ?, ?, ?, ?, 1)'
            );
            foreach (MaterialesReeferV12::all() as $r) {
                $ins->execute([$r[0], $r[1], $r[2] !== '' ? $r[2] : null, $r[3], $r[4]]);
            }
        }

        $this->pdo->prepare(
            "INSERT INTO zgroup_config (clave, valor) VALUES ('catalogos_v12_sembrados', ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)"
        )->execute([date('Y-m-d H:i:s')]);
    }

    private function asegurarCatalogoClientesCotizaciones(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS clientes_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(180) NOT NULL,
            ruc VARCHAR(30) DEFAULT NULL, contacto VARCHAR(160) DEFAULT NULL,
            telefono VARCHAR(80) DEFAULT NULL, correo VARCHAR(180) DEFAULT NULL,
            direccion VARCHAR(255) DEFAULT NULL, origen VARCHAR(30) DEFAULT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1, creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_clientes_catalogo_nombre (nombre)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS cotizaciones_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY, cotizacion VARCHAR(30) NOT NULL UNIQUE,
            cliente_id INT DEFAULT NULL, cliente_nombre VARCHAR(180) DEFAULT NULL,
            ticket_ref VARCHAR(30) DEFAULT NULL, cotizacion_odoo VARCHAR(80) DEFAULT NULL,
            origen VARCHAR(30) DEFAULT NULL,
            descripcion VARCHAR(220) DEFAULT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS contenedores_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY, numero VARCHAR(60) NOT NULL UNIQUE,
            serial_unidad VARCHAR(100) DEFAULT NULL, marca_equipo VARCHAR(100) DEFAULT NULL,
            controlador VARCHAR(100) DEFAULT NULL, refrigerante VARCHAR(50) DEFAULT NULL,
            descripcion VARCHAR(220) DEFAULT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        foreach ([
            'modelo_equipo' => 'VARCHAR(100) DEFAULT NULL',
            'anio_fabricacion' => 'SMALLINT UNSIGNED DEFAULT NULL',
            'tamano_contenedor' => 'VARCHAR(60) DEFAULT NULL',
            'modalidad_comercial' => 'VARCHAR(40) DEFAULT NULL',
            'tipo_equipo' => 'VARCHAR(30) DEFAULT NULL',
            'ticket_ref' => 'VARCHAR(30) DEFAULT NULL',
            'cliente_nombre' => 'VARCHAR(180) DEFAULT NULL',
            'origen' => 'VARCHAR(30) DEFAULT NULL',
        ] as $col => $def) {
            try {
                $this->pdo->exec("ALTER TABLE contenedores_catalogo ADD COLUMN `$col` $def");
            } catch (\Throwable $e) {
            }
        }

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS maquinas_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY, serial_unidad VARCHAR(100) NOT NULL UNIQUE,
            marca_equipo VARCHAR(100) DEFAULT NULL, modelo_equipo VARCHAR(100) DEFAULT NULL,
            controlador VARCHAR(100) DEFAULT NULL, anio_fabricacion SMALLINT UNSIGNED DEFAULT NULL,
            refrigerante VARCHAR(50) DEFAULT NULL, descripcion VARCHAR(220) DEFAULT NULL,
            numero_equipo VARCHAR(60) DEFAULT NULL, ticket_ref VARCHAR(30) DEFAULT NULL,
            cliente_nombre VARCHAR(180) DEFAULT NULL, origen VARCHAR(30) DEFAULT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1, creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS odoo_servicios_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY, ticket_ref VARCHAR(30) NOT NULL UNIQUE,
            odoo_ticket_id INT DEFAULT NULL, numero_reporte VARCHAR(30) DEFAULT NULL,
            cotizacion VARCHAR(80) DEFAULT NULL, cliente_id INT DEFAULT NULL,
            cliente_nombre VARCHAR(180) DEFAULT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
            importado_en DATETIME NOT NULL, actualizado_en DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY, codigo VARCHAR(60) DEFAULT NULL,
            detalle VARCHAR(220) NOT NULL, unidad VARCHAR(40) DEFAULT NULL,
            pendiente_revision TINYINT(1) NOT NULL DEFAULT 0, activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->asegurarCatalogoGeneradores();
        $this->asegurarCatalogosV12();
    }

    private function listarClientes(): array
    {
        return $this->pdo->query("
            SELECT id, nombre, COALESCE(ruc,'') ruc, COALESCE(contacto,'') contacto,
                   COALESCE(telefono,'') telefono, COALESCE(correo,'') correo,
                   COALESCE(direccion,'') direccion, COALESCE(origen,'') origen
            FROM clientes_catalogo WHERE activo = 1 ORDER BY nombre
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarCotizaciones(): array
    {
        return $this->pdo->query("
            SELECT c.id, c.cotizacion, c.cliente_id,
                   COALESCE(NULLIF(c.cliente_nombre,''), cl.nombre, '') AS cliente_nombre,
                   COALESCE(c.descripcion, '') AS descripcion,
                   COALESCE(c.ticket_ref,'') AS ticket_ref,
                   COALESCE(c.cotizacion_odoo,'') AS cotizacion_odoo,
                   COALESCE(c.origen,'') AS origen
            FROM cotizaciones_catalogo c
            LEFT JOIN clientes_catalogo cl ON cl.id = c.cliente_id
            WHERE c.activo = 1 ORDER BY c.cotizacion DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarServiciosOdoo(): array
    {
        return $this->pdo->query("
            SELECT id, ticket_ref, COALESCE(numero_reporte,'') AS numero_reporte,
                   COALESCE(cotizacion,'') AS cotizacion, cliente_id,
                   COALESCE(cliente_nombre,'') AS cliente_nombre,
                   COALESCE(ruc,'') AS ruc, COALESCE(contacto,'') AS contacto,
                   COALESCE(telefono,'') AS telefono, COALESCE(correo,'') AS correo,
                   COALESCE(direccion,'') AS direccion, COALESCE(fecha_servicio,'') AS fecha_servicio,
                   COALESCE(modalidad_comercial,'') AS modalidad_comercial,
                   COALESCE(tipo_instalacion,'') AS tipo_instalacion,
                   COALESCE(tipo_equipo,'') AS tipo_equipo,
                   COALESCE(tamano_contenedor,'') AS tamano_contenedor,
                   COALESCE(numero_equipo,'') AS numero_equipo,
                   COALESCE(serie_unidad,'') AS serie_unidad,
                   COALESCE(marca_equipo,'') AS marca_equipo,
                   COALESCE(modelo_equipo,'') AS modelo_equipo,
                   COALESCE(controlador,'') AS controlador,
                   COALESCE(anio_fabricacion,'') AS anio_fabricacion,
                   COALESCE(refrigerante,'') AS refrigerante,
                   COALESCE(titulo_ticket,'') AS titulo_ticket, actualizado_en
            FROM odoo_servicios_catalogo WHERE activo = 1
            ORDER BY COALESCE(fecha_servicio,'1900-01-01') DESC, actualizado_en DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarContenedores(): array
    {
        return $this->pdo->query("
            SELECT id, numero, COALESCE(serial_unidad,'') serial_unidad,
                   COALESCE(marca_equipo,'') marca_equipo, COALESCE(modelo_equipo,'') modelo_equipo,
                   COALESCE(controlador,'') controlador, COALESCE(anio_fabricacion,'') anio_fabricacion,
                   COALESCE(refrigerante,'') refrigerante, COALESCE(tamano_contenedor,'') tamano_contenedor,
                   COALESCE(modalidad_comercial,'') modalidad_comercial, COALESCE(tipo_equipo,'') tipo_equipo,
                   COALESCE(ticket_ref,'') ticket_ref, COALESCE(cliente_nombre,'') cliente_nombre,
                   COALESCE(descripcion,'') descripcion
            FROM contenedores_catalogo WHERE activo = 1
              AND numero NOT IN (SELECT numero FROM generadores_catalogo WHERE activo = 1)
            ORDER BY creado_en DESC, numero ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarMaquinas(): array
    {
        return $this->pdo->query("
            SELECT id, serial_unidad, COALESCE(marca_equipo,'') marca_equipo,
                   COALESCE(modelo_equipo,'') modelo_equipo, COALESCE(controlador,'') controlador,
                   COALESCE(anio_fabricacion,'') anio_fabricacion, COALESCE(refrigerante,'') refrigerante,
                   COALESCE(descripcion,'') descripcion
            FROM maquinas_catalogo WHERE activo = 1
              AND UPPER(COALESCE(marca_equipo,'')) <> 'GENSET'
              AND UPPER(COALESCE(controlador,'')) NOT IN ('SG-3000','SG-5000','ZG-3000','ZG-5000')
              AND serial_unidad NOT IN (SELECT COALESCE(serial_unidad,'') FROM generadores_catalogo WHERE activo = 1)
            ORDER BY creado_en DESC, serial_unidad ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarGeneradores(): array
    {
        return $this->pdo->query("
            SELECT id, numero, COALESCE(serial_unidad,'') serial_unidad,
                   COALESCE(marca_equipo,'THERMO KING') marca_equipo,
                   COALESCE(controlador,'') controlador, COALESCE(descripcion,'') descripcion
            FROM generadores_catalogo WHERE activo = 1 ORDER BY creado_en DESC, numero ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarRepuestosGenset(): array
    {
        return $this->pdo->query("
            SELECT id, controlador, COALESCE(codigo,'') codigo, detalle, COALESCE(unidad,'und') unidad
            FROM repuestos_genset_catalogo WHERE activo = 1 ORDER BY controlador, detalle
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarRepuestosReefer(): array
    {
        return $this->pdo->query("
            SELECT id, marca_equipo, controlador, COALESCE(codigo,'') codigo, detalle,
                   COALESCE(unidad,'und') unidad
            FROM repuestos_reefer_catalogo WHERE activo = 1
            ORDER BY marca_equipo, controlador, detalle
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarModelosReefer(): array
    {
        return $this->pdo->query("
            SELECT id, marca_equipo, controlador FROM modelos_reefer_catalogo
            WHERE activo = 1 AND NOT (UPPER(TRIM(marca_equipo))='THERMO KING'
              AND UPPER(REPLACE(TRIM(controlador),' ',''))='MP400')
            ORDER BY marca_equipo, controlador
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarModelosGenset(): array
    {
        return $this->pdo->query("
            SELECT id, marca_equipo, controlador FROM modelos_genset_catalogo
            WHERE activo = 1 ORDER BY marca_equipo, controlador
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarRepuestosGenerales(): array
    {
        return $this->pdo->query("
            SELECT id, COALESCE(codigo,'') codigo, detalle, COALESCE(unidad,'') unidad,
                   COALESCE(pendiente_revision,0) pendiente_revision
            FROM repuestos_catalogo WHERE activo = 1 ORDER BY creado_en DESC, codigo ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
}
