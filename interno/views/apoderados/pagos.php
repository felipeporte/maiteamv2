<?php
/** @var array $apoderado */
/** @var array $resumen */

$formatMoney = static fn ($value): string => '$' . number_format((float) $value, 0, ',', '.');
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Pagos de <?= e($apoderado['nombre']) ?></h1>
            <p>Resumen de pagos registrados a coaches y cuotas de socio.</p>
        </div>
        <div class="eventos-detail-actions">
            <a class="button ghost" href="<?= e(base_url('/?page=socios&action=edit&id=' . $apoderado['id'])) ?>">Editar apoderado</a>
            <a class="button" href="<?= e(base_url('/?page=socios')) ?>">Volver a apoderados</a>
        </div>
    </div>

    <div class="eventos-summary-cards">
        <article class="card eventos-metric">
            <p>Total pagado registrado</p>
            <h2><?= e($formatMoney($resumen['total_pagado'])) ?></h2>
            <p>Pagos a coaches y cuotas pagadas.</p>
        </article>
        <article class="card eventos-metric">
            <p>Pagos a coaches</p>
            <h2><?= e($formatMoney($resumen['total_pagos'])) ?></h2>
            <p>
                <?= e((string) $resumen['cantidad_pagos']) ?> pagos registrados.
                <?php if (!empty($resumen['ultima_fecha_pago'])): ?>
                    Último: <?= e($resumen['ultima_fecha_pago']) ?>.
                <?php endif; ?>
            </p>
        </article>
        <article class="card eventos-metric">
            <p>Cuotas de socio pagadas</p>
            <h2><?= e($formatMoney($resumen['total_cuotas_pagadas'])) ?></h2>
            <p><?= e((string) $resumen['cantidad_cuotas_pagadas']) ?> cuotas pagadas.</p>
        </article>
        <article class="card eventos-metric">
            <p>Cuotas pendientes</p>
            <h2><?= e($formatMoney($resumen['total_cuotas_pendientes'])) ?></h2>
            <p><?= e((string) $resumen['cantidad_cuotas_pendientes']) ?> cuotas pendientes.</p>
        </article>
    </div>

    <section class="eventos-panel" style="margin-top: 20px;">
        <div class="eventos-panel-head">
            <div>
                <h2>Historial de pagos a coaches</h2>
                <p>Ordenado desde el pago más reciente.</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Coach</th>
                        <th>Periodo</th>
                        <th>Clases</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resumen['pagos'])): ?>
                        <tr><td colspan="7">Este apoderado todavía no tiene pagos a coaches registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($resumen['pagos'] as $pago): ?>
                            <?php
                            $periodo = trim(
                                trim((string) ($pago['periodo_inicio'] ?? '')) . ' - ' . trim((string) ($pago['periodo_fin'] ?? '')),
                                ' -'
                            );
                            ?>
                            <tr>
                                <td><?= e($pago['fecha_pago']) ?></td>
                                <td><?= e($pago['coach_nombre'] ?? 'Coach no disponible') ?></td>
                                <td><?= $periodo !== '' ? e($periodo) : '—' ?></td>
                                <td><?= e((string) $pago['clases_total']) ?></td>
                                <td><?= e($pago['metodo'] ?: '—') ?></td>
                                <td><?= e($pago['referencia'] ?: '—') ?></td>
                                <td><?= e($formatMoney($pago['monto_total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="eventos-panel" style="margin-top: 20px;">
        <div class="eventos-panel-head">
            <div>
                <h2>Cuotas de socio</h2>
                <p>Estado de las cuotas mensuales registradas para este apoderado.</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Fecha de pago</th>
                        <th>Estado</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resumen['cuotas'])): ?>
                        <tr><td colspan="4">Este apoderado todavía no tiene cuotas de socio registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($resumen['cuotas'] as $cuota): ?>
                            <tr>
                                <td><?= e($cuota['periodo']) ?></td>
                                <td><?= e($cuota['fecha_pago'] ?: '—') ?></td>
                                <td><?= e(ucfirst($cuota['estado'])) ?></td>
                                <td><?= e($formatMoney($cuota['monto'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
