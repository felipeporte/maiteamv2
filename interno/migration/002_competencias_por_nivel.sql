-- 002_competencias_por_nivel.sql
-- Ajuste: competencias por nivel en vez de por deportista.

START TRANSACTION;

INSERT INTO niveles_deportivos (id, nombre) VALUES
(1, 'Alta competencia'),
(2, 'Promocionales'),
(3, 'Escuela'),
(4, 'Formativo')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

ALTER TABLE competencias
    ADD COLUMN nivel_id INT UNSIGNED DEFAULT NULL AFTER id;

UPDATE competencias c
INNER JOIN deportistas d ON d.id = c.deportista_id
SET c.nivel_id = COALESCE(d.nivel_id, 4)
WHERE c.nivel_id IS NULL;

UPDATE competencias
SET nivel_id = 4
WHERE nivel_id IS NULL;

ALTER TABLE competencias
    ADD CONSTRAINT fk_competencias_niveles
        FOREIGN KEY (nivel_id) REFERENCES niveles_deportivos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE INDEX idx_competencias_nivel ON competencias (nivel_id);

ALTER TABLE competencias
    DROP FOREIGN KEY fk_competencias_deportistas;

DROP INDEX idx_competencias_deportista ON competencias;

ALTER TABLE competencias
    MODIFY nivel_id INT UNSIGNED NOT NULL,
    DROP COLUMN deportista_id;

COMMIT;
