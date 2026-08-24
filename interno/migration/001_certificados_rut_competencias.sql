-- 001_certificados_rut_competencias.sql
-- Migration para bases existentes: RUT, niveles deportivos y competencias.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS niveles_deportivos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_niveles_deportivos_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO niveles_deportivos (id, nombre) VALUES
(1, 'Alta competencia'),
(2, 'Promocionales'),
(3, 'Escuela'),
(4, 'Formativo')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

ALTER TABLE deportistas
    ADD COLUMN rut VARCHAR(20) DEFAULT NULL AFTER categoria,
    ADD COLUMN nivel_id INT UNSIGNED DEFAULT NULL AFTER rut;

ALTER TABLE deportistas
    ADD CONSTRAINT fk_deportistas_niveles
        FOREIGN KEY (nivel_id) REFERENCES niveles_deportivos(id)
        ON DELETE SET NULL ON UPDATE CASCADE;

CREATE UNIQUE INDEX uq_deportistas_rut ON deportistas (rut);
CREATE INDEX idx_deportistas_nivel ON deportistas (nivel_id);

CREATE TABLE IF NOT EXISTS competencias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deportista_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL,
    lugar VARCHAR(160) DEFAULT NULL,
    observaciones VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_competencias_deportistas
        FOREIGN KEY (deportista_id) REFERENCES deportistas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_competencias_deportista ON competencias (deportista_id);
CREATE INDEX idx_competencias_fecha_inicio ON competencias (fecha_inicio);

COMMIT;
