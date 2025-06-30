<?php
/**
 * Purchases Section Template - Updated Design
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

<h2 class="section-title"><?php esc_html_e('Your Orders & Purchases', 'edd-customer-dashboard-pro'); ?></h2>

<?php if ($orders) : ?>
<div class="purchase-list">
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
    <div class="purchase-item">
        <div class="purchase-header">
            <div class="order-info">
                <div class="product-name">
                    <?php 
                    if (count($order_items) === 1) {
                        echo esc_html($order_items[0]->product_name);
                    } else {
                        /* translators: %s: Order number */
                        printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($order_number));
                    }
                    ?>
                </div>
                <div class="order-meta">
                    <span class="order-number">
                        <?php 
                        /* translators: %s: Order number */
                        printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($order_number)); 
                        ?>
                    </span>
                    <span class="order-date">
                        <?php echo esc_html($order_details->format_order_date($order->date_created, get_option('date_format'))); ?>
                    </span>
                    <span class="order-total">
                        <?php echo esc_html(eddcdp_format_price($total)); ?>
                    </span>
                    <?php if ($license_count > 0) : ?>
                    <span class="license-info-meta">
                        🔑 <?php 
                        /* translators: %d: Number of licenses */
                        printf(esc_html(_n('%d License', '%d Licenses', $license_count, 'edd-customer-dashboard-pro')), esc_html(number_format_i18n($license_count))); 
                        ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <span class="status-badge <?php echo esc_attr($status); ?>">
                <?php echo esc_html($status_info['icon'] . ' ' . $status_info['label']); ?>
            </span>
        </div>
        
        <?php if ($order_items && $status == 'complete') : ?>
        <div class="order-products">
            <?php foreach ($order_items as $item) : 
                $download_id = $item->product_id;
                $download_name = $item->product_name;
                $download_files = $order_details->get_order_item_download_files($order->id, $download_id, $item->price_id);
            ?>
            <div class="product-row">
                <div class="product-details">
                    <strong><?php echo esc_html($download_name); ?></strong>
                    <?php if ($download_files) : ?>
                        <div class="product-meta">
                            <?php 
                            $file_count = count($download_files);
                            /* translators: %d: Number of files */
                            printf(esc_html(_n('%d file available', '%d files available', $file_count, 'edd-customer-dashboard-pro')), esc_html(number_format_i18n($file_count)));
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-meta">
                        <?php 
                        /* translators: %1$d: Quantity, %2$s: Formatted price */
                        printf(esc_html__('Quantity: %1$d × %2$s', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($item->quantity)), esc_html(eddcdp_format_price($item->amount))); 
                        ?>
                    </div>
                </div>
                
                <?php if ($download_files) : ?>
                <div class="product-actions">
                    <?php if (count($download_files) === 1) : ?>
                        <!-- Single file - direct download button -->
                        <a href="<?php echo esc_url($download_files[0]['url']); ?>" 
                           class="btn btn-download">
                            🔽 <?php esc_html_e('Download', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    <?php else : ?>
                        <!-- Multiple files - link to details -->
                        <a href="<?php echo esc_url($order_details->get_order_details_url($order->id, get_permalink())); ?>" 
                           class="btn btn-download">
                            🔽 <?php 
                            /* translators: %d: Number of files */
                            printf(esc_html__('Download (%d)', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n(count($download_files)))); 
                            ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="product-actions">
                    <span class="btn btn-secondary" style="opacity: 0.6; cursor: not-allowed;">
                        <?php esc_html_e('No downloadable files', 'edd-customer-dashboard-pro'); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php elseif ($status !== 'complete') : ?>
        <div class="order-products" style="background: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107;">
            <div style="display: flex; align-items: center; gap: 10px; color: #856404;">
                <span style="font-size: 1.2rem;">⏳</span>
                <span>
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
        
        <div class="order-actions">
            <!-- Order Details Button -->
            <a href="<?php echo esc_url($order_details->get_order_details_url($order->id, get_permalink())); ?>" 
               class="btn btn-secondary">
                📋 <?php esc_html_e('Order Details', 'edd-customer-dashboard-pro'); ?>
            </a>
            
            <!-- Invoice Button (only if EDD Invoices is active and order has invoice) -->
            <?php if ($invoices_active && $order_details->order_has_invoice($order->id)) : 
                $invoice_url = $order_details->get_order_invoice_url($order->id);
            ?>
            <a href="<?php echo esc_url($invoice_url); ?>" 
               class="btn btn-secondary">
                📄 <?php esc_html_e('View Invoice', 'edd-customer-dashboard-pro'); ?>
            </a>
            <?php endif; ?>
            
            <!-- License Button (only if order has licenses) -->
            <?php if ($has_licenses) : ?>
            <a href="<?php echo esc_url($order_details->get_order_licenses_url($order->id, get_permalink())); ?>" 
               class="btn btn-success">
                🔑 <?php 
                /* translators: %d: Number of licenses */
                printf(esc_html(_n('Manage %d License', 'Manage %d Licenses', $license_count, 'edd-customer-dashboard-pro')), esc_html(number_format_i18n($license_count))); 
                ?>
            </a>
            <?php endif; ?>
            
            <!-- Support Button -->
            <button onclick="showSupportTab()" class="btn btn-secondary">
                💬 <?php esc_html_e('Support', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
        
    </div>
    
    <?php endforeach; ?>
</div>

<!-- Load More Button (if there are more orders) -->
<?php if (count($orders) >= 20) : ?>
<div style="text-align: center; margin-top: 30px;">
    <button onclick="loadMoreOrders()" 
            class="btn btn-secondary">
        <?php esc_html_e('Load More Orders', 'edd-customer-dashboard-pro'); ?>
    </button>
</div>
<?php endif; ?>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">📦</div>
    <h3><?php esc_html_e('No Purchases Yet', 'edd-customer-dashboard-pro'); ?></h3>
    <p><?php esc_html_e('You haven\'t made any purchases yet. Start exploring our products!', 'edd-customer-dashboard-pro'); ?></p>
    <div style="display: flex; flex-direction: column; gap: 15px; align-items: center;">
        <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" 
                class="btn">
            🛒 <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </button>
        
        <?php if (function_exists('edd_wl_get_wish_list')) : ?>
        <button onclick="showWishlistTab()" 
                class="btn btn-secondary">
            ❤️ <?php esc_html_e('View Wishlist', 'edd-customer-dashboard-pro'); ?>
        </button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
// Load more orders functionality
function loadMoreOrders() {
    // This would implement AJAX loading of more orders
    console.log('<?php esc_html_e('Loading more orders...', 'edd-customer-dashboard-pro'); ?>');
    
    // For now, just show a message
    alert('<?php esc_html_e('Load more functionality would be implemented here.', 'edd-customer-dashboard-pro'); ?>');
}

// Support tab switcher
function showSupportTab() {
    const dashboardElement = document.querySelector('[x-data]');
    if (dashboardElement && dashboardElement._x_dataStack) {
        dashboardElement._x_dataStack[0].activeTab = 'support';
        window.location.hash = 'support';
    } else {
        // Fallback for non-Alpine environments
        console.log('<?php esc_html_e('Switching to support tab...', 'edd-customer-dashboard-pro'); ?>');
    }
}

// Wishlist tab switcher
function showWishlistTab() {
    const dashboardElement = document.querySelector('[x-data]');
    if (dashboardElement && dashboardElement._x_dataStack) {
        dashboardElement._x_dataStack[0].activeTab = 'wishlist';
        window.location.hash = 'wishlist';
    }
}
</script>