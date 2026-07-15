<?php

namespace BDPay\API\Middleware;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;

class AuthMiddleware {
    private bool $requireAuth;

    public function __construct(bool $requireAuth = true) {
        $this->requireAuth = $requireAuth;
    }

    public function __invoke(Request $request, Response $response, callable $next): void {
        $apiKey = $request->header('X-API-KEY', '');

        if (empty($apiKey)) {
            $response->error('API key is required. Provide it via X-API-Key header', 401);
        }

        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM api_keys WHERE api_key = ? AND active = 1");
        $stmt->execute([$apiKey]);
        $keyData = $stmt->fetch();

        if (!$keyData) {
            $response->error('Invalid or inactive API key', 401);
        }

        $stmt = $db->prepare("UPDATE api_keys SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$keyData['id']]);

        $request->setAttribute('api_key_id', $keyData['id']);
        $request->setAttribute('api_key_label', $keyData['label']);
        $request->setAttribute('merchant_id', $keyData['merchant_id']);

        $next();
    }

    public static function optional(): self {
        return new self(false);
    }
}
