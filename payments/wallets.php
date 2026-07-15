<?php require_once '../includes/header.php'; ?>
<?php require_once '../config/database.php'; ?>
<?php
$db = Database::getInstance()->getConnection();

$tab = $_GET['tab'] ?? 'bdc';
$message = '';
$error = '';

function createWallet(string $prefix, string $table): ?string {
    global $db, $error;
    $address = $prefix . strtoupper(bin2hex(random_bytes(10)));
    try {
        $stmt = $db->prepare("INSERT INTO $table (wallet_address, balance) VALUES (?, 0)");
        $stmt->execute([$address]);
        return $address;
    } catch (\PDOException $e) {
        $error = "Failed to create $prefix wallet.";
        return null;
    }
}

function transferAsset(string $table, string $txTable, string $prefix, string $from, string $to, float $amount): ?string {
    global $db, $error;
    if ($amount <= 0) { $error = 'Amount must be greater than 0'; return null; }
    if ($from === $to) { $error = 'Cannot send to the same wallet'; return null; }

    $stmt = $db->prepare("SELECT balance FROM $table WHERE wallet_address = ?");
    $stmt->execute([$from]);
    $fromW = $stmt->fetch();
    if (!$fromW) { $error = 'Source wallet not found'; return null; }
    if ((float)$fromW['balance'] < $amount) { $error = 'Insufficient balance'; return null; }

    $stmt = $db->prepare("SELECT id FROM $table WHERE wallet_address = ?");
    $stmt->execute([$to]);
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO $table (wallet_address, balance) VALUES (?, 0)");
        $stmt->execute([$to]);
    }

    $txHash = $prefix . bin2hex(random_bytes(16));
    $db->beginTransaction();
    try {
        $db->prepare("UPDATE $table SET balance = balance - ? WHERE wallet_address = ?")->execute([$amount, $from]);
        $db->prepare("UPDATE $table SET balance = balance + ? WHERE wallet_address = ?")->execute([$amount, $to]);
        $db->prepare("INSERT INTO $txTable (tx_hash, from_address, to_address, amount, type, status) VALUES (?, ?, ?, ?, 'transfer', 'completed')")->execute([$txHash, $from, $to, $amount]);
        $db->commit();
        return $txHash;
    } catch (\Throwable $e) {
        $db->rollBack();
        $error = 'Transfer failed: ' . $e->getMessage();
        return null;
    }
}

function getRate(string $asset): float {
    global $crypto_assets;
    if ($asset === 'BDC') return BDC_PRICE;
    if ($asset === 'SWPE') return SWPE_PRICE;
    $key = strtolower($asset);
    return $crypto_assets[$key]['rate'] ?? 0;
}

// --- BDC Wallet Actions ---
if ($tab === 'bdc') {
    if (($_POST['action'] ?? '') === 'create_bdc' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $addr = createWallet('BDC', 'bdcoin_wallet');
        if ($addr) $message = "BDC Wallet created: <code>$addr</code>";
    }
    if (($_POST['action'] ?? '') === 'transfer_bdc' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = transferAsset('bdcoin_wallet', 'bdcoin_transactions', 'BDC', $_POST['from_address'], $_POST['to_address'], floatval($_POST['amount'] ?? 0));
        if ($result) $message = "Transferred BDC. Tx: <code>$result</code>";
    }
}

// --- SWPE Wallet Actions ---
if ($tab === 'swpe') {
    if (($_POST['action'] ?? '') === 'create_swpe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $addr = createWallet('SWPE', 'swpe_wallet');
        if ($addr) $message = "SWPE Wallet created: <code>$addr</code>";
    }
    if (($_POST['action'] ?? '') === 'transfer_swpe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = transferAsset('swpe_wallet', 'swpe_transactions', 'SWPE', $_POST['from_address'], $_POST['to_address'], floatval($_POST['amount'] ?? 0));
        if ($result) $message = "Transferred SWPE. Tx: <code>$result</code>";
    }
}

// --- Exchange Actions ---
if ($tab === 'exchange' && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'swap') {
    $fromAsset = $_POST['from_asset'] ?? '';
    $toAsset = $_POST['to_asset'] ?? '';
    $fromAddr = $_POST['from_address'] ?? '';
    $fromAmount = floatval($_POST['from_amount'] ?? 0);

    if ($fromAsset === $toAsset) {
        $error = 'Cannot swap same asset';
    } elseif ($fromAmount <= 0) {
        $error = 'Amount must be greater than 0';
    } else {
        $fromRate = getRate($fromAsset);
        $toRate = getRate($toAsset);
        if ($fromRate <= 0 || $toRate <= 0) {
            $error = 'Invalid asset rate';
        } else {
            $usdValue = $fromAmount * $fromRate;
            $rawToAmount = $usdValue / $toRate;
            $fee = $rawToAmount * (EXCHANGE_FEE_PERCENT / 100);
            $toAmount = $rawToAmount - $fee;

            $fromWalletTable = strtolower($fromAsset) === 'bdc' ? 'bdcoin_wallet' : (strtolower($fromAsset) === 'swpe' ? 'swpe_wallet' : null);
            $toWalletTable = strtolower($toAsset) === 'bdc' ? 'bdcoin_wallet' : (strtolower($toAsset) === 'swpe' ? 'swpe_wallet' : null);

            if (!$fromWalletTable || !$toWalletTable) {
                $error = 'Exchange only supports BDC and SWPE';
            } else {
                $stmt = $db->prepare("SELECT balance FROM $fromWalletTable WHERE wallet_address = ?");
                $stmt->execute([$fromAddr]);
                $fromW = $stmt->fetch();
                if (!$fromW) {
                    $error = 'Source wallet not found';
                } elseif ((float)$fromW['balance'] < $fromAmount) {
                    $error = 'Insufficient balance';
                } else {
                    $toAddr = $db->prepare("SELECT wallet_address FROM $toWalletTable ORDER BY RANDOM() LIMIT 1");
                    $toAddr->execute();
                    $toAddrRow = $toAddr->fetch();
                    if (!$toAddrRow) {
                        $error = 'No destination wallet available';
                    } else {
                        $orderId = 'SWP-' . strtoupper(bin2hex(random_bytes(8)));
                        $db->beginTransaction();
                        try {
                            $db->prepare("UPDATE $fromWalletTable SET balance = balance - ? WHERE wallet_address = ?")->execute([$fromAmount, $fromAddr]);
                            $db->prepare("UPDATE $toWalletTable SET balance = balance + ? WHERE wallet_address = ?")->execute([$toAmount, $toAddrRow['wallet_address']]);

                            $fromHash = strtoupper($fromAsset) . bin2hex(random_bytes(16));
                            $toHash = strtoupper($toAsset) . bin2hex(random_bytes(16));

                            $fromTxTable = $fromWalletTable === 'bdcoin_wallet' ? 'bdcoin_transactions' : 'swpe_transactions';
                            $toTxTable = $toWalletTable === 'bdcoin_wallet' ? 'bdcoin_transactions' : 'swpe_transactions';

                            $db->prepare("INSERT INTO $fromTxTable (tx_hash, from_address, to_address, amount, type, status) VALUES (?, ?, 'EXCHANGE', ?, 'exchange', 'completed')")->execute([$fromHash, $fromAddr, $fromAmount]);
                            $db->prepare("INSERT INTO $toTxTable (tx_hash, from_address, to_address, amount, type, status) VALUES (?, 'EXCHANGE', ?, ?, 'exchange', 'completed')")->execute([$toHash, $toAddrRow['wallet_address'], $toAmount]);

                            $db->prepare("INSERT INTO exchange_orders (order_id, from_asset, to_asset, from_address, to_address, from_amount, to_amount, rate, fee, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')")->execute([$orderId, $fromAsset, $toAsset, $fromAddr, $toAddrRow['wallet_address'], $fromAmount, $toAmount, $fromRate / $toRate, $fee]);

                            $db->commit();
                            $message = "Swapped <strong>" . number_format($fromAmount, 4) . " $fromAsset</strong> → <strong>" . number_format($toAmount, 4) . " $toAsset</strong> (fee: " . number_format($fee, 4) . " $toAsset). Order: <code>$orderId</code>";
                        } catch (\Throwable $e) {
                            $db->rollBack();
                            $error = 'Swap failed: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

// --- Data ---
$bdcWallets = $db->query("SELECT * FROM bdcoin_wallet ORDER BY balance DESC")->fetchAll();
$swpeWallets = $db->query("SELECT * FROM swpe_wallet ORDER BY balance DESC")->fetchAll();
$bdcSupply = $db->query("SELECT COALESCE(SUM(balance),0) as t FROM bdcoin_wallet")->fetch()['t'];
$swpeSupply = $db->query("SELECT COALESCE(SUM(balance),0) as t FROM swpe_wallet")->fetch()['t'];
$exchangeOrders = $db->query("SELECT * FROM exchange_orders ORDER BY created_at DESC LIMIT 20")->fetchAll();

// Wallet detail
$selectedAddr = $_GET['wallet'] ?? '';
$selectedAsset = $_GET['asset'] ?? 'bdc';
$selWallet = null;
$selTxns = [];

if ($selectedAddr) {
    $selTable = $selectedAsset === 'swpe' ? 'swpe_wallet' : 'bdcoin_wallet';
    $selTxTable = $selectedAsset === 'swpe' ? 'swpe_transactions' : 'bdcoin_transactions';
    $stmt = $db->prepare("SELECT * FROM $selTable WHERE wallet_address = ?");
    $stmt->execute([$selectedAddr]);
    $selWallet = $stmt->fetch();
    if ($selWallet) {
        $stmt = $db->prepare("SELECT * FROM $selTxTable WHERE from_address = ? OR to_address = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$selectedAddr, $selectedAddr]);
        $selTxns = $stmt->fetchAll();
    }
}

$balanceMap = [];
foreach ($bdcWallets as $w) $balanceMap[$w['wallet_address']] = ['bdc' => $w['balance'], 'swpe' => 0];
foreach ($swpeWallets as $w) {
    if (isset($balanceMap[$w['wallet_address']])) $balanceMap[$w['wallet_address']]['swpe'] = $w['balance'];
}
?>
<style>
.wallet-address { font-family: 'Courier New', monospace; font-size: 0.85rem; word-break: break-all; }
.nav-tabs .nav-link { border: none; padding: 12px 24px; font-weight: 600; color: #666; }
.nav-tabs .nav-link.active { color: #11998e; border-bottom: 3px solid #11998e; background: transparent; }
.nav-tabs .nav-link:hover { color: #11998e; }
.swap-card { transition: all 0.3s ease; }
.swap-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important; }
.exchange-rate-badge { background: #f0f2f5; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; }
</style>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-gradient-bdcoin text-white">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="fw-bold mb-1"><i class="bi bi-wallet2"></i> Crypto Wallet Manager</h3>
                        <p class="mb-0 opacity-75">Manage BDCoin, SWPE Token, and exchange between assets</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="row g-1">
                            <div class="col-4">
                                <small class="opacity-75">BDC Supply</small>
                                <h6 class="fw-bold mb-0"><?= number_format($bdcSupply) ?></h6>
                            </div>
                            <div class="col-4">
                                <small class="opacity-75">SWPE Supply</small>
                                <h6 class="fw-bold mb-0"><?= number_format($swpeSupply) ?></h6>
                            </div>
                            <div class="col-4">
                                <small class="opacity-75">BDC Price</small>
                                <h6 class="fw-bold mb-0">$<?= number_format(BDC_PRICE, 2) ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="col-12"><div class="alert alert-success border-0 rounded-4 shadow-sm mb-0"><?= $message ?></div></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="col-12"><div class="alert alert-danger border-0 rounded-4 shadow-sm mb-0"><?= htmlspecialchars($error) ?></div></div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="col-12">
        <ul class="nav nav-tabs border-0">
            <li class="nav-item"><a class="nav-link <?= $tab === 'bdc' ? 'active' : '' ?>" href="?tab=bdc"><i class="bi bi-gem text-success"></i> BDCoin</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'swpe' ? 'active' : '' ?>" href="?tab=swpe"><i class="bi bi-lightning text-info"></i> SWPE Token</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'exchange' ? 'active' : '' ?>" href="?tab=exchange"><i class="bi bi-arrow-left-right text-primary"></i> Exchange</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'orders' ? 'active' : '' ?>" href="?tab=orders"><i class="bi bi-clock-history text-secondary"></i> Orders</a></li>
        </ul>
    </div>

    <!-- BDCoin Tab -->
    <?php if ($tab === 'bdc'): ?>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 swap-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-success"></i> Create BDC Wallet</h5>
                <form method="POST"><input type="hidden" name="action" value="create_bdc">
                    <p class="small text-muted">Generate a new BDCoin wallet address.</p>
                    <button type="submit" class="btn btn-success w-100 fw-bold"><i class="bi bi-gem"></i> Generate BDC Wallet</button>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4 mt-4 swap-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-send text-success"></i> Transfer BDC</h5>
                <form method="POST"><input type="hidden" name="action" value="transfer_bdc">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">From</label>
                        <select name="from_address" class="form-select" required>
                            <option value="">Select wallet</option>
                            <?php foreach ($bdcWallets as $w): ?>
                            <option value="<?= htmlspecialchars($w['wallet_address']) ?>"><?= substr(htmlspecialchars($w['wallet_address']), 0, 16) ?>... (<?= number_format($w['balance'], 2) ?> BDC)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">To</label>
                        <input type="text" name="to_address" class="form-control" placeholder="BDC..." required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Amount (BDC)</label>
                        <input type="number" name="amount" class="form-control" min="0.0001" step="0.0001" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-send"></i> Send BDC</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-wallet2 text-success"></i> BDCoin Wallets (<?= count($bdcWallets) ?>)</h5>
                <small class="text-muted">Click to view details</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Address</th><th>Balance</th><th>Value</th><th>Created</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php if (count($bdcWallets) > 0): foreach ($bdcWallets as $w): ?>
                            <tr class="<?= $w['wallet_address'] === $selectedAddr && $selectedAsset === 'bdc' ? 'table-success' : '' ?>">
                                <td class="wallet-address"><code><?= htmlspecialchars($w['wallet_address']) ?></code></td>
                                <td class="fw-bold text-success"><?= number_format($w['balance'], 4) ?> BDC</td>
                                <td>$<?= number_format($w['balance'] * BDC_PRICE, 2) ?></td>
                                <td><small class="text-muted"><?= date('M d, Y', strtotime($w['created_at'])) ?></small></td>
                                <td><a href="?tab=bdc&wallet=<?= urlencode($w['wallet_address']) ?>&asset=bdc" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No wallets</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php if ($selWallet && $selectedAsset === 'bdc'): ?>
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-info-circle"></i> <code><?= htmlspecialchars($selWallet['wallet_address']) ?></code></h5>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-4"><div class="bg-light rounded-3 p-2 text-center"><small class="text-muted">Balance</small><h5 class="fw-bold text-success mb-0"><?= number_format($selWallet['balance'], 4) ?> BDC</h5></div></div>
                    <div class="col-4"><div class="bg-light rounded-3 p-2 text-center"><small class="text-muted">USD Value</small><h5 class="fw-bold mb-0">$<?= number_format($selWallet['balance'] * BDC_PRICE, 2) ?></h5></div></div>
                    <div class="col-4"><div class="bg-light rounded-3 p-2 text-center"><small class="text-muted">Created</small><h6 class="fw-bold mb-0"><?= date('M d, Y', strtotime($selWallet['created_at'])) ?></h6></div></div>
                </div>
                <h6 class="fw-bold"><i class="bi bi-activity"></i> Transactions</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Tx</th><th>Type</th><th>From/To</th><th>Amount</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php if (count($selTxns) > 0): foreach ($selTxns as $tx): ?>
                            <tr>
                                <td><code><?= substr($tx['tx_hash'], 0, 10) ?>...</code></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($tx['type']) ?></span></td>
                                <td class="wallet-address">
                                    <?= $tx['from_address'] === $selectedAddr ? '<span class="text-danger">Out</span>' : '<span class="text-success">In</span>' ?>
                                    <small><?= substr(htmlspecialchars($tx['from_address'] === $selectedAddr ? $tx['to_address'] : $tx['from_address']), 0, 10) ?>...</small>
                                </td>
                                <td class="fw-bold"><?= number_format($tx['amount'], 4) ?> BDC</td>
                                <td><small class="text-muted"><?= date('M d, H:i', strtotime($tx['created_at'])) ?></small></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">No transactions</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- SWPE Tab -->
    <?php if ($tab === 'swpe'): ?>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 swap-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-info"></i> Create SWPE Wallet</h5>
                <form method="POST"><input type="hidden" name="action" value="create_swpe">
                    <p class="small text-muted">Generate a new SWPE Token wallet address.</p>
                    <button type="submit" class="btn btn-info text-white w-100 fw-bold"><i class="bi bi-lightning"></i> Generate SWPE Wallet</button>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4 mt-4 swap-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-send text-info"></i> Transfer SWPE</h5>
                <form method="POST"><input type="hidden" name="action" value="transfer_swpe">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">From</label>
                        <select name="from_address" class="form-select" required>
                            <option value="">Select wallet</option>
                            <?php foreach ($swpeWallets as $w): ?>
                            <option value="<?= htmlspecialchars($w['wallet_address']) ?>"><?= substr(htmlspecialchars($w['wallet_address']), 0, 16) ?>... (<?= number_format($w['balance'], 2) ?> SWPE)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">To</label>
                        <input type="text" name="to_address" class="form-control" placeholder="SWPE..." required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Amount (SWPE)</label>
                        <input type="number" name="amount" class="form-control" min="0.0001" step="0.0001" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-send"></i> Send SWPE</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-wallet2 text-info"></i> SWPE Wallets (<?= count($swpeWallets) ?>)</h5>
                <small class="text-muted">Click to view details</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Address</th><th>Balance</th><th>Value</th><th>Created</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php if (count($swpeWallets) > 0): foreach ($swpeWallets as $w): ?>
                            <tr class="<?= $w['wallet_address'] === $selectedAddr && $selectedAsset === 'swpe' ? 'table-info' : '' ?>">
                                <td class="wallet-address"><code><?= htmlspecialchars($w['wallet_address']) ?></code></td>
                                <td class="fw-bold text-info"><?= number_format($w['balance'], 4) ?> SWPE</td>
                                <td>$<?= number_format($w['balance'] * SWPE_PRICE, 2) ?></td>
                                <td><small class="text-muted"><?= date('M d, Y', strtotime($w['created_at'])) ?></small></td>
                                <td><a href="?tab=swpe&wallet=<?= urlencode($w['wallet_address']) ?>&asset=swpe" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No wallets</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php if ($selWallet && $selectedAsset === 'swpe'): ?>
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-info-circle"></i> <code><?= htmlspecialchars($selWallet['wallet_address']) ?></code></h5>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-4"><div class="bg-light rounded-3 p-2 text-center"><small class="text-muted">Balance</small><h5 class="fw-bold text-info mb-0"><?= number_format($selWallet['balance'], 4) ?> SWPE</h5></div></div>
                    <div class="col-4"><div class="bg-light rounded-3 p-2 text-center"><small class="text-muted">USD Value</small><h5 class="fw-bold mb-0">$<?= number_format($selWallet['balance'] * SWPE_PRICE, 2) ?></h5></div></div>
                    <div class="col-4"><div class="bg-light rounded-3 p-2 text-center"><small class="text-muted">Created</small><h6 class="fw-bold mb-0"><?= date('M d, Y', strtotime($selWallet['created_at'])) ?></h6></div></div>
                </div>
                <h6 class="fw-bold"><i class="bi bi-activity"></i> Transactions</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Tx</th><th>Type</th><th>From/To</th><th>Amount</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php if (count($selTxns) > 0): foreach ($selTxns as $tx): ?>
                            <tr>
                                <td><code><?= substr($tx['tx_hash'], 0, 10) ?>...</code></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($tx['type']) ?></span></td>
                                <td class="wallet-address">
                                    <?= $tx['from_address'] === $selectedAddr ? '<span class="text-danger">Out</span>' : '<span class="text-success">In</span>' ?>
                                    <small><?= substr(htmlspecialchars($tx['from_address'] === $selectedAddr ? $tx['to_address'] : $tx['from_address']), 0, 10) ?>...</small>
                                </td>
                                <td class="fw-bold"><?= number_format($tx['amount'], 4) ?> SWPE</td>
                                <td><small class="text-muted"><?= date('M d, H:i', strtotime($tx['created_at'])) ?></small></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">No transactions</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Exchange Tab -->
    <?php if ($tab === 'exchange'): ?>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 swap-card">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-arrow-left-right text-primary"></i> Swap Assets</h5>
            </div>
            <div class="card-body p-4">
                <div class="bg-light rounded-3 p-3 mb-4 text-center">
                    <div class="row g-2">
                        <div class="col-4"><small class="text-muted">1 BDC</small><br><strong>$<?= number_format(BDC_PRICE, 2) ?></strong></div>
                        <div class="col-4"><small class="text-muted">1 SWPE</small><br><strong>$<?= number_format(SWPE_PRICE, 2) ?></strong></div>
                        <div class="col-4"><small class="text-muted">Fee</small><br><strong><?= EXCHANGE_FEE_PERCENT ?>%</strong></div>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="swap">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">From</label>
                        <select name="from_asset" class="form-select" required onchange="updateRate()">
                            <option value="BDC">BDCoin (BDC)</option>
                            <option value="SWPE">SWPE Token (SWPE)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">From Wallet</label>
                        <select name="from_address" class="form-select" required>
                            <optgroup label="BDC Wallets">
                                <?php foreach ($bdcWallets as $w): ?>
                                <option value="<?= htmlspecialchars($w['wallet_address']) ?>" data-asset="BDC">BDC: <?= substr($w['wallet_address'], 0, 12) ?>... (<?= number_format($w['balance'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="SWPE Wallets">
                                <?php foreach ($swpeWallets as $w): ?>
                                <option value="<?= htmlspecialchars($w['wallet_address']) ?>" data-asset="SWPE">SWPE: <?= substr($w['wallet_address'], 0, 12) ?>... (<?= number_format($w['balance'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Amount</label>
                        <input type="number" name="from_amount" id="swapAmount" class="form-control" min="0.0001" step="0.0001" required oninput="updateRate()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">To</label>
                        <select name="to_asset" class="form-select" required onchange="updateRate()">
                            <option value="SWPE">SWPE Token (SWPE)</option>
                            <option value="BDC">BDCoin (BDC)</option>
                        </select>
                    </div>
                    <div class="card bg-light mb-3">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">You receive</small>
                                <strong id="youReceive">0.0000</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted">Fee (<?= EXCHANGE_FEE_PERCENT ?>%)</small>
                                <strong id="feeDisplay">0.0000</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted">Rate</small>
                                <strong id="rateDisplay">-</strong>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-arrow-left-right"></i> Swap</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-wallet2"></i> Portfolio Overview</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Address</th><th>BDC Balance</th><th>SWPE Balance</th><th>Total Value</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $allAddrs = [];
                            foreach ($bdcWallets as $w) $allAddrs[$w['wallet_address']] = ['bdc' => $w['balance'], 'swpe' => 0];
                            foreach ($swpeWallets as $w) {
                                if (isset($allAddrs[$w['wallet_address']])) $allAddrs[$w['wallet_address']]['swpe'] = $w['balance'];
                            }
                            $shown = 0;
                            foreach ($allAddrs as $addr => $bal):
                                if ($shown >= 10) break;
                                $totalVal = $bal['bdc'] * BDC_PRICE + $bal['swpe'] * SWPE_PRICE;
                                $shown++;
                            ?>
                            <tr>
                                <td class="wallet-address"><code><?= substr(htmlspecialchars($addr), 0, 16) ?>...</code></td>
                                <td class="fw-bold text-success"><?= number_format($bal['bdc'], 4) ?></td>
                                <td class="fw-bold text-info"><?= number_format($bal['swpe'], 4) ?></td>
                                <td class="fw-bold">$<?= number_format($totalVal, 2) ?></td>
                            </tr>
                            <?php endforeach; if ($shown === 0): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No wallets found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    const BDC_RATE = <?= BDC_PRICE ?>;
    const SWPE_RATE = <?= SWPE_PRICE ?>;
    const FEE_PCT = <?= EXCHANGE_FEE_PERCENT ?>;

    function updateRate() {
        const from = document.querySelector('select[name="from_asset"]').value;
        const to = document.querySelector('select[name="to_asset"]').value;
        const amount = parseFloat(document.getElementById('swapAmount').value) || 0;

        const fromRate = from === 'BDC' ? BDC_RATE : SWPE_RATE;
        const toRate = to === 'BDC' ? BDC_RATE : SWPE_RATE;

        const usdValue = amount * fromRate;
        const rawTo = usdValue / toRate;
        const fee = rawTo * (FEE_PCT / 100);
        const netTo = rawTo - fee;

        document.getElementById('youReceive').textContent = netTo.toFixed(4) + ' ' + to;
        document.getElementById('feeDisplay').textContent = fee.toFixed(4) + ' ' + to;
        document.getElementById('rateDisplay').textContent = '1 ' + from + ' = ' + (fromRate / toRate).toFixed(6) + ' ' + to;
    }
    updateRate();
    </script>
    <?php endif; ?>

    <!-- Orders Tab -->
    <?php if ($tab === 'orders'): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Exchange Order History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Order ID</th><th>From</th><th>To</th><th>Sent</th><th>Received</th><th>Fee</th><th>Rate</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php if (count($exchangeOrders) > 0): foreach ($exchangeOrders as $o): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($o['order_id']) ?></code></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($o['from_asset']) ?></span></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($o['to_asset']) ?></span></td>
                                <td class="fw-bold"><?= number_format($o['from_amount'], 4) ?></td>
                                <td class="fw-bold text-success"><?= number_format($o['to_amount'], 4) ?></td>
                                <td class="text-danger"><?= number_format($o['fee'], 4) ?></td>
                                <td><small>1:<?= number_format($o['rate'], 6) ?></small></td>
                                <td><small class="text-muted"><?= date('M d, H:i', strtotime($o['created_at'])) ?></small></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No exchange orders yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
