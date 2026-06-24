-- ============================================================
-- ZGROUP Informes Técnicos — Esquema MySQL consolidado
-- Base de datos: zgroupin_zgroupinformes (InnoDB, utf8mb4)
-- ============================================================
-- Nota: las tablas informes y tecnicos son legacy y se crean
-- automáticamente en instalaciones nuevas vía migraciones del panel.
-- Este script documenta el esquema completo esperado por el sistema.

-- ------------------------------------------------------------
-- Tablas principales
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS informes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tecnico_id INT NOT NULL,
    orden VARCHAR(100) NOT NULL COMMENT 'N° de reporte / ticket',
    odoo_ticket_ref VARCHAR(120) DEFAULT NULL,
    cliente VARCHAR(150) DEFAULT NULL,
    direccion TEXT DEFAULT NULL,
    fecha DATE DEFAULT NULL,
    trabajos VARCHAR(255) DEFAULT NULL,
    archivo VARCHAR(255) DEFAULT NULL COMMENT 'Nombre del PDF en /informes',
    creado_en DATETIME NOT NULL,
    preinspeccion_id INT DEFAULT NULL,
    datos_json LONGTEXT DEFAULT NULL COMMENT 'Snapshot completo del formulario',
    repuestos_manual LONGTEXT DEFAULT NULL,
    tipo_equipo VARCHAR(30) DEFAULT NULL COMMENT 'Reefer / Genset',
    tamano_contenedor VARCHAR(60) DEFAULT NULL,
    actualizado_en DATETIME DEFAULT NULL,
    hora_inicio_servicio DATETIME DEFAULT NULL,
    hora_fin_servicio DATETIME DEFAULT NULL,
    odoo_estado VARCHAR(40) NOT NULL DEFAULT 'pendiente',
    odoo_ticket_id BIGINT DEFAULT NULL,
    odoo_attachment_id BIGINT DEFAULT NULL,
    odoo_nombre_adjunto VARCHAR(255) DEFAULT NULL,
    odoo_error TEXT DEFAULT NULL,
    odoo_intentos INT NOT NULL DEFAULT 0,
    odoo_ultimo_intento_en DATETIME DEFAULT NULL,
    odoo_sincronizado_en DATETIME DEFAULT NULL,
    INDEX idx_informes_tecnico (tecnico_id),
    INDEX idx_informes_orden (orden),
    INDEX idx_informes_preinspeccion (preinspeccion_id),
    INDEX idx_informes_odoo_estado (odoo_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inspecciones_preliminares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tecnico_id INT NOT NULL,
    cliente VARCHAR(150) DEFAULT NULL,
    cotizacion VARCHAR(100) DEFAULT NULL,
    trabajo VARCHAR(150) DEFAULT NULL,
    modalidad_comercial VARCHAR(40) DEFAULT NULL,
    tipo_instalacion VARCHAR(80) DEFAULT NULL,
    tipo_equipo VARCHAR(30) DEFAULT NULL,
    tamano_contenedor VARCHAR(60) DEFAULT NULL,
    numero_equipo VARCHAR(100) DEFAULT NULL,
    serie_unidad VARCHAR(100) DEFAULT NULL,
    marca_equipo VARCHAR(100) DEFAULT NULL,
    controlador VARCHAR(100) DEFAULT NULL,
    refrigerante VARCHAR(50) DEFAULT NULL,
    set_point DECIMAL(6,2) DEFAULT NULL,
    temperatura_ambiente DECIMAL(6,2) DEFAULT NULL,
    retorno_aire DECIMAL(6,2) DEFAULT NULL,
    suministro_aire DECIMAL(6,2) DEFAULT NULL,
    presion_alta VARCHAR(50) DEFAULT NULL,
    presion_baja VARCHAR(50) DEFAULT NULL,
    alarma_encontrada VARCHAR(180) DEFAULT NULL,
    genset_horometro_inicial DECIMAL(12,1) DEFAULT NULL,
    genset_voltaje_bateria_inicial VARCHAR(50) DEFAULT NULL,
    genset_nivel_combustible_inicial VARCHAR(40) DEFAULT NULL,
    genset_nivel_aceite_inicial VARCHAR(50) DEFAULT NULL,
    genset_refrigerante_motor_inicial VARCHAR(60) DEFAULT NULL,
    genset_arranque_inicial VARCHAR(80) DEFAULT NULL,
    genset_frecuencia_inicial DECIMAL(8,2) DEFAULT NULL,
    genset_presion_aceite_inicial VARCHAR(50) DEFAULT NULL,
    voltaje_l1_l2 VARCHAR(50) DEFAULT NULL,
    voltaje_l2_l3 VARCHAR(50) DEFAULT NULL,
    voltaje_l1_l3 VARCHAR(50) DEFAULT NULL,
    estado_inicial VARCHAR(150) DEFAULT NULL,
    observacion_inicial TEXT DEFAULT NULL,
    ubicacion_texto TEXT DEFAULT NULL,
    latitud VARCHAR(50) DEFAULT NULL,
    longitud VARCHAR(50) DEFAULT NULL,
    creado_en DATETIME NOT NULL,
    hora_inicio_servicio DATETIME DEFAULT NULL,
    hora_fin_servicio DATETIME DEFAULT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'abierto',
    token_continuacion VARCHAR(120) DEFAULT NULL,
    informe_id INT DEFAULT NULL,
    finalizado_en DATETIME DEFAULT NULL,
    odoo_ticket_ref VARCHAR(120) DEFAULT NULL,
    INDEX idx_preliminares_tecnico (tecnico_id),
    INDEX idx_preliminares_token (token_continuacion),
    INDEX idx_preliminares_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS borradores_servicio (
    preinspeccion_id INT NOT NULL PRIMARY KEY,
    token_continuacion VARCHAR(120) DEFAULT NULL,
    datos_json LONGTEXT NOT NULL,
    actualizado_en DATETIME NOT NULL,
    INDEX idx_borrador_token (token_continuacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Catálogos
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS trabajos_realizados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(90) NOT NULL UNIQUE,
    nombre VARCHAR(180) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clientes_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(180) NOT NULL,
    ruc VARCHAR(30) DEFAULT NULL,
    contacto VARCHAR(160) DEFAULT NULL,
    telefono VARCHAR(80) DEFAULT NULL,
    correo VARCHAR(180) DEFAULT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    origen VARCHAR(30) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_clientes_catalogo_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cotizaciones_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cotizacion VARCHAR(30) NOT NULL UNIQUE,
    cliente_id INT DEFAULT NULL,
    cliente_nombre VARCHAR(180) DEFAULT NULL,
    ticket_ref VARCHAR(30) DEFAULT NULL,
    cotizacion_odoo VARCHAR(80) DEFAULT NULL,
    origen VARCHAR(30) DEFAULT NULL,
    descripcion VARCHAR(220) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cotizaciones_catalogo_cotizacion (cotizacion),
    INDEX idx_cotizaciones_catalogo_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contenedores_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(60) NOT NULL UNIQUE,
    serial_unidad VARCHAR(100) DEFAULT NULL,
    marca_equipo VARCHAR(100) DEFAULT NULL,
    controlador VARCHAR(100) DEFAULT NULL,
    refrigerante VARCHAR(50) DEFAULT NULL,
    modelo_equipo VARCHAR(100) DEFAULT NULL,
    anio_fabricacion SMALLINT UNSIGNED DEFAULT NULL,
    tamano_contenedor VARCHAR(60) DEFAULT NULL,
    modalidad_comercial VARCHAR(40) DEFAULT NULL,
    tipo_equipo VARCHAR(30) DEFAULT NULL,
    ticket_ref VARCHAR(30) DEFAULT NULL,
    cliente_nombre VARCHAR(180) DEFAULT NULL,
    origen VARCHAR(30) DEFAULT NULL,
    descripcion VARCHAR(220) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contenedores_catalogo_numero (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS maquinas_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_unidad VARCHAR(100) NOT NULL UNIQUE,
    marca_equipo VARCHAR(100) DEFAULT NULL,
    modelo_equipo VARCHAR(100) DEFAULT NULL,
    controlador VARCHAR(100) DEFAULT NULL,
    anio_fabricacion SMALLINT UNSIGNED DEFAULT NULL,
    refrigerante VARCHAR(50) DEFAULT NULL,
    descripcion VARCHAR(220) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_maquinas_catalogo_serial (serial_unidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS generadores_catalogo (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS modelos_reefer_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca_equipo VARCHAR(100) NOT NULL,
    controlador VARCHAR(100) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_reefer_marca_ctrl (marca_equipo, controlador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS modelos_genset_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca_equipo VARCHAR(100) NOT NULL,
    controlador VARCHAR(40) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_genset_marca_ctrl (marca_equipo, controlador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS repuestos_reefer_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca_equipo VARCHAR(100) NOT NULL,
    controlador VARCHAR(100) NOT NULL,
    codigo VARCHAR(60) DEFAULT NULL,
    detalle VARCHAR(220) NOT NULL,
    unidad VARCHAR(40) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reefer_rep_controlador (controlador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS repuestos_genset_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    controlador VARCHAR(40) NOT NULL,
    codigo VARCHAR(60) DEFAULT NULL,
    detalle VARCHAR(220) NOT NULL,
    unidad VARCHAR(40) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_genset_rep_controlador (controlador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS repuestos_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(60) DEFAULT NULL,
    detalle VARCHAR(220) NOT NULL,
    unidad VARCHAR(40) DEFAULT NULL,
    pendiente_revision TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_repuestos_catalogo_codigo (codigo),
    INDEX idx_repuestos_catalogo_detalle (detalle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS odoo_servicios_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_ref VARCHAR(30) NOT NULL UNIQUE,
    odoo_ticket_id INT DEFAULT NULL,
    numero_reporte VARCHAR(30) DEFAULT NULL,
    cotizacion VARCHAR(80) DEFAULT NULL,
    cliente_id INT DEFAULT NULL,
    cliente_nombre VARCHAR(180) DEFAULT NULL,
    ruc VARCHAR(30) DEFAULT NULL,
    contacto VARCHAR(160) DEFAULT NULL,
    telefono VARCHAR(80) DEFAULT NULL,
    correo VARCHAR(180) DEFAULT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    fecha_servicio DATE DEFAULT NULL,
    equipo_soporte VARCHAR(120) DEFAULT NULL,
    asignado_a VARCHAR(160) DEFAULT NULL,
    tipo_servicio VARCHAR(160) DEFAULT NULL,
    modalidad_comercial VARCHAR(40) DEFAULT NULL,
    tipo_instalacion VARCHAR(80) DEFAULT NULL,
    tipo_equipo VARCHAR(30) DEFAULT NULL,
    tamano_contenedor VARCHAR(60) DEFAULT NULL,
    numero_equipo VARCHAR(60) DEFAULT NULL,
    serie_unidad VARCHAR(100) DEFAULT NULL,
    marca_equipo VARCHAR(100) DEFAULT NULL,
    modelo_equipo VARCHAR(100) DEFAULT NULL,
    controlador VARCHAR(100) DEFAULT NULL,
    anio_fabricacion SMALLINT UNSIGNED DEFAULT NULL,
    refrigerante VARCHAR(50) DEFAULT NULL,
    titulo_ticket VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    importado_en DATETIME NOT NULL,
    actualizado_en DATETIME NOT NULL,
    INDEX idx_odoo_servicio_cliente (cliente_id),
    INDEX idx_odoo_servicio_reporte (numero_reporte),
    INDEX idx_odoo_servicio_equipo (numero_equipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Panel y configuración
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS panel_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(60) NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    detalle TEXT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zgroup_config (
    clave VARCHAR(100) PRIMARY KEY,
    valor VARCHAR(220) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
