<?php
/** @var array $clase */
/** @var array $errors */
/** @var array $deportistas */
/** @var array $coaches */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar clase' : 'Nueva clase' ?></h1>
            <p><?= $isEdit ? 'Actualiza los datos de la clase.' : 'Completa la informacion de la clase.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=clases')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=clases&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $clase['id']) ?>">
        <?php endif; ?>

        <label>
            Deportista
            <select name="deportista_id" required>
                <option value="">Selecciona un deportista</option>
                <?php foreach ($deportistas as $deportista): ?>
                    <option value="<?= e((string) $deportista['id']) ?>" <?= (int) $clase['deportista_id'] === (int) $deportista['id'] ? 'selected' : '' ?>>
                        <?= e($deportista['nombre']) ?> (<?= e($deportista['apoderado_nombre']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Coach
            <select name="coach_id" required>
                <option value="">Selecciona un coach</option>
                <?php foreach ($coaches as $coach): ?>
                    <option value="<?= e((string) $coach['id']) ?>" <?= (int) $clase['coach_id'] === (int) $coach['id'] ? 'selected' : '' ?>>
                        <?= e($coach['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Fecha
            <input type="date" name="fecha" required value="<?= e($clase['fecha'] ?? '') ?>">
        </label>

        <label>
            Duracion (minutos)
            <input type="number" min="0" name="duracion_min" value="<?= e((string) ($clase['duracion_min'] ?? '')) ?>">
        </label>

        <label>
            Tarifa
            <input type="number" step="0.01" min="0" name="tarifa" value="<?= e((string) ($clase['tarifa'] ?? '')) ?>">
        </label>

        <label>
            Estado
            <select name="estado">
                <?php foreach (['programada', 'realizada', 'anulada'] as $estado): ?>
                    <option value="<?= e($estado) ?>" <?= $clase['estado'] === $estado ? 'selected' : '' ?>>
                        <?= e(ucfirst($estado)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Notas
            <input type="text" name="notas" value="<?= e($clase['notas'] ?? '') ?>">
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear clase' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=clases')) ?>">Cancelar</a>
        </div>
    </form>
</section>
