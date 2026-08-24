<?php
/** @var array $transferencias */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Transferencias a coaches</h1>
            <p>Registro de montos transferidos desde el club a cada coach.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=transferencias&action=create')) ?>">Nueva transferencia</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Transferencia creada correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Transferencia actualizada.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Transferencia eliminada.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Coach</th>
                    <th>Periodo</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Metodo</th>
                    <th>Referencia</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transferencias)): ?>
                    <tr>
                        <td colspan="7">Aun no hay transferencias registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transferencias as $transferencia): ?>
                        <tr>
                            <td><?= e($transferencia['coach_nombre']) ?></td>
                            <td><?= e($transferencia['periodo']) ?></td>
                            <td><?= e($transferencia['fecha_transferencia']) ?></td>
                            <td>$<?= e(number_format((float) $transferencia['monto'], 0, ',', '.')) ?></td>
                            <td><?= e($transferencia['metodo']) ?></td>
                            <td><?= e($transferencia['referencia']) ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=transferencias&action=edit&id=' . $transferencia['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=transferencias&action=delete')) ?>" onsubmit="return confirm('Eliminar esta transferencia?');">
                                    <input type="hidden" name="id" value="<?= e((string) $transferencia['id']) ?>">
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
