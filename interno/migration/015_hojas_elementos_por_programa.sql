-- 015_hojas_elementos_por_programa.sql
-- Permite una hoja independiente por cada programa técnico de una inscripción.

START TRANSACTION;

ALTER TABLE evento_federado_hojas_elementos
    DROP INDEX uq_evento_federado_hojas_inscripcion,
    ADD UNIQUE KEY uq_evento_federado_hojas_inscripcion_programa (
        evento_federado_inscripcion_id,
        plantilla_codigo
    );

COMMIT;
