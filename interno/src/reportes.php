<?php

declare(strict_types=1);

function reportes_apoderados(string $periodo, bool $soloPositivos = false): array
{
    $start = $periodo . '-01';
    $end = date('Y-m-t', strtotime($start));

    $stmtApoderados = db()->query('SELECT id, nombre FROM apoderados ORDER BY nombre');
    $apoderados = $stmtApoderados->fetchAll();

    $stmtCuotas = db()->prepare(
        'SELECT COUNT(*) AS registros, '
        . 'SUM(monto) AS cuota_total, '
        . 'SUM(CASE WHEN estado = "pendiente" THEN monto ELSE 0 END) AS cuota_pendiente, '
        . 'SUM(CASE WHEN estado = "pagado" THEN monto ELSE 0 END) AS cuota_pagada '
        . 'FROM cuotas_socios '
        . 'WHERE apoderado_id = :apoderado_id AND periodo = :periodo'
    );

    $stmtTieneInscripcionActiva = db()->prepare(
        'SELECT COUNT(*) '
        . 'FROM inscripciones i '
        . 'INNER JOIN deportistas d ON d.id = i.deportista_id '
        . 'WHERE d.apoderado_id = :apoderado_id '
        . 'AND i.activo = 1 '
        . 'AND i.fecha_inicio <= :end '
        . 'AND (i.fecha_fin IS NULL OR i.fecha_fin >= :start)'
    );

    $stmtModalidades = db()->prepare(
        'SELECT SUM(m.costo_mensual) '
        . 'FROM inscripciones i '
        . 'INNER JOIN modalidades m ON m.id = i.modalidad_id '
        . 'WHERE i.deportista_id IN (SELECT id FROM deportistas WHERE apoderado_id = :apoderado_id) '
        . 'AND i.activo = 1 '
        . 'AND i.fecha_inicio <= :end '
        . 'AND (i.fecha_fin IS NULL OR i.fecha_fin >= :start)'
    );

    $stmtClases = db()->prepare(
        'SELECT SUM(c.tarifa) '
        . 'FROM clases c '
        . 'INNER JOIN deportistas d ON d.id = c.deportista_id '
        . 'WHERE d.apoderado_id = :apoderado_id '
        . 'AND c.estado = "realizada" '
        . 'AND c.fecha BETWEEN :start AND :end'
    );

    $stmtPagos = db()->prepare(
        'SELECT SUM(monto_total) FROM pagos WHERE apoderado_id = :apoderado_id '
        . 'AND fecha_pago BETWEEN :start AND :end'
    );

    $resultados = [];

    foreach ($apoderados as $apoderado) {
        $id = (int) $apoderado['id'];

        $stmtCuotas->execute([
            'apoderado_id' => $id,
            'periodo' => $periodo,
        ]);
        $cuotasRow = $stmtCuotas->fetch();

        $stmtTieneInscripcionActiva->execute([
            'apoderado_id' => $id,
            'start' => $start,
            'end' => $end,
        ]);
        $tieneInscripcionActivaMes = (int) ($stmtTieneInscripcionActiva->fetchColumn() ?? 0) > 0;

        $tieneCuotaRegistrada = (int) ($cuotasRow['registros'] ?? 0) > 0;
        $cuota = $tieneCuotaRegistrada
            ? (float) ($cuotasRow['cuota_total'] ?? 0)
            : ($tieneInscripcionActivaMes ? 3000.0 : 0.0);
        $pagosCuota = $tieneCuotaRegistrada ? (float) ($cuotasRow['cuota_pagada'] ?? 0) : 0.0;

        $stmtModalidades->execute([
            'apoderado_id' => $id,
            'start' => $start,
            'end' => $end,
        ]);
        $modalidades = (float) ($stmtModalidades->fetchColumn() ?? 0);

        $stmtClases->execute([
            'apoderado_id' => $id,
            'start' => $start,
            'end' => $end,
        ]);
        $clases = (float) ($stmtClases->fetchColumn() ?? 0);

        $stmtPagos->execute([
            'apoderado_id' => $id,
            'start' => $start,
            'end' => $end,
        ]);
        $pagosOtros = (float) ($stmtPagos->fetchColumn() ?? 0);
        $pagos = $pagosOtros + $pagosCuota;

        $total = $cuota + $modalidades + $clases;
        $saldo = $total - $pagos;

        $resultados[] = [
            'apoderado' => $apoderado['nombre'],
            'cuota' => $cuota,
            'modalidades' => $modalidades,
            'clases' => $clases,
            'total' => $total,
            'pagos_cuota' => $pagosCuota,
            'pagos_otros' => $pagosOtros,
            'pagos' => $pagos,
            'saldo' => $saldo,
        ];
    }

    if ($soloPositivos) {
        $resultados = array_values(array_filter($resultados, static function (array $row): bool {
            return (float) $row['saldo'] > 0;
        }));
    }

    return $resultados;
}

function reportes_coaches(string $periodo, bool $soloPositivos = false): array
{
    $start = $periodo . '-01';
    $end = date('Y-m-t', strtotime($start));

    $stmtCoaches = db()->query('SELECT id, nombre FROM coaches ORDER BY nombre');
    $coaches = $stmtCoaches->fetchAll();

    $stmtModalidades = db()->prepare(
        'SELECT SUM(m.costo_mensual) '
        . 'FROM inscripciones i '
        . 'INNER JOIN modalidades m ON m.id = i.modalidad_id '
        . 'WHERE m.coach_id = :coach_id '
        . 'AND i.activo = 1 '
        . 'AND i.fecha_inicio <= :end '
        . 'AND (i.fecha_fin IS NULL OR i.fecha_fin >= :start)'
    );

    $stmtClases = db()->prepare(
        'SELECT SUM(c.tarifa) '
        . 'FROM clases c '
        . 'WHERE c.coach_id = :coach_id '
        . 'AND c.estado = "realizada" '
        . 'AND c.fecha BETWEEN :start AND :end'
    );

    $stmtPagos = db()->prepare(
        'SELECT SUM(monto_total) FROM pagos WHERE coach_id = :coach_id '
        . 'AND fecha_pago BETWEEN :start AND :end'
    );

    $stmtTransferencias = db()->prepare(
        'SELECT SUM(monto) FROM transferencias_coaches WHERE coach_id = :coach_id '
        . 'AND fecha_transferencia BETWEEN :start AND :end'
    );

    $resultados = [];

    foreach ($coaches as $coach) {
        $id = (int) $coach['id'];

        $stmtModalidades->execute([
            'coach_id' => $id,
            'start' => $start,
            'end' => $end,
        ]);
        $modalidades = (float) ($stmtModalidades->fetchColumn() ?? 0);

        $stmtClases->execute([
            'coach_id' => $id,
            'start' => $start,
            'end' => $end,
        ]);
        $clases = (float) ($stmtClases->fetchColumn() ?? 0);

        $stmtPagos->execute([
            'coach_id' => $id,
            'start' => $start,
            'end' => $end,
        ]);
        $pagos = (float) ($stmtPagos->fetchColumn() ?? 0);

        $stmtTransferencias->execute([
            'coach_id' => $id,
            'start' => $start,
            'end' => $end,
        ]);
        $transferido = (float) ($stmtTransferencias->fetchColumn() ?? 0);

        $total = $modalidades + $clases;
        $saldo = $total - $pagos;
        $porTransferir = $pagos - $transferido;

        $resultados[] = [
            'coach' => $coach['nombre'],
            'modalidades' => $modalidades,
            'clases' => $clases,
            'total' => $total,
            'pagos' => $pagos,
            'saldo' => $saldo,
            'transferido' => $transferido,
            'por_transferir' => $porTransferir,
        ];
    }

    if ($soloPositivos) {
        $resultados = array_values(array_filter($resultados, static function (array $row): bool {
            return (float) $row['saldo'] > 0;
        }));
    }

    return $resultados;
}
