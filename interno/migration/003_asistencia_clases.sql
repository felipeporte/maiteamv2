-- Migracion 003: asistencia en clases
-- Agrega campos para registrar presencia, ausencias y justificativos.

ALTER TABLE clases
    ADD COLUMN asistencia ENUM('pendiente','presente','ausente','justificada') NOT NULL DEFAULT 'pendiente' AFTER estado,
    ADD COLUMN asistencia_notas VARCHAR(255) DEFAULT NULL AFTER asistencia;

CREATE INDEX idx_clases_fecha ON clases (fecha);
