<?php
/**
 * Licenses Section Template - Updated Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Software Licensing is active
if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
    ?>
    <div class="empty-state">
        <div class="empty-icon">🔑</div>
        <h3><?php esc_html_e('Software Licensing Not Available', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('Software Licensing extension is not active.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

// Get current user and customer
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    ?>
    <div class="empty-state">
        <div class="empty-icon">⚠️</div>
        <h3><?php esc_html_e('Customer Data Not Found', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('Unable to retrieve your customer information.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
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

<h2 class="section-title"><?php esc_html_e('License Management', 'edd-customer-dashboard-pro'); ?></h2>

<?php if ($licenses) : ?>
<div class="purchase-list">
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
        
        // Get expiration info
        $expiration_info = eddcdp_get_license_expiration_status($license);
    ?>
    
    <!-- License Item -->
    <div class="purchase-item <?php echo esc_attr($license_info['container_class']); ?>">
        <div class="purchase-header">
            <div class="order-info">
                <div class="product-name"><?php echo esc_html($download_name); ?></div>
                <div class="order-meta">
                    <span class="order-date">
                        <?php 
                        /* translators: %s: License purchase date */
                        printf(esc_html__('Purchased: %s', 'edd-customer-dashboard-pro'), esc_html(date_i18n(get_option('date_format'), strtotime($license->date_created)))); 
                        ?>
                    </span>
                    <span class="license-type">
                        <?php 
                        /* translators: %s: License type/variation name */
                        printf(esc_html__('Type: %s', 'edd-customer-dashboard-pro'), esc_html($price_name)); 
                        ?>
                    </span>
                    <span class="activation-info">
                        <?php 
                        if ($activation_limit > 0) {
                            /* translators: %1$d: current activations, %2$d: activation limit */
                            printf(esc_html__('Sites: %1$d/%2$d', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($activation_count)), esc_html(number_format_i18n($activation_limit)));
                        } else {
                            /* translators: %d: current activations */
                            printf(esc_html__('Sites: %d/Unlimited', 'edd-customer-dashboard-pro'), esc_html(number_format_i18n($activation_count)));
                        }
                        ?>
                    </span>
                </div>
            </div>
            
            <span class="status-badge <?php echo esc_attr($license_info['badge_class']); ?>">
                <?php echo esc_html($license_info['icon'] . ' ' . $license_info['text']); ?>
            </span>
        </div>
        
        <div class="license-info">
            <!-- License Key Display -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--dark);">
                    <?php esc_html_e('License Key:', 'edd-customer-dashboard-pro'); ?>
                </label>
                <div class="license-key" onclick="copyToClipboard('<?php echo esc_js($license->license_key); ?>')">
                    <?php echo esc_html($license->license_key); ?>
                </div>
                <p style="font-size: 0.8rem; color: var(--gray); margin-top: 5px;">
                    <?php esc_html_e('Click to copy to clipboard', 'edd-customer-dashboard-pro'); ?>
                </p>
            </div>
            
            <!-- License Details -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div>
                    <strong><?php esc_html_e('Status:', 'edd-customer-dashboard-pro'); ?></strong><br>
                    <span style="color: var(--gray);"><?php echo esc_html(ucfirst($license->status)); ?></span>
                    <?php if ($expiration_info['expires_soon']) : ?>
                        <br><small style="color: #f56500;"><?php esc_html_e('Expires soon!', 'edd-customer-dashboard-pro'); ?></small>
                    <?php endif; ?>
                </div>
                <div>
                    <strong><?php esc_html_e('Expires:', 'edd-customer-dashboard-pro'); ?></strong><br>
                    <span style="color: var(--gray);">
                        <?php 
                        if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') {
                            echo esc_html(date_i18n(get_option('date_format'), strtotime($license->expiration)));
                            if ($expiration_info['days_until_expiry'] > 0) {
                                echo '<br><small>(' . esc_html(sprintf(_n('%d day left', '%d days left', $expiration_info['days_until_expiry'], 'edd-customer-dashboard-pro'), $expiration_info['days_until_expiry'])) . ')</small>';
                            }
                        } else {
                            esc_html_e('Never', 'edd-customer-dashboard-pro');
                        }
                        ?>
                    </span>
                </div>
            </div>
            
            <!-- Site Management -->
            <div class="site-management">
                <h4><?php esc_html_e('Manage Sites', 'edd-customer-dashboard-pro'); ?></h4>
                
                <?php if ($license_info['can_activate'] && !$activation_limit_reached) : ?>
                <!-- Add Site Form -->
                <form method="post" class="edd_sl_form" onsubmit="setLicenseTabFlag()">
                    <div class="site-input-group">
                        <input type="url" name="site_url" 
                               placeholder="<?php esc_attr_e('Enter your site URL (e.g., https://example.com)', 'edd-customer-dashboard-pro'); ?>"
                               value="https://" required>
                        <input type="submit" 
                               class="btn btn-success" 
                               value="<?php esc_attr_e('✅ Activate', 'edd-customer-dashboard-pro'); ?>">
                    </div>
                    <input type="hidden" name="license_id" value="<?php echo esc_attr($license->ID); ?>">
                    <input type="hidden" name="edd_action" value="insert_site">
                    <?php wp_nonce_field('edd_add_site_nonce', 'edd_add_site_nonce'); ?>
                </form>
                
                <?php elseif (!$license_info['can_activate']) : ?>
                <div style="background: rgba(245, 87, 108, 0.1); border: 1px solid rgba(245, 87, 108, 0.3); border-radius: 8px; padding: 15px; margin: 15px 0;">
                    <p style="color: #d32f2f; margin: 0; font-size: 0.9rem;">
                        <?php 
                        if ($license_info['text'] === esc_html__('Disabled', 'edd-customer-dashboard-pro')) {
                            echo '🚫 ' . esc_html__('This license has been disabled and cannot be used to activate sites.', 'edd-customer-dashboard-pro');
                        } elseif ($license_info['text'] === esc_html__('Expired', 'edd-customer-dashboard-pro')) {
                            echo '⏰ ' . esc_html__('This license has expired and cannot be used to activate new sites. Please renew your license.', 'edd-customer-dashboard-pro');
                        } else {
                            echo '❌ ' . esc_html__('This license cannot be used to activate sites at this time.', 'edd-customer-dashboard-pro');
                        }
                        ?>
                    </p>
                </div>
                
                <?php elseif ($activation_limit_reached) : ?>
                <div style="background: rgba(33, 150, 243, 0.1); border: 1px solid rgba(33, 150, 243, 0.3); border-radius: 8px; padding: 15px; margin: 15px 0;">
                    <p style="color: #1976d2; margin: 0; font-size: 0.9rem;">
                        🔒 <?php esc_html_e('Activation limit reached. Deactivate a site first or upgrade your license.', 'edd-customer-dashboard-pro'); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($active_sites)) : ?>
                <div style="margin-top: 15px;">
                    <h5 style="font-weight: 600; margin-bottom: 10px; color: var(--dark);">
                        <?php esc_html_e('Active Sites', 'edd-customer-dashboard-pro'); ?>
                    </h5>
                    <div style="display: grid; gap: 8px;">
                        <?php foreach ($active_sites as $site) : ?>
                        <div class="site-item">
                            <span style="flex: 1; word-break: break-all;"><?php echo esc_url($site->site_name); ?></span>
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
                            class="btn btn-secondary"
                            style="padding: 5px 10px; font-size: 0.8rem;">
                                🔓 <?php esc_html_e('Deactivate', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else : ?>
                <p style="color: var(--gray); font-style: italic; margin: 15px 0;">
                    <?php esc_html_e('No sites activated yet.', 'edd-customer-dashboard-pro'); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="order-actions">
            <?php if ($license_info['text'] === esc_html__('Expired', 'edd-customer-dashboard-pro') || $expiration_info['expires_soon']) : ?>
            <a href="<?php echo esc_url(edd_get_checkout_uri()); ?>?edd_action=purchase_renewal&license_id=<?php echo esc_attr($license->ID); ?>" 
               class="btn btn-warning">
                🔄 <?php esc_html_e('Renew License', 'edd-customer-dashboard-pro'); ?>
            </a>
            <?php endif; ?>
            
            <a href="<?php echo esc_url(get_permalink($license->download_id)); ?>" 
               class="btn btn-secondary">
                ⬆️ <?php esc_html_e('View Upgrades', 'edd-customer-dashboard-pro'); ?>
            </a>
            
            <button onclick="showSupportTab()" class="btn btn-secondary">
                💬 <?php esc_html_e('Get Support', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">🔑</div>
    <h3><?php esc_html_e('No Licenses Found', 'edd-customer-dashboard-pro'); ?></h3>
    <p><?php esc_html_e('You don\'t have any software licenses yet. Purchase a licensed product to get started!', 'edd-customer-dashboard-pro'); ?></p>
    <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" 
            class="btn">
        🛒 <?php esc_html_e('Browse Licensed Products', 'edd-customer-dashboard-pro'); ?>
    </button>
</div>
<?php endif; ?>

<!-- Deactivation Modal -->
<div id="deactivateModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div style="text-align: center;">
            <div style="width: 60px; height: 60px; margin: 0 auto 20px; background: rgba(245, 87, 108, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 1.5rem;">🔓</span>
            </div>
            <h3 style="color: var(--dark); margin-bottom: 10px;"><?php esc_html_e('Deactivate Site License', 'edd-customer-dashboard-pro'); ?></h3>
            <p style="color: var(--gray); margin-bottom: 20px;">
                <?php esc_html_e('Are you sure you want to deactivate the license for:', 'edd-customer-dashboard-pro'); ?>
                <br><strong id="modalSiteName" style="color: var(--dark);"></strong>
            </p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button onclick="closeDeactivateModal()" 
                        class="btn btn-secondary">
                    <?php esc_html_e('Cancel', 'edd-customer-dashboard-pro'); ?>
                </button>
                <button onclick="confirmDeactivation()" 
                        class="btn" style="background: linear-gradient(135deg, #f5576c, #d32f2f);">
                    <?php esc_html_e('🔓 Deactivate', 'edd-customer-dashboard-pro'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 30px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: modalAppear 0.3s ease;
}

@keyframes modalAppear {
    from { opacity: 0; transform: translateY(-20px) scale(0.9); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<script>
// Global variables for modal
let currentDeactivateUrl = '';
let currentSiteName = '';

// License management functions
function setLicenseTabFlag() {
    sessionStorage.setItem('returnToLicensesTab', 'true');
}

function showDeactivateModal(siteName, deactivateUrl) {
    currentSiteName = siteName;
    currentDeactivateUrl = deactivateUrl;
    
    document.getElementById('modalSiteName').textContent = siteName;
    document.getElementById('deactivateModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeactivateModal() {
    document.getElementById('deactivateModal').style.display = 'none';
    document.body.style.overflow = '';
}

function confirmDeactivation() {
    if (currentDeactivateUrl) {
        window.location.href = currentDeactivateUrl;
    }
}

// Support tab switcher
function showSupportTab() {
    const dashboardElement = document.querySelector('[x-data]');
    if (dashboardElement && dashboardElement._x_dataStack) {
        dashboardElement._x_dataStack[0].activeTab = 'support';
        window.location.hash = 'support';
    }
}

// Copy to clipboard function
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            // Visual feedback
            const event = new CustomEvent('licenseKeyCopied', { detail: { text } });
            document.dispatchEvent(event);
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
        const event = new CustomEvent('licenseKeyCopied', { detail: { text } });
        document.dispatchEvent(event);
    } catch (err) {
        console.error('Failed to copy license key');
    }
    
    document.body.removeChild(textArea);
}

// Check if we should return to licenses tab
document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('returnToLicensesTab') === 'true') {
        sessionStorage.removeItem('returnToLicensesTab');
        window.location.hash = 'licenses';
        
        setTimeout(function() {
            const dashboardElement = document.querySelector('[x-data]');
            if (dashboardElement && dashboardElement._x_dataStack && dashboardElement._x_dataStack[0]) {
                dashboardElement._x_dataStack[0].activeTab = 'licenses';
            }
        }, 100);
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeactivateModal();
    }
});

// Close modal on backdrop click
document.getElementById('deactivateModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeactivateModal();
    }
});
</script>