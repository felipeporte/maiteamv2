<?php
/** @var array $competencia */
/** @var array $errors */
/** @var array $niveles */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar competencia' : 'Nueva competencia' ?></h1>
            <p><?= $isEdit ? 'Actualiza los datos de la competencia.' : 'Registra una competencia para el justificativo escolar.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=competencias')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=competencias&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $competencia['id']) ?>">
        <?php endif; ?>

        <label>
            Nivel
            <select name="nivel_id" required>
                <option value="">Selecciona un nivel</option>
                <?php foreach ($niveles as $nivel): ?>
                    <option value="<?= e((string) $nivel['id']) ?>" <?= (int) ($competencia['nivel_id'] ?? 0) === (int) $nivel['id'] ? 'selected' : '' ?>>
                        <?= e($nivel['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Nombre competencia
            <input type="text" name="nombre" required value="<?= e($competencia['nombre'] ?? '') ?>">
        </label>

        <div class="form-row">
            <label>
                Fecha inicio
                <input type="date" name="fecha_inicio" required value="<?= e($competencia['fecha_inicio'] ?? '') ?>">
            </label>

            <label>
                Fecha termino
                <input type="date" name="fecha_fin" value="<?= e($competencia['fecha_fin'] ?? '') ?>">
            </label>
        </div>

        <label>
            Lugar
            <input type="text" name="lugar" value="<?= e($competencia['lugar'] ?? '') ?>">
        </label>

        <label>
            Observaciones
            <textarea name="observaciones" rows="3"><?= e($competencia['observaciones'] ?? '') ?></textarea>
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear competencia' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=competencias')) ?>">Cancelar</a>
        </div>
    </form>
</section>
