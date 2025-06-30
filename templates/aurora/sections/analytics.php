<?php
/**
 * Aurora Analytics Section Template - EDD 3.0+ Compatible
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and analytics data
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    echo '<div class="empty-state">';
    echo '<div class="empty-icon"><i class="fas fa-user-times"></i></div>';
    echo '<h3 class="empty-title">' . esc_html__('Customer data not found.', 'edd-customer-dashboard-pro') . '</h3>';
    echo '</div>';
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
$category_spending = array();

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
        
        // Category analysis
        $order_items = $order->get_items();
        foreach ($order_items as $item) {
            $download_id = $item->product_id;
            $categories = get_the_terms($download_id, 'download_category');
            if ($categories && !is_wp_error($categories)) {
                foreach ($categories as $category) {
                    if (!isset($category_spending[$category->name])) {
                        $category_spending[$category->name] = 0;
                    }
                    $category_spending[$category->name] += $item->total;
                }
            } else {
                // Uncategorized
                if (!isset($category_spending['Uncategorized'])) {
                    $category_spending['Uncategorized'] = 0;
                }
                $category_spending['Uncategorized'] += $item->total;
            }
        }
    }
}

// Sort by date
ksort($monthly_spending);
ksort($yearly_spending);
arsort($category_spending);

// Calculate days active
$first_order = null;
if ($orders) {
    $first_order = end($orders);
}
$days_active = 0;
if ($first_order) {
    $days_active = ceil((time() - strtotime($first_order->date_created)) / (60 * 60 * 24));
}

// Get recent activity
$recent_downloads = edd_get_file_download_logs(array(
    'customer' => $current_user->user_email,
    'number' => 5,
    'orderby' => 'date_created',
    'order' => 'DESC'
));
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1 class="dashboard-title"><?php esc_html_e('Purchase Analytics', 'edd-customer-dashboard-pro'); ?></h1>
    <div style="display: flex; gap: 10px;">
        <select id="analyticsTimeframe" style="padding: 8px 12px; border: 1px solid var(--aurora-gray-light); border-radius: 6px; background: white;">
            <option value="all"><?php esc_html_e('All Time', 'edd-customer-dashboard-pro'); ?></option>
            <option value="year"><?php esc_html_e('This Year', 'edd-customer-dashboard-pro'); ?></option>
            <option value="month"><?php esc_html_e('This Month', 'edd-customer-dashboard-pro'); ?></option>
        </select>
    </div>
</div>

<!-- Key Metrics -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card" style="border-left-color: var(--aurora-secondary);">
        <div class="stat-value"><?php echo esc_html(edd_currency_filter(edd_format_amount($total_spent))); ?></div>
        <div class="stat-label">
            <i class="fas fa-dollar-sign"></i>
            <?php esc_html_e('Total Spent', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: #3498db;">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($avg_downloads_per_product, 1)); ?></div>
        <div class="stat-label">
            <i class="fas fa-chart-line"></i>
            <?php esc_html_e('Avg Downloads/Product', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: #e67e22;">
        <div class="stat-value"><?php echo esc_html(edd_currency_filter(edd_format_amount($avg_order_value))); ?></div>
        <div class="stat-label">
            <i class="fas fa-shopping-cart"></i>
            <?php esc_html_e('Avg Order Value', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: #9b59b6;">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($days_active)); ?></div>
        <div class="stat-label">
            <i class="fas fa-calendar-alt"></i>
            <?php esc_html_e('Days Active', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>

<?php if (!empty($orders)) : ?>
<!-- Analytics Charts and Data -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
    
    <!-- Spending Over Time -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light);">
        <h3 style="margin: 0 0 20px 0; color: var(--aurora-dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-chart-area" style="color: var(--aurora-primary);"></i>
            <?php esc_html_e('Monthly Spending', 'edd-customer-dashboard-pro'); ?>
        </h3>
        
        <div style="max-height: 200px; overflow-y: auto;">
            <?php 
            $recent_months = array_slice($monthly_spending, -6, 6, true);
            $max_amount = $recent_months ? max($recent_months) : 1;
            
            foreach ($recent_months as $month => $amount) :
                $month_name = date_i18n('F Y', strtotime($month . '-01'));
                $percentage = ($amount / $max_amount) * 100;
            ?>
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <span style="font-size: 0.9rem; font-weight: 500; color: var(--aurora-dark);"><?php echo esc_html($month_name); ?></span>
                    <span style="font-size: 0.9rem; font-weight: 600; color: var(--aurora-primary);"><?php echo esc_html(edd_currency_filter(edd_format_amount($amount))); ?></span>
                </div>
                <div style="background: var(--aurora-light); border-radius: 10px; height: 8px; overflow: hidden;">
                    <div style="background: linear-gradient(90deg, var(--aurora-primary), var(--aurora-primary-light)); height: 100%; width: <?php echo esc_attr($percentage); ?>%; border-radius: 10px; transition: width 0.3s ease;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Category Breakdown -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light);">
        <h3 style="margin: 0 0 20px 0; color: var(--aurora-dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-chart-pie" style="color: var(--aurora-secondary);"></i>
            <?php esc_html_e('Spending by Category', 'edd-customer-dashboard-pro'); ?>
        </h3>
        
        <div style="max-height: 200px; overflow-y: auto;">
            <?php 
            $category_colors = array('#6c5ce7', '#00b894', '#e67e22', '#e84393', '#0984e3', '#a29bfe');
            $color_index = 0;
            $max_category_amount = $category_spending ? max($category_spending) : 1;
            
            foreach (array_slice($category_spending, 0, 6, true) as $category => $amount) :
                $percentage = ($amount / $max_category_amount) * 100;
                $color = $category_colors[$color_index % count($category_colors)];
                $color_index++;
            ?>
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <span style="font-size: 0.9rem; font-weight: 500; color: var(--aurora-dark); display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: <?php echo esc_attr($color); ?>;"></div>
                        <?php echo esc_html($category); ?>
                    </span>
                    <span style="font-size: 0.9rem; font-weight: 600; color: var(--aurora-secondary);"><?php echo esc_html(edd_currency_filter(edd_format_amount($amount))); ?></span>
                </div>
                <div style="background: var(--aurora-light); border-radius: 10px; height: 8px; overflow: hidden;">
                    <div style="background: <?php echo esc_attr($color); ?>; height: 100%; width: <?php echo esc_attr($percentage); ?>%; border-radius: 10px; transition: width 0.3s ease;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Yearly Summary -->
<?php if (!empty($yearly_spending)) : ?>
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light); margin-bottom: 30px;">
    <h3 style="margin: 0 0 20px 0; color: var(--aurora-dark); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-calendar-check" style="color: #e67e22;"></i>
        <?php esc_html_e('Yearly Summary', 'edd-customer-dashboard-pro'); ?>
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
        <?php foreach ($yearly_spending as $year => $amount) : ?>
        <div style="text-align: center; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
            <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 5px;"><?php echo esc_html($year); ?></div>
            <div style="font-size: 1.2rem; font-weight: 600; opacity: 0.9;"><?php echo esc_html(edd_currency_filter(edd_format_amount($amount))); ?></div>
            <div style="font-size: 0.8rem; opacity: 0.7; margin-top: 5px;">
                <?php 
                $year_orders = array_filter($orders, function($order) use ($year) {
                    return gmdate('Y', strtotime($order->date_created)) === $year;
                });
                /* translators: %d: Number of orders */
                printf(esc_html(_n('%d order', '%d orders', count($year_orders), 'edd-customer-dashboard-pro')), esc_html(number_format_i18n(count($year_orders))));
                ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Recent Activity -->
<?php if (!empty($recent_downloads)) : ?>
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light);">
    <h3 style="margin: 0 0 20px 0; color: var(--aurora-dark); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-history" style="color: var(--aurora-primary);"></i>
        <?php esc_html_e('Recent Activity', 'edd-customer-dashboard-pro'); ?>
    </h3>
    
    <div style="space-y: 10px;">
        <?php foreach ($recent_downloads as $log) : 
            $download_name = get_the_title($log->product_id);
            $download_date = $log->date_created;
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--aurora-gray-light);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--aurora-secondary);"></div>
                <div>
                    <div style="font-weight: 500; color: var(--aurora-dark);"><?php echo esc_html($download_name); ?></div>
                    <div style="font-size: 0.85rem; color: var(--aurora-gray);"><?php esc_html_e('Downloaded', 'edd-customer-dashboard-pro'); ?></div>
                </div>
            </div>
            <div style="text-align: right; color: var(--aurora-gray); font-size: 0.85rem;">
                <?php echo esc_html(human_time_diff(strtotime($download_date), time())); ?> <?php esc_html_e('ago', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-chart-pie"></i>
    </div>
    <h3 class="empty-title"><?php esc_html_e('No Analytics Data Yet', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="empty-text"><?php esc_html_e('Make your first purchase to start seeing analytics about your spending patterns and download activity.', 'edd-customer-dashboard-pro'); ?></p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo esc_url(home_url('/downloads/')); ?>" class="btn btn-primary">
            <i class="fas fa-store"></i> <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </a>
        <button onclick="window.AuroraDashboard?.switchSection('products')" class="btn btn-outline">
            <i class="fas fa-box-open"></i> <?php esc_html_e('View Dashboard', 'edd-customer-dashboard-pro'); ?>
        </button>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('[style*="width:"]');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
    
    // Timeframe selector (placeholder for future enhancement)
    const timeframeSelect = document.getElementById('analyticsTimeframe');
    if (timeframeSelect) {
        timeframeSelect.addEventListener('change', function() {
            // Future: Filter analytics data based on selected timeframe
            console.log('Timeframe changed to:', this.value);
        });
    }
});
</script>