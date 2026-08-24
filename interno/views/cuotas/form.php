<?php
/** @var array $cuota */
/** @var array $errors */
/** @var array $apoderados */
/** @var string $action */
/** @var string $periodo_filtro */
/** @var string $estado_filtro */

$isEdit = $action === 'edit';
$periodoFiltro = (string) ($periodo_filtro ?? '');
$estadoFiltro = (string) ($estado_filtro ?? '');
$queryParams = ['page' => 'cuotas'];
if ($periodoFiltro !== '') {
    $queryParams['periodo'] = $periodoFiltro;
}
if ($estadoFiltro !== '') {
    $queryParams['estado'] = $estadoFiltro;
}
$filtroQuery = '?' . http_build_query($queryParams);
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar cuota socio' : 'Generar cuotas socio' ?></h1>
            <p><?= $isEdit ? 'Actualiza una cuota existente.' : 'Crea cuotas pendientes por rango mensual (marzo a diciembre del mismo ano), sin duplicar meses ya existentes.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/' . $filtroQuery)) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=cuotas&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $cuota['id']) ?>">
        <?php endif; ?>
        <input type="hidden" name="periodo_filtro" value="<?= e($periodoFiltro) ?>">
        <input type="hidden" name="estado_filtro" value="<?= e($estadoFiltro) ?>">

        <label>
            Apoderado
            <select name="apoderado_id" required>
                <option value="">Selecciona un apoderado</option>
                <?php foreach ($apoderados as $apoderado): ?>
                    <option value="<?= e((string) $apoderado['id']) ?>" <?= (int) $cuota['apoderado_id'] === (int) $apoderado['id'] ? 'selected' : '' ?>>
                        <?= e($apoderado['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <?php if ($isEdit): ?>
            <label>
                Periodo (YYYY-MM)
                <input type="month" name="periodo" required value="<?= e($cuota['periodo'] ?? '') ?>">
            </label>

            <label>
                Monto
                <input type="number" step="0.01" min="0" name="monto" value="<?= e((string) ($cuota['monto'] ?? '3000')) ?>">
            </label>

            <label>
                Estado
                <select name="estado">
                    <?php foreach (['pendiente', 'pagado'] as $estado): ?>
                        <option value="<?= e($estado) ?>" <?= ($cuota['estado'] ?? 'pendiente') === $estado ? 'selected' : '' ?>>
                            <?= e(ucfirst($estado)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Fecha de pago
                <input type="date" name="fecha_pago" value="<?= e($cuota['fecha_pago'] ?? '') ?>">
            </label>
        <?php else: ?>
            <div class="form-row">
                <label>
                    Mes inicio
                    <input type="month" name="periodo_inicio" required value="<?= e($cuota['periodo_inicio'] ?? '') ?>">
                </label>

                <label>
                    Mes fin
                    <input type="month" name="periodo_fin" required value="<?= e($cuota['periodo_fin'] ?? '') ?>">
                </label>

                <label>
                    Monto mensual
                    <input type="number" step="0.01" min="0" name="monto" value="<?= e((string) ($cuota['monto'] ?? '3000')) ?>">
                </label>
            </div>
            <p class="muted">Las cuotas nuevas se crean en estado pendiente. Solo se permite rango marzo-diciembre del mismo ano. Si un mes ya existe (pagado o pendiente), se omite.</p>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Generar cuotas' ?></button>
            <a class="button ghost" href="<?= e(base_url('/' . $filtroQuery)) ?>">Cancelar</a>
        </div>
    </form>
</section>
