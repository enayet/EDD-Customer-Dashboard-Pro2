<?php
/**
 * Order Details Template Section - Updated
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$order_details = EDDCDP_Order_Details::instance();
$order = $order_details->get_current_order();

if (!$order) {
    echo '<div class="bg-red-50/80 rounded-2xl p-6 border border-red-200/50">';
    echo '<p class="text-red-800">' . __('Order not found.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

$order_items = $order->get_items();
$billing_address = $order->get_address();

// Get status styling
$status_classes = array(
    'complete' => 'bg-green-100 text-green-800',
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'failed' => 'bg-red-100 text-red-800'
);

$status_icons = array(
    'complete' => '✅',
    'pending' => '⏳',
    'processing' => '⚙️',
    'failed' => '❌'
);

$status_class = isset($status_classes[$order->status]) ? $status_classes[$order->status] : 'bg-gray-100 text-gray-800';
$status_icon = isset($status_icons[$order->status]) ? $status_icons[$order->status] : '📋';

// Generate invoice URL
$invoice_hash = md5($order->id . $order->email . $order->date_created);
$invoice_url = home_url('/?edd_action=view_invoice&payment_id=' . $order->id . '&invoice=' . $invoice_hash);
?>

<!-- Header with Back Button and Invoice -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <a href="<?php echo esc_url($order_details->get_return_url()); ?>" 
       class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium">
        ← <?php _e('Back to Dashboard', 'edd-customer-dashboard-pro'); ?>
    </a>
    
    <a href="<?php echo esc_url($invoice_url); ?>" 
       class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2 text-decoration-none">
        📄 <?php _e('View Invoice', 'edd-customer-dashboard-pro'); ?>
    </a>
</div>

<!-- Order Header -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-6 shadow-lg border border-white/20">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">
                <?php printf(__('Order #%s', 'edd-customer-dashboard-pro'), $order->get_number()); ?>
            </h2>
            <p class="text-gray-600">
                <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->date_created)); ?>
            </p>
        </div>
        <span class="<?php echo $status_class; ?> px-4 py-2 rounded-full text-sm font-medium w-fit">
            <?php echo $status_icon . ' ' . ucfirst($order->status); ?>
        </span>
    </div>
</div>

<!-- Order Items -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-6 shadow-lg border border-white/20">
    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Items Purchased', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div class="space-y-4">
        <?php foreach ($order_items as $item) : 
            $download_id = $item->product_id;
            $price_id = !empty($item->price_id) ? $item->price_id : null;
            
            // Get download files
            $files = edd_get_download_files($download_id, $price_id);
        ?>
        
        <div class="border border-gray-200 rounded-xl p-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800 text-lg"><?php echo esc_html($item->product_name); ?></h4>
                    <p class="text-gray-600 mt-1">
                        <?php printf(__('Quantity: %d × %s', 'edd-customer-dashboard-pro'), $item->quantity, edd_currency_filter(edd_format_amount($item->amount))); ?>
                    </p>
                    
                    <?php 
                    // Get license keys for this download if Software Licensing is active
                    if (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) {
                        $licenses = edd_software_licensing()->get_licenses_of_purchase($order->id, $download_id);
                        if ($licenses) :
                    ?>
                    <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                        <h6 class="text-sm font-semibold text-blue-900 mb-3 flex items-center gap-2">
                            🔑 <?php _e('License Keys', 'edd-customer-dashboard-pro'); ?>
                        </h6>
                        
                        <div class="space-y-3">
                            <?php foreach ($licenses as $license) : ?>
                            <div class="bg-white rounded-lg p-3 border border-blue-200">
                                <!-- License Key Row -->
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="flex-1">
                                        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-3 font-mono text-sm select-all cursor-pointer hover:border-indigo-400 transition-colors"
                                             onclick="copyLicenseKey(this, '<?php echo esc_js($license->license_key); ?>')">
                                            <?php echo esc_html($license->license_key); ?>
                                        </div>
                                    </div>
                                    <button onclick="copyLicenseKey(this, '<?php echo esc_js($license->license_key); ?>')" 
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                                        📋 <span class="copy-text"><?php _e('Copy', 'edd-customer-dashboard-pro'); ?></span>
                                    </button>
                                </div>
                                
                                <!-- License Status -->
                                <div class="flex flex-wrap gap-4 text-xs text-blue-700">
                                    <span class="flex items-center gap-1">
                                        <span class="w-2 h-2 bg-<?php echo $license->status === 'active' ? 'green' : 'red'; ?>-500 rounded-full"></span>
                                        <?php printf(__('Status: %s', 'edd-customer-dashboard-pro'), ucfirst($license->status)); ?>
                                    </span>
                                    
                                    <?php if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') : ?>
                                    <span class="flex items-center gap-1">
                                        ⏰ <?php printf(__('Expires: %s', 'edd-customer-dashboard-pro'), date_i18n(get_option('date_format'), strtotime($license->expiration))); ?>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <span class="flex items-center gap-1">
                                        🌐 <?php printf(__('Sites: %d/%s', 'edd-customer-dashboard-pro'), $license->activation_count, $license->activation_limit ?: __('∞', 'edd-customer-dashboard-pro')); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; } ?>
                </div>
                <div class="font-bold text-lg text-gray-800">
                    <?php echo edd_currency_filter(edd_format_amount($item->total)); ?>
                </div>
            </div>
            
            <?php if ($files && $order->status === 'complete') : ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h5 class="font-medium text-gray-700 mb-3">📥 <?php _e('Download Files:', 'edd-customer-dashboard-pro'); ?></h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach ($files as $file_key => $file) : 
                        $download_url = edd_get_download_file_url($order->payment_key, $order->email, $file_key, $download_id);
                    ?>
                    <a href="<?php echo esc_url($download_url); ?>" 
                       class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-3 rounded-lg font-medium hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2 text-decoration-none text-center">
                        <span class="text-lg">📥</span>
                        <div class="flex-1 min-w-0">
                            <div class="truncate"><?php echo esc_html($file['name']); ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php elseif ($order->status !== 'complete') : ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-yellow-800 text-sm">
                        ⏳ <?php _e('Downloads will be available once your order is complete.', 'edd-customer-dashboard-pro'); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php endforeach; ?>
    </div>
</div>

<!-- Order Summary -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-6 shadow-lg border border-white/20">
    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Order Summary', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div class="space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-gray-600"><?php _e('Subtotal:', 'edd-customer-dashboard-pro'); ?></span>
            <span class="font-medium"><?php echo edd_currency_filter(edd_format_amount($order->subtotal)); ?></span>
        </div>
        
        <?php if ($order->tax > 0) : ?>
        <div class="flex justify-between items-center">
            <span class="text-gray-600"><?php _e('Tax:', 'edd-customer-dashboard-pro'); ?></span>
            <span class="font-medium"><?php echo edd_currency_filter(edd_format_amount($order->tax)); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($order->discount_amount > 0) : ?>
        <div class="flex justify-between items-center">
            <span class="text-gray-600"><?php _e('Discount:', 'edd-customer-dashboard-pro'); ?></span>
            <span class="font-medium text-green-600">-<?php echo edd_currency_filter(edd_format_amount($order->discount_amount)); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="border-t border-gray-200 pt-3">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-800"><?php _e('Total:', 'edd-customer-dashboard-pro'); ?></span>
                <span class="text-lg font-bold text-gray-800"><?php echo edd_currency_filter(edd_format_amount($order->total)); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Payment & Billing Information -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Payment Information -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Payment Information', 'edd-customer-dashboard-pro'); ?></h3>
        
        <div class="space-y-3">
            <div>
                <span class="text-gray-600"><?php _e('Payment Method:', 'edd-customer-dashboard-pro'); ?></span>
                <div class="font-medium">
                    <?php 
                    $gateways = edd_get_payment_gateways();
                    if (isset($gateways[$order->gateway])) {
                        echo esc_html($gateways[$order->gateway]['admin_label']);
                    } else {
                        echo esc_html(ucfirst($order->gateway));
                    }
                    ?>
                </div>
            </div>
            
            <div>
                <span class="text-gray-600"><?php _e('Email:', 'edd-customer-dashboard-pro'); ?></span>
                <div class="font-medium"><?php echo esc_html($order->email); ?></div>
            </div>
            
            <?php if (!empty($order->transaction_id)) : ?>
            <div>
                <span class="text-gray-600"><?php _e('Transaction ID:', 'edd-customer-dashboard-pro'); ?></span>
                <div class="font-medium font-mono text-sm"><?php echo esc_html($order->transaction_id); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Billing Address -->
    <?php if ($billing_address && !empty(array_filter((array)$billing_address))) : ?>
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Billing Address', 'edd-customer-dashboard-pro'); ?></h3>
        
        <div class="text-gray-700 leading-relaxed">
            <?php if (!empty($billing_address->line1)) : ?>
                <?php echo esc_html($billing_address->line1); ?><br>
            <?php endif; ?>
            
            <?php if (!empty($billing_address->line2)) : ?>
                <?php echo esc_html($billing_address->line2); ?><br>
            <?php endif; ?>
            
            <?php if (!empty($billing_address->city)) : ?>
                <?php echo esc_html($billing_address->city); ?>
                <?php if (!empty($billing_address->region)) : ?>, <?php echo esc_html($billing_address->region); ?><?php endif; ?>
                <?php if (!empty($billing_address->postal_code)) : ?> <?php echo esc_html($billing_address->postal_code); ?><?php endif; ?>
                <br>
            <?php endif; ?>
            
            <?php if (!empty($billing_address->country)) : ?>
                <?php echo esc_html($billing_address->country); ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
/**
 * Copy license key to clipboard with visual feedback
 */
function copyLicenseKey(element, licenseKey) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(licenseKey).then(() => {
            showCopySuccess(element);
        }).catch(() => {
            fallbackCopy(licenseKey, element);
        });
    } else {
        fallbackCopy(licenseKey, element);
    }
}

/**
 * Fallback copy method for older browsers
 */
function fallbackCopy(text, element) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showCopySuccess(element);
    } catch (err) {
        showCopyError(element);
    }
    
    document.body.removeChild(textArea);
}

/**
 * Show copy success feedback
 */
function showCopySuccess(element) {
    const copyText = element.querySelector('.copy-text');
    if (copyText) {
        const originalText = copyText.textContent;
        copyText.textContent = '<?php _e('Copied!', 'edd-customer-dashboard-pro'); ?>';
        element.classList.add('bg-green-600');
        element.classList.remove('bg-indigo-600');
        
        setTimeout(() => {
            copyText.textContent = originalText;
            element.classList.remove('bg-green-600');
            element.classList.add('bg-indigo-600');
        }, 2000);
    }
    
    // Add visual feedback to the license key container
    const container = element.closest('.bg-white').querySelector('.bg-gray-50');
    if (container) {
        container.classList.add('border-green-400', 'bg-green-50');
        setTimeout(() => {
            container.classList.remove('border-green-400', 'bg-green-50');
        }, 2000);
    }
}

/**
 * Show copy error feedback
 */
function showCopyError(element) {
    const copyText = element.querySelector('.copy-text');
    if (copyText) {
        const originalText = copyText.textContent;
        copyText.textContent = '<?php _e('Failed', 'edd-customer-dashboard-pro'); ?>';
        element.classList.add('bg-red-600');
        element.classList.remove('bg-indigo-600');
        
        setTimeout(() => {
            copyText.textContent = originalText;
            element.classList.remove('bg-red-600');
            element.classList.add('bg-indigo-600');
        }, 2000);
    }
}
</script>