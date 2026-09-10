-- 016: sesiones grupales de clases extra previas a competencias.
ALTER TABLE clases
    ADD COLUMN clase_extra_id INT UNSIGNED DEFAULT NULL AFTER notas,
    ADD COLUMN valor_clase DECIMAL(10,2) DEFAULT NULL AFTER clase_extra_id,
    ADD COLUMN prorrateo_pista DECIMAL(10,2) DEFAULT NULL AFTER valor_clase;

CREATE TABLE IF NOT EXISTS clases_extras (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    competencia_id INT UNSIGNED DEFAULT NULL,
    evento_federado_id INT UNSIGNED DEFAULT NULL,
    coach_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    duracion_min SMALLINT UNSIGNED DEFAULT NULL,
    valor_clase DECIMAL(10,2) NOT NULL DEFAULT 10000.00,
    costo_pista DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado ENUM('programada','realizada','anulada') NOT NULL DEFAULT 'programada',
    notas VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_clases_extras_competencia FOREIGN KEY (competencia_id) REFERENCES competencias(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_clases_extras_evento_federado FOREIGN KEY (evento_federado_id) REFERENCES eventos_federados(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_clases_extras_coach FOREIGN KEY (coach_id) REFERENCES coaches(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_clases_extra ON clases (clase_extra_id);
CREATE INDEX idx_clases_extras_fecha ON clases_extras (fecha);
CREATE INDEX idx_clases_extras_evento_federado ON clases_extras (evento_federado_id);

UPDATE clases_extras SET valor_clase = 10000.00;
UPDATE clases
SET valor_clase = 10000.00,
    tarifa = 10000.00 + COALESCE(prorrateo_pista, 0.00)
WHERE clase_extra_id IS NOT NULL;
