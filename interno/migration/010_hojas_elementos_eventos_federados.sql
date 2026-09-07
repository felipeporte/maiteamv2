-- 010_hojas_elementos_eventos_federados.sql
-- Guarda borradores de hojas de elementos para inscripciones de eventos federados.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS evento_federado_hojas_elementos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    evento_federado_inscripcion_id INT UNSIGNED NOT NULL,
    deportista_id INT UNSIGNED NOT NULL,
    modalidad_competencia_id INT UNSIGNED NOT NULL,
    plantilla_codigo VARCHAR(40) NOT NULL,
    competidor_nombre VARCHAR(160) NOT NULL,
    categoria_label VARCHAR(160) DEFAULT NULL,
    club VARCHAR(160) DEFAULT NULL,
    representing VARCHAR(160) DEFAULT NULL,
    choreography VARCHAR(160) DEFAULT NULL,
    observaciones VARCHAR(255) DEFAULT NULL,
    elementos_json LONGTEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_evento_federado_hojas_evento
        FOREIGN KEY (evento_id) REFERENCES eventos_federados(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_evento_federado_hojas_inscripcion
        FOREIGN KEY (evento_federado_inscripcion_id) REFERENCES evento_federado_inscripciones(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_evento_federado_hojas_deportista
        FOREIGN KEY (deportista_id) REFERENCES deportistas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_evento_federado_hojas_modalidad
        FOREIGN KEY (modalidad_competencia_id) REFERENCES deportista_modalidades_competencia(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_evento_federado_hojas_inscripcion (evento_federado_inscripcion_id),
    KEY idx_evento_federado_hojas_evento (evento_id),
    KEY idx_evento_federado_hojas_deportista (deportista_id),
    KEY idx_evento_federado_hojas_plantilla (plantilla_codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
