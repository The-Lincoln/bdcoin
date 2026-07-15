<?php require_once '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-info-subtle mx-auto mb-3">
                        <i class="bi bi-credit-card text-info" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Visa Card Payment</h3>
                    <p class="text-muted">Enter your Visa card details to pay securely</p>
                </div>

                <div class="d-flex justify-content-center mb-4 gap-2">
                    <span class="badge bg-info fs-6 px-3 py-2"><i class="bi bi-credit-card"></i> Visa</span>
                    <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-credit-card"></i> Mastercard</span>
                    <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-credit-card"></i> Amex</span>
                </div>

                <form action="/bdpay/process.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="payment_method" value="visa">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cardholder Name</label>
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

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Card Number</label>
                        <input type="text" name="card_number" class="form-control form-control-lg" placeholder="4111 1111 1111 1111" maxlength="19" id="visaCardNumber" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold">Expiry</label>
                            <input type="text" name="card_expiry" class="form-control form-control-lg" placeholder="MM/YY" maxlength="5" id="visaExpiry" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">CVV</label>
                            <input type="text" name="card_cvv" class="form-control form-control-lg" placeholder="123" maxlength="4" id="visaCvv" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">ZIP Code</label>
                            <input type="text" name="card_zip" class="form-control form-control-lg" placeholder="12345" maxlength="10">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-info btn-lg fw-bold text-white">
                            <i class="bi bi-lock-fill"></i> Pay $<span id="visaAmountDisplay">0.00</span> with Visa
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> 256-bit SSL Encrypted | 
                            <i class="bi bi-lock"></i> Verified by Visa
                        </small>
                        <img src="https://img.icons8.com/color/48/000000/visa.png" alt="Visa" height="30" class="ms-2">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('visaCardNumber')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim().substring(0,19);
});
document.getElementById('visaExpiry')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').replace(/^(\d{2})(\d)/, '$1/$2').substring(0,5);
});
document.getElementById('visaCvv')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').substring(0,4);
});
document.querySelector('input[name="amount"]')?.addEventListener('input', function(e) {
    document.getElementById('visaAmountDisplay').textContent = parseFloat(this.value || 0).toFixed(2);
});
</script>

<?php require_once '../includes/footer.php'; ?>
