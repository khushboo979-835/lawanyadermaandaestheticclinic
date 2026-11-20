<?php
/**
 * Admin Authentication Guard
 * Lawanya Derma & Aesthetic Clinic
 */

require_once dirname(dirname(__DIR__)) . '/config/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Save attempted URL
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}

// Session timeout check (2 hours of inactivity)
$timeout = 7200;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    session_start();
    set_flash('warning', 'Session expired due to inactivity. Please log in again.');
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}
$_SESSION['last_activity'] = time();

// Helper to get current admin user details
function get_current_admin() {
    static $user = null;
    if ($user !== null) return $user;

    $pdo = get_db_connection();
    $userId = $_SESSION['admin_id'] ?? 0;
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, avatar FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user;
}
