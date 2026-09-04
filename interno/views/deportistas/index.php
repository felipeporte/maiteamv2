<?php
/** @var array $deportistas */
/** @var string|null $flash */
/** @var string $search */

$avatarPalettes = [
    ['start' => '#1f5fe0', 'end' => '#153a8a'],
    ['start' => '#0f766e', 'end' => '#155e75'],
    ['start' => '#c2410c', 'end' => '#9a3412'],
    ['start' => '#7c3aed', 'end' => '#5b21b6'],
    ['start' => '#be185d', 'end' => '#9d174d'],
    ['start' => '#047857', 'end' => '#065f46'],
];

$avatarInitials = static function (string $name): string {
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
};
?>
<section class="page">
    <div class="page-header">
        <div>
            <h1>Deportistas</h1>
            <p>Gestion de deportistas asociados a apoderados.</p>
        </div>
        <a class="button" href="<?= e(base_url('/?page=deportistas&action=create')) ?>">Nuevo deportista</a>
    </div>

    <?php if ($flash === 'created'): ?>
        <div class="alert success">Deportista creado correctamente.</div>
    <?php elseif ($flash === 'updated'): ?>
        <div class="alert success">Deportista actualizado.</div>
    <?php elseif ($flash === 'deleted'): ?>
        <div class="alert">Deportista eliminado.</div>
    <?php endif; ?>

    <form class="deportistas-search" method="get" action="<?= e(base_url('/')) ?>" role="search">
        <input type="hidden" name="page" value="deportistas">
        <label class="sr-only" for="deportistas-search-input">Buscar deportista</label>
        <span class="material-symbols-outlined deportistas-search-icon" aria-hidden="true">search</span>
        <input
            id="deportistas-search-input"
            type="search"
            name="q"
            value="<?= e($search) ?>"
            placeholder="Buscar por nombre o RUT"
            autocomplete="off"
        >
        <?php if ($search !== ''): ?>
            <a class="deportistas-search-clear" href="<?= e(base_url('/?page=deportistas')) ?>">Limpiar</a>
        <?php endif; ?>
        <button class="button" type="submit">Buscar</button>
    </form>

    <?php if (empty($deportistas)): ?>
        <div class="deportistas-empty">
            <?php if ($search !== ''): ?>
                <p>No encontramos deportistas para "<?= e($search) ?>".</p>
            <?php else: ?>
                <p>No hay deportistas registrados todavia.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="deportistas-grid">
            <?php foreach ($deportistas as $deportista): ?>
                <?php
                $displayName = trim((string) ($deportista['nombre'] ?? ''));
                $rut = trim((string) ($deportista['rut'] ?? ''));
                $avatarUrl = deportista_avatar_public_url($deportista['avatar_path'] ?? null);
                $initials = $avatarInitials($displayName);
                $palette = $avatarPalettes[((int) $deportista['id']) % count($avatarPalettes)];
                $avatarStyle = sprintf(
                    '--avatar-start:%s;--avatar-end:%s;',
                    $palette['start'],
                    $palette['end']
                );
                ?>
                <article class="deportista-card">
                    <div class="deportista-card-media">
                        <div class="deportista-avatar" style="<?= e($avatarStyle) ?>">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= e($avatarUrl) ?>" alt="Avatar de <?= e($displayName !== '' ? $displayName : 'deportista') ?>">
                            <?php else: ?>
                                <span><?= e($initials) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($deportista['edad_competencia'] !== null): ?>
                            <span class="chip"><?= e((string) $deportista['edad_competencia']) ?> años</span>
                        <?php else: ?>
                            <span class="chip muted">Sin edad</span>
                        <?php endif; ?>
                    </div>

                    <div class="deportista-card-body">
                        <h2 class="deportista-card-title"><?= e($displayName !== '' ? $displayName : 'Sin nombre') ?></h2>
                        <p class="deportista-card-subtitle"><?= e($rut !== '' ? $rut : 'Sin RUT') ?></p>
                    </div>

                    <div class="deportista-card-actions">
                        <a class="button ghost" href="<?= e(base_url('/?page=deportistas&action=edit&id=' . $deportista['id'])) ?>">Ficha</a>
                        <form method="post" action="<?= e(base_url('/?page=deportistas&action=delete')) ?>" onsubmit="return confirm('Eliminar este deportista?');">
                            <input type="hidden" name="id" value="<?= e((string) $deportista['id']) ?>">
                            <button type="submit" class="button icon danger" aria-label="Eliminar deportista">
                                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
