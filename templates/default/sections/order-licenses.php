<?php
/**
 * Order Licenses Template Section
 * Shows licenses specific to an order with management options
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$order_details = EDDCDP_Order_Details::instance();
$order = $order_details->get_current_order_licenses();

if (!$order) {
    echo '<div class="bg-red-50/80 rounded-2xl p-6 border border-red-200/50">';
    echo '<p class="text-red-800">' . __('Order not found.', 'eddcdp') . '</p>';
    echo '</div>';
    return;
}

// Check if EDD Software Licensing is active
if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . __('Software Licensing extension is not active.', 'eddcdp') . '</p>';
    echo '</div>';
    return;
}

// Get all licenses for this order
$order_items = $order->get_items();
$order_licenses = array();

foreach ($order_items as $item) {
    $licenses = edd_software_licensing()->get_licenses_of_purchase($order->id, $item->product_id);
    if ($licenses) {
        $order_licenses[$item->product_id] = array(
            'product_name' => $item->product_name,
            'licenses' => $licenses
        );
    }
}

if (empty($order_licenses)) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . __('No licenses found for this order.', 'eddcdp') . '</p>';
    echo '</div>';
    return;
}
?>

<!-- Header with Back Button -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <a href="<?php echo esc_url($order_details->get_return_url()); ?>" 
       class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium">
        ← <?php _e('Back to Dashboard', 'eddcdp'); ?>
    </a>
    
    <div class="text-sm text-gray-600">
        <?php printf(__('Order #%s Licenses', 'eddcdp'), $order->get_number()); ?>
    </div>
</div>

<!-- Order Licenses Header -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-6 shadow-lg border border-white/20">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2 flex items-center gap-3">
                🔑 <?php printf(__('Licenses for Order #%s', 'eddcdp'), $order->get_number()); ?>
            </h2>
            <p class="text-gray-600">
                <?php printf(__('Ordered on %s', 'eddcdp'), date_i18n(get_option('date_format'), strtotime($order->date_created))); ?>
            </p>
        </div>
    </div>
</div>

<!-- License Management -->
<div class="space-y-6">
    <?php foreach ($order_licenses as $product_id => $product_data) : ?>
    
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php echo esc_html($product_data['product_name']); ?></h3>
        
        <?php foreach ($product_data['licenses'] as $license) : 
            // Check license status
            $is_active = ($license->status === 'active');
            $is_expired = false;
            
            if (!empty($license->expiration)) {
                $expiration_date = strtotime($license->expiration);
                $is_expired = ($expiration_date < time());
            }
            
            // Get license sites and limits
            $sites = maybe_unserialize($license->sites);
            if (!is_array($sites)) {
                $sites = array();
            }
            
            $activation_limit = (int) $license->activation_limit;
            $activation_count = (int) $license->activation_count;
            
            // Determine overall license status
            $is_license_valid = ($is_active && !$is_expired);
            
            $status_class = $is_license_valid ? 'bg-green-50/50 border-green-200/50' : 'bg-red-50/50 border-red-200/50';
            $status_badge_class = $is_license_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            $status_text = $is_license_valid ? __('Active', 'eddcdp') : ($is_expired ? __('Expired', 'eddcdp') : __('Inactive', 'eddcdp'));
            $status_icon = $is_license_valid ? '✅' : ($is_expired ? '⏰' : '❌');
        ?>
        
        <div class="<?php echo $status_class; ?> rounded-xl p-6 mb-4 border">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-4">
                <h4 class="text-lg font-medium text-gray-800"><?php _e('License Key', 'eddcdp'); ?></h4>
                <span class="<?php echo $status_badge_class; ?> px-3 py-1 rounded-full text-sm font-medium">
                    <?php echo $status_icon . ' ' . $status_text; ?>
                </span>
            </div>
            
            <!-- License Key Display -->
            <div class="mb-4">
                <div 
                    onclick="copyToClipboard('<?php echo esc_js($license->license_key); ?>')"
                    class="bg-gray-100 p-3 rounded-lg font-mono text-sm cursor-pointer hover:bg-gray-200 transition-colors border break-all">
                    <?php echo esc_html($license->license_key); ?>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?php _e('Click to copy', 'eddcdp'); ?></p>
            </div>
            
            <!-- License Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('Status:', 'eddcdp'); ?></strong> 
                        <?php echo ucfirst($license->status); ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('Expires:', 'eddcdp'); ?></strong> 
                        <?php 
                        if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') {
                            echo date_i18n(get_option('date_format'), strtotime($license->expiration));
                        } else {
                            _e('Never', 'eddcdp');
                        }
                        ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('Activations:', 'eddcdp'); ?></strong> 
                        <?php 
                        if ($activation_limit > 0) {
                            echo $activation_count . ' ' . __('of', 'eddcdp') . ' ' . $activation_limit . ' ' . __('sites', 'eddcdp');
                        } else {
                            echo $activation_count . ' ' . __('of Unlimited sites', 'eddcdp');
                        }
                        ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('Download ID:', 'eddcdp'); ?></strong> 
                        <?php echo $license->download_id; ?>
                    </p>
                </div>
            </div>
            
            <!-- Site Management -->
            <div class="border-t pt-4">
                <h5 class="font-medium text-gray-800 mb-3"><?php _e('Manage Sites', 'eddcdp'); ?></h5>
                
                <?php if ($is_license_valid && ($activation_limit == 0 || $activation_count < $activation_limit)) : ?>
                <div class="flex gap-3 mb-4">
                    <input 
                        type="url" 
                        placeholder="<?php esc_attr_e('Enter your site URL (e.g., https://example.com)', 'eddcdp'); ?>"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        id="new-site-url-<?php echo $license->ID; ?>">
                    <button 
                        onclick="activateLicenseSite(<?php echo $license->ID; ?>)"
                        class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                        ✅ <?php _e('Activate', 'eddcdp'); ?>
                    </button>
                </div>
                <?php elseif (!$is_license_valid) : ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <p class="text-yellow-800 text-sm">
                        <?php _e('License must be active and not expired to activate new sites.', 'eddcdp'); ?>
                    </p>
                </div>
                <?php elseif ($activation_count >= $activation_limit) : ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-blue-800 text-sm">
                        <?php _e('Activation limit reached. Deactivate a site first or upgrade your license.', 'eddcdp'); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($sites)) : ?>
                <div class="space-y-2 mb-4">
                    <h6 class="font-medium text-gray-800"><?php _e('Active Sites', 'eddcdp'); ?></h6>
                    <?php foreach ($sites as $site) : ?>
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm break-all flex-1 mr-2"><?php echo esc_url($site); ?></span>
                        <button onclick="deactivateLicenseSite(<?php echo $license->ID; ?>, '<?php echo esc_js($site); ?>')" 
                                class="text-red-600 hover:text-red-800 text-sm font-medium px-3 py-1 rounded hover:bg-red-50 transition-colors">
                            🔓 <?php _e('Deactivate', 'eddcdp'); ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <p class="text-gray-500 italic text-sm mb-4"><?php _e('No sites activated yet.', 'eddcdp'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- License Actions -->
            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t">
                <?php if ($is_expired) : ?>
                <button onclick="renewLicense(<?php echo $license->ID; ?>, <?php echo $license->download_id; ?>)" 
                        class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300">
                    🔄 <?php _e('Renew License', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
                
                <button onclick="upgradeLicense(<?php echo $license->ID; ?>, <?php echo $license->download_id; ?>)" 
                        class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300">
                    ⬆️ <?php _e('Upgrade', 'eddcdp'); ?>
                </button>
                
                <button onclick="viewLicenseDetails(<?php echo $license->ID; ?>)" 
                        class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    📋 <?php _e('Details', 'eddcdp'); ?>
                </button>
            </div>
        </div>
        
        <?php endforeach; ?>
    </div>
    
    <?php endforeach; ?>
</div>

<script>
// Copy license key functionality
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('<?php _e('License key copied to clipboard!', 'eddcdp'); ?>', 'success');
    }).catch(() => {
        showNotification('<?php _e('Failed to copy license key.', 'eddcdp'); ?>', 'error');
    });
}

// Activate license site
function activateLicenseSite(licenseId) {
    const siteUrl = document.getElementById('new-site-url-' + licenseId).value;
    if (!siteUrl.trim()) {
        showNotification('<?php _e('Please enter a site URL.', 'eddcdp'); ?>', 'error');
        return;
    }
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'eddcdp_activate_license_site',
            license_id: licenseId,
            site_url: siteUrl,
            nonce: '<?php echo wp_create_nonce('eddcdp_ajax_nonce'); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('<?php _e('Site activated successfully!', 'eddcdp'); ?>', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.data || '<?php _e('Error activating site. Please try again.', 'eddcdp'); ?>', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('<?php _e('Error activating site. Please try again.', 'eddcdp'); ?>', 'error');
    });
}

// Deactivate license site
function deactivateLicenseSite(licenseId, siteUrl) {
    if (!confirm('<?php _e('Are you sure you want to deactivate this site?', 'eddcdp'); ?>')) {
        return;
    }
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'eddcdp_deactivate_license_site',
            license_id: licenseId,
            site_url: siteUrl,
            nonce: '<?php echo wp_create_nonce('eddcdp_ajax_nonce'); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('<?php _e('Site deactivated successfully!', 'eddcdp'); ?>', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.data || '<?php _e('Error deactivating site. Please try again.', 'eddcdp'); ?>', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('<?php _e('Error deactivating site. Please try again.', 'eddcdp'); ?>', 'error');
    });
}

// Renew license functionality
function renewLicense(licenseId, downloadId) {
    // Redirect to purchase renewal
    const renewUrl = '<?php echo edd_get_checkout_uri(); ?>?edd_action=purchase_renewal&license_id=' + licenseId;
    window.location.href = renewUrl;
}

// Upgrade license functionality  
function upgradeLicense(licenseId, downloadId) {
    // You can implement upgrade logic here or redirect to upgrade page
    alert('<?php _e('License upgrade functionality would be implemented here based on your upgrade system.', 'eddcdp'); ?>');
}

// View license details
function viewLicenseDetails(licenseId) {
    // Could open a modal or navigate to detailed license page
    alert('<?php _e('Detailed license information would be shown here.', 'eddcdp'); ?>');
}

// Simple notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm`;
    
    switch (type) {
        case 'success':
            notification.className += ' bg-green-500 text-white';
            break;
        case 'error':
            notification.className += ' bg-red-500 text-white';
            break;
        default:
            notification.className += ' bg-blue-500 text-white';
    }
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">×</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>