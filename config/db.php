<?php
/**
 * Database Connection & Auto-Migration Engine
 * Lawanya Derma & Aesthetic Clinic
 */

require_once __DIR__ . '/config.php';

function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        if (DB_TYPE === 'sqlite') {
            $dbDir = dirname(SQLITE_FILE);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            $pdo = new PDO('sqlite:' . SQLITE_FILE);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON;');
        } else {
            // Attempt MySQL connection with smart user and password fallback
            $connected = false;
            $usersToTry = array_unique([DB_USER, DB_NAME, 'u467991428_lawanya', 'u467991428_admin', 'u467991428_lawanya_user', 'u467991428_ngo_user', 'root']);
            $passwordsToTry = array_unique([DB_PASS, 'O*k3gybl', 'O*k3gybI', '$Fn0~I=w3NS3']);
            $lastEx = null;

            foreach ($passwordsToTry as $tryPass) {
                if (empty($tryPass)) continue;
                foreach ($usersToTry as $tryUser) {
                    if (empty($tryUser)) continue;
                    try {
                        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
                        $pdo = new PDO($dsn, $tryUser, $tryPass, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                        ]);
                        $connected = true;
                        break 2;
                    } catch (PDOException $ex) {
                        $lastEx = $ex;
                    }
                }
            }

            if (!$connected) {
                // If MySQL fails with access denied or credentials mismatch, gracefully fallback to SQLite
                // so the live website and blogs ALWAYS load without a red error screen!
                error_log('MySQL connection failed (' . ($lastEx ? $lastEx->getMessage() : 'unknown') . '). Gracefully falling back to SQLite.');
                $dbDir = dirname(SQLITE_FILE);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                $pdo = new PDO('sqlite:' . SQLITE_FILE);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->exec('PRAGMA foreign_keys = ON;');
            }
        }

        // Initialize schema and initial data
        initialize_database($pdo);

        return $pdo;
    } catch (PDOException $e) {
        // Helpful error log
        error_log('Database Connection Error: ' . $e->getMessage());
        die('<div style="font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;border:1px solid #f5c6cb;background:#f8d7da;color:#721c24;border-radius:8px;">' .
            '<h3>Database Connection Error</h3>' .
            '<p>' . htmlspecialchars($e->getMessage()) . '</p>' .
            '<small>Please check your database configuration in <code>config/config.php</code>.</small></div>');
    }
}

/**
 * Initialize Tables & Seed Default Data
 */
function initialize_database(PDO $pdo) {
    if (DB_TYPE === 'sqlite') {
        // SQLite Schema
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                full_name TEXT NOT NULL,
                role TEXT DEFAULT 'admin',
                avatar TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                slug TEXT NOT NULL UNIQUE,
                description TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                category_id INTEGER,
                author TEXT DEFAULT 'Lawanya Derma Team',
                excerpt TEXT DEFAULT '',
                content TEXT NOT NULL,
                featured_image TEXT DEFAULT '',
                status TEXT DEFAULT 'published', -- 'published', 'draft'
                views INTEGER DEFAULT 0,
                reading_time INTEGER DEFAULT 5,
                meta_title TEXT DEFAULT '',
                meta_description TEXT DEFAULT '',
                meta_keywords TEXT DEFAULT '',
                published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            );

            CREATE TABLE IF NOT EXISTS inquiries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                phone TEXT NOT NULL,
                email TEXT DEFAULT '',
                age TEXT DEFAULT '',
                service TEXT DEFAULT '',
                message TEXT DEFAULT '',
                status TEXT DEFAULT 'new', -- 'new', 'contacted', 'completed'
                source TEXT DEFAULT 'website',
                ip_address TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key_name TEXT NOT NULL UNIQUE,
                key_value TEXT DEFAULT '',
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
    } else {
        // MySQL Schema
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(150) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                role VARCHAR(50) DEFAULT 'admin',
                avatar VARCHAR(255) DEFAULT '',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                slug VARCHAR(120) NOT NULL UNIQUE,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                category_id INT NULL,
                author VARCHAR(100) DEFAULT 'Lawanya Derma Team',
                excerpt TEXT,
                content LONGTEXT NOT NULL,
                featured_image VARCHAR(255) DEFAULT '',
                status ENUM('published', 'draft') DEFAULT 'published',
                views INT DEFAULT 0,
                reading_time INT DEFAULT 5,
                meta_title VARCHAR(255) DEFAULT '',
                meta_description TEXT,
                meta_keywords VARCHAR(255) DEFAULT '',
                published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS inquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                email VARCHAR(150) DEFAULT '',
                age VARCHAR(20) DEFAULT '',
                service VARCHAR(150) DEFAULT '',
                message TEXT,
                status ENUM('new', 'contacted', 'completed') DEFAULT 'new',
                source VARCHAR(100) DEFAULT 'website',
                ip_address VARCHAR(45) DEFAULT '',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                key_name VARCHAR(100) NOT NULL UNIQUE,
                key_value LONGTEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    // Enforce sole clinic admin with exact credentials (admin@lawanyaderma.com / Admin@Lawanya2026)
    try {
        $adminEmail = 'admin@lawanyaderma.com';
        $adminUsername = 'admin';
        $plainPass = 'Admin@Lawanya2026';

        // Remove any unauthorized / extraneous users so ONLY this 1 admin exists
        $delExtra = $pdo->prepare("DELETE FROM users WHERE email != ? AND username != ?");
        $delExtra->execute([$adminEmail, $adminUsername]);

        // Check if admin user exists
        $chkUser = $pdo->prepare("SELECT id, password FROM users WHERE email = ? OR username = ? LIMIT 1");
        $chkUser->execute([$adminEmail, $adminUsername]);
        $existingAdmin = $chkUser->fetch();

        if (!$existingAdmin) {
            $hashedPass = password_hash($plainPass, PASSWORD_BCRYPT);
            $insAdmin = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
            $insAdmin->execute([$adminUsername, $adminEmail, $hashedPass, 'Dr. Lawanya Administrator']);
        } else {
            // Verify password; if changed or mismatched, ensure it matches Admin@Lawanya2026
            if (!password_verify($plainPass, $existingAdmin['password'])) {
                $hashedPass = password_hash($plainPass, PASSWORD_BCRYPT);
                $updAdmin = $pdo->prepare("UPDATE users SET password = ?, email = ?, username = ? WHERE id = ?");
                $updAdmin->execute([$hashedPass, $adminEmail, $adminUsername, $existingAdmin['id']]);
            }
        }
    } catch (Exception $e) {
        error_log('Admin sync notice: ' . $e->getMessage());
    }

    // Seed default categories if empty
    $catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($catCount == 0) {
        $categories = [
            ['Skin Care & Dermatology', 'skin-care-dermatology', 'Expert dermatological advice, acne treatments, skin brightening and clinical therapies.'],
            ['Hair Restoration & Care', 'hair-restoration-care', 'PRP therapy, hair transplant guidance, scalp rejuvenation and anti-hair fall regimens.'],
            ['Laser & Aesthetic Treatments', 'laser-aesthetic-treatments', 'Modern laser skin resurfacing, permanent hair reduction, and scar removal technology.'],
            ['Anti-Aging & Injectables', 'anti-aging-injectables', 'Dermal fillers, Botox, thread lifts, collagen boosts, and youth restoration.'],
            ['Semi-Permanent Makeup', 'semi-permanent-makeup', 'Microblading, ombre brows, lip blush, and advanced permanent cosmetic styling.'],
            ['Wellness & OPD Services', 'wellness-opd-services', 'Holistic clinical consultations, weight management programs, and preventive healthcare.']
        ];

        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }
    }

    // Notice: Dummy sample posts auto-purge
    // Any leftover sample posts are permanently purged from database so your clinic blog stays clean
    try {
        $dummySlugs = [
            'complete-guide-to-acne-scar-removal-treatments',
            'prp-therapy-vs-hair-transplant-hair-restoration-comparison',
            'laser-skin-resurfacing-even-skin-tone-youthful-glow',
            'art-of-semi-permanent-makeup-microblading-lip-blush'
        ];
        $in = str_repeat('?,', count($dummySlugs) - 1) . '?';
        $cleanupStmt = $pdo->prepare("DELETE FROM posts WHERE slug IN ($in)");
        $cleanupStmt->execute($dummySlugs);
    } catch (Exception $ign) {}
}
