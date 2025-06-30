<?php
/**
 * Aurora Template Footer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in fullscreen mode
$is_fullscreen = defined('EDDCDP_IS_FULLSCREEN') && EDDCDP_IS_FULLSCREEN;

if ($is_fullscreen) {
    // Fullscreen mode - close HTML document
    ?>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
} else {
    // Embedded mode - close wrappers
    ?>
        </div> <!-- Close eddcdp-embedded-wrapper -->
    </div> <!-- Close eddcdp-dashboard-wrapper -->
    <?php
}

// Add aurora template JavaScript
$template_url = EDDCDP_PLUGIN_URL . 'templates/aurora/';
?>

<!-- Aurora Template JavaScript -->
<script>
// Aurora Dashboard Core Functions
window.AuroraDashboard = window.AuroraDashboard || {};

// Copy to clipboard functionality
AuroraDashboard.copyToClipboard = function(text, element) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            AuroraDashboard.showCopySuccess(element);
        }).catch(() => {
            AuroraDashboard.fallbackCopy(text, element);
        });
    } else {
        AuroraDashboard.fallbackCopy(text, element);
    }
};

// Fallback copy method
AuroraDashboard.fallbackCopy = function(text, element) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        AuroraDashboard.showCopySuccess(element);
    } catch (err) {
        AuroraDashboard.showNotification('<?php esc_js_e('Failed to copy to clipboard.', 'edd-customer-dashboard-pro'); ?>', 'error');
    }
    
    document.body.removeChild(textArea);
};

// Show copy success feedback
AuroraDashboard.showCopySuccess = function(element) {
    if (element) {
        const originalStyle = {
            background: element.style.background,
            color: element.style.color
        };
        
        element.style.background = 'var(--aurora-primary)';
        element.style.color = 'white';
        
        setTimeout(() => {
            element.style.background = originalStyle.background;
            element.style.color = originalStyle.color;
        }, 1000);
    }
    
    AuroraDashboard.showNotification('<?php esc_js_e('Copied to clipboard!', 'edd-customer-dashboard-pro'); ?>', 'success');
};

// Show notification
AuroraDashboard.showNotification = function(message, type) {
    const notification = document.createElement('div');
    notification.className = `aurora-notification aurora-notification-${type}`;
    notification.innerHTML = `
        <div class="aurora-notification-content">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="aurora-notification-close">&times;</button>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
        font-family: inherit;
    `;
    
    // Set colors based on type
    switch (type) {
        case 'success':
            notification.style.backgroundColor = 'var(--aurora-secondary)';
            notification.style.color = 'white';
            break;
        case 'error':
            notification.style.backgroundColor = 'var(--aurora-danger)';
            notification.style.color = 'white';
            break;
        default:
            notification.style.backgroundColor = 'var(--aurora-primary)';
            notification.style.color = 'white';
    }
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
};

// Search functionality
AuroraDashboard.initSearch = function() {
    const searchInput = document.querySelector('.search-bar input');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.products-table tbody tr, .aurora-product-card');
        
        rows.forEach(row => {
            const productName = row.querySelector('.product-name, .aurora-product-title');
            if (productName) {
                const name = productName.textContent.toLowerCase();
                row.style.display = name.includes(searchTerm) ? '' : 'none';
            }
        });
    });
};

// Initialize license key copy functionality
AuroraDashboard.initLicenseKeys = function() {
    document.querySelectorAll('.license-key').forEach(key => {
        key.addEventListener('click', function() {
            const text = this.textContent.trim();
            AuroraDashboard.copyToClipboard(text, this);
        });
    });
};

// Initialize download buttons
AuroraDashboard.initDownloadButtons = function() {
    document.querySelectorAll('.btn-download').forEach(button => {
        button.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php esc_js_e('Preparing...', 'edd-customer-dashboard-pro'); ?>';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check"></i> <?php esc_js_e('Downloaded', 'edd-customer-dashboard-pro'); ?>';
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 2000);
            }, 1500);
        });
    });
};

// Initialize all Aurora dashboard features
document.addEventListener('DOMContentLoaded', function() {
    AuroraDashboard.initSearch();
    AuroraDashboard.initLicenseKeys();
    AuroraDashboard.initDownloadButtons();
});
</script>

<!-- Aurora Animation Styles -->
<style>
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.aurora-notification-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.aurora-notification-close {
    background: none;
    border: none;
    color: inherit;
    font-size: 18px;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.8;
}

.aurora-notification-close:hover {
    opacity: 1;
}
</style>