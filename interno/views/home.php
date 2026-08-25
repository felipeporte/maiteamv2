<?php
/** @var array|null $kpi_cuotas */
$kpi = $kpi_cuotas ?? null;
$periodoSeleccionado = $kpi_periodo ?? ($kpi['periodo'] ?? date('Y-m'));
?>
<section class="hero">
    <div>
        <h1>Panel interno</h1>
        <p>Gestiona apoderados, pagos y eventos del Club MaiTeam desde un solo lugar.</p>
        <div class="hero-actions">
            <a class="button" href="<?= e(config('base_url')) ?>/?page=socios">Ir a apoderados</a>
            <a class="button ghost" href="<?= e(config('base_url')) ?>/?page=clases">Ver clases</a>
        </div>
    </div>
    <div class="hero-card">
        <p class="kicker">KPI cuotas · <?= e($kpi['periodo'] ?? $periodoSeleccionado) ?></p>
        <form class="kpi-filter" method="get" action="<?= e(config('base_url')) ?>/">
            <input type="hidden" name="page" value="home">
            <label for="kpi_periodo">Mes</label>
            <input id="kpi_periodo" name="kpi_periodo" type="month" value="<?= e($periodoSeleccionado) ?>">
            <button class="button ghost" type="submit">Ver</button>
        </form>
        <?php if ($kpi === null): ?>
            <p class="muted">No se pudo cargar el KPI de cuotas.</p>
        <?php else: ?>
            <ul>
                <li><strong>Pagado en cuotas:</strong> $<?= e(number_format((float) $kpi['pagado'], 0, ',', '.')) ?></li>
                <li><strong>Deberia pagarse:</strong> $<?= e(number_format((float) $kpi['esperado'], 0, ',', '.')) ?></li>
                <li><strong>Pendiente por recaudar:</strong> $<?= e(number_format((float) $kpi['pendiente_esperado'], 0, ',', '.')) ?></li>
                <li><strong>Cobertura:</strong> <?= e(number_format((float) $kpi['cobertura_pct'], 1, ',', '.')) ?>%</li>
            </ul>
            <p class="muted">
                Apoderados considerados del mes: <?= e((string) ($kpi['apoderados_considerados'] ?? $kpi['apoderados_activos'])) ?>
                (activos: <?= e((string) ($kpi['apoderados_activos'] ?? 0)) ?>
                · con cuota registrada: <?= e((string) ($kpi['apoderados_con_cuota'] ?? 0)) ?>)
                · Cuota base: $<?= e(number_format((float) $kpi['valor_cuota'], 0, ',', '.')) ?>
            </p>
        <?php endif; ?>
    </div>
</section>

<section class="grid">
    <article class="card">
        <h2>Apoderados</h2>
        <p>Alta, baja y seguimiento de los apoderados del club.</p>
        <a href="<?= e(config('base_url')) ?>/?page=socios">Configurar</a>
    </article>
    <article class="card">
        <h2>Pagos</h2>
        <p>Control de cuotas, historico y alertas.</p>
        <a href="<?= e(config('base_url')) ?>/?page=pagos">Configurar</a>
    </article>
    <article class="card">
        <h2>Eventos federados</h2>
        <p>Gestiona competencias federadas e inscripciones por nivel.</p>
        <a href="<?= e(config('base_url')) ?>/?page=eventos">Configurar</a>
    </article>
    <article class="card">
        <h2>Deportistas</h2>
        <p>Gestion de deportistas asociados a apoderados.</p>
        <a href="<?= e(config('base_url')) ?>/?page=deportistas">Configurar</a>
    </article>
    <article class="card">
        <h2>Coaches</h2>
        <p>Equipo de coaches y especialidades.</p>
        <a href="<?= e(config('base_url')) ?>/?page=coaches">Configurar</a>
    </article>
    <article class="card">
        <h2>Clases</h2>
        <p>Registro de clases y seguimiento de asistencia.</p>
        <a href="<?= e(config('base_url')) ?>/?page=clases">Configurar</a>
    </article>
    <article class="card">
        <h2>Asistencia</h2>
        <p>Marca presencia, ausencias y justificativos por fecha.</p>
        <a href="<?= e(config('base_url')) ?>/?page=asistencia">Configurar</a>
    </article>
    <article class="card">
        <h2>Modalidades</h2>
        <p>Configura modalidades y costos mensuales.</p>
        <a href="<?= e(config('base_url')) ?>/?page=modalidades">Configurar</a>
    </article>
    <article class="card">
        <h2>Inscripciones</h2>
        <p>Asigna deportistas a sus modalidades.</p>
        <a href="<?= e(config('base_url')) ?>/?page=inscripciones">Configurar</a>
    </article>
    <article class="card">
        <h2>Competencias</h2>
        <p>Registra competencias para generar justificativos.</p>
        <a href="<?= e(config('base_url')) ?>/?page=competencias">Configurar</a>
    </article>
    <article class="card">
        <h2>Certificados</h2>
        <p>Emite certificados por RUT para recintos educacionales.</p>
        <a href="<?= e(config('base_url')) ?>/?page=certificados">Generar</a>
    </article>
    <article class="card">
        <h2>Cuotas socios</h2>
        <p>Cuota mensual fija de 3.000 por apoderado.</p>
        <a href="<?= e(config('base_url')) ?>/?page=cuotas">Configurar</a>
    </article>
</section>
