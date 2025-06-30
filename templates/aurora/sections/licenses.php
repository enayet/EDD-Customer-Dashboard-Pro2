<?php
/**
 * Aurora Licenses Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Software Licensing is active
if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
    echo '<div class="empty-state">';
    echo '<div class="empty-icon"><i class="fas fa-key"></i></div>';
    echo '<h3 class="empty-title">' . esc_html__('Software Licensing Not Available', 'edd-customer-dashboard-pro') . '</h3>';
    echo '<p class="empty-text">' . esc_html__('The Software Licensing extension is not active on this site.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Get current user and customer
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    echo '<div class="empty-state">';
    echo '<div class="empty-icon"><i class="fas fa-user-times"></i></div>';
    echo '<h3 class="empty-title">' . esc_html__('Customer data not found.', 'edd-customer-dashboard-pro') . '</h3>';
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

// Calculate license stats
$active_licenses = 0;
$expired_licenses = 0;
$expiring_soon = 0;

if ($licenses) {
    foreach ($licenses as $license) {
        $expiration_status = eddcdp_get_license_expiration_status($license);
        
        if ($license->status === 'active' && !$expiration_status['is_expired']) {
            $active_licenses++;
            if ($expiration_status['expires_soon']) {
                $expiring_soon++;
            }
        } else {
            $expired_licenses++;
        }
    }
}
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1 class="dashboard-title"><?php esc_html_e('License Management', 'edd-customer-dashboard-pro'); ?></h1>
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="<?php esc_attr_e('Search licenses...', 'edd-customer-dashboard-pro'); ?>">
    </div>
</div>

<?php if ($licenses) : ?>
<!-- License Stats -->
<div class="stats-grid">
    <div class="stat-card licenses">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($active_licenses)); ?></div>
        <div class="stat-label">
            <i class="fas fa-key"></i>
            <?php esc_html_e('Active Licenses', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: var(--aurora-warning);">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($expiring_soon)); ?></div>
        <div class="stat-label">
            <i class="fas fa-clock"></i>
            <?php esc_html_e('Expiring Soon', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: var(--aurora-danger);">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($expired_licenses)); ?></div>
        <div class="stat-label">
            <i class="fas fa-times-circle"></i>
            <?php esc_html_e('Expired', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: #9b59b6;">
        <div class="stat-value"><?php echo esc_html(number_format_i18n(count($licenses))); ?></div>
        <div class="stat-label">
            <i class="fas fa-certificate"></i>
            <?php esc_html_e('Total Licenses', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>

<!-- Licenses Table -->
<table class="products-table">
    <thead>
        <tr>
            <th><?php esc_html_e('Product', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('License Key', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Status', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Expires', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Sites', 'edd-customer-dashboard-pro'); ?></th>
            <th><?php esc_html_e('Actions', 'edd-customer-dashboard-pro'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($licenses as $license) : 
            $download_id = $license->download_id;
            $download_name = get_the_title($download_id);
            
            // Use helper function to get license status info
            $license_info = eddcdp_get_license_status_info($license);
            $expiration_status = eddcdp_get_license_expiration_status($license);
            
            // Get active sites using helper function
            $active_sites = eddcdp_get_license_active_sites($license->ID);
            
            $activation_limit = (int) $license->activation_limit;
            $activation_count = count($active_sites);
            
            // Get price name if available
            $price_name = esc_html__('Standard', 'edd-customer-dashboard-pro');
            if (!empty($license->price_id) && function_exists('edd_get_price_option_name')) {
                $price_option = edd_get_price_option_name($download_id, $license->price_id);
                if ($price_option) {
                    $price_name = $price_option;
                }
            }
            
            // Get product icon
            $product_icon = 'fas fa-box';
            if (stripos($download_name, 'plugin') !== false) {
                $product_icon = 'fas fa-plug';
            } elseif (stripos($download_name, 'theme') !== false) {
                $product_icon = 'fas fa-paint-brush';
            } elseif (stripos($download_name, 'voice') !== false) {
                $product_icon = 'fas fa-microphone-alt';
            } elseif (stripos($download_name, 'ecommerce') !== false) {
                $product_icon = 'fas fa-shopping-cart';
            }
        ?>
        <tr>
            <td>
                <div class="product-info">
                    <div class="product-icon">
                        <i class="<?php echo esc_attr($product_icon); ?>"></i>
                    </div>
                    <div>
                        <div class="product-name"><?php echo esc_html($download_name); ?></div>
                        <div class="product-meta"><?php echo esc_html($price_name); ?></div>
                    </div>
                </div>
            </td>
            <td>
                <div class="license-key" data-license="<?php echo esc_attr($license->license_key); ?>">
                    <?php echo esc_html($license->license_key); ?>
                </div>
            </td>
            <td>
                <span class="status-badge <?php 
                    if ($license_info['text'] === esc_html__('Active', 'edd-customer-dashboard-pro')) {
                        echo 'status-active';
                    } elseif ($license_info['text'] === esc_html__('Expired', 'edd-customer-dashboard-pro')) {
                        echo 'status-expired';
                    } else {
                        echo 'status-pending';
                    }
                ?>">
                    <?php echo esc_html($license_info['icon'] . ' ' . $license_info['text']); ?>
                </span>
                
                <?php if ($expiration_status['expires_soon'] && !$expiration_status['is_expired']) : ?>
                <div style="margin-top: 5px;">
                    <span class="status-badge" style="background: rgba(253, 203, 110, 0.1); color: var(--aurora-warning); font-size: 0.7rem;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <?php 
                        /* translators: %d: Days until expiry */
                        printf(esc_html__('%d days left', 'edd-customer-dashboard-pro'), esc_html($expiration_status['days_until_expiry'])); 
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') : ?>
                    <div style="font-weight: 500;">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($license->expiration))); ?>
                    </div>
                    <?php if ($expiration_status['is_expired']) : ?>
                    <div style="font-size: 0.8rem; color: var(--aurora-danger);">
                        <i class="fas fa-times-circle"></i> <?php esc_html_e('Expired', 'edd-customer-dashboard-pro'); ?>
                    </div>
                    <?php elseif ($expiration_status['expires_soon']) : ?>
                    <div style="font-size: 0.8rem; color: var(--aurora-warning);">
                        <i class="fas fa-exclamation-triangle"></i> <?php esc_html_e('Soon', 'edd-customer-dashboard-pro'); ?>
                    </div>
                    <?php endif; ?>
                <?php else : ?>
                    <span style="color: var(--aurora-secondary); font-weight: 500;">
                        <i class="fas fa-infinity"></i> <?php esc_html_e('Never', 'edd-customer-dashboard-pro'); ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <div style="font-weight: 500;">
                    <?php 
                    if ($activation_limit > 0) {
                        /* translators: %1$d: current activations, %2$d: activation limit */
                        printf(esc_html__('%1$d/%2$d', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($activation_count)), esc_html(number_format_i18n($activation_limit)));
                    } else {
                        /* translators: %d: current activations */
                        printf(esc_html__('%d/∞', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($activation_count)));
                    }
                    ?>
                </div>
                <div style="font-size: 0.8rem; color: var(--aurora-gray);">
                    <?php esc_html_e('sites used', 'edd-customer-dashboard-pro'); ?>
                </div>
            </td>
            <td>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <!-- Manage License Button -->
                    <button onclick="showLicenseModal(<?php echo esc_js($license->ID); ?>, '<?php echo esc_js($download_name); ?>')" class="btn btn-outline">
                        <i class="fas fa-cog"></i> <?php esc_html_e('Manage', 'edd-customer-dashboard-pro'); ?>
                    </button>
                    
                    <!-- Renew Button for Expired -->
                    <?php if ($license_info['text'] === esc_html__('Expired', 'edd-customer-dashboard-pro') || $expiration_status['expires_soon']) : ?>
                    <a href="<?php echo esc_url(edd_get_checkout_uri()); ?>?edd_action=purchase_renewal&license_id=<?php echo esc_attr($license->ID); ?>" class="btn btn-success">
                        <i class="fas fa-sync-alt"></i> <?php esc_html_e('Renew', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Upgrade Button -->
                    <a href="<?php echo esc_url(get_permalink($license->download_id)); ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-up"></i> <?php esc_html_e('Upgrade', 'edd-customer-dashboard-pro'); ?>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-key"></i>
    </div>
    <h3 class="empty-title"><?php esc_html_e('No Licenses Found', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="empty-text"><?php esc_html_e('You don\'t have any software licenses yet. Purchase a licensed product to get started!', 'edd-customer-dashboard-pro'); ?></p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo esc_url(home_url('/downloads/')); ?>" class="btn btn-primary">
            <i class="fas fa-store"></i> <?php esc_html_e('Browse Licensed Products', 'edd-customer-dashboard-pro'); ?>
        </a>
        <button onclick="window.AuroraDashboard?.switchSection('products')" class="btn btn-outline">
            <i class="fas fa-box-open"></i> <?php esc_html_e('View My Products', 'edd-customer-dashboard-pro'); ?>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- License Management Modal (Placeholder) -->
<div id="licenseModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: var(--aurora-dark);"><?php esc_html_e('Manage License', 'edd-customer-dashboard-pro'); ?></h3>
            <button onclick="closeLicenseModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--aurora-gray);">&times;</button>
        </div>
        <div id="licenseModalContent">
            <p><?php esc_html_e('License management features will be available here.', 'edd-customer-dashboard-pro'); ?></p>
            <p style="color: var(--aurora-gray); font-size: 0.9rem;"><?php esc_html_e('This includes site activation/deactivation, license details, and more.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    </div>
</div>

<script>
function showLicenseModal(licenseId, productName) {
    document.getElementById('licenseModal').style.display = 'flex';
    // Here you would load license details via AJAX
    console.log('Managing license:', licenseId, 'for product:', productName);
}

function closeLicenseModal() {
    document.getElementById('licenseModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('licenseModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeLicenseModal();
    }
});
</script>