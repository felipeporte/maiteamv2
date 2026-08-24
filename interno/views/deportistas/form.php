<?php
/** @var array $deportista */
/** @var array $errors */
/** @var array $apoderados */
/** @var array $niveles */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar deportista' : 'Nuevo deportista' ?></h1>
            <p><?= $isEdit ? 'Actualiza los datos del deportista.' : 'Completa la informacion del deportista.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=deportistas')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=deportistas&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $deportista['id']) ?>">
        <?php endif; ?>

        <label>
            Apoderado
            <select name="apoderado_id" required>
                <option value="">Selecciona un apoderado</option>
                <?php foreach ($apoderados as $apoderado): ?>
                    <option value="<?= e((string) $apoderado['id']) ?>" <?= (int) $deportista['apoderado_id'] === (int) $apoderado['id'] ? 'selected' : '' ?>>
                        <?= e($apoderado['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Nombre
            <input type="text" name="nombre" required value="<?= e($deportista['nombre'] ?? '') ?>">
        </label>

        <label>
            RUT
            <input type="text" name="rut" required value="<?= e(format_rut($deportista['rut'] ?? '')) ?>" placeholder="12345678-9">
        </label>

        <label>
            Fecha de nacimiento
            <input type="date" name="fecha_nacimiento" value="<?= e($deportista['fecha_nacimiento'] ?? '') ?>">
        </label>

        <label>
            Categoria
            <input type="text" name="categoria" value="<?= e($deportista['categoria'] ?? '') ?>">
        </label>

        <label>
            Nivel
            <select name="nivel_id" required>
                <option value="">Selecciona un nivel</option>
                <?php foreach ($niveles as $nivel): ?>
                    <option value="<?= e((string) $nivel['id']) ?>" <?= (int) ($deportista['nivel_id'] ?? 0) === (int) $nivel['id'] ? 'selected' : '' ?>>
                        <?= e($nivel['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="activo" <?= !empty($deportista['activo']) ? 'checked' : '' ?>>
            Activo
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear deportista' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=deportistas')) ?>">Cancelar</a>
        </div>
    </form>
</section>
