<?php
/** @var array $inscripciones */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Inscripciones</h1>
            <p>Inscripciones de deportistas a modalidades.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=inscripciones&action=create')) ?>">Nueva inscripcion</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Inscripcion creada correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Inscripcion actualizada.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Inscripcion eliminada.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Deportista</th>
                    <th>Apoderado</th>
                    <th>Modalidad</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inscripciones)): ?>
                    <tr>
                        <td colspan="7">Aun no hay inscripciones registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($inscripciones as $inscripcion): ?>
                        <tr>
                            <td><?= e($inscripcion['deportista_nombre']) ?></td>
                            <td><?= e($inscripcion['apoderado_nombre']) ?></td>
                            <td><?= e($inscripcion['modalidad_nombre']) ?></td>
                            <td><?= e($inscripcion['fecha_inicio']) ?></td>
                            <td><?= e($inscripcion['fecha_fin']) ?></td>
                            <td><?= $inscripcion['activo'] ? 'Si' : 'No' ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=inscripciones&action=edit&id=' . $inscripcion['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=inscripciones&action=delete')) ?>" onsubmit="return confirm('Eliminar esta inscripcion?');">
                                    <input type="hidden" name="id" value="<?= e((string) $inscripcion['id']) ?>">
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
