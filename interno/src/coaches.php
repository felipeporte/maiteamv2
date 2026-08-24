<?php

declare(strict_types=1);

function coaches_all(): array
{
    $stmt = db()->query(
        'SELECT id, nombre, telefono, email, especialidad, activo FROM coaches ORDER BY nombre'
    );
    return $stmt->fetchAll();
}

function coach_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, telefono, email, especialidad, activo FROM coaches WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function coach_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO coaches (nombre, telefono, email, especialidad, activo) '
        . 'VALUES (:nombre, :telefono, :email, :especialidad, :activo)'
    );
    $stmt->execute([
        'nombre' => $data['nombre'],
        'telefono' => $data['telefono'],
        'email' => $data['email'],
        'especialidad' => $data['especialidad'],
        'activo' => $data['activo'],
    ]);

    return (int) db()->lastInsertId();
}

function coach_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE coaches SET nombre = :nombre, telefono = :telefono, email = :email, '
        . 'especialidad = :especialidad, activo = :activo WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'nombre' => $data['nombre'],
        'telefono' => $data['telefono'],
        'email' => $data['email'],
        'especialidad' => $data['especialidad'],
        'activo' => $data['activo'],
    ]);
}

function coach_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM coaches WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
