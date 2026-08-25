<?php
/** @var array $deportista */
/** @var array $errors */
/** @var array $apoderados */
/** @var array $niveles */
/** @var array $modalidades_competencia */
/** @var array $sugerencias_competencia */
/** @var bool $competencia_schema_ready */
/** @var array $competencia_assignments */
/** @var string $action */

$isEdit = $action === 'edit';
$competenciaAssignments = $competencia_assignments ?? [];
$modalidadesById = [];
$modalidadNoCompiteId = 0;
foreach ($modalidades_competencia as $modalidad) {
    $modalidadId = (int) ($modalidad['id'] ?? 0);
    if ($modalidadId > 0) {
        $modalidadesById[$modalidadId] = $modalidad;
        if (($modalidad['codigo'] ?? '') === 'no_compite') {
            $modalidadNoCompiteId = $modalidadId;
        }
    }
}

$competenciaEdad = $sugerencias_competencia['edad_competencia'] ?? null;
$competenciaCategorias = $sugerencias_competencia['categorias'] ?? [];
$competenciaOptionsUrl = base_url('/?page=deportistas&action=competencia-options');
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar deportista' : 'Nuevo deportista' ?></h1>
            <p><?= $isEdit ? 'Actualiza los datos del deportista.' : 'Completa la informacion del deportista.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=deportistas')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=deportistas&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $deportista['id']) ?>">
        <?php endif; ?>

        <label>
            Apoderado
            <select name="apoderado_id" required>
                <option value="">Selecciona un apoderado</option>
                <?php foreach ($apoderados as $apoderado): ?>
                    <option value="<?= e((string) $apoderado['id']) ?>" <?= (int) $deportista['apoderado_id'] === (int) $apoderado['id'] ? 'selected' : '' ?>>
                        <?= e($apoderado['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Nombre
            <input type="text" name="nombre" required value="<?= e($deportista['nombre'] ?? '') ?>">
        </label>

        <label>
            RUT
            <input type="text" name="rut" required value="<?= e(format_rut($deportista['rut'] ?? '')) ?>" placeholder="12345678-9">
        </label>

        <label>
            Fecha de nacimiento
            <input type="date" name="fecha_nacimiento" value="<?= e($deportista['fecha_nacimiento'] ?? '') ?>">
        </label>

        <label>
            Categoria general / clases
            <input type="text" name="categoria" value="<?= e($deportista['categoria'] ?? '') ?>">
            <span class="hint">Este campo se mantiene para la ficha general del deportista y no reemplaza la asignacion competitiva.</span>
        </label>

        <label>
            Nivel
            <select name="nivel_id" required>
                <option value="">Selecciona un nivel</option>
                <?php foreach ($niveles as $nivel): ?>
                    <option value="<?= e((string) $nivel['id']) ?>" <?= (int) ($deportista['nivel_id'] ?? 0) === (int) $nivel['id'] ? 'selected' : '' ?>>
                        <?= e($nivel['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <section
            class="form-group competencia-builder"
            data-competencia-builder
            data-options-url="<?= e($competenciaOptionsUrl) ?>"
            data-modalidad-no-compite-id="<?= e((string) $modalidadNoCompiteId) ?>"
        >
            <div class="competencia-builder-head">
                <div>
                    <p class="form-label">Asignacion competitiva</p>
                    <p class="hint">Selecciona una modalidad y el sistema cargara niveles y subniveles por AJAX. La categoria se calcula segun la edad de competencia.</p>
                </div>
                <button type="button" class="button ghost" data-competencia-add>Agregar modalidad</button>
            </div>

            <?php if (empty($competencia_schema_ready)): ?>
                <div class="alert danger">
                    Falta aplicar la migracion de modalidades de competencia en la base de datos que usa esta instancia.
                </div>
            <?php endif; ?>

            <?php if ($competenciaEdad !== null): ?>
                <div class="alert">
                    <p><strong>Edad de competencia:</strong> <span class="chip"><?= e((string) $competenciaEdad) ?> años</span></p>
                    <?php if (!empty($competenciaCategorias)): ?>
                        <p class="hint">Categorias posibles segun la edad: <?= e(implode(', ', $competenciaCategorias)) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="competencia-assignments" data-competencia-assignments>
                <?php foreach ($competenciaAssignments as $index => $assignment): ?>
                    <?php
                    $selectedModalidadId = (int) ($assignment['modalidad_competencia_id'] ?? 0);
                    $selectedModalidad = $modalidadesById[$selectedModalidadId] ?? null;
                    $selectedNivel = (string) ($assignment['nivel'] ?? '');
                    $selectedSubnivel = (string) ($assignment['subnivel'] ?? '');
                    $selectedCategoria = (string) ($assignment['categoria'] ?? '');
                    $nivelesDisponibles = $selectedModalidadId > 0 ? modalidades_competencia_niveles_por_modalidad($selectedModalidadId) : [];
                    $subnivelesDisponibles = ($selectedModalidadId > 0 && $selectedNivel !== '')
                        ? modalidades_competencia_subniveles_por_modalidad_y_nivel($selectedModalidadId, $selectedNivel)
                        : [];
                    $isNoCompite = ($selectedModalidad['codigo'] ?? '') === 'no_compite';
                    ?>
                    <article class="competencia-row" data-competencia-row data-index="<?= e((string) $index) ?>">
                        <div class="competencia-row-grid">
                            <label>
                                Modalidad *
                                <select data-competencia-field="modalidad" name="competencia_assignments[<?= e((string) $index) ?>][modalidad_competencia_id]" required>
                                    <option value="">Selecciona una modalidad</option>
                                    <?php foreach ($modalidades_competencia as $modalidad): ?>
                                        <option
                                            value="<?= e((string) $modalidad['id']) ?>"
                                            data-codigo="<?= e((string) ($modalidad['codigo'] ?? '')) ?>"
                                            <?= (int) $modalidad['id'] === $selectedModalidadId ? 'selected' : '' ?>
                                        >
                                            <?= e($modalidad['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label>
                                Nivel *
                                <select data-competencia-field="nivel" name="competencia_assignments[<?= e((string) $index) ?>][nivel]" <?= $isNoCompite ? 'disabled' : '' ?>>
                                    <option value="">Selecciona un nivel</option>
                                    <?php foreach ($nivelesDisponibles as $nivelDisponible): ?>
                                        <option value="<?= e((string) $nivelDisponible['nivel']) ?>" <?= $selectedNivel === (string) $nivelDisponible['nivel'] ? 'selected' : '' ?>>
                                            <?= e((string) $nivelDisponible['nivel']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label>
                                Subnivel *
                                <select data-competencia-field="subnivel" name="competencia_assignments[<?= e((string) $index) ?>][subnivel]" <?= $isNoCompite ? 'disabled' : '' ?>>
                                    <option value="">Selecciona un subnivel</option>
                                    <?php foreach ($subnivelesDisponibles as $subnivelDisponible): ?>
                                        <option value="<?= e((string) $subnivelDisponible['subnivel']) ?>" <?= $selectedSubnivel === (string) $subnivelDisponible['subnivel'] ? 'selected' : '' ?>>
                                            <?= e((string) $subnivelDisponible['subnivel']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <div class="competencia-preview">
                                <p class="form-label">Categoria</p>
                                <?php if ($selectedCategoria !== ''): ?>
                                    <span class="chip" data-competencia-preview><?= e($selectedCategoria) ?></span>
                                <?php elseif ($isNoCompite): ?>
                                    <span class="chip" data-competencia-preview>No compite</span>
                                <?php else: ?>
                                    <span class="chip muted" data-competencia-preview>Pendiente</span>
                                <?php endif; ?>
                                <p class="hint">La categoria se completa segun la edad de competencia.</p>
                            </div>
                        </div>

                        <div class="competencia-row-actions">
                            <button type="button" class="button ghost" data-competencia-remove>Quitar</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <template data-competencia-template>
                <article class="competencia-row" data-competencia-row data-index="__INDEX__">
                    <div class="competencia-row-grid">
                        <label>
                            Modalidad *
                            <select data-competencia-field="modalidad" name="competencia_assignments[__INDEX__][modalidad_competencia_id]" required>
                                <option value="">Selecciona una modalidad</option>
                                <?php foreach ($modalidades_competencia as $modalidad): ?>
                                    <option
                                        value="<?= e((string) $modalidad['id']) ?>"
                                        data-codigo="<?= e((string) ($modalidad['codigo'] ?? '')) ?>"
                                    >
                                        <?= e($modalidad['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Nivel *
                            <select data-competencia-field="nivel" name="competencia_assignments[__INDEX__][nivel]" disabled>
                                <option value="">Selecciona un nivel</option>
                            </select>
                        </label>

                        <label>
                            Subnivel *
                            <select data-competencia-field="subnivel" name="competencia_assignments[__INDEX__][subnivel]" disabled>
                                <option value="">Selecciona un subnivel</option>
                            </select>
                        </label>

                        <div class="competencia-preview">
                            <p class="form-label">Categoria</p>
                            <span class="chip muted" data-competencia-preview>Pendiente</span>
                            <p class="hint">La categoria se completara luego de elegir la modalidad, el nivel y el subnivel.</p>
                        </div>
                    </div>

                    <div class="competencia-row-actions">
                        <button type="button" class="button ghost" data-competencia-remove>Quitar</button>
                    </div>
                </article>
            </template>
        </section>

        <label class="checkbox">
            <input type="checkbox" name="activo" <?= !empty($deportista['activo']) ? 'checked' : '' ?>>
            Activo
        </label>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear deportista' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=deportistas')) ?>">Cancelar</a>
        </div>
    </form>
</section>

<script>
(function () {
    const builder = document.querySelector('[data-competencia-builder]');
    if (!builder) {
        return;
    }

    const assignmentsWrap = builder.querySelector('[data-competencia-assignments]');
    const template = builder.querySelector('[data-competencia-template]');
    const addButton = builder.querySelector('[data-competencia-add]');
    const optionsUrl = builder.dataset.optionsUrl || '';
    const modalidadNoCompiteId = Number(builder.dataset.modalidadNoCompiteId || '0');
    const fechaNacimientoInput = document.querySelector('input[name="fecha_nacimiento"]');
    const emptyLabel = 'Selecciona una opcion';

    let nextIndex = Array.from(assignmentsWrap.querySelectorAll('[data-competencia-row]')).reduce((max, row) => {
        const rowIndex = Number(row.dataset.index || '0');
        return Number.isFinite(rowIndex) && rowIndex > max ? rowIndex : max;
    }, -1) + 1;

    function rowFields(row) {
        return {
            modalidad: row.querySelector('[data-competencia-field="modalidad"]'),
            nivel: row.querySelector('[data-competencia-field="nivel"]'),
            subnivel: row.querySelector('[data-competencia-field="subnivel"]'),
            preview: row.querySelector('[data-competencia-preview]'),
        };
    }

    function setPreview(row, text, muted) {
        const fields = rowFields(row);
        if (!fields.preview) {
            return;
        }

        fields.preview.textContent = text;
        fields.preview.classList.toggle('muted', Boolean(muted));
    }

    function resetSelect(select, placeholder) {
        if (!select) {
            return;
        }

        select.innerHTML = '';
        select.appendChild(new Option(placeholder, ''));
        select.disabled = true;
        select.value = '';
    }

    function fillSelect(select, items, placeholder, selectedValue) {
        if (!select) {
            return;
        }

        select.innerHTML = '';
        select.appendChild(new Option(placeholder, ''));

        items.forEach((item) => {
            select.appendChild(new Option(item.label, item.value));
        });

        select.disabled = items.length === 0;

        if (selectedValue && items.some((item) => item.value === selectedValue)) {
            select.value = selectedValue;
        } else {
            select.value = '';
        }
    }

    function setRowBusy(row, busy) {
        row.classList.toggle('is-loading', busy);
        row.setAttribute('aria-busy', busy ? 'true' : 'false');
    }

    function getModalidadMeta(select) {
        if (!select) {
            return { id: 0, codigo: '' };
        }

        const option = select.options[select.selectedIndex];
        return {
            id: Number(select.value || '0'),
            codigo: option?.dataset?.codigo || '',
        };
    }

    function buildUrl(params) {
        const url = new URL(optionsUrl, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.set(key, String(value));
            }
        });
        return url;
    }

    async function requestOptions(params) {
        const response = await fetch(buildUrl(params).toString(), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        const payload = await response.json().catch(() => null);
        if (!response.ok || !payload || payload.ok === false) {
            throw new Error(payload?.message || 'No se pudo cargar la informacion.');
        }

        return payload;
    }

    function currentFechaNacimiento() {
        return fechaNacimientoInput ? fechaNacimientoInput.value : '';
    }

    async function updatePreview(row) {
        const fields = rowFields(row);
        const modalidadMeta = getModalidadMeta(fields.modalidad);
        const nivel = fields.nivel ? fields.nivel.value : '';
        const subnivel = fields.subnivel ? fields.subnivel.value : '';
        const fechaNacimiento = currentFechaNacimiento();

        if (!modalidadMeta.id) {
            setPreview(row, 'Pendiente', true);
            return;
        }

        if (modalidadMeta.id === modalidadNoCompiteId || modalidadMeta.codigo === 'no_compite') {
            setPreview(row, 'No compite', false);
            return;
        }

        if (!nivel || !subnivel || !fechaNacimiento) {
            setPreview(row, 'Pendiente', true);
            return;
        }

        const requestId = Number(row.dataset.requestId || '0') + 1;
        row.dataset.requestId = String(requestId);

        try {
            const payload = await requestOptions({
                kind: 'preview',
                modalidad_id: modalidadMeta.id,
                nivel,
                subnivel,
                fecha_nacimiento: fechaNacimiento,
            });

            if (row.dataset.requestId !== String(requestId)) {
                return;
            }

            if (payload.categoria) {
                setPreview(row, payload.categoria, false);
            } else {
                setPreview(row, 'Pendiente', true);
            }
        } catch (error) {
            if (row.dataset.requestId === String(requestId)) {
                setPreview(row, 'Error al calcular', true);
            }
        }
    }

    async function loadLevels(row, preserveSelectedValue) {
        const fields = rowFields(row);
        const modalidadMeta = getModalidadMeta(fields.modalidad);

        if (!modalidadMeta.id) {
            resetSelect(fields.nivel, 'Selecciona un nivel');
            resetSelect(fields.subnivel, 'Selecciona un subnivel');
            setPreview(row, 'Pendiente', true);
            return;
        }

        if (modalidadMeta.id === modalidadNoCompiteId || modalidadMeta.codigo === 'no_compite') {
            resetSelect(fields.nivel, 'No aplica');
            resetSelect(fields.subnivel, 'No aplica');
            setPreview(row, 'No compite', false);
            return;
        }

        const requestId = Number(row.dataset.requestId || '0') + 1;
        row.dataset.requestId = String(requestId);
        setRowBusy(row, true);

        try {
            const payload = await requestOptions({
                kind: 'levels',
                modalidad_id: modalidadMeta.id,
            });

            if (row.dataset.requestId !== String(requestId)) {
                return;
            }

            const currentLevel = preserveSelectedValue ? (fields.nivel?.value || '') : '';
            fillSelect(fields.nivel, payload.items || [], 'Selecciona un nivel', currentLevel);

            if (fields.nivel && fields.nivel.value) {
                await loadSublevels(row, preserveSelectedValue);
            } else {
                resetSelect(fields.subnivel, 'Selecciona un subnivel');
                setPreview(row, 'Pendiente', true);
            }
        } catch (error) {
            if (row.dataset.requestId === String(requestId)) {
                resetSelect(fields.nivel, 'Selecciona un nivel');
                resetSelect(fields.subnivel, 'Selecciona un subnivel');
                setPreview(row, 'Error al cargar', true);
            }
        } finally {
            if (row.dataset.requestId === String(requestId)) {
                setRowBusy(row, false);
            }
        }
    }

    async function loadSublevels(row, preserveSelectedValue) {
        const fields = rowFields(row);
        const modalidadMeta = getModalidadMeta(fields.modalidad);
        const nivel = fields.nivel ? fields.nivel.value : '';

        if (!modalidadMeta.id || !nivel) {
            resetSelect(fields.subnivel, 'Selecciona un subnivel');
            setPreview(row, 'Pendiente', true);
            return;
        }

        if (modalidadMeta.id === modalidadNoCompiteId || modalidadMeta.codigo === 'no_compite') {
            resetSelect(fields.subnivel, 'No aplica');
            setPreview(row, 'No compite', false);
            return;
        }

        const requestId = Number(row.dataset.requestId || '0') + 1;
        row.dataset.requestId = String(requestId);
        setRowBusy(row, true);

        try {
            const payload = await requestOptions({
                kind: 'subniveles',
                modalidad_id: modalidadMeta.id,
                nivel,
            });

            if (row.dataset.requestId !== String(requestId)) {
                return;
            }

            const currentSublevel = preserveSelectedValue ? (fields.subnivel?.value || '') : '';
            fillSelect(fields.subnivel, payload.items || [], 'Selecciona un subnivel', currentSublevel);
            await updatePreview(row);
        } catch (error) {
            if (row.dataset.requestId === String(requestId)) {
                resetSelect(fields.subnivel, 'Selecciona un subnivel');
                setPreview(row, 'Error al cargar', true);
            }
        } finally {
            if (row.dataset.requestId === String(requestId)) {
                setRowBusy(row, false);
            }
        }
    }

    function addRow() {
        const html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
        nextIndex += 1;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        assignmentsWrap.appendChild(row);
        setPreview(row, 'Pendiente', true);
        return row;
    }

    function ensureAtLeastOneRow() {
        if (!assignmentsWrap.querySelector('[data-competencia-row]')) {
            addRow();
        }
    }

    assignmentsWrap.addEventListener('change', (event) => {
        const target = event.target;
        const row = target.closest('[data-competencia-row]');
        if (!row) {
            return;
        }

        if (target.matches('[data-competencia-field="modalidad"]')) {
            loadLevels(row, false);
            return;
        }

        if (target.matches('[data-competencia-field="nivel"]')) {
            loadSublevels(row, false);
            return;
        }

        if (target.matches('[data-competencia-field="subnivel"]')) {
            updatePreview(row);
        }
    });

    assignmentsWrap.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-competencia-remove]');
        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('[data-competencia-row]');
        if (!row) {
            return;
        }

        row.remove();
        ensureAtLeastOneRow();
    });

    if (addButton) {
        addButton.addEventListener('click', () => {
            addRow();
        });
    }

    if (fechaNacimientoInput) {
        fechaNacimientoInput.addEventListener('change', () => {
            assignmentsWrap.querySelectorAll('[data-competencia-row]').forEach((row) => {
                updatePreview(row);
            });
        });
    }

    assignmentsWrap.querySelectorAll('[data-competencia-row]').forEach((row) => {
        const fields = rowFields(row);
        const modalidadMeta = getModalidadMeta(fields.modalidad);

        if (modalidadMeta.id === modalidadNoCompiteId || modalidadMeta.codigo === 'no_compite') {
            resetSelect(fields.nivel, 'No aplica');
            resetSelect(fields.subnivel, 'No aplica');
            setPreview(row, 'No compite', false);
            return;
        }

        if (modalidadMeta.id && fields.nivel && fields.nivel.value && fields.subnivel && fields.subnivel.value) {
            updatePreview(row);
        } else if (modalidadMeta.id && fields.nivel && fields.nivel.value) {
            loadSublevels(row, true);
        } else if (modalidadMeta.id) {
            loadLevels(row, true);
        } else {
            setPreview(row, 'Pendiente', true);
        }
    });

    ensureAtLeastOneRow();
})();
</script>
