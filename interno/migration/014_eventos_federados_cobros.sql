-- 014_eventos_federados_cobros.sql
-- Separa el cobro de inscripcion del acompanamiento en pista.

START TRANSACTION;

ALTER TABLE eventos_federados
    ADD COLUMN tarifa_acompanamiento_pista DECIMAL(10,2) NOT NULL DEFAULT 40000.00 AFTER tarifa_dos_modalidades;

CREATE TABLE IF NOT EXISTS evento_federado_cobros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT UNSIGNED NOT NULL,
    deportista_id INT UNSIGNED NOT NULL,
    tipo_cobro ENUM('inscripcion', 'acompanamiento') NOT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado_pago ENUM('pendiente', 'pagado', 'anulado') NOT NULL DEFAULT 'pendiente',
    metodo_pago VARCHAR(40) DEFAULT NULL,
    referencia VARCHAR(120) DEFAULT NULL,
    pagado_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_evento_federado_cobros_evento
        FOREIGN KEY (evento_id) REFERENCES eventos_federados(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_evento_federado_cobros_deportista
        FOREIGN KEY (deportista_id) REFERENCES deportistas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_evento_federado_cobro_tipo (evento_id, deportista_id, tipo_cobro),
    KEY idx_evento_federado_cobros_evento (evento_id),
    KEY idx_evento_federado_cobros_estado (estado_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO evento_federado_cobros (evento_id, deportista_id, tipo_cobro, monto, estado_pago)
SELECT agrupadas.evento_id,
       agrupadas.deportista_id,
       'inscripcion',
       agrupadas.monto_total,
       IF(agrupadas.modalidades_activas = agrupadas.modalidades_pagadas, 'pagado', 'pendiente')
FROM (
    SELECT evento_id,
           deportista_id,
           SUM(CASE WHEN estado_pago <> 'anulado' THEN 1 ELSE 0 END) AS modalidades_activas,
           SUM(CASE WHEN estado_pago = 'pagado' THEN 1 ELSE 0 END) AS modalidades_pagadas,
           SUM(CASE WHEN estado_pago <> 'anulado' THEN monto ELSE 0 END) AS monto_total
    FROM evento_federado_inscripciones
    GROUP BY evento_id, deportista_id
) agrupadas
WHERE agrupadas.modalidades_activas > 0
ON DUPLICATE KEY UPDATE
    monto = VALUES(monto),
    estado_pago = IF(evento_federado_cobros.estado_pago = 'pagado', 'pagado', VALUES(estado_pago));

COMMIT;
