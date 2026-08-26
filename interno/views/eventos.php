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
$eventoFormDefaults = [
    'id' => 0,
    'nombre' => '',
    'nivel' => '',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'lugar' => '',
    'costo_inscripcion' => '0.00',
    'cupo' => '',
    'estado' => 'borrador',
    'observaciones' => '',
];
$formEvento = array_merge(
    $eventoFormDefaults,
    ($isEditing || $action === 'create') ? $evento_form : []
);
$defaultTab = 'eventos-resumen';
if ($isEditing || $action === 'create') {
    $defaultTab = 'eventos-formulario';
} elseif ($action === 'show' && $evento !== null) {
    $defaultTab = 'eventos-detalle';
} elseif ($action === 'list') {
    $defaultTab = 'eventos-listado';
}
?>
<section class="page ficha-page eventos-page" data-ficha-default-tab="<?= e($defaultTab) ?>">
    <div class="ficha-header">
        <div class="ficha-header-copy">
            <p class="kicker">Eventos federados</p>
            <h1>Gestion de eventos</h1>
            <p>La vista sigue la misma estructura de ficha que deportistas: pestañas, tarjetas separadas y cambios sin salto de pantalla.</p>
        </div>
        <div class="ficha-header-actions">
            <?php if ($evento !== null): ?>
                <?php if ($isEditing): ?>
                    <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>">Cancelar edición</a>
                <?php else: ?>
                    <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=edit&id=' . (int) $evento['id'])) ?>">Editar evento</a>
                <?php endif; ?>
            <?php else: ?>
                <a class="button ghost" href="#eventos-formulario" data-ficha-tab data-ficha-target="eventos-formulario">Nuevo evento</a>
            <?php endif; ?>
            <button
                type="submit"
                form="evento-form"
                class="button"
                <?= $schema_ready ? '' : 'disabled' ?>
            >
                <?= $isEditing ? 'Guardar cambios' : 'Crear evento' ?>
            </button>
        </div>
    </div>

    <nav class="ficha-tabs" aria-label="Secciones de eventos" role="tablist">
        <a
            id="tab-eventos-resumen"
            class="ficha-tab<?= $defaultTab === 'eventos-resumen' ? ' is-active' : '' ?>"
            href="#eventos-resumen"
            role="tab"
            aria-selected="<?= $defaultTab === 'eventos-resumen' ? 'true' : 'false' ?>"
            aria-controls="eventos-resumen"
            data-ficha-tab
            data-ficha-target="eventos-resumen"
        >Resumen</a>
        <a
            id="tab-eventos-formulario"
            class="ficha-tab<?= $defaultTab === 'eventos-formulario' ? ' is-active' : '' ?>"
            href="#eventos-formulario"
            role="tab"
            aria-selected="<?= $defaultTab === 'eventos-formulario' ? 'true' : 'false' ?>"
            aria-controls="eventos-formulario"
            data-ficha-tab
            data-ficha-target="eventos-formulario"
        >Formulario</a>
        <a
            id="tab-eventos-listado"
            class="ficha-tab<?= $defaultTab === 'eventos-listado' ? ' is-active' : '' ?>"
            href="#eventos-listado"
            role="tab"
            aria-selected="<?= $defaultTab === 'eventos-listado' ? 'true' : 'false' ?>"
            aria-controls="eventos-listado"
            data-ficha-tab
            data-ficha-target="eventos-listado"
        >Listado</a>
        <a
            id="tab-eventos-detalle"
            class="ficha-tab<?= $defaultTab === 'eventos-detalle' ? ' is-active' : '' ?>"
            href="#eventos-detalle"
            role="tab"
            aria-selected="<?= $defaultTab === 'eventos-detalle' ? 'true' : 'false' ?>"
            aria-controls="eventos-detalle"
            data-ficha-tab
            data-ficha-target="eventos-detalle"
        >Detalle</a>
    </nav>

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

    <form id="evento-form" class="ficha-form form" method="post" action="<?= e(base_url('/?page=eventos&action=' . ($isEditing ? 'edit' : 'create'))) ?>">
        <?php if ($isEditing): ?>
            <input type="hidden" name="id" value="<?= e((string) $formEvento['id']) ?>">
        <?php endif; ?>

        <div class="ficha-layout">
            <div class="ficha-main">
                <section
                    id="eventos-resumen"
                    class="ficha-card ficha-panel"
                    role="tabpanel"
                    aria-labelledby="tab-eventos-resumen"
                    data-ficha-panel
                    <?= $defaultTab !== 'eventos-resumen' ? 'hidden' : '' ?>
                >
                    <div class="ficha-card-head ficha-card-head-split">
                        <div>
                            <p class="form-label">Resumen operativo</p>
                            <p class="hint">Vista rapida del modulo y sus cifras clave.</p>
                        </div>
                    </div>

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

                    <?php if ($evento !== null): ?>
                        <div class="ficha-card ficha-card-compact">
                            <div class="ficha-card-head ficha-card-head-split">
                                <div>
                                    <p class="form-label">Evento seleccionado</p>
                                    <p class="hint"><?= e($selectedEventTitle !== '' ? $selectedEventTitle : 'Sin nombre definido') ?></p>
                                </div>
                                <div class="eventos-detail-actions">
                                    <a class="button ghost" href="#eventos-detalle" data-ficha-tab data-ficha-target="eventos-detalle">Ver detalle</a>
                                    <a class="button ghost" href="#eventos-formulario" data-ficha-tab data-ficha-target="eventos-formulario">Abrir formulario</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="ficha-placeholder">Aun no hay un evento seleccionado. Usa el formulario para crear uno o abre uno desde el listado.</p>
                    <?php endif; ?>
                </section>

                <section
                    id="eventos-formulario"
                    class="ficha-card ficha-panel"
                    role="tabpanel"
                    aria-labelledby="tab-eventos-formulario"
                    data-ficha-panel
                    <?= $defaultTab !== 'eventos-formulario' ? 'hidden' : '' ?>
                >
                    <div class="ficha-card-head ficha-card-head-split">
                        <div>
                            <p class="form-label"><?= $isEditing ? 'Editar evento' : 'Crear evento' ?></p>
                            <p class="hint">Completa la informacion base del evento federado.</p>
                        </div>
                    </div>

                    <div class="ficha-grid ficha-grid-2 eventos-form-grid">
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-nombre">Nombre del evento</label>
                            <input id="evento-nombre" type="text" name="nombre" required value="<?= e($formEvento['nombre'] ?? '') ?>" placeholder="Open Club MaiTeam 2026">
                        </div>

                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-nivel">Nivel</label>
                            <select id="evento-nivel" name="nivel" required>
                                <option value="">Selecciona un nivel</option>
                                <?php foreach ($niveles_evento as $nivel): ?>
                                    <option value="<?= e((string) $nivel['nivel']) ?>" <?= (string) ($formEvento['nivel'] ?? '') === (string) $nivel['nivel'] ? 'selected' : '' ?>>
                                        <?= e((string) $nivel['nivel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="hint">Ejemplo: Promotional cubre todas las modalidades y subniveles marcados en ese nivel.</span>
                        </div>

                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-fecha-inicio">Fecha inicio</label>
                            <input id="evento-fecha-inicio" type="date" name="fecha_inicio" required value="<?= e($formEvento['fecha_inicio'] ?? '') ?>">
                        </div>

                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-fecha-fin">Fecha termino</label>
                            <input id="evento-fecha-fin" type="date" name="fecha_fin" value="<?= e($formEvento['fecha_fin'] ?? '') ?>">
                        </div>

                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-lugar">Lugar</label>
                            <input id="evento-lugar" type="text" name="lugar" value="<?= e($formEvento['lugar'] ?? '') ?>" placeholder="Pista central / ciudad / recinto">
                        </div>

                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-estado">Estado</label>
                            <select id="evento-estado" name="estado" required>
                                <?php foreach ($eventoEstadoLabels as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= (string) ($formEvento['estado'] ?? 'borrador') === $key ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-costo-inscripcion">Costo inscripcion</label>
                            <input id="evento-costo-inscripcion" type="number" name="costo_inscripcion" step="0.01" min="0" value="<?= e((string) ($formEvento['costo_inscripcion'] ?? '0.00')) ?>">
                        </div>

                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-cupo">Cupo</label>
                            <input id="evento-cupo" type="number" name="cupo" min="0" value="<?= e((string) ($formEvento['cupo'] ?? '')) ?>">
                        </div>

                        <div class="ficha-field ficha-field-wide">
                            <label class="ficha-field-label" for="evento-observaciones">Observaciones</label>
                            <textarea id="evento-observaciones" name="observaciones" rows="4" placeholder="Notas internas sobre el evento"><?= e($formEvento['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </section>

                <section
                    id="eventos-listado"
                    class="ficha-card ficha-panel"
                    role="tabpanel"
                    aria-labelledby="tab-eventos-listado"
                    data-ficha-panel
                    <?= $defaultTab !== 'eventos-listado' ? 'hidden' : '' ?>
                >
                    <div class="ficha-card-head ficha-card-head-split">
                        <div>
                            <p class="form-label">Listado operativo</p>
                            <p class="hint">Desde aqui puedes abrir cada evento, editarlo o eliminarlo si aun no se utilizo.</p>
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
                </section>

                <section
                    id="eventos-detalle"
                    class="ficha-card ficha-panel"
                    role="tabpanel"
                    aria-labelledby="tab-eventos-detalle"
                    data-ficha-panel
                    <?= $defaultTab !== 'eventos-detalle' ? 'hidden' : '' ?>
                >
                    <div class="ficha-card-head">
                        <div>
                            <p class="form-label">Detalle del evento</p>
                            <h2><?= e($selectedEventTitle !== '' ? $selectedEventTitle : 'Evento seleccionado') ?></h2>
                            <p class="hint"><?= $evento !== null ? e((string) $evento['nivel']) . ' · Cubre todas las modalidades y subniveles de este nivel.' : 'Abre un evento desde el listado para ver su detalle.' ?></p>
                        </div>
                        <div class="eventos-detail-actions">
                            <a class="button ghost" href="#eventos-formulario" data-ficha-tab data-ficha-target="eventos-formulario">Formulario</a>
                            <a class="button ghost" href="#eventos-listado" data-ficha-tab data-ficha-target="eventos-listado">Listado</a>
                        </div>
                    </div>

                    <?php if ($evento === null): ?>
                        <p class="ficha-placeholder">No hay un evento seleccionado todavia.</p>
                    <?php else: ?>
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
                            <article class="ficha-card ficha-card-compact">
                                <div class="ficha-card-head">
                                    <div>
                                        <p class="form-label">Deportistas elegibles</p>
                                        <p class="hint">Filtrados por nivel competitivo.</p>
                                    </div>
                                </div>

                                <?php if (empty($deportistas_elegibles)): ?>
                                    <p class="ficha-placeholder">No hay deportistas elegibles para este evento.</p>
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

                            <article class="ficha-card ficha-card-compact">
                                <div class="ficha-card-head">
                                    <div>
                                        <p class="form-label">Inscritos</p>
                                        <p class="hint">Registro guardado para cada deportista inscrito.</p>
                                    </div>
                                </div>

                                <?php if (empty($inscripciones)): ?>
                                    <p class="ficha-placeholder">Todavia no hay inscripciones para este evento.</p>
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
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </form>

    <script>
    (function () {
        const tabs = Array.from(document.querySelectorAll('[data-ficha-tab]'));
        const panels = Array.from(document.querySelectorAll('[data-ficha-panel]'));
        const root = document.querySelector('[data-ficha-default-tab]');
        const defaultTab = root ? root.getAttribute('data-ficha-default-tab') : (tabs[0] ? tabs[0].getAttribute('data-ficha-target') : null);

        if (!tabs.length || !panels.length || !defaultTab) {
            return;
        }

        function activateTab(targetId, updateHash) {
            tabs.forEach(function (tab) {
                const isActive = tab.getAttribute('data-ficha-target') === targetId;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                const isActive = panel.id === targetId;
                panel.hidden = !isActive;
            });

            if (updateHash) {
                history.replaceState(null, '', '#' + targetId);
            }
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function (event) {
                const targetId = tab.getAttribute('data-ficha-target');
                if (!targetId) {
                    return;
                }

                event.preventDefault();
                activateTab(targetId, true);
            });
        });

        const hashTarget = window.location.hash ? window.location.hash.replace('#', '') : '';
        const initialTarget = tabs.some(function (tab) {
            return tab.getAttribute('data-ficha-target') === hashTarget;
        }) ? hashTarget : defaultTab;

        activateTab(initialTarget, false);
    })();
    </script>
</section>
