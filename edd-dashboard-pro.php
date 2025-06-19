<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://yourwebsite.com/
 * Description: Modern, clean dashboard interface for Easy Digital Downloads customers
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 * License: GPL v2 or later
 * Text Domain: edd-dashboard-pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EDD_DASHBOARD_PRO_VERSION', '1.0.0');
define('EDD_DASHBOARD_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDD_DASHBOARD_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));

class EDD_Dashboard_Pro {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Check if EDD is active
        if (!class_exists('Easy_Digital_Downloads')) {
            add_action('admin_notices', array($this, 'edd_missing_notice'));
            return;
        }
        
        // Replace EDD's account shortcode with our custom one
        remove_shortcode('purchase_history');
        remove_shortcode('edd_profile_editor');
        remove_shortcode('download_history');
        remove_shortcode('edd_login');
        remove_shortcode('edd_register');
        
        // Add our custom shortcode
        add_shortcode('edd_dashboard_pro', array($this, 'render_dashboard'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    public function edd_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and activated.', 'edd-dashboard-pro'); ?></p>
        </div>
        <?php
    }
    
    public function enqueue_assets() {
        if (is_user_logged_in()) {
            wp_enqueue_style('edd-dashboard-pro', EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/dashboard.css', array(), EDD_DASHBOARD_PRO_VERSION);
            wp_enqueue_script('edd-dashboard-pro', EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/dashboard.js', array('jquery'), EDD_DASHBOARD_PRO_VERSION, true);
        }
    }
    
    public function render_dashboard() {
        if (!is_user_logged_in()) {
            return edd_login_form();
        }
        
        $user = wp_get_current_user();
        $customer = edd_get_customer_by('user_id', $user->ID);
        
        if (!$customer) {
            return '<p>' . __('No customer data found.', 'edd-dashboard-pro') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="dashboard-container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <div class="welcome-text">
                        <h1><?php printf(__('Welcome back, %s!', 'edd-dashboard-pro'), esc_html($user->display_name)); ?></h1>
                        <p><?php _e('Manage your purchases, downloads, and account settings', 'edd-dashboard-pro'); ?></p>
                    </div>
                    <div class="user-avatar"><?php echo $this->get_user_initials($user->display_name); ?></div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon purchases">📦</div>
                    <div class="stat-number"><?php echo edd_count_purchases_of_customer($customer->id); ?></div>
                    <div class="stat-label"><?php _e('Total Purchases', 'edd-dashboard-pro'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon downloads">⬇️</div>
                    <div class="stat-number"><?php echo $this->get_download_count($customer->id); ?></div>
                    <div class="stat-label"><?php _e('Downloads', 'edd-dashboard-pro'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon licenses">🔑</div>
                    <div class="stat-number"><?php echo $this->get_active_licenses_count($customer->id); ?></div>
                    <div class="stat-label"><?php _e('Active Licenses', 'edd-dashboard-pro'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon wishlist">❤️</div>
                    <div class="stat-number"><?php echo $this->get_wishlist_count($user->ID); ?></div>
                    <div class="stat-label"><?php _e('Wishlist Items', 'edd-dashboard-pro'); ?></div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="dashboard-nav">
                <div class="nav-tabs">
                    <a href="#" class="nav-tab active" data-section="purchases">
                        📦 <?php _e('Purchases', 'edd-dashboard-pro'); ?>
                    </a>
                    <a href="#" class="nav-tab" data-section="downloads">
                        ⬇️ <?php _e('Downloads', 'edd-dashboard-pro'); ?>
                    </a>
                    <a href="#" class="nav-tab" data-section="licenses">
                        🔑 <?php _e('Licenses', 'edd-dashboard-pro'); ?>
                    </a>
                    <a href="#" class="nav-tab" data-section="wishlist">
                        ❤️ <?php _e('Wishlist', 'edd-dashboard-pro'); ?>
                    </a>
                    <a href="#" class="nav-tab" data-section="analytics">
                        📊 <?php _e('Analytics', 'edd-dashboard-pro'); ?>
                    </a>
                    <a href="#" class="nav-tab" data-section="support">
                        💬 <?php _e('Support', 'edd-dashboard-pro'); ?>
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Purchases Section -->
                <div class="content-section active" id="purchases">
                    <?php echo $this->render_purchases_section($customer); ?>
                </div>

                <!-- Downloads Section -->
                <div class="content-section" id="downloads">
                    <?php echo $this->render_downloads_section($customer); ?>
                </div>

                <!-- Licenses Section -->
                <div class="content-section" id="licenses">
                    <?php echo $this->render_licenses_section($customer); ?>
                </div>

                <!-- Wishlist Section -->
                <div class="content-section" id="wishlist">
                    <?php echo $this->render_wishlist_section($user->ID); ?>
                </div>

                <!-- Analytics Section -->
                <div class="content-section" id="analytics">
                    <?php echo $this->render_analytics_section($customer); ?>
                </div>

                <!-- Support Section -->
                <div class="content-section" id="support">
                    <?php echo $this->render_support_section(); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function get_user_initials($name) {
        $names = explode(' ', $name);
        $initials = '';
        foreach ($names as $n) {
            $initials .= strtoupper(substr($n, 0, 1));
        }
        return substr($initials, 0, 2);
    }
    
    private function get_download_count($customer_id) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
            'status' => 'complete',
            'number' => 9999
        ));
        
        $count = 0;
        foreach ($payments as $payment) {
            $downloads = edd_get_payment_meta_downloads($payment->ID);
            if ($downloads) {
                $count += count($downloads);
            }
        }
        return $count;
    }
    
    private function get_active_licenses_count($customer_id) {
        if (!class_exists('EDD_Software_Licensing')) {
            return 0;
        }
        
        $licensing = edd_software_licensing();
        if (!$licensing) {
            return 0;
        }
        
        $licenses = $licensing->get_license_keys_of_user(get_current_user_id());
        $active = 0;
        
        if ($licenses) {
            foreach ($licenses as $license) {
                if ($license->is_expired() === false) {
                    $active++;
                }
            }
        }
        
        return $active;
    }
    
    private function get_wishlist_count($user_id) {
        if (!class_exists('EDD_Wish_Lists')) {
            return 0;
        }
        
        $wish_lists = edd_wl_get_wish_lists(array('user_id' => $user_id));
        $count = 0;
        
        foreach ($wish_lists as $list) {
            $items = edd_wl_get_list_items($list->ID);
            $count += count($items);
        }
        
        return $count;
    }
    
    private function render_purchases_section($customer) {
        $payments = edd_get_payments(array(
            'customer' => $customer->id,
            'status' => array('complete', 'revoked'),
            'number' => 20
        ));
        
        ob_start();
        ?>
        <h2 class="section-title"><?php _e('Your Orders & Purchases', 'edd-dashboard-pro'); ?></h2>
        <div class="purchase-list">
            <?php if ($payments) : ?>
                <?php foreach ($payments as $payment) : ?>
                    <?php $downloads = edd_get_payment_meta_downloads($payment->ID); ?>
                    <div class="purchase-item">
                        <div class="purchase-header">
                            <div class="order-info">
                                <div class="product-name">
                                    <?php 
                                    if ($downloads && count($downloads) > 0) {
                                        $first_download = get_the_title($downloads[0]['id']);
                                        echo esc_html($first_download);
                                        if (count($downloads) > 1) {
                                            printf(__(' + %d more', 'edd-dashboard-pro'), count($downloads) - 1);
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="order-meta">
                                    <span class="order-number"><?php printf(__('Order #%s', 'edd-dashboard-pro'), $payment->number); ?></span>
                                    <span class="order-date"><?php echo date_i18n(get_option('date_format'), strtotime($payment->date)); ?></span>
                                    <span class="order-total"><?php echo edd_currency_filter(edd_format_amount($payment->total)); ?></span>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo esc_attr($payment->status); ?>"><?php echo edd_get_payment_status($payment, true); ?></span>
                        </div>
                        
                        <?php if ($downloads) : ?>
                            <div class="order-products">
                                <?php foreach ($downloads as $download) : ?>
                                    <div class="product-row">
                                        <div class="product-details">
                                            <strong><?php echo get_the_title($download['id']); ?></strong>
                                            <?php if (edd_use_skus() && edd_get_download_sku($download['id'])) : ?>
                                                <div class="product-meta"><?php printf(__('SKU: %s', 'edd-dashboard-pro'), edd_get_download_sku($download['id'])); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-actions">
                                            <?php if (edd_is_payment_complete($payment->ID) && edd_can_view_receipt($payment->key)) : ?>
                                                <?php 
                                                $download_files = edd_get_download_files($download['id']);
                                                if ($download_files) : ?>
                                                    <a href="<?php echo edd_get_download_file_url($payment->key, $payment->email, 0, $download['id']); ?>" class="btn btn-download">🔽 <?php _e('Download', 'edd-dashboard-pro'); ?></a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="order-actions">
                            <a href="<?php echo edd_get_success_page_uri('?payment_key=' . $payment->key); ?>" class="btn btn-secondary">📋 <?php _e('Order Details', 'edd-dashboard-pro'); ?></a>
                            <?php if (function_exists('edd_get_receipt_page_uri')) : ?>
                                <a href="<?php echo edd_get_receipt_page_uri($payment->ID); ?>" class="btn btn-secondary">📄 <?php _e('View Invoice', 'edd-dashboard-pro'); ?></a>
                            <?php endif; ?>
                            <?php if (class_exists('EDD_Software_Licensing')) : ?>
                                <a href="#" class="btn btn-secondary" onclick="document.querySelector('[data-section=licenses]').click(); return false;">🔑 <?php _e('Manage Licenses', 'edd-dashboard-pro'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <h3><?php _e('No purchases yet', 'edd-dashboard-pro'); ?></h3>
                    <p><?php _e('When you make your first purchase, it will appear here.', 'edd-dashboard-pro'); ?></p>
                    <a href="<?php echo edd_get_checkout_uri(); ?>" class="btn"><?php _e('Browse Products', 'edd-dashboard-pro'); ?></a>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_downloads_section($customer) {
        $payments = edd_get_payments(array(
            'customer' => $customer->id,
            'status' => 'complete',
            'number' => 9999
        ));
        
        ob_start();
        ?>
        <h2 class="section-title"><?php _e('Download History', 'edd-dashboard-pro'); ?></h2>
        <div class="purchase-list">
            <?php if ($payments) : ?>
                <?php foreach ($payments as $payment) : ?>
                    <?php $downloads = edd_get_payment_meta_downloads($payment->ID); ?>
                    <?php if ($downloads) : ?>
                        <?php foreach ($downloads as $download) : ?>
                            <div class="purchase-item">
                                <div class="purchase-header">
                                    <div class="product-name"><?php echo get_the_title($download['id']); ?></div>
                                    <div class="purchase-date"><?php printf(__('Purchased: %s', 'edd-dashboard-pro'), date_i18n(get_option('date_format'), strtotime($payment->date))); ?></div>
                                </div>
                                <div style="margin-top: 10px; color: #666;">
                                    <?php
                                    $download_limit = edd_get_file_download_limit($download['id']);
                                    if ($download_limit) {
                                        $downloads_remaining = edd_get_download_limit_remaining($download['id'], $payment->ID);
                                        printf(__('<strong>Downloads remaining:</strong> %d of %d', 'edd-dashboard-pro'), $downloads_remaining, $download_limit);
                                    } else {
                                        _e('<strong>Downloads remaining:</strong> Unlimited', 'edd-dashboard-pro');
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="empty-state">
                    <div class="empty-icon">⬇️</div>
                    <h3><?php _e('No downloads yet', 'edd-dashboard-pro'); ?></h3>
                    <p><?php _e('Your download history will appear here.', 'edd-dashboard-pro'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_licenses_section($customer) {
        if (!class_exists('EDD_Software_Licensing')) {
            return '<div class="empty-state"><div class="empty-icon">🔑</div><h3>' . __('Software Licensing not installed', 'edd-dashboard-pro') . '</h3><p>' . __('License management requires the Software Licensing add-on.', 'edd-dashboard-pro') . '</p></div>';
        }
        
        $licensing = edd_software_licensing();
        if (!$licensing) {
            return '<div class="empty-state"><div class="empty-icon">🔑</div><h3>' . __('Software Licensing not available', 'edd-dashboard-pro') . '</h3><p>' . __('License management is currently unavailable.', 'edd-dashboard-pro') . '</p></div>';
        }
        
        $licenses = $licensing->get_license_keys_of_user(get_current_user_id());
        
        ob_start();
        ?>
        <h2 class="section-title"><?php _e('License Management', 'edd-dashboard-pro'); ?></h2>
        <div class="purchase-list">
            <?php if ($licenses) : ?>
                <?php foreach ($licenses as $license) : ?>
                    <div class="purchase-item">
                        <div class="purchase-header">
                            <div class="product-name"><?php echo get_the_title($license->download_id); ?></div>
                            <span class="status-badge status-<?php echo $license->is_expired() ? 'expired' : 'active'; ?>">
                                <?php echo $license->is_expired() ? __('Expired', 'edd-dashboard-pro') : __('Active', 'edd-dashboard-pro'); ?>
                            </span>
                        </div>
                        <div class="license-info">
                            <div class="license-key"><?php echo $license->key; ?></div>
                            <div style="margin-top: 15px;">
                                <strong><?php _e('Purchase Date:', 'edd-dashboard-pro'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($license->date_created)); ?><br>
                                <strong><?php _e('Expires:', 'edd-dashboard-pro'); ?></strong> <?php echo $license->expiration ? date_i18n(get_option('date_format'), strtotime($license->expiration)) : __('Never', 'edd-dashboard-pro'); ?><br>
                                <strong><?php _e('Activations:', 'edd-dashboard-pro'); ?></strong> <?php echo $license->activation_count; ?> <?php _e('of', 'edd-dashboard-pro'); ?> <?php echo $license->activation_limit ? $license->activation_limit : __('Unlimited', 'edd-dashboard-pro'); ?><br>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <?php if ($license->is_expired()) : ?>
                                    <a href="<?php echo edd_get_checkout_uri(array('edd_license_key' => $license->key, 'download_id' => $license->download_id)); ?>" class="btn btn-warning">🔄 <?php _e('Renew License', 'edd-dashboard-pro'); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="empty-state">
                    <div class="empty-icon">🔑</div>
                    <h3><?php _e('No licenses yet', 'edd-dashboard-pro'); ?></h3>
                    <p><?php _e('Your software licenses will appear here.', 'edd-dashboard-pro'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_wishlist_section($user_id) {
        if (!class_exists('EDD_Wish_Lists')) {
            return '<div class="empty-state"><div class="empty-icon">❤️</div><h3>' . __('Wishlist not available', 'edd-dashboard-pro') . '</h3><p>' . __('Wishlist functionality requires the Wish Lists add-on.', 'edd-dashboard-pro') . '</p></div>';
        }
        
        // Implementation for wishlist would go here
        return '<div class="empty-state"><div class="empty-icon">❤️</div><h3>' . __('Your Wishlist', 'edd-dashboard-pro') . '</h3><p>' . __('Save items for later purchase.', 'edd-dashboard-pro') . '</p></div>';
    }
    
    private function render_analytics_section($customer) {
        $total_spent = $customer->purchase_value;
        $purchase_count = edd_count_purchases_of_customer($customer->id);
        $avg_per_order = $purchase_count > 0 ? $total_spent / $purchase_count : 0;
        
        ob_start();
        ?>
        <h2 class="section-title"><?php _e('Purchase Analytics', 'edd-dashboard-pro'); ?></h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon purchases">💰</div>
                <div class="stat-number"><?php echo edd_currency_filter(edd_format_amount($total_spent)); ?></div>
                <div class="stat-label"><?php _e('Total Spent', 'edd-dashboard-pro'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon downloads">📈</div>
                <div class="stat-number"><?php echo edd_currency_filter(edd_format_amount($avg_per_order)); ?></div>
                <div class="stat-label"><?php _e('Average Order Value', 'edd-dashboard-pro'); ?></div>
            </div>
        </div>
        <div style="margin-top: 30px; padding: 40px; background: rgba(248, 250, 252, 0.8); border-radius: 12px; text-align: center;">
            <h3>📊 <?php _e('Advanced Analytics Coming Soon', 'edd-dashboard-pro'); ?></h3>
            <p><?php _e('Detailed charts and insights about your purchase history', 'edd-dashboard-pro'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_support_section() {
        ob_start();
        ?>
        <h2 class="section-title"><?php _e('Support Center', 'edd-dashboard-pro'); ?></h2>
        <div class="empty-state">
            <div class="empty-icon">💬</div>
            <h3><?php _e('Need Help?', 'edd-dashboard-pro'); ?></h3>
            <p><?php _e('Contact our support team for assistance with your purchases', 'edd-dashboard-pro'); ?></p>
            <?php if (defined('EDD_SUPPORT_URL')) : ?>
                <a href="<?php echo esc_url(EDD_SUPPORT_URL); ?>" class="btn">📝 <?php _e('Create Support Ticket', 'edd-dashboard-pro'); ?></a>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/support')); ?>" class="btn">📝 <?php _e('Contact Support', 'edd-dashboard-pro'); ?></a>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the plugin
EDD_Dashboard_Pro::get_instance();