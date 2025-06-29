<?php
/**
 * Default Dashboard Template - Cleaned Layout
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
            
            
            <?php elseif (isset($_GET['eddcdp_invoice_form']) && isset($_GET['payment_id'])) : ?>

            <!-- Invoice View -->
            <div class="p-8">
                <?php include 'sections/invoice.php'; ?>
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
// Simplified Alpine.js dashboard component
function dashboard() {
    return {
        activeTab: 'purchases',
        
        tabs: [
            <?php if (!empty($enabled_sections['purchases'])) : ?>
            { id: 'purchases', label: '📦 <?php _e('Purchases', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['downloads'])) : ?>
            { id: 'downloads', label: '⬇️ <?php _e('Downloads', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['licenses'])) : ?>
            { id: 'licenses', label: '🔑 <?php _e('Licenses', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['wishlist'])) : ?>
            { id: 'wishlist', label: '❤️ <?php _e('Wishlist', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['analytics'])) : ?>
            { id: 'analytics', label: '📊 <?php _e('Reports', 'edd-customer-dashboard-pro'); ?>' },
            <?php endif; ?>
            <?php if (!empty($enabled_sections['support'])) : ?>
            { id: 'support', label: '💬 <?php _e('Support', 'edd-customer-dashboard-pro'); ?>' }
            <?php endif; ?>
        ].filter(tab => tab),
        
        // Initialize dashboard
        init() {
            this.setInitialTab();
            window.addEventListener('hashchange', () => this.handleHashChange());
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
</script>

<?php
add_action('wp_footer', function() {
    if (current_user_can('manage_options')) {
        $invoice_class = EDDCDP_Invoice_Redirect::instance();
        $url = $invoice_class->find_dashboard_page(); // Make this method public temporarily
        echo '<script>console.log("Debug: Dashboard URL = ' . $url . '");</script>';
    }
});
?>

<?php include 'footer.php'; ?>