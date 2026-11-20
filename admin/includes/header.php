<?php
/**
 * Admin Layout Header
 * Lawanya Derma & Aesthetic Clinic
 */

require_once __DIR__ . '/auth.php';

$currentUser = get_current_admin();
$pdo = get_db_connection();

// Count unread (new) inquiries for notification
$newInquiryCount = 0;
try {
    $newInquiryCount = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
} catch (Exception $e) {}

$pageTitle = $pageTitle ?? 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?> - Lawanya Derma Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL; ?>assets/logo/logo.jpeg">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    
    <!-- Admin Custom CSS -->
    <link rel="stylesheet" href="<?= ADMIN_URL; ?>assets/css/admin.css">
</head>
<body>

<div class="sidebar-overlay"></div>

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="admin-main">
        <!-- Top Header Navigation -->
        <header class="admin-header">
            <button class="header-toggle d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>

            <!-- Search Bar -->
            <form action="<?= ADMIN_URL; ?>posts.php" method="GET" class="header-search d-none d-md-block">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Search blog posts or articles..." value="<?= e($_GET['search'] ?? ''); ?>">
            </form>

            <!-- Header Right Actions -->
            <div class="header-actions">
                <!-- Create Post Quick Button -->
                <a href="<?= ADMIN_URL; ?>post-create.php" class="btn btn-sm btn-primary-clinic d-none d-sm-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> New Post
                </a>

                <!-- View Front Website -->
                <a href="<?= BASE_URL; ?>" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center" title="Open Public Website">
                    <i class="bi bi-globe me-1"></i> View Website
                </a>

                <!-- Inquiries Notification -->
                <div class="dropdown">
                    <a href="<?= ADMIN_URL; ?>inquiries.php" class="btn btn-action position-relative" title="Patient Inquiries">
                        <i class="bi bi-bell"></i>
                        <?php if ($newInquiryCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                <?= $newInquiryCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Admin Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn d-flex align-items-center p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="sidebar-user-avatar" style="width: 36px; height: 36px;">
                            <?= strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <span class="ms-2 d-none d-lg-inline-block text-start">
                            <span class="d-block fw-semibold" style="font-size: 0.85rem; line-height: 1.2; color: var(--dark-muted);">
                                <?= e($currentUser['full_name'] ?? 'Admin'); ?>
                            </span>
                            <small class="text-muted" style="font-size: 0.72rem;">Administrator</small>
                        </span>
                        <i class="bi bi-chevron-down ms-2 text-muted" style="font-size: 0.75rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="min-width: 200px; border-radius: 10px;">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <span class="d-block fw-semibold text-truncate"><?= e($currentUser['full_name'] ?? 'Admin'); ?></span>
                                <small class="text-muted text-truncate d-block"><?= e($currentUser['email'] ?? ''); ?></small>
                            </div>
                        </li>
                        <li><a class="dropdown-item py-2" href="<?= ADMIN_URL; ?>profile.php"><i class="bi bi-person me-2 text-primary"></i> My Profile</a></li>
                        <li><a class="dropdown-item py-2" href="<?= ADMIN_URL; ?>settings.php"><i class="bi bi-gear me-2 text-primary"></i> Clinic Settings</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL; ?>blogs.php" target="_blank"><i class="bi bi-journal-richtext me-2 text-primary"></i> Public Blog</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?= ADMIN_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Workspace Container -->
        <main class="admin-content">
            <!-- Flash message notification -->
            <?php $flash = get_flash(); if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show d-flex align-items-center shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div><?= e($flash['message']); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
