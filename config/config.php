<?php
/**
 * Lawanya Derma & Aesthetic Clinic
 * Global Configuration & Helper Utilities
 */

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'domain' => $cookieParams['domain'],
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// App details
define('APP_NAME', 'Lawanya Derma & Aesthetic Clinic');
define('APP_TAGLINE', 'Delhi\'s Premier Aesthetic Destination');
define('CLINIC_PHONE', '+91 70421 79159');
define('CLINIC_EMAIL', 'lawanyaderma@gmail.com');
define('CLINIC_ADDRESS', '328/1 Part-A, Pul Pahladpur, Suraj Kund Road, Near MCD Toll Tax, New Delhi - 110044');

// Project root directory
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'blogs' . DIRECTORY_SEPARATOR);

// Dynamic Base URL detection
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Auto-detect subfolder if running in subfolder
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // If inside /admin or /api, strip it
    $subDir = preg_replace('/(\/admin|\/api).*$/', '', $scriptDir);
    $subDir = rtrim($subDir, '/');
    
    define('BASE_URL', $protocol . $host . $subDir . '/');
    define('ADMIN_URL', BASE_URL . 'admin/');
    define('UPLOAD_URL', BASE_URL . 'uploads/blogs/');
}

// Database configuration
// Smart detection: Local/CLI uses SQLite, Hostinger Live uses MySQL
$isCli = (php_sapi_name() === 'cli');
$isLocalHost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost:') === 0;
$isLocalServer = $isCli || $isLocalHost;

if ($isLocalServer) {
    define('DB_TYPE', 'sqlite');
} else {
    define('DB_TYPE', 'mysql');
}

// ========================================================
// Hostinger MySQL Settings (Pre-configured for Hostinger)
// ========================================================
define('DB_HOST', 'localhost');              // Hostinger par hamesha 'localhost' hi rehta hai
define('DB_NAME', 'u467991428_lawanya');     // Hostinger Database Name
define('DB_USER', 'u467991428_lawanya');     // Hostinger Database Username
define('DB_PASS', 'O*k3gybl');               // Hostinger Password from modal
define('DB_PORT', '3306');

// SQLite Settings (Used automatically when on localhost)
define('SQLITE_FILE', ROOT_PATH . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'lawanya.db');

/**
 * CSRF Token Helpers
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash message helpers
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * String & slug helper
 */
function slugify($text) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // trim
    $text = trim($text, '-');
    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // lowercase
    $text = strtolower($text);

    return empty($text) ? 'n-a-' . time() : $text;
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Estimate reading time in minutes
 */
function calculate_reading_time($content) {
    $wordCount = str_word_count(strip_tags($content));
    $wordsPerMinute = 200;
    $minutes = ceil($wordCount / $wordsPerMinute);
    return max(1, $minutes);
}

/**
 * Human-readable relative time
 */
function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}
