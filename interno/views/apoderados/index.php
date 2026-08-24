<?php
/** @var array $apoderados */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Apoderados</h1>
            <p>Gestion de apoderados (socios) del club.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=socios&action=create')) ?>">Nuevo apoderado</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Apoderado creado correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Apoderado actualizado.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Apoderado eliminado.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Telefono</th>
                    <th>Email</th>
                    <th>Direccion</th>
                    <th>Saldo desde inscripción</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($apoderados)): ?>
                    <tr>
                        <td colspan="6">Aun no hay apoderados registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($apoderados as $apoderado): ?>
                        <tr>
                            <td><?= e($apoderado['nombre']) ?></td>
                            <td><?= e($apoderado['telefono']) ?></td>
                            <td><?= e($apoderado['email']) ?></td>
                            <td><?= e($apoderado['direccion']) ?></td>
                            <td>$<?= e(number_format((float) $apoderado['saldo'], 0, ',', '.')) ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=socios&action=edit&id=' . $apoderado['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=socios&action=delete')) ?>" onsubmit="return confirm('Eliminar este apoderado?');">
                                    <input type="hidden" name="id" value="<?= e((string) $apoderado['id']) ?>">
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
