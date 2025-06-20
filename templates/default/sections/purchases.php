<?php
/**
 * Enhanced Purchases Section Template with Receipt Links
 */

if (!defined('ABSPATH')) {
    exit;
}

$payments = $dashboard_data->get_customer_purchases($customer);
?>

<h2 class="eddcdp-section-title"><?php esc_html_e('Your Orders & Purchases', 'edd-customer-dashboard-pro'); ?></h2>

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
                                    // translators: %d is the number of additional items in the order
                                    printf(esc_html__(' + %d more', 'edd-customer-dashboard-pro'), count($downloads) - 1);
                                }
                            }
                            ?>
                        </div>
                        <div class="eddcdp-order-meta">
                            <span class="eddcdp-order-number"><?php 
                            // translators: %s is the order number
                            printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($payment->number)); 
                            ?></span>
                            <span class="eddcdp-order-date"><?php echo esc_html($dashboard_data->format_date($payment->date)); ?></span>
                            <span class="eddcdp-order-total"><?php echo esc_html($dashboard_data->format_currency($payment->total)); ?></span>
                        </div>
                    </div>
                    <span class="eddcdp-status-badge eddcdp-status-<?php echo esc_attr($payment->status); ?>">
                        <?php echo esc_html($dashboard_data->get_payment_status_label($payment)); ?>
                    </span>
                </div>
                
                <?php if ($downloads) : ?>
                    <div class="eddcdp-order-products">
                        <?php foreach ($downloads as $download) : ?>
                            <div class="eddcdp-product-row">
                                <div class="eddcdp-product-details">
                                    <strong><?php echo esc_html(get_the_title($download['id'])); ?></strong>
                                    <?php if (edd_use_skus() && edd_get_download_sku($download['id'])) : ?>
                                        <div class="eddcdp-product-meta"><?php 
                                        // translators: %s is the product SKU
                                        printf(esc_html__('SKU: %s', 'edd-customer-dashboard-pro'), esc_html(edd_get_download_sku($download['id']))); 
                                        ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="eddcdp-product-actions">
                                    <?php if ($dashboard_data->can_download_file($payment, $download['id'])) : ?>
                                        <?php 
                                        $download_files = $dashboard_data->get_download_files($download['id']);
                                        if ($download_files) : ?>
                                            <a href="<?php echo esc_url($dashboard_data->get_download_url($payment->key, $payment->email, 0, $download['id'])); ?>" class="eddcdp-btn eddcdp-btn-download">
                                                🔽 <?php esc_html_e('Download', 'edd-customer-dashboard-pro'); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="eddcdp-order-actions">
                    <!-- Enhanced Order Details Link to show receipt within dashboard -->
                    <a href="<?php echo esc_url(add_query_arg('payment_key', $payment->key)); ?>" class="eddcdp-btn eddcdp-btn-secondary">
                        📋 <?php esc_html_e('Order Details', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    
                    <!-- Invoice Link (if EDD Invoices is available) -->
                    <?php if (function_exists('edd_get_receipt_page_uri')) : ?>
                        <a href="<?php echo esc_url(edd_get_receipt_page_uri($payment->ID)); ?>" class="eddcdp-btn eddcdp-btn-secondary" target="_blank">
                            🧾 <?php esc_html_e('View Invoice', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- License Management Link -->
                    <?php if ($dashboard_data->is_licensing_active()) : ?>
                        <a href="#" class="eddcdp-btn eddcdp-btn-secondary" onclick="document.querySelector('[data-section=licenses]').click(); return false;">
                            🔑 <?php esc_html_e('Manage Licenses', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Support Link for this specific order -->
                    <a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>?subject=<?php echo esc_attr(sprintf(__('Question about Order #%s', 'edd-customer-dashboard-pro'), $payment->number)); ?>" class="eddcdp-btn eddcdp-btn-secondary">
                        💬 <?php esc_html_e('Support', 'edd-customer-dashboard-pro'); ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="eddcdp-empty-state">
            <div class="eddcdp-empty-icon">📦</div>
            <h3><?php esc_html_e('No purchases yet', 'edd-customer-dashboard-pro'); ?></h3>
            <p><?php esc_html_e('When you make your first purchase, it will appear here.', 'edd-customer-dashboard-pro'); ?></p>
            <a href="<?php echo esc_url(edd_get_checkout_uri()); ?>" class="eddcdp-btn"><?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?></a>
        </div>
    <?php endif; ?>
</div>