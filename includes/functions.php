<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = false;
}

function requireAdmin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        redirect('login.php');
    }
}

function generateApiKey() {
    return 'bdp_' . bin2hex(random_bytes(24));
}

function sendEmail($to, $subject, $body) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . APP_NAME . " <md.s.lincoln@gmail.com>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    return mail($to, $subject, $body, $headers);
}

function sendPaymentReceipt($txn) {
    $subject = "Payment Receipt - " . APP_NAME;
    $body = "
    <div style='font-family: Arial; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #667eea, #764ba2); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: white; margin: 0;'>" . APP_NAME . "</h1>
        </div>
        <div style='padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
            <h2>Payment " . ucfirst($txn['status']) . "</h2>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr><td style='padding: 8px; color: #666;'>Transaction ID</td><td style='padding: 8px; font-weight: bold;'>" . $txn['transaction_id'] . "</td></tr>
                <tr style='background: #f9f9f9;'><td style='padding: 8px; color: #666;'>Amount</td><td style='padding: 8px; font-weight: bold;'>$" . number_format($txn['amount'], 2) . "</td></tr>
                <tr><td style='padding: 8px; color: #666;'>Method</td><td style='padding: 8px; font-weight: bold;'>" . $txn['payment_method'] . "</td></tr>
                <tr style='background: #f9f9f9;'><td style='padding: 8px; color: #666;'>Date</td><td style='padding: 8px;'>" . date('M d, Y H:i:s', strtotime($txn['created_at'])) . "</td></tr>
                <tr><td style='padding: 8px; color: #666;'>Status</td><td style='padding: 8px; font-weight: bold; color: " . ($txn['status'] === 'completed' ? '#28a745' : '#dc3545') . ";'>" . ucfirst($txn['status']) . "</td></tr>
            </table>
            <p style='color: #999; font-size: 12px; margin-top: 20px;'>This is an automated receipt from " . APP_NAME . ". For support, contact md.s.lincoln@gmail.com</p>
        </div>
    </div>";
    return sendEmail($txn['payer_email'], $subject, $body);
}
