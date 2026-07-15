<?php

namespace BDPay\API\Controllers;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;
use BDPay\API\Helpers\Validator;

class RefundController {
    private \PDO $db;

    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }

    public function index(Request $request, Response $response): void {
        $stmt = $this->db->query("SELECT * FROM refunds ORDER BY created_at DESC LIMIT 50");
        $response->success($stmt->fetchAll());
    }

    public function process(Request $request, Response $response): void {
        $input = $request->input();

        $validator = Validator::make($input, [
            'transaction_id' => 'required|string',
            'amount' => 'numeric|min:0.01',
            'reason' => 'string|max:1000',
        ]);

        if (!$validator->passes()) {
            $response->error('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
        $stmt->execute([$data['transaction_id']]);
        $txn = $stmt->fetch();

        if (!$txn) {
            $response->error('Transaction not found', 404);
        }

        if ($txn['status'] !== 'completed') {
            $response->error('Only completed transactions can be refunded', 400);
        }

        $refundAmount = $data['amount'] ?? $txn['amount'];
        $refundId = 'REF-' . strtoupper(bin2hex(random_bytes(8)));

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO refunds (transaction_id, refund_id, amount, reason, status) VALUES (?, ?, ?, ?, 'processed')"
            );
            $stmt->execute([$txn['transaction_id'], $refundId, $refundAmount, $data['reason'] ?? null]);

            $stmt = $this->db->prepare("UPDATE transactions SET status = 'refunded' WHERE transaction_id = ?");
            $stmt->execute([$txn['transaction_id']]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            \logError('Refund failed', ['txn' => $data['transaction_id'], 'error' => $e->getMessage()]);
            $response->error('Refund processing failed', 500);
        }

        $response->created([
            'refund_id' => $refundId,
            'transaction_id' => $txn['transaction_id'],
            'amount' => (float)$refundAmount,
            'status' => 'processed',
        ], 'Refund processed successfully');
    }
}
