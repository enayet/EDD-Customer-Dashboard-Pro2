<?php
/**
 * Aurora Downloads Section Template - EDD 3.0+ Compatible
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
    echo '<div class="empty-state">';
    echo '<div class="empty-icon"><i class="fas fa-user-times"></i></div>';
    echo '<h3 class="empty-title">' . esc_html__('Customer data not found.', 'edd-customer-dashboard-pro') . '</h3>';
    echo '</div>';
    return;
}

// Get download logs using EDD 3.0+ method
$download_logs = edd_get_file_download_logs(array(
    'customer' => $customer->id,
    'number' => 50,
    'orderby' => 'date_created',
    'order' => 'DESC'
));
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1 class="dashboard-title"><?php esc_html_e('Download History', 'edd-customer-dashboard-pro'); ?></h1>
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="<?php esc_attr_e('Search downloads...', 'edd-customer-dashboard-pro'); ?>">
    </div>
</div>

<?php if ($download_logs) : ?>
<!-- Downloads Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: 25px;">
    <div class="stat-card downloads">
        <div class="stat-value"><?php echo esc_html(number_format_i18n(count($download_logs))); ?></div>
        <div class="stat-label">
            <i class="fas fa-download"></i>
            <?php esc_html_e('Total Downloads', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php 
        // Count recent downloads (last 30 days)
        $recent_count = 0;
        $thirty_days_ago = strtotime('-30 days');
        foreach ($download_logs as $log) {
            if (strtotime($log->date_created) > $thirty_days_ago) {
                $recent_count++;
            }
        }
        echo esc_html(number_format_i18n($recent_count)); 
        ?></div>
        <div class="stat-label">
            <i class="fas fa-calendar-alt"></i>
            <?php esc_html_e('Last 30 Days', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php 
        // Count unique products downloaded
        $unique_products = array();
        foreach ($download_logs as $log) {
            $unique_products[$log->product_id] = true;
        }
        echo esc_html(number_format_i18n(count($unique_products))); 
        ?></div>
        <div class="stat-label">
            <i class="fas fa-box"></i>
            <?php esc_html_e('Unique Products', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>

<!-- Downloads Table -->
<table class="products-table">
    <thead>
        <tr>
            <th><?php esc_html_e('Product', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('File', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Downloaded', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Status', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Actions', 'edd-customer-dashboard-pro'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($download_logs as $log) : 
            $download_id = $log->product_id;
            $download_name = get_the_title($download_id);
            $download_date = $log->date_created;
            $file_id = $log->file_id;
            
            // Get order info if available
            $order_id = !empty($log->order_id) ? $log->order_id : 0;
            $order = $order_id ? edd_get_order($order_id) : null;
            $order_number = $order ? $order->get_number() : '';
            
            // Get file info
            $files = edd_get_download_files($download_id);
            $file_name = '';
            if ($files && isset($files[$file_id])) {
                $file_name = $files[$file_id]['name'];
            } else {
                $file_name = esc_html__('Unknown File', 'edd-customer-dashboard-pro');
            }
            
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
            $remaining = esc_html__('Unlimited', 'edd-customer-dashboard-pro');
            
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
                /* translators: %1$d: remaining downloads, %2$d: total limit */
                $remaining = sprintf(esc_html__('%1$d of %2$d', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($remaining_count)), esc_html(number_format_i18n($limit)));
            }
            
            // Determine if download is recent (within 7 days)
            $is_recent = (strtotime($download_date) > strtotime('-7 days'));
            
            // Get product icon
            $product_icon = 'fas fa-file';
            if (stripos($download_name, 'plugin') !== false) {
                $product_icon = 'fas fa-plug';
            } elseif (stripos($download_name, 'theme') !== false) {
                $product_icon = 'fas fa-paint-brush';
            } elseif (stripos($file_name, '.zip') !== false) {
                $product_icon = 'fas fa-file-archive';
            } elseif (stripos($file_name, '.pdf') !== false) {
                $product_icon = 'fas fa-file-pdf';
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
                            <?php if ($order_number) : ?>
                                <?php 
                                /* translators: %s: Order number */
                                printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($order_number)); 
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-weight: 500; color: var(--aurora-dark);"><?php echo esc_html($file_name); ?></div>
                <div style="font-size: 0.85rem; color: var(--aurora-gray); margin-top: 3px;">
                    <strong><?php esc_html_e('Downloads remaining:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html($remaining); ?>
                </div>
            </td>
            <td>
                <div style="font-weight: 500;">
                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($download_date))); ?>
                </div>
                <div style="font-size: 0.85rem; color: var(--aurora-gray);">
                    <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($download_date))); ?>
                </div>
            </td>
            <td>
                <?php if ($limit && $downloads_used >= $limit) : ?>
                    <span class="status-badge status-expired">
                        <i class="fas fa-times-circle"></i> <?php esc_html_e('Limit Reached', 'edd-customer-dashboard-pro'); ?>
                    </span>
                <?php elseif ($is_recent) : ?>
                    <span class="status-badge" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
                        <i class="fas fa-clock"></i> <?php esc_html_e('Recent', 'edd-customer-dashboard-pro'); ?>
                    </span>
                <?php else : ?>
                    <span class="status-badge" style="background: rgba(149, 165, 166, 0.1); color: #95a5a6;">
                        <i class="fas fa-history"></i> <?php esc_html_e('Previous', 'edd-customer-dashboard-pro'); ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <?php if ($has_access && (!$limit || $downloads_used < $limit)) : ?>
                    <a href="<?php echo esc_url($download_url); ?>" class="btn btn-success btn-download">
                        <i class="fas fa-download"></i> <?php esc_html_e('Download Again', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <?php else : ?>
                    <span class="btn" style="background: #95a5a6; color: white; cursor: not-allowed;">
                        <i class="fas fa-ban"></i> <?php esc_html_e('Not Available', 'edd-customer-dashboard-pro'); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($order_id) : ?>
                    <a href="<?php echo esc_url(add_query_arg('eddcdp_order', $order_id, get_permalink())); ?>" class="btn btn-outline">
                        <i class="fas fa-info-circle"></i> <?php esc_html_e('Order Details', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-download"></i>
    </div>
    <h3 class="empty-title"><?php esc_html_e('No Downloads Yet', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="empty-text"><?php esc_html_e('You haven\'t downloaded any files yet. Make a purchase to get started!', 'edd-customer-dashboard-pro'); ?></p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <button onclick="window.AuroraDashboard?.switchSection('products')" class="btn btn-primary">
            <i class="fas fa-box-open"></i> <?php esc_html_e('View My Products', 'edd-customer-dashboard-pro'); ?>
        </button>
        <a href="<?php echo esc_url(home_url('/downloads/')); ?>" class="btn btn-outline">
            <i class="fas fa-store"></i> <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
</div>
<?php endif; ?>