<?php
/** @var array $deportistas */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Deportistas</h1>
            <p>Gestion de deportistas asociados a apoderados.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=deportistas&action=create')) ?>">Nuevo deportista</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Deportista creado correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Deportista actualizado.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Deportista eliminado.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apoderado</th>
                    <th>Nivel</th>
                    <th>Edad competencia</th>
                    <th>Modalidades</th>
                    <th>Categoria</th>
                    <th>Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deportistas)): ?>
                    <tr>
                        <td colspan="8">Aun no hay deportistas registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deportistas as $deportista): ?>
                        <tr>
                            <td><?= e($deportista['nombre']) ?></td>
                            <td><?= e($deportista['apoderado_nombre']) ?></td>
                            <td><?= e($deportista['nivel_nombre'] ?? '') ?></td>
                            <td>
                                <?php if ($deportista['edad_competencia'] !== null): ?>
                                    <span class="chip"><?= e((string) $deportista['edad_competencia']) ?> años</span>
                                <?php else: ?>
                                    <span class="chip muted">Sin dato</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($deportista['modalidades_competencia'] ?: 'Sin asignar') ?></td>
                            <td><?= e($deportista['categoria']) ?></td>
                            <td><?= $deportista['activo'] ? 'Si' : 'No' ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=deportistas&action=edit&id=' . $deportista['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=deportistas&action=delete')) ?>" onsubmit="return confirm('Eliminar este deportista?');">
                                    <input type="hidden" name="id" value="<?= e((string) $deportista['id']) ?>">
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
