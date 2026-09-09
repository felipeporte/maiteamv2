<?php

declare(strict_types=1);

$escape = static fn ($value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );


/*
|--------------------------------------------------------------------------
| Datos competencia
|--------------------------------------------------------------------------
*/

$fechaInicio = trim((string) ($inscripcion['fecha_inicio'] ?? ''));
$fechaFin = trim((string) ($inscripcion['fecha_fin'] ?? ''));

$fechas = certificado_texto_fechas_competencia([
    'fecha_inicio' => $fechaInicio,
    'fecha_fin' => $fechaFin,
]);


/*
|--------------------------------------------------------------------------
| Logo
|--------------------------------------------------------------------------
*/

$logoUri = '';

$signatureLogoUri = '';
$headCoachUri = '';
$assistantCoachUri = '';

if (is_file($logoPath)) {
    $logoUri =
        'data:image/png;base64,' .
        base64_encode(
            (string) file_get_contents($logoPath)
        );
}

$signatureLogoPath = dirname(__DIR__, 2) . '/assets/img/marcacentral.png';
if (is_file($signatureLogoPath)) {
    $signatureLogoUri =
        'data:image/png;base64,' .
        base64_encode((string) file_get_contents($signatureLogoPath));
}

$headCoachPath = dirname(__DIR__, 2) . '/assets/img/headcoach.png';
if (is_file($headCoachPath)) {
    $headCoachUri =
        'data:image/png;base64,' .
        base64_encode((string) file_get_contents($headCoachPath));
}

$assistantCoachPath = dirname(__DIR__, 2) . '/assets/img/coach_coreographer.png';
if (is_file($assistantCoachPath)) {
    $assistantCoachUri =
        'data:image/png;base64,' .
        base64_encode((string) file_get_contents($assistantCoachPath));
}


/*
|--------------------------------------------------------------------------
| Datos deportista
|--------------------------------------------------------------------------
*/

$nombreDeportista = trim(
    (string) ($inscripcion['deportista_nombre'] ?? '')
);

$rut = certificado_formatear_rut_documento(
    (string) ($inscripcion['rut'] ?? '')
);

$evento = trim(
    (string) ($inscripcion['evento_nombre'] ?? '')
);

$nivel = trim(
    (string) ($inscripcion['evento_nivel'] ?? '')
);

$modalidad = trim(
    (string) ($inscripcion['modalidad_nombre'] ?? '')
);

$subnivel = trim(
    (string) ($inscripcion['subnivel'] ?? '')
);

$categoria = trim(
    (string) ($inscripcion['categoria'] ?? '')
);

$lugar = trim(
    (string) ($inscripcion['lugar'] ?? '')
);


/*
|--------------------------------------------------------------------------
| Fecha emisión
|--------------------------------------------------------------------------
*/

$fechaEmision = certificado_fecha_carta(
    date('Y-m-d')
);


/*
|--------------------------------------------------------------------------
| Folio del justificativo
|--------------------------------------------------------------------------
|
| Dos dígitos para el evento + dos dígitos para la deportista.
| Ejemplo: evento 01 y deportista 23 => JUS-2026-0123.
|
*/

$eventoId = max(0, (int) ($inscripcion['evento_id'] ?? 0));
$deportistaId = max(0, (int) ($inscripcion['deportista_id'] ?? 0));

$folio = sprintf(
    'JUS-%s-%s%s',
    date('Y'),
    str_pad((string) $eventoId, 2, '0', STR_PAD_LEFT),
    str_pad((string) $deportistaId, 2, '0', STR_PAD_LEFT)
);

?>

<!doctype html>

<html lang="es">

<head>

<meta charset="utf-8">

<style>

/*
|--------------------------------------------------------------------------
| PÁGINA
|--------------------------------------------------------------------------
*/

@page {
    size: letter portrait;
    margin: 0;
}

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;

    width: 8.5in;
    height: auto;

    background: #ffffff;

    color: #292929;

    font-family: DejaVu Sans, sans-serif;
}

.page {
    position: relative;

    width: auto;
    height: 9.2in;

    padding:
        .24in
        .66in
        .62in;

    overflow: visible;
}


/*
|--------------------------------------------------------------------------
| COLORES INSTITUCIONALES
|--------------------------------------------------------------------------
|
| Gris:
| #a7a7a7
|
| Rosa:
| #e58894
|
*/

.top-grey {
    position: absolute;

    top: 0;
    left: 0;

    width: 8.5in;
    height: .07in;

    background: #a7a7a7;
}

.top-pink {
    position: absolute;

    top: 0;
    left: .72in;

    width: 6.55in;
    height: .07in;

    background: #e58894;
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.document-header {
    position: relative;

    width: 100%;

    z-index: 2;
}


/*
|--------------------------------------------------------------------------
| HEADER TABLE
|--------------------------------------------------------------------------
*/

.header-table {
    width: 100%;

    border-collapse: collapse;
}

.header-table td {
    vertical-align: middle;
}


/*
|--------------------------------------------------------------------------
| BRAND
|--------------------------------------------------------------------------
*/

.brand-cell {
    width: 58%;
}

.header-logo {
    display: block;

    width: 65px;
    height: 65px;

    object-fit: contain;

    vertical-align: middle;
}

.brand-copy {
    display: inline-block;

    margin-left: 14px;

    vertical-align: middle;
}

.brand-name {
    display: block;

    font-size: 16px;

    font-weight: bold;

    letter-spacing: 2.2px;

    color: #303030;
}

.brand-subtitle {
    display: block;

    margin-top: 4px;

    font-size: 7px;

    text-transform: uppercase;

    letter-spacing: 2.5px;

    color: #8f8f8f;
}


/*
|--------------------------------------------------------------------------
| CONTACTO
|--------------------------------------------------------------------------
*/

.contact-cell {
    width: 42%;

    text-align: left;

    font-size: 8px;

    line-height: 1.85;

    color: #555555;
}

.contact-row {
    white-space: nowrap;
}

/*
|--------------------------------------------------------------------------
| META HEADER
|--------------------------------------------------------------------------
*/

.document-meta {
    width: 100%;

    margin-top: 17px;

    padding-top: 14px;

    border-top: 1px solid #d9d9d9;

    border-collapse: separate;
}

.document-meta td {
    vertical-align: top;
}

.document-type {
    display: block;

    font-size: 7px;

    font-weight: bold;

    letter-spacing: 2.2px;

    text-transform: uppercase;

    color: #969696;
}

.document-mark {
    width: 31px;
    height: 3px;

    margin-top: 6px;

    background: #e58894;
}

.document-date {
    text-align: right;

    font-size: 9px;

    color: #555555;
}


/*
|--------------------------------------------------------------------------
| TÍTULO
|--------------------------------------------------------------------------
*/

.title-area {
    margin-top: 18px;

    text-align: center;
}

h1 {
    margin: 0;

    font-size: 27px;

    font-weight: 600;

    letter-spacing: 5px;

    color: #282828;
}

.title-accent {
    width: 36px;
    height: 3px;

    margin:
        12px
        auto
        0;

    background: #e58894;
}


/*
|--------------------------------------------------------------------------
| CONTENIDO
|--------------------------------------------------------------------------
*/

.content {
    margin-top: 24px;
}

.recipient {
    margin-bottom: 22px;

    font-size: 11px;

    font-weight: bold;

    letter-spacing: 1.5px;

    text-transform: uppercase;

    color: #383838;
}

p {
    margin:
        0
        0
        17px;

    font-size: 12.5px;

    line-height: 1.55;

    text-align: justify;

    color: #3e3e3e;
}


/*
|--------------------------------------------------------------------------
| BLOQUE DEPORTISTA
|--------------------------------------------------------------------------
*/

.athlete {
    margin:
        18px
        0
        20px;

    padding:
        12px
        15px;

    border-left: 4px solid #e58894;

    background: #f8f8f8;
}

.athlete-name {
    font-size: 16px;

    font-weight: bold;

    letter-spacing: .2px;

    color: #292929;
}

.athlete-meta {
    margin-top: 5px;

    font-size: 9px;

    font-weight: bold;

    text-transform: uppercase;

    letter-spacing: 1.1px;

    color: #8b8b8b;
}


/*
|--------------------------------------------------------------------------
| COMPETENCIA
|--------------------------------------------------------------------------
*/

.event-intro {
    margin-bottom: 8px;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 1.4px;

    color: #909090;
}


/*
|--------------------------------------------------------------------------
| TABLA EVENTO
|--------------------------------------------------------------------------
*/

.event-table {
    width: 100%;

    margin:
        7px
        0
        19px;

    border-collapse: collapse;

    table-layout: fixed;
}

.event-table td {
    padding:
        10px
        11px;

    background: #f7f7f7;

    border-right: 4px solid #ffffff;

    vertical-align: top;
}

.event-table td:last-child {
    border-right: 0;
}

.event-table .competition {
    width: 40%;
}

.event-table .date-info {
    width: 30%;

    padding-left: 9px;
    padding-right: 9px;
}

.event-table .place-info {
    width: 30%;
}

.data-label {
    display: block;

    margin-bottom: 5px;

    font-size: 8px;

    font-weight: bold;

    letter-spacing: 1.1px;

    text-transform: uppercase;

    color: #e58894;
}

.data-value {
    display: block;

    font-size: 9.5px;

    font-weight: bold;

    line-height: 1.4;

    color: #414141;
}

.date-value {
    line-height: 1.4;
}


/*
|--------------------------------------------------------------------------
| DETALLES DEPORTIVOS
|--------------------------------------------------------------------------
*/

.sport-table {
    width: 100%;

    margin:
        0
        0
        20px;

    border-collapse: collapse;
}

.sport-table td {
    padding:
        7px
        10px;

    border-bottom: 1px solid #eeeeee;

    font-size: 9.5px;

    color: #444444;
}

.sport-label {
    width: 25%;

    font-size: 8px !important;

    font-weight: bold;

    text-transform: uppercase;

    letter-spacing: 1px;

    color: #969696 !important;
}


/*
|--------------------------------------------------------------------------
| CIERRE
|--------------------------------------------------------------------------
*/

.closing {
    margin-top: 20px;
}

.goodbye {
    margin-top: 19px;

    font-size: 12px;

    color: #3f3f3f;
}


/*
|--------------------------------------------------------------------------
| FIRMAS
|--------------------------------------------------------------------------
*/

.signatures {
    position: absolute;

    left: .66in;
    right: .66in;

    top: 9.58in;

    z-index: 3;
}

.signature-table {
    width: 100%;

    border-collapse: collapse;

    table-layout: fixed;
}

.signature-table td {
    width: 33.333%;

    text-align: center;

    vertical-align: bottom;
}

.signature-space {
    height: 47px;
}

.signature-line {
    width: 85%;

    height: 1px;

    margin:
        0
        auto
        7px;

    background: #666666;
}

.signature-image {
    display: block;

    width: 132px;
    height: 44px;

    margin: 0 auto 3px;
}

.signature-name {
    font-size: 7px;

    font-weight: bold;

    text-transform: uppercase;

    letter-spacing: .3px;

    color: #363636;
}

.signature-role {
    margin-top: 3px;

    font-size: 6px;

    text-transform: uppercase;

    letter-spacing: .6px;

    color: #8c8c8c;
}

.signature-logo {
    display: block;

    width: 132px;
    height: 60px;

    margin: 0 auto;
}


/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

.document-footer {
    position: absolute;

    left: .66in;
    right: .66in;

    top: 10.68in;

    z-index: 4;

    padding-top: 9px;

    border-top: 1px solid #dddddd;
}

.footer-table {
    width: 100%;

    border-collapse: collapse;
}

.footer-table td {
    font-size: 6.5px;

    text-transform: uppercase;

    letter-spacing: 1.2px;

    color: #999999;
}

.footer-right {
    text-align: right;
}

.footer-folio {
    font-weight: bold;

    color: #686868;
}


/*
|--------------------------------------------------------------------------
| FRANJA INFERIOR
|--------------------------------------------------------------------------
*/

.bottom-grey {
    position: absolute;

    right: 0;
    top: 10.93in;

    width: 8.5in;
    height: .07in;

    background: #a7a7a7;
}

.bottom-pink {
    position: absolute;

    right: 1.12in;
    top: 10.93in;
    
    width: 6.55in;
    height: .07in;

    background: #e58894;
}

</style>

</head>


<body>


<main class="page">


    <!--
    |--------------------------------------------------------------------------
    | DECORACIÓN SUPERIOR
    |--------------------------------------------------------------------------
    -->

    <div class="top-grey"></div>
    <div class="top-pink"></div>


    <!--
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    -->

    <header class="document-header">


        <table class="header-table">

            <tr>

                <td class="brand-cell">


                    <?php if ($signatureLogoUri !== ''): ?>

                        <img
                            class="header-logo"
                            src="<?= $logoUri ?>"
                            alt="Club MaiTeam"
                        >

                    <?php endif; ?>


                    <div class="brand-copy">

                        <span class="brand-name">
                            MAITEAM
                        </span>

                        <span class="brand-subtitle">
                            Patinaje Artístico
                        </span>

                    </div>

                </td>


                <td class="contact-cell">

                    <div class="contact-row">

                        Teléfono:
                        +56 9 814 390 49

                    </div>


                    <div class="contact-row">

                        Correo:
                        bymaiteam@gmail.com

                    </div>


                    <div class="contact-row">

                        Instagram:
                        @by.maiteam

                    </div>


                </td>

            </tr>

        </table>


        <!-- META -->

        <table class="document-meta">

            <tr>

                <td>

                    <span class="document-type">
                        Documento oficial
                    </span>

                    <div class="document-mark"></div>

                </td>


                <td class="document-date">

                    <?= $escape($fechaEmision) ?>

                </td>

            </tr>

        </table>


    </header>


    <!--
    |--------------------------------------------------------------------------
    | TÍTULO
    |--------------------------------------------------------------------------
    -->

    <section class="title-area">

        <h1>
            JUSTIFICATIVO
        </h1>

        <div class="title-accent"></div>

    </section>


    <!--
    |--------------------------------------------------------------------------
    | CONTENIDO
    |--------------------------------------------------------------------------
    -->

    <section class="content">


        <div class="recipient">
            A quien corresponda
        </div>


        <p>

            Junto con saludar, informamos que la siguiente deportista
            forma parte de nuestro Club de Patinaje Artístico MAITEAM
            y se encuentra inscrita para participar en una competencia
            oficial.

        </p>


        <!-- DEPORTISTA -->

        <div class="athlete">

            <div class="athlete-name">

                <?= $escape($nombreDeportista) ?>

            </div>


            <div class="athlete-meta">

                RUT <?= $escape($rut) ?>

                <?php if ($categoria !== ''): ?>

                    &nbsp;&nbsp;·&nbsp;&nbsp;

                    <?= $escape($categoria) ?>

                <?php endif; ?>

            </div>

        </div>


        <div class="event-intro">
            Competencia oficial
        </div>


        <!-- DATOS COMPETENCIA -->

        <table class="event-table">

            <tr>

                <td class="competition">

                    <span class="data-label">
                        Competencia
                    </span>

                    <span class="data-value">
                        <?= $escape($evento) ?>
                    </span>

                </td>


                <td class="date-info">

                    <span class="data-label">
                        Fecha
                    </span>

                    <span class="data-value date-value">
                        <?php if ($fechaInicio !== ''): ?>

                            <?= $escape(certificado_fecha_larga($fechaInicio)) ?>

                            <?php if ($fechaFin !== '' && $fechaFin !== $fechaInicio): ?>

                                al
                                <?= $escape(certificado_fecha_larga($fechaFin)) ?>

                            <?php endif; ?>

                        <?php else: ?>

                            Por confirmar

                        <?php endif; ?>
                    </span>

                </td>


                <td class="place-info">

                    <span class="data-label">
                        Lugar
                    </span>

                    <span class="data-value">

                        <?= $lugar !== ''
                            ? $escape($lugar)
                            : 'Por confirmar'
                        ?>

                    </span>

                </td>

            </tr>

        </table>


        <!-- TEXTO JUSTIFICATIVO -->

        <p>

            Esta participación forma parte del calendario deportivo
            de nuestro club y corresponde a una actividad vinculada
            al proceso formativo y competitivo de la deportista.

        </p>


        <p>

            Por lo anterior, solicitamos justificar su inasistencia
            académica durante las fechas señaladas, agradeciendo desde
            ya su comprensión y apoyo para compatibilizar adecuadamente
            sus responsabilidades escolares con su desarrollo deportivo.

        </p>


        <p class="closing">

            Agradecemos su disposición y apoyo al desarrollo integral
            de nuestra deportista.

        </p>


        <div class="goodbye">
            Saludos afectuosos,
        </div>


    </section>


    <!--
    |--------------------------------------------------------------------------
    | FIRMAS
    |--------------------------------------------------------------------------
    -->

    <section class="signatures">


        <table class="signature-table">

            <tr>


                <!-- FIRMA 1 -->

                <td>

                    <?php if ($headCoachUri !== ''): ?>

                        <img
                            class="signature-image"
                            src="<?= $headCoachUri ?>"
                            alt="Firma Head Coach"
                        >

                    <?php else: ?>

                        <div class="signature-space"></div>

                    <?php endif; ?>

                    <div class="signature-line"></div>

                    <div class="signature-name">
                        Head Coach
                    </div>

                    <div class="signature-role">
                        Club MAITEAM
                    </div>

                </td>


                <!-- LOGO CENTRAL -->

                <td>

                    <?php if ($signatureLogoUri !== ''): ?>

                        <img
                            class="signature-logo"
                            src="<?= $signatureLogoUri ?>"
                            alt="MAITEAM"
                        >

                    <?php endif; ?>

                </td>


                <!-- FIRMA 2 -->

                <td>

                    <?php if ($assistantCoachUri !== ''): ?>

                        <img
                            class="signature-image"
                            src="<?= $assistantCoachUri ?>"
                            alt="Firma Coach & Choreographer"
                        >

                    <?php else: ?>

                        <div class="signature-space"></div>

                    <?php endif; ?>

                    <div class="signature-line"></div>

                    <div class="signature-name">
                        Coach & Choreographer
                    </div>

                    <div class="signature-role">
                        Club MAITEAM
                    </div>

                </td>


            </tr>

        </table>


    </section>


    <!--
    |--------------------------------------------------------------------------
    | FOOTER
    |--------------------------------------------------------------------------
    -->

    <footer class="document-footer">


        <table class="footer-table">

            <tr>

                <td>

                    MAITEAM · PATINAJE ARTÍSTICO

                </td>


                <td class="footer-right">

                    <span class="footer-folio">

                        <?= $escape($folio) ?>

                    </span>

                </td>

            </tr>

        </table>


    </footer>


    <!--
    |--------------------------------------------------------------------------
    | DECORACIÓN INFERIOR
    |--------------------------------------------------------------------------
    -->

    <div class="bottom-grey"></div>
    <div class="bottom-pink"></div>


</main>


</body>

</html>
