<?php

namespace BDPay\API\Controllers;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;

class WebhookController {
    private \PDO $db;

    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }

    public function handleStripe(Request $request, Response $response): void {
        $payload = $request->input();
        $eventType = $payload['type'] ?? '';

        \logError('Stripe webhook received', ['type' => $eventType]);

        switch ($eventType) {
            case 'payment_intent.succeeded':
                $this->handleStripePaymentSuccess($payload['data']['object'] ?? []);
                break;
            case 'payment_intent.payment_failed':
                $this->handleStripePaymentFailed($payload['data']['object'] ?? []);
                break;
            case 'charge.refunded':
                $this->handleStripeRefund($payload['data']['object'] ?? []);
                break;
        }

        $response->success(null, 'Webhook received');
    }

    public function handleCrypto(Request $request, Response $response): void {
        $payload = $request->input();
        \logError('Crypto webhook received', ['data' => $payload]);

        if (!empty($payload['tx_hash']) && !empty($payload['status'])) {
            $stmt = $this->db->prepare("UPDATE bdcoin_transactions SET status = ? WHERE tx_hash = ?");
            $stmt->execute([$payload['status'], $payload['tx_hash']]);
        }

        $response->success(null, 'Webhook received');
    }

    public function handleSquare(Request $request, Response $response): void {
        $payload = $request->input();
        $eventType = $payload['type'] ?? '';

        \logError('Square webhook received via API', ['type' => $eventType]);

        switch ($eventType) {
            case 'payment.created':
            case 'payment.updated':
                $payment = $payload['data']['object']['payment'] ?? $payload['data']['object'] ?? [];
                $paymentId = $payment['id'] ?? '';
                $status = $payment['status'] ?? '';

                if ($status === 'COMPLETED') {
                    $stmt = $this->db->prepare("UPDATE transactions SET status = 'completed' WHERE payment_details LIKE ?");
                    $stmt->execute(['%"payment_id":"' . $paymentId . '"%']);

                    if ($stmt->rowCount() === 0) {
                        $txnId = \generateTxId('SQR');
                        $amount = ($payment['amount_money']['amount'] ?? 0) / 100;
                        $email = $payment['buyer_email_address'] ?? 'square@api.com';

                        $stmt = $this->db->prepare(
                            "INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details)
                             VALUES (?, 'Square Customer', ?, ?, 'USD', 'square', 'completed', ?)"
                        );
                        $stmt->execute([$txnId, $email, $amount, json_encode([
                            'webhook' => true, 'payment_id' => $paymentId, 'event' => $eventType, 'sandbox' => true,
                        ])]);
                    }
                } elseif (in_array($status, ['FAILED', 'CANCELED'])) {
                    $dbStatus = $status === 'FAILED' ? 'failed' : 'cancelled';
                    $stmt = $this->db->prepare("UPDATE transactions SET status = ? WHERE payment_details LIKE ?");
                    $stmt->execute([$dbStatus, '%"payment_id":"' . $paymentId . '"%']);
                }
                break;

            case 'payment.refunded':
                $payment = $payload['data']['object'] ?? [];
                $paymentId = $payment['id'] ?? '';
                $refundAmount = ($payment['amount_money']['amount'] ?? 0) / 100;

                $stmt = $this->db->prepare("UPDATE transactions SET status = 'refunded' WHERE payment_details LIKE ?");
                $stmt->execute(['%"payment_id":"' . $paymentId . '"%']);

                $txnRow = $this->db->prepare("SELECT transaction_id FROM transactions WHERE payment_details LIKE ?");
                $txnRow->execute(['%"payment_id":"' . $paymentId . '"%']);
                $txnData = $txnRow->fetch();
                if ($txnData) {
                    $rid = 'REF-SQR-' . strtoupper(bin2hex(random_bytes(8)));
                    $stmt = $this->db->prepare(
                        "INSERT INTO refunds (transaction_id, refund_id, amount, reason, status) VALUES (?, ?, ?, 'Square API refund', 'processed')"
                    );
                    $stmt->execute([$txnData['transaction_id'], $rid, $refundAmount]);
                }
                break;
        }

        $response->success(null, 'Square webhook received');
    }

    public function handleCustom(Request $request, Response $response): void {
        $payload = $request->input();
        \logError('Custom webhook received', ['data' => $payload]);

        if (!empty($payload['transaction_id']) && !empty($payload['status'])) {
            $stmt = $this->db->prepare("UPDATE transactions SET status = ?, payment_details = json_set(COALESCE(payment_details, '{}'), '$.webhook_update', ?) WHERE transaction_id = ?");
            $details = json_encode(['webhook_data' => $payload, 'timestamp' => date('c')]);
            $stmt->execute([$payload['status'], $details, $payload['transaction_id']]);
        }

        $response->success(null, 'Webhook received');
    }

    private function handleStripePaymentSuccess(array $paymentIntent): void {
        $txnId = $paymentIntent['id'] ?? 'pi_unknown';
        $amount = ($paymentIntent['amount'] ?? 0) / 100;
        $currency = strtoupper($paymentIntent['currency'] ?? 'usd');
        $email = $paymentIntent['receipt_email'] ?? 'webhook@stripe.com';

        $stmt = $this->db->prepare("SELECT id FROM transactions WHERE transaction_id = ?");
        $stmt->execute([$txnId]);
        if (!$stmt->fetch()) {
            $stmt = $this->db->prepare(
                "INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details)
                 VALUES (?, 'Stripe Customer', ?, ?, ?, 'stripe', 'completed', ?)"
            );
            $stmt->execute([$txnId, $email, $amount, $currency, json_encode(['webhook' => true, 'stripe_event' => 'payment_intent.succeeded'])]);
        }
    }

    private function handleStripePaymentFailed(array $paymentIntent): void {
        $txnId = $paymentIntent['id'] ?? 'pi_unknown';
        $stmt = $this->db->prepare("UPDATE transactions SET status = 'failed' WHERE transaction_id = ?");
        $stmt->execute([$txnId]);
    }

    private function handleStripeRefund(array $charge): void {
        $txnId = $charge['payment_intent'] ?? '';
        $amount = ($charge['amount_refunded'] ?? 0) / 100;
        $refundId = $charge['id'] ?? 'ref_unknown';

        if ($txnId) {
            $stmt = $this->db->prepare("UPDATE transactions SET status = 'refunded' WHERE transaction_id = ?");
            $stmt->execute([$txnId]);

            $stmt = $this->db->prepare(
                "INSERT INTO refunds (transaction_id, refund_id, amount, reason, status) VALUES (?, ?, ?, 'Stripe automatic refund', 'processed')"
            );
            $stmt->execute([$txnId, $refundId, $amount]);
        }
    }
}
