<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

const DATA_DIR = __DIR__ . '/data';
const EXPENSES_FILE = DATA_DIR . '/expenses.json';
const PAYMENTS_FILE = DATA_DIR . '/payments.json';
const RECEIPTS_DIR = __DIR__ . '/receipts';
const RECEIPTS_URL = 'receipts';
const FIXED_FEE = 10000;
const TICKET_PRICE = 4000;
const MAX_RECEIPT_SIZE = 5242880;

function read_json(string $file): array {
    if (!file_exists($file)) {
        return ['items' => []];
    }
    $raw = file_get_contents($file);
    if ($raw === false) {
        return ['items' => []];
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
        return ['items' => []];
    }
    return $data;
}

function save_json(string $file, array $data): bool {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    return file_put_contents($file, $json . PHP_EOL, LOCK_EX) !== false;
}

function ensure_dir(string $dir): bool {
    if (is_dir($dir)) {
        return true;
    }
    return mkdir($dir, 0775, true);
}

function handle_receipt_upload(): ?array {
    if (empty($_FILES['receipt']) || !is_array($_FILES['receipt'])) {
        return null;
    }
    $file = $_FILES['receipt'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if (($file['size'] ?? 0) > MAX_RECEIPT_SIZE) {
        return null;
    }
    $tmp = $file['tmp_name'] ?? '';
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($extensions[$mime])) {
        return null;
    }
    if (!ensure_dir(RECEIPTS_DIR)) {
        return null;
    }
    $name = uniqid('exp_', true) . '.' . $extensions[$mime];
    $target = RECEIPTS_DIR . '/' . $name;
    if (!move_uploaded_file($tmp, $target)) {
        return null;
    }
    return [
        'name' => $name,
        'url' => RECEIPTS_URL . '/' . $name,
    ];
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_expenses': {
        $data = read_json(EXPENSES_FILE);
        $total = 0;
        foreach ($data['items'] as $item) {
            $total += (float)($item['amount'] ?? 0);
        }
        respond(['ok' => true, 'items' => $data['items'], 'total' => $total]);
    }
    case 'add_expense': {
        $input = json_input();
        $date = trim((string)($_POST['date'] ?? $input['date'] ?? ''));
        $concept = trim((string)($_POST['concept'] ?? $input['concept'] ?? ''));
        $amount = (float)($_POST['amount'] ?? $input['amount'] ?? 0);
        $reimbursableRaw = $_POST['reimbursable'] ?? $input['reimbursable'] ?? '0';
        $reimbursable = filter_var($reimbursableRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $reimbursable = $reimbursable ?? ((string)$reimbursableRaw === '1');
        if ($date === '' || $concept === '' || $amount <= 0) {
            respond(['ok' => false, 'error' => 'Invalid expense data'], 400);
        }
        $receipt = handle_receipt_upload();
        $data = read_json(EXPENSES_FILE);
        $data['items'][] = [
            'id' => uniqid('exp_', true),
            'date' => $date,
            'concept' => $concept,
            'amount' => $amount,
            'reimbursable' => $reimbursable,
            'reimbursed' => false,
            'receipt' => $receipt['name'] ?? null,
            'receipt_url' => $receipt['url'] ?? null,
        ];
        if (!save_json(EXPENSES_FILE, $data)) {
            respond(['ok' => false, 'error' => 'Failed to save'], 500);
        }
        respond(['ok' => true]);
    }
    case 'delete_expense': {
        $input = json_input();
        $id = (string)($input['id'] ?? '');
        if ($id === '') {
            respond(['ok' => false, 'error' => 'Missing id'], 400);
        }
        $data = read_json(EXPENSES_FILE);
        $kept = [];
        foreach ($data['items'] as $item) {
            if ((string)($item['id'] ?? '') === $id) {
                $receipt = (string)($item['receipt'] ?? '');
                if ($receipt !== '') {
                    $path = RECEIPTS_DIR . '/' . $receipt;
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
                continue;
            }
            $kept[] = $item;
        }
        $data['items'] = array_values($kept);
        if (!save_json(EXPENSES_FILE, $data)) {
            respond(['ok' => false, 'error' => 'Failed to save'], 500);
        }
        respond(['ok' => true]);
    }
    case 'toggle_expense_reimbursed': {
        $input = json_input();
        $id = (string)($input['id'] ?? '');
        if ($id === '') {
            respond(['ok' => false, 'error' => 'Missing id'], 400);
        }
        $data = read_json(EXPENSES_FILE);
        $updated = false;
        foreach ($data['items'] as &$item) {
            if ((string)($item['id'] ?? '') !== $id) {
                continue;
            }
            $reimbursable = (bool)($item['reimbursable'] ?? false);
            if (!$reimbursable) {
                respond(['ok' => false, 'error' => 'Not reimbursable'], 400);
            }
            $item['reimbursed'] = !((bool)($item['reimbursed'] ?? false));
            $updated = true;
            break;
        }
        unset($item);
        if (!$updated) {
            respond(['ok' => false, 'error' => 'Expense not found'], 404);
        }
        if (!save_json(EXPENSES_FILE, $data)) {
            respond(['ok' => false, 'error' => 'Failed to save'], 500);
        }
        respond(['ok' => true]);
    }
    case 'get_payments': {
        $data = read_json(PAYMENTS_FILE);
        $total = 0;
        foreach ($data['items'] as $item) {
            $total += (float)($item['total'] ?? 0);
        }
        respond(['ok' => true, 'items' => $data['items'], 'total' => $total]);
    }
    case 'add_payment': {
        $input = json_input();
        $date = trim((string)($input['date'] ?? ''));
        $apoderado = trim((string)($input['apoderado'] ?? ''));
        $athlete = trim((string)($input['athlete'] ?? ''));
        $paymentMethod = trim((string)($input['payment_method'] ?? ''));
        $notes = trim((string)($input['notes'] ?? ''));
        $tickets = (int)($input['tickets'] ?? 0);
        $includeFixed = (bool)($input['include_fixed'] ?? true);
        if ($date === '' || $apoderado === '' || $tickets < 0) {
            respond(['ok' => false, 'error' => 'Invalid payment data'], 400);
        }
        $fixedFeeApplied = $includeFixed ? FIXED_FEE : 0;
        $total = $fixedFeeApplied + (TICKET_PRICE * $tickets);
        $data = read_json(PAYMENTS_FILE);
        $data['items'][] = [
            'id' => uniqid('pay_', true),
            'date' => $date,
            'apoderado' => $apoderado,
            'athlete' => $athlete,
            'tickets' => $tickets,
            'include_fixed' => $includeFixed,
            'fixed_fee' => FIXED_FEE,
            'fixed_fee_applied' => $fixedFeeApplied,
            'ticket_price' => TICKET_PRICE,
            'total' => $total,
            'payment_method' => $paymentMethod,
            'notes' => $notes,
        ];
        if (!save_json(PAYMENTS_FILE, $data)) {
            respond(['ok' => false, 'error' => 'Failed to save'], 500);
        }
        respond(['ok' => true]);
    }
    case 'delete_payment': {
        $input = json_input();
        $id = (string)($input['id'] ?? '');
        if ($id === '') {
            respond(['ok' => false, 'error' => 'Missing id'], 400);
        }
        $data = read_json(PAYMENTS_FILE);
        $data['items'] = array_values(array_filter(
            $data['items'],
            static fn($item) => (string)($item['id'] ?? '') !== $id
        ));
        if (!save_json(PAYMENTS_FILE, $data)) {
            respond(['ok' => false, 'error' => 'Failed to save'], 500);
        }
        respond(['ok' => true]);
    }
    default:
        respond(['ok' => false, 'error' => 'Unknown action'], 404);
}
