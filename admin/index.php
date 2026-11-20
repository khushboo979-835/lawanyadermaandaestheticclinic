<?php
/**
 * Admin Dashboard
 * Lawanya Derma & Aesthetic Clinic
 */

$pageTitle = 'Clinic Dashboard';
require_once __DIR__ . '/includes/header.php';

// Fetch key dashboard statistics
$totalPosts = 0;
$publishedPosts = 0;
$draftPosts = 0;
$totalCategories = 0;
$totalInquiries = 0;
$newInquiries = 0;
$totalViews = 0;

try {
    $totalPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $publishedPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
    $draftPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
    $totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $totalInquiries = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
    $newInquiries = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
    $totalViews = (int)$pdo->query("SELECT COALESCE(SUM(views), 0) FROM posts")->fetchColumn();

    // Fetch recent 5 posts with category name
    $stmt = $pdo->query("
        SELECT p.*, c.name AS category_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ");
    $recentPosts = $stmt->fetchAll();

    // Fetch recent 5 inquiries
    $stmt = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5");
    $recentInquiries = $stmt->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!-- Welcome Banner -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="mb-1 fw-bold" style="color: var(--primary-dark);">Clinic Overview</h2>
        <p class="text-muted mb-0">Welcome back, <?= e($currentUser['full_name']); ?>! Manage your clinic's blog articles and patient inquiries.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= ADMIN_URL; ?>post-create.php" class="btn btn-primary-clinic d-inline-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> Create New Post
        </a>
        <a href="<?= BASE_URL; ?>blogs.php" target="_blank" class="btn btn-outline-secondary d-inline-flex align-items-center">
            <i class="bi bi-box-arrow-up-right me-2"></i> Visit Blog
        </a>
    </div>
</div>

<!-- Metrics & Stats Grid -->
<div class="row g-3 mb-4">
    <!-- Published Blogs -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Published Articles</div>
                    <div class="stat-number"><?= $publishedPosts; ?></div>
                    <small class="text-muted"><i class="bi bi-file-earmark-check text-success me-1"></i><?= $totalPosts; ?> total created</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-journal-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Inquiries -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-teal">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Patient Inquiries</div>
                    <div class="stat-number"><?= $totalInquiries; ?></div>
                    <small class="<?= $newInquiries > 0 ? 'text-danger fw-semibold' : 'text-muted'; ?>">
                        <i class="bi bi-bell me-1"></i><?= $newInquiries; ?> pending review
                    </small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-chat-heart"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Views -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-orange">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Article Reads</div>
                    <div class="stat-number"><?= number_format($totalViews); ?></div>
                    <small class="text-muted"><i class="bi bi-graph-up-arrow text-primary me-1"></i>Organic patient reach</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-eye"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-green">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Active Specialties</div>
                    <div class="stat-number"><?= $totalCategories; ?></div>
                    <small class="text-muted"><i class="bi bi-tags text-teal me-1"></i>Dermatology categories</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-folder2-open"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Recent Blog Articles -->
    <div class="col-xl-8">
        <div class="card-admin mb-4">
            <div class="card-admin-header">
                <h5><i class="bi bi-file-earmark-richtext text-primary me-2"></i>Recent Blog Articles</h5>
                <a href="<?= ADMIN_URL; ?>posts.php" class="btn btn-sm btn-outline-primary">
                    View All (<?= $totalPosts; ?>) <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Image</th>
                            <th>Title & Details</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPosts)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted"></i>
                                    No blog posts created yet. <br>
                                    <a href="<?= ADMIN_URL; ?>post-create.php" class="btn btn-sm btn-primary-clinic mt-2">Create First Blog</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentPosts as $post): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($post['featured_image'])): ?>
                                            <img src="<?= BASE_URL . e($post['featured_image']); ?>" alt="Thumb" class="post-thumb">
                                        <?php else: ?>
                                            <div class="post-thumb d-flex align-items-center justify-content-center text-muted">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= ADMIN_URL; ?>post-edit.php?id=<?= $post['id']; ?>" class="fw-semibold text-dark d-block" style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= e($post['title']); ?>
                                        </a>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i><?= time_ago($post['created_at']); ?> &bull; <?= e($post['author']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-primary">
                                            <?= e($post['category_name'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($post['status'] === 'published'): ?>
                                            <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Published</span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-warning"><i class="bi bi-hourglass-split me-1"></i>Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted fw-semibold"><i class="bi bi-eye me-1"></i><?= $post['views']; ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL; ?>blog-details.php?slug=<?= urlencode($post['slug']); ?>" target="_blank" class="btn btn-action" title="View live article">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL; ?>post-edit.php?id=<?= $post['id']; ?>" class="btn btn-action" title="Edit post">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL; ?>post-delete.php?id=<?= $post['id']; ?>" class="btn btn-action btn-action-danger" title="Delete post" onclick="return confirm('Are you sure you want to delete this blog post?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Inquiries & Quick Links -->
    <div class="col-xl-4">
        <!-- Patient Inquiries Widget -->
        <div class="card-admin mb-4">
            <div class="card-admin-header">
                <h5><i class="bi bi-chat-heart text-teal me-2"></i>Patient Leads</h5>
                <a href="<?= ADMIN_URL; ?>inquiries.php" class="btn btn-sm btn-outline-secondary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-admin-body p-0">
                <?php if (empty($recentInquiries)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-1 text-muted"></i>
                        No patient inquiries yet.
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentInquiries as $inq): ?>
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong class="text-dark"><?= e($inq['name']); ?></strong>
                                    <?php if ($inq['status'] === 'new'): ?>
                                        <span class="badge bg-danger" style="font-size:0.7rem;">New</span>
                                    <?php elseif ($inq['status'] === 'contacted'): ?>
                                        <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Contacted</span>
                                    <?php else: ?>
                                        <span class="badge bg-success" style="font-size:0.7rem;">Done</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted mb-2" style="font-size: 0.85rem;">
                                    <div><i class="bi bi-telephone text-primary me-1"></i><?= e($inq['phone']); ?></div>
                                    <?php if (!empty($inq['service'])): ?>
                                        <div><i class="bi bi-heart-pulse text-teal me-1"></i><?= e($inq['service']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $inq['phone']); ?>" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 0.78rem;">
                                        <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                    </a>
                                    <a href="tel:<?= e($inq['phone']); ?>" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.78rem;">
                                        <i class="bi bi-telephone me-1"></i>Call
                                    </a>
                                    <span class="ms-auto text-muted small align-self-center"><?= time_ago($inq['created_at']); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Clinic Actions Widget -->
        <div class="card-admin">
            <div class="card-admin-header">
                <h5><i class="bi bi-lightning-charge text-warning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-admin-body">
                <div class="d-grid gap-2">
                    <a href="<?= ADMIN_URL; ?>post-create.php" class="btn btn-outline-primary text-start py-2">
                        <i class="bi bi-pencil-square me-2"></i> Write & Publish New Blog
                    </a>
                    <a href="<?= ADMIN_URL; ?>categories.php" class="btn btn-outline-secondary text-start py-2">
                        <i class="bi bi-tags me-2"></i> Manage Specialties & Categories
                    </a>
                    <a href="<?= ADMIN_URL; ?>settings.php" class="btn btn-outline-secondary text-start py-2">
                        <i class="bi bi-gear me-2"></i> Update Clinic Information
                    </a>
                    <a href="<?= BASE_URL; ?>" target="_blank" class="btn btn-outline-secondary text-start py-2">
                        <i class="bi bi-box-arrow-up-right me-2"></i> Open Clinic Landing Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
