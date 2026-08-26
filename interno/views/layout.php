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
                <button class="sidebar-close" type="button" aria-label="Cerrar menú" data-sidebar-close>
                    <span class="sidebar-close-icon" aria-hidden="true">
                        <span class="material-symbols-outlined">close</span>
                    </span>
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

        <div class="sidebar-backdrop" data-sidebar-backdrop hidden></div>

        <div class="app-content">
            <header class="mobile-topbar">
                <button class="mobile-menu-toggle" type="button" aria-expanded="false" aria-controls="sidebar-nav" aria-label="Abrir menú" data-sidebar-toggle>
                    <span class="mobile-menu-toggle-icon" aria-hidden="true">
                        <span class="material-symbols-outlined">menu</span>
                    </span>
                </button>
                <div class="mobile-topbar-copy">
                    <p class="mobile-topbar-kicker">Panel interno</p>
                    <p class="mobile-topbar-title">Club MaiTeam</p>
                </div>
            </header>

            <main class="main-content">
                <?php require $viewPath; ?>
            </main>
        </div>
    </div>

    <script>
    (function () {
        const body = document.body;
        const openButton = document.querySelector('[data-sidebar-toggle]');
        const closeButton = document.querySelector('[data-sidebar-close]');
        const backdrop = document.querySelector('[data-sidebar-backdrop]');
        if (!openButton || !closeButton || !backdrop) {
            return;
        }

        const mediaQuery = window.matchMedia('(max-width: 720px)');
        const mobileLinkSelector = '.sidebar-link';

        function setOpenState(nextOpen) {
            const isMobile = mediaQuery.matches;
            body.classList.toggle('sidebar-open', nextOpen && isMobile);
            openButton.setAttribute('aria-expanded', String(nextOpen && isMobile));
            closeButton.setAttribute('aria-expanded', String(nextOpen && isMobile));
            backdrop.hidden = !(nextOpen && isMobile);
        }

        function syncMode() {
            setOpenState(false);
        }

        openButton.addEventListener('click', function () {
            setOpenState(!body.classList.contains('sidebar-open'));
        });

        closeButton.addEventListener('click', function () {
            setOpenState(false);
        });

        backdrop.addEventListener('click', function () {
            setOpenState(false);
        });

        document.querySelectorAll(mobileLinkSelector).forEach(function (link) {
            link.addEventListener('click', function () {
                if (mediaQuery.matches) {
                    setOpenState(false);
                }
            });
        });

        if (mediaQuery.matches) {
            syncMode();
        }

        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', syncMode);
        } else if (typeof mediaQuery.addListener === 'function') {
            mediaQuery.addListener(syncMode);
        }
    })();
    </script>
</body>
</html>
