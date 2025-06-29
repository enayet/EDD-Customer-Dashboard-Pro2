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
    📦 <?php esc_html_e('Your Orders & Purchases', 'edd-customer-dashboard-pro'); ?>
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
                    <?php 
                    /* translators: %s: Order number */
                    printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($order_number)); 
                    ?>
                </h3>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span class="flex items-center gap-1">
                        📋 <?php 
                        /* translators: %s: Order number */
                        printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($order_number)); 
                        ?>
                    </span>
                    <span class="flex items-center gap-1">
                        📅 <?php echo esc_html($order_details->format_order_date($order->date_created, get_option('date_format'))); ?>
                    </span>
                    <span class="flex items-center gap-1 font-semibold">
                        💰 <?php echo esc_html(eddcdp_format_price($total)); ?>
                    </span>
                    <?php if ($license_count > 0) : ?>
                    <span class="flex items-center gap-1">
                        🔑 <?php 
                        /* translators: %d: Number of licenses */
                        printf(esc_html(_n('%d License', '%d Licenses', $license_count, 'edd-customer-dashboard-pro')), esc_html(number_format_i18n($license_count))); 
                        ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <span class="<?php echo esc_attr($status_info['class']); ?> px-4 py-2 rounded-full text-sm font-medium w-fit">
                <?php echo esc_html($status_info['icon'] . ' ' . $status_info['label']); ?>
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
                            /* translators: %d: Number of files */
                            printf(esc_html(_n('%d file available', '%d files available', $file_count, 'edd-customer-dashboard-pro')), esc_html(number_format_i18n($file_count)));
                            ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="text-sm text-gray-600 mt-1">
                        <?php 
                        /* translators: %1$d: Quantity, %2$s: Formatted price */
                        printf(esc_html__('Quantity: %1$d × %2$s', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($item->quantity)), esc_html(eddcdp_format_price($item->amount))); 
                        ?>
                    </div>
                </div>
                
                <?php if ($download_files) : ?>
                <div class="flex flex-wrap gap-2">
                    <?php if (count($download_files) === 1) : ?>
                        <!-- Single file - direct download button -->
                        <a href="<?php echo esc_url($download_files[0]['url']); ?>" 
                           class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
                            🔽 <?php esc_html_e('Download', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    <?php else : ?>
                        <!-- Multiple files - dropdown or link to details -->
                        <a href="<?php echo esc_url($order_details->get_order_details_url($order->id, get_permalink())); ?>" 
                           class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
                            🔽 <?php 
                            /* translators: %d: Number of files */
                            printf(esc_html__('Download (%d)', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n(count($download_files)))); 
                            ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="text-sm text-gray-500 italic">
                    <?php esc_html_e('No downloadable files', 'edd-customer-dashboard-pro'); ?>
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
                        esc_html_e('Order is pending payment. Downloads will be available once payment is complete.', 'edd-customer-dashboard-pro');
                    } elseif ($status === 'processing') {
                        esc_html_e('Order is being processed. Downloads will be available shortly.', 'edd-customer-dashboard-pro');
                    } else {
                        esc_html_e('Downloads will be available once your order is complete.', 'edd-customer-dashboard-pro');
                    }
                    ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
            <!-- Order Details Button -->
            <a href="<?php echo esc_url($order_details->get_order_details_url($order->id, get_permalink())); ?>" 
               class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2 text-decoration-none">
                📋 <?php esc_html_e('Details', 'edd-customer-dashboard-pro'); ?>
            </a>
            
            <!-- Invoice Button (only if EDD Invoices is active and order has invoice) -->
            <?php if ($invoices_active && $order_details->order_has_invoice($order->id)) : 
                $invoice_url = $order_details->get_order_invoice_url($order->id);
            ?>
            <a href="<?php echo esc_url($invoice_url); ?>" 
               class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2 text-decoration-none">
                📄 <?php esc_html_e('Invoice', 'edd-customer-dashboard-pro'); ?>
            </a>
            <?php endif; ?>
            
            <!-- License Button (only if order has licenses) -->
            <?php if ($has_licenses) : ?>
            <a href="<?php echo esc_url($order_details->get_order_licenses_url($order->id, get_permalink())); ?>" 
               class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
                🔑 <?php 
                /* translators: %d: Number of licenses */
                printf(esc_html(_n('%d License', '%d Licenses', $license_count, 'edd-customer-dashboard-pro')), esc_html(number_format_i18n($license_count))); 
                ?>
            </a>
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
        <?php esc_html_e('Load More Orders', 'edd-customer-dashboard-pro'); ?>
    </button>
</div>
<?php endif; ?>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        📦
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php esc_html_e('No Purchases Yet', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="text-gray-600 mb-6"><?php esc_html_e('You haven\'t made any purchases yet. Start exploring our products!', 'edd-customer-dashboard-pro'); ?></p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" 
                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
            🛒 <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </button>
        
        <?php if (function_exists('edd_wl_get_wish_list')) : ?>
        <button onclick="showLicensesTab ? showLicensesTab() : (window.location.hash = 'wishlist')" 
                class="bg-white text-gray-600 border border-gray-300 px-6 py-3 rounded-xl hover:bg-gray-50 transition-colors">
            ❤️ <?php esc_html_e('View Wishlist', 'edd-customer-dashboard-pro'); ?>
        </button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
// Simple reorder functionality
function reorderItems(orderId) {
    if (confirm('<?php esc_html_e('Add all items from this order to your cart?', 'edd-customer-dashboard-pro'); ?>')) {
        // Simple redirect to avoid AJAX complexity
        window.location.href = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=eddcdp_reorder&order_id=' + orderId + '&redirect=' + encodeURIComponent(window.location.href);
    }
}
</script>