<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <h2 class="fw-bold mb-4"><i class="bi bi-check-shield"></i> Compliance & Regulatory Information</h2>
                <p class="text-muted small">Last updated: July 15, 2026</p>
                <hr>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> BDPay is committed to full regulatory compliance across all jurisdictions in which we operate.
                </div>

                <h5>1. PCI DSS Compliance</h5>
                <p>BDPay is PCI DSS Level 1 compliant, the highest level of payment security certification. This means:</p>
                <ul>
                    <li>Annual on-site assessment by a Qualified Security Assessor (QSA)</li>
                    <li>Quarterly network vulnerability scans by an Approved Scanning Vendor (ASV)</li>
                    <li>Continuous monitoring of all cardholder data environments</li>
                    <li>Strict access controls and multi-factor authentication</li>
                    <li>Encryption of cardholder data at rest (AES-256) and in transit (TLS 1.2+)</li>
                    <li>Tokenization — we never store full card numbers on our servers</li>
                </ul>

                <h5>2. Anti-Money Laundering (AML)</h5>
                <p>Our AML program complies with the Financial Action Task Force (FATF) recommendations and local regulations:</p>
                <ul>
                    <li>Customer Due Diligence (CDD) and Enhanced Due Diligence (EDD) for high-risk profiles</li>
                    <li>Real-time sanctions screening against OFAC, UN, EU, and local sanctions lists</li>
                    <li>Transaction monitoring with behavioral analytics</li>
                    <li>Suspicious Activity Report (SAR) filing to relevant FIUs</li>
                    <li>Designated Money Laundering Reporting Officer (MLRO)</li>
                    <li>Travel Rule compliance for cryptocurrency transfers >$1,000</li>
                </ul>

                <h5>3. Know Your Customer (KYC)</h5>
                <p>Merchants and high-value customers must complete KYC verification:</p>
                <ul>
                    <li>Government-issued photo ID verification</li>
                    <li>Proof of address (utility bill or bank statement, dated within 90 days)</li>
                    <li>Beneficial ownership declaration (for entities)</li>
                    <li>Ongoing monitoring and periodic re-verification</li>
                </ul>

                <h5>4. Data Protection</h5>
                <p>BDPay adheres to global data protection standards including:</p>
                <table class="table table-sm">
                    <thead><tr><th>Jurisdiction</th><th>Regulation</th><th>Status</th></tr></thead>
                    <tbody>
                        <tr><td>EU/EEA</td><td>General Data Protection Regulation (GDPR)</td><td><span class="badge bg-success">Compliant</span></td></tr>
                        <tr><td>UK</td><td>UK GDPR / Data Protection Act 2018</td><td><span class="badge bg-success">Compliant</span></td></tr>
                        <tr><td>India</td><td>Digital Personal Data Protection Act 2023</td><td><span class="badge bg-success">Compliant</span></td></tr>
                        <tr><td>Brazil</td><td>Lei Geral de Proteção de Dados (LGPD)</td><td><span class="badge bg-success">Compliant</span></td></tr>
                        <tr><td>UAE</td><td>PDPL No. 45 of 2021</td><td><span class="badge bg-success">Compliant</span></td></tr>
                        <tr><td>Singapore</td><td>Personal Data Protection Act (PDPA)</td><td><span class="badge bg-success">Compliant</span></td></tr>
                        <tr><td>Nigeria</td><td>NDPA 2023</td><td><span class="badge bg-success">Compliant</span></td></tr>
                    </tbody>
                </table>

                <h5>5. Payment Licenses</h5>
                <p>BDPay holds or operates through licensed partners in the following jurisdictions:</p>
                <ul>
                    <li><strong>EU:</strong> Payment Institution License (passported across EEA)</li>
                    <li><strong>UK:</strong> FCA Authorized Payment Institution</li>
                    <li><strong>Singapore:</strong> MAS Standard Payment Institution</li>
                    <li><strong>US:</strong> Money Transmitter Licenses (select states) / FinCEN MSB Registration</li>
                </ul>

                <h5>6. BDCoin Compliance</h5>
                <p>BDCoin is a utility token within the BDPay ecosystem. It is not offered as a security or investment product. In jurisdictions where cryptocurrency regulation applies (MiCA in EU, VARA in Dubai, etc.), BDCoin transactions comply with applicable Virtual Asset Service Provider (VASP) requirements including Travel Rule and custody standards.</p>

                <h5>7. Licenses & Certifications</h5>
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center"><i class="bi bi-shield-check text-success fs-3"></i><br><small>PCI DSS Level 1</small></div></div>
                    <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center"><i class="bi bi-lock text-primary fs-3"></i><br><small>SOC 2 Type II</small></div></div>
                    <div class="col-md-4"><div class="bg-light rounded-3 p-3 text-center"><i class="bi bi-globe text-info fs-3"></i><br><small>ISO 27001</small></div></div>
                </div>

                <h5>8. Complaints & Grievances</h5>
                <p><strong>Nodal Officer:</strong> John Doe<br>
                Email: grievances@bdpay.com<br>
                Phone: +1 (555) 987-6543<br>
                Address: BDPay International, Compliance Department, 123 Payment Street, Financial District, 10001<br>
                Response Time: We acknowledge complaints within 48 hours and resolve within 15 business days.</p>

                <h5>9. Regulatory Inquiries</h5>
                <p>Regulatory authorities may contact: compliance@bdpay.com</p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
