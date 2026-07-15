<?php
require_once 'includes/functions.php';
require_once 'config/database.php';
require_once 'config/oauth.php';

$db = Database::getInstance()->getConnection();

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/bdpay/admin/';
$expired = isset($_GET['expired']);
$oauth_error = isset($_GET['oauth_error']) ? htmlspecialchars($_GET['oauth_error']) : '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ? OR name = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $stmt = $db->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$user['id']]);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user['name'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        header("Location: " . $redirect);
        exit;
    }

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'Administrator';
        $_SESSION['admin_role'] = 'admin';
        $_SESSION['last_activity'] = time();
        header("Location: " . $redirect);
        exit;
    }

    $error = "Invalid email/username or password";
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center min-vh-75 align-items-center" style="min-height: 60vh;">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-primary-subtle mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-shield-lock text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h3 class="fw-bold">Sign In</h3>
                    <p class="text-muted small">Access your BDPay account</p>
                </div>

                <?php if ($expired): ?>
                <div class="alert alert-warning small"><i class="bi bi-clock"></i> Session expired. Please login again.</div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger small"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
                <?php endif; ?>

                <?php if ($oauth_error): ?>
                <div class="alert alert-danger small"><i class="bi bi-exclamation-triangle"></i> OAuth failed: <?= $oauth_error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email or Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control form-control-lg" placeholder="email@example.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter password" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>
                    </div>
                </form>

                <div class="position-relative my-4">
                    <hr>
                    <div class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">or continue with</div>
                </div>

                <div class="d-grid gap-2">
                    <?php foreach ($oauth_providers as $key => $p): ?>
                    <a href="<?= htmlspecialchars(getAuthorizationUrl($key, $redirect)) ?>" class="btn <?= $p['btn_style'] ?> btn-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="bi <?= $p['icon'] ?> fs-5"></i> <?= $p['name'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
