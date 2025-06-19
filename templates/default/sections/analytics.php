<?php
/**
 * Analytics Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$analytics = $dashboard_data->get_customer_analytics($customer);
?>

<h2 class="eddcdp-section-title"><?php _e('Purchase Analytics', 'edd-customer-dashboard-pro'); ?></h2>

<div class="eddcdp-stats-grid">
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-icon purchases">💰</div>
        <div class="eddcdp-stat-number"><?php echo $dashboard_data->format_currency($analytics['total_spent']); ?></div>
        <div class="eddcdp-stat-label"><?php _e('Total Spent', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-icon downloads">📈</div>
        <div class="eddcdp-stat-number"><?php echo $dashboard_data->format_currency($analytics['avg_per_order']); ?></div>
        <div class="eddcdp-stat-label"><?php _e('Average Order Value', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-icon licenses">📅</div>
        <div class="eddcdp-stat-number"><?php echo $analytics['purchase_count']; ?></div>
        <div class="eddcdp-stat-label"><?php _e('Total Orders', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-icon wishlist">⭐</div>
        <div class="eddcdp-stat-number">
            <?php 
            if ($analytics['first_purchase']) {
                $days = round((time() - strtotime($analytics['first_purchase'])) / DAY_IN_SECONDS);
                echo $days;
            } else {
                echo '0';
            }
            ?>
        </div>
        <div class="eddcdp-stat-label"><?php _e('Days as Customer', 'edd-customer-dashboard-pro'); ?></div>
    </div>
</div>

<div class="eddcdp-analytics-details" style="margin-top: 30px;">
    <div class="eddcdp-analytics-card" style="background: rgba(248, 250, 252, 0.8); border-radius: 12px; padding: 25px;">
        <h3><?php _e('Purchase Timeline', 'edd-customer-dashboard-pro'); ?></h3>
        <div style="margin-top: 15px;">
            <?php if ($analytics['first_purchase']) : ?>
                <p><strong><?php _e('First Purchase:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo $dashboard_data->format_date($analytics['first_purchase']); ?></p>
            <?php endif; ?>
            
            <?php if ($analytics['last_purchase']) : ?>
                <p><strong><?php _e('Most Recent Purchase:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo $dashboard_data->format_date($analytics['last_purchase']); ?></p>
            <?php endif; ?>
            
            <?php if ($analytics['purchase_count'] > 1) : ?>
                <?php 
                $days_between = 0;
                if ($analytics['first_purchase'] && $analytics['last_purchase']) {
                    $days_between = round((strtotime($analytics['last_purchase']) - strtotime($analytics['first_purchase'])) / DAY_IN_SECONDS);
                }
                ?>
                <p><strong><?php _e('Purchase Frequency:', 'edd-customer-dashboard-pro'); ?></strong> 
                    <?php 
                    if ($days_between > 0) {
                        printf(__('Every %d days on average', 'edd-customer-dashboard-pro'), round($days_between / ($analytics['purchase_count'] - 1)));
                    } else {
                        _e('Multiple purchases', 'edd-customer-dashboard-pro');
                    }
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="margin-top: 30px; padding: 40px; background: rgba(248, 250, 252, 0.8); border-radius: 12px; text-align: center;">
    <h3>📊 <?php _e('Advanced Analytics Coming Soon', 'edd-customer-dashboard-pro'); ?></h3>
    <p><?php _e('Detailed charts and insights about your purchase history will be available in future updates.', 'edd-customer-dashboard-pro'); ?></p>
</div>