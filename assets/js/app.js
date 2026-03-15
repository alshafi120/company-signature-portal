/**
 * Company Signature Portal - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Mobile Navigation Toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function () {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Copy to clipboard functionality
    window.copyToClipboard = function (text, btn) {
        navigator.clipboard.writeText(text).then(function () {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.style.background = '#27ae60';
            btn.style.color = '#fff';
            btn.style.borderColor = '#27ae60';

            showToast(document.documentElement.dir === 'rtl' ? 'تم النسخ!' : 'Copied!');

            setTimeout(function () {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2000);
        }).catch(function () {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);

            showToast(document.documentElement.dir === 'rtl' ? 'تم النسخ!' : 'Copied!');
        });
    };

    // Toast notification
    window.showToast = function (message) {
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = 'toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.display = 'block';

        setTimeout(function () {
            toast.style.display = 'none';
        }, 3000);
    };

    // Modal functionality
    window.openModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });

    // Confirm delete
    window.confirmDelete = function (form) {
        const message = document.documentElement.dir === 'rtl'
            ? 'هل أنت متأكد من الحذف؟'
            : 'Are you sure you want to delete?';
        return confirm(message);
    };

    // File input preview
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // View signature in modal
    window.viewSignature = function (imgSrc, name, position) {
        const modalHtml = `
            <div class="modal-overlay active" id="viewSignatureModal" onclick="if(event.target===this)closeModal('viewSignatureModal')">
                <div class="modal signature-view-modal">
                    <div class="modal-header">
                        <h2>${name}</h2>
                        <button class="modal-close" onclick="closeModal('viewSignatureModal')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="signature-large">
                            <img src="${imgSrc}" alt="${name}">
                        </div>
                        <div class="signature-details">
                            <p><strong>${document.documentElement.dir === 'rtl' ? 'الاسم' : 'Name'}:</strong> <span>${name}</span></p>
                            <p><strong>${document.documentElement.dir === 'rtl' ? 'المنصب' : 'Position'}:</strong> <span>${position}</span></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="${imgSrc}" download class="btn btn-primary btn-sm">
                            <i class="fas fa-download"></i> ${document.documentElement.dir === 'rtl' ? 'تحميل' : 'Download'}
                        </a>
                        <button class="btn btn-outline btn-sm" onclick="closeModal('viewSignatureModal')">
                            ${document.documentElement.dir === 'rtl' ? 'إغلاق' : 'Close'}
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existing = document.getElementById('viewSignatureModal');
        if (existing) existing.remove();

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        document.body.style.overflow = 'hidden';
    };
});
