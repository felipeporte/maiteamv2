<?php
/** @var string $rut */
/** @var array $errors */
/** @var array|null $deportista */
/** @var array $competencias */
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Certificados</h1>
            <p>Busca por RUT para generar certificados PDF para establecimientos educacionales.</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert danger">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(base_url('/?page=certificados')) ?>">
        <div class="form-row">
            <label>
                RUT del deportista
                <input type="text" name="rut" required value="<?= e($rut) ?>" placeholder="12345678-9">
            </label>
        </div>
        <div class="form-actions">
            <button type="submit" class="button">Buscar certificados</button>
        </div>
    </form>

    <?php if ($deportista !== null): ?>
        <div class="placeholder">
            <p><strong>Deportista:</strong> <?= e($deportista['nombre']) ?></p>
            <p><strong>RUT:</strong> <?= e(format_rut($deportista['rut'] ?? '')) ?></p>
            <p><strong>Nivel:</strong> <?= e($deportista['nivel_nombre'] ?? 'Sin nivel asignado') ?></p>

            <div class="form-actions">
                <a class="button" href="<?= e(base_url('/?page=certificados&action=pdf&type=permanencia&rut=' . urlencode((string) $deportista['rut']))) ?>">
                    Descargar certificado de permanencia (PDF)
                </a>
            </div>

            <?php if (empty($competencias)): ?>
                <p class="muted">No hay competencias registradas para el nivel de este deportista, por lo que no se puede emitir justificativo de competencia.</p>
            <?php else: ?>
                <h2>Justificativos de competencia por nivel</h2>
                <div class="class-list">
                    <?php foreach ($competencias as $competencia): ?>
                        <div>
                            <p class="class-list-title">
                                <?= e($competencia['nombre']) ?>
                                (<?= e(certificado_texto_fechas_competencia($competencia)) ?>)
                                <?= !empty($competencia['lugar']) ? ' - ' . e($competencia['lugar']) : '' ?>
                            </p>
                            <a class="button ghost" href="<?= e(base_url('/?page=certificados&action=pdf&type=competencia&rut=' . urlencode((string) $deportista['rut']) . '&competencia_id=' . (int) $competencia['id'])) ?>">
                                Descargar justificativo PDF
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
