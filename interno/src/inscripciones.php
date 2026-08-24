<?php

declare(strict_types=1);

function inscripciones_all(): array
{
    $stmt = db()->query(
        'SELECT i.id, i.fecha_inicio, i.fecha_fin, i.activo, '
        . 'd.nombre AS deportista_nombre, a.nombre AS apoderado_nombre, '
        . 'm.nombre AS modalidad_nombre '
        . 'FROM inscripciones i '
        . 'INNER JOIN deportistas d ON d.id = i.deportista_id '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'INNER JOIN modalidades m ON m.id = i.modalidad_id '
        . 'ORDER BY i.fecha_inicio DESC, i.id DESC'
    );
    return $stmt->fetchAll();
}

function inscripcion_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, deportista_id, modalidad_id, fecha_inicio, fecha_fin, activo '
        . 'FROM inscripciones WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function inscripcion_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO inscripciones (deportista_id, modalidad_id, fecha_inicio, fecha_fin, activo) '
        . 'VALUES (:deportista_id, :modalidad_id, :fecha_inicio, :fecha_fin, :activo)'
    );
    $stmt->execute([
        'deportista_id' => $data['deportista_id'],
        'modalidad_id' => $data['modalidad_id'],
        'fecha_inicio' => $data['fecha_inicio'],
        'fecha_fin' => $data['fecha_fin'] ?: null,
        'activo' => $data['activo'],
    ]);

    return (int) db()->lastInsertId();
}

function inscripcion_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE inscripciones SET deportista_id = :deportista_id, modalidad_id = :modalidad_id, '
        . 'fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, activo = :activo '
        . 'WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'deportista_id' => $data['deportista_id'],
        'modalidad_id' => $data['modalidad_id'],
        'fecha_inicio' => $data['fecha_inicio'],
        'fecha_fin' => $data['fecha_fin'] ?: null,
        'activo' => $data['activo'],
    ]);
}

function inscripcion_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM inscripciones WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function modalidades_options(): array
{
    $stmt = db()->query('SELECT id, nombre, costo_mensual FROM modalidades WHERE activo = 1 ORDER BY nombre');
    return $stmt->fetchAll();
}

function modalidades_por_apoderado(int $apoderadoId): array
{
    $stmt = db()->prepare(
        'SELECT DISTINCT m.id, m.nombre, m.costo_mensual, m.coach_id, c.nombre AS coach_nombre, '
        . 'MIN(i.fecha_inicio) AS fecha_inicio, '
        . 'MAX(COALESCE(i.fecha_fin, CURDATE())) AS fecha_fin '
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
    $stmt->execute(['apoderado_id' => $apoderadoId]);
    return $stmt->fetchAll();
}
