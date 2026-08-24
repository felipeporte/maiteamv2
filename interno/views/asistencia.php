<?php
/** @var array $asistencias */
/** @var array $resumen */
/** @var array $estados */
/** @var string $fecha */
/** @var string|null $flash */

$selectedDate = $fecha ?? date('Y-m-d');
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Asistencia</h1>
            <p>Marca presencia, ausencias y justificativos sobre las clases registradas en PHP.</p>
        </div>
        <form class="kpi-filter" method="get" action="<?= e(base_url('/')) ?>">
            <input type="hidden" name="page" value="asistencia">
            <label for="fecha">Fecha</label>
            <input id="fecha" name="fecha" type="date" value="<?= e($selectedDate) ?>">
            <button class="button ghost" type="submit">Ver</button>
        </form>
    </div>

    <?php if ($flash === 'updated'): ?>
        <div class="alert success">Asistencia actualizada correctamente.</div>
    <?php elseif ($flash === 'error'): ?>
        <div class="alert danger">No se pudo guardar la asistencia. Revisa los datos ingresados.</div>
    <?php endif; ?>

    <div class="grid">
        <article class="card">
            <h2>Total</h2>
            <p><?= e((string) ($resumen['total'] ?? 0)) ?> clases en la fecha seleccionada.</p>
        </article>
        <article class="card">
            <h2>Presentes</h2>
            <p><?= e((string) ($resumen['presentes'] ?? 0)) ?> marcadas como presentes.</p>
        </article>
        <article class="card">
            <h2>Ausentes</h2>
            <p><?= e((string) ($resumen['ausentes'] ?? 0)) ?> marcadas como ausentes.</p>
        </article>
        <article class="card">
            <h2>Justificadas</h2>
            <p><?= e((string) ($resumen['justificadas'] ?? 0)) ?> con justificativo.</p>
        </article>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Deportista</th>
                    <th>Apoderado</th>
                    <th>Coach</th>
                    <th>Estado clase</th>
                    <th>Asistencia actual</th>
                    <th>Gestion</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($asistencias)): ?>
                    <tr>
                        <td colspan="6">No hay clases registradas para esta fecha.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($asistencias as $clase): ?>
                        <tr>
                            <td><?= e($clase['deportista_nombre']) ?></td>
                            <td><?= e($clase['apoderado_nombre']) ?></td>
                            <td><?= e($clase['coach_nombre']) ?></td>
                            <td><?= e(ucfirst((string) $clase['estado'])) ?></td>
                            <td><?= e(asistencia_estado_label((string) ($clase['asistencia'] ?? 'pendiente'))) ?></td>
                            <td>
                                <form method="post" action="<?= e(base_url('/?page=asistencia&fecha=' . rawurlencode($selectedDate))) ?>" class="form">
                                    <input type="hidden" name="id" value="<?= e((string) $clase['id']) ?>">
                                    <input type="hidden" name="fecha" value="<?= e($selectedDate) ?>">

                                    <label>
                                        Estado
                                        <select name="asistencia">
                                            <?php foreach ($estados as $value => $label): ?>
                                                <option value="<?= e($value) ?>" <?= (string) ($clase['asistencia'] ?? 'pendiente') === $value ? 'selected' : '' ?>>
                                                    <?= e($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>

                                    <label>
                                        Notas
                                        <input type="text" name="asistencia_notas" value="<?= e($clase['asistencia_notas'] ?? '') ?>">
                                    </label>

                                    <button type="submit" class="button ghost">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
