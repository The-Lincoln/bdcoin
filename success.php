<?php require_once 'includes/header.php'; ?>
<?php require_once 'config/database.php'; ?>
<?php
$txn_id = $_GET['txn'] ?? '';
$status = $_GET['status'] ?? '';

$db = Database::getInstance()->getConnection();
$txn = $db->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
$txn->execute([$txn_id]);
$txn = $txn->fetch();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 text-center">
            <div class="card-body p-5">
                <?php if ($status === 'completed'): ?>
                    <div class="icon-circle bg-success-subtle mx-auto mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-check-circle text-success" style="font-size: 3.5rem;"></i>
                    </div>
                    <h2 class="fw-bold text-success">Payment Successful!</h2>
                    <p class="text-muted">Your transaction has been processed successfully.</p>
                <?php else: ?>
                    <div class="icon-circle bg-danger-subtle mx-auto mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-x-circle text-danger" style="font-size: 3.5rem;"></i>
                    </div>
                    <h2 class="fw-bold text-danger">Payment Failed</h2>
                    <p class="text-muted">Your transaction could not be processed.</p>
                <?php endif; ?>

                <?php if ($txn): ?>
                <div class="text-start bg-light p-3 rounded-3 mb-3">
                    <div class="row small">
                        <div class="col-4 text-muted">Transaction:</div>
                        <div class="col-8 fw-bold"><?= htmlspecialchars($txn['transaction_id']) ?></div>
                        <div class="col-4 text-muted">Amount:</div>
                        <div class="col-8 fw-bold">$<?= number_format($txn['amount'], 2) ?></div>
                        <div class="col-4 text-muted">Method:</div>
                        <div class="col-8"><?= htmlspecialchars($txn['payment_method']) ?></div>
                        <div class="col-4 text-muted">Date:</div>
                        <div class="col-8"><?= date('M d, Y H:i:s', strtotime($txn['created_at'])) ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="d-grid gap-2">
                    <a href="/bdpay/" class="btn btn-primary btn-lg fw-bold">
                        <i class="bi bi-house"></i> Back to Home
                    </a>
                    <a href="javascript:window.print()" class="btn btn-outline-secondary">
                        <i class="bi bi-printer"></i> Print Receipt
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
