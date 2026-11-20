<?php
/**
 * Admin: Manage Categories & Specialties
 * Lawanya Derma & Aesthetic Clinic
 */

$pageTitle = 'Manage Categories';
require_once __DIR__ . '/includes/header.php';

$errors = [];

// Handle Add or Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $errors[] = 'Category name is required.';
            } else {
                if (empty($slug)) $slug = slugify($name);
                else $slug = slugify($slug);

                try {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $slug, $description]);
                    set_flash('success', 'Category "' . $name . '" created successfully.');
                    header('Location: ' . ADMIN_URL . 'categories.php');
                    exit;
                } catch (Exception $e) {
                    $errors[] = 'A category with this name or slug already exists.';
                }
            }
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name) || $id <= 0) {
                $errors[] = 'Category name and valid ID are required.';
            } else {
                if (empty($slug)) $slug = slugify($name);
                else $slug = slugify($slug);

                try {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $description, $id]);
                    set_flash('success', 'Category updated successfully.');
                    header('Location: ' . ADMIN_URL . 'categories.php');
                    exit;
                } catch (Exception $e) {
                    $errors[] = 'Update failed: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                try {
                    // Posts with this category will have category_id set to NULL due to foreign key
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                    set_flash('success', 'Category removed successfully.');
                    header('Location: ' . ADMIN_URL . 'categories.php');
                    exit;
                } catch (Exception $e) {
                    $errors[] = 'Delete failed: ' . $e->getMessage();
                }
            }
        }
    }
}

// Fetch categories with post counts
$query = "
    SELECT c.*, COUNT(p.id) AS post_count 
    FROM categories c 
    LEFT JOIN posts p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
";
$categories = $pdo->query($query)->fetchAll();
?>

<!-- Header & Add Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold" style="color: var(--primary-dark);">Clinic Specialties & Categories</h2>
        <p class="text-muted mb-0">Organize patient education articles by medical and aesthetic specialty.</p>
    </div>
    <button type="button" class="btn btn-primary-clinic" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="bi bi-plus-circle me-1"></i> Add Category
    </button>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm mb-4">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card-admin">
    <div class="card-admin-header">
        <h5><i class="bi bi-tags text-primary me-2"></i>All Categories (<?= count($categories); ?>)</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Category Name</th>
                    <th>URL Slug</th>
                    <th>Description</th>
                    <th>Articles Count</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No categories created yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $index => $cat): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td>
                                <strong class="text-dark"><?= e($cat['name']); ?></strong>
                            </td>
                            <td>
                                <code><?= e($cat['slug']); ?></code>
                            </td>
                            <td>
                                <span class="text-muted small text-truncate d-block" style="max-width: 320px;">
                                    <?= e($cat['description'] ?: '—'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL; ?>posts.php?category=<?= $cat['id']; ?>" class="badge badge-soft-primary text-decoration-none">
                                    <i class="bi bi-journal-text me-1"></i><?= $cat['post_count']; ?> posts
                                </a>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-action" title="Edit Category"
                                    onclick="openEditCategoryModal(<?= $cat['id']; ?>, '<?= e(addslashes($cat['name'])); ?>', '<?= e(addslashes($cat['slug'])); ?>', '<?= e(addslashes($cat['description'] ?? '')); ?>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete category \'<?= e(addslashes($cat['name'])); ?>\'? Posts linked to it will become uncategorized.');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id']; ?>">
                                    <button type="submit" class="btn btn-action btn-action-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Category -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="create">

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-plus-circle me-2"></i>Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="createCatName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="createCatName" name="name" placeholder="e.g. Chemical Peels & Glow" required>
                    </div>
                    <div class="mb-3">
                        <label for="createCatSlug" class="form-label">URL Slug (Optional)</label>
                        <input type="text" class="form-control" id="createCatSlug" name="slug" placeholder="e.g. chemical-peels-glow">
                        <small class="text-muted">Leave empty to auto-generate from name.</small>
                    </div>
                    <div class="mb-2">
                        <label for="createCatDesc" class="form-label">Description</label>
                        <textarea class="form-control" id="createCatDesc" name="description" rows="3" placeholder="Brief overview of this clinical specialty..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-clinic">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editCatId" value="">

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-pencil me-2"></i>Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="editCatName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editCatName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCatSlug" class="form-label">URL Slug</label>
                        <input type="text" class="form-control" id="editCatSlug" name="slug" required>
                    </div>
                    <div class="mb-2">
                        <label for="editCatDesc" class="form-label">Description</label>
                        <textarea class="form-control" id="editCatDesc" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-clinic">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditCategoryModal(id, name, slug, desc) {
    document.getElementById('editCatId').value = id;
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatSlug').value = slug;
    document.getElementById('editCatDesc').value = desc;
    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
