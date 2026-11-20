<?php
/**
 * Admin: Delete Blog Post Action
 * Lawanya Derma & Aesthetic Clinic
 */

require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security verification failed.');
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

$postId = (int)($_POST['id'] ?? 0);
if ($postId <= 0) {
    set_flash('danger', 'Invalid post ID.');
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

try {
    $pdo = get_db_connection();

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

        // Delete post
        $delStmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $delStmt->execute([$postId]);

        set_flash('success', 'Blog article successfully deleted.');
    } else {
        set_flash('danger', 'Post not found.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Error deleting post: ' . $e->getMessage());
}

header('Location: ' . ADMIN_URL . 'posts.php');
exit;
