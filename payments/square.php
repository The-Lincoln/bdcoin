<?php require_once '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-dark-subtle mx-auto mb-3">
                        <i class="bi bi-credit-card-2-front text-dark" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Pay with Square</h3>
                    <p class="text-muted">Secure card payment powered by Square Sandbox</p>
                </div>

                <form id="payment-form" method="POST" action="/bdpay/process.php">
                    <input type="hidden" name="payment_method" value="square">
                    <input type="hidden" name="square_nonce" id="square-nonce">

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
                        <label class="form-label fw-semibold">Card Details</label>
                        <div id="square-card-container" class="form-control form-control-lg" style="min-height: 50px; padding: 12px;">
                            <div id="card-container"></div>
                        </div>
                        <div id="payment-status" class="small text-muted mt-1"></div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" required>
                        <label class="form-check-label small">
                            I agree to the <a href="#">terms and conditions</a>
                        </label>
                    </div>

                    <div class="d-grid">
                        <button id="submit-button" type="button" class="btn btn-dark btn-lg fw-bold" onclick="onSquareSubmit()">
                            <i class="bi bi-lock-fill"></i> Pay Securely with Square
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Secured by SSL |
                            <i class="bi bi-credit-card-2-front"></i> Powered by Square Sandbox
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
<script>
let card = null;
let squareApp = null;

async function initializeSquare() {
    try {
        squareApp = await Square.payments('<?= SQUARE_APP_ID ?>', '<?= SQUARE_LOCATION_ID ?>');
        card = await squareApp.card();
        await card.attach('#card-container');
    } catch (e) {
        console.error('Square init error:', e);
        document.getElementById('payment-status').textContent = 'Failed to load payment form. Please refresh.';
        document.getElementById('payment-status').classList.add('text-danger');
    }
}

async function onSquareSubmit() {
    const btn = document.getElementById('submit-button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

    if (!card) {
        document.getElementById('payment-status').textContent = 'Payment form not loaded. Please refresh.';
        document.getElementById('payment-status').classList.add('text-danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill"></i> Pay Securely with Square';
        return;
    }

    const result = await card.tokenize();
    if (result.status === 'OK') {
        document.getElementById('square-nonce').value = result.token;
        document.getElementById('payment-form').submit();
    } else {
        let msg = 'Payment validation failed.';
        if (result.errors) {
            msg = result.errors.map(e => e.message).join(' ');
        }
        document.getElementById('payment-status').textContent = msg;
        document.getElementById('payment-status').classList.add('text-danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill"></i> Pay Securely with Square';
    }
}

initializeSquare();
</script>

<?php require_once '../includes/footer.php'; ?>
