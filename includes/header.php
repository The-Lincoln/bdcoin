<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/payment_methods.php';

$admin_nav = '';
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $user_label = htmlspecialchars($_SESSION['admin_user'] ?? 'Admin');
    $avatar = '';
    if (!empty($_SESSION['oauth_avatar'])) {
        $avatar = '<img src="' . htmlspecialchars($_SESSION['oauth_avatar']) . '" class="rounded-circle me-1" style="width: 22px; height: 22px; object-fit: cover;" alt="avatar">';
    }
    $admin_nav = '<li class="nav-item"><a class="nav-link" href="/bdpay/install.php"><i class="bi bi-gear"></i> Setup</a></li>
                  <li class="nav-item"><a class="nav-link" href="/bdpay/api/"><i class="bi bi-code-slash"></i> API</a></li>
                  <li class="nav-item"><a class="nav-link" href="/bdpay/logout.php"><i class="bi bi-box-arrow-right"></i> ' . $avatar . $user_label . ' Logout</a></li>';
}

$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDPay - International Payment Gateway</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="/bdpay/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/bdpay/">
            <i class="bi bi-credit-card-2-front"></i> BDPay
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/bdpay/"><i class="bi bi-house"></i> Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-credit-card"></i> Pay With
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($payment_methods as $m): ?>
                            <li><a class="dropdown-item" href="<?= $m['path'] ?>"><i class="bi <?= $m['icon'] ?> <?= $m['color'] ?>"></i> <?= $m['name'] ?></a></li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($utility_links as $u): ?>
                            <li><a class="dropdown-item" href="<?= $u['path'] ?>"><i class="bi <?= $u['icon'] ?> <?= $u['color'] ?>"></i> <?= $u['name'] ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/bdpay/user-guide.php"><i class="bi bi-book"></i> Guide</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/bdpay/docs/"><i class="bi bi-journal-code"></i> API Docs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/bdpay/admin/"><i class="bi bi-shield-lock"></i> Admin</a>
                </li>
                <?= $admin_nav ?>
            </ul>
        </div>
    </div>
</nav>
<div class="payment-toolbar bg-white border-bottom shadow-sm">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center gap-1 py-2">
            <?php foreach ($payment_methods as $key => $m):
                $active = ($current_page === $key) ? 'btn-primary' : 'btn-outline-secondary';
            ?>
                <a href="<?= $m['path'] ?>" class="btn btn-sm <?= $active ?> d-inline-flex align-items-center gap-1">
                    <i class="bi <?= $m['icon'] ?> <?= $m['color'] ?>"></i> <?= $m['name'] ?>
                </a>
            <?php endforeach; ?>
            <span class="text-muted mx-1">|</span>
            <?php foreach ($utility_links as $u): ?>
                <a href="<?= $u['path'] ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi <?= $u['icon'] ?> <?= $u['color'] ?>"></i> <?= $u['name'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="container mt-4">
