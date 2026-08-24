<?php
/** @var array $apoderado */
/** @var array $errors */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar apoderado' : 'Nuevo apoderado' ?></h1>
            <p><?= $isEdit ? 'Actualiza los datos del apoderado.' : 'Completa la informacion del apoderado.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=socios')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=socios&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $apoderado['id']) ?>">
        <?php endif; ?>

        <label>
            Nombre
            <input type="text" name="nombre" required value="<?= e($apoderado['nombre'] ?? '') ?>">
        </label>

        <label>
            Telefono
            <input type="text" name="telefono" value="<?= e($apoderado['telefono'] ?? '') ?>">
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?= e($apoderado['email'] ?? '') ?>">
        </label>

        <label>
            Direccion
            <input type="text" name="direccion" value="<?= e($apoderado['direccion'] ?? '') ?>">
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear apoderado' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=socios')) ?>">Cancelar</a>
        </div>
    </form>
</section>
