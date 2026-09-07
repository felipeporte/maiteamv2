<?php
/** @var array $eventos */
/** @var array|null $evento */
/** @var array $evento_form */
/** @var array $inscripciones */
/** @var array $deportistas_elegibles */
/** @var array $hojas_elementos */
/** @var bool $hojas_schema_ready */
/** @var array|null $hoja_context */
/** @var array $hoja_form */
/** @var int $hoja_selected_inscripcion_id */
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

$totalHojas = 0;
foreach ($hojas_elementos as $hojaRow) {
    if ((int) ($hojaRow['hoja_id'] ?? 0) > 0) {
        $totalHojas++;
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
} elseif ($action === 'hoja' && $evento !== null) {
    $defaultTab = 'eventos-hojas';
} elseif ($action === 'list') {
    $defaultTab = 'eventos-listado';
}

if (in_array($action, ['list', 'create'], true)): ?>
    <?php $showCreateModal = $action === 'create'; ?>
    <section class="page ficha-page eventos-page">
        <div class="ficha-header">
            <div class="ficha-header-copy">
                <p class="kicker">Eventos federados</p>
                <h1>Resumen de eventos</h1>
                <p>Gestiona los eventos creados. Usa ver para abrir el menu del evento y eliminar solo los borradores que ya no necesitas.</p>
            </div>
            <div class="ficha-header-actions">
                <button
                    type="button"
                    class="button"
                    data-eventos-open-create
                >Nuevo evento</button>
            </div>
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
        <?php elseif ($flash === 'sheet-updated'): ?>
            <div class="alert success">Hoja de elementos guardada correctamente.</div>
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
            <?php if ($hojas_schema_ready): ?>
                <article class="card eventos-metric">
                    <p class="kicker">Hojas</p>
                    <h2><?= e((string) $totalHojas) ?></h2>
                    <p>Borradores guardados para imprimir o enviar.</p>
                </article>
            <?php endif; ?>
        </div>

        <?php if (empty($eventos)): ?>
            <p class="ficha-placeholder">Aun no hay eventos federados registrados.</p>
        <?php else: ?>
            <div class="eventos-summary-cards">
                <?php foreach ($eventos as $eventoRow): ?>
                    <article class="eventos-summary-card">
                        <div class="eventos-summary-card-top">
                            <div>
                                <p class="kicker">Evento</p>
                                <h2><?= e((string) $eventoRow['nombre']) ?></h2>
                                <?php if (!empty($eventoRow['lugar'])): ?>
                                    <p class="muted"><?= e((string) $eventoRow['lugar']) ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="chip"><?= e($eventoEstadoLabels[$eventoRow['estado']] ?? $eventoRow['estado']) ?></span>
                        </div>

                        <div class="eventos-summary-meta">
                            <span><strong>Nivel:</strong> <?= e((string) $eventoRow['nivel']) ?></span>
                            <span><strong>Fechas:</strong> <?= e((string) $eventoRow['fecha_inicio']) ?><?= !empty($eventoRow['fecha_fin']) ? ' a ' . e((string) $eventoRow['fecha_fin']) : '' ?></span>
                            <span><strong>Inscritos:</strong> <?= e((string) $eventoRow['inscritos_count']) ?></span>
                            <span><strong>Monto:</strong> <?= e($formatMoney($eventoRow['monto_total'] ?? 0)) ?></span>
                        </div>

                        <div class="eventos-summary-actions">
                            <a class="button icon" href="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $eventoRow['id'])) ?>" title="Ver evento" aria-label="Ver evento">
                                <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                            </a>
                            <form method="post" action="<?= e(base_url('/?page=eventos&action=delete')) ?>" onsubmit="return confirm('Eliminar este evento?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e((string) $eventoRow['id']) ?>">
                                <button type="submit" class="button icon danger" title="Eliminar evento" aria-label="Eliminar evento">
                                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="modal-overlay" data-eventos-create-modal <?= $showCreateModal ? '' : 'hidden' ?>>
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="nuevo-evento-title">
            <div class="modal-card-head">
                <div>
                    <p class="kicker">Eventos federados</p>
                    <h2 id="nuevo-evento-title">Nuevo evento</h2>
                    <p class="hint">Completa la informacion base del evento antes de abrirlo en el menu de gestion.</p>
                </div>
                <button type="button" class="button icon ghost" data-eventos-close-create aria-label="Cerrar modal">
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
            </div>

            <?php if (!$schema_ready): ?>
                <div class="alert danger">
                    Falta aplicar la migracion 008 de eventos federados en esta base de datos.
                </div>
            <?php endif; ?>

            <form id="evento-form" class="form" method="post" action="<?= e(base_url('/?page=eventos&action=create')) ?>">
                <input type="hidden" name="action" value="create">
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

                <div class="form-actions modal-actions">
                    <button type="button" class="button ghost" data-eventos-close-create>Cancelar</button>
                    <button type="submit" class="button" <?= $schema_ready ? '' : 'disabled' ?>>Crear evento</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const modal = document.querySelector('[data-eventos-create-modal]');
        const openButton = document.querySelector('[data-eventos-open-create]');
        const closeButtons = Array.from(document.querySelectorAll('[data-eventos-close-create]'));

        if (!modal || !openButton) {
            return;
        }

        function openModal() {
            modal.hidden = false;
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        }

        openButton.addEventListener('click', openModal);
        closeButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        if (<?= $showCreateModal ? 'true' : 'false' ?>) {
            openModal();
        }
    })();
    </script>
</section>
<?php return; ?>
<?php endif; ?>

<?php if (in_array($action, ['show', 'edit'], true) && $evento !== null): ?>
    <section class="page ficha-page eventos-page">
        <div class="ficha-header">
            <div class="ficha-header-copy">
                <p class="kicker">Eventos federados</p>
                <h1><?= e($selectedEventTitle !== '' ? $selectedEventTitle : 'Gestión de evento') ?></h1>
                <p>Vista de gestion del evento sin pestañas. Cada bloque queda visible para que sea más rapido revisar inscripciones y hojas.</p>
            </div>
            <div class="ficha-header-actions">
                <a class="button ghost" href="<?= e(base_url('/?page=eventos')) ?>">Volver al resumen</a>
                <?php if ($isEditing): ?>
                    <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>">Cancelar edición</a>
                <?php else: ?>
                    <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=edit&id=' . (int) $evento['id'])) ?>">Editar evento</a>
                <?php endif; ?>
            </div>
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
        <?php elseif ($flash === 'sheet-updated'): ?>
            <div class="alert success">Hoja de elementos guardada correctamente.</div>
        <?php endif; ?>

        <?php if (!$schema_ready): ?>
            <div class="alert danger">
                Falta aplicar la migracion 008 de eventos federados en esta base de datos.
            </div>
        <?php endif; ?>

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
                <p class="kicker">Acciones rápidas</p>
                <div class="eventos-summary-actions eventos-management-actions">
                    <a class="button" href="<?= e(base_url('/?page=eventos&action=edit&id=' . (int) $evento['id'])) ?>">Editar evento</a>
                    <?php if ($hojas_schema_ready): ?>
                        <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=hoja&id=' . (int) $evento['id'])) ?>">Hojas de elementos</a>
                    <?php endif; ?>
                </div>
                <p class="muted">La gestión ya no está dividida en pestañas. Las secciones aparecen una debajo de otra.</p>
            </article>
        </div>

        <?php if ($isEditing): ?>
            <article class="ficha-card">
                <div class="ficha-card-head">
                    <div>
                        <p class="form-label">Editar evento</p>
                        <p class="hint">Ajusta la informacion base del evento y guarda los cambios.</p>
                    </div>
                </div>
                <form id="evento-form" class="form" method="post" action="<?= e(base_url('/?page=eventos&action=edit')) ?>">
                    <input type="hidden" name="id" value="<?= e((string) $evento['id']) ?>">
                    <div class="ficha-grid ficha-grid-2 eventos-form-grid">
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-nombre-edit">Nombre del evento</label>
                            <input id="evento-nombre-edit" type="text" name="nombre" required value="<?= e($formEvento['nombre'] ?? '') ?>">
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-nivel-edit">Nivel</label>
                            <select id="evento-nivel-edit" name="nivel" required>
                                <option value="">Selecciona un nivel</option>
                                <?php foreach ($niveles_evento as $nivel): ?>
                                    <option value="<?= e((string) $nivel['nivel']) ?>" <?= (string) ($formEvento['nivel'] ?? '') === (string) $nivel['nivel'] ? 'selected' : '' ?>>
                                        <?= e((string) $nivel['nivel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-fecha-inicio-edit">Fecha inicio</label>
                            <input id="evento-fecha-inicio-edit" type="date" name="fecha_inicio" required value="<?= e($formEvento['fecha_inicio'] ?? '') ?>">
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-fecha-fin-edit">Fecha termino</label>
                            <input id="evento-fecha-fin-edit" type="date" name="fecha_fin" value="<?= e($formEvento['fecha_fin'] ?? '') ?>">
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-lugar-edit">Lugar</label>
                            <input id="evento-lugar-edit" type="text" name="lugar" value="<?= e($formEvento['lugar'] ?? '') ?>">
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-estado-edit">Estado</label>
                            <select id="evento-estado-edit" name="estado" required>
                                <?php foreach ($eventoEstadoLabels as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= (string) ($formEvento['estado'] ?? 'borrador') === $key ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-costo-inscripcion-edit">Costo inscripcion</label>
                            <input id="evento-costo-inscripcion-edit" type="number" name="costo_inscripcion" step="0.01" min="0" value="<?= e((string) ($formEvento['costo_inscripcion'] ?? '0.00')) ?>">
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-cupo-edit">Cupo</label>
                            <input id="evento-cupo-edit" type="number" name="cupo" min="0" value="<?= e((string) ($formEvento['cupo'] ?? '')) ?>">
                        </div>
                        <div class="ficha-field ficha-field-wide">
                            <label class="ficha-field-label" for="evento-observaciones-edit">Observaciones</label>
                            <textarea id="evento-observaciones-edit" name="observaciones" rows="4"><?= e($formEvento['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>">Cancelar</a>
                        <button type="submit" class="button">Guardar cambios</button>
                    </div>
                </form>
            </article>
        <?php endif; ?>

        <div class="grid eventos-columns">
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
                                            <?php if ($inscrito && $hojas_schema_ready): ?>
                                                <a class="link" href="<?= e(base_url('/?page=eventos&action=hoja&id=' . (int) $evento['id'] . '&inscripcion_id=' . (int) ($deportista['inscripcion_id'] ?? 0))) ?>">Hoja</a>
                                            <?php else: ?>
                                                <span class="muted"><?= $inscrito ? 'Falta la migracion de hojas' : 'Inscribe para generar la hoja' ?></span>
                                            <?php endif; ?>
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

        <?php if ($hojas_schema_ready): ?>
            <article class="ficha-card ficha-card-compact">
                <div class="ficha-card-head ficha-card-head-split">
                    <div>
                        <p class="form-label">Hojas de elementos</p>
                        <p class="hint">Las hojas se crean por inscripcion. La edicion detallada sigue disponible desde el modulo de hojas.</p>
                    </div>
                    <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=hoja&id=' . (int) $evento['id'])) ?>">Abrir hojas</a>
                </div>

                <?php if (empty($hojas_elementos)): ?>
                    <p class="ficha-placeholder">Aun no hay hojas guardadas para este evento.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Deportista</th>
                                    <th>Plantilla</th>
                                    <th>Actualizada</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hojas_elementos as $sheetRow): ?>
                                    <?php if ((int) ($sheetRow['hoja_id'] ?? 0) <= 0) { continue; } ?>
                                    <tr>
                                        <td>
                                            <strong><?= e((string) ($sheetRow['deportista_nombre'] ?? '')) ?></strong>
                                            <div class="muted"><?= e((string) ($sheetRow['modalidad_nombre'] ?? '')) ?></div>
                                        </td>
                                        <td><?= e(evento_federado_hoja_template_label((string) ($sheetRow['plantilla_codigo'] ?? ''))) ?></td>
                                        <td><?= e((string) ($sheetRow['hoja_updated_at'] ?? '')) ?></td>
                                        <td>
                                            <a class="link" href="<?= e(base_url('/?page=eventos&action=hoja&id=' . (int) $evento['id'] . '&inscripcion_id=' . (int) ($sheetRow['inscripcion_id'] ?? 0))) ?>">Editar</a>
                                            <a class="link" href="<?= e(base_url('/?page=eventos&action=hoja&id=' . (int) $evento['id'] . '&inscripcion_id=' . (int) ($sheetRow['inscripcion_id'] ?? 0))) ?>">Hoja</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>
        <?php endif; ?>
</section>
<?php return; ?>
<?php endif; ?>

<?php if ($action === 'hoja' && $evento !== null): ?>
    <?php require __DIR__ . '/eventos_hojas.php'; ?>
    <?php return; ?>
<?php endif; ?>
