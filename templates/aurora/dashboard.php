<?php
/**
 * Aurora Dashboard Template - Main Layout
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

<div class="eddcdp-aurora-dashboard <?php echo $is_fullscreen ? 'eddcdp-fullscreen-content' : 'eddcdp-embedded-content'; ?>">
    <div class="dashboard-container">
        
        <!-- Sidebar Navigation -->
        <?php if (!$order_details->is_viewing_order_page()) : ?>
            <?php include 'sections/sidebar.php'; ?>
        <?php endif; ?>

        <!-- Main Content Area -->
        <main class="dashboard-main">
            
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
            
            <!-- Normal Dashboard Content -->
            <div id="aurora-dashboard-content">
                
                <!-- Products Section (Default) -->
                <?php if (!empty($enabled_sections['purchases'])) : ?>
                <div class="content-section active" data-section="products">
                    <?php include 'sections/products.php'; ?>
                </div>
                <?php endif; ?>

                <!-- Downloads Section -->
                <?php if (!empty($enabled_sections['downloads'])) : ?>
                <div class="content-section" data-section="downloads">
                    <?php include 'sections/downloads.php'; ?>
                </div>
                <?php endif; ?>

                <!-- Licenses Section -->
                <?php if (!empty($enabled_sections['licenses'])) : ?>
                <div class="content-section" data-section="licenses">
                    <?php include 'sections/licenses.php'; ?>
                </div>
                <?php endif; ?>

                <!-- Wishlist Section -->
                <?php if (!empty($enabled_sections['wishlist'])) : ?>
                <div class="content-section" data-section="wishlist">
                    <?php include 'sections/wishlist.php'; ?>
                </div>
                <?php endif; ?>

                <!-- Analytics Section -->
                <?php if (!empty($enabled_sections['analytics'])) : ?>
                <div class="content-section" data-section="analytics">
                    <?php include 'sections/analytics.php'; ?>
                </div>
                <?php endif; ?>

                <!-- Support Section -->
                <?php if (!empty($enabled_sections['support'])) : ?>
                <div class="content-section" data-section="support">
                    <?php include 'sections/support.php'; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php endif; ?>
        </main>
        
    </div>
</div>

<!-- Dashboard JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Aurora Dashboard
    window.AuroraDashboard = {
        currentSection: 'products',
        
        // Switch sections
        switchSection: function(sectionId) {
            // Remove active class from all nav links and sections
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Add active class to clicked link and corresponding section
            const navLink = document.querySelector(`[data-section="${sectionId}"]`);
            const section = document.querySelector(`.content-section[data-section="${sectionId}"]`);
            
            if (navLink) navLink.classList.add('active');
            if (section) section.classList.add('active');
            
            this.currentSection = sectionId;
            
            // Update URL hash
            window.location.hash = sectionId;
        },
        
        // Initialize from URL hash
        init: function() {
            const hash = window.location.hash.substring(1);
            if (hash && document.querySelector(`.content-section[data-section="${hash}"]`)) {
                this.switchSection(hash);
            }
            
            // Handle hash changes
            window.addEventListener('hashchange', () => {
                const newHash = window.location.hash.substring(1);
                if (newHash && document.querySelector(`.content-section[data-section="${newHash}"]`)) {
                    this.switchSection(newHash);
                }
            });
        }
    };
    
    // Initialize dashboard
    window.AuroraDashboard.init();
});
</script>

<?php include 'footer.php'; ?>