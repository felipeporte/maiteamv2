<?php
/** @var array $eventos */
/** @var array|null $evento */
/** @var array $evento_form */
/** @var array $inscripciones */
/** @var array $deportistas_elegibles */
/** @var array $niveles_evento */
/** @var array $errors */
/** @var string|null $flash */
/** @var string $action */
/** @var bool $schema_ready */

$isEditing = $action === 'edit' && !empty($evento_form['id']);
$eventoEstadoLabels = [
    'borrador' => 'Borrador',
    'abierto' => 'Abierto',
    'cerrado' => 'Cerrado',
    'finalizado' => 'Finalizado',
];
$inscripcionEstadoLabels = [
    'pendiente' => 'Pendiente',
    'pagado' => 'Pagado',
    'anulado' => 'Anulado',
];
$formatMoney = static fn ($value): string => '$' . number_format((float) $value, 0, ',', '.');

$totalEventos = count($eventos);
$totalInscripciones = 0;
$totalMonto = 0.0;
$eventosAbiertos = 0;
foreach ($eventos as $eventoRow) {
    $totalInscripciones += (int) ($eventoRow['inscritos_count'] ?? 0);
    $totalMonto += (float) ($eventoRow['monto_total'] ?? 0);
    if (($eventoRow['estado'] ?? '') === 'abierto') {
        $eventosAbiertos++;
    }
}

$selectedEventTitle = $evento !== null ? trim((string) ($evento['nombre'] ?? '')) : '';
?>
<section class="page eventos-page">
    <div class="page-header">
        <div>
            <h1>Eventos federados</h1>
            <p>Gestiona competencias federadas por nivel. Cada evento cubre todas las modalidades y subniveles que tengan ese nivel asignado en la ficha competitiva.</p>
        </div>
        <a class="button" href="#evento-form"><?= $isEditing ? 'Editar evento' : 'Nuevo evento' ?></a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Evento creado correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Evento actualizado correctamente.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Evento eliminado.</div>
    <?php elseif ($flash === 'registered'): ?>
        <div class="alert success">Inscripcion registrada.</div>
    <?php endif; ?>

    <?php if (!$schema_ready): ?>
        <div class="alert danger">
            Falta aplicar la migracion 008 de eventos federados en esta base de datos.
        </div>
    <?php endif; ?>

    <div class="grid eventos-summary-grid">
        <article class="card eventos-metric">
            <p class="kicker">Eventos</p>
            <h2><?= e((string) $totalEventos) ?></h2>
            <p>Registrados en el modulo federado.</p>
        </article>
        <article class="card eventos-metric">
            <p class="kicker">Abiertos</p>
            <h2><?= e((string) $eventosAbiertos) ?></h2>
            <p>Listos para inscribir deportistas.</p>
        </article>
        <article class="card eventos-metric">
            <p class="kicker">Inscripciones</p>
            <h2><?= e((string) $totalInscripciones) ?></h2>
            <p>Inscripciones activas entre todos los eventos.</p>
        </article>
        <article class="card eventos-metric">
            <p class="kicker">Monto estimado</p>
            <h2><?= e($formatMoney($totalMonto)) ?></h2>
            <p>Total acumulado desde las inscripciones registradas.</p>
        </article>
    </div>

    <div class="eventos-panel" id="evento-form">
        <div class="eventos-panel-head">
            <div>
                <p class="kicker"><?= $isEditing ? 'Editar evento' : 'Crear evento' ?></p>
                <h2><?= $isEditing ? 'Modificar evento federado' : 'Definir nuevo evento federado' ?></h2>
                <p>El evento queda separado del flujo legacy y usa la tabla nueva de asignacion competitiva para filtrar deportistas elegibles por nivel.</p>
            </div>
            <?php if ($evento !== null): ?>
                <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>">Ver detalle</a>
            <?php endif; ?>
        </div>

        <form class="form eventos-form" method="post" action="<?= e(base_url('/?page=eventos&action=' . ($isEditing ? 'edit' : 'create'))) ?>">
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= e((string) $evento_form['id']) ?>">
            <?php endif; ?>

            <div class="form-row">
                <label>
                    Nombre del evento
                    <input type="text" name="nombre" required value="<?= e($evento_form['nombre'] ?? '') ?>" placeholder="Open Club MaiTeam 2026">
                </label>

                <label>
                    Nivel
                    <select name="nivel" required>
                        <option value="">Selecciona un nivel</option>
                        <?php foreach ($niveles_evento as $nivel): ?>
                            <option value="<?= e((string) $nivel['nivel']) ?>" <?= (string) ($evento_form['nivel'] ?? '') === (string) $nivel['nivel'] ? 'selected' : '' ?>>
                                <?= e((string) $nivel['nivel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="hint">Ejemplo: Promotional cubre todas las modalidades y subniveles marcados en ese nivel.</span>
                </label>
            </div>

            <div class="form-row">
                <label>
                    Fecha inicio
                    <input type="date" name="fecha_inicio" required value="<?= e($evento_form['fecha_inicio'] ?? '') ?>">
                </label>

                <label>
                    Fecha termino
                    <input type="date" name="fecha_fin" value="<?= e($evento_form['fecha_fin'] ?? '') ?>">
                </label>
            </div>

            <div class="form-row">
                <label>
                    Lugar
                    <input type="text" name="lugar" value="<?= e($evento_form['lugar'] ?? '') ?>" placeholder="Pista central / ciudad / recinto">
                </label>

                <label>
                    Estado
                    <select name="estado" required>
                        <?php foreach ($eventoEstadoLabels as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= (string) ($evento_form['estado'] ?? 'borrador') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="form-row">
                <label>
                    Costo inscripcion
                    <input type="number" name="costo_inscripcion" step="0.01" min="0" value="<?= e((string) ($evento_form['costo_inscripcion'] ?? '0.00')) ?>">
                </label>

                <label>
                    Cupo
                    <input type="number" name="cupo" min="0" value="<?= e((string) ($evento_form['cupo'] ?? '')) ?>">
                </label>
            </div>

            <label>
                Observaciones
                <textarea name="observaciones" rows="3" placeholder="Notas internas sobre el evento"><?= e($evento_form['observaciones'] ?? '') ?></textarea>
            </label>

            <div class="form-actions">
                <button type="submit" class="button"><?= $isEditing ? 'Guardar cambios' : 'Crear evento' ?></button>
                <?php if ($evento !== null): ?>
                    <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>">Cancelar edicion</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="eventos-panel">
        <div class="eventos-panel-head">
            <div>
                <p class="kicker">Eventos creados</p>
                <h2>Listado operativo</h2>
                <p>Desde aqui puedes abrir cada evento, editarlo o eliminarlo si aun no se utilizo.</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Nivel</th>
                        <th>Fechas</th>
                        <th>Inscritos</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventos)): ?>
                        <tr>
                            <td colspan="6">Aun no hay eventos federados registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($eventos as $eventoRow): ?>
                            <tr>
                                <td>
                                    <strong><?= e($eventoRow['nombre']) ?></strong>
                                    <?php if (!empty($eventoRow['lugar'])): ?>
                                        <div class="muted"><?= e($eventoRow['lugar']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= e($eventoRow['nivel']) ?>
                                    <div class="muted">Cubre todas las modalidades y subniveles de este nivel.</div>
                                </td>
                                <td>
                                    <div><?= e($eventoRow['fecha_inicio']) ?></div>
                                    <?php if (!empty($eventoRow['fecha_fin'])): ?>
                                        <div class="muted">Hasta <?= e($eventoRow['fecha_fin']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= e((string) $eventoRow['inscritos_count']) ?></strong>
                                    <div class="muted"><?= e($formatMoney($eventoRow['monto_total'] ?? 0)) ?></div>
                                </td>
                                <td>
                                    <span class="chip"><?= e($eventoEstadoLabels[$eventoRow['estado']] ?? $eventoRow['estado']) ?></span>
                                </td>
                                <td class="actions">
                                    <a class="link" href="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $eventoRow['id'])) ?>">Abrir</a>
                                    <a class="link" href="<?= e(base_url('/?page=eventos&action=edit&id=' . (int) $eventoRow['id'])) ?>">Editar</a>
                                    <form method="post" action="<?= e(base_url('/?page=eventos&action=delete')) ?>" onsubmit="return confirm('Eliminar este evento?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= e((string) $eventoRow['id']) ?>">
                                        <button type="submit" class="link danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($evento !== null): ?>
        <div class="eventos-detail">
            <div class="eventos-panel-head">
                <div>
                    <p class="kicker">Detalle</p>
                    <h2><?= e($selectedEventTitle !== '' ? $selectedEventTitle : 'Evento seleccionado') ?></h2>
                    <p><?= e((string) $evento['nivel']) ?> · Cubre todas las modalidades y subniveles de este nivel.</p>
                </div>
                <div class="eventos-detail-actions">
                    <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=edit&id=' . (int) $evento['id'])) ?>">Editar evento</a>
                    <a class="button ghost" href="#evento-form">Subir al formulario</a>
                </div>
            </div>

            <div class="grid eventos-detail-grid">
                <article class="card">
                    <p class="kicker">Resumen</p>
                    <ul class="eventos-meta">
                        <li><strong>Fechas:</strong> <?= e($evento['fecha_inicio']) ?><?= !empty($evento['fecha_fin']) ? ' a ' . e($evento['fecha_fin']) : '' ?></li>
                        <li><strong>Lugar:</strong> <?= e($evento['lugar'] ?? 'Sin lugar definido') ?></li>
                        <li><strong>Costo:</strong> <?= e($formatMoney($evento['costo_inscripcion'] ?? 0)) ?></li>
                        <li><strong>Cupo:</strong> <?= e(!empty($evento['cupo']) ? (string) $evento['cupo'] : 'Sin limite') ?></li>
                        <li><strong>Estado:</strong> <?= e($eventoEstadoLabels[$evento['estado']] ?? $evento['estado']) ?></li>
                    </ul>
                </article>

                <article class="card">
                    <p class="kicker">Inscripciones</p>
                    <h3><?= e((string) count($inscripciones)) ?> registradas</h3>
                    <p class="muted">Cada inscripcion guarda la asignacion competitiva exacta elegida para el evento.</p>
                </article>
            </div>

            <div class="eventos-columns">
                <article class="eventos-panel">
                    <div class="eventos-panel-head">
                        <div>
                            <p class="kicker">Deportistas elegibles</p>
                            <h3>Filtrados por nivel competitivo</h3>
                            <p>Elige en cada fila la modalidad y el subnivel exacto con el que competira el deportista en este evento.</p>
                        </div>
                    </div>

                    <?php if (empty($deportistas_elegibles)): ?>
                        <p class="muted">No hay deportistas elegibles para este evento.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Deportista</th>
                                        <th>Apoderado</th>
                                        <th>Modalidad</th>
                                        <th>Subnivel</th>
                                        <th>Categoria</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deportistas_elegibles as $deportista): ?>
                                        <?php
                                        $inscrito = (int) ($deportista['inscripcion_id'] ?? 0) > 0;
                                        $estadoPago = (string) ($deportista['estado_pago'] ?? 'pendiente');
                                        $formId = 'evento-inscripcion-' . (int) $deportista['deportista_id'] . '-' . (int) $deportista['asignacion_id'];
                                        $competenciaLabel = trim((string) ($deportista['modalidad_nombre'] ?? ''));
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($deportista['deportista_nombre']) ?></strong>
                                                <div class="muted"><?= e(format_rut($deportista['rut'] ?? '')) ?></div>
                                            </td>
                                            <td><?= e($deportista['apoderado_nombre'] ?? '') ?></td>
                                            <td><?= e($competenciaLabel !== '' ? $competenciaLabel : 'Sin modalidad') ?></td>
                                            <td><?= e((string) ($deportista['subnivel_competencia'] ?? '')) ?></td>
                                            <td><?= e((string) ($deportista['categoria_competencia'] ?? '')) ?></td>
                                            <td>
                                                <?php if ($inscrito): ?>
                                                    <span class="chip"><?= e($inscripcionEstadoLabels[$estadoPago] ?? $estadoPago) ?></span>
                                                    <div class="muted"><?= e($formatMoney($deportista['monto'] ?? 0)) ?></div>
                                                <?php else: ?>
                                                    <span class="chip muted">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="actions">
                                                <form id="<?= e($formId) ?>" method="post" action="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>">
                                                    <input type="hidden" name="action" value="inscribir">
                                                    <input type="hidden" name="evento_id" value="<?= e((string) $evento['id']) ?>">
                                                    <input type="hidden" name="deportista_id" value="<?= e((string) $deportista['deportista_id']) ?>">
                                                    <input type="hidden" name="deportista_modalidades_competencia_id" value="<?= e((string) $deportista['asignacion_id']) ?>">
                                                    <input type="hidden" name="fecha_inscripcion" value="<?= e($inscrito ? (string) ($deportista['fecha_inscripcion'] ?? date('Y-m-d')) : date('Y-m-d')) ?>">
                                                    <input type="hidden" name="monto" value="<?= e((string) ($inscrito ? ($deportista['monto'] ?? 0) : ($evento['costo_inscripcion'] ?? 0))) ?>">
                                                    <input type="hidden" name="estado_pago" value="<?= e($inscrito ? $estadoPago : 'pendiente') ?>">
                                                    <input type="hidden" name="referencia" value="<?= e((string) ($deportista['referencia'] ?? '')) ?>">
                                                    <input type="hidden" name="observaciones_inscripcion" value="<?= e((string) ($deportista['observaciones'] ?? '')) ?>">
                                                    <button type="submit" class="link"><?= $inscrito ? 'Actualizar' : 'Inscribir' ?></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </article>

                <article class="eventos-panel">
                    <div class="eventos-panel-head">
                        <div>
                            <p class="kicker">Inscritos</p>
                            <h3>Registro del evento</h3>
                            <p>Muestra el detalle guardado para cada deportista inscrito.</p>
                        </div>
                    </div>

                    <?php if (empty($inscripciones)): ?>
                        <p class="muted">Todavia no hay inscripciones para este evento.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Deportista</th>
                                        <th>Competencia</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inscripciones as $inscripcion): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($inscripcion['deportista_nombre']) ?></strong>
                                                <div class="muted"><?= e($inscripcion['apoderado_nombre']) ?></div>
                                            </td>
                                            <td>
                                                <div><?= e((string) ($inscripcion['modalidad_nombre'] ?? 'Sin modalidad')) ?></div>
                                                <div class="muted">
                                                    <?= e((string) ($inscripcion['subnivel'] ?? '')) ?>
                                                    <?= !empty($inscripcion['categoria']) ? ' · ' . e((string) $inscripcion['categoria']) : '' ?>
                                                </div>
                                            </td>
                                            <td><?= e($inscripcion['fecha_inscripcion']) ?></td>
                                            <td><?= e($formatMoney($inscripcion['monto'] ?? 0)) ?></td>
                                            <td><span class="chip"><?= e($inscripcionEstadoLabels[$inscripcion['estado_pago']] ?? $inscripcion['estado_pago']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </article>
            </div>
        </div>
    <?php endif; ?>
</section>
