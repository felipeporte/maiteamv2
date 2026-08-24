<?php
/** @var string $title */
/** @var string $viewPath */

$baseUrl = base_url();

$navItems = [
    'home' => 'Inicio',
    'socios' => 'Apoderados',
    'deportistas' => 'Deportistas',
    'coaches' => 'Coaches',
    'clases' => 'Clases',
    'modalidades' => 'Modalidades',
    'inscripciones' => 'Inscripciones',
    'competencias' => 'Competencias',
    'certificados' => 'Certificados',
    'cuotas' => 'Cuotas socios',
    'reportes' => 'Reportes',
    'pagos' => 'Pagos',
    'transferencias' => 'Transferencias',
    'eventos' => 'Eventos',
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= e($baseUrl) ?>/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="brand">
            <img class="brand-logo" src="<?= e($baseUrl) ?>/assets/img/Logo principal.png" alt="Logo Club MaiTeam">
            <div>
                <p class="brand-title">Club MaiTeam</p>
                <p class="brand-subtitle">Gestion interna</p>
            </div>
        </div>
        <nav class="site-nav">
            <?php foreach ($navItems as $key => $label): ?>
                <a class="nav-link<?= $key === ($page ?? '') ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>/?page=<?= e($key) ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </header>

    <main class="main-content">
        <?php require $viewPath; ?>
    </main>

    <footer class="site-footer">
        <p>Club MaiTeam · Sistema interno</p>
    </footer>
</body>
</html>
