<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/src/bootstrap.php';

$page = $_GET['page'] ?? 'home';

if ($page === 'socios') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
        ];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                apoderado_delete($id);
            }
            redirect(base_url('/?page=socios&flash=deleted'));
        }

        $errors = [];
        if ($form['nombre'] === '') {
            $errors[] = 'El nombre es obligatorio.';
        }

        if (empty($errors)) {
            if ($action === 'create') {
                apoderado_create($form);
                redirect(base_url('/?page=socios&flash=created'));
            }

            if ($action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    apoderado_update($id, $form);
                    redirect(base_url('/?page=socios&flash=updated'));
                }
                $errors[] = 'No se encontro el apoderado.';
            }
        }

        $view = 'apoderados/form';
        render($view, [
            'title' => page_title('socios'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'apoderado' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'apoderados/form';
        render($view, [
            'title' => 'Nuevo apoderado - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'apoderado' => ['id' => 0, 'nombre' => '', 'telefono' => '', 'email' => '', 'direccion' => ''],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $apoderado = $id > 0 ? apoderado_find($id) : null;
        if ($apoderado === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'apoderados/form';
        render($view, [
            'title' => 'Editar apoderado - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'apoderado' => $apoderado,
        ]);
        exit;
    }

    $view = 'apoderados/index';
    render($view, [
        'title' => 'Apoderados - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'apoderados' => apoderados_all_with_saldo(),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'deportistas') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;
    $search = trim((string) ($_GET['q'] ?? ''));
    $activeOnly = !isset($_GET['active_only']) || (string) $_GET['active_only'] === '1';
    $modalidadesCompetencia = modalidades_competencia_all();
    $modalidadesCompetenciaMap = [];
    $modalidadNoCompiteId = 0;

    foreach ($modalidadesCompetencia as $modalidadCompetencia) {
        $modalidadId = (int) $modalidadCompetencia['id'];
        $modalidadesCompetenciaMap[$modalidadId] = $modalidadCompetencia;
        if (($modalidadCompetencia['codigo'] ?? '') === 'no_compite') {
            $modalidadNoCompiteId = $modalidadId;
        }
    }

    if ($action === 'competencia-options') {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        if (!modalidades_competencia_schema_ready()) {
            http_response_code(503);
            echo json_encode([
                'ok' => false,
                'message' => 'La base de datos aun no tiene la migracion de competencia.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $kind = trim((string) ($_GET['kind'] ?? ''));
        $modalidadId = (int) ($_GET['modalidad_id'] ?? 0);
        $nivel = trim((string) ($_GET['nivel'] ?? ''));
        $subnivel = trim((string) ($_GET['subnivel'] ?? ''));
        $fechaNacimiento = trim((string) ($_GET['fecha_nacimiento'] ?? ''));
        $edadCompetencia = modalidades_competencia_edad_competencia($fechaNacimiento);
        $modalidad = $modalidadId > 0 ? ($modalidadesCompetenciaMap[$modalidadId] ?? null) : null;

        if ($modalidad === null) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'message' => 'Modalidad no valida.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($kind === 'levels') {
            $items = [];
            if (($modalidad['codigo'] ?? '') !== 'no_compite') {
                foreach (modalidades_competencia_niveles_por_modalidad($modalidadId) as $row) {
                    $items[] = [
                        'value' => (string) $row['nivel'],
                        'label' => (string) $row['nivel'],
                    ];
                }
            }

            echo json_encode([
                'ok' => true,
                'items' => $items,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($kind === 'subniveles') {
            $items = [];
            if ($nivel !== '' && ($modalidad['codigo'] ?? '') !== 'no_compite') {
                foreach (modalidades_competencia_subniveles_por_modalidad_y_nivel($modalidadId, $nivel) as $row) {
                    $items[] = [
                        'value' => (string) $row['subnivel'],
                        'label' => (string) $row['subnivel'],
                    ];
                }
            }

            echo json_encode([
                'ok' => true,
                'items' => $items,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($kind === 'preview') {
            $rule = null;
            if (($modalidad['codigo'] ?? '') !== 'no_compite'
                && $edadCompetencia !== null
                && $nivel !== ''
                && $subnivel !== ''
            ) {
                $rule = modalidades_competencia_regla_por_seleccion($modalidadId, $nivel, $subnivel, $edadCompetencia);
            }

            echo json_encode([
                'ok' => true,
                'edad_competencia' => $edadCompetencia,
                'categoria' => $rule['categoria'] ?? (($modalidad['codigo'] ?? '') === 'no_compite' ? 'No compite' : null),
                'rule' => $rule,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'message' => 'Parametro kind no valido.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $buildSugerencias = static function (array $deportista): array {
        return modalidades_competencia_sugerencias_para_deportista(
            (string) ($deportista['fecha_nacimiento'] ?? ''),
            []
        );
    };
    $competenciaSchemaReady = modalidades_competencia_schema_ready();
    $blankCompetenciaAssignment = [
        'modalidad_competencia_id' => 0,
        'nivel' => '',
        'subnivel' => '',
        'categoria' => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedId = (int) ($_POST['id'] ?? 0);
        $currentDeportista = $submittedId > 0 ? deportista_find($submittedId) : null;
        $currentAvatarPath = $currentDeportista['avatar_path'] ?? null;
        $modalidades = modalidades_options();
        $modalidadesMap = [];
        foreach ($modalidades as $modalidad) {
            $modalidadesMap[(int) $modalidad['id']] = $modalidad;
        }
        $rawAssignments = (array) ($_POST['competencia_assignments'] ?? []);
        $competenciaAssignments = [];
        foreach ($rawAssignments as $assignment) {
            if (!is_array($assignment)) {
                continue;
            }

            $normalized = [
                'modalidad_competencia_id' => (int) ($assignment['modalidad_competencia_id'] ?? 0),
                'nivel' => trim((string) ($assignment['nivel'] ?? '')),
                'subnivel' => trim((string) ($assignment['subnivel'] ?? '')),
                'categoria' => trim((string) ($assignment['categoria'] ?? '')),
            ];

            if ($normalized['modalidad_competencia_id'] <= 0
                && $normalized['nivel'] === ''
                && $normalized['subnivel'] === ''
                && $normalized['categoria'] === ''
            ) {
                continue;
            }

            $competenciaAssignments[] = $normalized;
        }
        $inscripciones = [];
        $rawInscripciones = (array) ($_POST['inscripciones'] ?? []);
        foreach ($rawInscripciones as $inscripcion) {
            if (!is_array($inscripcion)) {
                continue;
            }

            $normalized = [
                'id' => (int) ($inscripcion['id'] ?? 0),
                'modalidad_id' => (int) ($inscripcion['modalidad_id'] ?? 0),
                'fecha_inicio' => trim((string) ($inscripcion['fecha_inicio'] ?? '')),
                'fecha_fin' => trim((string) ($inscripcion['fecha_fin'] ?? '')),
                'activo' => isset($inscripcion['activo']) ? 1 : 0,
            ];

            if ($normalized['id'] <= 0 && $normalized['modalidad_id'] <= 0
                && $normalized['fecha_inicio'] === '' && $normalized['fecha_fin'] === '') {
                continue;
            }

            $inscripciones[] = $normalized;
        }
        $form = [
            'apoderado_id' => (int) ($_POST['apoderado_id'] ?? 0),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'rut' => format_rut($_POST['rut'] ?? ''),
            'nivel_id' => (int) ($_POST['nivel_id'] ?? 0),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'avatar_path' => $currentAvatarPath,
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                deportista_delete($id);
            }
            redirect(base_url('/?page=deportistas&flash=deleted'));
        }

        $errors = [];
        if ($form['apoderado_id'] <= 0) {
            $errors[] = 'Debes seleccionar un apoderado.';
        }
        if ($form['nombre'] === '') {
            $errors[] = 'El nombre es obligatorio.';
        }
        if ($form['rut'] === '') {
            $errors[] = 'El RUT es obligatorio.';
        } elseif (!is_valid_rut($form['rut'])) {
            $errors[] = 'El RUT no es valido.';
        } elseif (deportista_exists_by_rut($form['rut'], $submittedId)) {
            $errors[] = 'El RUT ya esta registrado en otro deportista.';
        }
        if ($form['nivel_id'] <= 0) {
            $errors[] = 'Debes seleccionar un nivel.';
        }
        if (empty($competenciaAssignments)) {
            $errors[] = 'Debes agregar al menos una modalidad de competencia.';
        }

        $existingInscriptionIds = $submittedId > 0
            ? array_map('intval', array_column(inscripciones_por_deportista($submittedId), 'id'))
            : [];
        foreach ($inscripciones as $inscripcion) {
            if ($inscripcion['id'] > 0 && !in_array($inscripcion['id'], $existingInscriptionIds, true)) {
                $errors[] = 'Una de las inscripciones no pertenece a este deportista.';
                continue;
            }
            if (!isset($modalidadesMap[$inscripcion['modalidad_id']])) {
                $errors[] = 'Debes seleccionar una modalidad valida para cada inscripcion.';
            }
            $start = DateTime::createFromFormat('!Y-m-d', $inscripcion['fecha_inicio']);
            if ($inscripcion['fecha_inicio'] === '' || $start === false || $start->format('Y-m-d') !== $inscripcion['fecha_inicio']) {
                $errors[] = 'Cada inscripcion debe tener una fecha de inicio valida.';
            }
            if ($inscripcion['fecha_fin'] !== '') {
                $end = DateTime::createFromFormat('!Y-m-d', $inscripcion['fecha_fin']);
                if ($end === false || $end->format('Y-m-d') !== $inscripcion['fecha_fin']) {
                    $errors[] = 'La fecha de fin de una inscripcion no es valida.';
                } elseif ($start !== false && $end < $start) {
                    $errors[] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
                }
            }
        }

        $competenciaAssignmentsForForm = [];
        $seenModalidades = [];
        $edadCompetencia = modalidades_competencia_edad_competencia($form['fecha_nacimiento']);

        foreach ($competenciaAssignments as $assignment) {
            $modalidadId = (int) $assignment['modalidad_competencia_id'];
            $modalidad = $modalidadesCompetenciaMap[$modalidadId] ?? null;
            if ($modalidad === null) {
                $errors[] = 'Una de las modalidades de competencia no es valida.';
                continue;
            }

            if (isset($seenModalidades[$modalidadId])) {
                $errors[] = 'No puedes repetir la misma modalidad de competencia.';
                continue;
            }
            $seenModalidades[$modalidadId] = true;

            $codigoModalidad = (string) ($modalidad['codigo'] ?? '');
            if ($codigoModalidad === 'no_compite') {
                if (count($competenciaAssignments) > 1) {
                    $errors[] = 'La modalidad "No compite" debe quedar sola.';
                }
                if ($assignment['nivel'] !== '' || $assignment['subnivel'] !== '') {
                    $errors[] = 'La modalidad "No compite" no usa nivel ni subnivel.';
                }

                $competenciaAssignmentsForForm[] = [
                    'modalidad_competencia_id' => $modalidadId,
                    'modalidad_nombre' => (string) ($modalidad['nombre'] ?? ''),
                    'nivel' => '',
                    'subnivel' => '',
                    'categoria' => 'No compite',
                ];
                continue;
            }

            if ($assignment['nivel'] === '') {
                $errors[] = 'Debes seleccionar un nivel para la modalidad ' . ($modalidad['nombre'] ?? 'seleccionada') . '.';
                continue;
            }
            if ($assignment['subnivel'] === '') {
                $errors[] = 'Debes seleccionar un subnivel para la modalidad ' . ($modalidad['nombre'] ?? 'seleccionada') . '.';
                continue;
            }
            if ($edadCompetencia === null) {
                $errors[] = 'Debes ingresar la fecha de nacimiento para calcular la edad de competencia.';
                continue;
            }

            $rule = modalidades_competencia_regla_por_seleccion(
                $modalidadId,
                $assignment['nivel'],
                $assignment['subnivel'],
                $edadCompetencia
            );

            if ($rule === null) {
                $errors[] = 'La seleccion no tiene una categoria valida para la edad de competencia.';
                continue;
            }

            $competenciaAssignmentsForForm[] = [
                'modalidad_competencia_id' => $modalidadId,
                'modalidad_nombre' => (string) ($modalidad['nombre'] ?? ''),
                'nivel' => $assignment['nivel'],
                'subnivel' => $assignment['subnivel'],
                'categoria' => (string) ($rule['categoria'] ?? ''),
            ];
        }

        if (isset($seenModalidades[$modalidadNoCompiteId]) && count($competenciaAssignmentsForForm) > 1) {
            $errors[] = 'La modalidad "No compite" debe quedar sola.';
        }

        if (empty($errors)) {
            $pdo = db();
            $pdo->beginTransaction();
            $uploadedAvatarPath = null;
            $avatarPathToDelete = null;

            try {
                if ($action === 'create') {
                    $deportistaId = deportista_create($form);
                    $currentAvatarPath = null;
                } elseif ($action === 'edit') {
                    $id = (int) ($_POST['id'] ?? 0);
                    if ($id > 0) {
                        deportista_update($id, $form);
                        $deportistaId = $id;
                    } else {
                        $errors[] = 'No se encontro el deportista.';
                        throw new RuntimeException('No se encontro el deportista.');
                    }
                } else {
                    throw new RuntimeException('Accion no valida.');
                }

                $avatarUpload = $_FILES['avatar'] ?? null;
                if (is_array($avatarUpload) && (($avatarUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE)) {
                    $avatarPathToDelete = $currentAvatarPath;
                    $uploadedAvatarPath = deportista_avatar_store_upload((int) $deportistaId, $avatarUpload);
                    deportista_update_avatar_path((int) $deportistaId, $uploadedAvatarPath);
                    $form['avatar_path'] = $uploadedAvatarPath;
                }

                deportista_modalidades_competencia_sync((int) $deportistaId, $competenciaAssignmentsForForm);
                inscripciones_sync_por_deportista((int) $deportistaId, $inscripciones);
                $pdo->commit();

                if ($avatarPathToDelete !== null) {
                    $avatarPathToDelete = trim($avatarPathToDelete);
                    $newAvatarPath = $uploadedAvatarPath !== null ? trim($uploadedAvatarPath) : '';
                    if ($avatarPathToDelete !== '' && $avatarPathToDelete !== $newAvatarPath) {
                        $avatarAbsolutePath = deportista_avatar_absolute_path($avatarPathToDelete);
                        if (is_file($avatarAbsolutePath)) {
                            @unlink($avatarAbsolutePath);
                        }
                    }
                }

                redirect(base_url('/?page=deportistas&flash=' . ($action === 'create' ? 'created' : 'updated')));
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($uploadedAvatarPath !== null) {
                    $avatarAbsolutePath = deportista_avatar_absolute_path($uploadedAvatarPath);
                    if (is_file($avatarAbsolutePath)) {
                        @unlink($avatarAbsolutePath);
                    }
                }
                $addedSpecificError = false;
                if ($e instanceof RuntimeException && $e->getMessage() !== '') {
                    $errors[] = $e->getMessage();
                    $addedSpecificError = true;
                }
                if (!$addedSpecificError) {
                    $errors[] = 'No se pudo guardar el deportista.';
                }
            }

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }

        $view = 'deportistas/form';
        render($view, [
            'title' => page_title('deportistas'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'apoderados' => apoderados_all(),
            'niveles' => niveles_all(),
            'modalidades_competencia' => $modalidadesCompetencia,
            'sugerencias_competencia' => $buildSugerencias(['fecha_nacimiento' => $form['fecha_nacimiento']]),
            'competencia_schema_ready' => $competenciaSchemaReady,
            'competencia_assignments' => !empty($competenciaAssignmentsForForm)
                ? $competenciaAssignmentsForForm
                : ($competenciaAssignments ?: [$blankCompetenciaAssignment]),
            'inscripciones' => $inscripciones,
            'modalidades' => $modalidades,
            'deportista' => array_merge(['id' => $submittedId], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'deportistas/form';
        render($view, [
            'title' => 'Nuevo deportista - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'apoderados' => apoderados_all(),
            'niveles' => niveles_all(),
            'modalidades_competencia' => $modalidadesCompetencia,
            'sugerencias_competencia' => $buildSugerencias(['fecha_nacimiento' => '']),
            'competencia_schema_ready' => $competenciaSchemaReady,
            'competencia_assignments' => [$blankCompetenciaAssignment],
            'inscripciones' => [],
            'modalidades' => modalidades_options(),
            'deportista' => [
                'id' => 0,
                'apoderado_id' => 0,
                'nombre' => '',
                'rut' => '',
                'avatar_path' => null,
                'nivel_id' => 0,
                'fecha_nacimiento' => '',
                'categoria' => '',
                'activo' => 1,
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $deportista = $id > 0 ? deportista_find($id) : null;
        if ($deportista === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'deportistas/form';
        $competenciaAssignments = deportista_modalidades_competencia_all($id);
        render($view, [
            'title' => 'Editar deportista - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'apoderados' => apoderados_all(),
            'niveles' => niveles_all(),
            'modalidades_competencia' => $modalidadesCompetencia,
            'sugerencias_competencia' => $buildSugerencias($deportista),
            'competencia_schema_ready' => $competenciaSchemaReady,
            'competencia_assignments' => !empty($competenciaAssignments) ? $competenciaAssignments : [$blankCompetenciaAssignment],
            'inscripciones' => inscripciones_por_deportista($id),
            'modalidades' => modalidades_options(),
            'deportista' => $deportista,
        ]);
        exit;
    }

    $view = 'deportistas/index';
    render($view, [
        'title' => 'Deportistas - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'search' => $search,
        'active_only' => $activeOnly,
        'deportistas' => deportistas_all($search, $activeOnly),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'coaches') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'especialidad' => trim($_POST['especialidad'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                coach_delete($id);
            }
            redirect(base_url('/?page=coaches&flash=deleted'));
        }

        $errors = [];
        if ($form['nombre'] === '') {
            $errors[] = 'El nombre es obligatorio.';
        }

        if (empty($errors)) {
            if ($action === 'create') {
                coach_create($form);
                redirect(base_url('/?page=coaches&flash=created'));
            }

            if ($action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    coach_update($id, $form);
                    redirect(base_url('/?page=coaches&flash=updated'));
                }
                $errors[] = 'No se encontro el coach.';
            }
        }

        $view = 'coaches/form';
        render($view, [
            'title' => page_title('coaches'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'coach' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'coaches/form';
        render($view, [
            'title' => 'Nuevo coach - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'coach' => [
                'id' => 0,
                'nombre' => '',
                'telefono' => '',
                'email' => '',
                'especialidad' => '',
                'activo' => 1,
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $coach = $id > 0 ? coach_find($id) : null;
        if ($coach === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'coaches/form';
        render($view, [
            'title' => 'Editar coach - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'coach' => $coach,
        ]);
        exit;
    }

    $view = 'coaches/index';
    render($view, [
        'title' => 'Coaches - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'coaches' => coaches_all(),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'clases') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = [
            'deportista_id' => (int) ($_POST['deportista_id'] ?? 0),
            'coach_id' => (int) ($_POST['coach_id'] ?? 0),
            'fecha' => trim($_POST['fecha'] ?? ''),
            'duracion_min' => (int) ($_POST['duracion_min'] ?? 0),
            'tarifa' => (float) ($_POST['tarifa'] ?? 0),
            'estado' => $_POST['estado'] ?? 'programada',
            'notas' => trim($_POST['notas'] ?? ''),
        ];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                clase_delete($id);
            }
            redirect(base_url('/?page=clases&flash=deleted'));
        }

        $errors = [];
        if ($form['deportista_id'] <= 0) {
            $errors[] = 'Debes seleccionar un deportista.';
        }
        if ($form['coach_id'] <= 0) {
            $errors[] = 'Debes seleccionar un coach.';
        }
        if ($form['fecha'] === '') {
            $errors[] = 'La fecha es obligatoria.';
        }

        if (empty($errors)) {
            if ($action === 'create') {
                clase_create($form);
                redirect(base_url('/?page=clases&flash=created'));
            }

            if ($action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    clase_update($id, $form);
                    redirect(base_url('/?page=clases&flash=updated'));
                }
                $errors[] = 'No se encontro la clase.';
            }
        }

        $view = 'clases/form';
        render($view, [
            'title' => page_title('clases'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'deportistas' => deportistas_options(),
            'coaches' => coaches_options(),
            'clase' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'clases/form';
        render($view, [
            'title' => 'Nueva clase - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'deportistas' => deportistas_options(),
            'coaches' => coaches_options(),
            'clase' => [
                'id' => 0,
                'deportista_id' => 0,
                'coach_id' => 0,
                'fecha' => '',
                'duracion_min' => '',
                'tarifa' => '',
                'estado' => 'programada',
                'notas' => '',
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $clase = $id > 0 ? clase_find($id) : null;
        if ($clase === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'clases/form';
        render($view, [
            'title' => 'Editar clase - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'deportistas' => deportistas_options(),
            'coaches' => coaches_options(),
            'clase' => $clase,
        ]);
        exit;
    }

    $view = 'clases/index';
    render($view, [
        'title' => 'Clases - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'clases' => clases_all(),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'asistencia') {
    $flash = $_GET['flash'] ?? null;
    $fecha = trim($_GET['fecha'] ?? date('Y-m-d'));

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $fecha = date('Y-m-d');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        $fecha = trim($_POST['fecha'] ?? $fecha);
        $asistencia = trim($_POST['asistencia'] ?? 'pendiente');
        $asistenciaNotas = trim($_POST['asistencia_notas'] ?? '');
        $estadosPermitidos = array_keys(asistencia_estados());

        if ($id > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) && in_array($asistencia, $estadosPermitidos, true)) {
            asistencia_update($id, [
                'asistencia' => $asistencia,
                'asistencia_notas' => $asistenciaNotas,
            ]);
            redirect(base_url('/?page=asistencia&fecha=' . rawurlencode($fecha) . '&flash=updated'));
        }

        redirect(base_url('/?page=asistencia&fecha=' . rawurlencode($fecha) . '&flash=error'));
    }

    render('asistencia', [
        'title' => page_title('asistencia'),
        'page' => $page,
        'flash' => $flash,
        'fecha' => $fecha,
        'asistencias' => asistencia_clases_por_fecha($fecha),
        'resumen' => asistencia_resumen_por_fecha($fecha),
        'estados' => asistencia_estados(),
    ]);
    exit;
}

if ($page === 'pagos') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $modalidadId = (int) ($_POST['modalidad_id'] ?? 0);
        $form = [
            'apoderado_id' => (int) ($_POST['apoderado_id'] ?? 0),
            'coach_id' => (int) ($_POST['coach_id'] ?? 0),
            'periodo_inicio' => trim($_POST['periodo_inicio'] ?? ''),
            'periodo_fin' => trim($_POST['periodo_fin'] ?? ''),
            'fecha_pago' => trim($_POST['fecha_pago'] ?? ''),
            'monto_total' => (float) ($_POST['monto_total'] ?? 0),
            'metodo' => trim($_POST['metodo'] ?? ''),
            'referencia' => trim($_POST['referencia'] ?? ''),
        ];
        $classIds = $_POST['clases'] ?? [];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                pago_delete($id);
            }
            redirect(base_url('/?page=pagos&flash=deleted'));
        }

        $errors = [];
        if ($form['apoderado_id'] <= 0) {
            $errors[] = 'Debes seleccionar un apoderado.';
        }
        if ($form['coach_id'] <= 0) {
            $errors[] = 'Debes seleccionar un coach.';
        }
        if ($form['fecha_pago'] === '') {
            $errors[] = 'La fecha de pago es obligatoria.';
        }

        if ($modalidadId > 0) {
            $modalidades = modalidades_por_apoderado($form['apoderado_id']);
            foreach ($modalidades as $modalidad) {
                if ((int) $modalidad['id'] === $modalidadId) {
                    $form['coach_id'] = (int) $modalidad['coach_id'];
                    break;
                }
            }
        }

        if (empty($errors)) {
            if ($action === 'create') {
                pago_create($form, $classIds);
                redirect(base_url('/?page=pagos&flash=created'));
            }

            if ($action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    pago_update($id, $form, $classIds);
                    redirect(base_url('/?page=pagos&flash=updated'));
                }
                $errors[] = 'No se encontro el pago.';
            }
        }

        $view = 'pagos/form';
        render($view, [
            'title' => page_title('pagos'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'apoderados' => apoderados_all(),
            'coaches' => coaches_options(),
            'clases' => clases_disponibles_para_pago(
                $form['apoderado_id'] ?: null,
                $form['coach_id'] ?: null,
                $classIds
            ),
            'selectedClases' => array_map('intval', $classIds),
            'deuda' => $form['apoderado_id'] > 0 ? apoderado_deudas_detalle($form['apoderado_id']) : null,
            'modalidades' => $form['apoderado_id'] > 0 ? modalidades_por_apoderado($form['apoderado_id']) : [],
            'selectedModalidadId' => $modalidadId,
            'pago' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $preApoderadoId = (int) ($_GET['apoderado_id'] ?? 0);
        $preModalidadId = (int) ($_GET['modalidad_id'] ?? 0);
        $view = 'pagos/form';
        render($view, [
            'title' => 'Nuevo pago - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'apoderados' => apoderados_all(),
            'coaches' => coaches_options(),
            'clases' => clases_disponibles_para_pago($preApoderadoId ?: null, null),
            'selectedClases' => [],
            'deuda' => $preApoderadoId > 0 ? apoderado_deudas_detalle($preApoderadoId) : null,
            'modalidades' => $preApoderadoId > 0 ? modalidades_por_apoderado($preApoderadoId) : [],
            'selectedModalidadId' => $preModalidadId,
            'pago' => [
                'id' => 0,
                'apoderado_id' => $preApoderadoId,
                'coach_id' => 0,
                'periodo_inicio' => '',
                'periodo_fin' => '',
                'fecha_pago' => '',
                'monto_total' => '',
                'metodo' => '',
                'referencia' => '',
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $pago = $id > 0 ? pago_find($id) : null;
        if ($pago === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $selectedClases = pago_clases_ids($id);
        $view = 'pagos/form';
        render($view, [
            'title' => 'Editar pago - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'apoderados' => apoderados_all(),
            'coaches' => coaches_options(),
            'clases' => clases_disponibles_para_pago(
                (int) $pago['apoderado_id'],
                (int) $pago['coach_id'],
                $selectedClases
            ),
            'selectedClases' => $selectedClases,
            'deuda' => (int) $pago['apoderado_id'] > 0 ? apoderado_deudas_detalle((int) $pago['apoderado_id']) : null,
            'modalidades' => (int) $pago['apoderado_id'] > 0 ? modalidades_por_apoderado((int) $pago['apoderado_id']) : [],
            'selectedModalidadId' => 0,
            'pago' => $pago,
        ]);
        exit;
    }

    $view = 'pagos/index';
    render($view, [
        'title' => 'Pagos - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'pagos' => pagos_all(),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'transferencias') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = [
            'coach_id' => (int) ($_POST['coach_id'] ?? 0),
            'periodo' => trim($_POST['periodo'] ?? ''),
            'fecha_transferencia' => trim($_POST['fecha_transferencia'] ?? ''),
            'monto' => (float) ($_POST['monto'] ?? 0),
            'metodo' => trim($_POST['metodo'] ?? ''),
            'referencia' => trim($_POST['referencia'] ?? ''),
        ];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                transferencia_delete($id);
            }
            redirect(base_url('/?page=transferencias&flash=deleted'));
        }

        $errors = [];
        if ($form['coach_id'] <= 0) {
            $errors[] = 'Debes seleccionar un coach.';
        }
        if ($form['fecha_transferencia'] === '') {
            $errors[] = 'La fecha de transferencia es obligatoria.';
        }
        if ($form['monto'] <= 0) {
            $errors[] = 'El monto debe ser mayor a 0.';
        }

        if (empty($errors)) {
            if ($action === 'create') {
                transferencia_create($form);
                redirect(base_url('/?page=transferencias&flash=created'));
            }

            if ($action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    transferencia_update($id, $form);
                    redirect(base_url('/?page=transferencias&flash=updated'));
                }
                $errors[] = 'No se encontro la transferencia.';
            }
        }

        $view = 'transferencias/form';
        render($view, [
            'title' => page_title('transferencias'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'coaches' => coaches_options(),
            'transferencia' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'transferencias/form';
        render($view, [
            'title' => 'Nueva transferencia - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'coaches' => coaches_options(),
            'transferencia' => [
                'id' => 0,
                'coach_id' => 0,
                'periodo' => '',
                'fecha_transferencia' => '',
                'monto' => '',
                'metodo' => '',
                'referencia' => '',
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $transferencia = $id > 0 ? transferencia_find($id) : null;
        if ($transferencia === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'transferencias/form';
        render($view, [
            'title' => 'Editar transferencia - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'coaches' => coaches_options(),
            'transferencia' => $transferencia,
        ]);
        exit;
    }

    $view = 'transferencias/index';
    render($view, [
        'title' => 'Transferencias coaches - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'transferencias' => transferencias_all(),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'modalidades') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'costo_mensual' => (float) ($_POST['costo_mensual'] ?? 0),
            'coach_id' => (int) ($_POST['coach_id'] ?? 0),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                modalidad_delete($id);
            }
            redirect(base_url('/?page=modalidades&flash=deleted'));
        }

        $errors = [];
        if ($form['nombre'] === '') {
            $errors[] = 'El nombre es obligatorio.';
        }
        if ($form['coach_id'] <= 0) {
            $errors[] = 'Debes seleccionar un profe.';
        }

        if (empty($errors)) {
            if ($action === 'create') {
                modalidad_create($form);
                redirect(base_url('/?page=modalidades&flash=created'));
            }

            if ($action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    modalidad_update($id, $form);
                    redirect(base_url('/?page=modalidades&flash=updated'));
                }
                $errors[] = 'No se encontro la modalidad.';
            }
        }

        $view = 'modalidades/form';
        render($view, [
            'title' => page_title('modalidades'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'coaches' => coaches_options(),
            'modalidad' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'modalidades/form';
        render($view, [
            'title' => 'Nueva modalidad - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'coaches' => coaches_options(),
            'modalidad' => [
                'id' => 0,
                'nombre' => '',
                'costo_mensual' => '',
                'coach_id' => 0,
                'activo' => 1,
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $modalidad = $id > 0 ? modalidad_find($id) : null;
        if ($modalidad === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'modalidades/form';
        render($view, [
            'title' => 'Editar modalidad - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'coaches' => coaches_options(),
            'modalidad' => $modalidad,
        ]);
        exit;
    }

    $view = 'modalidades/index';
    render($view, [
        'title' => 'Modalidades - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'modalidades' => modalidades_all(),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'inscripciones') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = [
            'deportista_id' => (int) ($_POST['deportista_id'] ?? 0),
            'modalidad_id' => (int) ($_POST['modalidad_id'] ?? 0),
            'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
            'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                inscripcion_delete($id);
            }
            redirect(base_url('/?page=inscripciones&flash=deleted'));
        }

        $errors = [];
        if ($form['deportista_id'] <= 0) {
            $errors[] = 'Debes seleccionar un deportista.';
        }
        if ($form['modalidad_id'] <= 0) {
            $errors[] = 'Debes seleccionar una modalidad.';
        }
        if ($form['fecha_inicio'] === '') {
            $errors[] = 'La fecha de inicio es obligatoria.';
        }

        if (empty($errors)) {
            if ($action === 'create') {
                inscripcion_create($form);
                redirect(base_url('/?page=inscripciones&flash=created'));
            }

            if ($action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    inscripcion_update($id, $form);
                    redirect(base_url('/?page=inscripciones&flash=updated'));
                }
                $errors[] = 'No se encontro la inscripcion.';
            }
        }

        $view = 'inscripciones/form';
        render($view, [
            'title' => page_title('inscripciones'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'deportistas' => deportistas_options(),
            'modalidades' => modalidades_options(),
            'inscripcion' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'inscripciones/form';
        render($view, [
            'title' => 'Nueva inscripcion - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'deportistas' => deportistas_options(),
            'modalidades' => modalidades_options(),
            'inscripcion' => [
                'id' => 0,
                'deportista_id' => 0,
                'modalidad_id' => 0,
                'fecha_inicio' => '',
                'fecha_fin' => '',
                'activo' => 1,
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $inscripcion = $id > 0 ? inscripcion_find($id) : null;
        if ($inscripcion === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'inscripciones/form';
        render($view, [
            'title' => 'Editar inscripcion - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'deportistas' => deportistas_options(),
            'modalidades' => modalidades_options(),
            'inscripcion' => $inscripcion,
        ]);
        exit;
    }

    $view = 'inscripciones/index';
    render($view, [
        'title' => 'Inscripciones - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'inscripciones' => inscripciones_all(),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'eventos') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;
    $schemaReady = eventos_federados_schema_ready();
    $nivelesEvento = modalidades_competencia_niveles_globales();
    $eventos = eventos_federados_all();
    $blankEvento = [
        'id' => 0,
        'nombre' => '',
        'nivel' => '',
        'fecha_inicio' => '',
        'fecha_fin' => '',
        'lugar' => '',
        'costo_inscripcion' => '0.00',
        'cupo' => '',
        'estado' => 'borrador',
        'observaciones' => '',
    ];
    $errors = [];
    $eventoForm = $blankEvento;
    $evento = null;
    $inscripciones = [];
    $deportistasElegibles = [];

    if ($schemaReady && in_array($action, ['show', 'edit'], true)) {
        $id = (int) ($_GET['id'] ?? 0);
        $evento = $id > 0 ? evento_federado_find($id) : null;
        if ($evento === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }
        $eventoForm = array_merge($blankEvento, $evento);
        $inscripciones = evento_federado_inscripciones_all((int) $evento['id']);
        $deportistasElegibles = evento_federado_deportistas_elegibles($evento);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$schemaReady) {
            $errors[] = 'Falta aplicar la migracion de eventos federados en esta base de datos.';
        } else {
            $postAction = $_POST['action'] ?? $action;
            $eventoId = (int) ($_POST['id'] ?? ($_POST['evento_id'] ?? 0));
            $form = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'nivel' => trim($_POST['nivel'] ?? ''),
                'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
                'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
                'lugar' => trim($_POST['lugar'] ?? ''),
                'costo_inscripcion' => (float) str_replace(',', '.', (string) ($_POST['costo_inscripcion'] ?? '0')),
                'cupo' => (int) ($_POST['cupo'] ?? 0),
                'estado' => trim($_POST['estado'] ?? 'borrador'),
                'observaciones' => trim($_POST['observaciones'] ?? ''),
            ];

            if ($postAction === 'delete') {
                if ($eventoId > 0) {
                    evento_federado_delete($eventoId);
                }
                redirect(base_url('/?page=eventos&flash=deleted'));
            }

            if ($postAction === 'inscribir') {
                $inscripcionDeportistaId = (int) ($_POST['deportista_id'] ?? 0);
                $inscripcionData = [
                    'deportista_modalidades_competencia_id' => (int) ($_POST['deportista_modalidades_competencia_id'] ?? 0),
                    'fecha_inscripcion' => trim((string) ($_POST['fecha_inscripcion'] ?? '')),
                    'monto' => (float) str_replace(',', '.', (string) ($_POST['monto'] ?? '0')),
                    'estado_pago' => trim((string) ($_POST['estado_pago'] ?? 'pendiente')),
                    'referencia' => trim((string) ($_POST['referencia'] ?? '')),
                    'observaciones' => trim((string) ($_POST['observaciones_inscripcion'] ?? '')),
                ];

                if ($eventoId <= 0) {
                    $errors[] = 'No se encontro el evento.';
                } elseif ($inscripcionDeportistaId <= 0) {
                    $errors[] = 'Debes seleccionar un deportista.';
                } else {
                    try {
                        evento_federado_inscribir($eventoId, $inscripcionDeportistaId, $inscripcionData);
                        redirect(base_url('/?page=eventos&action=show&id=' . $eventoId . '&flash=registered'));
                    } catch (Throwable $e) {
                        $errors[] = $e->getMessage() !== '' ? $e->getMessage() : 'No se pudo registrar la inscripcion.';
                    }
                }

                $evento = $eventoId > 0 ? evento_federado_find($eventoId) : null;
                if ($evento !== null) {
                    $eventoForm = array_merge($blankEvento, $evento);
                    $inscripciones = evento_federado_inscripciones_all((int) $evento['id']);
                    $deportistasElegibles = evento_federado_deportistas_elegibles($evento);
                }
            } else {
                if ($form['nombre'] === '') {
                    $errors[] = 'El nombre del evento es obligatorio.';
                }
                if ($form['nivel'] === '') {
                    $errors[] = 'Debes seleccionar un nivel.';
                }
                if ($form['fecha_inicio'] === '') {
                    $errors[] = 'La fecha de inicio es obligatoria.';
                }
                if ($form['fecha_fin'] !== '' && $form['fecha_fin'] < $form['fecha_inicio']) {
                    $errors[] = 'La fecha de termino no puede ser menor a la fecha de inicio.';
                }
                if (!in_array($form['estado'], ['borrador', 'abierto', 'cerrado', 'finalizado'], true)) {
                    $errors[] = 'El estado del evento no es valido.';
                }
                if ($form['costo_inscripcion'] < 0) {
                    $errors[] = 'El costo de inscripcion no puede ser negativo.';
                }
                if ($form['cupo'] < 0) {
                    $errors[] = 'El cupo no puede ser negativo.';
                }

                if (empty($errors)) {
                    if ($postAction === 'create') {
                        $newId = evento_federado_create($form);
                        redirect(base_url('/?page=eventos&action=show&id=' . $newId . '&flash=created'));
                    }

                    if ($postAction === 'edit') {
                        if ($eventoId > 0) {
                            evento_federado_update($eventoId, $form);
                            redirect(base_url('/?page=eventos&action=show&id=' . $eventoId . '&flash=updated'));
                        }
                        $errors[] = 'No se encontro el evento.';
                    }
                }

                $eventoForm = array_merge($blankEvento, $form, ['id' => $eventoId]);
                if ($eventoId > 0) {
                    $evento = evento_federado_find($eventoId);
                    if ($evento !== null) {
                        $inscripciones = evento_federado_inscripciones_all((int) $evento['id']);
                        $deportistasElegibles = evento_federado_deportistas_elegibles($evento);
                    }
                }
            }
        }
    }

    $view = 'eventos';
    render($view, [
        'title' => page_title('eventos'),
        'page' => $page,
        'action' => $action,
        'flash' => $flash,
        'errors' => $errors,
        'schema_ready' => $schemaReady,
        'eventos' => $eventos,
        'evento' => $evento,
        'evento_form' => $eventoForm,
        'inscripciones' => $inscripciones,
        'deportistas_elegibles' => $deportistasElegibles,
        'niveles_evento' => $nivelesEvento,
    ]);
    exit;
}

if ($page === 'competencias') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = [
            'nivel_id' => (int) ($_POST['nivel_id'] ?? 0),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
            'fecha_fin' => trim($_POST['fecha_fin'] ?? ''),
            'lugar' => trim($_POST['lugar'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
        ];

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                competencia_delete($id);
            }
            redirect(base_url('/?page=competencias&flash=deleted'));
        }

        $errors = [];
        if ($form['nivel_id'] <= 0) {
            $errors[] = 'Debes seleccionar un nivel.';
        }
        if ($form['nombre'] === '') {
            $errors[] = 'El nombre de la competencia es obligatorio.';
        }
        if ($form['fecha_inicio'] === '') {
            $errors[] = 'La fecha de inicio es obligatoria.';
        }
        if ($form['fecha_fin'] !== '' && $form['fecha_inicio'] !== '' && $form['fecha_fin'] < $form['fecha_inicio']) {
            $errors[] = 'La fecha de termino no puede ser menor a la fecha de inicio.';
        }

        if (empty($errors)) {
            if ($action === 'create') {
                competencia_create($form);
                redirect(base_url('/?page=competencias&flash=created'));
            }

            if ($action === 'edit') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    competencia_update($id, $form);
                    redirect(base_url('/?page=competencias&flash=updated'));
                }
                $errors[] = 'No se encontro la competencia.';
            }
        }

        $view = 'competencias/form';
        render($view, [
            'title' => page_title('competencias'),
            'page' => $page,
            'action' => $action,
            'errors' => $errors,
            'niveles' => niveles_competencias_options(),
            'competencia' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'competencias/form';
        render($view, [
            'title' => 'Nueva competencia - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'niveles' => niveles_competencias_options(),
            'competencia' => [
                'id' => 0,
                'nivel_id' => 0,
                'nombre' => '',
                'fecha_inicio' => '',
                'fecha_fin' => '',
                'lugar' => '',
                'observaciones' => '',
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $competencia = $id > 0 ? competencia_find($id) : null;
        if ($competencia === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'competencias/form';
        render($view, [
            'title' => 'Editar competencia - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'niveles' => niveles_competencias_options(),
            'competencia' => $competencia,
        ]);
        exit;
    }

    $view = 'competencias/index';
    render($view, [
        'title' => 'Competencias - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'competencias' => competencias_all(),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'certificados') {
    $action = $_GET['action'] ?? 'search';
    $rut = format_rut($_POST['rut'] ?? $_GET['rut'] ?? '');
    $errors = [];
    $deportista = null;
    $competencias = [];

    if ($action === 'pdf') {
        if ($rut === '') {
            $errors[] = 'Debes indicar un RUT.';
        } elseif (!is_valid_rut($rut)) {
            $errors[] = 'El RUT ingresado no es valido.';
        } else {
            $deportista = deportista_por_rut($rut);
            if ($deportista === null) {
                $errors[] = 'No existe un deportista asociado a ese RUT.';
            }
        }

        if (empty($errors) && $deportista !== null) {
            $type = $_GET['type'] ?? 'permanencia';

            if ($type === 'permanencia') {
                certificado_emitir_permanencia_pdf($deportista);
            } elseif ($type === 'competencia') {
                $competenciaId = (int) ($_GET['competencia_id'] ?? 0);
                $competencia = $competenciaId > 0
                    ? competencia_find_por_deportista($competenciaId, (int) $deportista['id'])
                    : null;

                if ($competencia === null) {
                    $errors[] = 'No se encontro la competencia solicitada para este deportista.';
                } else {
                    certificado_emitir_competencia_pdf($deportista, $competencia);
                }
            } else {
                $errors[] = 'Tipo de certificado no soportado.';
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($rut === '') {
            $errors[] = 'Debes indicar un RUT.';
        } elseif (!is_valid_rut($rut)) {
            $errors[] = 'El RUT ingresado no es valido.';
        } else {
            $deportista = deportista_por_rut($rut);
            if ($deportista === null) {
                $errors[] = 'No existe un deportista asociado a ese RUT.';
            } else {
                $competencias = competencias_por_deportista((int) $deportista['id']);
            }
        }
    }

    if ($deportista !== null && empty($competencias)) {
        $competencias = competencias_por_deportista((int) $deportista['id']);
    }

    $view = 'certificados/index';
    render($view, [
        'title' => 'Certificados - Club MaiTeam',
        'page' => $page,
        'rut' => $rut,
        'errors' => $errors,
        'deportista' => $deportista,
        'competencias' => $competencias,
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'cuotas') {
    $action = $_GET['action'] ?? 'list';
    $flash = $_GET['flash'] ?? null;
    $periodoFiltroInput = trim($_GET['periodo'] ?? '');
    $periodoFiltro = periodo_mensual_valido($periodoFiltroInput) ? $periodoFiltroInput : '';
    $estadoFiltroInput = trim($_GET['estado'] ?? '');
    $estadoFiltro = in_array($estadoFiltroInput, ['pendiente', 'pagado'], true) ? $estadoFiltroInput : '';
    $buildFiltroQuery = static function (string $periodo, string $estado): string {
        $params = [];
        if ($periodo !== '') {
            $params['periodo'] = $periodo;
        }
        if ($estado !== '') {
            $params['estado'] = $estado;
        }
        if (empty($params)) {
            return '';
        }
        return '&' . http_build_query($params);
    };
    $validarRangoMarzoDiciembre = static function (string $periodoInicio, string $periodoFin, array &$errors): void {
        if (!periodo_mensual_valido($periodoInicio) || !periodo_mensual_valido($periodoFin)) {
            return;
        }

        $yearInicio = (int) substr($periodoInicio, 0, 4);
        $yearFin = (int) substr($periodoFin, 0, 4);
        $mesInicio = (int) substr($periodoInicio, 5, 2);
        $mesFin = (int) substr($periodoFin, 5, 2);

        if ($yearInicio !== $yearFin) {
            $errors[] = 'La generacion debe ser dentro del mismo ano.';
            return;
        }

        if ($mesInicio < 3 || $mesFin < 3) {
            $errors[] = 'Solo se permite generar cuotas desde marzo a diciembre.';
        }
    };
    $flashCreated = (int) ($_GET['created'] ?? 0);
    $flashSkipped = (int) ($_GET['skipped'] ?? 0);
    $flashApoderados = (int) ($_GET['apoderados'] ?? 0);
    $defaultPeriodoInicio = date('Y') . '-03';
    $defaultPeriodoFin = date('Y') . '-12';
    $generador = [
        'periodo_inicio' => $defaultPeriodoInicio,
        'periodo_fin' => $defaultPeriodoFin,
        'monto' => 3000.0,
    ];
    $errorsGenerador = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $periodoFiltroPost = trim($_POST['periodo_filtro'] ?? '');
            $estadoFiltroPost = trim($_POST['estado_filtro'] ?? '');
            $periodoFiltroRedirect = periodo_mensual_valido($periodoFiltroPost) ? $periodoFiltroPost : '';
            $estadoFiltroRedirect = in_array($estadoFiltroPost, ['pendiente', 'pagado'], true) ? $estadoFiltroPost : '';
            if ($id > 0) {
                cuota_delete($id);
            }
            redirect(base_url('/?page=cuotas&flash=deleted'
                . $buildFiltroQuery($periodoFiltroRedirect, $estadoFiltroRedirect)));
        }

        if ($action === 'generate_all') {
            $generador = [
                'periodo_inicio' => trim($_POST['periodo_inicio'] ?? ''),
                'periodo_fin' => trim($_POST['periodo_fin'] ?? ''),
                'monto' => (float) ($_POST['monto'] ?? 3000),
            ];

            if (!periodo_mensual_valido($generador['periodo_inicio'])) {
                $errorsGenerador[] = 'El mes de inicio no es valido.';
            }
            if (!periodo_mensual_valido($generador['periodo_fin'])) {
                $errorsGenerador[] = 'El mes de fin no es valido.';
            }
            if (periodo_mensual_valido($generador['periodo_inicio'])
                && periodo_mensual_valido($generador['periodo_fin'])
                && $generador['periodo_inicio'] > $generador['periodo_fin']) {
                $errorsGenerador[] = 'El mes de inicio no puede ser mayor al mes de fin.';
            }
            $validarRangoMarzoDiciembre($generador['periodo_inicio'], $generador['periodo_fin'], $errorsGenerador);
            if ($generador['monto'] <= 0) {
                $errorsGenerador[] = 'El monto debe ser mayor a 0.';
            }

            if (empty($errorsGenerador)) {
                $resultado = cuotas_create_range_for_all_apoderados(
                    $generador['periodo_inicio'],
                    $generador['periodo_fin'],
                    $generador['monto']
                );

                redirect(base_url('/?page=cuotas&flash=generated_all'
                    . '&created=' . (int) $resultado['created']
                    . '&skipped=' . (int) $resultado['skipped']
                    . '&apoderados=' . (int) $resultado['apoderados']
                    . $buildFiltroQuery($periodoFiltro, $estadoFiltro)));
            }

            $view = 'cuotas/index';
            render($view, [
                'title' => 'Cuotas socios - Club MaiTeam',
                'page' => $page,
                'flash' => $flash,
                'flash_created' => $flashCreated,
                'flash_skipped' => $flashSkipped,
                'flash_apoderados' => $flashApoderados,
                'errors_generador' => $errorsGenerador,
                'generador' => $generador,
                'periodo_filtro' => $periodoFiltro,
                'estado_filtro' => $estadoFiltro,
                'cuotas' => cuotas_all($periodoFiltro, $estadoFiltro),
            ]);
            exit;
        }

        if ($action === 'edit') {
            $periodoFiltroPost = trim($_POST['periodo_filtro'] ?? '');
            $estadoFiltroPost = trim($_POST['estado_filtro'] ?? '');
            $periodoFiltroEdit = periodo_mensual_valido($periodoFiltroPost) ? $periodoFiltroPost : $periodoFiltro;
            $estadoFiltroEdit = in_array($estadoFiltroPost, ['pendiente', 'pagado'], true) ? $estadoFiltroPost : $estadoFiltro;
            $form = [
                'apoderado_id' => (int) ($_POST['apoderado_id'] ?? 0),
                'periodo' => trim($_POST['periodo'] ?? ''),
                'fecha_pago' => trim($_POST['fecha_pago'] ?? ''),
                'monto' => (float) ($_POST['monto'] ?? 3000),
                'estado' => $_POST['estado'] ?? 'pendiente',
            ];
            $errors = [];

            if ($form['apoderado_id'] <= 0) {
                $errors[] = 'Debes seleccionar un apoderado.';
            }
            if (!periodo_mensual_valido($form['periodo'])) {
                $errors[] = 'El periodo no es valido.';
            }
            if (!in_array($form['estado'], ['pendiente', 'pagado'], true)) {
                $errors[] = 'El estado seleccionado no es valido.';
            }
            if ($form['monto'] <= 0) {
                $errors[] = 'El monto debe ser mayor a 0.';
            }
            if ($form['estado'] !== 'pagado') {
                $form['fecha_pago'] = '';
            }

            if (empty($errors)) {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id > 0) {
                    cuota_update($id, $form);
                    redirect(base_url('/?page=cuotas&flash=updated'
                        . $buildFiltroQuery($periodoFiltroEdit, $estadoFiltroEdit)));
                }
                $errors[] = 'No se encontro la cuota.';
            }

            $view = 'cuotas/form';
            render($view, [
                'title' => page_title('cuotas'),
                'page' => $page,
                'action' => $action,
                'errors' => $errors,
                'apoderados' => apoderados_all(),
                'periodo_filtro' => $periodoFiltroEdit,
                'estado_filtro' => $estadoFiltroEdit,
                'cuota' => array_merge(['id' => (int) ($_POST['id'] ?? 0)], $form),
            ]);
            exit;
        }

        $form = [
            'apoderado_id' => (int) ($_POST['apoderado_id'] ?? 0),
            'periodo_inicio' => trim($_POST['periodo_inicio'] ?? ''),
            'periodo_fin' => trim($_POST['periodo_fin'] ?? ''),
            'monto' => (float) ($_POST['monto'] ?? 3000),
        ];
        $periodoFiltroPost = trim($_POST['periodo_filtro'] ?? '');
        $estadoFiltroPost = trim($_POST['estado_filtro'] ?? '');
        $periodoFiltroCreate = periodo_mensual_valido($periodoFiltroPost) ? $periodoFiltroPost : '';
        $estadoFiltroCreate = in_array($estadoFiltroPost, ['pendiente', 'pagado'], true) ? $estadoFiltroPost : '';
        $errors = [];

        if ($form['apoderado_id'] <= 0) {
            $errors[] = 'Debes seleccionar un apoderado.';
        }
        if (!periodo_mensual_valido($form['periodo_inicio'])) {
            $errors[] = 'El mes de inicio no es valido.';
        }
        if (!periodo_mensual_valido($form['periodo_fin'])) {
            $errors[] = 'El mes de fin no es valido.';
        }
        if (periodo_mensual_valido($form['periodo_inicio'])
            && periodo_mensual_valido($form['periodo_fin'])
            && $form['periodo_inicio'] > $form['periodo_fin']) {
            $errors[] = 'El mes de inicio no puede ser mayor al mes de fin.';
        }
        $validarRangoMarzoDiciembre($form['periodo_inicio'], $form['periodo_fin'], $errors);
        if ($form['monto'] <= 0) {
            $errors[] = 'El monto debe ser mayor a 0.';
        }

        if (empty($errors)) {
            $resultado = cuotas_create_range_for_apoderado(
                $form['apoderado_id'],
                $form['periodo_inicio'],
                $form['periodo_fin'],
                $form['monto']
            );

            redirect(base_url('/?page=cuotas&flash=generated'
                . '&created=' . (int) $resultado['created']
                . '&skipped=' . (int) $resultado['skipped']
                . $buildFiltroQuery($periodoFiltroCreate, $estadoFiltroCreate)));
        }

        $view = 'cuotas/form';
        render($view, [
            'title' => page_title('cuotas'),
            'page' => $page,
            'action' => 'create',
            'errors' => $errors,
            'apoderados' => apoderados_all(),
            'periodo_filtro' => $periodoFiltroCreate,
            'estado_filtro' => $estadoFiltroCreate,
            'cuota' => array_merge(['id' => 0], $form),
        ]);
        exit;
    }

    if ($action === 'create') {
        $view = 'cuotas/form';
        render($view, [
            'title' => 'Generar cuotas socio - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'apoderados' => apoderados_all(),
            'periodo_filtro' => $periodoFiltro,
            'estado_filtro' => $estadoFiltro,
            'cuota' => [
                'id' => 0,
                'apoderado_id' => 0,
                'periodo_inicio' => $defaultPeriodoInicio,
                'periodo_fin' => $defaultPeriodoFin,
                'monto' => '3000',
            ],
        ]);
        exit;
    }

    if ($action === 'edit') {
        $id = (int) ($_GET['id'] ?? 0);
        $cuota = $id > 0 ? cuota_find($id) : null;
        if ($cuota === null) {
            render('404', [
                'title' => page_title('404'),
                'page' => $page,
            ]);
            exit;
        }

        $view = 'cuotas/form';
        render($view, [
            'title' => 'Editar cuota socio - Club MaiTeam',
            'page' => $page,
            'action' => $action,
            'errors' => [],
            'apoderados' => apoderados_all(),
            'periodo_filtro' => $periodoFiltro,
            'estado_filtro' => $estadoFiltro,
            'cuota' => $cuota,
        ]);
        exit;
    }

    $view = 'cuotas/index';
    render($view, [
        'title' => 'Cuotas socios - Club MaiTeam',
        'page' => $page,
        'flash' => $flash,
        'flash_created' => $flashCreated,
        'flash_skipped' => $flashSkipped,
        'flash_apoderados' => $flashApoderados,
        'errors_generador' => $errorsGenerador,
        'generador' => $generador,
        'periodo_filtro' => $periodoFiltro,
        'estado_filtro' => $estadoFiltro,
        'cuotas' => cuotas_all($periodoFiltro, $estadoFiltro),
    ]);
    exit;
}

$page = $_GET['page'] ?? 'home';

if ($page === 'reportes') {
    $periodo = $_GET['periodo'] ?? date('Y-m');
    $periodo = preg_match('/^\\d{4}-\\d{2}$/', $periodo) ? $periodo : date('Y-m');
    $tipo = $_GET['tipo'] ?? 'apoderados';
    $tipo = in_array($tipo, ['apoderados', 'coaches'], true) ? $tipo : 'apoderados';
    $soloPositivos = isset($_GET['solo_positivos']) && $_GET['solo_positivos'] === '1';

    if ($tipo === 'coaches') {
        $view = 'reportes/coaches';
        render($view, [
            'title' => 'Reporte por coach - Club MaiTeam',
            'page' => $page,
            'periodo' => $periodo,
            'solo_positivos' => $soloPositivos,
            'resultados' => reportes_coaches($periodo, $soloPositivos),
        ]);
        exit;
    }

    $view = 'reportes/apoderados';
    render($view, [
        'title' => 'Reporte por apoderado - Club MaiTeam',
        'page' => $page,
        'periodo' => $periodo,
        'solo_positivos' => $soloPositivos,
        'resultados' => reportes_apoderados($periodo, $soloPositivos),
    ]);
    exit;
}

$routes = [
    'home' => 'home',
    'eventos' => 'eventos',
];

$view = $routes[$page] ?? '404';

$renderData = [
    'title' => page_title($view),
    'page' => $page,
];

if ($view === 'home') {
    $kpiPeriodo = $_GET['kpi_periodo'] ?? date('Y-m');
    $kpiPeriodo = preg_match('/^\d{4}-\d{2}$/', (string) $kpiPeriodo) ? (string) $kpiPeriodo : date('Y-m');
    $renderData['kpi_periodo'] = $kpiPeriodo;
    $renderData['kpi_cuotas'] = cuotas_kpi_mensual($kpiPeriodo);
}

render($view, $renderData);
