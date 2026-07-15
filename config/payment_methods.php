<?php
$payment_methods = [
    'stripe' => [
        'name' => 'Stripe',
        'icon' => 'bi-stripe',
        'color' => 'text-primary',
        'path' => '/bdpay/payments/stripe.php',
    ],
    'googlepay' => [
        'name' => 'Google Pay',
        'icon' => 'bi-google',
        'color' => 'text-warning',
        'path' => '/bdpay/payments/googlepay.php',
    ],
    'visa' => [
        'name' => 'Visa',
        'icon' => 'bi-credit-card',
        'color' => 'text-info',
        'path' => '/bdpay/payments/visa.php',
    ],
    'crypto' => [
        'name' => 'Crypto',
        'icon' => 'bi-currency-bitcoin',
        'color' => 'text-warning',
        'path' => '/bdpay/payments/crypto.php',
    ],
    'bdcoin' => [
        'name' => 'BDCoin',
        'icon' => 'bi-gem',
        'color' => 'text-success',
        'path' => '/bdpay/payments/bdcoin.php',
    ],
    'square' => [
        'name' => 'Square',
        'icon' => 'bi-credit-card-2-front',
        'color' => 'text-dark',
        'path' => '/bdpay/payments/square.php',
    ],
    'bank' => [
        'name' => 'Bank Pay',
        'icon' => 'bi-bank',
        'color' => 'text-secondary',
        'path' => '/bdpay/payments/bank.php',
    ],
    'bkash' => [
        'name' => 'bKash',
        'icon' => 'bi-phone',
        'color' => 'text-danger',
        'path' => '/bdpay/payments/bkash.php',
    ],
    'nagad' => [
        'name' => 'Nagad',
        'icon' => 'bi-phone',
        'color' => 'text-warning',
        'path' => '/bdpay/payments/nagad.php',
    ],
];

$utility_links = [
    'wallets' => [
        'name' => 'Wallets',
        'icon' => 'bi-wallet2',
        'color' => 'text-success',
        'path' => '/bdpay/payments/wallets.php',
    ],
    'swpe' => [
        'name' => 'SWPE Token',
        'icon' => 'bi-lightning',
        'color' => 'text-info',
        'path' => '/bdpay/payments/wallets.php?tab=swpe',
    ],
    'exchange' => [
        'name' => 'Exchange',
        'icon' => 'bi-arrow-left-right',
        'color' => 'text-primary',
        'path' => '/bdpay/payments/wallets.php?tab=exchange',
    ],
];
