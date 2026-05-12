-- ══════════════════════════════════════
--  install.sql — AgroGestor GT
--
--  INSTRUCCIONES:
--  1. Abre phpMyAdmin en tu panel de 3GoogieHost
--  2. Selecciona tu base de datos
--  3. Ve a la pestaña "SQL"
--  4. Pega todo este contenido y haz clic en "Ejecutar"
-- ══════════════════════════════════════

SET NAMES utf8mb4;
SET time_zone = '-06:00'; -- Zona horaria Guatemala (UTC-6)

-- ── Tabla: colonias ──────────────────
CREATE TABLE IF NOT EXISTS `colonias` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(150) NOT NULL,
  `cp`          VARCHAR(10)  NOT NULL,
  `municipio`   VARCHAR(100) NOT NULL,
  `estado`      VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `creado_en`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: clientes ──────────────────
CREATE TABLE IF NOT EXISTS `clientes` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(100) NOT NULL,
  `apellido`    VARCHAR(100) NOT NULL,
  `telefono`    VARCHAR(8)   NOT NULL COMMENT 'Guatemala: 8 dígitos',
  `email`       VARCHAR(150) DEFAULT NULL,
  `calle`       VARCHAR(200) NOT NULL,
  `referencia`  VARCHAR(200) DEFAULT NULL,
  `colonia_id`  INT UNSIGNED NOT NULL,
  `estatus`     ENUM('activo','inactivo','pendiente') NOT NULL DEFAULT 'activo',
  `creado_en`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_colonia` (`colonia_id`),
  KEY `idx_estatus` (`estatus`),
  CONSTRAINT `fk_colonia`
    FOREIGN KEY (`colonia_id`) REFERENCES `colonias` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Colonias de ejemplo (Guatemala) ──
INSERT INTO `colonias` (`nombre`, `cp`, `municipio`, `estado`) VALUES
('Zona 1 - Centro Histórico',     '01001', 'Guatemala City',   'Guatemala'),
('Zona 2 - Hipódromo del Norte',  '01002', 'Guatemala City',   'Guatemala'),
('Zona 4 - Centro Cívico',        '01004', 'Guatemala City',   'Guatemala'),
('Zona 6 - Martínez',             '01006', 'Guatemala City',   'Guatemala'),
('Zona 7 - Kaminal Juyú',         '01007', 'Guatemala City',   'Guatemala'),
('Zona 10 - Oakland',             '01010', 'Guatemala City',   'Guatemala'),
('Zona 11 - Roosevelt',           '01011', 'Guatemala City',   'Guatemala'),
('Zona 12 - Col. Bethania',       '01012', 'Guatemala City',   'Guatemala'),
('Zona 14 - Vista Hermosa',       '01014', 'Guatemala City',   'Guatemala'),
('Zona 16 - Acatan',              '01016', 'Guatemala City',   'Guatemala'),
('Mixco - Col. El Naranjo',       '01057', 'Mixco',            'Guatemala'),
('Mixco - Monte Real',            '01057', 'Mixco',            'Guatemala'),
('Villa Nueva - Col. Bárcenas',   '01064', 'Villa Nueva',      'Guatemala'),
('San Miguel Petapa',             '01068', 'San Miguel Petapa','Guatemala'),
('Antigua Guatemala - Zona 1',    '03001', 'Antigua Guatemala','Sacatepéquez'),
('Quetzaltenango - Zona 1',       '09001', 'Quetzaltenango',   'Quetzaltenango'),
('Escuintla - Col. Las Flores',   '05001', 'Escuintla',        'Escuintla'),
('Cobán - Col. San Pedro',        '16001', 'Cobán',            'Alta Verapaz');
