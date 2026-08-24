<?php

declare(strict_types=1);

function deportista_por_rut(string $rut): ?array
{
    $normalizedRut = normalize_rut($rut);
    if ($normalizedRut === '') {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT d.id, d.nombre, d.rut, d.categoria, d.activo, '
        . 'a.nombre AS apoderado_nombre, n.nombre AS nivel_nombre '
        . 'FROM deportistas d '
        . 'INNER JOIN apoderados a ON a.id = d.apoderado_id '
        . 'LEFT JOIN niveles_deportivos n ON n.id = d.nivel_id '
        . 'WHERE REPLACE(REPLACE(UPPER(d.rut), ".", ""), "-", "") = :rut '
        . 'LIMIT 1'
    );
    $stmt->execute(['rut' => $normalizedRut]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function certificado_emitir_permanencia_pdf(array $deportista): void
{
    $nombre = (string) ($deportista['nombre'] ?? '');
    $rut = certificado_formatear_rut_documento((string) ($deportista['rut'] ?? ''));
    $nivel = (string) ($deportista['nivel_nombre'] ?? 'Sin nivel asignado');

    $document = [
        'title' => 'CERTIFICADO',
        'date' => certificado_fecha_carta(date('Y-m-d')),
        'recipient' => 'A quien corresponda,',
        'paragraphs' => [
            sprintf(
                'Junto con saludar, certificamos que la deportista <strong>%s</strong>, RUT <strong>%s</strong>, pertenece actualmente al Club MaiTeam y participa de forma activa en entrenamientos y actividades oficiales de la Federacion Nacional de patinaje.',
                $nombre,
                $rut !== '' ? $rut : 'sin registro',
                $nivel
            ),
            'El presente documento se emite para ser presentado en su establecimiento educacional y acreditar permanencia deportiva vigente.',
            'Se extiende este certificado para los fines que se estimen pertinentes.',
        ],
        'farewell' => 'Saludos afectuosos,',
    ];

    $filename = 'certificado-permanencia-' . certificado_slug((string) $nombre) . '.pdf';
    certificado_emitir_pdf($filename, $document);
}

function certificado_emitir_competencia_pdf(array $deportista, array $competencia): void
{
    $nombre = (string) ($deportista['nombre'] ?? '');
    $rut = certificado_formatear_rut_documento((string) ($deportista['rut'] ?? ''));
    $nivel = (string) ($deportista['nivel_nombre'] ?? 'Sin nivel asignado');
    $nombreCompetencia = (string) ($competencia['nombre'] ?? 'Competencia');
    $fechaCompetencia = certificado_texto_fechas_competencia($competencia);
    $lugar = trim((string) ($competencia['lugar'] ?? ''));
    $lugarTexto = $lugar !== '' ? 'en ' . $lugar : 'en sede por confirmar';

    $document = [
        'title' => 'JUSTIFICATIVO',
        'date' => certificado_fecha_carta(date('Y-m-d')),
        'recipient' => 'A quien corresponda,',
        'paragraphs' => [
            sprintf(
                'Junto con saludar, informamos que la deportista <strong>%s</strong>, RUT <strong>%s</strong>, integrante del nivel %s de nuestro club, participara en la competencia %s %s %s.',
                $nombre,
                $rut !== '' ? $rut : 'sin registro',
                $nivel,
                $nombreCompetencia,
                $fechaCompetencia,
                $lugarTexto
            ),
            'Esta participacion forma parte del calendario oficial deportivo del Club MaiTeam y organizada por la Federacion nacional de patinaje.',
            'Por lo anterior, solicitamos justificar su inasistencia academica durante las fechas indicadas, agradeciendo su comprension y apoyo al desarrollo integral de nuestra deportista.',
        ],
        'farewell' => 'Saludos afectuosos,',
    ];

    $filename = 'certificado-competencia-' . certificado_slug((string) $nombre) . '.pdf';
    certificado_emitir_pdf($filename, $document);
}

function certificado_texto_fechas_competencia(array $competencia): string
{
    $fechaInicio = trim((string) ($competencia['fecha_inicio'] ?? ''));
    $fechaFin = trim((string) ($competencia['fecha_fin'] ?? ''));

    if ($fechaInicio === '') {
        return '';
    }

    $inicio = certificado_fecha_larga($fechaInicio);

    if ($fechaFin !== '' && $fechaFin !== $fechaInicio) {
        $fin = certificado_fecha_larga($fechaFin);
        return sprintf('desde el %s hasta el %s', $inicio, $fin);
    }

    return sprintf('el %s', $inicio);
}

function certificado_fecha_larga(string $fecha): string
{
    $date = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$date instanceof DateTime) {
        return $fecha;
    }

    $months = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre',
    ];

    $month = $months[(int) $date->format('n')] ?? $date->format('m');

    return sprintf('%d de %s de %d', (int) $date->format('j'), $month, (int) $date->format('Y'));
}

function certificado_fecha_carta(string $fecha): string
{
    $date = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$date instanceof DateTime) {
        return $fecha;
    }

    $months = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    $month = $months[(int) $date->format('n')] ?? $date->format('m');

    return sprintf('%d de %s, %d', (int) $date->format('j'), $month, (int) $date->format('Y'));
}

function certificado_formatear_rut_documento(string $rut): string
{
    $normalized = normalize_rut($rut);
    if ($normalized === '' || strlen($normalized) < 2) {
        return format_rut($rut);
    }

    $body = substr($normalized, 0, -1);
    $verifier = substr($normalized, -1);

    if ($body === '' || !ctype_digit($body)) {
        return format_rut($rut);
    }

    $reversed = strrev($body);
    $chunks = str_split($reversed, 3);
    $withDots = implode('.', array_reverse(array_map('strrev', $chunks)));

    return $withDots . '-' . $verifier;
}

function certificado_emitir_pdf(string $filename, array $document): void
{
    $assets = certificado_cargar_activos_pdf();
    $commands = certificado_pdf_comandos_documento($document, $assets);
    $stream = implode("\n", $commands) . "\n";
    $pdf = certificado_construir_documento_pdf($stream, $assets);

    $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '-', $filename) ?: 'certificado.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
    header('Content-Length: ' . strlen($pdf));

    echo $pdf;
    exit;
}

function certificado_pdf_comandos_documento(array $document, array $assets = []): array
{
    $branding = certificado_datos_institucion();
    $title = trim((string) ($document['title'] ?? 'CERTIFICADO'));
    $recipient = trim((string) ($document['recipient'] ?? 'A quien corresponda,'));
    $farewell = trim((string) ($document['farewell'] ?? 'Saludos afectuosos,'));
    $dateText = trim((string) ($document['date'] ?? certificado_fecha_carta(date('Y-m-d'))));
    $paragraphs = $document['paragraphs'] ?? [];

    if (!is_array($paragraphs)) {
        $paragraphs = [];
    }

    $commands = [];

    // Fondo y barras superior/inferior.
    $commands[] = '0.95 0.95 0.95 rg 0 0 595 842 re f';
    $commands[] = '0.62 0.62 0.62 rg 0 816 595 26 re f';
    $commands[] = '0.89 0.54 0.58 rg 84 816 165 26 re f';
    $commands[] = '0.62 0.62 0.62 rg 0 0 595 26 re f';
    $commands[] = '0.89 0.54 0.58 rg 58 0 125 26 re f';

    // Encabezado institucional.
    $commands[] = '0.12 0.12 0.12 rg';
    if (isset($assets['Im1'])) {
        $commands[] = certificado_pdf_image_cmd('Im1', 60, 748, 62, 62);
        $commands[] = certificado_pdf_text_cmd('F1', 9, 54, 738, $branding['discipline']);
    } else {
        $commands[] = certificado_pdf_text_cmd('F4', 16, 54, 780, $branding['club_short']);
        $commands[] = certificado_pdf_text_cmd('F1', 9, 54, 764, $branding['discipline']);
    }

    $contactY = 782;
    foreach ($branding['contacts'] as $contact) {
        $iconType = (string) ($contact['icon'] ?? '');
        $lineText = (string) ($contact['text'] ?? '');
        foreach (certificado_pdf_contact_icon_commands($iconType, 358, $contactY + 1) as $iconCmd) {
            $commands[] = $iconCmd;
        }
        $commands[] = '0.12 0.12 0.12 rg';
        $commands[] = certificado_pdf_text_cmd('F1', 10, 374, $contactY, $lineText);
        $contactY -= 16;
    }

    $commands[] = '0.22 0.22 0.22 RG 1 w 54 724 m 541 724 l S';

    // Fecha y titulo.
    $commands[] = '0.12 0.12 0.12 rg';
    $commands[] = certificado_pdf_text_cmd('F2', 13, 430, 690, $dateText);
    $titleSize = strlen($title) >= 20 ? 36 : 44;
    $commands[] = certificado_pdf_center_text_cmd('F3', $titleSize, 620, $title);

    // Cuerpo.
    $leftMargin = 54.0;
    $rightMargin = 541.0;
    $bodyWidth = $rightMargin - $leftMargin;
    $y = 565;
    $commands[] = '0.12 0.12 0.12 rg';
    $commands[] = certificado_pdf_text_cmd('F2', 14, $leftMargin, $y, $recipient);
    $y -= 40;

    foreach ($paragraphs as $paragraph) {
        $segments = certificado_parse_rich_text((string) $paragraph);
        $lines = certificado_wrap_rich_text_by_width($segments, 13, $bodyWidth);
        $lastLineIndex = count($lines) - 1;
        foreach ($lines as $index => $line) {
            if ($y < 220) {
                break 2;
            }

            $hasSpaces = (int) ($line['spaces'] ?? 0) > 0;
            $isLastLine = $index === $lastLineIndex;
            if (!$isLastLine && $hasSpaces) {
                foreach (certificado_pdf_rich_line_commands($line, 13, $leftMargin, $y, $bodyWidth, true) as $cmd) {
                    $commands[] = $cmd;
                }
            } else {
                foreach (certificado_pdf_rich_line_commands($line, 13, $leftMargin, $y, $bodyWidth, false) as $cmd) {
                    $commands[] = $cmd;
                }
            }
            $y -= 22;
        }

        $y -= 10;
    }

    if ($y > 200) {
        $commands[] = certificado_pdf_text_cmd('F2', 13, $leftMargin, $y, 'Nos despedimos.');
        $y -= 32;
        $commands[] = certificado_pdf_text_cmd('F2', 13, $leftMargin, $y, $farewell);
    }

    // Bloque de firmas.
    $commands[] = '0.12 0.12 0.12 RG 1 w 70 120 m 220 120 l S';
    $commands[] = '0.12 0.12 0.12 RG 1 w 375 120 m 525 120 l S';
    $commands[] = '0.12 0.12 0.12 rg';

    if (isset($assets['Im2'])) {
        $commands[] = certificado_pdf_image_cmd('Im2', 80, 124, 130, 52);
    } else {
        $commands[] = certificado_pdf_center_text_cmd_area('F5', 13, 145, $branding['head_coach'], 70, 220);
    }

    if (isset($assets['Im3'])) {
        $commands[] = certificado_pdf_image_cmd('Im3', 231, 120, 132, 70);
    } else {
        $commands[] = certificado_pdf_center_text_cmd_area('F3', 16, 143, $branding['club_short'], 245, 350);
    }

    if (isset($assets['Im4'])) {
        $commands[] = certificado_pdf_image_cmd('Im4', 385, 124, 130, 52);
    } else {
        $commands[] = certificado_pdf_center_text_cmd_area('F5', 13, 145, $branding['assistant_coach'], 375, 525);
    }

    $commands[] = certificado_pdf_center_text_cmd_area('F1', 8, 106, $branding['head_role'], 70, 220);
    $commands[] = certificado_pdf_center_text_cmd_area('F1', 8, 106, $branding['assistant_role'], 375, 525);

    return $commands;
}

function certificado_construir_documento_pdf(string $stream, array $assets = []): string
{
    $images = [];
    foreach ($assets as $asset) {
        if (!is_array($asset)) {
            continue;
        }
        if (
            !isset($asset['alias'], $asset['pixel_width'], $asset['pixel_height'], $asset['data'])
            || !is_string($asset['data'])
            || $asset['data'] === ''
        ) {
            continue;
        }
        $images[] = $asset;
    }

    $resources = '/Font << /F1 4 0 R /F2 5 0 R /F3 6 0 R /F4 7 0 R /F5 8 0 R >>';

    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '<< /Type /Pages /Count 1 /Kids [3 0 R] >>',
        4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Roman >>',
        6 => '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold >>',
        7 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        8 => '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Italic >>',
    ];

    $nextObjectId = 9;
    $xObjectRefs = [];

    foreach ($images as $image) {
        $objects[$nextObjectId] = '<< /Type /XObject /Subtype /Image '
            . '/Width ' . (int) $image['pixel_width'] . ' '
            . '/Height ' . (int) $image['pixel_height'] . ' '
            . '/ColorSpace /DeviceRGB /BitsPerComponent 8 '
            . '/Filter /DCTDecode /Length ' . strlen($image['data']) . " >>\nstream\n"
            . $image['data'] . "\nendstream";
        $xObjectRefs[] = '/' . $image['alias'] . ' ' . $nextObjectId . ' 0 R';
        $nextObjectId++;
    }

    if (!empty($xObjectRefs)) {
        $resources .= ' /XObject << ' . implode(' ', $xObjectRefs) . ' >>';
    }

    $contentsObjectId = $nextObjectId;
    $objects[$contentsObjectId] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';

    $objects[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << ' . $resources
        . ' >> /Contents ' . $contentsObjectId . ' 0 R >>';

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $index => $object) {
        $offsets[$index] = strlen($pdf);
        $pdf .= $index . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n";
    $maxObjectId = max(array_keys($objects));
    $pdf .= '0 ' . ($maxObjectId + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i <= $maxObjectId; $i++) {
        if (!isset($offsets[$i])) {
            $pdf .= "0000000000 65535 f \n";
            continue;
        }
        $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
    }

    $pdf .= "trailer\n";
    $pdf .= '<< /Size ' . ($maxObjectId + 1) . ' /Root 1 0 R >>' . "\n";
    $pdf .= "startxref\n";
    $pdf .= $xrefOffset . "\n";
    $pdf .= '%%EOF';

    return $pdf;
}

function certificado_pdf_text_cmd(string $font, float $size, float $x, float $y, string $text): string
{
    return 'BT /' . $font . ' ' . number_format($size, 2, '.', '') . ' Tf '
        . number_format($x, 2, '.', '') . ' ' . number_format($y, 2, '.', '')
        . ' Td (' . certificado_pdf_escape($text) . ') Tj ET';
}

function certificado_pdf_text_justify_cmd(
    string $font,
    float $size,
    float $x,
    float $y,
    string $text,
    float $targetWidth
): string {
    $spaces = substr_count($text, ' ');
    if ($spaces <= 0) {
        return certificado_pdf_text_cmd($font, $size, $x, $y, $text);
    }

    $textWidth = certificado_estimar_ancho_texto($text, $size, certificado_factor_fuente($font));
    $extraSpace = $targetWidth - $textWidth;
    if ($extraSpace <= 0.05) {
        return certificado_pdf_text_cmd($font, $size, $x, $y, $text);
    }

    $wordSpacing = $extraSpace / $spaces;
    if ($wordSpacing > 6.0) {
        $wordSpacing = 6.0;
    }

    return 'BT /' . $font . ' ' . number_format($size, 2, '.', '') . ' Tf '
        . number_format($x, 2, '.', '') . ' ' . number_format($y, 2, '.', '') . ' Td '
        . number_format($wordSpacing, 3, '.', '') . ' Tw '
        . '(' . certificado_pdf_escape($text) . ') Tj 0 Tw ET';
}

function certificado_pdf_image_cmd(string $alias, float $x, float $y, float $width, float $height): string
{
    return 'q '
        . number_format($width, 2, '.', '') . ' 0 0 '
        . number_format($height, 2, '.', '') . ' '
        . number_format($x, 2, '.', '') . ' '
        . number_format($y, 2, '.', '') . ' cm '
        . '/' . $alias . ' Do Q';
}

function certificado_pdf_contact_icon_commands(string $type, float $x, float $y): array
{
    $accentStroke = '0.89 0.54 0.58 RG 1 w';
    $accentFill = '0.89 0.54 0.58 rg';
    $cmd = [];

    if ($type === 'phone') {
        $cmd[] = $accentStroke . ' ' . number_format($x, 2, '.', '') . ' ' . number_format($y - 5.50, 2, '.', '')
            . ' 8.40 11.00 re S';
        $cmd[] = $accentStroke . ' ' . number_format($x + 2.00, 2, '.', '') . ' ' . number_format($y + 3.80, 2, '.', '')
            . ' m ' . number_format($x + 6.40, 2, '.', '') . ' ' . number_format($y + 3.80, 2, '.', '') . ' l S';
        $cmd[] = $accentFill . ' ' . number_format($x + 3.70, 2, '.', '') . ' ' . number_format($y - 4.10, 2, '.', '')
            . ' 1.00 1.00 re f';
        return $cmd;
    }

    if ($type === 'email') {
        $cmd[] = $accentStroke . ' ' . number_format($x, 2, '.', '') . ' ' . number_format($y - 3.60, 2, '.', '')
            . ' 10.00 7.20 re S';
        $cmd[] = $accentStroke . ' ' . number_format($x, 2, '.', '') . ' ' . number_format($y + 3.60, 2, '.', '')
            . ' m ' . number_format($x + 5.00, 2, '.', '') . ' ' . number_format($y - 0.20, 2, '.', '')
            . ' l ' . number_format($x + 10.00, 2, '.', '') . ' ' . number_format($y + 3.60, 2, '.', '') . ' l S';
        return $cmd;
    }

    if ($type === 'instagram') {
        $cmd[] = $accentStroke . ' ' . number_format($x, 2, '.', '') . ' ' . number_format($y - 4.50, 2, '.', '')
            . ' 9.00 9.00 re S';
        $cmd[] = $accentStroke . ' ' . certificado_pdf_circle_path($x + 4.50, $y, 2.10) . ' S';
        $cmd[] = $accentFill . ' ' . number_format($x + 6.80, 2, '.', '') . ' ' . number_format($y + 2.80, 2, '.', '')
            . ' 1.00 1.00 re f';
        return $cmd;
    }

    if ($type === 'location') {
        $cmd[] = $accentStroke . ' ' . certificado_pdf_circle_path($x + 4.50, $y + 1.30, 2.40) . ' S';
        $cmd[] = $accentStroke . ' ' . number_format($x + 4.50, 2, '.', '') . ' ' . number_format($y - 5.40, 2, '.', '')
            . ' m ' . number_format($x + 2.40, 2, '.', '') . ' ' . number_format($y - 0.60, 2, '.', '')
            . ' l ' . number_format($x + 6.60, 2, '.', '') . ' ' . number_format($y - 0.60, 2, '.', '') . ' l h S';
        return $cmd;
    }

    $cmd[] = $accentFill . ' ' . number_format($x + 3.00, 2, '.', '') . ' ' . number_format($y - 1.20, 2, '.', '')
        . ' 3.20 3.20 re f';

    return $cmd;
}

function certificado_pdf_circle_path(float $centerX, float $centerY, float $radius): string
{
    $k = 0.5522847498 * $radius;

    return number_format($centerX + $radius, 2, '.', '') . ' ' . number_format($centerY, 2, '.', '') . ' m '
        . number_format($centerX + $radius, 2, '.', '') . ' ' . number_format($centerY + $k, 2, '.', '') . ' '
        . number_format($centerX + $k, 2, '.', '') . ' ' . number_format($centerY + $radius, 2, '.', '') . ' '
        . number_format($centerX, 2, '.', '') . ' ' . number_format($centerY + $radius, 2, '.', '') . ' c '
        . number_format($centerX - $k, 2, '.', '') . ' ' . number_format($centerY + $radius, 2, '.', '') . ' '
        . number_format($centerX - $radius, 2, '.', '') . ' ' . number_format($centerY + $k, 2, '.', '') . ' '
        . number_format($centerX - $radius, 2, '.', '') . ' ' . number_format($centerY, 2, '.', '') . ' c '
        . number_format($centerX - $radius, 2, '.', '') . ' ' . number_format($centerY - $k, 2, '.', '') . ' '
        . number_format($centerX - $k, 2, '.', '') . ' ' . number_format($centerY - $radius, 2, '.', '') . ' '
        . number_format($centerX, 2, '.', '') . ' ' . number_format($centerY - $radius, 2, '.', '') . ' c '
        . number_format($centerX + $k, 2, '.', '') . ' ' . number_format($centerY - $radius, 2, '.', '') . ' '
        . number_format($centerX + $radius, 2, '.', '') . ' ' . number_format($centerY - $k, 2, '.', '') . ' '
        . number_format($centerX + $radius, 2, '.', '') . ' ' . number_format($centerY, 2, '.', '') . ' c';
}

function certificado_pdf_center_text_cmd(string $font, float $size, float $y, string $text): string
{
    $width = certificado_estimar_ancho_texto($text, $size, certificado_factor_fuente($font));
    $x = (595 - $width) / 2;

    if ($x < 40) {
        $x = 40;
    }

    return certificado_pdf_text_cmd($font, $size, $x, $y, $text);
}

function certificado_pdf_center_text_cmd_area(string $font, float $size, float $y, string $text, float $xStart, float $xEnd): string
{
    $width = certificado_estimar_ancho_texto($text, $size, certificado_factor_fuente($font));
    $x = $xStart + (($xEnd - $xStart) - $width) / 2;

    if ($x < $xStart) {
        $x = $xStart;
    }

    return certificado_pdf_text_cmd($font, $size, $x, $y, $text);
}

function certificado_estimar_ancho_texto(string $text, float $size, float $factor = 0.52): float
{
    $encoded = certificado_pdf_encode($text);
    $length = strlen($encoded);

    return $length * $size * $factor;
}

function certificado_factor_fuente(string $font): float
{
    if ($font === 'F3') {
        return 0.54;
    }

    if ($font === 'F6') {
        return 0.54;
    }

    if ($font === 'F5') {
        return 0.5;
    }

    return 0.52;
}

function certificado_wrap_text(string $text, int $maxChars = 90): array
{
    $normalized = preg_replace('/\s+/', ' ', trim($text));
    if ($normalized === null || $normalized === '') {
        return [];
    }

    return explode("\n", wordwrap($normalized, $maxChars, "\n", true));
}

function certificado_wrap_text_by_width(string $text, float $fontSize, float $maxWidth, string $font = 'F2'): array
{
    $normalized = preg_replace('/\s+/', ' ', trim($text));
    if ($normalized === null || $normalized === '') {
        return [];
    }

    $words = explode(' ', $normalized);
    $lines = [];
    $current = '';

    foreach ($words as $word) {
        $testLine = $current === '' ? $word : $current . ' ' . $word;
        $testWidth = certificado_estimar_ancho_texto($testLine, $fontSize, certificado_factor_fuente($font));

        if ($current === '' || $testWidth <= $maxWidth) {
            $current = $testLine;
            continue;
        }

        $lines[] = $current;
        $current = $word;
    }

    if ($current !== '') {
        $lines[] = $current;
    }

    return $lines;
}

function certificado_parse_rich_text(string $text): array
{
    $clean = trim($text);
    if ($clean === '') {
        return [];
    }

    $parts = preg_split('/(<\/?strong>)/i', $clean, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    if (!is_array($parts)) {
        return [['text' => preg_replace('/\s+/', ' ', $clean) ?? $clean, 'bold' => false]];
    }

    $segments = [];
    $isBold = false;
    foreach ($parts as $part) {
        $lower = strtolower($part);
        if ($lower === '<strong>') {
            $isBold = true;
            continue;
        }
        if ($lower === '</strong>') {
            $isBold = false;
            continue;
        }

        $normalized = preg_replace('/\s+/', ' ', $part);
        if ($normalized === null || $normalized === '') {
            continue;
        }

        $segments[] = [
            'text' => $normalized,
            'bold' => $isBold,
        ];
    }

    return $segments;
}

function certificado_wrap_rich_text_by_width(array $segments, float $fontSize, float $maxWidth): array
{
    $tokens = [];
    $safeMaxWidth = $maxWidth * 0.98;
    foreach ($segments as $segment) {
        if (!is_array($segment)) {
            continue;
        }
        $text = (string) ($segment['text'] ?? '');
        $bold = (bool) ($segment['bold'] ?? false);
        if ($text === '') {
            continue;
        }

        $pieces = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if (!is_array($pieces)) {
            continue;
        }

        foreach ($pieces as $piece) {
            $isSpace = preg_match('/^\s+$/u', $piece) === 1;
            $tokenText = $isSpace ? ' ' : $piece;
            if (!$isSpace) {
                $chunks = certificado_split_word_by_width($tokenText, $bold, $fontSize, $safeMaxWidth);
                foreach ($chunks as $chunk) {
                    $tokens[] = [
                        'text' => $chunk,
                        'bold' => $bold,
                        'space' => false,
                        'width' => certificado_rich_token_width($chunk, $bold, $fontSize),
                    ];
                }
                continue;
            }

            $tokens[] = [
                'text' => $tokenText,
                'bold' => false,
                'space' => true,
                'width' => certificado_rich_token_width($tokenText, false, $fontSize),
            ];
        }
    }

    $lines = [];
    $currentTokens = [];
    $currentWidth = 0.0;
    $spaceCount = 0;

    foreach ($tokens as $token) {
        if ($token['space'] && empty($currentTokens)) {
            continue;
        }

        $tokenWidth = (float) $token['width'];
        $wouldOverflow = ($currentWidth + $tokenWidth) > $safeMaxWidth;

        if ($wouldOverflow && !empty($currentTokens) && !$token['space']) {
            [$trimmedTokens, $trimmedWidth, $trimmedSpaces] = certificado_trim_rich_line($currentTokens, $currentWidth, $spaceCount);
            if (!empty($trimmedTokens)) {
                $lines[] = [
                    'tokens' => $trimmedTokens,
                    'width' => $trimmedWidth,
                    'spaces' => $trimmedSpaces,
                ];
            }
            $currentTokens = [];
            $currentWidth = 0.0;
            $spaceCount = 0;
        }

        if ($token['space'] && empty($currentTokens)) {
            continue;
        }

        $currentTokens[] = $token;
        $currentWidth += $tokenWidth;
        if ($token['space']) {
            $spaceCount++;
        }
    }

    [$trimmedTokens, $trimmedWidth, $trimmedSpaces] = certificado_trim_rich_line($currentTokens, $currentWidth, $spaceCount);
    if (!empty($trimmedTokens)) {
        $lines[] = [
            'tokens' => $trimmedTokens,
            'width' => $trimmedWidth,
            'spaces' => $trimmedSpaces,
        ];
    }

    return $lines;
}

function certificado_trim_rich_line(array $tokens, float $width, int $spaces): array
{
    while (!empty($tokens)) {
        $last = end($tokens);
        if (!is_array($last) || !(bool) ($last['space'] ?? false)) {
            break;
        }
        $width -= (float) ($last['width'] ?? 0);
        $spaces--;
        array_pop($tokens);
    }

    if ($width < 0) {
        $width = 0;
    }
    if ($spaces < 0) {
        $spaces = 0;
    }

    return [$tokens, $width, $spaces];
}

function certificado_rich_token_width(string $text, bool $bold, float $fontSize): float
{
    $font = $bold ? 'F6' : 'F2';
    $factor = certificado_factor_fuente($font);

    return certificado_estimar_ancho_texto($text, $fontSize, $factor) * 1.03;
}

function certificado_pdf_rich_line_commands(
    array $line,
    float $fontSize,
    float $x,
    float $y,
    float $targetWidth,
    bool $justify
): array {
    $tokens = $line['tokens'] ?? [];
    if (!is_array($tokens) || empty($tokens)) {
        return [];
    }

    $lineWidth = (float) ($line['width'] ?? 0);
    $spaces = (int) ($line['spaces'] ?? 0);
    $extraSpace = 0.0;
    if ($justify && $spaces > 0 && $lineWidth > 0) {
        $extraSpace = ($targetWidth - $lineWidth) / $spaces;
        if ($extraSpace < 0) {
            $extraSpace = 0.0;
        }
        if ($extraSpace > 2.2) {
            $extraSpace = 2.2;
        }
    }

    $parts = [];
    $currentFont = '';
    foreach ($tokens as $token) {
        if (!is_array($token)) {
            continue;
        }

        $tokenText = (string) ($token['text'] ?? '');
        $isSpace = (bool) ($token['space'] ?? false);
        if ($tokenText === '') {
            continue;
        }

        $font = $isSpace ? 'F2' : ((bool) ($token['bold'] ?? false) ? 'F6' : 'F2');
        if ($font !== $currentFont) {
            $parts[] = '/' . $font . ' ' . number_format($fontSize, 2, '.', '') . ' Tf';
            $currentFont = $font;
        }
        $parts[] = '(' . certificado_pdf_escape($tokenText) . ') Tj';
    }

    if (empty($parts)) {
        return [];
    }

    $command = 'BT '
        . number_format($x, 2, '.', '') . ' '
        . number_format($y, 2, '.', '') . ' Td '
        . number_format($extraSpace, 3, '.', '') . ' Tw '
        . implode(' ', $parts)
        . ' 0 Tw ET';

    return [$command];
}

function certificado_split_word_by_width(string $word, bool $bold, float $fontSize, float $maxWidth): array
{
    if ($word === '') {
        return [];
    }

    $wordWidth = certificado_rich_token_width($word, $bold, $fontSize);
    if ($wordWidth <= $maxWidth) {
        return [$word];
    }

    $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($chars) || empty($chars)) {
        return [$word];
    }

    $chunks = [];
    $current = '';
    foreach ($chars as $char) {
        $candidate = $current . $char;
        $candidateWidth = certificado_rich_token_width($candidate, $bold, $fontSize);
        if ($current !== '' && $candidateWidth > $maxWidth) {
            $chunks[] = $current;
            $current = $char;
            continue;
        }
        $current = $candidate;
    }

    if ($current !== '') {
        $chunks[] = $current;
    }

    return $chunks;
}

function certificado_pdf_encode(string $text): string
{
    $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
    if ($encoded === false) {
        $encoded = utf8_decode($text);
    }

    return $encoded;
}

function certificado_pdf_escape(string $text): string
{
    $encoded = certificado_pdf_encode($text);

    return str_replace(
        ['\\', '(', ')', "\r", "\n"],
        ['\\\\', '\\(', '\\)', ' ', ' '],
        $encoded
    );
}

function certificado_cargar_activos_pdf(): array
{
    $basePath = __DIR__ . '/../assets/img/';
    $assets = [];

    $logo = certificado_cargar_imagen_como_jpeg($basePath . 'Logo principal.png', 'Im1');
    if ($logo !== null) {
        $assets[$logo['alias']] = $logo;
    }

    $headCoach = certificado_cargar_imagen_como_jpeg($basePath . 'headcoach.png', 'Im2');
    if ($headCoach !== null) {
        $assets[$headCoach['alias']] = $headCoach;
    }

    $marcaCentral = certificado_cargar_imagen_como_jpeg($basePath . 'marcacentral.png', 'Im3');
    if ($marcaCentral !== null) {
        $assets[$marcaCentral['alias']] = $marcaCentral;
    }

    $coachCoreographer = certificado_cargar_imagen_como_jpeg($basePath . 'coach_coreographer.png', 'Im4');
    if ($coachCoreographer !== null) {
        $assets[$coachCoreographer['alias']] = $coachCoreographer;
    }

    return $assets;
}

function certificado_cargar_imagen_como_jpeg(string $path, string $alias): ?array
{
    if (!file_exists($path) || !function_exists('imagecreatefromstring')) {
        return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return null;
    }

    $source = @imagecreatefromstring($raw);
    if ($source === false) {
        return null;
    }

    $width = imagesx($source);
    $height = imagesy($source);
    if ($width <= 0 || $height <= 0) {
        imagedestroy($source);
        return null;
    }

    $canvas = imagecreatetruecolor($width, $height);
    if ($canvas === false) {
        imagedestroy($source);
        return null;
    }

    // Fondo gris claro para evitar fondo negro al convertir transparencia a JPG.
    $bg = imagecolorallocate($canvas, 242, 242, 242);
    imagefilledrectangle($canvas, 0, 0, $width, $height, $bg);
    imagealphablending($canvas, true);
    imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

    ob_start();
    imagejpeg($canvas, null, 92);
    $jpegData = (string) ob_get_clean();

    imagedestroy($canvas);
    imagedestroy($source);

    if ($jpegData === '') {
        return null;
    }

    return [
        'alias' => $alias,
        'pixel_width' => $width,
        'pixel_height' => $height,
        'data' => $jpegData,
    ];
}

function certificado_datos_institucion(): array
{
    return [
        'club_short' => 'MAITEAM',
        'club_label' => 'CLUB DE PATINAJE ARTISTICO',
        'discipline' => 'PATINAJE ARTISTICO',
        'contacts' => [
            ['icon' => 'phone', 'text' => '+56 9 814 390 49'],
            ['icon' => 'email', 'text' => 'bymaiteam@gmail.com'],
            ['icon' => 'instagram', 'text' => '@by.maiteam'],
            ['icon' => 'location', 'text' => 'La Florida, Santiago'],
        ],
        'head_coach' => 'Maira Flores Olivares',
        'head_role' => 'HEAD COACH',
        'assistant_coach' => 'Maite Flores Olivares',
        'assistant_role' => 'COACH & CHOREOGRAPHER',
    ];
}

function certificado_slug(string $text): string
{
    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $candidate = $transliterated === false ? $text : $transliterated;
    $candidate = strtolower($candidate);
    $candidate = preg_replace('/[^a-z0-9]+/', '-', $candidate) ?? '';
    $candidate = trim($candidate, '-');

    return $candidate !== '' ? $candidate : 'deportista';
}
