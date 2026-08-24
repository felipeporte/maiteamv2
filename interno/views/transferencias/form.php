<?php
/** @var array $transferencia */
/** @var array $errors */
/** @var array $coaches */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar transferencia' : 'Nueva transferencia' ?></h1>
            <p><?= $isEdit ? 'Actualiza el registro de transferencia.' : 'Registra una transferencia a coach.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=transferencias')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=transferencias&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $transferencia['id']) ?>">
        <?php endif; ?>

        <label>
            Coach
            <select name="coach_id" required>
                <option value="">Selecciona un coach</option>
                <?php foreach ($coaches as $coach): ?>
                    <option value="<?= e((string) $coach['id']) ?>" <?= (int) $transferencia['coach_id'] === (int) $coach['id'] ? 'selected' : '' ?>>
                        <?= e($coach['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Periodo (YYYY-MM)
            <input type="month" name="periodo" value="<?= e($transferencia['periodo'] ?? '') ?>">
        </label>

        <label>
            Fecha de transferencia
            <input type="date" name="fecha_transferencia" required value="<?= e($transferencia['fecha_transferencia'] ?? '') ?>">
        </label>

        <label>
            Monto
            <input type="number" step="0.01" min="0" name="monto" required value="<?= e((string) ($transferencia['monto'] ?? '')) ?>">
        </label>

        <label>
            Metodo
            <input type="text" name="metodo" value="<?= e($transferencia['metodo'] ?? '') ?>">
        </label>

        <label>
            Referencia
            <input type="text" name="referencia" value="<?= e($transferencia['referencia'] ?? '') ?>">
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear transferencia' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=transferencias')) ?>">Cancelar</a>
        </div>
    </form>
</section>
