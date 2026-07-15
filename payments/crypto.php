<?php require_once '../includes/header.php'; ?>
<?php require_once '../config/database.php'; ?>
<?php
$db = Database::getInstance()->getConnection();

$merchant_id = $_SESSION['merchant_id_crypto'] ?? null;
$merchant = null;
if ($merchant_id) {
    $stmt = $db->prepare("SELECT * FROM merchants WHERE merchant_id = ? AND payment_method = 'crypto'");
    $stmt->execute([$merchant_id]);
    $merchant = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_merchant_crypto'])) {
    $business_name = $_POST['business_name'] ?? '';
    $contact_name = $_POST['contact_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $wallet_address = $_POST['wallet_address'] ?? '';

    if ($business_name && $contact_name && $email) {
        $new_id = 'CRYM-' . strtoupper(bin2hex(random_bytes(8)));
        $stmt = $db->prepare("INSERT INTO merchants (merchant_id, business_name, contact_name, email, payment_method, wallet_address) VALUES (?, ?, ?, ?, 'crypto', ?)");
        $stmt->execute([$new_id, $business_name, $contact_name, $email, $wallet_address]);
        $_SESSION['merchant_id_crypto'] = $new_id;
        echo '<script>location.reload();</script>';
        exit;
    }
}

$crypto_rates = [
    'btc' => ['name' => 'Bitcoin', 'symbol' => 'BTC', 'icon' => 'bi-currency-bitcoin', 'color' => 'warning', 'rate' => 61500],
    'eth' => ['name' => 'Ethereum', 'symbol' => 'ETH', 'icon' => 'bi-currency-ethereum', 'color' => 'primary', 'rate' => 3400],
    'usdt' => ['name' => 'Tether', 'symbol' => 'USDT', 'icon' => 'bi-currency-dollar', 'color' => 'success', 'rate' => 1],
    'bnb' => ['name' => 'Binance Coin', 'symbol' => 'BNB', 'icon' => 'bi-box', 'color' => 'warning', 'rate' => 580],
];
?>

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-warning-subtle mx-auto mb-3">
                        <i class="bi bi-currency-bitcoin text-warning" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Crypto Currency Payment</h3>
                    <p class="text-muted">Pay with Bitcoin, Ethereum, USDT, or BNB</p>
                </div>

                <div class="row g-2 mb-4">
                    <?php foreach ($crypto_rates as $key => $crypto): ?>
                    <div class="col-3">
                        <div class="card border crypto-select cursor-pointer text-center p-2 <?= $key === 'btc' ? 'border-warning' : '' ?>" data-crypto="<?= $key ?>" onclick="selectCrypto('<?= $key ?>')">
                            <div class="card-body p-2">
                                <i class="bi <?= $crypto['icon'] ?> text-<?= $crypto['color'] ?>" style="font-size: 1.5rem;"></i>
                                <small class="d-block fw-bold"><?= $crypto['symbol'] ?></small>
                                <small class="text-muted">$<?= number_format($crypto['rate']) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form action="/bdpay/process.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="payment_method" value="crypto">
                    <input type="hidden" name="crypto_type" id="cryptoType" value="btc">

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
                            <input type="number" name="amount" class="form-control" min="1" step="0.01" required id="usdAmount" oninput="updateCryptoAmount()">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">You Pay <span id="cryptoSymbolLabel">BTC</span></label>
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" id="cryptoAmount" readonly placeholder="0.00000000">
                            <span class="input-group-text" id="cryptoSymbol">BTC</span>
                        </div>
                        <small class="text-muted" id="rateInfo">1 BTC = $61,500.00</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Wallet Address</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" id="walletAddress" value="3J98t1WpEZ73CNmQviecrnyiWrnqRhWNLy" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyAddress()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <small class="text-muted">Send exact amount to this address</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> After sending crypto, click confirm below. Transaction will be verified on the blockchain.
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark">
                            <i class="bi bi-currency-bitcoin"></i> Confirm Crypto Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body p-4 p-md-5">
                <h4 class="fw-bold mb-3"><i class="bi bi-shop text-warning"></i> Merchant Account</h4>
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
                            <small class="text-muted">Crypto Wallet Address</small>
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
                <h6 class="fw-bold"><i class="bi bi-arrow-down-circle"></i> Recent Crypto Payments Received</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Transaction</th><th>Method</th><th>Amount</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $merchant_txns = $db->prepare("SELECT * FROM transactions WHERE payment_method = 'crypto' AND status = 'completed' ORDER BY created_at DESC LIMIT 5");
                            $merchant_txns->execute();
                            $rows = $merchant_txns->fetchAll();
                            if (count($rows) > 0):
                                foreach ($rows as $tx):
                                    $details = json_decode($tx['payment_details'], true);
                                    $crypto_type = $details['crypto_type'] ?? 'btc';
                            ?>
                            <tr>
                                <td><code><?= htmlspecialchars($tx['transaction_id']) ?></code></td>
                                <td><span class="badge bg-warning text-dark"><?= strtoupper($crypto_type) ?></span></td>
                                <td class="fw-bold text-success">+$<?= number_format($tx['amount'], 2) ?></td>
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
                            <label class="form-label fw-semibold">Crypto Wallet Address <small class="text-muted">(for receiving payments)</small></label>
                            <input type="text" name="wallet_address" class="form-control form-control-lg" placeholder="BTC / ETH / USDT / BNB address">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="register_merchant_crypto" class="btn btn-warning btn-lg fw-bold text-dark">
                            <i class="bi bi-shop"></i> Register as Crypto Merchant
                        </button>
                    </div>
                </form>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> Accept crypto payments from customers. Settlements are instant on-chain with 0.5% processing fee.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const rates = <?= json_encode($crypto_rates) ?>;

function selectCrypto(key) {
    document.querySelectorAll('.crypto-select').forEach(el => {
        el.classList.remove('border-warning');
    });
    document.querySelector(`[data-crypto="${key}"]`).classList.add('border-warning');
    document.getElementById('cryptoType').value = key;
    document.getElementById('cryptoSymbolLabel').textContent = rates[key].symbol;
    document.getElementById('cryptoSymbol').textContent = rates[key].symbol;
    document.getElementById('rateInfo').textContent = `1 ${rates[key].symbol} = $${Number(rates[key].rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    updateCryptoAmount();
}

function updateCryptoAmount() {
    const usd = parseFloat(document.getElementById('usdAmount').value) || 0;
    const type = document.getElementById('cryptoType').value;
    const rate = rates[type].rate;
    const cryptoAmount = usd / rate;
    document.getElementById('cryptoAmount').value = cryptoAmount.toFixed(8);
}

function copyAddress() {
    const addr = document.getElementById('walletAddress');
    navigator.clipboard.writeText(addr.value).then(() => {
        alert('Wallet address copied!');
    });
}

selectCrypto('btc');
</script>

<?php require_once '../includes/footer.php'; ?>
