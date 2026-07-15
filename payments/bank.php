<?php require_once '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-secondary-subtle mx-auto mb-3">
                        <i class="bi bi-bank text-secondary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Pay with Bank Transfer</h3>
                    <p class="text-muted">Direct bank transfer to our account</p>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> After submitting, you'll receive our bank details to complete the transfer. Payments are processed within 1-2 business days after the transfer clears.
                </div>

                <form action="/bdpay/process.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="payment_method" value="bank">

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
                            <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                        </div>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="bi bi-info-circle"></i> Bank Transfer Details</h6>
                            <p class="small mb-1"><strong>Bank:</strong> Global Trust Bank</p>
                            <p class="small mb-1"><strong>Account Name:</strong> BDPay International</p>
                            <p class="small mb-1"><strong>Account Number:</strong> 1234567890</p>
                            <p class="small mb-1"><strong>Routing Number:</strong> 021000021</p>
                            <p class="small mb-1"><strong>SWIFT/BIC:</strong> GLOBTK22</p>
                            <p class="small mb-0"><strong>Reference:</strong> Use your full name as payment reference</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Your Bank</label>
                        <select name="payer_bank" class="form-select form-select-lg" required>
                            <option value="">Select your bank</option>
                            <option value="amex">American Express</option>
                            <option value="bofa">Bank of America</option>
                            <option value="chase">Chase</option>
                            <option value="citi">Citibank</option>
                            <option value="wells">Wells Fargo</option>
                            <option value="gtb">Global Trust Bank</option>
                            <option value="hsbc">HSBC</option>
                            <option value="other">Other Bank</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Transaction Reference (optional)</label>
                        <input type="text" name="bank_ref" class="form-control form-control-lg" placeholder="Enter your transfer reference number">
                        <small class="text-muted">Provide the reference number after making the transfer</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-secondary btn-lg fw-bold">
                            <i class="bi bi-bank"></i> Submit Bank Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-clock-history"></i> Processing Timeline</h6>
                <div class="row g-2">
                    <div class="col-4 text-center">
                        <i class="bi bi-send text-secondary fs-3"></i>
                        <small class="d-block fw-bold">Step 1</small>
                        <small class="text-muted">Submit & transfer</small>
                    </div>
                    <div class="col-4 text-center">
                        <i class="bi bi-hourglass-split text-warning fs-3"></i>
                        <small class="d-block fw-bold">Step 2</small>
                        <small class="text-muted">1-2 business days</small>
                    </div>
                    <div class="col-4 text-center">
                        <i class="bi bi-check-circle text-success fs-3"></i>
                        <small class="d-block fw-bold">Step 3</small>
                        <small class="text-muted">Payment confirmed</small>
                    </div>
                </div>
                <hr>
                <h6 class="fw-bold"><i class="bi bi-info-circle"></i> Important Notes</h6>
                <ul class="small mb-0">
                    <li>Transfers from domestic banks typically clear within 1 business day</li>
                    <li>International wire transfers may take 2-5 business days</li>
                    <li>A $1.00 processing fee applies to all bank transfers</li>
                    <li>Include your full name in the transfer reference for faster processing</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
