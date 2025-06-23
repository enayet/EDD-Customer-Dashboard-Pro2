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
    echo '<p class="text-yellow-800">' . __('Customer data not found.', 'eddcdp') . '</p>';
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
        $date = date('Y-m', strtotime($order->date_created));
        $year = date('Y', strtotime($order->date_created));
        
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
    📊 <?php _e('Purchase Analytics', 'eddcdp'); ?>
</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold mb-1"><?php echo edd_currency_filter(edd_format_amount($total_spent)); ?></p>
                <p class="text-green-100 font-medium"><?php _e('Total Spent', 'eddcdp'); ?></p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl">
                💰
            </div>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold mb-1"><?php echo $avg_downloads_per_product; ?></p>
                <p class="text-blue-100 font-medium"><?php _e('Avg Downloads/Product', 'eddcdp'); ?></p>
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
    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('Spending Overview', 'eddcdp'); ?></h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Monthly Spending -->
        <div>
            <h4 class="text-md font-medium text-gray-700 mb-3"><?php _e('Recent Monthly Spending', 'eddcdp'); ?></h4>
            <div class="space-y-2">
                <?php 
                $recent_months = array_slice($monthly_spending, -6, 6, true);
                foreach ($recent_months as $month => $amount) :
                    $month_name = date_i18n('F Y', strtotime($month . '-01'));
                ?>
                <div class="flex justify-between items-center bg-white p-3 rounded-lg">
                    <span class="text-sm font-medium text-gray-700"><?php echo $month_name; ?></span>
                    <span class="text-sm font-bold text-indigo-600"><?php echo edd_currency_filter(edd_format_amount($amount)); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Yearly Spending -->
        <div>
            <h4 class="text-md font-medium text-gray-700 mb-3"><?php _e('Yearly Totals', 'eddcdp'); ?></h4>
            <div class="space-y-2">
                <?php foreach ($yearly_spending as $year => $amount) : ?>
                <div class="flex justify-between items-center bg-white p-3 rounded-lg">
                    <span class="text-sm font-medium text-gray-700"><?php echo $year; ?></span>
                    <span class="text-sm font-bold text-green-600"><?php echo edd_currency_filter(edd_format_amount($amount)); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Stats -->
<div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('Quick Stats', 'eddcdp'); ?></h3>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-800"><?php echo $total_purchases; ?></div>
            <div class="text-sm text-gray-600"><?php _e('Orders', 'eddcdp'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-800"><?php echo $total_downloads; ?></div>
            <div class="text-sm text-gray-600"><?php _e('Downloads', 'eddcdp'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-800">
                <?php echo $total_purchases > 0 ? edd_currency_filter(edd_format_amount($total_spent / $total_purchases)) : edd_currency_filter('0'); ?>
            </div>
            <div class="text-sm text-gray-600"><?php _e('Avg Order', 'eddcdp'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-800"><?php echo $days_active; ?></div>
            <div class="text-sm text-gray-600"><?php _e('Days Active', 'eddcdp'); ?></div>
        </div>
    </div>
</div>

<!-- Advanced Analytics Coming Soon -->
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        📊
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('Advanced Analytics Coming Soon', 'eddcdp'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('Detailed charts and insights about your purchase history, usage patterns, and recommendations.', 'eddcdp'); ?></p>
    <button class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        📬 <?php _e('Notify Me When Available', 'eddcdp'); ?>
    </button>
</div>