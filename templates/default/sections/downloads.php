<?php
/**
 * Downloads Section Template - EDD 3.0+ Compatible
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$current_user = wp_get_current_user();

// Get customer first to ensure we have valid customer
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . __('Customer data not found.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Get download logs using EDD 3.0+ method
$download_logs = edd_get_file_download_logs(array(
    'customer' => $customer->id,
    'number' => 20,
    'orderby' => 'date_created',
    'order' => 'DESC'
));
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    ⬇️ <?php _e('Download History', 'edd-customer-dashboard-pro'); ?>
</h2>

<?php if ($download_logs) : ?>
<div class="space-y-4">
    <?php foreach ($download_logs as $log) : 
        $download_id = $log->product_id;
        $download_name = get_the_title($download_id);
        $download_date = $log->date_created;
        $file_id = $log->file_id;
        
        // Get order info if available
        $order_id = !empty($log->order_id) ? $log->order_id : 0;
        $order = $order_id ? edd_get_order($order_id) : null;
        $order_number = $order ? $order->get_number() : '';
        
        // Check if download is still valid
        $has_access = false;
        $download_url = '';
        
        if ($order && $order->status === 'complete') {
            $download_url = edd_get_download_file_url($order->payment_key, $customer->email, $file_id, $download_id);
            $has_access = !empty($download_url);
        }
        
        // Calculate download limits
        $limit = edd_get_file_download_limit($download_id);
        $downloads_used = 0;
        $remaining = __('Unlimited', 'edd-customer-dashboard-pro');
        
        if ($limit && $order_id) {
            // Count downloads for this specific order and product
            $used_logs = edd_get_file_download_logs(array(
                'product_id' => $download_id,
                'order_id' => $order_id,
                'customer' => $customer->id,
                'number' => 999999,
                'fields' => 'ids'
            ));
            $downloads_used = is_array($used_logs) ? count($used_logs) : 0;
            $remaining_count = max(0, $limit - $downloads_used);
            $remaining = $remaining_count . ' ' . __('of', 'edd-customer-dashboard-pro') . ' ' . $limit;
        }
        
        // Determine if download is recent (within 7 days)
        $is_recent = (strtotime($download_date) > strtotime('-7 days'));
    ?>
    
    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-800"><?php echo esc_html($download_name); ?></h3>
                <p class="text-gray-600 mt-1">
                    <?php printf(__('Downloaded: %s', 'edd-customer-dashboard-pro'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($download_date))); ?>
                </p>
                
                <?php if ($order_number) : ?>
                <p class="text-sm text-gray-500 mt-1">
                    <?php printf(__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($order_number)); ?>
                </p>
                <?php endif; ?>
                
                <p class="text-sm text-gray-500 mt-2">
                    <strong><?php _e('Downloads remaining:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo $remaining; ?>
                </p>
                
                <?php if ($limit && $downloads_used >= $limit) : ?>
                <p class="text-sm text-red-600 mt-1">
                    <strong><?php _e('Download limit reached', 'edd-customer-dashboard-pro'); ?></strong>
                </p>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-3">
                <?php if ($has_access && (!$limit || $downloads_used < $limit)) : ?>
                <a href="<?php echo esc_url($download_url); ?>" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
                    🔽 <?php _e('Download Again', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php else : ?>
                <span class="bg-gray-300 text-gray-600 px-6 py-2 rounded-xl font-medium flex items-center gap-2">
                    🔽 <?php _e('Not Available', 'edd-customer-dashboard-pro'); ?>
                </span>
                <?php endif; ?>
                
                <?php 
                $badge_class = $is_recent ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600';
                $badge_text = $is_recent ? __('Recent', 'edd-customer-dashboard-pro') : __('Previous', 'edd-customer-dashboard-pro');
                ?>
                <span class="<?php echo $badge_class; ?> px-4 py-2 rounded-full text-sm font-medium">
                    <?php echo $badge_text; ?>
                </span>
            </div>
        </div>
        
        <!-- Additional download actions -->
        <?php if ($order) : ?>
        <div class="flex flex-wrap gap-3 mt-4 pt-4 border-t border-gray-200">
            <button onclick="viewOrderDetails(<?php echo $order->id; ?>)" class="bg-white text-gray-600 border border-gray-300 px-3 py-1 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                📋 <?php _e('Order Details', 'edd-customer-dashboard-pro'); ?>
            </button>
            
            <?php if ($limit && $downloads_used >= $limit) : ?>
            <button onclick="requestAdditionalDownloads(<?php echo $order->id; ?>, <?php echo $download_id; ?>)" class="bg-orange-100 text-orange-700 border border-orange-300 px-3 py-1 rounded-lg hover:bg-orange-200 transition-colors text-sm">
                📞 <?php _e('Request More Downloads', 'edd-customer-dashboard-pro'); ?>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        ⬇️
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Downloads Yet', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('You haven\'t downloaded any files yet. Make a purchase to get started!', 'edd-customer-dashboard-pro'); ?></p>
    <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        🛒 <?php _e('Browse Products', 'edd-customer-dashboard-pro'); ?>
    </button>
</div>
<?php endif; ?>

<script>
function viewOrderDetails1(orderId) {
    // This could open a modal or redirect to order details page
    alert('<?php _e('Order details functionality would be implemented here.', 'edd-customer-dashboard-pro'); ?>');
}

function requestAdditionalDownloads(orderId, downloadId) {
    // This could open a support form for requesting additional downloads
    const message = '<?php _e('I need additional downloads for Order #', 'edd-customer-dashboard-pro'); ?>' + orderId + ' <?php _e('- Product ID:', 'edd-customer-dashboard-pro'); ?> ' + downloadId;
    alert(message);
}
</script>