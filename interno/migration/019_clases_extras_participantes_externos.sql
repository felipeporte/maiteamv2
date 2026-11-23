START TRANSACTION;

ALTER TABLE clases_extras
    ADD COLUMN participantes_externos INT UNSIGNED NOT NULL DEFAULT 0 AFTER costo_pista;

COMMIT;
