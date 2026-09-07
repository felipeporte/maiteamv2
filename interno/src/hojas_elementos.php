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
            'label' => 'Freeskating - Free Program',
            'short_label' => 'Free Program',
            'rows' => 9,
            'program_label' => 'Free - Choreography / Music',
            'program_short' => 'Free',
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
            'rows' => 7,
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
            'rows' => 7,
            'program_label' => 'Free - Choreography / Music',
            'program_short' => 'Free',
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
        'nacional_formativo_escuela_d' => [
            'label' => 'Nacional - Formativo / Escuela D',
            'short_label' => 'Nacional',
            'rows' => 8,
            'program_label' => 'Coreografía',
            'program_short' => 'Coreografía',
            'codes' => [
                'SFig' => 'Pirueta Individual',
                'CoFig' => 'Combinación de piruetas',
                'SSq' => 'Deslizamiento en S',
                'FoSq' => 'Footwork Sequence',
                'ChStS' => 'Choreo Stop / Step / Pose',
                'SJu' => 'Solo Jump',
                'CoJ' => 'Combo Jump',
                'SSp' => 'Solo Spin',
                'CSp' => 'Combo Spin',
            ],
        ],
    ];
}

function evento_federado_hoja_default_template(array $context): string
{
    $modalidadCodigo = strtolower(trim((string) ($context['modalidad_codigo'] ?? '')));
    $nivel = strtolower(trim((string) ($context['nivel_competencia'] ?? $context['evento_nivel'] ?? '')));
    $subnivel = strtoupper(trim((string) ($context['subnivel_competencia'] ?? $context['subnivel'] ?? '')));

    if ($modalidadCodigo === 'solo_dance') {
        return 'solo_dance_free';
    }

    if ($modalidadCodigo === 'freeskating') {
        return 'freeskating_free';
    }

    if (($nivel === 'formativo' || $nivel === 'escuela') && $subnivel === 'D') {
        return 'nacional_formativo_escuela_d';
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
    $parts = [];
    foreach ([
        (string) ($context['nivel_competencia'] ?? $context['evento_nivel'] ?? ''),
        (string) ($context['subnivel_competencia'] ?? $context['subnivel'] ?? ''),
        (string) ($context['categoria_competencia'] ?? $context['categoria'] ?? ''),
    ] as $part) {
        $part = trim($part);
        if ($part !== '') {
            $parts[] = $part;
        }
    }

    if (empty($parts)) {
        $parts[] = 'Sin categoria';
    }

    return implode(' - ', $parts);
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

function evento_federado_hoja_filter_rows(array $rows): array
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

    return array_slice($filtered, 0, 9);
}

function evento_federado_hoja_context_find(int $eventoId, int $inscripcionId): ?array
{
    if (!evento_federado_hojas_schema_ready()) {
        return null;
    }

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
        . 'LIMIT 1'
    );
    $stmt->execute([
        'evento_id' => $eventoId,
        'inscripcion_id' => $inscripcionId,
    ]);

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
    if ($templateCode === '' || !array_key_exists($templateCode, evento_federado_hoja_template_catalog())) {
        $templateCode = evento_federado_hoja_default_template($context);
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

    $categoriaLabel = evento_federado_hoja_clean_text((string) ($context['categoria_label'] ?? ''));
    if ($categoriaLabel === '') {
        $categoriaLabel = evento_federado_hoja_default_categoria_label($context);
    }

    $club = evento_federado_hoja_clean_text((string) ($context['club'] ?? ''));
    if ($club === '') {
        $club = 'Club MaiTeam';
    }

    $representing = evento_federado_hoja_clean_text((string) ($context['representing'] ?? ''));
    if ($representing === '') {
        $representing = 'Club MaiTeam';
    }

    return [
        'evento_id' => (int) ($context['evento_id'] ?? 0),
        'inscripcion_id' => (int) ($context['inscripcion_id'] ?? 0),
        'hoja_id' => (int) ($context['hoja_id'] ?? 0),
        'plantilla_codigo' => $templateCode,
        'plantilla_label' => evento_federado_hoja_template_label($templateCode),
        'programa_label' => evento_federado_hoja_program_label($templateCode),
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

function evento_federado_hoja_save(int $eventoId, int $inscripcionId, array $data): int
{
    if (!evento_federado_hojas_schema_ready()) {
        throw new RuntimeException('La base de datos no tiene la estructura para hojas de elementos.');
    }

    $context = evento_federado_hoja_context_find($eventoId, $inscripcionId);
    if ($context === null) {
        throw new RuntimeException('No se encontro la inscripcion seleccionada.');
    }

    $templateCode = evento_federado_hoja_clean_text((string) ($data['plantilla_codigo'] ?? ''));
    if ($templateCode === '' || !array_key_exists($templateCode, evento_federado_hoja_template_catalog())) {
        $templateCode = evento_federado_hoja_default_template($context);
    }

    $rows = evento_federado_hoja_filter_rows((array) ($data['rows'] ?? []));
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

    $categoriaLabel = evento_federado_hoja_clean_text((string) ($data['categoria_label'] ?? ''));
    if ($categoriaLabel === '') {
        $categoriaLabel = evento_federado_hoja_default_categoria_label($context);
    }

    $club = evento_federado_hoja_clean_text((string) ($data['club'] ?? ''));
    if ($club === '') {
        $club = 'Club MaiTeam';
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
    $commands = evento_federado_hoja_pdf_commands($document, $assets);
    $stream = implode("\n", $commands) . "\n";
    $pdf = certificado_construir_documento_pdf($stream, $assets);

    $nombre = (string) ($document['competidor_nombre'] ?? 'hoja-de-elementos');
    $template = (string) ($document['plantilla_codigo'] ?? 'documento');
    $filename = 'hoja-elementos-' . certificado_slug($nombre) . '-' . certificado_slug($template) . '.pdf';
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
            'table_headers' => ['#', 'Tiempo', 'Código', 'Elemento', 'Notas'],
            'footer_note' => 'Especificar tiempo de inicio cuando corresponda.',
            'code_notes' => [
                'SSq' => 'Especificar tiempo de inicio',
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

    $program = str_contains($templateCode, 'short') ? 'SHORT PROGRAM' : 'FREE PROGRAM';

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
    $rows = array_slice($rows, 0, 9);
    $rows = evento_federado_hoja_pad_rows($rows, $templateCode);
    $codeLabels = evento_federado_hoja_codes_for_template($templateCode);
    $codeNotes = $layout['code_notes'] ?? [];
    $isNational = $templateCode === 'nacional_formativo_escuela_d';

    $commands = [];
    $commands[] = '1 1 1 rg 0 0 595 842 re f';
    $commands[] = '0.78 0.81 0.86 rg 34 788 527 12 re f';
    $commands[] = '0.87 0.91 0.96 rg 34 744 527 42 re f';
    $commands[] = '0.93 0.95 0.98 rg 34 560 527 164 re f';
    $commands[] = '0.96 0.97 0.99 rg 34 58 527 472 re f';
    $commands[] = '0.74 0.76 0.80 RG 34 744 527 42 re S';
    $commands[] = '0.74 0.76 0.80 RG 34 560 527 164 re S';
    $commands[] = '0.74 0.76 0.80 RG 34 58 527 472 re S';

    if (isset($assets['Im1'])) {
        $commands[] = certificado_pdf_image_cmd('Im1', 44, 750, 58, 58);
    }

    $commands[] = '0.12 0.12 0.14 rg';
    $commands[] = certificado_pdf_center_text_cmd('F4', $isNational ? 17 : 18, 782, $layout['title']);
    $commands[] = certificado_pdf_center_text_cmd('F1', 10, 766, (string) $layout['subtitle']);
    if ($eventName !== '') {
        $commands[] = certificado_pdf_center_text_cmd('F2', 9, 752, evento_federado_hoja_pdf_trim($eventName, 62));
    }

    $commands[] = certificado_pdf_text_cmd('F1', 9, 52, 729, $isNational ? 'Fill in all details for the skater and event using the form below.' : 'Fill in all details for the skater and event using the form below.');
    $commands[] = certificado_pdf_text_cmd('F1', 9, 52, 714, $isNational ? 'Tiempo en mins:secs y codigo del elemento.' : 'For each element, enter the time in mins:secs and select the code.');

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
            [$layout['form_labels'][2], $representing !== '' ? $representing : $club],
            [$layout['form_labels'][3], $club],
            [$layout['form_labels'][4], $choreography],
        ];
    }

    foreach ($formRows as $index => [$label, $value]) {
        $rowTop = $formY - ($index * $rowHeight);
        $commands[] = '0.88 0.92 0.96 rg ' . number_format($formX, 2, '.', '') . ' ' . number_format($rowTop - $rowHeight, 2, '.', '') . ' ' . number_format($labelWidth, 2, '.', '') . ' ' . number_format($rowHeight, 2, '.', '') . ' re f';
        $commands[] = '1 1 1 rg ' . number_format($formX + $labelWidth, 2, '.', '') . ' ' . number_format($rowTop - $rowHeight, 2, '.', '') . ' ' . number_format($valueWidth, 2, '.', '') . ' ' . number_format($rowHeight, 2, '.', '') . ' re f';
        $commands[] = '0.67 0.69 0.73 RG ' . number_format($formX, 2, '.', '') . ' ' . number_format($rowTop - $rowHeight, 2, '.', '') . ' ' . number_format($labelWidth + $valueWidth, 2, '.', '') . ' ' . number_format($rowHeight, 2, '.', '') . ' re S';
        $commands[] = certificado_pdf_text_cmd('F1', 8.5, $formX + 8, $rowTop - 14, (string) $label);
        $commands[] = certificado_pdf_text_cmd('F2', 9.5, $formX + $labelWidth + 8, $rowTop - 14, evento_federado_hoja_pdf_trim((string) $value, 58));
    }

    $commands[] = certificado_pdf_center_text_cmd('F4', 13, 538, (string) $layout['section_title']);

    $tableX = 44.0;
    $tableTop = 522.0;
    $columnWidths = $isNational
        ? [24.0, 68.0, 72.0, 235.0, 116.0]
        : [24.0, 68.0, 72.0, 235.0, 116.0];
    $tableHeaders = $layout['table_headers'];
    $headerHeight = 20.0;
    $tableHeight = $headerHeight + (count($rows) * 22.0);

    $cursorX = $tableX;
    foreach ($columnWidths as $columnIndex => $width) {
        $commands[] = '0.15 0.46 0.82 rg ' . number_format($cursorX, 2, '.', '') . ' ' . number_format($tableTop - $headerHeight, 2, '.', '') . ' ' . number_format($width, 2, '.', '') . ' ' . number_format($headerHeight, 2, '.', '') . ' re f';
        $commands[] = '0.15 0.46 0.82 RG ' . number_format($cursorX, 2, '.', '') . ' ' . number_format($tableTop - $headerHeight, 2, '.', '') . ' ' . number_format($width, 2, '.', '') . ' ' . number_format($headerHeight, 2, '.', '') . ' re S';
        $commands[] = '1 1 1 rg';
        $commands[] = certificado_pdf_center_text_cmd('F1', 8.2, $tableTop - 13, (string) ($tableHeaders[$columnIndex] ?? ''));
        $cursorX += $width;
    }

    $rowY = $tableTop - $headerHeight;
    $totalWidth = array_sum($columnWidths);
    foreach ($rows as $index => $row) {
        $rowBottom = $rowY - 22.0;
        $fill = $index % 2 === 0 ? '1 1 1 rg' : '0.97 0.98 0.99 rg';
        $commands[] = $fill . ' ' . number_format($tableX, 2, '.', '') . ' ' . number_format($rowBottom, 2, '.', '') . ' ' . number_format($totalWidth, 2, '.', '') . ' ' . number_format(22.0, 2, '.', '') . ' re f';

        $cursorX = $tableX;
        foreach ($columnWidths as $width) {
            $commands[] = '0.79 0.80 0.83 RG ' . number_format($cursorX, 2, '.', '') . ' ' . number_format($rowBottom, 2, '.', '') . ' ' . number_format($width, 2, '.', '') . ' ' . number_format(22.0, 2, '.', '') . ' re S';
            $cursorX += $width;
        }

        $rowNumber = (string) ($index + 1);
        $time = evento_federado_hoja_pdf_trim((string) ($row['time'] ?? ''), 10);
        $code = evento_federado_hoja_pdf_trim((string) ($row['code'] ?? ''), 12);
        $notes = evento_federado_hoja_pdf_trim((string) ($row['notes'] ?? ''), 26);
        $element = evento_federado_hoja_pdf_trim((string) ($row['element'] ?? ''), 40);
        $showElement = !in_array('(Tech Panel)', $tableHeaders, true);
        if ($showElement && $element === '' && $code !== '') {
            $element = evento_federado_hoja_pdf_trim((string) ($codeLabels[$code] ?? $code), 40);
        }

        $commands[] = certificado_pdf_text_cmd('F1', 8.6, 49, $rowBottom + 7.5, $rowNumber);
        $commands[] = certificado_pdf_text_cmd('F1', 8.6, 76, $rowBottom + 7.5, $time);
        $commands[] = certificado_pdf_text_cmd('F1', 8.6, 146, $rowBottom + 7.5, $code);
        $commands[] = certificado_pdf_text_cmd('F1', 8.6, 222, $rowBottom + 7.5, $showElement ? $element : '');
        $commands[] = certificado_pdf_text_cmd('F1', 8.6, 455, $rowBottom + 7.5, $notes);

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
        $commands[] = certificado_pdf_text_cmd('F1', 8.4, 52, 82, ($isNational ? 'Observaciones: ' : 'Notes: ') . evento_federado_hoja_pdf_trim($observaciones, 92));
    }

    $commands[] = certificado_pdf_text_cmd('F1', 8.4, 52, 66, (string) $layout['footer_note']);
    $commands[] = certificado_pdf_text_cmd('F1', 8, 52, 46, $isNational ? 'Generado desde el modulo de eventos federados del Club MaiTeam.' : 'Generated from the federated events module of Club MaiTeam.');

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
