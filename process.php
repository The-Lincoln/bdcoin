<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

$db = Database::getInstance()->getConnection();

$payment_method = $_POST['payment_method'] ?? '';
$payer_name = $_POST['payer_name'] ?? '';
$payer_email = $_POST['payer_email'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);

if ($amount <= 0 || empty($payer_name) || empty($payer_email)) {
    header('Location: /bdpay/?error=invalid_input');
    exit;
}

$transaction_id = generateTxId();

$status = 'completed';
$payment_details = '';

try {
    switch ($payment_method) {
        case 'stripe':
            $payment_details = json_encode(['processor' => 'stripe', 'charge_id' => 'ch_' . bin2hex(random_bytes(12))]);
            break;

        case 'google_pay':
            $payment_details = json_encode(['processor' => 'google_pay', 'gpay_id' => 'gpay_' . bin2hex(random_bytes(12))]);
            break;

        case 'visa':
            $card_last4 = substr(preg_replace('/\D/', '', $_POST['card_number'] ?? ''), -4);
            $payment_details = json_encode(['processor' => 'visa', 'card_last4' => $card_last4, 'auth_code' => 'VISA' . random_int(100000, 999999)]);
            break;

        case 'crypto':
            $crypto_type = $_POST['crypto_type'] ?? 'btc';
            $payment_details = json_encode(['processor' => 'crypto', 'crypto_type' => $crypto_type, 'tx_hash' => strtolower(bin2hex(random_bytes(32)))]);
            break;

        case 'bdcoin':
            $wallet_address = $_POST['wallet_address'] ?? '';
            $tx_hash = 'BDC' . strtoupper(bin2hex(random_bytes(16)));
            $bdc_amount = $amount / BDC_PRICE;

            $stmt = $db->prepare("UPDATE bdcoin_wallet SET balance = balance - ? WHERE wallet_address = ? AND balance >= ?");
            $stmt->execute([$bdc_amount, $wallet_address, $bdc_amount]);

            if ($stmt->rowCount() === 0) {
                $payment_details = json_encode(['processor' => 'bdcoin', 'error' => 'Insufficient BDCoin balance']);
                $status = 'failed';
            } else {
                $stmt = $db->prepare("INSERT INTO bdcoin_transactions (tx_hash, from_address, to_address, amount, type, status) VALUES (?, ?, ?, ?, 'payment', 'confirmed')");
                $stmt->execute([$tx_hash, $wallet_address, 'BDC_RESERVE', $bdc_amount]);
                $payment_details = json_encode(['processor' => 'bdcoin', 'tx_hash' => $tx_hash, 'bdc_amount' => $bdc_amount]);
            }
            break;

        case 'bank':
            $payer_bank = $_POST['payer_bank'] ?? '';
            $bank_ref = $_POST['bank_ref'] ?? '';
            $payment_details = json_encode([
                'processor' => 'bank',
                'payer_bank' => $payer_bank,
                'bank_ref' => $bank_ref,
                'account_number' => '1234567890',
                'routing' => '021000021',
                'swift' => 'GLOBTK22',
            ]);
            $status = 'pending';
            break;

        case 'bkash':
            $bkash_number = $_POST['bkash_number'] ?? '';
            $bkash_trxid = $_POST['bkash_trxid'] ?? '';
            $payment_details = json_encode([
                'processor' => 'bkash',
                'bkash_number' => $bkash_number,
                'bkash_trxid' => $bkash_trxid,
                'merchant_account' => '+8801715-340463',
            ]);
            $status = 'pending';
            break;

        case 'nagad':
            $nagad_number = $_POST['nagad_number'] ?? '';
            $nagad_trxid = $_POST['nagad_trxid'] ?? '';
            $payment_details = json_encode([
                'processor' => 'nagad',
                'nagad_number' => $nagad_number,
                'nagad_trxid' => $nagad_trxid,
                'merchant_account' => '+8801715-340463',
            ]);
            $status = 'pending';
            break;

        case 'square':
            $nonce = $_POST['square_nonce'] ?? '';
            if (empty($nonce)) {
                $payment_details = json_encode(['processor' => 'square', 'error' => 'Missing card nonce']);
                $status = 'failed';
                break;
            }

            $idempotencyKey = bin2hex(random_bytes(16));
            $squarePayload = json_encode([
                'source_id' => $nonce,
                'idempotency_key' => $idempotencyKey,
                'amount_money' => [
                    'amount' => round($amount * 100),
                    'currency' => 'USD',
                ],
            ]);

            $ch = curl_init(SQUARE_API_URL . '/v2/payments');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $squarePayload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . SQUARE_ACCESS_TOKEN,
                    'Content-Type: application/json',
                    'Square-Version: 2024-01-18',
                ],
                CURLOPT_TIMEOUT => 30,
            ]);
            $squareResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $payment_details = json_encode(['processor' => 'square', 'error' => $curlError]);
                $status = 'failed';
                logError('Square cURL error', ['error' => $curlError]);
            } elseif ($httpCode >= 200 && $httpCode < 300) {
                $result = json_decode($squareResponse, true);
                $payment_details = json_encode([
                    'processor' => 'square',
                    'payment_id' => $result['payment']['id'] ?? '',
                    'order_id' => $result['payment']['order_id'] ?? '',
                    'sandbox' => true,
                ]);
            } else {
                $errorBody = json_decode($squareResponse, true);
                $errorMsg = $errorBody['errors'][0]['detail'] ?? 'Square payment failed';
                $payment_details = json_encode(['processor' => 'square', 'error' => $errorMsg, 'response' => $squareResponse]);
                $status = 'failed';
                logError('Square API error', ['http' => $httpCode, 'body' => $squareResponse]);
            }
            break;

        default:
            header('Location: /bdpay/?error=invalid_method');
            exit;
    }

    $stmt = $db->prepare("INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details) VALUES (?, ?, ?, ?, 'USD', ?, ?, ?)");
    $stmt->execute([$transaction_id, $payer_name, $payer_email, $amount, $payment_method, $status, $payment_details]);

    if ($status === 'completed') {
        $txn_data = [
            'transaction_id' => $transaction_id,
            'payer_name' => $payer_name,
            'payer_email' => $payer_email,
            'amount' => $amount,
            'payment_method' => $payment_method,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ];
        sendPaymentReceipt($txn_data);
    }

    header("Location: /bdpay/success.php?txn=$transaction_id&status=$status");
    exit;

} catch (Exception $e) {
    logError('Payment processing failed', ['method' => $payment_method, 'error' => $e->getMessage()]);
    $stmt = $db->prepare("INSERT INTO transactions (transaction_id, payer_name, payer_email, amount, currency, payment_method, status, payment_details) VALUES (?, ?, ?, ?, 'USD', ?, 'failed', ?)");
    $stmt->execute([$transaction_id, $payer_name, $payer_email, $amount, $payment_method, json_encode(['error' => $e->getMessage()])]);

    header('Location: /bdpay/?error=processing_failed');
    exit;
}
