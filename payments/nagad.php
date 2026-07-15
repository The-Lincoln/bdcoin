<?php require_once '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-warning-subtle mx-auto mb-3">
                        <i class="bi bi-phone text-warning" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Pay with Nagad</h3>
                    <p class="text-muted">Send payment via Nagad mobile wallet</p>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> Send the exact amount to our Nagad Merchant Number below. After sending, submit your details and transaction ID for confirmation.
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body text-center">
                        <h6 class="fw-bold">Nagad Merchant Account</h6>
                        <p class="fs-3 fw-bold text-warning mb-1">+8801715-340463</p>
                        <p class="small text-muted mb-0">Account Name: BDPay International</p>
                    </div>
                </div>

                <form action="/bdpay/process.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="payment_method" value="nagad">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="payer_name" class="form-control form-control-lg" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nagad Account Number</label>
                        <input type="text" name="nagad_number" class="form-control form-control-lg" placeholder="+8801XXXXXXXXX" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (USD)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nagad Transaction ID</label>
                        <input type="text" name="nagad_trxid" class="form-control form-control-lg" placeholder="e.g. NGD123ABC" required>
                        <small class="text-muted">Enter the transaction ID from your Nagad app after sending payment</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark">
                            <i class="bi bi-phone"></i> Confirm Nagad Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-info-circle"></i> How to Pay with Nagad</h6>
                <ol class="small mb-0">
                    <li>Open your Nagad app and select <strong>Send Money</strong></li>
                    <li>Enter our merchant number: <strong>+8801715-340463</strong></li>
                    <li>Enter the exact amount shown above in BDT equivalent</li>
                    <li>Complete the transfer and copy the <strong>Transaction ID</strong></li>
                    <li>Paste the Transaction ID above and submit this form</li>
                </ol>
                <hr>
                <p class="small text-muted mb-0">Transactions are processed within 24 hours after Nagad payment confirmation. A $0.50 processing fee applies.</p>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
