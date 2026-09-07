START TRANSACTION;

CREATE TABLE IF NOT EXISTS particular_clientes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL, email VARCHAR(160) NOT NULL,
 telefono VARCHAR(40) DEFAULT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS particular_deportistas (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cliente_id INT UNSIGNED NOT NULL, nombre VARCHAR(120) NOT NULL,
 fecha_nacimiento DATE DEFAULT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
 FOREIGN KEY (cliente_id) REFERENCES particular_clientes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS particular_monitores (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120) NOT NULL, email VARCHAR(160) DEFAULT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS particular_configuracion (
 id TINYINT UNSIGNED PRIMARY KEY, duracion_min SMALLINT UNSIGNED NOT NULL DEFAULT 60,
 valor_base DECIMAL(10,2) NOT NULL DEFAULT 30000, retencion_min SMALLINT UNSIGNED NOT NULL DEFAULT 15,
 zona_horaria VARCHAR(64) NOT NULL DEFAULT 'America/Santiago', iva_comision DECIMAL(5,4) NOT NULL DEFAULT 0.19
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO particular_configuracion (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;
CREATE TABLE IF NOT EXISTS particular_disponibilidad (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, monitor_id INT UNSIGNED DEFAULT NULL, dia_semana TINYINT UNSIGNED NOT NULL,
 hora_inicio TIME NOT NULL, hora_fin TIME NOT NULL, activo TINYINT(1) NOT NULL DEFAULT 1,
 FOREIGN KEY (monitor_id) REFERENCES particular_monitores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS particular_bloques (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, monitor_id INT UNSIGNED NOT NULL, fecha DATE NOT NULL,
 inicio DATETIME NOT NULL, fin DATETIME NOT NULL, valor_base DECIMAL(10,2) NOT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1, UNIQUE KEY uq_particular_bloque (monitor_id,inicio),
 FOREIGN KEY (monitor_id) REFERENCES particular_monitores(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS particular_reservas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, bloque_id BIGINT UNSIGNED NOT NULL, cliente_id INT UNSIGNED NOT NULL,
 deportista_id INT UNSIGNED NOT NULL, estado ENUM('pending','awaiting_payment','paid','confirmed','cancelled','expired','completed','no_show') NOT NULL DEFAULT 'pending',
 expira_at DATETIME DEFAULT NULL, base_amount DECIMAL(10,2) NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (bloque_id) REFERENCES particular_bloques(id) ON DELETE RESTRICT,
 FOREIGN KEY (cliente_id) REFERENCES particular_clientes(id) ON DELETE RESTRICT,
 FOREIGN KEY (deportista_id) REFERENCES particular_deportistas(id) ON DELETE RESTRICT,
 KEY idx_particular_reserva_estado (estado,expira_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS particular_ocupaciones (
 bloque_id BIGINT UNSIGNED PRIMARY KEY, reserva_id BIGINT UNSIGNED NOT NULL, ocupada_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (bloque_id) REFERENCES particular_bloques(id) ON DELETE CASCADE,
 FOREIGN KEY (reserva_id) REFERENCES particular_reservas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS particular_medios_pago (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, codigo VARCHAR(40) NOT NULL UNIQUE, nombre VARCHAR(80) NOT NULL,
 proveedor VARCHAR(40) NOT NULL, porcentaje DECIMAL(7,5) NOT NULL DEFAULT 0, aplica_iva TINYINT(1) NOT NULL DEFAULT 0,
 activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS particular_pagos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, reserva_id BIGINT UNSIGNED NOT NULL, medio_pago_id INT UNSIGNED NOT NULL,
 base_amount DECIMAL(10,2) NOT NULL, payment_fee DECIMAL(10,2) NOT NULL DEFAULT 0, charged_amount DECIMAL(10,2) NOT NULL,
 provider_payment_id VARCHAR(160) DEFAULT NULL, status VARCHAR(30) NOT NULL DEFAULT 'pending', paid_at DATETIME DEFAULT NULL,
 FOREIGN KEY (reserva_id) REFERENCES particular_reservas(id) ON DELETE RESTRICT,
 FOREIGN KEY (medio_pago_id) REFERENCES particular_medios_pago(id) ON DELETE RESTRICT,
 KEY idx_particular_pago_provider (provider_payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO particular_medios_pago (codigo,nombre,proveedor,porcentaje,aplica_iva) VALUES
 ('transferencia','Transferencia','transferencia',0,0),('khipu','Khipu','khipu',0.01,1),('mercadopago','Mercado Pago','mercadopago',0,1)
 ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

COMMIT;
