<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();

$action = $argv[1] ?? 'status';

switch ($action) {
    case 'cleanup':
        $days = intval($argv[2] ?? 90);
        $stmt = $db->prepare("DELETE FROM transactions WHERE status IN ('failed', 'cancelled') AND created_at < datetime('now', ?)");
        $stmt->execute(["-{$days} days"]);
        echo "[CLEANUP] Removed " . $stmt->rowCount() . " old failed transactions\n";
        break;

    case 'bdcoin_mint':
        $amount = floatval($argv[2] ?? 10000);
        $address = 'BDC_MINT_' . strtoupper(bin2hex(random_bytes(8)));
        $tx_hash = 'MINT' . strtoupper(bin2hex(random_bytes(16)));

        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO bdcoin_wallet (wallet_address, balance) VALUES (?, ?)");
        $stmt->execute([$address, $amount]);
        $stmt = $db->prepare("INSERT INTO bdcoin_transactions (tx_hash, from_address, to_address, amount, type, status) VALUES (?, 'BDC_MINTER', ?, ?, 'mint', 'confirmed')");
        $stmt->execute([$tx_hash, $address, $amount]);
        $db->commit();

        echo "[MINT] Created {$amount} BDC at address: {$address}\n";
        echo "[MINT] TX: {$tx_hash}\n";
        break;

    case 'status':
        $txns = $db->query("SELECT COUNT(*) as c FROM transactions")->fetch()['c'];
        $bdc = $db->query("SELECT SUM(balance) as s FROM bdcoin_wallet")->fetch()['s'];
        $bdc_txns = $db->query("SELECT COUNT(*) as c FROM bdcoin_transactions")->fetch()['c'];
        echo "=== BDPay System Status ===\n";
        echo "Transactions:     {$txns}\n";
        echo "BDCoin Supply:    " . number_format($bdc, 4) . " BDC\n";
        echo "BDCoin TXs:       {$bdc_txns}\n";
        echo "PHP Version:      " . PHP_VERSION . "\n";
        echo "SQLite Version:   " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        echo "===========================\n";
        break;

    case 'report':
        $period = $argv[2] ?? 'today';
        $date_map = ['today' => 'date("now")', 'week' => "date('now', '-7 days')", 'month' => "date('now', '-30 days')", 'year' => "date('now', '-365 days')"];
        $date_sql = $date_map[$period] ?? $date_map['today'];

        $revenue = $db->query("SELECT COALESCE(SUM(amount),0) as s FROM transactions WHERE status='completed' AND date(created_at) >= {$date_sql}")->fetch()['s'];
        $count = $db->query("SELECT COUNT(*) as c FROM transactions WHERE date(created_at) >= {$date_sql}")->fetch()['c'];

        echo "=== BDPay Report ({$period}) ===\n";
        echo "Revenue:    \$" . number_format($revenue, 2) . "\n";
        echo "Volume:     {$count} transactions\n";
        echo "Avg/Txn:    \$" . ($count > 0 ? number_format($revenue / $count, 2) : "0.00") . "\n";
        echo "==============================\n";
        break;

    default:
        echo "Usage: php cron.php [command]\n";
        echo "  status              - System status\n";
        echo "  cleanup [days]      - Remove old failed transactions\n";
        echo "  bdcoin_mint [amt]   - Mint new BDCoin tokens\n";
        echo "  report [period]     - Revenue report (today/week/month/year)\n";
        break;
}
