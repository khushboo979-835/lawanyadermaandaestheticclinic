<?php
/**
 * Admin: Blog Posts List
 * Lawanya Derma & Aesthetic Clinic
 */

$pageTitle = 'Manage Blog Posts';
require_once __DIR__ . '/includes/header.php';

// Handle quick status toggle
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $postId = (int)$_GET['id'];
    $currentStatus = $_GET['toggle_status'] === 'published' ? 'draft' : 'published';
    $stmt = $pdo->prepare("UPDATE posts SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$currentStatus, $postId]);
    set_flash('success', 'Post status updated to ' . ucfirst($currentStatus));
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

// Search and filter parameters
$search = trim($_GET['search'] ?? '');
$categoryFilter = (int)($_GET['category'] ?? 0);
$statusFilter = trim($_GET['status'] ?? '');

// Pagination setup
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}

if (!empty($statusFilter) && in_array($statusFilter, ['published', 'draft'])) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Count total matching posts
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts p $whereClause");
$countStmt->execute($params);
$totalMatching = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalMatching / $limit);

// Fetch matching posts with category
$query = "
    SELECT p.*, c.name AS category_name 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $whereClause 
    ORDER BY p.created_at DESC 
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Fetch categories for filter dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<!-- Header & Action Row -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="mb-1 fw-bold" style="color: var(--primary-dark);">Blog Articles</h2>
        <p class="text-muted mb-0">Create, edit, organize, and publish patient education articles.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= ADMIN_URL; ?>post-delete.php?action=delete_all_dummy" class="btn btn-outline-danger d-inline-flex align-items-center" onclick="return confirm('Are you sure you want to permanently remove all dummy/sample posts?');">
            <i class="bi bi-trash me-1"></i> Clear Sample Posts
        </a>
        <a href="<?= ADMIN_URL; ?>post-create.php" class="btn btn-primary-clinic d-inline-flex align-items-center">
            <i class="bi bi-plus-circle-fill me-2"></i> Add New Blog Post
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card-admin mb-4">
    <div class="card-admin-body py-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by title, keywords..." value="<?= e($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id']; ?>" <?= ($categoryFilter == $cat['id']) ? 'selected' : ''; ?>>
                            <?= e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="published" <?= ($statusFilter === 'published') ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?= ($statusFilter === 'draft') ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <?php if (!empty($search) || $categoryFilter > 0 || !empty($statusFilter)): ?>
                    <a href="<?= ADMIN_URL; ?>posts.php" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Posts Table -->
<div class="card-admin">
    <div class="card-admin-header">
        <h5>
            <i class="bi bi-journal-text text-primary me-2"></i>
            All Posts (<?= $totalMatching; ?>)
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th style="width: 75px;">Cover</th>
                    <th>Article Title & Slug</th>
                    <th>Specialty Category</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th class="text-end" style="min-width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-plus display-5 d-block mb-3 text-primary opacity-75"></i>
                            <h5 class="fw-bold text-dark">No blog articles yet</h5>
                            <p class="text-muted mb-3">Your clinic blog is clean. Click below to write and publish your first article.</p>
                            <a href="<?= ADMIN_URL; ?>post-create.php" class="btn btn-primary-clinic btn-sm">
                                <i class="bi bi-plus-circle-fill me-1"></i> Add Your First Blog Post
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
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
                                <a href="<?= ADMIN_URL; ?>post-edit.php?id=<?= $post['id']; ?>" class="fw-semibold text-dark d-block">
                                    <?= e($post['title']); ?>
                                </a>
                                <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                    <code>/<?= e($post['slug']); ?></code>
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary">
                                    <?= e($post['category_name'] ?? 'Uncategorized'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL; ?>posts.php?toggle_status=<?= $post['status']; ?>&id=<?= $post['id']; ?>" class="text-decoration-none" title="Click to toggle status">
                                    <?php if ($post['status'] === 'published'): ?>
                                        <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Published</span>
                                    <?php else: ?>
                                        <span class="badge badge-soft-warning"><i class="bi bi-hourglass-split me-1"></i>Draft</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td>
                                <span class="text-muted"><i class="bi bi-eye me-1"></i><?= $post['views']; ?></span>
                            </td>
                            <td>
                                <span class="small text-muted"><?= date('M d, Y', strtotime($post['created_at'])); ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL; ?>blog-details.php?slug=<?= urlencode($post['slug']); ?>" target="_blank" class="btn btn-action" title="View live article">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <a href="<?= ADMIN_URL; ?>post-edit.php?id=<?= $post['id']; ?>" class="btn btn-action" title="Edit post">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= ADMIN_URL; ?>post-delete.php?id=<?= $post['id']; ?>" class="btn btn-action btn-action-danger" title="Delete post" onclick="return confirm('Are you sure you want to permanently delete this blog post?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-admin-footer p-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                Showing <?= min($totalMatching, $offset + 1); ?> - <?= min($totalMatching, $offset + $limit); ?> of <?= $totalMatching; ?> posts
            </span>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&category=<?= $categoryFilter; ?>&status=<?= urlencode($statusFilter); ?>">
                                <?= $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
