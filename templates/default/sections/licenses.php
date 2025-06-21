<?php
/**
 * Enhanced Licenses Section Template - Form-based License Management
 * File: templates/default/sections/licenses.php
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$dashboard_data->is_licensing_active()) {
    ?>
    <div class="eddcdp-empty-state">
        <div class="eddcdp-empty-icon">🔑</div>
        <h3><?php esc_html_e('Software Licensing not installed', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('License management requires the Software Licensing add-on.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

// Show success/error messages from form submissions
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if (isset($_GET['eddcdp_message'])) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $message_type = sanitize_text_field(wp_unslash($_GET['eddcdp_message']));
    
    if ($message_type === 'license_activated') {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $site_url = isset($_GET['eddcdp_site']) ? sanitize_text_field(wp_unslash($_GET['eddcdp_site'])) : '';
        echo '<div class="eddcdp-success-message" style="background: linear-gradient(135deg, rgba(67, 233, 123, 0.1), rgba(56, 249, 215, 0.1)); border: 2px solid rgba(67, 233, 123, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px; color: #2d7d32; display: flex; align-items: center; gap: 10px;">';
        echo '<span style="font-size: 1.2rem;">✅</span>';
        echo '<div>';
        echo '<strong>' . esc_html__('License activated successfully!', 'edd-customer-dashboard-pro') . '</strong>';
        if ($site_url) {
            echo '<br><small>' . esc_html(sprintf(__('Site: %s', 'edd-customer-dashboard-pro'), $site_url)) . '</small>';
        }
        echo '</div>';
        echo '</div>';
    } elseif ($message_type === 'license_deactivated') {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $site_url = isset($_GET['eddcdp_site']) ? sanitize_text_field(wp_unslash($_GET['eddcdp_site'])) : '';
        echo '<div class="eddcdp-success-message" style="background: linear-gradient(135deg, rgba(67, 233, 123, 0.1), rgba(56, 249, 215, 0.1)); border: 2px solid rgba(67, 233, 123, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px; color: #2d7d32; display: flex; align-items: center; gap: 10px;">';
        echo '<span style="font-size: 1.2rem;">✅</span>';
        echo '<div>';
        echo '<strong>' . esc_html__('License deactivated successfully!', 'edd-customer-dashboard-pro') . '</strong>';
        if ($site_url) {
            echo '<br><small>' . esc_html(sprintf(__('Site: %s', 'edd-customer-dashboard-pro'), $site_url)) . '</small>';
        }
        echo '</div>';
        echo '</div>';
    }
}

$licenses = $dashboard_data->get_customer_licenses($user->ID);
?>

<h2 class="eddcdp-section-title"><?php esc_html_e('License Management', 'edd-customer-dashboard-pro'); ?></h2>

<div class="eddcdp-purchase-list">
    <?php if ($licenses) : ?>
        <?php foreach ($licenses as $license) : ?>
            <?php 
            // Get license status info using dashboard data helper
            $status_info = $dashboard_data->get_license_status_info($license);
            ?>
            <div class="eddcdp-purchase-item">
                <div class="eddcdp-purchase-header">
                    <div class="eddcdp-order-info">
                        <div class="eddcdp-product-name"><?php echo esc_html(get_the_title($license->download_id)); ?></div>
                        <div class="eddcdp-order-meta">
                            <span class="eddcdp-order-date"><?php echo esc_html($dashboard_data->format_date($license->date_created)); ?></span>
                            <span class="eddcdp-order-total">
                                <?php 
                                // translators: %s is the license type or variation name
                                printf(esc_html__('License: %s', 'edd-customer-dashboard-pro'), 
                                    !empty($license->price_id) ? esc_html(edd_get_price_option_name($license->download_id, $license->price_id)) : esc_html__('Standard', 'edd-customer-dashboard-pro')
                                ); 
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="eddcdp-license-status-container">
                        <span class="eddcdp-status-badge eddcdp-status-<?php echo esc_attr($status_info['status']); ?>">
                            <?php echo esc_html($status_info['label']); ?>
                        </span>
                        <?php if ($status_info['status'] === 'expiring_soon') : ?>
                            <div class="eddcdp-expiry-warning">
                                <?php 
                                // translators: %d is the number of days until expiration
                                printf(esc_html__('⚠️ Expires in %d days', 'edd-customer-dashboard-pro'), esc_html($status_info['days_remaining'])); 
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="eddcdp-license-info">
                    <!-- License Key Display -->
                    <div class="eddcdp-license-key-container">
                        <label class="eddcdp-license-key-label"><?php esc_html_e('License Key', 'edd-customer-dashboard-pro'); ?></label>
                        <div class="eddcdp-license-key" title="<?php esc_attr_e('Click to copy', 'edd-customer-dashboard-pro'); ?>">
                            <?php echo esc_html($license->key); ?>
                        </div>
                    </div>
                    
                    <!-- License Details -->
                    <div class="eddcdp-license-details" style="margin: 20px 0; padding: 15px; background: rgba(248, 250, 252, 0.6); border-radius: 8px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <strong><?php esc_html_e('Purchase Date:', 'edd-customer-dashboard-pro'); ?></strong><br>
                                <span style="color: #666;"><?php echo esc_html($dashboard_data->format_date($license->date_created)); ?></span>
                            </div>
                            <div>
                                <strong><?php esc_html_e('Expires:', 'edd-customer-dashboard-pro'); ?></strong><br>
                                <span style="color: #666;">
                                    <?php echo $license->expiration && $license->expiration !== '0000-00-00 00:00:00' ? 
                                        esc_html($dashboard_data->format_date($license->expiration)) : 
                                        esc_html__('Lifetime', 'edd-customer-dashboard-pro'); ?>
                                </span>
                            </div>
                            <div>
                                <strong><?php esc_html_e('Activations:', 'edd-customer-dashboard-pro'); ?></strong><br>
                                <span style="color: #666;">
                                    <?php echo esc_html($license->activation_count); ?> <?php esc_html_e('of', 'edd-customer-dashboard-pro'); ?> 
                                    <?php echo $license->activation_limit ? esc_html($license->activation_limit) : esc_html__('Unlimited', 'edd-customer-dashboard-pro'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Activation Progress Bar -->
                        <?php if ($license->activation_limit > 0) : ?>
                            <?php 
                            $usage_percentage = ($license->activation_count / $license->activation_limit) * 100;
                            $progress_class = $usage_percentage >= 90 ? 'danger' : ($usage_percentage >= 70 ? 'warning' : 'normal');
                            ?>
                            <div class="eddcdp-activation-progress" style="margin-top: 15px;">
                                <div class="eddcdp-progress-bar <?php echo esc_attr($progress_class); ?>" 
                                     style="width: <?php echo esc_attr($usage_percentage); ?>%;"></div>
                            </div>
                            
                            <?php if ($usage_percentage >= 90) : ?>
                                <div class="eddcdp-activation-warning <?php echo $usage_percentage >= 100 ? 'danger' : ''; ?>" style="margin-top: 8px;">
                                    <?php if ($usage_percentage >= 100) : ?>
                                        ⚠️ <?php esc_html_e('Activation limit reached! Deactivate a site to activate on a new one.', 'edd-customer-dashboard-pro'); ?>
                                    <?php else : ?>
                                        ⚠️ <?php esc_html_e('Approaching activation limit!', 'edd-customer-dashboard-pro'); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Site Management Section -->
                    <div class="eddcdp-site-management" style="margin-top: 20px;">
                        <h4 style="margin-bottom: 15px; color: #333; display: flex; align-items: center; gap: 8px;">
                            🌐 <?php esc_html_e('Site Management', 'edd-customer-dashboard-pro'); ?>
                        </h4>
                        
                        <?php 
                        // Get activated sites using the dashboard data helper method
                        $sites = $dashboard_data->get_license_sites($license->key);
                        ?>
                        
                        <!-- Show Activated Sites -->
                        <?php if (!empty($sites)) : ?>
                            <div class="eddcdp-activated-sites" style="margin-bottom: 15px;">
                                <h5 style="margin-bottom: 10px; color: #555; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <?php esc_html_e('Activated Sites', 'edd-customer-dashboard-pro'); ?>
                                </h5>
                                <?php foreach ($sites as $site) : ?>
                                    <div class="eddcdp-site-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 8px; border-left: 3px solid #43e97b;">
                                        <div class="eddcdp-site-url" style="flex: 1; margin-right: 15px;">
                                            <?php echo esc_html($site->site_name); ?>
                                        </div>
                                        
                                        <!-- Deactivation Form -->
                                        <form method="post" style="margin: 0;" class="eddcdp-deactivation-form">
                                            <?php wp_nonce_field('eddcdp_deactivate_license', 'eddcdp_license_nonce'); ?>
                                            <input type="hidden" name="eddcdp_action" value="deactivate_license">
                                            <input type="hidden" name="license_key" value="<?php echo esc_attr($license->key); ?>">
                                            <input type="hidden" name="site_url" value="<?php echo esc_attr($site->site_name); ?>">
                                            
                                            <button type="submit" 
                                                    class="eddcdp-btn eddcdp-btn-secondary" 
                                                    style="padding: 6px 12px; font-size: 0.8rem;"
                                                    onclick="return confirm('<?php echo esc_js(sprintf(__('Are you sure you want to deactivate this license from %s?', 'edd-customer-dashboard-pro'), $site->site_name)); ?>')">
                                                🔓 <?php esc_html_e('Deactivate', 'edd-customer-dashboard-pro'); ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="eddcdp-no-activations">
                                <div class="eddcdp-no-activations-icon">🌐</div>
                                <p><?php esc_html_e('No sites activated yet. Add your first site below.', 'edd-customer-dashboard-pro'); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Activation Form -->
                        <?php if ($license->activation_limit == 0 || $license->activation_count < $license->activation_limit) : ?>
                            <div class="eddcdp-activation-form <?php echo ($status_info['is_expired']) ? 'disabled' : ''; ?>">
                                <?php if ($status_info['is_expired']) : ?>
                                    <div class="eddcdp-activation-form-title">
                                        ⚠️ <?php esc_html_e('License Expired - Renewal Required', 'edd-customer-dashboard-pro'); ?>
                                    </div>
                                    <p style="margin: 10px 0; color: #d32f2f; font-size: 0.9rem;">
                                        <?php esc_html_e('This license has expired. Please renew it before activating new sites.', 'edd-customer-dashboard-pro'); ?>
                                    </p>
                                <?php else : ?>
                                    <div class="eddcdp-activation-form-title">
                                        🔗 <?php esc_html_e('Activate on New Site', 'edd-customer-dashboard-pro'); ?>
                                    </div>
                                    
                                    <form method="post" style="margin-top: 15px;" class="eddcdp-activation-form-inner">
                                        <?php wp_nonce_field('eddcdp_activate_license', 'eddcdp_license_nonce'); ?>
                                        <input type="hidden" name="eddcdp_action" value="activate_license">
                                        <input type="hidden" name="license_key" value="<?php echo esc_attr($license->key); ?>">
                                        
                                        <div class="eddcdp-site-input-group" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                            <input type="url" 
                                                   name="site_url"
                                                   placeholder="<?php esc_attr_e('Enter your site URL (e.g., https://example.com)', 'edd-customer-dashboard-pro'); ?>" 
                                                   style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 0.9rem;" 
                                                   required>
                                            <button type="submit" class="eddcdp-btn eddcdp-btn-success">
                                                ✅ <?php esc_html_e('Activate', 'edd-customer-dashboard-pro'); ?>
                                            </button>
                                        </div>
                                        
                                        <p style="margin: 8px 0 0 0; color: #666; font-size: 0.85rem;">
                                            💡 <?php esc_html_e('Enter the full URL where you want to use this license (including http:// or https://)', 'edd-customer-dashboard-pro'); ?>
                                        </p>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <div class="eddcdp-activation-warning">
                                ⚠️ <?php esc_html_e('License activation limit reached. Deactivate a site above to activate on a new one.', 'edd-customer-dashboard-pro'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- License Actions -->
                    <div class="eddcdp-license-actions-grid" style="margin-top: 25px;">
                        <?php if ($status_info['is_expired']) : ?>
                            <a href="<?php echo esc_url($dashboard_data->get_license_renewal_url($license)); ?>" 
                               class="eddcdp-btn eddcdp-btn-warning">
                                🔄 <?php esc_html_e('Renew License', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($dashboard_data->license_has_upgrades($license)) : ?>
                            <a href="<?php echo esc_url($dashboard_data->get_license_upgrade_url($license)); ?>" 
                               class="eddcdp-btn eddcdp-btn-secondary">
                                ⬆️ <?php esc_html_e('Upgrade License', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo esc_url($dashboard_data->get_license_invoice_url($license)); ?>" 
                           class="eddcdp-btn eddcdp-btn-secondary">
                            📄 <?php esc_html_e('View Invoice', 'edd-customer-dashboard-pro'); ?>
                        </a>
                        
                        <a href="<?php echo esc_url(add_query_arg('section', 'support', remove_query_arg('eddcdp_message'))); ?>" 
                           class="eddcdp-btn eddcdp-btn-secondary">
                            💬 <?php esc_html_e('Get Support', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="eddcdp-empty-state">
            <div class="eddcdp-empty-icon">🔑</div>
            <h3><?php esc_html_e('No licenses yet', 'edd-customer-dashboard-pro'); ?></h3>
            <p><?php esc_html_e('Your software licenses will appear here when you purchase products with licensing.', 'edd-customer-dashboard-pro'); ?></p>
            <a href="<?php echo esc_url(home_url()); ?>" class="eddcdp-btn">
                🛒 <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>