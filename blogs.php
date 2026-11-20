<?php
/**
 * Lawanya Derma & Aesthetic Clinic
 * Official Clinical Blog & Patient Education Hub
 */

require_once __DIR__ . '/config/db.php';

$pdo = get_db_connection();

// Filter parameters
$search = trim($_GET['search'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');

// Pagination setup
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

// Build query
$where = ["p.status = 'published'"];
$params = [];

if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$currentCategory = null;
if (!empty($categorySlug)) {
    $catStmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
    $catStmt->execute([$categorySlug]);
    $currentCategory = $catStmt->fetch();
    if ($currentCategory) {
        $where[] = "p.category_id = ?";
        $params[] = $currentCategory['id'];
    }
}

$whereClause = "WHERE " . implode(" AND ", $where);

// Count total published posts matching query
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts p $whereClause");
$countStmt->execute($params);
$totalPosts = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $limit);

// Fetch posts
$postQuery = "
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $whereClause 
    ORDER BY p.published_at DESC, p.created_at DESC 
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($postQuery);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Fetch categories with post counts for sidebar
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS post_count 
    FROM categories c 
    JOIN posts p ON c.id = p.category_id AND p.status = 'published'
    GROUP BY c.id 
    ORDER BY post_count DESC
")->fetchAll();

// Fetch 4 recent posts for sidebar
$recentPosts = $pdo->query("
    SELECT title, slug, featured_image, published_at, created_at 
    FROM posts 
    WHERE status = 'published' 
    ORDER BY published_at DESC 
    LIMIT 4
")->fetchAll();

// Dynamic SEO Head variables
$page_title = !empty($currentCategory) 
    ? e($currentCategory['name']) . ' Articles & Advice | Lawanya Derma Clinic Delhi' 
    : 'Dermatology & Aesthetic Blog | Expert Skin & Hair Care Articles | Lawanya Derma';
$page_description = 'Explore expert dermatological insights, skin rejuvenation secrets, hair restoration advice, and laser aesthetics from Dr. Lawanya and team in New Delhi.';
$page_keywords = 'dermatology blog delhi, skin clinic articles, hair loss guide, laser treatment benefits, aesthetic clinic tips';

include 'common/head.php';
include 'common/header.php';
?>

<!-- Blog Hero Banner -->
<section class="blog-hero-section py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(44, 90, 160, 0.06) 0%, rgba(32, 178, 170, 0.12) 100%); border-bottom: 1px solid rgba(44, 90, 160, 0.1);">
    <div class="container py-4 text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="badge px-3 py-2 rounded-pill mb-3 text-uppercase fw-semibold" style="background: rgba(32, 178, 170, 0.15); color: var(--primary-blue); font-size: 0.8rem; letter-spacing: 1px;">
                    <i class="bi bi-journal-medical me-1"></i> Medical Insights & Aesthetic Care
                </span>
                <h1 class="display-5 fw-bold mb-3" style="color: var(--primary-blue);">
                    Skin & Hair Health Hub
                </h1>
                <p class="lead text-muted mb-4">
                    Evidence-based articles, doctor-approved skincare advice, and advanced aesthetic treatment guides by Delhi's premier dermatology specialists.
                </p>

                <!-- Blog Search Bar -->
                <form action="blogs.php" method="GET" class="max-w-600 mx-auto" style="max-width: 580px;">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white p-1 border">
                        <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0 shadow-none ps-2" placeholder="Search acne scars, laser, PRP, chemical peels..." value="<?= e($search); ?>">
                        <button class="btn btn-primary rounded-pill px-4" type="submit" style="background: var(--primary-blue); border: none;">
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Category Filter Pills Bar -->
<section class="py-3 bg-white border-bottom sticky-top" style="top: 75px; z-index: 990;">
    <div class="container">
        <div class="d-flex align-items-center gap-2 overflow-auto py-1" style="white-space: nowrap;">
            <a href="blogs.php" class="btn btn-sm rounded-pill px-3 <?= empty($categorySlug) ? 'btn-primary' : 'btn-outline-secondary'; ?>" style="<?= empty($categorySlug) ? 'background: var(--primary-blue); border-color: var(--primary-blue);' : ''; ?>">
                All Topics
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="blogs.php?category=<?= urlencode($cat['slug']); ?>" 
                   class="btn btn-sm rounded-pill px-3 <?= ($categorySlug === $cat['slug']) ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                   style="<?= ($categorySlug === $cat['slug']) ? 'background: var(--primary-blue); border-color: var(--primary-blue);' : ''; ?>">
                    <?= e($cat['name']); ?> (<?= $cat['post_count']; ?>)
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Main Blog Section -->
<section class="blog-main-section py-5" style="background-color: var(--light-bg);">
    <div class="container">
        <div class="row g-4">
            <!-- Left / Main: Blog Cards Grid (Col-8) -->
            <div class="col-lg-8">
                <?php if (!empty($search) || !empty($categorySlug)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded border">
                        <span class="text-muted">
                            Showing results for: 
                            <strong>
                                <?= !empty($currentCategory) ? e($currentCategory['name']) : ''; ?>
                                <?= !empty($search) ? '"' . e($search) . '"' : ''; ?>
                            </strong>
                            (<?= $totalPosts; ?> articles found)
                        </span>
                        <a href="blogs.php" class="btn btn-sm btn-outline-danger">Clear Filter</a>
                    </div>
                <?php endif; ?>

                <?php if (empty($posts)): ?>
                    <div class="text-center py-5 bg-white rounded shadow-sm p-4">
                        <i class="bi bi-journal-x display-3 text-muted mb-3 d-block"></i>
                        <h4 class="fw-bold text-dark">No Articles Found</h4>
                        <p class="text-muted">We couldn't find any articles matching your search query. Try exploring other categories or keywords.</p>
                        <a href="blogs.php" class="btn btn-primary rounded-pill mt-2">View All Articles</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden blog-card hover-lift transition">
                                    <!-- Featured Image & Badge Overlay -->
                                    <div class="position-relative overflow-hidden" style="height: 220px; background-color: #e2e8f0;">
                                        <a href="blog-details.php?slug=<?= urlencode($post['slug']); ?>">
                                            <?php if (!empty($post['featured_image'])): ?>
                                                <img src="<?= e($post['featured_image']); ?>" alt="<?= e($post['title']); ?>" class="w-100 h-100 object-fit-cover blog-card-img">
                                            <?php else: ?>
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                                    <i class="bi bi-image fs-1"></i>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <?php if (!empty($post['category_name'])): ?>
                                            <span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-semibold shadow-sm" style="background: var(--primary-blue); font-size: 0.72rem;">
                                                <?= e($post['category_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Card Content -->
                                    <div class="card-body d-flex flex-column p-4">
                                        <!-- Meta Info -->
                                        <div class="d-flex align-items-center text-muted small mb-2 gap-3" style="font-size: 0.8rem;">
                                            <span><i class="bi bi-calendar3 me-1 text-primary"></i><?= date('M d, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?></span>
                                            <span><i class="bi bi-clock me-1 text-teal"></i><?= $post['reading_time']; ?> min read</span>
                                        </div>

                                        <!-- Title -->
                                        <h5 class="card-title fw-bold mb-2 lh-base" style="font-size: 1.15rem;">
                                            <a href="blog-details.php?slug=<?= urlencode($post['slug']); ?>" class="text-dark text-decoration-none blog-title-link">
                                                <?= e($post['title']); ?>
                                            </a>
                                        </h5>

                                        <!-- Excerpt -->
                                        <p class="card-text text-muted small flex-grow-1 mb-4" style="line-height: 1.6;">
                                            <?= e($post['excerpt']); ?>
                                        </p>

                                        <!-- Read More Link -->
                                        <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                                            <a href="blog-details.php?slug=<?= urlencode($post['slug']); ?>" class="fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                                Read Article <i class="bi bi-arrow-right"></i>
                                            </a>
                                            <span class="text-muted small"><i class="bi bi-eye me-1"></i><?= $post['views']; ?></span>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="d-flex justify-content-center mt-5">
                            <nav aria-label="Blog pagination">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1; ?>&search=<?= urlencode($search); ?>&category=<?= urlencode($categorySlug); ?>">&laquo; Prev</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&category=<?= urlencode($categorySlug); ?>">
                                                <?= $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1; ?>&search=<?= urlencode($search); ?>&category=<?= urlencode($categorySlug); ?>">Next &raquo;</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Right Sidebar (Col-4) -->
            <div class="col-lg-4">
                <!-- Free Consultation CTA Card -->
                <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 text-white text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));">
                    <div class="position-relative" style="z-index: 2;">
                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px; color: var(--primary-blue);">
                            <i class="bi bi-telephone-inbound-fill fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-white">Need Expert Skin Advice?</h4>
                        <p class="small text-white-50 mb-4">
                            Schedule a one-on-one consultation with Dr. Lawanya and our certified aesthetic dermatologists today.
                        </p>
                        <a href="https://wa.me/917042179159?text=Hi%2C%20I%20would%20like%20to%20book%20a%20consultation%20at%20Lawanya%20Derma%20Clinic" target="_blank" class="btn btn-light rounded-pill fw-bold w-100 py-2 mb-2 text-primary">
                            <i class="bi bi-whatsapp me-2 text-success"></i>Book on WhatsApp
                        </a>
                        <a href="tel:+917042179159" class="btn btn-outline-light rounded-pill fw-bold w-100 py-2">
                            <i class="bi bi-telephone-fill me-2"></i>Call +91 70421 79159
                        </a>
                    </div>
                </div>

                <!-- Specialties List Widget -->
                <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 bg-white">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-blue);">
                        <i class="bi bi-tags text-teal me-2"></i>Specialties
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($categories as $cat): ?>
                            <li class="mb-2">
                                <a href="blogs.php?category=<?= urlencode($cat['slug']); ?>" class="d-flex justify-content-between align-items-center text-decoration-none py-1 text-muted hover-primary">
                                    <span><?= e($cat['name']); ?></span>
                                    <span class="badge rounded-pill bg-light text-primary border"><?= $cat['post_count']; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Recent Articles Widget -->
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-blue);">
                        <i class="bi bi-lightning text-warning me-2"></i>Recent Guides
                    </h5>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($recentPosts as $rPost): ?>
                            <div class="d-flex gap-3 align-items-center">
                                <?php if (!empty($rPost['featured_image'])): ?>
                                    <img src="<?= e($rPost['featured_image']); ?>" alt="Thumb" class="rounded-3 object-fit-cover" style="width: 70px; height: 60px; min-width: 70px;">
                                <?php else: ?>
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 70px; height: 60px; min-width: 70px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-1" style="font-size: 0.92rem; line-height: 1.35;">
                                        <a href="blog-details.php?slug=<?= urlencode($rPost['slug']); ?>" class="text-dark text-decoration-none hover-primary">
                                            <?= e($rPost['title']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted" style="font-size: 0.78rem;">
                                        <?= date('M d, Y', strtotime($rPost['published_at'] ?? $rPost['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.blog-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(44, 90, 160, 0.12) !important;
}
.blog-card-img {
    transition: transform 0.35s ease;
}
.blog-card:hover .blog-card-img {
    transform: scale(1.05);
}
.blog-title-link:hover, .hover-primary:hover {
    color: var(--primary-blue) !important;
}
</style>

<?php 
include 'common/chat-bot.php';
include 'common/footer.php'; 
?>
