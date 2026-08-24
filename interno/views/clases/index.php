<?php
/** @var array $clases */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Clases</h1>
            <p>Registro de clases realizadas o programadas.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=clases&action=create')) ?>">Nueva clase</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Clase creada correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Clase actualizada.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Clase eliminada.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Deportista</th>
                    <th>Apoderado</th>
                    <th>Coach</th>
                    <th>Tarifa</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clases)): ?>
                    <tr>
                        <td colspan="7">Aun no hay clases registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clases as $clase): ?>
                        <tr>
                            <td><?= e($clase['fecha']) ?></td>
                            <td><?= e($clase['deportista_nombre']) ?></td>
                            <td><?= e($clase['apoderado_nombre']) ?></td>
                            <td><?= e($clase['coach_nombre']) ?></td>
                            <td>$<?= e(number_format((float) $clase['tarifa'], 0, ',', '.')) ?></td>
                            <td><?= e($clase['estado']) ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=clases&action=edit&id=' . $clase['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=clases&action=delete')) ?>" onsubmit="return confirm('Eliminar esta clase?');">
                                    <input type="hidden" name="id" value="<?= e((string) $clase['id']) ?>">
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
