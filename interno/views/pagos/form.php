<?php
/** @var array $pago */
/** @var array $errors */
/** @var array $apoderados */
/** @var array $coaches */
/** @var array $clases */
/** @var array $selectedClases */
/** @var array|null $deuda */
/** @var array $modalidades */
/** @var int $selectedModalidadId */
/** @var string $action */

$isEdit = $action === 'edit';
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1><?= $isEdit ? 'Editar pago' : 'Nuevo pago' ?></h1>
            <p><?= $isEdit ? 'Actualiza la informacion del pago.' : 'Registra un nuevo pago.' ?></p>
        </div>
        <a class="button ghost" href="<?= e(base_url('/?page=pagos')) ?>">Volver</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($deuda)): ?>
        <div class="alert">
            <strong>Deuda vigente (modalidades + cuota):</strong>
            $<?= e(number_format((float) $deuda['saldo'], 0, ',', '.')) ?>
            <span class="muted">
                · Total: $<?= e(number_format((float) $deuda['total'], 0, ',', '.')) ?>
                · Pagos: $<?= e(number_format((float) $deuda['pagos'], 0, ',', '.')) ?>
            </span>
        </div>

        <div class="class-list">
            <p class="class-list-title">Detalle de deudas por modalidad</p>
            <?php if (empty($deuda['modalidades'])): ?>
                <p class="muted">No hay modalidades con deuda vigente.</p>
            <?php else: ?>
                <?php foreach ($deuda['modalidades'] as $modalidad): ?>
                    <p class="muted">
                        <?= e($modalidad['nombre']) ?> · Coach <?= e($modalidad['coach_nombre']) ?>
                        · $<?= e(number_format((float) $modalidad['costo_mensual'], 0, ',', '.')) ?>/mes
                        · <?= e((string) $modalidad['meses']) ?> meses
                        · Total $<?= e(number_format((float) $modalidad['total'], 0, ',', '.')) ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>

            <p class="class-list-title">Cuota socio</p>
            <?php if (!empty($deuda['cuota'])): ?>
                <p class="muted">
                    Desde <?= e($deuda['cuota']['fecha_inicio'] ?? '') ?>
                    hasta <?= e($deuda['cuota']['fecha_fin'] ?? '') ?>
                    · <?= e((string) $deuda['cuota']['meses']) ?> meses
                    · Total $<?= e(number_format((float) $deuda['cuota']['total'], 0, ',', '.')) ?>
                </p>
            <?php else: ?>
                <p class="muted">Sin cuota registrada.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=pagos&action=' . $action)) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) $pago['id']) ?>">
        <?php endif; ?>

        <label>
            Apoderado
            <select name="apoderado_id" id="apoderado-select" required>
                <option value="">Selecciona un apoderado</option>
                <?php foreach ($apoderados as $apoderado): ?>
                    <option value="<?= e((string) $apoderado['id']) ?>" <?= (int) $pago['apoderado_id'] === (int) $apoderado['id'] ? 'selected' : '' ?>>
                        <?= e($apoderado['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Modalidad (define el coach)
            <select name="modalidad_id" id="modalidad-select">
                <option value="">Selecciona una modalidad</option>
                <?php foreach ($modalidades as $modalidad): ?>
                    <option
                        value="<?= e((string) $modalidad['id']) ?>"
                        data-coach-id="<?= e((string) $modalidad['coach_id']) ?>"
                        data-coach-name="<?= e($modalidad['coach_nombre']) ?>"
                        <?= (int) $selectedModalidadId === (int) $modalidad['id'] ? 'selected' : '' ?>
                    >
                        <?= e($modalidad['nombre']) ?> · $<?= e(number_format((float) $modalidad['costo_mensual'], 0, ',', '.')) ?>/mes
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Coach
            <select name="coach_id" id="coach-select" required>
                <option value="">Selecciona un coach</option>
                <?php foreach ($coaches as $coach): ?>
                    <option value="<?= e((string) $coach['id']) ?>" <?= (int) $pago['coach_id'] === (int) $coach['id'] ? 'selected' : '' ?>>
                        <?= e($coach['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="form-row">
            <label>
                Periodo inicio
                <input type="date" name="periodo_inicio" value="<?= e($pago['periodo_inicio'] ?? '') ?>">
            </label>
            <label>
                Periodo fin
                <input type="date" name="periodo_fin" value="<?= e($pago['periodo_fin'] ?? '') ?>">
            </label>
        </div>

        <label>
            Fecha de pago
            <input type="date" name="fecha_pago" required value="<?= e($pago['fecha_pago'] ?? '') ?>">
        </label>

        <label>
            Monto total (deja en blanco para calcularlo con las clases seleccionadas)
            <input type="number" step="0.01" min="0" name="monto_total" value="<?= e((string) ($pago['monto_total'] ?? '')) ?>">
        </label>

        <label>
            Metodo de pago
            <input type="text" name="metodo" value="<?= e($pago['metodo'] ?? '') ?>">
        </label>

        <label>
            Referencia
            <input type="text" name="referencia" value="<?= e($pago['referencia'] ?? '') ?>">
        </label>

        <div class="class-list">
            <p class="class-list-title">Clases realizadas sin pago (marca las que incluye este pago)</p>
            <?php if (empty($clases)): ?>
                <p class="muted">No hay clases disponibles para asociar.</p>
            <?php else: ?>
                <?php foreach ($clases as $clase): ?>
                    <?php $checked = in_array((int) $clase['id'], $selectedClases, true); ?>
                    <label class="checkbox">
                        <input type="checkbox" name="clases[]" value="<?= e((string) $clase['id']) ?>" <?= $checked ? 'checked' : '' ?>>
                        <?= e($clase['fecha']) ?> · <?= e($clase['deportista_nombre']) ?> (<?= e($clase['apoderado_nombre']) ?>) · <?= e($clase['coach_nombre']) ?> · $<?= e(number_format((float) $clase['tarifa'], 0, ',', '.')) ?>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="button"><?= $isEdit ? 'Guardar cambios' : 'Crear pago' ?></button>
            <a class="button ghost" href="<?= e(base_url('/?page=pagos')) ?>">Cancelar</a>
        </div>
    </form>
</section>

<script>
  const modalidadSelect = document.getElementById('modalidad-select');
  const coachSelect = document.getElementById('coach-select');
  const apoderadoSelect = document.getElementById('apoderado-select');

  if (modalidadSelect && coachSelect) {
    const applyCoachFromModalidad = () => {
      const selected = modalidadSelect.options[modalidadSelect.selectedIndex];
      const coachId = selected?.dataset?.coachId;
      if (coachId) {
        coachSelect.value = coachId;
      }
    };

    modalidadSelect.addEventListener('change', applyCoachFromModalidad);
    applyCoachFromModalidad();
  }

  if (apoderadoSelect) {
    apoderadoSelect.addEventListener('change', () => {
      const apoderadoId = apoderadoSelect.value;
      if (!apoderadoId) {
        return;
      }
      const url = new URL(window.location.href);
      url.searchParams.set('page', 'pagos');
      url.searchParams.set('action', 'create');
      url.searchParams.set('apoderado_id', apoderadoId);
      window.location.href = url.toString();
    });
  }
</script>
