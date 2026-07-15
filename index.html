<?php require_once 'includes/header.php'; ?>
<?php require_once 'config/database.php'; ?>
<?php
$db = Database::getInstance()->getConnection();
$stats = [
    'total' => $db->query("SELECT COUNT(*) as c FROM transactions")->fetch()['c'],
    'revenue' => $db->query("SELECT COALESCE(SUM(amount),0) as s FROM transactions WHERE status='completed'")->fetch()['s'],
    'bdcoin' => $db->query("SELECT COALESCE(SUM(balance),0) as s FROM bdcoin_wallet")->fetch()['s'],
    'pending' => $db->query("SELECT COUNT(*) as c FROM transactions WHERE status='pending'")->fetch()['c'],
];
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 bg-gradient-primary text-white p-4 shadow rounded-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold animate__animated animate__fadeInDown">
                        <i class="bi bi-globe"></i> International Payment Gateway
                    </h1>
                    <p class="lead mb-0">Secure. Fast. Global. Choose your preferred payment method below.</p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="bi bi-credit-card-2-front" style="font-size: 6rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-gradient-blue">
            <div class="card-body">
                <h3 class="text-white fw-bold mb-0"><?= $stats['total'] ?></h3>
                <small class="text-white-50">Total Transactions</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-gradient-green">
            <div class="card-body">
                <h3 class="text-white fw-bold mb-0">$<?= number_format($stats['revenue'], 2) ?></h3>
                <small class="text-white-50">Total Revenue</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-gradient-purple">
            <div class="card-body">
                <h3 class="text-white fw-bold mb-0"><?= number_format($stats['bdcoin']) ?></h3>
                <small class="text-white-50">BDCoin Supply</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-gradient-orange">
            <div class="card-body">
                <h3 class="text-white fw-bold mb-0"><?= $stats['pending'] ?></h3>
                <small class="text-white-50">Pending Payments</small>
            </div>
        </div>
    </div>
</div>

<h4 class="mb-3 fw-bold"><i class="bi bi-credit-card"></i> Choose Payment Method</h4>
<div class="row g-4">
    <div class="col-md-4 col-lg-2">
        <a href="/bdpay/payments/stripe.php" class="text-decoration-none">
            <div class="card payment-card border-0 shadow-sm rounded-4 text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="icon-circle bg-primary-subtle mb-3">
                        <i class="bi bi-stripe text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Stripe</h5>
                    <small class="text-muted">Credit/Debit Cards</small>
                    <span class="badge bg-primary mt-2">Popular</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-lg-2">
        <a href="/bdpay/payments/googlepay.php" class="text-decoration-none">
            <div class="card payment-card border-0 shadow-sm rounded-4 text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="icon-circle bg-warning-subtle mb-3">
                        <i class="bi bi-google text-warning" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Google Pay</h5>
                    <small class="text-muted">Fast Checkout</small>
                    <span class="badge bg-warning text-dark mt-2">Quick</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-lg-2">
        <a href="/bdpay/payments/visa.php" class="text-decoration-none">
            <div class="card payment-card border-0 shadow-sm rounded-4 text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="icon-circle bg-info-subtle mb-3">
                        <i class="bi bi-credit-card text-info" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Visa</h5>
                    <small class="text-muted">Card Payment</small>
                    <span class="badge bg-info mt-2">Secure</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-lg-2">
        <a href="/bdpay/payments/crypto.php" class="text-decoration-none">
            <div class="card payment-card border-0 shadow-sm rounded-4 text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="icon-circle bg-warning-subtle mb-3">
                        <i class="bi bi-currency-bitcoin text-warning" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Crypto</h5>
                    <small class="text-muted">BTC / ETH / USDT</small>
                    <span class="badge bg-warning text-dark mt-2">Decentralized</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-lg-2">
        <a href="/bdpay/payments/bdcoin.php" class="text-decoration-none">
            <div class="card payment-card border-0 shadow-sm rounded-4 text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="icon-circle bg-success-subtle mb-3">
                        <i class="bi bi-gem text-success" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">BDCoin</h5>
                    <small class="text-muted">Native Token</small>
                    <span class="badge bg-success mt-2">New</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-lg-2">
        <a href="/bdpay/payments/square.php" class="text-decoration-none">
            <div class="card payment-card border-0 shadow-sm rounded-4 text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="icon-circle bg-dark-subtle mb-3">
                        <i class="bi bi-credit-card-2-front text-dark" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Square</h5>
                    <small class="text-muted">Card Payments</small>
                    <span class="badge bg-dark mt-2">Sandbox</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 col-lg-2">
        <a href="/bdpay/admin/" class="text-decoration-none">
            <div class="card payment-card border-0 shadow-sm rounded-4 text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                    <div class="icon-circle bg-danger-subtle mb-3">
                        <i class="bi bi-shield-lock text-danger" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold">Admin</h5>
                    <small class="text-muted">Dashboard</small>
                    <span class="badge bg-danger mt-2">Manage</span>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold"><i class="bi bi-clock-history"></i> Recent Transactions</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Payer</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $txns = $db->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10")->fetchAll();
                            if (count($txns) > 0):
                                foreach ($txns as $t):
                            ?>
                            <tr>
                                <td><code><?= htmlspecialchars(substr($t['transaction_id'], 0, 12)) ?>...</code></td>
                                <td><?= htmlspecialchars($t['payer_name']) ?></td>
                                <td class="fw-bold">$<?= number_format($t['amount'], 2) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($t['payment_method']) ?></span></td>
                                <td>
                                    <?php if ($t['status'] === 'completed'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Completed</span>
                                    <?php elseif ($t['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass"></i> Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($t['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= date('M d, Y H:i', strtotime($t['created_at'])) ?></small></td>
                            </tr>
                            <?php
                                endforeach;
                            else:
                            ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No transactions yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
