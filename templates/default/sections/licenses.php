<?php
/**
 * Licenses Section Template - EDD 3.0+ Compatible
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Software Licensing is active
if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . __('Software Licensing extension is not active.', 'eddcdp') . '</p>';
    echo '</div>';
    return;
}

// Get current user and customer
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . __('Customer data not found.', 'eddcdp') . '</p>';
    echo '</div>';
    return;
}

// Get licenses using EDD 3.0+ compatible method
$licenses = edd_software_licensing()->licenses_db->get_licenses(array(
    'user_id' => $current_user->ID,
    'number' => 999999,
    'orderby' => 'date_created',
    'order' => 'DESC'
));
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    🔑 <?php _e('License Management', 'eddcdp'); ?>
</h2>

<?php if ($licenses) : ?>
<div class="space-y-6">
    <?php foreach ($licenses as $license) : 
        $download_id = $license->download_id;
        $download_name = get_the_title($download_id);
        
        // Check license status using proper methods
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
        
        // Get price name if available
        $price_name = __('Standard', 'eddcdp');
        if (!empty($license->price_id) && function_exists('edd_get_price_option_name')) {
            $price_option = edd_get_price_option_name($download_id, $license->price_id);
            if ($price_option) {
                $price_name = $price_option;
            }
        }
    ?>
    
    <!-- License Item -->
    <div class="<?php echo $is_license_valid ? 'bg-green-50/50 border-green-200/50' : 'bg-red-50/50 border-red-200/50'; ?> rounded-2xl p-6 border">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
            <h3 class="text-xl font-semibold text-gray-800"><?php echo esc_html($download_name); ?></h3>
            <?php if ($is_license_valid) : ?>
            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-medium">
                ✅ <?php _e('Active', 'eddcdp'); ?>
            </span>
            <?php else : ?>
            <span class="bg-red-100 text-red-800 px-4 py-2 rounded-full text-sm font-medium">
                <?php if ($is_expired) : ?>
                    ⏰ <?php _e('Expired', 'eddcdp'); ?>
                <?php else : ?>
                    ❌ <?php _e('Inactive', 'eddcdp'); ?>
                <?php endif; ?>
            </span>
            <?php endif; ?>
        </div>
        
        <div class="bg-white/80 rounded-xl p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?php _e('License Key:', 'eddcdp'); ?></label>
                <div 
                    onclick="copyToClipboard('<?php echo esc_js($license->license_key); ?>')"
                    class="bg-gray-100 p-3 rounded-lg font-mono text-sm cursor-pointer hover:bg-gray-200 transition-colors border break-all">
                    <?php echo esc_html($license->license_key); ?>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?php _e('Click to copy', 'eddcdp'); ?></p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('Purchase Date:', 'eddcdp'); ?></strong> 
                        <?php echo date_i18n(get_option('date_format'), strtotime($license->date_created)); ?>
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
                        <strong><?php _e('License Type:', 'eddcdp'); ?></strong> 
                        <?php echo esc_html($price_name); ?>
                    </p>
                </div>
            </div>
            
            <!-- Site Management -->
            <div class="border-t pt-4">
                <h4 class="font-medium text-gray-800 mb-3"><?php _e('Manage Sites', 'eddcdp'); ?></h4>
                
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
                <?php endif; ?>
                
                <?php if (!empty($sites)) : ?>
                <div class="space-y-2 mb-4">
                    <h5 class="font-medium text-gray-800"><?php _e('Active Sites', 'eddcdp'); ?></h5>
                    <?php foreach ($sites as $site) : ?>
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm break-all"><?php echo esc_url($site); ?></span>
                        <button onclick="deactivateLicenseSite(<?php echo $license->ID; ?>, '<?php echo esc_js($site); ?>')" class="text-red-600 hover:text-red-800 text-sm font-medium ml-2">
                            🔓 <?php _e('Deactivate', 'eddcdp'); ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <p class="text-gray-500 italic text-sm mb-4"><?php _e('No sites activated yet.', 'eddcdp'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t">
                <?php if ($is_expired) : ?>
                <button onclick="renewLicense(<?php echo $license->ID; ?>, <?php echo $download_id; ?>)" class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300">
                    🔄 <?php _e('Renew License', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
                
                <button onclick="manageLicense(<?php echo $license->ID; ?>)" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    ⚙️ <?php _e('Manage', 'eddcdp'); ?>
                </button>
                <button onclick="viewLicenseInvoice(<?php echo $license->ID; ?>)" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    📄 <?php _e('Invoice', 'eddcdp'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        🔑
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Licenses Found', 'eddcdp'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('You don\'t have any software licenses yet. Purchase a licensed product to get started!', 'eddcdp'); ?></p>
    <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        🛒 <?php _e('Browse Licensed Products', 'eddcdp'); ?>
    </button>
</div>
<?php endif; ?>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('<?php _e('License key copied to clipboard!', 'eddcdp'); ?>', 'success');
    }).catch(() => {
        showNotification('<?php _e('Failed to copy license key.', 'eddcdp'); ?>', 'error');
    });
}

function activateLicenseSite(licenseId) {
    const siteUrl = document.getElementById('new-site-url-' + licenseId).value;
    if (!siteUrl.trim()) {
        showNotification('<?php _e('Please enter a site URL.', 'eddcdp'); ?>', 'error');
        return;
    }
    
    // AJAX call to activate site
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

function deactivateLicenseSite(licenseId, siteUrl) {
    if (!confirm('<?php _e('Are you sure you want to deactivate this site?', 'eddcdp'); ?>')) {
        return;
    }
    
    // AJAX call to deactivate site
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

function renewLicense(licenseId, downloadId) {
    // This could redirect to renewal page or open renewal modal
    alert('<?php _e('License renewal functionality would be implemented here.', 'eddcdp'); ?>');
}

function manageLicense(licenseId) {
    // This could open license management modal
    alert('<?php _e('License management functionality would be implemented here.', 'eddcdp'); ?>');
}

function viewLicenseInvoice(licenseId) {
    // This could open invoice in new window
    alert('<?php _e('Invoice viewing functionality would be implemented here.', 'eddcdp'); ?>');
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