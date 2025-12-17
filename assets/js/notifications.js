/**
 * Modern Notification System
 * Toast notifications and confirmation dialogs
 */

class NotificationSystem {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        const initContainer = () => {
            if (!document.getElementById('notification-container')) {
                this.container = document.createElement('div');
                this.container.id = 'notification-container';
                this.container.className = 'fixed top-4 right-4 z-[9999] space-y-2';
                document.body.appendChild(this.container);
            } else {
                this.container = document.getElementById('notification-container');
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initContainer);
        } else {
            initContainer();
        }
    }

    show(message, type = 'success', duration = 4000) {
        const toast = this.createToast(message, type);
        this.container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        }, 10);

        // Auto-remove
        const timeoutId = setTimeout(() => this.remove(toast), duration);

        // Allow manual close
        toast.dataset.timeoutId = timeoutId;
    }

    createToast(message, type) {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.className = `notification-toast transform translate-x-full transition-all duration-300 ease-out`;
        toast.innerHTML = `
            <div class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-white min-w-[300px] max-w-md ${colors[type]}">
                <i class="fas ${icons[type]} text-xl"></i>
                <p class="flex-1 font-medium">${this.escapeHtml(message)}</p>
                <button onclick="window.notificationSystem.removeToast(this.closest('.notification-toast'))" 
                    class="hover:opacity-70 transition-opacity">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        return toast;
    }

    removeToast(toast) {
        if (toast.dataset.timeoutId) {
            clearTimeout(parseInt(toast.dataset.timeoutId));
        }
        this.remove(toast);
    }

    remove(toast) {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize global notification system
if (typeof window !== 'undefined') {
    window.notificationSystem = new NotificationSystem();
}

// Helper functions for easy access
function showNotification(message, type = 'success', duration = 4000) {
    window.notificationSystem.show(message, type, duration);
}

function showSuccess(message, duration = 4000) {
    showNotification(message, 'success', duration);
}

function showError(message, duration = 5000) {
    showNotification(message, 'error', duration);
}

function showWarning(message, duration = 4000) {
    showNotification(message, 'warning', duration);
}

function showInfo(message, duration = 4000) {
    showNotification(message, 'info', duration);
}

/**
 * Confirmation Modal System
 */
function showConfirm(options) {
    return new Promise((resolve) => {
        const {
            title = 'Konfirmasi',
            message = 'Apakah Anda yakin?',
            type = 'danger',
            confirmText = 'Ya, Lanjutkan',
            cancelText = 'Batal'
        } = options;

        // Get or create modal
        let modal = document.getElementById('confirmModal');
        if (!modal) {
            modal = createConfirmModal();
            document.body.appendChild(modal);
        }

        const icon = document.getElementById('confirmIcon');
        const titleEl = document.getElementById('confirmTitle');
        const messageEl = document.getElementById('confirmMessage');
        const confirmBtn = document.getElementById('confirmButton');
        const cancelBtn = document.getElementById('cancelButton');

        // Set content
        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;

        // Set styling based on type
        const styles = {
            danger: {
                icon: 'fa-trash',
                iconBg: 'bg-red-100 text-red-600',
                btnBg: 'bg-red-600 hover:bg-red-700'
            },
            warning: {
                icon: 'fa-exclamation-triangle',
                iconBg: 'bg-yellow-100 text-yellow-600',
                btnBg: 'bg-yellow-600 hover:bg-yellow-700'
            },
            success: {
                icon: 'fa-check-circle',
                iconBg: 'bg-green-100 text-green-600',
                btnBg: 'bg-green-600 hover:bg-green-700'
            },
            info: {
                icon: 'fa-info-circle',
                iconBg: 'bg-blue-100 text-blue-600',
                btnBg: 'bg-blue-600 hover:bg-blue-700'
            }
        };

        const style = styles[type] || styles.danger;
        icon.className = `w-12 h-12 rounded-full flex items-center justify-center ${style.iconBg}`;
        icon.innerHTML = `<i class="fas ${style.icon} text-2xl"></i>`;
        confirmBtn.className = `px-6 py-2 rounded-lg font-semibold text-white transition-colors ${style.btnBg}`;

        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            modal.classList.remove('opacity-0');
            const content = document.getElementById('confirmModalContent');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);

        // Set callback
        const handleResponse = (result) => {
            // Hide modal
            modal.classList.add('opacity-0');
            const content = document.getElementById('confirmModalContent');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');

            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 200);

            resolve(result);
        };

        // Update button handlers
        confirmBtn.onclick = () => handleResponse(true);
        cancelBtn.onclick = () => handleResponse(false);

        // Close on backdrop click
        modal.onclick = (e) => {
            if (e.target === modal) {
                handleResponse(false);
            }
        };

        // Close on Escape key
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                handleResponse(false);
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    });
}

function createConfirmModal() {
    const modal = document.createElement('div');
    modal.id = 'confirmModal';
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm opacity-0 items-center justify-center z-[10000] transition-opacity duration-200';
    modal.style.display = 'none';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform scale-95 transition-transform duration-200" 
            id="confirmModalContent" onclick="event.stopPropagation()">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div id="confirmIcon" class="w-12 h-12 rounded-full flex items-center justify-center">
                        <i class="fas fa-question-circle text-2xl"></i>
                    </div>
                    <h3 id="confirmTitle" class="text-xl font-bold text-gray-800"></h3>
                </div>
                <p id="confirmMessage" class="text-gray-600 mb-6"></p>
                <div class="flex gap-3 justify-end">
                    <button id="cancelButton"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition-colors">
                        Batal
                    </button>
                    <button id="confirmButton"
                        class="px-6 py-2 rounded-lg font-semibold text-white">
                        Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    `;
    return modal;
}

// Helper functions for common confirmations
async function confirmDelete(message = 'Yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.') {
    return await showConfirm({
        title: 'Hapus Data',
        message: message,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal'
    });
}

async function confirmAction(title, message, type = 'warning') {
    return await showConfirm({
        title: title,
        message: message,
        type: type,
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal'
    });
}

async function confirmApprove(message = 'Apakah Anda yakin ingin menyetujui ini?') {
    return await showConfirm({
        title: 'Konfirmasi Persetujuan',
        message: message,
        type: 'success',
        confirmText: 'Ya, Setujui',
        cancelText: 'Batal'
    });
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.notificationSystem) {
            window.notificationSystem = new NotificationSystem();
        }
    });
} else {
    if (!window.notificationSystem) {
        window.notificationSystem = new NotificationSystem();
    }
}
