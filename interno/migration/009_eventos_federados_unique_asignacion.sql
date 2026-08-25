-- 009_eventos_federados_unique_asignacion.sql
-- Permite registrar multiples modalidades o subniveles del mismo deportista en un evento.

START TRANSACTION;

ALTER TABLE evento_federado_inscripciones
    DROP INDEX uq_evento_federado_deportista,
    ADD UNIQUE KEY uq_evento_federado_asignacion (evento_id, deportista_modalidades_competencia_id);

COMMIT;
