<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://yourwebsite.com/
 * Description: Modern, template-based dashboard interface for Easy Digital Downloads customers
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 * License: GPL v2 or later
 * Text Domain: edd-customer-dashboard-pro
 * Domain Path: /languages
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
define('EDDCDP_TEXT_DOMAIN', 'edd-customer-dashboard-pro');

// Include core files
require_once EDDCDP_PLUGIN_DIR . 'includes/class-template-loader.php';
require_once EDDCDP_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once EDDCDP_PLUGIN_DIR . 'includes/class-dashboard-data.php';

class EDD_Customer_Dashboard_Pro {
    
    private static $instance = null;
    public $template_loader;
    public $admin_settings;
    public $dashboard_data;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        register_activation_hook(__FILE__, array($this, 'activation'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation'));
    }
    
    public function init() {
        // Check if EDD is active
        if (!class_exists('Easy_Digital_Downloads')) {
            add_action('admin_notices', array($this, 'edd_missing_notice'));
            return;
        }
        
        // Initialize components
        $this->template_loader = new EDDCDP_Template_Loader();
        $this->admin_settings = new EDDCDP_Admin_Settings();
        $this->dashboard_data = new EDDCDP_Dashboard_Data();
        
        // Replace EDD's default shortcodes with our custom one
        $this->replace_edd_shortcodes();
        
        // Add our custom shortcode
        add_shortcode('edd_customer_dashboard_pro', array($this, 'render_dashboard'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            EDDCDP_TEXT_DOMAIN,
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
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
    }
    
    public function deactivation() {
        // Cleanup if needed
    }
    
    private function replace_edd_shortcodes() {
        $settings = get_option('eddcdp_settings', array());
        
        if (isset($settings['replace_edd_pages']) && $settings['replace_edd_pages']) {
            // Replace EDD shortcodes with our dashboard
            remove_shortcode('purchase_history');
            remove_shortcode('edd_profile_editor');
            remove_shortcode('download_history');
            
            add_shortcode('purchase_history', array($this, 'render_dashboard'));
            add_shortcode('edd_profile_editor', array($this, 'render_dashboard'));
            add_shortcode('download_history', array($this, 'render_dashboard'));
        }
    }
    
    public function edd_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and activated.', EDDCDP_TEXT_DOMAIN); ?></p>
        </div>
        <?php
    }
    
    public function enqueue_frontend_assets() {
        if (!is_user_logged_in()) {
            return;
        }
        
        // Get active template
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        
        // Enqueue template-specific assets
        $this->template_loader->enqueue_template_assets($active_template);
    }
    
    public function render_dashboard($atts = array()) {
        if (!is_user_logged_in()) {
            return edd_login_form();
        }
        
        $user = wp_get_current_user();
        $customer = edd_get_customer_by('user_id', $user->ID);
        
        if (!$customer) {
            return '<p>' . __('No customer data found.', EDDCDP_TEXT_DOMAIN) . '</p>';
        }
        
        // Get settings
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        $enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();
        
        // Prepare data for template
        $template_data = array(
            'user' => $user,
            'customer' => $customer,
            'settings' => $settings,
            'enabled_sections' => $enabled_sections,
            'dashboard_data' => $this->dashboard_data
        );
        
        // Load template
        return $this->template_loader->load_template($active_template, $template_data);
    }
    
    public function get_template_loader() {
        return $this->template_loader;
    }
    
    public function get_admin_settings() {
        return $this->admin_settings;
    }
    
    public function get_dashboard_data() {
        return $this->dashboard_data;
    }
}

// Initialize the plugin
function eddcdp_init() {
    return EDD_Customer_Dashboard_Pro::get_instance();
}

// Global access function
function eddcdp() {
    return eddcdp_init();
}

// Start the plugin
eddcdp_init();