<?php
/**
 * Admin Sidebar Navigation
 * Lawanya Derma & Aesthetic Clinic
 */

$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
?>
<aside class="admin-sidebar">
    <!-- Brand Logo -->
    <a href="<?= ADMIN_URL; ?>" class="sidebar-brand text-decoration-none">
        <img src="<?= BASE_URL; ?>assets/logo/logo.jpeg" alt="Lawanya Derma Clinic">
        <div class="sidebar-brand-text">
            <h6>Lawanya Derma</h6>
            <small>Admin Panel</small>
        </div>
    </a>

    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        <div class="sidebar-category">Overview</div>
        <a href="<?= ADMIN_URL; ?>index.php" class="nav-link-custom <?= ($currentScript === 'index.php') ? 'active' : ''; ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-category">Blog Content</div>
        <a href="<?= ADMIN_URL; ?>posts.php" class="nav-link-custom <?= in_array($currentScript, ['posts.php', 'post-edit.php']) ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-richtext-fill"></i>
            <span>All Blog Posts</span>
        </a>
        <a href="<?= ADMIN_URL; ?>post-create.php" class="nav-link-custom <?= ($currentScript === 'post-create.php') ? 'active' : ''; ?>">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Add New Post</span>
        </a>
        <a href="<?= ADMIN_URL; ?>categories.php" class="nav-link-custom <?= ($currentScript === 'categories.php') ? 'active' : ''; ?>">
            <i class="bi bi-tags-fill"></i>
            <span>Categories</span>
        </a>

        <div class="sidebar-category">Patient Relations</div>
        <a href="<?= ADMIN_URL; ?>inquiries.php" class="nav-link-custom <?= ($currentScript === 'inquiries.php') ? 'active' : ''; ?>">
            <i class="bi bi-chat-heart-fill"></i>
            <span>Inquiries & Leads</span>
            <?php if (!empty($newInquiryCount) && $newInquiryCount > 0): ?>
                <span class="badge bg-danger sidebar-badge"><?= $newInquiryCount; ?> new</span>
            <?php endif; ?>
        </a>

        <div class="sidebar-category">Configuration</div>
        <a href="<?= ADMIN_URL; ?>profile.php" class="nav-link-custom <?= ($currentScript === 'profile.php') ? 'active' : ''; ?>">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Profile & Security</span>
        </a>
        <a href="<?= ADMIN_URL; ?>settings.php" class="nav-link-custom <?= ($currentScript === 'settings.php') ? 'active' : ''; ?>">
            <i class="bi bi-sliders"></i>
            <span>Clinic Settings</span>
        </a>

        <div class="sidebar-category">Live Website</div>
        <a href="<?= BASE_URL; ?>blogs.php" target="_blank" class="nav-link-custom">
            <i class="bi bi-newspaper"></i>
            <span>Browse Blog</span>
            <i class="bi bi-box-arrow-up-right ms-auto fs-6 text-muted"></i>
        </a>
        <a href="<?= BASE_URL; ?>" target="_blank" class="nav-link-custom">
            <i class="bi bi-globe2"></i>
            <span>Main Website</span>
            <i class="bi bi-box-arrow-up-right ms-auto fs-6 text-muted"></i>
        </a>
    </div>

    <!-- User Profile Strip -->
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <?= strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)); ?>
        </div>
        <div class="sidebar-user-info">
            <p class="user-name"><?= e($currentUser['full_name'] ?? 'Admin'); ?></p>
            <p class="user-role"><?= e($currentUser['email'] ?? 'admin@lawanya.com'); ?></p>
        </div>
        <a href="<?= ADMIN_URL; ?>logout.php" class="btn btn-action btn-sm text-danger" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</aside>
