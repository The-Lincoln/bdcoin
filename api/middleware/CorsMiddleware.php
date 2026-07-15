<?php

namespace BDPay\API\Middleware;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;

class CorsMiddleware {
    private array $allowedOrigins;
    private array $allowedMethods;
    private array $allowedHeaders;

    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-API-Key', 'X-Requested-With']
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
    }

    public function __invoke(Request $request, Response $response, callable $next): void {
        $origin = $request->header('Origin', '*');

        if (in_array('*', $this->allowedOrigins)) {
            header('Access-Control-Allow-Origin: *');
        } elseif (in_array($origin, $this->allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
        header('Access-Control-Max-Age: 86400');

        if ($request->isMethod('OPTIONS')) {
            http_response_code(204);
            exit;
        }

        $next();
    }
}
