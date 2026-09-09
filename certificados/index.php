<?php

declare(strict_types=1);

require __DIR__ . '/../interno/src/bootstrap.php';

$action = $_GET['action'] ?? 'search';
$rut = format_rut($_POST['rut'] ?? $_GET['rut'] ?? '');
$errors = [];
$deportista = null;
$eventosFederados = [];

if ($action === 'preview') {
    $inscripcionId = (int) ($_GET['inscripcion_id'] ?? 0);
    $eventosFederados = certificados_eventos_federados_por_rut($rut);
    foreach ($eventosFederados as $evento) {
        if ((int) ($evento['inscripcion_id'] ?? 0) === $inscripcionId) {
            header('Content-Type: text/html; charset=UTF-8');
            echo certificado_evento_federado_render_html($evento);
            exit;
        }
    }
    http_response_code(404);
    echo 'Certificado no encontrado.';
    exit;
} elseif ($action === 'pdf') {
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
        } elseif ($type === 'evento-federado') {
            $inscripcionId = (int) ($_GET['inscripcion_id'] ?? 0);
            $eventosFederados = certificados_eventos_federados_por_rut($rut);
            $inscripcion = null;
            foreach ($eventosFederados as $evento) {
                if ((int) ($evento['inscripcion_id'] ?? 0) === $inscripcionId) {
                    $inscripcion = $evento;
                    break;
                }
            }
            if ($inscripcion === null) {
                $errors[] = 'El evento federado seleccionado no esta disponible para este deportista.';
            } else {
                certificado_emitir_evento_federado_pdf($inscripcion);
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
            $eventosFederados = certificados_eventos_federados_por_rut($rut);
        }
    }
}

if ($deportista !== null && empty($eventosFederados)) {
    $eventosFederados = certificados_eventos_federados_por_rut($rut);
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
                    <h3>Certificados de eventos federados</h3>
                    <p>Disponibles para los eventos en que la deportista tiene una inscripción vigente.</p>
                </div>

                <?php if (empty($eventosFederados)): ?>
                    <p class="muted">No hay inscripciones en eventos federados disponibles.</p>
                <?php else: ?>
                    <div class="competition-list">
                        <?php foreach ($eventosFederados as $eventoFederado): ?>
                            <article class="competition-item">
                                <div>
                                    <h4><?= e($eventoFederado['evento_nombre']) ?></h4>
                                    <p><?= e(certificado_texto_fechas_competencia($eventoFederado)) ?></p>
                                    <?php if (!empty($eventoFederado['lugar'])): ?>
                                        <p class="place">Lugar: <?= e($eventoFederado['lugar']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <a class="download" href="/certificados/?action=pdf&type=evento-federado&rut=<?= urlencode((string) ($deportista['rut'] ?? '')) ?>&inscripcion_id=<?= (int) $eventoFederado['inscripcion_id'] ?>">
                                    Descargar certificado
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
