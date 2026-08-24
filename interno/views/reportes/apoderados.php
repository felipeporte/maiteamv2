<?php
/** @var string $periodo */
/** @var array $resultados */
/** @var bool $solo_positivos */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Reporte por apoderado</h1>
            <p>Resumen mensual de cuota, modalidades, clases y pagos.</p>
            <p class="muted">
                <a class="link" href="<?= e(base_url('/?page=reportes&tipo=coaches&periodo=' . $periodo . '&solo_positivos=' . ($solo_positivos ? '1' : '0'))) ?>">Ver por coach</a>
            </p>
        </div>
        <form class="form-inline" method="get" action="<?= e(base_url('/')) ?>">
            <input type="hidden" name="page" value="reportes">
            <input type="hidden" name="tipo" value="apoderados">
            <label>
                Periodo
                <input type="month" name="periodo" value="<?= e($periodo) ?>">
            </label>
            <label>
                <input type="checkbox" name="solo_positivos" value="1" <?= $solo_positivos ? 'checked' : '' ?>>
                Solo saldos positivos
            </label>
            <button type="submit" class="button">Ver</button>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Apoderado</th>
                    <th>Cuota</th>
                    <th>Modalidades</th>
                    <th>Clases</th>
                    <th>Total</th>
                    <th>Pagos cuota</th>
                    <th>Pagos otros</th>
                    <th>Pagos</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($resultados)): ?>
                    <tr>
                        <td colspan="9">No hay datos para el periodo seleccionado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($resultados as $row): ?>
                        <tr>
                            <td><?= e($row['apoderado']) ?></td>
                            <td>$<?= e(number_format((float) $row['cuota'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['modalidades'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['clases'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['total'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['pagos_cuota'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['pagos_otros'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['pagos'], 0, ',', '.')) ?></td>
                            <td class="<?= $row['saldo'] > 0 ? 'danger-text' : 'success-text' ?>">$<?= e(number_format((float) $row['saldo'], 0, ',', '.')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
