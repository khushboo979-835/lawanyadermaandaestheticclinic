<?php
/**
 * Admin: Create New Blog Post
 * Lawanya Derma & Aesthetic Clinic
 */

$pageTitle = 'Write New Blog Post';
require_once __DIR__ . '/includes/header.php';

$errors = [];
$title = '';
$slug = '';
$categoryId = 0;
$author = 'Dr. Lawanya Clinic Team';
$readingTime = 5;
$excerpt = '';
$content = '';
$status = 'published';
$metaTitle = '';
$metaDescription = '';
$metaKeywords = '';
$featuredImage = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid. Please reload and try again.';
    } else {
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

        // Validation
        if (empty($title)) {
            $errors[] = 'Article title is required.';
        }
        if (empty($content)) {
            $errors[] = 'Article content is required.';
        }

        // Generate slug if empty
        if (empty($slug)) {
            $slug = slugify($title);
        } else {
            $slug = slugify($slug);
        }

        // Ensure unique slug
        $slugCheck = $pdo->prepare("SELECT id FROM posts WHERE slug = ? LIMIT 1");
        $slugCheck->execute([$slug]);
        if ($slugCheck->fetch()) {
            $slug .= '-' . time();
        }

        // Auto-generate excerpt if empty
        if (empty($excerpt)) {
            $excerpt = substr(strip_tags($content), 0, 180) . '...';
        }

        // Auto-generate SEO meta if empty
        if (empty($metaTitle)) {
            $metaTitle = $title . ' | Lawanya Derma & Aesthetic Clinic';
        }
        if (empty($metaDescription)) {
            $metaDescription = substr(strip_tags($excerpt), 0, 160);
        }

        // Handle File Upload (Featured Image)
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
                        $errors[] = 'Failed to save uploaded image to disk.';
                    }
                }
            }
        } elseif (!empty($_POST['selected_gallery_image'])) {
            // Selected an existing clinic gallery image
            $featuredImage = trim($_POST['selected_gallery_image']);
        }

        // Insert into database if no errors
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO posts (
                        title, slug, category_id, author, excerpt, content, 
                        featured_image, status, reading_time, meta_title, 
                        meta_description, meta_keywords, published_at, created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                    )
                ");
                $stmt->execute([
                    $title, $slug, $categoryId ?: null, $author, $excerpt, $content,
                    $featuredImage, $status, $readingTime, $metaTitle,
                    $metaDescription, $metaKeywords
                ]);

                set_flash('success', 'Blog article successfully created!');
                header('Location: ' . ADMIN_URL . 'posts.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
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
                <li class="breadcrumb-item active" aria-current="page">Create New</li>
            </ol>
        </nav>
        <h2 class="mb-0 fw-bold" style="color: var(--primary-dark);">Write New Blog Post</h2>
    </div>
    <a href="<?= ADMIN_URL; ?>posts.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Posts
    </a>
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
    <input type="hidden" name="content" id="postContent" value="<?= htmlspecialchars($content); ?>">

    <div class="row g-4">
        <!-- Main Form Column (8 cols) -->
        <div class="col-lg-8">
            <!-- Article Content Card -->
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-pencil-square text-primary me-2"></i>Post Content</h5>
                </div>
                <div class="card-admin-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="postTitle" class="form-label">Article Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fw-semibold" id="postTitle" name="title" placeholder="e.g., Complete Guide to Laser Skin Resurfacing" value="<?= e($title); ?>" required autofocus>
                    </div>

                    <!-- Slug -->
                    <div class="mb-3">
                        <label for="postSlug" class="form-label">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted" style="font-size: 0.85rem;"><?= BASE_URL; ?>blog-details.php?slug=</span>
                            <input type="text" class="form-control" id="postSlug" name="slug" placeholder="complete-guide-to-laser-skin-resurfacing" value="<?= e($slug); ?>">
                        </div>
                        <small class="text-muted">Auto-generated from title. Uses URL-friendly lowercase letters and dashes.</small>
                    </div>

                    <!-- Excerpt / Short Summary -->
                    <div class="mb-4">
                        <label for="postExcerpt" class="form-label">Short Excerpt / Summary</label>
                        <textarea class="form-control" id="postExcerpt" name="excerpt" rows="3" placeholder="A brief 1-2 sentence overview shown in blog cards and social share snippets..."><?= e($excerpt); ?></textarea>
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
                            <button type="button" class="editor-btn" data-cmd="insertTable" title="Insert Clinical Comparison Table"><i class="bi bi-table"></i></button>
                            <button type="button" class="editor-btn" data-cmd="insertImage" title="Insert Image by URL"><i class="bi bi-image"></i></button>
                            <button type="button" class="editor-btn" data-cmd="removeFormat" title="Clear Formatting"><i class="bi bi-eraser"></i></button>
                            <span class="vr mx-1 ms-auto"></span>
                            <button type="button" class="editor-btn" data-cmd="toggleHtml" title="View/Edit HTML Source"><i class="bi bi-code-slash"></i> HTML</button>
                        </div>

                        <!-- Editable Content Area -->
                        <div class="editor-area" id="postEditor" contenteditable="true" placeholder="Start writing your medical / aesthetic blog article here..."><?= $content; ?></div>
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
                        <input type="text" class="form-control" id="metaTitle" name="meta_title" placeholder="e.g. Best Laser Skin Resurfacing in Delhi | Lawanya Derma" value="<?= e($metaTitle); ?>">
                        <small class="text-muted">Recommended: 50-60 characters.</small>
                    </div>

                    <div class="mb-3">
                        <label for="metaDescription" class="form-label">SEO Meta Description</label>
                        <textarea class="form-control" id="metaDescription" name="meta_description" rows="2" placeholder="Brief summary displayed in Google search results..."><?= e($metaDescription); ?></textarea>
                        <small class="text-muted">Recommended: 150-160 characters.</small>
                    </div>

                    <div class="mb-3">
                        <label for="metaKeywords" class="form-label">SEO Keywords & Tags</label>
                        <input type="text" class="form-control" id="metaKeywords" name="meta_keywords" placeholder="skin treatment, laser clinic delhi, best dermatologist" value="<?= e($metaKeywords); ?>">
                        <small class="text-muted">Separate with commas.</small>
                    </div>

                    <!-- Live Google Search Result Preview -->
                    <label class="form-label mt-2">Live Google Search Preview</label>
                    <div class="serp-preview">
                        <div class="serp-url">
                            <img src="<?= BASE_URL; ?>assets/logo/logo.jpeg" alt="favicon">
                            <span>https://lawanyadermaandaestheticclinic.com &gt; blogs &gt; <span id="serpSlug"><?= e($slug ?: 'article-slug'); ?></span></span>
                        </div>
                        <div class="serp-title" id="serpTitle">
                            <?= e($metaTitle ?: ($title ?: 'Article Title - Lawanya Derma Clinic')); ?>
                        </div>
                        <div class="serp-desc" id="serpDesc">
                            <?= e($metaDescription ?: 'Discover advanced dermatological and aesthetic insights, treatments and professional advice from Lawanya Derma Clinic.'); ?>
                        </div>
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

                    <div class="mb-4">
                        <label for="readingTime" class="form-label">Est. Reading Time (Minutes)</label>
                        <input type="number" class="form-control" id="readingTime" name="reading_time" min="1" max="60" value="<?= $readingTime; ?>">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-clinic py-2">
                            <i class="bi bi-check2-circle me-1"></i> Save & Publish Post
                        </button>
                        <a href="<?= ADMIN_URL; ?>posts.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>

            <!-- Featured Image Upload Card -->
            <div class="card-admin mb-4">
                <div class="card-admin-header">
                    <h5><i class="bi bi-image text-teal me-2"></i>Featured Cover Image</h5>
                </div>
                <div class="card-admin-body">
                    <input type="file" name="featured_image" id="featuredImage" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif">

                    <!-- Drag & Drop Zone -->
                    <div class="upload-dropzone" id="uploadDropzone">
                        <i class="bi bi-cloud-arrow-up-fill d-block"></i>
                        <span class="fw-semibold text-dark d-block">Click or Drag & Drop Cover Image</span>
                        <small class="text-muted">JPG, PNG, WEBP (Max 6MB)</small>
                    </div>

                    <!-- Image Preview -->
                    <div class="upload-preview-box" id="uploadPreviewBox">
                        <button type="button" class="btn-remove-preview" id="btnRemovePreview" title="Remove Image">&times;</button>
                        <img src="" alt="Preview" id="uploadPreviewImg">
                    </div>

                    <!-- Alternative: Pick from Clinic Stock Photos -->
                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label text-muted small d-block">Or Select Clinic Photo:</label>
                        <div class="row g-2">
                            <?php
                            $clinicPhotos = [
                                'assets/images/about/1.jpg' => 'Clinic Consultation',
                                'assets/images/about/3.jpg' => 'Hair Restoration',
                                'assets/images/about/4.jpg' => 'Laser Equipment',
                                'assets/images/about/5.jpg' => 'Aesthetic Procedure',
                            ];
                            foreach ($clinicPhotos as $cPath => $cLabel):
                            ?>
                                <div class="col-6">
                                    <div class="form-check p-0">
                                        <label class="d-block border rounded p-1 text-center cursor-pointer" style="cursor: pointer;">
                                            <input type="radio" name="selected_gallery_image" value="<?= $cPath; ?>" class="form-check-input d-none">
                                            <img src="<?= BASE_URL . $cPath; ?>" class="img-fluid rounded mb-1" style="height: 60px; width: 100%; object-fit: cover;">
                                            <small class="d-block text-truncate" style="font-size:0.7rem;"><?= $cLabel; ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Select gallery radio thumbnail interaction
document.querySelectorAll('input[name="selected_gallery_image"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('input[name="selected_gallery_image"]').forEach(r => {
            r.closest('label').classList.remove('border-primary', 'shadow-sm');
        });
        if (this.checked) {
            this.closest('label').classList.add('border-primary', 'shadow-sm');
            // Show preview
            const previewBox = document.getElementById('uploadPreviewBox');
            const previewImg = document.getElementById('uploadPreviewImg');
            if (previewBox && previewImg) {
                previewImg.src = '<?= BASE_URL; ?>' + this.value;
                previewBox.style.display = 'block';
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
