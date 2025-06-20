<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://theweblab.xyz/
 * Description: Modern, template-based dashboard interface for Easy Digital Downloads customers
 * Version: 1.0.2
 * Author: TheWebLab
 * Author URI: https://theweblab.xyz/
 * License: GPL v2 or later
 * Text Domain: edd-customer-dashboard-pro
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EDDCDP_VERSION', '1.0.3');
define('EDDCDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDDCDP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDDCDP_PLUGIN_FILE', __FILE__);
define('EDDCDP_TEXT_DOMAIN', 'edd-customer-dashboard-pro');

/**
 * Main Plugin Class
 */
class EDD_Customer_Dashboard_Pro {
    
    private static $instance = null;
    public $template_loader;
    public $admin_settings;
    public $dashboard_data;
    private $fullscreen_mode = false;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->define_hooks();
    }
    
    /**
     * Define WordPress hooks
     */
    private function define_hooks() {
        register_activation_hook(__FILE__, array($this, 'activation'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation'));
        
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // ENHANCED: Full screen mode detection
        add_action('template_redirect', array($this, 'maybe_load_fullscreen'));
        
        // Security nonce verification for form processing
        add_action('wp_ajax_eddcdp_activate_license', array($this, 'verify_nonce_and_process'));
        add_action('wp_ajax_eddcdp_deactivate_license', array($this, 'verify_nonce_and_process'));
        add_action('wp_ajax_eddcdp_remove_wishlist', array($this, 'verify_nonce_and_process'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if EDD is active
        if (!$this->is_edd_active()) {
            add_action('admin_notices', array($this, 'edd_missing_notice'));
            return;
        }
        
        $this->include_files();
        $this->init_components();
        $this->setup_shortcodes();
    }
    
    /**
     * Check if EDD is active
     */
    private function is_edd_active() {
        return class_exists('Easy_Digital_Downloads');
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        $files = array(
            'includes/class-template-loader.php',
            'includes/class-admin-settings.php',
            'includes/class-dashboard-data.php'
        );
        
        foreach ($files as $file) {
            $file_path = EDDCDP_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        if (class_exists('EDDCDP_Template_Loader')) {
            $this->template_loader = new EDDCDP_Template_Loader();
        }
        
        if (class_exists('EDDCDP_Admin_Settings')) {
            $this->admin_settings = new EDDCDP_Admin_Settings();
        }
        
        if (class_exists('EDDCDP_Dashboard_Data')) {
            $this->dashboard_data = new EDDCDP_Dashboard_Data();
        }
    }
    
    /**
     * Setup shortcodes
     */
    private function setup_shortcodes() {
        // Add our custom shortcode
        add_shortcode('edd_customer_dashboard_pro', array($this, 'render_dashboard'));
        
        // Replace EDD's default shortcodes with our custom one if enabled
        // Use higher priority to ensure we override after EDD loads
        add_action('wp', array($this, 'maybe_replace_edd_shortcodes'), 15);
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            EDDCDP_TEXT_DOMAIN,
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Plugin activation
     */
    public function activation() {
        // Set default options
        $default_options = array(
            'active_template' => 'default',
            'enabled_sections' => array(
                'purchases' => true,
                'downloads' => true,
                'licenses' => true,
                'wishlist' => true,
                'analytics' => true,
                'support' => true
            ),
            'replace_edd_pages' => true,
            'fullscreen_mode' => false // NEW: Full screen mode setting
        );
        
        add_option('eddcdp_settings', $default_options);
        
        // Create necessary database tables if needed
        $this->create_plugin_tables();
        
        // Set activation flag
        add_option('eddcdp_activated', true);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivation() {
        // Clear any cached data
        wp_cache_flush();
        
        // Remove activation flag
        delete_option('eddcdp_activated');
    }
    
    /**
     * Create plugin tables (if needed)
     */
    private function create_plugin_tables() {
        // Currently no custom tables needed
        // This is a placeholder for future functionality
    }
    
/**
 * ENHANCED: Auto Full Screen Mode - Load full screen by default when enabled
 */
public function maybe_load_fullscreen() {
    // Only process if user is logged in
    if (!is_user_logged_in()) {
        return;
    }
    
    $is_fullscreen_request = false;
    
    // Check if we're on an EDD dashboard page
    global $post;
    
    // Method 1: Check if current page has EDD dashboard shortcodes
    if ($post && (
        has_shortcode($post->post_content, 'edd_customer_dashboard_pro') ||
        has_shortcode($post->post_content, 'purchase_history') ||
        has_shortcode($post->post_content, 'download_history') ||
        has_shortcode($post->post_content, 'edd_profile_editor') ||
        has_shortcode($post->post_content, 'edd_receipt')
    )) {
        $settings = get_option('eddcdp_settings', array());
        
        // If full screen mode is enabled in admin, automatically use full screen
        if (isset($settings['fullscreen_mode']) && $settings['fullscreen_mode']) {
            $is_fullscreen_request = true;
        }
    }
    
    // Method 2: Check if we're on known EDD pages by URL pattern
    $current_url = $_SERVER['REQUEST_URI'] ?? '';
    $edd_patterns = array(
        'order-history',
        'purchase-history', 
        'checkout-2/order-history',
        'account/orders',
        'my-account/orders',
        'customer/dashboard',
        'member/dashboard'
    );
    
    foreach ($edd_patterns as $pattern) {
        if (strpos($current_url, $pattern) !== false) {
            $settings = get_option('eddcdp_settings', array());
            if (isset($settings['fullscreen_mode']) && $settings['fullscreen_mode']) {
                $is_fullscreen_request = true;
                break;
            }
        }
    }
    
    // Method 3: Check for payment_key parameter (receipt pages)
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['payment_key']) || isset($_GET['purchase_key'])) {
        $settings = get_option('eddcdp_settings', array());
        if (isset($settings['fullscreen_mode']) && $settings['fullscreen_mode']) {
            $is_fullscreen_request = true;
        }
    }
    
    // Override: Allow manual exit from full screen with parameter
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['eddcdp_exit_fullscreen']) && $_GET['eddcdp_exit_fullscreen'] === '1') {
        $is_fullscreen_request = false;
    }
    
    // Override: Allow manual force full screen (for testing)
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['eddcdp_fullscreen']) && $_GET['eddcdp_fullscreen'] === '1') {
        $is_fullscreen_request = true;
    }
    
    if ($is_fullscreen_request) {
        $this->fullscreen_mode = true;
        $this->load_fullscreen_template();
    }
}
    
    /**
     * ENHANCED: Load full screen template
     */
    private function load_fullscreen_template() {
        // Prevent any caching
        nocache_headers();
        
        // Get current user and customer
        $user = wp_get_current_user();
        $customer = edd_get_customer_by('user_id', $user->ID);

        if (!$customer) {
            wp_die(esc_html__('No customer data found.', 'edd-customer-dashboard-pro'));
        }

        // Check if this is a receipt view
        $payment_key = '';
        $payment = null;
        $view_mode = 'dashboard';
        
        // Check URL parameters for payment key
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['payment_key'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $payment_key = sanitize_text_field(wp_unslash($_GET['payment_key']));
        } elseif (isset($_GET['purchase_key'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $payment_key = sanitize_text_field(wp_unslash($_GET['purchase_key']));
        }
        
        if (!empty($payment_key)) {
            $payment = edd_get_payment_by('key', $payment_key);
            
            if ($payment && edd_can_view_receipt($payment_key)) {
                $view_mode = 'receipt';
            }
        }

        // Get settings
        $settings = get_option('eddcdp_settings', array());
        $enabled_sections = isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

        // Prepare data for template
        $template_data = array(
            'user' => $user,
            'customer' => $customer,
            'payment' => $payment,
            'payment_key' => $payment_key,
            'settings' => $settings,
            'enabled_sections' => $enabled_sections,
            'dashboard_data' => $this->dashboard_data,
            'view_mode' => $view_mode,
            'fullscreen_mode' => true
        );

        // Apply filters to template data
        $template_data = apply_filters('eddcdp_fullscreen_template_data', $template_data);

        // Output the full screen template
        $this->render_fullscreen_template($template_data);
        exit;
    }
    
/**
 * ENHANCED: Render full screen template with smart back navigation
 */
private function render_fullscreen_template($template_data) {
    // Extract data for template
    extract($template_data);
    
    // Get template URL for assets
    $template_url = $this->template_loader ? $this->template_loader->get_template_url() : '';
    
    // ENHANCED: Smart back URL detection
    $back_url = home_url(); // Default to homepage
    
    // Check for HTTP referrer
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referrer = $_SERVER['HTTP_REFERER'];
        $site_url = home_url();
        
        // Only use referrer if it's from the same site and not a dashboard page
        if (strpos($referrer, $site_url) === 0) {
            $referrer_path = str_replace($site_url, '', $referrer);
            
            // Don't go back to another dashboard page
            $dashboard_patterns = array('order-history', 'purchase-history', 'payment_key=');
            $is_dashboard_referrer = false;
            
            foreach ($dashboard_patterns as $pattern) {
                if (strpos($referrer_path, $pattern) !== false) {
                    $is_dashboard_referrer = true;
                    break;
                }
            }
            
            if (!$is_dashboard_referrer) {
                $back_url = $referrer;
            }
        }
    }
    
    // Add exit parameter to ensure we don't loop back to full screen
    $back_url = add_query_arg('eddcdp_exit_fullscreen', '1', $back_url);
    
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php 
        if ($view_mode === 'receipt' && isset($payment)) {
            // translators: %s is the order number
            printf(esc_html__('Order #%s - %s', 'edd-customer-dashboard-pro'), esc_html($payment->number), get_bloginfo('name'));
        } else {
            // translators: %s is the site name
            printf(esc_html__('Customer Dashboard - %s', 'edd-customer-dashboard-pro'), get_bloginfo('name'));
        }
        ?></title>
        
        <?php if ($template_url) : ?>
            <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css?v=' . EDDCDP_VERSION); ?>">
        <?php endif; ?>
        
        <!-- Full Screen Specific Styles -->
        <style>
            body {
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                overflow-x: hidden;
            }
            
            .eddcdp-fullscreen-wrapper {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }
            
            .eddcdp-fullscreen-header {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                padding: 15px 30px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: sticky;
                top: 0;
                z-index: 1000;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .eddcdp-fullscreen-title {
                font-size: 1.5rem;
                font-weight: 700;
                background: linear-gradient(135deg, #667eea, #764ba2);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin: 0;
            }
            
            .eddcdp-fullscreen-actions {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            
            .eddcdp-back-to-site {
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                border: none;
                border-radius: 8px;
                padding: 10px 20px;
                cursor: pointer;
                font-size: 0.9rem;
                font-weight: 600;
                transition: all 0.3s ease;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            
            .eddcdp-back-to-site:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                color: white;
                text-decoration: none;
            }
            
            .eddcdp-fullscreen-content {
                flex: 1;
                padding: 0;
            }
            
            .eddcdp-dashboard-container {
                max-width: none;
                margin: 0;
                padding: 20px;
                background: transparent;
                min-height: calc(100vh - 80px);
            }
            
            /* Remove any floating buttons in full screen mode */
            .eddcdp-fullscreen-toggle {
                display: none !important;
            }
            
            /* Mobile adjustments */
            @media (max-width: 768px) {
                .eddcdp-fullscreen-header {
                    padding: 10px 15px;
                    flex-direction: column;
                    gap: 10px;
                    align-items: flex-start;
                }
                
                .eddcdp-fullscreen-actions {
                    width: 100%;
                    justify-content: flex-end;
                }
                
                .eddcdp-fullscreen-title {
                    font-size: 1.2rem;
                }
                
                .eddcdp-dashboard-container {
                    padding: 15px;
                    min-height: calc(100vh - 120px);
                }
            }
            
            /* Print styles for full screen */
            @media print {
                .eddcdp-fullscreen-header {
                    display: none !important;
                }
                
                .eddcdp-fullscreen-content {
                    padding: 0 !important;
                }
                
                body {
                    background: white !important;
                }
                
                .eddcdp-dashboard-container {
                    background: white !important;
                    padding: 0 !important;
                    min-height: auto !important;
                }
            }
        </style>
        
        <?php wp_head(); ?>
    </head>
    <body <?php body_class('eddcdp-fullscreen-mode'); ?>>
        
        <div class="eddcdp-fullscreen-wrapper">
            <!-- Full Screen Header -->
            <div class="eddcdp-fullscreen-header">
                <h1 class="eddcdp-fullscreen-title">
                    <?php 
                    if ($view_mode === 'receipt' && isset($payment)) {
                        // translators: %s is the order number
                        printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($payment->number));
                    } else {
                        esc_html_e('Customer Dashboard', 'edd-customer-dashboard-pro');
                    }
                    ?>
                </h1>
                
                <div class="eddcdp-fullscreen-actions">
                    <a href="<?php echo esc_url($back_url); ?>" class="eddcdp-back-to-site">
                        ← <?php esc_html_e('Back to Site', 'edd-customer-dashboard-pro'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Full Screen Content -->
            <div class="eddcdp-fullscreen-content">
                <?php
                // Load the dashboard template
                if ($this->template_loader) {
                    do_action('eddcdp_fullscreen_dashboard_loaded', $template_data);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                    echo $this->template_loader->load_template(null, $template_data);
                } else {
                    echo '<p>' . esc_html__('Dashboard template not available.', 'edd-customer-dashboard-pro') . '</p>';
                }
                ?>
            </div>
        </div>
        
        <?php if ($template_url) : ?>
            <script src="<?php echo esc_url($template_url . 'script.js?v=' . EDDCDP_VERSION); ?>"></script>
        <?php endif; ?>
        
        <!-- Full Screen Specific JavaScript -->
        <script>
            // Handle Escape key to go back to site
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    window.location.href = '<?php echo esc_js($back_url); ?>';
                }
            });
            
            // Ensure all dashboard links stay in normal mode when going back
            document.addEventListener('DOMContentLoaded', function() {
                // Update any "Back to Dashboard" links to go to normal site
                const backLinks = document.querySelectorAll('a[href*="order-history"], a[href*="purchase-history"]');
                backLinks.forEach(function(link) {
                    const linkUrl = new URL(link.href, window.location.origin);
                    linkUrl.searchParams.set('eddcdp_exit_fullscreen', '1');
                    link.href = linkUrl.toString();
                });
            });
        </script>
        
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
}
    
    /**
     * Maybe replace EDD shortcodes - ENHANCED with proper priority
     */
    public function maybe_replace_edd_shortcodes() {
        $settings = get_option('eddcdp_settings', array());
        
        if (isset($settings['replace_edd_pages']) && $settings['replace_edd_pages']) {
            // Store original shortcode functions for fallback
            global $shortcode_tags;
            
            // Remove EDD shortcodes and store originals
            $original_shortcodes = array();
            $edd_shortcodes = array('purchase_history', 'edd_profile_editor', 'download_history', 'edd_receipt');
            
            foreach ($edd_shortcodes as $shortcode) {
                if (isset($shortcode_tags[$shortcode])) {
                    $original_shortcodes[$shortcode] = $shortcode_tags[$shortcode];
                    remove_shortcode($shortcode);
                }
            }
            
            // Add our replacement shortcodes
            add_shortcode('purchase_history', array($this, 'render_dashboard'));
            add_shortcode('edd_profile_editor', array($this, 'render_dashboard'));
            add_shortcode('download_history', array($this, 'render_dashboard'));
            add_shortcode('edd_receipt', array($this, 'render_receipt'));
            
            // Store originals for potential fallback
            if (!empty($original_shortcodes)) {
                update_option('eddcdp_original_shortcodes', $original_shortcodes);
            }
        }
    }
    
    /**
     * Show EDD missing notice
     */
    public function edd_missing_notice() {
        $message = esc_html__('EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and activated.', 'edd-customer-dashboard-pro');
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if (!is_user_logged_in() || !$this->template_loader || $this->fullscreen_mode) {
            return;
        }

        // Enqueue active template assets
        $this->template_loader->enqueue_template_assets();
    }
    
    /**
     * Global nonce verification function
     */
    public function verify_nonce_and_process() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eddcdp_nonce')) {
            wp_send_json_error(esc_html__('Security verification failed.', 'edd-customer-dashboard-pro'));
            return;
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please log in first.', 'edd-customer-dashboard-pro'));
            return;
        }
        
        // Process the actual AJAX action
        $action = isset($_POST['action']) ? sanitize_text_field(wp_unslash($_POST['action'])) : '';
        
        if ($this->dashboard_data && method_exists($this->dashboard_data, $action)) {
            call_user_func(array($this->dashboard_data, $action));
        } else {
            wp_send_json_error(esc_html__('Invalid action.', 'edd-customer-dashboard-pro'));
        }
    }
    
    /**
     * ENHANCED: Render receipt within dashboard template
     */
    public function render_receipt($atts = array()) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            if (function_exists('edd_login_form')) {
                return edd_login_form();
            }
            return '<p>' . esc_html__('Please log in to view your receipt.', 'edd-customer-dashboard-pro') . '</p>';
        }

        // Get payment key from URL or shortcode attributes
        $payment_key = '';
        
        // First try to get from shortcode attributes
        if (isset($atts['payment_key'])) {
            $payment_key = sanitize_text_field($atts['payment_key']);
        }
        
        // Then try to get from URL parameters  
        if (empty($payment_key)) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $payment_key = isset($_GET['payment_key']) ? sanitize_text_field(wp_unslash($_GET['payment_key'])) : '';
        }
        
        // Also check for purchase_key (EDD sometimes uses this)
        if (empty($payment_key)) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $payment_key = isset($_GET['purchase_key']) ? sanitize_text_field(wp_unslash($_GET['purchase_key'])) : '';
        }
        
        if (empty($payment_key)) {
            return '<p>' . esc_html__('Invalid payment key.', 'edd-customer-dashboard-pro') . '</p>';
        }
        
        // Get payment details using EDD function
        $payment = edd_get_payment_by('key', $payment_key);
        
        if (!$payment) {
            return '<p>' . esc_html__('Payment not found.', 'edd-customer-dashboard-pro') . '</p>';
        }
        
        // Verify user can view this receipt
        if (!edd_can_view_receipt($payment_key)) {
            return '<p>' . esc_html__('Access denied. You do not have permission to view this receipt.', 'edd-customer-dashboard-pro') . '</p>';
        }

        // Get current user and customer
        $user = wp_get_current_user();
        $customer = edd_get_customer_by('user_id', $user->ID);

        if (!$customer) {
            return '<p>' . esc_html__('No customer data found.', 'edd-customer-dashboard-pro') . '</p>';
        }

        // Get settings
        $settings = get_option('eddcdp_settings', array());
        $enabled_sections = isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

        // Prepare data for template
        $template_data = array(
            'user' => $user,
            'customer' => $customer,
            'payment' => $payment,
            'payment_key' => $payment_key,
            'settings' => $settings,
            'enabled_sections' => $enabled_sections,
            'dashboard_data' => $this->dashboard_data,
            'view_mode' => 'receipt'
        );

        // Apply filters to template data
        $template_data = apply_filters('eddcdp_receipt_template_data', $template_data);

        // Load template with receipt view
        if ($this->template_loader) {
            do_action('eddcdp_receipt_loaded', $template_data);
            return $this->template_loader->load_template(null, $template_data);
        }

        return '<p>' . esc_html__('Receipt template not available.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    /**
     * ENHANCED: Render dashboard shortcode with full screen support
     */
    public function render_dashboard($atts = array()) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            if (function_exists('edd_login_form')) {
                return edd_login_form();
            }
            return '<p>' . esc_html__('Please log in to view your dashboard.', 'edd-customer-dashboard-pro') . '</p>';
        }

        // Get current user and customer
        $user = wp_get_current_user();
        $customer = edd_get_customer_by('user_id', $user->ID);

        if (!$customer) {
            return '<p>' . esc_html__('No customer data found.', 'edd-customer-dashboard-pro') . '</p>';
        }

        // Check if this is a receipt view
        $payment_key = '';
        $payment = null;
        $view_mode = 'dashboard';
        
        // Check URL parameters for payment key
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['payment_key'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $payment_key = sanitize_text_field(wp_unslash($_GET['payment_key']));
        } elseif (isset($_GET['purchase_key'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $payment_key = sanitize_text_field(wp_unslash($_GET['purchase_key']));
        }
        
        if (!empty($payment_key)) {
            $payment = edd_get_payment_by('key', $payment_key);
            
            if ($payment && edd_can_view_receipt($payment_key)) {
                $view_mode = 'receipt';
            }
        }

        // Get settings
        $settings = get_option('eddcdp_settings', array());
        $enabled_sections = isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

        // ENHANCED: Add full screen button if enabled
        $fullscreen_button = '';
        if (isset($settings['fullscreen_mode']) && $settings['fullscreen_mode']) {
            $fullscreen_url = add_query_arg('eddcdp_view', 'fullscreen');
            
            // Preserve current parameters
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['payment_key'])) {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $fullscreen_url = add_query_arg('payment_key', sanitize_text_field(wp_unslash($_GET['payment_key'])), $fullscreen_url);
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['view']) && $_GET['view'] === 'invoice') {
                $fullscreen_url = add_query_arg('view', 'invoice', $fullscreen_url);
            }
            
            $fullscreen_button = '<div class="eddcdp-fullscreen-toggle" style="position: fixed; top: 20px; right: 20px; z-index: 999;">
                <a href="' . esc_url($fullscreen_url) . '" class="eddcdp-btn eddcdp-btn-primary" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);">
                    🔍 ' . esc_html__('Full Screen', 'edd-customer-dashboard-pro') . '
                </a>
            </div>';
        }

        // Prepare data for template
        $template_data = array(
            'user' => $user,
            'customer' => $customer,
            'payment' => $payment,
            'payment_key' => $payment_key,
            'settings' => $settings,
            'enabled_sections' => $enabled_sections,
            'dashboard_data' => $this->dashboard_data,
            'view_mode' => $view_mode,
            'fullscreen_button' => $fullscreen_button
        );

        // Apply filters to template data
        $template_data = apply_filters('eddcdp_template_data', $template_data);

        // Load template
        if ($this->template_loader) {
            do_action('eddcdp_dashboard_loaded', $template_data);
            return $fullscreen_button . $this->template_loader->load_template(null, $template_data);
        }

        return '<p>' . esc_html__('Dashboard template not available.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    /**
     * Get template loader instance
     */
    public function get_template_loader() {
        return $this->template_loader;
    }
    
    /**
     * Get admin settings instance
     */
    public function get_admin_settings() {
        return $this->admin_settings;
    }
    
    /**
     * Get dashboard data instance
     */
    public function get_dashboard_data() {
        return $this->dashboard_data;
    }
    
    /**
     * Check if a specific EDD add-on is active
     */
    public function is_edd_addon_active($addon) {
        $active_addons = array(
            'licensing' => class_exists('EDD_Software_Licensing'),
            'wishlists' => class_exists('EDD_Wish_Lists'),
            'reviews' => class_exists('EDD_Reviews'),
            'recurring' => class_exists('EDD_Recurring'),
            'commissions' => class_exists('EDDC'),
        );
        
        return isset($active_addons[$addon]) ? $active_addons[$addon] : false;
    }
    
    /**
     * Get plugin version
     */
    public function get_version() {
        return EDDCDP_VERSION;
    }
}

/**
 * Initialize the plugin
 */
function eddcdp_init() {
    return EDD_Customer_Dashboard_Pro::get_instance();
}

/**
 * Global access function
 */
function eddcdp() {
    return eddcdp_init();
}

/**
 * Start the plugin
 */
eddcdp_init();

/**
 * Helper function to check if plugin is active
 */
function eddcdp_is_active() {
    return get_option('eddcdp_activated', false);
}

/**
 * Helper function to get plugin settings
 */
function eddcdp_get_settings() {
    return get_option('eddcdp_settings', array());
}

/**
 * Helper function to get enabled sections
 */
function eddcdp_get_enabled_sections() {
    $settings = eddcdp_get_settings();
    return isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) ? $settings['enabled_sections'] : array();
}