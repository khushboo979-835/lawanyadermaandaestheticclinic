-- ========================================================
-- Lawanya Derma & Aesthetic Clinic
-- MySQL Database Export for Hostinger phpMyAdmin
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `avatar` varchar(255) DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Account (Email: admin@lawanyaderma.com | Password: Admin@Lawanya2026)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`) VALUES
(1, 'admin', 'admin@lawanyaderma.com', '$2y$10$X9m7RW.PULTefXCeH0EvbOmC0FadNYD699FeLGGuyLyVUjAElq2lW', 'Dr. Lawanya Administrator', 'admin');

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Skin Care & Dermatology', 'skin-care-dermatology', 'Expert dermatological advice, acne treatments, skin brightening and clinical therapies.'),
(2, 'Hair Restoration & Care', 'hair-restoration-care', 'PRP therapy, hair transplant guidance, scalp rejuvenation and anti-hair fall regimens.'),
(3, 'Laser & Aesthetic Treatments', 'laser-aesthetic-treatments', 'Modern laser skin resurfacing, permanent hair reduction, and scar removal technology.'),
(4, 'Anti-Aging & Injectables', 'anti-aging-injectables', 'Dermal fillers, Botox, thread lifts, collagen boosts, and youth restoration.'),
(5, 'Semi-Permanent Makeup', 'semi-permanent-makeup', 'Microblading, ombre brows, lip blush, and advanced permanent cosmetic styling.'),
(6, 'Wellness & OPD Services', 'wellness-opd-services', 'Holistic clinical consultations, weight management programs, and preventive healthcare.');

-- --------------------------------------------------------
-- Table structure for table `posts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author` varchar(100) DEFAULT 'Dr. Lawanya Clinic Team',
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT '',
  `status` enum('published','draft') DEFAULT 'published',
  `views` int(11) DEFAULT 0,
  `reading_time` int(11) DEFAULT 5,
  `meta_title` varchar(255) DEFAULT '',
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT '',
  `published_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `posts_fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `posts` (`id`, `title`, `slug`, `category_id`, `author`, `excerpt`, `content`, `featured_image`, `status`, `views`, `reading_time`, `meta_title`, `meta_description`, `meta_keywords`) VALUES
(1, 'Complete Guide to Acne Scar Removal: Treatments That Actually Work in 2026', 'complete-guide-to-acne-scar-removal-treatments', 1, 'Dr. Lawanya Clinic Specialist', 'Acne scars can deeply affect skin texture and self-confidence. Discover the most effective clinical treatments including Fractional CO2 laser, subcision, and chemical peels.', '<p>Acne is one of the most common dermatological conditions worldwide, but what often outlasts the breakout itself are the stubborn scars and indentations left behind. For patients struggling with atrophic (pitted) or hypertrophic acne scars, over-the-counter creams rarely deliver significant textural improvements. Fortunately, modern dermatological science offers clinically proven procedures that stimulate natural collagen remodeling.</p><h3>Understanding the Types of Acne Scars</h3><p>Before selecting any treatment, an accurate dermatological assessment is critical. Acne scars primarily fall into three categories:</p><ul><li><strong>Ice Pick Scars:</strong> Deep, narrow, vertical pits that penetrate into the deep dermis.</li><li><strong>Boxcar Scars:</strong> Broader depressions with well-defined, sharp vertical edges.</li><li><strong>Rolling Scars:</strong> Wide, shallow depressions with sloping edges that create a wave-like texture due to underlying fibrous bands.</li></ul><h3>Gold-Standard Clinical Treatments at Lawanya Derma</h3><p>At Lawanya Derma & Aesthetic Clinic, we believe in a customized, multi-modality approach tailored to your specific scar pattern and skin tone:</p><h4>1. Fractional CO2 Laser Resurfacing</h4><p>Fractional CO2 laser delivers microscopic beams of light energy into the deep dermal layers, removing microscopic columns of damaged tissue while leaving surrounding areas intact. This triggers rapid cellular renewal, collagen synthesis, and smooth skin texture over consecutive sessions.</p><h4>2. Subcision with Dermal Fillers</h4><p>For tethered rolling scars, a specialized subcision needle is inserted beneath the skin surface to release the fibrous bands pulling down the epidermis. Combining subcision with hyaluronic acid fillers or platelet-rich plasma (PRP) lifts the depressed scar immediately and accelerates tissue regeneration.</p><h4>3. Microneedling Radiofrequency (MNRF)</h4><p>MNRF combines precision micro-punctures with controlled thermal radiofrequency energy delivered directly to the dermis. This provides powerful collagen tightening with minimal surface downtime, making it exceptionally safe for Indian skin types prone to post-inflammatory hyperpigmentation.</p><h3>Post-Procedure Aftercare</h3><p>Achieving optimal clinical results requires diligent post-procedure care: adhere strictly to broad-spectrum SPF 50+ sunscreen, use barrier-repairing ceramides, and avoid direct sun exposure for 7 to 10 days post-treatment.</p><p><em>Ready to restore your smooth, radiant skin? Schedule an in-person consultation with our senior dermatologist at Lawanya Derma & Aesthetic Clinic today.</em></p>', 'assets/images/about/1.jpg', 'published', 142, 5, 'Complete Guide to Acne Scar Removal in Delhi | Lawanya Derma Clinic', 'Discover effective acne scar removal treatments in New Delhi. Fractional CO2 laser, subcision, and MNRF explained by Lawanya Derma & Aesthetic Clinic.', 'acne scar treatment delhi, fractional co2 laser, mnrf treatment, skin clinic delhi'),
(2, 'PRP Therapy vs Hair Transplant: Which Hair Restoration Solution Is Right for You?', 'prp-therapy-vs-hair-transplant-hair-restoration-comparison', 2, 'Lawanya Trichology Team', 'Hair thinning and male or female pattern baldness can be challenging. Learn whether non-surgical Platelet-Rich Plasma (PRP) or permanent Hair Transplantation suits your stage.', '<p>Hair loss affects millions of men and women, impacting not just physical appearance but also emotional well-being and self-assurance. When looking for scientific solutions, two procedures frequently dominate the conversation: <strong>Platelet-Rich Plasma (PRP) Therapy</strong> and <strong>Surgical Hair Transplantation</strong>. While both are powerful treatments offered at Lawanya Derma Clinic, they address completely different stages of hair loss.</p><h3>What is PRP Hair Therapy?</h3><p>PRP is an advanced, non-surgical regenerative procedure. A small amount of your own blood is drawn, placed into a specialized centrifuge, and concentrated to isolate platelets rich in growth factors (VEGF, PDGF, EGF). When injected into thinning areas of the scalp, these concentrated growth factors:</p><ul><li>Reactivate dormant and miniaturized hair follicles.</li><li>Enhance microcirculation to the scalp.</li><li>Thicken existing hair shaft diameter.</li><li>Significantly reduce active hair shedding.</li></ul><p><strong>Ideal Candidate:</strong> Patients experiencing early to moderate thinning (Norwood Scale 1 to 3), diffuse thinning, or post-COVID hair loss where live hair follicles are still intact.</p><h3>What is Hair Transplantation?</h3><p>Hair transplantation—specifically Follicular Unit Extraction (FUE)—is a surgical procedure where permanent, DHT-resistant hair follicles are carefully harvested from the donor zone (usually the back of the head) and transplanted into bald or severely receded areas like the hairline or crown.</p><p><strong>Ideal Candidate:</strong> Individuals with defined areas of baldness or deeply receded hairlines where hair follicles have died and can no longer grow naturally.</p><h3>Comparison Summary</h3><p>If your hair follicles are alive but shrinking, PRP therapy provides a natural, zero-downtime boost that restores hair density. If an area is completely smooth or bald, hair transplantation provides a permanent, lifelong hairline restoration. Many patients achieve peak results by combining both approaches!</p>', 'assets/images/about/3.jpg', 'published', 98, 4, 'PRP vs Hair Transplant in Delhi | Hair Loss Solutions | Lawanya Derma', 'Compare PRP therapy and hair transplant surgery for hair loss. Find out which treatment is best for your hair stage at Lawanya Derma Clinic, New Delhi.', 'prp therapy delhi, hair transplant new delhi, hair loss treatment, best trichologist'),
(3, 'Laser Skin Resurfacing: The Secret to Even Skin Tone, Youthful Glow, and Pore Refinement', 'laser-skin-resurfacing-even-skin-tone-youthful-glow', 3, 'Dr. Lawanya Clinic Specialist', 'Unveil brighter, smoother skin with medical-grade laser treatments. Understand how advanced laser energy targets pigmentation, fine lines, and open pores safely.', '<p>Modern aesthetic dermatology has transformed dramatically over the last decade, transitioning from aggressive peels to precise, targeted light therapies. Laser skin resurfacing stands at the forefront of this revolution, offering unparalleled precision in treating hyperpigmentation, sun damage, enlarged pores, and early signs of aging.</p><h3>How Does Laser Resurfacing Work?</h3><p>Medical lasers emit concentrated wavelengths of light that are absorbed by specific chromophores in the skin—such as melanin (pigment) or hemoglobin (redness). The photo-thermal reaction gently shatters excess pigment into microscopic particles that your body naturally clears, while simultaneously heating the deep collagen matrix to trigger firming and tightening.</p><h3>Key Benefits of Laser Therapy:</h3><ol><li><strong>Even Skin Tone:</strong> Effectively treats melasma, age spots, freckles, and dark patches.</li><li><strong>Pore Reduction:</strong> Stimulates collagen walls around pores, making them visibly smaller and refined.</li><li><strong>Smooth Fine Lines:</strong> Softens crow\'s feet, smile lines, and rough skin texture.</li><li><strong>Minimal Downtime:</strong> Modern fractional technology allows quick healing compared to traditional ablative methods.</li></ol><h3>Is Laser Treatment Painful?</h3><p>At Lawanya Derma, patient comfort is our utmost priority. We apply a medical-grade topical anesthetic cream 30 minutes before the procedure, coupled with integrated cooling devices, ensuring the experience feels like gentle pinpricks or warmth rather than discomfort.</p>', 'assets/images/about/4.jpg', 'published', 115, 4, 'Laser Skin Resurfacing in Delhi | Lawanya Derma & Aesthetic Clinic', 'Transform your skin with laser skin resurfacing at Lawanya Derma Clinic. Treat pigmentation, open pores, and uneven skin tone with expert dermatologists.', 'laser skin resurfacing, pigmentation treatment delhi, skin glow clinic'),
(4, 'The Art of Semi-Permanent Makeup: Microblading and Lip Blush Explained', 'art-of-semi-permanent-makeup-microblading-lip-blush', 5, 'Lawanya Aesthetic Specialist', 'Wake up with flawless brows and radiant lips every day. Explore how semi-permanent cosmetic tattooing enhances your natural features effortlessly.', '<p>In our fast-paced lives, spending 30 minutes every morning filling in eyebrows, lining lips, and reapplying makeup can become tiresome. Semi-Permanent Makeup (SPMU) has revolutionized modern beauty routines by providing subtle, smudge-proof, and natural-looking enhancement that lasts 1 to 3 years.</p><h3>1. Eyebrow Microblading & Ombre Powder Brows</h3><p>Microblading involves hand-drawing ultra-fine, hair-like strokes using organic pigments that blend flawlessly with your natural brow hairs. For clients seeking a softer, filled-in makeup look, Ombre Powder Brows create a soft gradient from head to tail. The result is symmetrical, fuller, and defined arches that frame your face perfectly.</p><h3>2. Lip Blush Treatment</h3><p>Lip blush is a semi-permanent tinting procedure that deposits natural pink and coral pigments into the superficial lip layer. It corrects asymmetry, camouflages pale or hyperpigmented lips, and delivers a healthy, youthful flush without the daily need for lipstick.</p><h3>Clinical Safety Standards at Lawanya Clinic</h3><p>Semi-permanent makeup requires strict medical-grade hygiene. At Lawanya Derma Clinic:</p><ul><li>We use 100% disposable, single-use sterilized needle cartridges.</li><li>We utilize premium, hypoallergenic, organic pigments certified for cosmetic safety.</li><li>Our aesthetic artists design custom color palettes and brow maps tailored to your facial geometry before beginning any pigment application.</li></ul>', 'assets/images/about/5.jpg', 'published', 84, 4, 'Semi-Permanent Makeup in Delhi | Microblading & Lip Blush | Lawanya', 'Get flawless brows and radiant lips with semi-permanent makeup at Lawanya Derma & Aesthetic Clinic. Expert microblading and lip blush in New Delhi.', 'microblading delhi, lip blush delhi, semi permanent makeup, aesthetic clinic delhi');

-- --------------------------------------------------------
-- Table structure for table `inquiries`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT '',
  `age` varchar(20) DEFAULT '',
  `service` varchar(150) DEFAULT '',
  `message` text DEFAULT NULL,
  `status` enum('new','contacted','completed') DEFAULT 'new',
  `source` varchar(100) DEFAULT 'website',
  `ip_address` varchar(45) DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `key_value` longtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key_name`, `key_value`) VALUES
('clinic_phone', '+91 70421 79159'),
('whatsapp_number', '917042179159'),
('clinic_email', 'lawanyaderma@gmail.com'),
('clinic_address', '328/1 Part-A, Pul Pahladpur, Suraj Kund Road, Near MCD Toll Tax, New Delhi - 110044'),
('working_hours', 'Mon-Sat: 9:00 AM - 8:00 PM | Sun: 10:00 AM - 6:00 PM'),
('facebook_url', 'https://www.facebook.com/photo/?fbid=122100123681002381&set=a.122100110643002381'),
('instagram_url', 'https://www.instagram.com/lawanya_derma/?igsh=MTVmbzUweTZnNWp2Mg%3D%3D&utm_source=qr#'),
('site_title', 'Lawanya Derma & Aesthetic Clinic - Best Skin, Hair & Laser Treatment in Delhi'),
('site_description', 'Leading dermatology and aesthetic clinic offering advanced skin treatments, hair restoration, laser procedures, weight loss programs and OPD services.');

SET FOREIGN_KEY_CHECKS = 1;
