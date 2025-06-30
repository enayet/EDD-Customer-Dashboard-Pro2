<?php
/**
 * Aurora Products Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    echo '<div class="empty-state">';
    echo '<div class="empty-icon"><i class="fas fa-user-times"></i></div>';
    echo '<h3 class="empty-title">' . esc_html__('Customer data not found.', 'edd-customer-dashboard-pro') . '</h3>';
    echo '</div>';
    return;
}

// Get user's orders using EDD 3.0+ function
$orders = edd_get_orders(array(
    'customer' => $current_user->user_email,
    'number' => 20,
    'status' => array('complete', 'pending', 'processing'),
    'orderby' => 'date_created',
    'order' => 'DESC'
));

// Get stats for header
$total_purchases = $customer->purchase_count;
$total_downloads = eddcdp_get_customer_download_count($current_user->user_email);
$active_licenses = eddcdp_get_customer_active_license_count($current_user->ID);
$wishlist_items = eddcdp_get_customer_wishlist_count($current_user->ID);

// Get order details instance
$order_details = EDDCDP_Order_Details::instance();
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1 class="dashboard-title"><?php esc_html_e('My Digital Products', 'edd-customer-dashboard-pro'); ?></h1>
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="<?php esc_attr_e('Search products...', 'edd-customer-dashboard-pro'); ?>">
    </div>
</div>

<!-- Stats Overview -->
<div class="stats-grid">
    <div class="stat-card purchases">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($total_purchases)); ?></div>
        <div class="stat-label">
            <i class="fas fa-box-open"></i>
            <?php esc_html_e('Total Purchases', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card downloads">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($total_downloads)); ?></div>
        <div class="stat-label">
            <i class="fas fa-download"></i>
            <?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card licenses">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($active_licenses)); ?></div>
        <div class="stat-label">
            <i class="fas fa-key"></i>
            <?php esc_html_e('Active Licenses', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card wishlist">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($wishlist_items)); ?></div>
        <div class="stat-label">
            <i class="fas fa-heart"></i>
            <?php esc_html_e('Wishlist Items', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>

<?php if ($orders) : ?>
<!-- Products Table -->
<table class="products-table">
    <thead>
        <tr>
            <th><?php esc_html_e('Product', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('License', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Status', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Actions', 'edd-customer-dashboard-pro'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order) : 
            $order_items = $order->get_items();
            $status_info = $order_details->get_formatted_order_status($order);
            
            // Check if order has licenses
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
            
            foreach ($order_items as $item) :
                $download_id = $item->product_id;
                $download_name = $item->product_name;
                
                // Get first license key if available
                $license_key = '';
                if ($has_licenses && function_exists('edd_software_licensing')) {
                    $licenses = edd_software_licensing()->get_licenses_of_purchase($order->id, $download_id);
                    if ($licenses && !empty($licenses[0])) {
                        $license_key = $licenses[0]->license_key;
                    }
                }
                
                // Get product icon based on product type or use default
                $product_icon = 'fas fa-box';
                if (stripos($download_name, 'plugin') !== false) {
                    $product_icon = 'fas fa-plug';
                } elseif (stripos($download_name, 'theme') !== false) {
                    $product_icon = 'fas fa-paint-brush';
                } elseif (stripos($download_name, 'voice') !== false || stripos($download_name, 'audio') !== false) {
                    $product_icon = 'fas fa-microphone-alt';
                } elseif (stripos($download_name, 'ecommerce') !== false || stripos($download_name, 'shop') !== false) {
                    $product_icon = 'fas fa-shopping-cart';
                } elseif (stripos($download_name, 'seo') !== false) {
                    $product_icon = 'fas fa-chart-line';
                }
        ?>
        <tr>
            <td>
                <div class="product-info">
                    <div class="product-icon">
                        <i class="<?php echo esc_attr($product_icon); ?>"></i>
                    </div>
                    <div>
                        <div class="product-name"><?php echo esc_html($download_name); ?></div>
                        <div class="product-meta">
                            <?php 
                            /* translators: %1$s: Purchase date, %2$s: Order number */
                            printf(esc_html__('Purchased: %1$s • Order #%2$s', 'edd-customer-dashboard-pro'), 
                                esc_html(date_i18n(get_option('date_format'), strtotime($order->date_created))), 
                                esc_html($order->get_number())
                            ); 
                            ?>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <?php if ($license_key) : ?>
                <div class="license-key" data-license="<?php echo esc_attr($license_key); ?>">
                    <?php echo esc_html($license_key); ?>
                </div>
                <?php else : ?>
                <span class="status-badge" style="background: rgba(153, 153, 153, 0.1); color: #999;">
                    <?php esc_html_e('No License', 'edd-customer-dashboard-pro'); ?>
                </span>
                <?php endif; ?>
            </td>
            <td>
                <span class="status-badge <?php echo $order->status === 'complete' ? 'status-active' : ($order->status === 'pending' ? 'status-pending' : 'status-expired'); ?>">
                    <?php echo esc_html($status_info['icon'] . ' ' . $status_info['label']); ?>
                </span>
            </td>
            <td>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <!-- Download Button -->
                    <?php if ($order->status === 'complete') : 
                        $download_files = $order_details->get_order_item_download_files($order->id, $download_id, $item->price_id);
                        if ($download_files) :
                            if (count($download_files) === 1) :
                    ?>
                    <a href="<?php echo esc_url($download_files[0]['url']); ?>" class="btn btn-outline btn-download">
                        <i class="fas fa-download"></i> <?php esc_html_e('Download', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <?php else : ?>
                    <a href="<?php echo esc_url($order_details->get_order_details_url($order->id, get_permalink())); ?>" class="btn btn-outline">
                        <i class="fas fa-download"></i> <?php 
                        /* translators: %d: Number of files */
                        printf(esc_html__('Download (%d)', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n(count($download_files)))); 
                        ?>
                    </a>
                    <?php endif; endif; endif; ?>
                    
                    <!-- License Button -->
                    <?php if ($has_licenses) : ?>
                    <a href="<?php echo esc_url($order_details->get_order_licenses_url($order->id, get_permalink())); ?>" class="btn btn-outline">
                        <i class="fas fa-key"></i> <?php esc_html_e('License', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Renew Button for Expired -->
                    <?php if ($order->status !== 'complete' || ($has_licenses && $license_count > 0)) : 
                        // Check if any licenses are expired (simplified check)
                        $needs_renewal = false;
                        if ($has_licenses && function_exists('edd_software_licensing')) {
                            $licenses = edd_software_licensing()->get_licenses_of_purchase($order->id, $download_id);
                            foreach ($licenses as $license) {
                                if ($license->status === 'expired' || 
                                    (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00' && strtotime($license->expiration) < time())) {
                                    $needs_renewal = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($needs_renewal) :
                    ?>
                    <a href="<?php echo esc_url(edd_get_checkout_uri()); ?>?edd_action=purchase_renewal&license_id=<?php echo esc_attr($licenses[0]->ID ?? ''); ?>" class="btn btn-success">
                        <i class="fas fa-sync-alt"></i> <?php esc_html_e('Renew', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <?php endif; endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; endforeach; ?>
    </tbody>
</table>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-box-open"></i>
    </div>
    <h3 class="empty-title"><?php esc_html_e('No Purchases Yet', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="empty-text"><?php esc_html_e('You haven\'t made any purchases yet. Start exploring our products!', 'edd-customer-dashboard-pro'); ?></p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo esc_url(home_url('/downloads/')); ?>" class="btn btn-primary">
            <i class="fas fa-store"></i> <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </a>
        
        <?php if (function_exists('edd_wl_get_wish_list')) : ?>
        <button onclick="window.AuroraDashboard?.switchSection('wishlist')" class="btn btn-outline">
            <i class="fas fa-heart"></i> <?php esc_html_e('View Wishlist', 'edd-customer-dashboard-pro'); ?>
        </button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>