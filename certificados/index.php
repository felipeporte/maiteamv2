<?php

declare(strict_types=1);

require __DIR__ . '/../interno/src/bootstrap.php';

$action = $_GET['action'] ?? 'search';
$rut = format_rut($_POST['rut'] ?? $_GET['rut'] ?? '');
$errors = [];
$deportista = null;
$competencias = [];

if ($action === 'pdf') {
    if ($rut === '') {
        $errors[] = 'Debes ingresar un RUT.';
    } elseif (!is_valid_rut($rut)) {
        $errors[] = 'El RUT ingresado no es valido.';
    } else {
        $deportista = deportista_por_rut($rut);
        if ($deportista === null) {
            $errors[] = 'No encontramos un deportista asociado a ese RUT.';
        }
    }

    if (empty($errors) && $deportista !== null) {
        $type = $_GET['type'] ?? 'permanencia';

        if ($type === 'permanencia') {
            certificado_emitir_permanencia_pdf($deportista);
        } elseif ($type === 'competencia') {
            $competenciaId = (int) ($_GET['competencia_id'] ?? 0);
            $competencia = $competenciaId > 0
                ? competencia_find_por_deportista($competenciaId, (int) $deportista['id'])
                : null;

            if ($competencia === null) {
                $errors[] = 'La competencia seleccionada no esta disponible para este deportista.';
            } else {
                certificado_emitir_competencia_pdf($deportista, $competencia);
            }
        } else {
            $errors[] = 'Tipo de certificado no soportado.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($rut === '') {
        $errors[] = 'Debes ingresar un RUT.';
    } elseif (!is_valid_rut($rut)) {
        $errors[] = 'El RUT ingresado no es valido.';
    } else {
        $deportista = deportista_por_rut($rut);
        if ($deportista === null) {
            $errors[] = 'No encontramos un deportista asociado a ese RUT.';
        } else {
            $competencias = competencias_por_deportista((int) $deportista['id']);
        }
    }
}

if ($deportista !== null && empty($competencias)) {
    $competencias = competencias_por_deportista((int) $deportista['id']);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificados Club MaiTeam</title>
    <meta name="description" content="Portal de certificados para apoderados del Club MaiTeam.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700;800&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/certificados/assets/css/app.css">
</head>
<body>
    <div class="bg-shape shape-a" aria-hidden="true"></div>
    <div class="bg-shape shape-b" aria-hidden="true"></div>

    <main class="container">
        <section class="hero reveal">
            <p class="eyebrow">Club MaiTeam</p>
            <h1>Portal de certificados para apoderados</h1>
            <p class="lead">Ingresa el RUT del deportista y descarga certificados PDF para presentar en su establecimiento educacional.</p>
        </section>

        <section class="panel reveal delay-1">
            <h2>Buscar por RUT</h2>
            <form class="search-form" method="post" action="/certificados/">
                <label for="rut">RUT del deportista</label>
                <div class="input-row">
                    <input id="rut" name="rut" type="text" required placeholder="12345678-9" value="<?= e($rut) ?>">
                    <button type="submit">Buscar</button>
                </div>
            </form>

            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($deportista !== null): ?>
            <section class="panel reveal delay-2">
                <h2>Resultado</h2>
                <div class="identity">
                    <p><span>Deportista</span><strong><?= e($deportista['nombre']) ?></strong></p>
                    <p><span>RUT</span><strong><?= e(format_rut($deportista['rut'] ?? '')) ?></strong></p>
                    <p><span>Nivel</span><strong><?= e($deportista['nivel_nombre'] ?? 'Sin nivel asignado') ?></strong></p>
                </div>

                <a class="download main" href="/certificados/?action=pdf&type=permanencia&rut=<?= urlencode((string) ($deportista['rut'] ?? '')) ?>">
                    Descargar certificado de permanencia
                </a>

                <div class="competencias-head">
                    <h3>Justificativo de competencia por nivel</h3>
                    <p>Disponible cuando hay competencias registradas para el nivel del deportista.</p>
                </div>

                <?php if (empty($competencias)): ?>
                    <p class="muted">No hay competencias registradas para el nivel de este deportista.</p>
                <?php else: ?>
                    <div class="competition-list">
                        <?php foreach ($competencias as $competencia): ?>
                            <article class="competition-item">
                                <div>
                                    <h4><?= e($competencia['nombre']) ?></h4>
                                    <p><?= e(certificado_texto_fechas_competencia($competencia)) ?></p>
                                    <?php if (!empty($competencia['lugar'])): ?>
                                        <p class="place">Lugar: <?= e($competencia['lugar']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <a class="download" href="/certificados/?action=pdf&type=competencia&rut=<?= urlencode((string) ($deportista['rut'] ?? '')) ?>&competencia_id=<?= (int) $competencia['id'] ?>">
                                    Descargar PDF
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
