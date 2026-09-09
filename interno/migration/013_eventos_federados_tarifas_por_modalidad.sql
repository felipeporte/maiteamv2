-- 013_eventos_federados_tarifas_por_modalidad.sql
-- Guarda las tarifas por deportista para preservar el valor historico de cada evento.

START TRANSACTION;

ALTER TABLE eventos_federados
    ADD COLUMN tarifa_una_modalidad DECIMAL(10,2) NOT NULL DEFAULT 38000.00 AFTER costo_inscripcion,
    ADD COLUMN tarifa_dos_modalidades DECIMAL(10,2) NOT NULL DEFAULT 53000.00 AFTER tarifa_una_modalidad;

UPDATE eventos_federados
SET tarifa_una_modalidad = CASE
        WHEN costo_inscripcion > 0 THEN costo_inscripcion
        ELSE 38000.00
    END,
    tarifa_dos_modalidades = 53000.00;

COMMIT;
