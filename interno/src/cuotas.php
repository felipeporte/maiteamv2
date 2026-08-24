<?php

declare(strict_types=1);

function cuotas_all(string $periodo = '', string $estado = ''): array
{
    $where = [];
    $params = [];

    if ($periodo !== '') {
        $where[] = 'c.periodo = :periodo';
        $params['periodo'] = $periodo;
    }

    if (in_array($estado, ['pendiente', 'pagado'], true)) {
        $where[] = 'c.estado = :estado';
        $params['estado'] = $estado;
    }

    $sql = 'SELECT c.id, c.periodo, c.fecha_pago, c.monto, c.estado, '
        . 'a.nombre AS apoderado_nombre '
        . 'FROM cuotas_socios c '
        . 'INNER JOIN apoderados a ON a.id = c.apoderado_id ';

    if (!empty($where)) {
        $sql .= 'WHERE ' . implode(' AND ', $where) . ' ';
    }

    $sql .= 'ORDER BY c.periodo DESC, c.id DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function cuota_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT c.id, c.periodo, c.fecha_pago, c.monto, c.estado, '
        . 'c.apoderado_id, a.nombre AS apoderado_nombre '
        . 'FROM cuotas_socios c '
        . 'INNER JOIN apoderados a ON a.id = c.apoderado_id '
        . 'WHERE c.id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function cuota_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO cuotas_socios (apoderado_id, periodo, fecha_pago, monto, estado) '
        . 'VALUES (:apoderado_id, :periodo, :fecha_pago, :monto, :estado)'
    );
    $stmt->execute([
        'apoderado_id' => $data['apoderado_id'],
        'periodo' => $data['periodo'],
        'fecha_pago' => $data['fecha_pago'] ?: null,
        'monto' => $data['monto'],
        'estado' => $data['estado'],
    ]);

    return (int) db()->lastInsertId();
}

function periodo_mensual_valido(string $periodo): bool
{
    if (!preg_match('/^\d{4}-\d{2}$/', $periodo)) {
        return false;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $periodo . '-01');

    return $date instanceof DateTimeImmutable
        && $date->format('Y-m') === $periodo;
}

function periodo_mensual_rango_valido(string $periodoInicio, string $periodoFin): bool
{
    return periodo_mensual_valido($periodoInicio)
        && periodo_mensual_valido($periodoFin)
        && $periodoInicio <= $periodoFin;
}

function periodos_mensuales_en_rango(string $periodoInicio, string $periodoFin): array
{
    if (!periodo_mensual_rango_valido($periodoInicio, $periodoFin)) {
        return [];
    }

    $cursor = DateTimeImmutable::createFromFormat('!Y-m-d', $periodoInicio . '-01');
    $fin = DateTimeImmutable::createFromFormat('!Y-m-d', $periodoFin . '-01');

    if (!$cursor instanceof DateTimeImmutable || !$fin instanceof DateTimeImmutable) {
        return [];
    }

    $periodos = [];
    while ($cursor <= $fin) {
        $periodos[] = $cursor->format('Y-m');
        $cursor = $cursor->modify('+1 month');
    }

    return $periodos;
}

function cuotas_create_range_for_apoderado(
    int $apoderadoId,
    string $periodoInicio,
    string $periodoFin,
    float $monto = 3000.0
): array {
    if ($apoderadoId <= 0) {
        throw new InvalidArgumentException('Apoderado invalido.');
    }

    if (!periodo_mensual_rango_valido($periodoInicio, $periodoFin)) {
        throw new InvalidArgumentException('Rango de periodos invalido.');
    }

    $periodos = periodos_mensuales_en_rango($periodoInicio, $periodoFin);
    if (empty($periodos)) {
        return ['created' => 0, 'skipped' => 0];
    }

    $stmtExistentes = db()->prepare(
        'SELECT periodo FROM cuotas_socios '
        . 'WHERE apoderado_id = :apoderado_id '
        . 'AND periodo BETWEEN :periodo_inicio AND :periodo_fin'
    );
    $stmtExistentes->execute([
        'apoderado_id' => $apoderadoId,
        'periodo_inicio' => $periodoInicio,
        'periodo_fin' => $periodoFin,
    ]);

    $existentes = [];
    foreach ($stmtExistentes->fetchAll() as $row) {
        $existentes[(string) $row['periodo']] = true;
    }

    $pdo = db();
    $stmtInsert = $pdo->prepare(
        'INSERT INTO cuotas_socios (apoderado_id, periodo, fecha_pago, monto, estado) '
        . 'VALUES (:apoderado_id, :periodo, NULL, :monto, "pendiente")'
    );

    $created = 0;
    $skipped = 0;

    $pdo->beginTransaction();
    try {
        foreach ($periodos as $periodo) {
            if (isset($existentes[$periodo])) {
                $skipped++;
                continue;
            }

            $stmtInsert->execute([
                'apoderado_id' => $apoderadoId,
                'periodo' => $periodo,
                'monto' => $monto,
            ]);
            $created++;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    return ['created' => $created, 'skipped' => $skipped];
}

function cuotas_create_range_for_all_apoderados(
    string $periodoInicio,
    string $periodoFin,
    float $monto = 3000.0
): array {
    if (!periodo_mensual_rango_valido($periodoInicio, $periodoFin)) {
        throw new InvalidArgumentException('Rango de periodos invalido.');
    }

    $stmt = db()->query('SELECT id FROM apoderados ORDER BY id ASC');
    $apoderados = $stmt->fetchAll();

    $created = 0;
    $skipped = 0;

    foreach ($apoderados as $apoderado) {
        $resultado = cuotas_create_range_for_apoderado(
            (int) $apoderado['id'],
            $periodoInicio,
            $periodoFin,
            $monto
        );
        $created += (int) $resultado['created'];
        $skipped += (int) $resultado['skipped'];
    }

    return [
        'apoderados' => count($apoderados),
        'created' => $created,
        'skipped' => $skipped,
    ];
}

function cuota_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE cuotas_socios SET apoderado_id = :apoderado_id, periodo = :periodo, '
        . 'fecha_pago = :fecha_pago, monto = :monto, estado = :estado '
        . 'WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'apoderado_id' => $data['apoderado_id'],
        'periodo' => $data['periodo'],
        'fecha_pago' => $data['fecha_pago'] ?: null,
        'monto' => $data['monto'],
        'estado' => $data['estado'],
    ]);
}

function cuota_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM cuotas_socios WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function cuotas_kpi_mensual(string $periodo): array
{
    $periodo = preg_match('/^\d{4}-\d{2}$/', $periodo) ? $periodo : date('Y-m');
    $start = $periodo . '-01';
    $end = date('Y-m-t', strtotime($start));

    $stmtActivos = db()->prepare(
        'SELECT COUNT(DISTINCT d.apoderado_id) '
        . 'FROM inscripciones i '
        . 'INNER JOIN deportistas d ON d.id = i.deportista_id '
        . 'WHERE i.activo = 1 '
        . 'AND i.fecha_inicio <= :end '
        . 'AND (i.fecha_fin IS NULL OR i.fecha_fin >= :start)'
    );
    $stmtActivos->execute([
        'start' => $start,
        'end' => $end,
    ]);
    $apoderadosActivos = (int) ($stmtActivos->fetchColumn() ?? 0);

    $stmtConCuota = db()->prepare(
        'SELECT COUNT(DISTINCT apoderado_id) FROM cuotas_socios WHERE periodo = :periodo'
    );
    $stmtConCuota->execute(['periodo' => $periodo]);
    $apoderadosConCuota = (int) ($stmtConCuota->fetchColumn() ?? 0);

    $stmtEsperados = db()->prepare(
        'SELECT COUNT(DISTINCT t.apoderado_id) FROM ('
        . '  SELECT d.apoderado_id '
        . '  FROM inscripciones i '
        . '  INNER JOIN deportistas d ON d.id = i.deportista_id '
        . '  WHERE i.activo = 1 '
        . '  AND i.fecha_inicio <= :end '
        . '  AND (i.fecha_fin IS NULL OR i.fecha_fin >= :start) '
        . '  UNION '
        . '  SELECT c.apoderado_id '
        . '  FROM cuotas_socios c '
        . '  WHERE c.periodo = :periodo '
        . ') t'
    );
    $stmtEsperados->execute([
        'start' => $start,
        'end' => $end,
        'periodo' => $periodo,
    ]);
    $apoderadosConsiderados = (int) ($stmtEsperados->fetchColumn() ?? 0);

    $stmtCuotas = db()->prepare(
        'SELECT '
        . 'COALESCE(SUM(monto), 0) AS total_registrado, '
        . 'COALESCE(SUM(CASE WHEN estado = "pagado" THEN monto ELSE 0 END), 0) AS total_pagado, '
        . 'COALESCE(SUM(CASE WHEN estado = "pendiente" THEN monto ELSE 0 END), 0) AS total_pendiente '
        . 'FROM cuotas_socios '
        . 'WHERE periodo = :periodo'
    );
    $stmtCuotas->execute(['periodo' => $periodo]);
    $row = $stmtCuotas->fetch() ?: [];

    $valorCuota = 3000.0;
    $esperado = $apoderadosConsiderados * $valorCuota;
    $pagado = (float) ($row['total_pagado'] ?? 0);
    $registrado = (float) ($row['total_registrado'] ?? 0);
    $pendienteRegistrado = (float) ($row['total_pendiente'] ?? 0);
    $pendienteEsperado = max(0.0, $esperado - $pagado);
    $cobertura = $esperado > 0 ? ($pagado / $esperado) * 100 : 0.0;

    return [
        'periodo' => $periodo,
        'apoderados_activos' => $apoderadosActivos,
        'apoderados_con_cuota' => $apoderadosConCuota,
        'apoderados_considerados' => $apoderadosConsiderados,
        'valor_cuota' => $valorCuota,
        'esperado' => $esperado,
        'pagado' => $pagado,
        'registrado' => $registrado,
        'pendiente_registrado' => $pendienteRegistrado,
        'pendiente_esperado' => $pendienteEsperado,
        'cobertura_pct' => $cobertura,
    ];
}
