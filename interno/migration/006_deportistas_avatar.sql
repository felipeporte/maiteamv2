-- 006_deportistas_avatar.sql
-- Agrega soporte para avatar del deportista.

START TRANSACTION;

ALTER TABLE deportistas
    ADD COLUMN avatar_path VARCHAR(255) DEFAULT NULL AFTER rut;

CREATE INDEX idx_deportistas_avatar_path
    ON deportistas (avatar_path);

COMMIT;
