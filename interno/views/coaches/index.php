<?php
/** @var array $coaches */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Coaches</h1>
            <p>Gestion de coaches disponibles.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=coaches&action=create')) ?>">Nuevo coach</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Coach creado correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Coach actualizado.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Coach eliminado.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Telefono</th>
                    <th>Email</th>
                    <th>Especialidad</th>
                    <th>Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coaches)): ?>
                    <tr>
                        <td colspan="6">Aun no hay coaches registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($coaches as $coach): ?>
                        <tr>
                            <td><?= e($coach['nombre']) ?></td>
                            <td><?= e($coach['telefono']) ?></td>
                            <td><?= e($coach['email']) ?></td>
                            <td><?= e($coach['especialidad']) ?></td>
                            <td><?= $coach['activo'] ? 'Si' : 'No' ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=coaches&action=edit&id=' . $coach['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=coaches&action=delete')) ?>" onsubmit="return confirm('Eliminar este coach?');">
                                    <input type="hidden" name="id" value="<?= e((string) $coach['id']) ?>">
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
