<?php
/** @var string $periodo */
/** @var array $resultados */
/** @var bool $solo_positivos */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Reporte por coach</h1>
            <p>Resumen mensual de modalidades, clases y pagos por coach.</p>
            <p class="muted">
                <a class="link" href="<?= e(base_url('/?page=reportes&tipo=apoderados&periodo=' . $periodo . '&solo_positivos=' . ($solo_positivos ? '1' : '0'))) ?>">Ver por apoderado</a>
            </p>
        </div>
        <form class="form-inline" method="get" action="<?= e(base_url('/')) ?>">
            <input type="hidden" name="page" value="reportes">
            <input type="hidden" name="tipo" value="coaches">
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
                    <th>Coach</th>
                    <th>Modalidades</th>
                    <th>Clases</th>
                    <th>Total</th>
                    <th>Pagos</th>
                    <th>Saldo</th>
                    <th>Transferido</th>
                    <th>Por transferir</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($resultados)): ?>
                    <tr>
                        <td colspan="8">No hay datos para el periodo seleccionado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($resultados as $row): ?>
                        <tr>
                            <td><?= e($row['coach']) ?></td>
                            <td>$<?= e(number_format((float) $row['modalidades'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['clases'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['total'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['pagos'], 0, ',', '.')) ?></td>
                            <td class="<?= $row['saldo'] > 0 ? 'danger-text' : 'success-text' ?>">$<?= e(number_format((float) $row['saldo'], 0, ',', '.')) ?></td>
                            <td>$<?= e(number_format((float) $row['transferido'], 0, ',', '.')) ?></td>
                            <td class="<?= $row['por_transferir'] > 0 ? 'danger-text' : 'success-text' ?>">$<?= e(number_format((float) $row['por_transferir'], 0, ',', '.')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
