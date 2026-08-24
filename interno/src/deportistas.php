<?php

declare(strict_types=1);

function deportistas_all(): array
{
    $stmt = db()->query(
        'SELECT d.id, d.nombre, d.fecha_nacimiento, d.categoria, d.rut, d.nivel_id, d.activo, '
        . 'a.nombre AS apoderado_nombre, n.nombre AS nivel_nombre '
        . 'FROM deportistas d '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'LEFT JOIN niveles_deportivos n ON n.id = d.nivel_id '
        . 'ORDER BY d.nombre'
    );
    return $stmt->fetchAll();
}

function deportista_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, apoderado_id, nombre, fecha_nacimiento, categoria, rut, nivel_id, activo '
        . 'FROM deportistas WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function deportista_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO deportistas (apoderado_id, nombre, fecha_nacimiento, categoria, rut, nivel_id, activo) '
        . 'VALUES (:apoderado_id, :nombre, :fecha_nacimiento, :categoria, :rut, :nivel_id, :activo)'
    );
    $stmt->execute([
        'apoderado_id' => $data['apoderado_id'],
        'nombre' => $data['nombre'],
        'fecha_nacimiento' => $data['fecha_nacimiento'] ?: null,
        'categoria' => $data['categoria'],
        'rut' => $data['rut'] ?: null,
        'nivel_id' => $data['nivel_id'] > 0 ? $data['nivel_id'] : null,
        'activo' => $data['activo'],
    ]);

    return (int) db()->lastInsertId();
}

function deportista_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE deportistas SET apoderado_id = :apoderado_id, nombre = :nombre, '
        . 'fecha_nacimiento = :fecha_nacimiento, categoria = :categoria, rut = :rut, '
        . 'nivel_id = :nivel_id, activo = :activo '
        . 'WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'apoderado_id' => $data['apoderado_id'],
        'nombre' => $data['nombre'],
        'fecha_nacimiento' => $data['fecha_nacimiento'] ?: null,
        'categoria' => $data['categoria'],
        'rut' => $data['rut'] ?: null,
        'nivel_id' => $data['nivel_id'] > 0 ? $data['nivel_id'] : null,
        'activo' => $data['activo'],
    ]);
}

function deportista_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM deportistas WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function niveles_all(): array
{
    $stmt = db()->query('SELECT id, nombre FROM niveles_deportivos ORDER BY nombre');
    return $stmt->fetchAll();
}

function deportista_exists_by_rut(string $rut, int $excludeId = 0): bool
{
    $normalizedRut = normalize_rut($rut);
    if ($normalizedRut === '') {
        return false;
    }

    $sql = 'SELECT id FROM deportistas '
        . 'WHERE REPLACE(REPLACE(UPPER(rut), ".", ""), "-", "") = :rut';
    $params = ['rut' => $normalizedRut];

    if ($excludeId > 0) {
        $sql .= ' AND id <> :id';
        $params['id'] = $excludeId;
    }

    $sql .= ' LIMIT 1';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return (bool) $stmt->fetchColumn();
}
