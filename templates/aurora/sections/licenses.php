<?php
/**
 * Aurora Template - Licenses Section
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$dashboard_data->is_licensing_active()) {
    ?>
    <div class="eddcdp-empty-state">
        <div class="eddcdp-empty-icon">
            <i class="fas fa-key"></i>
        </div>
        <h3><?php esc_html_e('Software Licensing not installed', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('License management requires the Software Licensing add-on.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

$licenses = $dashboard_data->get_customer_licenses($user->ID);
$active_count = 0;
$expiring_count = 0;
$expired_count = 0;

// Calculate license stats
if ($licenses) {
    foreach ($licenses as $license) {
        if ($license->is_expired()) {
            $expired_count++;
        } else {
            $active_count++;
            // Check if expiring within 30 days
            if ($license->expiration && strtotime($license->expiration) < strtotime('+30 days')) {
                $expiring_count++;
            }
        }
    }
}
?>

<h2 class="eddcdp-section-title"><?php esc_html_e('License Management', 'edd-customer-dashboard-pro'); ?></h2>

<!-- License Stats -->
<div class="eddcdp-stats-grid" style="grid-column: 1; margin-bottom: 30px;">
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-number"><?php echo esc_html($active_count); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-key"></i>
            <?php esc_html_e('Active Licenses', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-number"><?php echo esc_html($expiring_count); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-clock"></i>
            <?php esc_html_e('Expiring Soon', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    
    <div class="eddcdp-stat-card">
        <div class="eddcdp-stat-number"><?php echo esc_html($expired_count); ?></div>
        <div class="eddcdp-stat-label">
            <i class="fas fa-times-circle"></i>
            <?php esc_html_e('Expired', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>

<?php if ($licenses) : ?>
    <table class="eddcdp-products-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Product', 'edd-customer-dashboard-pro'); ?></th>
                <th><?php esc_html_e('License Key', 'edd-customer-dashboard-pro'); ?></th>
                <th><?php esc_html_e('Status', 'edd-customer-dashboard-pro'); ?></th>
                <th><?php esc_html_e('Expires', 'edd-customer-dashboard-pro'); ?></th>
                <th><?php esc_html_e('Actions', 'edd-customer-dashboard-pro'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($licenses as $license) : ?>
                <tr>
                    <td>
                        <div class="eddcdp-product-info">
                            <div class="eddcdp-product-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <div class="eddcdp-product-name"><?php echo esc_html(get_the_title($license->download_id)); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="eddcdp-license-key" title="<?php esc_attr_e('Click to copy', 'edd-customer-dashboard-pro'); ?>">
                            <?php echo esc_html($license->key); ?>
                        </div>
                    </td>
                    <td>
                        <span class="eddcdp-status-badge <?php echo $license->is_expired() ? 'eddcdp-status-expired' : 'eddcdp-status-active'; ?>">
                            <?php echo $license->is_expired() ? esc_html__('Expired', 'edd-customer-dashboard-pro') : esc_html__('Active', 'edd-customer-dashboard-pro'); ?>
                        </span>
                    </td>
                    <td>
                        <?php echo $license->expiration ? esc_html($dashboard_data->format_date($license->expiration)) : esc_html__('Never', 'edd-customer-dashboard-pro'); ?>
                    </td>
                    <td>
                        <?php if ($license->is_expired()) : ?>
                            <a href="<?php echo esc_url(edd_get_checkout_uri(array('edd_license_key' => $license->key, 'download_id' => $license->download_id))); ?>" class="eddcdp-btn eddcdp-btn-success">
                                <i class="fas fa-sync-alt"></i> <?php esc_html_e('Renew', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        <?php else : ?>
                            <button class="eddcdp-btn eddcdp-btn-secondary eddcdp-manage-license" data-license="<?php echo esc_attr($license->key); ?>">
                                <i class="fas fa-cog"></i> <?php esc_html_e('Manage', 'edd-customer-dashboard-pro'); ?>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <!-- License management row (initially hidden) -->
                <tr class="eddcdp-license-details" id="license-details-<?php echo esc_attr($license->key); ?>" style="display: none;">
                    <td colspan="5">
                        <div class="eddcdp-license-info">
                            <div style="margin-bottom: 15px;">
                                <strong><?php esc_html_e('Purchase Date:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html($dashboard_data->format_date($license->date_created)); ?><br>
                                <strong><?php esc_html_e('Activations:', 'edd-customer-dashboard-pro'); ?></strong> 
                                <?php echo esc_html($license->activation_count); ?> <?php esc_html_e('of', 'edd-customer-dashboard-pro'); ?> 
                                <?php echo $license->activation_limit ? esc_html($license->activation_limit) : esc_html__('Unlimited', 'edd-customer-dashboard-pro'); ?>
                            </div>
                            
                            <?php
                                $sites = $dashboard_data->get_license_sites($license->key);
                            ?>
                                <?php if (!empty($sites)) : ?>
                                    <div class="eddcdp-site-management" style="margin-bottom: 20px;">
                                        <h4><?php esc_html_e('Activated Sites', 'edd-customer-dashboard-pro'); ?></h4>
                                        <div class="eddcdp-activated-sites">
                                            <?php foreach ($sites as $site) : ?>
                                                <div class="eddcdp-site-item">
                                                    <span><?php echo esc_html($site->site_name); ?></span>
                                                    <button class="eddcdp-btn eddcdp-btn-secondary" style="padding: 5px 10px; font-size: 0.8rem;" data-license="<?php echo esc_attr($license->key); ?>" data-site="<?php echo esc_attr($site->site_name); ?>">
                                                        <i class="fas fa-times"></i> <?php esc_html_e('Deactivate', 'edd-customer-dashboard-pro'); ?>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($license->activation_limit == 0 || $license->activation_count < $license->activation_limit) : ?>
                                    <div class="eddcdp-site-input-group">
                                        <input type="url" placeholder="<?php esc_attr_e('Enter your site URL (e.g., https://example.com)', 'edd-customer-dashboard-pro'); ?>" 
                                               class="eddcdp-site-url" data-license="<?php echo esc_attr($license->key); ?>">
                                        <button class="eddcdp-btn eddcdp-btn-success eddcdp-activate-license">
                                            <i class="fas fa-plus"></i> <?php esc_html_e('Activate', 'edd-customer-dashboard-pro'); ?>
                                        </button>
                                    </div>
                                <?php else : ?>
                                    <p style="color: var(--gray); font-size: 0.9rem; font-style: italic;">
                                        <?php esc_html_e('License limit reached. Deactivate a site to activate on a new one.', 'edd-customer-dashboard-pro'); ?>
                                    </p>
                                <?php endif; ?>
                            
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <div class="eddcdp-empty-state">
        <div class="eddcdp-empty-icon">
            <i class="fas fa-key"></i>
        </div>
        <h3><?php esc_html_e('No licenses yet', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('Your software licenses will appear here.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
<?php endif; ?>