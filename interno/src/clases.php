<?php

declare(strict_types=1);

function clases_all(): array
{
    $stmt = db()->query(
        'SELECT c.id, c.fecha, c.duracion_min, c.tarifa, c.estado, c.notas, c.clase_extra_id, '
        . 'd.nombre AS deportista_nombre, a.nombre AS apoderado_nombre, '
        . 'co.nombre AS coach_nombre, ce.id AS extra_id, ce.valor_clase, ce.costo_pista, '
        . 'cp.nombre AS competencia_nombre, ef.nombre AS evento_federado_nombre, ef.nivel AS evento_federado_nivel '
        . 'FROM clases c '
        . 'INNER JOIN deportistas d ON d.id = c.deportista_id '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'INNER JOIN coaches co ON co.id = c.coach_id '
        . 'LEFT JOIN clases_extras ce ON ce.id = c.clase_extra_id '
        . 'LEFT JOIN competencias cp ON cp.id = ce.competencia_id '
        . 'LEFT JOIN eventos_federados ef ON ef.id = ce.evento_federado_id '
        . 'ORDER BY c.fecha DESC, c.id DESC'
    );
    return $stmt->fetchAll();
}

function clases_extras_all(): array
{
    $stmt = db()->query(
        'SELECT ce.*, co.nombre AS coach_nombre, cp.nombre AS competencia_nombre, ef.nombre AS evento_federado_nombre, ef.nivel AS evento_federado_nivel, '
        . '(SELECT COUNT(*) FROM clases c WHERE c.clase_extra_id = ce.id) AS participantes '
        . 'FROM clases_extras ce INNER JOIN coaches co ON co.id = ce.coach_id '
        . 'LEFT JOIN competencias cp ON cp.id = ce.competencia_id '
        . 'LEFT JOIN eventos_federados ef ON ef.id = ce.evento_federado_id '
        . 'ORDER BY ce.fecha DESC, ce.id DESC'
    );
    return $stmt->fetchAll();
}

function clases_extras_abiertas_all(): array
{
    $stmt = db()->query(
        'SELECT ce.*, co.nombre AS coach_nombre, ef.nombre AS evento_federado_nombre, ef.nivel AS evento_federado_nivel, '
        . '(SELECT COUNT(*) FROM clases c WHERE c.clase_extra_id = ce.id) AS participantes '
        . 'FROM clases_extras ce INNER JOIN coaches co ON co.id = ce.coach_id '
        . 'LEFT JOIN eventos_federados ef ON ef.id = ce.evento_federado_id '
        . 'WHERE ce.estado = "programada" ORDER BY ce.fecha ASC, ce.id ASC'
    );
    return $stmt->fetchAll();
}

function clase_extra_cerrar(int $id, float $costoPista): void
{
    if ($id <= 0 || $costoPista < 0) throw new InvalidArgumentException('El valor de cancha no es válido.');
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, valor_clase, estado FROM clases_extras WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $id]);
        $extra = $stmt->fetch();
        if (!$extra) throw new InvalidArgumentException('No se encontró la sesión extra.');
        if ($extra['estado'] !== 'programada') throw new InvalidArgumentException('La sesión ya está cerrada.');
        $stmt = $pdo->prepare('SELECT id FROM clases WHERE clase_extra_id = :id ORDER BY id ASC FOR UPDATE');
        $stmt->execute(['id' => $id]); $clases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!$clases) throw new InvalidArgumentException('La sesión no tiene deportistas seleccionadas.');
        $base = round($costoPista / count($clases), 2); $asignado = 0.0;
        $update = $pdo->prepare('UPDATE clases SET prorrateo_pista = :pista, tarifa = :tarifa, estado = "realizada" WHERE id = :id');
        foreach ($clases as $index => $claseId) {
            $pista = $index === count($clases) - 1 ? round($costoPista - $asignado, 2) : $base;
            $asignado += $pista;
            $update->execute(['id' => $claseId, 'pista' => $pista, 'tarifa' => (float) $extra['valor_clase'] + $pista]);
        }
        $updateExtra = $pdo->prepare('UPDATE clases_extras SET costo_pista = :pista, estado = "realizada" WHERE id = :id');
        $updateExtra->execute(['id' => $id, 'pista' => $costoPista]);
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
}

function clases_extras_create(array $data, array $deportistaIds): int
{
    $deportistaIds = array_values(array_unique(array_filter(array_map('intval', $deportistaIds))));
    if (empty($deportistaIds)) {
        throw new InvalidArgumentException('Selecciona al menos una deportista.');
    }
    if ((int) ($data['evento_federado_id'] ?? 0) > 0) {
        $placeholders = implode(', ', array_fill(0, count($deportistaIds), '?'));
        $stmt = db()->prepare('SELECT COUNT(DISTINCT ei.deportista_id) FROM evento_federado_inscripciones ei INNER JOIN eventos_federados ef ON ef.id = ei.evento_id WHERE ei.evento_id = ? AND ei.deportista_id IN (' . $placeholders . ') AND ei.estado_pago <> "anulado"');
        $stmt->execute(array_merge([(int) $data['evento_federado_id']], $deportistaIds));
        if ((int) $stmt->fetchColumn() !== count($deportistaIds)) {
            throw new InvalidArgumentException('Todas las deportistas deben pertenecer al nivel de la competencia seleccionada.');
        }
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $data['valor_clase'] = 10000.00;
        $stmt = $pdo->prepare('INSERT INTO clases_extras (competencia_id, evento_federado_id, coach_id, fecha, duracion_min, valor_clase, costo_pista, estado, notas) VALUES (:competencia_id, :evento_federado_id, :coach_id, :fecha, :duracion_min, :valor_clase, :costo_pista, :estado, :notas)');
        $stmt->execute([
            'competencia_id' => (int) ($data['competencia_id'] ?? 0) ?: null,
            'evento_federado_id' => (int) ($data['evento_federado_id'] ?? 0) ?: null,
            'coach_id' => $data['coach_id'], 'fecha' => $data['fecha'],
            'duracion_min' => $data['duracion_min'] ?: null, 'valor_clase' => $data['valor_clase'],
            'costo_pista' => $data['costo_pista'], 'estado' => $data['estado'], 'notas' => $data['notas'] ?: null,
        ]);
        $extraId = (int) $pdo->lastInsertId();
        $pistaTotal = round((float) $data['costo_pista'], 2);
        $pistaBase = round($pistaTotal / count($deportistaIds), 2);
        $pistaAsignada = 0.0;
        $stmt = $pdo->prepare('INSERT INTO clases (deportista_id, coach_id, fecha, duracion_min, tarifa, estado, notas, clase_extra_id, valor_clase, prorrateo_pista) VALUES (:deportista_id, :coach_id, :fecha, :duracion_min, :tarifa, :estado, :notas, :extra_id, :valor_clase, :prorrateo_pista)');
        foreach ($deportistaIds as $index => $deportistaId) {
            $prorrateoPista = $index === count($deportistaIds) - 1
                ? round($pistaTotal - $pistaAsignada, 2)
                : $pistaBase;
            $pistaAsignada += $prorrateoPista;
            $stmt->execute([
                'deportista_id' => $deportistaId, 'coach_id' => $data['coach_id'], 'fecha' => $data['fecha'],
                'duracion_min' => $data['duracion_min'] ?: null, 'tarifa' => (float) $data['valor_clase'] + $prorrateoPista,
                'estado' => $data['estado'], 'notas' => $data['notas'] ?: null, 'extra_id' => $extraId,
                'valor_clase' => $data['valor_clase'], 'prorrateo_pista' => $prorrateoPista,
            ]);
        }
        $pdo->commit();
        return $extraId;
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
}

function competencias_options(): array
{
    return db()->query('SELECT c.id, c.nombre, c.fecha_inicio, c.nivel_id, n.nombre AS nivel_nombre FROM competencias c INNER JOIN niveles_deportivos n ON n.id = c.nivel_id ORDER BY c.fecha_inicio DESC, c.nombre')->fetchAll();
}

function eventos_federados_clases_options(): array
{
    return db()->query("SELECT id, nombre, nivel, fecha_inicio, fecha_fin FROM eventos_federados WHERE estado <> 'finalizado' ORDER BY fecha_inicio DESC, nombre")->fetchAll();
}

function deportistas_evento_federado_options(int $eventoId): array
{
    if ($eventoId <= 0) return [];
    $stmt = db()->prepare('SELECT DISTINCT d.id, d.nombre, a.nombre AS apoderado_nombre, ef.nivel FROM evento_federado_inscripciones ei INNER JOIN deportistas d ON d.id = ei.deportista_id INNER JOIN apoderados a ON a.id = d.apoderado_id INNER JOIN eventos_federados ef ON ef.id = ei.evento_id WHERE ei.evento_id = :evento_id AND ei.estado_pago <> "anulado" AND d.activo = 1 ORDER BY d.nombre');
    $stmt->execute(['evento_id' => $eventoId]);
    return $stmt->fetchAll();
}

function deportistas_nivel_extra_options(string $nivel): array
{
    $stmt = db()->prepare('SELECT DISTINCT d.id, d.nombre, a.nombre AS apoderado_nombre, dmc.nivel FROM deportistas d INNER JOIN apoderados a ON a.id = d.apoderado_id INNER JOIN deportista_modalidades_competencia dmc ON dmc.deportista_id = d.id WHERE d.activo = 1 AND dmc.nivel = :nivel ORDER BY d.nombre');
    $stmt->execute(['nivel' => $nivel]);
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
