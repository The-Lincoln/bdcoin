<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$public_pages = ['login.php', 'logout.php', '', '/'];

$current = basename($_SERVER['PHP_SELF']);
if (!in_array($current, ['login.php', 'logout.php', 'index.php', 'success.php', '404.php', 'install.php', 'invoice.php']) && !str_starts_with($current, 'api/') && !str_starts_with($current, 'webhook/')) {
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if (str_starts_with($current, 'admin/')) {
        $login_path = '/bdpay/login.php';
        if (isset($_SERVER['REQUEST_URI'])) {
            $redirect = urlencode($_SERVER['REQUEST_URI']);
            header("Location: $login_path?redirect=$redirect");
        } else {
            header("Location: $login_path");
        }
        exit;
    }
}

$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    if (str_starts_with($current, 'admin/')) {
        header("Location: /bdpay/login.php?expired=1");
        exit;
    }
}
$_SESSION['last_activity'] = time();
