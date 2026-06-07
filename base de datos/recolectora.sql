-- ============================================================
--  Recolectora S.A. — Base de datos unificada
--  Combina esquemas de dev1Wendy + dev2Cesar + dev3Herielis
-- ============================================================

CREATE DATABASE IF NOT EXISTS recolectora
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE recolectora;

-- -------------------------------------------------------
--  Tabla: usuarios
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario  INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,   -- bcrypt hash
    rol         ENUM('ADMIN','COBRADOR','OPERADOR') NOT NULL DEFAULT 'COBRADOR',
    activo      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admin de prueba  (password: admin1234)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@recolectora.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN');

-- -------------------------------------------------------
--  Tabla: colonias
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS colonias (
    id_colonia      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL UNIQUE,
    descripcion     VARCHAR(255) DEFAULT NULL,
    tarifa_mensual  DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK (tarifa_mensual >= 0),
    activo          TINYINT(1)   DEFAULT 1,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO colonias (nombre, descripcion, tarifa_mensual) VALUES
('Colonia Centro', 'Zona central', 150.00),
('Colonia Norte',  'Zona norte',   130.00),
('Colonia Sur',    'Zona sur',     140.00);

-- -------------------------------------------------------
--  Tabla: camiones
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS camiones (
    id_camion    INT AUTO_INCREMENT PRIMARY KEY,
    numero_placa VARCHAR(20)  NOT NULL UNIQUE,
    marca        VARCHAR(100) NOT NULL,
    modelo       VARCHAR(100) NOT NULL,
    anio         YEAR(4)      NOT NULL,
    capacidad_kg DECIMAL(10,2) DEFAULT NULL,
    estado       ENUM('ACTIVO','INACTIVO','MANTENIMIENTO') DEFAULT 'ACTIVO',
    id_colonia   INT NOT NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_camion_colonia FOREIGN KEY (id_colonia) REFERENCES colonias(id_colonia)
) ENGINE=InnoDB;

INSERT INTO camiones (numero_placa, marca, modelo, anio, capacidad_kg, id_colonia) VALUES
('ABC-123', 'Kenworth',      'T370',  2020, 8000.00, 1),
('DEF-456', 'Kenworth',      'T370',  2019, 8000.00, 1),
('GHI-789', 'International', '4300',  2021, 7000.00, 2),
('JKL-012', 'Freightliner',  'M2',    2018, 6500.00, 3);

-- -------------------------------------------------------
--  Tabla: choferes
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS choferes (
    id_chofer  INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    licencia   VARCHAR(30),
    activo     TINYINT(1)   DEFAULT 1,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO choferes (nombre, licencia) VALUES
('Carlos Pérez',  'A-001234'),
('Miguel Rodas',  'A-002345'),
('José García',   'A-003456'),
('Luis Ajú',      'B-004567');

-- -------------------------------------------------------
--  Tabla: combustible
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS combustible (
    id_combustible   INT AUTO_INCREMENT PRIMARY KEY,
    id_camion        INT           NOT NULL,
    id_usuario       INT           NOT NULL,
    fecha            DATE          NOT NULL,
    litros           DECIMAL(10,2) NOT NULL CHECK (litros > 0),
    kilometraje      INT           NOT NULL DEFAULT 0,
    km_recorridos    DECIMAL(10,2) DEFAULT 0,
    precio_litro     DECIMAL(10,2) NOT NULL CHECK (precio_litro > 0),
    costo_total      DECIMAL(10,2) NOT NULL,
    rendimiento      DECIMAL(10,2) GENERATED ALWAYS AS (
                         CASE WHEN litros > 0 THEN km_recorridos / litros ELSE 0 END
                     ) STORED,
    tipo_combustible ENUM('DIESEL','GASOLINA') DEFAULT 'DIESEL',
    estacion         VARCHAR(120)  DEFAULT NULL,
    alerta           TINYINT(1)    NOT NULL DEFAULT 0,
    observaciones    VARCHAR(255)  DEFAULT NULL,
    created_at       DATETIME      DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_combustible_camion  FOREIGN KEY (id_camion)  REFERENCES camiones(id_camion)  ON DELETE CASCADE,
    CONSTRAINT fk_combustible_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

CREATE INDEX idx_combustible_fecha  ON combustible(fecha);
CREATE INDEX idx_combustible_camion ON combustible(id_camion);
CREATE INDEX idx_combustible_alerta ON combustible(alerta);

-- -------------------------------------------------------
--  Tabla: clientes
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    apellido   VARCHAR(100) NOT NULL,
    telefono   VARCHAR(20)  DEFAULT NULL,
    direccion  VARCHAR(255) DEFAULT NULL,
    id_colonia INT NOT NULL,
    activo     TINYINT(1)   DEFAULT 1,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cliente_colonia FOREIGN KEY (id_colonia) REFERENCES colonias(id_colonia) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
--  Tabla: pagos
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS pagos (
    id_pago      INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente   INT NOT NULL,
    id_usuario   INT NOT NULL,
    monto        DECIMAL(10,2) NOT NULL CHECK (monto > 0),
    fecha_pago   DATE NOT NULL,
    anio         SMALLINT(6) NOT NULL,
    mes          TINYINT(4)  NOT NULL CHECK (mes BETWEEN 1 AND 12),
    metodo_pago  ENUM('EFECTIVO','TRANSFERENCIA','TARJETA') DEFAULT 'EFECTIVO',
    observaciones VARCHAR(255) DEFAULT NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pago_cliente_mes (id_cliente, anio, mes),
    CONSTRAINT fk_pago_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_pago_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;
