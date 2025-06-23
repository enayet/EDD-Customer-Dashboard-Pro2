<?php
/**
 * Order Details Template Section
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$order_details = EDDCDP_Order_Details::instance();
$order = $order_details->get_current_order();

if (!$order) {
    echo '<div class="bg-red-50/80 rounded-2xl p-6 border border-red-200/50">';
    echo '<p class="text-red-800">' . __('Order not found.', 'eddcdp') . '</p>';
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
?>

<!-- Back Button -->
<div class="mb-6">
    <a href="<?php echo esc_url($order_details->get_return_url()); ?>" 
       class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium">
        ← <?php _e('Back to Dashboard', 'eddcdp'); ?>
    </a>
</div>

<!-- Order Header -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-6 shadow-lg border border-white/20">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">
                <?php printf(__('Order #%s', 'eddcdp'), $order->get_number()); ?>
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
    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Items Purchased', 'eddcdp'); ?></h3>
    
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
                        <?php printf(__('Quantity: %d × %s', 'eddcdp'), $item->quantity, edd_currency_filter(edd_format_amount($item->amount))); ?>
                    </p>
                    
                    <?php 
                    // Get license keys for this download if Software Licensing is active
                    if (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) {
                        $licenses = edd_software_licensing()->get_licenses_of_purchase($order->id, $download_id);
                        if ($licenses) :
                    ?>
                    <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                        <h6 class="text-sm font-medium text-blue-800 mb-2">🔑 <?php _e('License Keys:', 'eddcdp'); ?></h6>
                        <?php foreach ($licenses as $license) : ?>
                        <div class="mb-2 last:mb-0">
                            <div class="flex items-center gap-3">
                                <code class="bg-white px-3 py-1 rounded border text-sm font-mono select-all">
                                    <?php echo esc_html($license->license_key); ?>
                                </code>
                                <button onclick="copyToClipboard('<?php echo esc_js($license->license_key); ?>')" 
                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                    📋 <?php _e('Copy', 'eddcdp'); ?>
                                </button>
                            </div>
                            <p class="text-xs text-blue-600 mt-1">
                                <?php printf(__('Status: %s', 'eddcdp'), ucfirst($license->status)); ?>
                                <?php if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') : ?>
                                    | <?php printf(__('Expires: %s', 'eddcdp'), date_i18n(get_option('date_format'), strtotime($license->expiration))); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; } ?>
                </div>
                <div class="font-bold text-lg text-gray-800">
                    <?php echo edd_currency_filter(edd_format_amount($item->total)); ?>
                </div>
            </div>
            
            <?php if ($files && $order->status === 'complete') : ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h5 class="font-medium text-gray-700 mb-3">📥 <?php _e('Download Files:', 'eddcdp'); ?></h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach ($files as $file_key => $file) : 
                        $download_url = edd_get_download_file_url($order->payment_key, $order->email, $file_key, $download_id);
                        $file_size = '';
                        
                        // Get file size if available
                        if (!empty($file['file'])) {
                            $file_path = EDD()->session->get('edd_download_file');
                            if (file_exists($file['file'])) {
                                $file_size = ' (' . size_format(filesize($file['file'])) . ')';
                            }
                        }
                    ?>
                    <a href="<?php echo esc_url($download_url); ?>" 
                       class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-3 rounded-lg font-medium hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2 text-decoration-none text-center">
                        <span class="text-lg">📥</span>
                        <div class="flex-1 min-w-0">
                            <div class="truncate"><?php echo esc_html($file['name']); ?></div>
                            <?php if ($file_size) : ?>
                            <div class="text-xs opacity-90"><?php echo $file_size; ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-3 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-1">
                        ℹ️ <?php _e('Right-click and "Save As" to download files directly.', 'eddcdp'); ?>
                    </span>
                </div>
            </div>
            <?php elseif ($order->status !== 'complete') : ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-yellow-800 text-sm">
                        ⏳ <?php _e('Downloads will be available once your order is complete.', 'eddcdp'); ?>
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
    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Order Summary', 'eddcdp'); ?></h3>
    
    <div class="space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-gray-600"><?php _e('Subtotal:', 'eddcdp'); ?></span>
            <span class="font-medium"><?php echo edd_currency_filter(edd_format_amount($order->subtotal)); ?></span>
        </div>
        
        <?php if ($order->tax > 0) : ?>
        <div class="flex justify-between items-center">
            <span class="text-gray-600"><?php _e('Tax:', 'eddcdp'); ?></span>
            <span class="font-medium"><?php echo edd_currency_filter(edd_format_amount($order->tax)); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($order->discount_amount > 0) : ?>
        <div class="flex justify-between items-center">
            <span class="text-gray-600"><?php _e('Discount:', 'eddcdp'); ?></span>
            <span class="font-medium text-green-600">-<?php echo edd_currency_filter(edd_format_amount($order->discount_amount)); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="border-t border-gray-200 pt-3">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-800"><?php _e('Total:', 'eddcdp'); ?></span>
                <span class="text-lg font-bold text-gray-800"><?php echo edd_currency_filter(edd_format_amount($order->total)); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Payment & Billing Information -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Payment Information -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Payment Information', 'eddcdp'); ?></h3>
        
        <div class="space-y-3">
            <div>
                <span class="text-gray-600"><?php _e('Payment Method:', 'eddcdp'); ?></span>
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
                <span class="text-gray-600"><?php _e('Email:', 'eddcdp'); ?></span>
                <div class="font-medium"><?php echo esc_html($order->email); ?></div>
            </div>
            
            <?php if (!empty($order->transaction_id)) : ?>
            <div>
                <span class="text-gray-600"><?php _e('Transaction ID:', 'eddcdp'); ?></span>
                <div class="font-medium font-mono text-sm"><?php echo esc_html($order->transaction_id); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Billing Address -->
    <?php if ($billing_address && !empty(array_filter((array)$billing_address))) : ?>
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Billing Address', 'eddcdp'); ?></h3>
        
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

<!-- Order Notes -->
<?php 
$notes = edd_get_payment_notes($order->id);
$public_notes = array();

if ($notes) {
    foreach ($notes as $note) {
        // Only show notes that are not private
        if (empty($note->comment_type) || $note->comment_type !== 'private') {
            $public_notes[] = $note;
        }
    }
}

if (!empty($public_notes)) : ?>
<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-6 shadow-lg border border-white/20">
    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Order Notes', 'eddcdp'); ?></h3>
    
    <div class="space-y-4">
        <?php foreach ($public_notes as $note) : ?>
        <div class="border-l-4 border-indigo-500 pl-4 py-2">
            <p class="text-gray-700 mb-1"><?php echo esc_html($note->comment_content); ?></p>
            <small class="text-gray-500"><?php echo date_i18n(get_option('date_format'), strtotime($note->comment_date)); ?></small>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Action Buttons -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
    <h3 class="text-xl font-semibold text-gray-800 mb-4">📋 <?php _e('Order Actions', 'eddcdp'); ?></h3>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- View Receipt -->
        <?php if (function_exists('edd_get_order_receipt_url')) : ?>
        <a href="<?php echo edd_get_order_receipt_url($order->id); ?>" 
           class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2 text-decoration-none">
            📄 <?php _e('View Receipt', 'eddcdp'); ?>
        </a>
        <?php endif; ?>
        
        <!-- Download Invoice -->
        <?php 
        $invoice_url = '';
        // Check for various invoice plugins
        if (function_exists('edd_get_invoice_url')) {
            $invoice_url = edd_get_invoice_url($order->id);
        } elseif (function_exists('eddpdfi_get_invoice_url')) {
            $invoice_url = eddpdfi_get_invoice_url($order->id);
        } elseif (class_exists('EDD_Invoices') && method_exists('EDD_Invoices', 'get_invoice_url')) {
            $invoice_url = EDD_Invoices::get_invoice_url($order->id);
        }
        
        if ($invoice_url) :
        ?>
        <a href="<?php echo esc_url($invoice_url); ?>" 
           class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2 text-decoration-none">
            📊 <?php _e('Download Invoice', 'eddcdp'); ?>
        </a>
        <?php else : ?>
        <button onclick="generateInvoice(<?php echo $order->id; ?>)" 
                class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
            📊 <?php _e('Generate Invoice', 'eddcdp'); ?>
        </button>
        <?php endif; ?>
        
        <!-- Print Order -->
        <button onclick="window.print()" 
                class="bg-white text-gray-600 border border-gray-300 px-6 py-3 rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
            🖨️ <?php _e('Print Order', 'eddcdp'); ?>
        </button>
        
        <!-- Contact Support -->
        <a href="mailto:<?php echo get_option('admin_email'); ?>?subject=<?php echo urlencode(sprintf(__('Order #%s Support Request', 'eddcdp'), $order->get_number())); ?>&body=<?php echo urlencode(sprintf(__('Hi, I need help with Order #%s placed on %s.', 'eddcdp'), $order->get_number(), date_i18n(get_option('date_format'), strtotime($order->date_created)))); ?>" 
           class="bg-white text-gray-600 border border-gray-300 px-6 py-3 rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 text-decoration-none">
            💬 <?php _e('Contact Support', 'eddcdp'); ?>
        </a>
    </div>
    
    <!-- Download All Files Button (if multiple files) -->
    <?php 
    $all_files = array();
    $total_files = 0;
    
    if ($order->status === 'complete') {
        foreach ($order_items as $item) {
            $download_id = $item->product_id;
            $price_id = !empty($item->price_id) ? $item->price_id : null;
            $files = edd_get_download_files($download_id, $price_id);
            
            if ($files) {
                $total_files += count($files);
                foreach ($files as $file_key => $file) {
                    $all_files[] = array(
                        'name' => $file['name'],
                        'url' => edd_get_download_file_url($order->payment_key, $order->email, $file_key, $download_id)
                    );
                }
            }
        }
    }
    
    if ($total_files > 1) :
    ?>
    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="bg-blue-50 rounded-xl p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h4 class="font-medium text-blue-800">📦 <?php _e('Download All Files', 'eddcdp'); ?></h4>
                    <p class="text-sm text-blue-600 mt-1">
                        <?php printf(_n('%d file available', '%d files available', $total_files, 'eddcdp'), $total_files); ?>
                    </p>
                </div>
                <button onclick="downloadAllFiles()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    📥 <?php _e('Download All', 'eddcdp'); ?>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Copy license key to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            // Create a temporary success message
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = '✅ <?php _e('Copied!', 'eddcdp'); ?>';
            button.style.color = '#10b981';
            
            setTimeout(() => {
                button.textContent = originalText;
                button.style.color = '';
            }, 2000);
        }).catch(() => {
            alert('<?php _e('Failed to copy to clipboard. Please copy manually.', 'eddcdp'); ?>');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = '✅ <?php _e('Copied!', 'eddcdp'); ?>';
            button.style.color = '#10b981';
            
            setTimeout(() => {
                button.textContent = originalText;
                button.style.color = '';
            }, 2000);
        } catch (err) {
            alert('<?php _e('Failed to copy to clipboard. Please copy manually.', 'eddcdp'); ?>');
        }
        document.body.removeChild(textArea);
    }
}

// Generate invoice (fallback if no invoice plugin)
function generateInvoice(orderId) {
    alert('<?php _e('Invoice generation requires an EDD invoice plugin to be installed and activated.', 'eddcdp'); ?>');
}

// Download all files functionality
function downloadAllFiles() {
    const files = <?php echo json_encode($all_files); ?>;
    let downloadCount = 0;
    
    files.forEach((file, index) => {
        setTimeout(() => {
            const link = document.createElement('a');
            link.href = file.url;
            link.download = file.name;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            downloadCount++;
            if (downloadCount === files.length) {
                alert('<?php printf(__('Started downloading %d files. Check your downloads folder.', 'eddcdp'), $total_files); ?>');
            }
        }, index * 500); // Stagger downloads by 500ms
    });
}
</script>