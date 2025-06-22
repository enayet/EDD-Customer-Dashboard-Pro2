<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://theweblab.xyz/
 * Description: Modern, template-based dashboard interface for Easy Digital Downloads customers
 * Version: 1.0.3
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
define('EDDCDP_VERSION', '1.0.3');
define('EDDCDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDDCDP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDDCDP_PLUGIN_FILE', __FILE__);
define('EDDCDP_TEXT_DOMAIN', 'edd-customer-dashboard-pro');

/**
 * Main Plugin Class - Clean Architecture
 */
class EDD_Customer_Dashboard_Pro {
    
    private static $instance = null;
    private $component_manager;
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
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        if (!$this->is_edd_active()) {
            add_action('admin_notices', array($this, 'show_edd_missing_notice'));
            return;
        }
        
        $this->load_core_files();
        $this->load_components();
        $this->setup_cache();
    }
    
    /**
     * Load core files
     */
    private function load_core_files() {
        // Load interfaces first
        require_once EDDCDP_PLUGIN_DIR . 'includes/core/interfaces/interface-component.php';
        require_once EDDCDP_PLUGIN_DIR . 'includes/core/interfaces/interface-data-provider.php';
        
        // Load component manager
        require_once EDDCDP_PLUGIN_DIR . 'includes/core/class-component-manager.php';
        $this->component_manager = new EDDCDP_Component_Manager();
        
        // Load utilities
        require_once EDDCDP_PLUGIN_DIR . 'includes/utils/class-formatter.php';
        require_once EDDCDP_PLUGIN_DIR . 'includes/utils/class-validator.php';
        require_once EDDCDP_PLUGIN_DIR . 'includes/utils/class-cache-helper.php';
        require_once EDDCDP_PLUGIN_DIR . 'includes/utils/class-template-data-helper.php';
    }
    
    /**
     * Load and register components
     */
    private function load_components() {
        // Load component files
        $this->load_component_files();
        
        // Register components with manager
        $this->register_components();
        
        // Load all registered components
        $this->component_manager->load_components();
        
        // Store component instances for legacy compatibility
        $this->store_component_instances();
    }
    
    /**
     * Load component files
     */
    private function load_component_files() {
        $component_directories = array(
            'integrations' => array(
                'includes/integrations/class-edd-integration.php',
                'includes/integrations/class-licensing-integration.php',
                'includes/integrations/class-wishlist-integration.php'
            ),
            'data' => array(
                'includes/data/class-customer-data.php',
                'includes/data/class-payment-data.php',
                'includes/data/class-license-data.php',
                'includes/data/class-analytics-data.php'
            ),
            'frontend' => array(
                'includes/frontend/class-asset-manager.php',
                'includes/frontend/class-template-manager.php',
                'includes/frontend/class-shortcode-handler.php',
                'includes/frontend/class-fullscreen-manager.php'
            ),
            'admin' => array(
                'includes/admin/class-admin-ajax.php'
            ),
            'legacy' => array(
                'includes/class-template-loader.php',
                'includes/class-admin-settings.php'
            )
        );
        
        foreach ($component_directories as $directory => $files) {
            foreach ($files as $file) {
                $this->safe_require($file);
            }
        }
    }
    
    /**
     * Register components with the component manager
     */
    private function register_components() {
        $components_to_register = array(
            'EDDCDP_Edd_Integration',
            'EDDCDP_Customer_Data',
            'EDDCDP_Payment_Data',
            'EDDCDP_License_Data',
            'EDDCDP_Analytics_Data',
            'EDDCDP_Asset_Manager',
            'EDDCDP_Template_Manager',
            'EDDCDP_Shortcode_Handler',
            'EDDCDP_Admin_Ajax'
        );
        
        foreach ($components_to_register as $component_class) {
            $this->component_manager->register($component_class);
        }
        
        // Register legacy components
        $this->register_legacy_components();
    }
    
    /**
     * Register legacy components for backward compatibility
     */
    private function register_legacy_components() {
        // Template loader (legacy)
        if (class_exists('EDDCDP_Template_Loader')) {
            $this->components['template_loader'] = new EDDCDP_Template_Loader();
        }
        
        // Admin settings (legacy)
        if (class_exists('EDDCDP_Admin_Settings')) {
            $this->components['admin_settings'] = new EDDCDP_Admin_Settings();
        }
        
        // Fullscreen manager (initialize with dependencies)
        if (class_exists('EDDCDP_Fullscreen_Manager')) {
            $template_loader = isset($this->components['template_loader']) ? $this->components['template_loader'] : null;
            $dashboard_data = isset($this->components['customer_data']) ? $this->components['customer_data'] : null;
            $this->components['fullscreen_manager'] = new EDDCDP_Fullscreen_Manager($template_loader, $dashboard_data);
            
            // Handle fullscreen mode
            add_action('template_redirect', array($this, 'handle_fullscreen_mode'));
        }
    }
    
    /**
     * Store component instances for legacy compatibility
     */
    private function store_component_instances() {
        $component_map = array(
            'edd_integration' => 'EDDCDP_Edd_Integration',
            'customer_data' => 'EDDCDP_Customer_Data',
            'payment_data' => 'EDDCDP_Payment_Data',
            'license_data' => 'EDDCDP_License_Data',
            'analytics_data' => 'EDDCDP_Analytics_Data',
            'asset_manager' => 'EDDCDP_Asset_Manager',
            'template_manager' => 'EDDCDP_Template_Manager',
            'shortcode_handler' => 'EDDCDP_Shortcode_Handler',
            'admin_ajax' => 'EDDCDP_Admin_Ajax'
        );
        
        foreach ($component_map as $key => $class_name) {
            $instance = $this->component_manager->get_component($class_name);
            if ($instance) {
                $this->components[$key] = $instance;
            }
        }
        
        // Setup component dependencies
        $this->setup_component_dependencies();
    }
    
    /**
     * Setup dependencies between components
     */
    private function setup_component_dependencies() {
        // Template manager needs template loader
        if (isset($this->components['template_manager']) && isset($this->components['template_loader'])) {
            $this->components['template_manager']->set_template_loader($this->components['template_loader']);
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
        
        // Customer data needs EDD integration
        if (isset($this->components['customer_data']) && isset($this->components['edd_integration'])) {
            if (method_exists($this->components['customer_data'], 'set_edd_integration')) {
                $this->components['customer_data']->set_edd_integration($this->components['edd_integration']);
            }
        }
        
        // License data needs EDD integration
        if (isset($this->components['license_data']) && isset($this->components['edd_integration'])) {
            if (method_exists($this->components['license_data'], 'set_edd_integration')) {
                $this->components['license_data']->set_edd_integration($this->components['edd_integration']);
            }
        }
    }
    
    /**
     * Setup caching
     */
    private function setup_cache() {
        EDDCDP_Cache_Helper::setup_auto_invalidation();
        EDDCDP_Cache_Helper::schedule_cleanup();
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
        
        // Schedule cache cleanup
        EDDCDP_Cache_Helper::schedule_cleanup();
        
        wp_cache_flush();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivation() {
        // Unschedule cache cleanup
        EDDCDP_Cache_Helper::unschedule_cleanup();
        
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
        return $this->get_component('customer_data'); // Updated to use new component
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