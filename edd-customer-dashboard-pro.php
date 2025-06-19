<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://theweblab.xyz/
 * Description: Modern, template-based dashboard interface for Easy Digital Downloads customers
 * Version: 1.0.0
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
define('EDDCDP_VERSION', '1.1.5');
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
        $this->maybe_replace_edd_shortcodes();
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
            'replace_edd_pages' => true
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
     * Maybe replace EDD shortcodes
     */
    private function maybe_replace_edd_shortcodes() {
        $settings = get_option('eddcdp_settings', array());
        
        if (isset($settings['replace_edd_pages']) && $settings['replace_edd_pages']) {
            // Remove EDD shortcodes
            remove_shortcode('purchase_history');
            remove_shortcode('edd_profile_editor');
            remove_shortcode('download_history');
            
            // Add our replacement shortcodes
            add_shortcode('purchase_history', array($this, 'render_dashboard'));
            add_shortcode('edd_profile_editor', array($this, 'render_dashboard'));
            add_shortcode('download_history', array($this, 'render_dashboard'));
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
        if (!is_user_logged_in() || !$this->template_loader) {
            return;
        }

        // Enqueue active template assets (no need to specify template name)
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
     * Render dashboard shortcode
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

        // Get settings with proper sanitization
        $settings = get_option('eddcdp_settings', array());
        $enabled_sections = isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

        // Prepare data for template
        $template_data = array(
            'user' => $user,
            'customer' => $customer,
            'settings' => $settings,
            'enabled_sections' => $enabled_sections,
            'dashboard_data' => $this->dashboard_data
        );

        // Apply filters to template data
        $template_data = apply_filters('eddcdp_template_data', $template_data);

        // Load template using active template (no need to specify template name)
        if ($this->template_loader) {
            do_action('eddcdp_dashboard_loaded', $template_data);
            return $this->template_loader->load_template(null, $template_data);
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