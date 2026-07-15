<?php require_once '../includes/auth_check.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../config/database.php'; ?>
<?php
$db = Database::getInstance()->getConnection();

$total_txns = $db->query("SELECT COUNT(*) as c FROM transactions")->fetch()['c'];
$total_revenue = $db->query("SELECT COALESCE(SUM(amount),0) as s FROM transactions WHERE status='completed'")->fetch()['s'];
$bdcoin_supply = $db->query("SELECT COALESCE(SUM(balance),0) as s FROM bdcoin_wallet")->fetch()['s'];
$pending_txns = $db->query("SELECT COUNT(*) as c FROM transactions WHERE status='pending'")->fetch()['c'];

$method_stats = $db->query("SELECT payment_method, COUNT(*) as count, COALESCE(SUM(amount),0) as total FROM transactions GROUP BY payment_method ORDER BY count DESC")->fetchAll();
$recent = $db->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-shield-lock"></i> Admin Dashboard</h3>
    <a href="/bdpay/admin/transactions.php" class="btn btn-outline-primary">
        <i class="bi bi-list"></i> All Transactions
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
            <div class="card-body text-center">
                <h2 class="fw-bold mb-0"><?= $total_txns ?></h2>
                <small>Total Transactions</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
            <div class="card-body text-center">
                <h2 class="fw-bold mb-0">$<?= number_format($total_revenue, 2) ?></h2>
                <small>Total Revenue</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-info text-white">
            <div class="card-body text-center">
                <h2 class="fw-bold mb-0"><?= number_format($bdcoin_supply) ?></h2>
                <small>BDCoin Supply</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 bg-warning text-dark">
            <div class="card-body text-center">
                <h2 class="fw-bold mb-0"><?= $pending_txns ?></h2>
                <small>Pending</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold"><i class="bi bi-pie-chart"></i> Payment Method Distribution</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Count</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($method_stats as $m): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($m['payment_method']) ?></span></td>
                                <td class="fw-bold"><?= $m['count'] ?></td>
                                <td>$<?= number_format($m['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($method_stats) === 0): ?>
                            <tr><td colspan="3" class="text-center text-muted">No data yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold"><i class="bi bi-wallet2"></i> BDCoin Wallets</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Address</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $wallets = $db->query("SELECT * FROM bdcoin_wallet ORDER BY balance DESC")->fetchAll();
                            foreach ($wallets as $w):
                            ?>
                            <tr>
                                <td><code><?= htmlspecialchars($w['wallet_address']) ?></code></td>
                                <td class="fw-bold text-success"><?= number_format($w['balance'], 4) ?> BDC</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Recent Transactions</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Payer</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent) > 0): foreach ($recent as $t): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($t['transaction_id']) ?></code></td>
                        <td><?= htmlspecialchars($t['payer_name']) ?></td>
                        <td><small><?= htmlspecialchars($t['payer_email']) ?></small></td>
                        <td class="fw-bold">$<?= number_format($t['amount'], 2) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($t['payment_method']) ?></span></td>
                        <td>
                            <?php if ($t['status'] === 'completed'): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php elseif ($t['status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= htmlspecialchars($t['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= date('M d, Y H:i', strtotime($t['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No transactions yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
