<?php
/**
 * Admin: Clinic & Website Settings
 * Lawanya Derma & Aesthetic Clinic
 */

$pageTitle = 'Clinic Settings';
require_once __DIR__ . '/includes/header.php';

// Handle database export/backup request
if (isset($_GET['action']) && $_GET['action'] === 'backup_db') {
    if (DB_TYPE === 'sqlite' && file_exists(SQLITE_FILE)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="lawanya_backup_' . date('Y-m-d_His') . '.db"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize(SQLITE_FILE));
        readfile(SQLITE_FILE);
        exit;
    } else {
        set_flash('info', 'Database backup file is located at database/schema.sql');
        header('Location: ' . ADMIN_URL . 'settings.php');
        exit;
    }
}

// Default settings map
$defaults = [
    'clinic_phone' => CLINIC_PHONE,
    'clinic_email' => CLINIC_EMAIL,
    'clinic_address' => CLINIC_ADDRESS,
    'working_hours' => 'Mon-Sat: 9:00 AM - 8:00 PM | Sun: 10:00 AM - 6:00 PM',
    'facebook_url' => 'https://www.facebook.com/photo/?fbid=122100123681002381&set=a.122100110643002381',
    'instagram_url' => 'https://www.instagram.com/lawanya_derma/?igsh=MTVmbzUweTZnNWp2Mg%3D%3D&utm_source=qr#',
    'whatsapp_number' => '917042179159',
    'site_title' => 'Lawanya Derma & Aesthetic Clinic - Best Skin, Hair & Laser Treatment in Delhi',
    'site_description' => 'Leading dermatology and aesthetic clinic offering advanced skin treatments, hair restoration, laser procedures, weight loss programs and OPD services.'
];

// Fetch stored settings
$stored = [];
try {
    $rows = $pdo->query("SELECT key_name, key_value FROM settings")->fetchAll();
    foreach ($rows as $row) {
        $stored[$row['key_name']] = $row['key_value'];
    }
} catch (Exception $e) {}

$settings = array_merge($defaults, $stored);

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security token invalid.');
    } else {
        foreach ($defaults as $k => $def) {
            $val = trim($_POST[$k] ?? '');
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP) ON CONFLICT(key_name) DO UPDATE SET key_value = excluded.key_value, updated_at = CURRENT_TIMESTAMP");
            // Fallback for MySQL if running MySQL
            if (DB_TYPE !== 'sqlite') {
                $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            }
            $stmt->execute([$k, $val]);
            $settings[$k] = $val;
        }
        set_flash('success', 'Clinic settings saved successfully.');
        header('Location: ' . ADMIN_URL . 'settings.php');
        exit;
    }
}
?>

<div class="mb-4">
    <h2 class="mb-1 fw-bold" style="color: var(--primary-dark);">Clinic & System Settings</h2>
    <p class="text-muted mb-0">Configure contact coordinates, social channels, and site information.</p>
</div>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

    <div class="row g-4">
        <!-- Clinic Contact Coordinates -->
        <div class="col-lg-7">
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-hospital text-primary me-2"></i>Clinic Contact Details</h5>
                </div>
                <div class="card-admin-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="clinic_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="clinic_phone" name="clinic_phone" value="<?= e($settings['clinic_phone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number (with country code)</label>
                            <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" value="<?= e($settings['whatsapp_number']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="clinic_email" class="form-label">Clinic Contact Email</label>
                        <input type="email" class="form-control" id="clinic_email" name="clinic_email" value="<?= e($settings['clinic_email']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="clinic_address" class="form-label">Physical Clinic Address</label>
                        <textarea class="form-control" id="clinic_address" name="clinic_address" rows="2"><?= e($settings['clinic_address']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="working_hours" class="form-label">Working Hours</label>
                        <input type="text" class="form-control" id="working_hours" name="working_hours" value="<?= e($settings['working_hours']); ?>">
                    </div>
                </div>
            </div>

            <!-- Social Media Handles -->
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-share text-teal me-2"></i>Social Media Links</h5>
                </div>
                <div class="card-admin-body">
                    <div class="mb-3">
                        <label for="facebook_url" class="form-label"><i class="bi bi-facebook text-primary me-1"></i> Facebook Page URL</label>
                        <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="<?= e($settings['facebook_url']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="instagram_url" class="form-label"><i class="bi bi-instagram text-danger me-1"></i> Instagram Profile URL</label>
                        <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="<?= e($settings['instagram_url']); ?>">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-clinic py-2 px-4">
                <i class="bi bi-check2-circle me-1"></i> Save All Settings
            </button>
        </div>

        <!-- System & Backup Column -->
        <div class="col-lg-5">
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-database text-warning me-2"></i>Data & Backup</h5>
                </div>
                <div class="card-admin-body">
                    <p class="text-muted small mb-3">
                        Regularly back up your clinic database containing all blog articles, categories, and patient consultation leads.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="<?= ADMIN_URL; ?>settings.php?action=backup_db" class="btn btn-outline-primary text-start">
                            <i class="bi bi-download me-2"></i> Download Database Backup
                        </a>
                    </div>
                    <div class="mt-3 pt-3 border-top small text-muted">
                        <div><strong>Engine:</strong> <?= strtoupper(DB_TYPE); ?></div>
                        <div><strong>Status:</strong> Active & Connected</div>
                    </div>
                </div>
            </div>

            <div class="card-admin">
                <div class="card-admin-header">
                    <h5><i class="bi bi-info-circle text-info me-2"></i>About Clinic Software</h5>
                </div>
                <div class="card-admin-body">
                    <p class="small text-muted mb-2">
                        <strong>Lawanya Derma & Aesthetic Clinic</strong> Admin Portal v2.0
                    </p>
                    <p class="small text-muted mb-0">
                        Engineered with PHP 8, PDO, Bootstrap 5, responsive modern SaaS architecture, and SEO structured data.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
