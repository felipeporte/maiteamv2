-- Schema base para Club MaiTeam

CREATE TABLE IF NOT EXISTS apoderados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(40) DEFAULT NULL,
    email VARCHAR(160) DEFAULT NULL,
    direccion VARCHAR(200) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coaches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(40) DEFAULT NULL,
    email VARCHAR(160) DEFAULT NULL,
    especialidad VARCHAR(120) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS niveles_deportivos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_niveles_deportivos_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS deportistas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    apoderado_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    categoria VARCHAR(80) DEFAULT NULL,
    rut VARCHAR(20) DEFAULT NULL,
    nivel_id INT UNSIGNED DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_deportistas_apoderados
        FOREIGN KEY (apoderado_id) REFERENCES apoderados(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_deportistas_niveles
        FOREIGN KEY (nivel_id) REFERENCES niveles_deportivos(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE KEY uq_deportistas_rut (rut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS competencias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nivel_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL,
    lugar VARCHAR(160) DEFAULT NULL,
    observaciones VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_competencias_niveles
        FOREIGN KEY (nivel_id) REFERENCES niveles_deportivos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deportista_id INT UNSIGNED NOT NULL,
    coach_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    duracion_min SMALLINT UNSIGNED DEFAULT NULL,
    tarifa DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado ENUM('programada','realizada','anulada') NOT NULL DEFAULT 'programada',
    asistencia ENUM('pendiente','presente','ausente','justificada') NOT NULL DEFAULT 'pendiente',
    asistencia_notas VARCHAR(255) DEFAULT NULL,
    notas VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_clases_deportistas
        FOREIGN KEY (deportista_id) REFERENCES deportistas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_clases_coaches
        FOREIGN KEY (coach_id) REFERENCES coaches(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    apoderado_id INT UNSIGNED NOT NULL,
    coach_id INT UNSIGNED NOT NULL,
    periodo_inicio DATE DEFAULT NULL,
    periodo_fin DATE DEFAULT NULL,
    fecha_pago DATE NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    metodo VARCHAR(40) DEFAULT NULL,
    referencia VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagos_apoderados
        FOREIGN KEY (apoderado_id) REFERENCES apoderados(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pagos_coaches
        FOREIGN KEY (coach_id) REFERENCES coaches(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transferencias_coaches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coach_id INT UNSIGNED NOT NULL,
    periodo CHAR(7) DEFAULT NULL,
    fecha_transferencia DATE NOT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    metodo VARCHAR(40) DEFAULT NULL,
    referencia VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transferencias_coaches_coach
        FOREIGN KEY (coach_id) REFERENCES coaches(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagos_clases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pago_id INT UNSIGNED NOT NULL,
    clase_id INT UNSIGNED NOT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagos_clases_pago
        FOREIGN KEY (pago_id) REFERENCES pagos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pagos_clases_clase
        FOREIGN KEY (clase_id) REFERENCES clases(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_pago_clase (pago_id, clase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modalidades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    costo_mensual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    coach_id INT UNSIGNED NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_modalidades_coaches
        FOREIGN KEY (coach_id) REFERENCES coaches(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inscripciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deportista_id INT UNSIGNED NOT NULL,
    modalidad_id INT UNSIGNED NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inscripciones_deportistas
        FOREIGN KEY (deportista_id) REFERENCES deportistas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_inscripciones_modalidades
        FOREIGN KEY (modalidad_id) REFERENCES modalidades(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cuotas_socios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    apoderado_id INT UNSIGNED NOT NULL,
    periodo CHAR(7) NOT NULL,
    fecha_pago DATE DEFAULT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 3000.00,
    estado ENUM('pendiente','pagado') NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cuotas_socios_apoderados
        FOREIGN KEY (apoderado_id) REFERENCES apoderados(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_deportistas_apoderado ON deportistas (apoderado_id);
CREATE INDEX idx_deportistas_nivel ON deportistas (nivel_id);
CREATE INDEX idx_competencias_nivel ON competencias (nivel_id);
CREATE INDEX idx_competencias_fecha_inicio ON competencias (fecha_inicio);
CREATE INDEX idx_clases_deportista ON clases (deportista_id);
CREATE INDEX idx_clases_coach ON clases (coach_id);
CREATE INDEX idx_clases_fecha ON clases (fecha);
CREATE INDEX idx_pagos_apoderado ON pagos (apoderado_id);
CREATE INDEX idx_pagos_coach ON pagos (coach_id);
CREATE INDEX idx_transferencias_coach ON transferencias_coaches (coach_id);
CREATE INDEX idx_transferencias_fecha ON transferencias_coaches (fecha_transferencia);
CREATE INDEX idx_modalidades_coach ON modalidades (coach_id);
CREATE INDEX idx_inscripciones_deportista ON inscripciones (deportista_id);
CREATE INDEX idx_inscripciones_modalidad ON inscripciones (modalidad_id);
CREATE INDEX idx_cuotas_apoderado ON cuotas_socios (apoderado_id);

-- Migracion sugerida (BD existente): soporte certificados por RUT y competencias
-- Ejecutar en este orden en una base ya creada:
-- 1) Niveles deportivos
-- CREATE TABLE IF NOT EXISTS niveles_deportivos (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     nombre VARCHAR(80) NOT NULL,
--     created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
--     UNIQUE KEY uq_niveles_deportivos_nombre (nombre)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- INSERT INTO niveles_deportivos (id, nombre) VALUES
-- (1, 'Alta competencia'),
-- (2, 'Promocionales'),
-- (3, 'Escuela'),
-- (4, 'Formativo')
-- ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
-- 2) Nuevos campos en deportistas
-- ALTER TABLE deportistas
--     ADD COLUMN rut VARCHAR(20) NULL AFTER categoria,
--     ADD COLUMN nivel_id INT UNSIGNED NULL AFTER rut;
-- ALTER TABLE deportistas
--     ADD CONSTRAINT fk_deportistas_niveles
--         FOREIGN KEY (nivel_id) REFERENCES niveles_deportivos(id)
--         ON DELETE SET NULL ON UPDATE CASCADE;
-- CREATE UNIQUE INDEX uq_deportistas_rut ON deportistas (rut);
-- CREATE INDEX idx_deportistas_nivel ON deportistas (nivel_id);
-- 3) Tabla competencias
-- CREATE TABLE IF NOT EXISTS competencias (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     nivel_id INT UNSIGNED NOT NULL,
--     nombre VARCHAR(160) NOT NULL,
--     fecha_inicio DATE NOT NULL,
--     fecha_fin DATE DEFAULT NULL,
--     lugar VARCHAR(160) DEFAULT NULL,
--     observaciones VARCHAR(255) DEFAULT NULL,
--     created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
--     CONSTRAINT fk_competencias_niveles
--         FOREIGN KEY (nivel_id) REFERENCES niveles_deportivos(id)
--         ON DELETE RESTRICT ON UPDATE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- CREATE INDEX idx_competencias_nivel ON competencias (nivel_id);
-- CREATE INDEX idx_competencias_fecha_inicio ON competencias (fecha_inicio);

-- Migracion sugerida (BD existente): asignar coach_id a modalidades
-- Usar coach_id = 1 como valor por defecto.
-- Ejecutar solo en bases de datos ya creadas (no necesario en instalaciones nuevas).
-- 1) Agregar columna como NULL temporalmente
-- ALTER TABLE modalidades
--     ADD COLUMN coach_id INT UNSIGNED NULL AFTER costo_mensual;
-- 2) Asignar coach_id por defecto
-- UPDATE modalidades SET coach_id = 1 WHERE coach_id IS NULL;
-- 3) Volverla obligatoria
-- ALTER TABLE modalidades
--     MODIFY coach_id INT UNSIGNED NOT NULL;
-- 4) Agregar FK e indice
-- ALTER TABLE modalidades
--     ADD CONSTRAINT fk_modalidades_coaches
--         FOREIGN KEY (coach_id) REFERENCES coaches(id)
--         ON DELETE RESTRICT ON UPDATE CASCADE;
-- CREATE INDEX idx_modalidades_coach ON modalidades (coach_id);


INSERT INTO `niveles_deportivos` (`id`, `nombre`) VALUES
(1, 'Alta competencia'),
(2, 'Promocionales'),
(3, 'Escuela'),
(4, 'Formativo')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- SEED de datos
INSERT INTO `apoderados` (`id`, `nombre`, `telefono`, `email`, `direccion`, `created_at`, `updated_at`) VALUES
(1, 'Mailen Flores', '1111111', 'mailen@mailen', 'pfjkñaslfkasdl', '2026-01-30 21:46:20', NULL),
(2, 'Maria Jose Bustos', '', '', '', '2026-02-09 15:37:26', NULL),
(3, 'Benjamin Bravo', '', '', '', '2026-02-09 15:37:42', NULL),
(4, 'Cristina Nuñez', '', '', '', '2026-02-09 15:37:54', NULL),
(5, 'Claudia Barraza', '', '', '', '2026-02-09 15:38:30', NULL),
(6, 'Alejandra Alvarez', '', '', '', '2026-02-09 15:38:42', NULL),
(7, 'Thamara Albornoz', '', '', '', '2026-02-09 15:50:03', NULL),
(8, 'Maria Teresa Narbona', '', '', '', '2026-02-09 16:06:33', NULL),
(9, 'Erika Cuadra', '', '', '', '2026-02-09 16:06:56', NULL),
(10, 'Camila Soto', '', '', '', '2026-02-09 16:07:13', NULL),
(11, 'Paulina Infante', '', '', '', '2026-02-09 16:10:23', NULL),
(12, 'Karen Sepulveda', '', '', '', '2026-02-09 16:10:37', NULL),
(14, 'Johana Astorga', '', '', '', '2026-02-09 16:11:08', NULL),
(15, 'Elizabeth Vasquez', '', '', '', '2026-02-09 16:12:20', NULL),
(16, 'Maritza Jimenez', '', '', '', '2026-02-09 16:17:24', NULL),
(18, 'Claudia Roa', '', '', '', '2026-02-09 16:17:48', NULL),
(19, 'Paulina Gallardo', '', '', '', '2026-02-09 16:18:11', NULL),
(20, 'Elizabeth Velasco', '', '', '', '2026-02-09 16:19:02', NULL),
(21, 'Arlette Bonnefoy', '', '', '', '2026-02-09 16:19:22', NULL),
(22, 'Maribel Pérez', '', '', '', '2026-02-09 16:19:38', NULL),
(23, 'Lisbet Benavides', '', '', '', '2026-02-09 17:35:51', NULL),
(24, 'Rodrigo Carvajal', '', '', '', '2026-02-09 17:36:16', NULL),
(25, 'Patricio Barria', '', '', '', '2026-02-09 17:36:30', NULL),
(26, 'Susana Caceres', '', '', '', '2026-02-09 17:36:46', NULL),
(27, 'Maricella Guerra', '', '', '', '2026-02-09 17:37:07', NULL),
(28, 'Camila Norambuena', '', '', '', '2026-02-09 17:37:32', NULL),
(29, 'Javiera Norambuena', '', '', '', '2026-02-09 17:40:22', NULL),
(30, 'Jocelyn Retamal', '', '', '', '2026-02-09 17:40:37', NULL),
(31, 'Andres Espinoza', '', '', '', '2026-02-09 17:40:53', NULL),
(32, 'Erika Raza', '', '', '', '2026-02-09 17:41:34', NULL),
(33, 'Loreto Muñoz', '', '', '', '2026-02-09 17:41:48', NULL),
(34, 'Camila Gaete', '', '', '', '2026-02-09 17:41:56', NULL),
(35, 'Carolina Dinamarca', '', '', '', '2026-02-09 17:42:10', NULL),
(36, 'Marisol Albornoz', '', '', '', '2026-02-09 17:42:19', NULL);

--
-- Volcado de datos para la tabla `coaches`
--

INSERT INTO `coaches` (`id`, `nombre`, `telefono`, `email`, `especialidad`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Maira Flores', '11111', 'asdsadsa@saas.cl', 'Freeskating', 1, '2026-01-31 21:58:21', NULL),
(2, 'Maite Flores', '', '', 'Danza', 1, '2026-02-09 23:07:58', NULL);

--
-- Volcado de datos para la tabla `deportistas`
--

INSERT INTO `deportistas` (`id`, `apoderado_id`, `nombre`, `fecha_nacimiento`, `categoria`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mailen Flores', '2003-08-14', '', 1, '2026-02-02 16:45:07', NULL),
(2, 2, 'Maria Ignacia Fernandez', '2008-07-07', '', 1, '2026-02-09 23:01:31', NULL),
(3, 2, 'Maria Francisca Fernández', '2005-10-19', '', 1, '2026-02-10 16:28:49', NULL),
(4, 2, 'Maria Trinidad Fernandez Bustos', '2016-07-22', '', 1, '2026-02-10 16:54:46', NULL),
(5, 3, 'Benjamin Bravo', '2004-03-08', '', 1, '2026-02-10 16:55:24', NULL),
(6, 4, 'Fernanda Almarza', '2012-09-15', '', 1, '2026-02-10 17:14:35', NULL),
(7, 5, 'Josefa Campos', '2009-02-04', '', 1, '2026-02-10 21:13:44', NULL),
(8, 7, 'Thamara Albornoz', '2005-02-23', '', 1, '2026-02-10 21:18:10', NULL),
(9, 8, 'Pascuala Valdivia', '2006-04-25', '', 1, '2026-02-10 21:19:02', NULL),
(10, 9, 'Renata Meyer', '2006-01-18', '', 1, '2026-02-10 21:19:46', NULL),
(11, 10, 'Camila Soto', '1995-03-21', '', 1, '2026-02-10 21:20:19', NULL),
(12, 11, 'Agustina Quintana', '2018-07-05', '', 1, '2026-02-10 21:21:05', NULL),
(13, 11, 'Catalina Quintana', '2018-07-05', '', 1, '2026-02-10 21:21:35', NULL),
(14, 11, 'Rafaella Quintana', '2016-08-13', '', 1, '2026-02-10 21:22:08', NULL),
(15, 12, 'Fernanda López', '2016-01-04', '', 1, '2026-02-10 21:23:08', NULL),
(16, 14, 'Loreto Guerrero', '2012-07-15', '', 1, '2026-02-10 21:24:03', NULL),
(17, 15, 'Antonella Flores', '2014-12-19', '', 1, '2026-02-10 21:25:35', NULL),
(18, 16, 'Alondra Garrido', '2007-08-06', '', 1, '2026-02-10 21:26:16', NULL),
(19, 18, 'Amelia Rosales', '2016-01-02', '', 1, '2026-02-10 21:26:46', NULL),
(20, 19, 'Antonella Ramirez', '2016-09-29', '', 1, '2026-02-10 21:27:38', NULL),
(21, 20, 'Belen Valenzuela', '2012-04-05', '', 1, '2026-02-10 21:28:23', NULL),
(22, 21, 'Dominga Bollman', '2017-06-07', '', 1, '2026-02-10 21:29:12', NULL),
(23, 22, 'Fabiana Arrieta', '2018-01-17', '', 1, '2026-02-10 21:30:18', NULL),
(24, 23, 'Jade Guajardo', '2013-06-12', '', 1, '2026-02-10 21:31:01', NULL),
(25, 24, 'Magdalena Carvajal', '2012-06-03', '', 1, '2026-02-10 21:33:20', NULL),
(26, 25, 'Maria Jose Barria', '2013-06-21', '', 1, '2026-02-10 21:33:51', NULL),
(27, 26, 'Martina Gonzalez', '2012-07-26', '', 1, '2026-02-10 21:34:31', NULL),
(28, 27, 'Michelle Morel', '2012-08-17', '', 1, '2026-02-10 21:36:22', NULL),
(29, 28, 'Sofia Toloza', '2012-09-14', '', 1, '2026-02-10 21:37:00', NULL),
(30, 29, 'Agustina Ponce', '2018-06-12', '', 1, '2026-02-10 21:37:31', NULL),
(31, 30, 'Emily Rose', '2014-06-20', '', 1, '2026-02-10 21:43:41', NULL),
(32, 31, 'Agustina Espinoza', '2011-04-13', '', 1, '2026-02-10 21:44:50', NULL),
(33, 32, 'Erika Endera', '2019-01-30', '', 1, '2026-02-10 21:46:23', NULL),
(34, 33, 'Antonia Burdiles', '2014-04-16', '', 1, '2026-02-10 21:47:39', NULL),
(35, 34, 'Antonietta Gaete', '2015-10-28', '', 1, '2026-02-10 21:48:15', NULL),
(36, 35, 'Emilia Romero', '2014-01-28', '', 1, '2026-02-10 21:48:39', NULL),
(37, 36, 'Karla Corral', '2018-09-10', '', 1, '2026-02-10 21:49:14', NULL);

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `deportista_id`, `modalidad_id`, `fecha_inicio`, `fecha_fin`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-02-01', '2026-12-31', 1, '2026-02-02 16:45:42', NULL),
(2, 2, 7, '2026-03-01', NULL, 1, '2026-02-10 16:00:56', '2026-02-10 16:01:53');

--
-- Volcado de datos para la tabla `modalidades`
--

INSERT INTO `modalidades` (`id`, `nombre`, `costo_mensual`, `coach_id`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'FreeSkating - x3', 80000.00, 1, 1, '2026-02-02 16:43:44', NULL),
(2, 'Principiante - x3', 60000.00, 1, 1, '2026-02-09 23:05:56', '2026-02-09 23:11:11'),
(3, 'FreeSkating - x4', 100000.00, 1, 1, '2026-02-09 23:06:57', '2026-02-09 23:11:01'),
(4, 'FreeSkating - x5', 120000.00, 1, 1, '2026-02-09 23:08:55', NULL),
(5, 'Flex - x1', 25000.00, 2, 1, '2026-02-09 23:09:52', '2026-02-09 23:10:47'),
(6, 'FreeSkating Zoom - x3', 55000.00, 1, 1, '2026-02-10 15:56:57', '2026-02-10 15:57:09'),
(7, 'Danza', 80000.00, 2, 1, '2026-02-10 15:57:44', NULL),
(8, 'Danza - x1', 25000.00, 2, 1, '2026-02-10 15:58:19', NULL),
(9, 'FreeSkating - x1', 25000.00, 1, 1, '2026-02-10 15:58:42', NULL),
(10, 'Danza Zoom', 0.00, 2, 1, '2026-02-10 15:59:10', NULL),
(11, 'Transicion - Maira', 20000.00, 1, 1, '2026-02-10 21:51:45', NULL),
(12, 'Transicion - Maite', 50000.00, 2, 1, '2026-02-10 21:52:00', NULL);
