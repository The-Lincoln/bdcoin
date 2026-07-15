<?php require_once 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <h2 class="fw-bold mb-4"><i class="bi bi-book text-primary"></i> BDPay User Guide</h2>
                <p class="text-muted small">Learn how to use BDPay — send payments, manage wallets, register as a merchant, and more.</p>
                <hr>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> BDPay is a secure international payment gateway supporting Stripe, Google Pay, Visa, Cryptocurrency, BDCoin, SWPE Token, Square, Bank Pay, bKash, and Nagad.
                </div>

                <ul class="nav nav-tabs mb-4" id="guideTabs">
                    <li class="nav-item"><a class="nav-link active" href="#getting-started" data-bs-toggle="tab">Getting Started</a></li>
                    <li class="nav-item"><a class="nav-link" href="#payments" data-bs-toggle="tab">Payments</a></li>
                    <li class="nav-item"><a class="nav-link" href="#wallets" data-bs-toggle="tab">Wallets & Exchange</a></li>
                    <li class="nav-item"><a class="nav-link" href="#merchant" data-bs-toggle="tab">Merchant</a></li>
                    <li class="nav-item"><a class="nav-link" href="#admin" data-bs-toggle="tab">Admin Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="#api" data-bs-toggle="tab">API</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="getting-started">
                        <h4><i class="bi bi-rocket-takeoff text-primary"></i> Getting Started</h4>

                        <h5 class="mt-4">What is BDPay?</h5>
                        <p>BDPay International is a secure payment gateway that lets you send and receive payments using multiple methods — credit cards, Google Pay, cryptocurrencies, native tokens (BDCoin, SWPE), bank transfers, and mobile wallets (bKash, Nagad).</p>

                        <h5>How to Log In</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6><i class="bi bi-person-badge text-primary"></i> Admin Login</h6>
                                        <p class="small mb-0">Visit <a href="/bdpay/login.php">/login</a> and enter your email/username and password. Default: <code>admin / admin123</code>.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6><i class="bi bi-google text-danger"></i> OAuth Login</h6>
                                        <p class="small mb-0">Click <strong>Google</strong>, <strong>Facebook</strong>, or <strong>Yahoo</strong> on the login page to sign in with your existing account.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5>Navigation</h5>
                        <ul>
                            <li><strong>Top Navbar</strong> — Home, Pay With (all payment methods), API Docs, Admin</li>
                            <li><strong>Payment Toolbar</strong> — Quick-access buttons for each payment method, plus Wallets and Exchange</li>
                            <li><strong>Footer</strong> — Links to all payment methods, support contacts, legal policies, and documentation</li>
                        </ul>

                        <div class="bg-light rounded-3 p-3">
                            <h6 class="fw-bold"><i class="bi bi-lightbulb text-warning"></i> Tip</h6>
                            <p class="small mb-0">Bookmark the payment method you use most. The active method is highlighted in the toolbar.</p>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="payments">
                        <h4><i class="bi bi-credit-card text-primary"></i> Making Payments</h4>
                        <p>BDPay supports 10 payment methods. Each has its own dedicated page with a form to enter details and complete the transaction.</p>

                        <h5 class="mt-4">Payment Methods Overview</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr><th>Method</th><th>Fee</th><th>Processing Time</th><th>Max per Tx</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><i class="bi bi-stripe text-primary"></i> Stripe</td><td>2.9% + $0.30</td><td>Instant</td><td>$25,000</td></tr>
                                    <tr><td><i class="bi bi-google text-warning"></i> Google Pay</td><td>Free</td><td>Instant</td><td>$10,000</td></tr>
                                    <tr><td><i class="bi bi-credit-card text-info"></i> Visa</td><td>2.5% + $0.25</td><td>Instant</td><td>$25,000</td></tr>
                                    <tr><td><i class="bi bi-currency-bitcoin text-warning"></i> Crypto</td><td>0.5%</td><td>1-30 min</td><td>$100,000</td></tr>
                                    <tr><td><i class="bi bi-gem text-success"></i> BDCoin</td><td>0.1%</td><td>Instant</td><td>Unlimited</td></tr>
                                    <tr><td><i class="bi bi-lightning text-info"></i> SWPE Token</td><td>0.3%</td><td>Instant</td><td>Unlimited</td></tr>
                                    <tr><td><i class="bi bi-credit-card-2-front text-secondary"></i> Square</td><td>2.6% + $0.10</td><td>Instant</td><td>$25,000</td></tr>
                                    <tr><td><i class="bi bi-bank text-secondary"></i> Bank Pay</td><td>$1.00</td><td>1-2 business days</td><td>$50,000</td></tr>
                                    <tr><td><i class="bi bi-phone text-danger"></i> bKash</td><td>$0.50</td><td>Within 24 hours</td><td>$10,000</td></tr>
                                    <tr><td><i class="bi bi-phone text-warning"></i> Nagad</td><td>$0.50</td><td>Within 24 hours</td><td>$10,000</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5>Step-by-Step: Making a Payment</h5>
                        <ol>
                            <li>Click the payment method in the <strong>Pay With</strong> dropdown or the toolbar below the navbar</li>
                            <li>Fill in your <strong>Full Name</strong> and <strong>Email</strong></li>
                            <li>Enter the <strong>Amount</strong> in USD</li>
                            <li>Provide method-specific details (card number for Visa, wallet selection for BDCoin, etc.)</li>
                            <li>Click the <strong>Pay</strong> button</li>
                            <li>You'll be redirected to a confirmation page with your <strong>Transaction ID</strong> and receipt</li>
                        </ol>

                        <h5>BDCoin Payments</h5>
                        <ul>
                            <li>Select a BDCoin wallet with sufficient balance</li>
                            <li>The BDC amount is calculated automatically based on the USD amount you enter</li>
                            <li>1 BDC = $12.50</li>
                            <li>Transactions are instant on the BDPay Chain</li>
                        </ul>

                        <h5>Crypto Payments</h5>
                        <ul>
                            <li>Choose a cryptocurrency: Bitcoin (BTC), Ethereum (ETH), USDT, or BNB</li>
                            <li>Send the exact amount to the displayed wallet address</li>
                            <li>Click <strong>Confirm Crypto Payment</strong> after sending</li>
                            <li>The transaction will be verified on the blockchain</li>
                        </ul>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Cryptocurrency and BDCoin transactions are irreversible once confirmed on the blockchain. Always double-check addresses and amounts.
                        </div>
                    </div>

                    <div class="tab-pane fade" id="wallets">
                        <h4><i class="bi bi-wallet2 text-success"></i> Wallets & Exchange</h4>
                        <p>The <a href="/bdpay/payments/wallets.php">Wallet Manager</a> lets you manage BDCoin and SWPE Token wallets, transfer between them, and exchange one asset for another.</p>

                        <h5 class="mt-4">BDCoin Wallets</h5>
                        <ul>
                            <li><strong>Create Wallet</strong> — Generate a new BDC wallet address (prefixed with <code>BDC</code>)</li>
                            <li><strong>Transfer BDC</strong> — Send BDC to another wallet address. The destination is auto-created if it doesn't exist</li>
                            <li><strong>View Details</strong> — Click the eye icon to see wallet balance, USD value, and full transaction history</li>
                        </ul>

                        <h5>SWPE Token Wallets</h5>
                        <ul>
                            <li>Same features as BDCoin wallets, but for SWPE tokens (prefixed with <code>SWPE</code>)</li>
                            <li>1 SWPE = $0.75</li>
                        </ul>

                        <h5>Exchange (Swap)</h5>
                        <ol>
                            <li>Go to the <strong>Exchange</strong> tab in Wallet Manager</li>
                            <li>Select the asset to swap <strong>From</strong> (BDC or SWPE)</li>
                            <li>Choose the wallet to send from</li>
                            <li>Enter the amount</li>
                            <li>Select the asset to swap <strong>To</strong></li>
                            <li>Review the estimated rate, fee (0.5%), and amount you'll receive</li>
                            <li>Click <strong>Swap</strong> to execute</li>
                        </ol>

                        <div class="bg-light rounded-3 p-3">
                            <h6 class="fw-bold"><i class="bi bi-calculator"></i> Rate Calculation Example</h6>
                            <p class="small mb-0">Swapping 100 BDC to SWPE: 100 BDC × $12.50 = $1,250 USD → $1,250 / $0.75 = 1,666.67 SWPE → minus 0.5% fee = 1,658.33 SWPE received.</p>
                        </div>

                        <h5 class="mt-4">Portfolio Overview</h5>
                        <p>The Exchange tab also shows a portfolio view of all wallets with combined BDC and SWPE balances and total USD value.</p>
                    </div>

                    <div class="tab-pane fade" id="merchant">
                        <h4><i class="bi bi-shop text-warning"></i> Merchant Account</h4>
                        <p>Register as a merchant to accept BDPay payments from customers. Each payment method has its own merchant registration.</p>

                        <h5 class="mt-4">Registering as a Merchant</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6><i class="bi bi-gem text-success"></i> BDCoin Merchant</h6>
                                        <p class="small mb-0">Visit <a href="/bdpay/payments/bdcoin.php">BDCoin payment page</a> and scroll to the Merchant Account section. Fill in your business details and wallet address.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6><i class="bi bi-currency-bitcoin text-warning"></i> Crypto Merchant</h6>
                                        <p class="small mb-0">Visit <a href="/bdpay/payments/crypto.php">Crypto payment page</a> and scroll to the Merchant Account section.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5>Merchant Dashboard</h5>
                        <p>After registering, the merchant section shows:</p>
                        <ul>
                            <li><strong>Merchant ID</strong> — Unique identifier (e.g., BDCM-AB12CD34)</li>
                            <li><strong>Business Name</strong> and <strong>Contact</strong> details</li>
                            <li><strong>Wallet Address</strong> — Where you receive payouts</li>
                            <li><strong>Status</strong> badge (Active)</li>
                            <li><strong>Recent payments</strong> received</li>
                        </ul>

                        <h5>Merchant API</h5>
                        <p>Merchants can also register and manage their account programmatically:</p>
                        <ul>
                            <li><code>POST /api/v1/merchants/register</code> — Register and get an API key</li>
                            <li><code>GET /api/v1/merchants/profile</code> — View your merchant profile and stats</li>
                            <li><code>GET /api/v1/merchants/transactions</code> — View transaction history</li>
                            <li><code>GET /api/v1/merchants/settlements</code> — View settlement summaries</li>
                        </ul>
                        <p>Full documentation at <a href="/bdpay/docs/">API Docs</a>.</p>

                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> BDCoin merchants receive instant on-chain settlement with only 0.1% processing fees. Crypto merchants pay 0.5%.
                        </div>
                    </div>

                    <div class="tab-pane fade" id="admin">
                        <h4><i class="bi bi-shield-lock text-primary"></i> Admin Panel</h4>
                        <p>The <a href="/bdpay/admin/">Admin Dashboard</a> gives you full control over your BDPay instance.</p>

                        <h5 class="mt-4">Dashboard</h5>
                        <ul>
                            <li><strong>Statistics Cards</strong> — Total transactions, total revenue, BDCoin supply, pending transactions</li>
                            <li><strong>Payment Method Distribution</strong> — Count and revenue per payment method</li>
                            <li><strong>BDCoin Wallets</strong> — Overview of all BDC wallets</li>
                            <li><strong>Recent Transactions</strong> — Last 20 transactions</li>
                        </ul>

                        <h5>Transactions</h5>
                        <ul>
                            <li>View all transactions with search (by ID, name, or email)</li>
                            <li>Filter by status (completed, pending, failed)</li>
                            <li>Paginated results — 20 per page</li>
                            <li>Export to CSV</li>
                        </ul>

                        <h5>Setup Wizard</h5>
                        <p>Run the <a href="/bdpay/install.php">Setup Wizard</a> to configure admin credentials, BDCoin price, and load demo data.</p>
                    </div>

                    <div class="tab-pane fade" id="api">
                        <h4><i class="bi bi-code-slash text-primary"></i> API Integration</h4>
                        <p>BDPay provides a full REST API for developers. Full documentation is at <a href="/bdpay/docs/">API Docs</a>.</p>

                        <h5 class="mt-4">Quick Start</h5>
                        <ol>
                            <li>Get an API key — register as a merchant or generate one in the Admin panel</li>
                            <li>Include the key in the <code>X-API-Key</code> header</li>
                            <li>Base URL: <code>/bdpay/api/v1</code></li>
                        </ol>

                        <h5>Key Endpoints</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr><th>Method</th><th>Endpoint</th><th>Description</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><span class="badge bg-success">POST</span></td><td><code>/merchants/register</code></td><td>Register as a merchant</td></tr>
                                    <tr><td><span class="badge bg-primary">GET</span></td><td><code>/merchants/profile</code></td><td>Get merchant profile</td></tr>
                                    <tr><td><span class="badge bg-success">POST</span></td><td><code>/payments</code></td><td>Create a payment</td></tr>
                                    <tr><td><span class="badge bg-primary">GET</span></td><td><code>/payments</code></td><td>List payments</td></tr>
                                    <tr><td><span class="badge bg-success">POST</span></td><td><code>/bdcoin/transfer</code></td><td>Send BDCoin</td></tr>
                                    <tr><td><span class="badge bg-primary">GET</span></td><td><code>/rates</code></td><td>Get exchange rates</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5>Code Example (cURL)</h5>
                        <div class="bg-dark text-light rounded-3 p-3">
<pre><code>curl -H "X-API-Key: bdp_your_key_here" \
  /bdpay/api/v1/merchants/profile</code></pre>
                        </div>
                    </div>
                </div>

                <hr>
                <h5>Need Help?</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-envelope fs-3 text-primary"></i>
                                <h6>Email</h6>
                                <p class="small mb-0">md.s.lincoln@gmail.com</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-telephone fs-3 text-success"></i>
                                <h6>Phone</h6>
                                <p class="small mb-0">+8801715-340463</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-clock fs-3 text-info"></i>
                                <h6>Hours</h6>
                                <p class="small mb-0">24/7 Support</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
