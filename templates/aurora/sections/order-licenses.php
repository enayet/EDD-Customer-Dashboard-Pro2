<?php
/**
 * Aurora Template - Order Licenses Section
 * File: templates/aurora/sections/order-licenses.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$order_details = EDDCDP_Order_Details::instance();
$order = $order_details->get_current_order_licenses();

if (!$order) {
    ?>
    <div class="error-container">
        <div class="error-card">
            <i class="fas fa-exclamation-triangle"></i>
            <p><?php esc_html_e('Order not found.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    </div>
    <?php
    return;
}

// Check if EDD Software Licensing is active
if (!class_exists('EDD_Software_Licensing')) {
    ?>
    <div class="warning-container">
        <div class="warning-card">
            <i class="fas fa-exclamation-circle"></i>
            <p><?php esc_html_e('Software Licensing extension is not active.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    </div>
    <?php
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
    ?>
    <div class="warning-container">
        <div class="warning-card">
            <i class="fas fa-key"></i>
            <p><?php esc_html_e('No licenses found for this order.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    </div>
    <?php
    return;
}

// License status configurations for Aurora theme
function get_aurora_license_status_config($license) {
    $status = $license->status;
    
    $configs = array(
        'active' => array(
            'class' => 'license-active',
            'icon' => 'fas fa-check-circle',
            'label' => esc_html__('Active', 'edd-customer-dashboard-pro'),
            'container_class' => 'license-card active'
        ),
        'inactive' => array(
            'class' => 'license-inactive',
            'icon' => 'fas fa-pause-circle',
            'label' => esc_html__('Inactive', 'edd-customer-dashboard-pro'),
            'container_class' => 'license-card inactive'
        ),
        'expired' => array(
            'class' => 'license-expired',
            'icon' => 'fas fa-times-circle',
            'label' => esc_html__('Expired', 'edd-customer-dashboard-pro'),
            'container_class' => 'license-card expired'
        ),
        'disabled' => array(
            'class' => 'license-disabled',
            'icon' => 'fas fa-ban',
            'label' => esc_html__('Disabled', 'edd-customer-dashboard-pro'),
            'container_class' => 'license-card disabled'
        )
    );
    
    return isset($configs[$status]) ? $configs[$status] : array(
        'class' => 'license-unknown',
        'icon' => 'fas fa-question-circle',
        'label' => ucfirst($status),
        'container_class' => 'license-card unknown'
    );
}
?>

<div class="order-licenses-container">
    
    <!-- Header with navigation -->
    <div class="licenses-header">
        <div class="header-nav">
            <a href="<?php echo esc_url($order_details->get_return_url()); ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <?php esc_html_e('Back to Dashboard', 'edd-customer-dashboard-pro'); ?>
            </a>
        </div>
        
        <div class="order-info">
            <h1 class="licenses-title">
                <i class="fas fa-key"></i>
                <?php 
                /* translators: %s: Order number */
                printf(esc_html__('Licenses for Order #%s', 'edd-customer-dashboard-pro'), esc_html($order->get_number())); 
                ?>
            </h1>
            <div class="order-meta">
                <span class="order-date">
                    <i class="fas fa-calendar-alt"></i>
                    <?php 
                    /* translators: %s: Formatted order date */
                    printf(esc_html__('Ordered on %s', 'edd-customer-dashboard-pro'), esc_html(date_i18n(get_option('date_format'), strtotime($order->date_created)))); 
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- License Management -->
    <div class="licenses-grid">
        <?php foreach ($order_licenses as $product_id => $product_data) : ?>
        
        <div class="product-licenses-section">
            <h2 class="product-title">
                <i class="fas fa-box"></i>
                <?php echo esc_html($product_data['product_name']); ?>
            </h2>
            
            <?php foreach ($product_data['licenses'] as $license) : 
                $license_info = get_aurora_license_status_config($license);
                
                // Get active sites using helper function (assuming this exists)
                $active_sites = function_exists('eddcdp_get_license_active_sites') ? 
                    eddcdp_get_license_active_sites($license->ID) : array();
                
                $activation_limit = (int) $license->activation_limit;
                $activation_count = count($active_sites);
                $activation_limit_reached = ($activation_limit > 0 && $activation_count >= $activation_limit);
                $is_unlimited = ($activation_limit === 0);
            ?>
            
            <div class="<?php echo esc_attr($license_info['container_class']); ?>">
                
                <!-- License Header -->
                <div class="license-header">
                    <div class="license-key-section">
                        <h3 class="license-label">
                            <i class="fas fa-key"></i>
                            <?php esc_html_e('License Key', 'edd-customer-dashboard-pro'); ?>
                        </h3>
                        <div class="license-key-container">
                            <code class="license-key" id="license-<?php echo esc_attr($license->ID); ?>">
                                <?php echo esc_html($license->license); ?>
                            </code>
                            <button class="copy-btn" onclick="copyLicenseKey('<?php echo esc_attr($license->ID); ?>')">
                                <i class="fas fa-copy"></i>
                                <?php esc_html_e('Copy', 'edd-customer-dashboard-pro'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="license-status">
                        <span class="status-badge <?php echo esc_attr($license_info['class']); ?>">
                            <i class="<?php echo esc_attr($license_info['icon']); ?>"></i>
                            <?php echo esc_html($license_info['label']); ?>
                        </span>
                    </div>
                </div>

                <!-- License Details -->
                <div class="license-details">
                    
                    <!-- Activation Info -->
                    <div class="detail-section">
                        <h4 class="detail-title">
                            <i class="fas fa-server"></i>
                            <?php esc_html_e('Site Activations', 'edd-customer-dashboard-pro'); ?>
                        </h4>
                        <div class="activation-info">
                            <div class="activation-count">
                                <?php if ($is_unlimited) : ?>
                                    <span class="count-badge unlimited">
                                        <?php 
                                        /* translators: %d: Number of active sites */
                                        printf(esc_html__('%d / Unlimited', 'edd-customer-dashboard-pro'), $activation_count); 
                                        ?>
                                    </span>
                                <?php else : ?>
                                    <span class="count-badge <?php echo $activation_limit_reached ? 'limit-reached' : 'has-slots'; ?>">
                                        <?php 
                                        /* translators: 1: Number of active sites, 2: Activation limit */
                                        printf(esc_html__('%1$d / %2$d', 'edd-customer-dashboard-pro'), $activation_count, $activation_limit); 
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Expiration Date -->
                    <?php if ($license->expiration && $license->expiration !== 'lifetime') : ?>
                    <div class="detail-section">
                        <h4 class="detail-title">
                            <i class="fas fa-calendar-times"></i>
                            <?php esc_html_e('Expires', 'edd-customer-dashboard-pro'); ?>
                        </h4>
                        <div class="expiration-date">
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($license->expiration))); ?>
                        </div>
                    </div>
                    <?php elseif ($license->expiration === 'lifetime') : ?>
                    <div class="detail-section">
                        <h4 class="detail-title">
                            <i class="fas fa-infinity"></i>
                            <?php esc_html_e('License Type', 'edd-customer-dashboard-pro'); ?>
                        </h4>
                        <div class="lifetime-badge">
                            <?php esc_html_e('Lifetime License', 'edd-customer-dashboard-pro'); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Active Sites List -->
                <?php if (!empty($active_sites)) : ?>
                <div class="active-sites-section">
                    <h4 class="detail-title">
                        <i class="fas fa-globe"></i>
                        <?php esc_html_e('Active Sites', 'edd-customer-dashboard-pro'); ?>
                    </h4>
                    <div class="sites-list">
                        <?php foreach ($active_sites as $site) : ?>
                        <div class="site-item">
                            <div class="site-info">
                                <span class="site-url"><?php echo esc_html($site['site_name']); ?></span>
                                <span class="activation-date">
                                    <?php 
                                    /* translators: %s: Site activation date */
                                    printf(esc_html__('Activated: %s', 'edd-customer-dashboard-pro'), 
                                        esc_html(date_i18n(get_option('date_format'), strtotime($site['activated']))));
                                    ?>
                                </span>
                            </div>
                            <?php if ($license->status === 'active') : ?>
                            <button class="deactivate-btn" onclick="deactivateSite('<?php echo esc_attr($license->ID); ?>', '<?php echo esc_attr($site['site_name']); ?>')">
                                <i class="fas fa-times"></i>
                                <?php esc_html_e('Deactivate', 'edd-customer-dashboard-pro'); ?>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Site Activation Form -->
                <?php if ($license->status === 'active' && (!$activation_limit_reached || $is_unlimited)) : ?>
                <div class="activation-form-section">
                    <h4 class="detail-title">
                        <i class="fas fa-plus-circle"></i>
                        <?php esc_html_e('Activate New Site', 'edd-customer-dashboard-pro'); ?>
                    </h4>
                    <form class="activation-form" onsubmit="activateNewSite(event, '<?php echo esc_attr($license->ID); ?>')">
                        <div class="form-group">
                            <input type="url" 
                                   name="site_url" 
                                   class="site-url-input" 
                                   placeholder="<?php esc_attr_e('Enter your site URL (e.g., https://yoursite.com)', 'edd-customer-dashboard-pro'); ?>" 
                                   required>
                            <button type="submit" class="activate-btn">
                                <i class="fas fa-plus"></i>
                                <?php esc_html_e('Activate Site', 'edd-customer-dashboard-pro'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

            </div>
            
            <?php endforeach; ?>
        </div>
        
        <?php endforeach; ?>
    </div>

</div>

<!-- JavaScript for license management -->
<script>
function copyLicenseKey(licenseId) {
    const licenseKey = document.getElementById('license-' + licenseId).textContent;
    navigator.clipboard.writeText(licenseKey).then(function() {
        // Show success message
        showNotification('<?php esc_html_e('License key copied to clipboard!', 'edd-customer-dashboard-pro'); ?>', 'success');
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = licenseKey;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('<?php esc_html_e('License key copied to clipboard!', 'edd-customer-dashboard-pro'); ?>', 'success');
    });
}

function activateNewSite(event, licenseId) {
    event.preventDefault();
    const form = event.target;
    const siteUrl = form.site_url.value;
    
    if (!siteUrl) {
        showNotification('<?php esc_html_e('Please enter a site URL.', 'edd-customer-dashboard-pro'); ?>', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('.activate-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php esc_html_e('Activating...', 'edd-customer-dashboard-pro'); ?>';
    submitBtn.disabled = true;
    
    // AJAX request to activate site
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'eddcdp_activate_license_site',
            license_id: licenseId,
            site_url: siteUrl,
            nonce: '<?php echo wp_create_nonce('eddcdp_license_nonce'); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.data.message || '<?php esc_html_e('Site activated successfully!', 'edd-customer-dashboard-pro'); ?>', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.data.message || '<?php esc_html_e('Failed to activate site.', 'edd-customer-dashboard-pro'); ?>', 'error');
        }
    })
    .catch(error => {
        showNotification('<?php esc_html_e('An error occurred. Please try again.', 'edd-customer-dashboard-pro'); ?>', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function deactivateSite(licenseId, siteUrl) {
    if (!confirm('<?php esc_html_e('Are you sure you want to deactivate this site?', 'edd-customer-dashboard-pro'); ?>')) {
        return;
    }
    
    // AJAX request to deactivate site
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'eddcdp_deactivate_license_site',
            license_id: licenseId,
            site_url: siteUrl,
            nonce: '<?php echo wp_create_nonce('eddcdp_license_nonce'); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.data.message || '<?php esc_html_e('Site deactivated successfully!', 'edd-customer-dashboard-pro'); ?>', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.data.message || '<?php esc_html_e('Failed to deactivate site.', 'edd-customer-dashboard-pro'); ?>', 'error');
        }
    })
    .catch(error => {
        showNotification('<?php esc_html_e('An error occurred. Please try again.', 'edd-customer-dashboard-pro'); ?>', 'error');
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `aurora-notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Hide and remove notification
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => document.body.removeChild(notification), 300);
    }, 3000);
}
</script>