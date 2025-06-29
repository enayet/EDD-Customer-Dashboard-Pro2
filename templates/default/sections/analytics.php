<?php
/**
 * Analytics Section Template - EDD 3.0+ Compatible
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and analytics data
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . esc_html__('Customer data not found.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Calculate analytics using EDD 3.0+ methods
$total_spent = $customer->purchase_value;
$total_purchases = $customer->purchase_count;

// Get download count using EDD 3.0+ method
$download_logs = edd_get_file_download_logs(array(
    'customer' => $current_user->user_email,
    'number' => 999999, // Large number instead of -1
    'fields' => 'ids'
));
$total_downloads = is_array($download_logs) ? count($download_logs) : 0;

$avg_downloads_per_product = $total_purchases > 0 ? round($total_downloads / $total_purchases, 1) : 0;

// Get purchase history using EDD 3.0+ orders
$orders = edd_get_orders(array(
    'customer' => $customer->id,
    'number' => 999999, // Large number instead of -1
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
    $first_order = end($orders);
}
$days_active = 0;
if ($first_order) {
    $days_active = ceil((time() - strtotime($first_order->date_created)) / (60 * 60 * 24));
}
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    📊 <?php esc_html_e('Purchase Analytics', 'edd-customer-dashboard-pro'); ?>
</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold mb-1"><?php echo esc_html(edd_currency_filter(edd_format_amount($total_spent))); ?></p>
                <p class="text-green-100 font-medium"><?php esc_html_e('Total Spent', 'edd-customer-dashboard-pro'); ?></p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl">
                💰
            </div>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold mb-1"><?php echo esc_html(number_format_i18n($avg_downloads_per_product, 1)); ?></p>
                <p class="text-blue-100 font-medium"><?php esc_html_e('Avg Downloads/Product', 'edd-customer-dashboard-pro'); ?></p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl">
                📈
            </div>
        </div>
    </div>
</div>

<!-- Spending Overview -->
<?php if (!empty($monthly_spending)) : ?>
<div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php esc_html_e('Spending Overview', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Monthly Spending -->
        <div>
            <h4 class="text-md font-medium text-gray-700 mb-3"><?php esc_html_e('Recent Monthly Spending', 'edd-customer-dashboard-pro'); ?></h4>
            <div class="space-y-2">
                <?php 
                $recent_months = array_slice($monthly_spending, -6, 6, true);
                foreach ($recent_months as $month => $amount) :
                    $month_name = date_i18n('F Y', strtotime($month . '-01'));
                ?>
                <div class="flex justify-between items-center bg-white p-3 rounded-lg">
                    <span class="text-sm font-medium text-gray-700"><?php echo esc_html($month_name); ?></span>
                    <span class="text-sm font-bold text-indigo-600"><?php echo esc_html(edd_currency_filter(edd_format_amount($amount))); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Yearly Spending -->
        <div>
            <h4 class="text-md font-medium text-gray-700 mb-3"><?php esc_html_e('Yearly Totals', 'edd-customer-dashboard-pro'); ?></h4>
            <div class="space-y-2">
                <?php foreach ($yearly_spending as $year => $amount) : ?>
                <div class="flex justify-between items-center bg-white p-3 rounded-lg">
                    <span class="text-sm font-medium text-gray-700"><?php echo esc_html($year); ?></span>
                    <span class="text-sm font-bold text-green-600"><?php echo esc_html(edd_currency_filter(edd_format_amount($amount))); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Stats -->
<div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php esc_html_e('Quick Stats', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-800"><?php echo esc_html(number_format_i18n($total_purchases)); ?></div>
            <div class="text-sm text-gray-600"><?php esc_html_e('Orders', 'edd-customer-dashboard-pro'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-800"><?php echo esc_html(number_format_i18n($total_downloads)); ?></div>
            <div class="text-sm text-gray-600"><?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-800">
                <?php echo $total_purchases > 0 ? esc_html(edd_currency_filter(edd_format_amount($total_spent / $total_purchases))) : esc_html(edd_currency_filter('0')); ?>
            </div>
            <div class="text-sm text-gray-600"><?php esc_html_e('Avg Order', 'edd-customer-dashboard-pro'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-800"><?php echo esc_html(number_format_i18n($days_active)); ?></div>
            <div class="text-sm text-gray-600"><?php esc_html_e('Days Active', 'edd-customer-dashboard-pro'); ?></div>
        </div>
    </div>
</div>