<?php

namespace BDPay\API\Controllers;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;
use BDPay\API\Helpers\Validator;

class PaymentController {
    private \PDO $db;

    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }

    public function index(Request $request, Response $response): void {
        $page = max(1, (int)$request->query('page', 1));
        $perPage = min(100, max(1, (int)$request->query('per_page', 20)));
        $status = $request->query('status');
        $method = $request->query('method');
        $search = $request->query('search');
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($method) {
            $where[] = 'payment_method = ?';
            $params[] = $method;
        }
        if ($search) {
            $where[] = '(transaction_id LIKE ? OR payer_name LIKE ? OR payer_email LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM transactions $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $this->db->prepare(
            "SELECT * FROM transactions $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, $perPage, $offset]);
        $transactions = $stmt->fetchAll();

        foreach ($transactions as &$txn) {
            $txn['payment_details'] = json_decode($txn['payment_details'] ?? '{}', true);
            $txn['amount_formatted'] = \formatAmount($txn['amount'], $txn['currency']);
        }

        $response->success([
            'transactions' => $transactions,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int)ceil($total / $perPage),
                'has_next' => ($offset + $perPage) < $total,
                'has_prev' => $page > 1,
            ],
        ]);
    }

    public function show(Request $request, Response $response): void {
        $id = $request->param('id');
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE transaction_id = ? OR id = ?");
        $stmt->execute([$id, $id]);
        $txn = $stmt->fetch();

        if (!$txn) {
            $response->error('Transaction not found', 404);
        }

        $txn['payment_details'] = json_decode($txn['payment_details'] ?? '{}', true);
        $txn['amount_formatted'] = \formatAmount($txn['amount'], $txn['currency']);

        $refundStmt = $this->db->prepare("SELECT * FROM refunds WHERE transaction_id = ?");
        $refundStmt->execute([$txn['transaction_id']]);
        $txn['refunds'] = $refundStmt->fetchAll();

        $response->success($txn);
    }

    public function store(Request $request, Response $response): void {
        $input = $request->input();

        $validator = Validator::make($input, [
            'payer_name' => 'required|string|max:255',
            'payer_email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'in:USD,EUR,GBP,BDC',
            'payment_method' => 'required|in:stripe,google_pay,visa,crypto,bdcoin,square',
            'description' => 'string|max:1000',
        ]);

        if (!$validator->passes()) {
            $response->error('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();
        $txnId = \generateTxId();

        $paymentDetails = [
            'api' => true,
            'source' => 'api_v2',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'description' => $data['description'] ?? null,
            'timestamp' => date('c'),
        ];

        $stmt = $this->db->prepare(
            "INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details)
             VALUES (?, ?, ?, ?, ?, ?, 'completed', ?)"
        );
        $stmt->execute([
            $txnId,
            $data['payer_name'],
            $data['payer_email'],
            $data['amount'],
            $data['currency'] ?? 'USD',
            $data['payment_method'],
            json_encode($paymentDetails),
        ]);

        try {
            \sendPaymentReceipt([
                'transaction_id' => $txnId,
                'payer_email' => $data['payer_email'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            \logError('Failed to send receipt', ['txn' => $txnId, 'error' => $e->getMessage()]);
        }

        $response->created([
            'transaction_id' => $txnId,
            'amount' => (float)$data['amount'],
            'currency' => $data['currency'] ?? 'USD',
            'payment_method' => $data['payment_method'],
            'status' => 'completed',
            'amount_formatted' => \formatAmount($data['amount'], $data['currency'] ?? 'USD'),
            'receipt_url' => \BASE_URL . 'invoice.php?txn=' . $txnId,
        ], 'Payment processed successfully');
    }

    public function update(Request $request, Response $response): void {
        $id = $request->param('id');
        $input = $request->input();

        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE transaction_id = ? OR id = ?");
        $stmt->execute([$id, $id]);
        $txn = $stmt->fetch();

        if (!$txn) {
            $response->error('Transaction not found', 404);
        }

        $validator = Validator::make($input, [
            'status' => 'in:pending,completed,failed,refunded,cancelled',
            'payer_name' => 'string|max:255',
            'payer_email' => 'email|max:255',
        ]);

        if (!$validator->passes()) {
            $response->error('Validation failed', 422, $validator->errors());
        }

        $updates = [];
        $params = [];
        foreach (['status', 'payer_name', 'payer_email'] as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $params[] = $input[$field];
            }
        }

        if (empty($updates)) {
            $response->error('No valid fields to update', 400);
        }

        $params[] = $txn['id'];
        $stmt = $this->db->prepare(
            "UPDATE transactions SET " . implode(', ', $updates) . " WHERE id = ?"
        );
        $stmt->execute($params);

        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE id = ?");
        $stmt->execute([$txn['id']]);
        $response->success($stmt->fetch(), 'Transaction updated');
    }

    public function destroy(Request $request, Response $response): void {
        $id = $request->param('id');
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE transaction_id = ? OR id = ?");
        $stmt->execute([$id, $id]);
        $txn = $stmt->fetch();

        if (!$txn) {
            $response->error('Transaction not found', 404);
        }

        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$txn['id']]);

        $response->success(null, 'Transaction deleted');
    }

    public function stats(Request $request, Response $response): void {
        $period = $request->query('period', 'all');
        $dateFilter = '';

        switch ($period) {
            case 'today':
                $dateFilter = "WHERE date(created_at) = date('now')";
                break;
            case 'week':
                $dateFilter = "WHERE created_at >= date('now', '-7 days')";
                break;
            case 'month':
                $dateFilter = "WHERE created_at >= date('now', '-30 days')";
                break;
            case 'year':
                $dateFilter = "WHERE created_at >= date('now', '-1 year')";
                break;
        }

        $response->success([
            'total_transactions' => (int)$this->db->query("SELECT COUNT(*) as c FROM transactions $dateFilter")->fetch()['c'],
            'total_revenue' => (float)$this->db->query("SELECT COALESCE(SUM(amount),0) as s FROM transactions $dateFilter AND status='completed'")->fetch()['s'],
            'completed_count' => (int)$this->db->query("SELECT COUNT(*) as c FROM transactions $dateFilter AND status='completed'")->fetch()['c'],
            'pending_count' => (int)$this->db->query("SELECT COUNT(*) as c FROM transactions $dateFilter AND status='pending'")->fetch()['c'],
            'failed_count' => (int)$this->db->query("SELECT COUNT(*) as c FROM transactions $dateFilter AND status='failed'")->fetch()['c'],
            'refunded_count' => (int)$this->db->query("SELECT COUNT(*) as c FROM transactions $dateFilter AND status='refunded'")->fetch()['c'],
            'by_method' => $this->db->query("SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM transactions $dateFilter GROUP BY payment_method")->fetchAll(),
            'by_currency' => $this->db->query("SELECT currency, COUNT(*) as count, SUM(amount) as total FROM transactions $dateFilter GROUP BY currency")->fetchAll(),
        ]);
    }
}
