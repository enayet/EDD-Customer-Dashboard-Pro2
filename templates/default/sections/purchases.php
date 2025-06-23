<?php
/**
 * Purchases Section Template - EDD 3.0+ Compatible
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$current_user = wp_get_current_user();

// Get customer first to ensure we have valid customer ID
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . __('No customer data found.', 'eddcdp') . '</p>';
    echo '</div>';
    return;
}

// Get user's orders using EDD 3.0+ method
$orders = edd_get_orders(array(
    'customer' => $customer->id,
    'number' => 20,
    'status' => array('complete', 'pending', 'processing'),
    'orderby' => 'date_created',
    'order' => 'DESC'
));
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    📦 <?php _e('Your Orders & Purchases', 'eddcdp'); ?>
</h2>

<?php if ($orders) : ?>
<div class="space-y-6">
    <?php foreach ($orders as $order) : 
        $total = $order->total;
        $status = $order->status;
        $order_number = $order->get_number();
        
        // Get order items using EDD 3.0+ method
        $order_items = $order->get_items();
    ?>
    
    <!-- Purchase Item -->
    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    <?php printf(__('Order #%s', 'eddcdp'), $order_number); ?>
                </h3>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span class="flex items-center gap-1">📋 <?php printf(__('Order #%s', 'eddcdp'), $order_number); ?></span>
                    <span class="flex items-center gap-1">📅 <?php echo date_i18n(get_option('date_format'), strtotime($order->date_created)); ?></span>
                    <span class="flex items-center gap-1 font-semibold">💰 <?php echo edd_currency_filter(edd_format_amount($total)); ?></span>
                </div>
            </div>
            <?php 
            $status_class = 'bg-green-100 text-green-800';
            $status_icon = '✅';
            $status_text = __('Completed', 'eddcdp');
            
            if ($status == 'pending') {
                $status_class = 'bg-yellow-100 text-yellow-800';
                $status_icon = '⏳';
                $status_text = __('Pending', 'eddcdp');
            } elseif ($status == 'processing') {
                $status_class = 'bg-blue-100 text-blue-800';
                $status_icon = '⚙️';
                $status_text = __('Processing', 'eddcdp');
            } elseif ($status == 'failed') {
                $status_class = 'bg-red-100 text-red-800';
                $status_icon = '❌';
                $status_text = __('Failed', 'eddcdp');
            }
            ?>
            <span class="<?php echo $status_class; ?> px-4 py-2 rounded-full text-sm font-medium w-fit">
                <?php echo $status_icon . ' ' . $status_text; ?>
            </span>
        </div>
        
        <?php if ($order_items && $status == 'complete') : ?>
        <div class="bg-white/60 rounded-xl p-4 mb-4 space-y-3">
            <?php foreach ($order_items as $item) : 
                $download_id = $item->product_id;
                $download_name = $item->product_name;
                $download_files = edd_get_download_files($download_id);
                
                // Get download URL using EDD 3.0+ method
                $download_url = edd_get_download_file_url($order->payment_key, $current_user->user_email, 0, $download_id);
            ?>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 <?php echo count($order_items) > 1 ? 'pb-3 border-b border-gray-200 last:border-b-0 last:pb-0' : ''; ?>">
                <div>
                    <p class="font-medium text-gray-800"><?php echo esc_html($download_name); ?></p>
                    <?php if ($download_files) : ?>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php 
                            $file_info = reset($download_files);
                            printf(__('File: %s', 'eddcdp'), isset($file_info['name']) ? esc_html($file_info['name']) : __('Download File', 'eddcdp'));
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <?php if ($download_url) : ?>
                <a href="<?php echo esc_url($download_url); ?>" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
                    🔽 <?php _e('Download', 'eddcdp'); ?>
                </a>
                <?php else : ?>
                <span class="bg-gray-300 text-gray-600 px-6 py-2 rounded-xl font-medium flex items-center gap-2">
                    🔽 <?php _e('Not Available', 'eddcdp'); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
            <button onclick="viewOrderDetails(<?php echo $order->id; ?>)" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">
                📋 <?php _e('Details', 'eddcdp'); ?>
            </button>
            
            <?php if (function_exists('edd_get_order_receipt_url')) : ?>
            <a href="<?php echo edd_get_order_receipt_url($order->id); ?>" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2 text-decoration-none">
                📄 <?php _e('Receipt', 'eddcdp'); ?>
            </a>
            <?php endif; ?>
            
            <button onclick="contactSupport(<?php echo $order->id; ?>)" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">
                💬 <?php _e('Support', 'eddcdp'); ?>
            </button>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        📦
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Purchases Yet', 'eddcdp'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('You haven\'t made any purchases yet. Start exploring our products!', 'eddcdp'); ?></p>
    <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        🛒 <?php _e('Browse Products', 'eddcdp'); ?>
    </button>
</div>
<?php endif; ?>

<script>
function viewOrderDetails(orderId) {
    // This could open a modal or redirect to order details page
    alert('<?php _e('Order details functionality would be implemented here.', 'eddcdp'); ?>');
}

function contactSupport(orderId) {
    // This could open support form or redirect to support page
    alert('<?php _e('Support contact functionality would be implemented here.', 'eddcdp'); ?>');
}
</script>