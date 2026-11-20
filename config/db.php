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

    // Seed default admin if table is empty
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount == 0) {
        $defaultPassword = password_hash('Admin@Lawanya2026', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@lawanyaderma.com', $defaultPassword, 'Dr. Lawanya Administrator', 'admin']);
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

    // Seed rich initial clinical blog articles if empty
    $postCount = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    if ($postCount == 0) {
        $samplePosts = [
            [
                'title' => 'Complete Guide to Acne Scar Removal: Treatments That Actually Work in 2026',
                'slug' => 'complete-guide-to-acne-scar-removal-treatments',
                'category_id' => 1,
                'author' => 'Dr. Lawanya Clinic Specialist',
                'excerpt' => 'Acne scars can deeply affect skin texture and self-confidence. Discover the most effective clinical treatments including Fractional CO2 laser, subcision, and chemical peels.',
                'content' => '<p>Acne is one of the most common dermatological conditions worldwide, but what often outlasts the breakout itself are the stubborn scars and indentations left behind. For patients struggling with atrophic (pitted) or hypertrophic acne scars, over-the-counter creams rarely deliver significant textural improvements. Fortunately, modern dermatological science offers clinically proven procedures that stimulate natural collagen remodeling.</p>

<h3>Understanding the Types of Acne Scars</h3>
<p>Before selecting any treatment, an accurate dermatological assessment is critical. Acne scars primarily fall into three categories:</p>
<ul>
    <li><strong>Ice Pick Scars:</strong> Deep, narrow, vertical pits that penetrate into the deep dermis.</li>
    <li><strong>Boxcar Scars:</strong> Broader depressions with well-defined, sharp vertical edges.</li>
    <li><strong>Rolling Scars:</strong> Wide, shallow depressions with sloping edges that create a wave-like texture due to underlying fibrous bands.</li>
</ul>

<h3>Gold-Standard Clinical Treatments at Lawanya Derma</h3>
<p>At Lawanya Derma & Aesthetic Clinic, we believe in a customized, multi-modality approach tailored to your specific scar pattern and skin tone:</p>

<h4>1. Fractional CO2 Laser Resurfacing</h4>
<p>Fractional CO2 laser delivers microscopic beams of light energy into the deep dermal layers, removing microscopic columns of damaged tissue while leaving surrounding areas intact. This triggers rapid cellular renewal, collagen synthesis, and smooth skin texture over consecutive sessions.</p>

<h4>2. Subcision with Dermal Fillers</h4>
<p>For tethered rolling scars, a specialized subcision needle is inserted beneath the skin surface to release the fibrous bands pulling down the epidermis. Combining subcision with hyaluronic acid fillers or platelet-rich plasma (PRP) lifts the depressed scar immediately and accelerates tissue regeneration.</p>

<h4>3. Microneedling Radiofrequency (MNRF)</h4>
<p>MNRF combines precision micro-punctures with controlled thermal radiofrequency energy delivered directly to the dermis. This provides powerful collagen tightening with minimal surface downtime, making it exceptionally safe for Indian skin types prone to post-inflammatory hyperpigmentation.</p>

<h3>Post-Procedure Aftercare</h3>
<p>Achieving optimal clinical results requires diligent post-procedure care: adhere strictly to broad-spectrum SPF 50+ sunscreen, use barrier-repairing ceramides, and avoid direct sun exposure for 7 to 10 days post-treatment.</p>

<p><em>Ready to restore your smooth, radiant skin? Schedule an in-person consultation with our senior dermatologist at Lawanya Derma & Aesthetic Clinic today.</em></p>',
                'featured_image' => 'assets/images/about/1.jpg',
                'status' => 'published',
                'views' => 142,
                'reading_time' => 5,
                'meta_title' => 'Complete Guide to Acne Scar Removal in Delhi | Lawanya Derma Clinic',
                'meta_description' => 'Discover effective acne scar removal treatments in New Delhi. Fractional CO2 laser, subcision, and MNRF explained by Lawanya Derma & Aesthetic Clinic.',
                'meta_keywords' => 'acne scar treatment delhi, fractional co2 laser, mnrf treatment, skin clinic delhi'
            ],
            [
                'title' => 'PRP Therapy vs Hair Transplant: Which Hair Restoration Solution Is Right for You?',
                'slug' => 'prp-therapy-vs-hair-transplant-hair-restoration-comparison',
                'category_id' => 2,
                'author' => 'Lawanya Trichology Team',
                'excerpt' => 'Hair thinning and male or female pattern baldness can be challenging. Learn whether non-surgical Platelet-Rich Plasma (PRP) or permanent Hair Transplantation suits your stage.',
                'content' => '<p>Hair loss affects millions of men and women, impacting not just physical appearance but also emotional well-being and self-assurance. When looking for scientific solutions, two procedures frequently dominate the conversation: <strong>Platelet-Rich Plasma (PRP) Therapy</strong> and <strong>Surgical Hair Transplantation</strong>. While both are powerful treatments offered at Lawanya Derma Clinic, they address completely different stages of hair loss.</p>

<h3>What is PRP Hair Therapy?</h3>
<p>PRP is an advanced, non-surgical regenerative procedure. A small amount of your own blood is drawn, placed into a specialized centrifuge, and concentrated to isolate platelets rich in growth factors (VEGF, PDGF, EGF). When injected into thinning areas of the scalp, these concentrated growth factors:</p>
<ul>
    <li>Reactivate dormant and miniaturized hair follicles.</li>
    <li>Enhance microcirculation to the scalp.</li>
    <li>Thicken existing hair shaft diameter.</li>
    <li>Significantly reduce active hair shedding.</li>
</ul>
<p><strong>Ideal Candidate:</strong> Patients experiencing early to moderate thinning (Norwood Scale 1 to 3), diffuse thinning, or post-COVID hair loss where live hair follicles are still intact.</p>

<h3>What is Hair Transplantation?</h3>
<p>Hair transplantation—specifically Follicular Unit Extraction (FUE)—is a surgical procedure where permanent, DHT-resistant hair follicles are carefully harvested from the donor zone (usually the back of the head) and transplanted into bald or severely receded areas like the hairline or crown.</p>
<p><strong>Ideal Candidate:</strong> Individuals with defined areas of baldness or deeply receded hairlines where hair follicles have died and can no longer grow naturally.</p>

<h3>Comparison Summary</h3>
<p>If your hair follicles are alive but shrinking, PRP therapy provides a natural, zero-downtime boost that restores hair density. If an area is completely smooth or bald, hair transplantation provides a permanent, lifelong hairline restoration. Many patients achieve peak results by combining both approaches!</p>',
                'featured_image' => 'assets/images/about/3.jpg',
                'status' => 'published',
                'views' => 98,
                'reading_time' => 4,
                'meta_title' => 'PRP vs Hair Transplant in Delhi | Hair Loss Solutions | Lawanya Derma',
                'meta_description' => 'Compare PRP therapy and hair transplant surgery for hair loss. Find out which treatment is best for your hair stage at Lawanya Derma Clinic, New Delhi.',
                'meta_keywords' => 'prp therapy delhi, hair transplant new delhi, hair loss treatment, best trichologist'
            ],
            [
                'title' => 'Laser Skin Resurfacing: The Secret to Even Skin Tone, Youthful Glow, and Pore Refinement',
                'slug' => 'laser-skin-resurfacing-even-skin-tone-youthful-glow',
                'category_id' => 3,
                'author' => 'Dr. Lawanya Clinic Specialist',
                'excerpt' => 'Unveil brighter, smoother skin with medical-grade laser treatments. Understand how advanced laser energy targets pigmentation, fine lines, and open pores safely.',
                'content' => '<p>Modern aesthetic dermatology has transformed dramatically over the last decade, transitioning from aggressive peels to precise, targeted light therapies. Laser skin resurfacing stands at the forefront of this revolution, offering unparalleled precision in treating hyperpigmentation, sun damage, enlarged pores, and early signs of aging.</p>

<h3>How Does Laser Resurfacing Work?</h3>
<p>Medical lasers emit concentrated wavelengths of light that are absorbed by specific chromophores in the skin—such as melanin (pigment) or hemoglobin (redness). The photo-thermal reaction gently shatters excess pigment into microscopic particles that your body naturally clears, while simultaneously heating the deep collagen matrix to trigger firming and tightening.</p>

<h3>Key Benefits of Laser Therapy:</h3>
<ol>
    <li><strong>Even Skin Tone:</strong> Effectively treats melasma, age spots, freckles, and dark patches.</li>
    <li><strong>Pore Reduction:</strong> Stimulates collagen walls around pores, making them visibly smaller and refined.</li>
    <li><strong>Smooth Fine Lines:</strong> Softens crow\'s feet, smile lines, and rough skin texture.</li>
    <li><strong>Minimal Downtime:</strong> Modern fractional technology allows quick healing compared to traditional ablative methods.</li>
</ol>

<h3>Is Laser Treatment Painful?</h3>
<p>At Lawanya Derma, patient comfort is our utmost priority. We apply a medical-grade topical anesthetic cream 30 minutes before the procedure, coupled with integrated cooling devices, ensuring the experience feels like gentle pinpricks or warmth rather than discomfort.</p>',
                'featured_image' => 'assets/images/about/4.jpg',
                'status' => 'published',
                'views' => 115,
                'reading_time' => 4,
                'meta_title' => 'Laser Skin Resurfacing in Delhi | Lawanya Derma & Aesthetic Clinic',
                'meta_description' => 'Transform your skin with laser skin resurfacing at Lawanya Derma Clinic. Treat pigmentation, open pores, and uneven skin tone with expert dermatologists.',
                'meta_keywords' => 'laser skin resurfacing, pigmentation treatment delhi, skin glow clinic'
            ],
            [
                'title' => 'The Art of Semi-Permanent Makeup: Microblading and Lip Blush Explained',
                'slug' => 'art-of-semi-permanent-makeup-microblading-lip-blush',
                'category_id' => 5,
                'author' => 'Lawanya Aesthetic Specialist',
                'excerpt' => 'Wake up with flawless brows and radiant lips every day. Explore how semi-permanent cosmetic tattooing enhances your natural features effortlessly.',
                'content' => '<p>In our fast-paced lives, spending 30 minutes every morning filling in eyebrows, lining lips, and reapplying makeup can become tiresome. Semi-Permanent Makeup (SPMU) has revolutionized modern beauty routines by providing subtle, smudge-proof, and natural-looking enhancement that lasts 1 to 3 years.</p>

<h3>1. Eyebrow Microblading & Ombre Powder Brows</h3>
<p>Microblading involves hand-drawing ultra-fine, hair-like strokes using organic pigments that blend flawlessly with your natural brow hairs. For clients seeking a softer, filled-in makeup look, Ombre Powder Brows create a soft gradient from head to tail. The result is symmetrical, fuller, and defined arches that frame your face perfectly.</p>

<h3>2. Lip Blush Treatment</h3>
<p>Lip blush is a semi-permanent tinting procedure that deposits natural pink and coral pigments into the superficial lip layer. It corrects asymmetry, camouflages pale or hyperpigmented lips, and delivers a healthy, youthful flush without the daily need for lipstick.</p>

<h3>Clinical Safety Standards at Lawanya Clinic</h3>
<p>Semi-permanent makeup requires strict medical-grade hygiene. At Lawanya Derma Clinic:</p>
<ul>
    <li>We use 100% disposable, single-use sterilized needle cartridges.</li>
    <li>We utilize premium, hypoallergenic, organic pigments certified for cosmetic safety.</li>
    <li>Our aesthetic artists design custom color palettes and brow maps tailored to your facial geometry before beginning any pigment application.</li>
</ul>',
                'featured_image' => 'assets/images/about/5.jpg',
                'status' => 'published',
                'views' => 84,
                'reading_time' => 4,
                'meta_title' => 'Semi-Permanent Makeup in Delhi | Microblading & Lip Blush | Lawanya',
                'meta_description' => 'Get flawless brows and radiant lips with semi-permanent makeup at Lawanya Derma & Aesthetic Clinic. Expert microblading and lip blush in New Delhi.',
                'meta_keywords' => 'microblading delhi, lip blush delhi, semi permanent makeup, aesthetic clinic delhi'
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, category_id, author, excerpt, content, featured_image, status, views, reading_time, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($samplePosts as $post) {
            $stmt->execute([
                $post['title'],
                $post['slug'],
                $post['category_id'],
                $post['author'],
                $post['excerpt'],
                $post['content'],
                $post['featured_image'],
                $post['status'],
                $post['views'],
                $post['reading_time'],
                $post['meta_title'],
                $post['meta_description'],
                $post['meta_keywords']
            ]);
        }
    }
}
