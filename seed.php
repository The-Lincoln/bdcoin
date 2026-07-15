<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /bdpay/login.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();

$names = ['Alice Johnson', 'Bob Smith', 'Carol Williams', 'David Brown', 'Eva Martinez', 'Frank Lee', 'Grace Kim', 'Henry Davis', 'Iris Chen', 'Jack Wilson'];
$methods = ['stripe', 'google_pay', 'visa', 'crypto', 'bdcoin'];
$statuses = ['completed', 'completed', 'completed', 'pending', 'completed', 'completed', 'failed'];

$db->beginTransaction();
for ($i = 0; $i < 25; $i++) {
    $txn_id = generateTxId();
    $name = $names[array_rand($names)];
    $email = strtolower(str_replace(' ', '.', $name)) . '@example.com';
    $amount = round(mt_rand(500, 50000) / 100, 2);
    $method = $methods[array_rand($methods)];
    $status = $statuses[array_rand($statuses)];
    $created = date('Y-m-d H:i:s', strtotime("-" . mt_rand(0, 30) . " days " . mt_rand(0, 24) . " hours"));
    $stmt = $db->prepare("INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details, created_at) VALUES (?, ?, ?, ?, 'USD', ?, ?, ?, ?)");
    $stmt->execute([$txn_id, $name, $email, $amount, ucwords(str_replace('_', ' ', $method)), $status, json_encode(['demo' => true, 'seed' => 'v2']), $created]);

    if ($method === 'bdcoin' && $status === 'completed') {
        $bdc_amount = $amount / BDC_PRICE;
        $wallet = $db->query("SELECT wallet_address FROM bdcoin_wallet ORDER BY RANDOM() LIMIT 1")->fetch();
        if ($wallet) {
            $stmt = $db->prepare("UPDATE bdcoin_wallet SET balance = balance - ? WHERE wallet_address = ?");
            $stmt->execute([$bdc_amount, $wallet['wallet_address']]);
            $tx_hash = 'BDC' . strtoupper(bin2hex(random_bytes(16)));
            $stmt = $db->prepare("INSERT INTO bdcoin_transactions (tx_hash, from_address, to_address, amount, type, status, created_at) VALUES (?, ?, 'BDC_RESERVE', ?, 'payment', 'confirmed', ?)");
            $stmt->execute([$tx_hash, $wallet['wallet_address'], $bdc_amount, $created]);
        }
    }
}
$db->commit();

echo json_encode(['success' => true, 'seeded' => 25]);
