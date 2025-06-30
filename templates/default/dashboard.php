<?php
/**
 * Default Dashboard Template - Updated Design
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

// Check if we're in fullscreen mode
$is_fullscreen = defined('EDDCDP_IS_FULLSCREEN') && EDDCDP_IS_FULLSCREEN;

?>

<div class="eddcdp-dashboard <?php echo $is_fullscreen ? 'eddcdp-fullscreen-content' : 'eddcdp-embedded-content'; ?>" x-data="dashboard()">
    
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>
                    <?php 
                    /* translators: %s: User display name */
                    printf(esc_html__('Welcome back, %s!', 'edd-customer-dashboard-pro'), esc_html($current_user->display_name)); 
                    ?>
                </h1>
                <p><?php esc_html_e('Manage your purchases, downloads, and account settings', 'edd-customer-dashboard-pro'); ?></p>
            </div>
            <div class="user-avatar">
                <?php echo esc_html(strtoupper(substr($current_user->display_name, 0, 2))); ?>
            </div>
        </div>
    </div>

    <!-- Stats Grid Section (only show on main dashboard) -->
    <?php if (!$order_details->is_viewing_order_page()) : ?>
        <?php include 'sections/stats.php'; ?>
    <?php endif; ?>
    
    <!-- Navigation Tabs Section (only show if not viewing order-specific pages) -->
    <?php if (!$order_details->is_viewing_order_page()) : ?>
        <div class="dashboard-nav">
            <div class="nav-tabs">
                <template x-for="tab in tabs" :key="tab.id">
                    <a href="#" 
                       @click.prevent="activeTab = tab.id"
                       :class="activeTab === tab.id ? 'nav-tab active' : 'nav-tab'"
                       x-text="tab.label">
                    </a>
                </template>
            </div>
        </div>
    <?php endif; ?>        

    <!-- Content Area -->
    <div class="dashboard-content">
        
        <?php 
        // Check what we're viewing
        if ($order_details->is_viewing_order_details()) : 
        ?>
        
        <!-- Order Details View -->
        <?php include 'sections/order-details.php'; ?>
        
        <?php elseif ($order_details->is_viewing_order_licenses()) : ?>
        
        <!-- Order Licenses View -->
        <?php include 'sections/order-licenses.php'; ?>
        
        <?php elseif (isset($_GET['eddcdp_invoice_form']) && isset($_GET['payment_id'])) : ?>

        <!-- Invoice View -->
        <?php include 'sections/invoice.php'; ?>
        
        <?php else : ?>
        
        <!-- Normal Dashboard Tabs -->
        
        <!-- Tab Content Sections -->
        <?php if (!empty($enabled_sections['purchases'])) : ?>
        <div x-show="activeTab === 'purchases'" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-4" 
             x-transition:enter-end="opacity-100 transform translate-y-0" 
             class="content-section">
            <?php include 'sections/purchases.php'; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($enabled_sections['downloads'])) : ?>
        <div x-show="activeTab === 'downloads'" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-4" 
             x-transition:enter-end="opacity-100 transform translate-y-0" 
             class="content-section">
            <?php include 'sections/downloads.php'; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($enabled_sections['licenses'])) : ?>
        <div x-show="activeTab === 'licenses'" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-4" 
             x-transition:enter-end="opacity-100 transform translate-y-0" 
             class="content-section">
            <?php include 'sections/licenses.php'; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($enabled_sections['wishlist'])) : ?>
        <div x-show="activeTab === 'wishlist'" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-4" 
             x-transition:enter-end="opacity-100 transform translate-y-0" 
             class="content-section">
            <?php include 'sections/wishlist.php'; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($enabled_sections['analytics'])) : ?>
        <div x-show="activeTab === 'analytics'" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-4" 
             x-transition:enter-end="opacity-100 transform translate-y-0" 
             class="content-section">
            <?php include 'sections/analytics.php'; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($enabled_sections['support'])) : ?>
        <div x-show="activeTab === 'support'" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-4" 
             x-transition:enter-end="opacity-100 transform translate-y-0" 
             class="content-section">
            <?php include 'sections/support.php'; ?>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
    
</div>

<!-- Dashboard JavaScript -->
<script>
// Alpine.js dashboard component
function dashboard() {
    return {
        activeTab: 'purchases',
        
        tabs: [
            <?php if (!empty($enabled_sections['purchases'])) : ?>
            { id: 'purchases', label: '📦 <?php esc_html_e('Purchases', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['downloads'])) : ?>
            { id: 'downloads', label: '⬇️ <?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['licenses'])) : ?>
            { id: 'licenses', label: '🔑 <?php esc_html_e('Licenses', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['wishlist'])) : ?>
            { id: 'wishlist', label: '❤️ <?php esc_html_e('Wishlist', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['analytics'])) : ?>
            { id: 'analytics', label: '📊 <?php esc_html_e('Analytics', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['support'])) : ?>
            { id: 'support', label: '💬 <?php esc_html_e('Support', 'edd-customer-dashboard-pro'); ?>' }
            <?php endif; ?>
        ].filter(tab => tab),
        
        // Initialize dashboard
        init() {
            this.setInitialTab();
            window.addEventListener('hashchange', () => this.handleHashChange());
            this.bindEvents();
        },
        
        // Set initial active tab from URL hash or first available
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
        
        // Bind additional events
        bindEvents() {
            // Copy license key functionality
            this.bindLicenseKeyCopy();
            
            // Download button animations
            this.bindDownloadButtons();
        },
        
        // License key copy functionality
        bindLicenseKeyCopy() {
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('license-key')) {
                    const text = e.target.textContent;
                    navigator.clipboard.writeText(text).then(() => {
                        const originalBg = e.target.style.background;
                        e.target.style.background = 'rgba(67, 233, 123, 0.2)';
                        e.target.style.transition = 'background 0.3s ease';
                        
                        setTimeout(() => {
                            e.target.style.background = originalBg;
                        }, 1000);
                    });
                }
            });
        },
        
        // Download button animations
        bindDownloadButtons() {
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-download') || 
                    (e.target.textContent && e.target.textContent.includes('Download'))) {
                    
                    const btn = e.target;
                    const originalText = btn.innerHTML;
                    
                    btn.innerHTML = '⏳ <?php esc_html_e('Preparing...', 'edd-customer-dashboard-pro'); ?>';
                    btn.disabled = true;
                    
                    setTimeout(() => {
                        btn.innerHTML = '✅ <?php esc_html_e('Downloaded', 'edd-customer-dashboard-pro'); ?>';
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }, 2000);
                    }, 1500);
                }
            });
        }
    }
}

// Global function for template sections to switch to licenses tab
function showLicensesTab() {
    const dashboardElement = document.querySelector('[x-data]');
    if (dashboardElement && dashboardElement._x_dataStack) {
        dashboardElement._x_dataStack[0].activeTab = 'licenses';
        window.location.hash = 'licenses';
    }
}

// Global functions for template compatibility
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        console.log('<?php esc_html_e('Copied to clipboard', 'edd-customer-dashboard-pro'); ?>');
    });
}
</script>

<?php include 'footer.php'; ?>