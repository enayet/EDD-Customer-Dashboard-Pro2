<?php
/**
 * Licenses Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$dashboard_data->is_licensing_active()) {
    ?>
    <div class="eddcdp-empty-state">
        <div class="eddcdp-empty-icon">🔑</div>
        <h3><?php _e('Software Licensing not installed', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php _e('License management requires the Software Licensing add-on.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

$licenses = $dashboard_data->get_customer_licenses($user->ID);
?>

<h2 class="eddcdp-section-title"><?php _e('License Management', 'edd-customer-dashboard-pro'); ?></h2>

<div class="eddcdp-purchase-list">
    <?php if ($licenses) : ?>
        <?php foreach ($licenses as $license) : ?>
            <div class="eddcdp-purchase-item">
                <div class="eddcdp-purchase-header">
                    <div class="eddcdp-product-name"><?php echo get_the_title($license->download_id); ?></div>
                    <span class="eddcdp-status-badge eddcdp-status-<?php echo $license->is_expired() ? 'expired' : 'active'; ?>">
                        <?php echo $license->is_expired() ? __('Expired', 'edd-customer-dashboard-pro') : __('Active', 'edd-customer-dashboard-pro'); ?>
                    </span>
                </div>
                
                <div class="eddcdp-license-info">
                    <div class="eddcdp-license-key" title="<?php _e('Click to copy', 'edd-customer-dashboard-pro'); ?>"><?php echo esc_html($license->key); ?></div>
                    <div style="margin-top: 15px;">
                        <strong><?php _e('Purchase Date:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo $dashboard_data->format_date($license->date_created); ?><br>
                        <strong><?php _e('Expires:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <?php echo $license->expiration ? $dashboard_data->format_date($license->expiration) : __('Never', 'edd-customer-dashboard-pro'); ?><br>
                        <strong><?php _e('Activations:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <?php echo $license->activation_count; ?> <?php _e('of', 'edd-customer-dashboard-pro'); ?> 
                        <?php echo $license->activation_limit ? $license->activation_limit : __('Unlimited', 'edd-customer-dashboard-pro'); ?><br>
                    </div>
                    
                    <?php if (method_exists($license, 'get_sites')) : ?>
                        <?php $sites = $license->get_sites(); ?>
                        <?php if ($sites) : ?>
                            <div class="eddcdp-site-management" style="margin-top: 20px;">
                                <h4 style="margin-bottom: 10px;"><?php _e('Activated Sites', 'edd-customer-dashboard-pro'); ?></h4>
                                <div class="eddcdp-activated-sites">
                                    <?php foreach ($sites as $site) : ?>
                                        <div class="eddcdp-site-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 5px;">
                                            <span><?php echo esc_html($site->site_name); ?></span>
                                            <button class="eddcdp-btn eddcdp-btn-secondary" style="padding: 5px 10px; font-size: 0.8rem;" data-license="<?php echo esc_attr($license->key); ?>" data-site="<?php echo esc_attr($site->site_name); ?>">
                                                🔓 <?php _e('Deactivate', 'edd-customer-dashboard-pro'); ?>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($license->activation_limit == 0 || $license->activation_count < $license->activation_limit) : ?>
                            <div class="eddcdp-site-input-group" style="display: flex; gap: 10px; margin: 15px 0;">
                                <input type="url" placeholder="<?php _e('Enter your site URL (e.g., https://example.com)', 'edd-customer-dashboard-pro'); ?>" 
                                       style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px;" 
                                       class="eddcdp-site-url" data-license="<?php echo esc_attr($license->key); ?>">
                                <button class="eddcdp-btn eddcdp-btn-success eddcdp-activate-license">✅ <?php _e('Activate', 'edd-customer-dashboard-pro'); ?></button>
                            </div>
                        <?php else : ?>
                            <p style="color: #666; font-size: 0.9rem; font-style: italic; margin-top: 15px;">
                                <?php _e('License limit reached. Deactivate a site to activate on a new one.', 'edd-customer-dashboard-pro'); ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px;">
                        <?php if ($license->is_expired()) : ?>
                            <a href="<?php echo edd_get_checkout_uri(array('edd_license_key' => $license->key, 'download_id' => $license->download_id)); ?>" class="eddcdp-btn eddcdp-btn-warning">
                                🔄 <?php _e('Renew License', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        <?php endif; ?>
                        <button class="eddcdp-btn eddcdp-btn-secondary">⬆️ <?php _e('View Upgrades', 'edd-customer-dashboard-pro'); ?></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="eddcdp-empty-state">
            <div class="eddcdp-empty-icon">🔑</div>
            <h3><?php _e('No licenses yet', 'edd-customer-dashboard-pro'); ?></h3>
            <p><?php _e('Your software licenses will appear here.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    <?php endif; ?>
</div>