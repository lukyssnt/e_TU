/**
 * App.js - Common JavaScript Functions
 * E-ADMIN TU MA AL IHSAN
 */

// Modal Management
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Close modal on overlay click
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeModal(e.target.id);
    }
});

// Advanced Toast Notification System
function showToast(message, type = 'success', duration = 4000) {
    // Create toast container if it doesn't exist
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 10px;';
        document.body.appendChild(container);
    }

    // Toast configuration based on type
    const config = {
        success: {
            icon: 'fa-check-circle',
            bgColor: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
            iconColor: '#fff'
        },
        error: {
            icon: 'fa-times-circle',
            bgColor: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
            iconColor: '#fff'
        },
        warning: {
            icon: 'fa-exclamation-triangle',
            bgColor: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
            iconColor: '#fff'
        },
        info: {
            icon: 'fa-info-circle',
            bgColor: 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
            iconColor: '#fff'
        }
    };

    const settings = config[type] || config.success;

    // Create toast element
    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${settings.bgColor};
        color: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        min-width: 300px;
        max-width: 400px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 500;
        transform: translateX(400px);
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        opacity: 0;
        cursor: pointer;
    `;

    toast.innerHTML = `
        <i class="fas ${settings.icon}" style="font-size: 20px; color: ${settings.iconColor};"></i>
        <span style="flex: 1;">${message}</span>
        <i class="fas fa-times" style="font-size: 14px; opacity: 0.7; cursor: pointer;"></i>
    `;

    // Add to container
    container.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    }, 10);

    // Auto remove
    const removeToast = () => {
        toast.style.transform = 'translateX(400px)';
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 400);
    };

    // Click to close
    toast.addEventListener('click', removeToast);

    // Auto close after duration
    setTimeout(removeToast, duration);
}

// Custom Confirm Dialog (replaces browser confirm)
async function confirmAction(message = 'Apakah Anda yakin?', title = 'Konfirmasi') {
    return new Promise((resolve) => {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 99998;
            display: flex;
            align-items: center;
            justify-center;
            animation: fadeIn 0.2s ease;
        `;

        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white;
            border-radius: 16px;
            padding: 24px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
        `;

        modal.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-center;">
                    <i class="fas fa-exclamation-triangle" style="color: white; font-size: 24px;"></i>
                </div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #1f2937;">${title}</h3>
            </div>
            <p style="color: #6b7280; margin: 0 0 24px 0; font-size: 15px; line-height: 1.6;">${message}</p>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button id="cancelBtn" style="padding: 10px 20px; border: 2px solid #e5e7eb; background: white; color: #6b7280; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    Batal
                </button>
                <button id="confirmBtn" style="padding: 10px 20px; border: none; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                    Ya, Lanjutkan
                </button>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Add animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);

        // Handle buttons
        const confirmBtn = modal.querySelector('#confirmBtn');
        const cancelBtn = modal.querySelector('#cancelBtn');

        const cleanup = () => {
            overlay.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(overlay);
                document.head.removeChild(style);
            }, 200);
        };

        confirmBtn.addEventListener('click', () => {
            cleanup();
            resolve(true);
        });

        cancelBtn.addEventListener('click', () => {
            cleanup();
            resolve(false);
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                cleanup();
                resolve(false);
            }
        });
    });
}

// Legacy support - replace old confirmDelete
async function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
    return await confirmAction(message, 'Konfirmasi Hapus');
}

// AJAX Helper
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        return { success: false, message: 'Network error' };
    }
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('border-red-500');
            isValid = false;
        } else {
            input.classList.remove('border-red-500');
        }
    });

    return isValid;
}

// Reset Form
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        // Remove validation classes
        form.querySelectorAll('.border-red-500').forEach(input => {
            input.classList.remove('border-red-500');
        });
    }
}

// Format Currency (Rupiah)
function formatRupiah(angka) {
    if (!angka) return 'Rp 0';
    const number = parseInt(angka.toString().replace(/[^0-9]/g, ''));
    return 'Rp ' + number.toLocaleString('id-ID');
}

// Format Date (Indonesia)
function formatTanggal(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}

// Export Table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) {
        showToast('Tabel tidak ditemukan!', 'error');
        return;
    }

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            csvRow.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv.push(csvRow.join(';'));
    });

    // Download
    const csvContent = '\ufeff' + csv.join('\n'); // BOM for Excel UTF-8
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// Print Element
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        showToast('Element tidak ditemukan!', 'error');
        return;
    }

    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link rel="stylesheet" href="/e-TU/assets/css/custom.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
