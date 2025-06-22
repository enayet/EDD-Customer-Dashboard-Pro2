<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://yoursite.com/edd-dashboard-pro
 * Description: Custom dashboard templates for Easy Digital Downloads customer area
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * Text Domain: eddcdp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EDDCDP_VERSION', '1.0.0');
define('EDDCDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDDCDP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDDCDP_PLUGIN_FILE', __FILE__);

/**
 * Main EDD Dashboard Pro Class
 */
class EDDCDP_Dashboard_Pro {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get instance
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
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if EDD is active
        if (!$this->is_edd_active()) {
            add_action('admin_notices', array($this, 'edd_missing_notice'));
            return;
        }
        
        $this->load_textdomain();
        $this->includes();
        $this->hooks();
    }
    
    /**
     * Check if EDD is active
     */
    private function is_edd_active() {
        return class_exists('Easy_Digital_Downloads');
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'eddcdp',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Include required files
     */
    public function includes() {
        require_once EDDCDP_PLUGIN_DIR . 'includes/class-admin.php';
        require_once EDDCDP_PLUGIN_DIR . 'includes/class-templates.php';
        require_once EDDCDP_PLUGIN_DIR . 'includes/class-shortcodes.php';
    }
    
    /**
     * Setup hooks
     */
    public function hooks() {
        // Initialize admin
        if (is_admin()) {
            new EDDCDP_Admin();
        }
        
        // Initialize templates
        new EDDCDP_Templates();
        
        // Initialize shortcodes
        new EDDCDP_Shortcodes();
        
        // Plugin activation/deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = array(
            'replace_edd_pages' => true,
            'fullscreen_mode' => false,
            'active_template' => 'default',
            'enabled_sections' => array(
                'purchases' => true,
                'downloads' => true,
                'licenses' => true,
                'wishlist' => true,
                'analytics' => true,
                'support' => true
            )
        );
        
        add_option('eddcdp_settings', $default_options);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }
    
    /**
     * EDD missing notice
     */
    public function edd_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and active.', 'eddcdp');
        echo '</p></div>';
    }
}

// Initialize the plugin
function eddcdp_dashboard_pro() {
    return EDDCDP_Dashboard_Pro::get_instance();
}

eddcdp_dashboard_pro();