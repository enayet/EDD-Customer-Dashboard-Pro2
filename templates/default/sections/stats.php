<?php
/**
 * Stats Grid Section - Updated Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();

// Get customer data using EDD 3.0+ methods
$customer = edd_get_customer_by('email', $current_user->user_email);
$purchase_count = $customer ? $customer->purchase_count : 0;

// Get download count using utility function
$download_count = eddcdp_get_customer_download_count($current_user->user_email);

// Get active license count using utility function
$license_count = eddcdp_get_customer_active_license_count($current_user->ID);

// Get wishlist count using utility function
$wishlist_count = eddcdp_get_customer_wishlist_count($current_user->ID);
?>

<!-- Stats Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purchases">📦</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n($purchase_count)); ?></div>
        <div class="stat-label"><?php esc_html_e('Total Purchases', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon downloads">⬇️</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n($download_count)); ?></div>
        <div class="stat-label"><?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon licenses">🔑</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n($license_count)); ?></div>
        <div class="stat-label"><?php esc_html_e('Active Licenses', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon wishlist">❤️</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n($wishlist_count)); ?></div>
        <div class="stat-label"><?php esc_html_e('Wishlist Items', 'edd-customer-dashboard-pro'); ?></div>
    </div>
</div>