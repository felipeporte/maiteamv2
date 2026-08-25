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
    'home' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11.5 12 4l8 7.5V20a1 1 0 0 1-1 1h-4.5v-6h-5v6H5a1 1 0 0 1-1-1z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>',
    'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16.5 21v-2a4.5 4.5 0 0 0-4.5-4.5H7a4.5 4.5 0 0 0-4.5 4.5v2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="9.5" cy="7" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M20.5 21v-1.2a4 4 0 0 0-2.7-3.8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M14.5 4.7a3 3 0 0 1 0 5.7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'user-check' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="7" r="3.2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M3.8 20a6.2 6.2 0 0 1 12.4 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="m14.8 12.8 1.8 1.8 3.7-3.7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'users-gear' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16.4 17.4a2.2 2.2 0 1 0 4.4 0 2.2 2.2 0 0 0-4.4 0Z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m18.6 13.6.3-1.1m0 4.8-.3-1.1m2.3-2.3h-1.1m-4.8 0h1.1m3.2-3-.8.8m-3.4 3.4-.8.8m0-5 1.1.3m4.8 0-1.1.3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><circle cx="8.5" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M2.8 20a5.7 5.7 0 0 1 11.4 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="15" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M7 3.5v4M17 3.5v4M3.5 10h17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'checklist' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h2M4 12h2M4 18h2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="m8 6 1 1 2-2M8 12l1 1 2-2M8 18l1 1 2-2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 6h6M14 12h6M14 18h6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'layers' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 4 8 4-8 4-8-4 8-4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m4 12 8 4 8-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m4 16 8 4 8-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>',
    'clipboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4.5" width="12" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9 4.5a3 3 0 0 1 6 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M9 10h6M9 14h6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'medal' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="10.5" r="5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m9 3.5 1.8 4m4.2-4-1.8 4M10 14.8 8.5 20l3.5-2 3.5 2-1.5-5.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'badge' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.5 16.8 6v5.2c0 3.1-2.1 6.2-4.8 7.3-2.7-1.1-4.8-4.2-4.8-7.3V6L12 3.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m9.5 11.2 1.8 1.8 3.2-3.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'receipt' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3.8h12v16l-2-1.2-2 1.2-2-1.2-2 1.2-2-1.2-2 1.2z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M8 8h8M8 12h8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'wallet' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.5 7.5h15A2.5 2.5 0 0 1 22 10v6.5A2.5 2.5 0 0 1 19.5 19h-15A2.5 2.5 0 0 1 2 16.5V10a2.5 2.5 0 0 1 2.5-2.5Z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M16 13.5h4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4.8 7.5V6.2A2.2 2.2 0 0 1 7 4h10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'arrows' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h11l-3-3M7 7l3 3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 17H6l3 3M17 17l-3-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'chart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M7 16v-4M12 16V8M17 16v-7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M6 16.5 10.5 12l3 2.5 4.5-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'spark' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 1.6 5.4L19 9l-5.4 1.6L12 16l-1.6-5.4L5 9l5.4-1.6L12 2Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m18.5 14 1 3.2L23 18l-3.5 1-1 3.2-1-3.2L14 18l3.5-0.8L18.5 14Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',
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
                        <svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
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
                                        <?= $renderIcon($item['icon']) ?>
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
