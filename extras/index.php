<?php
declare(strict_types=1);
require __DIR__ . '/../interno/src/bootstrap.php';

$niveles = ['Todas', 'Formativo', 'Escuela', 'Promotional', 'Internacional'];
$fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['fecha'] ?? '')) ? (string) $_GET['fecha'] : date('Y-m-d');
$nivel = trim((string) ($_GET['nivel'] ?? ''));
$coachId = (int) ($_GET['coach_id'] ?? 0);
$step = max(1, min(2, (int) ($_GET['step'] ?? 1)));
$errors = [];
$flash = in_array($_GET['flash'] ?? '', ['created', 'closed'], true) ? (string) $_GET['flash'] : null;
$showSessions = ($_GET['sesiones'] ?? '') === '1';
$sessionErrors = [];
$coaches = coaches_options();
$deportistas = $nivel !== '' && in_array($nivel, $niveles, true) ? deportistas_nivel_extra_options($nivel) : [];
$sesionesAbiertas = clases_extras_abiertas_all();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'close_session') {
    try {
        $closeId = (int) ($_POST['clase_extra_id'] ?? 0);
        $courtCost = (float) ($_POST['costo_pista'] ?? -1);
        if ($courtCost < 0) throw new InvalidArgumentException('Ingresa el valor de la cancha antes de cerrar.');
        clase_extra_cerrar($closeId, $courtCost);
        header('Location: /extras/?flash=closed'); exit;
    } catch (Throwable $e) { $sessionErrors[] = $e->getMessage(); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_session') {
    $nivel = trim((string) ($_POST['nivel'] ?? ''));
    $fecha = trim((string) ($_POST['fecha'] ?? date('Y-m-d')));
    $coachId = (int) ($_POST['coach_id'] ?? 0);
    $ids = (array) ($_POST['deportistas'] ?? []);
    if (!in_array($nivel, $niveles, true)) $errors[] = 'Selecciona un nivel válido.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) $errors[] = 'Selecciona una fecha válida.';
    if ($coachId <= 0) $errors[] = 'Selecciona una entrenadora.';
    if (count(array_filter(array_map('intval', $ids))) === 0) $errors[] = 'Selecciona al menos una deportista.';
    if (!$errors) {
        try {
            clases_extras_create(['evento_federado_id' => 0, 'coach_id' => $coachId, 'fecha' => $fecha, 'duracion_min' => (int) ($_POST['duracion_min'] ?? 60), 'valor_clase' => 10000, 'costo_pista' => (float) ($_POST['costo_pista'] ?? 0), 'estado' => 'programada', 'notas' => trim((string) ($_POST['notas'] ?? ''))], $ids);
            header('Location: /extras/?fecha=' . urlencode($fecha) . '&flash=created'); exit;
        } catch (Throwable $e) { $errors[] = $e->getMessage(); }
    }
    $step = 2;
    $deportistas = deportistas_nivel_extra_options($nivel);
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Generar clases extras | MaiTeam</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="/particulares/particulares.css"><link rel="stylesheet" href="/extras/extras.css"></head>
<body><nav><a class="brand" href="/"><img src="/interno/assets/img/Logo principal.png" alt="Club MaiTeam"><span>Club MaiTeam</span></a><span class="section-name">Clases extras</span><a class="switch-link" href="/particulares/">Particulares</a><a class="back" href="/">Volver al portal</a></nav>
<header><div><h1>Generar clase extra</h1></div><div class="header-actions"><a class="header-action <?= !$showSessions ? 'active' : '' ?>" href="/extras/">Nueva sesión</a><a class="header-action <?= $showSessions ? 'active' : '' ?>" href="?sesiones=1">Ver sesiones</a></div></header>
<?php if ($showSessions): ?><section class="open-sessions"><div class="open-sessions-inner"><div class="section-toolbar"><h2>Sesiones abiertas</h2></div><?php if ($sessionErrors): ?><div class="form-error"><?php foreach ($sessionErrors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?></div><?php endif; ?><?php if (!$sesionesAbiertas): ?><p class="empty">No hay sesiones abiertas.</p><?php endif; ?><?php foreach ($sesionesAbiertas as $session): ?><article class="open-session-card"><div><strong><?= htmlspecialchars($session['fecha']) ?> · <?= htmlspecialchars($session['coach_nombre']) ?></strong><p><?= htmlspecialchars($session['evento_federado_nivel'] ?? 'Clase extra') ?> · <?= (int)$session['participantes'] ?> deportistas · $10.000 por clase</p></div><form method="post" class="close-session-form"><input type="hidden" name="action" value="close_session"><input type="hidden" name="clase_extra_id" value="<?= (int)$session['id'] ?>"><label>Valor cancha<input type="number" name="costo_pista" min="0" step="0.01" required placeholder="$"></label><button class="button close-button" type="submit">Cerrar sesión</button></form></article><?php endforeach; ?></div></section><?php endif; ?>
<main class="extras-main wizard-main <?= $step === 2 ? 'step-two-active' : '' ?> <?= $showSessions ? 'sessions-hidden' : '' ?>">
<?php if ($flash === 'created'): ?><div class="attendance-flash">Sesión creada correctamente. Ya quedó registrada en Clases.</div><?php endif; ?><?php if ($errors): ?><div class="form-error"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($step === 1): ?><section class="card wizard-card"><h2><b>1</b> Configura la sesión</h2><form method="get" class="wizard-form"><label>Nivel<select name="nivel" required><option value="">Selecciona nivel</option><?php foreach ($niveles as $opcion): ?><option value="<?= htmlspecialchars($opcion) ?>" <?= $nivel === $opcion ? 'selected' : '' ?>><?= htmlspecialchars($opcion) ?></option><?php endforeach; ?></select></label><label>Fecha<input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" required></label><div><span class="field-label">Entrenadora</span><input type="hidden" name="coach_id" id="coach-id" value="<?= $coachId ?>"><div class="coach-options"><?php foreach ($coaches as $coach): ?><button type="button" class="coach-option <?= $coachId === (int)$coach['id'] ? 'selected' : '' ?>" data-coach-id="<?= (int)$coach['id'] ?>"><?= htmlspecialchars($coach['nombre']) ?></button><?php endforeach; ?></div></div><button class="next-button" type="submit" name="step" value="2">Siguiente <span>→</span></button></form></section><?php endif; ?>
<?php if ($step === 2): ?><section class="card wizard-card step-two"><h2><b>2</b> Selecciona deportistas</h2><p><?= htmlspecialchars($nivel) ?> · <?= htmlspecialchars($fecha) ?></p><form method="post" class="wizard-form"><input type="hidden" name="action" value="create_session"><input type="hidden" name="nivel" value="<?= htmlspecialchars($nivel) ?>"><input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>"><input type="hidden" name="coach_id" value="<?= $coachId ?>"><div class="athlete-card"><div class="athlete-card-header"><strong>Deportistas disponibles</strong><span><?= count($deportistas) ?> niñas</span></div><?php if (!$deportistas): ?><p class="empty">No hay deportistas registradas en este nivel.</p><?php else: foreach ($deportistas as $deportista): ?><label class="athlete-option"><input type="checkbox" name="deportistas[]" value="<?= (int)$deportista['id'] ?>"><span><?= htmlspecialchars($deportista['nombre']) ?><small><?= htmlspecialchars($deportista['apoderado_nombre']) ?></small></span></label><?php endforeach; endif; ?></div><div class="create-grid"><label>Duración<input type="number" name="duracion_min" min="0" value="60"></label><label>Pista total<input type="number" name="costo_pista" min="0" step="0.01" value="0"></label></div><div class="fixed-price">Valor fijo por deportista: <strong>$10.000</strong></div><label>Notas<input type="text" name="notas"></label><button class="button create-button" type="submit">Crear sesión</button></form></section><?php endif; ?>
</main><script>document.querySelectorAll('[data-coach-id]').forEach(function(button){button.addEventListener('click',function(){document.getElementById('coach-id').value=this.dataset.coachId;document.querySelectorAll('[data-coach-id]').forEach(function(item){item.classList.remove('selected')});this.classList.add('selected')})});</script></body></html>
