<?php
/**
 * Admin: Manage Patient Inquiries & Leads
 * Lawanya Derma & Aesthetic Clinic
 */

$pageTitle = 'Patient Leads & Inquiries';
require_once __DIR__ . '/includes/header.php';

// Handle status update
if (isset($_GET['set_status']) && isset($_GET['id'])) {
    $inqId = (int)$_GET['id'];
    $newStatus = in_array($_GET['set_status'], ['new', 'contacted', 'completed']) ? $_GET['set_status'] : 'new';
    $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $inqId]);
    set_flash('success', 'Lead status marked as ' . ucfirst($newStatus));
    header('Location: ' . ADMIN_URL . 'inquiries.php');
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Inquiry deleted successfully.');
    }
    header('Location: ' . ADMIN_URL . 'inquiries.php');
    exit;
}

// Filter and search
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];

if (!empty($statusFilter) && in_array($statusFilter, ['new', 'contacted', 'completed'])) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ? OR service LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM inquiries $whereClause ORDER BY created_at DESC");
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

// Counts for pill tabs
$newCount = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
$contactedCount = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'contacted'")->fetchColumn();
$completedCount = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'completed'")->fetchColumn();
$totalCount = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="mb-1 fw-bold" style="color: var(--primary-dark);">Patient Inquiries & Leads</h2>
        <p class="text-muted mb-0">Follow up with patients who requested consultations through the website.</p>
    </div>
</div>

<!-- Status Tabs and Search -->
<div class="card-admin mb-4">
    <div class="card-admin-body py-3">
        <div class="row g-3 align-items-center justify-content-between">
            <div class="col-md-7">
                <ul class="nav nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 <?= empty($statusFilter) ? 'active' : ''; ?>" href="<?= ADMIN_URL; ?>inquiries.php">
                            All <span class="badge bg-secondary ms-1"><?= $totalCount; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 <?= ($statusFilter === 'new') ? 'active bg-danger text-white' : ''; ?>" href="<?= ADMIN_URL; ?>inquiries.php?status=new">
                            New <span class="badge bg-danger ms-1"><?= $newCount; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 <?= ($statusFilter === 'contacted') ? 'active bg-warning text-dark' : ''; ?>" href="<?= ADMIN_URL; ?>inquiries.php?status=contacted">
                            Contacted <span class="badge bg-warning text-dark ms-1"><?= $contactedCount; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 <?= ($statusFilter === 'completed') ? 'active bg-success text-white' : ''; ?>" href="<?= ADMIN_URL; ?>inquiries.php?status=completed">
                            Completed <span class="badge bg-success ms-1"><?= $completedCount; ?></span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-5">
                <form method="GET" action="" class="input-group">
                    <?php if (!empty($statusFilter)): ?>
                        <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control" placeholder="Search by patient name, phone..." value="<?= e($search); ?>">
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= ADMIN_URL; ?>inquiries.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Inquiries Table -->
<div class="card-admin">
    <div class="card-admin-header">
        <h5><i class="bi bi-inbox text-teal me-2"></i>Patient Leads (<?= count($inquiries); ?>)</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Patient Name</th>
                    <th>Contact</th>
                    <th>Requested Specialty</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inquiries)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                            No patient inquiries found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($inquiries as $inq): ?>
                        <tr>
                            <td>
                                <strong class="d-block text-dark"><?= date('M d, Y', strtotime($inq['created_at'])); ?></strong>
                                <small class="text-muted"><?= date('h:i A', strtotime($inq['created_at'])); ?> (<?= time_ago($inq['created_at']); ?>)</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($inq['name']); ?></div>
                                <?php if (!empty($inq['age'])): ?>
                                    <small class="text-muted">Age: <?= e($inq['age']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><a href="tel:<?= e($inq['phone']); ?>" class="fw-semibold text-primary"><i class="bi bi-telephone me-1"></i><?= e($inq['phone']); ?></a></div>
                                <?php if (!empty($inq['email'])): ?>
                                    <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= e($inq['email']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary">
                                    <i class="bi bi-heart-pulse me-1"></i><?= e($inq['service'] ?: 'General Consultation'); ?>
                                </span>
                                <?php if (!empty($inq['message'])): ?>
                                    <p class="text-muted small mb-0 mt-1 text-truncate" style="max-width: 260px;">
                                        <?= e($inq['message']); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm dropdown-toggle border-0 
                                        <?= ($inq['status'] === 'new') ? 'btn-danger' : (($inq['status'] === 'contacted') ? 'btn-warning text-dark' : 'btn-success'); ?>" 
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                                        <?= ucfirst($inq['status']); ?>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm">
                                        <li><a class="dropdown-item" href="?set_status=new&id=<?= $inq['id']; ?>"><span class="badge bg-danger me-1">&bull;</span> Mark as New</a></li>
                                        <li><a class="dropdown-item" href="?set_status=contacted&id=<?= $inq['id']; ?>"><span class="badge bg-warning text-dark me-1">&bull;</span> Mark as Contacted</a></li>
                                        <li><a class="dropdown-item" href="?set_status=completed&id=<?= $inq['id']; ?>"><span class="badge bg-success me-1">&bull;</span> Mark as Completed</a></li>
                                    </ul>
                                </div>
                            </td>
                            <td class="text-end">
                                <?php 
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $inq['phone']);
                                    $waMsg = urlencode("Hello " . $inq['name'] . ", greetings from Lawanya Derma & Aesthetic Clinic! We received your inquiry regarding " . ($inq['service'] ?: 'consultation') . ". How can we assist you today?");
                                ?>
                                <a href="https://wa.me/<?= $cleanPhone; ?>?text=<?= $waMsg; ?>" target="_blank" class="btn btn-action btn-action-success" title="Chat on WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="tel:<?= e($inq['phone']); ?>" class="btn btn-action" title="Call Patient">
                                    <i class="bi bi-telephone"></i>
                                </a>
                                <button type="button" class="btn btn-action" title="View Message Details" onclick="viewInquiryModal(<?= htmlspecialchars(json_encode($inq), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete this inquiry record?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $inq['id']; ?>">
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

<!-- Modal: View Inquiry Details -->
<div class="modal fade" id="inquiryDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-person-lines-fill me-2"></i>Patient Consultation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Patient Name</label>
                    <h5 id="mName" class="text-dark fw-bold mb-0"></h5>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="text-muted small text-uppercase fw-bold">Phone Number</label>
                        <p id="mPhone" class="fw-semibold text-primary mb-0"></p>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small text-uppercase fw-bold">Age</label>
                        <p id="mAge" class="mb-0 text-dark"></p>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Email</label>
                    <p id="mEmail" class="mb-0 text-dark"></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Requested Service</label>
                    <p id="mService" class="mb-0 fw-semibold text-dark"></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Patient Message / Concern</label>
                    <div class="p-3 bg-light rounded border text-dark" id="mMessage" style="min-height: 80px; white-space: pre-wrap;"></div>
                </div>
                <div class="small text-muted">
                    Received: <span id="mDate"></span> | Source: <span id="mSource"></span>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <a id="mWaLink" href="#" target="_blank" class="btn btn-success">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                </a>
                <a id="mCallLink" href="#" class="btn btn-primary">
                    <i class="bi bi-telephone me-1"></i> Call Patient
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewInquiryModal(inq) {
    document.getElementById('mName').textContent = inq.name || 'N/A';
    document.getElementById('mPhone').textContent = inq.phone || 'N/A';
    document.getElementById('mAge').textContent = inq.age || 'Not provided';
    document.getElementById('mEmail').textContent = inq.email || 'Not provided';
    document.getElementById('mService').textContent = inq.service || 'General Consultation';
    document.getElementById('mMessage').textContent = inq.message || 'No additional message was provided.';
    document.getElementById('mDate').textContent = inq.created_at;
    document.getElementById('mSource').textContent = inq.source || 'website';

    const cleanPhone = (inq.phone || '').replace(/[^0-9]/g, '');
    const waText = encodeURIComponent('Hello ' + inq.name + ', this is Lawanya Derma & Aesthetic Clinic regarding your consultation request for ' + (inq.service || 'treatment') + '.');
    document.getElementById('mWaLink').href = 'https://wa.me/' + cleanPhone + '?text=' + waText;
    document.getElementById('mCallLink').href = 'tel:' + inq.phone;

    const modal = new bootstrap.Modal(document.getElementById('inquiryDetailModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
