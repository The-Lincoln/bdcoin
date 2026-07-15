<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance()->getConnection();

$payload = file_get_contents('php://input');
$headers = getallheaders();
$signature = $headers['Stripe-Signature'] ?? '';
$event = json_decode($payload, true);

if (!$event || !isset($event['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook payload']);
    exit;
}

$event_type = $event['type'];
$data = $event['data']['object'] ?? [];

switch ($event_type) {
    case 'payment_intent.succeeded':
        $txn_id = generateTxId('STR');
        $stmt = $db->prepare("INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details) VALUES (?, ?, ?, ?, 'USD', 'Stripe', 'completed', ?)");
        $stmt->execute([
            $txn_id,
            $data['metadata']['payer_name'] ?? 'Stripe Customer',
            $data['metadata']['payer_email'] ?? 'customer@example.com',
            ($data['amount'] ?? 0) / 100,
            json_encode(['stripe_id' => $data['id'], 'event' => $event_type])
        ]);
        break;

    case 'payment_intent.payment_failed':
        logError('Stripe payment failed', ['stripe_id' => $data['id'], 'error' => $data['last_payment_error'] ?? 'Unknown']);
        break;

    case 'charge.refunded':
        $stmt = $db->prepare("UPDATE transactions SET status = 'refunded' WHERE payment_details LIKE ?");
        $stmt->execute(['%' . $data['id'] . '%']);
        break;

    case 'charge.dispute.created':
        logError('Dispute created', ['charge_id' => $data['id']]);
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);
