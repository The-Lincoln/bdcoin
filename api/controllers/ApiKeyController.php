<?php

namespace BDPay\API\Controllers;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;
use BDPay\API\Helpers\Validator;

class ApiKeyController {
    private \PDO $db;

    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }

    public function index(Request $request, Response $response): void {
        $stmt = $this->db->query("SELECT id, api_key, label, active, created_at, last_used_at FROM api_keys ORDER BY created_at DESC");
        $keys = $stmt->fetchAll();

        foreach ($keys as &$key) {
            $key['masked_key'] = substr($key['api_key'], 0, 8) . '...' . substr($key['api_key'], -4);
        }

        $response->success($keys);
    }

    public function generate(Request $request, Response $response): void {
        $input = $request->input();

        $validator = Validator::make($input, [
            'label' => 'required|string|max:100',
            'merchant_id' => 'string',
        ]);

        if (!$validator->passes()) {
            $response->error('Validation failed', 422, $validator->errors());
        }

        $apiKey = \generateApiKey();
        $merchantId = $input['merchant_id'] ?? $request->getAttribute('merchant_id');

        $stmt = $this->db->prepare("INSERT INTO api_keys (api_key, label, active, merchant_id) VALUES (?, ?, 1, ?)");
        $stmt->execute([$apiKey, $input['label'], $merchantId]);

        $response->created([
            'api_key' => $apiKey,
            'label' => $input['label'],
            'merchant_id' => $merchantId,
            'masked_key' => substr($apiKey, 0, 8) . '...' . substr($apiKey, -4),
        ], 'API key generated. Store it securely - it will not be shown again.');
    }

    public function revoke(Request $request, Response $response): void {
        $id = $request->param('id');
        $stmt = $this->db->prepare("UPDATE api_keys SET active = 0 WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            $response->error('API key not found', 404);
        }

        $response->success(null, 'API key revoked');
    }

    public function activate(Request $request, Response $response): void {
        $id = $request->param('id');
        $stmt = $this->db->prepare("UPDATE api_keys SET active = 1 WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            $response->error('API key not found', 404);
        }

        $response->success(null, 'API key activated');
    }
}
