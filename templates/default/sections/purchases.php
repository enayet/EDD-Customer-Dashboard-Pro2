<?php
/**
 * Purchases Section Template - Enhanced with Invoice and License buttons
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$current_user = wp_get_current_user();

// Get user's orders using EDD 3.0+ function
$orders = edd_get_orders(array(
    'customer' => $current_user->user_email,
    'number' => 20,
    'status' => array('complete', 'pending', 'processing'),
    'orderby' => 'date_created',
    'order' => 'DESC'
));

// Check if EDD Invoices add-on is active
$invoices_active = function_exists('edd_invoices_get_invoice_url');
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
        
        // Get order items
        $order_items = $order->get_items();
        
        // Check if order has licenses and count them
        $has_licenses = false;
        $license_count = 0;
        if (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) {
            foreach ($order_items as $item) {
                $licenses = edd_software_licensing()->get_licenses_of_purchase($order->id, $item->product_id);
                if ($licenses) {
                    $has_licenses = true;
                    $license_count += count($licenses);
                }
            }
        }
        
        // Get order status formatting
        $order_details = EDDCDP_Order_Details::instance();
        $status_info = $order_details->get_formatted_order_status($order);
    ?>
    
    <!-- Purchase Item -->
    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    <?php printf(__('Order #%s', 'eddcdp'), $order_number); ?>
                </h3>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span class="flex items-center gap-1">
                        📋 <?php printf(__('Order #%s', 'eddcdp'), $order_number); ?>
                    </span>
                    <span class="flex items-center gap-1">
                        📅 <?php echo $order_details->format_order_date($order->date_created, get_option('date_format')); ?>
                    </span>
                    <span class="flex items-center gap-1 font-semibold">
                        💰 <?php echo eddcdp_format_price($total); ?>
                    </span>
                    <?php if ($license_count > 0) : ?>
                    <span class="flex items-center gap-1">
                        🔑 <?php printf(_n('%d License', '%d Licenses', $license_count, 'eddcdp'), $license_count); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <span class="<?php echo $status_info['class']; ?> px-4 py-2 rounded-full text-sm font-medium w-fit">
                <?php echo $status_info['icon'] . ' ' . $status_info['label']; ?>
            </span>
        </div>
        
        <?php if ($order_items && $status == 'complete') : ?>
        <div class="bg-white/60 rounded-xl p-4 mb-4 space-y-3">
            <?php foreach ($order_items as $item) : 
                $download_id = $item->product_id;
                $download_name = $item->product_name;
                $download_files = $order_details->get_order_item_download_files($order->id, $download_id, $item->price_id);
            ?>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 <?php echo count($order_items) > 1 ? 'pb-3 border-b border-gray-200 last:border-b-0 last:pb-0' : ''; ?>">
                <div class="flex-1">
                    <p class="font-medium text-gray-800"><?php echo esc_html($download_name); ?></p>
                    <?php if ($download_files) : ?>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php 
                            $file_count = count($download_files);
                            printf(_n('%d file available', '%d files available', $file_count, 'eddcdp'), $file_count);
                            ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="text-sm text-gray-600 mt-1">
                        <?php printf(__('Quantity: %d × %s', 'eddcdp'), $item->quantity, eddcdp_format_price($item->amount)); ?>
                    </div>
                </div>
                
                <?php if ($download_files) : ?>
                <div class="flex flex-wrap gap-2">
                    <?php if (count($download_files) === 1) : ?>
                        <!-- Single file - direct download button -->
                        <a href="<?php echo esc_url($download_files[0]['url']); ?>" 
                           class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
                            🔽 <?php _e('Download', 'eddcdp'); ?>
                        </a>
                    <?php else : ?>
                        <!-- Multiple files - dropdown or link to details -->
                        <a href="<?php echo $order_details->get_order_details_url($order->id, get_permalink()); ?>" 
                           class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
                            🔽 <?php printf(__('Download (%d)', 'eddcdp'), count($download_files)); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="text-sm text-gray-500 italic">
                    <?php _e('No downloadable files', 'eddcdp'); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php elseif ($status !== 'complete') : ?>
        <div class="bg-yellow-50/60 rounded-xl p-4 mb-4 border border-yellow-200">
            <div class="flex items-center gap-2 text-yellow-800">
                <span class="text-lg">⏳</span>
                <span class="text-sm">
                    <?php 
                    if ($status === 'pending') {
                        _e('Order is pending payment. Downloads will be available once payment is complete.', 'eddcdp');
                    } elseif ($status === 'processing') {
                        _e('Order is being processed. Downloads will be available shortly.', 'eddcdp');
                    } else {
                        _e('Downloads will be available once your order is complete.', 'eddcdp');
                    }
                    ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
            <!-- Order Details Button -->
            <a href="<?php echo $order_details->get_order_details_url($order->id, get_permalink()); ?>" 
               class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2 text-decoration-none">
                📋 <?php _e('Details', 'eddcdp'); ?>
            </a>
            
            <!-- Invoice Button (only if EDD Invoices is active and order has invoice) -->
            <?php if ($invoices_active && $order_details->order_has_invoice($order->id)) : 
                $invoice_url = $order_details->get_order_invoice_url($order->id);
            ?>
            <a href="<?php echo esc_url($invoice_url); ?>" 
               class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2 text-decoration-none">
                📄 <?php _e('Invoice', 'eddcdp'); ?>
            </a>
            <?php endif; ?>
            
            <!-- Receipt Button (if available) -->
            <?php 
            $receipt_url = $order_details->get_order_receipt_url($order->id);
            if ($receipt_url) : 
            ?>
            <a href="<?php echo esc_url($receipt_url); ?>" 
               class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2 text-decoration-none"
               target="_blank">
                🧾 <?php _e('Receipt', 'eddcdp'); ?>
            </a>
            <?php endif; ?>
            
            <!-- License Button (only if order has licenses) -->
            <?php if ($has_licenses) : ?>
            <a href="<?php echo $order_details->get_order_licenses_url($order->id, get_permalink()); ?>" 
               class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
                🔑 <?php printf(_n('%d License', '%d Licenses', $license_count, 'eddcdp'), $license_count); ?>
            </a>
            <?php endif; ?>
            
            <!-- Reorder Button (for completed orders) -->
            <?php if ($status === 'complete' && count($order_items) > 0) : ?>
            <button onclick="reorderItems(<?php echo $order->id; ?>)" 
                    class="bg-indigo-100 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-xl hover:bg-indigo-200 transition-colors flex items-center gap-2">
                🔄 <?php _e('Reorder', 'eddcdp'); ?>
            </button>
            <?php endif; ?>
        </div>
        
    </div>
    
    <?php endforeach; ?>
</div>

<!-- Load More Button (if there are more orders) -->
<?php if (count($orders) >= 20) : ?>
<div class="text-center mt-8">
    <button onclick="loadMoreOrders()" 
            class="bg-white text-gray-600 border border-gray-300 px-6 py-3 rounded-xl hover:bg-gray-50 transition-colors">
        <?php _e('Load More Orders', 'eddcdp'); ?>
    </button>
</div>
<?php endif; ?>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        📦
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Purchases Yet', 'eddcdp'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('You haven\'t made any purchases yet. Start exploring our products!', 'eddcdp'); ?></p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" 
                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
            🛒 <?php _e('Browse Products', 'eddcdp'); ?>
        </button>
        
        <?php if (function_exists('edd_wl_get_wish_list')) : ?>
        <button onclick="showLicensesTab ? showLicensesTab() : (window.location.hash = 'wishlist')" 
                class="bg-white text-gray-600 border border-gray-300 px-6 py-3 rounded-xl hover:bg-gray-50 transition-colors">
            ❤️ <?php _e('View Wishlist', 'eddcdp'); ?>
        </button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
// Simple reorder functionality
function reorderItems(orderId) {
    if (confirm('<?php _e('Add all items from this order to your cart?', 'eddcdp'); ?>')) {
        // Simple redirect to avoid AJAX complexity
        window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=eddcdp_reorder&order_id=' + orderId + '&redirect=' + encodeURIComponent(window.location.href);
    }
}
</script>