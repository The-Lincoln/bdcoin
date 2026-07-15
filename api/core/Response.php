<?php

namespace BDPay\API\Core;

class Response {
    private int $statusCode = 200;
    private array $headers = [];
    private mixed $body = null;
    private bool $sent = false;

    public function status(int $code): self {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $key, string $value): self {
        $this->headers[$key] = $value;
        return $this;
    }

    public function json(mixed $data, int $status = null): never {
        if ($status) $this->statusCode = $status;
        $this->body = $data;
        $this->send('application/json');
    }

    public function success(mixed $data = null, string $message = 'Success', int $status = 200): never {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function error(string $message, int $status = 400, mixed $errors = null): never {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) $payload['errors'] = $errors;
        $this->json($payload, $status);
    }

    public function created(mixed $data = null, string $message = 'Created successfully'): never {
        $this->success($data, $message, 201);
    }

    public function noContent(): never {
        http_response_code(204);
        exit;
    }

    private function send(string $contentType): void {
        if ($this->sent) return;
        $this->sent = true;
        http_response_code($this->statusCode);
        header('Content-Type: ' . $contentType . '; charset=utf-8');
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
