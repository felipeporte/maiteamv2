ALTER TABLE particular_pagos
  ADD COLUMN comprobante_url VARCHAR(255) NULL AFTER provider_payment_id,
  ADD COLUMN validado_por VARCHAR(120) NULL AFTER paid_at,
  ADD COLUMN validado_at DATETIME NULL AFTER validado_por;
