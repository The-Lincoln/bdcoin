<?php

namespace BDPay\API\Controllers;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;
use BDPay\API\Helpers\Validator;

class MerchantController {
    private \PDO $db;

    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }

    public function register(Request $request, Response $response): void {
        $input = $request->input();

        $validator = Validator::make($input, [
            'business_name' => 'required|string|max:200',
            'contact_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'payment_method' => 'required|in:bdcoin,crypto,stripe,google_pay,visa,square',
            'wallet_address' => 'string',
        ]);

        if (!$validator->passes()) {
            $response->error('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();
        $merchantId = strtoupper(substr($data['payment_method'], 0, 4)) . 'M-' . strtoupper(bin2hex(random_bytes(8)));

        $stmt = $this->db->prepare(
            "INSERT INTO merchants (merchant_id, business_name, contact_name, email, payment_method, wallet_address, status)
             VALUES (?, ?, ?, ?, ?, ?, 'active')"
        );
        $stmt->execute([$merchantId, $data['business_name'], $data['contact_name'], $data['email'], $data['payment_method'], $data['wallet_address'] ?? null]);

        if (!empty($data['wallet_address']) && $data['payment_method'] === 'bdcoin') {
            $stmt = $this->db->prepare("INSERT OR IGNORE INTO bdcoin_wallet (wallet_address, balance) VALUES (?, 0)");
            $stmt->execute([$data['wallet_address']]);
        }

        $apiKey = \generateApiKey();
        $stmt = $this->db->prepare("INSERT INTO api_keys (api_key, label, active, merchant_id) VALUES (?, ?, 1, ?)");
        $stmt->execute([$apiKey, $data['business_name'], $merchantId]);

        $response->created([
            'merchant_id' => $merchantId,
            'business_name' => $data['business_name'],
            'contact_name' => $data['contact_name'],
            'email' => $data['email'],
            'payment_method' => $data['payment_method'],
            'wallet_address' => $data['wallet_address'] ?? null,
            'api_key' => $apiKey,
            'status' => 'active',
        ], 'Merchant registered successfully. Store your API key securely.');
    }

    public function profile(Request $request, Response $response): void {
        $merchantId = $request->getAttribute('merchant_id');
        if (!$merchantId) {
            $response->error('No merchant associated with this API key', 404);
        }

        $stmt = $this->db->prepare("SELECT * FROM merchants WHERE merchant_id = ?");
        $stmt->execute([$merchantId]);
        $merchant = $stmt->fetch();

        if (!$merchant) {
            $response->error('Merchant not found', 404);
        }

        $stmt = $this->db->prepare("SELECT id, label, active, created_at, last_used_at FROM api_keys WHERE merchant_id = ?");
        $stmt->execute([$merchantId]);
        $merchant['api_keys'] = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(amount),0) as revenue FROM transactions WHERE status = 'completed'");
        $stmt->execute();
        $stats = $stmt->fetch();
        $merchant['stats'] = $stats;

        $response->success($merchant);
    }

    public function updateProfile(Request $request, Response $response): void {
        $merchantId = $request->getAttribute('merchant_id');
        if (!$merchantId) {
            $response->error('No merchant associated with this API key', 404);
        }

        $input = $request->input();
        $fields = [];
        $params = [];

        foreach (['business_name', 'contact_name', 'email', 'wallet_address'] as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }

        if (empty($fields)) {
            $response->error('No fields to update', 400);
        }

        $params[] = $merchantId;
        $stmt = $this->db->prepare("UPDATE merchants SET " . implode(', ', $fields) . " WHERE merchant_id = ?");
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            $response->error('Merchant not found', 404);
        }

        $stmt = $this->db->prepare("SELECT * FROM merchants WHERE merchant_id = ?");
        $stmt->execute([$merchantId]);

        $response->success($stmt->fetch(), 'Profile updated');
    }

    public function transactions(Request $request, Response $response): void {
        $merchantId = $request->getAttribute('merchant_id');
        if (!$merchantId) {
            $response->error('No merchant associated with this API key', 404);
        }

        $stmt = $this->db->prepare("SELECT payment_method FROM merchants WHERE merchant_id = ?");
        $stmt->execute([$merchantId]);
        $merchant = $stmt->fetch();

        if (!$merchant) {
            $response->error('Merchant not found', 404);
        }

        $page = max(1, (int)$request->query('page', 1));
        $perPage = min(100, max(1, (int)$request->query('per_page', 20)));
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'completed'");
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $this->db->prepare(
            "SELECT transaction_id, payer_name, payer_email, amount, currency, payment_method, status, created_at
             FROM transactions WHERE status = 'completed' ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);

        $response->success([
            'transactions' => $stmt->fetchAll(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function settlements(Request $request, Response $response): void {
        $merchantId = $request->getAttribute('merchant_id');
        if (!$merchantId) {
            $response->error('No merchant associated with this API key', 404);
        }

        $stmt = $this->db->prepare("SELECT payment_method FROM merchants WHERE merchant_id = ?");
        $stmt->execute([$merchantId]);
        $merchant = $stmt->fetch();

        if (!$merchant) {
            $response->error('Merchant not found', 404);
        }

        $period = $request->query('period', 'all');

        $where = "status = 'completed'";
        if ($period === 'today') {
            $where .= " AND date(created_at) = date('now')";
        } elseif ($period === 'week') {
            $where .= " AND created_at >= date('now', '-7 days')";
        } elseif ($period === 'month') {
            $where .= " AND created_at >= date('now', '-30 days')";
        } elseif ($period === 'year') {
            $where .= " AND created_at >= date('now', '-365 days')";
        }

        $stmt = $this->db->query(
            "SELECT date(created_at) as settlement_date,
                    COUNT(*) as transaction_count,
                    COALESCE(SUM(amount),0) as total_amount,
                    COALESCE(SUM(CASE WHEN payment_method IN ('crypto','bdcoin') THEN amount ELSE 0 END),0) as crypto_amount,
                    COALESCE(SUM(CASE WHEN payment_method NOT IN ('crypto','bdcoin') THEN amount ELSE 0 END),0) as fiat_amount
             FROM transactions WHERE $where
             GROUP BY date(created_at) ORDER BY settlement_date DESC LIMIT 90"
        );

        $stmt2 = $this->db->query(
            "SELECT COALESCE(SUM(amount),0) as total,
                    COUNT(*) as count,
                    COALESCE(AVG(amount),0) as average
             FROM transactions WHERE $where"
        );
        $summary = $stmt2->fetch();

        $response->success([
            'settlements' => $stmt->fetchAll(),
            'summary' => $summary,
            'period' => $period,
        ]);
    }
}
