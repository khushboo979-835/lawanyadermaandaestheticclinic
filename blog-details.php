<?php
/**
 * Lawanya Derma & Aesthetic Clinic
 * Single Blog Article Detail View
 */

require_once __DIR__ . '/config/db.php';

$pdo = get_db_connection();

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (empty($slug) && $id <= 0) {
    header('Location: blogs.php');
    exit;
}

// Fetch article
if (!empty($slug)) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.slug = ? LIMIT 1
    ");
    $stmt->execute([$slug]);
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? LIMIT 1
    ");
    $stmt->execute([$id]);
}

$post = $stmt->fetch();

// If not found or draft (and not admin), redirect
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
if (!$post || ($post['status'] !== 'published' && !$isAdmin)) {
    header('Location: blogs.php');
    exit;
}

// Increment view count (prevent duplicate count per session)
if (!isset($_SESSION['viewed_post_' . $post['id']])) {
    $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);
    $_SESSION['viewed_post_' . $post['id']] = true;
    $post['views']++;
}

// Fetch related articles (same category or recent)
$relatedStmt = $pdo->prepare("
    SELECT id, title, slug, featured_image, published_at, reading_time 
    FROM posts 
    WHERE id != ? AND status = 'published' AND (category_id = ? OR category_id IS NOT NULL) 
    ORDER BY created_at DESC 
    LIMIT 3
");
$relatedStmt->execute([$post['id'], $post['category_id']]);
$relatedPosts = $relatedStmt->fetchAll();

// Dynamic SEO Head variables
$page_title = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'] . ' | Lawanya Derma Clinic';
$page_description = !empty($post['meta_description']) ? $post['meta_description'] : $post['excerpt'];
$page_keywords = !empty($post['meta_keywords']) ? $post['meta_keywords'] : 'dermatology, skin clinic delhi, ' . $post['category_name'];
$page_image = !empty($post['featured_image']) ? BASE_URL . $post['featured_image'] : BASE_URL . 'assets/logo/logo.jpeg';
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

include 'common/head.php';
include 'common/header.php';
?>

<!-- Structured Data for Blog Article -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "<?= addslashes($post['title']); ?>",
    "image": "<?= addslashes($page_image); ?>",
    "author": {
        "@type": "Organization",
        "name": "<?= addslashes($post['author'] ?: 'Lawanya Derma & Aesthetic Clinic'); ?>"
    },
    "publisher": {
        "@type": "Organization",
        "name": "Lawanya Derma & Aesthetic Clinic",
        "logo": {
            "@type": "ImageObject",
            "url": "<?= BASE_URL; ?>assets/logo/logo.jpeg"
        }
    },
    "datePublished": "<?= date('c', strtotime($post['published_at'] ?? $post['created_at'])); ?>",
    "dateModified": "<?= date('c', strtotime($post['updated_at'] ?? $post['created_at'])); ?>",
    "description": "<?= addslashes($post['excerpt']); ?>"
}
</script>

<!-- Breadcrumb Header -->
<div class="py-3 bg-light border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="<?= $base_url; ?>" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="blogs.php" class="text-decoration-none">Blog</a></li>
                <?php if (!empty($post['category_name'])): ?>
                    <li class="breadcrumb-item"><a href="blogs.php?category=<?= urlencode($post['category_slug']); ?>" class="text-decoration-none"><?= e($post['category_name']); ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page"><?= e($post['title']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<article class="single-article-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <!-- Draft Notice for Admin -->
                <?php if ($post['status'] === 'draft'): ?>
                    <div class="alert alert-warning d-flex align-items-center mb-4">
                        <i class="bi bi-eye-slash-fill me-2 fs-5"></i>
                        <div><strong>Draft Preview:</strong> This article is currently unpublished and visible only to logged-in administrators.</div>
                    </div>
                <?php endif; ?>

                <!-- Article Header -->
                <header class="mb-4">
                    <?php if (!empty($post['category_name'])): ?>
                        <a href="blogs.php?category=<?= urlencode($post['category_slug']); ?>" class="badge px-3 py-2 rounded-pill text-decoration-none mb-3" style="background: var(--primary-blue); font-size: 0.8rem;">
                            <?= e($post['category_name']); ?>
                        </a>
                    <?php endif; ?>

                    <h1 class="display-6 fw-bold mb-3" style="color: var(--primary-blue); line-height: 1.3;">
                        <?= e($post['title']); ?>
                    </h1>

                    <!-- Meta Details -->
                    <div class="d-flex flex-wrap align-items-center text-muted gap-3 pb-3 border-bottom" style="font-size: 0.88rem;">
                        <span class="d-flex align-items-center">
                            <i class="bi bi-person-circle text-primary me-2 fs-6"></i>
                            <strong><?= e($post['author']); ?></strong>
                        </span>
                        <span>&bull;</span>
                        <span class="d-flex align-items-center">
                            <i class="bi bi-calendar3 text-teal me-2"></i>
                            <?= date('F d, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?>
                        </span>
                        <span>&bull;</span>
                        <span class="d-flex align-items-center">
                            <i class="bi bi-clock me-1"></i>
                            <?= $post['reading_time']; ?> min read
                        </span>
                        <span>&bull;</span>
                        <span class="d-flex align-items-center">
                            <i class="bi bi-eye me-1"></i>
                            <?= $post['views']; ?> views
                        </span>

                        <?php if ($isAdmin): ?>
                            <span class="ms-auto">
                                <a href="<?= ADMIN_URL; ?>post-edit.php?id=<?= $post['id']; ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.78rem;">
                                    <i class="bi bi-pencil me-1"></i> Edit Article
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>

                <!-- Featured Image -->
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="featured-image-wrapper mb-5 rounded-4 overflow-hidden shadow-sm">
                        <img src="<?= e($post['featured_image']); ?>" alt="<?= e($post['title']); ?>" class="w-100 img-fluid" style="max-height: 480px; object-fit: cover;">
                    </div>
                <?php endif; ?>

                <!-- Article Lead / Excerpt -->
                <?php if (!empty($post['excerpt'])): ?>
                    <div class="lead p-4 rounded-4 mb-4" style="background: rgba(44, 90, 160, 0.05); border-left: 4px solid var(--primary-blue); font-size: 1.15rem; color: #334155;">
                        <?= e($post['excerpt']); ?>
                    </div>
                <?php endif; ?>

                <!-- Body Content -->
                <div class="article-body-content mb-5" style="font-size: 1.05rem; line-height: 1.85; color: #2d3748;">
                    <?= $post['content']; ?>
                </div>

                <!-- Tags / Keywords -->
                <?php if (!empty($post['meta_keywords'])): ?>
                    <div class="tags-section py-3 border-top border-bottom mb-4">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="fw-semibold text-muted small"><i class="bi bi-tags-fill me-1"></i>Tags:</span>
                            <?php 
                            $tags = explode(',', $post['meta_keywords']);
                            foreach ($tags as $tag): 
                                $cleanTag = trim($tag);
                                if (!empty($cleanTag)):
                            ?>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.8rem;">
                                    #<?= e($cleanTag); ?>
                                </span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Social Share Bar -->
                <div class="share-box p-3 bg-light rounded-4 mb-5 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <span class="fw-bold" style="color: var(--primary-blue);">
                        <i class="bi bi-share-fill me-2"></i>Share this article:
                    </span>
                    <div class="d-flex gap-2">
                        <?php 
                        $encodedUrl = urlencode($currentUrl);
                        $encodedTitle = urlencode($post['title']);
                        ?>
                        <a href="https://wa.me/?text=<?= $encodedTitle . '%20' . $encodedUrl; ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3" title="Share on WhatsApp">
                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl; ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3" title="Share on Facebook">
                            <i class="bi bi-facebook me-1"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= $encodedTitle; ?>&url=<?= $encodedUrl; ?>" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3" title="Share on Twitter / X">
                            <i class="bi bi-twitter-x me-1"></i> X
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="navigator.clipboard.writeText('<?= $currentUrl; ?>'); alert('Link copied to clipboard!');">
                            <i class="bi bi-link-45deg me-1"></i> Copy Link
                        </button>
                    </div>
                </div>

                <!-- Author Bio Box -->
                <div class="author-card p-4 rounded-4 mb-5 border bg-white shadow-sm d-flex gap-4 align-items-center">
                    <img src="assets/logo/logo.jpeg" alt="Lawanya Derma Clinic" class="rounded-circle border" style="width: 75px; height: 75px; object-fit: cover;">
                    <div>
                        <h5 class="fw-bold mb-1" style="color: var(--primary-blue);">Lawanya Derma & Aesthetic Clinic</h5>
                        <p class="small text-muted mb-2">Clinical Team & Dermatological Specialists &bull; New Delhi</p>
                        <p class="small text-muted mb-0" style="line-height: 1.5;">
                            With 15+ years of clinical excellence, Lawanya Derma provides patient-first treatments in clinical dermatology, laser resurfacing, hair restoration, and semi-permanent makeup.
                        </p>
                    </div>
                </div>

                <!-- Clinic Appointment Consultation CTA Card -->
                <div class="consultation-card p-4 p-md-5 rounded-4 text-white text-center mb-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));">
                    <h3 class="fw-bold mb-2">Ready to Transform Your Skin or Hair?</h3>
                    <p class="lead mb-4 text-white-50" style="font-size: 1.05rem;">
                        Schedule a comprehensive assessment with our expert doctors today.
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="https://wa.me/917042179159?text=Hi%2C%20I%20read%20your%20article%20on%20'<?= urlencode($post['title']); ?>'%20and%20would%20like%20to%20book%20a%20consultation" target="_blank" class="btn btn-light btn-lg rounded-pill px-4 fw-bold text-primary">
                            <i class="bi bi-whatsapp me-2 text-success"></i>Book on WhatsApp
                        </a>
                        <a href="tel:+917042179159" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-bold">
                            <i class="bi bi-telephone me-2"></i>Call +91 70421 79159
                        </a>
                    </div>
                </div>

                <!-- Related Articles Section -->
                <?php if (!empty($relatedPosts)): ?>
                    <div class="related-articles-section pt-4 border-top">
                        <h4 class="fw-bold mb-4" style="color: var(--primary-blue);">Related Clinical Articles</h4>
                        <div class="row g-4">
                            <?php foreach ($relatedPosts as $rPost): ?>
                                <div class="col-md-4">
                                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                                        <a href="blog-details.php?slug=<?= urlencode($rPost['slug']); ?>" style="height: 140px; display: block; overflow: hidden; background: #e2e8f0;">
                                            <?php if (!empty($rPost['featured_image'])): ?>
                                                <img src="<?= e($rPost['featured_image']); ?>" alt="<?= e($rPost['title']); ?>" class="w-100 h-100 object-fit-cover">
                                            <?php else: ?>
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="bi bi-image"></i></div>
                                            <?php endif; ?>
                                        </a>
                                        <div class="card-body p-3">
                                            <h6 class="card-title fw-bold mb-2">
                                                <a href="blog-details.php?slug=<?= urlencode($rPost['slug']); ?>" class="text-dark text-decoration-none">
                                                    <?= e($rPost['title']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted d-block"><?= date('M d, Y', strtotime($rPost['published_at'] ?? 'now')); ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</article>

<style>
.article-body-content h2, 
.article-body-content h3, 
.article-body-content h4 {
    color: var(--primary-blue);
    font-weight: 700;
    margin-top: 1.8rem;
    margin-bottom: 0.9rem;
}
.article-body-content p {
    margin-bottom: 1.25rem;
}
.article-body-content ul, 
.article-body-content ol {
    margin-bottom: 1.25rem;
    padding-left: 1.5rem;
}
.article-body-content li {
    margin-bottom: 0.5rem;
}
.article-body-content blockquote {
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    border-left: 4px solid var(--accent-teal);
    background: #f8fafc;
    font-style: italic;
    color: #475569;
    border-radius: 0 8px 8px 0;
}
.article-body-content table {
    width: 100%;
    margin: 1.5rem 0;
    border-collapse: collapse;
}
.article-body-content table th {
    background: #f1f5f9;
    color: var(--primary-blue);
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
}
.article-body-content table td {
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
}
</style>

<?php 
include 'common/chat-bot.php';
include 'common/footer.php'; 
?>
