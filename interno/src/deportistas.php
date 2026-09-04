<?php

declare(strict_types=1);

function deportistas_all(string $search = '', bool $activeOnly = true): array
{
    $search = trim($search);
    $conditions = [];
    $parameters = [];

    if ($activeOnly) {
        $conditions[] = 'd.activo = 1';
    }

    if ($search !== '') {
        $conditions[] = '(d.nombre LIKE :search_name OR d.rut LIKE :search_rut)';
        $likeSearch = '%' . $search . '%';
        $parameters['search_name'] = $likeSearch;
        $parameters['search_rut'] = $likeSearch;
    }

    $sql =
        'SELECT d.id, d.nombre, d.rut, d.avatar_path, d.fecha_nacimiento, d.categoria, d.nivel_id, d.activo, '
        . 'CASE '
        . '    WHEN d.fecha_nacimiento IS NULL THEN NULL '
        . '    ELSE YEAR(CURDATE()) - YEAR(d.fecha_nacimiento) '
        . 'END AS edad_competencia, '
        . 'a.nombre AS apoderado_nombre, n.nombre AS nivel_nombre, '
        . 'COALESCE(dm.modalidades_competencia, "") AS modalidades_competencia '
        . 'FROM deportistas d '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'LEFT JOIN niveles_deportivos n ON n.id = d.nivel_id '
        . 'LEFT JOIN ('
        . '    SELECT dmc.deportista_id, '
        . '           GROUP_CONCAT(mc.nombre ORDER BY mc.orden ASC, mc.nombre ASC SEPARATOR ", ") AS modalidades_competencia '
        . '    FROM deportista_modalidades_competencia dmc '
        . '    INNER JOIN modalidades_competencia mc ON mc.id = dmc.modalidad_competencia_id '
        . '    GROUP BY dmc.deportista_id'
        . ') dm ON dm.deportista_id = d.id '
        . 'ORDER BY d.nombre';

    if (!empty($conditions)) {
        $sql = str_replace('ORDER BY d.nombre', 'WHERE ' . implode(' AND ', $conditions) . ' ORDER BY d.nombre', $sql);
        $stmt = db()->prepare($sql);
        $stmt->execute($parameters);
    } else {
        $stmt = db()->query($sql);
    }

    return $stmt->fetchAll();
}

function deportista_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, apoderado_id, nombre, fecha_nacimiento, categoria, rut, avatar_path, nivel_id, activo '
        . 'FROM deportistas WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if ($row === false) {
        return null;
    }

    $row['modalidades_competencia_ids'] = deportista_modalidades_competencia_ids($id);

    return $row;
}

function deportista_avatar_public_url(?string $relativePath): ?string
{
    $relativePath = trim((string) $relativePath);
    if ($relativePath === '') {
        return null;
    }

    return base_url('/' . ltrim($relativePath, '/'));
}

function deportista_avatar_relative_path(int $deportistaId, string $extension): string
{
    $extension = strtolower(trim($extension, ". \t\n\r\0\x0B"));
    if ($extension === 'jpeg') {
        $extension = 'jpg';
    }

    return 'uploads/deportistas/deportista-' . $deportistaId . '-avatar.' . $extension;
}

function deportista_avatar_absolute_path(string $relativePath): string
{
    return dirname(__DIR__) . '/' . ltrim($relativePath, '/');
}

function deportista_avatar_store_upload(int $deportistaId, array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir la imagen del deportista.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('La imagen del deportista no es valida.');
    }

    $imageInfo = @getimagesize($tmpName);
    if ($imageInfo === false || empty($imageInfo['mime'])) {
        throw new RuntimeException('La imagen del deportista debe ser un archivo valido.');
    }

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $mime = (string) $imageInfo['mime'];
    if (!isset($allowedMimes[$mime])) {
        throw new RuntimeException('Formato de imagen no permitido. Usa JPG, PNG, WEBP o GIF.');
    }

    $maxSize = 5 * 1024 * 1024;
    $fileSize = (int) ($file['size'] ?? 0);
    if ($fileSize <= 0 || $fileSize > $maxSize) {
        throw new RuntimeException('La imagen debe pesar menos de 5 MB.');
    }

    $relativePath = deportista_avatar_relative_path($deportistaId, $allowedMimes[$mime]);
    $absolutePath = deportista_avatar_absolute_path($relativePath);
    $directory = dirname($absolutePath);

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('No se pudo crear la carpeta de imagenes.');
    }

    if (!move_uploaded_file($tmpName, $absolutePath)) {
        throw new RuntimeException('No se pudo guardar la imagen del deportista.');
    }

    return $relativePath;
}

function deportista_update_avatar_path(int $id, ?string $avatarPath): void
{
    $stmt = db()->prepare(
        'UPDATE deportistas SET avatar_path = :avatar_path WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'avatar_path' => $avatarPath !== null && trim($avatarPath) !== '' ? $avatarPath : null,
    ]);
}

function deportista_create(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO deportistas (apoderado_id, nombre, fecha_nacimiento, categoria, rut, avatar_path, nivel_id, activo) '
        . 'VALUES (:apoderado_id, :nombre, :fecha_nacimiento, :categoria, :rut, :avatar_path, :nivel_id, :activo)'
    );
    $stmt->execute([
        'apoderado_id' => $data['apoderado_id'],
        'nombre' => $data['nombre'],
        'fecha_nacimiento' => $data['fecha_nacimiento'] ?: null,
        'categoria' => $data['categoria'],
        'rut' => $data['rut'] ?: null,
        'avatar_path' => $data['avatar_path'] ?: null,
        'nivel_id' => $data['nivel_id'] > 0 ? $data['nivel_id'] : null,
        'activo' => $data['activo'],
    ]);

    return (int) db()->lastInsertId();
}

function deportista_update(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE deportistas SET apoderado_id = :apoderado_id, nombre = :nombre, '
        . 'fecha_nacimiento = :fecha_nacimiento, categoria = :categoria, rut = :rut, avatar_path = :avatar_path, '
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
        'avatar_path' => $data['avatar_path'] ?? null,
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
