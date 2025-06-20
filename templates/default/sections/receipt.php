<?php
/**
 * Order Details Receipt Section Template (Original Functionality)
 * File: templates/default/sections/receipt.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get payment downloads
$downloads = edd_get_payment_meta_downloads($payment->ID);
$user_info = edd_get_payment_meta_user_info($payment->ID);
$payment_meta = edd_get_payment_meta($payment->ID);
$payment_date = date_i18n(get_option('date_format'), strtotime($payment->date));
?>

<div class="eddcdp-receipt-section">
    <!-- Receipt Header -->
    <div class="eddcdp-receipt-header">
        <div class="eddcdp-receipt-title-area">
            <h2 class="eddcdp-section-title">
                📄 <?php esc_html_e('Order Receipt', 'edd-customer-dashboard-pro'); ?>
            </h2>
            <p class="eddcdp-receipt-subtitle">
                <?php 
                // translators: %s is the order number
                printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($payment->number)); 
                ?>
            </p>
        </div>
        <div class="eddcdp-receipt-actions">
            <a href="<?php echo esc_url(remove_query_arg('payment_key')); ?>" class="eddcdp-btn eddcdp-btn-secondary">
                ← <?php esc_html_e('Back to Dashboard', 'edd-customer-dashboard-pro'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg('view', 'invoice')); ?>" class="eddcdp-btn eddcdp-btn-primary">
                🧾 <?php esc_html_e('View Invoice', 'edd-customer-dashboard-pro'); ?>
            </a>
            <button onclick="window.print()" class="eddcdp-btn eddcdp-btn-outline">
                🖨️ <?php esc_html_e('Print Receipt', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </div>

    <!-- Receipt Content -->
    <div class="eddcdp-receipt-content">
        
        <!-- Order Summary -->
        <div class="eddcdp-receipt-card">
            <h3 class="eddcdp-receipt-card-title">
                <?php esc_html_e('Order Summary', 'edd-customer-dashboard-pro'); ?>
            </h3>
            
            <div class="eddcdp-receipt-grid">
                <div class="eddcdp-receipt-item">
                    <strong><?php esc_html_e('Order Number:', 'edd-customer-dashboard-pro'); ?></strong>
                    <span>#<?php echo esc_html($payment->number); ?></span>
                </div>
                <div class="eddcdp-receipt-item">
                    <strong><?php esc_html_e('Order Date:', 'edd-customer-dashboard-pro'); ?></strong>
                    <span><?php echo esc_html($payment_date); ?></span>
                </div>
                <div class="eddcdp-receipt-item">
                    <strong><?php esc_html_e('Payment Status:', 'edd-customer-dashboard-pro'); ?></strong>
                    <span class="eddcdp-status-badge eddcdp-status-<?php echo esc_attr($payment->status); ?>">
                        <?php echo esc_html($dashboard_data->get_payment_status_label($payment)); ?>
                    </span>
                </div>
                <div class="eddcdp-receipt-item">
                    <strong><?php esc_html_e('Payment Method:', 'edd-customer-dashboard-pro'); ?></strong>
                    <span><?php echo esc_html(edd_get_gateway_admin_label(edd_get_payment_gateway($payment->ID))); ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="eddcdp-receipt-card">
            <h3 class="eddcdp-receipt-card-title">
                <?php esc_html_e('Customer Information', 'edd-customer-dashboard-pro'); ?>
            </h3>
            
            <div class="eddcdp-receipt-customer-info">
                <div class="eddcdp-receipt-customer-section">
                    <h4><?php esc_html_e('Billing Details', 'edd-customer-dashboard-pro'); ?></h4>
                    <div class="eddcdp-receipt-address">
                        <?php if (!empty($user_info['first_name']) || !empty($user_info['last_name'])) : ?>
                            <div class="eddcdp-receipt-name">
                                <?php echo esc_html(trim($user_info['first_name'] . ' ' . $user_info['last_name'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="eddcdp-receipt-email">
                            <?php echo esc_html($payment->email); ?>
                        </div>
                        
                        <?php if (!empty($user_info['address'])) : ?>
                            <div class="eddcdp-receipt-full-address">
                                <?php 
                                $address = $user_info['address'];
                                $address_lines = array();
                                
                                if (!empty($address['line1'])) {
                                    $address_lines[] = esc_html($address['line1']);
                                }
                                if (!empty($address['line2'])) {
                                    $address_lines[] = esc_html($address['line2']);
                                }
                                
                                $city_state_zip = array();
                                if (!empty($address['city'])) {
                                    $city_state_zip[] = esc_html($address['city']);
                                }
                                if (!empty($address['state'])) {
                                    $city_state_zip[] = esc_html($address['state']);
                                }
                                if (!empty($address['zip'])) {
                                    $city_state_zip[] = esc_html($address['zip']);
                                }
                                
                                if (!empty($city_state_zip)) {
                                    $address_lines[] = implode(', ', $city_state_zip);
                                }
                                
                                if (!empty($address['country'])) {
                                    $address_lines[] = esc_html($address['country']);
                                }
                                
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above
                                echo implode('<br>', $address_lines);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="eddcdp-receipt-card">
            <h3 class="eddcdp-receipt-card-title">
                <?php esc_html_e('Order Items', 'edd-customer-dashboard-pro'); ?>
            </h3>
            
            <div class="eddcdp-receipt-items">
                <?php if ($downloads) : ?>
                    <?php foreach ($downloads as $download) : ?>
                        <div class="eddcdp-receipt-download-item">
                            <div class="eddcdp-receipt-download-info">
                                <div class="eddcdp-receipt-download-name">
                                    <?php echo esc_html(get_the_title($download['id'])); ?>
                                </div>
                                
                                <?php if (!empty($download['options']['price_name'])) : ?>
                                    <div class="eddcdp-receipt-download-option">
                                        <?php echo esc_html($download['options']['price_name']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (edd_use_skus() && edd_get_download_sku($download['id'])) : ?>
                                    <div class="eddcdp-receipt-download-sku">
                                        <?php 
                                        // translators: %s is the product SKU
                                        printf(esc_html__('SKU: %s', 'edd-customer-dashboard-pro'), esc_html(edd_get_download_sku($download['id']))); 
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="eddcdp-receipt-download-price">
                                <?php echo esc_html($dashboard_data->format_currency($dashboard_data->get_download_price_from_payment($download, $payment->ID))); ?>
                            </div>
                            
                            <div class="eddcdp-receipt-download-actions">
                                <?php if ($dashboard_data->can_download_file($payment, $download['id'])) : ?>
                                    <?php $download_files = $dashboard_data->get_download_files($download['id']); ?>
                                    <?php if ($download_files) : ?>
                                        <a href="<?php echo esc_url($dashboard_data->get_download_url($payment_key, $payment->email, 0, $download['id'])); ?>" 
                                           class="eddcdp-btn eddcdp-btn-download eddcdp-btn-small">
                                            🔽 <?php esc_html_e('Download', 'edd-customer-dashboard-pro'); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if ($dashboard_data->is_licensing_active()) : ?>
                                    <?php 
                                    $licenses = $dashboard_data->get_customer_licenses($user->ID);
                                    $download_has_license = false;
                                    foreach ($licenses as $license) {
                                        if ($license->download_id == $download['id'] && $license->payment_id == $payment->ID) {
                                            $download_has_license = true;
                                            break;
                                        }
                                    }
                                    ?>
                                    <?php if ($download_has_license) : ?>
                                        <a href="<?php echo esc_url(add_query_arg('section', 'licenses', remove_query_arg('payment_key'))); ?>" 
                                           class="eddcdp-btn eddcdp-btn-secondary eddcdp-btn-small">
                                            🔑 <?php esc_html_e('Manage License', 'edd-customer-dashboard-pro'); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="eddcdp-receipt-card">
            <h3 class="eddcdp-receipt-card-title">
                <?php esc_html_e('Payment Details', 'edd-customer-dashboard-pro'); ?>
            </h3>
            
            <div class="eddcdp-receipt-payment-summary">
                <div class="eddcdp-receipt-payment-row">
                    <span class="eddcdp-receipt-payment-label">
                        <?php esc_html_e('Subtotal:', 'edd-customer-dashboard-pro'); ?>
                    </span>
                    <span class="eddcdp-receipt-payment-value">
                        <?php echo esc_html($dashboard_data->format_currency(edd_get_payment_subtotal($payment->ID))); ?>
                    </span>
                </div>
                
                <?php 
                // Get discounts using the correct EDD method
                $cart_discounts = edd_get_payment_meta($payment->ID, '_edd_payment_discount', true);
                if (!empty($cart_discounts)) :
                    $subtotal = edd_get_payment_subtotal($payment->ID);
                    $total_before_tax = $payment->total - edd_get_payment_tax($payment->ID);
                    $discount_amount = $subtotal - $total_before_tax;
                    
                    if ($discount_amount > 0) :
                ?>
                    <div class="eddcdp-receipt-payment-row">
                        <span class="eddcdp-receipt-payment-label">
                            <?php esc_html_e('Discount:', 'edd-customer-dashboard-pro'); ?> (<?php echo esc_html($cart_discounts); ?>)
                        </span>
                        <span class="eddcdp-receipt-payment-value">
                            -<?php echo esc_html($dashboard_data->format_currency($discount_amount)); ?>
                        </span>
                    </div>
                <?php 
                    endif;
                endif; 
                ?>
                
                <?php $fees = edd_get_payment_fees($payment->ID); ?>
                <?php if (!empty($fees)) : ?>
                    <?php foreach ($fees as $fee) : ?>
                        <div class="eddcdp-receipt-payment-row">
                            <span class="eddcdp-receipt-payment-label">
                                <?php echo esc_html($fee['label']); ?>:
                            </span>
                            <span class="eddcdp-receipt-payment-value">
                                <?php echo esc_html($dashboard_data->format_currency($fee['amount'])); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php $tax = edd_get_payment_tax($payment->ID); ?>
                <?php if ($tax > 0) : ?>
                    <div class="eddcdp-receipt-payment-row">
                        <span class="eddcdp-receipt-payment-label">
                            <?php esc_html_e('Tax:', 'edd-customer-dashboard-pro'); ?>
                        </span>
                        <span class="eddcdp-receipt-payment-value">
                            <?php echo esc_html($dashboard_data->format_currency($tax)); ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <div class="eddcdp-receipt-payment-row eddcdp-receipt-payment-total">
                    <span class="eddcdp-receipt-payment-label">
                        <strong><?php esc_html_e('Total:', 'edd-customer-dashboard-pro'); ?></strong>
                    </span>
                    <span class="eddcdp-receipt-payment-value">
                        <strong><?php echo esc_html($dashboard_data->format_currency($payment->total)); ?></strong>
                    </span>
                </div>
                
                <?php if (!empty($payment_meta['key'])) : ?>
                    <div class="eddcdp-receipt-payment-row">
                        <span class="eddcdp-receipt-payment-label">
                            <?php esc_html_e('Payment Key:', 'edd-customer-dashboard-pro'); ?>
                        </span>
                        <span class="eddcdp-receipt-payment-value">
                            <code class="eddcdp-payment-key"><?php echo esc_html($payment_key); ?></code>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Additional Actions -->
        <div class="eddcdp-receipt-card">
            <h3 class="eddcdp-receipt-card-title">
                <?php esc_html_e('Need Help?', 'edd-customer-dashboard-pro'); ?>
            </h3>
            
            <div class="eddcdp-receipt-help">
                <p><?php esc_html_e('If you have any questions about this order, please contact our support team.', 'edd-customer-dashboard-pro'); ?></p>
                
                <div class="eddcdp-receipt-help-actions">
                    <a href="<?php echo esc_url(add_query_arg('section', 'support', remove_query_arg('payment_key'))); ?>" 
                       class="eddcdp-btn eddcdp-btn-outline">
                        💬 <?php esc_html_e('Contact Support', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    
                    <a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>?subject=<?php echo esc_attr(sprintf(__('Question about Order #%s', 'edd-customer-dashboard-pro'), $payment->number)); ?>" 
                       class="eddcdp-btn eddcdp-btn-outline">
                        ✉️ <?php esc_html_e('Email Support', 'edd-customer-dashboard-pro'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>