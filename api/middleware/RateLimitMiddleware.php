<?php

namespace BDPay\API\Middleware;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;

class RateLimitMiddleware {
    private int $maxRequests;
    private int $windowSeconds;
    private string $rateLimitDir;

    public function __construct(int $maxRequests = 60, int $windowSeconds = 60) {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->rateLimitDir = __DIR__ . '/../../storage/ratelimit';
        if (!is_dir($this->rateLimitDir)) {
            mkdir($this->rateLimitDir, 0755, true);
        }
    }

    public function __invoke(Request $request, Response $response, callable $next): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $apiKey = $request->header('X-API-KEY', '');
        $identifier = $apiKey ?: $ip;
        $key = md5($identifier);
        $file = $this->rateLimitDir . '/' . $key . '.json';

        $data = ['count' => 0, 'reset_time' => time() + $this->windowSeconds];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?? $data;
            if ($data['reset_time'] < time()) {
                $data = ['count' => 0, 'reset_time' => time() + $this->windowSeconds];
            }
        }

        $data['count']++;
        file_put_contents($file, json_encode($data), LOCK_EX);

        $remaining = max(0, $this->maxRequests - $data['count']);
        header('X-RateLimit-Limit: ' . $this->maxRequests);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . $data['reset_time']);

        if ($data['count'] > $this->maxRequests) {
            $retryAfter = $data['reset_time'] - time();
            header('Retry-After: ' . $retryAfter);
            $response->error('Rate limit exceeded. Try again in ' . $retryAfter . ' seconds', 429);
        }

        $next();
    }
}
