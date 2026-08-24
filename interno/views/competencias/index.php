<?php
/** @var array $competencias */
/** @var string|null $flash */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Competencias</h1>
            <p>Registro de competencias para habilitar certificados justificativos.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=competencias&action=create')) ?>">Nueva competencia</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Competencia creada correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Competencia actualizada.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Competencia eliminada.</div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Competencia</th>
                    <th>Nivel</th>
                    <th>Fecha inicio</th>
                    <th>Fecha termino</th>
                    <th>Lugar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($competencias)): ?>
                    <tr>
                        <td colspan="6">Aun no hay competencias registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($competencias as $competencia): ?>
                        <tr>
                            <td><?= e($competencia['nombre']) ?></td>
                            <td><?= e($competencia['nivel_nombre'] ?? '') ?></td>
                            <td><?= e($competencia['fecha_inicio']) ?></td>
                            <td><?= e($competencia['fecha_fin']) ?></td>
                            <td><?= e($competencia['lugar']) ?></td>
                            <td class="actions">
                                <a class="link" href="<?= e(base_url('/?page=competencias&action=edit&id=' . $competencia['id'])) ?>">Editar</a>
                                <form method="post" action="<?= e(base_url('/?page=competencias&action=delete')) ?>" onsubmit="return confirm('Eliminar esta competencia?');">
                                    <input type="hidden" name="id" value="<?= e((string) $competencia['id']) ?>">
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
