<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <h2 class="fw-bold mb-4"><i class="bi bi-arrow-return-left"></i> Refund & Cancellation Policy</h2>
                <p class="text-muted small">Last updated: July 15, 2026</p>
                <hr>

                <h5>1. Refund Eligibility</h5>
                <p>Refund requests are accepted within 14 calendar days of the original transaction date. To be eligible, the following conditions must be met:</p>
                <ul>
                    <li>The request is submitted via the original payment method's transaction record</li>
                    <li>The merchant has agreed to process the refund</li>
                    <li>The transaction is not marked as "final" under applicable law</li>
                </ul>

                <h5>2. Refund Timelines</h5>
                <table class="table table-sm">
                    <thead><tr><th>Payment Method</th><th>Refund Timeline</th><th>Notes</th></tr></thead>
                    <tbody>
                        <tr><td>Stripe (Card)</td><td>5-10 business days</td><td>Subject to card issuer processing</td></tr>
                        <tr><td>Google Pay</td><td>5-10 business days</td><td>Refunded to original card</td></tr>
                        <tr><td>Visa</td><td>3-7 business days</td><td>Standard card refund rules apply</td></tr>
                        <tr><td>Cryptocurrency</td><td>Merchant discretion</td><td>On-chain transactions are irreversible</td></tr>
                        <tr><td>BDCoin</td><td>Merchant discretion</td><td>Network fee may apply for return</td></tr>
                    </tbody>
                </table>

                <h5>3. Chargebacks</h5>
                <p>For card payments, customers have the right to file a chargeback with their issuing bank under applicable card network rules. Grounds for chargebacks include: unauthorized transaction, non-receipt of goods, defective goods, or credit not processed. BDPay will represent valid transactions with evidence of fulfillment.</p>

                <h5>4. Cryptocurrency & BDCoin Finality</h5>
                <p>Cryptocurrency and BDCoin transactions are final once they achieve the required number of blockchain confirmations. BDPay cannot reverse or refund these transactions without merchant cooperation. We strongly recommend merchants establish their own refund policies for crypto transactions.</p>

                <h5>5. Cancellation Policy</h5>
                <p>Pending transactions may be cancelled before processing completes. Once a transaction shows as "completed," cancellation is no longer possible and a refund must be initiated instead.</p>

                <h5>6. Processing Fees</h5>
                <p>Original processing fees are generally non-refundable unless the refund is due to an error on BDPay's part. Merchants may absorb or pass on processing fees for refunds at their discretion.</p>

                <h5>7. Dispute Resolution</h5>
                <p>If you believe a refund was improperly denied, contact our dispute resolution team at disputes@bdpay.com. We will investigate and respond within 15 business days. Unresolved disputes may be escalated to binding arbitration.</p>

                <h5>8. Contact</h5>
                <p>For refund-related inquiries, contact: refunds@bdpay.com</p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
