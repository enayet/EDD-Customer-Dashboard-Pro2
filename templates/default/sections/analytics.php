<?php
/**
 * Analytics Section Template - Updated Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and analytics data
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    ?>
    <div class="empty-state">
        <div class="empty-icon">⚠️</div>
        <h3><?php esc_html_e('Customer Data Not Found', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('Unable to retrieve your customer information.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

// Calculate analytics using EDD 3.0+ methods
$total_spent = $customer->purchase_value;
$total_purchases = $customer->purchase_count;

// Get download count using EDD 3.0+ method
$download_logs = edd_get_file_download_logs(array(
    'customer' => $current_user->user_email,
    'number' => 999999,
    'fields' => 'ids'
));
$total_downloads = is_array($download_logs) ? count($download_logs) : 0;

$avg_downloads_per_product = $total_purchases > 0 ? round($total_downloads / $total_purchases, 1) : 0;
$avg_order_value = $total_purchases > 0 ? $total_spent / $total_purchases : 0;

// Get purchase history using EDD 3.0+ orders
$orders = edd_get_orders(array(
    'customer' => $customer->id,
    'number' => 999999,
    'status' => array('complete')
));

$monthly_spending = array();
$yearly_spending = array();

if ($orders) {
    foreach ($orders as $order) {
        $date = gmdate('Y-m', strtotime($order->date_created));
        $year = gmdate('Y', strtotime($order->date_created));
        
        if (!isset($monthly_spending[$date])) {
            $monthly_spending[$date] = 0;
        }
        if (!isset($yearly_spending[$year])) {
            $yearly_spending[$year] = 0;
        }
        
        $monthly_spending[$date] += $order->total;
        $yearly_spending[$year] += $order->total;
    }
}

// Sort by date
ksort($monthly_spending);
ksort($yearly_spending);

// Calculate days active
$first_order = null;
if ($orders) {
    $sorted_orders = $orders;
    usort($sorted_orders, function($a, $b) {
        return strtotime($a->date_created) - strtotime($b->date_created);
    });
    $first_order = reset($sorted_orders);
}
$days_active = 0;
if ($first_order) {
    $days_active = ceil((time() - strtotime($first_order->date_created)) / (60 * 60 * 24));
}
?>

<h2 class="section-title"><?php esc_html_e('Purchase Analytics', 'edd-customer-dashboard-pro'); ?></h2>

<!-- Primary Analytics Cards -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">💰</div>
        <div class="stat-number"><?php echo esc_html(edd_currency_filter(edd_format_amount($total_spent))); ?></div>
        <div class="stat-label"><?php esc_html_e('Total Spent', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">📈</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n($avg_downloads_per_product, 1)); ?></div>
        <div class="stat-label"><?php esc_html_e('Avg Downloads/Product', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">💳</div>
        <div class="stat-number"><?php echo esc_html(edd_currency_filter(edd_format_amount($avg_order_value))); ?></div>
        <div class="stat-label"><?php esc_html_e('Average Order Value', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">📅</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n($days_active)); ?></div>
        <div class="stat-label"><?php esc_html_e('Days Active', 'edd-customer-dashboard-pro'); ?></div>
    </div>
</div>

<?php if (!empty($monthly_spending) || !empty($yearly_spending)) : ?>
<!-- Spending Overview -->
<div class="purchase-item" style="margin-bottom: 30px;">
    <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;"><?php esc_html_e('Spending Overview', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
        <!-- Recent Monthly Spending -->
        <?php if (!empty($monthly_spending)) : ?>
        <div>
            <h4 style="color: var(--dark); margin-bottom: 15px; font-size: 1.1rem;"><?php esc_html_e('Recent Monthly Spending', 'edd-customer-dashboard-pro'); ?></h4>
            <div style="display: grid; gap: 8px;">
                <?php 
                $recent_months = array_slice($monthly_spending, -6, 6, true);
                foreach ($recent_months as $month => $amount) :
                    $month_name = date_i18n('F Y', strtotime($month . '-01'));
                    $percentage = $total_spent > 0 ? ($amount / $total_spent) * 100 : 0;
                ?>
                <div style="background: rgba(255, 255, 255, 0.8); border-radius: 8px; padding: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <span style="font-weight: 600; color: var(--dark);"><?php echo esc_html($month_name); ?></span>
                        <span style="font-weight: 700; color: var(--primary);"><?php echo esc_html(edd_currency_filter(edd_format_amount($amount))); ?></span>
                    </div>
                    <div style="background: var(--gray-light); height: 4px; border-radius: 2px; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, var(--primary), var(--secondary)); height: 100%; width: <?php echo esc_attr(min($percentage, 100)); ?>%; transition: width 0.3s ease;"></div>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--gray); margin-top: 2px;">
                        <?php echo esc_html(number_format($percentage, 1)); ?>% <?php esc_html_e('of total', 'edd-customer-dashboard-pro'); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Yearly Spending -->
        <?php if (!empty($yearly_spending)) : ?>
        <div>
            <h4 style="color: var(--dark); margin-bottom: 15px; font-size: 1.1rem;"><?php esc_html_e('Yearly Totals', 'edd-customer-dashboard-pro'); ?></h4>
            <div style="display: grid; gap: 8px;">
                <?php foreach ($yearly_spending as $year => $amount) : 
                    $percentage = $total_spent > 0 ? ($amount / $total_spent) * 100 : 0;
                ?>
                <div style="background: rgba(255, 255, 255, 0.8); border-radius: 8px; padding: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <span style="font-weight: 600; color: var(--dark);"><?php echo esc_html($year); ?></span>
                        <span style="font-weight: 700; color: var(--success);"><?php echo esc_html(edd_currency_filter(edd_format_amount($amount))); ?></span>
                    </div>
                    <div style="background: var(--gray-light); height: 4px; border-radius: 2px; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, var(--success), var(--success-light)); height: 100%; width: <?php echo esc_attr(min($percentage, 100)); ?>%; transition: width 0.3s ease;"></div>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--gray); margin-top: 2px;">
                        <?php echo esc_html(number_format($percentage, 1)); ?>% <?php esc_html_e('of total', 'edd-customer-dashboard-pro'); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Quick Stats Summary -->
<div class="purchase-item">
    <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;"><?php esc_html_e('Quick Stats Summary', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
        <div style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 5px;">
                <?php echo esc_html(number_format_i18n($total_purchases)); ?>
            </div>
            <div style="color: var(--gray); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <?php esc_html_e('Customer Since', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
    </div>
    
    <div style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--success); margin-bottom: 5px;">
            <?php echo esc_html(number_format_i18n($total_downloads)); ?>
        </div>
        <div style="color: var(--gray); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <?php esc_html_e('Total Downloads', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>

    <div style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--warning); margin-bottom: 5px;">
            <?php 
            if ($orders) {
                $products = array();
                foreach ($orders as $order) {
                    $order_items = $order->get_items();
                    foreach ($order_items as $item) {
                        $products[$item->product_id] = true;
                    }
                }
                echo esc_html(number_format_i18n(count($products)));
            } else {
                echo '0';
            }
            ?>
        </div>
        <div style="color: var(--gray); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <?php esc_html_e('Unique Products', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>    
    <div style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--danger); margin-bottom: 5px;">
            <?php 
            if ($first_order) {
                echo esc_html(date_i18n('Y', strtotime($first_order->date_created)));
            } else {
                echo '—';
            }
            ?>
        </div>
        <div style="color: var(--gray); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <?php  esc_html_e('Total Orders', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>   

</div>

<?php if ($total_purchases > 0) : ?>
<!-- Purchase Behavior Insights -->
<div class="purchase-item" style="margin-top: 30px;">
    <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">📊 <?php esc_html_e('Purchase Insights', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div style="display: grid; gap: 15px;">
        <!-- Download Efficiency -->
        <div style="background: rgba(67, 233, 123, 0.05); border-left: 4px solid var(--success); padding: 15px; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                <span style="font-size: 1.2rem;">⬇️</span>
                <strong style="color: var(--dark);"><?php esc_html_e('Download Activity', 'edd-customer-dashboard-pro'); ?></strong>
            </div>
            <p style="color: var(--gray); margin: 0; font-size: 0.9rem;">
                <?php 
                if ($avg_downloads_per_product >= 2) {
                    esc_html_e('You frequently re-download your products, showing great engagement with your purchases.', 'edd-customer-dashboard-pro');
                } elseif ($avg_downloads_per_product >= 1) {
                    esc_html_e('You regularly download your purchased products.', 'edd-customer-dashboard-pro');
                } else {
                    esc_html_e('Consider downloading your purchased products to get the most value from them.', 'edd-customer-dashboard-pro');
                }
                ?>
            </p>
        </div>
        
        <!-- Spending Pattern -->
        <div style="background: rgba(102, 126, 234, 0.05); border-left: 4px solid var(--primary); padding: 15px; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                <span style="font-size: 1.2rem;">💰</span>
                <strong style="color: var(--dark);"><?php esc_html_e('Spending Pattern', 'edd-customer-dashboard-pro'); ?></strong>
            </div>
            <p style="color: var(--gray); margin: 0; font-size: 0.9rem;">
                <?php 
                if ($avg_order_value > 100) {
                    esc_html_e('You typically make high-value purchases, showing confidence in premium products.', 'edd-customer-dashboard-pro');
                } elseif ($avg_order_value > 50) {
                    esc_html_e('Your average order value shows you appreciate quality digital products.', 'edd-customer-dashboard-pro');
                } else {
                    esc_html_e('You prefer affordable options and smart purchasing decisions.', 'edd-customer-dashboard-pro');
                }
                ?>
            </p>
        </div>
        
        <!-- Customer Loyalty -->
        <?php if ($days_active > 0) : ?>
        <div style="background: rgba(245, 87, 108, 0.05); border-left: 4px solid var(--danger); padding: 15px; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                <span style="font-size: 1.2rem;">❤️</span>
                <strong style="color: var(--dark);"><?php esc_html_e('Customer Loyalty', 'edd-customer-dashboard-pro'); ?></strong>
            </div>
            <p style="color: var(--gray); margin: 0; font-size: 0.9rem;">
                <?php 
                if ($days_active > 365) {
                    /* translators: %d: Number of years as customer */
                    printf(esc_html__('You\'ve been a loyal customer for over %d year(s). Thank you for your continued trust!', 'edd-customer-dashboard-pro'), floor($days_active / 365));
                } elseif ($days_active > 90) {
                    esc_html_e('You\'re becoming a valued long-term customer. We appreciate your business!', 'edd-customer-dashboard-pro');
                } else {
                    esc_html_e('Welcome to our community! We\'re excited to have you as a new customer.', 'edd-customer-dashboard-pro');
                }
                ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php else : ?>
<!-- No Analytics Available -->
<div class="empty-state">
    <div class="empty-icon">📊</div>
    <h3><?php esc_html_e('No Analytics Data Yet', 'edd-customer-dashboard-pro'); ?></h3>
    <p><?php esc_html_e('Make your first purchase to see detailed analytics about your buying patterns and download activity.', 'edd-customer-dashboard-pro'); ?></p>
    <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" 
            class="btn">
        🛒 <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
    </button>
</div>
<?php endif; ?>

<!-- Future Analytics Teaser -->
<div style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)); border-radius: 12px; padding: 25px; margin-top: 30px; text-align: center; border: 1px solid rgba(102, 126, 234, 0.2);">
    <h3 style="color: var(--primary); margin-bottom: 10px; font-size: 1.2rem;">🚀 <?php esc_html_e('Coming Soon', 'edd-customer-dashboard-pro'); ?></h3>
    <p style="color: var(--gray); margin-bottom: 15px;">
        <?php esc_html_e('We\'re working on advanced analytics features including purchase trends, product recommendations, and detailed spending charts.', 'edd-customer-dashboard-pro'); ?>
    </p>
    <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
        <div style="display: flex; align-items: center; gap: 5px; color: var(--gray); font-size: 0.9rem;">
            <span>📈</span> <?php esc_html_e('Interactive Charts', 'edd-customer-dashboard-pro'); ?>
        </div>
        <div style="display: flex; align-items: center; gap: 5px; color: var(--gray); font-size: 0.9rem;">
            <span>🎯</span> <?php esc_html_e('Product Recommendations', 'edd-customer-dashboard-pro'); ?>
        </div>
        <div style="display: flex; align-items: center; gap: 5px; color: var(--gray); font-size: 0.9rem;">
            <span>📊</span> <?php esc_html_e('Detailed Reports', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>

<script>
// Analytics interactions
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('[style*="width:"]');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 300);
    });
    
    // Add hover effects to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>
        

        
