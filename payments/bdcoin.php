<?php require_once '../includes/header.php'; ?>
<?php require_once '../config/database.php'; ?>
<?php
$db = Database::getInstance()->getConnection();
$wallets = $db->query("SELECT * FROM bdcoin_wallet")->fetchAll();
$wallet_balance = $db->query("SELECT SUM(balance) as total FROM bdcoin_wallet")->fetch()['total'];
$bdcoin_price = 12.50;

$merchant_id = $_SESSION['merchant_id_bdcoin'] ?? null;
$merchant = null;
if ($merchant_id) {
    $stmt = $db->prepare("SELECT * FROM merchants WHERE merchant_id = ? AND payment_method = 'bdcoin'");
    $stmt->execute([$merchant_id]);
    $merchant = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_merchant_bdcoin'])) {
    $business_name = $_POST['business_name'] ?? '';
    $contact_name = $_POST['contact_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $wallet_address = $_POST['wallet_address'] ?? '';

    if ($business_name && $contact_name && $email) {
        $new_id = 'BDCM-' . strtoupper(bin2hex(random_bytes(8)));
        $stmt = $db->prepare("INSERT INTO merchants (merchant_id, business_name, contact_name, email, payment_method, wallet_address) VALUES (?, ?, ?, ?, 'bdcoin', ?)");
        $stmt->execute([$new_id, $business_name, $contact_name, $email, $wallet_address]);
        if ($wallet_address) {
            $stmt = $db->prepare("INSERT OR IGNORE INTO bdcoin_wallet (wallet_address, balance) VALUES (?, 0)");
            $stmt->execute([$wallet_address]);
        }
        $_SESSION['merchant_id_bdcoin'] = $new_id;
        echo '<script>location.reload();</script>';
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-gradient-bdcoin text-white">
            <div class="card-body p-5 text-center">
                <div class="icon-circle bg-white bg-opacity-25 mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-gem text-white" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="fw-bold">BDCoin <span class="badge bg-white text-success fs-6">BETA</span></h2>
                <p class="mb-0">The native cryptocurrency of BDPay ecosystem</p>
                <div class="row mt-4 g-2">
                    <div class="col-4">
                        <div class="bg-white bg-opacity-25 rounded-3 p-2">
                            <small>Market Price</small>
                            <h4 class="fw-bold mb-0">$<?= number_format($bdcoin_price, 2) ?></h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white bg-opacity-25 rounded-3 p-2">
                            <small>Total Supply</small>
                            <h4 class="fw-bold mb-0"><?= number_format($wallet_balance) ?></h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white bg-opacity-25 rounded-3 p-2">
                            <small>Wallets</small>
                            <h4 class="fw-bold mb-0"><?= count($wallets) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-send"></i> Pay with BDCoin</h4>

                <form action="/bdpay/process.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="payment_method" value="bdcoin">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="payer_name" class="form-control form-control-lg" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="payer_email" class="form-control form-control-lg" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (USD)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" class="form-control" min="1" step="0.01" required id="bdcoinUsd" oninput="updateBDCoin()">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">You Pay in BDCoin</label>
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" id="bdcoinAmount" readonly placeholder="0.0000">
                            <span class="input-group-text bg-success text-white fw-bold">BDC</span>
                        </div>
                        <small class="text-muted">1 BDC = $<?= number_format($bdcoin_price, 2) ?></small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select BDCoin Wallet</label>
                        <select name="wallet_address" class="form-select form-select-lg" required>
                            <?php foreach ($wallets as $w): ?>
                            <option value="<?= htmlspecialchars($w['wallet_address']) ?>">
                                <?= htmlspecialchars($w['wallet_address']) ?> (Balance: <?= number_format($w['balance']) ?> BDC)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="bi bi-info-circle"></i> BDCoin Overview</h6>
                            <div class="row small">
                                <div class="col-6">Network: BDPay Chain</div>
                                <div class="col-6">Consensus: PoS</div>
                                <div class="col-6">Block Time: 2.5s</div>
                                <div class="col-6">Transaction Fee: 0.001 BDC</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="bi bi-gem"></i> Pay <span id="bdcoinLabel">0.0000</span> BDC
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="table-responsive">
                    <h6 class="fw-bold"><i class="bi bi-activity"></i> BDCoin Network Activity</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tx Hash</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $bdc_txns = $db->query("SELECT * FROM bdcoin_transactions ORDER BY created_at DESC LIMIT 5")->fetchAll();
                            if (count($bdc_txns) > 0):
                                foreach ($bdc_txns as $tx):
                            ?>
                            <tr>
                                <td><code><?= substr(htmlspecialchars($tx['tx_hash']), 0, 10) ?>...</code></td>
                                <td><small><?= substr(htmlspecialchars($tx['from_address']), 0, 8) ?>...</small></td>
                                <td><small><?= substr(htmlspecialchars($tx['to_address']), 0, 8) ?>...</small></td>
                                <td class="fw-bold"><?= number_format($tx['amount'], 4) ?> BDC</td>
                                <td><span class="badge bg-success">Confirmed</span></td>
                            </tr>
                            <?php
                                endforeach;
                            else:
                            ?>
                            <tr><td colspan="5" class="text-center text-muted">No transactions yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body p-4 p-md-5">
                <h4 class="fw-bold mb-3"><i class="bi bi-shop text-success"></i> Merchant Account</h4>
                <hr>

                <?php if ($merchant): ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Merchant ID</small>
                            <p class="fw-bold mb-0 fs-5"><?= htmlspecialchars($merchant['merchant_id']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Business</small>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($merchant['business_name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Contact</small>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($merchant['contact_name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Status</small>
                            <p class="fw-bold mb-0"><span class="badge bg-success fs-6"><?= $merchant['status'] ?></span></p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">BDCoin Wallet Address</small>
                            <p class="fw-bold mb-0 font-monospace"><?= htmlspecialchars($merchant['wallet_address'] ?: 'Not set') ?></p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Registered</small>
                            <p class="fw-bold mb-0"><?= $merchant['created_at'] ?></p>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold"><i class="bi bi-arrow-down-circle"></i> Recent BDCoin Payments Received</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Tx Hash</th><th>From</th><th>Amount</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $merchant_txns = $db->prepare("SELECT * FROM bdcoin_transactions WHERE to_address = ? ORDER BY created_at DESC LIMIT 5");
                            $merchant_txns->execute([$merchant['wallet_address'] ?: 'BDC_RESERVE']);
                            $rows = $merchant_txns->fetchAll();
                            if (count($rows) > 0):
                                foreach ($rows as $tx):
                            ?>
                            <tr>
                                <td><code><?= substr(htmlspecialchars($tx['tx_hash']), 0, 10) ?>...</code></td>
                                <td><small><?= substr(htmlspecialchars($tx['from_address']), 0, 8) ?>...</small></td>
                                <td class="fw-bold text-success">+<?= number_format($tx['amount'], 4) ?> BDC</td>
                                <td><small class="text-muted"><?= $tx['created_at'] ?></small></td>
                            </tr>
                            <?php
                                endforeach;
                            else:
                            ?>
                            <tr><td colspan="4" class="text-center text-muted">No payments received yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php else: ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Business Name</label>
                            <input type="text" name="business_name" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Name</label>
                            <input type="text" name="contact_name" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">BDCoin Wallet Address <small class="text-muted">(for receiving payments)</small></label>
                            <input type="text" name="wallet_address" class="form-control form-control-lg" placeholder="BDC...">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="register_merchant_bdcoin" class="btn btn-success btn-lg fw-bold">
                            <i class="bi bi-shop"></i> Register as BDCoin Merchant
                        </button>
                    </div>
                </form>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> Accept BDCoin payments from customers. Merchants receive instant on-chain settlement with 0.1% processing fee.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const BDC_PRICE = <?= $bdcoin_price ?>;

function updateBDCoin() {
    const usd = parseFloat(document.getElementById('bdcoinUsd').value) || 0;
    const bdc = usd / BDC_PRICE;
    document.getElementById('bdcoinAmount').value = bdc.toFixed(4);
    document.getElementById('bdcoinLabel').textContent = bdc.toFixed(4);
}
</script>

<?php require_once '../includes/footer.php'; ?>
