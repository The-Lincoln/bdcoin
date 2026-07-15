<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

$db = Database::getInstance()->getConnection();

$txn_id = $_GET['txn'] ?? '';
if (empty($txn_id)) redirect('/bdpay/');

$stmt = $db->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
$stmt->execute([$txn_id]);
$txn = $stmt->fetch();

if (!$txn) redirect('/bdpay/');

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-4 text-center">
                <div class="icon-circle bg-success-subtle mx-auto mb-2" style="width: 80px; height: 80px;">
                    <i class="bi bi-receipt text-success" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="fw-bold">Payment Invoice</h3>
                <p class="text-muted small">Transaction #<?= htmlspecialchars($txn['transaction_id']) ?></p>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="bg-light rounded-3 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Status</span>
                                <?= getStatusBadge($txn['status']) ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Amount</span>
                                <span class="fw-bold fs-5">$<?= number_format($txn['amount'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Payment Method</span>
                                <span class="fw-bold"><?= htmlspecialchars($txn['payment_method']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Date</span>
                                <span><?= date('M d, Y H:i', strtotime($txn['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Payer</small>
                        <span class="fw-bold"><?= htmlspecialchars($txn['payer_name']) ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Email</small>
                        <span class="fw-bold"><?= htmlspecialchars($txn['payer_email']) ?></span>
                    </div>
                </div>

                <hr>

                <div class="row small text-muted mb-3">
                    <div class="col-6">
                        <strong>Transaction ID:</strong><br>
                        <?= htmlspecialchars($txn['transaction_id']) ?>
                    </div>
                    <div class="col-6 text-end">
                        <strong>Currency:</strong><br>
                        <?= htmlspecialchars($txn['currency']) ?>
                    </div>
                </div>

                <?php if ($txn['payment_details']): $details = json_decode($txn['payment_details'], true); if ($details): ?>
                <div class="bg-light rounded-3 p-3 mb-3">
                    <small class="text-muted d-block mb-2">Payment Details</small>
                    <?php foreach ($details as $key => $val): ?>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted"><?= ucfirst(str_replace('_', ' ', $key)) ?></span>
                        <span class="fw-bold"><?= htmlspecialchars(is_string($val) ? $val : json_encode($val)) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; endif; ?>

                <div class="d-grid gap-2">
                    <a href="javascript:window.print()" class="btn btn-primary btn-lg fw-bold">
                        <i class="bi bi-printer"></i> Print Invoice
                    </a>
                    <a href="/bdpay/" class="btn btn-outline-secondary">
                        <i class="bi bi-house"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .navbar, footer, .btn, .no-print { display: none !important; }
    .container { margin-top: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
</style>

<?php require_once 'includes/footer.php'; ?>
