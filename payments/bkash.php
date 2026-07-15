<?php require_once '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-danger-subtle mx-auto mb-3">
                        <i class="bi bi-phone text-danger" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Pay with bKash</h3>
                    <p class="text-muted">Send payment via bKash mobile wallet</p>
                </div>

                <div class="alert alert-danger">
                    <i class="bi bi-info-circle"></i> Send the exact amount to our bKash Merchant Number below. After sending, submit your details and transaction ID for confirmation.
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body text-center">
                        <h6 class="fw-bold">bKash Merchant Account</h6>
                        <p class="fs-3 fw-bold text-danger mb-1">+8801715-340463</p>
                        <p class="small text-muted mb-0">Account Name: BDPay International</p>
                    </div>
                </div>

                <form action="/bdpay/process.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="payment_method" value="bkash">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="payer_name" class="form-control form-control-lg" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">bKash Account Number</label>
                        <input type="text" name="bkash_number" class="form-control form-control-lg" placeholder="+8801XXXXXXXXX" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (USD)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">bKash Transaction ID (TrxID)</label>
                        <input type="text" name="bkash_trxid" class="form-control form-control-lg" placeholder="e.g. TRX123ABC" required>
                        <small class="text-muted">Enter the transaction ID from your bKash app after sending payment</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold">
                            <i class="bi bi-phone"></i> Confirm bKash Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-info-circle"></i> How to Pay with bKash</h6>
                <ol class="small mb-0">
                    <li>Open your bKash app and select <strong>Send Money</strong></li>
                    <li>Enter our merchant number: <strong>+8801715-340463</strong></li>
                    <li>Enter the exact amount shown above in BDT equivalent</li>
                    <li>Enter your reference and complete the transfer</li>
                    <li>Copy the <strong>Transaction ID (TrxID)</strong> from the confirmation</li>
                    <li>Paste the TrxID above and submit this form</li>
                </ol>
                <hr>
                <p class="small text-muted mb-0">Transactions are processed within 24 hours after bKash payment confirmation. A $0.50 processing fee applies.</p>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
