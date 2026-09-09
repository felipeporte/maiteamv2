<?php

declare(strict_types=1);

function evento_federado_hojas_schema_ready(): bool
{
    static $ready = null;

    if ($ready !== null) {
        return $ready;
    }

    if (!eventos_federados_schema_ready()) {
        $ready = false;
        return $ready;
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) '
        . 'FROM information_schema.tables '
        . 'WHERE table_schema = DATABASE() '
        . 'AND table_name = "evento_federado_hojas_elementos"'
    );
    $stmt->execute();

    $ready = (int) $stmt->fetchColumn() === 1;

    return $ready;
}

function evento_federado_hoja_template_catalog(): array
{
    return [
        'freeskating_short' => [
            'label' => 'Freeskating - Short Program',
            'short_label' => 'Short Program',
            'rows' => 7,
            'program_label' => 'Short - Choreography / Music',
            'program_short' => 'Short',
            'codes' => [
                'CoJ' => 'Combination Jump',
                'SJu' => 'Solo Jump',
                'CSp' => 'Combination Spin',
                'SSp' => 'Solo Spin',
                'FoSq' => 'Footwork Sequence',
                'ChSt' => 'Choreo Step Sequence',
            ],
        ],
        'freeskating_free' => [
            'label' => 'Freeskating - Long Program',
            'short_label' => 'Long Program',
            'rows' => 12,
            'program_label' => 'Long - Choreography / Music',
            'program_short' => 'Long',
            'codes' => [
                'CoJ' => 'Combination Jump',
                'SJu' => 'Solo Jump',
                'CSp' => 'Combination Spin',
                'SSp' => 'Solo Spin',
                'FoSq' => 'Footwork Sequence',
                'ChSt' => 'Choreo Step Sequence',
            ],
        ],
        'solo_dance_style' => [
            'label' => 'Solo Dance - Style Dance',
            'short_label' => 'Style Dance',
            'rows' => 5,
            'program_label' => 'Style - Choreography / Music',
            'program_short' => 'Style',
            'codes' => [
                'FoSq' => 'Footwork Sequence',
                'ASq' => 'Artistic Sequence',
                'Tr' => 'Travelling Sequence',
                'DSS' => 'Dance Step Sequence',
                'ClSq' => 'Cluster Sequence',
                '1SClSq' => 'One Set Cluster Sequence',
                'ChStS' => 'Choreo Stop / Step / Pose',
                'PtSq' => 'Pattern Sequence',
            ],
        ],
        'solo_dance_free' => [
            'label' => 'Solo Dance - Free Dance',
            'short_label' => 'Free Dance',
            'rows' => 5,
            'program_label' => 'Free - Choreography / Music',
            'program_short' => 'Free',
            'codes' => [
                'FoSq' => 'Footwork Sequence',
                'ASq' => 'Artistic Sequence',
                'Tr' => 'Travelling Sequence',
                'DSS' => 'Dance Step Sequence',
                'ClSq' => 'Cluster Sequence',
                '1SClSq' => 'One Set Cluster Sequence',
                'ChStS' => 'Choreo Stop',
                'PtSq' => 'Pattern Sequence',
            ],
        ],
        'nacional_formativo_escuela_d' => [
            'label' => 'Nacional - Formativo / Escuela D',
            'short_label' => 'Nacional',
            'rows' => 10,
            'program_label' => 'Coreografía',
            'program_short' => 'Coreografía',
            'codes' => [
                'SFig' => 'Pirueta Individual',
                'CoFig' => 'Combinación de piruetas',
                'SSSq' => 'Deslizamiento en S',
                'FoSq' => 'Footwork Sequence',
                'ChStS' => 'Choreo Stop',
                'SJu' => 'Solo Jump',
                'CoJ' => 'Combo Jump',
                'SSp' => 'Solo Spin',
                'CSp' => 'Combo Spin',
            ],
        ],
    ];
}

function evento_federado_hoja_normalize_rule_value(?string $value): string
{
    $normalized = strtolower(trim((string) $value));
    if ($normalized === '') {
        return '';
    }

    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $normalized);
    if ($transliterated !== false) {
        $normalized = $transliterated;
    }

    $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized;
    $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);

    return match ($normalized) {
        'mini' => 'minis',
        'unico', '-' => 'unico',
        default => $normalized,
    };
}

function evento_federado_hoja_program_matrix(): array
{
    return [
        'international' => [
            'freeskating' => [
                'tots' => ['long'],
                'minis' => ['long'],
                '*' => ['short', 'long'],
            ],
            'solo_dance' => [
                'tots' => ['compulsory', 'free_dance'],
                'minis' => ['compulsory', 'free_dance'],
                'espoir' => ['compulsory', 'free_dance'],
                '*' => ['style_dance', 'free_dance'],
            ],
        ],
        'promotional' => [
            'freeskating' => [
                'basic' => [
                    '*' => ['long'],
                ],
                'intermediate' => [
                    '*' => ['long'],
                ],
            ],
            'solo_dance' => [
                'basic' => [
                    '*' => ['compulsory', 'free_dance'],
                ],
                'intermediate' => [
                    'minis' => ['compulsory', 'free_dance'],
                    'espoir' => ['compulsory', 'free_dance'],
                    'cadet' => ['compulsory', 'free_dance'],
                    '*' => ['style_dance', 'free_dance'],
                ],
            ],
        ],
        'formativo' => [
            'freeskating' => [
                '*' => ['formativo_escuela'],
            ],
        ],
        'escuela' => [
            'freeskating' => [
                'd' => ['formativo_escuela'],
                'c' => ['long'],
                'b' => ['long'],
            ],
            'solo_dance' => [
                'd' => ['compulsory'],
                'c' => ['compulsory', 'free_dance'],
            ],
        ],
    ];
}

function evento_federado_hoja_program_definitions(): array
{
    return [
        'short' => [
            'label' => 'Short',
            'template' => 'freeskating_short',
            'requires_sheet' => true,
        ],
        'long' => [
            'label' => 'Long',
            'template' => 'freeskating_free',
            'requires_sheet' => true,
        ],
        'compulsory' => [
            'label' => 'Compulsory',
            'template' => null,
            'requires_sheet' => false,
        ],
        'free_dance' => [
            'label' => 'Free Dance',
            'template' => 'solo_dance_free',
            'requires_sheet' => true,
        ],
        'style_dance' => [
            'label' => 'Style Dance',
            'template' => 'solo_dance_style',
            'requires_sheet' => true,
        ],
        'formativo_escuela' => [
            'label' => 'Programa único',
            'template' => 'nacional_formativo_escuela_d',
            'requires_sheet' => true,
        ],
    ];
}

function evento_federado_hoja_programas(array $context): array
{
    $level = evento_federado_hoja_normalize_rule_value((string) (
        $context['nivel_competencia'] ?? $context['evento_nivel'] ?? ''
    ));
    $sublevel = evento_federado_hoja_normalize_rule_value((string) (
        $context['subnivel_competencia'] ?? $context['subnivel'] ?? ''
    ));
    $category = evento_federado_hoja_normalize_rule_value((string) (
        $context['categoria_competencia'] ?? $context['categoria'] ?? ''
    ));
    $modality = str_replace(
        ' ',
        '_',
        evento_federado_hoja_normalize_rule_value((string) ($context['modalidad_codigo'] ?? ''))
    );
    $matrix = evento_federado_hoja_program_matrix();
    $programCodes = [];

    if (isset($matrix[$level][$modality])) {
        $levelRules = $matrix[$level][$modality];
        if (isset($levelRules[$sublevel])) {
            $categoryRules = $levelRules[$sublevel];
            $programCodes = array_is_list($categoryRules)
                ? $categoryRules
                : ($categoryRules[$category] ?? $categoryRules['*'] ?? []);
        } else {
            $programCodes = $levelRules[$category] ?? $levelRules['*'] ?? [];
        }
    }

    if ($programCodes === []) {
        $programCodes = match ($modality) {
            'solo_dance' => ['free_dance'],
            'freeskating' => ['long'],
            default => [],
        };
    }

    $definitions = evento_federado_hoja_program_definitions();
    $programs = [];
    foreach (array_values(array_unique($programCodes)) as $programCode) {
        if (!isset($definitions[$programCode])) {
            continue;
        }

        $program = $definitions[$programCode];
        $program['code'] = $programCode;
        $programs[] = $program;
    }

    return $programs;
}

function evento_federado_hoja_programs_require_sheet(array $context): bool
{
    foreach (evento_federado_hoja_programas($context) as $program) {
        if (!empty($program['requires_sheet'])) {
            return true;
        }
    }

    return false;
}

function evento_federado_hoja_default_template(array $context): string
{
    foreach (evento_federado_hoja_programas($context) as $program) {
        if (!empty($program['requires_sheet']) && is_string($program['template'] ?? null)) {
            return $program['template'];
        }
    }

    return 'freeskating_free';
}

function evento_federado_hoja_template_label(string $templateCode): string
{
    $catalog = evento_federado_hoja_template_catalog();

    return $catalog[$templateCode]['label'] ?? $templateCode;
}

function evento_federado_hoja_template_rows(string $templateCode): int
{
    $catalog = evento_federado_hoja_template_catalog();

    return max(1, (int) ($catalog[$templateCode]['rows'] ?? 9));
}

function evento_federado_hoja_program_label(string $templateCode): string
{
    $catalog = evento_federado_hoja_template_catalog();

    return (string) ($catalog[$templateCode]['program_label'] ?? 'Choreography / Music');
}

function evento_federado_hoja_program_filename(string $templateCode): string
{
    return match ($templateCode) {
        'freeskating_short' => 'Short',
        'freeskating_free' => 'Long',
        'solo_dance_free' => 'Free Dance',
        'solo_dance_style' => 'Style Dance',
        default => 'Contenido Tecnico',
    };
}

function evento_federado_hoja_code_label_map(): array
{
    $labels = [];

    foreach (evento_federado_hoja_template_catalog() as $template) {
        foreach (($template['codes'] ?? []) as $code => $label) {
            $labels[(string) $code] = (string) $label;
        }
    }

    return $labels;
}

function evento_federado_hoja_codes_for_template(string $templateCode): array
{
    $catalog = evento_federado_hoja_template_catalog();

    return $catalog[$templateCode]['codes'] ?? [];
}

function evento_federado_hoja_default_categoria_label(array $context): string
{
    $categoria = evento_federado_hoja_clean_text((string) (
        $context['categoria_competencia'] ?? $context['categoria'] ?? ''
    ));

    if ($categoria === '') {
        return 'Sin categoria - Ladie';
    }

    return ucfirst($categoria) . ' - Ladie';
}

function evento_federado_hoja_world_skate_categoria_label(array $context): string
{
    $categoria = evento_federado_hoja_clean_text((string) (
        $context['categoria_competencia'] ?? $context['categoria'] ?? ''
    ));

    if ($categoria === '') {
        return 'Sin categoria - Ladies';
    }

    return ucfirst($categoria) . ' - Ladies';
}

function evento_federado_hoja_clean_text(?string $value): string
{
    $text = trim((string) $value);
    if ($text === '') {
        return '';
    }

    $clean = preg_replace('/\s+/u', ' ', $text);

    return $clean !== null ? $clean : $text;
}

function evento_federado_hoja_decode_rows(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return [];
    }

    $rows = [];
    foreach ($decoded as $row) {
        if (!is_array($row)) {
            continue;
        }

        $rows[] = [
            'time' => evento_federado_hoja_clean_text($row['time'] ?? ''),
            'code' => evento_federado_hoja_clean_text($row['code'] ?? ''),
            'element' => evento_federado_hoja_clean_text($row['element'] ?? ''),
            'notes' => evento_federado_hoja_clean_text($row['notes'] ?? ''),
        ];
    }

    return $rows;
}

function evento_federado_hoja_pad_rows(array $rows, string $templateCode): array
{
    $targetCount = evento_federado_hoja_template_rows($templateCode);
    $rows = array_values($rows);

    while (count($rows) < $targetCount) {
        $rows[] = [
            'time' => '',
            'code' => '',
            'element' => '',
            'notes' => '',
        ];
    }

    return $rows;
}

function evento_federado_hoja_filter_rows(array $rows, string $templateCode = ''): array
{
    $filtered = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $normalized = [
            'time' => evento_federado_hoja_clean_text($row['time'] ?? ''),
            'code' => evento_federado_hoja_clean_text($row['code'] ?? ''),
            'element' => evento_federado_hoja_clean_text($row['element'] ?? ''),
            'notes' => evento_federado_hoja_clean_text($row['notes'] ?? ''),
        ];

        if ($normalized['time'] === '' && $normalized['code'] === '' && $normalized['element'] === '' && $normalized['notes'] === '') {
            continue;
        }

        $filtered[] = $normalized;
    }

    $maxRows = $templateCode !== '' ? evento_federado_hoja_template_rows($templateCode) : 9;

    return array_slice($filtered, 0, $maxRows);
}

function evento_federado_hoja_context_find(int $eventoId, int $inscripcionId, ?string $templateCode = null): ?array
{
    if (!evento_federado_hojas_schema_ready()) {
        return null;
    }

    $templateCode = trim((string) $templateCode);
    $templateFilter = $templateCode !== '' ? ' AND h.plantilla_codigo = :plantilla_codigo' : '';
    $stmt = db()->prepare(
        'SELECT ei.id AS inscripcion_id, ei.evento_id, ei.deportista_id, ei.deportista_modalidades_competencia_id, '
        . 'ei.modalidad_competencia_id, ei.subnivel AS inscripcion_subnivel, ei.categoria AS inscripcion_categoria, '
        . 'ei.fecha_inscripcion, ei.monto, ei.estado_pago, ei.referencia, ei.observaciones AS inscripcion_observaciones, '
        . 'e.nombre AS evento_nombre, e.nivel AS evento_nivel, e.fecha_inicio AS evento_fecha_inicio, e.fecha_fin AS evento_fecha_fin, '
        . 'd.nombre AS deportista_nombre, d.rut AS deportista_rut, '
        . 'a.nombre AS apoderado_nombre, '
        . 'mc.nombre AS modalidad_nombre, mc.codigo AS modalidad_codigo, '
        . 'dmc.nivel AS nivel_competencia, dmc.subnivel AS subnivel_competencia, dmc.categoria AS categoria_competencia, '
        . 'h.id AS hoja_id, h.plantilla_codigo, h.competidor_nombre, h.categoria_label, h.club, h.representing, '
        . 'h.choreography, h.observaciones AS hoja_observaciones, h.elementos_json, h.updated_at AS hoja_updated_at '
        . 'FROM evento_federado_inscripciones ei '
        . 'INNER JOIN eventos_federados e ON e.id = ei.evento_id '
        . 'INNER JOIN deportistas d ON d.id = ei.deportista_id '
        . 'INNER JOIN apoderados a ON a.id = ei.apoderado_id '
        . 'LEFT JOIN deportista_modalidades_competencia dmc ON dmc.id = ei.deportista_modalidades_competencia_id '
        . 'LEFT JOIN modalidades_competencia mc ON mc.id = COALESCE(ei.modalidad_competencia_id, dmc.modalidad_competencia_id) '
        . 'LEFT JOIN evento_federado_hojas_elementos h ON h.evento_federado_inscripcion_id = ei.id '
        . 'WHERE ei.evento_id = :evento_id AND ei.id = :inscripcion_id '
        . $templateFilter . ' '
        . 'LIMIT 1'
    );
    $params = [
        'evento_id' => $eventoId,
        'inscripcion_id' => $inscripcionId,
    ];
    if ($templateCode !== '') {
        $params['plantilla_codigo'] = $templateCode;
    }
    $stmt->execute($params);

    $row = $stmt->fetch();

    return $row ?: null;
}

function evento_federado_hojas_all(int $eventoId): array
{
    if (!evento_federado_hojas_schema_ready()) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT ei.id AS inscripcion_id, ei.evento_id, ei.deportista_id, ei.deportista_modalidades_competencia_id, '
        . 'ei.modalidad_competencia_id, ei.subnivel AS inscripcion_subnivel, ei.categoria AS inscripcion_categoria, '
        . 'ei.fecha_inscripcion, ei.monto, ei.estado_pago, ei.referencia, ei.observaciones AS inscripcion_observaciones, '
        . 'd.nombre AS deportista_nombre, d.rut AS deportista_rut, '
        . 'a.nombre AS apoderado_nombre, '
        . 'mc.nombre AS modalidad_nombre, mc.codigo AS modalidad_codigo, '
        . 'dmc.nivel AS nivel_competencia, dmc.subnivel AS subnivel_competencia, dmc.categoria AS categoria_competencia, '
        . 'h.id AS hoja_id, h.plantilla_codigo, h.competidor_nombre, h.categoria_label, h.club, h.representing, '
        . 'h.choreography, h.observaciones AS hoja_observaciones, h.elementos_json, h.updated_at AS hoja_updated_at '
        . 'FROM evento_federado_inscripciones ei '
        . 'INNER JOIN deportistas d ON d.id = ei.deportista_id '
        . 'INNER JOIN apoderados a ON a.id = ei.apoderado_id '
        . 'LEFT JOIN deportista_modalidades_competencia dmc ON dmc.id = ei.deportista_modalidades_competencia_id '
        . 'LEFT JOIN modalidades_competencia mc ON mc.id = COALESCE(ei.modalidad_competencia_id, dmc.modalidad_competencia_id) '
        . 'LEFT JOIN evento_federado_hojas_elementos h ON h.evento_federado_inscripcion_id = ei.id '
        . 'WHERE ei.evento_id = :evento_id '
        . 'ORDER BY d.nombre ASC, ei.id ASC'
    );
    $stmt->execute(['evento_id' => $eventoId]);

    return $stmt->fetchAll();
}

function evento_federado_hoja_form_data(array $context): array
{
    $templateCode = trim((string) ($context['plantilla_codigo'] ?? ''));
    $defaultTemplateCode = evento_federado_hoja_default_template($context);
    if (
        $templateCode === ''
        || !array_key_exists($templateCode, evento_federado_hoja_template_catalog())
        || $defaultTemplateCode === 'nacional_formativo_escuela_d'
    ) {
        $templateCode = $defaultTemplateCode;
    }

    $savedRows = evento_federado_hoja_decode_rows((string) ($context['elementos_json'] ?? ''));
    if (empty($savedRows)) {
        $rows = evento_federado_hoja_pad_rows([], $templateCode);
    } else {
        $rows = evento_federado_hoja_pad_rows($savedRows, $templateCode);
    }

    $competidorNombre = evento_federado_hoja_clean_text((string) ($context['competidor_nombre'] ?? ''));
    if ($competidorNombre === '') {
        $competidorNombre = evento_federado_hoja_clean_text((string) ($context['deportista_nombre'] ?? ''));
    }

    $categoriaLabel = $templateCode === 'nacional_formativo_escuela_d'
        ? evento_federado_hoja_default_categoria_label($context)
        : evento_federado_hoja_world_skate_categoria_label($context);

    $club = evento_federado_hoja_clean_text((string) ($context['club'] ?? ''));
    if ($club === '') {
        $club = 'Maiteam';
    } elseif (strcasecmp($club, 'Club MaiTeam') === 0) {
        $club = 'Maiteam';
    }

    $representing = evento_federado_hoja_clean_text((string) ($context['representing'] ?? ''));
    if ($representing === '') {
        $representing = 'Maiteam';
    } elseif (strcasecmp($representing, 'Club MaiTeam') === 0) {
        $representing = 'Maiteam';
    }

    return [
        'evento_id' => (int) ($context['evento_id'] ?? 0),
        'inscripcion_id' => (int) ($context['inscripcion_id'] ?? 0),
        'hoja_id' => (int) ($context['hoja_id'] ?? 0),
        'plantilla_codigo' => $templateCode,
        'plantilla_label' => evento_federado_hoja_template_label($templateCode),
        'programa_label' => evento_federado_hoja_program_label($templateCode),
        'programas' => evento_federado_hoja_programas($context),
        'competidor_nombre' => $competidorNombre,
        'categoria_label' => $categoriaLabel,
        'club' => $club,
        'representing' => $representing,
        'choreography' => evento_federado_hoja_clean_text((string) ($context['choreography'] ?? '')),
        'observaciones' => evento_federado_hoja_clean_text((string) ($context['hoja_observaciones'] ?? $context['inscripcion_observaciones'] ?? '')),
        'rows' => $rows,
        'updated_at' => (string) ($context['hoja_updated_at'] ?? ''),
        'deportista_nombre' => evento_federado_hoja_clean_text((string) ($context['deportista_nombre'] ?? '')),
        'deportista_rut' => evento_federado_hoja_clean_text((string) ($context['deportista_rut'] ?? '')),
        'evento_nombre' => evento_federado_hoja_clean_text((string) ($context['evento_nombre'] ?? '')),
        'evento_nivel' => evento_federado_hoja_clean_text((string) ($context['evento_nivel'] ?? '')),
        'modalidad_nombre' => evento_federado_hoja_clean_text((string) ($context['modalidad_nombre'] ?? '')),
        'modalidad_codigo' => evento_federado_hoja_clean_text((string) ($context['modalidad_codigo'] ?? '')),
        'nivel_competencia' => evento_federado_hoja_clean_text((string) ($context['nivel_competencia'] ?? '')),
        'subnivel_competencia' => evento_federado_hoja_clean_text((string) ($context['subnivel_competencia'] ?? '')),
        'categoria_competencia' => evento_federado_hoja_clean_text((string) ($context['categoria_competencia'] ?? '')),
    ];
}

function evento_federado_hoja_template_allowed(array $context, string $templateCode): bool
{
    foreach (evento_federado_hoja_programas($context) as $program) {
        if (($program['template'] ?? null) === $templateCode && !empty($program['requires_sheet'])) {
            return true;
        }
    }

    return false;
}

function evento_federado_hoja_save(int $eventoId, int $inscripcionId, array $data): int
{
    if (!evento_federado_hojas_schema_ready()) {
        throw new RuntimeException('La base de datos no tiene la estructura para hojas de elementos.');
    }

    $context = evento_federado_hoja_context_find($eventoId, $inscripcionId);
    if ($context === null) {
        throw new RuntimeException('No se encontro la inscripcion seleccionada.');
    }
    if (!evento_federado_hoja_programs_require_sheet($context)) {
        throw new RuntimeException('El programa seleccionado no requiere hoja de elementos.');
    }

    $templateCode = evento_federado_hoja_clean_text((string) ($data['plantilla_codigo'] ?? ''));
    if ($templateCode === '' || !array_key_exists($templateCode, evento_federado_hoja_template_catalog())) {
        $templateCode = evento_federado_hoja_default_template($context);
    }
    if (!evento_federado_hoja_template_allowed($context, $templateCode)) {
        throw new RuntimeException('La plantilla seleccionada no corresponde a un programa válido para esta inscripción.');
    }
    $templateContext = evento_federado_hoja_context_find($eventoId, $inscripcionId, $templateCode);
    if ($templateContext !== null) {
        $context = $templateContext;
    }

    $rows = evento_federado_hoja_filter_rows((array) ($data['rows'] ?? []), $templateCode);
    foreach ($rows as &$row) {
        $code = evento_federado_hoja_clean_text((string) ($row['code'] ?? ''));
        if ($code !== '') {
            $row['element'] = evento_federado_hoja_clean_text((string) ($row['element'] ?? ''));
            if ($row['element'] === '') {
                $row['element'] = evento_federado_hoja_code_label_map()[$code] ?? $code;
            }
        }
    }
    unset($row);

    $competidorNombre = evento_federado_hoja_clean_text((string) ($data['competidor_nombre'] ?? ''));
    if ($competidorNombre === '') {
        $competidorNombre = evento_federado_hoja_clean_text((string) ($context['deportista_nombre'] ?? ''));
    }

    $categoriaLabel = $templateCode === 'nacional_formativo_escuela_d'
        ? evento_federado_hoja_default_categoria_label($context)
        : evento_federado_hoja_world_skate_categoria_label($context);

    $club = evento_federado_hoja_clean_text((string) ($data['club'] ?? ''));
    if ($club === '') {
        $club = 'Maiteam';
    } elseif (strcasecmp($club, 'Club MaiTeam') === 0) {
        $club = 'Maiteam';
    }

    $representing = evento_federado_hoja_clean_text((string) ($data['representing'] ?? ''));
    if ($representing === '') {
        $representing = $club;
    }

    $choreography = evento_federado_hoja_clean_text((string) ($data['choreography'] ?? ''));
    $observaciones = evento_federado_hoja_clean_text((string) ($data['observaciones'] ?? ''));
    $payloadRows = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payloadRows === false) {
        $payloadRows = '[]';
    }

    $existingId = (int) ($context['hoja_id'] ?? 0);
    if ($existingId > 0) {
        $stmt = db()->prepare(
            'UPDATE evento_federado_hojas_elementos SET '
            . 'plantilla_codigo = :plantilla_codigo, '
            . 'competidor_nombre = :competidor_nombre, '
            . 'categoria_label = :categoria_label, '
            . 'club = :club, '
            . 'representing = :representing, '
            . 'choreography = :choreography, '
            . 'observaciones = :observaciones, '
            . 'elementos_json = :elementos_json '
            . 'WHERE id = :id'
        );
        $stmt->execute([
            'id' => $existingId,
            'plantilla_codigo' => $templateCode,
            'competidor_nombre' => $competidorNombre,
            'categoria_label' => $categoriaLabel,
            'club' => $club,
            'representing' => $representing,
            'choreography' => $choreography,
            'observaciones' => $observaciones !== '' ? $observaciones : null,
            'elementos_json' => $payloadRows,
        ]);

        return $existingId;
    }

    $stmt = db()->prepare(
        'INSERT INTO evento_federado_hojas_elementos '
        . '(evento_id, evento_federado_inscripcion_id, deportista_id, modalidad_competencia_id, plantilla_codigo, '
        . 'competidor_nombre, categoria_label, club, representing, choreography, observaciones, elementos_json) '
        . 'VALUES '
        . '(:evento_id, :evento_federado_inscripcion_id, :deportista_id, :modalidad_competencia_id, :plantilla_codigo, '
        . ':competidor_nombre, :categoria_label, :club, :representing, :choreography, :observaciones, :elementos_json)'
    );
    $stmt->execute([
        'evento_id' => $eventoId,
        'evento_federado_inscripcion_id' => $inscripcionId,
        'deportista_id' => (int) ($context['deportista_id'] ?? 0),
        'modalidad_competencia_id' => (int) ($context['deportista_modalidades_competencia_id'] ?? 0),
        'plantilla_codigo' => $templateCode,
        'competidor_nombre' => $competidorNombre,
        'categoria_label' => $categoriaLabel,
        'club' => $club,
        'representing' => $representing,
        'choreography' => $choreography,
        'observaciones' => $observaciones !== '' ? $observaciones : null,
        'elementos_json' => $payloadRows,
    ]);

    return (int) db()->lastInsertId();
}

function evento_federado_hoja_emitir_pdf(array $document): void
{
    $assets = certificado_cargar_activos_pdf();
    $templateCode = (string) ($document['plantilla_codigo'] ?? '');
    $sheetLogo = certificado_cargar_imagen_como_jpeg(
        dirname(__DIR__) . '/assets/img/'
            . ($templateCode === 'nacional_formativo_escuela_d'
                ? 'federacion-patinaje-logo.png'
                : 'world-skate-logo.png'),
        'Im1'
    );
    if ($sheetLogo !== null) {
        $assets['Im1'] = $sheetLogo;
    }
    $commands = evento_federado_hoja_pdf_commands($document, $assets);
    $stream = implode("\n", $commands) . "\n";
    $pdf = certificado_construir_documento_pdf(
        $stream,
        $assets,
        $templateCode === 'nacional_formativo_escuela_d' ? 612 : 595,
        $templateCode === 'nacional_formativo_escuela_d' ? 792 : 842
    );

    $nombre = (string) ($document['competidor_nombre'] ?? 'Deportista');
    $categoria = (string) ($document['categoria_label'] ?? 'Categoria');
    $modalidad = (string) ($document['modalidad_nombre'] ?? 'Modalidad');
    $template = (string) ($document['plantilla_codigo'] ?? '');
    $programa = evento_federado_hoja_program_filename($template);
    $filename = implode('-', [
        certificado_slug($nombre),
        certificado_slug($categoria),
        certificado_slug($modalidad),
        certificado_slug($programa),
    ]) . '.pdf';
    $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '-', $filename) ?: 'hoja-elementos.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
    header('Content-Length: ' . strlen($pdf));

    echo $pdf;
    exit;
}

function evento_federado_hoja_pdf_layout(string $templateCode): array
{
    $templateCode = trim($templateCode);
    $year = date('Y');

    if ($templateCode === 'nacional_formativo_escuela_d') {
        return [
            'title' => 'HOJA DE CONTENIDO TÉCNICO ' . $year,
            'subtitle' => 'NIVELES FORMATIVOS Y ESCUELAS',
            'section_title' => 'ELEMENTOS DEL PROGRAMA',
            'form_labels' => [
                'Competidor(a)',
                'Categoría - Nivel',
                'Club',
                'Coreografía',
            ],
            'table_headers' => ['Tiempo', 'Código', 'Elemento'],
            'footer_note' => 'Especificar tiempo de inicio cuando corresponda.',
            'code_notes' => [
                'SSSq' => 'Especificar tiempo de inicio',
                'FoSq' => 'Especificar tiempo de inicio',
                'ChSt' => 'Especificar tiempo de inicio',
                'ChStS' => 'Especificar tiempo de inicio',
            ],
            'show_legend' => true,
            'table_total_width' => 515.0,
        ];
    }

    if (str_starts_with($templateCode, 'solo_dance')) {
        $program = str_contains($templateCode, 'style') ? 'SOLO STYLE DANCE' : 'SOLO FREE DANCE';

        return [
            'title' => $program . ' CONTENT SHEET ' . $year,
            'subtitle' => 'SOLO DANCE',
            'section_title' => $program,
            'form_labels' => [
                'Name',
                'Category',
                'Fed type',
                'Federation',
                'Music',
            ],
            'table_headers' => ['#', 'Time', 'Code', '(Tech Panel)', 'Notes'],
            'footer_note' => 'All times are relative to the first movement, not the start of the music.',
            'code_notes' => [],
            'show_legend' => false,
            'table_total_width' => 515.0,
        ];
    }

    $program = str_contains($templateCode, 'short') ? 'SHORT PROGRAM' : 'LONG PROGRAM';

    return [
        'title' => 'FREESKATE ' . $program . ' CONTENT SHEET ' . $year,
        'subtitle' => 'FREESKATE',
        'section_title' => 'ELEMENTS — ' . $program,
        'form_labels' => [
            'Name',
            'Category',
            'Fed type',
            'Federation',
            'Music',
        ],
        'table_headers' => ['#', 'Time', 'Code', '(Tech Panel)', 'Notes'],
        'footer_note' => 'All times are relative to the first movement, not the start of the music.',
        'code_notes' => [],
        'show_legend' => false,
        'table_total_width' => 515.0,
    ];
}

function evento_federado_hoja_pdf_commands(array $document, array $assets = []): array
{
    $templateCode = (string) ($document['plantilla_codigo'] ?? 'freeskating_free');
    if ($templateCode === 'nacional_formativo_escuela_d') {
        return evento_federado_hoja_nacional_pdf_commands($document, $assets);
    }

    $layout = evento_federado_hoja_pdf_layout($templateCode);
    $templateLabel = (string) ($document['plantilla_label'] ?? $layout['title']);
    $eventName = trim((string) ($document['evento_nombre'] ?? ''));
    $competidorNombre = trim((string) ($document['competidor_nombre'] ?? ''));
    $categoriaLabel = trim((string) ($document['categoria_label'] ?? ''));
    $club = trim((string) ($document['club'] ?? ''));
    $representing = trim((string) ($document['representing'] ?? ''));
    $choreography = trim((string) ($document['choreography'] ?? ''));
    $observaciones = trim((string) ($document['observaciones'] ?? ''));
    $rows = $document['rows'] ?? [];
    if (!is_array($rows)) {
        $rows = [];
    }
    $rows = array_slice($rows, 0, evento_federado_hoja_template_rows($templateCode));
    $rows = evento_federado_hoja_pad_rows($rows, $templateCode);
    $codeLabels = evento_federado_hoja_codes_for_template($templateCode);
    $codeNotes = $layout['code_notes'] ?? [];
    $isNational = $templateCode === 'nacional_formativo_escuela_d';
    $isSoloDance = str_starts_with($templateCode, 'solo_dance');
    $fedType = 'CLUB';

    $bandFill = $isSoloDance ? '0.15 0.46 0.41' : '0.78 0.81 0.86';
    $panelFill = $isSoloDance ? '0.90 0.96 0.95' : '0.87 0.91 0.96';
    $tableHeaderFill = $isSoloDance ? '0.18 0.55 0.49' : '0.15 0.46 0.82';
    $borderColor = $isSoloDance ? '0.63 0.72 0.70' : '0.74 0.76 0.80';

    $commands = [];
    $commands[] = '1 1 1 rg 0 0 595 842 re f';
    if ($isSoloDance) {
        $commands[] = $bandFill . ' rg 34 760 527 40 re f';
        $commands[] = $panelFill . ' rg 34 744 527 16 re f';
    } else {
        $commands[] = $bandFill . ' rg 34 788 527 12 re f';
        $commands[] = $panelFill . ' rg 34 744 527 42 re f';
    }
    $commands[] = ($isSoloDance ? '0.95 0.98 0.97' : '0.93 0.95 0.98') . ' rg 34 560 527 164 re f';
    $commands[] = '0.96 0.97 0.99 rg 34 58 527 472 re f';
    $commands[] = $borderColor . ' RG 34 ' . ($isSoloDance ? '744 527 56' : '744 527 42') . ' re S';
    $commands[] = $borderColor . ' RG 34 560 527 164 re S';
    $commands[] = $borderColor . ' RG 34 58 527 472 re S';

    if (!$isNational && isset($assets['Im1'])) {
        $commands[] = certificado_pdf_image_cmd('Im1', 44, 760, 100, 37);
    }

    if ($isSoloDance) {
        $commands[] = '1 1 1 rg';
        $commands[] = certificado_pdf_text_cmd('F4', 16, 152, 783, $layout['title']);
        $commands[] = certificado_pdf_text_cmd('F1', 10, 152, 766, (string) $layout['subtitle']);
    } else {
        $commands[] = '0.12 0.12 0.14 rg';
        $commands[] = certificado_pdf_center_text_cmd('F4', $isNational ? 17 : 18, 782, $layout['title']);
        $commands[] = certificado_pdf_center_text_cmd('F1', 10, 766, (string) $layout['subtitle']);
    }
    if (!$isNational && !$isSoloDance && $eventName !== '') {
        $commands[] = certificado_pdf_center_text_cmd('F2', 9, 752, evento_federado_hoja_pdf_trim($eventName, 62));
    }

    if ($isNational) {
        $commands[] = certificado_pdf_text_cmd('F1', 9, 52, 729, 'Tiempo en mins:secs y codigo del elemento.');
    } else {
        $commands[] = certificado_pdf_text_cmd('F1', 9, 52, 729, 'Fill in all details for the skater and event using the form below.');
        $commands[] = certificado_pdf_text_cmd('F1', 9, 52, 714, 'For each element, enter the time in mins:secs and select the code.');
    }

    $formX = 44.0;
    $formY = 722.0;
    $labelWidth = 146.0;
    $valueWidth = 481.0 - $labelWidth;
    $rowHeight = 22.0;
    if ($isNational) {
        $formRows = [
            [$layout['form_labels'][0], $competidorNombre],
            [$layout['form_labels'][1], $categoriaLabel],
            [$layout['form_labels'][2], $club !== '' ? $club : $representing],
            [$layout['form_labels'][3], $choreography !== '' ? $choreography : $representing],
        ];
    } else {
        $formRows = [
            [$layout['form_labels'][0], $competidorNombre],
            [$layout['form_labels'][1], $categoriaLabel],
            [$layout['form_labels'][2], $fedType],
            [$layout['form_labels'][3], $club],
            [$layout['form_labels'][4], $choreography],
        ];
    }

    foreach ($formRows as $index => [$label, $value]) {
        $rowTop = $formY - ($index * $rowHeight);
        $commands[] = ($isSoloDance ? '0.86 0.94 0.92' : '0.88 0.92 0.96') . ' rg ' . number_format($formX, 2, '.', '') . ' ' . number_format($rowTop - $rowHeight, 2, '.', '') . ' ' . number_format($labelWidth, 2, '.', '') . ' ' . number_format($rowHeight, 2, '.', '') . ' re f';
        $commands[] = '1 1 1 rg ' . number_format($formX + $labelWidth, 2, '.', '') . ' ' . number_format($rowTop - $rowHeight, 2, '.', '') . ' ' . number_format($valueWidth, 2, '.', '') . ' ' . number_format($rowHeight, 2, '.', '') . ' re f';
        $commands[] = $borderColor . ' RG ' . number_format($formX, 2, '.', '') . ' ' . number_format($rowTop - $rowHeight, 2, '.', '') . ' ' . number_format($labelWidth + $valueWidth, 2, '.', '') . ' ' . number_format($rowHeight, 2, '.', '') . ' re S';
        $commands[] = '0 0 0 rg';
        $commands[] = certificado_pdf_text_cmd('F1', 8.5, $formX + 8, $rowTop - 14, (string) $label);
        $commands[] = certificado_pdf_text_cmd('F2', 9.5, $formX + $labelWidth + 8, $rowTop - 14, evento_federado_hoja_pdf_trim((string) $value, 58));
    }

    if ($templateCode === 'solo_dance_style') {
        $commands[] = $tableHeaderFill . ' rg 44 522 527 20 re f';
        $commands[] = '1 1 1 rg';
        $commands[] = certificado_pdf_center_text_cmd_area('F4', 10.5, 529, 'STYLE DANCE', 44, 571);
    } else {
        $commands[] = '0 0 0 rg';
        $commands[] = certificado_pdf_center_text_cmd('F4', 13, 538, (string) $layout['section_title']);
    }

    $tableX = 44.0;
    $tableTop = 522.0;
    $columnWidths = $isNational
        ? [68.0, 72.0, 387.0]
        : [24.0, 68.0, 72.0, 235.0, 116.0];
    $tableHeaders = $layout['table_headers'];
    $headerHeight = 20.0;
    $bodyHeight = $templateCode === 'freeskating_free' ? 18.0 : 22.0;
    $bodyTextOffset = $templateCode === 'freeskating_free' ? 7.5 : 7.5;
    $tableHeight = $headerHeight + (count($rows) * $bodyHeight);

    $cursorX = $tableX;
    foreach ($columnWidths as $columnIndex => $width) {
        $commands[] = $tableHeaderFill . ' rg ' . number_format($cursorX, 2, '.', '') . ' ' . number_format($tableTop - $headerHeight, 2, '.', '') . ' ' . number_format($width, 2, '.', '') . ' ' . number_format($headerHeight, 2, '.', '') . ' re f';
        $commands[] = $tableHeaderFill . ' RG ' . number_format($cursorX, 2, '.', '') . ' ' . number_format($tableTop - $headerHeight, 2, '.', '') . ' ' . number_format($width, 2, '.', '') . ' ' . number_format($headerHeight, 2, '.', '') . ' re S';
        $commands[] = '1 1 1 rg';
        $commands[] = certificado_pdf_center_text_cmd_area('F1', 8.2, $tableTop - 13, (string) ($tableHeaders[$columnIndex] ?? ''), $cursorX, $cursorX + $width);
        $cursorX += $width;
    }

    $rowY = $tableTop - $headerHeight;
    $totalWidth = array_sum($columnWidths);
    foreach ($rows as $index => $row) {
        $rowBottom = $rowY - $bodyHeight;
        $fill = $index % 2 === 0 ? '1 1 1 rg' : '0.97 0.98 0.99 rg';
        $commands[] = $fill . ' ' . number_format($tableX, 2, '.', '') . ' ' . number_format($rowBottom, 2, '.', '') . ' ' . number_format($totalWidth, 2, '.', '') . ' ' . number_format($bodyHeight, 2, '.', '') . ' re f';

        $cursorX = $tableX;
        foreach ($columnWidths as $width) {
            $commands[] = '0.79 0.80 0.83 RG ' . number_format($cursorX, 2, '.', '') . ' ' . number_format($rowBottom, 2, '.', '') . ' ' . number_format($width, 2, '.', '') . ' ' . number_format($bodyHeight, 2, '.', '') . ' re S';
            $cursorX += $width;
        }

        $time = evento_federado_hoja_pdf_trim((string) ($row['time'] ?? ''), 10);
        $code = evento_federado_hoja_pdf_trim((string) ($row['code'] ?? ''), 12);
        $notes = evento_federado_hoja_pdf_trim((string) ($row['notes'] ?? ''), 26);
        $element = evento_federado_hoja_pdf_trim((string) ($row['element'] ?? ''), 40);
        $showElement = !in_array('(Tech Panel)', $tableHeaders, true);
        if ($showElement && $element === '' && $code !== '') {
            $element = evento_federado_hoja_pdf_trim((string) ($codeLabels[$code] ?? $code), 40);
        }

        $commands[] = '0 0 0 rg';
        if ($isNational) {
            $commands[] = certificado_pdf_text_cmd('F1', 8.6, 76, $rowBottom + $bodyTextOffset, $time);
            $commands[] = certificado_pdf_text_cmd('F1', 8.6, 146, $rowBottom + $bodyTextOffset, $code);
            $commands[] = certificado_pdf_text_cmd('F1', 8.6, 222, $rowBottom + $bodyTextOffset, $showElement ? $element : '');
        } else {
            $commands[] = certificado_pdf_text_cmd('F1', 8.6, 49, $rowBottom + $bodyTextOffset, (string) ($index + 1));
            $commands[] = certificado_pdf_text_cmd('F1', 8.6, 76, $rowBottom + $bodyTextOffset, $time);
            $commands[] = certificado_pdf_text_cmd('F1', 8.6, 146, $rowBottom + $bodyTextOffset, $code);
            $commands[] = certificado_pdf_text_cmd('F1', 8.6, 222, $rowBottom + $bodyTextOffset, $showElement ? $element : '');
            $commands[] = certificado_pdf_text_cmd('F1', 8.6, 455, $rowBottom + $bodyTextOffset, $notes);
        }

        $rowY = $rowBottom;
    }

    if (!empty($layout['show_legend'])) {
        $legendTitleY = max(166.0, $rowY - 26.0);
        $commands[] = certificado_pdf_center_text_cmd('F4', 11.5, $legendTitleY, $isNational ? 'CÓDIGOS DE ELEMENTOS' : 'ELEMENT CODES');

        $legendEntries = [];
        foreach ($codeLabels as $code => $label) {
            $labelText = (string) $label;
            if (isset($codeNotes[$code])) {
                $labelText .= ' (' . $codeNotes[$code] . ')';
            }
            $legendEntries[] = [
                'code' => (string) $code,
                'label' => $labelText,
            ];
        }

        $half = (int) ceil(count($legendEntries) / 2);
        $leftEntries = array_slice($legendEntries, 0, $half);
        $rightEntries = array_slice($legendEntries, $half);

        $legendLeftX = 52.0;
        $legendRightX = 306.0;
        $legendCodeOffset = 132.0;
        $legendY = $legendTitleY - 20.0;
        foreach ($leftEntries as $entry) {
            $commands[] = certificado_pdf_text_cmd('F1', 8.8, $legendLeftX, $legendY, $entry['label']);
            $commands[] = certificado_pdf_text_cmd('F2', 8.8, $legendLeftX + $legendCodeOffset, $legendY, $entry['code']);
            $legendY -= 15.0;
        }

        $legendY = $legendTitleY - 20.0;
        foreach ($rightEntries as $entry) {
            $commands[] = certificado_pdf_text_cmd('F1', 8.8, $legendRightX, $legendY, $entry['label']);
            $commands[] = certificado_pdf_text_cmd('F2', 8.8, $legendRightX + $legendCodeOffset, $legendY, $entry['code']);
            $legendY -= 15.0;
        }
    }

    if ($observaciones !== '') {
        $commands[] = '0 0 0 rg';
        $commands[] = certificado_pdf_text_cmd('F1', 8.4, 52, 82, ($isNational ? 'Observaciones: ' : 'Notes: ') . evento_federado_hoja_pdf_trim($observaciones, 92));
    }

    $commands[] = '0 0 0 rg';
    $commands[] = certificado_pdf_text_cmd('F1', 8.4, 52, 66, (string) $layout['footer_note']);
    if (!$isNational) {
        $commands[] = certificado_pdf_text_cmd('F1', 8, 52, 46, 'Generated from the federated events module of Maiteam.');
    }

    return $commands;
}

function evento_federado_hoja_nacional_pdf_commands(array $document, array $assets = []): array
{
    $competidor = evento_federado_hoja_pdf_trim((string) ($document['competidor_nombre'] ?? ''), 58);
    $categoria = evento_federado_hoja_pdf_trim((string) ($document['categoria_label'] ?? ''), 58);
    $club = evento_federado_hoja_pdf_trim((string) ($document['club'] ?? 'Maiteam'), 58);
    $coreografia = evento_federado_hoja_pdf_trim((string) ($document['choreography'] ?? ''), 58);
    $rows = is_array($document['rows'] ?? null) ? array_slice($document['rows'], 0, 10) : [];
    $rows = evento_federado_hoja_pad_rows($rows, 'nacional_formativo_escuela_d');
    $codeLabels = evento_federado_hoja_codes_for_template('nacional_formativo_escuela_d');
    $codeLabels['SSSq'] = 'Deslizamiento en S';
    $codeNotes = [
        'SSSq' => 'Especificar tiempo de inicio',
        'FoSq' => 'Especificar tiempo de inicio',
        'ChStS' => 'Especificar tiempo de inicio',
    ];
    $commands = [
        '1 1 1 rg 0 0 612 792 re f',
    ];

    if (isset($assets['Im1'])) {
        $commands[] = certificado_pdf_image_cmd('Im1', 68, 624, 112, 90);
    }

    $commands[] = '0 0 0 rg';
    $commands[] = certificado_pdf_center_text_cmd('F4', 13.5, 708, 'HOJA DE CONTENIDO');
    $commands[] = certificado_pdf_center_text_cmd('F4', 13.5, 682, 'TECNICO ' . date('Y') . ' NIVELES');
    $commands[] = certificado_pdf_center_text_cmd('F1', 13.5, 656, 'FORMATIVOS Y ESCUELAS');

    $tableX = 85.0;
    $tableWidth = 442.0;
    $labelWidth = 142.0;
    $valueWidth = $tableWidth - $labelWidth;
    $rowHeight = 18.0;
    $top = 626.0;
    $formRows = [
        ['Nombre del competidor(a)', $competidor],
        ['Categoría - Nivel', $categoria],
        ['Club', $club],
    ];
    foreach ($formRows as $index => [$label, $value]) {
        $bottom = $top - (($index + 1) * $rowHeight);
        $commands[] = '0.82 0.88 0.93 rg ' . $tableX . ' ' . $bottom . ' ' . $labelWidth . ' ' . $rowHeight . ' re f';
        $commands[] = '1 1 1 rg ' . ($tableX + $labelWidth) . ' ' . $bottom . ' ' . $valueWidth . ' ' . $rowHeight . ' re f';
        $commands[] = '0 0 0 RG ' . $tableX . ' ' . $bottom . ' ' . $tableWidth . ' ' . $rowHeight . ' re S';
        $commands[] = '0 0 0 RG ' . ($tableX + $labelWidth) . ' ' . $bottom . ' m ' . ($tableX + $labelWidth) . ' ' . ($bottom + $rowHeight) . ' l S';
        $commands[] = '0 0 0 rg';
        $commands[] = certificado_pdf_text_cmd('F1', 10, $tableX + 6, $bottom + 6, $label);
        $commands[] = certificado_pdf_text_cmd('F1', 10, $tableX + $labelWidth + 6, $bottom + 6, $value);
    }

    $choreoTop = 534.0;
    $choreoBottom = $choreoTop - $rowHeight;
    $commands[] = '0.82 0.88 0.93 rg ' . $tableX . ' ' . $choreoBottom . ' ' . $labelWidth . ' ' . $rowHeight . ' re f';
    $commands[] = '1 1 1 rg ' . ($tableX + $labelWidth) . ' ' . $choreoBottom . ' ' . $valueWidth . ' ' . $rowHeight . ' re f';
    $commands[] = '0 0 0 RG ' . $tableX . ' ' . $choreoBottom . ' ' . $tableWidth . ' ' . $rowHeight . ' re S';
    $commands[] = '0 0 0 RG ' . ($tableX + $labelWidth) . ' ' . $choreoBottom . ' m ' . ($tableX + $labelWidth) . ' ' . $choreoTop . ' l S';
    $commands[] = '0 0 0 rg';
    $commands[] = certificado_pdf_text_cmd('F1', 10, $tableX + 6, $choreoBottom + 6, 'Coreografía');
    $commands[] = certificado_pdf_text_cmd('F1', 10, $tableX + $labelWidth + 6, $choreoBottom + 6, $coreografia);

    $commands[] = certificado_pdf_center_text_cmd('F4', 13, 496, 'ELEMENTOS DEL PROGRAMA');
    $elementTop = 478.0;
    $headerHeight = 16.0;
    $bodyHeight = 18.0;
    $columnWidths = [57.0, 57.0, 328.0];
    $headers = ['Tiempo', 'Código', 'Elemento'];
    $x = $tableX;
    foreach ($headers as $index => $header) {
        $width = $columnWidths[$index];
        $commands[] = '0.82 0.88 0.93 rg ' . $x . ' ' . ($elementTop - $headerHeight) . ' ' . $width . ' ' . $headerHeight . ' re f';
        $commands[] = '0 0 0 RG ' . $x . ' ' . ($elementTop - $headerHeight) . ' ' . $width . ' ' . $headerHeight . ' re S';
        $commands[] = '0 0 0 rg';
        $commands[] = certificado_pdf_text_cmd('F4', 9.5, $x + ($width / 2) - 16, $elementTop - 11, $header);
        $x += $width;
    }

    $rowTop = $elementTop - $headerHeight;
    foreach ($rows as $index => $row) {
        $bottom = $rowTop - $bodyHeight;
        $x = $tableX;
        foreach ($columnWidths as $width) {
            $commands[] = '1 1 1 rg ' . $x . ' ' . $bottom . ' ' . $width . ' ' . $bodyHeight . ' re f';
        $commands[] = '0 0 0 RG ' . $x . ' ' . $bottom . ' ' . $width . ' ' . $bodyHeight . ' re S';
            $x += $width;
        }
        $code = evento_federado_hoja_pdf_trim((string) ($row['code'] ?? ''), 12);
        $element = evento_federado_hoja_pdf_trim((string) ($row['element'] ?? ''), 55);
        if ($element === '' && $code !== '') {
            $element = evento_federado_hoja_pdf_trim((string) ($codeLabels[$code] ?? $code), 55);
        }
        $commands[] = '0 0 0 rg';
        $commands[] = certificado_pdf_text_cmd('F1', 9, 92, $bottom + 6, evento_federado_hoja_pdf_trim((string) ($row['time'] ?? ''), 10));
        $commands[] = certificado_pdf_text_cmd('F1', 9, 149, $bottom + 6, $code);
        $commands[] = certificado_pdf_text_cmd('F1', 9, 206, $bottom + 6, $element);
        $rowTop = $bottom;
    }

    $legendTitleY = 254.0;
    $commands[] = certificado_pdf_text_cmd('F1', 9, $tableX, $legendTitleY, 'CÓDIGOS DE ELEMENTOS');
    $legendEntries = [];
    foreach ($codeLabels as $code => $label) {
        $legendEntries[] = [(string) $label, (string) $code, (string) ($codeNotes[$code] ?? '')];
    }
    $y = $legendTitleY - 20;
    foreach ($legendEntries as [$label, $code, $note]) {
        $commands[] = certificado_pdf_text_cmd('F1', 8.5, $tableX, $y, $label);
        $commands[] = certificado_pdf_text_cmd('F2', 8.5, $tableX + 130, $y, $code);
        if ($note !== '') {
            $commands[] = certificado_pdf_text_cmd('F1', 8.5, $tableX + 178, $y, '(' . $note . ')');
        }
        $y -= 20;
    }

    return $commands;
}

function evento_federado_hoja_pdf_trim(string $value, int $maxChars): string
{
    $text = trim($value);
    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        return mb_substr($text, 0, max(0, $maxChars - 1)) . '…';
    }

    if (strlen($text) <= $maxChars) {
        return $text;
    }

    return substr($text, 0, max(0, $maxChars - 1)) . '...';
}
