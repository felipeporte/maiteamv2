<?php
/** @var string $title */
/** @var string $viewPath */

$baseUrl = base_url();

$navSections = [
    'Principal' => [
        ['key' => 'home', 'label' => 'Inicio', 'icon' => 'home'],
    ],
    'Gestión' => [
        ['key' => 'socios', 'label' => 'Apoderados', 'icon' => 'users'],
        ['key' => 'deportistas', 'label' => 'Deportistas', 'icon' => 'user-check'],
        ['key' => 'coaches', 'label' => 'Coaches', 'icon' => 'users-gear'],
        ['key' => 'clases', 'label' => 'Clases', 'icon' => 'calendar'],
        ['key' => 'asistencia', 'label' => 'Asistencia', 'icon' => 'checklist'],
    ],
    'Operación' => [
        ['key' => 'modalidades', 'label' => 'Modalidades', 'icon' => 'layers'],
        ['key' => 'inscripciones', 'label' => 'Inscripciones', 'icon' => 'clipboard'],
        ['key' => 'competencias', 'label' => 'Competencias', 'icon' => 'medal'],
        ['key' => 'certificados', 'label' => 'Certificados', 'icon' => 'badge'],
        ['key' => 'cuotas', 'label' => 'Cuotas socios', 'icon' => 'receipt'],
    ],
    'Finanzas' => [
        ['key' => 'pagos', 'label' => 'Pagos', 'icon' => 'wallet'],
        ['key' => 'transferencias', 'label' => 'Transferencias', 'icon' => 'arrows'],
    ],
    'Soporte' => [
        ['key' => 'reportes', 'label' => 'Reportes', 'icon' => 'chart'],
        ['key' => 'eventos', 'label' => 'Eventos federados', 'icon' => 'spark'],
    ],
];

$navIcons = [
    'home' => 'home',
    'users' => 'groups',
    'user-check' => 'person_check',
    'users-gear' => 'manage_accounts',
    'calendar' => 'calendar_month',
    'checklist' => 'checklist',
    'layers' => 'layers',
    'clipboard' => 'assignment',
    'medal' => 'workspace_premium',
    'badge' => 'badge',
    'receipt' => 'receipt_long',
    'wallet' => 'account_balance_wallet',
    'arrows' => 'swap_horiz',
    'chart' => 'query_stats',
    'spark' => 'event',
];

$renderIcon = static function (string $key) use ($navIcons): string {
    return $navIcons[$key] ?? $navIcons['home'];
};
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e($baseUrl) ?>/assets/css/app.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" aria-label="Menú lateral">
            <div class="sidebar-top">
                <div class="brand">
                    <img class="brand-logo" src="<?= e($baseUrl) ?>/assets/img/Logo principal.png" alt="Logo Club MaiTeam">
                    <div class="brand-copy">
                        <p class="brand-title">Club MaiTeam</p>
                        <p class="brand-subtitle">Gestion interna</p>
                    </div>
                </div>
                <button class="sidebar-toggle" type="button" aria-expanded="true" aria-controls="sidebar-nav" data-sidebar-toggle>
                    <span class="sidebar-toggle-icon" aria-hidden="true">
                        <span class="material-symbols-outlined">menu</span>
                    </span>
                    <span class="sidebar-toggle-text">Menú</span>
                </button>
            </div>

            <nav class="sidebar-nav" id="sidebar-nav" aria-label="Menu principal">
                <?php foreach ($navSections as $sectionLabel => $items): ?>
                    <div class="sidebar-group">
                        <p class="sidebar-group-title"><?= e($sectionLabel) ?></p>
                        <div class="sidebar-links">
                            <?php foreach ($items as $item): ?>
                                <a
                                    class="sidebar-link<?= $item['key'] === ($page ?? '') ? ' is-active' : '' ?>"
                                    href="<?= e($baseUrl) ?>/?page=<?= e($item['key']) ?>"
                                    title="<?= e($item['label']) ?>"
                                    aria-label="<?= e($item['label']) ?>"
                                >
                                    <span class="sidebar-link-icon" aria-hidden="true">
                                        <span class="material-symbols-outlined"><?= e($renderIcon($item['icon'])) ?></span>
                                    </span>
                                    <span class="sidebar-link-text">
                                        <?= e($item['label']) ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-footer">
                <p class="sidebar-footer-text">Club MaiTeam · Sistema interno</p>
            </div>
        </aside>

        <div class="app-content">
            <main class="main-content">
                <?php require $viewPath; ?>
            </main>
        </div>
    </div>

    <script>
    (function () {
        const storageKey = 'maiteam-sidebar-collapsed';
        const body = document.body;
        const button = document.querySelector('[data-sidebar-toggle]');
        if (!button) {
            return;
        }

        const mediaQuery = window.matchMedia('(max-width: 720px)');
        const stored = localStorage.getItem(storageKey);
        const collapsed = mediaQuery.matches ? true : (stored === null ? false : stored === 'true');

        function setCollapsedState(nextCollapsed) {
            body.classList.toggle('sidebar-collapsed', nextCollapsed);
            button.setAttribute('aria-expanded', String(!nextCollapsed));
            localStorage.setItem(storageKey, nextCollapsed ? 'true' : 'false');
        }

        setCollapsedState(collapsed);

        button.addEventListener('click', function () {
            setCollapsedState(!body.classList.contains('sidebar-collapsed'));
        });
    })();
    </script>
</body>
</html>
