<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance()->getConnection();

$payload = file_get_contents('php://input');
$headers = getallheaders();
$signature = $headers['x-square-hmacsha256-signature'] ?? '';
$notificationUrl = $headers['x-square-notification-url'] ?? '';

$event = json_decode($payload, true);

if (!$event || !isset($event['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook payload']);
    exit;
}

$eventType = $event['type'];
$data = $event['data']['object']['payment'] ?? $event['data']['object'] ?? [];

logError('Square webhook received', ['type' => $eventType, 'payment_id' => $data['id'] ?? '']);

switch ($eventType) {
    case 'payment.created':
    case 'payment.updated':
        $paymentId = $data['id'] ?? '';
        $status = $data['status'] ?? '';
        $orderId = $data['order_id'] ?? '';

        if ($status === 'COMPLETED') {
            $stmt = $db->prepare("UPDATE transactions SET status = 'completed' WHERE payment_details LIKE ?");
            $stmt->execute(['%"payment_id":"' . $paymentId . '"%']);

            if ($stmt->rowCount() === 0) {
                $txnId = generateTxId('SQR');
                $amount = ($data['amount_money']['amount'] ?? 0) / 100;
                $email = $data['buyer_email_address'] ?? 'square@webhook.com';

                $stmt = $db->prepare(
                    "INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details)
                     VALUES (?, 'Square Customer', ?, ?, 'USD', 'square', 'completed', ?)"
                );
                $stmt->execute([$txnId, $email, $amount, json_encode([
                    'webhook' => true,
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                    'event' => $eventType,
                    'sandbox' => true,
                ])]);
            }
        } elseif (in_array($status, ['FAILED', 'CANCELED'])) {
            $dbStatus = $status === 'FAILED' ? 'failed' : 'cancelled';
            $stmt = $db->prepare("UPDATE transactions SET status = ? WHERE payment_details LIKE ?");
            $stmt->execute([$dbStatus, '%"payment_id":"' . $paymentId . '"%']);
        }
        break;

    case 'payment.refunded':
        $paymentId = $data['id'] ?? '';
        $refundAmount = ($data['amount_money']['amount'] ?? 0) / 100;

        $stmt = $db->prepare("UPDATE transactions SET status = 'refunded' WHERE payment_details LIKE ?");
        $stmt->execute(['%"payment_id":"' . $paymentId . '"%']);

        $txn = $db->prepare("SELECT transaction_id FROM transactions WHERE payment_details LIKE ?");
        $txn->execute(['%"payment_id":"' . $paymentId . '"%']);
        $txnData = $txn->fetch();

        if ($txnData) {
            $refundId = 'REF-SQR-' . strtoupper(bin2hex(random_bytes(8)));
            $stmt = $db->prepare(
                "INSERT INTO refunds (transaction_id, refund_id, amount, reason, status)
                 VALUES (?, ?, ?, 'Square webhook refund', 'processed')"
            );
            $stmt->execute([$txnData['transaction_id'], $refundId, $refundAmount]);
        }
        break;

    case 'payment.dispute.created':
        logError('Square dispute created', ['payment_id' => $data['payment_id'] ?? '']);
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);
