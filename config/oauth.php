<?php
// OAuth 2.0 Provider Configuration
// Replace placeholder values with your actual app credentials from each provider.

$oauth_providers = [
    'google' => [
        'name' => 'Google',
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET',
        'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'user_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
        'scopes' => ['openid', 'email', 'profile'],
        'icon' => 'bi-google',
        'color' => 'danger',
        'btn_style' => 'btn-danger',
    ],
    'facebook' => [
        'name' => 'Facebook',
        'client_id' => getenv('FACEBOOK_CLIENT_ID') ?: 'YOUR_FACEBOOK_APP_ID',
        'client_secret' => getenv('FACEBOOK_CLIENT_SECRET') ?: 'YOUR_FACEBOOK_APP_SECRET',
        'auth_url' => 'https://www.facebook.com/v19.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v19.0/oauth/access_token',
        'user_url' => 'https://graph.facebook.com/me?fields=id,name,email,picture',
        'scopes' => ['email', 'public_profile'],
        'icon' => 'bi-facebook',
        'color' => 'primary',
        'btn_style' => 'btn-primary',
    ],
    'yahoo' => [
        'name' => 'Yahoo',
        'client_id' => getenv('YAHOO_CLIENT_ID') ?: 'YOUR_YAHOO_CLIENT_ID',
        'client_secret' => getenv('YAHOO_CLIENT_SECRET') ?: 'YOUR_YAHOO_CLIENT_SECRET',
        'auth_url' => 'https://api.login.yahoo.com/oauth2/request_auth',
        'token_url' => 'https://api.login.yahoo.com/oauth2/get_token',
        'user_url' => 'https://api.login.yahoo.com/openid/v1/userinfo',
        'scopes' => ['openid', 'email', 'profile'],
                'icon' => 'bi-envelope-at',
        'color' => 'purple',
        'btn_style' => 'btn-dark',
    ],
];

function getOAuthRedirectUrl(string $provider): string {
    return rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/') . "/oauth_callback.php?provider=$provider";
}
