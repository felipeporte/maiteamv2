<?php

declare(strict_types=1);

function asistencia_estados(): array
{
    return [
        'pendiente' => 'Pendiente',
        'presente' => 'Presente',
        'ausente' => 'Ausente',
        'justificada' => 'Justificada',
    ];
}

function asistencia_estado_label(string $estado): string
{
    $labels = asistencia_estados();

    return $labels[$estado] ?? ucfirst($estado);
}

function asistencia_clases_por_fecha(string $fecha, int $eventoId = 0, string $nivel = ''): array
{
    $conditions = ['c.fecha = :fecha']; $params = ['fecha' => $fecha];
    if ($eventoId > 0) { $conditions[] = 'ce.evento_federado_id = :evento_id'; $params['evento_id'] = $eventoId; }
    if ($nivel !== '') { $conditions[] = 'ef.nivel = :nivel'; $params['nivel'] = $nivel; }
    $stmt = db()->prepare(
        'SELECT c.id, c.fecha, c.estado, c.asistencia, c.asistencia_notas, ce.evento_federado_id, ef.nombre AS evento_nombre, ef.nivel AS evento_nivel, '
        . 'd.nombre AS deportista_nombre, a.nombre AS apoderado_nombre, co.nombre AS coach_nombre '
        . 'FROM clases c '
        . 'INNER JOIN deportistas d ON d.id = c.deportista_id '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'INNER JOIN coaches co ON co.id = c.coach_id '
        . 'LEFT JOIN clases_extras ce ON ce.id = c.clase_extra_id '
        . 'LEFT JOIN eventos_federados ef ON ef.id = ce.evento_federado_id '
        . 'WHERE ' . implode(' AND ', $conditions) . ' '
        . 'ORDER BY co.nombre ASC, d.nombre ASC, c.id ASC'
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function asistencia_resumen_por_fecha(string $fecha, int $eventoId = 0, string $nivel = ''): array
{
    $conditions = ['c.fecha = :fecha']; $params = ['fecha' => $fecha];
    if ($eventoId > 0) { $conditions[] = 'ce.evento_federado_id = :evento_id'; $params['evento_id'] = $eventoId; }
    if ($nivel !== '') { $conditions[] = 'ef.nivel = :nivel'; $params['nivel'] = $nivel; }
    $stmt = db()->prepare(
        'SELECT COUNT(*) AS total, '
        . 'COALESCE(SUM(CASE WHEN asistencia = "presente" THEN 1 ELSE 0 END), 0) AS presentes, '
        . 'COALESCE(SUM(CASE WHEN asistencia = "ausente" THEN 1 ELSE 0 END), 0) AS ausentes, '
        . 'COALESCE(SUM(CASE WHEN asistencia = "justificada" THEN 1 ELSE 0 END), 0) AS justificadas, '
        . 'COALESCE(SUM(CASE WHEN asistencia = "pendiente" THEN 1 ELSE 0 END), 0) AS pendientes '
        . 'FROM clases c LEFT JOIN clases_extras ce ON ce.id = c.clase_extra_id '
        . 'LEFT JOIN eventos_federados ef ON ef.id = ce.evento_federado_id '
        . 'WHERE ' . implode(' AND ', $conditions)
    );
    $stmt->execute($params);
    $row = $stmt->fetch();

    return $row ?: [
        'total' => 0,
        'presentes' => 0,
        'ausentes' => 0,
        'justificadas' => 0,
        'pendientes' => 0,
    ];
}

function asistencia_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE clases SET asistencia = :asistencia, asistencia_notas = :asistencia_notas '
        . 'WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'asistencia' => $data['asistencia'],
        'asistencia_notas' => $data['asistencia_notas'] !== '' ? $data['asistencia_notas'] : null,
    ]);
}
