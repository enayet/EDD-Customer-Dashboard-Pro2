<?php
/**
 * Licenses Section Template - Simplified & Fast
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Software Licensing is active
if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . esc_html__('Software Licensing extension is not active.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Get current user and customer
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . esc_html__('Customer data not found.', 'edd-customer-dashboard-pro') . '</p>';
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
    🔑 <?php esc_html_e('License Management', 'edd-customer-dashboard-pro'); ?>
</h2>

<?php if ($licenses) : ?>
<div class="space-y-6">
    <?php foreach ($licenses as $license) : 
        $download_id = $license->download_id;
        $download_name = get_the_title($download_id);
        
        // Use helper function to get license status info
        $license_info = eddcdp_get_license_status_info($license);
        
        // Get active sites using helper function
        $active_sites = eddcdp_get_license_active_sites($license->ID);
        
        $activation_limit = (int) $license->activation_limit;
        $activation_count = count($active_sites);
        $activation_limit_reached = ($activation_limit > 0 && $activation_count >= $activation_limit);
        
        // Get price name if available
        $price_name = esc_html__('Standard', 'edd-customer-dashboard-pro');
        if (!empty($license->price_id) && function_exists('edd_get_price_option_name')) {
            $price_option = edd_get_price_option_name($download_id, $license->price_id);
            if ($price_option) {
                $price_name = $price_option;
            }
        }
    ?>
    
    <!-- License Item -->
    <div class="<?php echo esc_attr($license_info['container_class']); ?> rounded-2xl p-6 border">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
            <h3 class="text-xl font-semibold text-gray-800"><?php echo esc_html($download_name); ?></h3>
            <span class="<?php echo esc_attr($license_info['badge_class']); ?> px-4 py-2 rounded-full text-sm font-medium">
                <?php echo esc_html($license_info['icon'] . ' ' . $license_info['text']); ?>
            </span>
        </div>
        
        <div class="bg-white/80 rounded-xl p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('License Key:', 'edd-customer-dashboard-pro'); ?></label>
                <div 
                    onclick="copyToClipboard('<?php echo esc_js($license->license_key); ?>')"
                    class="bg-gray-100 p-3 rounded-lg font-mono text-sm cursor-pointer hover:bg-gray-200 transition-colors border break-all">
                    <?php echo esc_html($license->license_key); ?>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?php esc_html_e('Click to copy', 'edd-customer-dashboard-pro'); ?></p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-600">
                        <strong><?php esc_html_e('Purchase Date:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($license->date_created))); ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong><?php esc_html_e('Expires:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <?php 
                        if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') {
                            echo esc_html(date_i18n(get_option('date_format'), strtotime($license->expiration)));
                        } else {
                            esc_html_e('Never', 'edd-customer-dashboard-pro');
                        }
                        ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">
                        <strong><?php esc_html_e('Activations:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <?php 
                        if ($activation_limit > 0) {
                            /* translators: %1$d: current activations, %2$d: activation limit */
                            printf(esc_html__('%1$d of %2$d sites', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($activation_count)), esc_html(number_format_i18n($activation_limit)));
                        } else {
                            /* translators: %d: current activations */
                            printf(esc_html__('%d of Unlimited sites', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($activation_count)));
                        }
                        ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong><?php esc_html_e('License Type:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <?php echo esc_html($price_name); ?>
                    </p>
                </div>
            </div>
            
            <!-- Site Management -->
            <div class="border-t pt-4">
                <h4 class="font-medium text-gray-800 mb-3"><?php esc_html_e('Manage Sites', 'edd-customer-dashboard-pro'); ?></h4>
                
                <?php if ($license_info['can_activate'] && !$activation_limit_reached) : ?>
                <!-- Add Site Form (Same as order-licenses.php) -->
                <form method="post" class="edd_sl_form mb-4" onsubmit="setLicenseTabFlag()">
                    <div class="flex gap-3">
                        <input type="url" name="site_url" 
                               placeholder="<?php esc_attr_e('Enter your site URL (e.g., https://example.com)', 'edd-customer-dashboard-pro'); ?>"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               value="https://" required>
                        <input type="submit" 
                               class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300" 
                               value="<?php esc_attr_e('Activate', 'edd-customer-dashboard-pro'); ?>">
                    </div>
                    <input type="hidden" name="license_id" value="<?php echo esc_attr($license->ID); ?>">
                    <input type="hidden" name="edd_action" value="insert_site">
                    <?php wp_nonce_field('edd_add_site_nonce', 'edd_add_site_nonce'); ?>
                </form>
                
                <?php elseif (!$license_info['can_activate']) : ?>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <p class="text-gray-700 text-sm">
                        <?php 
                        if ($license_info['text'] === esc_html__('Disabled', 'edd-customer-dashboard-pro')) {
                            echo '🚫 ' . esc_html__('This license has been disabled and cannot be used to activate sites.', 'edd-customer-dashboard-pro');
                        } elseif ($license_info['text'] === esc_html__('Expired', 'edd-customer-dashboard-pro')) {
                            echo '⏰ ' . esc_html__('This license has expired and cannot be used to activate new sites. Please renew your license.', 'edd-customer-dashboard-pro');
                        }
                        ?>
                    </p>
                </div>
                
                <?php elseif ($activation_limit_reached) : ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-blue-800 text-sm">
                        🔒 <?php esc_html_e('Activation limit reached. Deactivate a site first or upgrade your license.', 'edd-customer-dashboard-pro'); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($active_sites)) : ?>
                <div class="space-y-2 mb-4">
                    <h5 class="font-medium text-gray-800"><?php esc_html_e('Active Sites', 'edd-customer-dashboard-pro'); ?></h5>
                    <?php foreach ($active_sites as $site) : ?>
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm break-all flex-1 mr-2"><?php echo esc_url($site->site_name); ?></span>
                        <a href="#" 
                        onclick="setLicenseTabFlag(); showDeactivateModal('<?php echo esc_js($site->site_name); ?>', '<?php echo esc_url(wp_nonce_url(
                            add_query_arg(array(
                                'action' => 'manage_licenses',
                                'payment_id' => '', // Not needed for main licenses
                                'license_id' => $license->ID,
                                'site_id' => $site->site_id,
                                'edd_action' => 'deactivate_site',
                                'license' => $license->ID
                            )), 
                            'edd_deactivate_site_nonce'
                        )); ?>'); return false;"
                        class="text-red-600 hover:text-red-800 text-sm font-medium px-3 py-1 rounded hover:bg-red-50 transition-colors">
                            🔓 <?php esc_html_e('Deactivate', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <p class="text-gray-500 italic text-sm mb-4"><?php esc_html_e('No sites activated yet.', 'edd-customer-dashboard-pro'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t">
                <?php if ($license_info['text'] === esc_html__('Expired', 'edd-customer-dashboard-pro')) : ?>
                <a href="<?php echo esc_url(edd_get_checkout_uri()); ?>?edd_action=purchase_renewal&license_id=<?php echo esc_attr($license->ID); ?>" 
                   class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300 text-decoration-none">
                    🔄 <?php esc_html_e('Renew License', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(get_permalink($license->download_id)); ?>" 
                   class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300 text-decoration-none">
                    ⬆️ <?php esc_html_e('Upgrade', 'edd-customer-dashboard-pro'); ?>
                </a>
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
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php esc_html_e('No Licenses Found', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="text-gray-600 mb-6"><?php esc_html_e('You don\'t have any software licenses yet. Purchase a licensed product to get started!', 'edd-customer-dashboard-pro'); ?></p>
    <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        🛒 <?php esc_html_e('Browse Licensed Products', 'edd-customer-dashboard-pro'); ?>
    </button>
</div>
<?php endif; ?>

<!-- Deactivation Modal -->
<div id="deactivateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-95">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                <span class="text-2xl">🔓</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php esc_html_e('Deactivate Site License', 'edd-customer-dashboard-pro'); ?></h3>
            <p class="text-gray-600 mb-6">
                <?php esc_html_e('Are you sure you want to deactivate the license for:', 'edd-customer-dashboard-pro'); ?>
                <br><strong id="modalSiteName" class="text-gray-800"></strong>
            </p>
            <div class="flex gap-3">
                <button onclick="closeDeactivateModal()" 
                        class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                    <?php esc_html_e('Cancel', 'edd-customer-dashboard-pro'); ?>
                </button>
                <button onclick="confirmDeactivation()" 
                        class="flex-1 bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                    <?php esc_html_e('Deactivate', 'edd-customer-dashboard-pro'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo esc_url(EDDCDP_PLUGIN_URL); ?>templates/default/assets/license-management.js"></script>

<script>
// Set flag when license forms are submitted from main licenses tab
function setLicenseTabFlag() {
    sessionStorage.setItem('returnToLicensesTab', 'true');
}

// Check if we should return to licenses tab
document.addEventListener('DOMContentLoaded', function() {
    // Check if we have the flag set
    if (sessionStorage.getItem('returnToLicensesTab') === 'true') {
        // Clear the flag
        sessionStorage.removeItem('returnToLicensesTab');
        
        // Set hash to licenses
        window.location.hash = 'licenses';
        
        // Try to set Alpine.js activeTab
        setTimeout(function() {
            const dashboardElement = document.querySelector('[x-data]');
            if (dashboardElement && dashboardElement._x_dataStack && dashboardElement._x_dataStack[0]) {
                dashboardElement._x_dataStack[0].activeTab = 'licenses';
            }
        }, 100);
    }
});
</script>