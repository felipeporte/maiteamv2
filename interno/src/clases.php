<?php

declare(strict_types=1);

function clases_all(): array
{
    $stmt = db()->query(
        'SELECT c.id, c.fecha, c.duracion_min, c.tarifa, c.estado, c.notas, '
        . 'd.nombre AS deportista_nombre, a.nombre AS apoderado_nombre, '
        . 'co.nombre AS coach_nombre '
        . 'FROM clases c '
        . 'INNER JOIN deportistas d ON d.id = c.deportista_id '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'INNER JOIN coaches co ON co.id = c.coach_id '
        . 'ORDER BY c.fecha DESC, c.id DESC'
    );
    return $stmt->fetchAll();
}

function clase_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, deportista_id, coach_id, fecha, duracion_min, tarifa, estado, notas '
        . 'FROM clases WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function clase_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO clases (deportista_id, coach_id, fecha, duracion_min, tarifa, estado, notas) '
        . 'VALUES (:deportista_id, :coach_id, :fecha, :duracion_min, :tarifa, :estado, :notas)'
    );
    $stmt->execute([
        'deportista_id' => $data['deportista_id'],
        'coach_id' => $data['coach_id'],
        'fecha' => $data['fecha'],
        'duracion_min' => $data['duracion_min'] ?: null,
        'tarifa' => $data['tarifa'],
        'estado' => $data['estado'],
        'notas' => $data['notas'],
    ]);

    return (int) db()->lastInsertId();
}

function clase_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE clases SET deportista_id = :deportista_id, coach_id = :coach_id, fecha = :fecha, '
        . 'duracion_min = :duracion_min, tarifa = :tarifa, estado = :estado, notas = :notas '
        . 'WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'deportista_id' => $data['deportista_id'],
        'coach_id' => $data['coach_id'],
        'fecha' => $data['fecha'],
        'duracion_min' => $data['duracion_min'] ?: null,
        'tarifa' => $data['tarifa'],
        'estado' => $data['estado'],
        'notas' => $data['notas'],
    ]);
}

function clase_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM clases WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function deportistas_options(): array
{
    $stmt = db()->query(
        'SELECT d.id, d.nombre, a.nombre AS apoderado_nombre '
        . 'FROM deportistas d INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'WHERE d.activo = 1 ORDER BY d.nombre'
    );
    return $stmt->fetchAll();
}

function coaches_options(): array
{
    $stmt = db()->query(
        'SELECT id, nombre FROM coaches WHERE activo = 1 ORDER BY nombre'
    );
    return $stmt->fetchAll();
}
