<?php
/**
 * Purchases Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$payments = $dashboard_data->get_customer_purchases($customer);
?>

<h2 class="eddcdp-section-title"><?php _e('Your Orders & Purchases', 'edd-customer-dashboard-pro'); ?></h2>

<div class="eddcdp-purchase-list">
    <?php if ($payments) : ?>
        <?php foreach ($payments as $payment) : ?>
            <?php $downloads = edd_get_payment_meta_downloads($payment->ID); ?>
            <div class="eddcdp-purchase-item">
                <div class="eddcdp-purchase-header">
                    <div class="eddcdp-order-info">
                        <div class="eddcdp-product-name">
                            <?php 
                            if ($downloads && count($downloads) > 0) {
                                $first_download = get_the_title($downloads[0]['id']);
                                echo esc_html($first_download);
                                if (count($downloads) > 1) {
                                    printf(__(' + %d more', 'edd-customer-dashboard-pro'), count($downloads) - 1);
                                }
                            }
                            ?>
                        </div>
                        <div class="eddcdp-order-meta">
                            <span class="eddcdp-order-number"><?php printf(__('Order #%s', 'edd-customer-dashboard-pro'), $payment->number); ?></span>
                            <span class="eddcdp-order-date"><?php echo $dashboard_data->format_date($payment->date); ?></span>
                            <span class="eddcdp-order-total"><?php echo $dashboard_data->format_currency($payment->total); ?></span>
                        </div>
                    </div>
                    <span class="eddcdp-status-badge eddcdp-status-<?php echo esc_attr($payment->status); ?>">
                        <?php echo $dashboard_data->get_payment_status_label($payment); ?>
                    </span>
                </div>
                
                <?php if ($downloads) : ?>
                    <div class="eddcdp-order-products">
                        <?php foreach ($downloads as $download) : ?>
                            <div class="eddcdp-product-row">
                                <div class="eddcdp-product-details">
                                    <strong><?php echo get_the_title($download['id']); ?></strong>
                                    <?php if (edd_use_skus() && edd_get_download_sku($download['id'])) : ?>
                                        <div class="eddcdp-product-meta"><?php printf(__('SKU: %s', 'edd-customer-dashboard-pro'), edd_get_download_sku($download['id'])); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="eddcdp-product-actions">
                                    <?php if ($dashboard_data->can_download_file($payment, $download['id'])) : ?>
                                        <?php 
                                        $download_files = $dashboard_data->get_download_files($download['id']);
                                        if ($download_files) : ?>
                                            <a href="<?php echo $dashboard_data->get_download_url($payment->key, $payment->email, 0, $download['id']); ?>" class="eddcdp-btn eddcdp-btn-download">
                                                🔽 <?php _e('Download', 'edd-customer-dashboard-pro'); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="eddcdp-order-actions">
                    <a href="<?php echo $dashboard_data->get_receipt_url($payment); ?>" class="eddcdp-btn eddcdp-btn-secondary">
                        📋 <?php _e('Order Details', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <?php if (function_exists('edd_get_receipt_page_uri')) : ?>
                        <a href="<?php echo edd_get_receipt_page_uri($payment->ID); ?>" class="eddcdp-btn eddcdp-btn-secondary">
                            📄 <?php _e('View Invoice', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($dashboard_data->is_licensing_active()) : ?>
                        <a href="#" class="eddcdp-btn eddcdp-btn-secondary" onclick="document.querySelector('[data-section=licenses]').click(); return false;">
                            🔑 <?php _e('Manage Licenses', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="eddcdp-empty-state">
            <div class="eddcdp-empty-icon">📦</div>
            <h3><?php _e('No purchases yet', 'edd-customer-dashboard-pro'); ?></h3>
            <p><?php _e('When you make your first purchase, it will appear here.', 'edd-customer-dashboard-pro'); ?></p>
            <a href="<?php echo edd_get_checkout_uri(); ?>" class="eddcdp-btn"><?php _e('Browse Products', 'edd-customer-dashboard-pro'); ?></a>
        </div>
    <?php endif; ?>
</div>