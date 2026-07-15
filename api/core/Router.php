<?php

namespace BDPay\API\Core;

class Router {
    private array $routes = [];
    private array $groupMiddleware = [];
    private array $globalMiddleware = [];
    private string $groupPrefix = '';
    private ?Request $request = null;
    private ?Response $response = null;

    public function __construct() {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function getRequest(): Request { return $this->request; }
    public function getResponse(): Response { return $this->response; }

    public function get(string $pattern, callable|array $handler): self {
        return $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): self {
        return $this->addRoute('POST', $pattern, $handler);
    }

    public function put(string $pattern, callable|array $handler): self {
        return $this->addRoute('PUT', $pattern, $handler);
    }

    public function patch(string $pattern, callable|array $handler): self {
        return $this->addRoute('PATCH', $pattern, $handler);
    }

    public function delete(string $pattern, callable|array $handler): self {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    public function any(string $pattern, callable|array $handler): self {
        return $this->addRoute('ANY', $pattern, $handler);
    }

    public function match(array $methods, string $pattern, callable|array $handler): self {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $pattern, $handler);
        }
        return $this;
    }

    public function addMiddleware(callable $middleware): self {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void {
        $previousPrefix = $this->groupPrefix;
        $previousGroupMiddleware = $this->groupMiddleware;
        $this->groupPrefix = $previousPrefix . $prefix;
        if (!empty($middleware)) {
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        }
        $callback($this);
        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousGroupMiddleware;
    }

    private function addRoute(string $method, string $pattern, callable|array $handler): self {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $this->groupPrefix . $pattern,
            'handler' => $handler,
            'middleware' => $this->groupMiddleware,
        ];
        return $this;
    }

    public function dispatch(): void {
        $method = $this->request->method();
        $uri = $this->request->uri();
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $path = '/' . trim(str_replace($basePath, '', $uri), '/');
        if ($path === '') $path = '/';

        $matchedRoute = null;
        $matchedParams = [];

        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) continue;
            $params = $this->matchPattern($route['pattern'], $path);
            if ($params !== false) {
                $matchedRoute = $route;
                $matchedParams = $params;
                break;
            }
        }

        if ($matchedRoute === null) {
            $this->response->error('Endpoint not found', 404);
        }

        $this->request->setParams($matchedParams);

        $middlewareChain = array_merge(
            $this->globalMiddleware,
            $matchedRoute['middleware']
        );

        $handler = $matchedRoute['handler'];
        $stack = $this->buildMiddlewareStack($middlewareChain, $handler);
        $stack();
    }

    private function matchPattern(string $pattern, string $path): array|false {
        $patternRegex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $patternRegex = str_replace(['/', '*'], ['\/', '.*'], $patternRegex);
        $patternRegex = '/^' . $patternRegex . '$/';
        if (preg_match($patternRegex, $path, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }
        return false;
    }

    private function buildMiddlewareStack(array $middleware, callable|array $handler): callable {
        $stack = function () use ($handler) {
            $this->callHandler($handler);
        };

        foreach (array_reverse($middleware) as $mw) {
            $next = $stack;
            $stack = function () use ($mw, $next) {
                $mw($this->request, $this->response, $next);
            };
        }

        return $stack;
    }

    private function callHandler(callable|array $handler): void {
        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $instance = new $class();
            $instance->$method($this->request, $this->response);
        } else {
            $handler($this->request, $this->response);
        }
    }
}
