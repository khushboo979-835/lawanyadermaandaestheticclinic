<?php
/**
 * Admin Login Page
 * Lawanya Derma & Aesthetic Clinic
 */

require_once dirname(__DIR__) . '/config/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ' . ADMIN_URL . 'index.php');
    exit;
}

$error = '';
$emailOrUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Security validation failed (invalid CSRF token). Please try again.';
    } else {
        $emailOrUsername = trim($_POST['login_identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($emailOrUsername) || empty($password)) {
            $error = 'Please enter your login email/username and password.';
        } else {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
            $stmt->execute([$emailOrUsername, $emailOrUsername]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Success: Regenerate session id
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');

                $redirect = $_SESSION['redirect_url'] ?? (ADMIN_URL . 'index.php');
                unset($_SESSION['redirect_url']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Invalid credentials. Please check your email and password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Lawanya Derma & Aesthetic Clinic</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL; ?>assets/logo/logo.jpeg">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= ADMIN_URL; ?>assets/css/admin.css">
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-header">
        <img src="<?= BASE_URL; ?>assets/logo/logo.jpeg" alt="Lawanya Derma Clinic" class="login-logo">
        <h4>Lawanya Derma</h4>
        <p>Clinic Administration Portal</p>
    </div>

    <div class="login-body-content">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-3 py-2 px-3" role="alert" style="font-size: 0.88rem;">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= e($error); ?></div>
            </div>
        <?php endif; ?>

        <?php $flash = get_flash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']); ?> mb-3 py-2 px-3" style="font-size: 0.88rem;">
                <?= e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

            <div class="mb-3">
                <label for="login_identity" class="form-label">Email or Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="login_identity" name="login_identity" placeholder="Enter your admin email or username" value="<?= e($emailOrUsername); ?>" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="password" class="form-label mb-0">Password</label>
                    <a href="javascript:void(0)" class="text-muted" style="font-size:0.78rem;" onclick="togglePasswordVisibility()">
                        <i class="bi bi-eye" id="toggleIcon"></i> Show
                    </a>
                </div>
                <div class="input-group mt-1">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="••••••••••••" required>
                </div>
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" checked>
                    <label class="form-check-label text-muted" for="rememberMe" style="font-size: 0.82rem;">
                        Remember this session
                    </label>
                </div>
                <span class="badge bg-light text-primary border" style="font-size:0.75rem;">
                    <i class="bi bi-shield-check me-1"></i> SSL Protected
                </span>
            </div>

            <button type="submit" class="btn btn-primary-clinic w-100 py-2 fs-6">
                <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Dashboard
            </button>
        </form>

        <div class="text-center mt-4 pt-2 border-top">
            <a href="<?= BASE_URL; ?>" class="text-muted text-decoration-none" style="font-size: 0.85rem;">
                <i class="bi bi-arrow-left me-1"></i> Back to Lawanya Clinic Website
            </a>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    if (passInput.type === 'password') {
        passInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
    } else {
        passInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
    }
}
</script>

</body>
</html>
