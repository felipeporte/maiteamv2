<?php

declare(strict_types=1);

function eventos_federados_schema_ready(): bool
{
    static $ready = null;

    if ($ready !== null) {
        return $ready;
    }

    if (!modalidades_competencia_schema_ready()) {
        $ready = false;
        return $ready;
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) '
        . 'FROM information_schema.tables '
        . 'WHERE table_schema = DATABASE() '
        . 'AND table_name IN ("eventos_federados", "evento_federado_inscripciones")'
    );
    $stmt->execute();

    if ((int) $stmt->fetchColumn() !== 2) {
        $ready = false;
        return $ready;
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) '
        . 'FROM information_schema.columns '
        . 'WHERE table_schema = DATABASE() '
        . 'AND table_name = "evento_federado_inscripciones" '
        . 'AND column_name IN ('
        . '    "deportista_modalidades_competencia_id", '
        . '    "modalidad_competencia_id", '
        . '    "subnivel", '
        . '    "categoria"'
        . ')'
    );
    $stmt->execute();

    $ready = (int) $stmt->fetchColumn() === 4;

    return $ready;
}

function eventos_federados_all(): array
{
    if (!eventos_federados_schema_ready()) {
        return [];
    }

    $stmt = db()->query(
        'SELECT e.id, e.nombre, e.nivel, e.fecha_inicio, e.fecha_fin, e.lugar, e.costo_inscripcion, e.cupo, '
        . 'e.estado, e.observaciones, '
        . 'COALESCE(s.inscritos_count, 0) AS inscritos_count, '
        . 'COALESCE(s.pagados_count, 0) AS pagados_count, '
        . 'COALESCE(s.monto_total, 0) AS monto_total '
        . 'FROM eventos_federados e '
        . 'LEFT JOIN ('
        . '    SELECT evento_id, '
        . '           COUNT(*) AS inscritos_count, '
        . '           SUM(CASE WHEN estado_pago = "pagado" THEN 1 ELSE 0 END) AS pagados_count, '
        . '           SUM(CASE WHEN estado_pago <> "anulado" THEN monto ELSE 0 END) AS monto_total '
        . '    FROM evento_federado_inscripciones '
        . '    GROUP BY evento_id'
        . ') s ON s.evento_id = e.id '
        . 'ORDER BY e.fecha_inicio DESC, e.id DESC'
    );

    return $stmt->fetchAll();
}

function evento_federado_find(int $id): ?array
{
    if (!eventos_federados_schema_ready()) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT e.id, e.nombre, e.nivel, e.fecha_inicio, e.fecha_fin, e.lugar, e.costo_inscripcion, e.cupo, '
        . 'e.estado, e.observaciones '
        . 'FROM eventos_federados e '
        . 'WHERE e.id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function evento_federado_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO eventos_federados '
        . '(nombre, nivel, fecha_inicio, fecha_fin, lugar, costo_inscripcion, cupo, estado, observaciones) '
        . 'VALUES '
        . '(:nombre, :nivel, :fecha_inicio, :fecha_fin, :lugar, :costo_inscripcion, :cupo, :estado, :observaciones)'
    );
    $stmt->execute([
        'nombre' => $data['nombre'],
        'nivel' => $data['nivel'],
        'fecha_inicio' => $data['fecha_inicio'],
        'fecha_fin' => $data['fecha_fin'] !== '' ? $data['fecha_fin'] : null,
        'lugar' => $data['lugar'] !== '' ? $data['lugar'] : null,
        'costo_inscripcion' => $data['costo_inscripcion'],
        'cupo' => $data['cupo'] > 0 ? $data['cupo'] : null,
        'estado' => $data['estado'],
        'observaciones' => $data['observaciones'] !== '' ? $data['observaciones'] : null,
    ]);

    return (int) db()->lastInsertId();
}

function evento_federado_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE eventos_federados SET '
        . 'nombre = :nombre, '
        . 'nivel = :nivel, '
        . 'fecha_inicio = :fecha_inicio, '
        . 'fecha_fin = :fecha_fin, '
        . 'lugar = :lugar, '
        . 'costo_inscripcion = :costo_inscripcion, '
        . 'cupo = :cupo, '
        . 'estado = :estado, '
        . 'observaciones = :observaciones '
        . 'WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'nombre' => $data['nombre'],
        'nivel' => $data['nivel'],
        'fecha_inicio' => $data['fecha_inicio'],
        'fecha_fin' => $data['fecha_fin'] !== '' ? $data['fecha_fin'] : null,
        'lugar' => $data['lugar'] !== '' ? $data['lugar'] : null,
        'costo_inscripcion' => $data['costo_inscripcion'],
        'cupo' => $data['cupo'] > 0 ? $data['cupo'] : null,
        'estado' => $data['estado'],
        'observaciones' => $data['observaciones'] !== '' ? $data['observaciones'] : null,
    ]);
}

function evento_federado_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM eventos_federados WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function evento_federado_inscripciones_all(int $eventoId): array
{
    if (!eventos_federados_schema_ready()) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT ei.id, ei.evento_id, ei.deportista_id, ei.deportista_modalidades_competencia_id, '
        . 'ei.modalidad_competencia_id, ei.subnivel, ei.categoria, ei.apoderado_id, ei.fecha_inscripcion, ei.monto, '
        . 'ei.estado_pago, ei.referencia, ei.observaciones, '
        . 'd.nombre AS deportista_nombre, d.rut AS deportista_rut, '
        . 'mc.nombre AS modalidad_nombre, mc.codigo AS modalidad_codigo, '
        . 'a.nombre AS apoderado_nombre '
        . 'FROM evento_federado_inscripciones ei '
        . 'INNER JOIN deportistas d ON d.id = ei.deportista_id '
        . 'INNER JOIN apoderados a ON a.id = ei.apoderado_id '
        . 'LEFT JOIN deportista_modalidades_competencia dmc ON dmc.id = ei.deportista_modalidades_competencia_id '
        . 'LEFT JOIN modalidades_competencia mc ON mc.id = COALESCE(ei.modalidad_competencia_id, dmc.modalidad_competencia_id) '
        . 'WHERE ei.evento_id = :evento_id '
        . 'ORDER BY d.nombre ASC, ei.id ASC'
    );
    $stmt->execute(['evento_id' => $eventoId]);

    return $stmt->fetchAll();
}

function evento_federado_deportistas_elegibles(array $evento): array
{
    if (!eventos_federados_schema_ready()) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT d.id AS deportista_id, d.nombre AS deportista_nombre, d.rut, d.fecha_nacimiento, d.categoria, d.activo, '
        . 'a.nombre AS apoderado_nombre, '
        . 'dmc.id AS asignacion_id, dmc.modalidad_competencia_id, mc.nombre AS modalidad_nombre, mc.codigo AS modalidad_codigo, '
        . 'mc.orden AS modalidad_orden, dmc.nivel AS nivel_competencia, dmc.subnivel AS subnivel_competencia, '
        . 'dmc.categoria AS categoria_competencia, '
        . 'ei.id AS inscripcion_id, ei.fecha_inscripcion, ei.monto, ei.estado_pago, ei.referencia, ei.observaciones '
        . 'FROM deportistas d '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'INNER JOIN deportista_modalidades_competencia dmc ON dmc.deportista_id = d.id '
        . 'INNER JOIN modalidades_competencia mc ON mc.id = dmc.modalidad_competencia_id '
        . 'LEFT JOIN evento_federado_inscripciones ei '
        . '    ON ei.evento_id = :evento_id AND ei.deportista_modalidades_competencia_id = dmc.id '
        . 'WHERE d.activo = 1 '
        . 'AND dmc.nivel = :nivel '
        . 'ORDER BY d.nombre ASC, d.id ASC, mc.orden ASC, mc.nombre ASC, dmc.subnivel ASC, dmc.categoria ASC'
    );
    $stmt->execute([
        'evento_id' => (int) ($evento['id'] ?? 0),
        'nivel' => (string) ($evento['nivel'] ?? ''),
    ]);

    return $stmt->fetchAll();
}

function evento_federado_deportista_es_elegible(array $evento, int $deportistaId): bool
{
    if (!eventos_federados_schema_ready()) {
        return false;
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) '
        . 'FROM deportista_modalidades_competencia '
        . 'WHERE deportista_id = :deportista_id '
        . 'AND nivel = :nivel'
    );
    $stmt->execute([
        'deportista_id' => $deportistaId,
        'nivel' => (string) ($evento['nivel'] ?? ''),
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

function evento_federado_deportista_asignacion_find(
    int $deportistaId,
    int $asignacionId,
    string $nivel
): ?array {
    if (!eventos_federados_schema_ready()) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT dmc.id, dmc.deportista_id, dmc.modalidad_competencia_id, mc.codigo AS modalidad_codigo, '
        . 'mc.nombre AS modalidad_nombre, dmc.nivel, dmc.subnivel, dmc.categoria '
        . 'FROM deportista_modalidades_competencia dmc '
        . 'INNER JOIN modalidades_competencia mc ON mc.id = dmc.modalidad_competencia_id '
        . 'WHERE dmc.id = :id '
        . 'AND dmc.deportista_id = :deportista_id '
        . 'AND dmc.nivel = :nivel '
        . 'LIMIT 1'
    );
    $stmt->execute([
        'id' => $asignacionId,
        'deportista_id' => $deportistaId,
        'nivel' => $nivel,
    ]);

    $row = $stmt->fetch();

    return $row ?: null;
}

function evento_federado_inscripcion_find(int $eventoId, int $asignacionId): ?array
{
    if (!eventos_federados_schema_ready()) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT id, evento_id, deportista_id, deportista_modalidades_competencia_id, modalidad_competencia_id, subnivel, categoria, '
        . 'apoderado_id, fecha_inscripcion, monto, estado_pago, referencia, observaciones '
        . 'FROM evento_federado_inscripciones '
        . 'WHERE evento_id = :evento_id AND deportista_modalidades_competencia_id = :deportista_modalidades_competencia_id '
        . 'LIMIT 1'
    );
    $stmt->execute([
        'evento_id' => $eventoId,
        'deportista_modalidades_competencia_id' => $asignacionId,
    ]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function evento_federado_inscribir(int $eventoId, int $deportistaId, array $data = []): void
{
    if (!eventos_federados_schema_ready()) {
        throw new RuntimeException('La base de datos no tiene la estructura para eventos federados.');
    }

    $evento = evento_federado_find($eventoId);
    if ($evento === null) {
        throw new RuntimeException('No se encontro el evento.');
    }

    if (!evento_federado_deportista_es_elegible($evento, $deportistaId)) {
        throw new RuntimeException('El deportista no cumple con los filtros del evento.');
    }

    $asignacionId = (int) ($data['deportista_modalidades_competencia_id'] ?? 0);
    if ($asignacionId <= 0) {
        throw new RuntimeException('Debes seleccionar una modalidad y subnivel para la inscripcion.');
    }

    $asignacion = evento_federado_deportista_asignacion_find(
        $deportistaId,
        $asignacionId,
        (string) ($evento['nivel'] ?? '')
    );
    if ($asignacion === null) {
        throw new RuntimeException('La modalidad seleccionada no es valida para este deportista en este nivel.');
    }

    $stmt = db()->prepare('SELECT apoderado_id FROM deportistas WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $deportistaId]);
    $apoderadoId = (int) $stmt->fetchColumn();
    if ($apoderadoId <= 0) {
        throw new RuntimeException('No se encontro el apoderado del deportista.');
    }

    $monto = array_key_exists('monto', $data) ? (float) $data['monto'] : (float) ($evento['costo_inscripcion'] ?? 0);
    $estadoPago = trim((string) ($data['estado_pago'] ?? 'pendiente'));
    if (!in_array($estadoPago, ['pendiente', 'pagado', 'anulado'], true)) {
        $estadoPago = 'pendiente';
    }

    $fechaInscripcion = trim((string) ($data['fecha_inscripcion'] ?? ''));
    if ($fechaInscripcion === '') {
        $fechaInscripcion = date('Y-m-d');
    }

    $stmt = db()->prepare(
        'INSERT INTO evento_federado_inscripciones '
        . '(evento_id, deportista_id, deportista_modalidades_competencia_id, modalidad_competencia_id, subnivel, categoria, '
        . 'apoderado_id, fecha_inscripcion, monto, estado_pago, referencia, observaciones) '
        . 'VALUES '
        . '(:evento_id, :deportista_id, :deportista_modalidades_competencia_id, :modalidad_competencia_id, :subnivel, :categoria, '
        . ':apoderado_id, :fecha_inscripcion, :monto, :estado_pago, :referencia, :observaciones) '
        . 'ON DUPLICATE KEY UPDATE '
        . 'apoderado_id = VALUES(apoderado_id), '
        . 'deportista_id = VALUES(deportista_id), '
        . 'deportista_modalidades_competencia_id = VALUES(deportista_modalidades_competencia_id), '
        . 'modalidad_competencia_id = VALUES(modalidad_competencia_id), '
        . 'subnivel = VALUES(subnivel), '
        . 'categoria = VALUES(categoria), '
        . 'fecha_inscripcion = VALUES(fecha_inscripcion), '
        . 'monto = VALUES(monto), '
        . 'estado_pago = VALUES(estado_pago), '
        . 'referencia = VALUES(referencia), '
        . 'observaciones = VALUES(observaciones)'
    );
    $stmt->execute([
        'evento_id' => $eventoId,
        'deportista_id' => $deportistaId,
        'deportista_modalidades_competencia_id' => $asignacion['id'],
        'modalidad_competencia_id' => $asignacion['modalidad_competencia_id'],
        'subnivel' => trim((string) ($asignacion['subnivel'] ?? '')) ?: null,
        'categoria' => trim((string) ($asignacion['categoria'] ?? '')) ?: null,
        'apoderado_id' => $apoderadoId,
        'fecha_inscripcion' => $fechaInscripcion,
        'monto' => $monto,
        'estado_pago' => $estadoPago,
        'referencia' => trim((string) ($data['referencia'] ?? '')) ?: null,
        'observaciones' => trim((string) ($data['observaciones'] ?? '')) ?: null,
    ]);
}

function evento_federado_inscripcion_delete(int $eventoId, int $asignacionId): void
{
    $stmt = db()->prepare(
        'DELETE FROM evento_federado_inscripciones '
        . 'WHERE evento_id = :evento_id AND deportista_modalidades_competencia_id = :deportista_modalidades_competencia_id'
    );
    $stmt->execute([
        'evento_id' => $eventoId,
        'deportista_modalidades_competencia_id' => $asignacionId,
    ]);
}
