<?php
require_once __DIR__ . '/../config/oauth.php';

function getAuthorizationUrl(string $provider, string $redirect = '/bdpay/admin/'): string {
    global $oauth_providers;
    $p = $oauth_providers[$provider] ?? null;
    if (!$p) return '';

    $state = bin2hex(random_bytes(32));
    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_provider'] = $provider;
    $_SESSION['oauth_redirect'] = $redirect;

    $params = http_build_query([
        'client_id' => $p['client_id'],
        'redirect_uri' => getOAuthRedirectUrl($provider),
        'response_type' => 'code',
        'scope' => implode(' ', $p['scopes']),
        'state' => $state,
        'access_type' => 'offline',
        'prompt' => 'select_account',
    ]);

    return $p['auth_url'] . '?' . $params;
}

function exchangeCodeForToken(string $provider, string $code): ?array {
    global $oauth_providers;
    $p = $oauth_providers[$provider] ?? null;
    if (!$p) return null;

    $ch = curl_init($p['token_url']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'code' => $code,
            'client_id' => $p['client_id'],
            'client_secret' => $p['client_secret'],
            'redirect_uri' => getOAuthRedirectUrl($provider),
            'grant_type' => 'authorization_code',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) return null;

    $data = json_decode($response, true);
    return $data['access_token'] ?? null ? $data : null;
}

function fetchUserInfo(string $provider, string $accessToken): ?array {
    global $oauth_providers;
    $p = $oauth_providers[$provider] ?? null;
    if (!$p) return null;

    $ch = curl_init($p['user_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_TIMEOUT => 15,
    ]);

    if ($provider === 'facebook') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) return null;

    $data = json_decode($response, true);
    if (!$data) return null;

    switch ($provider) {
        case 'google':
            return [
                'provider_user_id' => $data['id'] ?? '',
                'email' => $data['email'] ?? '',
                'name' => $data['name'] ?? '',
                'avatar_url' => $data['picture'] ?? '',
            ];
        case 'facebook':
            return [
                'provider_user_id' => $data['id'] ?? '',
                'email' => $data['email'] ?? '',
                'name' => $data['name'] ?? '',
                'avatar_url' => isset($data['picture']['data']['url']) ? $data['picture']['data']['url'] : '',
            ];
        case 'yahoo':
            return [
                'provider_user_id' => $data['sub'] ?? '',
                'email' => $data['email'] ?? '',
                'name' => $data['name'] ?? '',
                'avatar_url' => $data['picture'] ?? '',
            ];
        default:
            return null;
    }
}

function upsertOAuthUser(\PDO $db, string $provider, array $userInfo): ?array {
    $stmt = $db->prepare("SELECT * FROM oauth_users WHERE provider = ? AND provider_user_id = ?");
    $stmt->execute([$provider, $userInfo['provider_user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $db->prepare("UPDATE oauth_users SET last_login = CURRENT_TIMESTAMP, email = ?, name = ?, avatar_url = ? WHERE id = ?");
        $stmt->execute([$userInfo['email'], $userInfo['name'], $userInfo['avatar_url'], $user['id']]);
        return $user;
    }

    $stmt = $db->prepare("INSERT INTO oauth_users (provider, provider_user_id, email, name, avatar_url, admin) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->execute([$provider, $userInfo['provider_user_id'], $userInfo['email'], $userInfo['name'], $userInfo['avatar_url']]);
    $userInfo['id'] = (int)$db->lastInsertId();
    $userInfo['admin'] = 1;
    return $userInfo;
}
