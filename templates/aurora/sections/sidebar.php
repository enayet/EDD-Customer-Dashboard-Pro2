<?php
/**
 * Aurora Sidebar Navigation Section
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

// Get settings
$settings = get_option('eddcdp_settings', array());
$enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

// Get user stats for badges
$purchase_count = $customer ? $customer->purchase_count : 0;
$download_count = eddcdp_get_customer_download_count($current_user->user_email);
$license_count = eddcdp_get_customer_active_license_count($current_user->ID);
$wishlist_count = eddcdp_get_customer_wishlist_count($current_user->ID);

// Generate user initials
$display_name = $current_user->display_name;
$initials = '';
$name_parts = explode(' ', trim($display_name));
if (count($name_parts) >= 2) {
    $initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
} else {
    $initials = strtoupper(substr($display_name, 0, 2));
}
?>

<aside class="dashboard-sidebar">
    <!-- User Profile Section -->
    <div class="user-profile">
        <div class="user-avatar"><?php echo esc_html($initials); ?></div>
        <h3 class="user-name"><?php echo esc_html($current_user->display_name); ?></h3>
        <p class="user-email"><?php echo esc_html($current_user->user_email); ?></p>
    </div>

    <!-- Sidebar Navigation -->
    <ul class="sidebar-nav">
        
        <?php if (!empty($enabled_sections['purchases'])) : ?>
        <li>
            <a href="#" class="active" data-section="products">
                <i class="fas fa-box-open"></i>
                <?php esc_html_e('My Products', 'edd-customer-dashboard-pro'); ?>
                <?php if ($purchase_count > 0) : ?>
                <span class="badge"><?php echo esc_html(number_format_i18n($purchase_count)); ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (!empty($enabled_sections['downloads'])) : ?>
        <li>
            <a href="#" data-section="downloads">
                <i class="fas fa-download"></i>
                <?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?>
                <?php if ($download_count > 0) : ?>
                <span class="badge"><?php echo esc_html(number_format_i18n($download_count)); ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (!empty($enabled_sections['licenses'])) : ?>
        <li>
            <a href="#" data-section="licenses">
                <i class="fas fa-key"></i>
                <?php esc_html_e('Licenses', 'edd-customer-dashboard-pro'); ?>
                <?php if ($license_count > 0) : ?>
                <span class="badge"><?php echo esc_html(number_format_i18n($license_count)); ?> <?php esc_html_e('active', 'edd-customer-dashboard-pro'); ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (!empty($enabled_sections['wishlist']) && function_exists('edd_wl_get_wish_list')) : ?>
        <li>
            <a href="#" data-section="wishlist">
                <i class="fas fa-heart"></i>
                <?php esc_html_e('Wishlist', 'edd-customer-dashboard-pro'); ?>
                <?php if ($wishlist_count > 0) : ?>
                <span class="badge"><?php echo esc_html(number_format_i18n($wishlist_count)); ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (!empty($enabled_sections['analytics'])) : ?>
        <li>
            <a href="#" data-section="analytics">
                <i class="fas fa-chart-line"></i>
                <?php esc_html_e('Analytics', 'edd-customer-dashboard-pro'); ?>
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="<?php echo esc_url(get_edit_user_link()); ?>">
                <i class="fas fa-cog"></i>
                <?php esc_html_e('Account Settings', 'edd-customer-dashboard-pro'); ?>
            </a>
        </li>
        
        <?php if (!empty($enabled_sections['support'])) : ?>
        <li>
            <a href="#" data-section="support">
                <i class="fas fa-question-circle"></i>
                <?php esc_html_e('Support', 'edd-customer-dashboard-pro'); ?>
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="<?php echo esc_url(wp_logout_url()); ?>">
                <i class="fas fa-sign-out-alt"></i>
                <?php esc_html_e('Logout', 'edd-customer-dashboard-pro'); ?>
            </a>
        </li>
    </ul>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar navigation functionality
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a[data-section]');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetSection = this.getAttribute('data-section');
            if (targetSection && window.AuroraDashboard) {
                window.AuroraDashboard.switchSection(targetSection);
            }
        });
    });
    
    // Handle initial section from URL hash
    const initialHash = window.location.hash.substring(1);
    if (initialHash) {
        const validSections = <?php echo json_encode(array_keys(array_filter($enabled_sections))); ?>;
        if (validSections.includes(initialHash)) {
            // Remove active from products link
            document.querySelector('.sidebar-nav a.active')?.classList.remove('active');
            // Add active to target section
            document.querySelector(`[data-section="${initialHash}"]`)?.classList.add('active');
        }
    }
});
</script>