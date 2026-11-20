<?php
/**
 * Admin: Profile & Security Settings
 * Lawanya Derma & Aesthetic Clinic
 */

$pageTitle = 'Profile & Security';
require_once __DIR__ . '/includes/header.php';

$errors = [];
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $fullName = trim($_POST['full_name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($fullName) || empty($username) || empty($email)) {
                $errors[] = 'Full name, username, and email are required.';
            } else {
                // Check if username/email already taken by someone else
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $checkStmt->execute([$username, $email, $currentUser['id']]);
                if ($checkStmt->fetch()) {
                    $errors[] = 'Username or email is already taken by another account.';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$fullName, $username, $email, $currentUser['id']]);
                    $_SESSION['admin_name'] = $fullName;
                    set_flash('success', 'Profile information updated successfully.');
                    header('Location: ' . ADMIN_URL . 'profile.php');
                    exit;
                }
            }
        } elseif ($action === 'change_password') {
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
                $errors[] = 'All password fields are required.';
            } elseif ($newPass !== $confirmPass) {
                $errors[] = 'New password and confirmation do not match.';
            } elseif (strlen($newPass) < 6) {
                $errors[] = 'New password must be at least 6 characters long.';
            } else {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$currentUser['id']]);
                $dbPass = $stmt->fetchColumn();

                if (!password_verify($currentPass, $dbPass)) {
                    $errors[] = 'Current password is incorrect.';
                } else {
                    $hashed = password_hash($newPass, PASSWORD_BCRYPT);
                    $upStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upStmt->execute([$hashed, $currentUser['id']]);
                    set_flash('success', 'Your password has been changed successfully.');
                    header('Location: ' . ADMIN_URL . 'profile.php');
                    exit;
                }
            }
        }
    }
}

// Reload current user data
$currentUser = get_current_admin();
?>

<!-- Header -->
<div class="mb-4">
    <h2 class="mb-1 fw-bold" style="color: var(--primary-dark);">Administrator Profile & Security</h2>
    <p class="text-muted mb-0">Manage account credentials, personal details, and dashboard access security.</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm mb-4">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Personal Information -->
    <div class="col-lg-6">
        <div class="card-admin mb-4">
            <div class="card-admin-header">
                <h5><i class="bi bi-person-circle text-primary me-2"></i>Account Details</h5>
            </div>
            <div class="card-admin-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="mb-3 text-center">
                        <div class="sidebar-user-avatar mx-auto mb-2" style="width: 72px; height: 72px; font-size: 1.8rem;">
                            <?= strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <span class="badge bg-light text-primary border"><?= ucfirst($currentUser['role']); ?> Role</span>
                    </div>

                    <div class="mb-3">
                        <label for="fullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullName" name="full_name" value="<?= e($currentUser['full_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= e($currentUser['username']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($currentUser['email']); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary-clinic">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Change Password -->
    <div class="col-lg-6">
        <div class="card-admin mb-4">
            <div class="card-admin-header">
                <h5><i class="bi bi-shield-lock text-danger me-2"></i>Change Password</h5>
            </div>
            <div class="card-admin-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" minlength="6" placeholder="At least 6 characters" required>
                    </div>

                    <div class="mb-4">
                        <label for="confirmPassword" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>

                    <div class="alert alert-light border small text-muted mb-4">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        Use a strong combination of uppercase, lowercase letters, numbers, and symbols for optimum security.
                    </div>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-key me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
