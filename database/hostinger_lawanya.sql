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
