<?php
/** @var array $eventos */
/** @var array|null $evento */
/** @var array $evento_form */
/** @var array $inscripciones */
/** @var array $cobros */
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
    'costo_inscripcion' => '38000.00',
    'tarifa_una_modalidad' => '38000.00',
    'tarifa_dos_modalidades' => '53000.00',
    'tarifa_acompanamiento_pista' => '40000.00',
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
        <?php elseif ($flash === 'accompaniment-created'): ?>
            <div class="alert success">Acompanamiento agregado como cobro separado.</div>
        <?php elseif ($flash === 'payment-updated'): ?>
            <div class="alert success">Estado del cobro actualizado.</div>
        <?php elseif ($flash === 'sheet-updated'): ?>
            <div class="alert success">Hoja de elementos guardada correctamente.</div>
        <?php endif; ?>

        <?php if (!$schema_ready): ?>
            <div class="alert danger">
                Faltan aplicar las migraciones de eventos federados en esta base de datos.
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
                            <button
                                type="button"
                                class="button icon"
                                data-eventos-open-edit
                                data-evento-id="<?= e((string) $eventoRow['id']) ?>"
                                data-evento-nombre="<?= e((string) $eventoRow['nombre']) ?>"
                                data-evento-nivel="<?= e((string) $eventoRow['nivel']) ?>"
                                data-evento-fecha-inicio="<?= e((string) $eventoRow['fecha_inicio']) ?>"
                                data-evento-fecha-fin="<?= e((string) ($eventoRow['fecha_fin'] ?? '')) ?>"
                                data-evento-lugar="<?= e((string) ($eventoRow['lugar'] ?? '')) ?>"
                                data-evento-estado="<?= e((string) ($eventoRow['estado'] ?? 'borrador')) ?>"
                                data-evento-tarifa-una="<?= e((string) ($eventoRow['tarifa_una_modalidad'] ?? $eventoRow['costo_inscripcion'] ?? '0.00')) ?>"
                                data-evento-tarifa-dos="<?= e((string) ($eventoRow['tarifa_dos_modalidades'] ?? '0.00')) ?>"
                                data-evento-tarifa-acompanamiento="<?= e((string) ($eventoRow['tarifa_acompanamiento_pista'] ?? '40000.00')) ?>"
                                data-evento-cupo="<?= e((string) ($eventoRow['cupo'] ?? '')) ?>"
                                data-evento-observaciones="<?= e((string) ($eventoRow['observaciones'] ?? '')) ?>"
                                title="Editar evento"
                                aria-label="Editar evento"
                            >
                                <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                            </button>
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
                    Faltan aplicar las migraciones de eventos federados en esta base de datos.
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
                        <label class="ficha-field-label" for="evento-tarifa-una">1 modalidad</label>
                        <input id="evento-tarifa-una" type="number" name="tarifa_una_modalidad" step="0.01" min="0" value="<?= e((string) ($formEvento['tarifa_una_modalidad'] ?? $formEvento['costo_inscripcion'] ?? '38000')) ?>">
                    </div>

                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-tarifa-dos">2 modalidades</label>
                        <input id="evento-tarifa-dos" type="number" name="tarifa_dos_modalidades" step="0.01" min="0" value="<?= e((string) ($formEvento['tarifa_dos_modalidades'] ?? '53000')) ?>">
                    </div>

                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-tarifa-acompanamiento">Acompanamiento en pista</label>
                        <input id="evento-tarifa-acompanamiento" type="number" name="tarifa_acompanamiento_pista" step="0.01" min="0" value="<?= e((string) ($formEvento['tarifa_acompanamiento_pista'] ?? '40000')) ?>">
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

    <div class="modal-overlay" data-eventos-edit-modal hidden>
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editar-evento-title">
            <div class="modal-card-head">
                <div>
                    <p class="kicker">Eventos federados</p>
                    <h2 id="editar-evento-title">Editar evento</h2>
                    <p class="hint">Actualiza los datos principales del evento.</p>
                </div>
                <button type="button" class="button icon ghost" data-eventos-close-edit aria-label="Cerrar modal">
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
            </div>

            <form id="evento-edit-form" class="form" method="post" action="<?= e(base_url('/?page=eventos&action=edit')) ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" data-evento-edit-id>
                <div class="ficha-grid ficha-grid-2 eventos-form-grid">
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-nombre">Nombre del evento</label>
                        <input id="evento-modal-nombre" type="text" name="nombre" required data-evento-edit-field="nombre">
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-nivel">Nivel</label>
                        <select id="evento-modal-nivel" name="nivel" required data-evento-edit-field="nivel">
                            <option value="">Selecciona un nivel</option>
                            <?php foreach ($niveles_evento as $nivel): ?>
                                <option value="<?= e((string) $nivel['nivel']) ?>"><?= e((string) $nivel['nivel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-fecha-inicio">Fecha inicio</label>
                        <input id="evento-modal-fecha-inicio" type="date" name="fecha_inicio" required data-evento-edit-field="fecha_inicio">
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-fecha-fin">Fecha termino</label>
                        <input id="evento-modal-fecha-fin" type="date" name="fecha_fin" data-evento-edit-field="fecha_fin">
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-lugar">Lugar</label>
                        <input id="evento-modal-lugar" type="text" name="lugar" data-evento-edit-field="lugar">
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-estado">Estado</label>
                        <select id="evento-modal-estado" name="estado" required data-evento-edit-field="estado">
                            <?php foreach ($eventoEstadoLabels as $key => $label): ?>
                                <option value="<?= e($key) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-tarifa-una">1 modalidad</label>
                        <input id="evento-modal-tarifa-una" type="number" name="tarifa_una_modalidad" step="0.01" min="0" data-evento-edit-field="tarifa_una">
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-tarifa-dos">2 modalidades</label>
                        <input id="evento-modal-tarifa-dos" type="number" name="tarifa_dos_modalidades" step="0.01" min="0" data-evento-edit-field="tarifa_dos">
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-tarifa-acompanamiento">Acompanamiento en pista</label>
                        <input id="evento-modal-tarifa-acompanamiento" type="number" name="tarifa_acompanamiento_pista" step="0.01" min="0" data-evento-edit-field="tarifa_acompanamiento">
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label" for="evento-modal-cupo">Cupo</label>
                        <input id="evento-modal-cupo" type="number" name="cupo" min="0" data-evento-edit-field="cupo">
                    </div>
                    <div class="ficha-field ficha-field-wide">
                        <label class="ficha-field-label" for="evento-modal-observaciones">Observaciones</label>
                        <textarea id="evento-modal-observaciones" name="observaciones" rows="4" data-evento-edit-field="observaciones"></textarea>
                    </div>
                </div>
                <div class="form-actions modal-actions">
                    <button type="button" class="button ghost" data-eventos-close-edit>Cancelar</button>
                    <button type="submit" class="button" <?= $schema_ready ? '' : 'disabled' ?>>Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const modal = document.querySelector('[data-eventos-create-modal]');
        const openButton = document.querySelector('[data-eventos-open-create]');
        const closeButtons = Array.from(document.querySelectorAll('[data-eventos-close-create]'));
        const editModal = document.querySelector('[data-eventos-edit-modal]');
        const editButtons = Array.from(document.querySelectorAll('[data-eventos-open-edit]'));
        const editCloseButtons = Array.from(document.querySelectorAll('[data-eventos-close-edit]'));
        const editForm = document.querySelector('#evento-edit-form');

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

        function openEditModal(button) {
            if (!editModal || !editForm) {
                return;
            }

            editForm.querySelector('[data-evento-edit-id]').value = button.dataset.eventoId || '';
            Object.entries({
                nombre: button.dataset.eventoNombre,
                nivel: button.dataset.eventoNivel,
                fecha_inicio: button.dataset.eventoFechaInicio,
                fecha_fin: button.dataset.eventoFechaFin,
                lugar: button.dataset.eventoLugar,
                estado: button.dataset.eventoEstado,
                tarifa_una: button.dataset.eventoTarifaUna,
                tarifa_dos: button.dataset.eventoTarifaDos,
                tarifa_acompanamiento: button.dataset.eventoTarifaAcompanamiento,
                cupo: button.dataset.eventoCupo,
                observaciones: button.dataset.eventoObservaciones,
            }).forEach(function ([field, value]) {
                const input = editForm.querySelector('[data-evento-edit-field="' + field + '"]');
                if (input) {
                    input.value = value || '';
                }
            });

            editModal.hidden = false;
            document.body.classList.add('modal-open');
        }

        function closeEditModal() {
            if (editModal) {
                editModal.hidden = true;
            }
            if (!modal || modal.hidden) {
                document.body.classList.remove('modal-open');
            }
        }

        openButton.addEventListener('click', openModal);
        closeButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });
        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                openEditModal(button);
            });
        });
        editCloseButtons.forEach(function (button) {
            button.addEventListener('click', closeEditModal);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        if (editModal) {
            editModal.addEventListener('click', function (event) {
                if (event.target === editModal) {
                    closeEditModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && editModal && !editModal.hidden) {
                closeEditModal();
            } else if (event.key === 'Escape' && !modal.hidden) {
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
                Faltan aplicar las migraciones de eventos federados en esta base de datos.
            </div>
        <?php endif; ?>

        <div class="eventos-detail-grid">
            <article class="card eventos-detail-summary">
                <p class="kicker">Resumen</p>
                <div class="eventos-meta eventos-meta-chips">
                    <span><strong>Fechas:</strong> <?= e($evento['fecha_inicio']) ?><?= !empty($evento['fecha_fin']) ? ' a ' . e($evento['fecha_fin']) : '' ?></span>
                    <span><strong>Lugar:</strong> <?= e($evento['lugar'] ?? 'Sin lugar definido') ?></span>
                    <span><strong>Inscripcion:</strong> <?= e($formatMoney($evento['tarifa_una_modalidad'] ?? $evento['costo_inscripcion'] ?? 0)) ?> / <?= e($formatMoney($evento['tarifa_dos_modalidades'] ?? 0)) ?></span>
                    <span><strong>Pista:</strong> <?= e($formatMoney($evento['tarifa_acompanamiento_pista'] ?? 40000)) ?></span>
                    <span><strong>Cupo:</strong> <?= e(!empty($evento['cupo']) ? (string) $evento['cupo'] : 'Sin limite') ?></span>
                    <span><strong>Estado:</strong> <?= e($eventoEstadoLabels[$evento['estado']] ?? $evento['estado']) ?></span>
                </div>
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
                    <input type="hidden" name="action" value="edit">
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
                            <label class="ficha-field-label" for="evento-tarifa-una-edit">1 modalidad</label>
                            <input id="evento-tarifa-una-edit" type="number" name="tarifa_una_modalidad" step="0.01" min="0" value="<?= e((string) ($formEvento['tarifa_una_modalidad'] ?? $formEvento['costo_inscripcion'] ?? '38000')) ?>">
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-tarifa-dos-edit">2 modalidades</label>
                            <input id="evento-tarifa-dos-edit" type="number" name="tarifa_dos_modalidades" step="0.01" min="0" value="<?= e((string) ($formEvento['tarifa_dos_modalidades'] ?? '53000')) ?>">
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="evento-tarifa-acompanamiento-edit">Acompanamiento en pista</label>
                            <input id="evento-tarifa-acompanamiento-edit" type="number" name="tarifa_acompanamiento_pista" step="0.01" min="0" value="<?= e((string) ($formEvento['tarifa_acompanamiento_pista'] ?? '40000')) ?>">
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

        <?php
        $inscritosDeportistaIds = [];
        foreach ($inscripciones as $inscripcion) {
            if (($inscripcion['estado_pago'] ?? '') !== 'anulado') {
                $inscritosDeportistaIds[(string) ($inscripcion['deportista_id'] ?? '')] = true;
            }
        }
        ?>

        <div class="eventos-inscripciones-section">
            <article class="ficha-card ficha-card-compact eventos-inscripciones-card">
                <div class="ficha-card-head ficha-card-head-split">
                    <div>
                        <p class="form-label">Deportistas del evento</p>
                        <p class="hint">Filtrados por nivel competitivo.</p>
                    </div>
                    <button type="button" class="link eventos-detail-link" data-eventos-open-inscripciones>
                        Detalle <span class="chip"><?= e((string) count($inscritosDeportistaIds)) ?> inscritos</span>
                    </button>
                </div>

                <?php if (empty($deportistas_elegibles)): ?>
                    <p class="ficha-placeholder">No hay deportistas elegibles para este evento.</p>
                <?php else: ?>
                    <?php
                    $modalidadesPorDeportista = [];
                    foreach ($deportistas_elegibles as $eligibleRow) {
                        $eligibleDeportistaId = (int) ($eligibleRow['deportista_id'] ?? 0);
                        $modalidadesPorDeportista[$eligibleDeportistaId] = ($modalidadesPorDeportista[$eligibleDeportistaId] ?? 0) + 1;
                    }
                    ?>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deportistas_elegibles as $deportista): ?>
                                    <?php
                                    $inscrito = (int) ($deportista['inscripcion_id'] ?? 0) > 0;
                                    $estadoPago = (string) ($deportista['estado_pago'] ?? 'pendiente');
                                    $formId = 'evento-inscripcion-' . (int) $deportista['deportista_id'] . '-' . (int) $deportista['asignacion_id'];
                                    $competenciaLabel = trim((string) ($deportista['modalidad_nombre'] ?? ''));
                                    $programasDisponibles = evento_federado_hoja_programas($deportista);
                                    $programaLabel = implode(' + ', array_map(
                                        static fn (array $programa): string => (string) ($programa['label'] ?? ''),
                                        $programasDisponibles
                                    ));
                                    $modalidadesDeportista = (int) ($modalidadesPorDeportista[(int) $deportista['deportista_id']] ?? 1);
                                    $montoModalidad = $inscrito
                                        ? (float) ($deportista['monto'] ?? 0)
                                        : evento_federado_monto_por_modalidad($evento, $modalidadesDeportista);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="evento-deportista-line">
                                                <?php if ($inscrito): ?>
                                                    <span class="material-symbols-outlined evento-inscripcion-status is-registered" aria-hidden="true">check_circle</span>
                                                <?php else: ?>
                                                    <form id="<?= e($formId) ?>" class="evento-inscripcion-form" method="post" action="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>">
                                                        <input type="hidden" name="action" value="inscribir">
                                                        <input type="hidden" name="evento_id" value="<?= e((string) $evento['id']) ?>">
                                                        <input type="hidden" name="deportista_id" value="<?= e((string) $deportista['deportista_id']) ?>">
                                                        <input type="hidden" name="deportista_modalidades_competencia_id" value="<?= e((string) $deportista['asignacion_id']) ?>">
                                                        <input type="hidden" name="fecha_inscripcion" value="<?= e(date('Y-m-d')) ?>">
                                                        <input type="hidden" name="monto" value="<?= e((string) $montoModalidad) ?>">
                                                        <input type="hidden" name="estado_pago" value="pendiente">
                                                        <input type="hidden" name="referencia" value="">
                                                        <input type="hidden" name="observaciones_inscripcion" value="">
                                                        <button type="submit" class="button icon ghost evento-inscripcion-action" title="Inscribir deportista" aria-label="Inscribir deportista">
                                                            <span class="material-symbols-outlined" aria-hidden="true">hourglass_empty</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= e($deportista['deportista_nombre']) ?></strong>
                                                    <div class="muted"><?= e(format_rut($deportista['rut'] ?? '')) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= e($deportista['apoderado_nombre'] ?? '') ?></td>
                                        <td>
                                            <?= e($competenciaLabel !== '' ? $competenciaLabel : 'Sin modalidad') ?>
                                            <?php if ($programaLabel !== ''): ?>
                                                <div class="muted"><?= e($programaLabel) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e((string) ($deportista['subnivel_competencia'] ?? '')) ?></td>
                                        <td><?= e((string) ($deportista['categoria_competencia'] ?? '')) ?></td>
                                        <td>
                                            <?php if ($inscrito): ?>
                                                <span class="chip"><?= e($inscripcionEstadoLabels[$estadoPago] ?? $estadoPago) ?></span>
                                                <div class="muted"><?= e($formatMoney($montoModalidad)) ?></div>
                                            <?php else: ?>
                                                <span class="chip muted">Pendiente</span>
                                                <div class="muted"><?= e($formatMoney($montoModalidad)) ?> por modalidad</div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>
        </div>

        <?php
        $cobrosPorDeportistaTipo = [];
        foreach ($cobros as $cobro) {
            $cobrosPorDeportistaTipo[(string) ($cobro['deportista_id'] ?? '')][(string) ($cobro['tipo_cobro'] ?? '')] = $cobro;
        }
        $inscripcionesAgrupadas = [];
        foreach ($inscripciones as $inscripcion) {
            if (($inscripcion['estado_pago'] ?? '') === 'anulado') {
                continue;
            }

            $grupoKey = (string) ($inscripcion['deportista_id'] ?? '');
            if (!isset($inscripcionesAgrupadas[$grupoKey])) {
                $inscripcionesAgrupadas[$grupoKey] = [
                    'deportista_id' => (int) ($inscripcion['deportista_id'] ?? 0),
                    'deportista_nombre' => (string) ($inscripcion['deportista_nombre'] ?? ''),
                    'apoderado_nombre' => (string) ($inscripcion['apoderado_nombre'] ?? ''),
                    'modalidades' => [],
                    'monto_total' => 0.00,
                    'estados' => [],
                ];
            }

            $inscripcionesAgrupadas[$grupoKey]['modalidades'][] = (string) ($inscripcion['modalidad_nombre'] ?? 'Sin modalidad');
            $inscripcionesAgrupadas[$grupoKey]['monto_total'] += (float) ($inscripcion['monto'] ?? 0);
            $inscripcionesAgrupadas[$grupoKey]['estados'][] = (string) ($inscripcion['estado_pago'] ?? 'pendiente');
        }
        ?>

        <div class="modal-overlay" data-eventos-inscripciones-modal hidden>
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="detalle-inscripciones-title">
                <div class="modal-card-head">
                    <div>
                        <p class="kicker">Eventos federados</p>
                        <h2 id="detalle-inscripciones-title">Detalle de inscritos</h2>
                        <p class="hint">Registro completo de las deportistas inscritas en este evento.</p>
                    </div>
                    <button type="button" class="button icon ghost" data-eventos-close-inscripciones aria-label="Cerrar detalle">
                        <span class="material-symbols-outlined" aria-hidden="true">close</span>
                    </button>
                </div>

                <?php if (empty($inscripcionesAgrupadas)): ?>
                    <p class="ficha-placeholder">Todavia no hay inscripciones para este evento.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Deportista</th>
                                    <th>Modalidades</th>
                                    <th>Inscripcion</th>
                                    <th>Acompanamiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscripcionesAgrupadas as $inscripcionAgrupada): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($inscripcionAgrupada['deportista_nombre']) ?></strong>
                                            <div class="muted"><?= e($inscripcionAgrupada['apoderado_nombre']) ?></div>
                                        </td>
                                        <td>
                                            <?= e(implode(' + ', $inscripcionAgrupada['modalidades'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $deportistaIdDetalle = (int) $inscripcionAgrupada['deportista_id'];
                                            $cobroInscripcion = $cobrosPorDeportistaTipo[(string) $deportistaIdDetalle]['inscripcion'] ?? null;
                                            $estadoInscripcion = (string) ($cobroInscripcion['estado_pago'] ?? 'pendiente');
                                            ?>
                                            <strong><?= e($formatMoney($cobroInscripcion['monto'] ?? $inscripcionAgrupada['monto_total'])) ?></strong>
                                            <span class="chip"><?= e($inscripcionEstadoLabels[$estadoInscripcion] ?? $estadoInscripcion) ?></span>
                                            <?php if ($cobroInscripcion !== null && $estadoInscripcion === 'pagado'): ?>
                                                <form method="post" action="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>" class="inline-form">
                                                    <input type="hidden" name="action" value="cobro-pendiente">
                                                    <input type="hidden" name="evento_id" value="<?= e((string) $evento['id']) ?>">
                                                    <input type="hidden" name="deportista_id" value="<?= e((string) $deportistaIdDetalle) ?>">
                                                    <input type="hidden" name="tipo_cobro" value="inscripcion">
                                                    <button type="submit" class="link">Revertir</button>
                                                </form>
                                            <?php elseif ($cobroInscripcion !== null): ?>
                                                <form method="post" action="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>" class="inline-form">
                                                    <input type="hidden" name="action" value="cobro-pagado">
                                                    <input type="hidden" name="evento_id" value="<?= e((string) $evento['id']) ?>">
                                                    <input type="hidden" name="deportista_id" value="<?= e((string) $deportistaIdDetalle) ?>">
                                                    <input type="hidden" name="tipo_cobro" value="inscripcion">
                                                    <input type="hidden" name="metodo_pago" value="transferencia">
                                                    <button type="submit" class="link">Marcar pagada</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $cobroAcompanamiento = $cobrosPorDeportistaTipo[(string) $deportistaIdDetalle]['acompanamiento'] ?? null; ?>
                                            <?php if ($cobroAcompanamiento === null): ?>
                                                <form method="post" action="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>">
                                                    <input type="hidden" name="action" value="cobro-acompanamiento-create">
                                                    <input type="hidden" name="evento_id" value="<?= e((string) $evento['id']) ?>">
                                                    <input type="hidden" name="deportista_id" value="<?= e((string) $deportistaIdDetalle) ?>">
                                                    <button type="submit" class="link">Agregar <?= e($formatMoney($evento['tarifa_acompanamiento_pista'] ?? 40000)) ?></button>
                                                </form>
                                            <?php else: ?>
                                                <?php $estadoAcompanamiento = (string) ($cobroAcompanamiento['estado_pago'] ?? 'pendiente'); ?>
                                                <strong><?= e($formatMoney($cobroAcompanamiento['monto'] ?? 0)) ?></strong>
                                                <span class="chip"><?= e($inscripcionEstadoLabels[$estadoAcompanamiento] ?? $estadoAcompanamiento) ?></span>
                                                <?php if ($estadoAcompanamiento === 'pagado'): ?>
                                                    <form method="post" action="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>" class="inline-form">
                                                        <input type="hidden" name="action" value="cobro-pendiente">
                                                        <input type="hidden" name="evento_id" value="<?= e((string) $evento['id']) ?>">
                                                        <input type="hidden" name="deportista_id" value="<?= e((string) $deportistaIdDetalle) ?>">
                                                        <input type="hidden" name="tipo_cobro" value="acompanamiento">
                                                        <button type="submit" class="link">Revertir</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" action="<?= e(base_url('/?page=eventos&action=show&id=' . (int) $evento['id'])) ?>" class="inline-form">
                                                        <input type="hidden" name="action" value="cobro-pagado">
                                                        <input type="hidden" name="evento_id" value="<?= e((string) $evento['id']) ?>">
                                                        <input type="hidden" name="deportista_id" value="<?= e((string) $deportistaIdDetalle) ?>">
                                                        <input type="hidden" name="tipo_cobro" value="acompanamiento">
                                                        <input type="hidden" name="metodo_pago" value="transferencia">
                                                        <button type="submit" class="link">Marcar pagado</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        (function () {
            const modal = document.querySelector('[data-eventos-inscripciones-modal]');
            const openButton = document.querySelector('[data-eventos-open-inscripciones]');
            const closeButtons = Array.from(document.querySelectorAll('[data-eventos-close-inscripciones]'));

            if (!modal || !openButton) {
                return;
            }

            function closeModal() {
                modal.hidden = true;
                document.body.classList.remove('modal-open');
            }

            openButton.addEventListener('click', function () {
                modal.hidden = false;
                document.body.classList.add('modal-open');
            });
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
        })();
        </script>

        <?php if ($hojas_schema_ready): ?>
            <?php
            $hojasPorPrograma = [];
            foreach ($hojas_elementos as $sheetRow) {
                $sheetKey = (int) ($sheetRow['inscripcion_id'] ?? 0) . ':' . (string) ($sheetRow['plantilla_codigo'] ?? '');
                $hojasPorPrograma[$sheetKey] = $sheetRow;
            }
            $hojasResumen = [];
            foreach ($inscripciones as $inscripcionRow) {
                if (($inscripcionRow['estado_pago'] ?? '') === 'anulado') {
                    continue;
                }

                $programas = array_values(array_filter(
                    evento_federado_hoja_programas($inscripcionRow),
                    static fn (array $programa): bool => !empty($programa['requires_sheet'])
                ));
                if ($programas === []) {
                    continue;
                }

                $programasResumen = [];
                foreach ($programas as $programa) {
                    $sheetKey = (int) ($inscripcionRow['id'] ?? 0) . ':' . (string) ($programa['template'] ?? '');
                    $sheetRow = $hojasPorPrograma[$sheetKey] ?? null;
                    $programasResumen[] = [
                        'label' => (string) ($programa['label'] ?? 'Hoja'),
                        'template' => (string) ($programa['template'] ?? ''),
                        'saved' => is_array($sheetRow) && (int) ($sheetRow['hoja_id'] ?? 0) > 0,
                        'updated_at' => is_array($sheetRow) ? (string) ($sheetRow['hoja_updated_at'] ?? '') : '',
                    ];
                }
                $hojasResumen[] = [
                    'inscripcion_id' => (int) ($inscripcionRow['id'] ?? 0),
                    'deportista_nombre' => (string) ($inscripcionRow['deportista_nombre'] ?? ''),
                    'modalidad_nombre' => (string) ($inscripcionRow['modalidad_nombre'] ?? ''),
                    'programas' => $programasResumen,
                ];
            }
            ?>
            <article class="ficha-card ficha-card-compact eventos-hojas-summary">
                <div class="ficha-card-head ficha-card-head-split">
                    <div>
                        <p class="form-label">Hojas de elementos</p>
                        <p class="hint">Las hojas se crean por programa tecnico. La edicion detallada sigue disponible desde el modulo de hojas.</p>
                    </div>
                    <a class="button ghost" href="<?= e(base_url('/?page=eventos&action=hoja&id=' . (int) $evento['id'])) ?>">Abrir hojas</a>
                </div>

                <?php if (empty($hojasResumen)): ?>
                    <p class="ficha-placeholder">No hay deportistas con programas que requieran hoja.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Deportista</th>
                                    <th>Programa</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hojasResumen as $hojaResumen): ?>
                                    <?php foreach ($hojaResumen['programas'] as $programa): ?>
                                    <tr>
                                        <td>
                                            <div class="hojas-resumen-persona">
                                                <span class="material-symbols-outlined hojas-resumen-status <?= $programa['saved'] ? 'is-complete' : 'is-pending' ?>" aria-hidden="true">
                                                    <?= $programa['saved'] ? 'check_circle' : 'schedule' ?>
                                                </span>
                                                <div>
                                                    <div class="hojas-resumen-chips">
                                                        <span class="chip hoja-program-chip <?= $programa['saved'] ? 'is-saved' : 'is-pending' ?>">
                                                            <?= e($programa['label']) ?>
                                                        </span>
                                                    </div>
                                                    <strong><?= e($hojaResumen['deportista_nombre']) ?></strong>
                                                    <div class="muted"><?= e($hojaResumen['modalidad_nombre']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?= e($programa['label']) ?>
                                        </td>
                                        <td>
                                            <span class="chip <?= $programa['saved'] ? 'is-saved' : 'is-pending' ?>">
                                                <?= $programa['saved'] ? 'Guardada' : 'Pendiente' ?>
                                            </span>
                                            <?php if ($programa['updated_at'] !== ''): ?>
                                                <div class="muted"><?= e($programa['updated_at']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a class="link" href="<?= e(base_url('/?page=eventos&action=hoja&id=' . (int) $evento['id'] . '&inscripcion_id=' . $hojaResumen['inscripcion_id'] . '&plantilla=' . rawurlencode($programa['template']))) ?>">
                                                <?= e($programa['saved'] ? 'Editar' : 'Ingresar') ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
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
