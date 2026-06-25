// AcadShare - Main JavaScript

// Toggle User Menu
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    if (menu) menu.classList.toggle('open');
}
// Close on outside click
document.addEventListener('click', function(e) {
    const menu = document.getElementById('userMenu');
    if (menu && !e.target.closest('.nav-user')) menu.classList.remove('open');
});

// Toggle Mobile Nav
function toggleMobileNav() {
    const nav = document.getElementById('mobileNav');
    if (nav) nav.classList.toggle('open');
}

// File Upload Drag & Drop
document.addEventListener('DOMContentLoaded', function () {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');

    if (uploadZone && fileInput) {
        uploadZone.addEventListener('click', () => fileInput.click());

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('drag-over');
        });

        uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                showFilePreview(files[0]);
            }
        });

        fileInput.addEventListener('change', function () {
            if (this.files.length) showFilePreview(this.files[0]);
        });

        function showFilePreview(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            const size = file.size > 1048576
                ? (file.size / 1048576).toFixed(2) + ' MB'
                : (file.size / 1024).toFixed(2) + ' KB';

            if (filePreview) {
                filePreview.textContent = '';

                const alert = document.createElement('div');
                alert.className = 'alert alert-success';

                const icon = document.createElement('i');
                icon.className = 'fa-solid fa-file-circle-check';

                const details = document.createElement('div');
                const name = document.createElement('strong');
                const meta = document.createElement('small');

                name.textContent = file.name;
                meta.textContent = `${ext.toUpperCase()} - ${size}`;

                details.append(name, document.createElement('br'), meta);
                alert.append(icon, details);
                filePreview.appendChild(alert);
            }
        }
    }

    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);

    // Confirm delete
    document.querySelectorAll('.confirm-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Star Rating UI
    const starBtns = document.querySelectorAll('.star-rate-btn');
    starBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const rating = this.dataset.rating;
            const form = document.getElementById('ratingForm');
            if (form) {
                document.getElementById('ratingInput').value = rating;
                form.submit();
            }
        });
        btn.addEventListener('mouseover', function() {
            const rating = parseInt(this.dataset.rating);
            starBtns.forEach((b, i) => {
                b.style.color = i < rating ? '#F59E0B' : '#94A3B8';
            });
        });
    });
    document.querySelector('.star-rating')?.addEventListener('mouseleave', function() {
        const current = parseInt(document.getElementById('ratingInput')?.value || 0);
        starBtns.forEach((b, i) => {
            b.style.color = i < current ? '#F59E0B' : '#94A3B8';
        });
    });

    // Tag input
    const tagInput = document.getElementById('tagInput');
    const tagsContainer = document.getElementById('tagsContainer');
    const tagsHidden = document.getElementById('tagsHidden');
    let tags = [];

    if (tagInput && tagsContainer) {
        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = this.value.trim().replace(',', '');
                if (val && !tags.includes(val) && tags.length < 8) {
                    tags.push(val);
                    renderTags();
                    this.value = '';
                }
            }
        });

        function renderTags() {
            tagsContainer.innerHTML = '';
            tags.forEach((tag, i) => {
                const span = document.createElement('span');
                span.className = 'tag';
                span.textContent = tag;

                const remove = document.createElement('i');
                remove.className = 'fa-solid fa-xmark tag-remove';
                remove.setAttribute('role', 'button');
                remove.setAttribute('aria-label', `Remove ${tag}`);
                remove.addEventListener('click', () => window.removeTag(i));

                span.appendChild(remove);
                tagsContainer.appendChild(span);
            });
            if (tagsHidden) tagsHidden.value = tags.join(',');
        }

        window.removeTag = function(idx) {
            tags.splice(idx, 1);
            renderTags();
        };
    }

    // Bookmark toggle (AJAX)
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const resourceId = this.dataset.id;
            const siteUrl = window.SITE_URL || '';
            fetch(`${siteUrl}/api.php?action=bookmark&id=${resourceId}`, { credentials: 'include' })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'added') {
                        this.classList.add('bookmarked');
                        this.querySelector('i').classList.replace('fa-regular', 'fa-solid');
                    } else if (data.status === 'removed') {
                        this.classList.remove('bookmarked');
                        this.querySelector('i').classList.replace('fa-solid', 'fa-regular');
                    }
                })
                .catch(() => {});
        });
    });
});
