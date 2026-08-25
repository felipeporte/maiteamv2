-- 007_eventos_federados.sql
-- Modulo para eventos federados e inscripciones por nivel.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS eventos_federados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(160) NOT NULL,
    nivel VARCHAR(80) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL,
    lugar VARCHAR(160) DEFAULT NULL,
    costo_inscripcion DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cupo SMALLINT UNSIGNED DEFAULT NULL,
    estado ENUM('borrador','abierto','cerrado','finalizado') NOT NULL DEFAULT 'borrador',
    observaciones VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_eventos_federados_nivel (nivel),
    KEY idx_eventos_federados_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evento_federado_inscripciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    deportista_id INT UNSIGNED NOT NULL,
    apoderado_id INT UNSIGNED NOT NULL,
    fecha_inscripcion DATE NOT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado_pago ENUM('pendiente','pagado','anulado') NOT NULL DEFAULT 'pendiente',
    referencia VARCHAR(120) DEFAULT NULL,
    observaciones VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_evento_federado_inscripciones_evento
        FOREIGN KEY (evento_id) REFERENCES eventos_federados(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_evento_federado_inscripciones_deportista
        FOREIGN KEY (deportista_id) REFERENCES deportistas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_evento_federado_inscripciones_apoderado
        FOREIGN KEY (apoderado_id) REFERENCES apoderados(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_evento_federado_deportista (evento_id, deportista_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_evento_federado_inscripciones_evento ON evento_federado_inscripciones (evento_id);
CREATE INDEX idx_evento_federado_inscripciones_deportista ON evento_federado_inscripciones (deportista_id);
CREATE INDEX idx_evento_federado_inscripciones_apoderado ON evento_federado_inscripciones (apoderado_id);
CREATE INDEX idx_evento_federado_inscripciones_estado ON evento_federado_inscripciones (estado_pago);

COMMIT;
