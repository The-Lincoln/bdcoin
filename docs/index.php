<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <h2 class="fw-bold mb-4"><i class="bi bi-code-slash text-primary"></i> Merchant API Integration</h2>
                <p class="text-muted small">Version <?= APP_VERSION ?> | Last updated: July 15, 2026</p>
                <hr>

                <div class="alert alert-primary">
                    <i class="bi bi-info-circle"></i> Integrate BDPay payment processing into your application using our REST API. Accept payments, manage wallets, and track settlements programmatically.
                </div>

                <h5>Base URL</h5>
                <div class="bg-light rounded-3 p-3 mb-4">
                    <code class="fs-5"><?= API_URL ?>v1</code>
                </div>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-key"></i> Authentication</h3>
                <p>All API requests (except public endpoints) require an API key sent via the <code>X-API-Key</code> header.</p>

                <h5>Getting an API Key</h5>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6><i class="bi bi-shop text-success"></i> Register as Merchant</h6>
                                <p class="small mb-0">Send a POST request to <code>/api/v1/merchants/register</code> with your business details. An API key is generated automatically.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6><i class="bi bi-shield-lock text-primary"></i> Admin Panel</h6>
                                <p class="small mb-0">Log in to the <a href="/bdpay/admin/">Admin Dashboard</a> and navigate to API Keys to generate a new key manually.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h5>Using Your API Key</h5>
                <p>Include the API key in every request:</p>
                <div class="bg-dark text-light rounded-3 p-3 mb-4">
                    <pre class="mb-0"><code>curl -H "X-API-Key: bdp_abc123..." <?= API_URL ?>v1/merchants/profile</code></pre>
                </div>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-shop"></i> Merchant Endpoints</h3>
                <p>Manage your merchant account, view transactions, and track settlements.</p>

                <div class="accordion mb-4" id="merchantEndpoints">

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epRegister">
                                <span class="badge bg-success me-2">POST</span> /api/v1/merchants/register
                            </button>
                        </h2>
                        <div id="epRegister" class="accordion-collapse collapse" data-bs-parent="#merchantEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Register as a merchant and receive an API key. No authentication required.</p>
                                <h6>Request Body</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>business_name</td><td>string</td><td><span class="text-danger">Yes</span></td><td>Your business or company name</td></tr>
                                            <tr><td>contact_name</td><td>string</td><td><span class="text-danger">Yes</span></td><td>Your full name</td></tr>
                                            <tr><td>email</td><td>string</td><td><span class="text-danger">Yes</span></td><td>Business email address</td></tr>
                                            <tr><td>payment_method</td><td>string</td><td><span class="text-danger">Yes</span></td><td>One of: bdcoin, crypto, stripe, google_pay, visa, square</td></tr>
                                            <tr><td>wallet_address</td><td>string</td><td>No</td><td>Wallet address for receiving crypto/BDC payouts</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <h6>Example Request</h6>
                                <div class="bg-dark text-light rounded-3 p-3 mb-2">
<pre><code>curl -X POST <?= API_URL ?>v1/merchants/register \
  -H "Content-Type: application/json" \
  -d '{
    "business_name": "My Store Inc.",
    "contact_name": "John Doe",
    "email": "john@mystore.com",
    "payment_method": "bdcoin",
    "wallet_address": "BDC1A2B3C4D5E6F7G8H9I0J"
  }'</code></pre>
                                </div>
                                <h6>Example Response</h6>
                                <div class="bg-light rounded-3 p-3">
<pre><code>{
  "success": true,
  "message": "Merchant registered successfully. Store your API key securely.",
  "data": {
    "merchant_id": "BDCM-AB12CD34EF56GH78",
    "business_name": "My Store Inc.",
    "contact_name": "John Doe",
    "email": "john@mystore.com",
    "payment_method": "bdcoin",
    "wallet_address": "BDC1A2B3C4D5E6F7G8H9I0J",
    "api_key": "bdp_abc123...",
    "status": "active"
  }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epProfile">
                                <span class="badge bg-primary me-2">GET</span> /api/v1/merchants/profile
                            </button>
                        </h2>
                        <div id="epProfile" class="accordion-collapse collapse" data-bs-parent="#merchantEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Get your merchant profile, API keys, and transaction statistics.</p>
                                <h6>Response</h6>
                                <div class="bg-light rounded-3 p-3">
<pre><code>{
  "success": true,
  "data": {
    "id": 1,
    "merchant_id": "BDCM-AB12CD34EF56GH78",
    "business_name": "My Store Inc.",
    "contact_name": "John Doe",
    "email": "john@mystore.com",
    "payment_method": "bdcoin",
    "wallet_address": "BDC1A2B3C4D5E6F7G8H9I0J",
    "status": "active",
    "created_at": "2026-07-15 10:00:00",
    "api_keys": [{"id": 1, "label": "My Store Inc.", "active": 1}],
    "stats": {"total": 150, "revenue": 12500.00}
  }
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epUpdateProfile">
                                <span class="badge bg-warning text-dark me-2">PUT</span> /api/v1/merchants/profile
                            </button>
                        </h2>
                        <div id="epUpdateProfile" class="accordion-collapse collapse" data-bs-parent="#merchantEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Update your merchant profile. Only send the fields you want to change.</p>
                                <h6>Request Body</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Field</th><th>Type</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>business_name</td><td>string</td><td>New business name</td></tr>
                                            <tr><td>contact_name</td><td>string</td><td>New contact name</td></tr>
                                            <tr><td>email</td><td>string</td><td>New email address</td></tr>
                                            <tr><td>wallet_address</td><td>string</td><td>New payout wallet address</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epTxns">
                                <span class="badge bg-primary me-2">GET</span> /api/v1/merchants/transactions
                            </button>
                        </h2>
                        <div id="epTxns" class="accordion-collapse collapse" data-bs-parent="#merchantEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">View completed transactions with pagination.</p>
                                <h6>Query Parameters</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Parameter</th><th>Default</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>page</td><td>1</td><td>Page number</td></tr>
                                            <tr><td>per_page</td><td>20</td><td>Items per page (max 100)</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epSettle">
                                <span class="badge bg-primary me-2">GET</span> /api/v1/merchants/settlements
                            </button>
                        </h2>
                        <div id="epSettle" class="accordion-collapse collapse" data-bs-parent="#merchantEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Get settlement summaries grouped by day, with crypto vs fiat breakdown.</p>
                                <h6>Query Parameters</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Parameter</th><th>Default</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>period</td><td>all</td><td>Filter: today, week, month, year, all</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-credit-card"></i> Payment Endpoints</h3>
                <p>Create and manage payments through the API.</p>

                <div class="accordion mb-4" id="paymentEndpoints">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epPayCreate">
                                <span class="badge bg-success me-2">POST</span> /api/v1/payments
                            </button>
                        </h2>
                        <div id="epPayCreate" class="accordion-collapse collapse" data-bs-parent="#paymentEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Create a new payment. This endpoint does not require authentication.</p>
                                <h6>Request Body</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>payer_name</td><td>string</td><td><span class="text-danger">Yes</span></td><td>Customer full name</td></tr>
                                            <tr><td>payer_email</td><td>string</td><td><span class="text-danger">Yes</span></td><td>Customer email</td></tr>
                                            <tr><td>amount</td><td>number</td><td><span class="text-danger">Yes</span></td><td>Amount in USD (min: 1, max: 25000)</td></tr>
                                            <tr><td>payment_method</td><td>string</td><td><span class="text-danger">Yes</span></td><td>stripe, google_pay, visa, crypto, bdcoin, square</td></tr>
                                            <tr><td>currency</td><td>string</td><td>No</td><td>Currency code (default: USD)</td></tr>
                                            <tr><td>description</td><td>string</td><td>No</td><td>Payment description</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <h6>Example</h6>
                                <div class="bg-dark text-light rounded-3 p-3">
<pre><code>curl -X POST <?= API_URL ?>v1/payments \
  -H "Content-Type: application/json" \
  -d '{
    "payer_name": "Jane Smith",
    "payer_email": "jane@example.com",
    "amount": 99.99,
    "payment_method": "stripe"
  }'</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epPayList">
                                <span class="badge bg-primary me-2">GET</span> /api/v1/payments
                            </button>
                        </h2>
                        <div id="epPayList" class="accordion-collapse collapse" data-bs-parent="#paymentEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">List all payments with optional filters.</p>
                                <h6>Query Parameters</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>page</td><td>Page number (default: 1)</td></tr>
                                            <tr><td>per_page</td><td>Items per page (default: 20, max: 100)</td></tr>
                                            <tr><td>status</td><td>Filter by status: completed, pending, failed</td></tr>
                                            <tr><td>method</td><td>Filter by payment method</td></tr>
                                            <tr><td>search</td><td>Search by transaction ID, name, or email</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-currency-bitcoin"></i> BDCoin & Crypto Endpoints</h3>
                <p>Manage wallets and transfer tokens.</p>

                <div class="accordion mb-4" id="cryptoEndpoints">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epWallets">
                                <span class="badge bg-primary me-2">GET</span> /api/v1/bdcoin/wallets
                            </button>
                        </h2>
                        <div id="epWallets" class="accordion-collapse collapse" data-bs-parent="#cryptoEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">List all BDCoin wallets with balances, total supply, and market cap.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epTransfer">
                                <span class="badge bg-success me-2">POST</span> /api/v1/bdcoin/transfer
                            </button>
                        </h2>
                        <div id="epTransfer" class="accordion-collapse collapse" data-bs-parent="#cryptoEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Send BDCoin between wallets.</p>
                                <h6>Request Body</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Field</th><th>Required</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>from_address</td><td><span class="text-danger">Yes</span></td><td>Source wallet address</td></tr>
                                            <tr><td>to_address</td><td><span class="text-danger">Yes</span></td><td>Destination wallet address</td></tr>
                                            <tr><td>amount</td><td><span class="text-danger">Yes</span></td><td>Amount in BDC (min: 0.0001)</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epRates">
                                <span class="badge bg-primary me-2">GET</span> /api/v1/rates
                            </button>
                        </h2>
                        <div id="epRates" class="accordion-collapse collapse" data-bs-parent="#cryptoEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Get all crypto exchange rates. No authentication required.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-arrow-left-right"></i> Webhooks</h3>
                <p>Receive real-time payment notifications by setting up webhook endpoints.</p>

                <div class="accordion mb-4" id="webhookEndpoints">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epWebhook">
                                <span class="badge bg-secondary me-2">POST</span> /api/v1/webhooks/custom
                            </button>
                        </h2>
                        <div id="epWebhook" class="accordion-collapse collapse" data-bs-parent="#webhookEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Send transaction status updates to your own webhook URL.</p>
                                <h6>Request Body</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Field</th><th>Type</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>transaction_id</td><td>string</td><td>The transaction ID to update</td></tr>
                                            <tr><td>status</td><td>string</td><td>New status: completed, failed, refunded</td></tr>
                                            <tr><td>event</td><td>string</td><td>Event type (e.g., payment.completed)</td></tr>
                                            <tr><td>data</td><td>object</td><td>Additional webhook payload</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <h6>Platform-Specific Webhooks</h6>
                                <ul>
                                    <li><code>POST /api/v1/webhooks/stripe</code> — Stripe webhook receiver</li>
                                    <li><code>POST /api/v1/webhooks/square</code> — Square webhook receiver</li>
                                    <li><code>POST /api/v1/webhooks/crypto</code> — Crypto blockchain webhook receiver</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-arrow-return-left"></i> Refunds</h3>
                <div class="accordion mb-4" id="refundEndpoints">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epRefund">
                                <span class="badge bg-success me-2">POST</span> /api/v1/refunds
                            </button>
                        </h2>
                        <div id="epRefund" class="accordion-collapse collapse" data-bs-parent="#refundEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Process a refund for a completed transaction.</p>
                                <h6>Request Body</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Field</th><th>Required</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>transaction_id</td><td><span class="text-danger">Yes</span></td><td>The transaction to refund</td></tr>
                                            <tr><td>amount</td><td>No</td><td>Partial refund amount (default: full amount)</td></tr>
                                            <tr><td>reason</td><td>No</td><td>Reason for the refund</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-key"></i> API Key Management</h3>
                <div class="accordion mb-4" id="keyEndpoints">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epKeys">
                                <span class="badge bg-primary me-2">GET</span> /api/v1/keys
                            </button>
                        </h2>
                        <div id="epKeys" class="accordion-collapse collapse" data-bs-parent="#keyEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">List all API keys for your account.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#epKeyCreate">
                                <span class="badge bg-success me-2">POST</span> /api/v1/keys
                            </button>
                        </h2>
                        <div id="epKeyCreate" class="accordion-collapse collapse" data-bs-parent="#keyEndpoints">
                            <div class="accordion-body">
                                <p class="text-muted">Generate a new API key.</p>
                                <h6>Request Body</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Field</th><th>Required</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr><td>label</td><td><span class="text-danger">Yes</span></td><td>A label to identify this key (e.g., "Production Server")</td></tr>
                                            <tr><td>merchant_id</td><td>No</td><td>Associate key with a merchant</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-exclamation-triangle"></i> Errors & Rate Limiting</h3>
                <div class="table-responsive mb-3">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr><th>Code</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>400</td><td>Bad request — invalid parameters</td></tr>
                            <tr><td>401</td><td>Missing or invalid API key</td></tr>
                            <tr><td>404</td><td>Resource not found</td></tr>
                            <tr><td>422</td><td>Validation failed — check the response for field errors</td></tr>
                            <tr><td>429</td><td>Rate limit exceeded — 60 requests per minute</td></tr>
                            <tr><td>500</td><td>Internal server error</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>Rate-limited responses include <code>X-RateLimit-Remaining</code> and <code>X-RateLimit-Reset</code> headers.</p>

                <hr>
                <h3 class="fw-bold"><i class="bi bi-terminal"></i> Code Examples</h3>

                <ul class="nav nav-tabs mb-3" id="codeTabs">
                    <li class="nav-item"><a class="nav-link active" href="#codeCurl" data-bs-toggle="tab">cURL</a></li>
                    <li class="nav-item"><a class="nav-link" href="#codePhp" data-bs-toggle="tab">PHP</a></li>
                    <li class="nav-item"><a class="nav-link" href="#codeJs" data-bs-toggle="tab">JavaScript</a></li>
                    <li class="nav-item"><a class="nav-link" href="#codePython" data-bs-toggle="tab">Python</a></li>
                </ul>

                <div class="tab-content mb-4">
                    <div class="tab-pane fade show active" id="codeCurl">
                        <div class="bg-dark text-light rounded-3 p-3">
<pre><code>#!/bin/bash
API_KEY="bdp_your_api_key_here"
BASE="<?= API_URL ?>v1"

# Get merchant profile
curl -H "X-API-Key: $API_KEY" $BASE/merchants/profile

# List payments
curl -H "X-API-Key: $API_KEY" "$BASE/payments?page=1&per_page=10"

# Create a payment
curl -X POST $BASE/payments \
  -H "Content-Type: application/json" \
  -d '{
    "payer_name": "Jane Smith",
    "payer_email": "jane@example.com",
    "amount": 49.99,
    "payment_method": "stripe"
  }'

# Get settlements
curl -H "X-API-Key: $API_KEY" "$BASE/merchants/settlements?period=month"</code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="codePhp">
                        <div class="bg-dark text-light rounded-3 p-3">
<pre><code>&lt;?php
$apiKey = 'bdp_your_api_key_here';
$base = '<?= API_URL ?>v1';

$ch = curl_init("$base/merchants/profile");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["X-API-Key: $apiKey"],
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo "Merchant: " . $data['data']['business_name'];</code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="codeJs">
                        <div class="bg-dark text-light rounded-3 p-3">
<pre><code>const API_KEY = 'bdp_your_api_key_here';
const BASE = '<?= API_URL ?>v1';

async function getProfile() {
  const res = await fetch(`${BASE}/merchants/profile`, {
    headers: { 'X-API-Key': API_KEY }
  });
  const data = await res.json();
  console.log('Merchant:', data.data.business_name);
}

async function createPayment() {
  const res = await fetch(`${BASE}/payments`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      payer_name: 'Jane Smith',
      payer_email: 'jane@example.com',
      amount: 49.99,
      payment_method: 'stripe'
    })
  });
  const data = await res.json();
  console.log('Payment:', data.data.transaction_id);
}</code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="codePython">
                        <div class="bg-dark text-light rounded-3 p-3">
<pre><code>import requests

API_KEY = 'bdp_your_api_key_here'
BASE = '<?= API_URL ?>v1'
headers = {'X-API-Key': API_KEY}

# Get merchant profile
res = requests.get(f'{BASE}/merchants/profile', headers=headers)
profile = res.json()
print(f"Merchant: {profile['data']['business_name']}")

# Create a payment
payment = requests.post(f'{BASE}/payments', json={
    'payer_name': 'Jane Smith',
    'payer_email': 'jane@example.com',
    'amount': 49.99,
    'payment_method': 'stripe',
})
print(f"Txn ID: {payment.json()['data']['transaction_id']}")
</code></pre>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="alert alert-info">
                    <i class="bi bi-question-circle"></i> <strong>Need Help?</strong> Contact our developer support team at <a href="mailto:api@bdpay.com">api@bdpay.com</a> or visit the <a href="/bdpay/policies/payment_guide.php">Payment Guide</a> for merchant onboarding.
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
