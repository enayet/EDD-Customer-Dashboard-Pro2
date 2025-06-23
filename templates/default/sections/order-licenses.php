<?php
/**
 * Order Licenses Template Section - Clean Final Version
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
if (!class_exists('EDD_Software_Licensing')) {
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
    <h2 class="text-3xl font-bold text-gray-800 mb-2 flex items-center gap-3">
        🔑 <?php printf(__('Licenses for Order #%s', 'eddcdp'), $order->get_number()); ?>
    </h2>
    <p class="text-gray-600">
        <?php printf(__('Ordered on %s', 'eddcdp'), date_i18n(get_option('date_format'), strtotime($order->date_created))); ?>
    </p>
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
            
            // Get active sites from database
            global $wpdb;
            $active_sites = $wpdb->get_results($wpdb->prepare(
                "SELECT site_id, site_name FROM {$wpdb->prefix}edd_license_activations WHERE license_id = %d AND activated = 1",
                $license->ID
            ));
            
            $activation_limit = (int) $license->activation_limit;
            $activation_count = count($active_sites);
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
                <div onclick="copyToClipboard('<?php echo esc_js($license->license_key); ?>')"
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
                <!-- Add Site Form -->
                <form method="post" class="edd_sl_form mb-4">
                    <div class="flex gap-3">
                        <input type="url" name="site_url" 
                               placeholder="<?php esc_attr_e('Enter your site URL (e.g., https://example.com)', 'eddcdp'); ?>"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               value="https://" required>
                        <input type="submit" 
                               class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300" 
                               value="<?php esc_attr_e('Add Site', 'eddcdp'); ?>">
                    </div>
                    <input type="hidden" name="license_id" value="<?php echo esc_attr($license->ID); ?>">
                    <input type="hidden" name="edd_action" value="insert_site">
                    <?php wp_nonce_field('edd_add_site_nonce', 'edd_add_site_nonce'); ?>
                </form>
                
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
                
                <?php if (!empty($active_sites)) : ?>
                <div class="space-y-2 mb-4">
                    <h6 class="font-medium text-gray-800"><?php _e('Active Sites', 'eddcdp'); ?></h6>
                    <?php foreach ($active_sites as $site) : ?>
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm break-all flex-1 mr-2"><?php echo esc_url($site->site_name); ?></span>
                        <a href="#" 
                        onclick="showDeactivateModal('<?php echo esc_js($site->site_name); ?>', '<?php echo wp_nonce_url(
                            add_query_arg(array(
                                'action' => 'manage_licenses',
                                'payment_id' => $order->id,
                                'license_id' => $license->ID,
                                'site_id' => $site->site_id,
                                'edd_action' => 'deactivate_site',
                                'license' => $license->ID
                            )), 
                            'edd_deactivate_site_nonce'
                        ); ?>'); return false;"
                        class="text-red-600 hover:text-red-800 text-sm font-medium px-3 py-1 rounded hover:bg-red-50 transition-colors">
                            🔓 <?php _e('Deactivate', 'eddcdp'); ?>
                        </a>
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
                <a href="<?php echo edd_get_checkout_uri(); ?>?edd_action=purchase_renewal&license_id=<?php echo $license->ID; ?>" 
                   class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300 text-decoration-none">
                    🔄 <?php _e('Renew License', 'eddcdp'); ?>
                </a>
                <?php endif; ?>
                
                <a href="<?php echo get_permalink($license->download_id); ?>" 
                   class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300 text-decoration-none">
                    ⬆️ <?php _e('Upgrade', 'eddcdp'); ?>
                </a>
            </div>
        </div>
        
        <?php endforeach; ?>
    </div>
    
    <?php endforeach; ?>
</div>

<!-- Deactivation Modal -->
<div id="deactivateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-95">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                <span class="text-2xl">🔓</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php _e('Deactivate Site License', 'eddcdp'); ?></h3>
            <p class="text-gray-600 mb-6">
                <?php _e('Are you sure you want to deactivate the license for:', 'eddcdp'); ?>
                <br><strong id="modalSiteName" class="text-gray-800"></strong>
            </p>
            <div class="flex gap-3">
                <button onclick="closeDeactivateModal()" 
                        class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                    <?php _e('Cancel', 'eddcdp'); ?>
                </button>
                <button onclick="confirmDeactivation()" 
                        class="flex-1 bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                    <?php _e('Deactivate', 'eddcdp'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<script>
let currentDeactivateUrl = '';
let currentSiteName = '';

// Check for success/error messages in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check for deactivation success
    if (urlParams.get('edd_action') === 'deactivate_site' && !urlParams.get('error')) {
        const siteName = urlParams.get('site_name') || '<?php _e('the site', 'eddcdp'); ?>';
        showToast(`<?php _e('✅ License successfully deactivated for', 'eddcdp'); ?> ${siteName}`, 'success');
        
        // Clean URL
        const cleanUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, cleanUrl);
    }
    
    // Check for errors
    if (urlParams.get('error')) {
        showToast('<?php _e('❌ Failed to deactivate license. Please try again.', 'eddcdp'); ?>', 'error');
    }
});

function showDeactivateModal(siteName, deactivateUrl) {
    currentSiteName = siteName;
    currentDeactivateUrl = deactivateUrl;
    
    document.getElementById('modalSiteName').textContent = siteName;
    const modal = document.getElementById('deactivateModal');
    modal.classList.remove('hidden');
    
    // Animate modal in
    setTimeout(() => {
        modal.querySelector('.bg-white').classList.remove('scale-95');
        modal.querySelector('.bg-white').classList.add('scale-100');
    }, 10);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeDeactivateModal() {
    const modal = document.getElementById('deactivateModal');
    const modalContent = modal.querySelector('.bg-white');
    
    // Animate out
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}

function confirmDeactivation() {
    // Add site name to URL for success message
    const url = new URL(currentDeactivateUrl, window.location.origin);
    url.searchParams.append('site_name', currentSiteName);
    
    // Show loading state
    const confirmBtn = document.querySelector('#deactivateModal button[onclick="confirmDeactivation()"]');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '⏳ <?php _e('Deactivating...', 'eddcdp'); ?>';
    confirmBtn.disabled = true;
    
    // Redirect to deactivation URL
    window.location.href = url.toString();
}

// Close modal when clicking outside
document.getElementById('deactivateModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeactivateModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeactivateModal();
    }
});

function showToast(message, type = 'info', duration = 5000) {
    const toast = document.createElement('div');
    toast.className = `toast-notification transform transition-all duration-300 translate-x-full`;
    
    let bgColor, icon;
    switch (type) {
        case 'success':
            bgColor = 'bg-green-500';
            icon = '✅';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            icon = '❌';
            break;
        default:
            bgColor = 'bg-blue-500';
            icon = 'ℹ️';
    }
    
    toast.innerHTML = `
        <div class="${bgColor} text-white px-6 py-4 rounded-xl shadow-lg max-w-sm">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-lg">${icon}</span>
                    <span class="font-medium">${message}</span>
                </div>
                <button onclick="this.closest('.toast-notification').remove()" 
                        class="text-white hover:text-gray-200 text-xl leading-none">
                    ×
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('toastContainer').appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
        toast.classList.add('translate-x-0');
    }, 10);
    
    // Auto remove
    setTimeout(() => {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, duration);
}

function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('<?php _e('License key copied to clipboard!', 'eddcdp'); ?>', 'success', 3000);
        }).catch(() => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('<?php _e('License key copied to clipboard!', 'eddcdp'); ?>', 'success', 3000);
    } catch (err) {
        showToast('<?php _e('Failed to copy license key.', 'eddcdp'); ?>', 'error', 3000);
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type = 'info') {
    showToast(message, type);
}
</script>