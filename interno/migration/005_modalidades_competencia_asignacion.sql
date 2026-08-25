-- 005_modalidades_competencia_asignacion.sql
-- Extiende la asignacion competitiva para guardar nivel, subnivel y categoria por modalidad.

START TRANSACTION;

ALTER TABLE deportista_modalidades_competencia
    ADD COLUMN nivel VARCHAR(80) DEFAULT NULL AFTER modalidad_competencia_id,
    ADD COLUMN subnivel VARCHAR(80) DEFAULT NULL AFTER nivel,
    ADD COLUMN categoria VARCHAR(80) DEFAULT NULL AFTER subnivel;

CREATE INDEX idx_deportista_modalidades_competencia_nivel
    ON deportista_modalidades_competencia (nivel);
CREATE INDEX idx_deportista_modalidades_competencia_subnivel
    ON deportista_modalidades_competencia (subnivel);

COMMIT;
