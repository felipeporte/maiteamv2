<?php

declare(strict_types=1);

function modalidades_all(): array
{
    $stmt = db()->query(
        'SELECT m.id, m.nombre, m.costo_mensual, m.coach_id, m.activo, c.nombre AS coach_nombre '
        . 'FROM modalidades m INNER JOIN coaches c ON c.id = m.coach_id '
        . 'ORDER BY m.nombre'
    );
    return $stmt->fetchAll();
}

function modalidad_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, costo_mensual, coach_id, activo FROM modalidades WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function modalidad_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO modalidades (nombre, costo_mensual, coach_id, activo) '
        . 'VALUES (:nombre, :costo_mensual, :coach_id, :activo)'
    );
    $stmt->execute([
        'nombre' => $data['nombre'],
        'costo_mensual' => $data['costo_mensual'],
        'coach_id' => $data['coach_id'],
        'activo' => $data['activo'],
    ]);

    return (int) db()->lastInsertId();
}

function modalidad_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE modalidades SET nombre = :nombre, costo_mensual = :costo_mensual, '
        . 'coach_id = :coach_id, activo = :activo WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'nombre' => $data['nombre'],
        'costo_mensual' => $data['costo_mensual'],
        'coach_id' => $data['coach_id'],
        'activo' => $data['activo'],
    ]);
}

function modalidad_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM modalidades WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
