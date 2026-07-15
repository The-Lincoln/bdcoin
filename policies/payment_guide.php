<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <h2 class="fw-bold mb-4"><i class="bi bi-journal-text"></i> Payment User Guide</h2>
                <p class="text-muted small">Last updated: July 15, 2026</p>
                <hr>

                <div class="alert alert-primary">
                    <i class="bi bi-info-circle"></i> This guide explains how to pay for services and how to receive payments through BDPay. Choose the section that applies to you.
                </div>

                <ul class="nav nav-tabs mb-4" id="guideTabs">
                    <li class="nav-item"><a class="nav-link active" href="#paying" data-bs-toggle="tab">Paying for Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#getting-paid" data-bs-toggle="tab">Getting Paid</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="paying">
                        <h4><i class="bi bi-cart-check text-primary"></i> How to Pay for Services</h4>
                        <p>BDPay supports multiple payment methods. Follow the steps below to complete a payment.</p>

                        <h5 class="mt-4">Step 1: Choose a Payment Method</h5>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-stripe text-primary fs-3"></i><br><strong>Stripe</strong><br><small class="text-muted">Credit/Debit cards</small></div></div>
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-google text-warning fs-3"></i><br><strong>Google Pay</strong><br><small class="text-muted">Fast checkout</small></div></div>
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-credit-card text-info fs-3"></i><br><strong>Visa</strong><br><small class="text-muted">Direct card payment</small></div></div>
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-currency-bitcoin text-warning fs-3"></i><br><strong>Crypto</strong><br><small class="text-muted">BTC, ETH, USDT, BNB</small></div></div>
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-gem text-success fs-3"></i><br><strong>BDCoin</strong><br><small class="text-muted">Native token</small></div></div>
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-credit-card-2-front text-dark fs-3"></i><br><strong>Square</strong><br><small class="text-muted">Card payments</small></div></div>
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-bank text-secondary fs-3"></i><br><strong>Bank Pay</strong><br><small class="text-muted">Wire transfer</small></div></div>
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-phone text-danger fs-3"></i><br><strong>bKash</strong><br><small class="text-muted">Mobile wallet</small></div></div>
                            <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center h-100"><i class="bi bi-phone text-warning fs-3"></i><br><strong>Nagad</strong><br><small class="text-muted">Mobile wallet</small></div></div>
                        </div>

                        <h5>Step 2: Enter Payment Details</h5>
                        <ul>
                            <li><strong>Name & Email</strong> — Provide your full name and email address. A receipt will be sent to this email.</li>
                            <li><strong>Amount</strong> — Enter the amount in USD. Minimum: $1.00, Maximum: $25,000 per transaction.</li>
                            <li><strong>Card Info (if applicable)</strong> — Enter card number, expiry date, and CVV. All data is transmitted over TLS 1.2+ encryption; BDPay never stores full card numbers.</li>
                            <li><strong>Wallet (for crypto/BDC/SWPE)</strong> — Select the wallet you wish to pay from. Ensure sufficient balance before confirming.</li>
                        </ul>

                        <h5>Step 3: Confirm & Complete</h5>
                        <ul>
                            <li>Review your payment details before submitting.</li>
                            <li>Click the <strong>"Pay"</strong> button to process the payment.</li>
                            <li>You will be redirected to a confirmation page with your transaction ID and receipt.</li>
                            <li>A confirmation email will be sent to your registered email address.</li>
                        </ul>

                        <h5 class="mt-4">Supported Payment Methods Comparison</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr><th>Method</th><th>Processing Time</th><th>Fee</th><th>Max per Tx</th><th>Refundable</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Stripe</td><td>Instant</td><td>2.9% + $0.30</td><td>$25,000</td><td>Yes (5-10 days)</td></tr>
                                    <tr><td>Google Pay</td><td>Instant</td><td>0% + $0.00</td><td>$10,000</td><td>Yes (5-10 days)</td></tr>
                                    <tr><td>Visa</td><td>Instant</td><td>2.5% + $0.25</td><td>$25,000</td><td>Yes (3-7 days)</td></tr>
                                    <tr><td>Crypto</td><td>1-30 min (network)</td><td>0.5%</td><td>$100,000</td><td>Merchant discretion</td></tr>
                                    <tr><td>BDCoin</td><td>Instant (on-chain)</td><td>0.1%</td><td>Unlimited</td><td>Merchant discretion</td></tr>
                                    <tr><td>SWPE Token</td><td>Instant (on-chain)</td><td>0.3%</td><td>Unlimited</td><td>Merchant discretion</td></tr>
                                    <tr><td>Square</td><td>Instant</td><td>2.6% + $0.10</td><td>$25,000</td><td>Yes (5-10 days)</td></tr>
                                    <tr><td>Bank Pay</td><td>1-2 business days</td><td>$1.00</td><td>$50,000</td><td>Yes (7-14 days)</td></tr>
                                    <tr><td>bKash</td><td>Within 24 hours</td><td>$0.50</td><td>$10,000</td><td>Yes (3-5 days)</td></tr>
                                    <tr><td>Nagad</td><td>Within 24 hours</td><td>$0.50</td><td>$10,000</td><td>Yes (3-5 days)</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="mt-4">Frequently Asked Questions</h5>
                        <div class="accordion" id="payFAQ">
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q1">What payment methods are accepted?</button></h2><div id="q1" class="accordion-collapse collapse" data-bs-parent="#payFAQ"><div class="accordion-body">We accept Stripe, Google Pay, Visa, Cryptocurrency (BTC, ETH, USDT, BNB), BDCoin, SWPE Token, Square, Bank Pay, bKash, and Nagad. All major credit and debit cards are supported through our card processing partners.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q2">Is my payment information secure?</button></h2><div id="q2" class="accordion-collapse collapse" data-bs-parent="#payFAQ"><div class="accordion-body">Yes. All payment data is encrypted using TLS 1.2+ in transit and AES-256 at rest. We are PCI DSS Level 1 compliant and use tokenization to avoid storing sensitive card data. Cryptocurrency transactions are secured by their respective blockchain networks.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q3">Can I get a refund?</button></h2><div id="q3" class="accordion-collapse collapse" data-bs-parent="#payFAQ"><div class="accordion-body">Refund eligibility depends on the payment method and the merchant's policy. Card payments are eligible for refunds or chargebacks within 14 days. Cryptocurrency and BDCoin/SWPE transactions are irreversible on-chain; refunds are at merchant discretion. See our <a href="refund.php">Refund Policy</a> for details.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q4">How long does a payment take to process?</button></h2><div id="q4" class="accordion-collapse collapse" data-bs-parent="#payFAQ"><div class="accordion-body">Card payments (Stripe, Google Pay, Visa, Square) are instant. Cryptocurrency payments depend on network confirmation times: Bitcoin ~10-30 min, Ethereum ~1-5 min, USDT/BNB ~1-2 min. BDCoin and SWPE transfers are instant on the BDPay Chain.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q5">What is the maximum transaction amount?</button></h2><div id="q5" class="accordion-collapse collapse" data-bs-parent="#payFAQ"><div class="accordion-body">Card payments: $25,000 per transaction. Cryptocurrency: $100,000 per transaction. BDCoin and SWPE: no limit. Higher amounts may require additional KYC verification.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q6">Will I receive a receipt?</button></h2><div id="q6" class="accordion-collapse collapse" data-bs-parent="#payFAQ"><div class="accordion-body">Yes. A detailed receipt is emailed to the address you provide at checkout. You can also view and print your receipt from the success page after payment.</div></div></div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="getting-paid">
                        <h4><i class="bi bi-bank text-success"></i> How to Get Paid (Merchant Guide)</h4>
                        <p>BDPay enables merchants to accept payments from customers worldwide. This section explains the merchant onboarding process and how settlements work.</p>

                        <h5 class="mt-4">Step 1: Merchant Onboarding</h5>
                        <ol>
                            <li><strong>Register</strong> — Create a merchant account via the <a href="/bdpay/admin/">Admin Dashboard</a>.</li>
                            <li><strong>Verify Your Identity</strong> — Complete KYC by submitting:
                                <ul>
                                    <li>Government-issued photo ID (passport, driver's license, national ID)</li>
                                    <li>Proof of business registration (for business accounts)</li>
                                    <li>Proof of address (utility bill or bank statement, dated within 90 days)</li>
                                    <li>Beneficial ownership declaration (if applicable)</li>
                                </ul>
                            </li>
                            <li><strong>Integrate BDPay</strong> — Use our API or hosted payment pages to accept payments. Generate API keys from the admin panel.</li>
                            <li><strong>Go Live</strong> — Once verified and integrated, you can start accepting live payments.</li>
                        </ol>

                        <h5>Step 2: Accepting Payments</h5>
                        <p>Merchants can accept payments through two methods:</p>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6><i class="bi bi-link-45deg text-primary"></i> Payment Links</h6>
                                        <p class="small mb-0">Share a payment link with your customer. They complete the payment on BDPay's hosted page. No integration required.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6><i class="bi bi-code-slash text-success"></i> API Integration</h6>
                                        <p class="small mb-0">Embed BDPay directly into your website or app using our REST API. Full control over the checkout experience.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5>Step 3: Settlement & Payouts</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr><th>Payment Method</th><th>Settlement Time</th><th>Settlement Currency</th><th>Minimum Payout</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Stripe</td><td>T+2 business days</td><td>USD</td><td>$10</td></tr>
                                    <tr><td>Google Pay</td><td>T+2 business days</td><td>USD</td><td>$10</td></tr>
                                    <tr><td>Visa</td><td>T+2 business days</td><td>USD</td><td>$10</td></tr>
                                    <tr><td>Cryptocurrency</td><td>T+0 (instant)</td><td>BTC/ETH/USDT/BNB</td><td>0.001 BTC / 0.01 ETH</td></tr>
                                    <tr><td>BDCoin</td><td>T+0 (instant)</td><td>BDC</td><td>1 BDC</td></tr>
                                    <tr><td>SWPE Token</td><td>T+0 (instant)</td><td>SWPE</td><td>10 SWPE</td></tr>
                                    <tr><td>Square</td><td>T+2 business days</td><td>USD</td><td>$10</td></tr>
                                    <tr><td>Bank Pay</td><td>T+1 business day</td><td>USD</td><td>$10</td></tr>
                                    <tr><td>bKash</td><td>Within 24 hours</td><td>BDT</td><td>$10</td></tr>
                                    <tr><td>Nagad</td><td>Within 24 hours</td><td>BDT</td><td>$10</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="mt-4">Transaction & Withdrawal Fees</h5>
                        <ul>
                            <li><strong>Processing Fee:</strong> Deducted at the time of payment (varies by method, see comparison table above).</li>
                            <li><strong>Settlement Fee:</strong> $0.50 per payout for fiat settlements. Free for cryptocurrency and BDCoin/SWPE.</li>
                            <li><strong>Chargeback Fee:</strong> $15 per chargeback event (card payments only). Refunded if chargeback is won.</li>
                            <li><strong>API Access:</strong> Free for up to 1,000 requests/hour. Higher tiers available on request.</li>
                        </ul>

                        <h5 class="mt-4">Best Practices for Merchants</h5>
                        <ul>
                            <li><strong>Display Clear Policies</strong> — Ensure your refund, shipping, and cancellation policies are prominently displayed before checkout.</li>
                            <li><strong>Use Webhooks</strong> — Set up webhook endpoints to receive real-time payment notifications and automate order fulfillment.</li>
                            <li><strong>Monitor Transactions</strong> — Regularly review transactions in the admin dashboard. Flag any suspicious activity immediately.</li>
                            <li><strong>Maintain Records</strong> — Keep transaction records for at least 5 years for compliance purposes.</li>
                            <li><strong>Secure Your API Keys</strong> — Never expose your API keys in client-side code. Rotate keys periodically.</li>
                        </ul>

                        <h5>Frequently Asked Questions</h5>
                        <div class="accordion" id="merchantFAQ">
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#m1">How do I get started as a merchant?</button></h2><div id="m1" class="accordion-collapse collapse" data-bs-parent="#merchantFAQ"><div class="accordion-body">Register through the <a href="/bdpay/admin/">Admin Dashboard</a>, complete KYC verification, generate an API key, and integrate using our API documentation at <code>/api/v1</code>.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#m2">When will I receive my funds?</button></h2><div id="m2" class="accordion-collapse collapse" data-bs-parent="#merchantFAQ"><div class="accordion-body">Fiat settlements are processed within T+2 business days. Cryptocurrency and BDCoin/SWPE payments are settled instantly to your designated wallet.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#m3">What happens if a transaction is disputed?</button></h2><div id="m3" class="accordion-collapse collapse" data-bs-parent="#merchantFAQ"><div class="accordion-body">Card payments may be subject to chargebacks. BDPay will notify you and request evidence of fulfillment. If valid evidence is provided within the response window, the chargeback may be overturned. Crypto/BDC/SWPE transactions are final and cannot be disputed.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#m4">Can I accept payments in multiple currencies?</button></h2><div id="m4" class="accordion-collapse collapse" data-bs-parent="#merchantFAQ"><div class="accordion-body">Currently, all payments are processed in USD. Cryptocurrency and BDCoin/SWPE amounts are calculated based on real-time exchange rates at the time of transaction.</div></div></div>
                            <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#m5">Is there a volume discount?</button></h2><div id="m5" class="accordion-collapse collapse" data-bs-parent="#merchantFAQ"><div class="accordion-body">Yes. Merchants processing over $50,000/month may qualify for reduced processing fees. Contact our sales team at sales@bdpay.com for a custom quote.</div></div></div>
                        </div>
                    </div>
                </div>

                <hr class="mt-4">
                <h5>Need Help?</h5>
                <p>Contact our support team for assistance with payments or merchant onboarding:</p>
                <ul>
                    <li><strong>Email:</strong> md.s.lincoln@gmail.com</li>
                    <li><strong>Phone:</strong> +8801715-340463</li>
                    <li><strong>Support Hours:</strong> 24/7</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
