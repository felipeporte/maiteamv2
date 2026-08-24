<?php
/** @var array $modalidad */
/** @var array $errors */
/** @var array $coaches */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar modalidad' : 'Nueva modalidad' ?></h1>
            <p><?= $isEdit ? 'Actualiza los datos de la modalidad.' : 'Completa la informacion de la modalidad.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=modalidades')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=modalidades&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $modalidad['id']) ?>">
        <?php endif; ?>

        <label>
            Nombre
            <input type="text" name="nombre" required value="<?= e($modalidad['nombre'] ?? '') ?>">
        </label>

        <label>
            Costo mensual
            <input type="number" step="0.01" min="0" name="costo_mensual" value="<?= e((string) ($modalidad['costo_mensual'] ?? '')) ?>">
        </label>

        <label>
            Profe
            <select name="coach_id" required>
                <option value="">Selecciona un profe</option>
                <?php foreach ($coaches as $coach): ?>
                    <option value="<?= e((string) $coach['id']) ?>" <?= (int) ($modalidad['coach_id'] ?? 0) === (int) $coach['id'] ? 'selected' : '' ?>>
                        <?= e($coach['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="activo" <?= !empty($modalidad['activo']) ? 'checked' : '' ?>>
            Activo
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear modalidad' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=modalidades')) ?>">Cancelar</a>
        </div>
    </form>
</section>
