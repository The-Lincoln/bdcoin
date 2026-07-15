<?php require_once '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-warning-subtle mx-auto mb-3">
                        <i class="bi bi-google text-warning" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Pay with Google Pay</h3>
                    <p class="text-muted">Fast, simple checkout with Google Pay</p>
                </div>

                <div class="text-center mb-4">
                    <button id="gpayButton" class="btn btn-dark btn-lg px-5 fw-bold" style="border-radius: 50px;" onclick="simulateGPay()">
                        <i class="bi bi-google"></i> Pay with Google Pay
                    </button>
                </div>

                <div id="gpayForm" class="d-none">
                    <hr>
                    <form action="/bdpay/process.php" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="payment_method" value="google_pay">

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
                            <label class="form-label fw-semibold">Google Pay Email</label>
                            <input type="email" class="form-control form-control-lg" value="user@gmail.com" readonly>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg fw-bold">
                                <i class="bi bi-google"></i> Confirm Google Pay Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-info-circle"></i> Why Google Pay?</h6>
                <div class="row g-2">
                    <div class="col-4 text-center">
                        <i class="bi bi-shield-check text-success fs-3"></i>
                        <small class="d-block">Secure</small>
                    </div>
                    <div class="col-4 text-center">
                        <i class="bi bi-lightning-charge text-warning fs-3"></i>
                        <small class="d-block">Fast</small>
                    </div>
                    <div class="col-4 text-center">
                        <i class="bi bi-phone text-primary fs-3"></i>
                        <small class="d-block">Mobile</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function simulateGPay() {
    document.getElementById('gpayButton').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    document.getElementById('gpayButton').disabled = true;
    setTimeout(() => {
        document.getElementById('gpayButton').innerHTML = '<i class="bi bi-check-circle"></i> Google Pay Connected';
        document.getElementById('gpayButton').classList.remove('btn-dark');
        document.getElementById('gpayButton').classList.add('btn-success');
        document.getElementById('gpayForm').classList.remove('d-none');
        document.getElementById('gpayForm').scrollIntoView({ behavior: 'smooth' });
    }, 1500);
}
</script>

<?php require_once '../includes/footer.php'; ?>
