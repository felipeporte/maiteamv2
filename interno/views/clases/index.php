<?php
/** @var array $clases */
/** @var string|null $flash */
/** @var array $clases_extras */
/** @var string|null $mes */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Clases</h1>
            <p>Control de clases, inscritos y valores adeudados del mes.</p>
        </div>
        <div class="form-actions">
            <form method="get" action="<?= e(base_url('/')) ?>" class="month-filter"><input type="hidden" name="page" value="clases"><label>Mes<input type="month" name="mes" value="<?= e($mes ?? '') ?>"></label><button class="button ghost" type="submit">Filtrar</button><?php if ($mes !== null): ?><a class="link" href="<?= e(base_url('/?page=clases')) ?>">Ver todos</a><?php endif; ?></form>
            <a class="button ghost" href="<?= e(base_url('/?page=clases&action=create')) ?>">Nueva clase</a>
            <a class="button" href="/extras/">Generar extras</a>
        </div>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Clase creada correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Clase actualizada.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Clase eliminada.</div>
    <?php elseif ($flash === 'extra-created'): ?>
        <div class="alert success">Clase extra creada y distribuida entre las deportistas seleccionadas.</div>
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
                            <td><?= e($clase['fecha']) ?><?php if (!empty($clase['extra_id'])): ?><br><span class="chip">Extra</span><?php endif; ?></td>
                            <td><?= e($clase['deportista_nombre']) ?></td>
                            <td><?= e($clase['apoderado_nombre']) ?></td>
                            <td><?= e($clase['coach_nombre']) ?></td>
                            <td>$<?= e(number_format((float) $clase['tarifa'], 0, ',', '.')) ?><?php if (!empty($clase['extra_id'])): ?><br><small><?= e($clase['competencia_nombre'] ?? 'Sesión grupal') ?></small><?php endif; ?></td>
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

    <?php if (!empty($clases_extras)): ?>
        <h2>Sesiones extras</h2>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Fecha</th><th>Competencia</th><th>Coach</th><th>Participantes</th><th>Valor clase</th><th>Pista total</th><th>Total por deportista</th></tr></thead>
                <tbody><?php foreach ($clases_extras as $extra): ?><tr>
                    <td><?= e($extra['fecha']) ?></td><td><?= e($extra['competencia_nombre'] ?? 'Sin competencia') ?></td><td><?= e($extra['coach_nombre']) ?></td>
                    <td><?= e((string) $extra['participantes']) ?></td><td>$<?= e(number_format((float) $extra['valor_clase'], 0, ',', '.')) ?></td>
                    <td>$<?= e(number_format((float) $extra['costo_pista'], 0, ',', '.')) ?></td>
                    <td>$<?= e(number_format((float) $extra['valor_clase'] + ((float) $extra['costo_pista'] / max(1, (int) $extra['participantes'])), 0, ',', '.')) ?></td>
                </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
