<?php

declare(strict_types=1);

function apoderados_all(): array
{
    $stmt = db()->query('SELECT id, nombre, telefono, email, direccion FROM apoderados ORDER BY nombre');
    return $stmt->fetchAll();
}

function apoderados_all_with_saldo(): array
{
    $sql = 'SELECT a.id, a.nombre, a.telefono, a.email, a.direccion, '
        . 'COALESCE(modalidades.total_modalidades, 0) AS total_modalidades, '
        . 'COALESCE(cuota.total_cuota, 0) AS total_cuota, '
        . 'COALESCE(pagos.total_pagos, 0) AS total_pagos, '
        . '(COALESCE(modalidades.total_modalidades, 0) + COALESCE(cuota.total_cuota, 0) - COALESCE(pagos.total_pagos, 0)) AS saldo '
        . 'FROM apoderados a '
        . 'LEFT JOIN ('
        . '  SELECT d.apoderado_id, '
        . '  SUM('
        . '    m.costo_mensual * ('
        . '      TIMESTAMPDIFF('
        . '        MONTH, i.fecha_inicio, LEAST(CURDATE(), COALESCE(i.fecha_fin, CURDATE()))'
        . '      ) + 1'
        . '    )'
        . '  ) AS total_modalidades '
        . '  FROM inscripciones i '
        . '  INNER JOIN deportistas d ON d.id = i.deportista_id '
        . '  INNER JOIN modalidades m ON m.id = i.modalidad_id '
        . '  WHERE i.fecha_inicio <= CURDATE() '
        . '  AND (i.fecha_fin IS NULL OR i.fecha_fin >= i.fecha_inicio) '
        . '  GROUP BY d.apoderado_id '
        . ') modalidades ON modalidades.apoderado_id = a.id '
        . 'LEFT JOIN ('
        . '  SELECT d.apoderado_id, '
        . '  3000.00 * ('
        . '    TIMESTAMPDIFF('
        . '      MONTH, MIN(i.fecha_inicio), LEAST(CURDATE(), MAX(COALESCE(i.fecha_fin, CURDATE())))'
        . '    ) + 1'
        . '  ) AS total_cuota '
        . '  FROM inscripciones i '
        . '  INNER JOIN deportistas d ON d.id = i.deportista_id '
        . '  WHERE i.fecha_inicio <= CURDATE() '
        . '  AND (i.fecha_fin IS NULL OR i.fecha_fin >= i.fecha_inicio) '
        . '  GROUP BY d.apoderado_id '
        . ') cuota ON cuota.apoderado_id = a.id '
        . 'LEFT JOIN ('
        . '  SELECT apoderado_id, SUM(monto_total) AS total_pagos '
        . '  FROM pagos '
        . '  WHERE fecha_pago <= CURDATE() '
        . '  GROUP BY apoderado_id '
        . ') pagos ON pagos.apoderado_id = a.id '
        . 'ORDER BY a.nombre';

    $stmt = db()->query($sql);
    return $stmt->fetchAll();
}

function apoderado_deuda_modalidad_cuota(int $apoderadoId): array
{
    $stmtModalidades = db()->prepare(
        'SELECT '
        . 'COALESCE(SUM('
        . '  m.costo_mensual * ('
        . '    TIMESTAMPDIFF('
        . '      MONTH, i.fecha_inicio, LEAST(CURDATE(), COALESCE(i.fecha_fin, CURDATE()))'
        . '    ) + 1'
        . '  )'
        . '), 0) '
        . 'FROM inscripciones i '
        . 'INNER JOIN deportistas d ON d.id = i.deportista_id '
        . 'INNER JOIN modalidades m ON m.id = i.modalidad_id '
        . 'WHERE d.apoderado_id = :apoderado_id '
        . 'AND i.fecha_inicio <= CURDATE() '
        . 'AND (i.fecha_fin IS NULL OR i.fecha_fin >= i.fecha_inicio)'
    );
    $stmtModalidades->execute(['apoderado_id' => $apoderadoId]);
    $modalidades = (float) ($stmtModalidades->fetchColumn() ?? 0);

    $stmtCuota = db()->prepare(
        'SELECT '
        . 'COALESCE('
        . '  3000.00 * ('
        . '    TIMESTAMPDIFF('
        . '      MONTH, MIN(i.fecha_inicio), LEAST(CURDATE(), MAX(COALESCE(i.fecha_fin, CURDATE())))'
        . '    ) + 1'
        . '  ), 0'
        . ') '
        . 'FROM inscripciones i '
        . 'INNER JOIN deportistas d ON d.id = i.deportista_id '
        . 'WHERE d.apoderado_id = :apoderado_id '
        . 'AND i.fecha_inicio <= CURDATE() '
        . 'AND (i.fecha_fin IS NULL OR i.fecha_fin >= i.fecha_inicio)'
    );
    $stmtCuota->execute(['apoderado_id' => $apoderadoId]);
    $cuota = (float) ($stmtCuota->fetchColumn() ?? 0);

    $stmtPagos = db()->prepare(
        'SELECT COALESCE(SUM(monto_total), 0) FROM pagos '
        . 'WHERE apoderado_id = :apoderado_id AND fecha_pago <= CURDATE()'
    );
    $stmtPagos->execute(['apoderado_id' => $apoderadoId]);
    $pagos = (float) ($stmtPagos->fetchColumn() ?? 0);

    $total = $modalidades + $cuota;
    $saldo = $total - $pagos;

    return [
        'modalidades' => $modalidades,
        'cuota' => $cuota,
        'total' => $total,
        'pagos' => $pagos,
        'saldo' => $saldo,
    ];
}

function apoderado_deudas_detalle(int $apoderadoId): array
{
    $stmtModalidades = db()->prepare(
        'SELECT m.id, m.nombre, m.costo_mensual, m.coach_id, c.nombre AS coach_nombre, '
        . 'MIN(i.fecha_inicio) AS fecha_inicio, '
        . 'MAX(COALESCE(i.fecha_fin, CURDATE())) AS fecha_fin, '
        . '(TIMESTAMPDIFF(MONTH, MIN(i.fecha_inicio), LEAST(CURDATE(), MAX(COALESCE(i.fecha_fin, CURDATE())))) + 1) AS meses, '
        . 'SUM('
        . '  m.costo_mensual * ('
        . '    TIMESTAMPDIFF('
        . '      MONTH, i.fecha_inicio, LEAST(CURDATE(), COALESCE(i.fecha_fin, CURDATE()))'
        . '    ) + 1'
        . '  )'
        . ') AS total '
        . 'FROM inscripciones i '
        . 'INNER JOIN deportistas d ON d.id = i.deportista_id '
        . 'INNER JOIN modalidades m ON m.id = i.modalidad_id '
        . 'INNER JOIN coaches c ON c.id = m.coach_id '
        . 'WHERE d.apoderado_id = :apoderado_id '
        . 'AND i.fecha_inicio <= CURDATE() '
        . 'AND (i.fecha_fin IS NULL OR i.fecha_fin >= i.fecha_inicio) '
        . 'GROUP BY m.id, m.nombre, m.costo_mensual, m.coach_id, c.nombre '
        . 'ORDER BY m.nombre'
    );
    $stmtModalidades->execute(['apoderado_id' => $apoderadoId]);
    $modalidades = $stmtModalidades->fetchAll();

    $stmtCuota = db()->prepare(
        'SELECT '
        . 'MIN(i.fecha_inicio) AS fecha_inicio, '
        . 'MAX(COALESCE(i.fecha_fin, CURDATE())) AS fecha_fin, '
        . '(TIMESTAMPDIFF(MONTH, MIN(i.fecha_inicio), LEAST(CURDATE(), MAX(COALESCE(i.fecha_fin, CURDATE())))) + 1) AS meses, '
        . '3000.00 * ('
        . '  TIMESTAMPDIFF('
        . '    MONTH, MIN(i.fecha_inicio), LEAST(CURDATE(), MAX(COALESCE(i.fecha_fin, CURDATE())))'
        . '  ) + 1'
        . ') AS total '
        . 'FROM inscripciones i '
        . 'INNER JOIN deportistas d ON d.id = i.deportista_id '
        . 'WHERE d.apoderado_id = :apoderado_id '
        . 'AND i.fecha_inicio <= CURDATE() '
        . 'AND (i.fecha_fin IS NULL OR i.fecha_fin >= i.fecha_inicio)'
    );
    $stmtCuota->execute(['apoderado_id' => $apoderadoId]);
    $cuota = $stmtCuota->fetch() ?: null;

    $stmtPagos = db()->prepare(
        'SELECT COALESCE(SUM(monto_total), 0) FROM pagos '
        . 'WHERE apoderado_id = :apoderado_id AND fecha_pago <= CURDATE()'
    );
    $stmtPagos->execute(['apoderado_id' => $apoderadoId]);
    $pagos = (float) ($stmtPagos->fetchColumn() ?? 0);

    $totalModalidades = 0.0;
    foreach ($modalidades as $row) {
        $totalModalidades += (float) $row['total'];
    }
    $totalCuota = $cuota ? (float) $cuota['total'] : 0.0;
    $total = $totalModalidades + $totalCuota;
    $saldo = $total - $pagos;

    return [
        'modalidades' => $modalidades,
        'cuota' => $cuota,
        'pagos' => $pagos,
        'total' => $total,
        'saldo' => $saldo,
    ];
}

function apoderado_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT id, nombre, telefono, email, direccion FROM apoderados WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function apoderado_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO apoderados (nombre, telefono, email, direccion) VALUES (:nombre, :telefono, :email, :direccion)'
    );
    $stmt->execute([
        'nombre' => $data['nombre'],
        'telefono' => $data['telefono'],
        'email' => $data['email'],
        'direccion' => $data['direccion'],
    ]);

    return (int) db()->lastInsertId();
}

function apoderado_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE apoderados SET nombre = :nombre, telefono = :telefono, email = :email, direccion = :direccion WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'nombre' => $data['nombre'],
        'telefono' => $data['telefono'],
        'email' => $data['email'],
        'direccion' => $data['direccion'],
    ]);
}

function apoderado_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM apoderados WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
