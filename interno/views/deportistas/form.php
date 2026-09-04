<?php
/** @var array $deportista */
/** @var array $errors */
/** @var array $apoderados */
/** @var array $niveles */
/** @var array $modalidades_competencia */
/** @var array $sugerencias_competencia */
/** @var bool $competencia_schema_ready */
/** @var array $competencia_assignments */
/** @var array $inscripciones */
/** @var array $modalidades */
/** @var string $action */

$isEdit = $action === 'edit';
$competenciaAssignments = $competencia_assignments ?? [];
$inscripciones = $inscripciones ?? [];
$modalidades = $modalidades ?? [];
$deportistaNombre = trim((string) ($deportista['nombre'] ?? ''));
$deportistaAvatarUrl = deportista_avatar_public_url($deportista['avatar_path'] ?? null);
$deportistaAvatarInitials = (static function (string $name): string {
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    if (empty($parts)) {
        return 'D';
    }

    $firstPart = $parts[0] ?? '';
    $lastPart = $parts[count($parts) - 1] ?? '';
    $getInitial = static function (string $value): string {
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_strtoupper(mb_substr($value, 0, 1));
        }

        return strtoupper(substr($value, 0, 1));
    };

    $initials = $getInitial($firstPart) . $getInitial($lastPart);
    return $initials !== '' ? $initials : 'D';
})($deportistaNombre);
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
$competenciaAssignmentsSummary = array_values(array_filter(
    $competenciaAssignments,
    static fn (array $assignment): bool => (int) ($assignment['modalidad_competencia_id'] ?? 0) > 0
));
?>
<section class="page ficha-page">
    <div class="ficha-header">
        <div class="ficha-header-copy">
            <p class="kicker"><?= $isEdit ? 'Ficha de Deportista' : 'Nuevo deportista' ?></p>
            <h1><?= $isEdit ? 'Editar deportista' : 'Crear deportista' ?></h1>
            <p>Completa la informacion base y sus modalidades.</p>
        </div>
        <div class="ficha-header-actions">
            <a class="button ghost" href="<?= e(base_url('/?page=deportistas')) ?>">Cancelar</a>
            <button type="submit" form="deportista-form" class="button">Guardar ficha</button>
        </div>
    </div>

    <nav class="ficha-tabs" aria-label="Secciones de la ficha" role="tablist">
        <a id="tab-info-personal" class="ficha-tab is-active" href="#info-personal" role="tab" aria-selected="true" aria-controls="info-personal" data-ficha-tab data-ficha-target="info-personal">Información personal</a>
        <a id="tab-modalidades-niveles" class="ficha-tab" href="#modalidades-niveles" role="tab" aria-selected="false" aria-controls="modalidades-niveles" data-ficha-tab data-ficha-target="modalidades-niveles">Modalidades y niveles</a>
        <a id="tab-inscripciones" class="ficha-tab" href="#inscripciones" role="tab" aria-selected="false" aria-controls="inscripciones" data-ficha-tab data-ficha-target="inscripciones">Inscripciones</a>
        <a id="tab-datos-adicionales" class="ficha-tab" href="#datos-adicionales" role="tab" aria-selected="false" aria-controls="datos-adicionales" data-ficha-tab data-ficha-target="datos-adicionales">Datos adicionales</a>
        <a id="tab-documentos" class="ficha-tab" href="#documentos" role="tab" aria-selected="false" aria-controls="documentos" data-ficha-tab data-ficha-target="documentos">Documentos</a>
        <a id="tab-observaciones" class="ficha-tab" href="#observaciones" role="tab" aria-selected="false" aria-controls="observaciones" data-ficha-tab data-ficha-target="observaciones">Observaciones</a>
    </nav>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form id="deportista-form" class="ficha-form" method="post" enctype="multipart/form-data" action="<?= e(base_url('/?page=deportistas&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $deportista['id']) ?>">
        <?php endif; ?>

        <div class="ficha-layout">
            <div class="ficha-main">
                <section id="info-personal" class="ficha-card ficha-panel" role="tabpanel" aria-labelledby="tab-info-personal" data-ficha-panel>
                    <div class="ficha-card-head">
                        <div>
                            <p class="form-label">Información personal</p>
                        </div>
                    </div>
                    <div class="ficha-personal-layout">
                        <div class="ficha-avatar-panel">
                            <label class="avatar-picker" for="avatar-input">
                                <div
                                    class="deportista-avatar deportista-avatar--large"
                                    data-avatar-preview
                                    data-avatar-current-src="<?= e($deportistaAvatarUrl ?? '') ?>"
                                    data-avatar-initials="<?= e($deportistaAvatarInitials) ?>"
                                >
                                    <?php if ($deportistaAvatarUrl): ?>
                                        <img src="<?= e($deportistaAvatarUrl) ?>" alt="Avatar del deportista">
                                    <?php else: ?>
                                        <span data-avatar-fallback><?= e($deportistaAvatarInitials) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="avatar-picker-action">
                                    <span class="material-symbols-outlined" aria-hidden="true">photo_camera</span>
                                    <span>Cambiar avatar</span>
                                </span>
                            </label>
                            <input id="avatar-input" class="avatar-picker-input" type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif">
                        </div>

                        <div class="ficha-grid ficha-personal-grid">
                            <div class="ficha-field">
                                <label class="ficha-field-label" for="deportista-apoderado">Apoderado</label>
                                <select id="deportista-apoderado" name="apoderado_id" required>
                                    <option value="">Selecciona un apoderado</option>
                                    <?php foreach ($apoderados as $apoderado): ?>
                                        <option value="<?= e((string) $apoderado['id']) ?>" <?= (int) $deportista['apoderado_id'] === (int) $apoderado['id'] ? 'selected' : '' ?>>
                                            <?= e($apoderado['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="ficha-field">
                                <label class="ficha-field-label" for="deportista-nombre">Nombre</label>
                                <input id="deportista-nombre" type="text" name="nombre" required value="<?= e($deportista['nombre'] ?? '') ?>">
                            </div>

                            <div class="ficha-field">
                                <label class="ficha-field-label" for="deportista-rut">RUT</label>
                                <input id="deportista-rut" type="text" name="rut" required value="<?= e(format_rut($deportista['rut'] ?? '')) ?>" placeholder="12345678-9">
                            </div>

                            <div class="ficha-field">
                                <label class="ficha-field-label" for="deportista-fecha-nacimiento">Fecha de nacimiento</label>
                                <input id="deportista-fecha-nacimiento" type="date" name="fecha_nacimiento" value="<?= e($deportista['fecha_nacimiento'] ?? '') ?>">
                            </div>

                            <div class="ficha-field ficha-field-static">
                                <p class="ficha-field-label">Edad de competencia</p>
                                <?php if ($competenciaEdad !== null): ?>
                                    <span class="chip"><?= e((string) $competenciaEdad) ?> años</span>
                                    <?php if (!empty($competenciaCategorias)): ?>
                                        <p class="hint">Categorias: <?= e(implode(', ', $competenciaCategorias)) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="chip muted">Pendiente</span>
                                    <p class="hint">Se calcula al completar la fecha de nacimiento.</p>
                                <?php endif; ?>
                            </div>

                            <div class="ficha-field ficha-field-static">
                                <p class="ficha-field-label">Estado</p>
                                <label class="checkbox ficha-inline-check">
                                    <input type="checkbox" name="activo" <?= !empty($deportista['activo']) ? 'checked' : '' ?>>
                                    Activo
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="modalidades-niveles" class="ficha-card ficha-panel" role="tabpanel" aria-labelledby="tab-modalidades-niveles" data-ficha-panel hidden>
                    <div class="ficha-card-head ficha-card-head-split">
                        <div>
                            <p class="form-label">Modalidades y niveles</p>
                            <p class="hint">Selecciona una modalidad y el sistema cargara niveles y subniveles por AJAX.</p>
                        </div>
                        <div class="competencia-card-actions">
                            <span class="chip muted" data-competencia-count>0 modalidades</span>
                            <button type="button" class="button" data-competencia-add>
                                <span class="material-symbols-outlined" aria-hidden="true">add</span>
                                Agregar modalidad
                            </button>
                        </div>
                    </div>

                    <div class="competencia-layout">
                        <div
                            class="competencia-builder"
                            data-competencia-builder
                            data-options-url="<?= e($competenciaOptionsUrl) ?>"
                            data-modalidad-no-compite-id="<?= e((string) $modalidadNoCompiteId) ?>"
                        >
                            <?php if (empty($competencia_schema_ready)): ?>
                                <div class="alert danger">
                                    Falta aplicar la migracion de modalidades de competencia en la base de datos que usa esta instancia.
                                </div>
                            <?php endif; ?>

                            <?php if ($competenciaEdad !== null): ?>
                                <div class="alert ficha-alert-inline">
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
                                        <div class="competencia-row-top">
                                            <div>
                                                <p class="competencia-row-title">Asignación competitiva</p>
                                                <p class="competencia-row-subtitle">Completa modalidad, nivel, subnivel y categoría.</p>
                                            </div>
                                            <button type="button" class="button ghost" data-competencia-remove>Quitar</button>
                                        </div>
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
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <template data-competencia-template>
                                <article class="competencia-row" data-competencia-row data-index="__INDEX__">
                                    <div class="competencia-row-top">
                                        <div>
                                            <p class="competencia-row-title">Asignación competitiva</p>
                                            <p class="competencia-row-subtitle">Completa modalidad, nivel, subnivel y categoría.</p>
                                        </div>
                                        <button type="button" class="button ghost" data-competencia-remove>Quitar</button>
                                    </div>
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
                                </article>
                            </template>
                        </div>

                        <aside class="ficha-summary competencia-summary">
                            <div class="ficha-card ficha-card-compact">
                                <p class="form-label">Resumen</p>
                                <p class="hint">Vista rapida del deportista y sus asignaciones.</p>
                                <p class="ficha-summary-count"><?= e((string) count($competenciaAssignmentsSummary)) ?> modalidades agregadas</p>
                                <?php if (!empty($competenciaAssignmentsSummary)): ?>
                                    <ul class="ficha-summary-list">
                                        <?php foreach ($competenciaAssignmentsSummary as $assignment): ?>
                                            <?php
                                            $summaryParts = array_filter([
                                                trim((string) ($assignment['nivel'] ?? '')),
                                                trim((string) ($assignment['subnivel'] ?? '')),
                                            ]);
                                            $summaryLabel = !empty($summaryParts) ? implode(' · ', $summaryParts) : 'Pendiente';
                                            ?>
                                            <li>
                                                <strong><?= e((string) ($assignment['modalidad_nombre'] ?? 'Modalidad')) ?></strong>
                                                <span><?= e($summaryLabel) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="ficha-placeholder">Aun no hay modalidades agregadas.</p>
                                <?php endif; ?>
                            </div>
                        </aside>
                    </div>
                </section>

                <section id="inscripciones" class="ficha-card ficha-panel" role="tabpanel" aria-labelledby="tab-inscripciones" data-ficha-panel hidden>
                    <div class="ficha-card-head ficha-card-head-split">
                        <div>
                            <p class="form-label">Inscripciones</p>
                            <p class="hint">Gestiona las modalidades contratadas y su vigencia desde la ficha.</p>
                        </div>
                        <?php if ($isEdit): ?>
                            <button type="button" class="button" data-inscripcion-add>
                                <span class="material-symbols-outlined" aria-hidden="true">add</span>
                                Agregar inscripción
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isEdit): ?>
                        <p class="ficha-placeholder">Guarda primero al deportista para poder agregar sus inscripciones.</p>
                    <?php else: ?>
                        <div class="inscripciones-inline" data-inscripciones-inline>
                            <div class="inscripciones-inline-list" data-inscripciones-list>
                                <?php foreach ($inscripciones as $index => $inscripcion): ?>
                                    <article class="inscripcion-inline-row" data-inscripcion-row>
                                        <input type="hidden" name="inscripciones[<?= e((string) $index) ?>][id]" value="<?= e((string) ($inscripcion['id'] ?? 0)) ?>">
                                        <label>
                                            Modalidad
                                            <select name="inscripciones[<?= e((string) $index) ?>][modalidad_id]" required>
                                                <option value="">Selecciona una modalidad</option>
                                                <?php foreach ($modalidades as $modalidad): ?>
                                                    <option value="<?= e((string) $modalidad['id']) ?>" <?= (int) ($inscripcion['modalidad_id'] ?? 0) === (int) $modalidad['id'] ? 'selected' : '' ?>>
                                                        <?= e($modalidad['nombre']) ?> ($<?= e(number_format((float) $modalidad['costo_mensual'], 0, ',', '.')) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label>
                                            Fecha inicio
                                            <input type="date" name="inscripciones[<?= e((string) $index) ?>][fecha_inicio]" required value="<?= e((string) ($inscripcion['fecha_inicio'] ?? '')) ?>">
                                        </label>
                                        <label>
                                            Fecha fin
                                            <input type="date" name="inscripciones[<?= e((string) $index) ?>][fecha_fin]" value="<?= e((string) ($inscripcion['fecha_fin'] ?? '')) ?>">
                                        </label>
                                        <label class="checkbox">
                                            <input type="checkbox" name="inscripciones[<?= e((string) $index) ?>][activo]" <?= !empty($inscripcion['activo']) ? 'checked' : '' ?>>
                                            Activa
                                        </label>
                                        <button type="button" class="button ghost danger" data-inscripcion-remove>Quitar</button>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <?php if (empty($inscripciones)): ?>
                                <p class="ficha-placeholder" data-inscripciones-empty>No hay inscripciones registradas.</p>
                            <?php endif; ?>
                        </div>
                        <template data-inscripcion-template>
                            <article class="inscripcion-inline-row" data-inscripcion-row>
                                <input type="hidden" name="inscripciones[__INDEX__][id]" value="0">
                                <label>
                                    Modalidad
                                    <select name="inscripciones[__INDEX__][modalidad_id]" required>
                                        <option value="">Selecciona una modalidad</option>
                                        <?php foreach ($modalidades as $modalidad): ?>
                                            <option value="<?= e((string) $modalidad['id']) ?>"><?= e($modalidad['nombre']) ?> ($<?= e(number_format((float) $modalidad['costo_mensual'], 0, ',', '.')) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>
                                    Fecha inicio
                                    <input type="date" name="inscripciones[__INDEX__][fecha_inicio]" required>
                                </label>
                                <label>
                                    Fecha fin
                                    <input type="date" name="inscripciones[__INDEX__][fecha_fin]">
                                </label>
                                <label class="checkbox">
                                    <input type="checkbox" name="inscripciones[__INDEX__][activo]" checked>
                                    Activa
                                </label>
                                <button type="button" class="button ghost danger" data-inscripcion-remove>Quitar</button>
                            </article>
                        </template>
                    <?php endif; ?>
                </section>

                <section id="datos-adicionales" class="ficha-card ficha-panel" role="tabpanel" aria-labelledby="tab-datos-adicionales" data-ficha-panel hidden>
                    <div class="ficha-card-head">
                        <div>
                            <p class="form-label">Datos adicionales</p>
                            <p class="hint">Informacion que no pertenece a la ficha competitiva.</p>
                        </div>
                    </div>
                    <div class="ficha-grid ficha-grid-2">
                        <div class="ficha-field">
                            <label class="ficha-field-label" for="deportista-categoria">Categoria general / clases</label>
                            <input id="deportista-categoria" type="text" name="categoria" value="<?= e($deportista['categoria'] ?? '') ?>">
                            <span class="hint">Este campo se mantiene para la ficha general del deportista y no reemplaza la asignacion competitiva.</span>
                        </div>

                        <div class="ficha-field">
                            <label class="ficha-field-label" for="deportista-nivel">Nivel</label>
                            <select id="deportista-nivel" name="nivel_id" required>
                                <option value="">Selecciona un nivel</option>
                                <?php foreach ($niveles as $nivel): ?>
                                    <option value="<?= e((string) $nivel['id']) ?>" <?= (int) ($deportista['nivel_id'] ?? 0) === (int) $nivel['id'] ? 'selected' : '' ?>>
                                        <?= e($nivel['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <section id="documentos" class="ficha-card ficha-card-muted ficha-panel" role="tabpanel" aria-labelledby="tab-documentos" data-ficha-panel hidden>
                    <div class="ficha-card-head">
                        <div>
                            <p class="form-label">Documentos</p>
                            <p class="hint">Seccion preparada para agregar archivos o respaldos mas adelante.</p>
                        </div>
                    </div>
                    <p class="ficha-placeholder">Estructura reservada para documentos del deportista.</p>
                </section>

                <section id="observaciones" class="ficha-card ficha-card-muted ficha-panel" role="tabpanel" aria-labelledby="tab-observaciones" data-ficha-panel hidden>
                    <div class="ficha-card-head">
                        <div>
                            <p class="form-label">Observaciones</p>
                            <p class="hint">Espacio reservado para notas internas.</p>
                        </div>
                    </div>
                    <p class="ficha-placeholder">Estructura reservada para observaciones del deportista.</p>
                </section>
            </div>
        </div>
    </form>
</section>

<script>
(function () {
    const tabs = Array.from(document.querySelectorAll('[data-ficha-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-ficha-panel]'));
    if (!tabs.length || !panels.length) {
        return;
    }

    const panelById = new Map(panels.map((panel) => [panel.id, panel]));

    function activateTab(targetId, updateHash) {
        const panel = panelById.get(targetId);
        if (!panel) {
            return;
        }

        tabs.forEach((tab) => {
            const isActive = tab.dataset.fichaTarget === targetId;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach((candidate) => {
            candidate.hidden = candidate.id !== targetId;
        });

        if (updateHash) {
            history.replaceState(null, '', `#${targetId}`);
        }
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', (event) => {
            const targetId = tab.dataset.fichaTarget || '';
            if (!targetId) {
                return;
            }

            event.preventDefault();
            activateTab(targetId, true);
        });
    });

    const initialTarget = panelById.has(window.location.hash.replace('#', ''))
        ? window.location.hash.replace('#', '')
        : (tabs.find((tab) => tab.classList.contains('is-active'))?.dataset?.fichaTarget || panels[0].id);

    activateTab(initialTarget, false);
})();

(function () {
    const builder = document.querySelector('[data-competencia-builder]');
    if (!builder) {
        return;
    }

    const section = builder.closest('.ficha-card') || document;
    const assignmentsWrap = builder.querySelector('[data-competencia-assignments]');
    const template = builder.querySelector('[data-competencia-template]');
    const addButton = section.querySelector('[data-competencia-add]');
    const countBadge = section.querySelector('[data-competencia-count]');
    const optionsUrl = builder.dataset.optionsUrl || '';
    const modalidadNoCompiteId = Number(builder.dataset.modalidadNoCompiteId || '0');
    const fechaNacimientoInput = document.querySelector('input[name="fecha_nacimiento"]');
    const avatarInput = document.querySelector('input[name="avatar"]');
    const avatarPreview = document.querySelector('[data-avatar-preview]');
    const avatarCurrentSrc = avatarPreview?.dataset?.avatarCurrentSrc || '';
    const avatarInitials = avatarPreview?.dataset?.avatarInitials || 'D';
    let avatarObjectUrl = null;

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

    function updateAssignmentsCount() {
        if (!countBadge) {
            return;
        }

        const count = assignmentsWrap.querySelectorAll('[data-competencia-row]').length;
        countBadge.textContent = count === 1 ? '1 modalidad' : `${count} modalidades`;
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

    function renderAvatarPreviewFromState() {
        if (!avatarPreview) {
            return;
        }

        avatarPreview.innerHTML = '';

        if (avatarCurrentSrc) {
            const image = document.createElement('img');
            image.src = avatarCurrentSrc;
            image.alt = 'Avatar del deportista';
            avatarPreview.appendChild(image);
            return;
        }

        const fallback = document.createElement('span');
        fallback.setAttribute('data-avatar-fallback', 'true');
        fallback.textContent = avatarInitials;
        avatarPreview.appendChild(fallback);
    }

    function renderAvatarFromFile(file) {
        if (!avatarPreview) {
            return;
        }

        if (avatarObjectUrl) {
            URL.revokeObjectURL(avatarObjectUrl);
            avatarObjectUrl = null;
        }

        avatarPreview.innerHTML = '';

        if (!file) {
            renderAvatarPreviewFromState();
            return;
        }

        avatarObjectUrl = URL.createObjectURL(file);
        const image = document.createElement('img');
        image.src = avatarObjectUrl;
        image.alt = 'Avatar seleccionado';
        avatarPreview.appendChild(image);
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

    function addRow(focusFirstField = false) {
        const html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
        nextIndex += 1;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        assignmentsWrap.appendChild(row);
        setPreview(row, 'Pendiente', true);
        updateAssignmentsCount();

        if (focusFirstField) {
            window.requestAnimationFrame(() => {
                row.querySelector('[data-competencia-field="modalidad"]')?.focus();
            });
        }

        return row;
    }

    function ensureAtLeastOneRow() {
        if (!assignmentsWrap.querySelector('[data-competencia-row]')) {
            addRow();
        }

        updateAssignmentsCount();
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
            addRow(true);
        });
    }

    if (fechaNacimientoInput) {
        fechaNacimientoInput.addEventListener('change', () => {
            assignmentsWrap.querySelectorAll('[data-competencia-row]').forEach((row) => {
                updatePreview(row);
            });
        });
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', () => {
            renderAvatarFromFile(avatarInput.files ? avatarInput.files[0] : null);
        });
    }

    renderAvatarPreviewFromState();

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
    updateAssignmentsCount();
})();

(function () {
    const inline = document.querySelector('[data-inscripciones-inline]');
    const template = document.querySelector('[data-inscripcion-template]');
    const addButton = document.querySelector('[data-inscripcion-add]');
    const list = inline?.querySelector('[data-inscripciones-list]');
    if (!inline || !template || !addButton || !list) {
        return;
    }

    let nextIndex = list.querySelectorAll('[data-inscripcion-row]').length;

    function updateEmptyState() {
        const empty = list.querySelectorAll('[data-inscripcion-row]').length === 0;
        let message = inline.querySelector('[data-inscripciones-empty]');
        if (empty && !message) {
            message = document.createElement('p');
            message.className = 'ficha-placeholder';
            message.dataset.inscripcionesEmpty = '';
            message.textContent = 'No hay inscripciones registradas.';
            inline.appendChild(message);
        } else if (!empty && message) {
            message.remove();
        }
    }

    addButton.addEventListener('click', () => {
        const row = template.content.firstElementChild.cloneNode(true);
        row.innerHTML = row.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        list.appendChild(row);
        updateEmptyState();
        row.querySelector('select')?.focus();
    });

    list.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-inscripcion-remove]');
        if (!removeButton) {
            return;
        }

        removeButton.closest('[data-inscripcion-row]')?.remove();
        updateEmptyState();
    });
})();
</script>
