<?php
/**
 * Default Dashboard Template - Enhanced Layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and settings
$current_user = wp_get_current_user();
$settings = get_option('eddcdp_settings', array());
$enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

// Include header
include 'header.php';

// Get order details instance
$order_details = EDDCDP_Order_Details::instance();

?>

<div class="eddcdp-dashboard" x-data="dashboard()">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Welcome Header Section -->
        <?php include 'sections/welcome.php'; ?>

        <!-- Stats Grid Section (only show on main dashboard) -->
        <?php if (!$order_details->is_viewing_order_page()) : ?>
            <?php include 'sections/stats.php'; ?>
        <?php endif; ?>
        
        <!-- Navigation Tabs Section (only show if not viewing order-specific pages) -->
        <?php if (!$order_details->is_viewing_order_page()) : ?>
            <?php include 'sections/navigation.php'; ?>
        <?php endif; ?>        

        <!-- Content Area -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 min-h-[600px]">
            
            <?php 
            // Check what we're viewing
            if ($order_details->is_viewing_order_details()) : 
            ?>
            
            <!-- Order Details View -->
            <div class="p-8">
                <?php include 'sections/order-details.php'; ?>
            </div>
            
            <?php elseif ($order_details->is_viewing_order_licenses()) : ?>
            
            <!-- Order Licenses View -->
            <div class="p-8">
                <?php include 'sections/order-licenses.php'; ?>
            </div>
            
            <?php else : ?>
            
            <!-- Normal Dashboard Tabs -->
            
            <!-- Tab Content Sections -->
            <?php if (!empty($enabled_sections['purchases'])) : ?>
            <div x-show="activeTab === 'purchases'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/purchases.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['downloads'])) : ?>
            <div x-show="activeTab === 'downloads'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/downloads.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['licenses'])) : ?>
            <div x-show="activeTab === 'licenses'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/licenses.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['wishlist'])) : ?>
            <div x-show="activeTab === 'wishlist'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/wishlist.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['analytics'])) : ?>
            <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/analytics.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['support'])) : ?>
            <div x-show="activeTab === 'support'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/support.php'; ?>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- Dashboard JavaScript -->
<?php include 'sections/script.php'; ?>

<script>
// Enhanced Alpine.js integration for license management
function dashboard() {
    return {
        activeTab: 'purchases',
        downloading: null,
        newSiteUrl: '',
        
        tabs: [
            <?php if (!empty($enabled_sections['purchases'])) : ?>
            { id: 'purchases', label: '📦 <?php _e('Purchases', 'eddcdp'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['downloads'])) : ?>
            { id: 'downloads', label: '⬇️ <?php _e('Downloads', 'eddcdp'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['licenses'])) : ?>
            { id: 'licenses', label: '🔑 <?php _e('Licenses', 'eddcdp'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['wishlist'])) : ?>
            { id: 'wishlist', label: '❤️ <?php _e('Wishlist', 'eddcdp'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['analytics'])) : ?>
            { id: 'analytics', label: '📊 <?php _e('Analytics', 'eddcdp'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['support'])) : ?>
            { id: 'support', label: '💬 <?php _e('Support', 'eddcdp'); ?>' }
            <?php endif; ?>
        ].filter(tab => tab), // Remove any empty entries
        
        // Initialize dashboard
        init() {
            // Set initial tab based on URL hash or first available tab
            this.setInitialTab();
            
            // Listen for hash changes
            window.addEventListener('hashchange', () => {
                this.handleHashChange();
            });
        },
        
        // Set initial active tab
        setInitialTab() {
            const hash = window.location.hash.substring(1);
            const validTabs = this.tabs.map(tab => tab.id);
            
            if (hash && validTabs.includes(hash)) {
                this.activeTab = hash;
            } else if (this.tabs.length > 0) {
                this.activeTab = this.tabs[0].id;
            }
        },
        
        // Handle hash changes for deep linking
        handleHashChange() {
            const hash = window.location.hash.substring(1);
            const validTabs = this.tabs.map(tab => tab.id);
            
            if (hash && validTabs.includes(hash)) {
                this.activeTab = hash;
            }
        },
        
        // Switch tab and update URL
        switchTab(tabId) {
            this.activeTab = tabId;
            history.pushState(null, null, '#' + tabId);
        },
        
        // Download file simulation
        downloadFile(productId) {
            this.downloading = productId;
            
            // Simulate download process
            setTimeout(() => {
                this.downloading = null;
                this.showNotification('<?php _e('Download started successfully!', 'eddcdp'); ?>', 'success');
            }, 2000);
        },
        
        // Copy license key to clipboard
        copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showNotification('<?php _e('License key copied to clipboard!', 'eddcdp'); ?>', 'success');
                }).catch(() => {
                    this.showNotification('<?php _e('Failed to copy to clipboard.', 'eddcdp'); ?>', 'error');
                });
            } else {
                // Fallback for older browsers
                this.fallbackCopyToClipboard(text);
            }
        },
        
        // Fallback copy method
        fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                this.showNotification('<?php _e('License key copied to clipboard!', 'eddcdp'); ?>', 'success');
            } catch (err) {
                this.showNotification('<?php _e('Failed to copy to clipboard.', 'eddcdp'); ?>', 'error');
            }
            
            document.body.removeChild(textArea);
        },
        
        // Activate site license
        activateSite() {
            if (!this.newSiteUrl.trim()) {
                this.showNotification('<?php _e('Please enter a site URL.', 'eddcdp'); ?>', 'error');
                return;
            }
            
            // Here you would make an AJAX call to activate the site
            console.log('Activating site:', this.newSiteUrl);
            
            // Simulate activation
            setTimeout(() => {
                this.newSiteUrl = '';
                this.showNotification('<?php _e('Site activated successfully!', 'eddcdp'); ?>', 'success');
                // You could refresh the page or update the UI here
            }, 1500);
        },
        
        // Show notification
        showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `eddcdp-notification eddcdp-notification-${type}`;
            notification.innerHTML = `
                <div class="eddcdp-notification-content">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="eddcdp-notification-close">&times;</button>
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
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                max-width: 400px;
                animation: slideInRight 0.3s ease-out;
            `;
            
            // Set background color based on type
            switch (type) {
                case 'success':
                    notification.style.backgroundColor = '#10b981';
                    notification.style.color = 'white';
                    break;
                case 'error':
                    notification.style.backgroundColor = '#ef4444';
                    notification.style.color = 'white';
                    break;
                default:
                    notification.style.backgroundColor = '#3b82f6';
                    notification.style.color = 'white';
            }
            
            // Add to page
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
    }
}

// Global functions for template use
function showLicensesTab() {
    // Use Alpine.js to switch to licenses tab
    const dashboardElement = document.querySelector('[x-data]');
    if (dashboardElement && dashboardElement._x_dataStack) {
        dashboardElement._x_dataStack[0].activeTab = 'licenses';
        history.pushState(null, null, '#licenses');
    }
}

// Handle URL hash changes for direct linking to tabs
window.addEventListener('load', function() {
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
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
        
        .eddcdp-notification-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        
        .eddcdp-notification-close {
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
        }
        
        .eddcdp-notification-close:hover {
            opacity: 0.7;
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php
// Include footer
include 'footer.php';
?>