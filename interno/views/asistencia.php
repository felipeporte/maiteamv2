<?php
$baseUrl = rtrim(base_url(), '/');
$assetBaseUrl = $baseUrl . '/interno/assets/asistencia-app';
$manifestPath = dirname(__DIR__) . '/assets/asistencia-app/manifest.json';
$entry = null;

if (is_file($manifestPath)) {
    $manifest = json_decode((string) file_get_contents($manifestPath), true);

    if (is_array($manifest)) {
        if (isset($manifest['index.html']) && is_array($manifest['index.html'])) {
            $entry = $manifest['index.html'];
        } elseif (isset($manifest['src/main.jsx']) && is_array($manifest['src/main.jsx'])) {
            $entry = $manifest['src/main.jsx'];
        }
    }
}
?>
<section class="page">
    <h1>Asistencia</h1>
    <p>Modulo React en preparacion para el control de asistencia del sistema interno.</p>

    <?php if ($entry !== null): ?>
        <?php foreach (($entry['css'] ?? []) as $cssFile): ?>
            <link rel="stylesheet" href="<?= e($assetBaseUrl . '/' . ltrim($cssFile, '/')) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <article class="card">
        <div id="root"></div>
    </article>

    <?php if ($entry !== null && !empty($entry['file'])): ?>
        <script type="module" src="<?= e($assetBaseUrl . '/' . ltrim($entry['file'], '/')) ?>"></script>
    <?php else: ?>
        <p class="muted">No se encontro el build de React. Ejecuta <code>npm run build</code> en <code>interno/react-asistencia</code>.</p>
    <?php endif; ?>
</section>
