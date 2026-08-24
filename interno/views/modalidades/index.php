<?php
/** @var array $modalidades */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Modalidades</h1>
            <p>Modalidades y costos mensuales.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=modalidades&action=create')) ?>">Nueva modalidad</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Modalidad creada correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Modalidad actualizada.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Modalidad eliminada.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Costo mensual</th>
                    <th>Profe</th>
                    <th>Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($modalidades)): ?>
                    <tr>
                        <td colspan="5">Aun no hay modalidades registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($modalidades as $modalidad): ?>
                        <tr>
                            <td><?= e($modalidad['nombre']) ?></td>
                            <td>$<?= e(number_format((float) $modalidad['costo_mensual'], 0, ',', '.')) ?></td>
                            <td><?= e($modalidad['coach_nombre'] ?? '') ?></td>
                            <td><?= $modalidad['activo'] ? 'Si' : 'No' ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=modalidades&action=edit&id=' . $modalidad['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=modalidades&action=delete')) ?>" onsubmit="return confirm('Eliminar esta modalidad?');">
                                    <input type="hidden" name="id" value="<?= e((string) $modalidad['id']) ?>">
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
