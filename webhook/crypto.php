<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance()->getConnection();

$payload = json_decode(file_get_contents('php://input'), true);

if (!$payload || !isset($payload['event'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$event = $payload['event'];
$tx_hash = $payload['tx_hash'] ?? '';
$asset = $payload['asset'] ?? '';
$amount = floatval($payload['amount'] ?? 0);
$from_addr = $payload['from'] ?? '';
$to_addr = $payload['to'] ?? '';
$confirmations = intval($payload['confirmations'] ?? 0);
$status = $payload['status'] ?? '';

switch ($event) {
    case 'payment.received':
        if ($confirmations >= 3) {
            $txn_id = generateTxId('CRYPTO');
            $stmt = $db->prepare("INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details) VALUES (?, 'Crypto User', 'crypto@blockchain.com', ?, 'USD', 'Cryptocurrency', 'completed', ?)");
            $stmt->execute([$txn_id, $amount, json_encode(['tx_hash' => $tx_hash, 'asset' => $asset, 'confirmations' => $confirmations])]);
        }
        break;

    case 'payment.confirmed':
        $stmt = $db->prepare("UPDATE transactions SET status = 'completed' WHERE payment_details LIKE ?");
        $stmt->execute(['%' . $tx_hash . '%']);
        break;

    case 'payment.failed':
        logError('Crypto payment failed', ['tx_hash' => $tx_hash, 'reason' => $payload['reason'] ?? 'Unknown']);
        break;

    case 'bdcoin.transfer':
        if ($status === 'confirmed') {
            $stmt = $db->prepare("UPDATE bdcoin_wallet SET balance = balance - ? WHERE wallet_address = ?");
            $stmt->execute([$amount, $from_addr]);
            $stmt = $db->prepare("UPDATE bdcoin_wallet SET balance = balance + ? WHERE wallet_address = ?");
            $stmt->execute([$amount, $to_addr]);

            $stmt = $db->prepare("INSERT INTO bdcoin_transactions (tx_hash, from_address, to_address, amount, type, status) VALUES (?, ?, ?, ?, 'transfer', 'confirmed')");
            $stmt->execute([$tx_hash, $from_addr, $to_addr, $amount]);
        }
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);
