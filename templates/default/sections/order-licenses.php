<?php
/**
 * Order Licenses Template Section - Simple & Clean
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$order_details = EDDCDP_Order_Details::instance();
$order = $order_details->get_current_order_licenses();

if (!$order) {
    echo '<div class="bg-red-50/80 rounded-2xl p-6 border border-red-200/50">';
    echo '<p class="text-red-800">' . esc_html__('Order not found.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Check if EDD Software Licensing is active
if (!class_exists('EDD_Software_Licensing')) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . esc_html__('Software Licensing extension is not active.', 'edd-customer-dashboard-pro') . '</p>';
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
    echo '<p class="text-yellow-800">' . esc_html__('No licenses found for this order.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}
?>

<!-- Header with Back Button and Invoice -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <a href="<?php echo esc_url($order_details->get_return_url()); ?>" 
       class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium">
        ← <?php esc_html_e('Back to Dashboard', 'edd-customer-dashboard-pro'); ?>
    </a>
    
    <div class="text-sm text-gray-600">
        <?php 
        /* translators: %s: Order number */
        printf(esc_html__('Order #%s Licenses', 'edd-customer-dashboard-pro'), esc_html($order->get_number())); 
        ?>
    </div>
</div>

<!-- Order Licenses Header -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-6 shadow-lg border border-white/20">
    <h2 class="text-3xl font-bold text-gray-800 mb-2 flex items-center gap-3">
        🔑 <?php 
        /* translators: %s: Order number */
        printf(esc_html__('Licenses for Order #%s', 'edd-customer-dashboard-pro'), esc_html($order->get_number())); 
        ?>
    </h2>
    <p class="text-gray-600">
        <?php 
        /* translators: %s: Formatted order date */
        printf(esc_html__('Ordered on %s', 'edd-customer-dashboard-pro'), esc_html(date_i18n(get_option('date_format'), strtotime($order->date_created)))); 
        ?>
    </p>
</div>

<!-- License Management -->
<div class="space-y-6">
    <?php foreach ($order_licenses as $product_id => $product_data) : ?>
    
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
        <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php echo esc_html($product_data['product_name']); ?></h3>
        
        <?php foreach ($product_data['licenses'] as $license) : 
            // Use helper function to get license status info
            $license_info = eddcdp_get_license_status_info($license);
            
            // Get active sites using helper function
            $active_sites = eddcdp_get_license_active_sites($license->ID);
            
            $activation_limit = (int) $license->activation_limit;
            $activation_count = count($active_sites);
            $activation_limit_reached = ($activation_limit > 0 && $activation_count >= $activation_limit);
        ?>
        
        <div class="<?php echo esc_attr($license_info['container_class']); ?> rounded-xl p-6 mb-4 border">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-4">
                <h4 class="text-lg font-medium text-gray-800"><?php esc_html_e('License Key', 'edd-customer-dashboard-pro'); ?></h4>
                <span class="<?php echo esc_attr($license_info['badge_class']); ?> px-3 py-1 rounded-full text-sm font-medium">
                    <?php echo esc_html($license_info['icon'] . ' ' . $license_info['text']); ?>
                </span>
            </div>
            
            <!-- License Key Display -->
            <div class="mb-4">
                <div onclick="copyToClipboard('<?php echo esc_js($license->license_key); ?>')"
                     class="bg-gray-100 p-3 rounded-lg font-mono text-sm cursor-pointer hover:bg-gray-200 transition-colors border break-all">
                    <?php echo esc_html($license->license_key); ?>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?php esc_html_e('Click to copy', 'edd-customer-dashboard-pro'); ?></p>
            </div>
            
            <!-- License Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-600">
                        <strong><?php esc_html_e('Status:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <?php echo esc_html(ucfirst($license->status)); ?>
                        <?php if ($activation_count === 0 && $license->status === 'inactive') : ?>
                            <span class="text-xs text-gray-500">(<?php esc_html_e('No sites activated yet', 'edd-customer-dashboard-pro'); ?>)</span>
                        <?php endif; ?>
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
                        <strong><?php esc_html_e('Download ID:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <?php echo esc_html($license->download_id); ?>
                    </p>
                </div>
            </div>
            
            <!-- Site Management -->
            <div class="border-t pt-4">
                <h5 class="font-medium text-gray-800 mb-3"><?php esc_html_e('Manage Sites', 'edd-customer-dashboard-pro'); ?></h5>
                
                <?php if ($license_info['can_activate'] && !$activation_limit_reached) : ?>
                <!-- Add Site Form -->
                <form method="post" class="edd_sl_form mb-4">
                    <div class="flex gap-3">
                        <input type="url" name="site_url" 
                               placeholder="<?php esc_attr_e('Enter your site URL (e.g., https://example.com)', 'edd-customer-dashboard-pro'); ?>"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               value="https://" required>
                        <input type="submit" 
                               class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300" 
                               value="<?php esc_attr_e('Add Site', 'edd-customer-dashboard-pro'); ?>">
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
                    <h6 class="font-medium text-gray-800"><?php esc_html_e('Active Sites', 'edd-customer-dashboard-pro'); ?></h6>
                    <?php foreach ($active_sites as $site) : ?>
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm break-all flex-1 mr-2"><?php echo esc_url($site->site_name); ?></span>
                        <a href="#" 
                        onclick="showDeactivateModal('<?php echo esc_js($site->site_name); ?>', '<?php echo esc_url(wp_nonce_url(
                            add_query_arg(array(
                                'action' => 'manage_licenses',
                                'payment_id' => $order->id,
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
            
            <!-- License Actions -->
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