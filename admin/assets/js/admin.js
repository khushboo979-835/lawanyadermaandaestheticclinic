/**
 * Lawanya Derma & Aesthetic Clinic - Admin JavaScript Suite
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function () {
            adminSidebar.classList.toggle('show');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('show');
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            adminSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // 2. Auto Slug Generator
    const titleInput = document.getElementById('postTitle');
    const slugInput = document.getElementById('postSlug');
    const serpTitle = document.getElementById('serpTitle');
    const serpSlug = document.getElementById('serpSlug');

    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }

    if (titleInput && slugInput) {
        let isSlugManuallyChanged = slugInput.value.length > 0;

        slugInput.addEventListener('input', function () {
            isSlugManuallyChanged = true;
            if (serpSlug) serpSlug.textContent = slugInput.value || 'your-slug-here';
        });

        titleInput.addEventListener('input', function () {
            if (!isSlugManuallyChanged) {
                const slug = slugify(this.value);
                slugInput.value = slug;
                if (serpSlug) serpSlug.textContent = slug || 'your-slug-here';
            }
            if (serpTitle) {
                const metaTitleInput = document.getElementById('metaTitle');
                if (!metaTitleInput || !metaTitleInput.value) {
                    serpTitle.textContent = this.value ? this.value + ' | Lawanya Derma' : 'Page Title - Lawanya Derma';
                }
            }
            calculateReadingTime();
        });
    }

    // 3. Meta Title & Meta Description Live SERP Preview
    const metaTitleInput = document.getElementById('metaTitle');
    const metaDescInput = document.getElementById('metaDescription');
    const serpDesc = document.getElementById('serpDesc');

    if (metaTitleInput && serpTitle) {
        metaTitleInput.addEventListener('input', function () {
            serpTitle.textContent = this.value || (titleInput ? titleInput.value : 'Page Title Preview');
        });
    }

    if (metaDescInput && serpDesc) {
        metaDescInput.addEventListener('input', function () {
            serpDesc.textContent = this.value || 'Provide a meta description for this article to preview how it appears on Google search results...';
        });
    }

    // 4. Image Upload Drag & Drop Preview
    const fileInput = document.getElementById('featuredImage');
    const dropzone = document.getElementById('uploadDropzone');
    const previewBox = document.getElementById('uploadPreviewBox');
    const previewImg = document.getElementById('uploadPreviewImg');
    const removeBtn = document.getElementById('btnRemovePreview');

    if (fileInput && dropzone) {
        dropzone.addEventListener('click', function (e) {
            if (e.target !== removeBtn && !removeBtn?.contains(e.target)) {
                fileInput.click();
            }
        });

        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                showImagePreview(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                showImagePreview(this.files[0]);
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                fileInput.value = '';
                if (previewBox) previewBox.style.display = 'none';
                const removeImageFlag = document.getElementById('removeExistingImage');
                if (removeImageFlag) removeImageFlag.value = '1';
            });
        }
    }

    function showImagePreview(file) {
        if (!file.type.match('image.*')) {
            alert('Please select a valid image file (JPG, PNG, WebP)');
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            if (previewImg) previewImg.src = e.target.result;
            if (previewBox) previewBox.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    // 5. Rich WYSIWYG Editor
    const editorArea = document.getElementById('postEditor');
    const hiddenContent = document.getElementById('postContent');
    const editorToolbar = document.querySelector('.editor-toolbar');

    if (editorArea && hiddenContent) {
        // Execute command
        if (editorToolbar) {
            editorToolbar.querySelectorAll('.editor-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const cmd = this.dataset.cmd;
                    const val = this.dataset.val || null;

                    if (cmd === 'createLink') {
                        const url = prompt('Enter the link URL:', 'https://');
                        if (url) document.execCommand(cmd, false, url);
                    } else if (cmd === 'insertImage') {
                        const imgUrl = prompt('Enter the Image URL:', 'https://');
                        if (imgUrl) document.execCommand('insertImage', false, imgUrl);
                    } else if (cmd === 'insertTable') {
                        insertTableTemplate();
                    } else if (cmd === 'toggleHtml') {
                        toggleSourceCodeView(this);
                    } else {
                        document.execCommand(cmd, false, val);
                    }
                    editorArea.focus();
                });
            });
        }

        // Keep hidden textarea updated
        editorArea.addEventListener('input', function () {
            hiddenContent.value = editorArea.innerHTML;
            calculateReadingTime();
        });

        // Sync before form submit
        const postForm = document.getElementById('postForm');
        if (postForm) {
            postForm.addEventListener('submit', function () {
                hiddenContent.value = editorArea.innerHTML;
            });
        }
    }

    function insertTableTemplate() {
        const tableHtml = `
            <table class="table table-bordered my-3">
                <thead>
                    <tr>
                        <th>Feature / Treatment</th>
                        <th>Details</th>
                        <th>Recommended For</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Treatment Option 1</td>
                        <td>High precision, clinically tested</td>
                        <td>Active concerns</td>
                    </tr>
                    <tr>
                        <td>Treatment Option 2</td>
                        <td>Zero downtime, natural recovery</td>
                        <td>Maintenance & prevention</td>
                    </tr>
                </tbody>
            </table><p><br></p>
        `;
        document.execCommand('insertHTML', false, tableHtml);
    }

    let isHtmlView = false;
    function toggleSourceCodeView(btn) {
        if (!isHtmlView) {
            editorArea.innerText = editorArea.innerHTML;
            btn.classList.add('active');
            isHtmlView = true;
        } else {
            editorArea.innerHTML = editorArea.innerText;
            btn.classList.remove('active');
            isHtmlView = false;
        }
    }

    // 6. Word Count & Reading Time Estimation
    function calculateReadingTime() {
        const text = editorArea ? editorArea.innerText : '';
        const words = text.trim() ? text.trim().split(/\s+/).length : 0;
        const readingTime = Math.max(1, Math.ceil(words / 200));

        const wordCountDisplay = document.getElementById('wordCountDisplay');
        const readingTimeInput = document.getElementById('readingTime');

        if (wordCountDisplay) wordCountDisplay.textContent = words + ' words';
        if (readingTimeInput) readingTimeInput.value = readingTime;
    }

    // Calculate once on load
    calculateReadingTime();

    // 7. Delete Confirmation
    document.querySelectorAll('.btn-delete-confirm').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const message = this.dataset.confirm || 'Are you sure you want to delete this item? This action cannot be undone.';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
