<?php
/**
 * Aurora Template - Order Details Section
 * File: templates/aurora/sections/order-details.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$order_details = EDDCDP_Order_Details::instance();
$order = $order_details->get_current_order();

if (!$order) {
    ?>
    <div class="error-container">
        <div class="error-card">
            <i class="fas fa-exclamation-triangle"></i>
            <p><?php esc_html_e('Order not found.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    </div>
    <?php
    return;
}

$order_items = $order->get_items();
$billing_address = $order->get_address();

// Get status styling for Aurora theme
$status_config = array(
    'complete' => array(
        'class' => 'status-complete',
        'icon' => 'fas fa-check-circle',
        'label' => esc_html__('Complete', 'edd-customer-dashboard-pro')
    ),
    'pending' => array(
        'class' => 'status-pending', 
        'icon' => 'fas fa-clock',
        'label' => esc_html__('Pending', 'edd-customer-dashboard-pro')
    ),
    'processing' => array(
        'class' => 'status-processing',
        'icon' => 'fas fa-cog fa-spin',
        'label' => esc_html__('Processing', 'edd-customer-dashboard-pro')
    ),
    'failed' => array(
        'class' => 'status-failed',
        'icon' => 'fas fa-times-circle',
        'label' => esc_html__('Failed', 'edd-customer-dashboard-pro')
    )
);

$status_info = isset($status_config[$order->status]) ? $status_config[$order->status] : array(
    'class' => 'status-unknown',
    'icon' => 'fas fa-question-circle',
    'label' => ucfirst($order->status)
);

// Generate invoice URL
$invoice_hash = md5($order->id . $order->email . $order->date_created);
$invoice_url = home_url('/?edd_action=view_invoice&payment_id=' . $order->id . '&invoice=' . $invoice_hash);
?>

<div class="order-details-container">
    
    <!-- Header with navigation -->
    <div class="order-header">
        <div class="header-nav">
            <a href="<?php echo esc_url($order_details->get_return_url()); ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <?php esc_html_e('Back to Dashboard', 'edd-customer-dashboard-pro'); ?>
            </a>
            
            <a href="<?php echo esc_url($invoice_url); ?>" class="invoice-btn">
                <i class="fas fa-file-invoice"></i>
                <?php esc_html_e('View Invoice', 'edd-customer-dashboard-pro'); ?>
            </a>
        </div>
        
        <div class="order-info">
            <h1 class="order-title">
                <?php 
                /* translators: %s: Order number */
                printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($order->get_number())); 
                ?>
            </h1>
            <div class="order-meta">
                <span class="order-date">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->date_created))); ?>
                </span>
                <span class="order-status <?php echo esc_attr($status_info['class']); ?>">
                    <i class="<?php echo esc_attr($status_info['icon']); ?>"></i>
                    <?php echo esc_html($status_info['label']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="order-section">
        <h2 class="section-title">
            <i class="fas fa-shopping-bag"></i>
            <?php esc_html_e('Order Items', 'edd-customer-dashboard-pro'); ?>
        </h2>
        
        <div class="items-grid">
            <?php foreach ($order_items as $item) : 
                $download = edd_get_download($item->product_id);
                $price_id = $item->price_id;
                $download_files = $order_details->get_order_item_download_files($order->id, $item->product_id, $price_id);
            ?>
            <div class="item-card">
                <div class="item-info">
                    <h3 class="item-name"><?php echo esc_html($item->product_name); ?></h3>
                    <div class="item-details">
                        <span class="item-price">
                            <?php echo edd_currency_filter(edd_format_amount($item->total)); ?>
                        </span>
                        <?php if ($item->quantity > 1) : ?>
                        <span class="item-quantity">
                            <?php 
                            /* translators: %d: Item quantity */
                            printf(esc_html__('Qty: %d', 'edd-customer-dashboard-pro'), (int) $item->quantity); 
                            ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($order->status === 'complete' && !empty($download_files)) : ?>
                <div class="item-downloads">
                    <?php foreach ($download_files as $file) : ?>
                    <a href="<?php echo esc_url($file['url']); ?>" class="download-btn" target="_blank">
                        <i class="fas fa-download"></i>
                        <?php echo esc_html($file['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="order-section">
        <h2 class="section-title">
            <i class="fas fa-calculator"></i>
            <?php esc_html_e('Order Summary', 'edd-customer-dashboard-pro'); ?>
        </h2>
        
        <div class="summary-card">
            <div class="summary-row">
                <span class="summary-label"><?php esc_html_e('Subtotal:', 'edd-customer-dashboard-pro'); ?></span>
                <span class="summary-value"><?php echo edd_currency_filter(edd_format_amount($order->subtotal)); ?></span>
            </div>
            
            <?php if ($order->tax > 0) : ?>
            <div class="summary-row">
                <span class="summary-label"><?php esc_html_e('Tax:', 'edd-customer-dashboard-pro'); ?></span>
                <span class="summary-value"><?php echo edd_currency_filter(edd_format_amount($order->tax)); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($order->discount_amount > 0) : ?>
            <div class="summary-row discount">
                <span class="summary-label"><?php esc_html_e('Discount:', 'edd-customer-dashboard-pro'); ?></span>
                <span class="summary-value">-<?php echo edd_currency_filter(edd_format_amount($order->discount_amount)); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="summary-row total">
                <span class="summary-label"><?php esc_html_e('Total:', 'edd-customer-dashboard-pro'); ?></span>
                <span class="summary-value"><?php echo edd_currency_filter(edd_format_amount($order->total)); ?></span>
            </div>
        </div>
    </div>

    <!-- Payment & Billing Information -->
    <div class="info-grid">
        <!-- Payment Information -->
        <div class="info-section">
            <h2 class="section-title">
                <i class="fas fa-credit-card"></i>
                <?php esc_html_e('Payment Information', 'edd-customer-dashboard-pro'); ?>
            </h2>
            
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label"><?php esc_html_e('Payment Method:', 'edd-customer-dashboard-pro'); ?></span>
                    <span class="info-value"><?php echo esc_html($order->gateway); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label"><?php esc_html_e('Email:', 'edd-customer-dashboard-pro'); ?></span>
                    <span class="info-value"><?php echo esc_html($order->email); ?></span>
                </div>
                
                <?php if (!empty($order->transaction_id)) : ?>
                <div class="info-row">
                    <span class="info-label"><?php esc_html_e('Transaction ID:', 'edd-customer-dashboard-pro'); ?></span>
                    <span class="info-value transaction-id"><?php echo esc_html($order->transaction_id); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Billing Address -->
        <?php if ($billing_address && !empty(array_filter((array)$billing_address))) : ?>
        <div class="info-section">
            <h2 class="section-title">
                <i class="fas fa-map-marker-alt"></i>
                <?php esc_html_e('Billing Address', 'edd-customer-dashboard-pro'); ?>
            </h2>
            
            <div class="info-card">
                <div class="address-content">
                    <?php if (!empty($billing_address->line1)) : ?>
                        <?php echo esc_html($billing_address->line1); ?><br>
                    <?php endif; ?>
                    
                    <?php if (!empty($billing_address->line2)) : ?>
                        <?php echo esc_html($billing_address->line2); ?><br>
                    <?php endif; ?>
                    
                    <?php if (!empty($billing_address->city)) : ?>
                        <?php echo esc_html($billing_address->city); ?>
                        <?php if (!empty($billing_address->region)) : ?>, <?php echo esc_html($billing_address->region); ?><?php endif; ?>
                        <?php if (!empty($billing_address->postal_code)) : ?> <?php echo esc_html($billing_address->postal_code); ?><?php endif; ?><br>
                    <?php endif; ?>
                    
                    <?php if (!empty($billing_address->country)) : ?>
                        <?php echo esc_html($billing_address->country); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>