<?php
/**
 * Aurora Template - Stats Section
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Stats Overview -->
<div class="eddcdp-stats-grid">
    <div class="eddcdp-stat-card purchases">
        <div class="eddcdp-stat-number"><?php echo esc_html(edd_count_purchases_of_customer($customer->id)); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-box-open"></i>
            <?php esc_html_e('Total Purchases', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    
    <div class="eddcdp-stat-card downloads">
        <div class="eddcdp-stat-number"><?php echo esc_html($dashboard_data->get_download_count($customer->id)); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-download"></i>
            <?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    
    <div class="eddcdp-stat-card licenses">
        <div class="eddcdp-stat-number"><?php echo esc_html($dashboard_data->get_active_licenses_count($customer->id)); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-key"></i>
            <?php esc_html_e('Active Licenses', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    
    <div class="eddcdp-stat-card wishlist">
        <div class="eddcdp-stat-number"><?php echo esc_html($dashboard_data->get_wishlist_count($user->ID)); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-heart"></i>
            <?php esc_html_e('Wishlist Items', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>