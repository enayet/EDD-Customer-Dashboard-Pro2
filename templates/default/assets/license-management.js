/**
 * License Management JavaScript - Simplified & Fast
 */

// Global variables
let currentDeactivateUrl = '';
let currentSiteName = '';

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    bindEventListeners();
});

// Bind event listeners
function bindEventListeners() {
    // Close modal when clicking outside
    const modal = document.getElementById('deactivateModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeactivateModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeactivateModal();
        }
    });
}

// Floating toast notification
function showFloatingToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        z-index: 99999;
        transition: all 0.3s ease;
        opacity: 0;
        pointer-events: none;
    `;
    
    let bgColor, borderColor;
    switch (type) {
        case 'success':
            bgColor = 'bg-green-500';
            borderColor = 'border-green-400';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            borderColor = 'border-red-400';
            break;
        default:
            bgColor = 'bg-blue-500';
            borderColor = 'border-blue-400';
    }
    
    toast.innerHTML = `
        <div class="${bgColor} text-white px-8 py-6 rounded-2xl shadow-2xl border-2 ${borderColor} max-w-md">
            <div class="flex items-center gap-4">
                <span class="font-medium text-lg">${message}</span>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translate(-50%, -50%) scale(1)';
    }, 10);
    
    // Auto remove
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translate(-50%, -50%) scale(0.8)';
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, duration);
}

// Copy license key to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('License key copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

// Fallback copy method
function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('License key copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy license key.', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Top-right notification function (same style as licenses.php)
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm`;
    
    switch (type) {
        case 'success':
            notification.className += ' bg-green-500 text-white';
            break;
        case 'error':
            notification.className += ' bg-red-500 text-white';
            break;
        default:
            notification.className += ' bg-blue-500 text-white';
    }
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">×</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Modal functions
function showDeactivateModal(siteName, deactivateUrl) {
    currentSiteName = siteName;
    currentDeactivateUrl = deactivateUrl;
    
    document.getElementById('modalSiteName').textContent = siteName;
    const modal = document.getElementById('deactivateModal');
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        modal.querySelector('.bg-white').classList.remove('scale-95');
        modal.querySelector('.bg-white').classList.add('scale-100');
    }, 10);
    
    document.body.style.overflow = 'hidden';
}

function closeDeactivateModal() {
    const modal = document.getElementById('deactivateModal');
    if (!modal) return;
    
    const modalContent = modal.querySelector('.bg-white');
    
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}

function confirmDeactivation() {
    const confirmBtn = document.querySelector('#deactivateModal button[onclick="confirmDeactivation()"]');
    if (confirmBtn) {
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '⏳ Deactivating...';
        confirmBtn.disabled = true;
    }
    
    // Simple redirect - let EDD handle the rest
    window.location.href = currentDeactivateUrl;
}