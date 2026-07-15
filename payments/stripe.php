<?php require_once '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-primary-subtle mx-auto mb-3">
                        <i class="bi bi-stripe text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Pay with Stripe</h3>
                    <p class="text-muted">Secure credit/debit card payment powered by Stripe</p>
                </div>

                <form action="/bdpay/process.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="payment_method" value="stripe">

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

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Card Number</label>
                        <input type="text" class="form-control form-control-lg" placeholder="4242 4242 4242 4242" maxlength="19" id="cardNumber">
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Expiry</label>
                            <input type="text" class="form-control form-control-lg" placeholder="MM/YY" maxlength="5" id="cardExpiry">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">CVC</label>
                            <input type="text" class="form-control form-control-lg" placeholder="123" maxlength="4" id="cardCvc">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" required>
                        <label class="form-check-label small">
                            I agree to the <a href="#">terms and conditions</a>
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">
                            <i class="bi bi-lock-fill"></i> Pay Securely with Stripe
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Secured by SSL | 
                            <i class="bi bi-credit-card"></i> Powered by Stripe
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('cardNumber')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
});
document.getElementById('cardExpiry')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').replace(/^(\d{2})(\d)/, '$1/$2').substring(0,5);
});
document.getElementById('cardCvc')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').substring(0,4);
});
</script>

<?php require_once '../includes/footer.php'; ?>
