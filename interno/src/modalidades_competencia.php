<?php

declare(strict_types=1);

function modalidades_competencia_all(): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $stmt = db()->query(
        'SELECT id, codigo, nombre, orden, activo '
        . 'FROM modalidades_competencia '
        . 'WHERE activo = 1 '
        . 'ORDER BY orden ASC, nombre ASC'
    );

    return $stmt->fetchAll();
}

function modalidad_competencia_find(int $id): ?array
{
    if (!modalidades_competencia_schema_ready()) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT id, codigo, nombre, orden, activo '
        . 'FROM modalidades_competencia '
        . 'WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function modalidades_competencia_reglas_all(): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $stmt = db()->query(
        'SELECT r.id, r.modalidad_competencia_id, m.codigo AS modalidad_codigo, m.nombre AS modalidad_nombre, '
        . 'r.nivel, r.subnivel, r.categoria, r.edad_min, r.edad_max, r.activo '
        . 'FROM modalidades_competencia_reglas r '
        . 'INNER JOIN modalidades_competencia m ON m.id = r.modalidad_competencia_id '
        . 'WHERE r.activo = 1 '
        . 'ORDER BY m.orden ASC, r.nivel ASC, r.subnivel ASC, r.categoria ASC'
    );

    return $stmt->fetchAll();
}

function modalidades_competencia_niveles_globales(): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $stmt = db()->query(
        'SELECT nivel, MIN(id) AS sort_order '
        . 'FROM modalidades_competencia_reglas '
        . 'WHERE activo = 1 '
        . 'GROUP BY nivel '
        . 'ORDER BY sort_order ASC, nivel ASC'
    );

    return $stmt->fetchAll();
}

function modalidades_competencia_reglas_por_modalidad(int $modalidadId): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT id, modalidad_competencia_id, nivel, subnivel, categoria, edad_min, edad_max, activo '
        . 'FROM modalidades_competencia_reglas '
        . 'WHERE modalidad_competencia_id = :modalidad_id AND activo = 1 '
        . 'ORDER BY nivel ASC, subnivel ASC, categoria ASC'
    );
    $stmt->execute(['modalidad_id' => $modalidadId]);

    return $stmt->fetchAll();
}

function modalidades_competencia_niveles_por_modalidad(int $modalidadId): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT r.nivel, MIN(r.id) AS sort_order '
        . 'FROM modalidades_competencia_reglas r '
        . 'INNER JOIN modalidades_competencia m ON m.id = r.modalidad_competencia_id '
        . 'WHERE r.modalidad_competencia_id = :modalidad_id '
        . 'AND r.activo = 1 AND m.activo = 1 '
        . 'GROUP BY r.nivel '
        . 'ORDER BY sort_order ASC, r.nivel ASC'
    );
    $stmt->execute(['modalidad_id' => $modalidadId]);

    return $stmt->fetchAll();
}

function modalidades_competencia_subniveles_por_modalidad_y_nivel(int $modalidadId, string $nivel): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT r.subnivel, MIN(r.id) AS sort_order '
        . 'FROM modalidades_competencia_reglas r '
        . 'INNER JOIN modalidades_competencia m ON m.id = r.modalidad_competencia_id '
        . 'WHERE r.modalidad_competencia_id = :modalidad_id '
        . 'AND r.nivel = :nivel '
        . 'AND r.activo = 1 AND m.activo = 1 '
        . 'GROUP BY r.subnivel '
        . 'ORDER BY sort_order ASC, r.subnivel ASC'
    );
    $stmt->execute([
        'modalidad_id' => $modalidadId,
        'nivel' => $nivel,
    ]);

    return $stmt->fetchAll();
}

function modalidades_competencia_regla_por_seleccion(
    int $modalidadId,
    string $nivel,
    string $subnivel,
    int $edadCompetencia
): ?array {
    if (!modalidades_competencia_schema_ready()) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT r.id, r.modalidad_competencia_id, m.codigo AS modalidad_codigo, m.nombre AS modalidad_nombre, '
        . 'r.nivel, r.subnivel, r.categoria, r.edad_min, r.edad_max '
        . 'FROM modalidades_competencia_reglas r '
        . 'INNER JOIN modalidades_competencia m ON m.id = r.modalidad_competencia_id '
        . 'WHERE r.modalidad_competencia_id = :modalidad_id '
        . 'AND r.nivel = :nivel '
        . 'AND r.subnivel = :subnivel '
        . 'AND r.activo = 1 AND m.activo = 1 '
        . 'AND (r.edad_min IS NULL OR r.edad_min <= :edad) '
        . 'AND (r.edad_max IS NULL OR r.edad_max >= :edad) '
        . 'ORDER BY r.id ASC '
        . 'LIMIT 1'
    );
    $stmt->execute([
        'modalidad_id' => $modalidadId,
        'nivel' => $nivel,
        'subnivel' => $subnivel,
        'edad' => $edadCompetencia,
    ]);

    $row = $stmt->fetch();

    return $row ?: null;
}

function modalidades_competencia_anio_competencia(?int $anioCompetencia = null): int
{
    return $anioCompetencia !== null ? $anioCompetencia : (int) date('Y');
}

function modalidades_competencia_edad_competencia(?string $fechaNacimiento, ?int $anioCompetencia = null): ?int
{
    $fechaNacimiento = trim((string) $fechaNacimiento);
    if ($fechaNacimiento === '' || strlen($fechaNacimiento) < 4) {
        return null;
    }

    $anioNacimiento = (int) substr($fechaNacimiento, 0, 4);
    if ($anioNacimiento <= 0) {
        return null;
    }

    $anioCompetencia = modalidades_competencia_anio_competencia($anioCompetencia);

    return max(0, $anioCompetencia - $anioNacimiento);
}

function modalidades_competencia_reglas_por_edad(int $edadCompetencia, array $modalidadIds = []): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $sql = (
        'SELECT r.id, r.modalidad_competencia_id, m.codigo AS modalidad_codigo, m.nombre AS modalidad_nombre, '
        . 'm.orden AS modalidad_orden, r.nivel, r.subnivel, r.categoria, r.edad_min, r.edad_max '
        . 'FROM modalidades_competencia_reglas r '
        . 'INNER JOIN modalidades_competencia m ON m.id = r.modalidad_competencia_id '
        . 'WHERE r.activo = 1 AND m.activo = 1 '
        . 'AND (r.edad_min IS NULL OR r.edad_min <= :edad) '
        . 'AND (r.edad_max IS NULL OR r.edad_max >= :edad)'
    );

    $params = ['edad' => $edadCompetencia];

    $ids = array_values(array_unique(array_filter(
        array_map('intval', $modalidadIds),
        static fn (int $value): bool => $value > 0
    )));
    if (!empty($ids)) {
        $placeholders = [];
        foreach ($ids as $index => $id) {
            $placeholder = ':modalidad_id_' . $index;
            $placeholders[] = $placeholder;
            $params[substr($placeholder, 1)] = $id;
        }
        $sql .= ' AND r.modalidad_competencia_id IN (' . implode(', ', $placeholders) . ')';
    }

    $sql .= ' ORDER BY m.orden ASC, r.nivel ASC, r.subnivel ASC, r.categoria ASC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function modalidades_competencia_sugerencias_para_deportista(?string $fechaNacimiento, array $modalidadIds = [], ?int $anioCompetencia = null): array
{
    $anioCompetencia = modalidades_competencia_anio_competencia($anioCompetencia);
    $edadCompetencia = modalidades_competencia_edad_competencia($fechaNacimiento, $anioCompetencia);

    if ($edadCompetencia === null) {
        return [
            'anio_competencia' => $anioCompetencia,
            'edad_competencia' => null,
            'reglas' => [],
            'categorias' => [],
            'categoria_unica' => null,
        ];
    }

    $reglas = modalidades_competencia_reglas_por_edad($edadCompetencia, $modalidadIds);
    $categorias = [];
    foreach ($reglas as $regla) {
        $categoria = trim((string) ($regla['categoria'] ?? ''));
        if ($categoria !== '') {
            $categorias[$categoria] = $categoria;
        }
    }
    $categorias = array_values($categorias);
    sort($categorias, SORT_NATURAL | SORT_FLAG_CASE);

    return [
        'anio_competencia' => $anioCompetencia,
        'edad_competencia' => $edadCompetencia,
        'reglas' => $reglas,
        'categorias' => $categorias,
        'categoria_unica' => count($categorias) === 1 ? $categorias[0] : null,
    ];
}

function deportista_modalidades_competencia_ids(int $deportistaId): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT modalidad_competencia_id '
        . 'FROM deportista_modalidades_competencia '
        . 'WHERE deportista_id = :deportista_id '
        . 'ORDER BY modalidad_competencia_id'
    );
    $stmt->execute(['deportista_id' => $deportistaId]);

    return array_map(static fn ($value): int => (int) $value, $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function deportista_modalidades_competencia_all(int $deportistaId): array
{
    if (!modalidades_competencia_schema_ready()) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT dmc.id, dmc.deportista_id, dmc.modalidad_competencia_id, mc.codigo AS modalidad_codigo, '
        . 'mc.nombre AS modalidad_nombre, dmc.nivel, dmc.subnivel, dmc.categoria '
        . 'FROM deportista_modalidades_competencia dmc '
        . 'INNER JOIN modalidades_competencia mc ON mc.id = dmc.modalidad_competencia_id '
        . 'WHERE dmc.deportista_id = :deportista_id '
        . 'ORDER BY mc.orden ASC, dmc.id ASC'
    );
    $stmt->execute(['deportista_id' => $deportistaId]);

    return $stmt->fetchAll();
}

function deportista_modalidades_competencia_sync(int $deportistaId, array $assignments): void
{
    if (!modalidades_competencia_schema_ready()) {
        return;
    }

    $rows = [];
    foreach ($assignments as $assignment) {
        if (!is_array($assignment)) {
            continue;
        }

        $modalidadId = (int) ($assignment['modalidad_competencia_id'] ?? $assignment['modalidad_id'] ?? 0);
        if ($modalidadId <= 0) {
            continue;
        }

        $rows[] = [
            'modalidad_competencia_id' => $modalidadId,
            'nivel' => trim((string) ($assignment['nivel'] ?? '')),
            'subnivel' => trim((string) ($assignment['subnivel'] ?? '')),
            'categoria' => trim((string) ($assignment['categoria'] ?? '')),
        ];
    }

    $pdo = db();
    $deleteStmt = $pdo->prepare(
        'DELETE FROM deportista_modalidades_competencia WHERE deportista_id = :deportista_id'
    );
    $deleteStmt->execute(['deportista_id' => $deportistaId]);

    if (!empty($rows)) {
        $insertStmt = $pdo->prepare(
            'INSERT INTO deportista_modalidades_competencia '
            . '(deportista_id, modalidad_competencia_id, nivel, subnivel, categoria) '
            . 'VALUES (:deportista_id, :modalidad_competencia_id, :nivel, :subnivel, :categoria)'
        );

        foreach ($rows as $row) {
            $insertStmt->execute([
                'deportista_id' => $deportistaId,
                'modalidad_competencia_id' => $row['modalidad_competencia_id'],
                'nivel' => $row['nivel'] !== '' ? $row['nivel'] : null,
                'subnivel' => $row['subnivel'] !== '' ? $row['subnivel'] : null,
                'categoria' => $row['categoria'] !== '' ? $row['categoria'] : null,
            ]);
        }
    }
}

function modalidades_competencia_schema_ready(): bool
{
    static $ready = null;

    if ($ready !== null) {
        return $ready;
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) '
        . 'FROM information_schema.tables '
        . 'WHERE table_schema = DATABASE() '
        . 'AND table_name IN ('
        . '    "modalidades_competencia", '
        . '    "modalidades_competencia_reglas", '
        . '    "deportista_modalidades_competencia"'
        . ')'
    );
    $stmt->execute();
    if ((int) $stmt->fetchColumn() !== 3) {
        $ready = false;
        return $ready;
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) '
        . 'FROM information_schema.columns '
        . 'WHERE table_schema = DATABASE() '
        . 'AND table_name = "deportista_modalidades_competencia" '
        . 'AND column_name IN ("nivel", "subnivel", "categoria")'
    );
    $stmt->execute();
    $ready = (int) $stmt->fetchColumn() === 3;

    return $ready;
}
