<?php

namespace BDPay\API\Controllers;

use BDPay\API\Core\Request;
use BDPay\API\Core\Response;
use BDPay\API\Helpers\Validator;

class BDCoinController {
    private \PDO $db;

    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }

    public function wallets(Request $request, Response $response): void {
        $wallets = $this->db->query("SELECT wallet_address, balance, created_at FROM bdcoin_wallet ORDER BY balance DESC")->fetchAll();
        $totalSupply = (float)$this->db->query("SELECT COALESCE(SUM(balance),0) as total FROM bdcoin_wallet")->fetch()['total'];
        $circulating = $totalSupply;

        $response->success([
            'wallets' => $wallets,
            'total_supply' => $totalSupply,
            'circulating_supply' => $circulating,
            'price_usd' => \BDC_PRICE,
            'market_cap' => round($totalSupply * \BDC_PRICE, 2),
            'decimals' => \BDC_DECIMALS,
        ]);
    }

    public function transactions(Request $request, Response $response): void {
        $page = max(1, (int)$request->query('page', 1));
        $perPage = min(100, max(1, (int)$request->query('per_page', 20)));
        $offset = ($page - 1) * $perPage;
        $type = $request->query('type');
        $address = $request->query('address');

        $where = [];
        $params = [];

        if ($type) {
            $where[] = 'type = ?';
            $params[] = $type;
        }
        if ($address) {
            $where[] = '(from_address = ? OR to_address = ?)';
            $params[] = $address;
            $params[] = $address;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM bdcoin_transactions $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $this->db->prepare(
            "SELECT * FROM bdcoin_transactions $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, $perPage, $offset]);

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

    public function send(Request $request, Response $response): void {
        $input = $request->input();

        $validator = Validator::make($input, [
            'from_address' => 'required|string',
            'to_address' => 'required|string',
            'amount' => 'required|numeric|min:0.0001',
        ]);

        if (!$validator->passes()) {
            $response->error('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        $stmt = $this->db->prepare("SELECT balance FROM bdcoin_wallet WHERE wallet_address = ?");
        $stmt->execute([$data['from_address']]);
        $fromWallet = $stmt->fetch();

        if (!$fromWallet) {
            $response->error('Source wallet not found', 404);
        }

        if ((float)$fromWallet['balance'] < (float)$data['amount']) {
            $response->error('Insufficient balance', 400);
        }

        $stmt = $this->db->prepare("SELECT id FROM bdcoin_wallet WHERE wallet_address = ?");
        $stmt->execute([$data['to_address']]);
        if (!$stmt->fetch()) {
            $stmt = $this->db->prepare("INSERT INTO bdcoin_wallet (wallet_address, balance) VALUES (?, 0)");
            $stmt->execute([$data['to_address']]);
        }

        $txHash = 'BDC' . bin2hex(random_bytes(16));

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE bdcoin_wallet SET balance = balance - ? WHERE wallet_address = ?");
            $stmt->execute([$data['amount'], $data['from_address']]);

            $stmt = $this->db->prepare("UPDATE bdcoin_wallet SET balance = balance + ? WHERE wallet_address = ?");
            $stmt->execute([$data['amount'], $data['to_address']]);

            $stmt = $this->db->prepare(
                "INSERT INTO bdcoin_transactions (tx_hash, from_address, to_address, amount, type, status)
                 VALUES (?, ?, ?, ?, 'transfer', 'completed')"
            );
            $stmt->execute([$txHash, $data['from_address'], $data['to_address'], $data['amount']]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            \logError('BDCoin transfer failed', ['error' => $e->getMessage()]);
            $response->error('Transfer failed. Please try again.', 500);
        }

        $response->created([
            'tx_hash' => $txHash,
            'from_address' => $data['from_address'],
            'to_address' => $data['to_address'],
            'amount' => (float)$data['amount'],
            'type' => 'transfer',
            'status' => 'completed',
        ], 'Transfer completed successfully');
    }

    public function createWallet(Request $request, Response $response): void {
        $address = 'BDC' . strtoupper(bin2hex(random_bytes(10)));

        $stmt = $this->db->prepare("INSERT INTO bdcoin_wallet (wallet_address, balance) VALUES (?, 0)");
        $stmt->execute([$address]);

        $response->created([
            'wallet_address' => $address,
            'balance' => 0,
        ], 'Wallet created');
    }

    public function getBalance(Request $request, Response $response): void {
        $address = $request->param('address');
        $stmt = $this->db->prepare("SELECT wallet_address, balance, created_at FROM bdcoin_wallet WHERE wallet_address = ?");
        $stmt->execute([$address]);
        $wallet = $stmt->fetch();

        if (!$wallet) {
            $response->error('Wallet not found', 404);
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as tx_count, COALESCE(SUM(amount),0) as total_sent FROM bdcoin_transactions WHERE from_address = ? AND status = 'completed'"
        );
        $stmt->execute([$address]);
        $sent = $stmt->fetch();

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as tx_count, COALESCE(SUM(amount),0) as total_received FROM bdcoin_transactions WHERE to_address = ? AND status = 'completed'"
        );
        $stmt->execute([$address]);
        $received = $stmt->fetch();

        $wallet['total_sent'] = (float)$sent['total_sent'];
        $wallet['total_received'] = (float)$received['total_received'];
        $wallet['transaction_count'] = (int)$sent['tx_count'] + (int)$received['tx_count'];

        $response->success($wallet);
    }
}
