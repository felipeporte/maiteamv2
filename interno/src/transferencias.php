<?php

declare(strict_types=1);

function transferencias_all(): array
{
    $stmt = db()->query(
        'SELECT t.id, t.periodo, t.fecha_transferencia, t.monto, t.metodo, t.referencia, '
        . 'c.nombre AS coach_nombre '
        . 'FROM transferencias_coaches t '
        . 'INNER JOIN coaches c ON c.id = t.coach_id '
        . 'ORDER BY t.fecha_transferencia DESC, t.id DESC'
    );
    return $stmt->fetchAll();
}

function transferencia_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, coach_id, periodo, fecha_transferencia, monto, metodo, referencia '
        . 'FROM transferencias_coaches WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function transferencia_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO transferencias_coaches (coach_id, periodo, fecha_transferencia, monto, metodo, referencia) '
        . 'VALUES (:coach_id, :periodo, :fecha_transferencia, :monto, :metodo, :referencia)'
    );
    $stmt->execute([
        'coach_id' => $data['coach_id'],
        'periodo' => $data['periodo'] ?: null,
        'fecha_transferencia' => $data['fecha_transferencia'],
        'monto' => $data['monto'],
        'metodo' => $data['metodo'],
        'referencia' => $data['referencia'],
    ]);

    return (int) db()->lastInsertId();
}

function transferencia_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE transferencias_coaches SET coach_id = :coach_id, periodo = :periodo, '
        . 'fecha_transferencia = :fecha_transferencia, monto = :monto, metodo = :metodo, referencia = :referencia '
        . 'WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'coach_id' => $data['coach_id'],
        'periodo' => $data['periodo'] ?: null,
        'fecha_transferencia' => $data['fecha_transferencia'],
        'monto' => $data['monto'],
        'metodo' => $data['metodo'],
        'referencia' => $data['referencia'],
    ]);
}

function transferencia_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM transferencias_coaches WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
