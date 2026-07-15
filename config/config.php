<?php
define('APP_NAME', 'BDPay International');
define('APP_VERSION', '2.0.0');
define('BASE_URL', '/bdpay/');
define('API_URL', BASE_URL . 'api/');

define('DB_PATH', __DIR__ . '/../bdpay.db');

define('DEFAULT_CURRENCY', 'USD');
define('BDC_PRICE', 12.50);
define('BDC_DECIMALS', 4);

define('SWPE_PRICE', 0.75);
define('SWPE_DECIMALS', 4);
define('EXCHANGE_FEE_PERCENT', 0.5);

define('PAGINATION_PER_PAGE', 20);

define('SESSION_TIMEOUT', 3600);
define('CSRF_TOKEN_NAME', 'bdpay_csrf');

define('SQUARE_ACCESS_TOKEN', 'EAAAELwG6BZ3s5Ls4OzPpMpJjO3J9o7iV6kF2xR1nA8cD0mQ4bY5tU3vH7wS2zX');
define('SQUARE_LOCATION_ID', 'LATEST');
define('SQUARE_APP_ID', 'sandbox-sq0idb-YOUR_SANDBOX_APP_ID');
define('SQUARE_ENVIRONMENT', 'sandbox');
define('SQUARE_API_URL', 'https://connect.squareupsandbox.com');

$payment_methods = [
    'stripe' => [
        'name' => 'Stripe',
        'icon' => 'bi-stripe',
        'color' => 'primary',
        'processor' => 'Stripe, Inc.',
        'fee_percent' => 2.9,
        'fee_fixed' => 0.30,
    ],
    'google_pay' => [
        'name' => 'Google Pay',
        'icon' => 'bi-google',
        'color' => 'warning',
        'processor' => 'Google LLC',
        'fee_percent' => 0,
        'fee_fixed' => 0,
    ],
    'visa' => [
        'name' => 'Visa',
        'icon' => 'bi-credit-card',
        'color' => 'info',
        'processor' => 'Visa Inc.',
        'fee_percent' => 2.5,
        'fee_fixed' => 0.25,
    ],
    'crypto' => [
        'name' => 'Cryptocurrency',
        'icon' => 'bi-currency-bitcoin',
        'color' => 'warning',
        'processor' => 'Blockchain Network',
        'fee_percent' => 0.5,
        'fee_fixed' => 0,
    ],
    'bdcoin' => [
        'name' => 'BDCoin',
        'icon' => 'bi-gem',
        'color' => 'success',
        'processor' => 'BDPay Chain',
        'fee_percent' => 0.1,
        'fee_fixed' => 0,
    ],
    'square' => [
        'name' => 'Square',
        'icon' => 'bi-credit-card-2-front',
        'color' => 'dark',
        'processor' => 'Square, Inc.',
        'fee_percent' => 2.6,
        'fee_fixed' => 0.10,
    ],
    'swpe' => [
        'name' => 'SWPE Token',
        'icon' => 'bi-lightning',
        'color' => 'info',
        'processor' => 'SWPE Network',
        'fee_percent' => 0.3,
        'fee_fixed' => 0,
    ],
    'bank' => [
        'name' => 'Bank Pay',
        'icon' => 'bi-bank',
        'color' => 'secondary',
        'processor' => 'Bank Transfer',
        'fee_percent' => 0,
        'fee_fixed' => 1.00,
    ],
    'bkash' => [
        'name' => 'bKash',
        'icon' => 'bi-phone',
        'color' => 'danger',
        'processor' => 'bKash Limited',
        'fee_percent' => 0,
        'fee_fixed' => 0.50,
    ],
    'nagad' => [
        'name' => 'Nagad',
        'icon' => 'bi-phone',
        'color' => 'warning',
        'processor' => 'Nagad Limited',
        'fee_percent' => 0,
        'fee_fixed' => 0.50,
    ],
];

$crypto_assets = [
    'btc' => ['name' => 'Bitcoin', 'symbol' => 'BTC', 'icon' => 'bi-currency-bitcoin', 'color' => 'warning', 'rate' => 61500],
    'eth' => ['name' => 'Ethereum', 'symbol' => 'ETH', 'icon' => 'bi-currency-ethereum', 'color' => 'primary', 'rate' => 3400],
    'usdt' => ['name' => 'Tether', 'symbol' => 'USDT', 'icon' => 'bi-currency-dollar', 'color' => 'success', 'rate' => 1],
    'bnb' => ['name' => 'Binance Coin', 'symbol' => 'BNB', 'icon' => 'bi-box', 'color' => 'warning', 'rate' => 580],
    'bdcoin' => ['name' => 'BDCoin', 'symbol' => 'BDC', 'icon' => 'bi-gem', 'color' => 'success', 'rate' => BDC_PRICE],
    'swpe' => ['name' => 'SWPE Token', 'symbol' => 'SWPE', 'icon' => 'bi-lightning', 'color' => 'info', 'rate' => SWPE_PRICE],
];

$status_badges = [
    'completed' => 'bg-success',
    'pending' => 'bg-warning text-dark',
    'failed' => 'bg-danger',
    'refunded' => 'bg-info',
    'cancelled' => 'bg-secondary',
];

function getStatusBadge($status) {
    global $status_badges;
    $class = $status_badges[$status] ?? 'bg-secondary';
    $icons = ['completed' => 'check-circle', 'pending' => 'hourglass', 'failed' => 'x-circle', 'refunded' => 'arrow-return-left', 'cancelled' => 'slash-circle'];
    $icon = $icons[$status] ?? 'question-circle';
    return "<span class=\"badge $class\"><i class=\"bi bi-$icon\"></i> " . ucfirst($status) . "</span>";
}

function generateTxId($prefix = 'TXN') {
    return strtoupper($prefix . bin2hex(random_bytes(8)));
}

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($path, $status = 302) {
    header("Location: " . BASE_URL . ltrim($path, '/'), true, $status);
    exit;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function logError($message, $context = []) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $entry = date('Y-m-d H:i:s') . " [$message] " . json_encode($context) . PHP_EOL;
    file_put_contents($logDir . '/error.log', $entry, FILE_APPEND | LOCK_EX);
}

function csrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrf($token) {
    return hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
}

function formatAmount($amount, $currency = 'USD') {
    $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'BDC' => '⟠'];
    $sym = $symbols[$currency] ?? '$';
    return $sym . number_format($amount, 2);
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    $units = [
        31536000 => 'year', 2592000 => 'month', 604800 => 'week',
        86400 => 'day', 3600 => 'hour', 60 => 'minute', 1 => 'second'
    ];
    foreach ($units as $sec => $unit) {
        if ($diff >= $sec) {
            $n = floor($diff / $sec);
            return "$n $unit" . ($n > 1 ? 's' : '') . ' ago';
        }
    }
    return 'just now';
}
