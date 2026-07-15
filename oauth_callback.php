<?php
require_once 'includes/functions.php';
require_once 'config/database.php';
require_once 'includes/oauth_helper.php';

$db = Database::getInstance()->getConnection();
$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

if ($error) {
    header('Location: /bdpay/login.php?oauth_error=' . urlencode($error));
    exit;
}

if (!isset($oauth_providers[$provider]) || !$code || !$state) {
    header('Location: /bdpay/login.php?oauth_error=invalid_request');
    exit;
}

if (!isset($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
    header('Location: /bdpay/login.php?oauth_error=state_mismatch');
    exit;
}

$redirect = $_SESSION['oauth_redirect'] ?? '/bdpay/admin/';
unset($_SESSION['oauth_state'], $_SESSION['oauth_provider'], $_SESSION['oauth_redirect']);

$tokenData = exchangeCodeForToken($provider, $code);
if (!$tokenData) {
    header('Location: /bdpay/login.php?oauth_error=token_failed');
    exit;
}

$userInfo = fetchUserInfo($provider, $tokenData['access_token']);
if (!$userInfo || empty($userInfo['email'])) {
    header('Location: /bdpay/login.php?oauth_error=userinfo_failed');
    exit;
}

$oauthUser = upsertOAuthUser($db, $provider, $userInfo);
if (!$oauthUser) {
    header('Location: /bdpay/login.php?oauth_error=save_failed');
    exit;
}

$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_user'] = $userInfo['name'];
$_SESSION['admin_email'] = $userInfo['email'];
$_SESSION['oauth_provider'] = $provider;
$_SESSION['oauth_avatar'] = $userInfo['avatar_url'] ?? '';
$_SESSION['last_activity'] = time();

header("Location: $redirect");
exit;
