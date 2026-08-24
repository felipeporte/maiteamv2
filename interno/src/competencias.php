<?php

declare(strict_types=1);

function competencias_all(): array
{
    $stmt = db()->query(
        'SELECT c.id, c.nivel_id, c.nombre, c.fecha_inicio, c.fecha_fin, c.lugar, c.observaciones, '
        . 'n.nombre AS nivel_nombre '
        . 'FROM competencias c '
        . 'INNER JOIN niveles_deportivos n ON n.id = c.nivel_id '
        . 'ORDER BY c.fecha_inicio DESC, c.id DESC'
    );

    return $stmt->fetchAll();
}

function competencia_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nivel_id, nombre, fecha_inicio, fecha_fin, lugar, observaciones '
        . 'FROM competencias WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function competencia_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO competencias (nivel_id, nombre, fecha_inicio, fecha_fin, lugar, observaciones) '
        . 'VALUES (:nivel_id, :nombre, :fecha_inicio, :fecha_fin, :lugar, :observaciones)'
    );
    $stmt->execute([
        'nivel_id' => $data['nivel_id'],
        'nombre' => $data['nombre'],
        'fecha_inicio' => $data['fecha_inicio'],
        'fecha_fin' => $data['fecha_fin'] ?: null,
        'lugar' => $data['lugar'] ?: null,
        'observaciones' => $data['observaciones'] ?: null,
    ]);

    return (int) db()->lastInsertId();
}

function competencia_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE competencias SET nivel_id = :nivel_id, nombre = :nombre, '
        . 'fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, lugar = :lugar, observaciones = :observaciones '
        . 'WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'nivel_id' => $data['nivel_id'],
        'nombre' => $data['nombre'],
        'fecha_inicio' => $data['fecha_inicio'],
        'fecha_fin' => $data['fecha_fin'] ?: null,
        'lugar' => $data['lugar'] ?: null,
        'observaciones' => $data['observaciones'] ?: null,
    ]);
}

function competencia_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM competencias WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function competencias_por_deportista(int $deportistaId): array
{
    $stmt = db()->prepare(
        'SELECT c.id, c.nivel_id, c.nombre, c.fecha_inicio, c.fecha_fin, c.lugar, c.observaciones '
        . 'FROM competencias c '
        . 'INNER JOIN deportistas d ON d.id = :deportista_id '
        . 'WHERE d.nivel_id IS NOT NULL AND c.nivel_id = d.nivel_id '
        . 'ORDER BY c.fecha_inicio DESC, c.id DESC'
    );
    $stmt->execute(['deportista_id' => $deportistaId]);

    return $stmt->fetchAll();
}

function competencia_find_por_deportista(int $competenciaId, int $deportistaId): ?array
{
    $stmt = db()->prepare(
        'SELECT c.id, c.nivel_id, c.nombre, c.fecha_inicio, c.fecha_fin, c.lugar, c.observaciones '
        . 'FROM competencias c '
        . 'INNER JOIN deportistas d ON d.id = :deportista_id '
        . 'WHERE c.id = :id AND d.nivel_id IS NOT NULL AND c.nivel_id = d.nivel_id'
    );
    $stmt->execute([
        'id' => $competenciaId,
        'deportista_id' => $deportistaId,
    ]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function niveles_competencias_options(): array
{
    $stmt = db()->query('SELECT id, nombre FROM niveles_deportivos ORDER BY nombre');

    return $stmt->fetchAll();
}
