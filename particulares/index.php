<?php

declare(strict_types=1);
require __DIR__ . '/../interno/src/bootstrap.php';
$fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['fecha'] ?? '')) ? $_GET['fecha'] : date('Y-m-d');
$error = null;
$confirmado = null;
$medios = db()->query('SELECT * FROM particular_medios_pago WHERE activo=1 ORDER BY nombre')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reserve') {
    try {
        $confirmado = particulares_reservar_publico((int)$_POST['bloque_id'], trim($_POST['cliente_nombre']), trim($_POST['email']), trim($_POST['telefono'] ?? ''), trim($_POST['deportista_nombre']));
        $base = (int)db()->query('SELECT valor_base FROM particular_bloques WHERE id=' . (int)$_POST['bloque_id'])->fetchColumn();
        $medio = (int)$_POST['medio_pago_id'];
        $cot = particulares_cotizar($base, $medio);
        $pago = particulares_crear_pago($confirmado, $medio, $cot);
        if ($medio !== 1) {
            header('Location: ' . mercadopago_crear_preferencia_v2($pago, 'Clase particular MaiTeam', (int)$cot['charged_amount'], 'particular_reserva_' . $confirmado));
            exit;
        }
        db()->prepare("UPDATE particular_reservas SET estado='awaiting_payment' WHERE id=:id")->execute(['id' => $confirmado]);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
$agenda = particulares_agenda($fecha);
$diasDisponibles = db()->query("SELECT DISTINCT fecha FROM particular_bloques WHERE activo=1 AND fecha>=CURDATE() ORDER BY fecha LIMIT 90")->fetchAll(PDO::FETCH_COLUMN);
$diasEs = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
if (!empty($_GET['external_reference']) && preg_match('/^particular_reserva_(\d+)$/', (string)$_GET['external_reference'], $rm)) {
    $sr=db()->prepare("SELECT r.id,r.estado,p.charged_amount,p.status,b.fecha,b.inicio,b.fin,m.nombre monitor,d.nombre deportista FROM particular_reservas r JOIN particular_pagos p ON p.reserva_id=r.id JOIN particular_bloques b ON b.id=r.bloque_id JOIN particular_monitores m ON m.id=b.monitor_id JOIN particular_deportistas d ON d.id=r.deportista_id WHERE r.id=:id ORDER BY p.id DESC LIMIT 1"); $sr->execute(['id'=>(int)$rm[1]]); $confirm=$sr->fetch();
    if ($confirm) { $estadoTexto=$confirm['estado']==='confirmed'?'¡Reserva confirmada!':'Pago recibido'; ?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=$estadoTexto?> | MaiTeam</title><link rel="stylesheet" href="/particulares/particulares.css"></head><body><nav><a class="brand" href="/"><img src="/interno/assets/img/Logo principal.png" alt="Club MaiTeam"><span>Club MaiTeam</span></a><a class="back" href="/particulares/">Agendar otra clase</a></nav><main class="confirmation"><section class="card"><div class="success-icon">✓</div><h1><?=$estadoTexto?></h1><p><?= $confirm['estado']==='confirmed'?'Tu clase ha sido agendada correctamente.':'Estamos validando el pago mediante Mercado Pago.' ?></p><div class="summary"><p><b>Fecha:</b> <?=htmlspecialchars(date('d/m/Y',strtotime($confirm['fecha'])))?></p><p><b>Horario:</b> <?=htmlspecialchars(substr($confirm['inicio'],11,5).' - '.substr($confirm['fin'],11,5))?></p><p><b>Monitor:</b> <?=htmlspecialchars($confirm['monitor'])?></p><p><b>Deportista:</b> <?=htmlspecialchars($confirm['deportista'])?></p><p><b>Monto pagado:</b> $<?=number_format((float)$confirm['charged_amount'],0,',','.')?></p><p><b>Estado:</b> <?=htmlspecialchars($confirm['status'])?></p></div><a class="button" href="/particulares/">Agendar otra clase</a></section></main></body></html><?php exit; }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Clases particulares | MaiTeam</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/particulares/particulares.css">
</head>

<body>
    <nav><a class="brand" href="/"><img src="/interno/assets/img/Logo principal.png" alt="Club MaiTeam"><span>Club MaiTeam</span></a><span class="section-name">Clases particulares</span><a class="back" href="/">Volver al portal</a></nav>
    <header>
        <div>
            <h1>Clases particulares</h1>
            <p>Reserva clases personalizadas con nuestros monitores.</p>
            <div class="steps"><span>◉ Elige fecha y horario</span><span>◉ Selecciona tu monitor</span><span>◉ Confirma y paga</span></div>
        </div>
    </header>
    <main class="wizard-step-<?=!empty($_GET["bloque_id"])?3:(!empty($agenda)?2:1)?>">
        <section class="card step-card step-1 <?=!empty($agenda)?"completed":""?>">
            <h2><b>1</b> Selecciona la fecha</h2>
            <p>Elige el día en que quieres la clase particular.</p>
            
            <div class="available-days"><strong>Días con clases disponibles</strong>
                    <div><?php foreach ($diasDisponibles as $dia): ?><a class="available-day <?= ($dia === $fecha ? 'selected' : '') ?>" href="?fecha=<?= urlencode($dia) ?>"><?= htmlspecialchars(substr($diasEs[date('l', strtotime($dia))], 0, 3) . ' ' . date('d/m', strtotime($dia))) ?></a><?php endforeach; ?></div>
                </div>
            </div>
        </section>
        <section class="card step-card step-2 <?=empty($agenda)||!empty($_GET["bloque_id"])?"locked":""?>">
            <h2><b>2</b> Selecciona horario y monitor</h2><a class="date-change" href="/particulares/">← Seleccionar otra fecha</a><?php if (!$agenda): ?><p>No hay bloques publicados para esta fecha.</p><?php endif; ?><?php foreach ($agenda as $b): ?><div class="slot"><strong><?= htmlspecialchars(substr($b['inicio'], 11, 5)) ?> - <?= htmlspecialchars(substr($b['fin'], 11, 5)) ?></strong><?php if (empty($b['reserva_id'])): ?><span><?= htmlspecialchars($b['monitor']) ?></span><a href="?fecha=<?= urlencode($fecha) ?>&bloque_id=<?= $b['id'] ?>">Elegir</a><?php else: ?><em>Reservado · <?= htmlspecialchars($b['monitor']) ?></em><?php endif; ?></div><?php endforeach; ?>
        </section>
        <section class="card step-card step-3 <?=empty($_GET["bloque_id"])?"locked":""?>">
            <h2><b>3</b> Datos y medio de pago</h2><?php if ($error): ?><p><?= htmlspecialchars($error) ?></p><?php elseif ($confirmado): ?><h3>Reserva #<?= $confirmado ?> creada</h3>
                <p>Pago pendiente de completar.</p><?php if ((int)($_POST['medio_pago_id'] ?? 0) === 1): ?><div class="transfer-info"><strong>Transferencia bancaria</strong>
                        <p>Realiza la transferencia indicando como referencia <b>Reserva #<?= $confirmado ?></b>.</p>
                        <p>Titular: <?= htmlspecialchars((string)env('PARTICULARES_TRANSFER_HOLDER', 'Pendiente de configurar')) ?><br>Banco: <?= htmlspecialchars((string)env('PARTICULARES_TRANSFER_BANK', 'Pendiente de configurar')) ?><br>Cuenta: <?= htmlspecialchars((string)env('PARTICULARES_TRANSFER_ACCOUNT', 'Pendiente de configurar')) ?><br>RUT: <?= htmlspecialchars((string)env('PARTICULARES_TRANSFER_RUT', 'Pendiente de configurar')) ?></p>
                        <p>Envía el comprobante a <?= htmlspecialchars((string)env('PARTICULARES_TRANSFER_EMAIL', 'pagos@maiteam.cl')) ?>.</p>
                    </div><?php endif; ?><?php else: ?><form method="post"><input type="hidden" name="action" value="reserve"><input type="hidden" name="bloque_id" value="<?= htmlspecialchars((string)($_GET['bloque_id'] ?? '')) ?>"><input name="cliente_nombre" placeholder="Nombre apoderado" required><input type="email" name="email" placeholder="Email" required><input name="deportista_nombre" placeholder="Nombre deportista" required>
                        <h3>Forma de pago</h3><?php foreach ($medios as $m): ?><label><input type="radio" name="medio_pago_id" value="<?= $m['id'] ?>" required> <?= htmlspecialchars($m['nombre']) ?></label><?php endforeach; ?><button>Reservar y pagar</button>
                    </form><?php endif; ?>
        </section>
    </main>
</body>

</html>
