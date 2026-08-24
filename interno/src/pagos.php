<?php

declare(strict_types=1);

function pagos_all(): array
{
    $stmt = db()->query(
        'SELECT p.id, p.fecha_pago, p.periodo_inicio, p.periodo_fin, p.monto_total, '
        . 'a.nombre AS apoderado_nombre, c.nombre AS coach_nombre, '
        . '(SELECT COUNT(*) FROM pagos_clases pc WHERE pc.pago_id = p.id) AS clases_total '
        . 'FROM pagos p '
        . 'INNER JOIN apoderados a ON a.id = p.apoderado_id '
        . 'INNER JOIN coaches c ON c.id = p.coach_id '
        . 'ORDER BY p.fecha_pago DESC, p.id DESC'
    );
    return $stmt->fetchAll();
}

function pago_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, apoderado_id, coach_id, periodo_inicio, periodo_fin, fecha_pago, '
        . 'monto_total, metodo, referencia '
        . 'FROM pagos WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function pago_clases_ids(int $pagoId): array
{
    $stmt = db()->prepare('SELECT clase_id FROM pagos_clases WHERE pago_id = :pago_id');
    $stmt->execute(['pago_id' => $pagoId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function clases_disponibles_para_pago(?int $apoderadoId, ?int $coachId, array $includeIds = []): array
{
    $conditions = ["c.estado = 'realizada'"];
    $params = [];

    if ($apoderadoId) {
        $conditions[] = 'a.id = :apoderado_id';
        $params['apoderado_id'] = $apoderadoId;
    }

    if ($coachId) {
        $conditions[] = 'co.id = :coach_id';
        $params['coach_id'] = $coachId;
    }

    $includeIds = array_values(array_filter(array_map('intval', $includeIds)));

    if (!empty($includeIds)) {
        $placeholders = implode(', ', array_fill(0, count($includeIds), '?'));
        $conditions[] = '(pc.clase_id IS NULL OR c.id IN (' . $placeholders . '))';
        $params = array_merge($params, $includeIds);
    } else {
        $conditions[] = 'pc.clase_id IS NULL';
    }

    $where = implode(' AND ', $conditions);

    $sql = 'SELECT c.id, c.fecha, c.tarifa, d.nombre AS deportista_nombre, '
        . 'a.nombre AS apoderado_nombre, co.nombre AS coach_nombre '
        . 'FROM clases c '
        . 'INNER JOIN deportistas d ON d.id = c.deportista_id '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'INNER JOIN coaches co ON co.id = c.coach_id '
        . 'LEFT JOIN pagos_clases pc ON pc.clase_id = c.id '
        . 'WHERE ' . $where . ' '
        . 'ORDER BY c.fecha DESC, c.id DESC';

    $stmt = db()->prepare($sql);

    $index = 1;
    foreach ($params as $key => $value) {
        if (is_int($key)) {
            $stmt->bindValue($index, $value, PDO::PARAM_INT);
            $index++;
        } else {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
    }

    $stmt->execute();
    return $stmt->fetchAll();
}

function pago_create(array $data, array $classIds): int
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $classIds = array_values(array_filter(array_map('intval', $classIds)));
        $montoTotal = (float) $data['monto_total'];

        if ($montoTotal <= 0 && !empty($classIds)) {
            $placeholders = implode(', ', array_fill(0, count($classIds), '?'));
            $stmt = $pdo->prepare('SELECT SUM(tarifa) FROM clases WHERE id IN (' . $placeholders . ')');
            $stmt->execute($classIds);
            $montoTotal = (float) $stmt->fetchColumn();
        }

        $stmt = $pdo->prepare(
            'INSERT INTO pagos (apoderado_id, coach_id, periodo_inicio, periodo_fin, fecha_pago, '
            . 'monto_total, metodo, referencia) '
            . 'VALUES (:apoderado_id, :coach_id, :periodo_inicio, :periodo_fin, :fecha_pago, '
            . ':monto_total, :metodo, :referencia)'
        );
        $stmt->execute([
            'apoderado_id' => $data['apoderado_id'],
            'coach_id' => $data['coach_id'],
            'periodo_inicio' => $data['periodo_inicio'] ?: null,
            'periodo_fin' => $data['periodo_fin'] ?: null,
            'fecha_pago' => $data['fecha_pago'],
            'monto_total' => $montoTotal,
            'metodo' => $data['metodo'],
            'referencia' => $data['referencia'],
        ]);

        $pagoId = (int) $pdo->lastInsertId();

        if (!empty($classIds)) {
            $stmt = $pdo->prepare(
                'INSERT INTO pagos_clases (pago_id, clase_id, monto) VALUES (:pago_id, :clase_id, :monto)'
            );
            $tarifas = clases_tarifas($classIds);
            foreach ($classIds as $classId) {
                $stmt->execute([
                    'pago_id' => $pagoId,
                    'clase_id' => $classId,
                    'monto' => $tarifas[$classId] ?? 0,
                ]);
            }
        }

        $pdo->commit();
        return $pagoId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function pago_update(int $id, array $data, array $classIds): void
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $classIds = array_values(array_filter(array_map('intval', $classIds)));
        $montoTotal = (float) $data['monto_total'];

        if ($montoTotal <= 0 && !empty($classIds)) {
            $placeholders = implode(', ', array_fill(0, count($classIds), '?'));
            $stmt = $pdo->prepare('SELECT SUM(tarifa) FROM clases WHERE id IN (' . $placeholders . ')');
            $stmt->execute($classIds);
            $montoTotal = (float) $stmt->fetchColumn();
        }

        $stmt = $pdo->prepare(
            'UPDATE pagos SET apoderado_id = :apoderado_id, coach_id = :coach_id, '
            . 'periodo_inicio = :periodo_inicio, periodo_fin = :periodo_fin, fecha_pago = :fecha_pago, '
            . 'monto_total = :monto_total, metodo = :metodo, referencia = :referencia '
            . 'WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'apoderado_id' => $data['apoderado_id'],
            'coach_id' => $data['coach_id'],
            'periodo_inicio' => $data['periodo_inicio'] ?: null,
            'periodo_fin' => $data['periodo_fin'] ?: null,
            'fecha_pago' => $data['fecha_pago'],
            'monto_total' => $montoTotal,
            'metodo' => $data['metodo'],
            'referencia' => $data['referencia'],
        ]);

        $pdo->prepare('DELETE FROM pagos_clases WHERE pago_id = :pago_id')
            ->execute(['pago_id' => $id]);

        if (!empty($classIds)) {
            $stmt = $pdo->prepare(
                'INSERT INTO pagos_clases (pago_id, clase_id, monto) VALUES (:pago_id, :clase_id, :monto)'
            );
            $tarifas = clases_tarifas($classIds);
            foreach ($classIds as $classId) {
                $stmt->execute([
                    'pago_id' => $id,
                    'clase_id' => $classId,
                    'monto' => $tarifas[$classId] ?? 0,
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function pago_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM pagos WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function clases_tarifas(array $classIds): array
{
    if (empty($classIds)) {
        return [];
    }

    $placeholders = implode(', ', array_fill(0, count($classIds), '?'));
    $stmt = db()->prepare('SELECT id, tarifa FROM clases WHERE id IN (' . $placeholders . ')');
    $stmt->execute($classIds);

    $rows = $stmt->fetchAll();
    $tarifas = [];
    foreach ($rows as $row) {
        $tarifas[(int) $row['id']] = (float) $row['tarifa'];
    }

    return $tarifas;
}
