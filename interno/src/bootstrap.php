<?php

declare(strict_types=1);

require __DIR__ . '/helpers.php';
load_env_file(dirname(__DIR__, 2) . '/.env');
require __DIR__ . '/apoderados.php';
require __DIR__ . '/deportistas.php';
require __DIR__ . '/coaches.php';
require __DIR__ . '/clases.php';
require __DIR__ . '/asistencia.php';
require __DIR__ . '/pagos.php';
require __DIR__ . '/transferencias.php';
require __DIR__ . '/modalidades.php';
require __DIR__ . '/inscripciones.php';
require __DIR__ . '/cuotas.php';
require __DIR__ . '/reportes.php';
require __DIR__ . '/competencias.php';
require __DIR__ . '/certificados.php';

$baseConfig = require __DIR__ . '/../config/app.php';
$databaseConfig = require __DIR__ . '/../config/database.php';

// Expose config via a simple helper.
app_config($baseConfig, $databaseConfig);
