<?php
/**
 * Aurora Template - Purchases Section
 */

if (!defined('ABSPATH')) {
    exit;
}

$payments = $dashboard_data->get_customer_purchases($customer);

// Function to get product icon based on product name
function get_product_icon($product_name) {
    $name_lower = strtolower($product_name);
    
    if (strpos($name_lower, 'voice') !== false || strpos($name_lower, 'audio') !== false) {
        return 'fas fa-microphone-alt';
    } elseif (strpos($name_lower, 'ecommerce') !== false || strpos($name_lower, 'shop') !== false || strpos($name_lower, 'store') !== false) {
        return 'fas fa-shopping-cart';
    } elseif (strpos($name_lower, 'seo') !== false || strpos($name_lower, 'analytics') !== false) {
        return 'fas fa-chart-line';
    } elseif (strpos($name_lower, 'theme') !== false || strpos($name_lower, 'design') !== false) {
        return 'fas fa-paint-brush';
    } elseif (strpos($name_lower, 'plugin') !== false) {
        return 'fas fa-plug';
    } else {
        return 'fas fa-box';
    }
}
?>

<h2 class="eddcdp-section-title"><?php esc_html_e('My Digital Products', 'edd-customer-dashboard-pro'); ?></h2>

<?php if ($payments) : ?>
    <table class="eddcdp-products-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Product', 'edd-customer-dashboard-pro'); ?></th>
                <th><?php esc_html_e('License', 'edd-customer-dashboard-pro'); ?></th>
                <th><?php esc_html_e('Status', 'edd-customer-dashboard-pro'); ?></th>
                <th><?php esc_html_e('Actions', 'edd-customer-dashboard-pro'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment) : ?>
                <?php $downloads = edd_get_payment_meta_downloads($payment->ID); ?>
                <?php if ($downloads) : ?>
                    <?php foreach ($downloads as $download) : ?>
                        <?php $product_name = get_the_title($download['id']); ?>
                        <tr class="eddcdp-product-row">
                            <td>
                                <div class="eddcdp-product-info">
                                    <div class="eddcdp-product-icon">
                                        <i class="<?php echo esc_attr(get_product_icon($product_name)); ?>"></i>
                                    </div>
                                    <div>
                                        <div class="eddcdp-product-name"><?php echo esc_html($product_name); ?></div>
                                        <div class="eddcdp-product-meta">
                                            <?php 
                                            // translators: %1$s is the purchase date, %2$s is the order number
                                            printf(
                                                esc_html__('Purchased: %1$s • Order #%2$s', 'edd-customer-dashboard-pro'),
                                                esc_html($dashboard_data->format_date($payment->date)),
                                                esc_html($payment->number)
                                            ); 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($dashboard_data->is_licensing_active()) : ?>
                                    <?php 
                                    $licenses = $dashboard_data->get_customer_licenses($payment->user_id);
                                    $product_license = null;
                                    
                                    // Find license for this product
                                    if ($licenses) {
                                        foreach ($licenses as $license) {
                                            if ($license->download_id == $download['id']) {
                                                $product_license = $license;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    if ($product_license) : ?>
                                        <div class="eddcdp-license-key" title="<?php esc_attr_e('Click to copy', 'edd-customer-dashboard-pro'); ?>">
                                            <?php echo esc_html($product_license->key); ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="eddcdp-no-license"><?php esc_html_e('No license', 'edd-customer-dashboard-pro'); ?></span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="eddcdp-no-license"><?php esc_html_e('N/A', 'edd-customer-dashboard-pro'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($dashboard_data->is_licensing_active() && isset($product_license)) : ?>
                                    <span class="eddcdp-status-badge <?php echo $product_license->is_expired() ? 'eddcdp-status-expired' : 'eddcdp-status-active'; ?>">
                                        <?php echo $product_license->is_expired() ? esc_html__('Expired', 'edd-customer-dashboard-pro') : esc_html__('Active', 'edd-customer-dashboard-pro'); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="eddcdp-status-badge eddcdp-status-<?php echo esc_attr($payment->status); ?>">
                                        <?php echo esc_html($dashboard_data->get_payment_status_label($payment)); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($dashboard_data->can_download_file($payment, $download['id'])) : ?>
                                    <?php $download_files = $dashboard_data->get_download_files($download['id']); ?>
                                    <?php if ($download_files) : ?>
                                        <a href="<?php echo esc_url($dashboard_data->get_download_url($payment->key, $payment->email, 0, $download['id'])); ?>" class="eddcdp-btn eddcdp-btn-secondary">
                                            <i class="fas fa-download"></i> <?php esc_html_e('Download', 'edd-customer-dashboard-pro'); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if ($dashboard_data->is_licensing_active() && isset($product_license)) : ?>
                                    <?php if ($product_license->is_expired()) : ?>
                                        <a href="<?php echo esc_url(edd_get_checkout_uri(array('edd_license_key' => $product_license->key, 'download_id' => $product_license->download_id))); ?>" class="eddcdp-btn eddcdp-btn-success">
                                            <i class="fas fa-sync-alt"></i> <?php esc_html_e('Renew', 'edd-customer-dashboard-pro'); ?>
                                        </a>
                                    <?php else : ?>
                                        <button class="eddcdp-btn eddcdp-btn-secondary" onclick="document.querySelector('[data-section=licenses]').click(); return false;">
                                            <i class="fas fa-key"></i> <?php esc_html_e('License', 'edd-customer-dashboard-pro'); ?>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <div class="eddcdp-empty-state">
        <div class="eddcdp-empty-icon">
            <i class="fas fa-box-open"></i>
        </div>
        <h3><?php esc_html_e('No purchases yet', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('When you make your first purchase, it will appear here.', 'edd-customer-dashboard-pro'); ?></p>
        <a href="<?php echo esc_url(home_url()); ?>" class="eddcdp-btn eddcdp-btn-primary">
            <i class="fas fa-store"></i> <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
<?php endif; ?>