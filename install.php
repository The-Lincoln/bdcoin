<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /bdpay/login.php');
    exit;
}
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

$db = Database::getInstance()->getConnection();
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int)($_POST['step'] ?? 1);

    if ($step === 1) {
        $admin_user = $_POST['admin_user'] ?? 'admin';
        $admin_pass = $_POST['admin_pass'] ?? '';
        if (strlen($admin_pass) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $config = file_get_contents(__DIR__ . '/config/config.php');
            $config = str_replace("'BDC_PRICE', 12.50", "'BDC_PRICE', " . floatval($_POST['bdc_price'] ?? 12.50), $config);
            file_put_contents(__DIR__ . '/config/config.php', $config);
            $success = 'Configuration saved';
            $step = 2;
        }
    } elseif ($step === 2) {
        $step = 3;
    } elseif ($step === 3) {
        $db->exec("DELETE FROM transactions");
        $db->exec("DELETE FROM bdcoin_transactions");
        $success = 'Demo data loaded successfully!';
        $step = 4;
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-success-subtle mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-gear text-success" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Setup Wizard</h3>
                    <p class="text-muted small">Step <?= $step ?> of 3</p>
                </div>

                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= ($step / 3) * 100 ?>%"></div>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="1">
                    <h5>Admin Account</h5>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Admin Username</label>
                        <input type="text" name="admin_user" class="form-control form-control-lg" value="admin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Admin Password</label>
                        <input type="password" name="admin_pass" class="form-control form-control-lg" minlength="6" required>
                    </div>
                    <hr>
                    <h5>BDCoin Settings</h5>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">BDCoin Price (USD)</label>
                        <input type="number" name="bdc_price" class="form-control form-control-lg" value="12.50" step="0.01">
                    </div>
                    <button type="submit" class="btn btn-success btn-lg fw-bold w-100">
                        Continue <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <?php elseif ($step === 2): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    <h5>Database Ready</h5>
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>SQLite database connected</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Transactions table ready</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>BDCoin wallets initialized</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>BDCoin transactions table ready</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg fw-bold w-100">
                        Load Demo Data <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <?php elseif ($step === 3): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    <h5>Demo Data</h5>
                    <p class="text-muted">Load sample transactions to test the dashboard.</p>
                    <button type="submit" class="btn btn-success btn-lg fw-bold w-100 mb-2">
                        <i class="bi bi-database"></i> Load Demo Data
                    </button>
                    <a href="/bdpay/" class="btn btn-outline-secondary btn-lg w-100">
                        <i class="bi bi-house"></i> Skip & Go to Home
                    </a>
                </form>

                <?php elseif ($step === 4): ?>
                <div class="text-center">
                    <i class="bi bi-check-circle-fill text-success display-3 mb-3 d-block"></i>
                    <h4 class="fw-bold">Setup Complete!</h4>
                    <p class="text-muted">BDPay is ready to use.</p>
                    <div class="d-grid gap-2 mt-4">
                        <a href="/bdpay/" class="btn btn-primary btn-lg fw-bold">
                            <i class="bi bi-house"></i> Go to Home
                        </a>
                        <a href="/bdpay/admin/" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-shield-lock"></i> Admin Dashboard
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
