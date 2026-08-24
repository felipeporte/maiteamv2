<?php
/** @var array $inscripcion */
/** @var array $errors */
/** @var array $deportistas */
/** @var array $modalidades */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar inscripcion' : 'Nueva inscripcion' ?></h1>
            <p><?= $isEdit ? 'Actualiza los datos de la inscripcion.' : 'Completa la informacion de la inscripcion.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=inscripciones')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=inscripciones&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $inscripcion['id']) ?>">
        <?php endif; ?>

        <label>
            Deportista
            <select name="deportista_id" required>
                <option value="">Selecciona un deportista</option>
                <?php foreach ($deportistas as $deportista): ?>
                    <option value="<?= e((string) $deportista['id']) ?>" <?= (int) $inscripcion['deportista_id'] === (int) $deportista['id'] ? 'selected' : '' ?>>
                        <?= e($deportista['nombre']) ?> (<?= e($deportista['apoderado_nombre']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Modalidad
            <select name="modalidad_id" required>
                <option value="">Selecciona una modalidad</option>
                <?php foreach ($modalidades as $modalidad): ?>
                    <option value="<?= e((string) $modalidad['id']) ?>" <?= (int) $inscripcion['modalidad_id'] === (int) $modalidad['id'] ? 'selected' : '' ?>>
                        <?= e($modalidad['nombre']) ?> ($<?= e(number_format((float) $modalidad['costo_mensual'], 0, ',', '.')) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Fecha inicio
            <input type="date" name="fecha_inicio" required value="<?= e($inscripcion['fecha_inicio'] ?? '') ?>">
        </label>

        <label>
            Fecha fin
            <input type="date" name="fecha_fin" value="<?= e($inscripcion['fecha_fin'] ?? '') ?>">
        </label>

        <label class="checkbox">
            <input type="checkbox" name="activo" <?= !empty($inscripcion['activo']) ? 'checked' : '' ?>>
            Activo
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear inscripcion' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=inscripciones')) ?>">Cancelar</a>
        </div>
    </form>
</section>
