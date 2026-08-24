<?php
/** @var array $cuotas */
/** @var string|null $flash */
/** @var int $flash_created */
/** @var int $flash_skipped */
/** @var int $flash_apoderados */
/** @var array $errors_generador */
/** @var array $generador */
/** @var string $periodo_filtro */
/** @var string $estado_filtro */

$periodoFiltro = (string) ($periodo_filtro ?? '');
$estadoFiltro = (string) ($estado_filtro ?? '');
$queryParams = [];
if ($periodoFiltro !== '') {
    $queryParams['periodo'] = $periodoFiltro;
}
if ($estadoFiltro !== '') {
    $queryParams['estado'] = $estadoFiltro;
}
$filtroQuery = empty($queryParams) ? '' : '&' . http_build_query($queryParams);
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Cuotas socios</h1>
            <p>Cuotas mensuales de socios por apoderado.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=cuotas&action=create')) ?>">Generar cuotas</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Cuota creada correctamente.</div>
    <?php elseif ($flash === 'generated'): ?>
        <div class="alert success">
            Generacion finalizada: <?= e((string) $flash_created) ?> cuotas creadas, <?= e((string) $flash_skipped) ?> meses omitidos por ya existir.
        </div>
    <?php elseif ($flash === 'generated_all'): ?>
        <div class="alert success">
            Generacion masiva finalizada para <?= e((string) $flash_apoderados) ?> apoderados:
            <?= e((string) $flash_created) ?> cuotas creadas y <?= e((string) $flash_skipped) ?> meses omitidos.
        </div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Cuota actualizada.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Cuota eliminada.</div>
    <?php endif; ?>

    <div class="card" style="margin-top: 20px;">
        <h2>Filtro por mes</h2>
        <form class="form form-inline" method="get" action="<?= e(base_url('/')) ?>">
            <input type="hidden" name="page" value="cuotas">
            <label>
                Mes
                <input type="month" name="periodo" value="<?= e($periodoFiltro) ?>">
            </label>
            <label>
                Estado
                <select name="estado">
                    <option value="" <?= $estadoFiltro === '' ? 'selected' : '' ?>>Todos</option>
                    <option value="pendiente" <?= $estadoFiltro === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="pagado" <?= $estadoFiltro === 'pagado' ? 'selected' : '' ?>>Pagado</option>
                </select>
            </label>
            <button type="submit" class="button">Filtrar</button>
            <a class="button ghost" href="<?= e(base_url('/?page=cuotas')) ?>">Limpiar</a>
        </form>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>Completar cuotas pendientes (masivo)</h2>
        <p class="muted">Crea cuotas pendientes faltantes para todos los apoderados en el rango seleccionado (solo marzo a diciembre del mismo ano).</p>

        <?php if (!empty($errors_generador)): ?>
            <div class="alert danger">
                <?php foreach ($errors_generador as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="form form-inline" method="post" action="<?= e(base_url('/?page=cuotas&action=generate_all' . $filtroQuery)) ?>">
            <label>
                Mes inicio
                <input type="month" name="periodo_inicio" required value="<?= e($generador['periodo_inicio'] ?? '') ?>">
            </label>
            <label>
                Mes fin
                <input type="month" name="periodo_fin" required value="<?= e($generador['periodo_fin'] ?? '') ?>">
            </label>
            <label>
                Monto mensual
                <input type="number" step="0.01" min="0" name="monto" value="<?= e((string) ($generador['monto'] ?? '3000')) ?>">
            </label>
            <button type="submit" class="button">Completar pendientes</button>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Apoderado</th>
                    <th>Periodo</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Fecha pago</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cuotas)): ?>
                    <tr>
                        <td colspan="6">
                            <?= ($periodoFiltro !== '' || $estadoFiltro !== '') ? 'No hay cuotas con el filtro seleccionado.' : 'Aun no hay cuotas registradas.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cuotas as $cuota): ?>
                        <tr>
                            <td><?= e($cuota['apoderado_nombre']) ?></td>
                            <td><?= e($cuota['periodo']) ?></td>
                            <td>$<?= e(number_format((float) $cuota['monto'], 0, ',', '.')) ?></td>
                            <td><?= e($cuota['estado']) ?></td>
                            <td><?= e($cuota['fecha_pago']) ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=cuotas&action=edit&id=' . $cuota['id'] . $filtroQuery)) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=cuotas&action=delete' . $filtroQuery)) ?>" onsubmit="return confirm('Eliminar esta cuota?');">
                                    <input type="hidden" name="id" value="<?= e((string) $cuota['id']) ?>">
                                    <input type="hidden" name="periodo_filtro" value="<?= e($periodoFiltro) ?>">
                                    <input type="hidden" name="estado_filtro" value="<?= e($estadoFiltro) ?>">
                                    <button type="submit" class="link danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
