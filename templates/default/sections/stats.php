<?php
/**
 * Stats Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Stats Overview -->
<div class="eddcdp-stats-grid">
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-icon purchases">📦</div>
        <div class="eddcdp-stat-number"><?php echo edd_count_purchases_of_customer($customer->id); ?></div>
        <div class="eddcdp-stat-label"><?php _e('Total Purchases', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-icon downloads">⬇️</div>
        <div class="eddcdp-stat-number"><?php echo $dashboard_data->get_download_count($customer->id); ?></div>
        <div class="eddcdp-stat-label"><?php _e('Downloads', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-icon licenses">🔑</div>
        <div class="eddcdp-stat-number"><?php echo $dashboard_data->get_active_licenses_count($customer->id); ?></div>
        <div class="eddcdp-stat-label"><?php _e('Active Licenses', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-icon wishlist">❤️</div>
        <div class="eddcdp-stat-number"><?php echo $dashboard_data->get_wishlist_count($user->ID); ?></div>
        <div class="eddcdp-stat-label"><?php _e('Wishlist Items', 'edd-customer-dashboard-pro'); ?></div>
    </div>
</div>