<?php

declare(strict_types=1);

function app_config(array $appConfig, array $databaseConfig): void
{
    $GLOBALS['app_config'] = $appConfig;
    $GLOBALS['db_config'] = $databaseConfig;
}

function config(string $key, $default = null)
{
    $segments = explode('.', $key);
    $root = array_shift($segments);

    if ($root === 'db') {
        $source = $GLOBALS['db_config'] ?? [];
    } elseif ($root === 'app') {
        $source = $GLOBALS['app_config'] ?? [];
    } else {
        $source = $GLOBALS['app_config'] ?? [];
        array_unshift($segments, $root);
    }

    foreach ($segments as $segment) {
        if (!is_array($source) || !array_key_exists($segment, $source)) {
            return $default;
        }
        $source = $source[$segment];
    }

    return $source;
}

function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return $value;
}

function load_env_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $trimmed, 2));
        if ($key === '' || array_key_exists($key, $_ENV) || getenv($key) !== false) {
            continue;
        }

        $value = trim($value);
        $length = strlen($value);
        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST', config('db.host', '127.0.0.1'));
    $dbname = env('DB_NAME', config('db.name', 'maiteam'));
    $user = env('DB_USER', config('db.user', 'root'));
    $pass = env('DB_PASS', config('db.pass', ''));

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $dbname);

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function render(string $view, array $data = []): void
{
    $viewPath = __DIR__ . '/../views/' . $view . '.php';
    $layoutPath = __DIR__ . '/../views/layout.php';

    if (!file_exists($viewPath)) {
        $viewPath = __DIR__ . '/../views/404.php';
    }

    $data['viewPath'] = $viewPath;

    extract($data, EXTR_SKIP);

    require $layoutPath;
}

function base_url(string $path = ''): string
{
    $base = rtrim((string) config('base_url', '/interno'), '/');
    $path = ltrim($path, '/');

    return $path === '' ? $base : $base . '/' . $path;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function normalize_rut(?string $rut): string
{
    $clean = strtoupper(trim((string) $rut));
    $clean = str_replace(['.', '-', ' '], '', $clean);
    $normalized = preg_replace('/[^0-9K]/', '', $clean);

    return $normalized ?? '';
}

function format_rut(?string $rut): string
{
    $normalized = normalize_rut($rut);
    $length = strlen($normalized);

    if ($length <= 1) {
        return $normalized;
    }

    return substr($normalized, 0, $length - 1) . '-' . substr($normalized, -1);
}

function is_valid_rut(?string $rut): bool
{
    $normalized = normalize_rut($rut);

    if (strlen($normalized) < 2) {
        return false;
    }

    $body = substr($normalized, 0, -1);
    $verifier = substr($normalized, -1);

    if ($body === '' || !ctype_digit($body)) {
        return false;
    }

    $sum = 0;
    $multiplier = 2;

    for ($i = strlen($body) - 1; $i >= 0; $i--) {
        $sum += (int) $body[$i] * $multiplier;
        $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
    }

    $remainder = 11 - ($sum % 11);
    if ($remainder === 11) {
        $expected = '0';
    } elseif ($remainder === 10) {
        $expected = 'K';
    } else {
        $expected = (string) $remainder;
    }

    return $verifier === $expected;
}

function page_title(string $view): string
{
    $titles = [
        'home' => 'Panel - Club MaiTeam',
        'socios' => 'Apoderados - Club MaiTeam',
        'deportistas' => 'Deportistas - Club MaiTeam',
        'coaches' => 'Coaches - Club MaiTeam',
        'clases' => 'Clases - Club MaiTeam',
        'asistencia' => 'Asistencia - Club MaiTeam',
        'modalidades' => 'Modalidades - Club MaiTeam',
        'inscripciones' => 'Inscripciones - Club MaiTeam',
        'competencias' => 'Competencias - Club MaiTeam',
        'cuotas' => 'Cuotas socios - Club MaiTeam',
        'reportes' => 'Reportes - Club MaiTeam',
        'certificados' => 'Certificados - Club MaiTeam',
        'pagos' => 'Pagos - Club MaiTeam',
        'transferencias' => 'Transferencias coaches - Club MaiTeam',
        'eventos' => 'Eventos - Club MaiTeam',
        '404' => 'No encontrado - Club MaiTeam',
    ];

    return $titles[$view] ?? 'Club MaiTeam';
}
