<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://theweblab.xyz/
 * Description: Modern, template-based dashboard interface for Easy Digital Downloads customers
 * Version: 1.0.2
 * Author: TheWebLab
 * Author URI: https://theweblab.xyz/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
define('EDDCDP_VERSION', '1.3.3');
define('EDDCDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDDCDP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDDCDP_PLUGIN_FILE', __FILE__);
define('EDDCDP_TEXT_DOMAIN', 'edd-customer-dashboard-pro');

/**
 * Main Plugin Class - Corrected File Structure
 */
class EDD_Customer_Dashboard_Pro {
    
    private static $instance = null;
    private $components = array();
    
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
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'), 5);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('template_redirect', array($this, 'handle_fullscreen_mode'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        if (!$this->is_edd_active()) {
            add_action('admin_notices', array($this, 'show_edd_missing_notice'));
            return;
        }
        
        $this->load_components();
        $this->setup_shortcodes();
    }
    
    /**
     * Load components with corrected file paths
     */
    private function load_components() {
        // Load core interfaces first
        $this->load_core_files();
        
        // Load components in correct order
        $this->load_legacy_components();
        $this->load_new_components();
    }
    
    /**
     * Load core files
     */
    private function load_core_files() {
        $core_files = array(
            'includes/core/interfaces/interface-component.php',
            'includes/core/interfaces/interface-data-provider.php'
        );
        
        foreach ($core_files as $file) {
            $this->safe_require($file);
        }
    }
    
    /**
     * Load legacy components (current working files)
     */
    private function load_legacy_components() {
        // First, try legacy locations (your current working files)
        $legacy_components = array(
            'template_loader' => 'includes/class-template-loader.php',
            'dashboard_data' => 'includes/class-dashboard-data.php',
            'admin_settings' => 'includes/class-admin-settings.php'
        );
        
        foreach ($legacy_components as $key => $file) {
            if ($this->safe_require($file)) {
                $class_name = 'EDDCDP_' . ucfirst(str_replace('_', '_', ucwords($key, '_')));
                $this->safe_instantiate($key, $class_name);
            }
        }
        
        // Load fullscreen manager - try both locations
        $this->load_fullscreen_manager();
    }
    
    /**
     * Load fullscreen manager from either location
     */
    private function load_fullscreen_manager() {
        $fullscreen_locations = array(
            'includes/class-fullscreen-manager.php',           // Legacy location
            'includes/frontend/class-fullscreen-manager.php'   // New location
        );
        
        foreach ($fullscreen_locations as $file) {
            if ($this->safe_require($file)) {
                // Try to instantiate with dependencies
                if (class_exists('EDDCDP_Fullscreen_Manager')) {
                    try {
                        $template_loader = isset($this->components['template_loader']) ? $this->components['template_loader'] : null;
                        $dashboard_data = isset($this->components['dashboard_data']) ? $this->components['dashboard_data'] : null;
                        
                        $this->components['fullscreen_manager'] = new EDDCDP_Fullscreen_Manager($template_loader, $dashboard_data);
                    } catch (Exception $e) {
                        error_log('EDDCDP: Error loading fullscreen manager: ' . $e->getMessage());
                    }
                }
                break; // Stop after first successful load
            }
        }
    }
    
    /**
     * Load new components (new architecture)
     */
    private function load_new_components() {
        // Load integrations first (other components depend on them)
        $this->load_integration_components();
        
        // Load data components
        $this->load_data_components();
        
        // Load frontend components
        $this->load_frontend_components();
        
        // Load utility components
        $this->load_utility_components();
    }
    
    /**
     * Load integration components
     */
    private function load_integration_components() {
        // Load sub-integrations first
        $this->safe_require('includes/integrations/class-licensing-integration.php');
        $this->safe_require('includes/integrations/class-wishlist-integration.php');
        
        // Load main EDD integration
        if ($this->safe_require('includes/integrations/class-edd-integration.php')) {
            $this->safe_instantiate_and_init('edd_integration', 'EDDCDP_Edd_Integration');
        }
    }
    
    /**
     * Load data components
     */
    private function load_data_components() {
        $data_components = array(
            'customer_data' => 'includes/data/class-customer-data.php'
        );
        
        foreach ($data_components as $key => $file) {
            if ($this->safe_require($file)) {
                $class_name = 'EDDCDP_' . ucfirst(str_replace('_', '_', ucwords($key, '_')));
                $this->safe_instantiate_and_init($key, $class_name);
            }
        }
    }
    
    /**
     * Load frontend components
     */
    private function load_frontend_components() {
        if (is_admin()) {
            return; // Skip frontend components in admin
        }
        
        $frontend_components = array(
            'asset_manager' => 'includes/frontend/class-asset-manager.php',
            'template_manager' => 'includes/frontend/class-template-manager.php',
            'shortcode_handler' => 'includes/frontend/class-shortcode-handler.php'
        );
        
        foreach ($frontend_components as $key => $file) {
            if ($this->safe_require($file)) {
                $class_name = 'EDDCDP_' . ucfirst(str_replace('_', '_', ucwords($key, '_')));
                $this->safe_instantiate_and_init($key, $class_name);
            }
        }
        
        // Setup dependencies for frontend components
        $this->setup_frontend_dependencies();
    }
    
    /**
     * Setup dependencies for frontend components
     */
    private function setup_frontend_dependencies() {
        // Template manager needs template loader
        if (isset($this->components['template_manager']) && isset($this->components['template_loader'])) {
            if (method_exists($this->components['template_manager'], 'set_template_loader')) {
                $this->components['template_manager']->set_template_loader($this->components['template_loader']);
            }
        }
        
        // Shortcode handler needs dependencies
        if (isset($this->components['shortcode_handler'])) {
            $handler = $this->components['shortcode_handler'];
            
            if (isset($this->components['template_manager']) && method_exists($handler, 'set_template_manager')) {
                $handler->set_template_manager($this->components['template_manager']);
            }
            
            if (isset($this->components['customer_data']) && method_exists($handler, 'set_customer_data')) {
                $handler->set_customer_data($this->components['customer_data']);
            }
            
            if (isset($this->components['edd_integration']) && method_exists($handler, 'set_edd_integration')) {
                $handler->set_edd_integration($this->components['edd_integration']);
            }
        }
    }
    
    /**
     * Load utility components
     */
    private function load_utility_components() {
        $utility_files = array(
            'includes/utils/class-formatter.php'
        );
        
        foreach ($utility_files as $file) {
            $this->safe_require($file);
        }
    }
    
    /**
     * Safely require a file
     */
    private function safe_require($file) {
        $file_path = EDDCDP_PLUGIN_DIR . $file;
        if (file_exists($file_path)) {
            try {
                require_once $file_path;
                return true;
            } catch (Exception $e) {
                error_log('EDDCDP: Error requiring file ' . $file . ': ' . $e->getMessage());
            }
        }
        return false;
    }
    
    /**
     * Safely instantiate a class
     */
    private function safe_instantiate($key, $class_name) {
        if (class_exists($class_name)) {
            try {
                $this->components[$key] = new $class_name();
                return true;
            } catch (Exception $e) {
                error_log('EDDCDP: Error instantiating ' . $class_name . ': ' . $e->getMessage());
            }
        }
        return false;
    }
    
    /**
     * Safely instantiate and initialize a component
     */
    private function safe_instantiate_and_init($key, $class_name) {
        if ($this->safe_instantiate($key, $class_name)) {
            if (method_exists($this->components[$key], 'init')) {
                try {
                    $this->components[$key]->init();
                } catch (Exception $e) {
                    error_log('EDDCDP: Error initializing ' . $class_name . ': ' . $e->getMessage());
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * Setup shortcodes
     */
    private function setup_shortcodes() {
        // If new shortcode handler exists, it will handle registration
        if (isset($this->components['shortcode_handler'])) {
            return;
        }
        
        // Legacy shortcode handling
        add_shortcode('edd_customer_dashboard_pro', array($this, 'render_dashboard'));
        add_action('wp', array($this, 'maybe_replace_edd_shortcodes'), 15);
    }
    
    /**
     * Handle fullscreen mode
     */
    public function handle_fullscreen_mode() {
        if (isset($this->components['fullscreen_manager'])) {
            if (method_exists($this->components['fullscreen_manager'], 'should_load_fullscreen') &&
                $this->components['fullscreen_manager']->should_load_fullscreen()) {
                $this->components['fullscreen_manager']->load_fullscreen();
            }
        }
    }
    
    /**
     * Legacy shortcode rendering (fallback)
     */
    public function render_dashboard($atts = array()) {
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }

        $user = wp_get_current_user();
        $customer = new EDD_Customer($user->ID, true);

        if (!$customer->id) {
            return '<p>' . esc_html__('No customer data found.', 'edd-customer-dashboard-pro') . '</p>';
        }

        $template_data = array(
            'user' => $user,
            'customer' => $customer,
            'settings' => get_option('eddcdp_settings', array()),
            'enabled_sections' => $this->get_enabled_sections(),
            'dashboard_data' => isset($this->components['dashboard_data']) ? $this->components['dashboard_data'] : null,
            'view_mode' => 'dashboard'
        );

        if (isset($this->components['template_loader'])) {
            return $this->components['template_loader']->load_template(null, $template_data);
        }

        return '<p>' . esc_html__('Dashboard template not available.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    /**
     * Maybe replace EDD shortcodes
     */
    public function maybe_replace_edd_shortcodes() {
        $settings = get_option('eddcdp_settings', array());
        
        if (!isset($settings['replace_edd_pages']) || !$settings['replace_edd_pages']) {
            return;
        }
        
        $shortcodes_to_replace = array(
            'purchase_history' => array($this, 'render_dashboard'),
            'edd_profile_editor' => array($this, 'render_dashboard'),
            'download_history' => array($this, 'render_dashboard'),
            'edd_receipt' => array($this, 'render_dashboard')
        );
        
        foreach ($shortcodes_to_replace as $shortcode => $callback) {
            remove_all_shortcodes($shortcode);
            add_shortcode($shortcode, $callback);
        }
    }
    
    /**
     * Get enabled sections
     */
    private function get_enabled_sections() {
        $settings = get_option('eddcdp_settings', array());
        return isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) 
            ? $settings['enabled_sections'] 
            : array();
    }
    
    /**
     * Get login form
     */
    private function get_login_form() {
        if (function_exists('edd_login_form')) {
            return edd_login_form();
        }
        return '<p>' . esc_html__('Please log in to view your dashboard.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        if (!is_user_logged_in()) {
            return;
        }
        
        // Use asset manager if available
        if (isset($this->components['asset_manager'])) {
            return; // Asset manager handles this
        }
        
        // Legacy asset loading
        if (isset($this->components['template_loader'])) {
            $this->components['template_loader']->enqueue_template_assets();
        }
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
     * Check if EDD is active
     */
    private function is_edd_active() {
        return class_exists('Easy_Digital_Downloads');
    }
    
    /**
     * Show EDD missing notice
     */
    public function show_edd_missing_notice() {
        $message = __('EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and activated.', 'edd-customer-dashboard-pro');
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    }
    
    /**
     * Plugin activation
     */
    public function activation() {
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
            'fullscreen_mode' => false
        );
        
        add_option('eddcdp_settings', $default_options);
        add_option('eddcdp_activated', true);
        wp_cache_flush();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivation() {
        wp_cache_flush();
        delete_option('eddcdp_activated');
    }
    
    /**
     * Get component (for backward compatibility)
     */
    public function get_component($component_name) {
        return isset($this->components[$component_name]) ? $this->components[$component_name] : null;
    }
    
    /**
     * Legacy method compatibility
     */
    public function get_template_loader() {
        return $this->get_component('template_loader');
    }
    
    public function get_admin_settings() {
        return $this->get_component('admin_settings');
    }
    
    public function get_dashboard_data() {
        return $this->get_component('dashboard_data');
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
 * Helper functions
 */
function eddcdp_is_active() {
    return get_option('eddcdp_activated', false);
}

function eddcdp_get_settings() {
    return get_option('eddcdp_settings', array());
}

function eddcdp_get_enabled_sections() {
    $settings = eddcdp_get_settings();
    return isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) 
        ? $settings['enabled_sections'] 
        : array();
}

// Start the plugin
eddcdp_init();