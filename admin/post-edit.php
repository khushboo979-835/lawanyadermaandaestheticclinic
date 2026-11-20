<?php
/**
 * Admin: Edit Blog Post
 * Lawanya Derma & Aesthetic Clinic
 */

$pageTitle = 'Edit Blog Post';
require_once __DIR__ . '/includes/header.php';

$postId = (int)($_GET['id'] ?? 0);
if ($postId <= 0) {
    set_flash('danger', 'Invalid post ID.');
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

// Fetch existing post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    set_flash('danger', 'Blog post not found.');
    header('Location: ' . ADMIN_URL . 'posts.php');
    exit;
}

$errors = [];
$title = $post['title'];
$slug = $post['slug'];
$categoryId = $post['category_id'];
$author = $post['author'];
$readingTime = $post['reading_time'];
$excerpt = $post['excerpt'];
$content = $post['content'];
$status = $post['status'];
$metaTitle = $post['meta_title'];
$metaDescription = $post['meta_description'];
$metaKeywords = $post['meta_keywords'];
$featuredImage = $post['featured_image'];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $author = trim($_POST['author'] ?? 'Dr. Lawanya Clinic Team');
    $readingTime = (int)($_POST['reading_time'] ?? 5);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDescription = trim($_POST['meta_description'] ?? '');
    $metaKeywords = trim($_POST['meta_keywords'] ?? '');

    if (empty($title)) {
        $errors[] = 'Article title is required.';
    }
    if (empty($content) || trim(strip_tags($content)) === '') {
        if (!empty($excerpt)) {
            $content = '<p>' . nl2br(htmlspecialchars($excerpt)) . '</p>';
        } else {
            $content = '<p>' . htmlspecialchars($title) . '</p>';
        }
    }

        if (empty($slug)) {
            $slug = slugify($title);
        } else {
            $slug = slugify($slug);
        }

        // Check unique slug ignoring this post
        $slugCheck = $pdo->prepare("SELECT id FROM posts WHERE slug = ? AND id != ? LIMIT 1");
        $slugCheck->execute([$slug, $postId]);
        if ($slugCheck->fetch()) {
            $slug .= '-' . time();
        }

        if (empty($excerpt)) {
            $excerpt = substr(strip_tags($content), 0, 180) . '...';
        }

        // Check if remove image was clicked
        if (!empty($_POST['remove_existing_image']) && $_POST['remove_existing_image'] == '1') {
            $featuredImage = '';
        }

        // Check for new uploaded file
        if (!empty($_FILES['featured_image']['name'])) {
            $file = $_FILES['featured_image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Failed to upload image. (Error code: ' . $file['error'] . ')';
            } elseif (!in_array($fileExt, $allowedExtensions)) {
                $errors[] = 'Invalid image extension. Allowed: JPG, PNG, WEBP, GIF.';
            } elseif ($file['size'] > 6 * 1024 * 1024) {
                $errors[] = 'Image size exceeds maximum limit of 6MB.';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $allowedTypes)) {
                    $errors[] = 'Uploaded file is not a valid image.';
                } else {
                    $newFileName = 'blog_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $fileExt;
                    $targetPath = UPLOAD_DIR . $newFileName;

                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $featuredImage = 'uploads/blogs/' . $newFileName;
                    } else {
                        $errors[] = 'Failed to save uploaded image.';
                    }
                }
            }
        } elseif (!empty($_POST['selected_gallery_image'])) {
            $featuredImage = trim($_POST['selected_gallery_image']);
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE posts SET 
                        title = ?, slug = ?, category_id = ?, author = ?, 
                        excerpt = ?, content = ?, featured_image = ?, status = ?, 
                        reading_time = ?, meta_title = ?, meta_description = ?, 
                        meta_keywords = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title, $slug, $categoryId ?: null, $author,
                    $excerpt, $content, $featuredImage, $status,
                    $readingTime, $metaTitle, $metaDescription,
                    $metaKeywords, $postId
                ]);

                set_flash('success', 'Blog article successfully updated!');
                header('Location: ' . ADMIN_URL . 'posts.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
}

// Fetch categories
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>

<!-- Breadcrumbs & Heading -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="<?= ADMIN_URL; ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= ADMIN_URL; ?>posts.php">Blog Articles</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Post</li>
            </ol>
        </nav>
        <h2 class="mb-0 fw-bold" style="color: var(--primary-dark);">Edit Blog Post</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL; ?>blog-details.php?slug=<?= urlencode($slug); ?>" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-box-arrow-up-right me-1"></i> View Live
        </a>
        <a href="<?= ADMIN_URL; ?>posts.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm mb-4">
        <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please correct the following:</strong>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form id="postForm" method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
    <textarea name="content" id="postContent" style="display:none;"><?= htmlspecialchars($content); ?></textarea>
    <input type="hidden" name="remove_existing_image" id="removeExistingImage" value="0">

    <div class="row g-4">
        <!-- Main Form Column (8 cols) -->
        <div class="col-lg-8">
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-pencil-square text-primary me-2"></i>Post Content</h5>
                </div>
                <div class="card-admin-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="postTitle" class="form-label">Article Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fw-semibold" id="postTitle" name="title" value="<?= e($title); ?>" required>
                    </div>

                    <!-- Slug -->
                    <div class="mb-3">
                        <label for="postSlug" class="form-label">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted" style="font-size: 0.85rem;"><?= BASE_URL; ?>blog-details.php?slug=</span>
                            <input type="text" class="form-control" id="postSlug" name="slug" value="<?= e($slug); ?>">
                        </div>
                    </div>

                    <!-- Excerpt / Short Summary -->
                    <div class="mb-4">
                        <label for="postExcerpt" class="form-label">Short Excerpt / Summary</label>
                        <textarea class="form-control" id="postExcerpt" name="excerpt" rows="3"><?= e($excerpt); ?></textarea>
                    </div>

                    <!-- WYSIWYG Content Editor -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Full Body Article <span class="text-danger">*</span></label>
                            <span class="badge bg-light text-muted border" id="wordCountDisplay">0 words</span>
                        </div>

                        <!-- Editor Toolbar -->
                        <div class="editor-toolbar">
                            <button type="button" class="editor-btn" data-cmd="bold" title="Bold (Ctrl+B)"><b>B</b></button>
                            <button type="button" class="editor-btn" data-cmd="italic" title="Italic (Ctrl+I)"><i>I</i></button>
                            <button type="button" class="editor-btn" data-cmd="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                            <span class="vr mx-1"></span>
                            <button type="button" class="editor-btn" data-cmd="formatBlock" data-val="h2" title="Heading 2">H2</button>
                            <button type="button" class="editor-btn" data-cmd="formatBlock" data-val="h3" title="Heading 3">H3</button>
                            <button type="button" class="editor-btn" data-cmd="formatBlock" data-val="h4" title="Heading 4">H4</button>
                            <button type="button" class="editor-btn" data-cmd="formatBlock" data-val="p" title="Normal Paragraph">P</button>
                            <span class="vr mx-1"></span>
                            <button type="button" class="editor-btn" data-cmd="insertUnorderedList" title="Bullet List"><i class="bi bi-list-ul"></i></button>
                            <button type="button" class="editor-btn" data-cmd="insertOrderedList" title="Numbered List"><i class="bi bi-list-ol"></i></button>
                            <button type="button" class="editor-btn" data-cmd="formatBlock" data-val="blockquote" title="Quote Block"><i class="bi bi-quote"></i></button>
                            <span class="vr mx-1"></span>
                            <button type="button" class="editor-btn" data-cmd="createLink" title="Insert Link"><i class="bi bi-link-45deg"></i></button>
                            <button type="button" class="editor-btn" data-cmd="insertTable" title="Insert Comparison Table"><i class="bi bi-table"></i></button>
                            <button type="button" class="editor-btn" data-cmd="insertImage" title="Insert Image by URL"><i class="bi bi-image"></i></button>
                            <button type="button" class="editor-btn" data-cmd="removeFormat" title="Clear Formatting"><i class="bi bi-eraser"></i></button>
                            <span class="vr mx-1 ms-auto"></span>
                            <button type="button" class="editor-btn" data-cmd="toggleHtml" title="View/Edit HTML Source"><i class="bi bi-code-slash"></i> HTML</button>
                        </div>

                        <!-- Editable Content Area -->
                        <div class="editor-area" id="postEditor" contenteditable="true"><?= $content; ?></div>
                    </div>
                </div>
            </div>

            <!-- SEO Suite Card -->
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-google text-danger me-2"></i>SEO & Search Engine Optimization</h5>
                </div>
                <div class="card-admin-body">
                    <div class="mb-3">
                        <label for="metaTitle" class="form-label">SEO Meta Title</label>
                        <input type="text" class="form-control" id="metaTitle" name="meta_title" value="<?= e($metaTitle); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="metaDescription" class="form-label">SEO Meta Description</label>
                        <textarea class="form-control" id="metaDescription" name="meta_description" rows="2"><?= e($metaDescription); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="metaKeywords" class="form-label">SEO Keywords & Tags</label>
                        <input type="text" class="form-control" id="metaKeywords" name="meta_keywords" value="<?= e($metaKeywords); ?>">
                    </div>

                    <!-- Live Google Search Result Preview -->
                    <label class="form-label mt-2">Live Google Search Preview</label>
                    <div class="serp-preview">
                        <div class="serp-url">
                            <img src="<?= BASE_URL; ?>assets/logo/logo.jpeg" alt="favicon">
                            <span>https://lawanyadermaandaestheticclinic.com &gt; blogs &gt; <span id="serpSlug"><?= e($slug); ?></span></span>
                        </div>
                        <div class="serp-title" id="serpTitle"><?= e($metaTitle ?: $title); ?></div>
                        <div class="serp-desc" id="serpDesc"><?= e($metaDescription ?: $excerpt); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options Column (4 cols) -->
        <div class="col-lg-4">
            <!-- Publish Controls Card -->
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-send text-success me-2"></i>Publishing</h5>
                </div>
                <div class="card-admin-body">
                    <div class="mb-3">
                        <label for="postStatus" class="form-label">Publication Status</label>
                        <select name="status" id="postStatus" class="form-select">
                            <option value="published" <?= ($status === 'published') ? 'selected' : ''; ?>>Published (Live on Website)</option>
                            <option value="draft" <?= ($status === 'draft') ? 'selected' : ''; ?>>Draft (Saved Privately)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="postCategory" class="form-label">Specialty Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="postCategory" class="form-select" required>
                            <option value="">Select Specialty</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id']; ?>" <?= ($categoryId == $cat['id']) ? 'selected' : ''; ?>>
                                    <?= e($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="postAuthor" class="form-label">Author Name</label>
                        <input type="text" class="form-control" id="postAuthor" name="author" value="<?= e($author); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="readingTime" class="form-label">Est. Reading Time (Minutes)</label>
                        <input type="number" class="form-control" id="readingTime" name="reading_time" min="1" max="60" value="<?= $readingTime; ?>">
                    </div>

                    <div class="mb-4 text-muted small">
                        <div><i class="bi bi-calendar-event me-1"></i> Created: <?= date('M d, Y H:i', strtotime($post['created_at'])); ?></div>
                        <div><i class="bi bi-eye me-1"></i> Total Views: <?= $post['views']; ?></div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-clinic py-2">
                            <i class="bi bi-check2-circle me-1"></i> Update Post
                        </button>
                        <a href="<?= ADMIN_URL; ?>posts.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>

            <!-- Featured Image Card -->
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-image text-teal me-2"></i>Featured Cover Image</h5>
                </div>
                <div class="card-admin-body">
                    <input type="file" name="featured_image" id="featuredImage" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif">

                    <!-- Existing Image Display -->
                    <?php if (!empty($featuredImage)): ?>
                        <div class="mb-3 text-center" id="existingImageWrapper">
                            <img src="<?= BASE_URL . e($featuredImage); ?>" class="img-fluid rounded border mb-2" style="max-height: 200px; width: 100%; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCurrentImage()">
                                <i class="bi bi-trash me-1"></i> Remove Current Image
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Drag & Drop Zone -->
                    <div class="upload-dropzone" id="uploadDropzone">
                        <i class="bi bi-cloud-arrow-up-fill d-block"></i>
                        <span class="fw-semibold text-dark d-block">Click to Replace Cover Image</span>
                        <small class="text-muted">JPG, PNG, WEBP (Max 6MB)</small>
                    </div>

                    <!-- Image Preview -->
                    <div class="upload-preview-box" id="uploadPreviewBox">
                        <button type="button" class="btn-remove-preview" id="btnRemovePreview" title="Remove Image">&times;</button>
                        <img src="" alt="Preview" id="uploadPreviewImg">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function removeCurrentImage() {
    const wrapper = document.getElementById('existingImageWrapper');
    if (wrapper) wrapper.style.display = 'none';
    document.getElementById('removeExistingImage').value = '1';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
