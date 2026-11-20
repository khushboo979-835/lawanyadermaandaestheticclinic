<?php
/**
 * Admin: Delete Blog Post Action
 * Lawanya Derma & Aesthetic Clinic
 */

require_once __DIR__ . '/includes/auth.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}

$pdo = get_db_connection();

// Check if action is delete_all_dummy
if (isset($_GET['action']) && $_GET['action'] === 'delete_all_dummy') {
    try {
        $dummySlugs = [
            'complete-guide-to-acne-scar-removal-treatments',
            'prp-therapy-vs-hair-transplant-hair-restoration-comparison',
            'laser-skin-resurfacing-even-skin-tone-youthful-glow',
            'art-of-semi-permanent-makeup-microblading-lip-blush'
        ];
        $in = str_repeat('?,', count($dummySlugs) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM posts WHERE slug IN ($in)");
        $stmt->execute($dummySlugs);

        set_flash('success', 'All dummy / sample posts have been permanently removed.');
    } catch (Exception $e) {
        set_flash('danger', 'Error: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

// Check if action is delete_all (wipe all posts)
if (isset($_GET['action']) && $_GET['action'] === 'delete_all') {
    try {
        // Clean uploaded blog images from disk
        $stmt = $pdo->query("SELECT featured_image FROM posts WHERE featured_image LIKE 'uploads/blogs/%'");
        while ($img = $stmt->fetchColumn()) {
            $imagePath = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $img);
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        $pdo->exec("DELETE FROM posts");

        set_flash('success', 'All blog articles have been permanently removed from database.');
    } catch (Exception $e) {
        set_flash('danger', 'Error: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

// Get post ID from POST or GET
$postId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($postId <= 0) {
    set_flash('danger', 'Invalid post ID.');
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

try {
    // Fetch post to check for uploaded image
    $stmt = $pdo->prepare("SELECT featured_image FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if ($post) {
        // If the image is stored in uploads/blogs, remove it from disk
        if (!empty($post['featured_image']) && strpos($post['featured_image'], 'uploads/blogs/') === 0) {
            $imagePath = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $post['featured_image']);
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // Delete post permanently from database
        $delStmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $delStmt->execute([$postId]);

        set_flash('success', 'Blog article deleted permanently.');
    } else {
        set_flash('warning', 'Post was already deleted or does not exist.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Error deleting post: ' . $e->getMessage());
}

header('Location: ' . ADMIN_URL . 'posts.php');
exit;
