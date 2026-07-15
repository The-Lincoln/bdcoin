<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

spl_autoload_register(function ($class) {
    $prefix = 'BDPay\\API\\';
    $baseDir = __DIR__ . '/';

    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) require $file;
    }
});

use BDPay\API\Core\Router;
use BDPay\API\Core\Response as ApiResponse;
use BDPay\API\Core\Request as ApiRequest;
use BDPay\API\Middleware\CorsMiddleware;
use BDPay\API\Middleware\AuthMiddleware;
use BDPay\API\Middleware\RateLimitMiddleware;
use BDPay\API\Controllers\PaymentController;
use BDPay\API\Controllers\BDCoinController;
use BDPay\API\Controllers\RateController;
use BDPay\API\Controllers\WebhookController;
use BDPay\API\Controllers\RefundController;
use BDPay\API\Controllers\ApiKeyController;
use BDPay\API\Controllers\MerchantController;

$router = new Router();

$router->addMiddleware(new CorsMiddleware());
$router->addMiddleware(new RateLimitMiddleware(60, 60));

$router->get('/', function (ApiRequest $req, ApiResponse $res) {
    $res->success([
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'documentation' => BASE_URL . 'api/v1',
        'endpoints' => [
            'GET /api' => 'API information',
            'GET /api/v1' => 'API documentation',
            'GET /api/v1/payments' => 'List payments (paginated, filterable)',
            'POST /api/v1/payments' => 'Create a payment',
            'GET /api/v1/payments/{id}' => 'Get payment details',
            'PUT /api/v1/payments/{id}' => 'Update payment',
            'DELETE /api/v1/payments/{id}' => 'Delete payment',
            'GET /api/v1/payments/stats' => 'Payment statistics',
            'GET /api/v1/bdcoin/wallets' => 'List BDCoin wallets',
            'POST /api/v1/bdcoin/wallets' => 'Create BDCoin wallet',
            'GET /api/v1/bdcoin/wallets/{address}' => 'Get wallet balance & details',
            'GET /api/v1/bdcoin/transactions' => 'List BDCoin transactions',
            'POST /api/v1/bdcoin/transfer' => 'Send BDCoin',
            'GET /api/v1/rates' => 'List crypto exchange rates',
            'GET /api/v1/rates/{symbol}' => 'Get specific rate',
            'GET /api/v1/refunds' => 'List refunds',
            'POST /api/v1/refunds' => 'Process a refund',
            'POST /api/v1/webhooks/stripe' => 'Stripe webhook endpoint',
            'POST /api/v1/webhooks/square' => 'Square webhook endpoint',
            'POST /api/v1/webhooks/crypto' => 'Crypto webhook endpoint',
            'POST /api/v1/webhooks/custom' => 'Custom webhook endpoint',
            'POST /api/v1/merchants/register' => 'Register as merchant',
            'GET /api/v1/merchants/profile' => 'Get merchant profile',
            'PUT /api/v1/merchants/profile' => 'Update merchant profile',
            'GET /api/v1/merchants/transactions' => 'Merchant transaction history',
            'GET /api/v1/merchants/settlements' => 'Merchant settlement history',
            'GET /api/v1/keys' => 'List API keys',
            'POST /api/v1/keys' => 'Generate API key',
            'POST /api/v1/keys/{id}/revoke' => 'Revoke API key',
            'POST /api/v1/keys/{id}/activate' => 'Activate API key',
        ],
    ], 200);
});

$router->get('/v1', function (ApiRequest $req, ApiResponse $res) {
    $docs = [
        'name' => APP_NAME . ' API v2',
        'version' => APP_VERSION,
        'base_url' => BASE_URL . 'api/v1',
        'auth' => [
            'type' => 'API Key',
            'header' => 'X-API-Key',
            'how_to' => 'Generate an API key from the admin panel or POST /api/v1/keys',
        ],
        'pagination' => [
            'parameters' => ['page' => 'Page number (default: 1)', 'per_page' => 'Items per page (default: 20, max: 100)'],
            'response' => ['pagination' => ['page', 'per_page', 'total', 'total_pages', 'has_next', 'has_prev']],
        ],
        'errors' => [
            '400' => 'Bad request',
            '401' => 'Missing or invalid API key',
            '404' => 'Resource not found',
            '422' => 'Validation failed',
            '429' => 'Rate limit exceeded',
            '500' => 'Server error',
        ],
        'endpoints' => [
            'payments' => [
                'GET /v1/payments' => ['description' => 'List all payments', 'auth' => true],
                'POST /v1/payments' => ['description' => 'Create a new payment', 'auth' => false, 'body' => ['payer_name (required)', 'payer_email (required)', 'amount (required)', 'payment_method (required)', 'currency', 'description']],
                'GET /v1/payments/{id}' => ['description' => 'Get payment by ID or transaction ID', 'auth' => true],
                'PUT /v1/payments/{id}' => ['description' => 'Update payment status/details', 'auth' => true],
                'DELETE /v1/payments/{id}' => ['description' => 'Delete a payment', 'auth' => true],
                'GET /v1/payments/stats' => ['description' => 'Payment statistics with period filter', 'auth' => true],
            ],
            'bdcoin' => [
                'GET /v1/bdcoin/wallets' => ['description' => 'List all BDCoin wallets', 'auth' => true],
                'POST /v1/bdcoin/wallets' => ['description' => 'Create a new BDCoin wallet', 'auth' => true],
                'GET /v1/bdcoin/wallets/{address}' => ['description' => 'Get wallet details and balance', 'auth' => true],
                'GET /v1/bdcoin/transactions' => ['description' => 'List BDCoin transactions', 'auth' => true],
                'POST /v1/bdcoin/transfer' => ['description' => 'Send BDCoin between wallets', 'auth' => true, 'body' => ['from_address (required)', 'to_address (required)', 'amount (required)']],
            ],
            'rates' => [
                'GET /v1/rates' => ['description' => 'Get all crypto exchange rates', 'auth' => false],
                'GET /v1/rates/{symbol}' => ['description' => 'Get rate for specific crypto', 'auth' => false],
            ],
            'refunds' => [
                'GET /v1/refunds' => ['description' => 'List all refunds', 'auth' => true],
                'POST /v1/refunds' => ['description' => 'Process a refund', 'auth' => true, 'body' => ['transaction_id (required)', 'amount', 'reason']],
            ],
            'webhooks' => [
                'POST /v1/webhooks/stripe' => ['description' => 'Stripe webhook receiver', 'auth' => false],
                'POST /v1/webhooks/square' => ['description' => 'Square webhook receiver', 'auth' => false],
                'POST /v1/webhooks/crypto' => ['description' => 'Crypto webhook receiver', 'auth' => false],
                'POST /v1/webhooks/custom' => ['description' => 'Custom webhook receiver', 'auth' => false],
            ],
            'merchants' => [
                'POST /v1/merchants/register' => ['description' => 'Register as a merchant and get API key', 'auth' => false, 'body' => ['business_name (required)', 'contact_name (required)', 'email (required)', 'payment_method (required)', 'wallet_address']],
                'GET /v1/merchants/profile' => ['description' => 'Get merchant profile with stats', 'auth' => true],
                'PUT /v1/merchants/profile' => ['description' => 'Update merchant profile', 'auth' => true],
                'GET /v1/merchants/transactions' => ['description' => 'Merchant transaction history', 'auth' => true],
                'GET /v1/merchants/settlements' => ['description' => 'Merchant settlement summary by period', 'auth' => true],
            ],
            'keys' => [
                'GET /v1/keys' => ['description' => 'List all API keys', 'auth' => true],
                'POST /v1/keys' => ['description' => 'Generate new API key', 'auth' => true, 'body' => ['label (required)', 'merchant_id']],
                'POST /v1/keys/{id}/revoke' => ['description' => 'Revoke an API key', 'auth' => true],
                'POST /v1/keys/{id}/activate' => ['description' => 'Activate an API key', 'auth' => true],
            ],
        ],
    ];
    $res->success($docs);
});

$router->group('/v1/payments', function ($router) {
    $router->get('', [PaymentController::class, 'index']);
    $router->post('', [PaymentController::class, 'store']);
    $router->get('/stats', [PaymentController::class, 'stats']);
    $router->get('/{id}', [PaymentController::class, 'show']);
    $router->put('/{id}', [PaymentController::class, 'update']);
    $router->patch('/{id}', [PaymentController::class, 'update']);
    $router->delete('/{id}', [PaymentController::class, 'destroy']);
}, [new AuthMiddleware()]);

$router->group('/v1/bdcoin', function ($router) {
    $router->get('/wallets', [BDCoinController::class, 'wallets']);
    $router->post('/wallets', [BDCoinController::class, 'createWallet']);
    $router->get('/wallets/{address}', [BDCoinController::class, 'getBalance']);
    $router->get('/transactions', [BDCoinController::class, 'transactions']);
    $router->post('/transfer', [BDCoinController::class, 'send']);
}, [new AuthMiddleware()]);

$router->get('/v1/rates', [RateController::class, 'index']);
$router->get('/v1/rates/{symbol}', [RateController::class, 'show']);

$router->group('/v1/refunds', function ($router) {
    $router->get('', [RefundController::class, 'index']);
    $router->post('', [RefundController::class, 'process']);
}, [new AuthMiddleware()]);

$router->post('/v1/webhooks/stripe', [WebhookController::class, 'handleStripe']);
$router->post('/v1/webhooks/square', [WebhookController::class, 'handleSquare']);
$router->post('/v1/webhooks/crypto', [WebhookController::class, 'handleCrypto']);
$router->post('/v1/webhooks/custom', [WebhookController::class, 'handleCustom']);

$router->post('/v1/merchants/register', [MerchantController::class, 'register']);

$router->group('/v1/merchants', function ($router) {
    $router->get('/profile', [MerchantController::class, 'profile']);
    $router->put('/profile', [MerchantController::class, 'updateProfile']);
    $router->patch('/profile', [MerchantController::class, 'updateProfile']);
    $router->get('/transactions', [MerchantController::class, 'transactions']);
    $router->get('/settlements', [MerchantController::class, 'settlements']);
}, [new AuthMiddleware()]);

$router->group('/v1/keys', function ($router) {
    $router->get('', [ApiKeyController::class, 'index']);
    $router->post('', [ApiKeyController::class, 'generate']);
    $router->post('/{id}/revoke', [ApiKeyController::class, 'revoke']);
    $router->post('/{id}/activate', [ApiKeyController::class, 'activate']);
}, [new AuthMiddleware()]);

$router->any('/*', function (ApiRequest $req, ApiResponse $res) {
    $res->error('Endpoint not found. See GET /api for available endpoints.', 404);
});

try {
    $router->dispatch();
} catch (\Throwable $e) {
    \logError('API Fatal Error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
    ]);
    exit;
}
