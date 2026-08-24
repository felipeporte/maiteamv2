<?php
/** @var array $pagos */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Pagos</h1>
            <p>Pagos registrados por apoderado y coach.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=pagos&action=create')) ?>">Nuevo pago</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Pago creado correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Pago actualizado.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Pago eliminado.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Apoderado</th>
                    <th>Coach</th>
                    <th>Periodo</th>
                    <th>Clases</th>
                    <th>Monto</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagos)): ?>
                    <tr>
                        <td colspan="7">Aun no hay pagos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td><?= e($pago['fecha_pago']) ?></td>
                            <td><?= e($pago['apoderado_nombre']) ?></td>
                            <td><?= e($pago['coach_nombre']) ?></td>
                            <td><?= e(trim(($pago['periodo_inicio'] ?? '') . ' - ' . ($pago['periodo_fin'] ?? ''))) ?></td>
                            <td><?= e((string) $pago['clases_total']) ?></td>
                            <td>$<?= e(number_format((float) $pago['monto_total'], 0, ',', '.')) ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=pagos&action=edit&id=' . $pago['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=pagos&action=delete')) ?>" onsubmit="return confirm('Eliminar este pago?');">
                                    <input type="hidden" name="id" value="<?= e((string) $pago['id']) ?>">
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
