-- 004_modalidades_competencia.sql
-- Tabla de modalidades de competencia, reglas de categoria y asignacion multiple de deportistas.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS modalidades_competencia (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_modalidades_competencia_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO modalidades_competencia (codigo, nombre, orden) VALUES
('freeskating', 'Freeskating', 1),
('solo_dance', 'Solo Dance', 2),
('figura', 'Figura', 3),
('inline', 'Inline', 4),
('no_compite', 'No compite', 5)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    orden = VALUES(orden),
    activo = 1;

CREATE TABLE IF NOT EXISTS modalidades_competencia_reglas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    modalidad_competencia_id INT UNSIGNED NOT NULL,
    nivel VARCHAR(80) NOT NULL,
    subnivel VARCHAR(80) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    edad_min SMALLINT UNSIGNED DEFAULT NULL,
    edad_max SMALLINT UNSIGNED DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_modalidades_competencia_reglas_modalidad
        FOREIGN KEY (modalidad_competencia_id) REFERENCES modalidades_competencia(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_modalidades_competencia_regla (
        modalidad_competencia_id,
        nivel,
        subnivel,
        categoria,
        edad_min,
        edad_max
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modalidades_competencia_reglas (
    modalidad_competencia_id,
    nivel,
    subnivel,
    categoria,
    edad_min,
    edad_max
)
SELECT m.id, r.nivel, r.subnivel, r.categoria, r.edad_min, r.edad_max
FROM modalidades_competencia m
INNER JOIN (
    SELECT 'freeskating' AS codigo_modalidad, 'Formativo' AS nivel, 'Inicio' AS subnivel, 'pre-novato' AS categoria, NULL AS edad_min, 5 AS edad_max
    UNION ALL SELECT 'freeskating', 'Formativo', 'Inicio', 'novato', 6, 7
    UNION ALL SELECT 'freeskating', 'Formativo', 'Inicio', 'tots', 8, 9
    UNION ALL SELECT 'freeskating', 'Formativo', 'Inicio', 'mini', 10, 11
    UNION ALL SELECT 'freeskating', 'Formativo', 'Inicio', 'espoir', 12, 13
    UNION ALL SELECT 'freeskating', 'Formativo', 'Intermedio', 'pre-novato', NULL, 5
    UNION ALL SELECT 'freeskating', 'Formativo', 'Intermedio', 'novato', 6, 7
    UNION ALL SELECT 'freeskating', 'Formativo', 'Intermedio', 'tots', 8, 9
    UNION ALL SELECT 'freeskating', 'Formativo', 'Intermedio', 'mini', 10, 11
    UNION ALL SELECT 'freeskating', 'Formativo', 'Intermedio', 'espoir', 12, 13
    UNION ALL SELECT 'freeskating', 'Escuela', 'D', 'tots', 8, 9
    UNION ALL SELECT 'freeskating', 'Escuela', 'D', 'mini', 10, 11
    UNION ALL SELECT 'freeskating', 'Escuela', 'D', 'espoir', 12, 13
    UNION ALL SELECT 'freeskating', 'Escuela', 'D', 'cadete', 14, 15
    UNION ALL SELECT 'freeskating', 'Escuela', 'D', 'youth', 16, 16
    UNION ALL SELECT 'freeskating', 'Escuela', 'D', 'todo competidor', 17, NULL
    UNION ALL SELECT 'freeskating', 'Escuela', 'C', 'espoir', 12, 13
    UNION ALL SELECT 'freeskating', 'Escuela', 'C', 'cadete', 14, 15
    UNION ALL SELECT 'freeskating', 'Escuela', 'C', 'youth', 16, 16
    UNION ALL SELECT 'freeskating', 'Escuela', 'C', 'junior', 17, 18
    UNION ALL SELECT 'freeskating', 'Escuela', 'C', 'senior', 19, NULL
    UNION ALL SELECT 'freeskating', 'Escuela', 'B', 'cadete', 14, 15
    UNION ALL SELECT 'freeskating', 'Escuela', 'B', 'youth', 16, 16
    UNION ALL SELECT 'freeskating', 'Escuela', 'B', 'junior', 17, 18
    UNION ALL SELECT 'freeskating', 'Escuela', 'B', 'senior', 19, NULL
    UNION ALL SELECT 'freeskating', 'Promotional', 'Basic', 'mini', 10, 11
    UNION ALL SELECT 'freeskating', 'Promotional', 'Basic', 'espoir', 12, 13
    UNION ALL SELECT 'freeskating', 'Promotional', 'Basic', 'cadete', 14, 15
    UNION ALL SELECT 'freeskating', 'Promotional', 'Basic', 'youth', 16, 16
    UNION ALL SELECT 'freeskating', 'Promotional', 'Basic', 'junior', 17, 18
    UNION ALL SELECT 'freeskating', 'Promotional', 'Basic', 'senior', 19, NULL
    UNION ALL SELECT 'freeskating', 'Promotional', 'Intermediate', 'tots', 8, 9
    UNION ALL SELECT 'freeskating', 'Promotional', 'Intermediate', 'mini', 10, 11
    UNION ALL SELECT 'freeskating', 'Promotional', 'Intermediate', 'espoir', 12, 13
    UNION ALL SELECT 'freeskating', 'Promotional', 'Intermediate', 'cadete', 14, 15
    UNION ALL SELECT 'freeskating', 'Promotional', 'Intermediate', 'youth', 16, 16
    UNION ALL SELECT 'freeskating', 'Promotional', 'Intermediate', 'junior', 17, 18
    UNION ALL SELECT 'freeskating', 'Promotional', 'Intermediate', 'senior', 19, NULL
    UNION ALL SELECT 'freeskating', 'International', 'Unico', 'tots', 8, 9
    UNION ALL SELECT 'freeskating', 'International', 'Unico', 'mini', 10, 11
    UNION ALL SELECT 'freeskating', 'International', 'Unico', 'espoir', 12, 13
    UNION ALL SELECT 'freeskating', 'International', 'Unico', 'cadete', 14, 15
    UNION ALL SELECT 'freeskating', 'International', 'Unico', 'youth', 16, 16
    UNION ALL SELECT 'freeskating', 'International', 'Unico', 'junior', 17, 18
    UNION ALL SELECT 'freeskating', 'International', 'Unico', 'senior', 19, NULL
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Inicio', 'novato', 5, 7
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Inicio', 'mini', 8, 11
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Inicio', 'cadete', 12, 15
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Inicio', 'junior', 16, NULL
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Intermedio', 'novato', 5, 7
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Intermedio', 'mini', 8, 11
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Intermedio', 'cadete', 12, 15
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Intermedio', 'junior', 16, NULL
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Avanzado', 'novato', 5, 7
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Avanzado', 'mini', 8, 11
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Avanzado', 'cadete', 12, 15
    UNION ALL SELECT 'freeskating', 'Adaptados', 'Avanzado', 'junior', 16, NULL
    UNION ALL SELECT 'inline', 'Escuela', 'Unico', 'tots', 8, 9
    UNION ALL SELECT 'inline', 'Escuela', 'Unico', 'mini', 10, 11
    UNION ALL SELECT 'inline', 'Escuela', 'Unico', 'espoir', 12, 13
    UNION ALL SELECT 'inline', 'Escuela', 'Unico', 'cadete', 14, 15
    UNION ALL SELECT 'inline', 'Escuela', 'Unico', 'youth', 16, 16
    UNION ALL SELECT 'inline', 'Escuela', 'Unico', 'junior', 17, 18
    UNION ALL SELECT 'inline', 'Escuela', 'Unico', 'senior', 19, NULL
    UNION ALL SELECT 'inline', 'Promotional', 'Unico', 'tots', 8, 9
    UNION ALL SELECT 'inline', 'Promotional', 'Unico', 'mini', 10, 11
    UNION ALL SELECT 'inline', 'Promotional', 'Unico', 'espoir', 12, 13
    UNION ALL SELECT 'inline', 'Promotional', 'Unico', 'cadete', 14, 15
    UNION ALL SELECT 'inline', 'Promotional', 'Unico', 'youth', 16, 16
    UNION ALL SELECT 'inline', 'Promotional', 'Unico', 'junior', 17, 18
    UNION ALL SELECT 'inline', 'Promotional', 'Unico', 'senior', 19, NULL
    UNION ALL SELECT 'inline', 'International', 'Unico', 'tots', 8, 9
    UNION ALL SELECT 'inline', 'International', 'Unico', 'mini', 10, 11
    UNION ALL SELECT 'inline', 'International', 'Unico', 'espoir', 12, 13
    UNION ALL SELECT 'inline', 'International', 'Unico', 'cadete', 14, 15
    UNION ALL SELECT 'inline', 'International', 'Unico', 'youth', 16, 16
    UNION ALL SELECT 'inline', 'International', 'Unico', 'junior', 17, 18
    UNION ALL SELECT 'inline', 'International', 'Unico', 'senior', 19, NULL
    UNION ALL SELECT 'figura', 'Escuela', 'D', 'novato', 6, 7
    UNION ALL SELECT 'figura', 'Escuela', 'D', 'tots', 8, 9
    UNION ALL SELECT 'figura', 'Escuela', 'D', 'mini', 10, 11
    UNION ALL SELECT 'figura', 'Escuela', 'D', 'espoir', 12, 13
    UNION ALL SELECT 'figura', 'Escuela', 'D', 'cadete', 14, 15
    UNION ALL SELECT 'figura', 'Escuela', 'D', 'youth', 16, 16
    UNION ALL SELECT 'figura', 'Escuela', 'D', 'junior', 17, 18
    UNION ALL SELECT 'figura', 'Escuela', 'D', 'senior', 19, NULL
    UNION ALL SELECT 'figura', 'Escuela', 'C', 'tots', 8, 9
    UNION ALL SELECT 'figura', 'Escuela', 'C', 'mini', 10, 11
    UNION ALL SELECT 'figura', 'Escuela', 'C', 'espoir', 12, 13
    UNION ALL SELECT 'figura', 'Escuela', 'C', 'cadete', 14, 15
    UNION ALL SELECT 'figura', 'Escuela', 'C', 'youth', 16, 16
    UNION ALL SELECT 'figura', 'Escuela', 'C', 'junior', 17, 18
    UNION ALL SELECT 'figura', 'Escuela', 'C', 'senior', 19, NULL
    UNION ALL SELECT 'figura', 'Promotional', 'Eficencia Basica', 'tots', 8, 9
    UNION ALL SELECT 'figura', 'Promotional', 'Eficencia Basica', 'mini', 10, 11
    UNION ALL SELECT 'figura', 'Promotional', 'Eficencia Basica', 'espoir', 12, 13
    UNION ALL SELECT 'figura', 'Promotional', 'Eficencia Basica', 'cadete', 14, 15
    UNION ALL SELECT 'figura', 'Promotional', 'Eficencia Basica', 'youth', 16, 16
    UNION ALL SELECT 'figura', 'Promotional', 'Eficencia Basica', 'junior', 17, 18
    UNION ALL SELECT 'figura', 'Promotional', 'Eficencia Basica', 'senior', 19, NULL
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Intermedia', 'tots', 8, 9
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Intermedia', 'mini', 10, 11
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Intermedia', 'espoir', 12, 13
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Intermedia', 'cadete', 14, 15
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Intermedia', 'youth', 16, 16
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Intermedia', 'junior', 17, 18
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Intermedia', 'senior', 19, NULL
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Avanzado', 'tots', 8, 9
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Avanzado', 'mini', 10, 11
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Avanzado', 'espoir', 12, 13
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Avanzado', 'cadete', 14, 15
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Avanzado', 'youth', 16, 16
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Avanzado', 'junior', 17, 18
    UNION ALL SELECT 'figura', 'Promotional', 'Eficiencia Avanzado', 'senior', 19, NULL
    UNION ALL SELECT 'figura', 'International', 'Unico', 'tots', 8, 9
    UNION ALL SELECT 'figura', 'International', 'Unico', 'mini', 10, 11
    UNION ALL SELECT 'figura', 'International', 'Unico', 'espoir', 12, 13
    UNION ALL SELECT 'figura', 'International', 'Unico', 'cadete', 14, 15
    UNION ALL SELECT 'figura', 'International', 'Unico', 'youth', 16, 16
    UNION ALL SELECT 'figura', 'International', 'Unico', 'junior', 17, 18
    UNION ALL SELECT 'figura', 'International', 'Unico', 'senior', 19, NULL
    UNION ALL SELECT 'solo_dance', 'Escuela', 'D', 'tots', 8, 9
    UNION ALL SELECT 'solo_dance', 'Escuela', 'D', 'mini', 10, 11
    UNION ALL SELECT 'solo_dance', 'Escuela', 'D', 'espoir', 12, 13
    UNION ALL SELECT 'solo_dance', 'Escuela', 'D', 'cadete', 14, 15
    UNION ALL SELECT 'solo_dance', 'Escuela', 'D', 'youth', 16, 16
    UNION ALL SELECT 'solo_dance', 'Escuela', 'D', 'todo competidor', 17, NULL
    UNION ALL SELECT 'solo_dance', 'Escuela', 'C', 'espoir', 12, 13
    UNION ALL SELECT 'solo_dance', 'Escuela', 'C', 'cadete', 14, 15
    UNION ALL SELECT 'solo_dance', 'Escuela', 'C', 'youth', 16, 16
    UNION ALL SELECT 'solo_dance', 'Escuela', 'C', 'todo competidor', 17, NULL
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Intermediate', 'mini', 10, 11
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Intermediate', 'espoir', 12, 13
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Intermediate', 'cadete', 14, 15
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Intermediate', 'youth', 16, 16
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Intermediate', 'junior', 17, 18
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Intermediate', 'senior', 19, NULL
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Basic', 'espoir', 12, 13
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Basic', 'cadete', 14, 15
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Basic', 'youth', 16, 16
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Basic', 'junior', 17, 18
    UNION ALL SELECT 'solo_dance', 'Promotional', 'Basic', 'senior', 19, NULL
    UNION ALL SELECT 'solo_dance', 'International', 'Unico', 'tots', 8, 9
    UNION ALL SELECT 'solo_dance', 'International', 'Unico', 'mini', 10, 11
    UNION ALL SELECT 'solo_dance', 'International', 'Unico', 'espoir', 12, 13
    UNION ALL SELECT 'solo_dance', 'International', 'Unico', 'cadete', 14, 15
    UNION ALL SELECT 'solo_dance', 'International', 'Unico', 'youth', 16, 16
    UNION ALL SELECT 'solo_dance', 'International', 'Unico', 'junior', 17, 18
    UNION ALL SELECT 'solo_dance', 'International', 'Unico', 'senior', 19, NULL
) AS r
WHERE m.codigo = r.codigo_modalidad;

CREATE TABLE IF NOT EXISTS deportista_modalidades_competencia (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deportista_id INT UNSIGNED NOT NULL,
    modalidad_competencia_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_deportista_modalidades_competencia_deportista
        FOREIGN KEY (deportista_id) REFERENCES deportistas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_deportista_modalidades_competencia_modalidad
        FOREIGN KEY (modalidad_competencia_id) REFERENCES modalidades_competencia(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_deportista_modalidad_competencia (
        deportista_id,
        modalidad_competencia_id
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_deportista_modalidades_competencia_deportista
    ON deportista_modalidades_competencia (deportista_id);
CREATE INDEX idx_deportista_modalidades_competencia_modalidad
    ON deportista_modalidades_competencia (modalidad_competencia_id);
CREATE INDEX idx_modalidades_competencia_reglas_modalidad
    ON modalidades_competencia_reglas (modalidad_competencia_id);

COMMIT;
