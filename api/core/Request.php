<?php

namespace BDPay\API\Core;

class Request {
    private array $query;
    private array $body;
    private array $headers;
    private array $files;
    private string $method;
    private string $uri;
    private array $params = [];
    private array $attributes = [];

    public function __construct() {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->query = $_GET;
        $this->headers = $this->parseHeaders();
        $this->files = $_FILES;
        $this->body = $this->parseBody();
    }

    private function parseHeaders(): array {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) $headers['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE'];
        if (isset($_SERVER['CONTENT_LENGTH'])) $headers['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH'];
        return $headers;
    }

    private function parseBody(): array {
        if ($this->method === 'GET') return [];
        $contentType = $this->header('Content-Type', '');
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        }
        if (str_contains($contentType, 'multipart/form-data')) {
            return $_POST;
        }
        return $_POST;
    }

    public function method(): string { return $this->method; }
    public function uri(): string { return $this->uri; }
    public function query(?string $key = null, mixed $default = null): mixed {
        if ($key === null) return $this->query;
        return $this->query[$key] ?? $default;
    }
    public function input(?string $key = null, mixed $default = null): mixed {
        if ($key === null) return $this->body;
        return $this->body[$key] ?? $default;
    }
    public function header(?string $key = null, mixed $default = null): mixed {
        if ($key === null) return $this->headers;
        return $this->headers[$key] ?? $default;
    }
    public function file(?string $key = null): mixed {
        if ($key === null) return $this->files;
        return $this->files[$key] ?? null;
    }
    public function param(?string $key = null, mixed $default = null): mixed {
        if ($key === null) return $this->params;
        return $this->params[$key] ?? $default;
    }

    public function setParams(array $params): void { $this->params = $params; }
    public function setAttribute(string $key, mixed $value): void { $this->attributes[$key] = $value; }
    public function getAttribute(string $key, mixed $default = null): mixed { return $this->attributes[$key] ?? $default; }
    public function isMethod(string $method): bool { return $this->method === strtoupper($method); }
    public function isAjax(): bool { return $this->header('X-Requested-With') === 'XMLHttpRequest'; }
}
