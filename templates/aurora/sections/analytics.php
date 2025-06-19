<?php
/**
 * Aurora Template - Analytics Section
 */

if (!defined('ABSPATH')) {
    exit;
}

$analytics = $dashboard_data->get_customer_analytics($customer);
?>

<h2 class="eddcdp-section-title"><?php esc_html_e('Purchase Analytics', 'edd-customer-dashboard-pro'); ?></h2>

<div class="eddcdp-stats-grid" style="grid-column: 1; margin-bottom: 30px;">
    <div class="eddcdp-stat-card purchases">
        <div class="eddcdp-stat-number"><?php echo esc_html($dashboard_data->format_currency($analytics['total_spent'])); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-dollar-sign"></i>
            <?php esc_html_e('Total Spent', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    
    <div class="eddcdp-stat-card downloads">
        <div class="eddcdp-stat-number"><?php echo esc_html($dashboard_data->format_currency($analytics['avg_per_order'])); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-chart-line"></i>
            <?php esc_html_e('Average Order Value', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    
    <div class="eddcdp-stat-card licenses">
        <div class="eddcdp-stat-number"><?php echo esc_html($analytics['purchase_count']); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-shopping-bag"></i>
            <?php esc_html_e('Total Orders', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    
    <div class="eddcdp-stat-card wishlist">
        <div class="eddcdp-stat-number">
            <?php 
            if ($analytics['first_purchase']) {
                $days = round((time() - strtotime($analytics['first_purchase'])) / DAY_IN_SECONDS);
                echo esc_html($days);
            } else {
                echo '0';
            }
            ?>
        </div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-calendar-alt"></i>
            <?php esc_html_e('Days as Customer', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>

<div class="eddcdp-analytics-details">
    <div class="eddcdp-purchase-item" style="background: white; padding: 25px;">
        <h3 style="margin-bottom: 20px; color: var(--dark); display: flex; align-items: center;">
            <i class="fas fa-chart-pie" style="margin-right: 10px; color: var(--primary);"></i>
            <?php esc_html_e('Purchase Timeline', 'edd-customer-dashboard-pro'); ?>
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <?php if ($analytics['first_purchase']) : ?>
                <div style="padding: 15px; background: var(--light); border-radius: 8px; border-left: 4px solid var(--primary);">
                    <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 5px;">
                        <i class="fas fa-play" style="margin-right: 5px;"></i>
                        <?php esc_html_e('First Purchase', 'edd-customer-dashboard-pro'); ?>
                    </div>
                    <div style="font-weight: 600; color: var(--dark);">
                        <?php echo esc_html($dashboard_data->format_date($analytics['first_purchase'])); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($analytics['last_purchase']) : ?>
                <div style="padding: 15px; background: var(--light); border-radius: 8px; border-left: 4px solid var(--secondary);">
                    <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 5px;">
                        <i class="fas fa-clock" style="margin-right: 5px;"></i>
                        <?php esc_html_e('Most Recent Purchase', 'edd-customer-dashboard-pro'); ?>
                    </div>
                    <div style="font-weight: 600; color: var(--dark);">
                        <?php echo esc_html($dashboard_data->format_date($analytics['last_purchase'])); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($analytics['purchase_count'] > 1) : ?>
                <?php 
                $days_between = 0;
                if ($analytics['first_purchase'] && $analytics['last_purchase']) {
                    $days_between = round((strtotime($analytics['last_purchase']) - strtotime($analytics['first_purchase'])) / DAY_IN_SECONDS);
                }
                ?>
                <div style="padding: 15px; background: var(--light); border-radius: 8px; border-left: 4px solid #e84393;">
                    <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 5px;">
                        <i class="fas fa-sync-alt" style="margin-right: 5px;"></i>
                        <?php esc_html_e('Purchase Frequency', 'edd-customer-dashboard-pro'); ?>
                    </div>
                    <div style="font-weight: 600; color: var(--dark);">
                        <?php 
                        if ($days_between > 0) {
                            // translators: %d is the average number of days between purchases
                            printf(esc_html__('Every %d days', 'edd-customer-dashboard-pro'), esc_html(round($days_between / ($analytics['purchase_count'] - 1))));
                        } else {
                            esc_html_e('Multiple purchases', 'edd-customer-dashboard-pro');
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="eddcdp-empty-state" style="background: white; margin-top: 30px; padding: 40px; border-radius: 12px;">
    <div class="eddcdp-empty-icon">
        <i class="fas fa-chart-pie"></i>
    </div>
    <h3><?php esc_html_e('Advanced Analytics Coming Soon', 'edd-customer-dashboard-pro'); ?></h3>
    <p><?php esc_html_e('We\'re working on detailed charts and insights about your purchase history.', 'edd-customer-dashboard-pro'); ?></p>
</div>