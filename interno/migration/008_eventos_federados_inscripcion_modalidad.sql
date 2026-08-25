-- 008_eventos_federados_inscripcion_modalidad.sql
-- Agrega a la inscripcion del evento la asignacion competitiva exacta elegida.

START TRANSACTION;

ALTER TABLE evento_federado_inscripciones
    ADD COLUMN deportista_modalidades_competencia_id INT UNSIGNED DEFAULT NULL AFTER deportista_id,
    ADD COLUMN modalidad_competencia_id INT UNSIGNED DEFAULT NULL AFTER deportista_modalidades_competencia_id,
    ADD COLUMN subnivel VARCHAR(80) DEFAULT NULL AFTER modalidad_competencia_id,
    ADD COLUMN categoria VARCHAR(80) DEFAULT NULL AFTER subnivel;

ALTER TABLE evento_federado_inscripciones
    ADD CONSTRAINT fk_evento_federado_inscripciones_deportista_modalidad
        FOREIGN KEY (deportista_modalidades_competencia_id) REFERENCES deportista_modalidades_competencia(id)
        ON DELETE SET NULL ON UPDATE CASCADE;

CREATE INDEX idx_evento_federado_inscripciones_asignacion
    ON evento_federado_inscripciones (deportista_modalidades_competencia_id);

COMMIT;
