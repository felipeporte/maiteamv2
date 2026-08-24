<?php
/** @var array $coach */
/** @var array $errors */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar coach' : 'Nuevo coach' ?></h1>
            <p><?= $isEdit ? 'Actualiza los datos del coach.' : 'Completa la informacion del coach.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=coaches')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=coaches&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $coach['id']) ?>">
        <?php endif; ?>

        <label>
            Nombre
            <input type="text" name="nombre" required value="<?= e($coach['nombre'] ?? '') ?>">
        </label>

        <label>
            Telefono
            <input type="text" name="telefono" value="<?= e($coach['telefono'] ?? '') ?>">
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?= e($coach['email'] ?? '') ?>">
        </label>

        <label>
            Especialidad
            <input type="text" name="especialidad" value="<?= e($coach['especialidad'] ?? '') ?>">
        </label>

        <label class="checkbox">
            <input type="checkbox" name="activo" <?= !empty($coach['activo']) ? 'checked' : '' ?>>
            Activo
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear coach' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=coaches')) ?>">Cancelar</a>
        </div>
    </form>
</section>
