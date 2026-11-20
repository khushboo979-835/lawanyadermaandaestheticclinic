        </main>

        <!-- Admin Footer -->
        <footer class="admin-footer">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div>
                    &copy; <?= date('Y'); ?> <strong>Lawanya Derma & Aesthetic Clinic</strong>. All rights reserved.
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> Admin v2.0</span>
                    <a href="<?= BASE_URL; ?>" target="_blank" class="text-muted">Clinic Home</a>
                    <a href="<?= BASE_URL; ?>blogs.php" target="_blank" class="text-muted">Clinic Blog</a>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS Suite -->
<script src="<?= ADMIN_URL; ?>assets/js/admin.js"></script>
</body>
</html>
